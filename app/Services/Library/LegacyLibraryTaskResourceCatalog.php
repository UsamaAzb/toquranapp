<?php

namespace App\Services\Library;

use App\Models\User;
use App\Services\SeriesLibrarySourceResolver;
use App\Support\BookingSubjectProvisioning;
use App\Support\SeriesTasks\SeriesLibraryCollection;
use App\Support\SeriesTasks\SeriesLibraryItem;
use Illuminate\Support\Str;

class LegacyLibraryTaskResourceCatalog
{
    public function __construct(
        private readonly SeriesLibrarySourceResolver $sourceResolver,
    ) {}

    /**
     * @return array<int, array{type: string, title: string, count: int}>
     */
    public function collectionTypesForSubject(
        User $user,
        int $subjectId,
        ?string $search = null,
        bool $includeVocabulary = false
    ): array
    {
        if (! $this->canUse($user, $subjectId)) {
            return [];
        }

        $term = $this->searchTerm($search);

        return collect($this->collectionTypes($includeVocabulary))
            ->flatMap(fn (string $type): array => $this->sourceResolver->collectionsForType(
                $type,
                (int) $user->id,
                $subjectId
            ))
            ->filter(fn (SeriesLibraryCollection $collection): bool => $collection->selectable)
            ->when($term !== '', fn ($items) => $items->filter(
                fn (SeriesLibraryCollection $collection): bool => str_contains($this->searchText($collection->type.' '.$collection->title.' '.$collection->description), $term)
            ))
            ->groupBy('type')
            ->map(fn ($collections, string $type): array => [
                'type' => $type,
                'title' => $this->collectionTypeTitle($type),
                'count' => $collections->count(),
            ])
            ->sortBy('title')
            ->values()
            ->all();
    }

    public function hasLegacyCollectionsForSubject(User $user, int $subjectId): bool
    {
        return $this->collectionTypesForSubject($user, $subjectId) !== [];
    }

    public function hasVocabularyCollectionsForSubject(User $user, int $subjectId): bool
    {
        if (! $this->canUse($user, $subjectId)) {
            return false;
        }

        return collect($this->sourceResolver->collectionsForType(
            SeriesLibrarySourceResolver::TYPE_VOCABULARY,
            (int) $user->id,
            $subjectId
        ))
            ->contains(fn (SeriesLibraryCollection $collection): bool => $collection->selectable || $collection->childFolderCount > 0);
    }

