<?php

namespace Tests\Unit;

use App\Models\SeriesTask;
use App\Models\SeriesTaskStudentGenerationState;
use App\Services\SeriesTaskRecurrenceService;
use Carbon\Carbon;
use Tests\TestCase;

class SeriesTaskRecurrenceServiceTest extends TestCase
{
    public function test_weekly_recurrence_accepts_text_weekday_keys(): void
    {
        $service = app(SeriesTaskRecurrenceService::class);
        $task = new SeriesTask([
            'recurrence_kind' => 'weekly',
            'recurrence_weekdays' => 'mon,wed',
        ]);

        $this->assertTrue($service->shouldGenerateOn($task, Carbon::parse('2026-04-27')));
        $this->assertTrue($service->shouldGenerateOn($task, Carbon::parse('2026-04-29')));
        $this->assertFalse($service->shouldGenerateOn($task, Carbon::parse('2026-04-28')));
    }

    public function test_monthly_recurrence_clamps_to_month_end(): void
    {
        $service = app(SeriesTaskRecurrenceService::class);
        $task = new SeriesTask([
            'recurrence_kind' => 'monthly',
            'recurrence_day_of_month' => 31,
        ]);

        $this->assertTrue($service->shouldGenerateOn($task, Carbon::parse('2026-02-28')));
        $this->assertFalse($service->shouldGenerateOn($task, Carbon::parse('2026-02-27')));
    }

    public function test_candidate_dates_skip_last_generated_and_pause_fences(): void
    {
        $service = app(SeriesTaskRecurrenceService::class);
        $task = new SeriesTask([
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
        ]);
        $state = new SeriesTaskStudentGenerationState([
            'start_date' => '2026-04-20',
            'last_generated_date' => '2026-04-27',
            'paused_through_date' => '2026-04-28',
        ]);

        $dates = collect($service->candidateDatesForState(
            $task,
            $state,
            Carbon::parse('2026-04-30')->startOfDay()
        ))->map(fn (Carbon $date): string => $date->toDateString())->all();

        $this->assertSame(['2026-04-29', '2026-04-30'], $dates);
    }
}
