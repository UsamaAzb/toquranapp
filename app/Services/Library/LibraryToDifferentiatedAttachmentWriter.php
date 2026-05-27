<?php

namespace App\Services\Library;

use App\Models\DifferentiatedTask;
use App\Models\DifferentiatedTaskAttachment;

class LibraryToDifferentiatedAttachmentWriter
{
    use ResolvesLibraryAttachmentAttributes;

    public function writeOneForTaskAtSortOrder(
        DifferentiatedTask $task,
        string $resourceId,
        int $ownerUserId,
        int $subjectId,
        int $sortOrder
    ): bool {
        $attributes = $this->attachmentAttributesForResourceId($resourceId, $ownerUserId, $subjectId);

        if ($attributes === null) {
            return false;
        }

        DifferentiatedTaskAttachment::create([
            'differentiated_task_id' => $task->id,
            'sort_order' => $sortOrder,
        ] + $attributes);

        return true;
    }
}
