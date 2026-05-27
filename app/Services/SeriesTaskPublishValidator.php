<?php

namespace App\Services;

use App\Models\SeriesTask;

class SeriesTaskPublishValidator
{
    public function __construct(
        private readonly SeriesTaskRecurrenceService $recurrenceService,
        private readonly SeriesLibrarySourceResolver $sourceResolver,
    ) {}

    public function validate(SeriesTask $task): PublishValidationResult
    {
        $errors = [];

        $task->loadMissing(['versions.items', 'versions.studentAssignments']);

        if (blank($task->title)) {
            $errors[] = 'Series Task title is required before activation.';
        }

        if (! in_array($task->sequence_behavior, ['stop_at_end', 'loop'], true)) {
            $errors[] = 'Choose a valid sequence behavior.';
        }

        if (! in_array((string) ($task->release_policy ?? 'continuous'), ['continuous', 'wait_for_completion'], true)) {
            $errors[] = 'Choose a valid release policy.';
        }

        if (! $this->sourceResolver->sourceIsSelectable(
            (string) $task->library_collection_type,
            $task->library_collection_id === null ? null : (int) $task->library_collection_id,
            (int) $task->created_by_user_id,
            (int) $task->subject_id
        )) {
            $errors[] = 'Choose a ready Library source before activation.';
        }

        $errors = array_merge($errors, $this->recurrenceService->validateRule($task));

        if ($task->versions->isEmpty()) {
            $errors[] = 'Add at least one Series version before activation.';
        }

        $hasAnyActiveItems = false;

        foreach ($task->versions as $version) {
            $activeItems = $version->items->where('is_active', true);

            if ($activeItems->isEmpty()) {
                if ($version->assignedStudentCount() > 0) {
                    $errors[] = "Version {$version->display_name} has assigned students and needs at least one active Library item.";
                }

                continue;
            }

            $hasAnyActiveItems = true;

            foreach ($activeItems as $item) {
                if (! $this->sourceResolver->itemBelongsToCollection(
                    (string) $task->library_collection_type,
                    $task->library_collection_id === null ? null : (int) $task->library_collection_id,
                    (string) $item->library_source_type,
                    (int) $item->library_source_id
                )) {
                    $errors[] = "Library item {$item->library_title_snapshot} does not belong to the selected Library source.";

                    continue;
                }

                $resolved = $this->sourceResolver->resolveItem(
                    (string) $item->library_source_type,
                    (int) $item->library_source_id
                );

                if (
                    ! $resolved
                    || (
                        $resolved->sourceType !== SeriesLibrarySourceResolver::SOURCE_VOCABULARY_LIST
                        && ! $resolved->hasSafeDeliveryTarget()
                    )
                ) {
                    $errors[] = "Library item {$item->library_title_snapshot} can no longer be resolved safely.";
                }
            }
        }

        if (! $hasAnyActiveItems) {
            $errors[] = 'Add at least one active Library item to a pathway before activation.';
        }

        return new PublishValidationResult(empty($errors), array_values(array_unique($errors)));
    }
}
