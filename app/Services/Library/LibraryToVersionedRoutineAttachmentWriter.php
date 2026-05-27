<?php

namespace App\Services\Library;

use App\Models\MainDailySessionMainTask;
use App\Models\MainDailySessionMainTaskAttachment;

class LibraryToVersionedRoutineAttachmentWriter
{
    use ResolvesLibraryAttachmentAttributes;

    public function writeOneForMainTaskAtSortOrder(
        MainDailySessionMainTask $task,
        string $resourceId,
        int $ownerUserId,
        int $subjectId,
        int $sortOrder
    ): bool {
        $attributes = $this->attachmentAttributesForResourceId($resourceId, $ownerUserId, $subjectId);

        if ($attributes === null) {
            return false;
        }

        MainDailySessionMainTaskAttachment::create([
            'main_task_id' => $task->id,
            'sort_order' => $sortOrder,
        ] + $attributes);

        return true;
    }
}
