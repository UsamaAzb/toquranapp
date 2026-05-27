<?php

namespace App\Services\Library;

use App\Models\LibraryResource;
use App\Models\LibrarySection;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class LibraryResourceQuery
{
    public function sections(User $owner, int $subjectId, ?int $parentId = null, bool $activeOnly = true): Builder
    {
        return LibrarySection::query()
            ->ownedBy((int) $owner->id)
            ->forSubject($subjectId)
            ->when($activeOnly, fn (Builder $query) => $query->active())
            ->where(function (Builder $query) use ($parentId) {
                $parentId === null
                    ? $query->whereNull('parent_id')
                    : $query->where('parent_id', $parentId);
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->orderBy('id');
    }

    public function resources(
        User $owner,
        int $subjectId,
        ?int $sectionId = null,
        ?string $search = null,
        bool $activeOnly = true
    ): Builder {
        $activeSectionIds = $activeOnly ? $this->activeSectionIdsForOwner((int) $owner->id, $subjectId) : null;

        return LibraryResource::query()
            ->with('section')
            ->ownedBy((int) $owner->id)
            ->forSubject($subjectId)
            ->when($activeOnly, fn (Builder $query) => $query->active())
            ->when(
                $activeSectionIds !== null,
                fn (Builder $query) => empty($activeSectionIds)
                    // No active sections exist for this owner/subject, so force an empty result set.
                    ? $query->whereRaw('1 = 0')
                    : $query->whereIn('library_section_id', $activeSectionIds)
            )
            ->when($sectionId !== null, fn (Builder $query) => $query->where('library_section_id', $sectionId))
            ->when(filled($search), function (Builder $query) use ($search) {
                $term = '%'.trim((string) $search).'%';

                $query->where(function (Builder $nested) use ($term) {
                    $nested->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('original_filename', 'like', $term)
                        ->orWhere('external_url', 'like', $term);
                });
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->orderBy('id');
    }

    /**
     * @return array<int, int>
     */
    public function activeSectionIdsForOwner(int $ownerUserId, int $subjectId): array
    {
        $sections = LibrarySection::query()
            ->ownedBy($ownerUserId)
            ->forSubject($subjectId)
            ->get(['id', 'parent_id', 'status'])
            ->keyBy('id');

        $activeIds = [];

        foreach ($sections as $section) {
            $current = $section;

            while ($current) {
                if ($current->status !== LibrarySection::STATUS_ACTIVE) {
                    continue 2;
                }

                $current = $current->parent_id
                    ? $sections->get((int) $current->parent_id)
                    : null;
            }

            $activeIds[] = (int) $section->id;
        }

        return $activeIds;
    }
}
