<?php

namespace App\Livewire\Teacher;

use App\Models\LibraryResource;
use App\Models\LibrarySection;
use App\Models\GeneralLibraryFolder;
use App\Models\GeneralLibraryResource;
use App\Services\Library\GeneralLibraryAccessService;
use App\Services\Library\GeneralLibraryAttachmentAdapter;
use App\Services\Library\LegacyLibraryTaskResourceCatalog;
use App\Services\Library\LibraryResourceAccessService;
use App\Services\Library\LibraryResourceQuery;
use App\Services\SeriesLibrarySourceResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class LibraryPicker extends Component
{
    private const LOCAL_SELECTION_PREFIX = 'library__';

    public bool $open = false;

    public ?int $subjectId = null;

    public ?int $currentSectionId = null;

    public ?int $currentGeneralFolderId = null;

    public string $search = '';

    public bool $showLegacySources = false;

    public ?string $legacyCollectionType = null;

    public ?int $legacyCollectionId = null;

    public bool $legacyCollectionChosen = false;

    public ?int $legacyVocabularyParentId = null;

    public ?array $legacyVocabularyCurrentFolder = null;

    /** @var array<string, bool> */
    public array $selected = [];

    #[On('open-library-picker')]
    public function openPicker(int $subjectId, array $selectedResourceIds = []): void
    {
        app(LibraryResourceAccessService::class)->authorizeSubject(Auth::user(), $subjectId);

        $this->subjectId = $subjectId;
        $this->currentSectionId = null;
        $this->currentGeneralFolderId = null;
        $this->search = '';
        $this->showLegacySources = false;
        $this->legacyCollectionType = null;
        $this->legacyCollectionId = null;
        $this->legacyCollectionChosen = false;
        $this->legacyVocabularyParentId = null;
        $this->legacyVocabularyCurrentFolder = null;
        $this->selected = collect($selectedResourceIds)
            ->mapWithKeys(fn ($id): array => [$this->selectionKey((string) $id) => true])
            ->all();
        $this->open = true;
        $this->resetValidation();
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function enterSection(int $sectionId): void
    {
        $section = $this->resolveSection($sectionId);
        abort_unless($section->isActive(), 404);

        $this->currentSectionId = (int) $section->id;
        $this->search = '';
        $this->showLegacySources = false;
        $this->legacyCollectionType = null;
        $this->legacyCollectionId = null;
        $this->legacyCollectionChosen = false;
        $this->legacyVocabularyParentId = null;
        $this->legacyVocabularyCurrentFolder = null;
    }

    public function enterGeneralFolder(int $folderId): void
    {
        $folder = GeneralLibraryFolder::query()->findOrFail($folderId);
        abort_unless(app(GeneralLibraryAccessService::class)->canUseFolder(Auth::user(), $folder), 404);

        $this->currentSectionId = null;
        $this->currentGeneralFolderId = (int) $folder->id;
        $this->search = '';
        $this->showLegacySources = false;
    }

    public function enterLegacySources(): void
    {
        $this->currentSectionId = null;
        $this->search = '';
        $this->showLegacySources = true;
        $this->legacyCollectionType = null;
        $this->legacyCollectionId = null;
        $this->legacyCollectionChosen = false;
        $this->legacyVocabularyParentId = null;
        $this->legacyVocabularyCurrentFolder = null;
    }

    public function enterVocabularySources(): void
    {
        $this->currentSectionId = null;
        $this->search = '';
        $this->showLegacySources = true;
        $this->legacyCollectionType = SeriesLibrarySourceResolver::TYPE_VOCABULARY;
        $this->legacyCollectionId = null;
        $this->legacyCollectionChosen = false;
        $this->legacyVocabularyParentId = null;
        $this->legacyVocabularyCurrentFolder = null;
    }

    public function enterLegacyCollectionType(string $type): void
    {
        $this->showLegacySources = true;
        $this->legacyCollectionType = $type;
        $this->legacyCollectionId = null;
        $this->legacyCollectionChosen = false;
        $this->legacyVocabularyParentId = null;
        $this->legacyVocabularyCurrentFolder = null;
        $this->search = '';
    }

    public function enterLegacyVocabularyFolder(int $folderId): void
    {
        abort_unless($this->subjectId !== null, 404);

        $folder = $this->findVocabularyCollectionSummary($folderId);
        abort_unless(is_array($folder) && (int) ($folder['child_folder_count'] ?? 0) > 0, 404);

        $this->showLegacySources = true;
        $this->legacyCollectionType = SeriesLibrarySourceResolver::TYPE_VOCABULARY;
        $this->legacyCollectionId = null;
        $this->legacyCollectionChosen = false;
        $this->legacyVocabularyParentId = $folderId;
        $this->legacyVocabularyCurrentFolder = $folder;
        $this->search = '';
    }

    public function enterLegacyCollection(string $key): void
    {
        $parsed = app(LegacyLibraryTaskResourceCatalog::class)->parseCollectionKey($key);
        abort_unless($parsed !== null, 404);

        $this->showLegacySources = true;
        $this->legacyCollectionType = $parsed['type'];
        $this->legacyCollectionId = $parsed['id'];
        $this->legacyCollectionChosen = true;
        $this->search = '';
    }

    public function goToParent(): void
    {
        if ($this->currentSectionId === null) {
            if ($this->currentGeneralFolderId !== null) {
                $folder = GeneralLibraryFolder::query()->find($this->currentGeneralFolderId);
                $this->currentGeneralFolderId = $folder?->parent_id ? (int) $folder->parent_id : null;

                return;
            }

            if ($this->showLegacySources && $this->legacyCollectionChosen) {
                $this->legacyCollectionChosen = false;
                $this->legacyCollectionId = null;

                return;
            }

            if (
                $this->showLegacySources
                && $this->legacyCollectionType === SeriesLibrarySourceResolver::TYPE_VOCABULARY
                && $this->legacyVocabularyCurrentFolder !== null
            ) {
                $parentId = $this->legacyVocabularyCurrentFolder['parent_id'] ?? null;
                $this->legacyVocabularyParentId = is_numeric($parentId) ? (int) $parentId : null;
                $this->legacyVocabularyCurrentFolder = $this->legacyVocabularyParentId
                    ? $this->findVocabularyCollectionSummary($this->legacyVocabularyParentId)
                    : null;
                $this->legacyCollectionId = null;
                $this->legacyCollectionChosen = false;

                return;
            }

            if (
                $this->showLegacySources
                && $this->legacyCollectionType === SeriesLibrarySourceResolver::TYPE_VOCABULARY
            ) {
                $this->showLegacySources = false;
                $this->legacyCollectionType = null;
                $this->legacyCollectionId = null;
                $this->legacyCollectionChosen = false;
                $this->legacyVocabularyParentId = null;
                $this->legacyVocabularyCurrentFolder = null;

                return;
            }

            if ($this->showLegacySources && $this->legacyCollectionType !== null) {
                $this->legacyCollectionType = null;
                $this->legacyCollectionChosen = false;
                $this->legacyVocabularyParentId = null;
                $this->legacyVocabularyCurrentFolder = null;

                return;
            }

            $this->showLegacySources = false;

            return;
        }

        $section = $this->resolveSection($this->currentSectionId);
        $this->currentSectionId = $section->parent_id ? (int) $section->parent_id : null;
    }

    public function goToRoot(): void
    {
        $this->currentSectionId = null;
        $this->currentGeneralFolderId = null;
        $this->search = '';
        $this->showLegacySources = false;
        $this->legacyCollectionType = null;
        $this->legacyCollectionId = null;
        $this->legacyCollectionChosen = false;
        $this->legacyVocabularyParentId = null;
        $this->legacyVocabularyCurrentFolder = null;
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function removeSelection(string $resourceId): void
    {
        unset($this->selected[$resourceId]);
        unset($this->selected[$this->selectionKey($resourceId)]);
    }

    public function applySelection(): void
    {
        $this->dispatch('library-resources-selected', resourceIds: $this->selectedResourceIds());
        $this->close();
    }

    public function render(): View
    {
        $sections = collect();
        $resources = collect();
        $generalFolders = collect();
        $generalResources = collect();
        $breadcrumbs = [];
        $currentSection = null;
        $legacyResources = [];
        $legacyTypes = [];
        $legacyCollections = [];
        $legacyFolderAvailable = false;
        $vocabularyFolderAvailable = false;
        $selectedItems = [];

        if ($this->open && $this->subjectId !== null) {
            $query = app(LibraryResourceQuery::class);
            $user = Auth::user();
            $legacyCatalog = app(LegacyLibraryTaskResourceCatalog::class);
            $selectedItems = $this->selectedResourceSummaries($legacyCatalog);
            $legacyFolderAvailable = false;
            $vocabularyFolderAvailable = false;

            if ($this->showLegacySources) {
                if ($user !== null && $this->legacyCollectionType === null) {
                    $legacyTypes = $legacyCatalog->collectionTypesForSubject($user, $this->subjectId, $this->search);
                } elseif ($user !== null && ! $this->legacyCollectionChosen) {
                    $legacyCollections = $legacyCatalog->collectionsForSubject(
                        $user,
                        $this->subjectId,
                        $this->legacyCollectionType,
                        $this->search,
                        $this->legacyCollectionType === SeriesLibrarySourceResolver::TYPE_VOCABULARY
                            ? $this->legacyVocabularyParentId
                            : null
                    );
                } elseif ($user !== null) {
                    $legacyResources = $legacyCatalog->itemsForSubjectCollection(
                        $user,
                        $this->subjectId,
                        $this->legacyCollectionType,
                        $this->legacyCollectionId,
                        $this->search
                    );
                }

                if ($this->legacyCollectionType === SeriesLibrarySourceResolver::TYPE_VOCABULARY) {
                    $breadcrumbs = [
                        [
                            'id' => null,
                            'title' => 'Vocabulary',
                        ],
                    ];
                } else {
                    $breadcrumbs = [
                        [
                            'id' => null,
                            'title' => 'Legacy Library Sources',
                        ],
                    ];
                }
                if ($this->legacyCollectionType !== null && $this->legacyCollectionType !== SeriesLibrarySourceResolver::TYPE_VOCABULARY) {
                    $breadcrumbs[] = [
                        'id' => null,
                        'title' => str_replace('_', ' ', $this->legacyCollectionType),
                    ];
                }
                if ($this->legacyVocabularyCurrentFolder !== null) {
                    $breadcrumbs[] = [
                        'id' => null,
                        'title' => (string) ($this->legacyVocabularyCurrentFolder['title'] ?? 'Folder'),
                    ];
                }
            } else {
                $currentSection = $this->currentSectionId ? $this->resolveSection($this->currentSectionId) : null;
                if ($currentSection) {
                    $sections = $query
                        ->sections(Auth::user(), $this->subjectId, $this->currentSectionId)
                        ->get();
                    $resources = $query
                        ->resources(Auth::user(), $this->subjectId, $this->currentSectionId)
                        ->get();
                    $breadcrumbs = $this->breadcrumbs($currentSection);
                }

                if (
                    app(GeneralLibraryAccessService::class)->canView($user)
                    && Schema::hasTable('general_library_folders')
                    && Schema::hasTable('general_library_resources')
                ) {
                    $generalParentId = $this->currentGeneralFolderId;
                    $generalFolders = GeneralLibraryFolder::query()
                        ->where(function ($folderQuery) use ($generalParentId): void {
                            $generalParentId === null
                                ? $folderQuery->whereNull('parent_id')
                                : $folderQuery->where('parent_id', $generalParentId);
                        })
                        ->active()
                        ->orderBy('sort_order')
                        ->orderBy('title')
                        ->get();

                    $generalResources = GeneralLibraryResource::query()
                        ->when(filled($this->search), fn ($resourceQuery) => $resourceQuery->where('title', 'like', '%'.$this->search.'%'))
                        ->where(function ($resourceQuery) use ($generalParentId): void {
                            $generalParentId === null
                                ? $resourceQuery->whereNull('general_library_folder_id')
                                : $resourceQuery->where('general_library_folder_id', $generalParentId);
                        })
                        ->active()
                        ->orderBy('sort_order')
                        ->orderBy('title')
                        ->get();
                }
            }
        }

        return view('livewire.teacher.library-picker', [
            'sections' => $sections,
            'resources' => $resources,
            'generalFolders' => $generalFolders,
            'generalResources' => $generalResources,
            'breadcrumbs' => $breadcrumbs,
            'currentSection' => $currentSection,
            'selectedCount' => count($this->selectedResourceIds()),
            'showLegacySources' => $this->showLegacySources,
            'legacyResources' => $legacyResources,
            'legacyTypes' => $legacyTypes,
            'legacyCollections' => $legacyCollections,
            'legacyFolderAvailable' => $legacyFolderAvailable,
            'vocabularyFolderAvailable' => $vocabularyFolderAvailable,
            'legacyCollectionChosen' => $this->legacyCollectionChosen,
            'selectedItems' => $selectedItems,
        ]);
    }

    private function selectedResourceIds(): array
    {
        return collect($this->selected)
            ->filter()
            ->keys()
            ->map(fn ($id): string => $this->normalizeSelectionKey((string) $id))
            ->values()
            ->all();
    }

    private function selectionKey(string $resourceId): string
    {
        return ctype_digit($resourceId)
            ? self::LOCAL_SELECTION_PREFIX.$resourceId
            : $resourceId;
    }

    private function normalizeSelectionKey(string $selectionKey): string
    {
        return str_starts_with($selectionKey, self::LOCAL_SELECTION_PREFIX)
            ? substr($selectionKey, strlen(self::LOCAL_SELECTION_PREFIX))
            : $selectionKey;
    }

    private function selectedResourceSummaries(LegacyLibraryTaskResourceCatalog $legacyCatalog): array
    {
        $user = Auth::user();
        if ($this->subjectId === null || $user === null) {
            return [];
        }

        $ids = $this->selectedResourceIds();
        if ($ids === []) {
            return [];
        }

        $localIds = collect($ids)
            ->filter(fn (string $id): bool => ctype_digit($id))
            ->map(fn (string $id): int => (int) $id)
            ->values()
            ->all();

        $legacyIds = collect($ids)
            ->reject(fn (string $id): bool => ctype_digit($id))
            ->reject(fn (string $id): bool => app(GeneralLibraryAttachmentAdapter::class)->isGeneralLibrarySelection($id))
            ->values()
            ->all();
        $generalIds = collect($ids)
            ->filter(fn (string $id): bool => str_starts_with($id, GeneralLibraryAttachmentAdapter::GENERAL_PREFIX))
            ->map(fn (string $id): int => (int) substr($id, strlen(GeneralLibraryAttachmentAdapter::GENERAL_PREFIX)))
            ->filter()
            ->values()
            ->all();
        $localItems = collect(LibraryResource::query()
            ->with('section:id,title')
            ->whereIn('id', $localIds)
            ->where('owner_user_id', (int) $user->id)
            ->where('subject_id', $this->subjectId)
            ->get()
            ->all())
            ->mapWithKeys(fn (LibraryResource $resource): array => [
                $this->selectionKey((string) $resource->id) => [
                    'id' => (string) $resource->id,
                    'title' => (string) $resource->title,
                    'meta' => (string) $resource->resource_type,
                    'context' => (string) ($resource->section?->title ?? 'My Library'),
                ],
            ]);

        $legacyItems = collect($legacyCatalog->findManyForSubject($user, $this->subjectId, $legacyIds))
            ->mapWithKeys(fn (array $resource): array => [
                (string) $resource['id'] => [
                    'id' => (string) $resource['id'],
                    'title' => (string) $resource['title'],
                    'meta' => ($resource['source_type'] ?? '') === SeriesLibrarySourceResolver::SOURCE_VOCABULARY_LIST
                        ? 'Vocab Games lesson'
                        : str_replace('_', ' ', (string) $resource['source_type']),
                    'context' => (string) ($resource['description'] ?: 'Legacy Library'),
                ],
            ]);

        $generalItems = Schema::hasTable('general_library_resources')
            ? GeneralLibraryResource::query()
            ->whereIn('id', $generalIds)
            ->active()
            ->get()
            ->mapWithKeys(fn (GeneralLibraryResource $resource): array => [
                GeneralLibraryAttachmentAdapter::GENERAL_PREFIX.$resource->id => [
                    'id' => GeneralLibraryAttachmentAdapter::GENERAL_PREFIX.$resource->id,
                    'title' => (string) $resource->title,
                    'meta' => (string) $resource->resource_type,
                    'context' => (string) ($resource->folder?->title ?? 'Shared Library'),
                ],
            ])
            : collect();
        $items = $localItems->union($generalItems)->union($legacyItems);

        return collect($ids)
            ->map(fn (string $id): ?array => $items->get($this->selectionKey($id)))
            ->filter()
            ->values()
            ->all();
    }

    private function resolveSection(int $sectionId): LibrarySection
    {
        return LibrarySection::query()
            ->whereKey($sectionId)
            ->where('owner_user_id', Auth::id())
            ->where('subject_id', $this->subjectId)
            ->firstOrFail();
    }

    private function findVocabularyCollectionSummary(int $folderId): ?array
    {
        if ($this->subjectId === null || Auth::user() === null) {
            return null;
        }

        $collection = collect(app(SeriesLibrarySourceResolver::class)->collectionsForType(
            SeriesLibrarySourceResolver::TYPE_VOCABULARY,
            (int) Auth::id(),
            $this->subjectId
        ))
            ->first(fn ($collection): bool => (int) ($collection->id ?? 0) === $folderId);

        if ($collection === null) {
            return null;
        }

        return [
            'key' => SeriesLibrarySourceResolver::TYPE_VOCABULARY.':'.$collection->id,
            'type' => $collection->type,
            'id' => $collection->id,
            'title' => $collection->title,
            'description' => $collection->description,
            'selectable' => $collection->selectable,
            'blocked_reason' => $collection->blockedReason,
            'parent_id' => $collection->parentId,
            'direct_resource_count' => $collection->directResourceCount,
            'tree_resource_count' => $collection->treeResourceCount,
            'child_folder_count' => $collection->childFolderCount,
        ];
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
}
