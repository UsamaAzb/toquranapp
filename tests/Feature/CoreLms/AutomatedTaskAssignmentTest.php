<?php

namespace Tests\Feature\CoreLms;

use App\Livewire\Teacher\AutomatedTaskAssignmentModal;
use App\Livewire\Teacher\AutomatedTasksBoard;
use App\Models\MainDailySessionMainTask;
use App\Models\MainDailySessionStudentAssignment;
use App\Models\MainDailySessionSubscription;
use App\Models\MainDailySessionTemplate;
use App\Models\MainDailySessionVersion;
use App\Models\MainDailySessionVersionTask;
use App\Models\User;
use App\Services\AutomatedTaskAssignmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

class AutomatedTaskAssignmentTest extends TestCase
{
    use CreatesAutomatedTaskTestingSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAutomatedTaskSchema();
        $this->seedTaskTypes();
    }

    public function test_assignment_modal_supports_name_disambiguation_and_saves_subscription_and_assignment_state(): void
    {
        $teacher = User::factory()->create();
        $contextA = $this->createTeacherSubjectContext($teacher, null, 'Class A');
        $contextB = $this->createTeacherSubjectContext($teacher, $contextA['subject_id'], 'Class B');

        $studentA = $this->enrollStudent($contextA, 'Omar', 'Reed', 'Mona', 'Stone');
        $studentB = $this->enrollStudent($contextB, 'Omar', 'Reed', 'Nina', 'Stone');

        $template = MainDailySessionTemplate::create([
            'title' => 'Assignment template',
            'subject_id' => $contextA['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $versionA = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Version A',
            'sort_order' => 1,
        ]);

        $versionB = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Version B',
            'sort_order' => 2,
        ]);

        $task = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $template->id,
            'title' => 'Reading prompt',
            'description' => 'Read and write.',
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);

        MainDailySessionVersionTask::create([
            'version_id' => $versionA->id,
            'main_task_id' => $task->id,
            'description_override' => 'Ready for assignment.',
            'sort_order' => 1,
        ]);

        MainDailySessionVersionTask::create([
            'version_id' => $versionB->id,
            'main_task_id' => $task->id,
            'description_override' => 'Also ready for assignment.',
            'sort_order' => 1,
        ]);

        $this->actingAs($teacher);

        $component = Livewire::test(AutomatedTaskAssignmentModal::class)
            ->call('open', $template->id)
            ->assertSee('Omar Reed')
            ->assertSee('Mona Stone')
            ->assertSee('Nina Stone')
            ->assertSee('Class A')
            ->assertSee('Class B')
            ->assertSee((string) $studentA['student_id'])
            ->assertSee((string) $studentB['student_id']);

        $component->set("rowForms.{$studentA['student_id']}.subscription_state", 'active')
            ->set("rowForms.{$studentA['student_id']}.version_id", $versionA->id)
            ->call('saveRow', $studentA['student_id']);

        $component->set("rowForms.{$studentB['student_id']}.subscription_state", 'active')
            ->set("rowForms.{$studentB['student_id']}.version_id", '')
            ->call('saveRow', $studentB['student_id']);

        $this->assertDatabaseHas('main_daily_session_subscriptions', [
            'student_id' => $studentA['student_id'],
            'main_daily_session_template_id' => $template->id,
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('main_daily_session_student_assignments', [
            'student_id' => $studentA['student_id'],
            'main_daily_session_template_id' => $template->id,
            'version_id' => $versionA->id,
        ]);

        $this->assertDatabaseHas('main_daily_session_subscriptions', [
            'student_id' => $studentB['student_id'],
            'main_daily_session_template_id' => $template->id,
            'is_active' => 1,
        ]);

        $this->assertDatabaseMissing('main_daily_session_student_assignments', [
            'student_id' => $studentB['student_id'],
            'main_daily_session_template_id' => $template->id,
        ]);
    }

    public function test_assignment_modal_only_lists_teacher_owned_students_once_even_with_duplicate_enrollments(): void
    {
        $teacher = User::factory()->create();
        $otherTeacher = User::factory()->create();
        $contextA = $this->createTeacherSubjectContext($teacher, null, 'Class A');
        $contextB = $this->createTeacherSubjectContext($teacher, $contextA['subject_id'], 'Class B');
        $otherContext = $this->createTeacherSubjectContext($otherTeacher, $contextA['subject_id'], 'Class C');

        $ownedStudent = $this->enrollStudent($contextA, 'Ava', 'Stone', 'Mona');
        $otherStudent = $this->enrollStudent($otherContext, 'Nora', 'Lane', 'Pia');

        DB::table('students_subjects')->insert([
            'student_id' => $ownedStudent['student_id'],
            'grade_level_subject_id' => $contextB['grade_level_subject_id'],
            'academic_year_id' => 1,
            'status' => 'active',
            'class_subject_id' => $contextB['class_subject_id'],
        ]);

        $template = MainDailySessionTemplate::create([
            'title' => 'Scoped template',
            'subject_id' => $contextA['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $this->actingAs($teacher);

        $component = Livewire::test(AutomatedTaskAssignmentModal::class)
            ->call('open', $template->id)
            ->assertDontSee('Nora Lane');

        $rendered = $component->instance()->render()->getData();
        $rows = $rendered['rows'];
        $summary = $rendered['summary'];

        $this->assertCount(1, $rows);
        $this->assertSame($ownedStudent['student_id'], $rows[0]['student_id']);
        $this->assertSame(1, $summary['not_subscribed']);
    }

    public function test_bulk_version_manager_moves_students_between_versions(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-25 10:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $assignedHereStudent = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
            $assignedElsewhereStudent = $this->enrollStudent($context, 'Lina', 'Hart', 'Nora');
            $availableStudent = $this->enrollStudent($context, 'Omar', 'Reed', 'Pia');
            $suspendedStudent = $this->enrollStudent($context, 'Yara', 'West', 'Dina');
            [$template, $versionA, $versionB] = $this->createAssignableTemplateWithVersions($teacher, $context);

            DB::table('students')
                ->where('id', $suspendedStudent['student_id'])
                ->update(['account_status' => 'suspended']);

            foreach ([$assignedHereStudent, $assignedElsewhereStudent, $suspendedStudent] as $student) {
                MainDailySessionSubscription::create([
                    'student_id' => $student['student_id'],
                    'main_daily_session_template_id' => $template->id,
                    'is_active' => 1,
                    'paused_at' => null,
                    'start_at' => Carbon::parse('2026-04-20 09:00:00'),
                    'end_at' => null,
                    'last_generated_date' => null,
                ]);
            }

            MainDailySessionStudentAssignment::create([
                'student_id' => $assignedHereStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionA->id,
                'effective_from_date' => '2026-04-20',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            MainDailySessionStudentAssignment::create([
                'student_id' => $assignedElsewhereStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionB->id,
                'effective_from_date' => '2026-04-20',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            MainDailySessionStudentAssignment::create([
                'student_id' => $suspendedStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionA->id,
                'effective_from_date' => '2026-04-20',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            $this->actingAs($teacher);

            $component = Livewire::test(AutomatedTaskAssignmentModal::class)
                ->call('open', $template->id, $versionA->id)
                ->assertSet('activeVersionId', $versionA->id)
                ->assertSet("selectedStudentIds.{$assignedHereStudent['student_id']}", true)
                ->assertSet("selectedStudentIds.{$assignedElsewhereStudent['student_id']}", false)
                ->assertSet("selectedStudentIds.{$availableStudent['student_id']}", false)
                ->assertDontSee('Yara West');

            $rendered = $component->instance()->render()->getData();
            $bulkSections = $rendered['bulkSections'];

            $this->assertCount(1, $bulkSections['assigned_here']['rows']);
            $this->assertSame($assignedHereStudent['student_id'], $bulkSections['assigned_here']['rows'][0]['student_id']);
            $this->assertCount(1, $bulkSections['assigned_elsewhere']['rows']);
            $this->assertSame($assignedElsewhereStudent['student_id'], $bulkSections['assigned_elsewhere']['rows'][0]['student_id']);
            $this->assertCount(1, $bulkSections['unassigned']['rows']);
            $expectedUnassigned = [$availableStudent['student_id']];
            $actualUnassigned = collect($bulkSections['unassigned']['rows'])->pluck('student_id')->all();
            sort($expectedUnassigned);
            sort($actualUnassigned);
            $this->assertSame($expectedUnassigned, $actualUnassigned);

            $component->set("selectedStudentIds.{$assignedHereStudent['student_id']}", false)
                ->set("selectedStudentIds.{$assignedElsewhereStudent['student_id']}", true)
                ->set("selectedStudentIds.{$availableStudent['student_id']}", true)
                ->call('saveBulk');

            $assignedHereStillEffectiveToday = MainDailySessionStudentAssignment::query()
                ->where('student_id', $assignedHereStudent['student_id'])
                ->where('main_daily_session_template_id', $template->id)
                ->where('effective_from_date', '<=', '2026-04-25')
                ->where(function ($query): void {
                    $query->whereNull('effective_to_date')
                        ->orWhere('effective_to_date', '>=', '2026-04-25');
                })
                ->exists();

            $this->assertFalse($assignedHereStillEffectiveToday);

            $this->assertDatabaseHas('main_daily_session_subscriptions', [
                'student_id' => $assignedHereStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'is_active' => 1,
            ]);

            $movedAssignment = MainDailySessionStudentAssignment::query()
                ->where('student_id', $assignedElsewhereStudent['student_id'])
                ->where('main_daily_session_template_id', $template->id)
                ->where('version_id', $versionA->id)
                ->whereNull('effective_to_date')
                ->firstOrFail();

            $this->assertSame('2026-04-25', $movedAssignment->effective_from_date->toDateString());

            $this->assertDatabaseHas('main_daily_session_student_assignments', [
                'student_id' => $assignedElsewhereStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionB->id,
                'effective_to_date' => '2026-04-24',
            ]);

            $this->assertDatabaseHas('main_daily_session_student_assignment_history', [
                'student_id' => $assignedElsewhereStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'event_type' => 'unassign',
                'from_version_id' => $versionB->id,
                'to_version_id' => null,
                'actor_user_id' => $teacher->id,
            ]);
            $this->assertDatabaseHas('main_daily_session_student_assignment_history', [
                'student_id' => $assignedElsewhereStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'event_type' => 'assign',
                'from_version_id' => null,
                'to_version_id' => $versionA->id,
                'actor_user_id' => $teacher->id,
            ]);

            $this->assertDatabaseHas('main_daily_session_subscriptions', [
                'student_id' => $availableStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'is_active' => 1,
            ]);

            $availableAssignment = MainDailySessionStudentAssignment::query()
                ->where('student_id', $availableStudent['student_id'])
                ->where('main_daily_session_template_id', $template->id)
                ->where('version_id', $versionA->id)
                ->whereNull('effective_to_date')
                ->firstOrFail();

            $this->assertSame('2026-04-25', $availableAssignment->effective_from_date->toDateString());

            $suspendedAssignment = MainDailySessionStudentAssignment::query()
                ->where('student_id', $suspendedStudent['student_id'])
                ->where('main_daily_session_template_id', $template->id)
                ->whereNull('effective_to_date')
                ->firstOrFail();

            $this->assertSame($versionA->id, (int) $suspendedAssignment->version_id);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_bulk_version_manager_leaves_unselected_assigned_elsewhere_student_unchanged(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-25 10:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $assignedElsewhereStudent = $this->enrollStudent($context, 'Lina', 'Hart', 'Nora');
            [$template, $versionA, $versionB] = $this->createAssignableTemplateWithVersions($teacher, $context);

            MainDailySessionSubscription::create([
                'student_id' => $assignedElsewhereStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'is_active' => 1,
                'paused_at' => null,
                'start_at' => Carbon::parse('2026-04-20 09:00:00'),
                'end_at' => null,
                'last_generated_date' => null,
            ]);

            MainDailySessionStudentAssignment::create([
                'student_id' => $assignedElsewhereStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionB->id,
                'effective_from_date' => '2026-04-20',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            $this->actingAs($teacher);

            Livewire::test(AutomatedTaskAssignmentModal::class)
                ->call('open', $template->id, $versionA->id)
                ->assertSee('Assigned to another version of this template')
                ->assertSet("selectedStudentIds.{$assignedElsewhereStudent['student_id']}", false)
                ->call('saveBulk');

            $this->assertDatabaseHas('main_daily_session_student_assignments', [
                'student_id' => $assignedElsewhereStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionB->id,
                'effective_to_date' => null,
            ]);
            $this->assertDatabaseMissing('main_daily_session_student_assignments', [
                'student_id' => $assignedElsewhereStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionA->id,
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_assignment_save_rolls_back_subscription_changes_when_assignment_write_fails(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Omar', 'Reed', 'Mona', 'Stone');

        $template = MainDailySessionTemplate::create([
            'title' => 'Atomic assignment template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $version = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Version A',
            'sort_order' => 1,
        ]);

        $task = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $template->id,
            'title' => 'Reading prompt',
            'description' => 'Read and write.',
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);

        MainDailySessionVersionTask::create([
            'version_id' => $version->id,
            'main_task_id' => $task->id,
            'description_override' => 'Ready for assignment.',
            'sort_order' => 1,
        ]);

        app()->bind(AutomatedTaskAssignmentService::class, fn () => new class extends AutomatedTaskAssignmentService
        {
            public function versionIsAssignable(int $versionId): bool
            {
                return true;
            }

            public function resolveEffectiveAssignment(
                int $studentId,
                int $templateId,
                Carbon $date,
                bool $lockForUpdate = false
            ): ?MainDailySessionStudentAssignment {
                return null;
            }

            public function createAssignment(
                int $studentId,
                int $templateId,
                int $versionId,
                Carbon $effectiveFrom,
                int $actorUserId
            ): MainDailySessionStudentAssignment {
                throw new \RuntimeException('Assignment write failed.');
            }
        });

        $this->actingAs($teacher);

        $component = Livewire::test(AutomatedTaskAssignmentModal::class)
            ->call('open', $template->id)
            ->set("rowForms.{$student['student_id']}.subscription_state", 'active')
            ->set("rowForms.{$student['student_id']}.version_id", $version->id);

        try {
            $component->call('saveRow', $student['student_id']);
            $this->fail('Expected assignment failure was not raised.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Assignment write failed.', $exception->getMessage());
        }

        $this->assertDatabaseMissing('main_daily_session_subscriptions', [
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
        ]);

        $this->assertDatabaseMissing('main_daily_session_student_assignments', [
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
        ]);
    }

    public function test_paused_student_remains_paused_and_teacher_cannot_change_subscription_state(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $activeStudent = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
        $pausedStudent = $this->enrollStudent($context, 'Lina', 'Hart', 'Nora');
        $inactiveStudent = $this->enrollStudent($context, 'Omar', 'Reed', 'Pia');

        $template = MainDailySessionTemplate::create([
            'title' => 'State transition template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $version = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Version A',
            'sort_order' => 1,
        ]);

        $task = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $template->id,
            'title' => 'Reading prompt',
            'description' => 'Read and write.',
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);

        MainDailySessionVersionTask::create([
            'version_id' => $version->id,
            'main_task_id' => $task->id,
            'description_override' => 'Ready for assignment.',
            'sort_order' => 1,
        ]);

        MainDailySessionSubscription::create([
            'student_id' => $activeStudent['student_id'],
            'main_daily_session_template_id' => $template->id,
            'is_active' => 1,
            'paused_at' => null,
            'start_at' => now(),
            'end_at' => null,
            'last_generated_date' => null,
        ]);

        MainDailySessionSubscription::create([
            'student_id' => $pausedStudent['student_id'],
            'main_daily_session_template_id' => $template->id,
            'is_active' => 1,
            'paused_at' => now(),
            'start_at' => now(),
            'end_at' => null,
            'last_generated_date' => null,
        ]);

        MainDailySessionSubscription::create([
            'student_id' => $inactiveStudent['student_id'],
            'main_daily_session_template_id' => $template->id,
            'is_active' => 0,
            'paused_at' => null,
            'start_at' => now()->subDay(),
            'end_at' => now()->subHour(),
            'last_generated_date' => null,
        ]);

        $this->actingAs($teacher);

        $component = Livewire::test(AutomatedTaskAssignmentModal::class)
            ->call('open', $template->id)
            ->set('statusFilter', 'active');

        $rendered = $component->instance()->render()->getData();
        $summary = $rendered['summary'];
        $rows = $rendered['rows'];

        $this->assertSame(1, $summary['active']);
        $this->assertSame(1, $summary['paused']);
        $this->assertSame(1, $summary['not_subscribed']);
        $this->assertCount(1, $rows);
        $this->assertSame($activeStudent['student_id'], $rows[0]['student_id']);

        $component->set('statusFilter', 'all')
            ->set("rowForms.{$pausedStudent['student_id']}.subscription_state", 'not_subscribed')
            ->set("rowForms.{$pausedStudent['student_id']}.version_id", '')
            ->call('saveRow', $pausedStudent['student_id']);

        $this->assertDatabaseHas('main_daily_session_subscriptions', [
            'student_id' => $pausedStudent['student_id'],
            'main_daily_session_template_id' => $template->id,
            'is_active' => 1,
        ]);

        $subscription = MainDailySessionSubscription::query()
            ->where('student_id', $pausedStudent['student_id'])
            ->where('main_daily_session_template_id', $template->id)
            ->firstOrFail();

        $this->assertNotNull($subscription->paused_at);
    }

    public function test_assignment_updates_preserve_subscription_anchor_and_clear_assignment_for_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-25 10:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');

            $template = MainDailySessionTemplate::create([
                'title' => 'Anchor-safe template',
                'subject_id' => $context['subject_id'],
                'created_by_user_id' => $teacher->id,
                'recurrence_kind' => 'monthly',
                'recurrence_interval' => 1,
                'status' => 'draft',
            ]);

            $versionA = MainDailySessionVersion::create([
                'main_daily_session_template_id' => $template->id,
                'display_name' => 'Version A',
                'sort_order' => 1,
            ]);

            $versionB = MainDailySessionVersion::create([
                'main_daily_session_template_id' => $template->id,
                'display_name' => 'Version B',
                'sort_order' => 2,
            ]);

            $task = MainDailySessionMainTask::create([
                'main_daily_session_template_id' => $template->id,
                'title' => 'Reading prompt',
                'description' => 'Read and write.',
                'task_type_id' => 1,
                'default_points' => 5,
                'max_points' => 10,
                'sort_order' => 1,
            ]);

            foreach ([$versionA, $versionB] as $version) {
                MainDailySessionVersionTask::create([
                    'version_id' => $version->id,
                    'main_task_id' => $task->id,
                    'description_override' => 'Ready for assignment.',
                    'sort_order' => 1,
                ]);
            }

            MainDailySessionSubscription::create([
                'student_id' => $student['student_id'],
                'main_daily_session_template_id' => $template->id,
                'is_active' => 1,
                'paused_at' => null,
                'start_at' => Carbon::parse('2026-04-15 09:00:00'),
                'end_at' => null,
                'last_generated_date' => null,
            ]);

            MainDailySessionStudentAssignment::create([
                'student_id' => $student['student_id'],
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionA->id,
                'effective_from_date' => '2026-04-20',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            $this->actingAs($teacher);

            $component = Livewire::test(AutomatedTaskAssignmentModal::class)
                ->call('open', $template->id)
                ->set("rowForms.{$student['student_id']}.subscription_state", 'active')
                ->set("rowForms.{$student['student_id']}.version_id", $versionB->id)
                ->call('saveRow', $student['student_id']);

            $subscription = MainDailySessionSubscription::query()
                ->where('student_id', $student['student_id'])
                ->where('main_daily_session_template_id', $template->id)
                ->firstOrFail();

            $this->assertSame('2026-04-15', $subscription->start_at->toDateString());

            $component->set("rowForms.{$student['student_id']}.version_id", '')
                ->call('saveRow', $student['student_id']);

            $hasAssignmentToday = MainDailySessionStudentAssignment::query()
                ->where('student_id', $student['student_id'])
                ->where('main_daily_session_template_id', $template->id)
                ->where('effective_from_date', '<=', '2026-04-25')
                ->where(function ($query): void {
                    $query->whereNull('effective_to_date')
                        ->orWhere('effective_to_date', '>=', '2026-04-25');
                })
                ->exists();

            $this->assertFalse($hasAssignmentToday);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_resubscribing_after_deactivation_starts_a_new_subscription_anchor(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-25 10:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Lina', 'Hart', 'Nora');

            $template = MainDailySessionTemplate::create([
                'title' => 'Resubscribe template',
                'subject_id' => $context['subject_id'],
                'created_by_user_id' => $teacher->id,
                'recurrence_kind' => 'monthly',
                'recurrence_interval' => 1,
                'status' => 'draft',
            ]);

            $version = MainDailySessionVersion::create([
                'main_daily_session_template_id' => $template->id,
                'display_name' => 'Version A',
                'sort_order' => 1,
            ]);

            $task = MainDailySessionMainTask::create([
                'main_daily_session_template_id' => $template->id,
                'title' => 'Reading prompt',
                'description' => 'Read and write.',
                'task_type_id' => 1,
                'default_points' => 5,
                'max_points' => 10,
                'sort_order' => 1,
            ]);

            MainDailySessionVersionTask::create([
                'version_id' => $version->id,
                'main_task_id' => $task->id,
                'description_override' => 'Ready for assignment.',
                'sort_order' => 1,
            ]);

            MainDailySessionSubscription::create([
                'student_id' => $student['student_id'],
                'main_daily_session_template_id' => $template->id,
                'is_active' => 0,
                'paused_at' => null,
                'start_at' => Carbon::parse('2026-04-15 09:00:00'),
                'end_at' => Carbon::parse('2026-04-20 12:00:00'),
                'last_generated_date' => null,
            ]);

            $this->actingAs($teacher);

            Livewire::test(AutomatedTaskAssignmentModal::class)
                ->call('open', $template->id)
                ->set("rowForms.{$student['student_id']}.subscription_state", 'active')
                ->set("rowForms.{$student['student_id']}.version_id", $version->id)
                ->call('saveRow', $student['student_id']);

            $subscription = MainDailySessionSubscription::query()
                ->where('student_id', $student['student_id'])
                ->where('main_daily_session_template_id', $template->id)
                ->firstOrFail();

            $this->assertTrue((bool) $subscription->is_active);
            $this->assertSame('2026-04-25', $subscription->start_at->toDateString());
            $this->assertNull($subscription->end_at);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_row_version_change_moves_student_and_closes_existing_assignment(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-25 10:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
            [$template, $versionA, $versionB] = $this->createAssignableTemplateWithVersions($teacher, $context);

            MainDailySessionSubscription::create([
                'student_id' => $student['student_id'],
                'main_daily_session_template_id' => $template->id,
                'is_active' => 1,
                'paused_at' => null,
                'start_at' => Carbon::parse('2026-04-20 09:00:00'),
                'end_at' => null,
                'last_generated_date' => null,
            ]);

            MainDailySessionStudentAssignment::create([
                'student_id' => $student['student_id'],
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionA->id,
                'effective_from_date' => '2026-04-20',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            $this->actingAs($teacher);

            Livewire::test(AutomatedTaskAssignmentModal::class)
                ->call('open', $template->id)
                ->set("rowForms.{$student['student_id']}.subscription_state", 'active')
                ->set("rowForms.{$student['student_id']}.version_id", $versionB->id)
                ->call('saveRow', $student['student_id']);

            $oldAssignment = MainDailySessionStudentAssignment::query()
                ->where('student_id', $student['student_id'])
                ->where('main_daily_session_template_id', $template->id)
                ->where('version_id', $versionA->id)
                ->firstOrFail();

            $newAssignment = MainDailySessionStudentAssignment::query()
                ->where('student_id', $student['student_id'])
                ->where('main_daily_session_template_id', $template->id)
                ->where('version_id', $versionB->id)
                ->firstOrFail();

            $this->assertSame('2026-04-20', $oldAssignment->effective_from_date->toDateString());
            $this->assertSame('2026-04-24', $oldAssignment->effective_to_date->toDateString());
            $this->assertSame('2026-04-25', $newAssignment->effective_from_date->toDateString());
            $this->assertNull($newAssignment->effective_to_date);

            $this->assertDatabaseHas('main_daily_session_student_assignment_history', [
                'student_id' => $student['student_id'],
                'main_daily_session_template_id' => $template->id,
                'event_type' => 'unassign',
                'from_version_id' => $versionA->id,
                'to_version_id' => null,
                'actor_user_id' => $teacher->id,
            ]);
            $this->assertDatabaseHas('main_daily_session_student_assignment_history', [
                'student_id' => $student['student_id'],
                'main_daily_session_template_id' => $template->id,
                'event_type' => 'assign',
                'from_version_id' => null,
                'from_version_display_name' => null,
                'to_version_id' => $versionB->id,
                'to_version_display_name' => 'Version B',
                'actor_user_id' => $teacher->id,
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_deleting_version_unassigns_current_and_future_students_and_writes_history(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-25 10:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $currentStudent = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
            $futureStudent = $this->enrollStudent($context, 'Lina', 'Hart', 'Nora');
            [$template, $versionA, $versionB] = $this->createAssignableTemplateWithVersions($teacher, $context);

            foreach ([$currentStudent, $futureStudent] as $student) {
                MainDailySessionSubscription::create([
                    'student_id' => $student['student_id'],
                    'main_daily_session_template_id' => $template->id,
                    'is_active' => 1,
                    'paused_at' => null,
                    'start_at' => Carbon::parse('2026-04-20 09:00:00'),
                    'end_at' => null,
                    'last_generated_date' => null,
                ]);
            }

            MainDailySessionStudentAssignment::create([
                'student_id' => $currentStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionB->id,
                'effective_from_date' => '2026-04-20',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            MainDailySessionStudentAssignment::create([
                'student_id' => $futureStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionB->id,
                'effective_from_date' => '2026-04-28',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            $this->actingAs($teacher);

            Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
                ->call('setActiveVersion', $template->id, $versionB->id)
                ->assertSee('Delete version')
                ->assertSee('2')
                ->assertSee('Ava Stone')
                ->assertSee('Lina Hart')
                ->call('deleteVersion', $versionB->id);

            $this->assertDatabaseMissing('main_daily_session_versions', [
                'id' => $versionB->id,
            ]);

            $this->assertDatabaseHas('main_daily_session_versions', [
                'id' => $versionA->id,
            ]);

            $closedAssignment = MainDailySessionStudentAssignment::query()
                ->where('student_id', $currentStudent['student_id'])
                ->where('main_daily_session_template_id', $template->id)
                ->where('version_id', $versionB->id)
                ->firstOrFail();

            $this->assertSame('2026-04-20', $closedAssignment->effective_from_date->toDateString());
            $this->assertSame('2026-04-24', $closedAssignment->effective_to_date->toDateString());

            $this->assertDatabaseMissing('main_daily_session_student_assignments', [
                'student_id' => $futureStudent['student_id'],
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionB->id,
            ]);

            $this->assertDatabaseCount('main_daily_session_student_assignment_history', 2);

            foreach ([$currentStudent, $futureStudent] as $student) {
                $this->assertDatabaseHas('main_daily_session_student_assignment_history', [
                    'student_id' => $student['student_id'],
                    'main_daily_session_template_id' => $template->id,
                    'event_type' => 'unassign',
                    'from_version_id' => $versionB->id,
                    'from_version_display_name' => 'Version B',
                    'to_version_id' => null,
                    'to_version_display_name' => null,
                    'actor_user_id' => $teacher->id,
                ]);
            }

            $rendered = Livewire::test(AutomatedTaskAssignmentModal::class)
                ->call('open', $template->id)
                ->set('statusFilter', 'unassigned')
                ->instance()
                ->render()
                ->getData();

            $this->assertSame(2, $rendered['summary']['unassigned']);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createAssignableTemplateWithVersions(User $teacher, array $context): array
    {
        $template = MainDailySessionTemplate::create([
            'title' => 'Assignable template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $versionA = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Version A',
            'sort_order' => 1,
        ]);

        $versionB = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Version B',
            'sort_order' => 2,
        ]);

        $task = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $template->id,
            'title' => 'Reading prompt',
            'description' => 'Read and write.',
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);

        foreach ([$versionA, $versionB] as $version) {
            MainDailySessionVersionTask::create([
                'version_id' => $version->id,
                'main_task_id' => $task->id,
                'description_override' => 'Ready for assignment.',
                'sort_order' => 1,
            ]);
        }

        return [$template, $versionA, $versionB];
    }

    public function test_teacher_cannot_set_active_student_to_paused_from_assignment_modal(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');

        $template = MainDailySessionTemplate::create([
            'title' => 'Pause blocked template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        MainDailySessionSubscription::create([
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
            'is_active' => 1,
            'paused_at' => null,
            'start_at' => now(),
            'end_at' => null,
            'last_generated_date' => null,
        ]);

        $this->actingAs($teacher);

        Livewire::test(AutomatedTaskAssignmentModal::class)
            ->call('open', $template->id)
            ->set("rowForms.{$student['student_id']}.subscription_state", 'paused')
            ->call('saveRow', $student['student_id'])
            ->assertHasErrors(['subscription_state']);

        $this->assertDatabaseHas('main_daily_session_subscriptions', [
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
            'is_active' => 1,
            'paused_at' => null,
        ]);
    }

    public function test_paused_student_version_assignment_does_not_change_subscription_state(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-25 10:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Lina', 'Hart', 'Nora');
            [$template, $versionA, $versionB] = $this->createAssignableTemplateWithVersions($teacher, $context);

            MainDailySessionSubscription::create([
                'student_id' => $student['student_id'],
                'main_daily_session_template_id' => $template->id,
                'is_active' => 1,
                'paused_at' => Carbon::parse('2026-04-20 09:00:00'),
                'start_at' => Carbon::parse('2026-04-10 09:00:00'),
                'end_at' => null,
                'last_generated_date' => null,
            ]);

            $this->actingAs($teacher);

            Livewire::test(AutomatedTaskAssignmentModal::class)
                ->call('open', $template->id)
                ->set("rowForms.{$student['student_id']}.subscription_state", 'paused')
                ->set("rowForms.{$student['student_id']}.version_id", $versionA->id)
                ->call('saveRow', $student['student_id']);

            $subscription = MainDailySessionSubscription::query()
                ->where('student_id', $student['student_id'])
                ->where('main_daily_session_template_id', $template->id)
                ->firstOrFail();

            $this->assertTrue($subscription->isPaused());
            $this->assertSame('2026-04-20', $subscription->paused_at->toDateString());
            $this->assertTrue((bool) $subscription->is_active);

            $this->assertDatabaseHas('main_daily_session_student_assignments', [
                'student_id' => $student['student_id'],
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionA->id,
            ]);

            Livewire::test(AutomatedTaskAssignmentModal::class)
                ->call('open', $template->id)
                ->set("rowForms.{$student['student_id']}.subscription_state", 'active')
                ->set("rowForms.{$student['student_id']}.version_id", $versionB->id)
                ->call('saveRow', $student['student_id']);

            $subscription = $subscription->fresh();
            $this->assertTrue($subscription->isPaused());
            $this->assertSame('2026-04-20', $subscription->paused_at->toDateString());

            $this->assertDatabaseHas('main_daily_session_student_assignments', [
                'student_id' => $student['student_id'],
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionB->id,
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }
}
