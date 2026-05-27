<?php

namespace App\Services;

use App\Models\LibraryResource;
use App\Models\LibrarySection;
use App\Models\VocabularySet;
use App\Services\Library\LibraryResourcePayload;
use App\Support\SeriesTasks\SeriesLibraryCollection;
use App\Support\SeriesTasks\SeriesLibraryItem;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeriesLibrarySourceResolver
{
    public const TYPE_SAT = 'sat';

    public const TYPE_STORY = 'story';

    public const TYPE_LEVEL_UP = 'level_up';

    public const TYPE_TV_SERIES = 'tv_series';

    public const TYPE_AUDIO_LEVEL = 'audio_level';

    public const TYPE_PEER_COACH = 'peer_coach';

    public const TYPE_GRAMMAR = 'grammar';

    public const TYPE_NOTICE_NOTE = 'notice_note';

    public const TYPE_BACKGROUND = 'background';

    public const TYPE_LIBRARY_SECTION = 'library_section';

    public const TYPE_VOCABULARY = 'vocabulary';

    public const SOURCE_VOCABULARY_LIST = 'vocabulary_list';

    /** @var array<string, array<int, SeriesLibraryCollection>> */
    private array $vocabularyCollectionCache = [];

    /** @return array<int, SeriesLibraryCollection> */
    public function collectionsForType(
        string $type,
        ?int $ownerUserId = null,
        ?int $subjectId = null,
        bool $topLevelOnly = false,
        ?int $parentId = null
    ): array {
        return match ($type) {
            self::TYPE_LIBRARY_SECTION => $this->librarySectionCollections($ownerUserId, $subjectId, $topLevelOnly, $parentId),
            self::TYPE_VOCABULARY => $this->vocabularyCollections($ownerUserId, $parentId),
            self::TYPE_SAT => $this->satCollections(),
            self::TYPE_STORY => $this->storyCollections(),
            self::TYPE_LEVEL_UP => $this->levelUpCollections(),
            self::TYPE_TV_SERIES => $this->tvSeriesCollections(),
            self::TYPE_AUDIO_LEVEL => $this->audioCollections(),
            self::TYPE_PEER_COACH => $this->hierarchicalCollections(self::TYPE_PEER_COACH, 'peer_coach', 'Peer Coach activity list'),
            self::TYPE_GRAMMAR => $this->hierarchicalCollections(self::TYPE_GRAMMAR, 'grammar', 'Grammar activity list'),
            self::TYPE_NOTICE_NOTE => $this->flatCollection(self::TYPE_NOTICE_NOTE, 'notice_note', 'Notice & Note', 'Close-reading signposts'),
            self::TYPE_BACKGROUND => $this->hierarchicalCollections(self::TYPE_BACKGROUND, 'background', 'Background reading list'),
            default => [],
        };
    }

    /** @return array<int, SeriesLibraryCollection> */
    public function allCollections(?int $ownerUserId = null, ?int $subjectId = null, ?int $libraryParentId = null): array
    {
        return array_merge(
            $this->collectionsForType(
                self::TYPE_LIBRARY_SECTION,
                $ownerUserId,
                $subjectId,
                topLevelOnly: $libraryParentId === null,
                parentId: $libraryParentId
            ),
            $this->collectionsForType(self::TYPE_VOCABULARY, $ownerUserId, $subjectId),
            $this->collectionsForType(self::TYPE_SAT),
            $this->collectionsForType(self::TYPE_STORY),
            $this->collectionsForType(self::TYPE_LEVEL_UP),
            $this->collectionsForType(self::TYPE_TV_SERIES),
            $this->collectionsForType(self::TYPE_AUDIO_LEVEL),
            $this->collectionsForType(self::TYPE_PEER_COACH),
            $this->collectionsForType(self::TYPE_GRAMMAR),
            $this->collectionsForType(self::TYPE_NOTICE_NOTE),
            $this->collectionsForType(self::TYPE_BACKGROUND),
        );
    }

    /** @return array<int, SeriesLibraryItem> */
    public function orderedItems(
        string $collectionType,
        ?int $collectionId,
        ?int $ownerUserId = null,
        ?int $subjectId = null
    ): array
    {
        return match ($collectionType) {
            self::TYPE_LIBRARY_SECTION => $collectionId ? $this->libraryResourceItems($collectionId) : [],
            self::TYPE_VOCABULARY => $collectionId ? $this->vocabularyItems($collectionId, $ownerUserId) : [],
            self::TYPE_SAT => $collectionId ? $this->satItems($collectionId) : [],
            self::TYPE_STORY => $collectionId ? $this->storyItems($collectionId) : [],
            self::TYPE_LEVEL_UP => $this->levelUpItems(),
            self::TYPE_TV_SERIES => $collectionId ? $this->tvSeriesItems($collectionId) : [],
            self::TYPE_AUDIO_LEVEL => $collectionId ? $this->audioItems($collectionId) : [],
            self::TYPE_PEER_COACH => $collectionId ? $this->hierarchicalItems('peer_coach', $collectionId, 'peer_coach') : [],
            self::TYPE_GRAMMAR => $collectionId ? $this->hierarchicalItems('grammar', $collectionId, 'grammar') : [],
            self::TYPE_NOTICE_NOTE => $this->flatItems('notice_note', 'notice_note'),
            self::TYPE_BACKGROUND => $collectionId ? $this->hierarchicalItems('background', $collectionId, 'background') : [],
            default => [],
        };
    }

    public function resolveItem(string $sourceType, int $sourceId, ?int $ownerUserId = null): ?SeriesLibraryItem
    {
        return match ($sourceType) {
            'library_resource' => $this->resolveLibraryResourceItem($sourceId),
            self::SOURCE_VOCABULARY_LIST => $this->resolveVocabularyItem($sourceId, $ownerUserId),
            'sat' => $this->resolveSatItem($sourceId),
            'story_chapter' => $this->resolveStoryChapterItem($sourceId),
            'level_up' => $this->resolveLevelUpItem($sourceId),
            'series_episode' => $this->resolveSeriesEpisodeItem($sourceId),
            'audio_unit' => $this->resolveAudioUnitItem($sourceId),
            'audio_lesson' => $this->resolveAudioLessonItem($sourceId),
            'peer_coach' => $this->resolveHierarchicalItem('peer_coach', $sourceId, 'peer_coach'),
            'grammar' => $this->resolveHierarchicalItem('grammar', $sourceId, 'grammar'),
            'notice_note' => $this->resolveFlatItem('notice_note', $sourceId, 'notice_note'),
            'background' => $this->resolveHierarchicalItem('background', $sourceId, 'background'),
            default => null,
        };
    }

    public function sourceIsSelectable(string $type, ?int $id, ?int $ownerUserId = null, ?int $subjectId = null): bool
    {
        if ($type === self::TYPE_VOCABULARY) {
            return $id !== null && $this->vocabularyCollectionIsSelectable((int) $id, $ownerUserId);
        }

        return collect($this->collectionsForType($type, $ownerUserId, $subjectId))
            ->contains(fn (SeriesLibraryCollection $collection): bool => $collection->id === $id && $collection->selectable);
    }

    public function itemBelongsToCollection(
        string $collectionType,
        ?int $collectionId,
        string $sourceType,
        int $sourceId
    ): bool {
        return match ($collectionType) {
            self::TYPE_LIBRARY_SECTION => $sourceType === 'library_resource'
                && $collectionId !== null
                && $this->libraryResourceBelongsToSection($sourceId, $collectionId),
            self::TYPE_VOCABULARY => $sourceType === self::SOURCE_VOCABULARY_LIST
                && $collectionId !== null
                && $this->vocabularyItemBelongsToCollection($sourceId, $collectionId),
            self::TYPE_SAT => $sourceType === 'sat'
                && $collectionId !== null
                && $this->satItemBelongsToParent($sourceId, $collectionId),
            self::TYPE_STORY => $sourceType === 'story_chapter'
                && $collectionId !== null
                && $this->storyChapterBelongsToStory($sourceId, $collectionId),
            self::TYPE_LEVEL_UP => $sourceType === 'level_up'
                && $collectionId === null
                && $this->levelUpItemExists($sourceId),
            self::TYPE_TV_SERIES => $sourceType === 'series_episode'
                && $collectionId !== null
                && $this->seriesEpisodeBelongsToSeason($sourceId, $collectionId),
            self::TYPE_AUDIO_LEVEL => in_array($sourceType, ['audio_unit', 'audio_lesson'], true)
                && $collectionId !== null
                && $this->audioItemBelongsToUnit($sourceType, $sourceId, $collectionId),
            self::TYPE_PEER_COACH => $sourceType === 'peer_coach'
                && $collectionId !== null
                && $this->hierarchicalItemBelongsToParent('peer_coach', $sourceId, $collectionId),
            self::TYPE_GRAMMAR => $sourceType === 'grammar'
                && $collectionId !== null
                && $this->hierarchicalItemBelongsToParent('grammar', $sourceId, $collectionId),
            self::TYPE_NOTICE_NOTE => $sourceType === 'notice_note'
                && $collectionId === null
                && $this->flatItemExists('notice_note', $sourceId),
            self::TYPE_BACKGROUND => $sourceType === 'background'
                && $collectionId !== null
                && $this->hierarchicalItemBelongsToParent('background', $sourceId, $collectionId),
            default => false,
        };
    }

    /** @return array<int, SeriesLibraryCollection> */
    private function vocabularyCollections(?int $ownerUserId = null, ?int $parentId = null): array
    {
        if (! Schema::hasTable('vocabulary_sets')) {
            return [];
        }

        $cacheKey = (string) ($ownerUserId ?? 'all');

        if (array_key_exists($cacheKey, $this->vocabularyCollectionCache)) {
            return $this->filterVocabularyCollectionsByParent($this->vocabularyCollectionCache[$cacheKey], $parentId);
        }

        $sets = VocabularySet::query()
            ->when($ownerUserId !== null, fn ($query) => $query->visibleToTeachers($ownerUserId))
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'parent_id', 'title', 'description', 'node_type', 'sort_order', 'visibility', 'owner_user_id']);

        $folders = $sets
            ->filter(fn (VocabularySet $set): bool => $set->isFolder())
            ->values();
        $directLessonCounts = $sets
            ->filter(fn (VocabularySet $set): bool => $set->isPlayable())
            ->groupBy(fn (VocabularySet $set): int => (int) ($set->parent_id ?? 0))
            ->map(fn ($items): int => $items->count());
        $childrenByParent = $folders->groupBy(fn (VocabularySet $set): int => (int) ($set->parent_id ?? 0));
        $treeLessonCountCache = [];
        $treeLessonCount = function (int $folderId) use (&$treeLessonCount, &$treeLessonCountCache, $childrenByParent, $directLessonCounts): int {
            if (array_key_exists($folderId, $treeLessonCountCache)) {
                return $treeLessonCountCache[$folderId];
            }

            $count = (int) ($directLessonCounts[$folderId] ?? 0);

            foreach ($childrenByParent->get($folderId, collect()) as $childFolder) {
                $count += $treeLessonCount((int) $childFolder->id);
            }

            $treeLessonCountCache[$folderId] = $count;

            return $count;
        };

        $collections = $folders
            ->map(function (VocabularySet $set) use ($childrenByParent, $directLessonCounts, $treeLessonCount): SeriesLibraryCollection {
                $directLessons = (int) ($directLessonCounts[(int) $set->id] ?? 0);
                $treeLessons = $treeLessonCount((int) $set->id);
                $childFolders = $childrenByParent
                    ->get((int) $set->id, collect())
                    ->filter(fn (VocabularySet $child): bool => $child->isFolder())
                    ->count();
                $selectable = $directLessons > 0 && $childFolders === 0;

                return new SeriesLibraryCollection(
                    self::TYPE_VOCABULARY,
                    (int) $set->id,
                    (string) $set->title,
                    $directLessons > 0
                        ? $directLessons.' vocabulary lessons here'
                        : $treeLessons.' vocabulary lessons inside',
                    $selectable,
                    $selectable
                        ? null
                        : ($childFolders > 0
                            ? 'Open this folder and choose a child folder that contains lessons directly.'
                            : 'No playable vocabulary lessons'),
                    $set->parent_id === null ? null : (int) $set->parent_id,
                    $directLessons,
                    $treeLessons,
                    $childFolders
                );
            })
            ->all();

        $this->vocabularyCollectionCache[$cacheKey] = $collections;

        return $this->filterVocabularyCollectionsByParent($collections, $parentId);
    }

    /**
     * @param  array<int, SeriesLibraryCollection>  $collections
     * @return array<int, SeriesLibraryCollection>
     */
    private function filterVocabularyCollectionsByParent(array $collections, ?int $parentId): array
    {
        if ($parentId === null) {
            return $collections;
        }

        return collect($collections)
            ->filter(fn (SeriesLibraryCollection $collection): bool => (int) ($collection->parentId ?? 0) === (int) $parentId)
            ->values()
            ->all();
    }

    private function vocabularyCollectionIsSelectable(int $folderId, ?int $ownerUserId = null): bool
    {
        if (! Schema::hasTable('vocabulary_sets')) {
            return false;
        }

        $folderExists = VocabularySet::query()
            ->folders()
            ->whereKey($folderId)
            ->when($ownerUserId !== null, fn ($query) => $query->visibleToTeachers($ownerUserId))
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)
            ->exists();

        if (! $folderExists) {
            return false;
        }

        $hasChildFolders = VocabularySet::query()
            ->folders()
            ->where('parent_id', $folderId)
            ->when($ownerUserId !== null, fn ($query) => $query->visibleToTeachers($ownerUserId))
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)
            ->exists();

        if ($hasChildFolders) {
            return false;
        }

        return VocabularySet::query()
            ->playable()
            ->where('parent_id', $folderId)
            ->when($ownerUserId !== null, fn ($query) => $query->visibleToTeachers($ownerUserId))
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)
            ->exists();
    }

    /** @return array<int, SeriesLibraryItem> */
    private function vocabularyItems(int $folderId, ?int $ownerUserId = null): array
    {
        if (! Schema::hasTable('vocabulary_sets')) {
            return [];
        }

        $folder = VocabularySet::query()
            ->folders()
            ->whereKey($folderId)
            ->when($ownerUserId !== null, fn ($query) => $query->visibleToTeachers($ownerUserId))
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)
            ->first();

        if (! $folder instanceof VocabularySet) {
            return [];
        }

        return $this->directVocabularyLessons($folder, $ownerUserId)
            ->map(fn (VocabularySet $set): SeriesLibraryItem => $this->vocabularySetToItem($set))
            ->all();
    }

    private function resolveVocabularyItem(int $id, ?int $ownerUserId = null): ?SeriesLibraryItem
    {
        if (! Schema::hasTable('vocabulary_sets')) {
            return null;
        }

        $set = VocabularySet::query()
            ->playable()
            ->whereKey($id)
            ->when($ownerUserId !== null, fn ($query) => $query->visibleToTeachers($ownerUserId))
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)
            ->first();

        return $set instanceof VocabularySet ? $this->vocabularySetToItem($set) : null;
    }

    private function vocabularySetToItem(VocabularySet $set): SeriesLibraryItem
    {
        return new SeriesLibraryItem(
            self::SOURCE_VOCABULARY_LIST,
            (int) $set->id,
            (string) $set->title,
            $this->vocabularySetPath($set),
            'vocabulary://set/'.(int) $set->id,
            null,
            'vocabulary_game'
        );
    }

    private function vocabularySetPath(VocabularySet $set): string
    {
        $titles = [(string) $set->title];
        $parent = $set->parent;

        while ($parent instanceof VocabularySet) {
            array_unshift($titles, (string) $parent->title);
            $parent = $parent->parent;
        }

        return implode(' / ', $titles);
    }

    private function vocabularyItemBelongsToCollection(int $sourceId, int $folderId): bool
    {
        $folder = VocabularySet::query()
            ->folders()
            ->whereKey($folderId)
            ->first();

        if (! $folder instanceof VocabularySet) {
            return false;
        }

        return $this->directVocabularyLessons($folder)
            ->contains(fn (VocabularySet $set): bool => (int) $set->id === $sourceId);
    }

    /** @return \Illuminate\Support\Collection<int, VocabularySet> */
    private function directVocabularyLessons(VocabularySet $folder, ?int $ownerUserId = null)
    {
        return VocabularySet::query()
            ->where('parent_id', (int) $folder->id)
            ->playable()
            ->when($ownerUserId !== null, fn ($query) => $query->visibleToTeachers($ownerUserId))
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->values();
    }

    /** @return array<int, SeriesLibraryCollection> */
    private function librarySectionCollections(
        ?int $ownerUserId,
        ?int $subjectId,
        bool $topLevelOnly = false,
        ?int $parentId = null
    ): array {
        if (
            $ownerUserId === null
            || $subjectId === null
            || ! Schema::hasTable('library_sections')
            || ! Schema::hasTable('library_resources')
        ) {
            return [];
        }

        $sections = LibrarySection::query()
            ->where('owner_user_id', $ownerUserId)
            ->where('subject_id', $subjectId)
            ->where('status', LibrarySection::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $activeResourceCounts = LibraryResource::query()
            ->whereIn('library_section_id', $sections->pluck('id')->all())
            ->where('status', LibraryResource::STATUS_ACTIVE)
            ->selectRaw('library_section_id, count(*) as aggregate')
            ->groupBy('library_section_id')
            ->pluck('aggregate', 'library_section_id');
        $childrenByParent = $sections->groupBy(fn (LibrarySection $section): int => (int) ($section->parent_id ?? 0));

        return $sections
            ->when($topLevelOnly, fn ($collection) => $collection->filter(
                fn (LibrarySection $section): bool => $section->parent_id === null
            ))
            ->when($parentId !== null, fn ($collection) => $collection->filter(
                fn (LibrarySection $section): bool => (int) $section->parent_id === (int) $parentId
            ))
            ->map(function (LibrarySection $section) use ($activeResourceCounts, $childrenByParent): SeriesLibraryCollection {
                $sectionTreeIds = $this->collectSectionTreeIds((int) $section->id, $childrenByParent);
                $directResourcesCount = (int) ($activeResourceCounts[(int) $section->id] ?? 0);
                $treeResourcesCount = collect($sectionTreeIds)
                    ->sum(fn (int $sectionId): int => (int) ($activeResourceCounts[$sectionId] ?? 0));
                $childFolderCount = $childrenByParent->get((int) $section->id, collect())->count();

                return new SeriesLibraryCollection(
                    self::TYPE_LIBRARY_SECTION,
                    (int) $section->id,
                    (string) $section->title,
                    $section->description ?: $this->librarySectionDescription($directResourcesCount, $treeResourcesCount, $childFolderCount),
                    $directResourcesCount > 0,
                    'Empty folder',
                    $section->parent_id === null ? null : (int) $section->parent_id,
                    $directResourcesCount,
                    $treeResourcesCount,
                    $childFolderCount
                );
            })
            ->all();
    }

    /** @return array<int, SeriesLibraryItem> */
    private function libraryResourceItems(int $sectionId): array
    {
        if (! Schema::hasTable('library_sections') || ! Schema::hasTable('library_resources')) {
            return [];
        }

        if (! $this->activeLibrarySectionExists($sectionId)) {
            return [];
        }

        return LibraryResource::query()
            ->where('library_section_id', $sectionId)
            ->where('status', LibraryResource::STATUS_ACTIVE)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (LibraryResource $resource): SeriesLibraryItem => $this->libraryResourceToItem($resource))
            ->all();
    }

    private function resolveLibraryResourceItem(int $id): ?SeriesLibraryItem
    {
        if (! Schema::hasTable('library_resources')) {
            return null;
        }

        $resource = LibraryResource::query()
            ->whereKey($id)
            ->where('status', LibraryResource::STATUS_ACTIVE)
            ->first();

        return $resource instanceof LibraryResource
            ? $this->libraryResourceToItem($resource)
            : null;
    }

    private function libraryResourceToItem(LibraryResource $resource): SeriesLibraryItem
    {
        $payload = LibraryResourcePayload::forSnapshot($resource);

        return new SeriesLibraryItem(
            'library_resource',
            (int) $resource->id,
            (string) $payload['title'],
            $payload['description'] ?: null,
            $payload['url'],
            $payload['path'],
            $payload['type'],
            $payload['file_size']
        );
    }

    /** @return array<int, SeriesLibraryCollection> */
    private function satCollections(): array
    {
        if (! Schema::hasTable('sat')) {
            return [];
        }

        return DB::table('sat')
            ->where(function ($query): void {
                $query->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'title'])
            ->map(fn ($row): SeriesLibraryCollection => new SeriesLibraryCollection(
                self::TYPE_SAT,
                (int) $row->id,
                (string) $row->title,
                'SAT practice list'
            ))
            ->all();
    }

    /** @return array<int, SeriesLibraryCollection> */
    private function storyCollections(): array
    {
        if (! Schema::hasTable('stories')) {
            return [];
        }

        return DB::table('stories')
            ->when(Schema::hasColumn('stories', 'active'), fn ($query) => $query->where('active', 1))
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'title', 'description'])
            ->map(fn ($row): SeriesLibraryCollection => new SeriesLibraryCollection(
                self::TYPE_STORY,
                (int) $row->id,
                (string) $row->title,
                $row->description !== null ? (string) $row->description : null
            ))
            ->all();
    }

    /** @return array<int, SeriesLibraryCollection> */
    private function levelUpCollections(): array
    {
        return [
            new SeriesLibraryCollection(
                self::TYPE_LEVEL_UP,
                null,
                'Level up Tutorials',
                'Flat tutorial list from Library'
            ),
        ];
    }

    /** @return array<int, SeriesLibraryCollection> */
    private function tvSeriesCollections(): array
    {
        if (! Schema::hasTable('series_seasons')) {
            return [];
        }

        return DB::table('series_seasons')
            ->orderBy('type_id')
            ->orderBy('id')
            ->get(['id', 'title', 'type_id'])
            ->map(fn ($row): SeriesLibraryCollection => new SeriesLibraryCollection(
                self::TYPE_TV_SERIES,
                (int) $row->id,
                (string) $row->title,
                $this->seriesFamily((int) $row->type_id).' season'
            ))
            ->all();
    }

    /** @return array<int, SeriesLibraryCollection> */
    private function audioCollections(): array
    {
        if (! Schema::hasTable('audio_units')) {
            return [];
        }

        return DB::table('audio_units')
            ->when(Schema::hasColumn('audio_units', 'active'), fn ($query) => $query->where('active', 1))
            ->orderBy('order')
            ->orderBy('id')
            ->get(['id', 'title'])
            ->map(fn ($row): SeriesLibraryCollection => new SeriesLibraryCollection(
                self::TYPE_AUDIO_LEVEL,
                (int) $row->id,
                (string) $row->title,
                'Audio course unit',
                false,
                'Audio course routes are student-account scoped; keep this source diagnostics-only until owner approves safe delivery.'
            ))
            ->all();
    }

    /** @return array<int, SeriesLibraryCollection> */
    private function hierarchicalCollections(string $type, string $table, string $description): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return DB::table($table)
            ->where(function ($query): void {
                $query->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'title'])
            ->map(fn ($row): SeriesLibraryCollection => new SeriesLibraryCollection(
                $type,
                (int) $row->id,
                (string) $row->title,
                $description
            ))
            ->all();
    }

    /** @return array<int, SeriesLibraryCollection> */
    private function flatCollection(string $type, string $table, string $title, string $description): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return [
            new SeriesLibraryCollection($type, null, $title, $description),
        ];
    }

    /** @return array<int, SeriesLibraryItem> */
    private function satItems(int $parentId): array
    {
        if (! Schema::hasTable('sat')) {
            return [];
        }

        $parent = DB::table('sat')->where('id', $parentId)->first(['id', 'slug']);

        if (! $parent) {
            return [];
        }

        return DB::table('sat')
            ->where('parent_id', $parentId)
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'title', 'slug'])
            ->map(fn ($row): SeriesLibraryItem => new SeriesLibraryItem(
                'sat',
                (int) $row->id,
                (string) $row->title,
                null,
                url('course/sat/'.trim((string) $parent->slug, '/').'/'.trim((string) $row->slug, '/'))
            ))
            ->all();
    }

    /** @return array<int, SeriesLibraryItem> */
    private function storyItems(int $storyId): array
    {
        if (! Schema::hasTable('story_chapters')) {
            return [];
        }

        $story = $this->legacyParentRow('stories', $storyId);

        if (! $story) {
            return [];
        }

        return DB::table('story_chapters')
            ->where('story_id', $storyId)
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'title', 'slug', 'text', 'audio'])
            ->map(fn ($row): SeriesLibraryItem => new SeriesLibraryItem(
                'story_chapter',
                (int) $row->id,
                (string) $row->title,
                $this->excerpt($row->text ?? null),
                url('reading/listen-read/'.$this->legacyNestedSlug($story->slug ?? null, (string) $row->slug)),
                $row->audio ? ltrim((string) $row->audio, '/') : null,
                $row->audio ? 'audio' : null
            ))
            ->all();
    }

    /** @return array<int, SeriesLibraryItem> */
    private function levelUpItems(): array
    {
        if (! Schema::hasTable('level_up')) {
            return [];
        }

        return DB::table('level_up')
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'title', 'slug', 'iframe_link'])
            ->map(fn ($row): SeriesLibraryItem => new SeriesLibraryItem(
                'level_up',
                (int) $row->id,
                (string) $row->title,
                null,
                url('tutriols/level-up/'.trim((string) $row->slug, '/')),
                null,
                $row->iframe_link ? 'link' : null
            ))
            ->all();
    }

    /** @return array<int, SeriesLibraryItem> */
    private function tvSeriesItems(int $seasonId): array
    {
        if (! Schema::hasTable('series_episodes')) {
            return [];
        }

        $season = DB::table('series_seasons')->where('id', $seasonId)->first(['id', 'type_id']);

        if (! $season) {
            return [];
        }

        $base = $this->seriesBasePath((int) $season->type_id);

        return DB::table('series_episodes')
            ->where('series_season_id', $seasonId)
            ->when(Schema::hasColumn('series_episodes', 'active'), fn ($query) => $query->where('active', 1))
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'title', 'slug', 'subtitles'])
            ->map(fn ($row): SeriesLibraryItem => new SeriesLibraryItem(
                'series_episode',
                (int) $row->id,
                (string) $row->title,
                $this->excerpt($row->subtitles ?? null),
                url($base.'/'.trim((string) $row->slug, '/'))
            ))
            ->all();
    }

    /** @return array<int, SeriesLibraryItem> */
    private function audioItems(int $unitId): array
    {
        if (! Schema::hasTable('audio_lessons')) {
            return [];
        }

        return DB::table('audio_lessons')
            ->where('unit_id', $unitId)
            ->when(Schema::hasColumn('audio_lessons', 'active'), fn ($query) => $query->where('active', 1))
            ->orderBy('order')
            ->orderBy('id')
            ->get(['id', 'title', 'file', 'type'])
            ->map(fn ($row): SeriesLibraryItem => new SeriesLibraryItem(
                'audio_lesson',
                (int) $row->id,
                (string) $row->title,
                'Diagnostics only until Audio is owner-approved for Series delivery.',
                null,
                $row->file ? ltrim((string) $row->file, '/') : null,
                $row->type ? (string) $row->type : 'audio'
            ))
            ->all();
    }

    /** @return array<int, SeriesLibraryItem> */
    private function hierarchicalItems(string $table, int $parentId, string $sourceType): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        $parent = $this->legacyParentRow($table, $parentId);

        if (! $parent) {
            return [];
        }

        return DB::table($table)
            ->where('parent_id', $parentId)
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'title', 'slug'])
            ->map(fn ($row): SeriesLibraryItem => new SeriesLibraryItem(
                $sourceType,
                (int) $row->id,
                (string) $row->title,
                null,
                $this->legacyCourseUrl($sourceType, (string) $row->slug, $parent->slug ?? null)
            ))
            ->all();
    }

    /** @return array<int, SeriesLibraryItem> */
    private function flatItems(string $table, string $sourceType): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return $this->flatItemQuery($table)
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'title', 'slug'])
            ->map(fn ($row): SeriesLibraryItem => new SeriesLibraryItem(
                $sourceType,
                (int) $row->id,
                (string) $row->title,
                null,
                $this->legacyCourseUrl($sourceType, (string) $row->slug)
            ))
            ->all();
    }

    private function resolveSatItem(int $id): ?SeriesLibraryItem
    {
        if (! Schema::hasTable('sat')) {
            return null;
        }

        $item = DB::table('sat')->where('id', $id)->first(['id', 'parent_id']);

        if (! $item || ! $item->parent_id) {
            return null;
        }

        return collect($this->satItems((int) $item->parent_id))->firstWhere('sourceId', $id);
    }

    private function resolveStoryChapterItem(int $id): ?SeriesLibraryItem
    {
        if (! Schema::hasTable('story_chapters')) {
            return null;
        }

        $item = DB::table('story_chapters')->where('id', $id)->first(['id', 'story_id']);

        if (! $item) {
            return null;
        }

        return collect($this->storyItems((int) $item->story_id))->firstWhere('sourceId', $id);
    }

    private function resolveLevelUpItem(int $id): ?SeriesLibraryItem
    {
        return collect($this->levelUpItems())->firstWhere('sourceId', $id);
    }

    private function resolveSeriesEpisodeItem(int $id): ?SeriesLibraryItem
    {
        if (! Schema::hasTable('series_episodes')) {
            return null;
        }

        $item = DB::table('series_episodes')->where('id', $id)->first(['id', 'series_season_id']);

        if (! $item) {
            return null;
        }

        return collect($this->tvSeriesItems((int) $item->series_season_id))->firstWhere('sourceId', $id);
    }

    private function resolveAudioUnitItem(int $id): ?SeriesLibraryItem
    {
        if (! Schema::hasTable('audio_units')) {
            return null;
        }

        $unit = DB::table('audio_units')->where('id', $id)->first(['id', 'title']);

        if (! $unit) {
            return null;
        }

        return new SeriesLibraryItem(
            'audio_unit',
            (int) $unit->id,
            (string) $unit->title,
            'Diagnostics only until Audio is owner-approved for Series delivery.',
            null
        );
    }

    private function resolveAudioLessonItem(int $id): ?SeriesLibraryItem
    {
        if (! Schema::hasTable('audio_lessons')) {
            return null;
        }

        $lesson = DB::table('audio_lessons')->where('id', $id)->first(['id', 'unit_id']);

        if (! $lesson) {
            return null;
        }

        return collect($this->audioItems((int) $lesson->unit_id))->firstWhere('sourceId', $id);
    }

    private function resolveHierarchicalItem(string $table, int $id, string $sourceType): ?SeriesLibraryItem
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        $item = DB::table($table)->where('id', $id)->first(['id', 'parent_id']);

        if (! $item || ! $item->parent_id) {
            return null;
        }

        return collect($this->hierarchicalItems($table, (int) $item->parent_id, $sourceType))->firstWhere('sourceId', $id);
    }

    private function resolveFlatItem(string $table, int $id, string $sourceType): ?SeriesLibraryItem
    {
        return collect($this->flatItems($table, $sourceType))->firstWhere('sourceId', $id);
    }

    private function satItemBelongsToParent(int $sourceId, int $parentId): bool
    {
        return Schema::hasTable('sat')
            && DB::table('sat')
                ->where('id', $sourceId)
                ->where('parent_id', $parentId)
                ->exists();
    }

    private function libraryResourceBelongsToSection(int $sourceId, int $sectionId): bool
    {
        if (! $this->activeLibrarySectionExists($sectionId)) {
            return false;
        }

        return Schema::hasTable('library_resources')
            && LibraryResource::query()
                ->whereKey($sourceId)
                ->where('library_section_id', $sectionId)
                ->where('status', LibraryResource::STATUS_ACTIVE)
                ->exists();
    }

    private function librarySectionDescription(int $directResourcesCount, int $treeResourcesCount, int $childFolderCount): string
    {
        if ($childFolderCount > 0 && $directResourcesCount > 0) {
            return $directResourcesCount.' resources here, '.$childFolderCount.' subfolders';
        }

        if ($childFolderCount > 0) {
            return $childFolderCount.' subfolders, '.$treeResourcesCount.' resources inside';
        }

        return $directResourcesCount.' Library resources';
    }

    private function activeLibrarySectionExists(int $sectionId): bool
    {
        return Schema::hasTable('library_sections')
            && LibrarySection::query()
                ->whereKey($sectionId)
                ->where('status', LibrarySection::STATUS_ACTIVE)
                ->exists();
    }

    /** @return array<int, int> */
    private function collectSectionTreeIds(int $sectionId, $childrenByParent): array
    {
        $ids = [$sectionId];

        foreach ($childrenByParent->get($sectionId, collect()) as $childSection) {
            $ids = array_merge($ids, $this->collectSectionTreeIds((int) $childSection->id, $childrenByParent));
        }

        return $ids;
    }

    private function storyChapterBelongsToStory(int $sourceId, int $storyId): bool
    {
        return Schema::hasTable('story_chapters')
            && DB::table('story_chapters')
                ->where('id', $sourceId)
                ->where('story_id', $storyId)
                ->exists();
    }

    private function levelUpItemExists(int $sourceId): bool
    {
        return Schema::hasTable('level_up')
            && DB::table('level_up')
                ->where('id', $sourceId)
                ->exists();
    }

    private function seriesEpisodeBelongsToSeason(int $sourceId, int $seasonId): bool
    {
        return Schema::hasTable('series_episodes')
            && DB::table('series_episodes')
                ->where('id', $sourceId)
                ->where('series_season_id', $seasonId)
                ->exists();
    }

    private function audioItemBelongsToUnit(string $sourceType, int $sourceId, int $unitId): bool
    {
        if ($sourceType === 'audio_unit') {
            return Schema::hasTable('audio_units')
                && DB::table('audio_units')
                    ->where('id', $sourceId)
                    ->exists()
                && $sourceId === $unitId;
        }

        return Schema::hasTable('audio_lessons')
            && DB::table('audio_lessons')
                ->where('id', $sourceId)
                ->where('unit_id', $unitId)
                ->exists();
    }

    private function hierarchicalItemBelongsToParent(string $table, int $sourceId, int $parentId): bool
    {
        return Schema::hasTable($table)
            && DB::table($table)
                ->where('id', $sourceId)
                ->where('parent_id', $parentId)
                ->exists();
    }

    private function flatItemExists(string $table, int $sourceId): bool
    {
        return Schema::hasTable($table)
            && $this->flatItemQuery($table)
                ->where('id', $sourceId)
                ->exists();
    }

    private function flatItemQuery(string $table): Builder
    {
        $query = DB::table($table);

        if (Schema::hasColumn($table, 'parent_id')) {
            $query
                ->whereNotNull('parent_id')
                ->where('parent_id', '<>', 0);
        }

        return $query;
    }

    private function legacyCourseUrl(string $sourceType, string $slug, ?string $parentSlug = null): string
    {
        $slug = trim($slug, '/');

        return match ($sourceType) {
            'peer_coach' => url('peer-coach/'.$this->legacyNestedSlug($parentSlug, $slug)),
            'grammar' => url('course/grammar/'.$this->legacyNestedSlug($parentSlug, $slug)),
            'notice_note' => url('notice-note/'.$slug),
            'background' => url('background/'.(str_starts_with($slug, 'background/') ? substr($slug, 11) : $slug)),
            default => url($slug),
        };
    }

    private function legacyParentRow(string $table, int $id): ?object
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        $columns = ['id'];

        if (Schema::hasColumn($table, 'slug')) {
            $columns[] = 'slug';
        }

        return DB::table($table)->where('id', $id)->first($columns);
    }

    private function legacyNestedSlug(?string $parentSlug, string $childSlug): string
    {
        $childSlug = trim($childSlug, '/');

        if ($childSlug === '' || str_contains($childSlug, '/')) {
            return $childSlug;
        }

        $parentSlug = trim((string) $parentSlug, '/');

        if ($parentSlug === '') {
            return $childSlug;
        }

        return $parentSlug.'/'.$childSlug;
    }

    private function seriesFamily(int $typeId): string
    {
        return $typeId === 2 ? 'Avatar' : 'Friends';
    }

    private function seriesBasePath(int $typeId): string
    {
        return $typeId === 2 ? 'tv_series/avatar' : 'tv_series/friends';
    }

    private function excerpt(?string $text): ?string
    {
        $clean = trim(strip_tags((string) $text));

        if ($clean === '') {
            return null;
        }

        return mb_substr($clean, 0, 240);
    }
}