    /**
     * @return array<int, array{key: string, type: string, id: int|null, title: string, description: string|null}>
     */
    public function collectionsForSubject(
        User $user,
        int $subjectId,
        string $type,
        ?string $search = null,
        ?int $parentId = null
    ): array
    {
        if (! $this->canUse($user, $subjectId)) {
            return [];
        }

        $term = $this->searchTerm($search);
        $isVocabulary = $type === SeriesLibrarySourceResolver::TYPE_VOCABULARY;

        return collect($this->sourceResolver->collectionsForType($type, (int) $user->id, $subjectId, parentId: $parentId))
            ->filter(fn (SeriesLibraryCollection $collection): bool => ! $isVocabulary
                || $parentId !== null
                || $collection->parentId === null)
            ->filter(fn (SeriesLibraryCollection $collection): bool => $isVocabulary
                ? ($collection->selectable || $collection->childFolderCount > 0)
                : $collection->selectable)
            ->when($term !== '', fn ($items) => $items->filter(
                fn (SeriesLibraryCollection $collection): bool => str_contains($this->searchText($collection->title.' '.$collection->description), $term)
            ))
            ->map(fn (SeriesLibraryCollection $collection): array => [
                'key' => $this->collectionKey($collection->type, $collection->id),
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
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: string, source_type: string, source_id: int, title: string, description: string|null, type: string, url: string|null}>
     */
    public function itemsForSubjectCollection(
        User $user,
        int $subjectId,
        string $collectionType,
        ?int $collectionId,
        ?string $search = null
    ): array {
        if (
            ! $this->canUse($user, $subjectId)
            || ! $this->sourceResolver->sourceIsSelectable($collectionType, $collectionId, (int) $user->id, $subjectId)
        ) {
            return [];
        }

        $term = $this->searchTerm($search);

        return collect($this->sourceResolver->orderedItems($collectionType, $collectionId, (int) $user->id, $subjectId))
            ->filter(fn (SeriesLibraryItem $item): bool => $this->hasSafeDeliveryTarget($item))
            ->when($term !== '', fn ($items) => $items->filter(
                fn (SeriesLibraryItem $item): bool => str_contains($this->searchText($item->title.' '.$item->summary), $term)
            ))
            ->map(fn (SeriesLibraryItem $item): array => $this->itemArray($item))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $ids
     * @return array<int, SeriesLibraryItem>
     */
    public function resolveItemsForSubject(User $user, int $subjectId, array $ids): array
    {
        if (! $this->canUse($user, $subjectId)) {
            return [];
        }

        return collect($ids)
            ->map(fn (string $id): ?SeriesLibraryItem => $this->resolveId($id, (int) $user->id))
            ->filter(fn (?SeriesLibraryItem $item): bool => $item !== null && $this->hasSafeDeliveryTarget($item))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $ids
     * @return array<int, array{id: string, source_type: string, source_id: int, title: string, description: string|null, type: string, url: string|null}>
     */
    public function findManyForSubject(User $user, int $subjectId, array $ids): array
    {
        return collect($this->resolveItemsForSubject($user, $subjectId, $ids))
            ->map(fn (SeriesLibraryItem $item): array => $this->itemArray($item))
            ->all();
    }

    public function itemId(SeriesLibraryItem $item): string
    {
        return 'series__'.$item->sourceType.'__'.$item->sourceId;
    }

    public function collectionKey(string $type, ?int $id): string
    {
        return $type.':'.($id ?? 'root');
    }

    public function parseCollectionKey(string $key): ?array
    {
        [$type, $id] = array_pad(explode(':', $key, 2), 2, null);

        if (! is_string($type) || $type === '') {
            return null;
        }

        return [
            'type' => $type,
            'id' => $id === 'root' || $id === null ? null : (int) $id,
        ];
    }

    private function resolveId(string $id, ?int $ownerUserId = null): ?SeriesLibraryItem
    {
        if (! preg_match('/^series__(.+)__([0-9]+)$/', $id, $matches)) {
            return null;
        }

        return $this->sourceResolver->resolveItem($matches[1], (int) $matches[2], $ownerUserId);
    }

    private function hasSafeDeliveryTarget(SeriesLibraryItem $item): bool
    {
        return $item->sourceType === SeriesLibrarySourceResolver::SOURCE_VOCABULARY_LIST
            || $item->hasSafeDeliveryTarget();
    }

    private function itemArray(SeriesLibraryItem $item): array
    {
        return [
            'id' => $this->itemId($item),
            'source_type' => $item->sourceType,
            'source_id' => $item->sourceId,
            'title' => $item->title,
            'description' => $item->summary,
            'type' => $item->mediaType ?: 'link',
            'url' => $item->mediaPath ?: $item->url,
        ];
    }

    private function canUse(User $user, int $subjectId): bool
    {
        return app(LegacyLibraryAccessService::class)->canAccessLegacyLibrary($user)
            && $this->isLanguageLiteratureSubject($subjectId);
    }

    private function searchTerm(?string $search): string
    {
        return $this->searchText((string) $search);
    }

    private function searchText(?string $text): string
    {
        return Str::of((string) $text)->lower()->squish()->toString();
    }

    private function isLanguageLiteratureSubject(int $subjectId): bool
    {
        return $subjectId === BookingSubjectProvisioning::SUBJECT_LANGUAGE_AND_LITERATURE;
    }

    private function collectionTypeTitle(string $type): string
    {
        return match ($type) {
            SeriesLibrarySourceResolver::TYPE_STORY => 'Listen & Read',
            SeriesLibrarySourceResolver::TYPE_LEVEL_UP => 'Level Up',
            SeriesLibrarySourceResolver::TYPE_NOTICE_NOTE => 'Notice & Note',
            SeriesLibrarySourceResolver::TYPE_TV_SERIES => 'TV Series',
            SeriesLibrarySourceResolver::TYPE_VOCABULARY => 'Vocabulary',
            default => Str::headline(str_replace('_', ' ', $type)),
        };
    }

    /**
     * @return array<int, string>
     */
    private function collectionTypes(bool $includeVocabulary): array
    {
        $types = [
            SeriesLibrarySourceResolver::TYPE_SAT,
            SeriesLibrarySourceResolver::TYPE_STORY,
            SeriesLibrarySourceResolver::TYPE_LEVEL_UP,
            SeriesLibrarySourceResolver::TYPE_TV_SERIES,
            SeriesLibrarySourceResolver::TYPE_AUDIO_LEVEL,
            SeriesLibrarySourceResolver::TYPE_PEER_COACH,
            SeriesLibrarySourceResolver::TYPE_GRAMMAR,
            SeriesLibrarySourceResolver::TYPE_NOTICE_NOTE,
            SeriesLibrarySourceResolver::TYPE_BACKGROUND,
        ];

        if ($includeVocabulary) {
            $types[] = SeriesLibrarySourceResolver::TYPE_VOCABULARY;
        }

        return $types;
    }
}
