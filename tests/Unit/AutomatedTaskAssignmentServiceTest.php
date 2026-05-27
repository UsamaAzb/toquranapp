<?php

namespace Tests\Unit;

use App\Models\MainDailySessionStudentAssignment;
use App\Models\MainDailySessionTemplate;
use App\Models\MainDailySessionVersion;
use App\Models\Student;
use App\Models\User;
use App\Services\AutomatedTaskAssignmentService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

/**
 * Unit coverage for AutomatedTaskAssignmentService.
 *
 * Verifies effective assignment lookup (resolves the correct version for a
 * given student + template + date), overlap prevention via transactional
 * range locking, and multi-version membership behavior.
 *
 * Tests are added in Phase 4 (T024).
 */
class AutomatedTaskAssignmentServiceTest extends TestCase
{
    use CreatesAutomatedTaskTestingSchema;
    use RefreshDatabase;

    private AutomatedTaskAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAutomatedTaskSchema();
        $this->service = app(AutomatedTaskAssignmentService::class);
    }

    public function test_resolve_effective_assignment_returns_the_interval_covering_the_requested_date(): void
    {
        $teacher = User::factory()->create();
        $student = Student::factory()->create();
        $template = MainDailySessionTemplate::create([
            'title' => 'Assignment lookup template',
            'subject_id' => 1,
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

        MainDailySessionStudentAssignment::create([
            'student_id' => $student->id,
            'main_daily_session_template_id' => $template->id,
            'version_id' => $versionA->id,
            'effective_from_date' => '2026-04-01',
            'effective_to_date' => '2026-04-09',
            'assigned_by_user_id' => $teacher->id,
        ]);
        MainDailySessionStudentAssignment::create([
            'student_id' => $student->id,
            'main_daily_session_template_id' => $template->id,
            'version_id' => $versionB->id,
            'effective_from_date' => '2026-04-10',
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
        ]);

        $assignment = $this->service->resolveEffectiveAssignment(
            $student->id,
            $template->id,
            Carbon::parse('2026-04-10')
        );

        $this->assertNotNull($assignment);
        $this->assertSame($versionB->id, $assignment->version_id);
        $this->assertSame('Version B', $assignment->version->display_name);
    }

    public function test_create_assignment_is_idempotent_for_same_version_interval(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-05 09:00:00'));

        try {
            $teacher = User::factory()->create();
            $student = Student::factory()->create();
            $template = MainDailySessionTemplate::create([
                'title' => 'Overlap template',
                'subject_id' => 1,
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

            MainDailySessionStudentAssignment::create([
                'student_id' => $student->id,
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionA->id,
                'effective_from_date' => '2026-04-01',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            $assignment = $this->service->createAssignment(
                $student->id,
                $template->id,
                $versionA->id,
                Carbon::parse('2026-04-05'),
                $teacher->id
            );

            $this->assertSame($versionA->id, $assignment->version_id);
            $this->assertSame(1, MainDailySessionStudentAssignment::query()
                ->where('student_id', $student->id)
                ->where('main_daily_session_template_id', $template->id)
                ->where('version_id', $versionA->id)
                ->count());

            $secondAssignment = $this->service->createAssignment(
                $student->id,
                $template->id,
                $versionB->id,
                Carbon::parse('2026-04-05'),
                $teacher->id
            );

            $this->assertSame($versionB->id, $secondAssignment->version_id);
            $this->assertSame(1, MainDailySessionStudentAssignment::query()
                ->where('student_id', $student->id)
                ->where('main_daily_session_template_id', $template->id)
                ->whereNull('effective_to_date')
                ->count());
            $this->assertDatabaseHas('main_daily_session_student_assignments', [
                'student_id' => $student->id,
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionA->id,
                'effective_to_date' => '2026-04-04',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_reassign_alias_moves_to_new_version_and_closes_existing_interval(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-25 09:00:00'));

        try {
            $teacher = User::factory()->create();
            $student = Student::factory()->create();
            $template = MainDailySessionTemplate::create([
                'title' => 'Reassignment template',
                'subject_id' => 1,
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

            MainDailySessionStudentAssignment::create([
                'student_id' => $student->id,
                'main_daily_session_template_id' => $template->id,
                'version_id' => $versionA->id,
                'effective_from_date' => '2026-04-01',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            $assignment = $this->service->reassign(
                $student->id,
                $template->id,
                $versionB->id,
                Carbon::parse('2026-04-25'),
                $teacher->id
            );

            $closedInterval = MainDailySessionStudentAssignment::query()
                ->where('student_id', $student->id)
                ->where('version_id', $versionA->id)
                ->firstOrFail();
            $openInterval = MainDailySessionStudentAssignment::query()
                ->where('student_id', $student->id)
                ->where('version_id', $versionB->id)
                ->firstOrFail();

            $this->assertSame($versionB->id, $assignment->version_id);
            $this->assertSame('2026-04-24', $closedInterval->effective_to_date->toDateString());
            $this->assertSame('2026-04-25', $openInterval->effective_from_date->toDateString());
            $this->assertNull($openInterval->effective_to_date);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_reassign_alias_can_create_without_existing_open_interval(): void
    {
        $teacher = User::factory()->create();
        $student = Student::factory()->create();
        $template = MainDailySessionTemplate::create([
            'title' => 'Reassignment guard template',
            'subject_id' => 1,
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

        $assignment = $this->service->reassign(
            $student->id,
            $template->id,
            $version->id,
            Carbon::parse('2026-04-25'),
            $teacher->id
        );

        $this->assertSame($version->id, $assignment->version_id);
        $this->assertSame('2026-04-25', $assignment->effective_from_date->toDateString());
        $this->assertNull($assignment->effective_to_date);
    }

    public function test_create_assignment_rejects_a_version_that_no_longer_exists(): void
    {
        $teacher = User::factory()->create();
        $student = Student::factory()->create();
        $template = MainDailySessionTemplate::create([
            'title' => 'Deleted version template',
            'subject_id' => 1,
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $this->expectException(ModelNotFoundException::class);

        $this->service->createAssignment(
            $student->id,
            $template->id,
            999,
            Carbon::parse('2026-04-25'),
            $teacher->id
        );
    }

    public function test_reassign_rejects_a_version_from_a_different_template(): void
    {
        $teacher = User::factory()->create();
        $student = Student::factory()->create();
        $templateA = MainDailySessionTemplate::create([
            'title' => 'Template A',
            'subject_id' => 1,
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);
        $templateB = MainDailySessionTemplate::create([
            'title' => 'Template B',
            'subject_id' => 1,
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);
        $versionA = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $templateA->id,
            'display_name' => 'Version A',
            'sort_order' => 1,
        ]);
        $wrongTemplateVersion = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $templateB->id,
            'display_name' => 'Wrong template',
            'sort_order' => 1,
        ]);

        MainDailySessionStudentAssignment::create([
            'student_id' => $student->id,
            'main_daily_session_template_id' => $templateA->id,
            'version_id' => $versionA->id,
            'effective_from_date' => '2026-04-01',
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
        ]);

        $this->expectException(ModelNotFoundException::class);

        $this->service->reassign(
            $student->id,
            $templateA->id,
            $wrongTemplateVersion->id,
            Carbon::parse('2026-04-25'),
            $teacher->id
        );
    }
}
