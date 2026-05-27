<?php

namespace App\Services;

use App\Models\DifferentiatedTask;
use App\Models\DifferentiatedTaskStudentGenerationState;
use Carbon\Carbon;

class DifferentiatedTaskRecurrenceService
{
    private const WEEKDAY_TEXT_TO_CARBON = [
        'sun' => 0,
        'mon' => 1,
        'tue' => 2,
        'wed' => 3,
        'thu' => 4,
        'fri' => 5,
        'sat' => 6,
    ];

    public function shouldGenerateOn(DifferentiatedTask $task, Carbon $date): bool
    {
        return match ($task->recurrence_kind) {
            'daily' => $this->matchesDaily($task, $date),
            'weekly' => $this->matchesWeekly($task, $date),
            'monthly' => $this->matchesMonthly($task, $date),
            default => false,
        };
    }

    /**
     * @return string[]
     */
    public function validateRule(DifferentiatedTask $task): array
    {
        $errors = [];

        if (! in_array($task->recurrence_kind, ['daily', 'weekly', 'monthly'], true)) {
            return ['Choose a valid recurrence schedule.'];
        }

        if ($task->recurrence_kind === 'daily' && (int) $task->recurrence_interval < 1) {
            $errors[] = 'Daily recurrence interval must be at least 1.';
        }

        if ($task->recurrence_kind === 'weekly' && $this->normalizeWeekdayCsv((string) $task->recurrence_weekdays) === []) {
            $errors[] = 'Choose at least one weekday for weekly recurrence.';
        }

        if ($task->recurrence_kind === 'monthly') {
            $dayOfMonth = (int) $task->recurrence_day_of_month;

            if ($dayOfMonth < 1 || $dayOfMonth > 31) {
                $errors[] = 'Choose a monthly recurrence day from 1 to 31.';
            }
        }

        return $errors;
    }

    /**
     * Returns all eligible generation dates inside the 7-day catch-up window.
     *
     * @return array<int, Carbon>
     */
    public function candidateDatesForState(
        DifferentiatedTask $task,
        DifferentiatedTaskStudentGenerationState $state,
        Carbon $today
    ): array {
        $startDate = $this->resolveStartDate($state, $today);
        $lastGeneratedDate = $this->resolveDate($state->getRawOriginal('last_generated_date'), $state->last_generated_date);
        $pausedThroughDate = $this->resolveDate($state->getRawOriginal('paused_through_date'), $state->paused_through_date);
        $dates = [];

        for ($offset = 6; $offset >= 0; $offset--) {
            $candidate = $today->copy()->startOfDay()->subDays($offset);

            if ($candidate->lt($startDate)) {
                continue;
            }

            if ($lastGeneratedDate !== null && $candidate->lte($lastGeneratedDate)) {
                continue;
            }

            if ($pausedThroughDate !== null && $candidate->lte($pausedThroughDate)) {
                continue;
            }

            if ($this->shouldGenerateOn($task, $candidate)) {
                $dates[] = $candidate->copy();
            }
        }

        return $dates;
    }

    /**
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
                $day = (int) $part;

                if ($day >= 0 && $day <= 6) {
                    $normalized[] = $day;
                }
            }
        }

        return array_values(array_unique($normalized));
    }

    private function matchesDaily(DifferentiatedTask $task, Carbon $date): bool
    {
        $interval = max(1, (int) $task->recurrence_interval);

        if ($interval === 1) {
            return true;
        }

        $createdAt = $task->created_at;
        $origin = Carbon::parse($createdAt instanceof \DateTimeInterface
            ? $createdAt->format('Y-m-d')
            : $createdAt
        )->startOfDay();
        $target = $date->copy()->startOfDay();
        $originDay = Carbon::createFromFormat('Y-m-d', $origin->toDateString(), 'UTC')->startOfDay();
        $targetDay = Carbon::createFromFormat('Y-m-d', $target->toDateString(), 'UTC')->startOfDay();
        $diff = intdiv($targetDay->getTimestamp() - $originDay->getTimestamp(), 86400);

        return $diff % $interval === 0;
    }

    private function matchesWeekly(DifferentiatedTask $task, Carbon $date): bool
    {
        $allowed = $this->normalizeWeekdayCsv((string) ($task->recurrence_weekdays ?? ''));

        return in_array($date->dayOfWeek, $allowed, true);
    }

    private function matchesMonthly(DifferentiatedTask $task, Carbon $date): bool
    {
        $targetDay = (int) ($task->recurrence_day_of_month ?? 0);

        if ($targetDay < 1 || $targetDay > 31) {
            return false;
        }

        return $date->day === min($targetDay, $date->copy()->endOfMonth()->day);
    }

    private function resolveStartDate(DifferentiatedTaskStudentGenerationState $state, Carbon $today): Carbon
    {
        return $this->resolveDate($state->getRawOriginal('start_date'), $state->start_date)
            ?? $today->copy()->startOfDay();
    }

    private function resolveDate(mixed $rawValue, mixed $castValue): ?Carbon
    {
        if ($rawValue !== null && $rawValue !== '') {
            return Carbon::parse($rawValue)->startOfDay();
        }

        if ($castValue !== null) {
            return Carbon::parse($castValue)->startOfDay();
        }

        return null;
    }
}
