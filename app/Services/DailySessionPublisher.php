<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\MainDailySessionSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailySessionPublisher
{
    public function __construct(
        private readonly AutomatedTaskAssignmentService $assignmentService,
        private readonly AutomatedTaskRecurrenceService $recurrenceService,
        private readonly AutomatedTaskSnapshotWriter $snapshotWriter,
        private readonly AutomatedTaskSubscriptionService $subscriptionService,
    ) {}

    public function needsGenerationForStudent(int $studentId, Carbon $today): bool
    {
        return MainDailySessionSubscription::query()
            ->forStudent($studentId)
            ->where('is_active', 1)
            ->whereNull('paused_at')
            ->where(function ($query) use ($today) {
                $query->whereNull('start_at')
                    ->orWhere('start_at', '<=', $today->copy()->endOfDay());
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('last_generated_date')
                    ->orWhereDate('last_generated_date', '<', $today->toDateString());
            })
            ->whereHas('template', fn ($query) => $query->where('status', 'active'))
            ->exists();
    }

    public function generateForStudent(int $studentId, Carbon $today): void
    {
        $subscriptions = MainDailySessionSubscription::query()
            ->forStudent($studentId)
            ->where('is_active', 1)
            ->with('template')
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $academicYearId = $this->resolveCurrentAcademicYearId();

        $studentClassId = (int) DB::table('students')
            ->where('id', $studentId)
            ->value('current_class_id');

        if ($studentClassId <= 0) {
            return;
        }

        foreach ($subscriptions as $subscription) {
            $template = $subscription->template;

            if (! $template || $template->status !== 'active') {
                continue;
            }

            if (! $subscription->isActive()) {
                continue;
            }

            $candidateDates = $this->recurrenceService->candidateDatesForSubscription(
                $template,
                $subscription,
                $today
            );

            if ($candidateDates === []) {
                continue;
            }

            foreach ($candidateDates as $candidateDate) {
                DB::transaction(function () use (
                    $studentId,
                    $subscription,
                    $template,
                    $candidateDate,
                    $academicYearId,
                    $studentClassId
                ): bool {
                    $lockedSubscription = MainDailySessionSubscription::query()
                        ->whereKey($subscription->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $lockedSubscription || ! $lockedSubscription->isActive()) {
                        return false;
                    }

                    if ($this->candidateAlreadyProcessed($lockedSubscription, $candidateDate)) {
                        return false;
                    }

                    $effectiveAssignmentRows = DB::table('main_daily_session_student_assignments')
                        ->where('student_id', $studentId)
                        ->where('main_daily_session_template_id', (int) $template->id)
                        ->where('effective_from_date', '<=', $candidateDate->toDateString())
                        ->where(function ($query) use ($candidateDate): void {
                            $query->whereNull('effective_to_date')
                                ->orWhere('effective_to_date', '>=', $candidateDate->toDateString());
                        })
                        ->lockForUpdate()
                        ->get();

                    if ($effectiveAssignmentRows->isEmpty()) {
                        Log::info('Automated Task generation skipped because the student is unassigned.', [
                            'student_id' => $studentId,
                            'template_id' => $template->id,
                            'date' => $candidateDate->toDateString(),
                        ]);

                        return false;
                    }

                    if ($effectiveAssignmentRows->count() > 1) {
                        Log::warning('Automated Task generation skipped because multiple effective version assignments were found.', [
                            'student_id' => $studentId,
                            'template_id' => $template->id,
                            'date' => $candidateDate->toDateString(),
                            'assignment_ids' => $effectiveAssignmentRows->pluck('id')->map(fn ($id): int => (int) $id)->values()->all(),
                        ]);

                        return false;
                    }

                    $assignment = $this->assignmentService->resolveEffectiveAssignment(
                        $studentId,
                        (int) $template->id,
                        $candidateDate
                    );

                    if ($assignment === null) {
                        return false;
                    }

                    $didWrite = $this->snapshotWriter->writeSnapshot(
                        $studentId,
                        $assignment,
                        $candidateDate,
                        $academicYearId,
                        $studentClassId
                    );

                    if ($didWrite) {
                        $this->subscriptionService->advanceLastGeneratedDate($lockedSubscription->id, $candidateDate);
                    }

                    return $didWrite;
                });
            }
        }
    }

    private function candidateAlreadyProcessed(MainDailySessionSubscription $subscription, Carbon $candidateDate): bool
    {
        if ($subscription->last_generated_date !== null && $candidateDate->lte($subscription->last_generated_date)) {
            return true;
        }

        if ($subscription->paused_through_date !== null && $candidateDate->lte($subscription->paused_through_date)) {
            return true;
        }

        return false;
    }

    private function resolveCurrentAcademicYearId(): int
    {
        return AcademicYear::currentId();
    }
}
