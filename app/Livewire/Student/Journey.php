<?php

namespace App\Livewire\Student;

use App\Models\AcademicYear;
use App\Models\AttachmentFile;
use App\Models\ClassSession;
use App\Models\SessionTask;
use App\Models\SessionTaskStudent;
use App\Models\Student;
use App\Models\StudentGift;
use App\Models\StudentsSubject;
use App\Models\TaskPinHash;
use App\Services\StudentTaskApprovalService;
use App\Support\BookingSubjectProvisioning;
use App\Support\JourneyBackgrounds;
use App\Support\LifecycleGate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

// #[Layout('components.layouts.blank')]
class Journey extends Component
{
    public string $bgUrl = '';

    public ?int $pendingGiftId = null;

    public ?int $lastReachedGiftId = null;

    public int $sessionId;

    public int $studentId;

    public ?int $currentTaskId = null;

    public array $currentTask = [];         // title, description, sort, ...

    public array $currentAttachments = [];

    public array $session = [];

    public array $taskIndex = [];

    public array $attachmentsByTaskId = [];

    public int $completedCount = 0;

    public int $totalCount = 0;

    public int $subjectId = 0;

    public string $pinInput = '';

    public ?string $pinErrorMessage = null;

    public ?int $currentTaskDefaultPoint = null;

    public ?int $currentTaskMaxPoint = null;

    public ?float $lastPinSubmitAt = null;

    public ?int $autoOpenTaskId = null;

    public ?string $autoOpenMode = null;

    public ?int $parentDirectTaskId = null;

    public ?int $parentDirectPivotId = null;

    public ?string $parentDirectAction = null;

    public ?int $parentDirectPoints = null;

    public ?int $parentDirectMaxPoints = null;

    public bool $returnToTaskModalAfterViewer = false;

    protected function getCurrentStudentId(): ?int
    {
        if (! empty($this->studentId)) {
            return $this->studentId;
        }

        $user = Auth::user();
        if (! $user) {
            return null;
        }

        return Student::where('user_id', $user->id)->value('id');
    }

    protected function computeGiftAnchors(): void
    {
        $academicYearId = AcademicYear::currentId();

        $this->pendingGiftId = StudentGift::query()
            ->where('student_id', $this->studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', StudentGift::STATUS_PENDING)
            ->orderBy('points_required', 'asc')
            ->value('id');

        $this->lastReachedGiftId = StudentGift::query()
            ->where('student_id', $this->studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', StudentGift::STATUS_REACHED)
            ->orderBy('points_required', 'desc')
            ->value('id');
    }

    public function mount(int $sessionId, int $studentId, ?int $autoOpenTaskId = null, ?string $autoOpenMode = null): void
    {
        $this->sessionId = $sessionId;
        $this->studentId = $studentId;
        $this->autoOpenTaskId = $autoOpenTaskId;
        $this->autoOpenMode = $autoOpenMode;

        if ((Auth::check()) && (Auth::user()->hasRole('student'))) {
            $user = Auth::user();
            $user_id = $user->id;
            $student_id = Student::where('user_id', $user_id)->value('id');
            $this->studentId = $student_id;
        }

        if (Auth::check() && Auth::user()->hasRole('parent')) {
            $parentModel = Auth::user()->parent_user;
            if (! $parentModel || ! $parentModel->students()->where('students.id', $this->studentId)->exists()) {
                abort(403, 'Unauthorized access to student journey.');
            }
        }

        if ($this->currentUserIsTeacher() && ! $this->teacherCanViewSessionForStudent($this->sessionId, $this->studentId)) {
            abort(403, 'Unauthorized access to student journey.');
        }

        $lifecycleGate = LifecycleGate::inspect($this->studentId);
        abort_if($lifecycleGate->denied(), 403, LifecycleGate::NEUTRAL_MESSAGE);

        $sessionModel = $this->resolveVisibleSessionOrFail();
        $this->subjectId = (int) $sessionModel->subject_id;

        $this->loadSessionData();
        $this->computeGiftAnchors();

        if ($this->autoOpenTaskId && isset($this->taskIndex[$this->autoOpenTaskId])) {
            $this->currentTaskId = $this->autoOpenTaskId;
            $this->currentTask = $this->taskIndex[$this->autoOpenTaskId];
            $this->currentAttachments = $this->attachmentsByTaskId[$this->autoOpenTaskId] ?? [];
        }

        $this->bgUrl = JourneyBackgrounds::currentUrl();
    }

    protected function loadSessionData(): void
    {
        $studentId = $this->getCurrentStudentId();

        $sessionModel = $this->resolveVisibleSessionOrFail();

        // Tasks related to the session
        $tasks = SessionTask::query()
            ->where('class_session_id', $this->sessionId)
            ->orderBy('sort')
            ->get(['id', 'class_session_id', 'title', 'description', 'sort', 'default_points', 'max_points']);

        $this->totalCount = $tasks->count();

        $this->completedCount = SessionTaskStudent::query()
            ->whereIn('session_task_id', $tasks->pluck('id'))
            ->where('student_id', $this->studentId)
            ->where('status', 'completed')
            ->count();

        $pivots = SessionTaskStudent::query()
            ->whereIn('session_task_id', $tasks->pluck('id'))
            ->where('student_id', $studentId)
            ->get([
                'id',
                'session_task_id',
                'status',
                'flag',
                'student_points',
                'review_submitted_at',
                'approval_source',
                'approved_at',
            ])
            ->keyBy('session_task_id');

        $attachmentsByTaskId = AttachmentFile::query()
            ->whereIn('session_task_id', $tasks->pluck('id'))
            ->orderedForDelivery()
            ->get(['id', 'session_task_id', 'type', 'path', 'title'])
            ->groupBy('session_task_id')
            ->map(fn ($files) => $files->map(fn ($file) => $this->mapAttachmentForDisplay($file))->values()->all())
            ->all();

        $taskList = $tasks->map(function ($task) use ($pivots) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'sort' => $task->sort,
                'points' => $task->default_points,
                'max' => $task->max_points,
                'pivot' => $pivots->get($task->id)?->toArray()
                  ?? ['status' => null, 'flag' => null, 'student_points' => null],
            ];
        })->values();

        // Prepare data to send to Blade
        $this->session = [
            'id' => $this->sessionId,
            'date' => $sessionModel->date ?? null,
            'class_subject_id' => $sessionModel->class_subject_id,
            'subject_title' => $sessionModel->subject
                ? BookingSubjectProvisioning::displaySubjectName((int) $sessionModel->subject->id, $sessionModel->subject->title)
                : null,
            'title' => $sessionModel->title,
            'tasks' => $taskList->all(),
        ];

        $this->taskIndex = $taskList->keyBy('id')->all();
        $this->attachmentsByTaskId = $attachmentsByTaskId;
    }

