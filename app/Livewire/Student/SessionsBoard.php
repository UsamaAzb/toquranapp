<?php

namespace App\Livewire\Student;

use App\Models\ClassSession;
use App\Models\SessionTask;
use App\Models\SessionTaskStudent;
use App\Models\Student;
use App\Models\StudentsSubject;
use App\Models\TaskPinHash;
use App\Services\StudentTaskApprovalService;
use App\Support\LifecycleGate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class SessionsBoard extends Component
{
    public int $studentSubjectId;

    public int $completedCount;

    public int $studentId;

    public array $sessions = [];

    public array $open = [];

    public string $pinInput = '';

    public ?string $pinErrorMessage = null;

    public ?int $currentTaskId = null;

    public ?int $currentTaskDefaultPoint = null;

    public ?int $currentTaskMaxPoint = null;

    public ?float $lastPinSubmitAt = null;

    public ?string $parentTaskAction = null;

    public ?int $parentTaskId = null;

    public ?int $parentTaskPivotId = null;

    public ?int $parentTaskPoints = null;

    public ?int $parentTaskMaxPoints = null;

    public function mount(int $studentSubjectId, int $studentId): void
    {
        $this->studentId = $this->resolveAuthorizedStudentId($studentId);
        abort_if(LifecycleGate::inspect($this->studentId)->denied(), 403, LifecycleGate::NEUTRAL_MESSAGE);

        $this->studentSubjectId = $studentSubjectId;
        $this->loadSessions();
        $this->openRequestedSession();
    }

    protected function resolveAuthorizedStudentId(int $studentId): int
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasRole('student')) {
            $authorizedStudentId = Student::where('user_id', $user->id)->value('id');
            abort_unless($authorizedStudentId, 403);

            return (int) $authorizedStudentId;
        }

        if ($user->hasRole('parent')) {
            $parentModel = $user->parent_user;
            abort_unless(
                $parentModel && $parentModel->students()->where('students.id', $studentId)->exists(),
                403
            );
        }

        return $studentId;
    }

    protected function loadSessions(): void
    {
        $classSubjectId = StudentsSubject::query()
            ->whereKey($this->studentSubjectId)
            ->where('student_id', $this->studentId)
            ->where('status', 'active')
            ->value('class_subject_id');

        abort_unless($classSubjectId, 403);

        $studentId = $this->studentId;

        $this->sessions = ClassSession::with([
            'tasks' => fn ($q) => $q->orderByRaw('sort IS NULL, sort ASC')->orderBy('id')
                ->withCount([
                    'taskStudents as completed_for_student_count' => fn ($qq) => $qq->where('student_id', $studentId)
                        ->where('status', 'completed'),
                ]),
            'tasks.taskStudents' => fn ($q) => $q->where('student_id', $studentId),
            'tasks.attachments' => fn ($q) => $q->orderedForDelivery(),
            'sessionMaterials:id,session_id,status',
        ])
            ->where('class_subject_id', $classSubjectId)
            ->visibleToLearner($this->studentId)
            ->whereHas('sessionMaterials', fn ($q) => $q->where('status', 'published'))
            ->orderByRaw('date IS NULL, date DESC')
            ->orderByDesc('id')
            ->get()
            ->map(function ($session) {
                $completedCount = $session->tasks->where('completed_for_student_count', '>', 0)->count();

                return [
                    'id' => $session->id,
                    'date' => $session->date,
                    'start' => $session->session_start_time,
                    'end' => $session->session_end_time,
                    'title' => $session->title,
                    'completedCount' => $completedCount,
                    'materials_status' => optional($session->sessionMaterials)->status,
                    'tasks' => $session->tasks->map(function ($task) use ($session) {
                        $pivot = $task->taskStudents->first();

                        return [
                            'id' => $task->id,
                            'title' => $task->title,
                            'task_type_id' => (int) $task->task_type_id,
                            'status' => $task->status,
                            'desc' => $task->description,
                            'max' => (int) ($task->max_points ?? 0),
                            'default_points' => (int) ($task->default_points ?? 0),
                            'pivot' => [
                                'id' => $pivot?->id,
                                'status' => $pivot?->status,
                                'student_points' => $pivot?->student_points,
                                'review_submitted_at' => $pivot?->review_submitted_at,
                                'approval_source' => $pivot?->approval_source,
                                'approved_at' => $pivot?->approved_at,
                            ],
                            'files' => $task->attachments->map(function ($attachment) use ($session) {
                                $isExternal = in_array($attachment->type, ['link', 'youtube'], true);
                                $isVocabularyGame = $attachment->type === 'link'
                                    && str_contains((string) $attachment->path, '/vocabulary/games/assignment/');

                                return [
                                    'id' => $attachment->id,
                                    'name' => $attachment->title ?? ($isExternal
                                        ? (parse_url($attachment->path, PHP_URL_HOST) ?? 'link')
                                        : basename($attachment->path)),
                                    'path' => $attachment->path,
                                    'url' => $isExternal ? $attachment->path : route('student.sessions.attachment.file', [
                                        'session' => $session->id,
                                        'attachment' => $attachment->id,
                                        'student_id' => $this->studentId,
                                    ]),
                                    'size' => $attachment->file_size,
                                    'type' => $isVocabularyGame ? 'vocabulary_game' : $attachment->type,
                                ];
                            })->toArray(),
                        ];
                    })->toArray(),
                ];
            })
            ->toArray();
    }

    public function refreshTaskState(): void
    {
        $taskIds = collect($this->sessions)
            ->flatMap(fn (array $session): array => $session['tasks'] ?? [])
            ->pluck('id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($taskIds->isEmpty()) {
            return;
        }

        $before = $this->taskStateSignature();

        $pivots = SessionTaskStudent::query()
            ->whereIn('session_task_id', $taskIds)
            ->where('student_id', $this->studentId)
            ->get([
                'id',
                'session_task_id',
                'status',
                'student_points',
                'review_submitted_at',
                'approval_source',
                'approved_at',
            ])
            ->keyBy('session_task_id');

        foreach ($this->sessions as &$session) {
            $completedCount = 0;

            foreach ($session['tasks'] as &$task) {
                $pivot = $pivots->get((int) $task['id']);
                $task['pivot'] = $this->pivotDisplayState($pivot);

                if (($task['pivot']['status'] ?? null) === SessionTaskStudent::STATUS_COMPLETED) {
                    $completedCount++;
                }
            }

            unset($task);
            $session['completedCount'] = $completedCount;
        }

        unset($session);

        if ($before === $this->taskStateSignature()) {
            $this->skipRender();

            return;
        }

        $this->dispatch('task-state-refreshed', studentId: $this->studentId);
        $this->dispatch('reward-points:updated');
    }

    public function putToReview(int $taskId): void
    {
        $user = Auth::user();

        if (! $user || ! $user->hasRole('student')) {
            abort(403);
        }

        app(StudentTaskApprovalService::class)->putToReview($user, $taskId, $this->studentId);

        $this->loadSessions();
        $this->dispatch('task-submitted-for-review', taskId: $taskId, studentId: $this->studentId);
    }

    public function openParentTaskPointsModal(int $taskId, string $action): void
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('parent'), 403);
        abort_unless(in_array($action, ['complete', 'approve'], true), 403);

        $task = SessionTask::query()
            ->whereKey($taskId)
            ->firstOrFail(['id', 'default_points', 'max_points']);

        $taskRow = collect($this->sessions)
            ->flatMap(fn (array $session): array => $session['tasks'] ?? [])
            ->first(fn (array $sessionTask): bool => (int) $sessionTask['id'] === $taskId);

        abort_unless($taskRow, 404);

        $this->resetValidation();
        $freshPivot = $this->freshParentTaskPivot($task->id);
        if ($freshPivot?->isCompleted()) {
            $this->loadSessions();
            $this->dispatch('task-completed', taskId: $task->id, studentId: $this->studentId);
            $this->dispatch('reward-points:updated');

            return;
        }

        if ($action === 'complete' && $freshPivot?->isInReviewLike()) {
            $action = 'approve';
        }

        $this->parentTaskAction = $action;
        $this->parentTaskId = $task->id;
        $this->parentTaskPivotId = $freshPivot?->id ?? ($taskRow['pivot']['id'] ?? null);
        $this->parentTaskMaxPoints = (int) ($task->max_points ?? $task->default_points ?? 0);
        $this->parentTaskPoints = min((int) ($task->default_points ?? 0), $this->parentTaskMaxPoints);

        if ($action === 'approve') {
            abort_unless($this->parentTaskPivotId, 404);
        }

        $this->dispatch('open-student-session-parent-task-points-modal');
    }

    public function closeParentTaskPointsModal(): void
    {
        $this->parentTaskAction = null;
        $this->parentTaskId = null;
        $this->parentTaskPivotId = null;
        $this->parentTaskPoints = null;
        $this->parentTaskMaxPoints = null;

        $this->dispatch('close-student-session-parent-task-points-modal');
    }

    public function confirmParentTaskPoints(): void
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('parent'), 403);

        $this->validate([
            'parentTaskAction' => ['required', 'in:complete,approve'],
            'parentTaskId' => ['required', 'integer'],
            'parentTaskPoints' => ['required', 'integer', 'min:0', 'max:'.($this->parentTaskMaxPoints ?? 0)],
        ]);

        $service = app(StudentTaskApprovalService::class);
        $freshPivot = $this->freshParentTaskPivot((int) $this->parentTaskId);

        if ($freshPivot?->isCompleted()) {
            $this->loadSessions();
            $this->dispatch('task-completed', taskId: (int) $this->parentTaskId, studentId: $this->studentId);
            $this->dispatch('reward-points:updated');
            $this->closeParentTaskPointsModal();

            return;
        }

        if ($this->parentTaskAction === 'complete' && $freshPivot?->isInReviewLike()) {
            $this->parentTaskAction = 'approve';
            $this->parentTaskPivotId = $freshPivot->id;
        }

        if ($this->parentTaskAction === 'approve') {
            abort_unless($this->parentTaskPivotId, 404);
            $service->approveAsParent($user, (int) $this->parentTaskPivotId, (int) $this->parentTaskPoints);
        } else {
            $service->completeAsParent($user, (int) $this->parentTaskId, $this->studentId, (int) $this->parentTaskPoints);
        }

        $taskId = (int) $this->parentTaskId;
        $this->loadSessions();
        $this->dispatch('task-completed', taskId: $taskId, studentId: $this->studentId);
        $this->dispatch('reward-points:updated');
        $this->closeParentTaskPointsModal();
    }

    private function freshParentTaskPivot(int $taskId): ?SessionTaskStudent
    {
        return SessionTaskStudent::query()
            ->where('session_task_id', $taskId)
            ->where('student_id', $this->studentId)
            ->first();
    }

    private function pivotDisplayState(?SessionTaskStudent $pivot): array
    {
        return [
            'id' => $pivot?->id,
            'status' => $pivot?->status,
            'student_points' => $pivot?->student_points,
            'review_submitted_at' => $pivot?->review_submitted_at,
            'approval_source' => $pivot?->approval_source,
            'approved_at' => $pivot?->approved_at,
        ];
    }

    private function taskStateSignature(): string
    {
        return (string) json_encode(
            collect($this->sessions)
                ->flatMap(fn (array $session): array => $session['tasks'] ?? [])
                ->mapWithKeys(fn (array $task): array => [
                    (int) $task['id'] => [
                        'status' => $task['pivot']['status'] ?? null,
                        'student_points' => $task['pivot']['student_points'] ?? null,
                        'approval_source' => $task['pivot']['approval_source'] ?? null,
                        'approved_at' => (string) ($task['pivot']['approved_at'] ?? ''),
                    ],
                ])
                ->all()
        );
    }

    public function openCompleteModal(int $taskId, ?int $defaultPoints = null, ?int $maxPoints = null): void
    {
        $user = Auth::user();

        if (! $user || ! $user->hasRole('student')) {
            abort(403);
        }

        $taskRow = collect($this->sessions)
            ->flatMap(fn (array $session): array => $session['tasks'] ?? [])
            ->first(fn (array $task): bool => (int) $task['id'] === $taskId);

        abort_unless($taskRow, 404);

        if ($defaultPoints === null || $maxPoints === null) {
            $task = SessionTask::query()
                ->whereKey($taskId)
                ->firstOrFail(['id', 'default_points', 'max_points']);

            $defaultPoints = (int) ($task->default_points ?? 0);
            $maxPoints = (int) ($task->max_points ?? $task->default_points ?? 0);
        }

        $this->resetValidation();
        $this->pinInput = '';
        $this->pinErrorMessage = null;
        $this->currentTaskId = $taskId;
        $effectiveMax = max(0, (int) $maxPoints);
        $this->currentTaskMaxPoint = $effectiveMax;
        $this->currentTaskDefaultPoint = min(
            max(0, (int) $defaultPoints),
            $effectiveMax
        );

        $this->dispatch('open-student-session-pin-modal');
    }

    public function openAttachmentStudyViewer(int $sessionId, int $taskId, int $attachmentId): void
    {
        $matchingSession = collect($this->sessions)
            ->first(fn (array $session): bool => (int) $session['id'] === $sessionId
                && collect($session['tasks'] ?? [])
                    ->first(fn (array $task): bool => (int) $task['id'] === $taskId
                        && collect($task['files'] ?? [])
                            ->contains(fn (array $file): bool => (int) ($file['id'] ?? 0) === $attachmentId)
                    )
            );

        abort_unless($matchingSession, 404);

        $this->dispatch(
            'open-attachment-study-viewer',
            sessionId: $sessionId,
            taskId: $taskId,
            attachmentId: $attachmentId
        );
    }

    public function closePinModal(): void
    {
        $this->pinInput = '';
        $this->pinErrorMessage = null;
        $this->dispatch('close-student-session-pin-modal');
    }

    public function confirmTaskCompletionWithPin(): void
    {
        $this->validate([
            'pinInput' => ['required', 'regex:/^[0-9]{4}$/'],
            'currentTaskId' => ['required', 'integer'],
        ]);

        $user = Auth::user();
        if (! $user || ! $user->hasRole('student') || ! $this->currentTaskId) {
            abort(403);
        }

        $pinRow = TaskPinHash::where('user_id', $user->id)->first();
        if (! ($pinRow && Hash::check($this->pinInput, $pinRow->pin_hash))) {
            $this->pinErrorMessage = 'Invalid PIN.';

            return;
        }

        try {
            app(StudentTaskApprovalService::class)->completeWithStudentPin(
                $user,
                (int) $this->currentTaskId,
                $this->studentId,
                (int) $pinRow->user_id
            );
        } catch (\Throwable $e) {
            $this->pinErrorMessage = $e->getMessage();

            return;
        }

        $taskId = (int) $this->currentTaskId;
        $this->loadSessions();
        $this->dispatch('task-completed', taskId: $taskId, studentId: $this->studentId);
        $this->dispatch('reward-points:updated');
        $this->closePinModal();
        $this->currentTaskId = null;
        $this->currentTaskDefaultPoint = null;
        $this->currentTaskMaxPoint = null;
    }

    public function updatedPinInput($value): void
    {
        if (! is_string($value) || strlen($value) !== 4) {
            return;
        }

        $now = microtime(true);
        if ($this->lastPinSubmitAt && ($now - $this->lastPinSubmitAt) < 2.0) {
            return;
        }

        $this->lastPinSubmitAt = $now;
        $this->confirmTaskCompletionWithPin();
    }

    private function openRequestedSession(): void
    {
        $requestedSessionId = request()->integer('open_session');

        if (! $requestedSessionId) {
            return;
        }

        $sessionExists = collect($this->sessions)
            ->contains(fn (array $session) => (int) $session['id'] === $requestedSessionId);

        if ($sessionExists) {
            $this->open = [$requestedSessionId];
        }
    }

    public function render()
    {
        return view('livewire.student.sessions-board');
    }
}
