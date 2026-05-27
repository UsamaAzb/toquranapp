<?php

namespace App\Livewire\Teacher;

use App\Models\AcademicYear;
use App\Models\DifferentiatedTask;
use App\Models\Student;
use App\Models\TeacherSubjectClass;
use App\Services\DifferentiatedTaskAssignmentService;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use RuntimeException;

class DifferentiatedTaskAssignmentModal extends Component
{
    public bool $show = false;

    public ?int $taskId = null;

    public ?int $activeVersionId = null;

    public string $search = '';

    /** @var array<int, bool> */
    public array $selectedStudentIds = [];

    #[On('open-differentiated-task-assignment-modal')]
    public function open(int $taskId, int $versionId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId)->load([
            'versions' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
        ]);

        abort_if($task->isArchived(), 404);
        abort_unless($task->versions->contains('id', $versionId), 404);

        $this->resetValidation();
        $this->show = true;
        $this->taskId = $task->id;
        $this->activeVersionId = $versionId;
        $this->search = '';
        $this->selectedStudentIds = [];
    }

    public function close(): void
    {
        $this->show = false;
    }

    public function saveBulk(DifferentiatedTaskAssignmentService $assignmentService): void
    {
        $task = $this->resolveOwnedTaskOrFail((int) $this->taskId)->load('versions');
        abort_if($task->isArchived(), 404);
        $version = $task->versions->firstWhere('id', $this->activeVersionId);
        abort_if($version === null, 404);

        $allRows = $this->assignmentRows($task, false);
        $this->initializeSelectedStudents($allRows, (int) $version->id);

        $selectedStudentIds = collect($this->selectedStudentIds)
            ->filter(fn ($selected): bool => $this->truthy($selected))
            ->keys()
            ->map(fn ($studentId): int => (int) $studentId)
            ->values()
            ->all();
        $managedStudentIds = $allRows
            ->pluck('student_id')
            ->map(fn ($studentId): int => (int) $studentId)
            ->values()
            ->all();

        try {
            $assignmentService->bulkSave(
                (int) $task->id,
                (int) $version->id,
                $selectedStudentIds,
                (int) Auth::id(),
                (int) $task->subject_id,
                $managedStudentIds
            );
        } catch (RuntimeException $exception) {
            $this->addError('assignment', $exception->getMessage());

            return;
        }

        $this->dispatch('differentiated-task-assignment-saved');
        $this->close();
    }

    public function render(): View
    {
        if (! $this->show || $this->taskId === null) {
            return view('livewire.teacher.differentiated-task-assignment-modal', [
                'task' => null,
                'activeVersion' => null,
                'sections' => [],
            ]);
        }

        $task = $this->resolveOwnedTaskOrFail($this->taskId)->load([
            'versions' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
        ]);
        $activeVersion = $task->versions->firstWhere('id', $this->activeVersionId) ?? $task->versions->first();
        $this->activeVersionId = $activeVersion?->id ? (int) $activeVersion->id : null;

        $allRows = $this->assignmentRows($task, false);
        $visibleRows = trim($this->search) === ''
            ? $allRows
            : $this->assignmentRows($task, true);

        if ($activeVersion) {
            $this->initializeSelectedStudents($allRows, (int) $activeVersion->id);
        }

        return view('livewire.teacher.differentiated-task-assignment-modal', [
            'task' => $task,
            'activeVersion' => $activeVersion,
            'sections' => $activeVersion
                ? $this->buildSections($visibleRows, (int) $activeVersion->id)
                : [],
        ]);
    }

    private function assignmentRows(DifferentiatedTask $task, bool $applySearch): SupportCollection
    {
        $students = $this->eligibleStudents($task, $applySearch);
        $studentIds = $students->pluck('id')->map(fn ($value): int => (int) $value)->all();

        if (empty($studentIds)) {
            return collect();
        }

        $assignments = DB::table('differentiated_task_student_assignments as assignments')
            ->leftJoin('differentiated_task_versions as versions', 'versions.id', '=', 'assignments.version_id')
            ->select(
                'assignments.student_id',
                'assignments.version_id',
                'versions.display_name as version_display_name'
            )
            ->where('assignments.differentiated_task_id', $task->id)
            ->whereIn('assignments.student_id', $studentIds)
            ->whereNull('assignments.effective_to_date')
            ->get()
            ->keyBy('student_id');

        $deliveredToday = DB::table('class_sessions')
            ->where('differentiated_task_id', $task->id)
            ->whereIn('student_id', $studentIds)
            ->whereDate('generated_for_date', now('Africa/Cairo')->toDateString())
            ->pluck('student_id')
            ->map(fn ($value): int => (int) $value)
            ->flip();

        return $students->map(function (Student $student) use ($assignments, $deliveredToday): array {
            $assignment = $assignments->get($student->id);
            $fatherName = trim((string) ($student->father_name ?? ''));
            $displayName = $student->display_name;
            $studentWithFather = trim($student->first_name.' '.($fatherName !== '' ? $fatherName : ($student->parent?->first_name ?? '')));

            return [
                'student_id' => (int) $student->id,
                'display_name' => $displayName,
                'student_with_father' => $studentWithFather !== '' ? $studentWithFather : $displayName,
                'class_name' => $this->compactClassLabel($student->currentClass?->title),
                'current_version_id' => $assignment ? (int) $assignment->version_id : null,
                'current_version_name' => $assignment?->version_display_name ?? 'Unassigned',
                'delivered_today' => $deliveredToday->has((int) $student->id),
            ];
        })->values();
    }

    private function eligibleStudents(DifferentiatedTask $task, bool $applySearch): SupportCollection
    {
        $query = Student::query()
            ->whereIn('students.id', $this->eligibleStudentIdsSubquery($task))
            ->where(function ($query): void {
                $query->whereNull('students.account_status')
                    ->orWhere('students.account_status', '')
                    ->orWhere('students.account_status', 'active');
            })
            ->with([
                'parent:id,first_name,last_name',
                'currentClass:id,title',
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('students.id');

        if ($applySearch && trim($this->search) !== '') {
            $search = trim($this->search);

            $query->where(function ($innerQuery) use ($search): void {
                $innerQuery->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('students.id', 'like', "%{$search}%")
                    ->orWhereHas('parent', function ($parentQuery) use ($search): void {
                        $parentQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->get();
    }

    private function buildSections(SupportCollection $rows, int $activeVersionId): array
    {
        return [
            'assigned_here' => [
                'title' => 'Assigned to this version',
                'description' => 'Unselect students here to remove them from this version for future generation.',
                'rows' => $rows
                    ->filter(fn (array $row): bool => (int) ($row['current_version_id'] ?? 0) === $activeVersionId)
                    ->values()
                    ->all(),
            ],
            'unassigned' => [
                'title' => 'Not assigned',
                'description' => 'Select students here to assign this task version.',
                'rows' => $rows
                    ->filter(fn (array $row): bool => $row['current_version_id'] === null)
                    ->values()
                    ->all(),
            ],
            'assigned_elsewhere' => [
                'title' => 'Assigned to another version',
                'description' => 'Select students here to move them to this version for future generation.',
                'rows' => $rows
                    ->filter(fn (array $row): bool => $row['current_version_id'] !== null
                        && (int) $row['current_version_id'] !== $activeVersionId)
                    ->values()
                    ->all(),
            ],
        ];
    }

    private function initializeSelectedStudents(SupportCollection $rows, int $activeVersionId): void
    {
        foreach ($rows as $row) {
            $studentId = (int) $row['student_id'];

            if (! array_key_exists($studentId, $this->selectedStudentIds)) {
                $this->selectedStudentIds[$studentId] = (int) ($row['current_version_id'] ?? 0) === $activeVersionId;
            }
        }
    }

    private function resolveOwnedTaskOrFail(int $taskId): DifferentiatedTask
    {
        $task = DifferentiatedTask::query()
            ->whereKey($taskId)
            ->where('created_by_user_id', Auth::id())
            ->firstOrFail();

        TeacherSubjectClass::query()
            ->where('subject_id', $task->subject_id)
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->firstOrFail();

        return $task;
    }

    private function eligibleStudentIdsSubquery(DifferentiatedTask $task): QueryBuilder
    {
        return DB::table('students_subjects')
            ->select('students_subjects.student_id')
            ->join('grade_level_subjects', 'grade_level_subjects.id', '=', 'students_subjects.grade_level_subject_id')
            ->where('students_subjects.academic_year_id', AcademicYear::currentId())
            ->where('students_subjects.status', 'active')
            ->where('grade_level_subjects.subject_id', $task->subject_id)
            ->whereIn('students_subjects.class_subject_id', $this->ownedClassSubjectIds($task))
            ->distinct();
    }

    /**
     * @return array<int, int>
     */
    private function ownedClassSubjectIds(DifferentiatedTask $task): array
    {
        return TeacherSubjectClass::query()
            ->where('subject_id', $task->subject_id)
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->pluck('class_subject_id')
            ->filter()
            ->map(fn ($value): int => (int) $value)
            ->unique()
            ->values()
            ->all();
    }

    private function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ?? false;
    }

    private function compactClassLabel(?string $classTitle): string
    {
        $classTitle = trim((string) $classTitle);

        if ($classTitle === '') {
            return 'No current class';
        }

        if (preg_match('/\bGrade\s*\d+\b/i', $classTitle, $matches)) {
            return preg_replace('/\s+/', ' ', $matches[0]) ?: $matches[0];
        }

        return $classTitle;
    }
}
