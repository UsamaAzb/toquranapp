<?php

namespace App\Services;

use App\Models\DifferentiatedTask;
use App\Models\DifferentiatedTaskStudentGenerationState;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DifferentiatedTaskPublisher
{
    public function __construct(
        private readonly DifferentiatedTaskAssignmentService $assignmentService,
        private readonly DifferentiatedTaskRecurrenceService $recurrenceService,
        private readonly DifferentiatedTaskSnapshotWriter $snapshotWriter,
        private readonly TeacherStudentSubjectVisibilityService $studentVisibility,
    ) {}

    public function needsGenerationForStudent(int $studentId, Carbon $today): bool
    {
        if (! Schema::hasTable('differentiated_task_student_generation_states')) {
            return false;
        }

        return DifferentiatedTaskStudentGenerationState::query()
            ->forStudent($studentId)
            ->active()
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
        if (! Schema::hasTable('differentiated_task_student_generation_states')) {
            return;
        }

        $states = DifferentiatedTaskStudentGenerationState::query()
            ->forStudent($studentId)
            ->active()
            ->with('task')
            ->get();

        if ($states->isEmpty()) {
            return;
        }

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
                    $lockedState = DifferentiatedTaskStudentGenerationState::query()
                        ->whereKey($state->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $lockedState || ! $lockedState->isActive()) {
                        return;
                    }

                    if ($this->candidateAlreadyProcessed($lockedState, $candidateDate)) {
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

                    if (! $assignment->version || ! $assignment->version->hasMeaningfulContent()) {
                        throw new RuntimeException('Differentiated Task assignment resolved to an invalid version.');
                    }

                    $this->snapshotWriter->generateForStudent(
                        $studentId,
                        (int) $task->id,
                        (int) $assignment->version_id,
                        (int) $assignment->id,
                        $candidateDate
                    );

                    $this->advanceLastGeneratedDate($lockedState->id, $candidateDate);
                });
            }
        }
    }

    private function activationFenceBlocksDate(mixed $publishedAt, Carbon $candidateDate): bool
    {
        if ($publishedAt === null) {
            return true;
        }

        return $candidateDate->lt(Carbon::parse($publishedAt)->startOfDay());
    }

    private function candidateAlreadyProcessed(
        DifferentiatedTaskStudentGenerationState $state,
        Carbon $candidateDate
    ): bool {
        if ($state->last_generated_date !== null && $candidateDate->lte($state->last_generated_date)) {
            return true;
        }

        if ($state->paused_through_date !== null && $candidateDate->lte($state->paused_through_date)) {
            return true;
        }

        return false;
    }

    private function studentCanReceiveTask(int $studentId, DifferentiatedTask $task): bool
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
        DB::table('differentiated_task_student_generation_states')
            ->where('id', $stateId)
            ->where(function ($query) use ($date): void {
                $query->whereNull('last_generated_date')
                    ->orWhereDate('last_generated_date', '<', $date->toDateString());
            })
            ->update(['last_generated_date' => $date->toDateString()]);
    }
}
