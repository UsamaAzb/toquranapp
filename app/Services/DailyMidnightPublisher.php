<?php

namespace App\Services;

use App\Models\DifferentiatedTaskStudentGenerationState;
use App\Models\MainDailySessionSubscription;
use App\Models\SeriesTaskStudentGenerationState;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class DailyMidnightPublisher
{
    public function __construct(
        private readonly DailySessionPublisher $dailySessionPublisher,
        private readonly DifferentiatedTaskPublisher $differentiatedTaskPublisher,
        private readonly SeriesTaskPublisher $seriesTaskPublisher,
    ) {}

    public function publishForToday(?Carbon $today = null): void
    {
        $generationDate = ($today ?? now(config('app.timezone', 'Africa/Cairo')))->copy()->startOfDay();
        $firstFailure = null;

        $studentIds = MainDailySessionSubscription::query()
            ->where('is_active', 1)
            ->whereNull('paused_at')
            ->distinct()
            ->pluck('student_id');

        if (Schema::hasTable('differentiated_task_student_generation_states')) {
            $dtStudentIds = DifferentiatedTaskStudentGenerationState::query()
                ->active()
                ->distinct()
                ->pluck('student_id');

            $studentIds = $studentIds->merge($dtStudentIds)->unique()->values();
        }

        if (Schema::hasTable('series_task_student_generation_states')) {
            $seriesStudentIds = SeriesTaskStudentGenerationState::query()
                ->active()
                ->distinct()
                ->pluck('student_id');

            $studentIds = $studentIds->merge($seriesStudentIds)->unique()->values();
        }

        foreach ($studentIds as $studentId) {
            try {
                $this->dailySessionPublisher->generateForStudent((int) $studentId, $generationDate);
                $this->differentiatedTaskPublisher->generateForStudent((int) $studentId, $generationDate);
                $this->seriesTaskPublisher->generateForStudent((int) $studentId, $generationDate);
            } catch (\Throwable $exception) {
                if ($firstFailure === null) {
                    $firstFailure = $exception;

                    continue;
                }

                report($exception);
            }
        }

        if ($firstFailure) {
            throw $firstFailure;
        }
    }
}
