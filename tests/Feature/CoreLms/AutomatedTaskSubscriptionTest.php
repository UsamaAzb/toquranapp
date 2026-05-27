<?php

namespace Tests\Feature\CoreLms;

use App\Models\MainDailySessionMainTask;
use App\Models\MainDailySessionStudentAssignment;
use App\Models\MainDailySessionSubscription;
use App\Models\MainDailySessionTemplate;
use App\Models\MainDailySessionVersion;
use App\Models\MainDailySessionVersionTask;
use App\Models\User;
use App\Services\AutomatedTaskSubscriptionService;
use App\Services\DailySessionPublisher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

/**
 * Covers US2: subscription-state generation behavior — active vs paused
 * subscriptions, subscribed-but-unassigned skips, and state transitions
 * triggered by generation runs.
 *
 * Tests are added in Phase 4 (T023).
 */
class AutomatedTaskSubscriptionTest extends TestCase
{
    use CreatesAutomatedTaskTestingSchema;
    use RefreshDatabase;

    private DailySessionPublisher $publisher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAutomatedTaskSchema();
        $this->createAutomatedTaskGenerationRuntimeTables();
        $this->seedTaskTypes();
        $this->publisher = app(DailySessionPublisher::class);
    }

    public function test_paused_subscription_skips_generation_and_resume_does_not_backfill_the_skipped_occurrence(): void
    {
        Carbon::setTestNow('2026-04-30 08:00:00');

        try {
            $teacher = User::factory()->create();
            $fixture = $this->createSubscriptionFixture(
                $teacher,
                [
                    'template' => [
                        'recurrence_kind' => 'weekly',
                        'recurrence_weekdays' => '3',
                        'status' => 'active',
                    ],
                    'subscription' => [
                        'paused_at' => Carbon::parse('2026-04-29 07:00:00'),
                        'start_at' => Carbon::parse('2026-04-20 09:00:00'),
                    ],
                    'assignment' => [
                        'effective_from_date' => '2026-04-20',
                    ],
                ]
            );

            $pausedDay = Carbon::parse('2026-04-29')->startOfDay();
            $this->assertFalse($this->publisher->needsGenerationForStudent($fixture['student_id'], $pausedDay));

            $this->publisher->generateForStudent($fixture['student_id'], $pausedDay);

            $this->assertSame(0, DB::table('class_sessions')->count());
            $this->assertDatabaseHas('main_daily_session_subscriptions', [
                'id' => $fixture['subscription_id'],
                'last_generated_date' => null,
            ]);

            app(AutomatedTaskSubscriptionService::class)->resume($fixture['student_id'], $fixture['template_id']);

            $nextDay = Carbon::parse('2026-04-30')->startOfDay();
            $this->publisher->generateForStudent($fixture['student_id'], $nextDay);

            $this->assertSame(0, DB::table('class_sessions')->count());
            $this->assertDatabaseHas('main_daily_session_subscriptions', [
                'id' => $fixture['subscription_id'],
                'last_generated_date' => null,
                'paused_through_date' => '2026-04-30',
            ]);

            $nextWeeklyOccurrence = Carbon::parse('2026-05-06')->startOfDay();
            $this->publisher->generateForStudent($fixture['student_id'], $nextWeeklyOccurrence);

            $this->assertDatabaseHas('class_sessions', [
                'student_id' => $fixture['student_id'],
                'main_daily_session_template_id' => $fixture['template_id'],
                'generated_for_date' => '2026-05-06',
            ]);
            $this->assertDatabaseHas('main_daily_session_subscriptions', [
                'id' => $fixture['subscription_id'],
                'last_generated_date' => '2026-05-06',
                'paused_through_date' => '2026-04-30',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_active_subscription_generates_on_recurrence_day(): void
    {
        $teacher = User::factory()->create();
        $fixture = $this->createSubscriptionFixture(
            $teacher,
            [
                'template' => [
                    'recurrence_kind' => 'weekly',
                    'recurrence_weekdays' => '3',
                    'status' => 'active',
                ],
                'subscription' => [
                    'start_at' => Carbon::parse('2026-04-20 09:00:00'),
                ],
            ]
        );

        $this->publisher->generateForStudent($fixture['student_id'], Carbon::parse('2026-04-29')->startOfDay());

        $this->assertDatabaseHas('class_sessions', [
            'student_id' => $fixture['student_id'],
            'main_daily_session_template_id' => $fixture['template_id'],
            'generated_for_date' => '2026-04-29',
        ]);
        $this->assertDatabaseHas('main_daily_session_subscriptions', [
            'id' => $fixture['subscription_id'],
            'last_generated_date' => '2026-04-29',
        ]);
    }

    public function test_active_subscription_does_not_generate_on_non_recurrence_day(): void
    {
        $teacher = User::factory()->create();
        $fixture = $this->createSubscriptionFixture(
            $teacher,
            [
                'template' => [
                    'recurrence_kind' => 'weekly',
                    'recurrence_weekdays' => '3',
                    'status' => 'active',
                ],
                'subscription' => [
                    'start_at' => Carbon::parse('2026-04-30 09:00:00'),
                ],
                'assignment' => [
                    'effective_from_date' => '2026-04-30',
                ],
            ]
        );

        $this->publisher->generateForStudent($fixture['student_id'], Carbon::parse('2026-04-30')->startOfDay());

        $this->assertSame(0, DB::table('class_sessions')->count());
        $this->assertDatabaseHas('main_daily_session_subscriptions', [
            'id' => $fixture['subscription_id'],
            'last_generated_date' => null,
        ]);
    }

    public function test_deactivated_subscription_does_not_generate_even_when_an_assignment_exists(): void
    {
        $teacher = User::factory()->create();
        $fixture = $this->createSubscriptionFixture(
            $teacher,
            [
                'template' => [
                    'recurrence_kind' => 'daily',
                    'recurrence_interval' => 1,
                    'status' => 'active',
                ],
                'subscription' => [
                    'is_active' => 0,
                    'start_at' => Carbon::parse('2026-04-20 09:00:00'),
                    'end_at' => Carbon::parse('2026-04-24 12:00:00'),
                ],
                'assignment' => [
                    'effective_from_date' => '2026-04-20',
                ],
            ]
        );

        $today = Carbon::parse('2026-04-25')->startOfDay();

        $this->assertFalse($this->publisher->needsGenerationForStudent($fixture['student_id'], $today));

        $this->publisher->generateForStudent($fixture['student_id'], $today);

        $this->assertSame(0, DB::table('class_sessions')->count());
        $this->assertDatabaseHas('main_daily_session_subscriptions', [
            'id' => $fixture['subscription_id'],
            'is_active' => 0,
            'last_generated_date' => null,
        ]);
    }

    /**
     * @param  array{
     *     template?: array<string, mixed>,
     *     subscription?: array<string, mixed>,
     *     assignment?: array<string, mixed>,
     * }  $overrides
     * @return array{student_id: int, template_id: int, subscription_id: int}
     */
    private function createSubscriptionFixture(User $teacher, array $overrides = []): array
    {
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');

        $template = MainDailySessionTemplate::create(array_merge([
            'title' => 'Subscription generation template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_weekdays' => null,
            'recurrence_day_of_month' => null,
            'recurrence_interval' => 1,
            'status' => 'active',
        ], $overrides['template'] ?? []));

        $version = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Version A',
            'sort_order' => 1,
        ]);

        $task = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $template->id,
            'title' => 'Read and respond',
            'description' => 'Read the text and respond.',
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);

        MainDailySessionVersionTask::create([
            'version_id' => $version->id,
            'main_task_id' => $task->id,
            'description_override' => 'Student-facing prompt.',
            'sort_order' => 1,
        ]);

        $subscription = MainDailySessionSubscription::create(array_merge([
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
            'is_active' => 1,
            'paused_at' => null,
            'start_at' => Carbon::parse('2026-04-20 09:00:00'),
            'end_at' => null,
            'last_generated_date' => null,
        ], $overrides['subscription'] ?? []));

        MainDailySessionStudentAssignment::create(array_merge([
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
            'version_id' => $version->id,
            'effective_from_date' => '2026-04-20',
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
        ], $overrides['assignment'] ?? []));

        return [
            'student_id' => $student['student_id'],
            'template_id' => $template->id,
            'subscription_id' => $subscription->id,
        ];
    }
}