    public function refreshTaskState(): void
    {
        $taskIds = collect($this->session['tasks'] ?? [])
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
                'flag',
                'student_points',
                'review_submitted_at',
                'approval_source',
                'approved_at',
            ])
            ->keyBy('session_task_id');

        $completedCount = 0;

        foreach ($this->session['tasks'] as &$task) {
            $pivot = $pivots->get((int) $task['id']);
            $task['pivot'] = $pivot?->toArray()
                ?? ['status' => null, 'flag' => null, 'student_points' => null];

            if (($task['pivot']['status'] ?? null) === SessionTaskStudent::STATUS_COMPLETED) {
                $completedCount++;
            }
        }

        unset($task);

        $this->completedCount = $completedCount;
        $this->taskIndex = collect($this->session['tasks'])->keyBy('id')->all();

        if ($this->currentTaskId && isset($this->taskIndex[$this->currentTaskId])) {
            $this->currentTask = $this->taskIndex[$this->currentTaskId];
        }

        if ($before === $this->taskStateSignature()) {
            $this->skipRender();

            return;
        }

        $this->dispatch('task-state-refreshed', studentId: $this->studentId);
        $this->dispatch('reward-points:updated');
    }

    protected function resolveVisibleSessionOrFail(): ClassSession
    {
        $sessionModel = ClassSession::with('subject')
            ->whereKey($this->sessionId)
            ->visibleToLearner($this->studentId)
            ->firstOrFail();

        $hasEnrollment = StudentsSubject::query()
            ->where('student_id', $this->studentId)
            ->where('class_subject_id', $sessionModel->class_subject_id)
            ->where('status', 'active')
            ->exists();

        abort_unless($hasEnrollment, 403);
        abort_if($this->currentUserIsTeacher() && ! $this->teacherCanViewSessionForStudent($this->sessionId, $this->studentId), 403);

        return $sessionModel;
    }

    protected function currentUserIsTeacher(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();

        return $user->getRoleNames()->diff(['teacher'])->isEmpty()
            && $user->hasRole('teacher');
    }

    protected function teacherCanViewSessionForStudent(int $sessionId, int $studentId): bool
    {
        return ClassSession::query()
            ->whereKey($sessionId)
            ->visibleToLearner($studentId)
            ->whereHas('teacherSubjectClass', fn ($query) => $query
                ->where('user_teacher_coteacher_id', Auth::id())
                ->availableForTeacher()
                ->whereHas('classSubject.studentsSubjects', fn ($studentSubjectQuery) => $studentSubjectQuery
                    ->where('student_id', $studentId)
                    ->where('status', 'active')
                    ->whereHas('student', fn ($studentQuery) => $studentQuery->visibleToTeacher())))
            ->exists();
    }

    protected function mapAttachmentForDisplay(AttachmentFile $attachment): array
    {
        $type = $attachment->type ?? 'file';
        $path = $attachment->path ?? '';
        $isVocabularyGame = $type === 'link' && str_contains((string) $path, '/vocabulary/games/assignment/');

        return [
            'type' => $isVocabularyGame ? 'vocabulary_game' : $type,
            'path' => $path,
            'name' => $attachment->title
              ?? (in_array($type, ['link', 'youtube'], true) ? $path : basename((string) $path)),
            'id' => $attachment->id,
        ];
    }

    #[On('open-task')]
    public function openTask(int $taskId): void
    {
        $this->resetValidation();
        $this->pinInput = '';
        $this->pinErrorMessage = null;
        $this->returnToTaskModalAfterViewer = false;

        abort_unless(isset($this->taskIndex[$taskId]), 404);

        $this->currentTaskId = $taskId;
        $this->currentTask = $this->taskIndex[$taskId];
        $this->currentAttachments = $this->attachmentsByTaskId[$taskId] ?? [];

        $this->dispatch('open-task-modal');
    }

    public function closeTaskModal(): void
    {
        $this->returnToTaskModalAfterViewer = false;
        $this->dispatch('close-task-modal');
    }

    public function openAttachmentStudyViewer(int $taskId, ?int $attachmentId = null): void
    {
        abort_unless(isset($this->taskIndex[$taskId]), 404);

        $attachments = $this->attachmentsByTaskId[$taskId] ?? [];
        abort_if($attachments === [], 404);

        $attachmentId ??= (int) ($attachments[0]['id'] ?? 0);

        abort_unless(
            collect($attachments)->contains(fn (array $attachment): bool => (int) ($attachment['id'] ?? 0) === $attachmentId),
            404
        );

        $this->currentTaskId = $taskId;
        $this->currentTask = $this->taskIndex[$taskId];
        $this->currentAttachments = $attachments;
        $this->returnToTaskModalAfterViewer = true;

        $this->dispatch('close-task-modal');
        $this->dispatch(
            'open-attachment-study-viewer',
            sessionId: $this->sessionId,
            taskId: $taskId,
            attachmentId: $attachmentId
        );
    }

    #[On('attachment-study-viewer-closed')]
    public function reopenTaskModalAfterViewer(): void
    {
        if (! $this->returnToTaskModalAfterViewer || ! $this->currentTaskId) {
            return;
        }

        $this->returnToTaskModalAfterViewer = false;
        $this->dispatch('open-task-modal');
    }

    protected function completeTask(int $taskId, int $verifierUserId): void
    {
        $studentId = $this->getCurrentStudentId();

        if (! Auth::user() || ! $studentId) {
            throw new \RuntimeException('Missing task or student for completion.');
        }

        app(StudentTaskApprovalService::class)->completeWithStudentPin(
            Auth::user(),
            $taskId,
            $studentId,
            $verifierUserId
        );

        $this->dispatch('task-completed', taskId: $taskId, studentId: $this->getCurrentStudentId());
        $this->dispatch('reward-points:updated');
        $this->computeGiftAnchors();
        $this->dispatch('close-pin-modal');
        $this->pinInput = '';
        $this->pinErrorMessage = null;
        $this->loadSessionData();
        $this->dispatch('close-task-modal');
    }

    public function putToReview(int $taskId): void
    {
        $studentId = $this->getCurrentStudentId();

        if (! Auth::user() || ! $studentId) {
            return;
        }

        app(StudentTaskApprovalService::class)->putToReview(Auth::user(), $taskId, $studentId);

        $this->loadSessionData();
        $this->currentTask = $this->taskIndex[$taskId] ?? $this->currentTask;
        $this->dispatch('task-submitted-for-review', taskId: $taskId, studentId: $studentId);
    }

    // CompleteModal

    #[On('open-complete-modal')]
    public function openCompleteModal(int $taskId, ?int $defaultPoints = null, ?int $maxPoints = null): void
    {
        $this->resetValidation();
        $this->pinErrorMessage = null;
        $this->pinInput = '';

        if (! isset($this->taskIndex[$taskId])) {
            throw new \RuntimeException('Task not found.');
        }

        if ($defaultPoints === null || $maxPoints === null) {
            $task = SessionTask::query()
                ->whereKey($taskId)
                ->where('class_session_id', $this->sessionId)
                ->first(['id', 'default_points', 'max_points']);

            if (! $task) {
                throw new \RuntimeException('Task not found.');
            }

            $defaultPoints = (int) ($task->default_points ?? 0);
            $maxPoints = (int) ($task->max_points ?? $task->default_points ?? 0);
        }

        $effectiveMax = max(0, (int) $maxPoints);
        $this->currentTaskMaxPoint = $effectiveMax;
        $this->currentTaskDefaultPoint = min(
            max(0, (int) $defaultPoints),
            $effectiveMax
        );

        $this->currentTaskId = $taskId;

        $this->dispatch('open-pin-modal');
        $this->dispatch('pin-modal-opened');
        // $this->dispatch('focus-pin');
    }

    public function closePinModal(): void
    {
        // $this->showCompletePinModal = false;
        $this->pinInput = '';
        $this->pinErrorMessage = null;

        $this->dispatch('close-pin-modal');
    }

    #[On('open-parent-direct-complete-modal-requested')]
    public function openParentDirectCompleteModal(int $taskId): void
    {
        abort_unless(Auth::user()?->hasRole('parent'), 403);

        $task = SessionTask::query()
            ->whereKey($taskId)
            ->where('class_session_id', $this->sessionId)
            ->firstOrFail(['id', 'default_points', 'max_points']);

        $freshPivot = $this->freshParentTaskPivot($task->id);
        if ($freshPivot?->isCompleted()) {
            $this->loadSessionData();
            $this->currentTask = $this->taskIndex[$task->id] ?? $this->currentTask;
            $this->dispatch('task-completed', taskId: $task->id, studentId: $this->studentId);
            $this->dispatch('reward-points:updated');
            $this->dispatch('close-task-modal');

            return;
        }

        $this->parentDirectTaskId = $task->id;
        $this->parentDirectPivotId = $freshPivot?->id;
        $this->parentDirectAction = $freshPivot?->isInReviewLike() ? 'approve' : 'complete';
        $this->parentDirectMaxPoints = (int) ($task->max_points ?? $task->default_points ?? 0);
        $this->parentDirectPoints = min((int) ($task->default_points ?? 0), $this->parentDirectMaxPoints);

        $this->dispatch('close-task-modal');
        $this->dispatch('open-parent-direct-complete-modal');
    }

    public function openParentReviewApprovalModal(int $taskId): void
    {
        abort_unless(Auth::user()?->hasRole('parent'), 403);

        $task = SessionTask::query()
            ->whereKey($taskId)
            ->where('class_session_id', $this->sessionId)
            ->firstOrFail(['id', 'default_points', 'max_points']);

        $pivotId = $this->taskIndex[$taskId]['pivot']['id'] ?? null;
        abort_unless($pivotId, 404);

        $this->parentDirectTaskId = $task->id;
        $this->parentDirectPivotId = (int) $pivotId;
        $this->parentDirectAction = 'approve';
        $this->parentDirectMaxPoints = (int) ($task->max_points ?? $task->default_points ?? 0);
        $this->parentDirectPoints = min((int) ($task->default_points ?? 0), $this->parentDirectMaxPoints);

        $this->dispatch('close-task-modal');
        $this->dispatch('open-parent-direct-complete-modal');
    }

    public function closeParentDirectCompleteModal(): void
    {
        $this->parentDirectTaskId = null;
        $this->parentDirectPivotId = null;
        $this->parentDirectAction = null;
        $this->parentDirectPoints = null;
        $this->parentDirectMaxPoints = null;

        $this->dispatch('close-parent-direct-complete-modal');
    }

    public function confirmParentDirectCompletion(): void
    {
        abort_unless(Auth::user()?->hasRole('parent'), 403);

        $this->validate([
            'parentDirectAction' => ['required', 'in:complete,approve'],
            'parentDirectTaskId' => ['required', 'integer'],
            'parentDirectPoints' => ['required', 'integer', 'min:0', 'max:'.($this->parentDirectMaxPoints ?? 0)],
        ]);

        $service = app(StudentTaskApprovalService::class);
        $freshPivot = $this->freshParentTaskPivot((int) $this->parentDirectTaskId);

        if ($freshPivot?->isCompleted()) {
            $taskId = (int) $this->parentDirectTaskId;
            $this->closeParentDirectCompleteModal();
            $this->loadSessionData();
            $this->currentTask = $this->taskIndex[$taskId] ?? $this->currentTask;
            $this->dispatch('task-completed', taskId: $taskId, studentId: $this->studentId);
            $this->dispatch('reward-points:updated');
            $this->dispatch('close-task-modal');

            return;
        }

        if ($this->parentDirectAction === 'complete' && $freshPivot?->isInReviewLike()) {
            $this->parentDirectAction = 'approve';
            $this->parentDirectPivotId = $freshPivot->id;
        }

        if ($this->parentDirectAction === 'approve') {
            abort_unless($this->parentDirectPivotId, 404);
            $service->approveAsParent(Auth::user(), (int) $this->parentDirectPivotId, (int) $this->parentDirectPoints);
        } else {
            $service->completeAsParent(
                Auth::user(),
                (int) $this->parentDirectTaskId,
                $this->studentId,
                (int) $this->parentDirectPoints
            );
        }

        $taskId = (int) $this->parentDirectTaskId;
        $this->closeParentDirectCompleteModal();
        $this->dispatch('task-completed', taskId: $taskId, studentId: $this->studentId);
        $this->dispatch('reward-points:updated');
        $this->computeGiftAnchors();
        $this->loadSessionData();
        $this->dispatch('close-task-modal');
    }

    private function freshParentTaskPivot(int $taskId): ?SessionTaskStudent
    {
        return SessionTaskStudent::query()
            ->where('session_task_id', $taskId)
            ->where('student_id', $this->studentId)
            ->first();
    }

    private function taskStateSignature(): string
    {
        return (string) json_encode(
            collect($this->session['tasks'] ?? [])
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

    public function confirmTaskCompletionWithPin(): void
    {
        $this->validate([
            //   'pinInput' => ['required','string','size:8'],
            'pinInput' => ['required', 'regex:/^[0-9]{4}$/'],
            'currentTaskDefaultPoint' => ['required', 'integer', 'min:0', 'max:'.$this->currentTaskMaxPoint],

        ]);

        $taskId = $this->currentTaskId;
        $studentId = $this->getCurrentStudentId();
        $subjectId = $this->subjectId;
        if (! $taskId || ! $studentId) {
            $this->pinErrorMessage = 'Missing task or student.';

            return;
        }

        try {
            DB::transaction(function () use ($taskId) {

                // $verifierUserId = auth()->id();
                $verifierUserId = null;
                if ((Auth::check()) && (Auth::user()->hasRole('student'))) {
                    $user = Auth::user();
                    $verifierUserId = $user->id;
                }
                $pinRow = null;

                if ($verifierUserId) {
                    $pinRow = TaskPinHash::where('user_id', $verifierUserId)->first();
                    if (! ($pinRow && Hash::check($this->pinInput, $pinRow->pin_hash))) {
                        $pinRow = null;
                    }
                }

                //   if (!$pinRow) {
                //       $pinRow = TaskPinHash::get()
                //           ->first(fn ($row) => Hash::check($this->pinInput, $row->pin_hash));
                //   }

                if (! $pinRow) {
                    throw new \RuntimeException('Invalid PIN.');
                }
                $verifierUserId = $pinRow->user_id;

                $this->completeTask($taskId, $verifierUserId);

                $this->pinInput = '';
                $this->pinErrorMessage = null;
            }, 3);
        } catch (\Throwable $e) {
            $this->pinErrorMessage = $e->getMessage();
        }
    }

    public function updatedPinInput($value): void
    {
        if (! is_string($value)) {
            return;
        }

        if (strlen($value) === 4) {
            $now = microtime(true);
            if ($this->lastPinSubmitAt && ($now - $this->lastPinSubmitAt) < 2.0) {
                return;
            }
            $this->lastPinSubmitAt = $now;
            $this->confirmTaskCompletionWithPin();
        }
    }

    public function render()
    {
        return view('livewire.student.journey', [
            'session' => $this->session,
            'completedCount' => $this->completedCount,
            'totalCount' => $this->totalCount,
            'bgUrl' => $this->bgUrl,
        ]);
    }
}
