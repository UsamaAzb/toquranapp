<?php

namespace App\Livewire\Teacher;

use App\Models\DailySession;
use App\Models\DailySessionTask;
use App\Models\MainDailySession;
use App\Models\TeacherSubjectClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class DailySessionsBoard extends Component
{
    public int $subjectId;

    public array $sessions = [];

    public array $openDaily = [];

    protected function ensureOwnedSubjectOrFail(?int $subjectId = null): int
    {
        $resolvedSubjectId = $subjectId ?? $this->subjectId;

        TeacherSubjectClass::query()
            ->where('subject_id', $resolvedSubjectId)
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->withActiveStudentSubject()
            ->firstOrFail();

        return $resolvedSubjectId;
    }

    protected function resolveOwnedMainDailySessionOrFail(?int $mainDailySessionId = null): MainDailySession
    {
        $ownedSubjectId = $this->ensureOwnedSubjectOrFail();

        return MainDailySession::query()
            ->whereKey($mainDailySessionId)
            ->where('subject_id', $ownedSubjectId)
            ->firstOrFail();
    }

    protected function resolveOwnedDailySessionOrFail(?int $dailySessionId = null): DailySession
    {
        $ownedSubjectId = $this->ensureOwnedSubjectOrFail();

        return DailySession::query()
            ->whereKey($dailySessionId)
            ->whereHas('main_daily_session', fn ($query) => $query->where('subject_id', $ownedSubjectId))
            ->firstOrFail();
    }

    public function mount(int $subjectId): void
    {
        $this->subjectId = $this->ensureOwnedSubjectOrFail($subjectId);
        $requestedOpenDaily = (int) request()->query('open_daily', 0);
        if ($requestedOpenDaily > 0) {
            $this->openDaily[$requestedOpenDaily] = true;
        }
        $this->loadSessions();
    }

    #[On('main-daily-session-added')]
    #[On('daily-session-added')]
    public function refreshList(?int $dailySessionId = null): void
    {
        if ($dailySessionId) {
            $this->openDaily[$dailySessionId] = true;
        }
        $this->loadSessions();
    }

    protected function loadSessions(): void
    {
        $ownedSubjectId = $this->ensureOwnedSubjectOrFail();

        $data = MainDailySession::query()
            ->where('subject_id', $ownedSubjectId)
            ->with([
                'daily_sessions' => fn ($query) => $query->orderBy('id', 'desc'),
                'daily_sessions.daily_session_tasks' => fn ($query) => $query->orderBy('sort')->orderBy('id'),
                'daily_sessions.daily_session_tasks.attachments',
            ])
            ->orderBy('id', 'desc')
            ->get();

        $this->sessions = $data->map(function ($mainSession) {
            return [
                'id' => (int) $mainSession->id,
                'title' => (string) $mainSession->title,
                'daily_sessions' => $mainSession->daily_sessions->map(fn ($dailySession) => [
                    'id' => (int) $dailySession->id,
                    'title' => (string) $dailySession->title,
                    'tasks' => $dailySession->daily_session_tasks->map(fn ($task) => [
                        'id' => (int) $task->id,
                        'title' => (string) $task->title,
                        'desc' => (string) ($task->description ?? ''),
                        'default_points' => (int) ($task->default_points ?? 0),
                        'max_points' => (int) ($task->max_points ?? 0),
                        'attachments' => $task->attachments->map(fn ($attachment) => [
                            'id' => (int) $attachment->id,
                            'type' => (string) $attachment->type,
                            'title' => (string) ($attachment->title ?? ''),
                            'name' => (string) ($attachment->title ?? ''),
                            'path' => (string) $attachment->path,
                            'url' => in_array(strtolower($attachment->type), ['link', 'youtube'], true)
                                ? (string) $attachment->path
                                : route('daily-sessions.attachment.file', [
                                    'dailySession' => $dailySession->id,
                                    'attachment' => $attachment->id,
                                ]),
                            'size' => $attachment->file_size,
                            'ext' => $attachment->type,
                            'type' => $attachment->type,
                        ])->values()->all(),
                    ])->values()->all(),
                ])->values()->all(),
            ];
        })->values()->all();
    }

    #[On('daily-session-task-saved')]
    public function refreshAfterTaskSaved(?int $dailySessionId = null): void
    {
        if ($dailySessionId) {
            $this->openDaily[$dailySessionId] = true;
        }
        $this->loadSessions();
    }

    #[On('reorder-daily-session-tasks')]
    public function reorderDailyTasks(int $dailySessionId, array $orderedIds): void
    {
        $dailySession = $this->resolveOwnedDailySessionOrFail($dailySessionId);
        $orderedIds = array_values(array_unique(array_map('intval', $orderedIds)));

        $validIds = DailySessionTask::query()
            ->whereIn('id', $orderedIds)
            ->where('daily_session_id', $dailySession->id)
            ->pluck('id')
            ->map(fn ($value) => (int) $value)
            ->all();

        $orderedIds = array_values(array_intersect($orderedIds, $validIds));

        DB::transaction(function () use ($dailySession, $orderedIds) {
            foreach ($orderedIds as $index => $taskId) {
                DailySessionTask::where('id', $taskId)
                    ->where('daily_session_id', $dailySession->id)
                    ->update(['sort' => $index + 1]);
            }
        });

        $this->loadSessions();
    }

    public function updateMainTitle(int $id, string $title): void
    {
        $title = trim($title);
        if ($title === '') {
            return;
        }

        $mainDailySession = $this->resolveOwnedMainDailySessionOrFail($id);
        $mainDailySession->update(['title' => $title]);

        $this->loadSessions();
    }

    public function updateDailyTitle(int $id, string $title): void
    {
        $title = trim($title);
        if ($title === '') {
            return;
        }

        $dailySession = $this->resolveOwnedDailySessionOrFail($id);
        $dailySession->update(['title' => $title]);

        $this->loadSessions();
    }

    public function addOpen(int $dailySessionId): void
    {
        $this->openDaily[$dailySessionId] = true;
    }

    public function removeOpen(int $dailySessionId): void
    {
        unset($this->openDaily[$dailySessionId]);
    }

    public function render()
    {
        return view('livewire.teacher.daily-sessions-board');
    }
}
