<?php

namespace App\Livewire\Admin;

use App\Models\ClassModel;
use App\Models\ClassSubject;
use App\Models\GradeLevelSubject;
use App\Models\Subject;
use App\Models\TeacherSubjectClass;
use App\Models\User;
use App\Support\BookingSubjectProvisioning;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class TeacherClassAssignments extends Component
{
    use WithPagination;

    private const LAUNCH_SUBJECT_IDS = [
        BookingSubjectProvisioning::SUBJECT_QURAN_MEMORIZATION,
        BookingSubjectProvisioning::SUBJECT_ARABIC_LANGUAGE,
        BookingSubjectProvisioning::SUBJECT_QURANIC_ARABIC,
        BookingSubjectProvisioning::SUBJECT_SANAD_PROGRAM,
        BookingSubjectProvisioning::SUBJECT_MY_DEEN_JOURNEY,
        BookingSubjectProvisioning::SUBJECT_WELL_BEING,
    ];

    protected string $paginationTheme = 'bootstrap';

    public ?int $teacherId = null;

    public ?int $classId = null;

    public ?int $subjectId = null;

    public string $search = '';

    public string $statusFilter = 'current';

    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'current'],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        abort_unless($this->canManageTeacherAssignments(), 403);
        $this->normalizeFilters();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function assignTeacher(): void
    {
        abort_unless($this->canManageTeacherAssignments(), 403);

        $data = $this->validate([
            'teacherId' => ['required', 'integer', Rule::exists('users', 'id')],
            'classId' => ['required', 'integer', Rule::exists('classes', 'id')],
            'subjectId' => ['required', 'integer', Rule::exists('subjects', 'id')],
        ]);

        $teacher = $this->activeTeacher((int) $data['teacherId']);
        $class = $this->activeClass((int) $data['classId']);
        $subject = $this->activeLaunchSubject((int) $data['subjectId']);
        $gradeLevelSubject = $this->gradeLevelSubjectFor($class, $subject);
        $classSubject = ClassSubject::query()->firstOrCreate([
            'class_id' => $class->id,
            'grade_level_subject_id' => $gradeLevelSubject->id,
        ]);

        $assignment = TeacherSubjectClass::query()->firstOrNew([
            'user_teacher_coteacher_id' => $teacher->id,
            'class_subject_id' => $classSubject->id,
        ]);

        $assignment->forceFill([
            'teacher_name' => $teacher->name,
            'grade_id' => (int) ($class->grade_level_id ?? $gradeLevelSubject->grade_level_id),
            'grade_name' => $class->grade_name,
            'class_id' => $class->id,
            'class_name' => $class->title,
            'class_img' => $class->class_img,
            'subject_id' => $subject->id,
            'subject_name' => BookingSubjectProvisioning::displaySubjectName((int) $subject->id, $subject->title),
            'status' => 'current',
            'assigned_at' => $assignment->assigned_at ?: now(),
            'removed_at' => null,
        ])->save();

        $this->reset(['teacherId', 'classId', 'subjectId']);
        $this->resetValidation();
        $this->resetPage();
        $this->dispatch('teacher-assignment-form-reset');

        session()->flash('success', 'Teacher assignment saved.');
    }

    public function deactivateAssignment(int $assignmentId): void
    {
        abort_unless($this->canManageTeacherAssignments(), 403);

        $assignment = TeacherSubjectClass::query()->findOrFail($assignmentId);
        $assignment->forceFill([
            'status' => 'inactive',
            'removed_at' => now(),
        ])->save();

        session()->flash('success', 'Teacher assignment deactivated.');
    }

    public function reactivateAssignment(int $assignmentId): void
    {
        abort_unless($this->canManageTeacherAssignments(), 403);

        $assignment = TeacherSubjectClass::query()->findOrFail($assignmentId);
        $assignment->forceFill([
            'status' => 'current',
            'assigned_at' => $assignment->assigned_at ?: now(),
            'removed_at' => null,
        ])->save();

        session()->flash('success', 'Teacher assignment reactivated.');
    }

    public function render(): View
    {
        $this->normalizeFilters();

        return view('livewire.admin.teacher-class-assignments', [
            'teachers' => $this->teacherOptions(),
            'classes' => $this->classOptions(),
            'subjects' => $this->subjectOptions(),
            'assignments' => $this->assignmentQuery()->paginate($this->perPage),
            'stats' => $this->assignmentStats(),
        ])->layout('components.layouts.app', ['title' => 'Teacher Class Assignments']);
    }

    protected function teacherOptions()
    {
        return User::role('teacher')
            ->when(Schema::hasColumn('users', 'status'), fn (Builder $query) => $query->where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    protected function classOptions()
    {
        return ClassModel::query()
            ->where('status', 'active')
            ->orderBy('title')
            ->get(['id', 'title', 'grade_level_id', 'grade_name', 'academic_year_id']);
    }

    protected function subjectOptions()
    {
        return Subject::query()
            ->whereIn('id', self::LAUNCH_SUBJECT_IDS)
            ->where('active', true)
            ->where('row_status', 'current')
            ->orderByRaw($this->subjectOrderSql())
            ->get(['id', 'title']);
    }

    protected function assignmentQuery(): Builder
    {
        return TeacherSubjectClass::query()
            ->with(['teacher:id,name,email,status', 'class:id,title,grade_name', 'subject:id,title'])
            ->whereIn('subject_id', self::LAUNCH_SUBJECT_IDS)
            ->when($this->statusFilter !== 'all', function (Builder $query): void {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->search !== '', function (Builder $query): void {
                $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($this->search)).'%';

                $query->where(function (Builder $nested) use ($term): void {
                    $nested->where('teacher_name', 'like', $term)
                        ->orWhere('class_name', 'like', $term)
                        ->orWhere('subject_name', 'like', $term)
                        ->orWhereHas('teacher', fn (Builder $teacherQuery) => $teacherQuery->where('email', 'like', $term));
                });
            })
            ->orderByRaw("CASE WHEN status IN ('current', 'active') THEN 0 WHEN status = 'inactive' THEN 1 ELSE 2 END")
            ->latest('id');
    }

    protected function assignmentStats(): array
    {
        return [
            'current' => TeacherSubjectClass::query()
                ->whereIn('subject_id', self::LAUNCH_SUBJECT_IDS)
                ->whereIn('status', ['current', 'active'])
                ->count(),
            'inactive' => TeacherSubjectClass::query()
                ->whereIn('subject_id', self::LAUNCH_SUBJECT_IDS)
                ->where('status', 'inactive')
                ->count(),
            'teachers' => TeacherSubjectClass::query()
                ->whereIn('subject_id', self::LAUNCH_SUBJECT_IDS)
                ->whereIn('status', ['current', 'active'])
                ->whereNotNull('user_teacher_coteacher_id')
                ->count(DB::raw('DISTINCT user_teacher_coteacher_id')),
        ];
    }

    protected function activeTeacher(int $teacherId): User
    {
        $teacher = User::query()
            ->whereKey($teacherId)
            ->when(Schema::hasColumn('users', 'status'), fn (Builder $query) => $query->where('status', 'active'))
            ->first();

        if (! $teacher || ! $teacher->hasRole('teacher')) {
            throw ValidationException::withMessages([
                'teacherId' => 'Choose an active teacher user.',
            ]);
        }

        return $teacher;
    }

    protected function activeClass(int $classId): ClassModel
    {
        $class = ClassModel::query()
            ->whereKey($classId)
            ->where('status', 'active')
            ->first();

        if (! $class) {
            throw ValidationException::withMessages([
                'classId' => 'Choose an active class.',
            ]);
        }

        if (! $class->grade_level_id) {
            throw ValidationException::withMessages([
                'classId' => 'The selected class is missing a learner level.',
            ]);
        }

        return $class;
    }

    protected function activeLaunchSubject(int $subjectId): Subject
    {
        $subject = Subject::query()
            ->whereKey($subjectId)
            ->whereIn('id', self::LAUNCH_SUBJECT_IDS)
            ->where('active', true)
            ->where('row_status', 'current')
            ->first();

        if (! $subject) {
            throw ValidationException::withMessages([
                'subjectId' => 'Choose an active To Quran class subject.',
            ]);
        }

        return $subject;
    }

    protected function gradeLevelSubjectFor(ClassModel $class, Subject $subject): GradeLevelSubject
    {
        $query = GradeLevelSubject::query()
            ->where('grade_level_id', $class->grade_level_id)
            ->where('subject_id', $subject->id)
            ->where('status', 'active');

        if ($class->academic_year_id) {
            $query->where(function (Builder $nested) use ($class): void {
                $nested->where('academic_year_id', $class->academic_year_id)
                    ->orWhereNull('academic_year_id');
            })->orderByRaw('CASE WHEN academic_year_id = ? THEN 0 ELSE 1 END', [$class->academic_year_id]);
        }

        $gradeLevelSubject = $query->first();

        if (! $gradeLevelSubject) {
            throw ValidationException::withMessages([
                'subjectId' => 'This class does not have the selected class subject in its learner-level catalog.',
            ]);
        }

        return $gradeLevelSubject;
    }

    protected function canManageTeacherAssignments(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    protected function normalizeFilters(): void
    {
        if (! in_array($this->statusFilter, ['all', 'current', 'active', 'inactive', 'archived'], true)) {
            $this->statusFilter = 'current';
        }

        if (! in_array($this->perPage, [10, 25, 50], true)) {
            $this->perPage = 10;
        }
    }

    protected function subjectOrderSql(): string
    {
        $cases = collect(self::LAUNCH_SUBJECT_IDS)
            ->values()
            ->map(fn (int $id, int $index): string => "WHEN {$id} THEN {$index}")
            ->implode(' ');

        return "CASE id {$cases} ELSE 99 END";
    }
}
