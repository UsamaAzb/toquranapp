<?php

namespace App\Services;

use App\Models\MainDailySessionTemplate;
use App\Models\MainDailySessionVersion;
use Illuminate\Support\Facades\DB;

class AutomatedTaskPublishValidator
{
    public function validate(MainDailySessionTemplate $template): PublishValidationResult
    {
        $errors = [];

        $template->loadMissing([
            'versions.versionTasks.mainTask.attachments',
            'versions.studentAssignments' => fn ($query) => $query->whereNull('effective_to_date'),
        ]);

        $activeSubscribedStudentIds = DB::table('main_daily_session_subscriptions')
            ->where('main_daily_session_template_id', $template->id)
            ->where('is_active', 1)
            ->whereNull('paused_at')
            ->pluck('student_id')
            ->map(fn ($value): int => (int) $value)
            ->all();

        if (empty($activeSubscribedStudentIds)) {
            $errors[] = 'No active subscribed students are assigned to this Automated Task template.';

            return new PublishValidationResult(false, $errors);
        }

        $participatingVersionIds = [];

        foreach ($template->versions as $version) {
            $assignedStudentIds = $version->studentAssignments
                ->pluck('student_id')
                ->map(fn ($value): int => (int) $value)
                ->all();

            if (count(array_intersect($assignedStudentIds, $activeSubscribedStudentIds)) > 0) {
                $participatingVersionIds[] = $version->id;
            }
        }

        if (empty($participatingVersionIds)) {
            $errors[] = 'No active subscribed students are assigned to any version.';

            return new PublishValidationResult(false, $errors);
        }

        foreach ($template->versions as $version) {
            if (! in_array($version->id, $participatingVersionIds, true)) {
                continue;
            }

            $errors = array_merge($errors, $this->diagnoseVersion($version)['errors']);
        }

        return new PublishValidationResult(empty($errors), $errors);
    }

    /**
     * @return array{passes: bool, errors: string[]}
     */
    public function diagnoseVersion(MainDailySessionVersion $version): array
    {
        $errors = [];

        if ($version->versionTasks->isEmpty()) {
            $errors[] = sprintf(
                'Version "%s" has no included tasks and cannot be published.',
                $version->display_name
            );

            return [
                'passes' => false,
                'errors' => $errors,
            ];
        }

        foreach ($version->versionTasks as $versionTask) {
            if (! $versionTask->passesMeaningfulContentRule()) {
                $errors[] = sprintf(
                    'Version "%s" - task "%s": This task needs a description, an attachment, or both before it can be saved.',
                    $version->display_name,
                    $versionTask->mainTask?->title ?? "(task #{$versionTask->main_task_id})"
                );
            }
        }

        return [
            'passes' => empty($errors),
            'errors' => $errors,
        ];
    }
}
