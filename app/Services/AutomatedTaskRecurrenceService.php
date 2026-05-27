<?php

namespace App\Services;

use App\Models\MainDailySessionSubscription;
use App\Models\MainDailySessionTemplate;
use Carbon\Carbon;

class AutomatedTaskRecurrenceService
{
    private const WEEKDAY_TEXT_TO_CARBON = [
        'sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3,
        'thu' => 4, 'fri' => 5, 'sat' => 6,
    ];

    /**
     * Returns true if the template's recurrence rule fires on the given date.
     *
     * recurrence_kind = 'daily'   -> fires every N days from template creation
     * recurrence_kind = 'weekly'  -> fires on specified weekdays (CSV 0=Sun..6=Sat)
     * recurrence_kind = 'monthly' -> fires on specified day-of-month
     *
     * recurrence_interval applies to 'daily' kind only (every N days).
     * For 'weekly' and 'monthly' the interval field is reserved for future use.
     */
    public function shouldGenerateOn(MainDailySessionTemplate $template, Carbon $date): bool
    {
        return match ($template->recurrence_kind) {
            'daily' => $this->matchesDaily($template, $date),
            'weekly' => $this->matchesWeekly($template, $date),
            'monthly' => $this->matchesMonthly($template, $date),
            default => false,
        };
    }

    /**
     * Returns all eligible generation dates for the subscription inside the
     * exact 7-day catch-up window ending on $today.
     *
     * @return array<int, Carbon>
     */
    public function candidateDatesForSubscription(
        MainDailySessionTemplate $template,
        MainDailySessionSubscription $subscription,
        Carbon $today
    ): array {
        $subscriptionStart = $this->resolveSubscriptionStart($subscription, $today);
        $lastGeneratedDate = $this->resolveLastGeneratedDate($subscription);
        $pausedThroughDate = $this->resolvePausedThroughDate($subscription);
        $dates = [];

        for ($offset = 6; $offset >= 0; $offset--) {
            $candidate = $today->copy()->startOfDay()->subDays($offset);

            if ($candidate->lt($subscriptionStart)) {
                continue;
            }

            if ($lastGeneratedDate !== null && $candidate->lte($lastGeneratedDate)) {
                continue;
            }

            if ($pausedThroughDate !== null && $candidate->lte($pausedThroughDate)) {
                continue;
            }

            if ($this->matchesSubscriptionDate($template, $subscription, $candidate)) {
                $dates[] = $candidate->copy();
            }
        }

        return $dates;
    }

    // -------------------------------------------------------------------------
    // Private matchers
    // -------------------------------------------------------------------------

    private function matchesDaily(MainDailySessionTemplate $template, Carbon $date): bool
    {
        $interval = max(1, (int) $template->recurrence_interval);

        if ($interval === 1) {
            return true;
        }

        // Count days from template creation date to the generation date.
        $createdAt = $template->created_at;
        $origin = Carbon::parse($createdAt instanceof \DateTimeInterface
            ? $createdAt->format('Y-m-d')
            : $createdAt
        )->startOfDay();
        $target = $date->copy()->startOfDay();
        // Normalize calendar dates to UTC so DST shifts cannot skew whole-day counting.
        $originDay = Carbon::createFromFormat('Y-m-d', $origin->toDateString(), 'UTC')->startOfDay();
        $targetDay = Carbon::createFromFormat('Y-m-d', $target->toDateString(), 'UTC')->startOfDay();
        $diff = intdiv($targetDay->getTimestamp() - $originDay->getTimestamp(), 86400);

        return $diff % $interval === 0;
    }

    private function matchesWeekly(MainDailySessionTemplate $template, Carbon $date): bool
    {
        $raw = (string) ($template->recurrence_weekdays ?? '');

        if ($raw === '') {
            return false;
        }

        $allowed = $this->normalizeWeekdayCsv($raw);

        return in_array($date->dayOfWeek, $allowed, true);
    }

    /**
     * Normalizes a CSV weekday string into an array of Carbon dayOfWeek integers.
     * Accepts both text keys (mon,tue,wed) and numeric values (1,2,3).
     * Used to tolerate legacy text-key rows alongside the new numeric format.
     *
     * @return int[]
     */
    public function normalizeWeekdayCsv(string $raw): array
    {
        $parts = array_map('trim', explode(',', $raw));
        $normalized = [];

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $lower = strtolower($part);

            if (isset(self::WEEKDAY_TEXT_TO_CARBON[$lower])) {
                $normalized[] = self::WEEKDAY_TEXT_TO_CARBON[$lower];
            } elseif (is_numeric($part)) {
                $normalized[] = (int) $part;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function matchesMonthly(MainDailySessionTemplate $template, Carbon $date): bool
    {
        $targetDay = (int) ($template->recurrence_day_of_month ?? 0);

        if ($targetDay < 1 || $targetDay > 31) {
            return false;
        }

        return $date->day === $this->effectiveMonthlyDay($targetDay, $date);
    }

    private function matchesSubscriptionDate(
        MainDailySessionTemplate $template,
        MainDailySessionSubscription $subscription,
        Carbon $date
    ): bool {
        return match ($template->recurrence_kind) {
            'daily' => $this->matchesDaily($template, $date),
            'weekly' => $this->matchesWeekly($template, $date),
            'monthly' => $this->matchesMonthlyForSubscription($template, $subscription, $date),
            default => false,
        };
    }

    private function matchesMonthlyForSubscription(
        MainDailySessionTemplate $template,
        MainDailySessionSubscription $subscription,
        Carbon $date
    ): bool {
        $targetDay = (int) ($template->recurrence_day_of_month ?? 0);

        if ($targetDay < 1 || $targetDay > 31) {
            $targetDay = $subscription->start_at?->day ?? 0;
        }

        if ($targetDay < 1 || $targetDay > 31) {
            return false;
        }

        return $date->day === $this->effectiveMonthlyDay($targetDay, $date);
    }

    private function effectiveMonthlyDay(int $targetDay, Carbon $date): int
    {
        // On months where the target day exceeds the month's length,
        // fire on the last day of that month instead.
        $lastDayOfMonth = $date->copy()->endOfMonth()->day;

        return min($targetDay, $lastDayOfMonth);
    }

    private function resolveSubscriptionStart(
        MainDailySessionSubscription $subscription,
        Carbon $today
    ): Carbon {
        $rawStart = $subscription->getRawOriginal('start_at');

        if ($rawStart !== null && $rawStart !== '') {
            return Carbon::parse($rawStart)->startOfDay();
        }

        if ($subscription->start_at !== null) {
            return Carbon::parse($subscription->start_at)->startOfDay();
        }

        return $today->copy()->startOfDay();
    }

    private function resolveLastGeneratedDate(MainDailySessionSubscription $subscription): ?Carbon
    {
        $rawLastGenerated = $subscription->getRawOriginal('last_generated_date');

        if ($rawLastGenerated !== null && $rawLastGenerated !== '') {
            return Carbon::parse($rawLastGenerated)->startOfDay();
        }

        if ($subscription->last_generated_date !== null) {
            return Carbon::parse($subscription->last_generated_date)->startOfDay();
        }

        return null;
    }

    private function resolvePausedThroughDate(MainDailySessionSubscription $subscription): ?Carbon
    {
        $rawPausedThrough = $subscription->getRawOriginal('paused_through_date');

        if ($rawPausedThrough !== null && $rawPausedThrough !== '') {
            return Carbon::parse($rawPausedThrough)->startOfDay();
        }

        if ($subscription->paused_through_date !== null) {
            return Carbon::parse($subscription->paused_through_date)->startOfDay();
        }

        return null;
    }
}
