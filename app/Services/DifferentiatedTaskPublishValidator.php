<?php

namespace App\Services;

use App\Models\DifferentiatedTask;

class DifferentiatedTaskPublishValidator
{
    public function __construct(
        private readonly DifferentiatedTaskRecurrenceService $recurrenceService,
    ) {}

    public function validate(DifferentiatedTask $task): PublishValidationResult
    {
        $errors = [];

        $task->loadMissing(['versions.selectedAttachments']);

        if (blank($task->title)) {
            $errors[] = 'Task title is required before this Differentiated Task can be made active.';
        }

        $recurrenceErrors = $this->recurrenceService->validateRule($task);
        $errors = array_merge($errors, $recurrenceErrors);

        $validVersions = $task->versions
            ->filter(fn ($version): bool => $version->hasMeaningfulContent());

        if ($validVersions->count() < 2) {
            $errors[] = 'At least two task versions need student-facing content before this Differentiated Task can be made active.';
        }

        return new PublishValidationResult(empty($errors), $errors);
    }
}
