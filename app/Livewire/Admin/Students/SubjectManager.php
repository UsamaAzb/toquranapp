<?php

namespace App\Livewire\Admin\Students;

use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\GradeLevel;
use App\Models\Student;
use App\Models\StudentsSubject;
use App\Models\TeacherSubjectClass;
use App\Support\BookingSubjectProvisioning;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SubjectManager extends Component
{
    public int $studentId;

    public bool $subjectsSynced = false;

    public function mount(int $studentId): void
    {
        $this->studentId = $studentId;
        $this->syncMissingSubjectsForCurrentStudent();
    }

    public function toggleSubject(int $studentSubjectId): void
    {
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

        DB::transaction(function () use ($student, $subjectPlan, $gradeName, $className, $academicYearId): void {
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
                        'user_teacher_coteacher_id' => 3,
                        'class_id' => $student->current_class_id,
                        'subject_id' => $subject['subject_id'],
                        'class_subject_id' => $classSubject->id,
                    ],
                    [
                        'teacher_name' => 'Dr.Osama',
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
}
