<?php

namespace App\Livewire\Teacher;

use App\Models\SessionTaskStudent;
use App\Models\Student;
use App\Models\Subject;
use App\Services\StudentTaskApprovalService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.layoutMaster')]
class TaskApprovalWorkView extends Component
{
    public int $studentId;

    public int $subjectId;

    public string $studentName = '';

    public string $subjectTitle = '';

    public array $selected = [];

    public array $points = [];

    public array $editingPoints = [];

    public array $pointErrors = [];

    public array $tasks = [];

    public ?array $result = null;

    public function mount(int $student, int $subject): void
    {
        abort_unless(Auth::user()?->hasRole('teacher'), 403);

        $this->studentId = $student;
        $this->subjectId = $subject;
        $this->studentName = Student::query()->findOrFail($student)->display_name;
        $this->subjectTitle = Subject::query()->find($subject)?->title ?? 'Subject';
        $this->loadRows();
    }

    public function approveSelected(StudentTaskApprovalService $service): void
    {
        $this->refreshSelectedPointErrors();

        if ($this->hasSelectedPointErrors()) {
            return;
        }

        $approved = 0;
        $skipped = 0;

        foreach ($this->selected as $pivotId => $isSelected) {
            if (! $isSelected) {
                continue;
            }

            try {
                $service->approveAsTeacher(
                    Auth::user(),
                    (int) $pivotId,
                    (int) ($this->points[$pivotId] ?? 0)
                );
                $approved++;
            } catch (\Throwable $exception) {
                Log::warning('Teacher task approval skipped after exception.', [
                    'pivot_id' => (int) $pivotId,
                    'student_id' => $this->studentId,
                    'subject_id' => $this->subjectId,
                    'teacher_id' => Auth::id(),
                    'exception' => $exception,
                ]);
                $skipped++;
            }
        }

        $this->result = ['approved' => $approved, 'skipped' => $skipped];
        $this->loadRows();
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

    public function toggleAllTasks(bool $checked): void
    {
        foreach ($this->tasks as $task) {
            $this->selected[$task['pivot_id']] = $checked;
        }
    }

    private function loadRows(): void
    {
        $this->selected = [];
        $this->points = [];
        $this->editingPoints = [];
        $this->pointErrors = [];

        $rows = app(StudentTaskApprovalService::class)
            ->teacherInReviewRows(Auth::user(), $this->studentId, $this->subjectId);

        $this->tasks = $rows->map(function (SessionTaskStudent $pivot): array {
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
        })->values()->all();
    }

    public function render()
    {
        return view('livewire.teacher.task-approval-work-view');
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

    private function maxPointsForPivot(int $pivotId): int
    {
        foreach ($this->tasks as $task) {
            if ((int) $task['pivot_id'] === $pivotId) {
                return (int) $task['max_points'];
            }
        }

        return 0;
    }

    private function refreshSelectedPointErrors(): void
    {
        foreach ($this->selected as $pivotId => $isSelected) {
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

        if (! $task || ! $session || ! $session->teacher_subject_classes_id) {
            return null;
        }

        return route('teacher.sessions', ['teachersubjectid' => $session->teacher_subject_classes_id])
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
