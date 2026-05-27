<?php

namespace App\Http\Controllers\Front\Teacher;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\AttachmentFile;
use App\Models\LibraryResource;
use App\Models\LibrarySection;
use App\Models\Subject;
use App\Models\TeacherSubjectClass;
use App\Models\User;
use App\Services\Library\LegacyLibraryAccessService;
use App\Services\Library\LibraryFileRetentionService;
use App\Services\Library\LibraryResourceAccessService;
use App\Services\Library\LibraryResourceValidator;
use App\Services\Library\LibrarySectionValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LibraryController extends Controller
{
    /**
     * Display a listing of the teacher's classes.
     */
    public function get_library()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        abort_unless($user->hasAnyRole(['admin', 'teacher']), 403);

        $showArchived = $user->hasRole('teacher') && request()->boolean('show_archived');
        $canManageTeacherLibrary = $user->hasRole('teacher');
        $teacherSubjectOptions = $canManageTeacherLibrary ? $this->teacherSubjectOptions($user) : [];
        $singleTeacherSubject = count($teacherSubjectOptions) === 1 ? $teacherSubjectOptions[0] : null;
        $teacherFolderContext = null;
        $rootSubjectContext = null;
        $teacherBrowseSection = $this->resolveTeacherBrowseSection($user, request()->integer('folder') ?: null);
        $requestedSubjectId = $canManageTeacherLibrary ? (request()->integer('subject') ?: null) : null;

        if (! $teacherBrowseSection && $canManageTeacherLibrary) {
            $rootSubjectContext = $requestedSubjectId
                ? collect($teacherSubjectOptions)->firstWhere('id', $requestedSubjectId)
                : $singleTeacherSubject;

            if ($requestedSubjectId !== null && ! $rootSubjectContext) {
                abort(404);
            }
        }

        $teacherLibraryCards = $this->teacherLibraryCards(
            $user,
            $teacherBrowseSection,
            $showArchived,
            $rootSubjectContext ? (int) $rootSubjectContext['id'] : null
        );

        $teacherToolCards = $this->teacherToolCards($user, $rootSubjectContext);

        $library_list = [
            [
                'title' => 'Listen & Read',
                'link' => 'reading/listen-read',
                'description' => 'Guided reading resources for classroom and home practice.',
                'icon' => 'ti tabler-headphones',
                'tone' => 'primary',
                'meta' => 'Reading',
                'allowed_roles' => ['teacher'],
            ],
            [
                'title' => 'Level up Tutorials',
                'link' => 'tutriols/level-up',
                'description' => 'Short tutorial paths for targeted skill-building.',
                'icon' => 'ti tabler-stairs-up',
                'tone' => 'info',
                'meta' => 'Tutorials',
                'allowed_roles' => ['teacher'],
            ],
            [
                'title' => 'Notice & Note',
                'link' => 'course/notice-note',
                'description' => 'Close-reading signposts and discussion prompts.',
                'icon' => 'ti tabler-notes',
                'tone' => 'warning',
                'meta' => 'Course',
                'allowed_roles' => ['teacher'],
            ],
            [
                'title' => 'Peer Coach',
                'link' => 'course/peer-coach',
                'description' => 'Structured peer-support activities and coaching tasks.',
                'icon' => 'ti tabler-users-group',
                'tone' => 'success',
                'meta' => 'Course',
                'allowed_roles' => ['teacher'],
            ],
            [
                'title' => 'TV Series / Friends',
                'link' => 'tv_series/friends',
                'description' => 'Language practice through familiar scenes and dialogue.',
                'icon' => 'ti tabler-device-tv',
                'tone' => 'danger',
                'meta' => 'Video',
                'allowed_roles' => ['teacher'],
            ],
            [
                'title' => 'TED',
                'link' => 'videos/ted',
                'description' => 'Talk-based listening, vocabulary, and reflection work.',
                'icon' => 'ti tabler-video',
                'tone' => 'primary',
                'meta' => 'Video',
                'allowed_roles' => ['teacher'],
            ],
            [
                'title' => 'Court',
                'link' => 'videos/court',
                'description' => 'Argument, evidence, and speaking practice resources.',
                'icon' => 'ti tabler-gavel',
                'tone' => 'secondary',
                'meta' => 'Video',
                'allowed_roles' => ['teacher'],
            ],
            [
                'title' => 'Radio',
                'link' => 'course/radio',
                'description' => 'Audio-led activities for listening fluency.',
                'icon' => 'ti tabler-radio',
                'tone' => 'info',
                'meta' => 'Course',
                'allowed_roles' => ['admin', 'teacher'],
            ],
            [
                'title' => 'SAT',
                'link' => 'course/sat',
                'description' => 'Practice collections for exam-focused reading and language.',
                'icon' => 'ti tabler-school',
                'tone' => 'success',
                'meta' => 'Exam',
                'allowed_roles' => ['teacher'],
            ],
            [
                'title' => 'Grammar',
                'link' => 'course/grammar',
                'description' => 'Grammar references and practice pathways.',
                'icon' => 'ti tabler-pencil',
                'tone' => 'warning',
                'meta' => 'Course',
                'allowed_roles' => ['teacher'],
            ],
            [
                'title' => 'Background',
                'link' => 'course/background',
                'description' => 'Context-building materials before deeper reading tasks.',
                'icon' => 'ti tabler-book-2',
                'tone' => 'danger',
                'meta' => 'Course',
                'allowed_roles' => ['teacher'],
            ],
        ];

        if ($teacherToolCards !== []) {
            $library_list = array_merge($teacherToolCards, $library_list);
        }

        if ($teacherBrowseSection) {
            $folderSubjectTitle = Subject::query()
                ->whereKey((int) $teacherBrowseSection->subject_id)
                ->value('title');

            $teacherFolderContext = [
                'id' => (int) $teacherBrowseSection->id,
                'title' => (string) $teacherBrowseSection->title,
                'description' => (string) ($teacherBrowseSection->description ?? ''),
                'subject_id' => (int) $teacherBrowseSection->subject_id,
                'subject_title' => (string) ($folderSubjectTitle ?: 'Subject'),
                'breadcrumbs' => $this->sectionBreadcrumbs($teacherBrowseSection),
                'backHref' => $teacherBrowseSection->parent_id
                    ? $this->teacherLibraryRoute((int) $teacherBrowseSection->parent_id, $showArchived ? ['show_archived' => 1] : [])
                    : $this->teacherLibraryRoute(null, $showArchived ? ['show_archived' => 1] : []),
            ];
            $library_list = $teacherLibraryCards;
        } elseif ($rootSubjectContext) {
            $library_list = array_merge($teacherLibraryCards, $library_list);
        } elseif ($canManageTeacherLibrary && count($teacherSubjectOptions) > 1) {
            $library_list = array_merge($teacherToolCards, $this->teacherSubjectCards($teacherSubjectOptions));
        } else {
            $library_list = array_merge($teacherLibraryCards, $library_list);
        }

        $viewerRole = $user->hasRole('teacher') ? 'teacher' : 'admin';
        $canAccessLegacyLibrary = app(LegacyLibraryAccessService::class)->canAccessLegacyLibrary($user);
        $library_list = array_map(function (array $item) use ($viewerRole, $canAccessLegacyLibrary) {
            $allowedRoles = $item['allowed_roles'] ?? ['teacher'];
            $isLegacyItem = ! in_array(($item['source'] ?? null), ['teacher_library', 'subject_library'], true)
                && ($item['meta'] ?? null) !== 'Teacher tool';
            $item['is_available'] = in_array($viewerRole, $allowedRoles, true)
                && (! $isLegacyItem || $canAccessLegacyLibrary);
            $item['access_note'] = $item['is_available']
                ? null
                : ($isLegacyItem ? 'Private legacy Library source' : 'Teacher access only');

            return $item;
        }, $library_list);

        $breadcrumb_links = [
            'Library' => null,
        ];
        if (! empty($teacherFolderContext['breadcrumbs'])) {
            $breadcrumb_links = [];
            foreach ($teacherFolderContext['breadcrumbs'] as $crumb) {
                $breadcrumb_links[$crumb['title']] = $crumb['href'];
            }
        } elseif ($rootSubjectContext) {
            $breadcrumb_links = [
                'Library' => route('teacher.get_library'),
                $rootSubjectContext['title'] => null,
            ];
        }

        $libraryHeroTitle = ! empty($teacherFolderContext)
            ? $teacherFolderContext['title']
            : ($rootSubjectContext ? $rootSubjectContext['title'].' Library' : 'Library');
        $libraryHeroSubtitle = ! empty($teacherFolderContext)
            ? $teacherFolderContext['subject_title']
            : ($rootSubjectContext ? 'Teaching library' : 'Choose a subject to manage reusable resources');
        $librarySortSubjectId = $teacherBrowseSection
            ? (int) $teacherBrowseSection->subject_id
            : ($rootSubjectContext['id'] ?? null);
        $librarySortContext = [
            'can_sort' => $canManageTeacherLibrary && ! $showArchived && $librarySortSubjectId !== null,
            'subject_id' => $librarySortSubjectId,
            'parent_id' => $teacherBrowseSection ? (int) $teacherBrowseSection->id : null,
        ];

        return view('teacher.library.index', compact(
            'library_list',
            'breadcrumb_links',
            'canManageTeacherLibrary',
            'teacherFolderContext',
            'rootSubjectContext',
            'teacherSubjectOptions',
            'showArchived',
            'libraryHeroTitle',
            'libraryHeroSubtitle',
            'librarySortContext'
        ));
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $user = $this->authorizedTeacher();
        $payload = $request->validate([
            'parent_id' => ['nullable', 'integer'],
            'subject_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:300'],
        ]);

        $access = app(LibraryResourceAccessService::class);
        $parentId = filled($payload['parent_id'] ?? null) ? (int) $payload['parent_id'] : null;
        $parent = null;
        $ownerId = (int) $user->id;
        $subjectId = filled($payload['subject_id'] ?? null) ? (int) $payload['subject_id'] : null;

        if ($parentId !== null) {
            $parent = LibrarySection::query()->findOrFail($parentId);
            $access->authorizeSection($user, $parent);
            $ownerId = (int) $parent->owner_user_id;
            $subjectId = (int) $parent->subject_id;
        } else {
            if ($subjectId === null) {
                return back()->withErrors(['library_action' => 'Choose a subject before creating a root Library folder.']);
            }

            $access->authorizeSubject($user, $subjectId);
        }

        app(LibrarySectionValidator::class)->validateUniqueActiveSiblingTitle(
            $ownerId,
            $subjectId,
            $parentId,
            $payload['title']
        );

        LibrarySection::create([
            'owner_user_id' => $ownerId,
            'subject_id' => $subjectId,
            'parent_id' => $parentId,
            'title' => trim($payload['title']),
            'description' => $this->cleanNullableText($payload['description'] ?? null),
            'sort_order' => $this->nextSectionSortOrder($ownerId, $subjectId, $parentId),
            'created_by_user_id' => (int) $user->id,
        ]);

        return back()->with('success', $parent ? 'Library subfolder added.' : 'Library folder added.');
    }

    public function reorderPageItems(Request $request): JsonResponse
    {
        $user = $this->authorizedTeacher();
        $payload = $request->validate([
            'subject_id' => ['required', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'items' => ['required', 'array'],
            'items.*.type' => ['required', 'string', 'in:section,resource'],
            'items.*.id' => ['required', 'integer'],
        ]);

        $subjectId = (int) $payload['subject_id'];
        $parentId = filled($payload['parent_id'] ?? null) ? (int) $payload['parent_id'] : null;
        $items = array_values($payload['items']);
        $access = app(LibraryResourceAccessService::class);

        if ($this->hasDuplicateReorderItems($items)) {
            throw ValidationException::withMessages([
                'items' => 'Library reorder contains duplicate items.',
            ]);
        }

        if ($parentId !== null) {
            $parent = LibrarySection::query()->findOrFail($parentId);
            $access->authorizeSection($user, $parent);

            if ((int) $parent->subject_id !== $subjectId) {
                throw ValidationException::withMessages([
                    'subject_id' => 'The folder does not belong to this subject.',
                ]);
            }
        } else {
            $access->authorizeSubject($user, $subjectId);

            if (collect($items)->contains(fn (array $item): bool => $item['type'] === 'resource')) {
                throw ValidationException::withMessages([
                    'items' => 'Root Library reorder cannot include resources.',
                ]);
            }
        }

        $sectionIds = collect($items)
            ->where('type', 'section')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
        $resourceIds = collect($items)
            ->where('type', 'resource')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();

        $validSectionCount = LibrarySection::query()
            ->ownedBy((int) $user->id)
            ->forSubject($subjectId)
            ->active()
            ->where(function ($query) use ($parentId): void {
                $parentId === null
                    ? $query->whereNull('parent_id')
                    : $query->where('parent_id', $parentId);
            })
            ->whereIn('id', $sectionIds)
            ->count();

        if ($validSectionCount !== count($sectionIds)) {
            throw ValidationException::withMessages([
                'items' => 'One or more folders cannot be reordered here.',
            ]);
        }

        if ($resourceIds !== []) {
            $validResourceCount = LibraryResource::query()
                ->ownedBy((int) $user->id)
                ->forSubject($subjectId)
                ->active()
                ->where('library_section_id', $parentId)
                ->whereIn('id', $resourceIds)
                ->count();

            if ($validResourceCount !== count($resourceIds)) {
                throw ValidationException::withMessages([
                    'items' => 'One or more resources cannot be reordered here.',
                ]);
            }
        }

        DB::transaction(function () use ($items): void {
            foreach ($items as $index => $item) {
                $attributes = ['sort_order' => $index + 1];

                if ($item['type'] === 'section') {
                    LibrarySection::query()
                        ->whereKey((int) $item['id'])
                        ->update($attributes);

                    continue;
                }

                LibraryResource::query()
                    ->whereKey((int) $item['id'])
                    ->update($attributes);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function storeResource(Request $request, LibrarySection $section): RedirectResponse
    {
        $user = $this->authorizedTeacher();
        app(LibraryResourceAccessService::class)->authorizeSection($user, $section);

        $payload = $request->validate([
            'resource_kind' => ['required', 'in:file,link,youtube'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:300'],
            'external_url' => ['nullable', 'string', 'max:2048'],
            'resource_files' => ['nullable', 'array'],
            'resource_files.*' => ['file', 'max:'.LibraryResourceValidator::MAX_UPLOAD_KB],
        ]);

        $validator = app(LibraryResourceValidator::class);
        $baseData = [
            'owner_user_id' => (int) $section->owner_user_id,
            'subject_id' => (int) $section->subject_id,
            'library_section_id' => (int) $section->id,
            'description' => $this->cleanNullableText($payload['description'] ?? null),
            'created_by_user_id' => (int) $user->id,
        ];

        if ($payload['resource_kind'] === 'file') {
            $files = $request->file('resource_files', []);
            if (! is_array($files) || $files === []) {
                return back()->withErrors(['library_action' => 'Choose one or more files to add.']);
            }

            $titles = $this->resourceTitlesForFiles($files, $payload['title'] ?? null);
            if (count($titles) !== count(array_unique($titles))) {
                return back()->withErrors(['library_action' => 'Selected files must have unique filenames.']);
            }

            foreach ($titles as $title) {
                $validator->validateUniqueActiveTitle((int) $section->id, $title);
            }

            $nextSortOrder = $this->nextResourceSortOrder((int) $section->id);
            foreach ($files as $index => $file) {
                $validator->validateFileUpload($file);
                $path = $file->store('library-resources', 'public');
                if ($path === false) {
                    return back()->withErrors(['library_action' => 'A Library file could not be stored.']);
                }

                LibraryResource::create($baseData + [
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

            return back()->with('success', count($files) === 1 ? 'Library file added.' : count($files).' Library files added.');
        }

        $title = $this->cleanNullableText($payload['title'] ?? null);
        $url = trim((string) ($payload['external_url'] ?? ''));
        if ($title === null) {
            return back()->withErrors(['library_action' => 'Enter a title for this Library link.']);
        }

        $validator->validateUniqueActiveTitle((int) $section->id, $title);
        $validator->validateLinkUrl($url);

        if ($payload['resource_kind'] === 'youtube' && ! Helpers::isYoutubeUrl($url)) {
            return back()->withErrors(['library_action' => 'Enter a valid YouTube link.']);
        }

        LibraryResource::create($baseData + [
            'resource_type' => LibraryResource::TYPE_LINK,
            'title' => $title,
            'external_url' => $url,
            'sort_order' => $this->nextResourceSortOrder((int) $section->id),
        ]);

        return back()->with('success', $payload['resource_kind'] === 'youtube' ? 'YouTube Library link added.' : 'Library link added.');
    }

    public function updateSection(Request $request, LibrarySection $section): RedirectResponse
    {
        $user = $this->authorizedTeacher();
        app(LibraryResourceAccessService::class)->authorizeSection($user, $section);

        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:300'],
        ]);

        app(LibrarySectionValidator::class)->validateUniqueActiveSiblingTitle(
            (int) $section->owner_user_id,
            (int) $section->subject_id,
            $section->parent_id ? (int) $section->parent_id : null,
            $payload['title'],
            (int) $section->id
        );

        $section->update([
            'title' => trim($payload['title']),
            'description' => $this->cleanNullableText($payload['description'] ?? null),
        ]);

        return back()->with('success', 'Library folder updated.');
    }

    public function archiveSection(LibrarySection $section): RedirectResponse
    {
        $user = $this->authorizedTeacher();
        app(LibraryResourceAccessService::class)->authorizeSection($user, $section);

        $section->update([
            'status' => LibrarySection::STATUS_ARCHIVED,
            'archived_at' => now(config('app.timezone')),
        ]);

        return redirect()->to($this->teacherLibraryRoute(
            $section->parent_id ? (int) $section->parent_id : null,
            $section->parent_id ? [] : ['subject' => (int) $section->subject_id]
        ))
            ->with('success', 'Library folder archived. Existing assigned work is unchanged.');
    }

    public function restoreSection(LibrarySection $section): RedirectResponse
    {
        $user = $this->authorizedTeacher();
        app(LibraryResourceAccessService::class)->authorizeSection($user, $section);

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

        return back()->with('success', 'Library folder restored.');
    }

    public function deleteSection(LibrarySection $section): RedirectResponse
    {
        $user = $this->authorizedTeacher();
        app(LibraryResourceAccessService::class)->authorizeSection($user, $section);

        if ($section->children()->exists() || $section->resources()->exists()) {
            return back()->withErrors(['library_action' => 'Delete or archive everything inside this folder first.']);
        }

        $section->delete();

        return redirect()->to($this->teacherLibraryRoute(
            $section->parent_id ? (int) $section->parent_id : null,
            $section->parent_id ? [] : ['subject' => (int) $section->subject_id]
        ))
            ->with('success', 'Empty Library folder deleted.');
    }

    public function updateResource(Request $request, LibraryResource $resource): RedirectResponse
    {
        $user = $this->authorizedTeacher();
        app(LibraryResourceAccessService::class)->authorizeResource($user, $resource);

        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:300'],
        ]);

        app(LibraryResourceValidator::class)->validateUniqueActiveTitle(
            (int) $resource->library_section_id,
            $payload['title'],
            (int) $resource->id
        );

        $resource->update([
            'title' => trim($payload['title']),
            'description' => $this->cleanNullableText($payload['description'] ?? null),
        ]);

        return back()->with('success', 'Library resource updated. Existing assigned work is unchanged.');
    }

    public function archiveResource(LibraryResource $resource): RedirectResponse
    {
        $user = $this->authorizedTeacher();
        app(LibraryResourceAccessService::class)->authorizeResource($user, $resource);

        $resource->update([
            'status' => LibraryResource::STATUS_ARCHIVED,
            'archived_at' => now(config('app.timezone')),
        ]);

        return back()->with('success', 'Library resource archived. Existing assigned work is unchanged.');
    }

    public function restoreResource(LibraryResource $resource): RedirectResponse
    {
        $user = $this->authorizedTeacher();
        app(LibraryResourceAccessService::class)->authorizeResource($user, $resource);

        app(LibraryResourceValidator::class)->validateUniqueActiveTitle(
            (int) $resource->library_section_id,
            (string) $resource->title,
            (int) $resource->id
        );

        $resource->update([
            'status' => LibraryResource::STATUS_ACTIVE,
            'archived_at' => null,
        ]);

        return back()->with('success', 'Library resource restored.');
    }

    public function deleteResource(LibraryResource $resource): RedirectResponse
    {
        $user = $this->authorizedTeacher();
        app(LibraryResourceAccessService::class)->authorizeResource($user, $resource);

        if ($this->resourceHasAttachmentSnapshot($resource)) {
            return back()->withErrors(['library_action' => 'This resource is already used in a task. Archive it instead so existing work stays safe.']);
        }

        $path = $resource->isFile() ? $resource->file_path : null;
        $disk = $resource->storage_disk ?: 'public';
        $resource->delete();

        if ($path) {
            app(LibraryFileRetentionService::class)->deleteIfUnreferenced($path, $disk);
        }

        return back()->with('success', 'Unused Library resource deleted.');
    }

    public function openResource(LibraryResource $resource): View|\Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        abort_unless($user && $user->hasRole('teacher'), 403);
        app(LibraryResourceAccessService::class)->authorizeResource($user, $resource);

        if ($resource->isLink()) {
            $externalUrl = $this->trustedExternalAttachmentUrl((string) $resource->external_url);
            abort_unless($externalUrl, 404);

            return redirect()->away($externalUrl);
        }

        $path = ltrim((string) $resource->file_path, '/');
        $disk = $resource->storage_disk ?: 'public';
        $fileAvailable = $path !== '' && $disk === 'public' && Storage::disk($disk)->exists($path);
        $fileUrl = route('teacher.library.resources.file', $resource);
        $downloadUrl = route('teacher.library.resources.file', [
            'resource' => $resource,
            'download' => 1,
        ]);
        $ext = strtolower(pathinfo($resource->original_filename ?: $path, PATHINFO_EXTENSION));
        $folderUrl = $this->teacherLibraryRoute((int) $resource->library_section_id);
        $breadcrumb_links = [
            'Library' => $this->teacherLibraryRoute(),
            $resource->section?->title ?? 'Folder' => $folderUrl,
            $resource->title => null,
        ];

        return view('teacher.library.file-show', [
            'resource' => $resource,
            'fileUrl' => $fileUrl,
            'downloadUrl' => $downloadUrl,
            'ext' => $ext,
            'fileAvailable' => $fileAvailable,
            'folderUrl' => $folderUrl,
            'breadcrumb_links' => $breadcrumb_links,
        ]);
    }

    public function streamResourceFile(Request $request, LibraryResource $resource): BinaryFileResponse
    {
        $user = Auth::user();

        abort_unless($user && $user->hasRole('teacher'), 403);
        app(LibraryResourceAccessService::class)->authorizeResource($user, $resource);
        abort_unless($resource->isFile(), 404);

        $disk = $resource->storage_disk ?: 'public';
        abort_unless($disk === 'public', 404);

        $path = ltrim((string) $resource->file_path, '/');
        abort_if($path === '' || ! Storage::disk($disk)->exists($path), 404);

        $absolutePath = Storage::disk($disk)->path($path);
        $mimeType = $resource->mime_type ?: (Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream');
        $downloadName = $resource->original_filename ?: $this->safeDownloadFilename($path, 'library-resource');

        if ($request->boolean('download')) {
            return $this->attachmentDownloadResponse($absolutePath, $downloadName, [
                'Content-Type' => $mimeType,
            ]);
        }

        return $this->inlineAttachmentResponse($absolutePath, $downloadName, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function resolveTeacherBrowseSection(User $user, ?int $folderId): ?LibrarySection
    {
        if ($folderId === null) {
            return null;
        }

        abort_unless($user->hasRole('teacher'), 404);
        abort_unless(Schema::hasTable('library_sections'), 404);

        $section = LibrarySection::query()->findOrFail($folderId);
        abort_unless($section->isActive(), 404);

        if (! app(LibraryResourceAccessService::class)->canManageSection($user, $section)) {
            abort(404);
        }

        return $section;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function teacherLibraryCards(
        User $user,
        ?LibrarySection $parentSection = null,
        bool $showArchived = false,
        ?int $rootSubjectId = null
    ): array {
        if (! $user->hasRole('teacher') || ! Schema::hasTable('library_sections') || ! Schema::hasTable('library_resources')) {
            return [];
        }

        $subjectIds = $parentSection
            ? [(int) $parentSection->subject_id]
            : ($rootSubjectId ? [$rootSubjectId] : $this->teacherSubjectIds($user));

        if ($subjectIds === []) {
            return [];
        }

        $parentId = $parentSection ? (int) $parentSection->id : null;
        $sections = LibrarySection::query()
            ->withCount([
                'children as active_children_count' => fn ($query) => $query->active(),
                'resources as active_resources_count' => fn ($query) => $query->active(),
            ])
            ->ownedBy((int) $user->id)
            ->whereIn('subject_id', $subjectIds)
            ->when(! $showArchived, fn ($query) => $query->active())
            ->where(function ($query) use ($parentId) {
                $parentId === null
                    ? $query->whereNull('parent_id')
                    : $query->where('parent_id', $parentId);
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->orderBy('id')
            ->get();

        $cards = $sections->map(function (LibrarySection $section): array {
            $folderCount = (int) ($section->active_children_count ?? 0);
            $resourceCount = (int) ($section->active_resources_count ?? 0);
            $description = trim((string) ($section->description ?? ''));

            return [
                'title' => (string) $section->title,
                'entity_type' => 'section',
                'entity_id' => (int) $section->id,
                'sort_order' => (int) $section->sort_order,
                'entity_description' => $description,
                'href' => $this->teacherLibraryRoute((int) $section->id),
                'description' => $description !== ''
                    ? $description
                    : $folderCount.' folders, '.$resourceCount.' resources',
                'icon' => 'ti tabler-folder',
                'tone' => 'primary',
                'meta' => 'My Library folder',
                'kind' => 'folder',
                'can_add_children' => true,
                'cta' => 'Open folder',
                'source' => 'teacher_library',
                'manage_href' => route('teacher.library.manage', ['section' => $section->id]),
                'allowed_roles' => ['teacher'],
                'is_available' => true,
                'access_note' => null,
                'status' => (string) $section->status,
                'is_archived' => $section->isArchived(),
            ];
        })->all();

        if ($parentSection) {
            $resources = LibraryResource::query()
                ->ownedBy((int) $user->id)
                ->forSubject((int) $parentSection->subject_id)
                ->where('library_section_id', (int) $parentSection->id)
                ->when(! $showArchived, fn ($query) => $query->active())
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get();

            foreach ($resources as $resource) {
                $cards[] = [
                    'title' => (string) $resource->title,
                    'entity_type' => 'resource',
                    'entity_id' => (int) $resource->id,
                    'sort_order' => (int) $resource->sort_order,
                    'entity_description' => (string) ($resource->description ?? ''),
                    'href' => route('teacher.library.resources.open', [
                        'resource' => $resource,
                        'return_to' => $this->teacherLibraryRoute((int) $parentSection->id),
                    ]),
                    'description' => (string) ($resource->description
                        ?: ($resource->isFile()
                            ? ($resource->original_filename ?: $resource->file_path)
                            : $resource->external_url)),
                    'icon' => $resource->isFile() ? 'ti tabler-file' : 'ti tabler-link',
                    'tone' => $resource->isFile() ? 'info' : ($this->isYoutubeUrl((string) $resource->external_url) ? 'danger' : 'success'),
                    'meta' => $resource->isFile() ? 'File' : ($this->isYoutubeUrl((string) $resource->external_url) ? 'YouTube' : 'Link'),
                    'kind' => $resource->isFile() ? 'file' : ($this->isYoutubeUrl((string) $resource->external_url) ? 'youtube' : 'link'),
                    'cta' => $resource->isFile() ? 'Open file' : ($this->isYoutubeUrl((string) $resource->external_url) ? 'Open video' : 'Open link'),
                    'source' => 'teacher_library',
                    'manage_href' => route('teacher.library.manage', ['section' => $parentSection->id]),
                    'allowed_roles' => ['teacher'],
                    'is_available' => true,
                    'access_note' => null,
                    'status' => (string) $resource->status,
                    'is_archived' => $resource->isArchived(),
                ];
            }

            usort($cards, fn (array $left, array $right): int => [
                (int) ($left['sort_order'] ?? 0),
                strtolower((string) ($left['title'] ?? '')),
                (int) ($left['entity_id'] ?? 0),
            ] <=> [
                (int) ($right['sort_order'] ?? 0),
                strtolower((string) ($right['title'] ?? '')),
                (int) ($right['entity_id'] ?? 0),
            ]);
        }

        return $cards;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function teacherToolCards(User $user, ?array $rootSubjectContext = null): array
    {
        if (! $user->hasAnyRole(['admin', 'teacher', 'owner'])) {
            return [];
        }

        $subjectTitle = Str::lower((string) ($rootSubjectContext['title'] ?? ''));

        if (! str_contains($subjectTitle, 'language') && ! str_contains($subjectTitle, 'literature')) {
            return [];
        }

        return [
            [
                'title' => 'Vocabulary',
                'link' => route('teacher.library.vocabulary'),
                'description' => 'Manage words, wrong spellings, audio, folders, access, and vocabulary games.',
                'icon' => 'ti tabler-alphabet-latin',
                'tone' => 'info',
                'meta' => 'Teacher tool',
                'kind' => 'folder',
                'cta' => 'Open Vocabulary',
                'allowed_roles' => ['admin', 'teacher', 'owner'],
            ],
        ];
    }

    /**
     * @return array<int, int>
     */
    private function teacherSubjectIds(User $user): array
    {
        if (! Schema::hasTable('teacher_subject_classes')) {
            return [];
        }

        return TeacherSubjectClass::query()
            ->availableForTeacher()
            ->where('user_teacher_coteacher_id', $user->id)
            ->pluck('subject_id')
            ->filter()
            ->map(fn ($subjectId): int => (int) $subjectId)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, title: string}>
     */
    private function teacherSubjectOptions(User $user): array
    {
        $subjectIds = $this->teacherSubjectIds($user);

        if ($subjectIds === []) {
            return [];
        }

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

    /**
     * @param  array<int, array{id: int, title: string}>  $subjectOptions
     * @return array<int, array<string, mixed>>
     */
    private function teacherSubjectCards(array $subjectOptions): array
    {
        return collect($subjectOptions)
            ->map(fn (array $subject): array => [
                'title' => (string) $subject['title'],
                'link' => 'teacher/library?subject='.(int) $subject['id'],
                'href' => route('teacher.get_library', ['subject' => (int) $subject['id']]),
                'description' => 'Open folders, saved sources, and reusable resources for this subject.',
                'icon' => 'ti tabler-books',
                'tone' => 'primary',
                'meta' => 'Subject',
                'kind' => 'folder',
                'cta' => 'Open library',
                'source' => 'subject_library',
                'allowed_roles' => ['teacher'],
            ])
            ->values()
            ->all();
    }

    private function sectionBreadcrumbs(LibrarySection $section): array
    {
        $subjectTitle = Subject::query()
            ->whereKey((int) $section->subject_id)
            ->value('title') ?: 'Subject';
        $items = [
            [
                'title' => 'Library',
                'href' => $this->teacherLibraryRoute(),
            ],
            [
                'title' => (string) $subjectTitle,
                'href' => $this->teacherLibraryRoute(null, ['subject' => (int) $section->subject_id]),
            ],
        ];
        $ancestors = [];
        $current = $section;

        while ($current) {
            array_unshift($ancestors, $current);
            $current = $current->parent_id
                ? LibrarySection::query()->find($current->parent_id)
                : null;
        }

        foreach ($ancestors as $ancestor) {
            $items[] = [
                'title' => (string) $ancestor->title,
                'href' => (int) $ancestor->id === (int) $section->id
                    ? null
                    : $this->teacherLibraryRoute((int) $ancestor->id),
            ];
        }

        return $items;
    }

    private function teacherLibraryRoute(?int $folderId = null, array $query = []): string
    {
        $parameters = $folderId ? ['folder' => $folderId] : [];

        return route('teacher.get_library', array_merge($parameters, $query));
    }

    private function authorizedTeacher(): User
    {
        $user = Auth::user();

        abort_unless($user && $user->hasRole('teacher'), 403);

        return $user;
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

    private function cleanNullableText(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
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

    private function hasDuplicateReorderItems(array $items): bool
    {
        $seen = [];

        foreach ($items as $item) {
            $key = ($item['type'] ?? '').':'.((int) ($item['id'] ?? 0));

            if (isset($seen[$key])) {
                return true;
            }

            $seen[$key] = true;
        }

        return false;
    }

    private function isYoutubeUrl(string $url): bool
    {
        return Helpers::isYoutubeUrl($url);
    }
}
