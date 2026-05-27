<?php

namespace App\Services;

use App\Models\MainDailySessionSubscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AutomatedTaskSubscriptionService
{
    /**
     * Creates or reactivates a subscription for a student on a template.
     * Returns the subscription record.
     */
    public function subscribe(
        int $studentId,
        int $templateId,
        Carbon $startAt
    ): MainDailySessionSubscription {
        return DB::transaction(function () use ($studentId, $templateId, $startAt) {
            $existing = MainDailySessionSubscription::query()
                ->forStudent($studentId)
                ->forTemplate($templateId)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->update([
                    'is_active' => 1,
                    'paused_at' => null,
                    'start_at' => $existing->is_active
                        ? $existing->start_at
                        : $startAt->toDateTimeString(),
                    'end_at' => null,
                ]);

                return $existing->fresh();
            }

            return MainDailySessionSubscription::create([
                'student_id' => $studentId,
                'main_daily_session_template_id' => $templateId,
                'is_active' => 1,
                'paused_at' => null,
                'start_at' => $startAt->toDateTimeString(),
                'end_at' => null,
                'last_generated_date' => null,
            ]);
        });
    }

    /**
     * Marks a subscription as paused. Publisher will skip this student
     * until unpaused; missed dates are not backfilled on resume.
     */
    public function pause(int $studentId, int $templateId): void
    {
        MainDailySessionSubscription::query()
            ->forStudent($studentId)
            ->forTemplate($templateId)
            ->where('is_active', 1)
            ->update(['paused_at' => now()]);
    }

    /**
     * Resumes a paused subscription from today.
     * Does NOT backfill skipped dates (skip-not-queue per spec).
     */
    public function resume(int $studentId, int $templateId): void
    {
        DB::transaction(function () use ($studentId, $templateId): void {
            $subscription = MainDailySessionSubscription::query()
                ->forStudent($studentId)
                ->forTemplate($templateId)
                ->lockForUpdate()
                ->first();

            if (! $subscription) {
                return;
            }

            $today = now()->startOfDay();
            $pausedThroughDate = $subscription->paused_through_date;

            if ($pausedThroughDate === null || $pausedThroughDate->lt($today)) {
                $pausedThroughDate = $today->copy();
            }

            DB::table('main_daily_session_subscriptions')
                ->where('id', $subscription->id)
                ->update([
                    'paused_at' => null,
                    'paused_through_date' => $pausedThroughDate->toDateString(),
                ]);
        });
    }

    /**
     * Deactivates a subscription entirely.
     */
    public function deactivate(int $studentId, int $templateId): void
    {
        MainDailySessionSubscription::query()
            ->forStudent($studentId)
            ->forTemplate($templateId)
            ->update([
                'is_active' => 0,
                'paused_at' => null,
                'end_at' => now(),
            ]);
    }

    /**
     * Advances last_generated_date only to $date (never backwards).
     * Called by the publisher after a successful generation transaction.
     */
    public function advanceLastGeneratedDate(int $subscriptionId, Carbon $date): void
    {
        DB::table('main_daily_session_subscriptions')
            ->where('id', $subscriptionId)
            ->where(function ($q) use ($date) {
                $q->whereNull('last_generated_date')
                    ->orWhereDate('last_generated_date', '<', $date->toDateString());
            })
            ->update(['last_generated_date' => $date->toDateString()]);
    }

    /**
     * Returns active (not paused) subscriptions for a student.
     */
    public function getActiveSubscriptions(int $studentId): Collection
    {
        return MainDailySessionSubscription::query()
            ->active()
            ->forStudent($studentId)
            ->with('template')
            ->get();
    }

    /**
     * Returns all subscriptions for a template (active and paused).
     */
    public function getSubscriptionsForTemplate(int $templateId): Collection
    {
        return MainDailySessionSubscription::query()
            ->forTemplate($templateId)
            ->with('student')
            ->get();
    }
}
