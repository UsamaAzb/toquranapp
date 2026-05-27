<?php

namespace Tests\Unit;

use App\Models\DifferentiatedTask;
use App\Models\DifferentiatedTaskStudentGenerationState;
use App\Services\DifferentiatedTaskRecurrenceService;
use Carbon\Carbon;
use Tests\TestCase;

class DifferentiatedTaskRecurrenceServiceTest extends TestCase
{
    public function test_weekly_recurrence_tolerates_text_and_numeric_weekdays(): void
    {
        $service = app(DifferentiatedTaskRecurrenceService::class);
        $task = new DifferentiatedTask([
            'recurrence_kind' => 'weekly',
            'recurrence_weekdays' => 'mon,3,5',
        ]);

        $this->assertTrue($service->shouldGenerateOn($task, Carbon::parse('2026-04-27')));
        $this->assertTrue($service->shouldGenerateOn($task, Carbon::parse('2026-04-29')));
        $this->assertTrue($service->shouldGenerateOn($task, Carbon::parse('2026-05-01')));
        $this->assertFalse($service->shouldGenerateOn($task, Carbon::parse('2026-04-30')));
    }

    public function test_candidate_dates_are_bounded_by_state_fences_and_seven_day_window(): void
    {
        $service = app(DifferentiatedTaskRecurrenceService::class);
        $task = new DifferentiatedTask([
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
        ]);
        $state = new DifferentiatedTaskStudentGenerationState([
            'is_active' => 1,
            'start_date' => '2026-04-20',
            'last_generated_date' => '2026-04-27',
            'paused_through_date' => null,
        ]);

        $dates = collect($service->candidateDatesForState(
            $task,
            $state,
            Carbon::parse('2026-04-30')->startOfDay()
        ))->map(fn (Carbon $date): string => $date->toDateString())->all();

        $this->assertSame(['2026-04-28', '2026-04-29', '2026-04-30'], $dates);
    }

    public function test_monthly_recurrence_clamps_to_month_end(): void
    {
        $service = app(DifferentiatedTaskRecurrenceService::class);
        $task = new DifferentiatedTask([
            'recurrence_kind' => 'monthly',
            'recurrence_day_of_month' => 31,
        ]);

        $this->assertTrue($service->shouldGenerateOn($task, Carbon::parse('2026-02-28')));
        $this->assertFalse($service->shouldGenerateOn($task, Carbon::parse('2026-02-27')));
    }
}
