<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\RewardPointsLedger;
use App\Models\RewardTotal;
use App\Models\Student;
use App\Models\StudentGift;
use App\Models\StudentGiftPointsHistory;
use Illuminate\Support\Facades\DB;

/**
 * Owns signed reward point changes and gift milestone progression.
 *
 * Gifts are earned milestones, not spendable currency. Reaching a milestone
 * records it and immediately opens the next target; redemption only records
 * the claim timestamp.
 *
 * Reward mutation lock order is student row, points history, reward totals,
 * then gift rows. Keep future reward writers on that order to reduce deadlock
 * risk.
 */
class RewardProgressionService
{
    /**
     * Apply a signed points change and advance milestone gifts when thresholds are reached.
     */
    public function applyPointDelta(
        int $studentId,
        int $pointsDelta,
        string $sourceType,
        int $sourceId,
        int $grantedBy,
        ?int $academicYearId = null,
        ?int $subjectId = null,
        ?string $comment = null
    ): StudentGiftPointsHistory {
        return DB::transaction(function () use (
            $studentId,
            $pointsDelta,
            $sourceType,
            $sourceId,
            $grantedBy,
            $academicYearId,
            $subjectId,
            $comment
        ): StudentGiftPointsHistory {
            $academicYearId ??= AcademicYear::currentId();

            $history = $this->lockOrCreatePointsHistory($studentId, $academicYearId);
            $currentTotal = $this->signedHistoryPoints($history) + $pointsDelta;

            $history->sign = $currentTotal < 0 ? 'minus' : 'plus';
            $history->points = abs($currentTotal);
            $history->date = now()->toDateString();
            $history->save();

            RewardPointsLedger::create([
                'student_id' => $studentId,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'points_delta' => $pointsDelta,
                'sign' => $pointsDelta < 0 ? 'minus' : 'plus',
                'granted_by' => $grantedBy,
                'granted_at' => now(config('app.timezone')),
                'academic_year_id' => $academicYearId,
                'subject_id' => $subjectId,
                'comment' => $comment,
            ]);

            $this->applyRewardTotalDelta($studentId, $academicYearId, $subjectId, $pointsDelta);
            $this->advanceGiftQueueForTotal($studentId, $currentTotal, $academicYearId);

            return $history->fresh();
        }, 3);
    }

    /**
     * Mark a reached gift as redeemed without spending points.
     */
    public function redeemGift(int $studentId, int $giftId): ?StudentGift
    {
        return DB::transaction(function () use ($studentId, $giftId): ?StudentGift {
            $gift = StudentGift::query()
                ->where('student_id', $studentId)
                ->whereKey($giftId)
                ->lockForUpdate()
                ->first();

            if (! $gift) {
                return null;
            }

            if ($gift->status === StudentGift::STATUS_REDEEMED) {
                return $gift;
            }

            if ($gift->status !== StudentGift::STATUS_REACHED) {
                return null;
            }

            $gift->status = StudentGift::STATUS_REDEEMED;
            $gift->redeemed_at ??= now(config('app.timezone'));
            $gift->save();

            $this->ensurePendingGift($studentId, $gift->academic_year_id ? (int) $gift->academic_year_id : null);

            return $gift->fresh();
        }, 3);
    }

    /**
     * Return the signed current reward total for the student's academic year.
     */
    public function currentPoints(int $studentId, ?int $academicYearId = null): int
    {
        $academicYearId ??= AcademicYear::currentId();

        $history = StudentGiftPointsHistory::query()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->first(['points', 'sign']);

        return $history ? $this->signedHistoryPoints($history) : 0;
    }

    /**
     * Ensure a valid pending gift exists without changing reached or redeemed gifts.
     */
    public function ensurePendingGift(int $studentId, ?int $academicYearId = null): ?StudentGift
    {
        $academicYearId ??= AcademicYear::currentId();

        if ($pending = $this->pendingGift($studentId, $academicYearId)) {
            return $pending;
        }

        StudentGift::maintainUpcomingRunway($studentId, $academicYearId);

        return $this->pendingGift($studentId, $academicYearId);
    }

    /**
     * Advance as many gift milestones as the current total has earned.
     *
     * The current total must come from the caller's locked point-history flow.
     */
    public function advanceGiftQueueForTotal(int $studentId, int $currentTotal, ?int $academicYearId = null): void
    {
        $academicYearId ??= AcademicYear::currentId();

        for ($guard = 0; $guard < 100; $guard++) {
            $pending = $this->ensurePendingGift($studentId, $academicYearId);

            if (! $pending || $pending->points_required === null) {
                return;
            }

            if ($currentTotal < (int) $pending->points_required) {
                StudentGift::maintainUpcomingRunway($studentId, $academicYearId);

                return;
            }

            $locked = StudentGift::query()
                ->whereKey($pending->id)
                ->where('student_id', $studentId)
                ->where('academic_year_id', $academicYearId)
                ->lockForUpdate()
                ->first();

            if (! $locked || in_array($locked->status, [StudentGift::STATUS_REACHED, StudentGift::STATUS_REDEEMED], true)) {
                continue;
            }

            $locked->status = StudentGift::STATUS_REACHED;
            $locked->reached_at ??= now(config('app.timezone'));
            $locked->save();
        }

        StudentGift::maintainUpcomingRunway($studentId, $academicYearId);
    }

    private function lockOrCreatePointsHistory(int $studentId, int $academicYearId): StudentGiftPointsHistory
    {
        Student::query()
            ->whereKey($studentId)
            ->lockForUpdate()
            ->firstOrFail();

        $history = StudentGiftPointsHistory::query()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->lockForUpdate()
            ->first();

        if ($history) {
            return $history;
        }

        return StudentGiftPointsHistory::create([
            'student_id' => $studentId,
            'academic_year_id' => $academicYearId,
            'points' => 0,
            'date' => now()->toDateString(),
            'sign' => 'plus',
        ]);
    }

    private function signedHistoryPoints(StudentGiftPointsHistory $history): int
    {
        $points = (int) ($history->points ?? 0);

        return $history->sign === 'minus' ? -1 * $points : $points;
    }

    private function applyRewardTotalDelta(int $studentId, int $academicYearId, ?int $subjectId, int $pointsDelta): void
    {
        $rewardTotal = RewardTotal::query()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('subject_id', $subjectId)
            ->lockForUpdate()
            ->first();

        if (! $rewardTotal) {
            $rewardTotal = new RewardTotal([
                'student_id' => $studentId,
                'academic_year_id' => $academicYearId,
                'subject_id' => $subjectId,
                'total_points' => 0,
            ]);
            $rewardTotal->created_at = now(config('app.timezone'));
        }

        $rewardTotal->total_points = (int) ($rewardTotal->total_points ?? 0) + $pointsDelta;
        $rewardTotal->save();
    }

    private function pendingGift(int $studentId, ?int $academicYearId = null): ?StudentGift
    {
        $academicYearId ??= AcademicYear::currentId();

        return StudentGift::query()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', StudentGift::STATUS_PENDING)
            ->orderBy('points_required')
            ->first();
    }
}
