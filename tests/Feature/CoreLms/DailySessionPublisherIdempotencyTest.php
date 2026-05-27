<?php

namespace Tests\Feature\CoreLms;

use App\Models\MainDailySessionMainTask;
use App\Models\MainDailySessionMainTaskAttachment;
use App\Models\MainDailySessionStudentAssignment;
use App\Models\MainDailySessionSubscription;
use App\Models\MainDailySessionTemplate;
use App\Models\MainDailySessionVersion;
use App\Models\MainDailySessionVersionTask;
use App\Models\User;
use App\Services\AutomatedTaskAssignmentService;
use App\Services\AutomatedTaskRecurrenceService;
use App\Services\DailySessionPublisher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

class DailySessionPublisherIdempotencyTest extends TestCase
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

    public function test_generate_for_student_creates_per_student_snapshots_and_is_idempotent_on_same_day(): void
    {
        $today = Carbon::parse('2026-04-29')->startOfDay();
        $teacher = User::factory()->create();
        $fixture = $this->createPublisherFixture($teacher);
        $studentASubscription = MainDailySessionSubscription::query()
            ->where('student_id', $fixture['student_a_id'])
            ->with('template')
            ->firstOrFail();
        $studentAAssignment = app(AutomatedTaskAssignmentService::class)->resolveEffectiveAssignment(
            $fixture['student_a_id'],
            $fixture['template_id'],
            $today
        );
        $studentACandidateDates = collect(app(AutomatedTaskRecurrenceService::class)->candidateDatesForSubscription(
            $studentASubscription->template,
            $studentASubscription,
            $today
        ))->map(fn (Carbon $date): string => $date->toDateString())->all();
        $studentAShouldGenerateToday = app(AutomatedTaskRecurrenceService::class)->shouldGenerateOn(
            $studentASubscription->template,
            $today
        );

        $this->assertTrue($this->publisher->needsGenerationForStudent($fixture['student_a_id'], $today));
        $this->assertTrue($this->publisher->needsGenerationForStudent($fixture['student_b_id'], $today));
        $this->assertNotNull($studentAAssignment);
        $this->assertTrue($studentAShouldGenerateToday);
        $this->assertSame('2026-04-20', $studentASubscription->start_at?->toDateString());
        $this->assertNull($studentASubscription->last_generated_date);
        $this->assertSame(['2026-04-29'], $studentACandidateDates);

        $this->publisher->generateForStudent($fixture['student_a_id'], $today);
        $this->publisher->generateForStudent($fixture['student_b_id'], $today);

        $firstCounts = $this->tableCounts();

        $this->assertSame(1, $firstCounts['units'], json_encode($firstCounts));
        $this->assertSame(2, $firstCounts['class_sessions']);
        $this->assertSame(2, $firstCounts['session_materials']);
        $this->assertSame(3, $firstCounts['session_tasks']);
        $this->assertSame(3, $firstCounts['session_task_student']);
        $this->assertSame(4, $firstCounts['attachment_files']);

        $studentATaskDescriptions = DB::table('session_tasks')
            ->join('class_sessions', 'class_sessions.id', '=', 'session_tasks.class_session_id')
            ->where('class_sessions.student_id', $fixture['student_a_id'])
            ->orderBy('session_tasks.sort')
            ->pluck('session_tasks.description')
            ->all();
        $studentAVersions = DB::table('session_tasks')
            ->join('class_sessions', 'class_sessions.id', '=', 'session_tasks.class_session_id')
            ->where('class_sessions.student_id', $fixture['student_a_id'])
            ->orderBy('session_tasks.sort')
            ->pluck('session_tasks.version_display_name_snapshot')
            ->all();

        $studentBTaskDescriptions = DB::table('session_tasks')
            ->join('class_sessions', 'class_sessions.id', '=', 'session_tasks.class_session_id')
            ->where('class_sessions.student_id', $fixture['student_b_id'])
            ->orderBy('session_tasks.sort')
            ->pluck('session_tasks.description')
            ->all();

        $this->assertSame([
            'Read the passage and answer the prompt.',
            'Read aloud to a parent.',
        ], $studentATaskDescriptions);
        $this->assertSame(['Reading 1', 'Reading 1'], $studentAVersions);
        $this->assertSame(['Trace each word carefully.'], $studentBTaskDescriptions);
        $studentAAttachmentTitles = DB::table('attachment_files')
            ->join('session_tasks', 'session_tasks.id', '=', 'attachment_files.session_task_id')
            ->join('class_sessions', 'class_sessions.id', '=', 'session_tasks.class_session_id')
            ->where('class_sessions.student_id', $fixture['student_a_id'])
            ->orderBy('attachment_files.id')
            ->pluck('attachment_files.title')
            ->all();
        $studentBAttachmentTitles = DB::table('attachment_files')
            ->join('session_tasks', 'session_tasks.id', '=', 'attachment_files.session_task_id')
            ->join('class_sessions', 'class_sessions.id', '=', 'session_tasks.class_session_id')
            ->where('class_sessions.student_id', $fixture['student_b_id'])
            ->orderBy('attachment_files.id')
            ->pluck('attachment_files.title')
            ->all();

        $this->assertSame(['Reading A', 'Reading B'], $studentAAttachmentTitles);
        $this->assertSame(['Reading A', 'Reading B'], $studentBAttachmentTitles);

        $this->assertDatabaseHas('main_daily_session_subscriptions', [
            'student_id' => $fixture['student_a_id'],
            'main_daily_session_template_id' => $fixture['template_id'],
            'last_generated_date' => $today->toDateString(),
        ]);
        $this->assertDatabaseHas('main_daily_session_subscriptions', [
            'student_id' => $fixture['student_b_id'],
            'main_daily_session_template_id' => $fixture['template_id'],
            'last_generated_date' => $today->toDateString(),
        ]);

        $this->publisher->generateForStudent($fixture['student_a_id'], $today);
        $this->publisher->generateForStudent($fixture['student_b_id'], $today);

        $this->assertSame($firstCounts, $this->tableCounts());
    }

    public function test_generate_for_student_skips_active_subscription_when_no_assignment_covers_the_date(): void
    {
        $today = Carbon::parse('2026-04-29')->startOfDay();
        $teacher = User::factory()->create();
        $fixture = $this->createPublisherFixture($teacher);

        $this->assertTrue($this->publisher->needsGenerationForStudent($fixture['student_unassigned_id'], $today));

        $this->publisher->generateForStudent($fixture['student_unassigned_id'], $today);

        $this->assertSame(0, DB::table('class_sessions')->count());
        $this->assertSame(0, DB::table('session_tasks')->count());
        $this->assertDatabaseHas('main_daily_session_subscriptions', [
            'student_id' => $fixture['student_unassigned_id'],
            'main_daily_session_template_id' => $fixture['template_id'],
            'last_generated_date' => null,
        ]);
    }

    public function test_generate_for_student_logs_warning_and_skips_when_multiple_versions_are_effective(): void
    {
        $today = Carbon::parse('2026-04-29')->startOfDay();
        $teacher = User::factory()->create();
        $fixture = $this->createPublisherFixture($teacher);
        Log::spy();

        $versionBId = MainDailySessionVersion::query()
            ->where('main_daily_session_template_id', $fixture['template_id'])
            ->where('display_name', 'Reading 2')
            ->value('id');

        MainDailySessionStudentAssignment::create([
            'student_id' => $fixture['student_a_id'],
            'main_daily_session_template_id' => $fixture['template_id'],
            'version_id' => $versionBId,
            'effective_from_date' => '2026-04-21',
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
        ]);

        $this->publisher->generateForStudent($fixture['student_a_id'], $today);

        $this->assertSame(0, DB::table('class_sessions')->count());
        $this->assertSame(0, DB::table('session_tasks')->count());
        $this->assertSame(0, DB::table('session_task_student')->count());
        $this->assertSame(0, DB::table('attachment_files')->count());

        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(function (string $message, array $context) use ($fixture, $today): bool {
                return $message === 'Automated Task generation skipped because multiple effective version assignments were found.'
                    && $context['student_id'] === $fixture['student_a_id']
                    && $context['template_id'] === $fixture['template_id']
                    && $context['date'] === $today->toDateString()
                    && count($context['assignment_ids']) === 2;
            });
    }

    public function test_generate_for_student_skips_when_student_has_no_active_subject_link_for_the_template_subject(): void
    {
        $today = Carbon::parse('2026-04-29')->startOfDay();
        $teacher = User::factory()->create();
        $fixture = $this->createPublisherFixture($teacher);

        DB::table('students_subjects')
            ->where('student_id', $fixture['student_a_id'])
            ->delete();

        $this->publisher->generateForStudent($fixture['student_a_id'], $today);

        $this->assertSame(0, DB::table('class_sessions')->count());
        $this->assertSame(0, DB::table('session_tasks')->count());
        $this->assertDatabaseHas('main_daily_session_subscriptions', [
            'student_id' => $fixture['student_a_id'],
            'main_daily_session_template_id' => $fixture['template_id'],
            'last_generated_date' => null,
        ]);
    }

    public function test_generate_for_student_throws_when_no_current_academic_year_is_configured(): void
    {
        $today = Carbon::parse('2026-04-29')->startOfDay();
        $teacher = User::factory()->create();
        $fixture = $this->createPublisherFixture($teacher);

        DB::table('academic_years')->update(['is_current' => 0]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No current academic year is configured.');

        $this->publisher->generateForStudent($fixture['student_a_id'], $today);
    }

    public function test_generate_for_student_throws_when_multiple_current_academic_years_are_configured(): void
    {
        $today = Carbon::parse('2026-04-29')->startOfDay();
        $teacher = User::factory()->create();
        $fixture = $this->createPublisherFixture($teacher);

        DB::table('academic_years')->insert([
            'id' => 2,
            'title' => '2027-2028',
            'is_current' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Multiple current academic years are configured.');

        $this->publisher->generateForStudent($fixture['student_a_id'], $today);
    }

    private function tableCounts(): array
    {
        return [
            'units' => DB::table('units')->count(),
            'class_sessions' => DB::table('class_sessions')->count(),
            'session_materials' => DB::table('session_materials')->count(),
            'session_tasks' => DB::table('session_tasks')->count(),
            'session_task_student' => DB::table('session_task_student')->count(),
            'attachment_files' => DB::table('attachment_files')->count(),
        ];
    }

    /**
     * @return array{
     *     template_id: int,
     *     student_a_id: int,
     *     student_b_id: int,
     *     student_unassigned_id: int
     * }
     */
    private function createPublisherFixture(User $teacher): array
    {
        $context = $this->createTeacherSubjectContext($teacher);
        $studentA = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
        $studentB = $this->enrollStudent($context, 'Lina', 'Hart', 'Nora');
        $studentUnassigned = $this->enrollStudent($context, 'Omar', 'Reed', 'Pia');

        $template = MainDailySessionTemplate::create([
            'title' => 'Daily Reading',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'weekly',
            'recurrence_weekdays' => '3',
            'recurrence_day_of_month' => null,
            'recurrence_interval' => 1,
            'status' => 'active',
        ]);

        $versionA = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Reading 1',
            'sort_order' => 1,
        ]);
        $versionB = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Reading 2',
            'sort_order' => 2,
        ]);

        $mainTaskOne = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $template->id,
            'title' => 'Read the passage',
            'description' => 'Read the passage and answer the prompt.',
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);
        $mainTaskTwo = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $template->id,
            'title' => 'Read aloud',
            'description' => null,
            'task_type_id' => 1,
            'default_points' => 3,
            'max_points' => 5,
            'sort_order' => 2,
        ]);

        $attachmentA = MainDailySessionMainTaskAttachment::create([
            'main_task_id' => $mainTaskOne->id,
            'type' => 'pdf',
            'title' => 'Reading A',
            'description' => 'Level A text',
            'path' => 'automated/a.pdf',
            'file_size' => 1200,
            'sort_order' => 1,
        ]);
        $attachmentB = MainDailySessionMainTaskAttachment::create([
            'main_task_id' => $mainTaskOne->id,
            'type' => 'pdf',
            'title' => 'Reading B',
            'description' => 'Level B text',
            'path' => 'automated/b.pdf',
            'file_size' => 1400,
            'sort_order' => 2,
        ]);

        MainDailySessionVersionTask::create([
            'version_id' => $versionA->id,
            'main_task_id' => $mainTaskOne->id,
            'description_override' => null,
            'sort_order' => 1,
        ]);
        MainDailySessionVersionTask::create([
            'version_id' => $versionA->id,
            'main_task_id' => $mainTaskTwo->id,
            'description_override' => 'Read aloud to a parent.',
            'sort_order' => 2,
        ]);
        MainDailySessionVersionTask::create([
            'version_id' => $versionB->id,
            'main_task_id' => $mainTaskOne->id,
            'description_override' => 'Trace each word carefully.',
            'sort_order' => 1,
        ]);

        foreach ([$studentA['student_id'], $studentB['student_id'], $studentUnassigned['student_id']] as $studentId) {
            MainDailySessionSubscription::create([
                'student_id' => $studentId,
                'main_daily_session_template_id' => $template->id,
                'is_active' => 1,
                'paused_at' => null,
                'start_at' => Carbon::parse('2026-04-20 09:00:00'),
                'end_at' => null,
                'last_generated_date' => null,
            ]);
        }

        MainDailySessionStudentAssignment::create([
            'student_id' => $studentA['student_id'],
            'main_daily_session_template_id' => $template->id,
            'version_id' => $versionA->id,
            'effective_from_date' => '2026-04-20',
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
        ]);
        MainDailySessionStudentAssignment::create([
            'student_id' => $studentB['student_id'],
            'main_daily_session_template_id' => $template->id,
            'version_id' => $versionB->id,
            'effective_from_date' => '2026-04-20',
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
        ]);

        return [
            'template_id' => $template->id,
            'student_a_id' => $studentA['student_id'],
            'student_b_id' => $studentB['student_id'],
            'student_unassigned_id' => $studentUnassigned['student_id'],
        ];
    }
}
