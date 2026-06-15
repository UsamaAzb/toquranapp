<?php

namespace App\Services;

use App\Models\SeriesTask;
use App\Models\SeriesTaskStudentGenerationState;
use App\Models\SeriesTaskVersionItem;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SeriesTaskPublisher
{
    public function __construct(
        private readonly SeriesTaskAssignmentService $assignmentService,
        private readonly SeriesTaskRecurrenceService $recurrenceService,
        private readonly SeriesTaskSnapshotWriter $snapshotWriter,
        private readonly SeriesLibrarySourceResolver $sourceResolver,
        private readonly TeacherStudentSubjectVisibilityService $studentVisibility,
    ) {}

    public function needsGenerationForStudent(int $studentId, Carbon $today): bool
    {
        if (! Schema::hasTable('series_task_student_generation_states')) {
            return false;
        }

        return SeriesTaskStudentGenerationState::query()
            ->forStudent($studentId)
            ->active()
            ->whereNull('completed_at')
            ->where(function ($query) use ($today): void {
                $query->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $today->toDateString());
            })
            ->where(function ($query) use ($today): void {
                $query->whereNull('last_generated_date')
                    ->orWhereDate('last_generated_date', '<', $today->toDateString());
            })
            ->where(function ($query) use ($today): void {
                $query->whereNull('paused_through_date')
                    ->orWhereDate('paused_through_date', '<', $today->toDateString());
            })
            ->whereHas('task', fn ($query) => $query->where('status', 'active'))
            ->exists();
    }

    public function generateForStudent(int $studentId, Carbon $today): void
    {
        if (! Schema::hasTable('series_task_student_generation_states')) {
            return;
        }

        $states = SeriesTaskStudentGenerationState::query()
            ->forStudent($studentId)
            ->active()
            ->with('task')
            ->get();

        foreach ($states as $state) {
            $task = $state->task;

            if (! $task || ! $task->isActive()) {
                continue;
            }

            $candidateDates = $this->recurrenceService->candidateDatesForState($task, $state, $today);

            foreach ($candidateDates as $candidateDate) {
                if ($this->activationFenceBlocksDate($task->published_at, $candidateDate)) {
                    continue;
                }

                DB::transaction(function () use ($studentId, $state, $task, $candidateDate): void {
                    $lockedState = SeriesTaskStudentGenerationState::query()
                        ->whereKey($state->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $lockedState || ! $lockedState->isActive() || $lockedState->completed_at !== null) {
                        return;
                    }

                    if ($this->candidateAlreadyProcessed($lockedState, $candidateDate)) {
                        return;
                    }

                    if ($this->generatedRowsAlreadyExist((int) $task->id, $studentId, $candidateDate)) {
                        $this->advanceLastGeneratedDate($lockedState->id, $candidateDate);

                        return;
                    }

                    if (! $this->studentCanReceiveTask($studentId, $task)) {
                        $this->advanceLastGeneratedDate($lockedState->id, $candidateDate);

                        return;
                    }

                    $assignment = $this->assignmentService->resolveEffectiveAssignment(
                        $studentId,
                        (int) $task->id,
                        $candidateDate,
                        lockForUpdate: true
                    );

                    if (! $assignment) {
                        $this->advanceLastGeneratedDate($lockedState->id, $candidateDate);

                        return;
                    }

                    if ((int) $assignment->version_id !== (int) $lockedState->current_version_id) {
                        $lockedState->update([
                            'current_version_id' => $assignment->version_id,
                            'next_sequence_position' => max(1, (int) $assignment->start_sequence_position),
                            'completed_at' => null,
                        ]);
                    }

                    $lockedState->refresh();

                    if ($this->releasePolicyBlocksGeneration($task, $studentId)) {
                        return;
                    }

                    $position = max(1, (int) $lockedState->next_sequence_position);
                    $lastActivePosition = (int) SeriesTaskVersionItem::query()
                        ->where('version_id', $assignment->version_id)
                        ->active()
                        ->max('sequence_position');

                    if ($lastActivePosition < 1) {
                        $this->pauseThroughDate($lockedState->id, $candidateDate);

                        return;
                    }

                    if ($position > $lastActivePosition) {
                        if ($task->sequence_behavior !== 'loop') {
                            $lockedState->update([
                                'last_generated_date' => $candidateDate->toDateString(),
                                'completed_at' => now(config('app.timezone', 'Africa/Cairo')),
                            ]);

                            return;
                        }

                        $position = 1;
                        $lockedState->update(['next_sequence_position' => $position]);
                    }

                    $versionItem = $this->resolveVersionItem($assignment->version_id, $position, $task);

                    if (! $versionItem) {
                        $this->pauseThroughDate($lockedState->id, $candidateDate);

                        return;
                    }

                    if (! $this->seriesSourceIsLaunchAllowed($task, $versionItem)) {
                        Log::warning('Series Task generation blocked by legacy Library source.', [
                            'series_task_id' => $task->id,
                            'version_item_id' => $versionItem->id,
                            'student_id' => $studentId,
                            'generated_for_date' => $candidateDate->toDateString(),
                        ]);
                        $this->pauseThroughDate($lockedState->id, $candidateDate);

                        return;
                    }

                    $resolved = $this->sourceResolver->resolveItem(
                        (string) $versionItem->library_source_type,
                        (int) $versionItem->library_source_id,
                        (int) $task->created_by_user_id
                    );

                    if (
                        ! $resolved
                        || (
                            $resolved->sourceType !== SeriesLibrarySourceResolver::SOURCE_VOCABULARY_LIST
                            && ! $resolved->hasSafeDeliveryTarget()
                        )
                    ) {
                        Log::warning('Series Task generation blocked by missing Library item.', [
                            'series_task_id' => $task->id,
                            'version_item_id' => $versionItem->id,
                            'student_id' => $studentId,
                            'generated_for_date' => $candidateDate->toDateString(),
                        ]);
                        $this->pauseThroughDate($lockedState->id, $candidateDate);

                        return;
                    }

                    try {
                        $didWrite = $this->snapshotWriter->generateForStudent(
                            $studentId,
                            (int) $task->id,
                            (int) $assignment->version_id,
                            (int) $versionItem->id,
                            (int) $assignment->id,
                            $candidateDate
                        );
                    } catch (QueryException $exception) {
                        if ($this->isDuplicateKey($exception)) {
                            $this->advanceLastGeneratedDate($lockedState->id, $candidateDate);

                            return;
                        }

                        throw $exception;
                    }

                    if (! $didWrite) {
                        if ($this->generatedRowsAlreadyExist((int) $task->id, $studentId, $candidateDate)) {
                            $this->advanceLastGeneratedDate($lockedState->id, $candidateDate);

                            return;
                        }

                        $this->pauseThroughDate($lockedState->id, $candidateDate);

                        return;
                    }

                    $this->advanceSequence($lockedState->id, $task, $versionItem, $candidateDate);
                });
            }
        }
    }

    private function resolveVersionItem(int $versionId, int $position, SeriesTask $task): ?SeriesTaskVersionItem
    {
        $item = SeriesTaskVersionItem::query()
            ->where('version_id', $versionId)
            ->where('sequence_position', $position)
            ->first();

        if ($item && ! $item->is_active) {
            Log::warning('Series Task generation blocked by inactive next item.', [
                'series_task_id' => $task->id,
                'version_id' => $versionId,
                'sequence_position' => $position,
            ]);

            return null;
        }

        if ($item) {
            return $item;
        }

        $lastPosition = (int) SeriesTaskVersionItem::query()
            ->where('version_id', $versionId)
            ->active()
            ->max('sequence_position');

        if ($lastPosition < 1) {
            return null;
        }

        if ($task->sequence_behavior === 'loop') {
            return SeriesTaskVersionItem::query()
                ->where('version_id', $versionId)
                ->where('sequence_position', 1)
                ->active()
                ->first();
        }

        return null;
    }

    private function seriesSourceIsLaunchAllowed(SeriesTask $task, SeriesTaskVersionItem $versionItem): bool
    {
        $collectionType = (string) $task->library_collection_type;
        $collectionId = $task->library_collection_id === null ? null : (int) $task->library_collection_id;
        $sourceType = (string) $versionItem->library_source_type;
        $sourceId = (int) $versionItem->library_source_id;

        return $collectionType === SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER
            && $sourceType === SeriesLibrarySourceResolver::SOURCE_GENERAL_LIBRARY_RESOURCE
            && $this->sourceResolver->sourceIsSelectableForSeriesLaunch(
                $collectionType,
                $collectionId,
                (int) $task->created_by_user_id,
                (int) $task->subject_id
            )
            && $this->sourceResolver->itemBelongsToCollection(
                $collectionType,
                $collectionId,
                $sourceType,
                $sourceId
            );
    }

    private function advanceSequence(
        int $stateId,
        SeriesTask $task,
        SeriesTaskVersionItem $versionItem,
        Carbon $candidateDate
    ): void {
        $lastPosition = (int) SeriesTaskVersionItem::query()
            ->where('version_id', $versionItem->version_id)
            ->active()
            ->max('sequence_position');
        $nextPosition = ((int) $versionItem->sequence_position) + 1;
        $completedAt = null;

        if ($nextPosition > $lastPosition) {
            if ($task->sequence_behavior === 'loop') {
                $nextPosition = 1;
            } else {
                $completedAt = now(config('app.timezone', 'Africa/Cairo'));
            }
        }

        DB::table('series_task_student_generation_states')
            ->where('id', $stateId)
            ->update([
                'next_sequence_position' => $nextPosition,
                'last_delivered_sequence_position' => $versionItem->sequence_position,
                'last_generated_date' => $candidateDate->toDateString(),
                'completed_at' => $completedAt,
            ]);
    }

    private function activationFenceBlocksDate(mixed $publishedAt, Carbon $candidateDate): bool
    {
        if ($publishedAt === null) {
            return true;
        }

        return $candidateDate->lt(Carbon::parse($publishedAt)->startOfDay());
    }

    private function releasePolicyBlocksGeneration(SeriesTask $task, int $studentId): bool
    {
        if ((string) ($task->release_policy ?? 'continuous') !== 'wait_for_completion') {
            return false;
        }

        $latestSession = DB::table('class_sessions')
            ->where('series_task_id', $task->id)
            ->where('student_id', $studentId)
            ->orderByDesc('generated_for_date')
            ->orderByDesc('id')
            ->first(['id']);

        if (! $latestSession) {
            return false;
        }

        $latestTaskId = DB::table('session_tasks')
            ->where('class_session_id', $latestSession->id)
            ->where('source_series_task_id_snapshot', $task->id)
            ->orderByDesc('id')
            ->value('id');

        if (! $latestTaskId) {
            return true;
        }

        $status = DB::table('session_task_student')
            ->where('session_task_id', $latestTaskId)
            ->where('student_id', $studentId)
            ->orderByDesc('id')
            ->value('status');

        return $status !== 'completed';
    }

    private function candidateAlreadyProcessed(SeriesTaskStudentGenerationState $state, Carbon $candidateDate): bool
    {
        if ($state->last_generated_date !== null && $candidateDate->lte($state->last_generated_date)) {
            return true;
        }

        if ($state->paused_through_date !== null && $candidateDate->lte($state->paused_through_date)) {
            return true;
        }

        return false;
    }

    private function generatedRowsAlreadyExist(int $taskId, int $studentId, Carbon $date): bool
    {
        return DB::table('class_sessions')
            ->where('series_task_id', $taskId)
            ->where('student_id', $studentId)
            ->whereDate('generated_for_date', $date->toDateString())
            ->exists();
    }

    private function studentCanReceiveTask(int $studentId, SeriesTask $task): bool
    {
        $student = Student::query()
            ->whereKey($studentId)
            ->where(function ($query): void {
                $query->whereNull('account_status')
                    ->orWhere('account_status', '')
                    ->orWhere('account_status', 'active');
            })
            ->first();

        if (! $student || (int) $student->current_class_id <= 0) {
            return false;
        }

        return $this->studentVisibility->studentIsVisible(
            (int) $task->created_by_user_id,
            (int) $task->subject_id,
            $studentId
        );
    }

    private function advanceLastGeneratedDate(int $stateId, Carbon $date): void
    {
        DB::table('series_task_student_generation_states')
            ->where('id', $stateId)
            ->where(function ($query) use ($date): void {
                $query->whereNull('last_generated_date')
                    ->orWhereDate('last_generated_date', '<', $date->toDateString());
            })
            ->update(['last_generated_date' => $date->toDateString()]);
    }

    private function pauseThroughDate(int $stateId, Carbon $date): void
    {
        DB::table('series_task_student_generation_states')
            ->where('id', $stateId)
            ->where(function ($query) use ($date): void {
                $query->whereNull('paused_through_date')
                    ->orWhereDate('paused_through_date', '<', $date->toDateString());
            })
            ->update(['paused_through_date' => $date->toDateString()]);
    }

    private function isDuplicateKey(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $driverCode = (string) ($exception->errorInfo[1] ?? '');

        return $sqlState === '23000' && in_array($driverCode, ['1062', '19'], true);
    }
}
