<?php

namespace App\Livewire\Parent;

use App\Models\SessionTaskStudent;
use App\Models\Student;
use App\Models\StudentsSubject;
use App\Services\StudentTaskApprovalService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.layoutMaster')]
class TaskApprovalWorkView extends Component
{
    public int $studentId;

    public string $studentName = '';

    public array $selected = [];

    public array $points = [];

    public array $editingPoints = [];

    public array $pointErrors = [];

    public array $sections = [];

    public ?array $result = null;

    public function mount(int $student): void
    {
        $user = Auth::user();
        abort_unless($user?->hasRole('parent'), 403);

        $studentModel = Student::query()->findOrFail($student);
        abort_unless(
            $user->parent_user
                && $user->parent_user->students()->where('students.id', $studentModel->id)->exists(),
            403
        );

        $this->studentId = $studentModel->id;
        $this->studentName = $studentModel->display_name;
        $this->loadRows();
    }

    public function toggleSubject(int $subjectId, bool $checked): void
    {
        foreach ($this->sections[$subjectId]['tasks'] ?? [] as $task) {
            $this->selected[$task['pivot_id']] = $checked;
        }
    }

    public function toggleAll(bool $checked): void
    {
        foreach ($this->sections as $section) {
            foreach ($section['tasks'] ?? [] as $task) {
                $this->selected[$task['pivot_id']] = $checked;
            }
        }
    }

    public function togglePointEditor(int $pivotId): void
    {
        $this->editingPoints[$pivotId] = ! (bool) ($this->editingPoints[$pivotId] ?? false);
    }

    public function updated(string $property, mixed $value): void
    {
        if (! str_starts_with($property, 'points.')) {
            return;
        }

        $pivotId = (int) Str::after($property, 'points.');
        $error = $this->pointValidationError($pivotId, $value);

        if ($error) {
            $this->pointErrors[$pivotId] = $error;

            return;
        }

        unset($this->pointErrors[$pivotId]);
    }

    public function approveSelected(StudentTaskApprovalService $service): void
    {
        $this->approvePivotMap($service, $this->selected);
    }

    public function approveSubject(StudentTaskApprovalService $service, int $subjectId): void
    {
        $subjectPivotIds = collect($this->sections[$subjectId]['tasks'] ?? [])
            ->pluck('pivot_id')
            ->mapWithKeys(fn ($pivotId): array => [(int) $pivotId => (bool) ($this->selected[$pivotId] ?? false)])
            ->all();

        $this->approvePivotMap($service, $subjectPivotIds);
    }

