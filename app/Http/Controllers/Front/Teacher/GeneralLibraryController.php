<?php

namespace App\Http\Controllers\Front\Teacher;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\AttachmentFile;
use App\Models\GeneralLibraryFolder;
use App\Models\GeneralLibraryResource;
use App\Services\Library\GeneralLibraryAccessService;
use App\Services\Library\LibraryResourceValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class GeneralLibraryController extends Controller
{
    private const RESOURCE_DISK = 'local';

    private const LEGACY_PUBLIC_DISK = 'public';

    public function index(Request $request): View
    {
        $user = Auth::user();
        app(GeneralLibraryAccessService::class)->authorizeView($user);
        $this->assertTablesReady();
        $libraryRouteName = $this->libraryRouteName($request);

        $folder = $request->integer('folder')
            ? GeneralLibraryFolder::query()->findOrFail($request->integer('folder'))
            : null;

        abort_if($folder && ! app(GeneralLibraryAccessService::class)->canUseFolder($user, $folder), 404);

        $showArchived = $request->boolean('show_archived') && $user?->hasAnyRole(['admin', 'super_admin']);
        $parentId = $folder?->id;

        $folders = GeneralLibraryFolder::query()
            ->withCount([
                'children as active_children_count' => fn ($query) => $query->active(),
                'resources as active_resources_count' => fn ($query) => $query->active(),
            ])
            ->where(function ($query) use ($parentId): void {
                $parentId === null
                    ? $query->whereNull('parent_id')
                    : $query->where('parent_id', $parentId);
            })
            ->when(! $showArchived, fn ($query) => $query->active())
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $resources = GeneralLibraryResource::query()
            ->where(function ($query) use ($parentId): void {
                $parentId === null
                    ? $query->whereNull('general_library_folder_id')
                    : $query->where('general_library_folder_id', $parentId);
            })
            ->when(! $showArchived, fn ($query) => $query->active())
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $activeChildCount = $folder
            ? GeneralLibraryFolder::query()
                ->where('parent_id', (int) $folder->id)
                ->active()
                ->count()
            : 0;
        $activeResourceCount = $folder
            ? GeneralLibraryResource::query()
                ->where('general_library_folder_id', (int) $folder->id)
                ->active()
                ->count()
            : 0;

        return view('teacher.general-library.index', [
            'folder' => $folder,
            'folders' => $folders,
            'resources' => $resources,
            'breadcrumbs' => $this->breadcrumbs($folder, $libraryRouteName),
            'breadcrumb_links' => $this->breadcrumbLinks($folder, $libraryRouteName),
            'libraryRouteName' => $libraryRouteName,
            'canCreateSubfolderHere' => ! $folder || (! $folder->isSourcesOnly() && $activeResourceCount === 0),
            'canCreateSourceHere' => $folder && $activeChildCount === 0,
            'canReorderPageHere' => ! $showArchived
                && ($folders->count() + $resources->count()) > 1
                && $folders->every(fn (GeneralLibraryFolder $folder): bool => app(GeneralLibraryAccessService::class)->canManageFolder($user, $folder))
                && $resources->every(fn (GeneralLibraryResource $resource): bool => app(GeneralLibraryAccessService::class)->canManageResource($user, $resource)),
            'showArchived' => $showArchived,
        ]);
    }

    public function storeFolder(Request $request): RedirectResponse
    {
        $user = Auth::user();
        app(GeneralLibraryAccessService::class)->authorizeView($user);
        $payload = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:general_library_folders,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'content_mode' => ['nullable', 'in:mixed,sources_only'],
        ]);

        $parentId = $payload['parent_id'] ?? null;
        if ($parentId) {
            $parent = GeneralLibraryFolder::query()->findOrFail((int) $parentId);
            abort_unless(app(GeneralLibraryAccessService::class)->canUseFolder($user, $parent), 404);
            $this->ensureFolderAcceptsSubfolders($parent);
        }

        $title = trim($payload['title']);
        $duplicateExists = GeneralLibraryFolder::query()
            ->active()
            ->where(function ($query) use ($parentId): void {
                $parentId
                    ? $query->where('parent_id', (int) $parentId)
                    : $query->whereNull('parent_id');
            })
            ->whereRaw('LOWER(title) = ?', [Str::lower($title)])
            ->exists();

        if ($duplicateExists) {
            return back()->withErrors(['library_action' => 'A folder with this title already exists here.']);
        }

        GeneralLibraryFolder::query()->create([
            'parent_id' => $parentId ? (int) $parentId : null,
            'title' => $title,
            'description' => $this->cleanNullableText($payload['description'] ?? null),
            'content_mode' => $payload['content_mode'] ?? GeneralLibraryFolder::CONTENT_MODE_MIXED,
            'sort_order' => $this->nextFolderSortOrder($parentId ? (int) $parentId : null),
            'created_by_user_id' => (int) $user->id,
            'updated_by_user_id' => (int) $user->id,
        ]);

        return back()->with('success', 'Library folder added.');
    }

    public function updateFolder(Request $request, GeneralLibraryFolder $folder): RedirectResponse
    {
        $user = Auth::user();
        app(GeneralLibraryAccessService::class)->authorizeManageFolder($user, $folder);
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'content_mode' => ['nullable', 'in:mixed,sources_only'],
        ]);

        $title = trim($payload['title']);
        $parentId = $folder->parent_id ? (int) $folder->parent_id : null;
        $duplicateExists = GeneralLibraryFolder::query()
            ->active()
            ->whereKeyNot($folder->getKey())
            ->where(function ($query) use ($parentId): void {
                $parentId
                    ? $query->where('parent_id', $parentId)
                    : $query->whereNull('parent_id');
            })
            ->whereRaw('LOWER(title) = ?', [Str::lower($title)])
            ->exists();

        if ($duplicateExists) {
            return back()->withErrors(['library_action' => 'A folder with this title already exists here.']);
        }

        $contentMode = $payload['content_mode'] ?? GeneralLibraryFolder::CONTENT_MODE_MIXED;
        if ($contentMode === GeneralLibraryFolder::CONTENT_MODE_MIXED
            && $folder->resources()->active()->exists()) {
            return back()->withErrors(['library_action' => 'This folder already contains sources, so it must remain sources-only.']);
        }
        if ($contentMode === GeneralLibraryFolder::CONTENT_MODE_SOURCES_ONLY
            && $folder->children()->active()->exists()) {
            return back()->withErrors(['library_action' => 'This folder already contains subfolders, so it cannot become a final source folder.']);
        }

        $folder->update([
            'title' => $title,
            'description' => $this->cleanNullableText($payload['description'] ?? null),
            'content_mode' => $contentMode,
            'updated_by_user_id' => (int) $user->id,
        ]);

        return back()->with('success', 'Library folder updated.');
    }

    public function archiveFolder(GeneralLibraryFolder $folder): RedirectResponse
    {
        $user = Auth::user();
        app(GeneralLibraryAccessService::class)->authorizeManageFolder($user, $folder);
        $folder->update([
            'status' => GeneralLibraryFolder::STATUS_ARCHIVED,
            'archived_at' => now(config('app.timezone')),
            'updated_by_user_id' => (int) $user->id,
        ]);

        return back()->with('success', 'Library folder archived.');
    }

    public function deleteFolder(GeneralLibraryFolder $folder): RedirectResponse
    {
        $user = Auth::user();
        app(GeneralLibraryAccessService::class)->authorizeManageFolder($user, $folder);

        if ($folder->children()->exists() || $folder->resources()->exists()) {
            $folder->update([
                'status' => GeneralLibraryFolder::STATUS_ARCHIVED,
                'archived_at' => now(config('app.timezone')),
                'updated_by_user_id' => (int) $user->id,
            ]);

            return back()->with('success', 'This folder contains Library items, so it was archived instead of deleted.');
        }

        $folder->delete();

        return back()->with('success', 'Unused Library folder deleted.');
    }

    public function storeResource(Request $request): RedirectResponse
    {
        $user = Auth::user();
        app(GeneralLibraryAccessService::class)->authorizeView($user);
        $payload = $request->validate([
            'folder_id' => ['nullable', 'integer', 'exists:general_library_folders,id'],
            'resource_kind' => ['required', 'in:file,link,youtube,batch'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'external_url' => ['nullable', 'string', 'max:2048'],
            'link_titles' => ['nullable', 'array'],
            'link_titles.*' => ['nullable', 'string', 'max:255'],
            'link_urls' => ['nullable', 'array'],
            'link_urls.*' => ['nullable', 'string', 'max:2048'],
            'youtube_titles' => ['nullable', 'array'],
            'youtube_titles.*' => ['nullable', 'string', 'max:255'],
            'youtube_urls' => ['nullable', 'array'],
            'youtube_urls.*' => ['nullable', 'string', 'max:2048'],
            'uploaded_files' => ['nullable', 'array'],
            'uploaded_files.*' => ['string'],
            'resource_files' => ['nullable', 'array'],
            'resource_files.*' => ['file', 'max:'.LibraryResourceValidator::MAX_UPLOAD_KB],
        ]);

        $folderId = $payload['folder_id'] ?? null;
        if (! $folderId) {
            return back()->withErrors(['library_action' => 'Open or create a final folder before adding sources.']);
        }

        $folder = GeneralLibraryFolder::query()->findOrFail((int) $folderId);
        abort_unless(app(GeneralLibraryAccessService::class)->canUseFolder($user, $folder), 404);
        $this->ensureFolderAcceptsSources($folder);

        if ($payload['resource_kind'] === 'batch') {
            return $this->storeBatchResources($request, $payload, (int) $folderId);
        }

        if ($payload['resource_kind'] === 'file') {
            return $this->storeFileResources($request, $payload, (int) $folderId);
        }

        $title = $this->cleanNullableText($payload['title'] ?? null);
        $url = trim((string) ($payload['external_url'] ?? ''));
        if ($title === null) {
            return back()->withErrors(['library_action' => 'Enter a title for this Library source.']);
        }

        app(LibraryResourceValidator::class)->validateLinkUrl($url);
        if ($payload['resource_kind'] === 'youtube' && ! Helpers::isYoutubeUrl($url)) {
            return back()->withErrors(['library_action' => 'Enter a valid YouTube link.']);
        }

        GeneralLibraryResource::query()->create([
            'general_library_folder_id' => (int) $folderId,
            'resource_type' => $payload['resource_kind'] === 'youtube'
                ? GeneralLibraryResource::TYPE_YOUTUBE
                : GeneralLibraryResource::TYPE_LINK,
            'title' => $title,
            'description' => $this->cleanNullableText($payload['description'] ?? null),
            'external_url' => $url,
            'sort_order' => $this->nextResourceSortOrder($folderId ? (int) $folderId : null),
            'created_by_user_id' => (int) Auth::id(),
            'updated_by_user_id' => (int) Auth::id(),
        ]);

        $this->markFolderSourcesOnly((int) $folderId);

        return back()->with('success', $payload['resource_kind'] === 'youtube' ? 'YouTube source added.' : 'Library source added.');
    }

    public function uploadTemporaryResources(Request $request): JsonResponse
    {
        $user = Auth::user();
        app(GeneralLibraryAccessService::class)->authorizeView($user);

        $request->validate([
            'resource_files' => ['required', 'array', 'min:1'],
        ]);

        $files = collect($request->file('resource_files', []))
            ->filter()
            ->values()
            ->all();

        if ($files === []) {
            throw ValidationException::withMessages([
                'resource_files' => 'Choose one or more Library files to upload.',
            ]);
        }

        $validator = app(LibraryResourceValidator::class);
        $invalid = [];
        $validFiles = [];
        foreach ($files as $file) {
            try {
                $validator->validateFileUpload($file);
            } catch (ValidationException) {
                $invalid[] = [
                    'name' => $file->getClientOriginalName(),
                    'reason' => 'Unsupported file type or larger than '.(LibraryResourceValidator::MAX_UPLOAD_KB / 1024).' MB',
                ];

                continue;
            }

            $validFiles[] = $file;
        }

        $uploaded = [];
        foreach ($validFiles as $file) {
            $extension = strtolower((string) $file->getClientOriginalExtension());
            $filename = (string) Str::uuid().($extension !== '' ? '.'.$extension : '');
            $path = $file->storeAs($this->temporaryUploadDirectory((int) $user->id), $filename, self::RESOURCE_DISK);
            if ($path === false) {
                throw ValidationException::withMessages([
                    'resource_files' => 'A Library file could not be uploaded. Please try again.',
                ]);
            }

            $payload = [
                'user_id' => (int) $user->id,
                'disk' => self::RESOURCE_DISK,
                'path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];

            $uploaded[] = [
                'token' => Crypt::encryptString(json_encode($payload, JSON_THROW_ON_ERROR)),
                'name' => $payload['original_filename'],
                'title' => pathinfo($payload['original_filename'], PATHINFO_FILENAME),
                'size' => $payload['size'],
            ];
        }

        return response()->json([
            'files' => $uploaded,
            'blocked' => $invalid,
        ]);
    }

    public function deleteTemporaryResources(Request $request): JsonResponse
    {
        $user = Auth::user();
        app(GeneralLibraryAccessService::class)->authorizeView($user);

        $payload = $request->validate([
            'uploaded_files' => ['nullable', 'array'],
            'uploaded_files.*' => ['string'],
        ]);

        foreach ($payload['uploaded_files'] ?? [] as $token) {
            try {
                $staged = $this->stagedUploadFromToken((string) $token);
            } catch (ValidationException) {
                continue;
            }

            Storage::disk((string) $staged['disk'])->delete((string) $staged['path']);
        }

        return response()->json(['ok' => true]);
    }

    public function updateResource(Request $request, GeneralLibraryResource $resource): RedirectResponse
    {
        $user = Auth::user();
        app(GeneralLibraryAccessService::class)->authorizeManageResource($user, $resource);
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'external_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $updates = [
            'title' => trim($payload['title']),
            'description' => $this->cleanNullableText($payload['description'] ?? null),
            'updated_by_user_id' => (int) $user->id,
        ];

        if (! $resource->isFile()) {
            $url = trim((string) ($payload['external_url'] ?? ''));
            app(LibraryResourceValidator::class)->validateLinkUrl($url);
            if ($resource->isYoutube() && ! Helpers::isYoutubeUrl($url)) {
                return back()->withErrors(['library_action' => 'Enter a valid YouTube link.']);
            }

            $updates['external_url'] = $url;
        }

        $resource->update($updates);

        return back()->with('success', 'Library source updated.');
    }

    public function archiveResource(GeneralLibraryResource $resource): RedirectResponse
    {
        $user = Auth::user();
        app(GeneralLibraryAccessService::class)->authorizeManageResource($user, $resource);
        $resource->update([
            'status' => GeneralLibraryResource::STATUS_ARCHIVED,
            'archived_at' => now(config('app.timezone')),
            'updated_by_user_id' => (int) $user->id,
        ]);

        return back()->with('success', 'Library source archived.');
    }

    public function deleteResource(GeneralLibraryResource $resource): RedirectResponse
    {
        $user = Auth::user();
        app(GeneralLibraryAccessService::class)->authorizeManageResource($user, $resource);

        if ($this->resourceHasAttachmentSnapshot($resource)) {
            $resource->update([
                'status' => GeneralLibraryResource::STATUS_ARCHIVED,
                'archived_at' => now(config('app.timezone')),
                'updated_by_user_id' => (int) $user->id,
            ]);

            return back()->with('success', 'This source was already assigned, so it was archived. Student task snapshots stay available.');
        }

        $path = $resource->isFile() ? ltrim((string) $resource->file_path, '/') : null;
        $disk = $resource->storage_disk ?: self::RESOURCE_DISK;
        $resourceId = (int) $resource->id;
        $resource->delete();

        if ($path && $this->filePathCanBeDeleted($path, $resourceId) && $this->isAllowedResourceDisk($disk)) {
            Storage::disk($disk)->delete($path);
        }

        return back()->with('success', 'Unused Library source deleted.');
    }

    public function reorderPageItems(Request $request): JsonResponse
    {
        $user = Auth::user();
        app(GeneralLibraryAccessService::class)->authorizeView($user);

        $payload = $request->validate([
            'folder_id' => ['nullable', 'integer', 'exists:general_library_folders,id'],
            'items' => ['required', 'array', 'min:2'],
            'items.*.type' => ['required', 'string', 'in:folder,resource'],
            'items.*.id' => ['required', 'integer'],
        ]);

        $folderId = isset($payload['folder_id']) ? (int) $payload['folder_id'] : null;
        $items = collect($payload['items'])
            ->map(fn (array $item): array => [
                'type' => (string) $item['type'],
                'id' => (int) $item['id'],
            ])
            ->filter(fn (array $item): bool => $item['id'] > 0)
            ->values();

        if ($items->count() !== count($payload['items'])
            || $items->map(fn (array $item): string => $item['type'].':'.$item['id'])->unique()->count() !== $items->count()) {
            throw ValidationException::withMessages([
                'items' => 'The Library order could not be saved.',
            ]);
        }

        $folderIds = $items->where('type', 'folder')->pluck('id')->values();
        $resourceIds = $items->where('type', 'resource')->pluck('id')->values();

        $folders = GeneralLibraryFolder::query()
            ->whereIn('id', $folderIds->all())
            ->where(function ($query) use ($folderId): void {
                $folderId === null
                    ? $query->whereNull('parent_id')
                    : $query->where('parent_id', $folderId);
            })
            ->get()
            ->keyBy('id');

        $resources = GeneralLibraryResource::query()
            ->whereIn('id', $resourceIds->all())
            ->where(function ($query) use ($folderId): void {
                $folderId === null
                    ? $query->whereNull('general_library_folder_id')
                    : $query->where('general_library_folder_id', $folderId);
            })
            ->get()
            ->keyBy('id');

        if ($folders->count() !== $folderIds->count() || $resources->count() !== $resourceIds->count()) {
            throw ValidationException::withMessages([
                'items' => 'The Library order could not be saved.',
            ]);
        }

        foreach ($folders as $folder) {
            app(GeneralLibraryAccessService::class)->authorizeManageFolder($user, $folder);
        }

        foreach ($resources as $resource) {
            app(GeneralLibraryAccessService::class)->authorizeManageResource($user, $resource);
        }

        foreach ($items as $index => $item) {
            $attributes = [
                'sort_order' => ($index + 1) * 10,
                'updated_by_user_id' => (int) $user->id,
            ];

            if ($item['type'] === 'folder') {
                GeneralLibraryFolder::query()
                    ->whereKey((int) $item['id'])
                    ->update($attributes);

                continue;
            }

            GeneralLibraryResource::query()
                ->whereKey((int) $item['id'])
                ->update($attributes);
        }

        return response()->json(['ok' => true]);
    }

    public function openResource(GeneralLibraryResource $resource): RedirectResponse|View
    {
        $user = Auth::user();
        abort_unless(app(GeneralLibraryAccessService::class)->canUseResource($user, $resource), 403);

        if (! $resource->isFile()) {
            $url = $resource->isYoutube()
                ? Helpers::trustedVideoEmbedUrl((string) $resource->external_url)
                : $resource->external_url;
            abort_unless($url, 404);

            return redirect()->away((string) $url);
        }

        return view('teacher.general-library.file-show', [
            'resource' => $resource,
            'fileAvailable' => $this->resourceFileExists($resource),
            'fileUrl' => route('teacher.general-library.resources.file', $resource),
            'downloadUrl' => route('teacher.general-library.resources.file', ['resource' => $resource, 'download' => 1]),
            'breadcrumb_links' => [
                'Library' => route('teacher.get_library'),
                $resource->folder?->title ?? 'Sources' => $resource->folder
                    ? route('teacher.get_library', ['folder' => (int) $resource->folder->id])
                    : route('teacher.get_library'),
                $resource->title => null,
            ],
        ]);
    }

    public function streamResourceFile(Request $request, GeneralLibraryResource $resource): BinaryFileResponse
    {
        $user = Auth::user();
        abort_unless(app(GeneralLibraryAccessService::class)->canUseResource($user, $resource), 403);
        abort_unless($resource->isFile(), 404);

        $disk = $resource->storage_disk ?: self::RESOURCE_DISK;
        abort_unless($this->isAllowedResourceDisk($disk), 404);
        $path = ltrim((string) $resource->file_path, '/');
        abort_if($path === '' || ! Storage::disk($disk)->exists($path), 404);

        $absolutePath = Storage::disk($disk)->path($path);
        $mimeType = $resource->mime_type ?: (Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream');
        $downloadName = $resource->original_filename ?: basename($path);

        if ($request->boolean('download')) {
            return response()->download($absolutePath, $downloadName, ['Content-Type' => $mimeType]);
        }

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function storeBatchResources(Request $request, array $payload, ?int $folderId): RedirectResponse
    {
        $validator = app(LibraryResourceValidator::class);
        $created = 0;
        $nextSortOrder = $this->nextResourceSortOrder($folderId);
        $description = $this->cleanNullableText($payload['description'] ?? null);

        $stagedTokens = $payload['uploaded_files'] ?? [];
        $rawFiles = is_array($request->file('resource_files', [])) ? $request->file('resource_files', []) : [];
        $fileCount = count($stagedTokens) + count($rawFiles);
        $created += $this->storeStagedFileResources(
            tokens: $stagedTokens,
            folderId: $folderId,
            titleOverride: $fileCount === 1 ? $this->cleanNullableText($payload['title'] ?? null) : null,
            description: $description,
            nextSortOrder: $nextSortOrder
        );

        if ($rawFiles !== []) {
            $this->validateAllFiles($rawFiles);
            foreach ($rawFiles as $file) {
                $created += $this->createFileResourceFromUpload(
                    file: $file,
                    folderId: $folderId,
                    title: $fileCount === 1
                        ? ($this->cleanNullableText($payload['title'] ?? null) ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                        : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    description: $description,
                    sortOrder: $nextSortOrder++
                );
            }
        }

        $created += $this->storeQueuedUrlResources(
            $folderId,
            GeneralLibraryResource::TYPE_LINK,
            $payload['link_titles'] ?? [],
            $payload['link_urls'] ?? [],
            $description,
            $nextSortOrder
        );

        $created += $this->storeQueuedUrlResources(
            $folderId,
            GeneralLibraryResource::TYPE_YOUTUBE,
            $payload['youtube_titles'] ?? [],
            $payload['youtube_urls'] ?? [],
            $description,
            $nextSortOrder
        );

        if ($created === 0) {
            return back()->withErrors(['library_action' => 'Add at least one file, link, or YouTube source.']);
        }

        $this->markFolderSourcesOnly($folderId);

        return back()->with('success', $created === 1 ? 'Library source added.' : $created.' Library sources added.');
    }

    private function storeQueuedUrlResources(
        ?int $folderId,
        string $resourceType,
        array $titles,
        array $urls,
        ?string $description,
        int &$nextSortOrder
    ): int {
        $created = 0;
        $validator = app(LibraryResourceValidator::class);

        foreach ($urls as $index => $rawUrl) {
            $url = trim((string) $rawUrl);
            $title = $this->cleanNullableText($titles[$index] ?? null);

            if ($url === '' && $title === null) {
                continue;
            }

            if ($url === '' || $title === null) {
                throw ValidationException::withMessages([
                    'library_action' => 'Each queued source needs both a title and a URL.',
                ]);
            }

            $validator->validateLinkUrl($url);
            if ($resourceType === GeneralLibraryResource::TYPE_YOUTUBE && ! Helpers::isYoutubeUrl($url)) {
                throw ValidationException::withMessages([
                    'library_action' => 'Each queued YouTube source needs a valid YouTube link.',
                ]);
            }

            GeneralLibraryResource::query()->create([
                'general_library_folder_id' => $folderId,
                'resource_type' => $resourceType,
                'title' => $title,
                'description' => $description,
                'external_url' => $url,
                'sort_order' => $nextSortOrder++,
                'created_by_user_id' => (int) Auth::id(),
                'updated_by_user_id' => (int) Auth::id(),
            ]);

            $created++;
        }

        return $created;
    }

    private function storeFileResources(Request $request, array $payload, ?int $folderId): RedirectResponse
    {
        $stagedTokens = $payload['uploaded_files'] ?? [];
        $files = $request->file('resource_files', []);
        if ((! is_array($files) || $files === []) && $stagedTokens === []) {
            return back()->withErrors(['library_action' => 'Choose one or more files to add.']);
        }

        $nextSortOrder = $this->nextResourceSortOrder($folderId);
        $rawFiles = is_array($files) ? $files : [];
        $fileCount = count($stagedTokens) + count($rawFiles);
        $created = $this->storeStagedFileResources(
            tokens: $stagedTokens,
            folderId: $folderId,
            titleOverride: $fileCount === 1 ? $this->cleanNullableText($payload['title'] ?? null) : null,
            description: $this->cleanNullableText($payload['description'] ?? null),
            nextSortOrder: $nextSortOrder
        );

        if ($rawFiles !== []) {
            $this->validateAllFiles($rawFiles);
            foreach ($rawFiles as $file) {
                $created += $this->createFileResourceFromUpload(
                    file: $file,
                    folderId: $folderId,
                    title: $fileCount === 1
                        ? ($this->cleanNullableText($payload['title'] ?? null) ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                        : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    description: $this->cleanNullableText($payload['description'] ?? null),
                    sortOrder: $nextSortOrder++
                );
            }
        }

        $this->markFolderSourcesOnly($folderId);

        return back()->with('success', $created === 1 ? 'Library file added.' : $created.' Library files added.');
    }

    private function createFileResourceFromUpload(
        $file,
        ?int $folderId,
        string $title,
        ?string $description,
        int $sortOrder
    ): int {
        app(LibraryResourceValidator::class)->validateFileUpload($file);
        $path = $file->store('general-library-resources', self::RESOURCE_DISK);
        if ($path === false) {
            throw ValidationException::withMessages(['library_action' => 'A Library file could not be stored.']);
        }

        GeneralLibraryResource::query()->create([
            'general_library_folder_id' => $folderId,
            'resource_type' => GeneralLibraryResource::TYPE_FILE,
            'title' => $title,
            'description' => $description,
            'storage_disk' => self::RESOURCE_DISK,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'sort_order' => $sortOrder,
            'created_by_user_id' => (int) Auth::id(),
            'updated_by_user_id' => (int) Auth::id(),
        ]);

        return 1;
    }

    private function storeStagedFileResources(
        array $tokens,
        ?int $folderId,
        ?string $titleOverride,
        ?string $description,
        int &$nextSortOrder
    ): int {
        $created = 0;
        foreach ($tokens as $token) {
            $staged = $this->stagedUploadFromToken((string) $token);
            $sourceDisk = (string) $staged['disk'];
            $sourcePath = (string) $staged['path'];
            $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
            $targetPath = 'general-library-resources/'.(string) Str::uuid().($extension !== '' ? '.'.$extension : '');

            if (! Storage::disk($sourceDisk)->move($sourcePath, $targetPath)) {
                throw ValidationException::withMessages(['library_action' => 'A staged Library file could not be saved. Please upload it again.']);
            }

            GeneralLibraryResource::query()->create([
                'general_library_folder_id' => $folderId,
                'resource_type' => GeneralLibraryResource::TYPE_FILE,
                'title' => $titleOverride ?: pathinfo((string) $staged['original_filename'], PATHINFO_FILENAME),
                'description' => $description,
                'storage_disk' => $sourceDisk,
                'file_path' => $targetPath,
                'original_filename' => (string) $staged['original_filename'],
                'mime_type' => (string) $staged['mime_type'],
                'file_size' => (int) $staged['size'],
                'sort_order' => $nextSortOrder++,
                'created_by_user_id' => (int) Auth::id(),
                'updated_by_user_id' => (int) Auth::id(),
            ]);

            $created++;
        }

        return $created;
    }

    private function stagedUploadFromToken(string $token): array
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            throw ValidationException::withMessages(['library_action' => 'A staged Library file is no longer valid. Please upload it again.']);
        }

        if (! is_array($payload)
            || (int) ($payload['user_id'] ?? 0) !== (int) Auth::id()
            || ! $this->isAllowedResourceDisk((string) ($payload['disk'] ?? ''))
            || ! isset($payload['path'], $payload['original_filename'], $payload['mime_type'], $payload['size'])) {
            throw ValidationException::withMessages(['library_action' => 'A staged Library file is no longer valid. Please upload it again.']);
        }

        $path = ltrim((string) $payload['path'], '/');
        $disk = (string) $payload['disk'];
        $tempDirectory = $this->temporaryUploadDirectory((int) Auth::id()).'/';
        if (! str_starts_with($path, $tempDirectory) || ! Storage::disk($disk)->exists($path)) {
            throw ValidationException::withMessages(['library_action' => 'A staged Library file is no longer available. Please upload it again.']);
        }

        $payload['path'] = $path;

        return $payload;
    }

    private function validateAllFiles(array $files): void
    {
        $validator = app(LibraryResourceValidator::class);
        $invalid = [];
        foreach ($files as $file) {
            try {
                $validator->validateFileUpload($file);
            } catch (ValidationException) {
                $invalid[] = $file->getClientOriginalName();
            }
        }

        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'library_action' => 'These files are not supported: '.implode(', ', $invalid).'. Supported types: '.strtoupper(implode(', ', LibraryResourceValidator::allowedExtensions())).'.',
            ]);
        }
    }

    private function temporaryUploadDirectory(int $userId): string
    {
        return 'general-library-temp/'.$userId;
    }

    private function isAllowedResourceDisk(string $disk): bool
    {
        return in_array($disk, [self::RESOURCE_DISK, self::LEGACY_PUBLIC_DISK], true);
    }

    private function assertTablesReady(): void
    {
        abort_unless(
            Schema::hasTable('general_library_folders')
            && Schema::hasTable('general_library_resources'),
            503
        );
    }

    private function nextFolderSortOrder(?int $parentId): int
    {
        return ((int) GeneralLibraryFolder::query()
            ->where('parent_id', $parentId)
            ->max('sort_order')) + 10;
    }

    private function nextResourceSortOrder(?int $folderId): int
    {
        return ((int) GeneralLibraryResource::query()
            ->where('general_library_folder_id', $folderId)
            ->max('sort_order')) + 10;
    }

    private function cleanNullableText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function resourceFileExists(GeneralLibraryResource $resource): bool
    {
        $disk = $resource->storage_disk ?: self::RESOURCE_DISK;

        return $resource->isFile()
            && $this->isAllowedResourceDisk($disk)
            && filled($resource->file_path)
            && Storage::disk($disk)->exists(ltrim((string) $resource->file_path, '/'));
    }

    private function libraryRouteName(Request $request): string
    {
        return str_starts_with(trim($request->path(), '/'), 'admin/library')
            ? 'admin.library.index'
            : 'teacher.get_library';
    }

    private function breadcrumbs(?GeneralLibraryFolder $folder, string $libraryRouteName): array
    {
        $items = [[
            'title' => 'Library',
            'url' => route($libraryRouteName),
        ]];

        $trail = [];
        $current = $folder;
        while ($current) {
            array_unshift($trail, $current);
            $current = $current->parent;
        }

        foreach ($trail as $index => $item) {
            $items[] = [
                'title' => (string) $item->title,
                'url' => $index === count($trail) - 1
                    ? null
                    : route($libraryRouteName, ['folder' => (int) $item->id]),
            ];
        }

        return $items;
    }

    private function breadcrumbLinks(?GeneralLibraryFolder $folder, string $libraryRouteName): array
    {
        return collect($this->breadcrumbs($folder, $libraryRouteName))
            ->mapWithKeys(fn (array $crumb): array => [$crumb['title'] => $crumb['url']])
            ->all();
    }

    private function ensureFolderAcceptsSubfolders(GeneralLibraryFolder $folder): void
    {
        if ($folder->isSourcesOnly() || $folder->resources()->active()->exists()) {
            throw ValidationException::withMessages([
                'library_action' => 'This is a final source folder. Add sources here, not subfolders.',
            ]);
        }
    }

    private function ensureFolderAcceptsSources(GeneralLibraryFolder $folder): void
    {
        if ($folder->children()->active()->exists()) {
            throw ValidationException::withMessages([
                'library_action' => 'Add sources inside a final folder, not a folder that already contains subfolders.',
            ]);
        }
    }

    private function markFolderSourcesOnly(?int $folderId): void
    {
        if ($folderId === null) {
            return;
        }

        GeneralLibraryFolder::query()
            ->whereKey($folderId)
            ->update(['content_mode' => GeneralLibraryFolder::CONTENT_MODE_SOURCES_ONLY]);
    }

    private function resourceHasAttachmentSnapshot(GeneralLibraryResource $resource): bool
    {
        $path = $this->snapshotPathFor($resource);

        if ($path === null) {
            return false;
        }

        $query = AttachmentFile::query()->where('path', $path);

        if ($resource->isFile()) {
            $query->orWhere('path', 'like', 'attachments/general-library-resource-'.(int) $resource->id.'/%');
        }

        return $query->exists();
    }

    private function snapshotPathFor(GeneralLibraryResource $resource): ?string
    {
        if ($resource->isFile()) {
            $path = trim((string) $resource->file_path);

            return $path !== '' ? ltrim($path, '/') : null;
        }

        if ($resource->isYoutube()) {
            return Helpers::trustedVideoEmbedUrl((string) $resource->external_url);
        }

        $url = trim((string) $resource->external_url);

        return $url !== '' ? $url : null;
    }

    private function filePathCanBeDeleted(string $path, int $deletedResourceId): bool
    {
        return ! GeneralLibraryResource::query()
            ->where('file_path', $path)
            ->whereKeyNot($deletedResourceId)
            ->exists()
            && ! AttachmentFile::query()->where('path', $path)->exists();
    }
}
