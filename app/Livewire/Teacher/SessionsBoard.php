<?php

namespace App\Livewire\Teacher;

use App\Models\AttachmentFile;
use App\Models\ClassSession;
use App\Models\SessionMaterial;
use App\Models\SessionTask;
use App\Models\SessionTaskStudent;
use App\Models\StudentsSubject;
use App\Models\TeacherSubjectClass;
use App\Services\Library\LibraryFileRetentionService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class SessionsBoard extends Component
{
    private const TASK_DOT_COLORS = ['primary', 'success', 'danger', 'info'];

    public int $teacherSubjectClassId;

    public ?int $editingId = null;

    public string $editingTitle = '';

    protected $listeners = [
        'session-added' => 'refreshList',
        'session-task-added' => 'refreshList',
    ];

    public array $open = [];

    public array $sessions = [];

    protected function resolveOwnedTeacherSubjectClassOrFail(?int $teacherSubjectClassId = null): TeacherSubjectClass
    {
        return TeacherSubjectClass::query()
            ->whereKey($teacherSubjectClassId ?? $this->teacherSubjectClassId)
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->withActiveStudentSubject()
            ->firstOrFail();
    }

    protected function resolveOwnedSessionOrFail(?int $sessionId = null): ClassSession
    {
        return ClassSession::query()
            ->whereKey($sessionId)
            ->where('teacher_subject_classes_id', $this->teacherSubjectClassId)
            ->normal()
            ->whereHas('teacherSubjectClass', fn ($query) => $query
                ->where('user_teacher_coteacher_id', Auth::id())
                ->availableForTeacher()
                ->withActiveStudentSubject())
            ->firstOrFail();
    }

    public function mount(int $teacherSubjectClassId): void
    {
        $this->teacherSubjectClassId = $this->resolveOwnedTeacherSubjectClassOrFail($teacherSubjectClassId)->id;
        $requestedOpenSession = (int) request()->query('open_session', 0);
        if ($requestedOpenSession > 0) {
            $this->open = [$requestedOpenSession];
        }
        $this->loadSessions();
    }

    public function refreshList(?int $sessionId = null): void
    {
        if ($sessionId) {
            $this->addOpen($sessionId);
        }
        $this->loadSessions();
    }

    protected function loadSessions(): void
    {
        $this->resolveOwnedTeacherSubjectClassOrFail();

        $sessions = $this->sessionsQuery()
            ->orderByRaw('date IS NULL, date DESC')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $requestedOpenSessionId = (int) ($this->open[0] ?? 0);

        if ($requestedOpenSessionId > 0 && ! $sessions->contains('id', $requestedOpenSessionId)) {
            $requestedSession = $this->sessionsQuery()
                ->whereKey($requestedOpenSessionId)
                ->first();

            if ($requestedSession) {
                $sessions->push($requestedSession);
            }
        }

        $this->sessions = $sessions
            ->sortByDesc(fn (ClassSession $session): string => $this->sessionSortKey($session))
            ->values()
            ->map(function ($session) {
                $taskCount = $session->tasks->count();
                $isPublished = optional($session->sessionMaterials)->status === 'published';
                $sessionDate = $session->date ? Carbon::parse($session->date, config('app.timezone'))->startOfDay() : null;
                $today = Carbon::now(config('app.timezone'))->startOfDay();

                return [
                    'id' => $session->id,
                    'date' => $session->date,
                    'start' => $session->session_start_time,
                    'end' => $session->session_end_time,
                    'title' => $session->title,
                    'materials_status' => optional($session->sessionMaterials)->status,
                    'task_count' => $taskCount,
                    'is_published' => $isPublished,
                    'is_past_draft' => ! $isPublished && $sessionDate !== null && $sessionDate->lt($today),
                    'card_state_class' => $this->sessionCardStateClass($taskCount, $isPublished),
                    'tasks' => $this->decorateTaskRows($session->tasks->values()->map(function ($task) use ($session) {
                        return [
                            'id' => $task->id,
                            'title' => $task->title,
                            'desc' => $task->description,
                            'task_type_id' => $task->task_type_id,
                            'max' => $task->max_points,
                            'default_points' => $task->default_points,
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
                                    'url' => $isExternal
                                        ? $attachment->path
                                        : route('teacher.sessions.attachment.file', [
                                            'session' => $session->id,
                                            'attachment' => $attachment->id,
                                        ]),
                                    'size' => $attachment->file_size,
                                    'ext' => $attachment->type,
                                    'type' => $isVocabularyGame ? 'vocabulary_game' : $attachment->type,
                                ];
                            })->toArray(),
                        ];
                    })->toArray()),
                ];
            })
            ->toArray();
    }

    private function sessionCardStateClass(int $taskCount, bool $isPublished): string
    {
        if ($taskCount === 0) {
            return 'session-card-state-empty';
        }

        return $isPublished ? 'session-card-state-complete' : 'session-card-state-ready';
    }

    private function decorateTaskRows(array $tasks): array
    {
        $indexed = array_values($tasks);

        return array_map(function (array $task, int $index): array {
            $task['dot_class'] = $this->taskDotClass($index);
            $task['default_points'] = (int) ($task['default_points'] ?? 0);

            return $task;
        }, $indexed, array_keys($indexed));
    }

    private function taskDotClass(int $index): string
    {
        return 'session-task-dot-'.self::TASK_DOT_COLORS[$index % count(self::TASK_DOT_COLORS)];
    }

    protected function sessionsQuery(): Builder
    {
        return ClassSession::with([
            'tasks' => function ($query) {
                $query->orderByRaw('sort IS NULL, sort ASC')->orderBy('id', 'ASC');
            },
            'sessionMaterials:id,session_id,status',
            'tasks.attachments',
        ])
            ->where('teacher_subject_classes_id', $this->teacherSubjectClassId)
            ->normal();
    }

    private function sessionSortKey(ClassSession $session): string
    {
        $date = $session->date
            ? Carbon::parse($session->date, config('app.timezone'))->format('Ymd')
            : '00000000';

        return $date.str_pad((string) $session->id, 12, '0', STR_PAD_LEFT);
    }

    public function addOpen(int $id): void
    {
        if (! in_array($id, $this->open, true)) {
            $this->open[] = $id;
        }
    }

    public function removeOpen(int $id): void
    {
        $this->open = array_values(array_diff($this->open, [$id]));
    }

    #[On('reorder-session-tasks')]
    public function reorderSessionTasks(int $sessionId, array $orderedIds): void
    {
        $ownedSession = $this->resolveOwnedSessionOrFail($sessionId);
        $orderedIds = array_values(array_unique(array_map('intval', $orderedIds)));

        $validIds = SessionTask::whereIn('id', $orderedIds)
            ->where('class_session_id', $ownedSession->id)
            ->pluck('id')
            ->map(fn ($value) => (int) $value)
            ->all();

        $orderedIds = array_values(array_intersect($orderedIds, $validIds));

        DB::transaction(function () use ($ownedSession, $orderedIds) {
            foreach ($orderedIds as $index => $id) {
                SessionTask::where('id', $id)
                    ->where('class_session_id', $ownedSession->id)
                    ->update(['sort' => $index + 1]);
            }
        });

        foreach ($this->sessions as &$session) {
            if ($session['id'] !== $ownedSession->id) {
                continue;
            }

            $byId = [];
            foreach ($session['tasks'] as $task) {
                $byId[(int) $task['id']] = $task;
            }

            $newOrder = [];
            foreach ($orderedIds as $id) {
                if (isset($byId[$id])) {
                    $newOrder[] = $byId[$id];
                }
            }

            foreach ($session['tasks'] as $task) {
                if (! in_array((int) $task['id'], $orderedIds, true)) {
                    $newOrder[] = $task;
                }
            }

            $session['tasks'] = $this->decorateTaskRows($newOrder);
            break;
        }
        unset($session);

        $this->sessions = $this->sessions;
    }

    #[On('session-task-updated')]
    public function refreshAfterUpdate(?int $sessionId = null): void
    {
        if ($sessionId) {
            $this->addOpen($sessionId);
        }
        $this->loadSessions();
    }

    public function openAttachmentStudyViewer(int $sessionId, int $taskId, int $attachmentId): void
    {
        $session = $this->resolveOwnedSessionOrFail($sessionId);
        $this->open = [$session->id];

        $task = SessionTask::query()
            ->whereKey($taskId)
            ->where('class_session_id', $session->id)
            ->whereHas('attachments', fn ($query) => $query->whereKey($attachmentId))
            ->firstOrFail();

        $this->dispatch(
            'open-teacher-attachment-study-viewer',
            sessionId: $session->id,
            taskId: $task->id,
            attachmentId: $attachmentId
        );
    }

    public function publishSession(int $sessionId, bool $movePastDateToToday = false): void
    {
        $classSession = $this->resolveOwnedSessionOrFail($sessionId);
        $today = Carbon::now(config('app.timezone'))->toDateString();
        $isPastDraft = $this->isPastDatedDraft($classSession, $today);

        if ($isPastDraft && ! $movePastDateToToday) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Confirm moving this old draft session to today before publishing.']);

            return;
        }

        $tasks = SessionTask::query()
            ->with('attachments')
            ->where('class_session_id', $classSession->id)
            ->orderBy('sort', 'asc')
            ->get();

        if ($tasks->isEmpty()) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Add at least one task before publishing this session.']);

            return;
        }

        if ($tasks->contains(function (SessionTask $task): bool {
            $hasDescription = trim((string) $task->description) !== '';
            $hasAttachments = $task->attachments->isNotEmpty();

            return ! $hasDescription && ! $hasAttachments;
        })) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Each task needs a description or an attachment before publishing.']);

            return;
        }

        DB::transaction(function () use ($classSession, $isPastDraft, $movePastDateToToday, $today) {
            if ($isPastDraft && $movePastDateToToday) {
                $classSession->update(['date' => $today]);
            }

            SessionMaterial::updateOrCreate(
                ['session_id' => $classSession->id],
                [
                    'teacher_subject_classes_id' => $classSession->teacher_subject_classes_id,
                    'subject_id' => $classSession->subject_id,
                    'grade_id' => $classSession->grade_id,
                    'teacher_id' => $classSession->teacher_id,
                    'unit_id' => $classSession->unit_id,
                    'status' => 'published',
                    'assign_to_all' => 'all',
                ]
            );

            SessionTask::where('class_session_id', $classSession->id)
                ->update(['status' => 'published']);

            $tasks = SessionTask::where('class_session_id', $classSession->id)
                ->orderBy('sort', 'asc')
                ->get();

            $studentSubjects = StudentsSubject::where('class_subject_id', $classSession->class_subject_id)->get();
            $taskIds = $tasks->pluck('id')->all();

            foreach ($studentSubjects as $studentSubject) {
                $studentId = $studentSubject->student_id;

                foreach ($tasks as $task) {
                    SessionTaskStudent::firstOrCreate(
                        [
                            'session_task_id' => $task->id,
                            'student_id' => $studentId,
                        ],
                        [
                            'student_user_id' => $studentId,
                            'student_points' => null,
                            'status' => 'assigned',
                            'flag' => null,
                        ]
                    );
                }

                SessionTaskStudent::where('student_id', $studentId)
                    ->whereIn('session_task_id', $taskIds)
                    ->where('flag', 'up-next')
                    ->update(['flag' => null]);

                $firstUpNextTaskId = null;
                foreach ($tasks as $task) {
                    $studentTask = SessionTaskStudent::where('session_task_id', $task->id)
                        ->where('student_id', $studentId)
                        ->first(['status', 'session_task_id']);

                    if (! $studentTask || $studentTask->status !== 'completed') {
                        $firstUpNextTaskId = $task->id;
                        break;
                    }
                }

                if ($firstUpNextTaskId) {
                    SessionTaskStudent::where('session_task_id', $firstUpNextTaskId)
                        ->where('student_id', $studentId)
                        ->update(['flag' => 'up-next']);
                }
            }
        });

        $this->addOpen($classSession->id);
        $this->loadSessions();
    }

    public function deleteDraftTask(int $taskId): void
    {
        $task = $this->resolveOwnedTaskOrFail($taskId);
        $session = $task->classSession;

        if (! $this->canDeleteDraftTask($task, $session)) {
            throw ValidationException::withMessages([
                'task' => 'Only unpublished draft tasks with no learner work can be deleted.',
            ]);
        }

        $paths = $task->attachments()
            ->where('type', 'file')
            ->pluck('path')
            ->filter()
            ->all();

        DB::transaction(function () use ($task): void {
            AttachmentFile::query()
                ->where('session_task_id', $task->id)
                ->delete();

            $task->delete();
        });

        $this->deletePublicPathsQuietly($paths);
        $this->addOpen((int) $session->id);
        $this->loadSessions();
    }

    public function deleteDraftSession(int $sessionId): void
    {
        $session = $this->resolveOwnedSessionOrFail($sessionId);
        $tasks = SessionTask::query()
            ->with('attachments')
            ->where('class_session_id', $session->id)
            ->get();

        if (! $this->canDeleteDraftSession($session, $tasks)) {
            throw ValidationException::withMessages([
                'session' => 'Only unpublished draft sessions with no learner work can be deleted.',
            ]);
        }

        $taskIds = $tasks->pluck('id')->all();
        $paths = $tasks
            ->flatMap(fn (SessionTask $task) => $task->attachments
                ->where('type', 'file')
                ->pluck('path'))
            ->filter()
            ->values()
            ->all();

        DB::transaction(function () use ($session, $taskIds): void {
            if (! empty($taskIds)) {
                AttachmentFile::query()
                    ->whereIn('session_task_id', $taskIds)
                    ->delete();

                SessionTask::query()
                    ->whereIn('id', $taskIds)
                    ->delete();
            }

            SessionMaterial::query()
                ->where('session_id', $session->id)
                ->delete();

            $session->delete();
        });

        $this->deletePublicPathsQuietly($paths);
        $this->open = array_values(array_diff($this->open, [$session->id]));
        $this->loadSessions();
    }

    public function startEdit(int $sessionId): void
    {
        $session = $this->resolveOwnedSessionOrFail($sessionId);

        $this->editingId = $session->id;
        $this->editingTitle = (string) $session->title;
        $this->dispatch('focus-title', id: $session->id);
    }

    public function finishEdit(?string $value = null): void
    {
        if ($this->editingId) {
            $this->persistEditingTitle($this->editingId, $value ?? $this->editingTitle);
        }

        $this->reset(['editingId']);
    }

    public function updatedEditingTitle(string $value): void
    {
        if (! $this->editingId) {
            return;
        }

        $this->persistEditingTitle($this->editingId, $value);
    }

    protected function persistEditingTitle(int $sessionId, ?string $rawValue = null): void
    {
        $session = $this->resolveOwnedSessionOrFail($sessionId);
        $new = trim((string) $rawValue);

        $this->validate([
            'editingTitle' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $current = (string) ($session->title ?? '');
        if ($new === $current) {
            return;
        }

        $session->update(['title' => $new]);

        if (isset($this->sessions) && is_array($this->sessions)) {
            foreach ($this->sessions as &$row) {
                if ((int) $row['id'] === $sessionId) {
                    $row['title'] = $new;
                    break;
                }
            }
            unset($row);
        }

        $this->editingTitle = $new;
    }

    private function resolveOwnedTaskOrFail(int $taskId): SessionTask
    {
        return SessionTask::query()
            ->with(['classSession.sessionMaterials', 'attachments'])
            ->whereKey($taskId)
            ->whereHas('classSession', fn ($query) => $query
                ->where('teacher_subject_classes_id', $this->teacherSubjectClassId)
                ->normal()
                ->whereHas('teacherSubjectClass', fn ($teacherQuery) => $teacherQuery
                    ->where('user_teacher_coteacher_id', Auth::id())
                    ->availableForTeacher()
                    ->withActiveStudentSubject()))
            ->firstOrFail();
    }

    private function canDeleteDraftTask(SessionTask $task, ClassSession $session): bool
    {
        return ! $this->isSessionPublished($session)
            && in_array($task->status, [null, 'draft'], true)
            && ! $task->isAutomatedTaskSnapshot()
            && ! SessionTaskStudent::query()->where('session_task_id', $task->id)->exists();
    }

    private function canDeleteDraftSession(ClassSession $session, $tasks): bool
    {
        if ($this->isSessionPublished($session)) {
            return false;
        }

        $taskIds = $tasks->pluck('id')->all();

        if (! empty($taskIds) && SessionTaskStudent::query()->whereIn('session_task_id', $taskIds)->exists()) {
            return false;
        }

        return $tasks->every(fn (SessionTask $task): bool => in_array($task->status, [null, 'draft'], true)
            && ! $task->isAutomatedTaskSnapshot());
    }

    private function isSessionPublished(ClassSession $session): bool
    {
        return optional($session->sessionMaterials)->status === 'published';
    }

    private function isPastDatedDraft(ClassSession $session, string $today): bool
    {
        if ($this->isSessionPublished($session) || $session->date === null) {
            return false;
        }

        return Carbon::parse($session->date, config('app.timezone'))->startOfDay()
            ->lt(Carbon::parse($today, config('app.timezone'))->startOfDay());
    }

    private function deletePublicPathsQuietly(array $paths): void
    {
        foreach ($paths as $path) {
            try {
                app(LibraryFileRetentionService::class)->deleteIfUnreferenced($path);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function render()
    {
        return view('livewire.teacher.sessions-board');
    }
}
