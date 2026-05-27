<?php

namespace App\Services\Library;

use App\Models\LibraryResource;
use App\Models\User;
use App\Services\SeriesLibrarySourceResolver;
use App\Services\Vocabulary\VocabularyGameAttachmentBuilder;
use Illuminate\Support\Collection;

trait ResolvesLibraryAttachmentAttributes
{
    protected function attachmentAttributesForResourceId(string $resourceId, int $ownerUserId, int $subjectId): ?array
    {
        if (is_numeric($resourceId)) {
            $resource = $this->eligibleResources([(int) $resourceId], $ownerUserId, $subjectId)->first();

            return $resource instanceof LibraryResource
                ? LibraryResourcePayload::forSnapshot($resource)
                : null;
        }

        if (! str_starts_with($resourceId, 'series__')) {
            return null;
        }

        $owner = User::query()->find($ownerUserId);
        if ($owner === null) {
            return null;
        }

        $resource = collect(app(LegacyLibraryTaskResourceCatalog::class)->findManyForSubject(
            $owner,
            $subjectId,
            [$resourceId]
        ))->first();

        if (! is_array($resource)) {
            return null;
        }

        if (($resource['source_type'] ?? '') === SeriesLibrarySourceResolver::SOURCE_VOCABULARY_LIST) {
            return [
                'type' => 'link',
                'title' => 'Vocab Game: '.(string) $resource['title'],
                'description' => $resource['description'] ?: null,
                'path' => VocabularyGameAttachmentBuilder::sourcePath((int) $resource['source_id']),
                'url' => null,
                'file_size' => null,
            ];
        }

        return [
            'type' => 'link',
            'title' => (string) $resource['title'],
            'description' => $resource['description'] ?: null,
            'path' => null,
            'url' => (string) $resource['url'],
            'file_size' => null,
        ];
    }

    /**
     * @return Collection<int, LibraryResource>
     */
    protected function eligibleResources(array $resourceIds, int $ownerUserId, int $subjectId): Collection
    {
        $ids = collect($resourceIds)
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return collect();
        }

        $activeSectionIds = app(LibraryResourceQuery::class)->activeSectionIdsForOwner($ownerUserId, $subjectId);

        if ($activeSectionIds === []) {
            return collect();
        }

        return LibraryResource::query()
            ->whereIn('id', $ids)
            ->where('owner_user_id', $ownerUserId)
            ->where('subject_id', $subjectId)
            ->whereIn('library_section_id', $activeSectionIds)
            ->where('status', LibraryResource::STATUS_ACTIVE)
            ->get()
            ->sortBy(fn (LibraryResource $resource): int => array_search((int) $resource->id, $ids, true))
            ->values();
    }
}
