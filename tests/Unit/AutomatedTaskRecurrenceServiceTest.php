<?php

namespace Tests\Unit;

use App\Models\MainDailySessionSubscription;
use App\Models\MainDailySessionTemplate;
use App\Services\AutomatedTaskRecurrenceService;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Unit coverage for AutomatedTaskRecurrenceService.
 *
 * Verifies recurrence matching logic (daily / weekly / monthly),
 * interval handling, and weekday / day-of-month edge cases.
 *
 * Tests are added in Phase 4 (T022).
 */
class AutomatedTaskRecurrenceServiceTest extends TestCase
{
    public function test_weekly_recurrence_only_fires_on_selected_weekdays(): void
    {
        $service = app(AutomatedTaskRecurrenceService::class);
        $template = new MainDailySessionTemplate([
            'recurrence_kind' => 'weekly',
            'recurrence_weekdays' => '1,3',
        ]);

        $this->assertTrue($service->shouldGenerateOn($template, Carbon::parse('2026-04-27')));
        $this->assertTrue($service->shouldGenerateOn($template, Carbon::parse('2026-04-29')));
        $this->assertFalse($service->shouldGenerateOn($template, Carbon::parse('2026-04-30')));
    }

    public function test_weekly_recurrence_tolerates_text_weekday_keys(): void
    {
        $service = app(AutomatedTaskRecurrenceService::class);
        $template = new MainDailySessionTemplate([
            'recurrence_kind' => 'weekly',
            'recurrence_weekdays' => 'mon,wed',
        ]);

        $this->assertTrue($service->shouldGenerateOn($template, Carbon::parse('2026-04-27')));
        $this->assertTrue($service->shouldGenerateOn($template, Carbon::parse('2026-04-29')));
        $this->assertFalse($service->shouldGenerateOn($template, Carbon::parse('2026-04-28')));
        $this->assertFalse($service->shouldGenerateOn($template, Carbon::parse('2026-04-30')));
    }

    public function test_weekly_recurrence_tolerates_mixed_numeric_and_text_weekdays(): void
    {
        $service = app(AutomatedTaskRecurrenceService::class);
        $template = new MainDailySessionTemplate([
            'recurrence_kind' => 'weekly',
            'recurrence_weekdays' => '1,wed,5',
        ]);

        $this->assertTrue($service->shouldGenerateOn($template, Carbon::parse('2026-04-27')));
        $this->assertTrue($service->shouldGenerateOn($template, Carbon::parse('2026-04-29')));
        $this->assertFalse($service->shouldGenerateOn($template, Carbon::parse('2026-04-30')));
        $this->assertTrue($service->shouldGenerateOn($template, Carbon::parse('2026-05-01')));
    }

    public function test_normalize_weekday_csv_returns_carbon_day_of_week_integers(): void
    {
        $service = app(AutomatedTaskRecurrenceService::class);

        $this->assertSame([1, 3], $service->normalizeWeekdayCsv('1,3'));
        $this->assertSame([1, 3], $service->normalizeWeekdayCsv('mon,wed'));
        $this->assertSame([0, 1, 3, 5], $service->normalizeWeekdayCsv('sun,mon,wed,fri'));
        $this->assertSame([0, 6], $service->normalizeWeekdayCsv('0,sat'));
        $this->assertSame([], $service->normalizeWeekdayCsv(''));
        $this->assertSame([], $service->normalizeWeekdayCsv('invalid'));
    }

    public function test_daily_candidate_dates_respect_last_generated_date_and_exact_seven_day_window(): void
    {
        $service = app(AutomatedTaskRecurrenceService::class);
        $template = new MainDailySessionTemplate([
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'created_at' => Carbon::parse('2026-04-01 09:00:00'),
        ]);
        $subscription = new MainDailySessionSubscription([
            'is_active' => 1,
            'paused_at' => null,
            'start_at' => Carbon::parse('2026-04-01 09:00:00'),
            'last_generated_date' => Carbon::parse('2026-04-27'),
        ]);

        $dates = collect($service->candidateDatesForSubscription(
            $template,
            $subscription,
            Carbon::parse('2026-04-30')->startOfDay()
        ))->map(fn (Carbon $date): string => $date->toDateString())->all();

        $this->assertSame([
            '2026-04-28',
            '2026-04-29',
            '2026-04-30',
        ], $dates);
    }

    public function test_daily_candidate_dates_respect_interval_greater_than_one(): void
    {
        $service = app(AutomatedTaskRecurrenceService::class);
        $template = new MainDailySessionTemplate([
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 2,
        ]);
        $template->created_at = Carbon::parse('2026-04-24 09:00:00');
        $subscription = new MainDailySessionSubscription([
            'is_active' => 1,
            'paused_at' => null,
            'start_at' => Carbon::parse('2026-04-24 09:00:00'),
            'last_generated_date' => null,
        ]);

        $dates = collect($service->candidateDatesForSubscription(
            $template,
            $subscription,
            Carbon::parse('2026-04-30')->startOfDay()
        ))->map(fn (Carbon $date): string => $date->toDateString())->all();

        $this->assertSame([
            '2026-04-24',
            '2026-04-26',
            '2026-04-28',
            '2026-04-30',
        ], $dates);
    }

    public function test_candidate_dates_skip_dates_on_or_before_pause_fence_without_advancing_generation_marker(): void
    {
        $service = app(AutomatedTaskRecurrenceService::class);
        $template = new MainDailySessionTemplate([
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
        ]);
        $template->created_at = Carbon::parse('2026-04-20 09:00:00');
        $subscription = new MainDailySessionSubscription([
            'is_active' => 1,
            'paused_at' => null,
            'start_at' => Carbon::parse('2026-04-20 09:00:00'),
            'last_generated_date' => null,
            'paused_through_date' => Carbon::parse('2026-04-28'),
        ]);

        $dates = collect($service->candidateDatesForSubscription(
            $template,
            $subscription,
            Carbon::parse('2026-04-30')->startOfDay()
        ))->map(fn (Carbon $date): string => $date->toDateString())->all();

        $this->assertSame([
            '2026-04-29',
            '2026-04-30',
        ], $dates);
    }

    public function test_monthly_candidate_dates_fall_back_to_subscription_anchor_and_clamp_to_month_end(): void
    {
        $service = app(AutomatedTaskRecurrenceService::class);
        $template = new MainDailySessionTemplate([
            'recurrence_kind' => 'monthly',
            'recurrence_day_of_month' => null,
            'created_at' => Carbon::parse('2026-01-05 09:00:00'),
        ]);
        $subscription = new MainDailySessionSubscription([
            'is_active' => 1,
            'paused_at' => null,
            'start_at' => Carbon::parse('2026-01-31 08:00:00'),
            'last_generated_date' => null,
        ]);

        $dates = collect($service->candidateDatesForSubscription(
            $template,
            $subscription,
            Carbon::parse('2026-02-28')->startOfDay()
        ))->map(fn (Carbon $date): string => $date->toDateString())->all();

        $this->assertSame(['2026-02-28'], $dates);
    }
}
