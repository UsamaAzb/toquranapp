<?php

namespace App\Services\Library;

use App\Models\LibraryResource;
use Illuminate\Support\Collection;

trait ResolvesLibraryAttachmentAttributes
{
    protected function attachmentAttributesForResourceId(string $resourceId, int $ownerUserId, int $subjectId): ?array
    {
        $generalAttributes = app(GeneralLibraryAttachmentSnapshotter::class)
            ->snapshotAttributesForSelection($resourceId, $ownerUserId);

        if ($generalAttributes !== null) {
            return $generalAttributes;
        }

        if (is_numeric($resourceId)) {
            $resource = $this->eligibleResources([(int) $resourceId], $ownerUserId, $subjectId)->first();

            return $resource instanceof LibraryResource
                ? LibraryResourcePayload::forSnapshot($resource)
                : null;
        }

        return null;
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