    public function loadRows(): void
    {
        $this->selected = [];
        $this->points = [];
        $this->editingPoints = [];
        $this->pointErrors = [];

        $rows = app(StudentTaskApprovalService::class)
            ->parentInReviewRows(Auth::user(), $this->studentId);

        $this->sections = $rows
            ->groupBy(fn (SessionTaskStudent $pivot): int => (int) ($pivot->task?->classSession?->subject_id ?? 0))
            ->map(function ($rows, int $subjectId): array {
                $first = $rows->first();

                return [
                    'subject_id' => $subjectId,
                    'subject_title' => $first?->task?->classSession?->subject?->title ?? 'Subject',
                    'tasks' => $rows->map(function (SessionTaskStudent $pivot): array {
                        $task = $pivot->task;
                        $session = $task?->classSession;
                        $default = (int) ($task?->default_points ?? 0);
                        $max = (int) ($task?->max_points ?? $default);

                        $this->selected[$pivot->id] = false;
                        $this->points[$pivot->id] = min($default, $max);

                        return [
                            'pivot_id' => $pivot->id,
                            'title' => $task?->title ?? 'Task',
                            'details_url' => $this->taskDetailsUrl($pivot),
                            'default_points' => $default,
                            'max_points' => $max,
                            'review_submitted_at' => $this->formatDateTime($pivot->review_submitted_at),
                            'session_title' => $session?->title ?: 'Session',
                            'session_title_short' => Str::limit($session?->title ?: 'Session', 42),
                            'session_date' => $this->formatDate($session?->date),
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->keyBy('subject_id')
            ->all();
    }

    public function render()
    {
        return view('livewire.parent.task-approval-work-view');
    }

    public function hasSelectedPointErrors(): bool
    {
        foreach ($this->selected as $pivotId => $isSelected) {
            if ($isSelected && $this->pointValidationError((int) $pivotId, $this->points[$pivotId] ?? null)) {
                return true;
            }
        }

        return false;
    }

    public function subjectHasSelectedPointErrors(int $subjectId): bool
    {
        foreach ($this->sections[$subjectId]['tasks'] ?? [] as $task) {
            $pivotId = (int) $task['pivot_id'];

            if (($this->selected[$pivotId] ?? false) && $this->pointValidationError($pivotId, $this->points[$pivotId] ?? null)) {
                return true;
            }
        }

        return false;
    }

    private function approvePivotMap(StudentTaskApprovalService $service, array $selectedMap): void
    {
        $this->refreshSelectedPointErrors($selectedMap);

        if ($this->hasPointErrorsForMap($selectedMap)) {
            return;
        }

        $approved = 0;
        $skipped = [];

        foreach ($selectedMap as $pivotId => $isSelected) {
            if (! $isSelected) {
                continue;
            }

            try {
                $service->approveAsParent(
                    Auth::user(),
                    (int) $pivotId,
                    (int) ($this->points[$pivotId] ?? 0)
                );
                $approved++;
            } catch (\Throwable $e) {
                $skipped[] = [
                    'id' => (int) $pivotId,
                    'reason' => $this->skipReason($e),
                ];
            }
        }

        $this->result = [
            'approved' => $approved,
            'skipped' => count($skipped),
            'skipped_rows' => $skipped,
        ];

        $this->loadRows();
    }

    private function skipReason(\Throwable $e): string
    {
        return $e instanceof \Illuminate\Validation\ValidationException
            ? 'invalid_points'
            : 'stale_or_not_allowed';
    }

    private function maxPointsForPivot(int $pivotId): int
    {
        foreach ($this->sections as $section) {
            foreach ($section['tasks'] ?? [] as $task) {
                if ((int) $task['pivot_id'] === $pivotId) {
                    return (int) $task['max_points'];
                }
            }
        }

        return 0;
    }

    private function refreshSelectedPointErrors(array $selectedMap): void
    {
        foreach ($selectedMap as $pivotId => $isSelected) {
            if (! $isSelected) {
                continue;
            }

            $pivotId = (int) $pivotId;
            $error = $this->pointValidationError($pivotId, $this->points[$pivotId] ?? null);

            if ($error) {
                $this->pointErrors[$pivotId] = $error;
            } else {
                unset($this->pointErrors[$pivotId]);
            }
        }
    }

    private function hasPointErrorsForMap(array $selectedMap): bool
    {
        foreach ($selectedMap as $pivotId => $isSelected) {
            if ($isSelected && isset($this->pointErrors[(int) $pivotId])) {
                return true;
            }
        }

        return false;
    }

    private function pointValidationError(int $pivotId, mixed $value): ?string
    {
        $max = $this->maxPointsForPivot($pivotId);

        if ($value === null || $value === '') {
            return "Enter points from 0 to {$max}.";
        }

        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return 'Use a whole number.';
        }

        $points = (int) $value;

        if ($points < 0 || $points > $max) {
            return "Points must be from 0 to {$max}.";
        }

        return null;
    }

    private function taskDetailsUrl(SessionTaskStudent $pivot): ?string
    {
        $task = $pivot->task;
        $session = $task?->classSession;

        if (! $task || ! $session) {
            return null;
        }

        $studentSubjectId = StudentsSubject::query()
            ->where('student_id', $pivot->student_id)
            ->where('class_subject_id', $session->class_subject_id)
            ->where('status', 'active')
            ->value('id');

        if (! $studentSubjectId) {
            return null;
        }

        return route('student.sessions', [$studentSubjectId, $pivot->student_id])
            .'?'.http_build_query(['open_session' => $session->id])
            .'#task-'.$task->id;
    }

    private function formatDateTime(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('d M, H:i') : null;
    }

    private function formatDate(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('d M') : null;
    }
}
