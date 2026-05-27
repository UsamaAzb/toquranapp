<?php

namespace App\Livewire\Teacher;

use App\Models\AttachmentFile;
use App\Models\LibraryResource;
use App\Models\LibrarySection;
use App\Models\Subject;
use App\Models\TeacherSubjectClass;
use App\Services\Library\LibraryFileRetentionService;
use App\Services\Library\LibraryResourceAccessService;
use App\Services\Library\LibraryResourceQuery;
use App\Services\Library\LibraryResourceValidator;
use App\Services\Library\LibrarySectionValidator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class LibraryManager extends Component
{
    use WithFileUploads;

    protected array $validationAttributes = [
        'resourceFiles' => 'Library files',
        'resourceFiles.*' => 'Library file',
        'quickLinkTitle' => 'link title',
        'quickLinkUrl' => 'link URL',
        'quickYoutubeTitle' => 'YouTube title',
        'quickYoutubeUrl' => 'YouTube URL',
        'editingResourceFile' => 'replacement Library file',
    ];

    protected array $messages = [
        'resourceFiles.*.max' => 'Each Library file must be 500 MB or smaller.',
        'editingResourceFile.max' => 'Library files must be 500 MB or smaller.',
        'resourceFiles.*.uploaded' => 'The selected files did not finish uploading, so none were kept. Select them again and try once more.',
        'editingResourceFile.uploaded' => 'The replacement file did not finish uploading. Select it again and try once more.',
    ];

    public ?int $selectedSubjectId = null;

    public ?int $currentSectionId = null;

    public bool $quickAdd = false;

    public string $quickAddMode = 'all';

    public bool $showArchived = false;

    public string $sectionTitle = '';

    public string $sectionDescription = '';

    public ?int $editingSectionId = null;

    public string $editingSectionTitle = '';

    public string $editingSectionDescription = '';

    public ?int $resourceSectionId = null;

    public string $resourceFolderSearch = '';

    public bool $resourceFolderDropdownOpen = false;

    public string $resourceKind = LibraryResource::TYPE_FILE;

    public string $resourceTitle = '';

    public string $resourceDescription = '';

    public string $externalUrl = '';

    public array $resourceFiles = [];

    public array $quickLinks = [];

    public array $quickYoutubes = [];

    public string $quickLinkTitle = '';

    public string $quickLinkUrl = '';

    public string $quickYoutubeTitle = '';

    public string $quickYoutubeUrl = '';

    public ?int $editingResourceId = null;

    public string $editingResourceKind = LibraryResource::TYPE_FILE;

    public string $editingResourceTitle = '';

    public string $editingResourceDescription = '';

    public string $editingExternalUrl = '';

    public mixed $editingResourceFile = null;

    /** @var array<int, array{id: int, title: string}> */
    public array $subjects = [];

    public function mount(?int $initialSectionId = null, bool $quickAdd = false, string $quickAddMode = 'all'): void
    {
        $this->quickAdd = $quickAdd;
        $this->quickAddMode = in_array($quickAddMode, ['all', 'resources', 'section'], true)
            ? $quickAddMode
            : 'all';
        $this->subjects = $this->teacherSubjects();
        $this->selectedSubjectId = $this->subjects[0]['id'] ?? null;

        if ($initialSectionId !== null) {
            $this->openInitialSection($initialSectionId);
        }
    }

    public function updatedSelectedSubjectId(): void
    {
        if ($this->selectedSubjectId !== null) {
            app(LibraryResourceAccessService::class)->authorizeSubject(Auth::user(), (int) $this->selectedSubjectId);
        }

        $this->currentSectionId = null;
        $this->resourceSectionId = null;
        $this->resourceFolderSearch = '';
        $this->resourceFolderDropdownOpen = false;
        $this->resetValidation();
    }

    public function updatedResourceFolderSearch(): void
    {
        $this->resourceFolderDropdownOpen = true;
    }

    public function toggleResourceFolderDropdown(): void
    {
        $this->resourceFolderDropdownOpen = ! $this->resourceFolderDropdownOpen;
    }

    public function chooseResourceSection(?int $sectionId = null): void
    {
        $this->resourceSectionId = $sectionId;
        $this->resourceFolderDropdownOpen = false;
    }

    public function enterSection(int $sectionId): void
    {
        $section = $this->resolveOwnedSection($sectionId);
        abort_unless($section->isActive(), 404);

        $this->currentSectionId = (int) $section->id;
        $this->resourceSectionId = (int) $section->id;
    }

    public function goToParent(): void
    {
        if ($this->currentSectionId === null) {
            return;
        }

        $section = $this->resolveOwnedSection($this->currentSectionId);
        $this->currentSectionId = $section->parent_id ? (int) $section->parent_id : null;
        $this->resourceSectionId = $this->currentSectionId;
    }

    public function goToRoot(): void
    {
        $this->currentSectionId = null;
        $this->resourceSectionId = null;
    }

    public function createSection(): void
    {
        if (! $this->requireSubject()) {
            return;
        }
        $payload = $this->validate([
            'sectionTitle' => ['required', 'string', 'max:255'],
            'sectionDescription' => ['nullable', 'string', 'max:300'],
        ]);

        $ownerId = (int) Auth::id();
        $subjectId = (int) $this->selectedSubjectId;
        $parentId = $this->currentSectionId;
        $sectionValidator = app(LibrarySectionValidator::class);
        $sectionValidator->validateParentForWrite($ownerId, $subjectId, $parentId);
        $sectionValidator->validateUniqueActiveSiblingTitle($ownerId, $subjectId, $parentId, $payload['sectionTitle']);

        LibrarySection::create([
            'owner_user_id' => $ownerId,
            'subject_id' => $subjectId,
            'parent_id' => $parentId,
            'title' => trim($payload['sectionTitle']),
            'description' => $this->cleanNullableText($payload['sectionDescription'] ?? null),
            'sort_order' => $this->nextSectionSortOrder($ownerId, $subjectId, $parentId),
            'created_by_user_id' => $ownerId,
        ]);

        $this->reset(['sectionTitle', 'sectionDescription']);
        $this->dispatch('toast', type: 'success', message: 'Library folder created.');
        if ($this->quickAdd) {
            $this->dispatch('library-folder-updated');
        }
    }

    public function editSection(int $sectionId): void
    {
        $section = $this->resolveOwnedSection($sectionId);
        $this->editingSectionId = (int) $section->id;
        $this->editingSectionTitle = (string) $section->title;
        $this->editingSectionDescription = (string) ($section->description ?? '');
        $this->resetValidation();
    }

    public function cancelSectionEdit(): void
    {
        $this->reset(['editingSectionId', 'editingSectionTitle', 'editingSectionDescription']);
    }

    public function saveSection(): void
    {
        if ($this->editingSectionId === null) {
            return;
        }

        $section = $this->resolveOwnedSection($this->editingSectionId);
        $payload = $this->validate([
            'editingSectionTitle' => ['required', 'string', 'max:255'],
            'editingSectionDescription' => ['nullable', 'string', 'max:300'],
        ]);

        app(LibrarySectionValidator::class)->validateUniqueActiveSiblingTitle(
            (int) $section->owner_user_id,
            (int) $section->subject_id,
            $section->parent_id ? (int) $section->parent_id : null,
            $payload['editingSectionTitle'],
            (int) $section->id
        );

        $section->update([
            'title' => trim($payload['editingSectionTitle']),
            'description' => $this->cleanNullableText($payload['editingSectionDescription'] ?? null),
        ]);

        $this->cancelSectionEdit();
        $this->dispatch('toast', type: 'success', message: 'Library folder updated.');
    }

    public function archiveSection(int $sectionId): void
    {
        $section = $this->resolveOwnedSection($sectionId);

        $section->update([
            'status' => LibrarySection::STATUS_ARCHIVED,
            'archived_at' => now(config('app.timezone')),
        ]);

        if ($this->currentSectionId === (int) $section->id) {
            $this->currentSectionId = $section->parent_id ? (int) $section->parent_id : null;
        }

        $this->dispatch('toast', type: 'warning', message: 'Library folder archived. Existing assigned work is unchanged.');
    }

    public function restoreSection(int $sectionId): void
    {
        $section = $this->resolveOwnedSection($sectionId);
        app(LibrarySectionValidator::class)->validateUniqueActiveSiblingTitle(
            (int) $section->owner_user_id,
            (int) $section->subject_id,
            $section->parent_id ? (int) $section->parent_id : null,
            (string) $section->title,
            (int) $section->id
        );

        $section->update([
            'status' => LibrarySection::STATUS_ACTIVE,
            'archived_at' => null,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Library folder restored.');
    }

    public function deleteSection(int $sectionId): void
    {
        $section = $this->resolveOwnedSection($sectionId);

        if ($section->children()->exists() || $section->resources()->exists()) {
            $this->addError('section_delete_'.$section->id, 'Delete or archive everything inside this folder first.');

            return;
        }

        $parentId = $section->parent_id ? (int) $section->parent_id : null;
        $section->delete();

        if ($this->currentSectionId === (int) $section->id) {
            $this->currentSectionId = $parentId;
            $this->resourceSectionId = $parentId;
        }

        $this->dispatch('toast', type: 'success', message: 'Empty Library folder deleted.');
    }

    public function createResource(): void
    {
        if (! $this->requireSubject()) {
            return;
        }
        $payload = $this->validate([
            'resourceSectionId' => ['required', 'integer', Rule::exists('library_sections', 'id')],
            'resourceKind' => ['required', Rule::in([LibraryResource::TYPE_FILE, LibraryResource::TYPE_LINK, 'youtube'])],
            'resourceTitle' => [
                Rule::requiredIf($this->resourceKind !== LibraryResource::TYPE_FILE),
                'nullable',
                'string',
                'max:255',
            ],
            'resourceDescription' => ['nullable', 'string', 'max:300'],
            'externalUrl' => ['nullable', 'string', 'max:2048'],
            'resourceFiles' => ['nullable', 'array'],
            'resourceFiles.*' => ['file', 'max:'.LibraryResourceValidator::MAX_UPLOAD_KB],
        ]);

        $ownerId = (int) Auth::id();
        $subjectId = (int) $this->selectedSubjectId;
        $section = $this->resolveOwnedSection((int) $payload['resourceSectionId']);
        $resourceValidator = app(LibraryResourceValidator::class);
        $resourceValidator->validateSectionForResource($ownerId, $subjectId, $section);

        $baseResourceData = [
            'owner_user_id' => $ownerId,
            'subject_id' => $subjectId,
            'library_section_id' => (int) $section->id,
            'description' => $this->cleanNullableText($payload['resourceDescription'] ?? null),
            'created_by_user_id' => $ownerId,
        ];

        if ($payload['resourceKind'] !== LibraryResource::TYPE_FILE) {
            $resourceValidator->validateUniqueActiveTitle((int) $section->id, $payload['resourceTitle']);
            $resourceValidator->validateLinkUrl($payload['externalUrl'] ?? null);
            LibraryResource::create($baseResourceData + [
                'resource_type' => LibraryResource::TYPE_LINK,
                'title' => trim($payload['resourceTitle']),
                'external_url' => trim((string) $payload['externalUrl']),
                'sort_order' => $this->nextResourceSortOrder((int) $section->id),
            ]);
        } else {
            $files = $this->uploadedResourceFiles();
            if ($files === []) {
                $this->addError('resourceFiles', 'No Library files are ready to save. Select the files again, wait for the upload to finish, then click Add resources.');

                return;
            }

            $titles = $this->resourceTitlesForFiles($files, $payload['resourceTitle'] ?? null);
            if (count($titles) !== count(array_unique($titles))) {
                $this->addError('resourceFiles', 'Selected files must have unique filenames.');

                return;
            }

            foreach ($titles as $title) {
                $resourceValidator->validateUniqueActiveTitle((int) $section->id, $title);
            }

            $nextSortOrder = $this->nextResourceSortOrder((int) $section->id);
            foreach ($files as $index => $file) {
                $resourceValidator->validateFileUpload($file);
                $path = $file->store('library-resources', 'public');
                if ($path === false) {
                    $this->addError('resourceFiles', 'A Library file could not be stored.');

                    return;
                }

                LibraryResource::create($baseResourceData + [
                    'resource_type' => LibraryResource::TYPE_FILE,
                    'title' => $titles[$index],
                    'storage_disk' => 'public',
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'sort_order' => $nextSortOrder++,
                ]);
            }
        }

        $resourceCount = $payload['resourceKind'] === LibraryResource::TYPE_FILE
            ? count($this->uploadedResourceFiles())
            : 1;
        $this->reset(['resourceTitle', 'resourceDescription', 'externalUrl', 'resourceFiles']);
        $this->resourceKind = LibraryResource::TYPE_FILE;
        if ($this->quickAdd && $this->currentSectionId !== null) {
            $this->resourceSectionId = $this->currentSectionId;
        }
        $this->dispatch(
            'toast',
            type: 'success',
            message: $resourceCount === 1 ? 'Library resource added.' : $resourceCount.' Library resources added.'
        );
        $this->dispatch('library-resource-form-reset');
        if ($this->quickAdd) {
            $this->dispatch('library-folder-updated');
        }
    }

    public function removeResourceFileAt(?int $index): void
    {
        if ($index === null || ! array_key_exists($index, $this->resourceFiles)) {
            return;
        }

        unset($this->resourceFiles[$index]);
        $this->resourceFiles = array_values($this->resourceFiles);
        $this->resetErrorBag(['resourceFiles', 'resourceFiles.*', 'file', 'quickAdd']);
    }

    public function addQuickLink(): void
    {
        $this->resetErrorBag(['quickLinkTitle', 'quickLinkUrl', 'quickAdd']);

        $payload = $this->validate([
            'quickLinkTitle' => ['required', 'string', 'max:255'],
            'quickLinkUrl' => ['required', 'url', 'max:2048'],
        ]);

        $this->quickLinks[] = [
            'title' => trim($payload['quickLinkTitle']),
            'url' => trim($payload['quickLinkUrl']),
        ];

        $this->quickLinkTitle = '';
        $this->quickLinkUrl = '';
    }

    public function addQuickYoutube(): void
    {
        $this->resetErrorBag(['quickYoutubeTitle', 'quickYoutubeUrl', 'quickAdd']);

        $payload = $this->validate([
            'quickYoutubeTitle' => ['nullable', 'string', 'max:255'],
            'quickYoutubeUrl' => ['required', 'url', 'max:2048'],
        ]);

        $url = trim($payload['quickYoutubeUrl']);
        $this->quickYoutubes[] = [
            'title' => trim($payload['quickYoutubeTitle']) ?: 'YouTube',
            'url' => $url,
        ];

        $this->quickYoutubeTitle = '';
        $this->quickYoutubeUrl = '';
    }

    public function removeQuickLink(int $index): void
    {
        unset($this->quickLinks[$index]);
        $this->quickLinks = array_values($this->quickLinks);
    }

    public function removeQuickYoutube(int $index): void
    {
        unset($this->quickYoutubes[$index]);
        $this->quickYoutubes = array_values($this->quickYoutubes);
    }

    public function saveQuickAdd(): void
    {
        if (! $this->quickAdd || ! $this->requireSubject() || $this->currentSectionId === null) {
            return;
        }

        if ($this->quickAddAllowsResources() && ! $this->quickAddPendingFieldsAreClean()) {
            return;
        }

        $ownerId = (int) Auth::id();
        $subjectId = (int) $this->selectedSubjectId;
        $section = $this->resolveOwnedSection($this->currentSectionId);
        $resourceValidator = app(LibraryResourceValidator::class);
        $resourceValidator->validateSectionForResource($ownerId, $subjectId, $section);

        $createFolder = $this->quickAddAllowsSection() && trim($this->sectionTitle) !== '';
        $files = $this->quickAddAllowsResources() ? $this->uploadedResourceFiles() : [];
        $links = $this->quickAddAllowsResources() ? collect($this->quickLinks)
            ->map(fn (array $link): array => [
                'title' => trim((string) ($link['title'] ?? '')),
                'url' => trim((string) ($link['url'] ?? '')),
            ])
            ->filter(fn (array $link): bool => $link['title'] !== '' && $link['url'] !== '')
            ->values() : collect();
        $youtubes = $this->quickAddAllowsResources() ? collect($this->quickYoutubes)
            ->map(fn (array $youtube): array => [
                'title' => trim((string) ($youtube['title'] ?? 'YouTube')) ?: 'YouTube',
                'url' => trim((string) ($youtube['url'] ?? '')),
            ])
            ->filter(fn (array $youtube): bool => $youtube['url'] !== '')
            ->values() : collect();

        if ($this->quickAddMode === 'section' && ! $createFolder) {
            $this->addError('sectionTitle', 'Enter a folder title before saving.');

            return;
        }

        if (! $createFolder && $files === [] && $links->isEmpty() && $youtubes->isEmpty()) {
            $this->addError('quickAdd', 'Add a file, link, or YouTube link before saving.');

            return;
        }

        if ($createFolder) {
            $this->validate([
                'sectionTitle' => ['required', 'string', 'max:255'],
                'sectionDescription' => ['nullable', 'string', 'max:300'],
            ]);

            $sectionValidator = app(LibrarySectionValidator::class);
            $sectionValidator->validateParentForWrite($ownerId, $subjectId, $this->currentSectionId);
            $sectionValidator->validateUniqueActiveSiblingTitle($ownerId, $subjectId, $this->currentSectionId, $this->sectionTitle);
        }

        if ($files !== []) {
            $this->validate([
                'resourceTitle' => ['nullable', 'string', 'max:255'],
                'resourceDescription' => ['nullable', 'string', 'max:300'],
                'resourceFiles' => ['nullable', 'array'],
                'resourceFiles.*' => ['file', 'max:'.LibraryResourceValidator::MAX_UPLOAD_KB],
            ]);
        }

        $fileTitles = $files === [] ? [] : $this->resourceTitlesForFiles($files, $this->resourceTitle);
        $resourceTitles = array_merge($fileTitles, $links->pluck('title')->all(), $youtubes->pluck('title')->all());

        if (count($resourceTitles) !== count(array_unique($resourceTitles))) {
            $this->addError('quickAdd', 'Every file, link, and YouTube item needs a unique title.');

            return;
        }

        foreach ($fileTitles as $title) {
            $resourceValidator->validateUniqueActiveTitle((int) $section->id, $title);
        }

        foreach ($links as $link) {
            $resourceValidator->validateUniqueActiveTitle((int) $section->id, $link['title']);
            $resourceValidator->validateLinkUrl($link['url']);
        }

        foreach ($youtubes as $youtube) {
            $resourceValidator->validateUniqueActiveTitle((int) $section->id, $youtube['title']);
            $resourceValidator->validateLinkUrl($youtube['url']);
        }

        if ($createFolder) {
            LibrarySection::create([
                'owner_user_id' => $ownerId,
                'subject_id' => $subjectId,
                'parent_id' => $this->currentSectionId,
                'title' => trim($this->sectionTitle),
                'description' => $this->cleanNullableText($this->sectionDescription),
                'sort_order' => $this->nextSectionSortOrder($ownerId, $subjectId, $this->currentSectionId),
                'created_by_user_id' => $ownerId,
            ]);
        }

        $baseResourceData = [];
        if ($this->quickAddAllowsResources()) {
            $baseResourceData = [
                'owner_user_id' => $ownerId,
                'subject_id' => $subjectId,
                'library_section_id' => (int) $section->id,
                'description' => $this->cleanNullableText($this->resourceDescription),
                'created_by_user_id' => $ownerId,
            ];
        }

        $nextSortOrder = $this->nextResourceSortOrder((int) $section->id);

        foreach ($files as $index => $file) {
            $resourceValidator->validateFileUpload($file);
            $path = $file->store('library-resources', 'public');
            if ($path === false) {
                $this->addError('resourceFiles', 'A Library file could not be stored.');

                return;
            }

            LibraryResource::create($baseResourceData + [
                'resource_type' => LibraryResource::TYPE_FILE,
                'title' => $fileTitles[$index],
                'storage_disk' => 'public',
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'sort_order' => $nextSortOrder++,
            ]);
        }

        foreach ($links as $link) {
            LibraryResource::create($baseResourceData + [
                'resource_type' => LibraryResource::TYPE_LINK,
                'title' => $link['title'],
                'external_url' => $link['url'],
                'sort_order' => $nextSortOrder++,
            ]);
        }

        foreach ($youtubes as $youtube) {
            LibraryResource::create([
                ...$baseResourceData,
                'resource_type' => LibraryResource::TYPE_LINK,
                'title' => $youtube['title'],
                'external_url' => $youtube['url'],
                'sort_order' => $nextSortOrder++,
            ]);
        }

        $createdCount = (int) $createFolder + count($files) + $links->count() + $youtubes->count();
        $this->reset([
            'sectionTitle',
            'sectionDescription',
            'resourceTitle',
            'resourceDescription',
            'resourceFiles',
            'quickLinks',
            'quickYoutubes',
            'quickLinkTitle',
            'quickLinkUrl',
            'quickYoutubeTitle',
            'quickYoutubeUrl',
        ]);
        $this->resetValidation();
        $message = $this->quickAddMode === 'section'
            ? 'Library folder saved.'
            : ($createdCount === 1 ? 'Library source saved.' : $createdCount.' Library sources saved.');

        $this->dispatch('toast', type: 'success', message: $message);
        $this->dispatch('library-resource-form-reset');
        $this->dispatch('library-folder-updated');
    }

    public function editResource(int $resourceId): void
    {
        $resource = $this->resolveOwnedResource($resourceId);

        $this->editingResourceId = (int) $resource->id;
        $this->editingResourceKind = (string) $resource->resource_type;
        $this->editingResourceTitle = (string) $resource->title;
        $this->editingResourceDescription = (string) ($resource->description ?? '');
        $this->editingExternalUrl = (string) ($resource->external_url ?? '');
        $this->editingResourceFile = null;
        $this->resetValidation();
    }

    public function cancelResourceEdit(): void
    {
        $this->reset([
            'editingResourceId',
            'editingResourceTitle',
            'editingResourceDescription',
            'editingExternalUrl',
            'editingResourceFile',
        ]);
        $this->editingResourceKind = LibraryResource::TYPE_FILE;
    }

    public function saveResource(): void
    {
        if ($this->editingResourceId === null) {
            return;
        }

        $resource = $this->resolveOwnedResource($this->editingResourceId);
        $payload = $this->validate([
            'editingResourceTitle' => ['required', 'string', 'max:255'],
            'editingResourceDescription' => ['nullable', 'string', 'max:300'],
            'editingExternalUrl' => ['nullable', 'string', 'max:2048'],
            'editingResourceFile' => ['nullable', 'file', 'max:'.LibraryResourceValidator::MAX_UPLOAD_KB],
        ]);

        $resourceValidator = app(LibraryResourceValidator::class);
        $resourceValidator->validateUniqueActiveTitle(
            (int) $resource->library_section_id,
            $payload['editingResourceTitle'],
            (int) $resource->id
        );

        $updates = [
            'title' => trim($payload['editingResourceTitle']),
            'description' => $this->cleanNullableText($payload['editingResourceDescription'] ?? null),
        ];

        if ($resource->isLink()) {
            $resourceValidator->validateLinkUrl($payload['editingExternalUrl'] ?? null);
            $updates['external_url'] = trim((string) $payload['editingExternalUrl']);
        }

        $oldPath = null;
        $oldDisk = 'public';
        $newPath = null;

        if ($resource->isFile() && $this->editingResourceFile) {
            $resourceValidator->validateFileUpload($this->editingResourceFile);

            $oldPath = $resource->file_path;
            $oldDisk = $resource->storage_disk ?: 'public';
            $newPath = $this->editingResourceFile->store('library-resources', 'public');

            if ($newPath === false) {
                $this->addError('editingResourceFile', 'The replacement Library file could not be stored.');

                return;
            }

            $updates += [
                'storage_disk' => 'public',
                'file_path' => $newPath,
                'original_filename' => $this->editingResourceFile->getClientOriginalName(),
                'mime_type' => $this->editingResourceFile->getMimeType(),
                'file_size' => $this->editingResourceFile->getSize(),
            ];
        }

        try {
            $resource->update($updates);
        } catch (\Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if ($oldPath) {
            app(LibraryFileRetentionService::class)->deleteIfUnreferenced($oldPath, $oldDisk);
        }

        $this->cancelResourceEdit();
        $this->dispatch('toast', type: 'success', message: 'Library resource updated. Existing assigned work is unchanged.');
    }

    public function archiveResource(int $resourceId): void
    {
        $resource = $this->resolveOwnedResource($resourceId);

        $resource->update([
            'status' => LibraryResource::STATUS_ARCHIVED,
            'archived_at' => now(config('app.timezone')),
        ]);

        if ($this->editingResourceId === (int) $resource->id) {
            $this->cancelResourceEdit();
        }

        $this->dispatch('toast', type: 'warning', message: 'Library resource archived. Existing assigned work is unchanged.');
    }

    public function restoreResource(int $resourceId): void
    {
        $resource = $this->resolveOwnedResource($resourceId);
        app(LibraryResourceValidator::class)->validateUniqueActiveTitle(
            (int) $resource->library_section_id,
            (string) $resource->title,
            (int) $resource->id
        );

        $resource->update([
            'status' => LibraryResource::STATUS_ACTIVE,
            'archived_at' => null,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Library resource restored.');
    }

    public function deleteResource(int $resourceId): void
    {
        $resource = $this->resolveOwnedResource($resourceId);

        if ($this->resourceHasAttachmentSnapshot($resource)) {
            $this->addError('resource_delete_'.$resource->id, 'This resource is already used in a task. Archive it instead so existing work stays safe.');

            return;
        }

        $path = $resource->isFile() ? $resource->file_path : null;
        $disk = $resource->storage_disk ?: 'public';
        $resource->delete();

        if ($path) {
            app(LibraryFileRetentionService::class)->deleteIfUnreferenced($path, $disk);
        }

        if ($this->editingResourceId === (int) $resource->id) {
            $this->cancelResourceEdit();
        }

        $this->dispatch('toast', type: 'success', message: 'Unused Library resource deleted.');
    }

    public function render(): View
    {
        $subjectId = $this->selectedSubjectId;
        $sections = collect();
        $resources = collect();
        $breadcrumbs = [];
        $currentSection = null;

        if ($subjectId !== null) {
            $query = app(LibraryResourceQuery::class);
            $sections = $query
                ->sections(Auth::user(), (int) $subjectId, $this->currentSectionId, ! $this->showArchived)
                ->get();
            $resources = $this->currentSectionId === null
                ? collect()
                : $query
                    ->resources(Auth::user(), (int) $subjectId, $this->currentSectionId, null, ! $this->showArchived)
                    ->get();
            $currentSection = $this->currentSectionId ? $this->resolveOwnedSection($this->currentSectionId) : null;
            $breadcrumbs = $this->breadcrumbs($currentSection);
        }

        return view('livewire.teacher.library-manager', [
            'sections' => $sections,
            'resources' => $resources,
            'breadcrumbs' => $breadcrumbs,
            'currentSection' => $currentSection,
            'uploadLimit' => $this->uploadLimit(),
            'resourceFolderOptions' => $this->resourceFolderOptions(),
            'selectedResourceFolderLabel' => $this->selectedResourceFolderLabel(),
            'quickAdd' => $this->quickAdd,
            'quickAddAllowsResources' => $this->quickAddAllowsResources(),
            'quickAddAllowsSection' => $this->quickAddAllowsSection(),
            'allowedFileExtensions' => LibraryResourceValidator::allowedExtensions(),
            'fileAcceptAttribute' => LibraryResourceValidator::acceptAttribute(),
        ]);
    }

    private function requireSubject(): bool
    {
        if ($this->selectedSubjectId === null) {
            $this->addError('selectedSubjectId', 'Choose a subject first.');

            return false;
        }

        app(LibraryResourceAccessService::class)->authorizeSubject(Auth::user(), (int) $this->selectedSubjectId);

        return true;
    }

    private function resolveOwnedSection(int $sectionId): LibrarySection
    {
        $section = LibrarySection::query()->findOrFail($sectionId);
        app(LibraryResourceAccessService::class)->authorizeSection(Auth::user(), $section);

        return $section;
    }

    private function resolveOwnedResource(int $resourceId): LibraryResource
    {
        $resource = LibraryResource::query()->findOrFail($resourceId);
        app(LibraryResourceAccessService::class)->authorizeResource(Auth::user(), $resource);

        return $resource;
    }

    private function teacherSubjects(): array
    {
        $subjectIds = TeacherSubjectClass::query()
            ->availableForTeacher()
            ->where('user_teacher_coteacher_id', Auth::id())
            ->orderBy('subject_name')
            ->pluck('subject_id')
            ->filter()
            ->unique()
            ->values();

        return Subject::query()
            ->whereIn('id', $subjectIds)
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Subject $subject): array => [
                'id' => (int) $subject->id,
                'title' => (string) $subject->title,
            ])
            ->all();
    }

    private function openInitialSection(int $sectionId): void
    {
        $section = LibrarySection::query()->find($sectionId);

        if (! $section || ! $section->isActive()) {
            return;
        }

        $access = app(LibraryResourceAccessService::class);

        if (! $access->canManageSection(Auth::user(), $section)) {
            return;
        }

        $this->selectedSubjectId = (int) $section->subject_id;
        $this->currentSectionId = (int) $section->id;
        $this->resourceSectionId = (int) $section->id;
    }

    private function breadcrumbs(?LibrarySection $section): array
    {
        $items = [];
        $current = $section;

        while ($current) {
            array_unshift($items, [
                'id' => (int) $current->id,
                'title' => (string) $current->title,
            ]);

            $current = $current->parent_id
                ? LibrarySection::query()->find($current->parent_id)
                : null;
        }

        return $items;
    }

    private function cleanNullableText(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function quickAddAllowsResources(): bool
    {
        return in_array($this->quickAddMode, ['all', 'resources'], true);
    }

    private function quickAddAllowsSection(): bool
    {
        return in_array($this->quickAddMode, ['all', 'section'], true);
    }

    private function quickAddPendingFieldsAreClean(): bool
    {
        $linkTitle = trim($this->quickLinkTitle);
        $linkUrl = trim($this->quickLinkUrl);
        if ($linkTitle !== '' || $linkUrl !== '') {
            $payload = $this->validate([
                'quickLinkTitle' => ['required', 'string', 'max:255'],
                'quickLinkUrl' => ['required', 'url', 'max:2048'],
            ]);

            $this->quickLinks[] = [
                'title' => trim($payload['quickLinkTitle']),
                'url' => trim($payload['quickLinkUrl']),
            ];
            $this->quickLinkTitle = '';
            $this->quickLinkUrl = '';
        }

        $youtubeTitle = trim($this->quickYoutubeTitle);
        $youtubeUrl = trim($this->quickYoutubeUrl);
        if ($youtubeTitle !== '' || $youtubeUrl !== '') {
            $payload = $this->validate([
                'quickYoutubeTitle' => ['nullable', 'string', 'max:255'],
                'quickYoutubeUrl' => ['required', 'url', 'max:2048'],
            ]);

            $url = trim($payload['quickYoutubeUrl']);
            $this->quickYoutubes[] = [
                'title' => trim($payload['quickYoutubeTitle']) ?: 'YouTube',
                'url' => $url,
            ];
            $this->quickYoutubeTitle = '';
            $this->quickYoutubeUrl = '';
        }

        return true;
    }

    private function uploadedResourceFiles(): array
    {
        return collect($this->resourceFiles)
            ->filter()
            ->values()
            ->all();
    }

    private function nextSectionSortOrder(int $ownerId, int $subjectId, ?int $parentId): int
    {
        $currentMax = LibrarySection::query()
            ->ownedBy($ownerId)
            ->forSubject($subjectId)
            ->where(function ($query) use ($parentId): void {
                $parentId === null
                    ? $query->whereNull('parent_id')
                    : $query->where('parent_id', $parentId);
            })
            ->max('sort_order');

        return ((int) $currentMax) + 1;
    }

    private function nextResourceSortOrder(int $sectionId): int
    {
        $currentMax = LibraryResource::query()
            ->where('library_section_id', $sectionId)
            ->max('sort_order');

        return ((int) $currentMax) + 1;
    }

    private function resourceFolderOptions(): array
    {
        if ($this->selectedSubjectId === null) {
            return [];
        }

        $search = mb_strtolower(trim($this->resourceFolderSearch));

        return LibrarySection::query()
            ->ownedBy((int) Auth::id())
            ->forSubject((int) $this->selectedSubjectId)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->orderBy('id')
            ->get()
            ->map(function (LibrarySection $section): array {
                $breadcrumbs = collect($this->breadcrumbs($section))
                    ->pluck('title')
                    ->implode(' / ');

                return [
                    'id' => (int) $section->id,
                    'title' => (string) $section->title,
                    'label' => $breadcrumbs,
                ];
            })
            ->filter(function (array $option) use ($search): bool {
                if ($search === '') {
                    return true;
                }

                return str_contains(mb_strtolower($option['label']), $search);
            })
            ->take(50)
            ->values()
            ->all();
    }

    private function selectedResourceFolderLabel(): string
    {
        if ($this->resourceSectionId === null) {
            return 'Choose folder';
        }

        $section = LibrarySection::query()
            ->ownedBy((int) Auth::id())
            ->forSubject((int) $this->selectedSubjectId)
            ->whereKey($this->resourceSectionId)
            ->first();

        if (! $section) {
            return 'Choose folder';
        }

        return collect($this->breadcrumbs($section))
            ->pluck('title')
            ->implode(' / ');
    }

    private function resourceTitlesForFiles(array $files, mixed $typedTitle): array
    {
        $typedTitle = $this->cleanNullableText($typedTitle);

        return collect($files)
            ->map(function ($file) use ($files, $typedTitle): string {
                if (count($files) === 1 && $typedTitle !== null) {
                    return $typedTitle;
                }

                $filename = trim((string) $file->getClientOriginalName());
                $nameWithoutExtension = trim((string) pathinfo($filename, PATHINFO_FILENAME));
                $readableName = Str::headline(str_replace(['_', '-'], ' ', $nameWithoutExtension));

                return Str::limit($readableName !== '' ? $readableName : 'Library File', 255, '');
            })
            ->values()
            ->all();
    }

    private function resourceHasAttachmentSnapshot(LibraryResource $resource): bool
    {
        if (! $resource->isFile()) {
            return false;
        }

        if (! filled($resource->file_path)) {
            return false;
        }

        return AttachmentFile::query()
            ->where('path', $resource->file_path)
            ->exists();
    }

    private function uploadLimit(): array
    {
        $appBytes = LibraryResourceValidator::MAX_UPLOAD_KB * 1024;
        $phpFileBytes = $this->iniBytes((string) ini_get('upload_max_filesize'));
        $phpPostBytes = $this->iniBytes((string) ini_get('post_max_size'));
        $effectiveBytes = min($appBytes, $phpFileBytes, $phpPostBytes);

        return [
            'appBytes' => $appBytes,
            'fileBytes' => $effectiveBytes,
            'batchBytes' => min($appBytes, $phpPostBytes),
            'label' => $this->formatBytes($effectiveBytes),
            'appLabel' => $this->formatBytes($appBytes),
            'serverIsLower' => $effectiveBytes < $appBytes,
        ];
    }

    private function iniBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return match ($unit) {
            'g' => (int) ($number * 1024 * 1024 * 1024),
            'm' => (int) ($number * 1024 * 1024),
            'k' => (int) ($number * 1024),
            default => (int) $number,
        };
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024 * 1024) {
            return rtrim(rtrim(number_format($bytes / 1024 / 1024 / 1024, 1), '0'), '.').' GB';
        }

        if ($bytes >= 1024 * 1024) {
            return rtrim(rtrim(number_format($bytes / 1024 / 1024, 1), '0'), '.').' MB';
        }

        return max(1, (int) ceil($bytes / 1024)).' KB';
    }
}
