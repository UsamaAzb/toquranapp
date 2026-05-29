<?php

namespace App\Livewire\Admin\Students;

use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\GradeLevel;
use App\Models\Student;
use App\Models\StudentsSubject;
use App\Models\TeacherSubjectClass;
use App\Models\User;
use App\Support\BookingSubjectProvisioning;
use App\Support\DefaultTeacherResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class SubjectManager extends Component
{
    public int $studentId;

    public bool $subjectsSynced = false;

    public function mount(int $studentId): void
    {
        $this->ensureCanManageStudentSubjects();

        $this->studentId = $studentId;
        $this->syncMissingSubjectsForCurrentStudent();
    }

    public function toggleSubject(int $studentSubjectId): void
    {
        $this->ensureCanManageStudentSubjects();

        $studentSubject = StudentsSubject::query()
            ->where('student_id', $this->studentId)
            ->findOrFail($studentSubjectId);

        DB::transaction(function () use ($studentSubject): void {
            $studentSubject->status = $studentSubject->status === 'active' ? 'inactive' : 'active';
            $studentSubject->save();

            BookingSubjectProvisioning::syncTeacherSubjectClassStatus($studentSubject->class_subject_id);
        });

        $this->dispatch('toast', type: 'success', message: 'Subject access updated.');
    }

    public function assignTeacherToSubject(int $studentSubjectId, mixed $teacherId = null): void
    {
        $this->ensureCanManageStudentSubjects();

        $teacherId = filled($teacherId) ? (int) $teacherId : null;

        $studentSubject = StudentsSubject::query()
            ->where('student_id', $this->studentId)
            ->with(['gradeLevelSubject.subject', 'classSubject.class', 'student.gradeLevel', 'student.currentClass'])
            ->findOrFail($studentSubjectId);

        $classSubject = $this->ensureClassSubject($studentSubject);

        if (! $teacherId) {
            DB::transaction(function () use ($classSubject): void {
                TeacherSubjectClass::query()
                    ->where('class_subject_id', $classSubject->id)
                    ->update([
                        'status' => 'inactive',
                        'assigned_at' => null,
                        'removed_at' => now(),
                    ]);
            });

            $this->dispatch('toast', type: 'success', message: 'Teacher assignment cleared.');
            $this->dispatch('student-subject-teacher-selects-refresh');

            return;
        }

        $teacher = $this->activeTeacher($teacherId);
        $student = $studentSubject->student;
        $gradeLevelSubject = $studentSubject->gradeLevelSubject;
        $class = $classSubject->class ?? $student?->currentClass;
        $subject = $gradeLevelSubject?->subject;
        $subjectId = (int) ($subject?->id ?? $gradeLevelSubject?->subject_id ?? 0);
        $assignmentStatus = $studentSubject->status === 'active' ? 'current' : 'inactive';

        DB::transaction(function () use ($teacher, $student, $studentSubject, $classSubject, $class, $gradeLevelSubject, $subject, $subjectId, $assignmentStatus): void {
            $assignment = TeacherSubjectClass::query()
                ->where('class_subject_id', $classSubject->id)
                ->orderByRaw("CASE WHEN status IN ('current', 'active') THEN 0 WHEN status = 'inactive' THEN 1 ELSE 2 END")
                ->oldest('id')
                ->first() ?? new TeacherSubjectClass;

            TeacherSubjectClass::query()
                ->where('class_subject_id', $classSubject->id)
                ->when($assignment->exists, fn (Builder $query) => $query->whereKeyNot($assignment->id))
                ->update([
                    'status' => 'inactive',
                    'assigned_at' => null,
                    'removed_at' => now(),
                ]);

            $assignment->forceFill([
                'user_teacher_coteacher_id' => $teacher->id,
                'teacher_name' => $teacher->name,
                'grade_id' => (int) ($student?->grade_level_id ?? $gradeLevelSubject?->grade_level_id ?? 0),
                'grade_name' => $student?->gradeLevel?->title ?? $student?->grade_name,
                'class_id' => (int) ($classSubject->class_id ?? $student?->current_class_id ?? 0),
                'class_name' => $class?->title,
                'class_img' => $class?->class_img,
                'subject_id' => $subjectId,
                'subject_name' => BookingSubjectProvisioning::displaySubjectName($subjectId, $subject?->title),
                'class_subject_id' => $classSubject->id,
                'status' => $assignmentStatus,
                'assigned_at' => $assignmentStatus === 'current' ? ($assignment->assigned_at ?: now()) : null,
                'removed_at' => $assignmentStatus === 'current' ? null : now(),
            ])->save();

            if (! $studentSubject->class_subject_id) {
                $studentSubject->class_subject_id = $classSubject->id;
                $studentSubject->save();
            }
        });

        $this->dispatch('toast', type: 'success', message: 'Teacher assignment saved.');
        $this->dispatch('student-subject-teacher-selects-refresh');
    }

    public function render()
    {
        $student = Student::query()
            ->with(['gradeLevel', 'currentClass'])
            ->findOrFail($this->studentId);

        $this->syncMissingSubjectsForCurrentStudent();

        $studentSubjects = StudentsSubject::query()
            ->where('student_id', $this->studentId)
            ->with(['gradeLevelSubject.subject', 'classSubject'])
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get();

        return view('livewire.admin.students.subject-manager', [
            'student' => $student,
            'studentSubjects' => $studentSubjects,
            'teachers' => $this->teacherOptions(),
            'teacherAssignments' => $this->teacherAssignmentMap($studentSubjects->pluck('class_subject_id')->filter()->map(fn ($id) => (int) $id)->all()),
        ]);
    }

    protected function syncMissingSubjectsForCurrentStudent(): void
    {
        if ($this->subjectsSynced) {
            return;
        }

        $student = Student::query()
            ->with(['gradeLevel', 'currentClass'])
            ->findOrFail($this->studentId);

        $this->syncMissingSubjects($student);
        $this->subjectsSynced = true;
    }

    protected function syncMissingSubjects(Student $student): void
    {
        if (! $student->grade_level_id || ! $student->current_class_id) {
            return;
        }

        $subjectPlan = BookingSubjectProvisioning::planForGradeLevel((int) $student->grade_level_id);

        if ($subjectPlan === []) {
            return;
        }

        $gradeName = $student->gradeLevel?->title
            ?? GradeLevel::query()->whereKey($student->grade_level_id)->value('title');
        $className = $student->currentClass?->title ?? 'Class #'.$student->current_class_id;
        $academicYearId = $this->currentAcademicYearId();
        $defaultTeacher = app(DefaultTeacherResolver::class)->assignmentPayload();

        DB::transaction(function () use ($student, $subjectPlan, $gradeName, $className, $academicYearId, $defaultTeacher): void {
            foreach ($subjectPlan as $subject) {
                $classSubject = ClassSubject::firstOrCreate([
                    'grade_level_subject_id' => $subject['grade_level_subject_id'],
                    'class_id' => $student->current_class_id,
                ]);

                $studentSubject = StudentsSubject::firstOrCreate(
                    [
                        'grade_level_subject_id' => $subject['grade_level_subject_id'],
                        'student_id' => $student->id,
                    ],
                    [
                        'academic_year_id' => $academicYearId,
                        'status' => $subject['student_status'] ?? 'inactive',
                        'enrolled_at' => now()->toDateString(),
                        'class_subject_id' => $classSubject->id,
                    ]
                );

                TeacherSubjectClass::firstOrCreate(
                    [
                        'user_teacher_coteacher_id' => $defaultTeacher['user_teacher_coteacher_id'],
                        'class_id' => $student->current_class_id,
                        'subject_id' => $subject['subject_id'],
                        'class_subject_id' => $classSubject->id,
                    ],
                    [
                        'teacher_name' => $defaultTeacher['teacher_name'],
                        'grade_id' => $student->grade_level_id,
                        'grade_name' => $gradeName,
                        'class_name' => $className,
                        'subject_name' => $subject['subject_name'],
                        'status' => $subject['teacher_status'] ?? 'inactive',
                        'assigned_at' => ($subject['teacher_status'] ?? 'inactive') === 'active' ? now() : null,
                    ]
                );

                if (! $studentSubject->class_subject_id) {
                    $studentSubject->class_subject_id = $classSubject->id;
                    $studentSubject->save();
                }

                if ($studentSubject->status === 'active') {
                    BookingSubjectProvisioning::syncTeacherSubjectClassStatus($studentSubject->class_subject_id);
                }
            }
        });
    }

    protected function currentAcademicYearId(): int
    {
        return AcademicYear::currentId();
    }

    protected function activeTeacher(int $teacherId): User
    {
        if (! $this->teacherRoleExists()) {
            throw ValidationException::withMessages([
                'teacher' => 'Teacher role is not available yet.',
            ]);
        }

        $teacher = User::query()
            ->whereKey($teacherId)
            ->when(Schema::hasColumn('users', 'status'), fn (Builder $query) => $query->where('status', 'active'))
            ->first();

        if (! $teacher || ! $teacher->hasRole('teacher')) {
            throw ValidationException::withMessages([
                'teacher' => 'Choose an active teacher user.',
            ]);
        }

        return $teacher;
    }

    protected function ensureClassSubject(StudentsSubject $studentSubject): ClassSubject
    {
        if ($studentSubject->classSubject) {
            return $studentSubject->classSubject;
        }

        $student = $studentSubject->student;

        if (! $student?->current_class_id) {
            throw ValidationException::withMessages([
                'teacher' => 'This student is missing a current class before a teacher can be assigned.',
            ]);
        }

        $classSubject = ClassSubject::firstOrCreate([
            'grade_level_subject_id' => $studentSubject->grade_level_subject_id,
            'class_id' => $student->current_class_id,
        ]);

        $studentSubject->class_subject_id = $classSubject->id;
        $studentSubject->save();

        return $classSubject->load('class');
    }

    protected function teacherOptions(): Collection
    {
        if (! $this->teacherRoleExists()) {
            return collect();
        }

        return User::role('teacher')
            ->when(Schema::hasColumn('users', 'status'), fn (Builder $query) => $query->where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    protected function teacherAssignmentMap(array $classSubjectIds): array
    {
        if ($classSubjectIds === [] || ! $this->teacherRoleExists()) {
            return [];
        }

        return TeacherSubjectClass::query()
            ->with('teacher:id,name,email,status')
            ->whereIn('class_subject_id', $classSubjectIds)
            ->whereNotNull('user_teacher_coteacher_id')
            ->whereHas('teacher', function (Builder $query): void {
                $query->role('teacher')
                    ->when(Schema::hasColumn('users', 'status'), fn (Builder $statusQuery) => $statusQuery->where('status', 'active'));
            })
            ->orderByRaw("CASE WHEN status IN ('current', 'active') THEN 0 WHEN assigned_at IS NOT NULL THEN 1 ELSE 2 END")
            ->latest('id')
            ->get()
            ->unique('class_subject_id')
            ->mapWithKeys(fn (TeacherSubjectClass $assignment): array => [
                (int) $assignment->class_subject_id => [
                    'teacher_id' => (int) $assignment->user_teacher_coteacher_id,
                    'teacher_name' => $assignment->teacher?->name ?? $assignment->teacher_name,
                    'status' => $assignment->status,
                ],
            ])
            ->all();
    }

    protected function teacherRoleExists(): bool
    {
        return Schema::hasTable('roles')
            && Role::query()
                ->where('name', 'teacher')
                ->where('guard_name', 'web')
                ->exists();
    }

    protected function ensureCanManageStudentSubjects(): void
    {
        $user = auth()->user();

        abort_unless($user && $user->hasAnyRole(['super_admin', 'admin']), 403);
    }
}
