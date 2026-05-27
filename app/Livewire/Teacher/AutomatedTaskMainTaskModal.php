<?php

namespace App\Livewire\Teacher;

use App\Models\MainDailySessionMainTask;
use App\Models\MainDailySessionMainTaskAttachment;
use App\Models\MainDailySessionTemplate;
use App\Models\TaskType;
use App\Models\TeacherSubjectClass;
use App\Models\LibraryResource;
use App\Services\Library\LegacyLibraryTaskResourceCatalog;
use App\Services\Library\LibraryResourceQuery;
use App\Services\Library\LibraryResourceValidator;
use App\Services\Library\LibraryToVersionedRoutineAttachmentWriter;
use App\Services\SeriesLibrarySourceResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class AutomatedTaskMainTaskModal extends Component
{
    use WithFileUploads;

    private const MAX_ATTACHMENT_FILE_KB = 51200;

    public bool $show = false;

    public ?int $templateId = null;

    public ?int $mainTaskId = null;

    public string $title = '';

    public ?string $description = null;

    public ?int $taskTypeId = null;

    public ?int $defaultPoints = 5;

    public ?int $maxPoints = 10;

    public array $taskTypes = [];

    public array $files = [];

    public array $finalFiles = [];

    public array $links = [];

    public array $youtubes = [];

    public array $selectedLibraryResourceIds = [];

    public array $selectedLibraryResources = [];

    public array $attachmentDraftOrder = [];

    public string $linkTitle = '';

    public string $linkUrl = '';

    public string $youtubeTitle = '';

    public string $youtubeUrl = '';

    public array $existingAttachments = [];

    public array $existingFiles = [];

    public array $existingLinks = [];

    public array $existingYoutubes = [];

    public array $attachmentsToDelete = [];

    public bool $showLinkForm = false;

    public bool $showYoutubeForm = false;

    public bool $locked = false;

    public bool $uploadsInProgress = false;

    public function mount(): void
    {
        $this->taskTypes = TaskType::query()
            ->orderBy('title')
            ->get(['id', 'title', 'default_points', 'max_points'])
            ->map(fn ($type): array => [
                'id' => (int) $type->id,
                'title' => (string) $type->title,
                'default_points' => $type->default_points,
                'max_points' => $type->max_points,
            ])
            ->all();

        $this->taskTypeId = $this->taskTypes[0]['id'] ?? null;
    }

    public function setUploadsInProgress(bool $value): void
    {
        $this->uploadsInProgress = $value;
    }

    #[On('open-automated-task-main-task-modal')]
    public function open(int $templateId, ?int $mainTaskId = null): void
    {
        $template = $this->resolveOwnedTemplateOrFail($templateId);

        $this->resetValidation();
        $this->show = true;
        $this->templateId = $template->id;
        $this->mainTaskId = null;
        $this->title = '';
        $this->description = null;
        $this->taskTypeId = $this->taskTypes[0]['id'] ?? null;
        $this->defaultPoints = 5;
        $this->maxPoints = 10;
        $this->files = [];
        $this->finalFiles = [];
        $this->links = [];
        $this->youtubes = [];
        $this->selectedLibraryResourceIds = [];
        $this->selectedLibraryResources = [];
        $this->attachmentDraftOrder = [];
        $this->linkTitle = '';
        $this->linkUrl = '';
        $this->youtubeTitle = '';
        $this->youtubeUrl = '';
        $this->existingAttachments = [];
        $this->existingFiles = [];
        $this->existingLinks = [];
        $this->existingYoutubes = [];
        $this->attachmentsToDelete = [];
        $this->showLinkForm = false;
        $this->showYoutubeForm = false;

        if ($mainTaskId === null) {
            return;
        }

        $task = $this->resolveOwnedMainTaskOrFail($mainTaskId);
        $this->mainTaskId = $task->id;
        $this->title = (string) $task->title;
        $this->description = $task->description;
        $this->taskTypeId = $task->task_type_id;
        $this->defaultPoints = $task->default_points;
        $this->maxPoints = $task->max_points;
        $this->existingAttachments = $task->attachments
            ->map(fn ($attachment): array => $this->mapAttachment($attachment))
            ->all();
        $this->rebuildAttachmentState();
    }

    public function close(): void
    {
        $this->show = false;
    }

    public function updatedTaskTypeId($value): void
    {
        $selectedType = collect($this->taskTypes)->firstWhere('id', (int) $value);

        if ($selectedType === null) {
            return;
        }

        $this->defaultPoints = is_numeric($selectedType['default_points'] ?? null)
            ? (int) $selectedType['default_points']
            : $this->defaultPoints;

        $this->maxPoints = is_numeric($selectedType['max_points'] ?? null)
            ? (int) $selectedType['max_points']
            : $this->maxPoints;
    }

    public function addLink(): void
    {
        $this->resetErrorBag(['links_pending', 'linkTitle', 'linkUrl']);

        $this->validate([
            'linkTitle' => ['required', 'string', 'max:191'],
            'linkUrl' => ['required', 'url', 'max:2048'],
        ]);

        $this->links[] = [
            'key' => $this->newDraftItemKey('link'),
            'title' => trim($this->linkTitle),
            'url' => trim($this->linkUrl),
        ];

        $this->linkTitle = '';
        $this->linkUrl = '';
        $this->rebuildAttachmentState();
    }

    public function addYoutube(): void
    {
        $this->resetErrorBag(['youtubes_pending', 'youtubeTitle', 'youtubeUrl']);

        $this->validate([
            'youtubeTitle' => ['nullable', 'string', 'max:191'],
            'youtubeUrl' => ['required', 'url', 'max:2048'],
        ]);

        $this->youtubes[] = [
            'key' => $this->newDraftItemKey('youtube'),
            'title' => trim($this->youtubeTitle) ?: 'YouTube',
            'url' => trim($this->youtubeUrl),
        ];

        $this->youtubeTitle = '';
        $this->youtubeUrl = '';
        $this->rebuildAttachmentState();
    }

    public function removeLink(int $index): void
    {
        unset($this->links[$index]);
        $this->links = array_values($this->links);
        $this->rebuildAttachmentState();
    }

    public function removeYoutube(int $index): void
    {
        unset($this->youtubes[$index]);
        $this->youtubes = array_values($this->youtubes);
        $this->rebuildAttachmentState();
    }

    public function markAttachmentForDeletion(int $attachmentId): void
    {
        if (! in_array($attachmentId, $this->attachmentsToDelete, true)) {
            $this->attachmentsToDelete[] = $attachmentId;
        }

        $this->rebuildAttachmentState();
    }

    public function removePendingUpload(int $index): void
    {
        $this->removeFile($index);
    }

    public function clickAddLink(): void
    {
        $this->linkTitle = '';
        $this->linkUrl = '';
        $this->youtubeTitle = '';
        $this->youtubeUrl = '';
        $this->showLinkForm = true;
        $this->showYoutubeForm = false;
    }

    public function clickAddYoutube(): void
    {
        $this->linkTitle = '';
        $this->linkUrl = '';
        $this->youtubeTitle = '';
        $this->youtubeUrl = '';
        $this->showYoutubeForm = true;
        $this->showLinkForm = false;
    }

    public function removeFile(int $index): void
    {
        unset($this->finalFiles[$index]);
        $this->finalFiles = array_values($this->finalFiles);
        $this->rebuildAttachmentState();
    }

    public function removePendingFile(string $uploadKey): void
    {
        $this->finalFiles = array_values(array_filter(
            $this->finalFiles,
            fn ($upload, int|string $index): bool => $this->temporaryUploadKey($upload) !== $uploadKey
                && $this->pendingFileKey($upload, $index) !== $uploadKey,
            ARRAY_FILTER_USE_BOTH
        ));

        $this->files = array_values(array_filter(
            $this->files,
            fn ($upload, int|string $index): bool => $this->temporaryUploadKey($upload) !== $uploadKey
                && $this->pendingFileKey($upload, $index) !== $uploadKey,
            ARRAY_FILTER_USE_BOTH
        ));

        $this->rebuildAttachmentState();
    }

    public function reorderPendingFiles(array $orderedKeys): void
    {
        if ($this->locked || $orderedKeys === []) {
            return;
        }

        $order = array_flip(array_map('strval', $orderedKeys));

        usort(
            $this->finalFiles,
            fn ($left, $right): int => $this->pendingFileOrderIndex($left, $order)
                <=> $this->pendingFileOrderIndex($right, $order)
        );

        $this->finalFiles = array_values($this->finalFiles);
        $this->attachmentDraftOrder = $this->currentDraftKeys();
        $this->rebuildAttachmentState();
    }

    public function pendingFileKey(mixed $upload, int|string|null $index = null): string
    {
        $uploadKey = $this->temporaryUploadKey($upload);

        if ($uploadKey !== null) {
            return $uploadKey;
        }

        if (is_object($upload) && method_exists($upload, 'getClientOriginalName')) {
            return (string) $upload->getClientOriginalName();
        }

        return 'pending-file-'.(string) ($index ?? 0);
    }

    public function updatedFiles(): void
    {
        $this->appendUniqueFinalUploads($this->files);
        $this->files = [];
        $this->rebuildAttachmentState();
    }

    public function openLibraryPicker(): void
    {
        if ($this->locked || $this->templateId === null) {
            return;
        }

        $template = $this->resolveOwnedTemplateOrFail((int) $this->templateId);

        $this->dispatch(
            'open-library-picker',
            subjectId: (int) $template->subject_id,
            selectedResourceIds: $this->selectedLibraryResourceIds
        );
    }

    #[On('library-resources-selected')]
    public function useLibraryResources(array $resourceIds): void
    {
        if (! $this->show) {
            return;
        }

        $this->selectedLibraryResourceIds = collect($resourceIds)
            ->map(fn ($id): string => trim((string) $id))
            ->filter(fn (string $id): bool => $id !== '')
            ->unique()
            ->values()
            ->all();

        $this->refreshSelectedLibraryResources();
        $this->resetErrorBag('content');
    }

    public function removeLibraryResource(string $resourceId): void
    {
        $resourceId = trim($resourceId);
        $this->selectedLibraryResourceIds = array_values(array_filter(
            $this->selectedLibraryResourceIds,
            fn ($selectedId): bool => (string) $selectedId !== $resourceId
        ));

        $this->refreshSelectedLibraryResources();
    }

    public function reorderAttachmentDraftItems(array $orderedKeys): void
    {
        if ($this->locked || $orderedKeys === []) {
            return;
        }

        $validKeys = array_flip($this->currentDraftKeys());
        $orderedKeys = collect($orderedKeys)
            ->map(fn ($key): string => trim((string) $key))
            ->filter(fn (string $key): bool => isset($validKeys[$key]))
            ->unique()
            ->values()
            ->all();

        foreach ($this->currentDraftKeys() as $key) {
            if (! in_array($key, $orderedKeys, true)) {
                $orderedKeys[] = $key;
            }
        }

        $this->attachmentDraftOrder = $orderedKeys;
        $this->applyDraftOrderToSourceArrays();
        $this->rebuildAttachmentState();
    }

    public function removeDraftAttachmentItem(string $draftKey): void
    {
        if ($this->locked) {
            return;
        }

        [$kind, $rawKey] = $this->splitDraftKey($draftKey);

        if ($kind === 'existing') {
            $this->markAttachmentForDeletion((int) $rawKey);

            return;
        }

        if ($kind === 'pending_file') {
            $this->removePendingFile($rawKey);

            return;
        }

        if ($kind === 'library') {
            $this->removeLibraryResource($rawKey);

            return;
        }

        if ($kind === 'link') {
            $index = $this->findLinkIndexByKey($rawKey);
            if ($index !== null) {
                $this->removeLink($index);
            }

            return;
        }

        if ($kind === 'youtube') {
            $index = $this->findYoutubeIndexByKey($rawKey);
            if ($index !== null) {
                $this->removeYoutube($index);
            }
        }
    }

    public function save(): void
    {
        $pendingLink = trim($this->linkTitle) !== '' || trim($this->linkUrl) !== '';
        $pendingYoutube = trim($this->youtubeTitle) !== '' || trim($this->youtubeUrl) !== '';

        $this->resetErrorBag(['links_pending', 'youtubes_pending', 'files']);

        if ($pendingLink) {
            if (trim($this->linkTitle) === '') {
                $this->addError('links_pending', 'Enter the Link Title, then click Add Link.');

                return;
            }

            if (trim($this->linkUrl) === '') {
                $this->addError('links_pending', 'Enter the Link URL, then click Add Link.');

                return;
            }

            $this->addError('links_pending', 'Click Add Link before saving this task.');

            return;
        }

        if ($pendingYoutube) {
            if (trim($this->youtubeUrl) === '') {
                $this->addError('youtubes_pending', 'Enter the Youtube Link, then click Add Youtube.');

                return;
            }

            $this->addError('youtubes_pending', 'Click Add Youtube before saving this task.');

            return;
        }

        if ($this->uploadsInProgress) {
            $this->addError('files', 'Wait for uploads to finish before saving.');

            return;
        }

        $this->validate([
            'templateId' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:191'],
            'description' => ['nullable', 'string'],
            'taskTypeId' => ['required', 'integer', 'exists:task_types,id'],
            'defaultPoints' => ['nullable', 'integer', 'min:0'],
            'maxPoints' => ['nullable', 'integer', 'min:0', 'gte:defaultPoints'],
            'finalFiles' => ['nullable', 'array'],
            'finalFiles.*' => ['file', 'max:'.self::MAX_ATTACHMENT_FILE_KB],
        ]);

        $template = $this->resolveOwnedTemplateOrFail((int) $this->templateId);
        $this->refreshSelectedLibraryResources();
        $this->syncAttachmentDraftOrder();
        $this->applyDraftOrderToSourceArrays();

        $storedFiles = [];
        $pathsToDelete = [];

        try {
            DB::transaction(function () use ($template, &$storedFiles, &$pathsToDelete): void {
                $task = $this->mainTaskId
                    ? $this->resolveOwnedMainTaskOrFail((int) $this->mainTaskId)
                    : MainDailySessionMainTask::create([
                        'main_daily_session_template_id' => $template->id,
                        'title' => trim($this->title),
                        'description' => $this->cleanNullableText($this->description),
                        'task_type_id' => (int) $this->taskTypeId,
                        'default_points' => $this->defaultPoints,
                        'max_points' => $this->maxPoints,
                        'sort_order' => ((int) $template->mainTasks()->max('sort_order')) + 1,
                    ]);

                if ($this->mainTaskId) {
                    $task->update([
                        'title' => trim($this->title),
                        'description' => $this->cleanNullableText($this->description),
                        'task_type_id' => (int) $this->taskTypeId,
                        'default_points' => $this->defaultPoints,
                        'max_points' => $this->maxPoints,
                    ]);
                }

                $this->createAttachmentsForMainTask($task, $template, $storedFiles);

                if (! empty($this->attachmentsToDelete)) {
                    $attachments = $task->attachments()
                        ->whereIn('id', $this->attachmentsToDelete)
                        ->get();

                    foreach ($attachments as $attachment) {
                        if ($attachment->isFile() && $attachment->path) {
                            $pathsToDelete[] = $attachment->path;
                        }

                        $attachment->delete();
                    }
                }
            });
        } catch (\Throwable $throwable) {
            foreach ($storedFiles as $storedFile) {
                if ($storedFile && Storage::disk('public')->exists($storedFile)) {
                    Storage::disk('public')->delete($storedFile);
                }
            }

            throw $throwable;
        }

        foreach ($pathsToDelete as $pathToDelete) {
            if ($pathToDelete && Storage::disk('public')->exists($pathToDelete)) {
                Storage::disk('public')->delete($pathToDelete);
            }
        }

        $this->dispatch('automated-task-main-task-saved');
        $this->selectedLibraryResourceIds = [];
        $this->selectedLibraryResources = [];
        $this->attachmentDraftOrder = [];
        $this->close();
    }

    public function render(): View
    {
        return view('livewire.teacher.automated-task-main-task-modal', [
            'taskFileAcceptAttribute' => LibraryResourceValidator::acceptAttribute(),
            'taskFileAllowedExtensions' => LibraryResourceValidator::allowedExtensions(),
            'taskFileMaxBytes' => self::MAX_ATTACHMENT_FILE_KB * 1024,
        ]);
    }

    private function createAttachmentsForMainTask(
        MainDailySessionMainTask $task,
        MainDailySessionTemplate $template,
        array &$storedFiles
    ): void {
        $this->syncAttachmentDraftOrder();
        $sortOrder = 1;
        $libraryWriter = app(LibraryToVersionedRoutineAttachmentWriter::class);

        foreach ($this->attachmentDraftOrder as $draftKey) {
            [$kind, $rawKey] = $this->splitDraftKey((string) $draftKey);

            if ($kind === 'existing') {
                $attachmentId = (int) $rawKey;

                if ($attachmentId > 0 && ! in_array($attachmentId, $this->attachmentsToDelete, true)) {
                    MainDailySessionMainTaskAttachment::query()
                        ->whereKey($attachmentId)
                        ->where('main_task_id', $task->id)
                        ->update(['sort_order' => $sortOrder]);

                    $sortOrder++;
                }

                continue;
            }

            if ($kind === 'pending_file') {
                $upload = $this->findPendingFileByKey($rawKey);

                if ($upload === null) {
                    continue;
                }

                $storedPath = Storage::disk('public')->putFile('automated-task-attachments', $upload);

                if ($storedPath === false) {
                    throw new \RuntimeException('Failed to store attachment file.');
                }

                $storedFiles[] = $storedPath;

                MainDailySessionMainTaskAttachment::create([
                    'main_task_id' => $task->id,
                    'type' => 'file',
                    'title' => $upload->getClientOriginalName(),
                    'description' => null,
                    'path' => $storedPath,
                    'url' => null,
                    'file_size' => $upload->getSize() ?: null,
                    'sort_order' => $sortOrder,
                ]);

                $sortOrder++;

                continue;
            }

            if ($kind === 'library') {
                if ($libraryWriter->writeOneForMainTaskAtSortOrder(
                    $task,
                    $rawKey,
                    (int) Auth::id(),
                    (int) $template->subject_id,
                    $sortOrder
                )) {
                    $sortOrder++;
                }

                continue;
            }

            if ($kind === 'link') {
                $link = $this->findLinkByKey($rawKey);

                if ($link === null) {
                    continue;
                }

                MainDailySessionMainTaskAttachment::create([
                    'main_task_id' => $task->id,
                    'type' => 'link',
                    'title' => $link['title'],
                    'description' => null,
                    'path' => null,
                    'url' => $link['url'],
                    'file_size' => null,
                    'sort_order' => $sortOrder,
                ]);

                $sortOrder++;

                continue;
            }

            if ($kind === 'youtube') {
                $youtube = $this->findYoutubeByKey($rawKey);

                if ($youtube === null) {
                    continue;
                }

                MainDailySessionMainTaskAttachment::create([
                    'main_task_id' => $task->id,
                    'type' => 'youtube',
                    'title' => $youtube['title'],
                    'description' => null,
                    'path' => null,
                    'url' => $youtube['url'],
                    'file_size' => null,
                    'sort_order' => $sortOrder,
                ]);

                $sortOrder++;
            }
        }
    }

    private function resolveOwnedTemplateOrFail(int $templateId): MainDailySessionTemplate
    {
        TeacherSubjectClass::query()
            ->where('subject_id', function ($query) use ($templateId): void {
                $query->select('subject_id')
                    ->from('main_daily_session_templates')
                    ->where('id', $templateId)
                    ->where('created_by_user_id', Auth::id())
                    ->limit(1);
            })
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->firstOrFail();

        return MainDailySessionTemplate::query()
            ->whereKey($templateId)
            ->where('created_by_user_id', Auth::id())
            ->firstOrFail();
    }

    private function resolveOwnedMainTaskOrFail(int $mainTaskId): MainDailySessionMainTask
    {
        return MainDailySessionMainTask::query()
            ->with('attachments')
            ->whereKey($mainTaskId)
            ->whereHas('template', function ($query): void {
                $query->whereExists(function ($subQuery): void {
                    $subQuery->selectRaw('1')
                        ->from('teacher_subject_classes')
                        ->whereColumn('teacher_subject_classes.subject_id', 'main_daily_session_templates.subject_id')
                        ->where('teacher_subject_classes.user_teacher_coteacher_id', Auth::id())
                        ->whereIn('teacher_subject_classes.status', ['active', 'current']);
                })->where('created_by_user_id', Auth::id());
            })
            ->firstOrFail();
    }

    private function mapAttachment(MainDailySessionMainTaskAttachment $attachment): array
    {
        $type = strtolower((string) $attachment->type);
        $resource = (string) ($attachment->url ?? $attachment->path ?? '');
        $url = $type === 'file'
            ? route('daily-sessions.template-attachment.file', [
                'template' => (int) $this->templateId,
                'attachment' => $attachment->id,
            ])
            : $resource;

        return [
            'id' => (int) $attachment->id,
            'type' => $type,
            'title' => (string) ($attachment->title ?: 'Attachment'),
            'name' => (string) ($attachment->title ?: 'Attachment'),
            'path' => $resource,
            'url' => $url,
            'size' => $attachment->file_size,
        ];
    }

    private function rebuildAttachmentState(): void
    {
        $activeAttachments = array_values(array_filter(
            $this->existingAttachments,
            fn (array $attachment): bool => ! in_array($attachment['id'] ?? null, $this->attachmentsToDelete, true)
        ));

        $this->existingFiles = array_values(array_filter(
            $activeAttachments,
            fn (array $attachment): bool => strtolower((string) ($attachment['type'] ?? '')) === 'file'
        ));

        $this->existingLinks = array_values(array_filter(
            $activeAttachments,
            fn (array $attachment): bool => strtolower((string) ($attachment['type'] ?? '')) === 'link'
        ));

        $this->existingYoutubes = array_values(array_filter(
            $activeAttachments,
            fn (array $attachment): bool => strtolower((string) ($attachment['type'] ?? '')) === 'youtube'
        ));

        $this->syncAttachmentDraftOrder();
    }

    #[Computed]
    public function attachmentDraftItems(): array
    {
        $this->syncAttachmentDraftOrder();

        $itemsByKey = [];

        foreach ($this->existingAttachments as $attachment) {
            if (in_array($attachment['id'] ?? null, $this->attachmentsToDelete, true)) {
                continue;
            }

            $type = (string) ($attachment['type'] ?? 'file');
            $key = 'existing:'.(string) ($attachment['id'] ?? '');
            $itemsByKey[$key] = [
                'key' => $key,
                'kind' => 'existing',
                'type' => $type,
                'title' => (string) ($attachment['title'] ?? 'Attachment'),
                'url' => (string) ($attachment['url'] ?? ''),
                'meta' => $this->draftItemMeta('existing', $type),
                'size' => $attachment['size'] ?? null,
            ];
        }

        foreach ($this->finalFiles as $index => $upload) {
            $rawKey = $this->pendingFileKey($upload, $index);
            $key = 'pending_file:'.$rawKey;
            $itemsByKey[$key] = [
                'key' => $key,
                'kind' => 'pending_file',
                'type' => 'file',
                'title' => is_object($upload) && method_exists($upload, 'getClientOriginalName')
                    ? (string) $upload->getClientOriginalName()
                    : 'New file',
                'url' => '',
                'meta' => 'New file',
                'size' => is_object($upload) && method_exists($upload, 'getSize') ? $upload->getSize() : null,
            ];
        }

        foreach ($this->selectedLibraryResources as $resource) {
            $type = (string) ($resource['type'] ?? 'link');
            $key = 'library:'.(string) ($resource['id'] ?? '');
            $itemsByKey[$key] = [
                'key' => $key,
                'kind' => 'library',
                'type' => $type,
                'title' => (string) ($resource['title'] ?? 'Library source'),
                'url' => '',
                'meta' => 'Library source',
                'detail' => (string) ($resource['detail'] ?? ''),
                'size' => null,
            ];
        }

        foreach ($this->links as $index => $link) {
            $rawKey = $this->linkKey($link, $index);
            $key = 'link:'.$rawKey;
            $itemsByKey[$key] = [
                'key' => $key,
                'kind' => 'link',
                'type' => 'link',
                'title' => (string) ($link['title'] ?? 'Link'),
                'url' => (string) ($link['url'] ?? ''),
                'meta' => 'New link',
                'size' => null,
            ];
        }

        foreach ($this->youtubes as $index => $youtube) {
            $rawKey = $this->youtubeKey($youtube, $index);
            $key = 'youtube:'.$rawKey;
            $itemsByKey[$key] = [
                'key' => $key,
                'kind' => 'youtube',
                'type' => 'youtube',
                'title' => (string) ($youtube['title'] ?? 'YouTube'),
                'url' => (string) ($youtube['url'] ?? ''),
                'meta' => 'New YouTube link',
                'size' => null,
            ];
        }

        $items = [];
        foreach ($this->attachmentDraftOrder as $key) {
            if (isset($itemsByKey[$key])) {
                $items[] = $this->decorateDraftItem($itemsByKey[$key]);
            }
        }

        return $items;
    }

    private function syncAttachmentDraftOrder(): void
    {
        $validKeys = $this->currentDraftKeys();
        $validLookup = array_flip($validKeys);

        $ordered = collect($this->attachmentDraftOrder)
            ->map(fn ($key): string => (string) $key)
            ->filter(fn (string $key): bool => isset($validLookup[$key]))
            ->unique()
            ->values()
            ->all();

        foreach ($validKeys as $key) {
            if (! in_array($key, $ordered, true)) {
                $ordered[] = $key;
            }
        }

        $this->attachmentDraftOrder = $ordered;
    }

    private function currentDraftKeys(): array
    {
        $keys = [];

        foreach ($this->existingAttachments as $attachment) {
            if (! in_array($attachment['id'] ?? null, $this->attachmentsToDelete, true)) {
                $keys[] = 'existing:'.(string) ($attachment['id'] ?? '');
            }
        }

        foreach ($this->finalFiles as $index => $upload) {
            $keys[] = 'pending_file:'.$this->pendingFileKey($upload, $index);
        }

        foreach ($this->selectedLibraryResourceIds as $resourceId) {
            $keys[] = 'library:'.(string) $resourceId;
        }

        foreach ($this->links as $index => $link) {
            $keys[] = 'link:'.$this->linkKey($link, $index);
        }

        foreach ($this->youtubes as $index => $youtube) {
            $keys[] = 'youtube:'.$this->youtubeKey($youtube, $index);
        }

        return array_values(array_unique(array_filter($keys, fn (string $key): bool => ! str_ends_with($key, ':'))));
    }

    private function applyDraftOrderToSourceArrays(): void
    {
        $order = array_flip($this->attachmentDraftOrder);

        usort(
            $this->finalFiles,
            fn ($left, $right): int => ($order['pending_file:'.$this->pendingFileKey($left)] ?? PHP_INT_MAX)
                <=> ($order['pending_file:'.$this->pendingFileKey($right)] ?? PHP_INT_MAX)
        );
        $this->finalFiles = array_values($this->finalFiles);

        usort(
            $this->links,
            fn (array $left, array $right): int => ($order['link:'.$this->linkKey($left)] ?? PHP_INT_MAX)
                <=> ($order['link:'.$this->linkKey($right)] ?? PHP_INT_MAX)
        );
        $this->links = array_values($this->links);

        usort(
            $this->youtubes,
            fn (array $left, array $right): int => ($order['youtube:'.$this->youtubeKey($left)] ?? PHP_INT_MAX)
                <=> ($order['youtube:'.$this->youtubeKey($right)] ?? PHP_INT_MAX)
        );
        $this->youtubes = array_values($this->youtubes);

        $selectedById = [];
        foreach ($this->selectedLibraryResourceIds as $resourceId) {
            $selectedById[(string) $resourceId] = (string) $resourceId;
        }

        uksort(
            $selectedById,
            fn (string $left, string $right): int => ($order['library:'.$left] ?? PHP_INT_MAX)
                <=> ($order['library:'.$right] ?? PHP_INT_MAX)
        );
        $this->selectedLibraryResourceIds = array_values($selectedById);
        $this->refreshSelectedLibraryResources();
    }

    private function splitDraftKey(string $draftKey): array
    {
        $parts = explode(':', $draftKey, 2);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    private function findPendingFileByKey(string $rawKey): mixed
    {
        foreach ($this->finalFiles as $index => $upload) {
            if ($this->pendingFileKey($upload, $index) === $rawKey) {
                return $upload;
            }
        }

        return null;
    }

    private function findLinkByKey(string $rawKey): ?array
    {
        $index = $this->findLinkIndexByKey($rawKey);

        return $index === null ? null : $this->links[$index];
    }

    private function findYoutubeByKey(string $rawKey): ?array
    {
        $index = $this->findYoutubeIndexByKey($rawKey);

        return $index === null ? null : $this->youtubes[$index];
    }

    private function findLinkIndexByKey(string $rawKey): ?int
    {
        foreach ($this->links as $index => $link) {
            if ($this->linkKey($link, $index) === $rawKey) {
                return $index;
            }
        }

        return null;
    }

    private function findYoutubeIndexByKey(string $rawKey): ?int
    {
        foreach ($this->youtubes as $index => $youtube) {
            if ($this->youtubeKey($youtube, $index) === $rawKey) {
                return $index;
            }
        }

        return null;
    }

    private function linkKey(array $link, ?int $index = null): string
    {
        return (string) ($link['key'] ?? 'link-'.$index);
    }

    private function youtubeKey(array $youtube, ?int $index = null): string
    {
        return (string) ($youtube['key'] ?? 'youtube-'.$index);
    }

    private function newDraftItemKey(string $prefix): string
    {
        return $prefix.'-'.str_replace('.', '', uniqid('', true));
    }

    private function draftItemMeta(string $kind, string $type): string
    {
        if ($kind === 'existing') {
            return match ($type) {
                'youtube' => 'Saved YouTube link',
                'link' => 'Saved link',
                default => 'Saved file',
            };
        }

        return match ($type) {
            'youtube' => 'YouTube',
            'link' => 'Link',
            default => 'File',
        };
    }

    private function decorateDraftItem(array $item): array
    {
        $type = (string) ($item['type'] ?? 'file');

        $item['icon'] = match ($type) {
            'youtube' => 'tabler-brand-youtube',
            'link' => 'tabler-link',
            default => 'tabler-file-description',
        };
        $item['iconClass'] = match ($type) {
            'youtube' => 'w14-attachment-icon--youtube',
            'link' => 'w14-attachment-icon--link',
            default => (($item['kind'] ?? '') === 'pending_file' ? 'w14-attachment-icon--pending' : 'w14-attachment-icon--file'),
        };

        return $item;
    }

    private function refreshSelectedLibraryResources(): void
    {
        $subjectId = $this->templateId === null
            ? 0
            : (int) MainDailySessionTemplate::query()
                ->whereKey($this->templateId)
                ->where('created_by_user_id', Auth::id())
                ->value('subject_id');

        if ($this->selectedLibraryResourceIds === [] || $subjectId <= 0) {
            $this->selectedLibraryResourceIds = [];
            $this->selectedLibraryResources = [];
            $this->syncAttachmentDraftOrder();

            return;
        }

        $requestedIds = $this->selectedLibraryResourceIds;
        $libraryResourceIds = collect($requestedIds)
            ->filter(fn ($id): bool => is_numeric($id))
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $legacyResourceIds = collect($requestedIds)
            ->filter(fn ($id): bool => is_string($id) && str_starts_with($id, 'series__'))
            ->unique()
            ->values()
            ->all();
        $activeSectionIds = app(LibraryResourceQuery::class)->activeSectionIdsForOwner((int) Auth::id(), $subjectId);

        if ($activeSectionIds === [] && $legacyResourceIds === []) {
            $this->selectedLibraryResourceIds = [];
            $this->selectedLibraryResources = [];
            $this->syncAttachmentDraftOrder();

            return;
        }

        $libraryResources = $libraryResourceIds === [] || $activeSectionIds === []
            ? collect()
            : LibraryResource::query()
                ->whereIn('id', $libraryResourceIds)
                ->where('owner_user_id', Auth::id())
                ->where('subject_id', $subjectId)
                ->whereIn('library_section_id', $activeSectionIds)
                ->where('status', LibraryResource::STATUS_ACTIVE)
                ->get();

        $selectedResources = $libraryResources
            ->map(fn (LibraryResource $resource): array => [
                'id' => (string) $resource->id,
                'title' => (string) $resource->title,
                'type' => (string) $resource->resource_type,
                'detail' => $resource->isFile()
                    ? (string) ($resource->original_filename ?: basename((string) $resource->file_path))
                    : (string) parse_url((string) $resource->external_url, PHP_URL_HOST),
            ])
            ->all();

        $legacyResources = app(LegacyLibraryTaskResourceCatalog::class)
            ->findManyForSubject(Auth::user(), $subjectId, $legacyResourceIds);

        foreach ($legacyResources as $resource) {
            $isVocabularyGame = ($resource['source_type'] ?? '') === SeriesLibrarySourceResolver::SOURCE_VOCABULARY_LIST;

            $selectedResources[] = [
                'id' => $resource['id'],
                'title' => $resource['title'],
                'type' => 'link',
                'detail' => $isVocabularyGame ? 'Vocab Games lesson' : 'Library source',
            ];
        }

        $order = array_flip(array_map('strval', $requestedIds));
        usort(
            $selectedResources,
            fn (array $left, array $right): int => ($order[(string) $left['id']] ?? PHP_INT_MAX) <=> ($order[(string) $right['id']] ?? PHP_INT_MAX)
        );

        $this->selectedLibraryResourceIds = collect($selectedResources)
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->all();
        $this->selectedLibraryResources = $selectedResources;
        $this->syncAttachmentDraftOrder();
    }

    private function appendUniqueFinalUploads(array $uploads): void
    {
        $existingKeys = [];

        foreach ($this->finalFiles as $existingUpload) {
            $existingKey = $this->temporaryUploadKey($existingUpload);

            if ($existingKey !== null) {
                $existingKeys[$existingKey] = true;
            }
        }

        foreach ($uploads as $upload) {
            $uploadKey = $this->temporaryUploadKey($upload);

            if ($uploadKey !== null && isset($existingKeys[$uploadKey])) {
                continue;
            }

            if ($uploadKey !== null) {
                $existingKeys[$uploadKey] = true;
            }

            $this->finalFiles[] = $upload;
        }

        $this->finalFiles = array_values($this->finalFiles);
    }

    private function pendingFileOrderIndex(mixed $upload, array $order): int
    {
        $candidateKeys = [$this->pendingFileKey($upload)];

        if (is_object($upload) && method_exists($upload, 'getClientOriginalName')) {
            $candidateKeys[] = (string) $upload->getClientOriginalName();
        }

        $uploadKey = $this->temporaryUploadKey($upload);
        if ($uploadKey !== null) {
            $candidateKeys[] = $uploadKey;
        }

        foreach ($candidateKeys as $key) {
            if (isset($order[(string) $key])) {
                return (int) $order[(string) $key];
            }
        }

        return PHP_INT_MAX;
    }

    private function temporaryUploadKey(mixed $upload): ?string
    {
        if (is_object($upload) && method_exists($upload, 'getFilename')) {
            return (string) $upload->getFilename();
        }

        return null;
    }

    private function cleanNullableText(?string $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
