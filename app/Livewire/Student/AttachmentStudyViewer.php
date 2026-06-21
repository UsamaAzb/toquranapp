<?php

namespace App\Livewire\Student;

use App\Models\AttachmentFile;
use App\Models\ClassSession;
use App\Models\SessionTask;
use App\Models\StudentsSubject;
use App\Services\Library\LibraryResourceAccessService;
use App\Services\Library\TaskAttachmentPresenter;
use App\Support\LifecycleGate;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class AttachmentStudyViewer extends Component
{
    public int $studentId;

    public string $surface = TaskAttachmentPresenter::SURFACE_SESSION;

    public bool $open = false;

    public ?int $sessionId = null;

    public ?int $taskId = null;

    public ?int $attachmentId = null;

    public int $currentIndex = 0;

    public string $taskTitle = '';

    /** @var array<int, array<string, mixed>> */
    public array $items = [];

    /** @var array<string, mixed> */
    public array $currentItem = [];

    public function mount(int $studentId, string $surface = TaskAttachmentPresenter::SURFACE_SESSION): void
    {
        $this->studentId = $studentId;
        $this->surface = $surface;
    }

    #[On('open-attachment-study-viewer')]
    public function openViewer(int $sessionId, int $taskId, int $attachmentId): void
    {
        $user = Auth::user();
        abort_unless($user, 403);
        abort_if(LifecycleGate::inspect($this->studentId)->denied(), 403, LifecycleGate::NEUTRAL_MESSAGE);

        $session = ClassSession::query()
            ->whereKey($sessionId)
            ->visibleToLearner($this->studentId)
            ->firstOrFail();

        if ($this->currentUserIsTeacher() && ! $this->teacherCanViewSessionForStudent($session)) {
            abort(403);
        }

        abort_unless(
            StudentsSubject::query()
                ->where('student_id', $this->studentId)
                ->where('class_subject_id', $session->class_subject_id)
                ->where('status', 'active')
                ->exists(),
            403
        );

        $task = SessionTask::query()
            ->with(['attachments' => fn ($query) => $query->orderedForDelivery()])
            ->whereKey($taskId)
            ->where('class_session_id', $sessionId)
            ->firstOrFail();

        $this->sessionId = $sessionId;
        $this->taskId = $taskId;
        $this->attachmentId = $attachmentId;

        $presenter = app(TaskAttachmentPresenter::class);
        $access = app(LibraryResourceAccessService::class);
        $returnTo = $this->returnTarget($session);

        $this->items = $task->attachments
            ->filter(fn (AttachmentFile $attachment): bool => $this->currentUserIsTeacher()
                ? $access->canTeacherAccessAttachment($user, $this->studentId, $sessionId, $attachment)
                : $access->canLearnerAccessAttachment($user, $this->studentId, $sessionId, $attachment))
            ->map(fn (AttachmentFile $attachment): array => $presenter->forLearner(
                $attachment,
                $sessionId,
                $this->studentId,
                $this->surface,
                $returnTo
            ))
            ->values()
            ->all();

        abort_if($this->items === [], 404);

        $index = collect($this->items)->search(
            fn (array $item): bool => (int) ($item['id'] ?? 0) === $attachmentId
        );

        abort_if($index === false, 404);

        $this->taskTitle = (string) ($task->title ?: 'Task');
        $this->currentIndex = (int) $index;
        $this->syncCurrentItem();
        $this->open = true;
    }

    public function closeViewer(): void
    {
        $this->open = false;
        $this->items = [];
        $this->currentItem = [];
        $this->sessionId = null;
        $this->taskId = null;
        $this->attachmentId = null;
        $this->currentIndex = 0;
        $this->taskTitle = '';

        $this->dispatch('attachment-study-viewer-closed');
    }

    public function previousAttachment(): void
    {
        if ($this->currentIndex <= 0) {
            return;
        }

        $this->currentIndex--;
        $this->syncCurrentItem();
    }

    public function nextAttachment(): void
    {
        if ($this->currentIndex >= count($this->items) - 1) {
            return;
        }

        $this->currentIndex++;
        $this->syncCurrentItem();
    }

    private function syncCurrentItem(): void
    {
        $this->currentItem = $this->items[$this->currentIndex] ?? [];
        $this->attachmentId = isset($this->currentItem['id']) ? (int) $this->currentItem['id'] : null;
    }

    #[Computed]
    public function currentItemMode(): string
    {
        return (string) ($this->currentItem['mode'] ?? 'unavailable');
    }

    #[Computed]
    public function currentItemExtension(): string
    {
        return (string) ($this->currentItem['extension'] ?? '');
    }

    #[Computed]
    public function currentItemMedia(): array
    {
        $extension = $this->currentItemExtension();

        return [
            'image' => in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true),
            'video' => in_array($extension, ['mp4', 'mov', 'webm', 'm4v', 'ogv'], true),
            'audio' => in_array($extension, ['mp3', 'wav', 'ogg', 'm4a'], true),
            'pdf' => $extension === 'pdf',
            'office' => in_array($extension, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'], true),
        ];
    }

    #[Computed]
    public function currentItemViewerKey(): string
    {
        return 'viewer-item-'
            .($this->currentItem['id'] ?? 'none')
            .'-'.$this->currentIndex
            .'-'.$this->currentItemMode()
            .'-'.md5(implode('|', [
                (string) ($this->currentItem['content_url'] ?? ''),
                (string) ($this->currentItem['open_url'] ?? ''),
                (string) ($this->currentItem['embed_url'] ?? ''),
                (string) ($this->currentItem['viewer_provider'] ?? ''),
                (string) ($this->currentItem['viewer_url'] ?? ''),
                $this->currentItemExtension(),
            ]));
    }

    #[Computed]
    public function isFirstAttachment(): bool
    {
        return $this->currentIndex <= 0;
    }

    #[Computed]
    public function isLastAttachment(): bool
    {
        return $this->currentIndex >= count($this->items) - 1;
    }

    private function returnTarget(ClassSession $session): string
    {
        $studentSubjectId = StudentsSubject::query()
            ->where('student_id', $this->studentId)
            ->where('class_subject_id', $session->class_subject_id)
            ->where('status', 'active')
            ->value('id');

        if ($this->surface === TaskAttachmentPresenter::SURFACE_JOURNEY) {
            return route('student.tasks.journey', [
                'sessionId' => $session->id,
                'student_id' => $this->studentId,
                'task' => $this->taskId,
            ]);
        }

        $isParentContext = (bool) Auth::user()?->hasRole('parent');

        $base = match (true) {
            $studentSubjectId && $isParentContext => route('student.sessions', [
                'student_subject_id' => $studentSubjectId,
                'student_id' => $this->studentId,
            ]),
            (bool) $studentSubjectId => route('student.sessions', [
                'student_subject_id' => $studentSubjectId,
            ]),
            $isParentContext => route('student.classes', [
                'student_id' => $this->studentId,
            ]),
            default => route('student.classes'),
        };

        return $base.'?'.http_build_query(['open_session' => $session->id]).'#task-'.$this->taskId;
    }

    private function currentUserIsTeacher(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();

        return $user->getRoleNames()->diff(['teacher'])->isEmpty()
            && $user->hasRole('teacher');
    }

    private function teacherCanViewSessionForStudent(ClassSession $session): bool
    {
        return ClassSession::query()
            ->whereKey($session->id)
            ->visibleToLearner($this->studentId)
            ->whereHas('teacherSubjectClass', fn ($query) => $query
                ->where('user_teacher_coteacher_id', Auth::id())
                ->availableForTeacher()
                ->whereHas('classSubject.studentsSubjects', fn ($studentSubjectQuery) => $studentSubjectQuery
                    ->where('student_id', $this->studentId)
                    ->where('status', 'active')
                    ->whereHas('student', fn ($studentQuery) => $studentQuery->visibleToTeacher())))
            ->exists();
    }

    public function render()
    {
        return view('livewire.student.attachment-study-viewer');
    }
}
