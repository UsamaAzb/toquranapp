<?php

namespace App\Services\Library;

use App\Models\LibrarySection;
use Illuminate\Validation\ValidationException;

class LibrarySectionValidator
{
    public function validateParentForWrite(
        int $ownerUserId,
        int $subjectId,
        ?int $parentId,
        ?int $movingSectionId = null
    ): void {
        if ($parentId === null) {
            return;
        }

        if ($movingSectionId !== null && $parentId === $movingSectionId) {
            throw ValidationException::withMessages([
                'parent_id' => 'A Library folder cannot be its own parent.',
            ]);
        }

        $parent = LibrarySection::query()->find($parentId);

        if (! $parent) {
            throw ValidationException::withMessages([
                'parent_id' => 'Choose an existing Library folder.',
            ]);
        }

        if ((int) $parent->owner_user_id !== $ownerUserId) {
            throw ValidationException::withMessages([
                'parent_id' => 'Choose a folder from your own Library.',
            ]);
        }

        if ((int) $parent->subject_id !== $subjectId) {
            throw ValidationException::withMessages([
                'parent_id' => 'Choose a folder from the same subject.',
            ]);
        }

        if ($movingSectionId !== null && $this->isDescendantOf($parent, $movingSectionId)) {
            throw ValidationException::withMessages([
                'parent_id' => 'A Library folder cannot be moved inside one of its own subfolders.',
            ]);
        }
    }

    public function validateUniqueActiveSiblingTitle(
        int $ownerUserId,
        int $subjectId,
        ?int $parentId,
        string $title,
        ?int $ignoreSectionId = null
    ): void {
        $exists = LibrarySection::query()
            ->where('owner_user_id', $ownerUserId)
            ->where('subject_id', $subjectId)
            ->where('status', LibrarySection::STATUS_ACTIVE)
            ->where('title', trim($title))
            ->where(function ($query) use ($parentId) {
                $parentId === null
                    ? $query->whereNull('parent_id')
                    : $query->where('parent_id', $parentId);
            })
            ->when($ignoreSectionId !== null, fn ($query) => $query->whereKeyNot($ignoreSectionId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'title' => 'This folder already exists in the same Library location.',
            ]);
        }
    }

    private function isDescendantOf(LibrarySection $candidateParent, int $movingSectionId): bool
    {
        $current = $candidateParent;
        $depth = 0;
        $maxDepth = 100;

        while ($current) {
            if (++$depth > $maxDepth) {
                return true;
            }

            if ((int) $current->id === $movingSectionId) {
                return true;
            }

            $current = $current->parent_id
                ? LibrarySection::query()->find($current->parent_id)
                : null;
        }

        return false;
    }
}
