<?php

namespace App\Livewire\Teacher;

use App\Models\AttachmentFile;
use App\Models\ClassSession;
use App\Models\SessionTask;
use App\Services\Library\TaskAttachmentPresenter;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class AttachmentStudyViewer extends Component
{
    public int $teacherSubjectClassId;

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

    public function mount(int $teacherSubjectClassId): void
    {
        $this->teacherSubjectClassId = $teacherSubjectClassId;
    }

    #[On('open-teacher-attachment-study-viewer')]
    public function openViewer(int $sessionId, int $taskId, int $attachmentId): void
    {
        abort_unless(Auth::check() && Auth::user()?->hasRole('teacher'), 403);

        $session = ClassSession::query()
            ->whereKey($sessionId)
            ->where('teacher_subject_classes_id', $this->teacherSubjectClassId)
            ->normal()
            ->whereHas('teacherSubjectClass', fn ($query) => $query
                ->where('user_teacher_coteacher_id', Auth::id())
                ->availableForTeacher()
                ->withActiveStudentSubject())
            ->firstOrFail();

        $task = SessionTask::query()
            ->with(['attachments' => fn ($query) => $query->orderedForDelivery()])
            ->whereKey($taskId)
            ->where('class_session_id', $session->id)
            ->firstOrFail();

        $this->sessionId = $session->id;
        $this->taskId = $task->id;
        $this->attachmentId = $attachmentId;

        $presenter = app(TaskAttachmentPresenter::class);
        $returnTo = $this->returnTarget($session, $task);

        $this->items = $task->attachments
            ->map(fn (AttachmentFile $attachment): array => $presenter->forTeacherSession(
                $attachment,
                $session->id,
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

    private function returnTarget(ClassSession $session, SessionTask $task): string
    {
        return route('teacher.sessions', ['teachersubjectid' => $session->teacher_subject_classes_id])
            .'?'.http_build_query(['open_session' => $session->id])
            .'#task-'.$task->id;
    }

    public function render()
    {
        return view('livewire.student.attachment-study-viewer');
    }
}
