<?php

namespace Tests\Feature\CoreLms;

use App\Livewire\Teacher\AutomatedTaskGeneratedHistoryPanel;
use App\Models\MainDailySessionMainTask;
use App\Models\MainDailySessionMainTaskAttachment;
use App\Models\MainDailySessionStudentAssignment;
use App\Models\MainDailySessionSubscription;
use App\Models\MainDailySessionTemplate;
use App\Models\MainDailySessionVersion;
use App\Models\MainDailySessionVersionTask;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use App\Services\AutomatedTaskAssignmentService;
use App\Services\AutomatedTaskSnapshotWriter;
use App\Services\DailySessionPublisher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

/**
 * Covers US3: snapshot immutability verification — generated rows remain
 * frozen after source edits.
 *
 * Covers US6: learner-visibility and attachment-ownership checks — the active
 * student and parent-view-as-child see only their own generated Automated Task
 * rows; protected attachment routes enforce ownership.
 *
 * Tests are added in Phase 5 (T029) and Phase 8 (T045).
 */
class AutomatedTaskVisibilityTest extends TestCase
{
    use CreatesAutomatedTaskTestingSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAutomatedTaskSchema();
        $this->createAutomatedTaskGenerationRuntimeTables();
        $this->seedTaskTypes();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('student');
        Role::findOrCreate('parent');
        Role::findOrCreate('teacher');
    }

    public function test_generated_snapshot_rows_remain_frozen_after_template_version_task_and_attachment_edits(): void
    {
        $fixture = $this->createGeneratedSnapshotFixture();
        $generatedTask = $this->generatedTaskFor($fixture['student_id'], $fixture['template_id'], '2026-04-29');
        $generatedAttachment = DB::table('attachment_files')
            ->where('session_task_id', $generatedTask->id)
            ->first();

        MainDailySessionVersion::query()
            ->whereKey($fixture['version_id'])
            ->update(['display_name' => 'Reading-Intermediate']);
        MainDailySessionMainTask::query()
            ->whereKey($fixture['main_task_id'])
            ->update([
                'title' => 'Changed source title',
                'description' => 'Changed source description.',
                'default_points' => 99,
                'max_points' => 100,
            ]);
        MainDailySessionVersionTask::query()
            ->whereKey($fixture['version_task_id'])
            ->update(['description_override' => 'Changed version override.']);
        MainDailySessionMainTaskAttachment::query()
            ->whereKey($fixture['source_attachment_id'])
            ->delete();

        $this->assertDatabaseHas('session_tasks', [
            'id' => $generatedTask->id,
            'title' => 'Read the passage',
            'description' => 'Original source description.',
            'default_points' => 5,
            'max_points' => 10,
            'version_display_name_snapshot' => 'Reading2',
        ]);
        $this->assertDatabaseHas('attachment_files', [
            'id' => $generatedAttachment->id,
            'session_task_id' => $generatedTask->id,
            'title' => 'Original PDF',
            'description' => 'Original attachment description.',
            'path' => 'automated/original.pdf',
        ]);
    }

    public function test_added_version_keeps_prior_generated_rows_and_future_rows_use_the_new_assigned_version(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-30 09:00:00'));

        try {
            $fixture = $this->createGeneratedSnapshotFixture();
            $futureVersion = MainDailySessionVersion::create([
                'main_daily_session_template_id' => $fixture['template_id'],
                'display_name' => 'Reading-Intermediate',
                'sort_order' => 2,
            ]);

            MainDailySessionVersionTask::create([
                'version_id' => $futureVersion->id,
                'main_task_id' => $fixture['main_task_id'],
                'description_override' => 'Future V2 prompt.',
                'sort_order' => 1,
            ]);

            app(AutomatedTaskAssignmentService::class)->reassign(
                $fixture['student_id'],
                $fixture['template_id'],
                $futureVersion->id,
                Carbon::parse('2026-04-30')->startOfDay(),
                $fixture['teacher']->id
            );

            $effectiveAssignment = app(AutomatedTaskAssignmentService::class)->resolveEffectiveAssignment(
                $fixture['student_id'],
                $fixture['template_id'],
                Carbon::parse('2026-04-30')->startOfDay()
            );

            $this->assertNotNull($effectiveAssignment);
            $this->assertSame($futureVersion->id, (int) $effectiveAssignment->version_id);

            app(AutomatedTaskSnapshotWriter::class)->writeSnapshot(
                $fixture['student_id'],
                $effectiveAssignment,
                Carbon::parse('2026-04-30')->startOfDay(),
                1,
                (int) Student::query()->whereKey($fixture['student_id'])->value('current_class_id')
            );

            $oldTask = $this->generatedTaskFor($fixture['student_id'], $fixture['template_id'], '2026-04-29');
            $futureTasks = DB::table('session_tasks')
                ->join('class_sessions', 'class_sessions.id', '=', 'session_tasks.class_session_id')
                ->where('class_sessions.student_id', $fixture['student_id'])
                ->where('class_sessions.main_daily_session_template_id', $fixture['template_id'])
                ->whereDate('class_sessions.generated_for_date', '2026-04-30')
                ->orderBy('session_tasks.version_display_name_snapshot')
                ->get(['session_tasks.version_display_name_snapshot', 'session_tasks.description']);

            $this->assertSame('Reading2', $oldTask->version_display_name_snapshot);
            $this->assertSame('Original source description.', $oldTask->description);
            $this->assertSame(['Reading-Intermediate'], $futureTasks->pluck('version_display_name_snapshot')->all());
            $this->assertSame(['Future V2 prompt.'], $futureTasks->pluck('description')->all());
            $this->assertSame(2, DB::table('class_sessions')
                ->where('student_id', $fixture['student_id'])
                ->where('main_daily_session_template_id', $fixture['template_id'])
                ->count());
            $this->assertDatabaseHas('main_daily_session_student_assignments', [
                'student_id' => $fixture['student_id'],
                'main_daily_session_template_id' => $fixture['template_id'],
                'version_id' => $fixture['version_id'],
                'effective_to_date' => '2026-04-29',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_deleting_source_version_keeps_existing_generated_snapshot_rows_unchanged(): void
    {
        $fixture = $this->createGeneratedSnapshotFixture();
        $generatedTask = $this->generatedTaskFor($fixture['student_id'], $fixture['template_id'], '2026-04-29');
        $generatedAttachment = DB::table('attachment_files')
            ->where('session_task_id', $generatedTask->id)
            ->first();

        app(AutomatedTaskAssignmentService::class)->deleteVersion(
            $fixture['version_id'],
            Carbon::parse('2026-04-30')->startOfDay(),
            $fixture['teacher']->id
        );

        $this->assertDatabaseMissing('main_daily_session_versions', [
            'id' => $fixture['version_id'],
        ]);

        $this->assertDatabaseHas('session_tasks', [
            'id' => $generatedTask->id,
            'title' => 'Read the passage',
            'description' => 'Original source description.',
            'version_display_name_snapshot' => 'Reading2',
        ]);

        $this->assertDatabaseHas('attachment_files', [
            'id' => $generatedAttachment->id,
            'session_task_id' => $generatedTask->id,
            'title' => 'Original PDF',
            'path' => 'automated/original.pdf',
        ]);

        $this->assertDatabaseHas('main_daily_session_student_assignment_history', [
            'student_id' => $fixture['student_id'],
            'main_daily_session_template_id' => $fixture['template_id'],
            'event_type' => 'unassign',
            'from_version_id' => $fixture['version_id'],
            'from_version_display_name' => 'Reading2',
            'to_version_id' => null,
        ]);
    }

    public function test_teacher_history_panel_renders_snapshot_name_description_copied_attachments_and_completion_state(): void
    {
        $fixture = $this->createGeneratedSnapshotFixture();
        $generatedTask = $this->generatedTaskFor($fixture['student_id'], $fixture['template_id'], '2026-04-29');

        DB::table('session_task_student')
            ->where('session_task_id', $generatedTask->id)
            ->where('student_id', $fixture['student_id'])
            ->update([
                'status' => 'completed',
                'submitted_at' => '2026-04-29 18:00:00',
            ]);

        MainDailySessionVersion::query()
            ->whereKey($fixture['version_id'])
            ->update(['display_name' => 'Renamed Future Version']);
        MainDailySessionMainTask::query()
            ->whereKey($fixture['main_task_id'])
            ->update(['description' => 'Future-only edited source text.']);

        $this->actingAs($fixture['teacher']);

        Livewire::test(AutomatedTaskGeneratedHistoryPanel::class, [
            'templateId' => $fixture['template_id'],
            'studentId' => $fixture['student_id'],
            'show' => true,
        ])
            ->assertSee('Generated history')
            ->assertSee('Ava Stone')
            ->assertSee('Reading2')
            ->assertSee('Original source description.')
            ->assertSee('Original PDF')
            ->assertSee('Completed')
            ->assertDontSee('Renamed Future Version')
            ->assertDontSee('Future-only edited source text.');
    }

    public function test_student_sessions_board_shows_shared_and_own_automated_rows_only(): void
    {
        $fixture = $this->createLearnerVisibilityFixture();

        $this->actingAs($fixture['ava_user']);

        Livewire::test(\App\Livewire\Student\SessionsBoard::class, [
            'studentSubjectId' => $fixture['ava_student_subject_id'],
            'studentId' => $fixture['ava_student_id'],
        ])
            ->assertSee('Shared Normal Session')
            ->assertSee('Ava Automated Task')
            ->assertDontSee('Lina Automated Task');
    }

    public function test_student_subject_cards_show_open_task_counts(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-29 10:00:00'));

        try {
            $fixture = $this->createGeneratedSnapshotFixture();
            $studentUser = User::factory()->create();
            $studentUser->assignRole('student');
            Student::whereKey($fixture['student_id'])->update(['user_id' => $studentUser->id]);

            $generatedTask = $this->generatedTaskFor($fixture['student_id'], $fixture['template_id'], '2026-04-29');
            $this->assertDatabaseHas('session_task_student', [
                'session_task_id' => $generatedTask->id,
                'student_id' => $fixture['student_id'],
                'status' => 'assigned',
            ]);

            $this->actingAs($studentUser)
                ->get(route('student.classes'))
                ->assertOk()
                ->assertSeeInOrder([
                    'class="w14-subject-task-badge"',
                    'aria-label="1 task to do"',
                    '>1</span>',
                ], false)
                ->assertDontSeeText('tasks to do');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_parent_sessions_board_shows_selected_child_shared_and_own_automated_rows_only(): void
    {
        $fixture = $this->createLearnerVisibilityFixture();

        $this->actingAs($fixture['ava_parent_user']);

        Livewire::test(\App\Livewire\Student\SessionsBoard::class, [
            'studentSubjectId' => $fixture['ava_student_subject_id'],
            'studentId' => $fixture['ava_student_id'],
        ])
            ->assertSee('Shared Normal Session')
            ->assertSee('Ava Automated Task')
            ->assertDontSee('Lina Automated Task');
    }

    public function test_teacher_normal_sessions_board_excludes_per_student_automated_rows(): void
    {
        $fixture = $this->createLearnerVisibilityFixture();

        $this->actingAs($fixture['teacher']);

        Livewire::test(\App\Livewire\Teacher\SessionsBoard::class, [
            'teacherSubjectClassId' => $fixture['teacher_subject_class_id'],
        ])
            ->assertSee('Shared Normal Session')
            ->assertDontSee('Ava Automated Task')
            ->assertDontSee('Lina Automated Task');
    }

    public function test_teacher_normal_attachment_route_rejects_per_student_automated_rows(): void
    {
        $fixture = $this->createLearnerVisibilityFixture();

        $this->actingAs($fixture['teacher']);

        $this->get(route('teacher.sessions.attachment.show', [
            'session' => $fixture['ava_automated_session_id'],
            'attachment' => $fixture['ava_attachment_id'],
        ]))->assertNotFound();
    }

    public function test_student_attachment_and_journey_routes_reject_another_students_automated_session(): void
    {
        $fixture = $this->createLearnerVisibilityFixture();

        $this->actingAs($fixture['ava_user']);

        $this->get(route('student.sessions.attachment.show', [
            'session' => $fixture['lina_automated_session_id'],
            'attachment' => $fixture['lina_attachment_id'],
        ]))->assertForbidden();

        $this->get(route('student.sessions.attachment.file', [
            'session' => $fixture['lina_automated_session_id'],
            'attachment' => $fixture['lina_attachment_id'],
        ]))->assertForbidden();

        $this->get(route('student.tasks.journey', [
            'sessionId' => $fixture['lina_automated_session_id'],
        ]))->assertForbidden();
    }

    /**
     * @return array{
     *     teacher: User,
     *     template_id: int,
     *     version_id: int,
     *     main_task_id: int,
     *     version_task_id: int,
     *     source_attachment_id: int,
     *     student_id: int
     * }
     */
    private function createGeneratedSnapshotFixture(): array
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');

        $template = MainDailySessionTemplate::create([
            'title' => 'Snapshot Template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'active',
            'created_at' => '2026-04-20 09:00:00',
            'updated_at' => '2026-04-20 09:00:00',
        ]);

        $version = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Reading2',
            'sort_order' => 1,
        ]);

        $mainTask = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $template->id,
            'title' => 'Read the passage',
            'description' => 'Original source description.',
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);

        $attachment = MainDailySessionMainTaskAttachment::create([
            'main_task_id' => $mainTask->id,
            'type' => 'pdf',
            'title' => 'Original PDF',
            'description' => 'Original attachment description.',
            'path' => 'automated/original.pdf',
            'file_size' => 1234,
            'sort_order' => 1,
        ]);

        $versionTask = MainDailySessionVersionTask::create([
            'version_id' => $version->id,
            'main_task_id' => $mainTask->id,
            'description_override' => null,
            'sort_order' => 1,
        ]);

        MainDailySessionSubscription::create([
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
            'is_active' => 1,
            'paused_at' => null,
            'start_at' => Carbon::parse('2026-04-29 09:00:00'),
            'end_at' => null,
            'last_generated_date' => null,
            'paused_through_date' => null,
        ]);

        MainDailySessionStudentAssignment::create([
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
            'version_id' => $version->id,
            'effective_from_date' => '2026-04-20',
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
        ]);

        app(DailySessionPublisher::class)->generateForStudent(
            $student['student_id'],
            Carbon::parse('2026-04-29')->startOfDay()
        );

        return [
            'teacher' => $teacher,
            'template_id' => $template->id,
            'version_id' => $version->id,
            'main_task_id' => $mainTask->id,
            'version_task_id' => $versionTask->id,
            'source_attachment_id' => $attachment->id,
            'student_id' => $student['student_id'],
        ];
    }

    private function generatedTaskFor(int $studentId, int $templateId, string $date): object
    {
        return DB::table('session_tasks')
            ->join('class_sessions', 'class_sessions.id', '=', 'session_tasks.class_session_id')
            ->where('class_sessions.student_id', $studentId)
            ->where('class_sessions.main_daily_session_template_id', $templateId)
            ->where('class_sessions.generated_for_date', $date)
            ->select('session_tasks.*')
            ->firstOrFail();
    }

    private function createLearnerVisibilityFixture(): array
    {
        Storage::fake('public');

        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');
        $context = $this->createTeacherSubjectContext($teacher);

        $avaUser = User::factory()->create();
        $avaUser->assignRole('student');
        $linaUser = User::factory()->create();
        $linaUser->assignRole('student');
        $avaParentUser = User::factory()->create();
        $avaParentUser->assignRole('parent');

        $ava = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
        $lina = $this->enrollStudent($context, 'Lina', 'Hart', 'Nora');

        Student::whereKey($ava['student_id'])->update(['user_id' => $avaUser->id]);
        Student::whereKey($lina['student_id'])->update(['user_id' => $linaUser->id]);
        ParentModel::whereKey($ava['parent_id'])->update(['user_id' => $avaParentUser->id]);

        $sharedSessionId = DB::table('class_sessions')->insertGetId([
            'teacher_subject_classes_id' => $context['teacher_subject_class_id'],
            'class_id' => $context['class_id'],
            'subject_id' => $context['subject_id'],
            'class_subject_id' => $context['class_subject_id'],
            'student_id' => null,
            'main_daily_session_template_id' => null,
            'title' => 'Shared Normal Session',
            'date' => '2026-04-29',
        ]);

        DB::table('session_materials')->insert([
            'session_id' => $sharedSessionId,
            'status' => 'published',
        ]);

        DB::table('session_tasks')->insert([
            'class_session_id' => $sharedSessionId,
            'title' => 'Shared task',
            'description' => 'Shared task description',
            'task_type_id' => 1,
            'sort' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'status' => 'published',
        ]);

        $avaTemplateId = $this->createGeneratedAutomatedTaskForStudent(
            $context,
            $teacher,
            $ava['student_id'],
            'Ava Automated Task',
            'attachments/ava-automated.pdf'
        );
        $linaTemplateId = $this->createGeneratedAutomatedTaskForStudent(
            $context,
            $teacher,
            $lina['student_id'],
            'Lina Automated Task',
            'attachments/lina-automated.pdf'
        );

        $avaSession = DB::table('class_sessions')
            ->where('student_id', $ava['student_id'])
            ->where('main_daily_session_template_id', $avaTemplateId)
            ->first();
        $linaSession = DB::table('class_sessions')
            ->where('student_id', $lina['student_id'])
            ->where('main_daily_session_template_id', $linaTemplateId)
            ->first();

        $linaAttachmentId = DB::table('attachment_files')
            ->join('session_tasks', 'session_tasks.id', '=', 'attachment_files.session_task_id')
            ->where('session_tasks.class_session_id', $linaSession->id)
            ->value('attachment_files.id');
        $avaAttachmentId = DB::table('attachment_files')
            ->join('session_tasks', 'session_tasks.id', '=', 'attachment_files.session_task_id')
            ->where('session_tasks.class_session_id', $avaSession->id)
            ->value('attachment_files.id');

        return [
            'teacher' => $teacher,
            'teacher_subject_class_id' => $context['teacher_subject_class_id'],
            'ava_user' => $avaUser,
            'ava_parent_user' => $avaParentUser,
            'ava_student_id' => $ava['student_id'],
            'ava_student_subject_id' => DB::table('students_subjects')
                ->where('student_id', $ava['student_id'])
                ->value('id'),
            'lina_automated_session_id' => (int) $linaSession->id,
            'lina_attachment_id' => (int) $linaAttachmentId,
            'ava_automated_session_id' => (int) $avaSession->id,
            'ava_attachment_id' => (int) $avaAttachmentId,
        ];
    }

    private function createGeneratedAutomatedTaskForStudent(
        array $context,
        User $teacher,
        int $studentId,
        string $templateTitle,
        string $attachmentPath
    ): int {
        Storage::disk('public')->put($attachmentPath, '%PDF-1.4 automated');

        $template = MainDailySessionTemplate::create([
            'title' => $templateTitle,
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'active',
        ]);

        $version = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Reading',
            'sort_order' => 1,
        ]);

        $mainTask = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $template->id,
            'title' => $templateTitle.' snapshot task',
            'description' => $templateTitle.' description',
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);

        $attachment = MainDailySessionMainTaskAttachment::create([
            'main_task_id' => $mainTask->id,
            'type' => 'file',
            'title' => $templateTitle.' PDF',
            'path' => $attachmentPath,
            'file_size' => 123,
            'sort_order' => 1,
        ]);

        MainDailySessionVersionTask::create([
            'version_id' => $version->id,
            'main_task_id' => $mainTask->id,
            'description_override' => null,
            'sort_order' => 1,
        ]);

        MainDailySessionSubscription::create([
            'student_id' => $studentId,
            'main_daily_session_template_id' => $template->id,
            'is_active' => 1,
            'start_at' => Carbon::parse('2026-04-29 09:00:00'),
        ]);

        MainDailySessionStudentAssignment::create([
            'student_id' => $studentId,
            'main_daily_session_template_id' => $template->id,
            'version_id' => $version->id,
            'effective_from_date' => '2026-04-20',
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
        ]);

        app(DailySessionPublisher::class)->generateForStudent(
            $studentId,
            Carbon::parse('2026-04-29')->startOfDay()
        );

        return (int) $template->id;
    }
}
