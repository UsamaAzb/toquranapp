<?php

namespace Tests\Feature\CoreLms;

use App\Livewire\Teacher\AutomatedTaskMainTaskModal;
use App\Livewire\Teacher\AutomatedTasksBoard;
use App\Models\AttachmentFile;
use App\Models\LibraryResource;
use App\Models\LibrarySection;
use App\Models\MainDailySessionMainTask;
use App\Models\MainDailySessionMainTaskAttachment;
use App\Models\MainDailySessionStudentAssignment;
use App\Models\MainDailySessionSubscription;
use App\Models\MainDailySessionTemplate;
use App\Models\MainDailySessionVersion;
use App\Models\User;
use App\Models\VocabularyGameAssignment;
use App\Services\AutomatedTaskSnapshotWriter;
use App\Services\Vocabulary\VocabularyGameAttachmentBuilder;
use App\Support\BookingSubjectProvisioning;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

class AutomatedTaskAuthoringTest extends TestCase
{
    use CreatesAutomatedTaskTestingSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAutomatedTaskSchema();
        $this->seedTaskTypes();
    }

    public function test_teacher_can_create_versions_author_main_tasks_and_publish_the_template(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');

        $this->actingAs($teacher);

        $board = Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->set('draftTemplate.title', 'Reading Cycle')
            ->set('draftTemplate.recurrence_kind', 'weekly')
            ->set('draftTemplate.recurrence_weekdays', ['mon', 'wed'])
            ->call('createTemplate');

        $template = MainDailySessionTemplate::query()->firstOrFail();

        $this->assertSame('1,3', $template->recurrence_weekdays);

        $board->call('addVersion', $template->id)
            ->call('addVersion', $template->id);

        $versions = MainDailySessionVersion::query()
            ->where('main_daily_session_template_id', $template->id)
            ->orderBy('sort_order')
            ->get();

        $this->assertCount(3, $versions);

        Livewire::test(AutomatedTaskMainTaskModal::class)
            ->call('open', $template->id)
            ->set('title', 'Read and respond')
            ->set('description', 'Read the passage and respond in writing.')
            ->set('taskTypeId', 1)
            ->set('defaultPoints', 5)
            ->set('maxPoints', 10)
            ->set('linkTitle', 'Passage')
            ->set('linkUrl', 'https://example.com/passage')
            ->call('addLink')
            ->call('save');

        $mainTask = $template->fresh()->mainTasks()->firstOrFail();

        $board->call('refreshBoard')
            ->set("versionTaskForms.{$versions[0]->id}.{$mainTask->id}.enabled", true)
            ->set("versionTaskForms.{$versions[0]->id}.{$mainTask->id}.description_override", 'Student-facing directions')
            ->call('saveVersionTask', $versions[0]->id, $mainTask->id);

        MainDailySessionSubscription::create([
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
            'is_active' => 1,
            'paused_at' => null,
            'start_at' => now(),
            'end_at' => null,
            'last_generated_date' => null,
        ]);

        MainDailySessionStudentAssignment::create([
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
            'version_id' => $versions[0]->id,
            'effective_from_date' => now()->toDateString(),
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $board->call('publishTemplate', $template->id);

        $this->assertDatabaseHas('main_daily_session_templates', [
            'id' => $template->id,
            'title' => 'Reading Cycle',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('main_daily_session_main_task_attachments', [
            'main_task_id' => $mainTask->id,
            'type' => 'link',
            'url' => 'https://example.com/passage',
            'description' => null,
        ]);
    }

    public function test_publish_validation_blocks_invalid_participating_versions_until_meaningful_content_is_added(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');

        $this->actingAs($teacher);

        $board = Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->set('draftTemplate.title', 'Validator board template')
            ->call('createTemplate');

        $template = MainDailySessionTemplate::query()->firstOrFail();

        Livewire::test(AutomatedTaskMainTaskModal::class)
            ->call('open', $template->id)
            ->set('title', 'Draft task')
            ->set('description', null)
            ->set('taskTypeId', 1)
            ->set('defaultPoints', 5)
            ->set('maxPoints', 10)
            ->call('save');

        $version = $template->fresh()->versions()->firstOrFail();
        $mainTask = $template->fresh()->mainTasks()->firstOrFail();

        $board->call('refreshBoard')
            ->set("versionTaskForms.{$version->id}.{$mainTask->id}.enabled", true)
            ->set("versionTaskForms.{$version->id}.{$mainTask->id}.description_override", null)
            ->call('saveVersionTask', $version->id, $mainTask->id);

        MainDailySessionSubscription::create([
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
            'is_active' => 1,
            'paused_at' => null,
            'start_at' => now(),
            'end_at' => null,
            'last_generated_date' => null,
        ]);

        MainDailySessionStudentAssignment::create([
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
            'version_id' => $version->id,
            'effective_from_date' => now()->toDateString(),
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $board->call('publishTemplate', $template->id);

        $this->assertSame('draft', $template->fresh()->status);
        $this->assertNotEmpty($board->instance()->publishErrors[$template->id] ?? []);

        $board->set("versionTaskForms.{$version->id}.{$mainTask->id}.description_override", 'Now this row has meaningful content.')
            ->call('saveVersionTask', $version->id, $mainTask->id)
            ->call('publishTemplate', $template->id);

        $this->assertSame('active', $template->fresh()->status);
    }

    public function test_board_and_template_attachment_routes_are_scoped_to_the_template_creator(): void
    {
        Storage::fake('public');

        $teacher = User::factory()->create();
        $otherTeacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $otherContext = $this->createTeacherSubjectContext($otherTeacher, $context['subject_id'], 'Other Class');
        $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
        $this->enrollStudent($otherContext, 'Nora', 'Lane', 'Pia');

        $template = MainDailySessionTemplate::create([
            'title' => 'Owner only template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $task = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $template->id,
            'title' => 'Protected attachment task',
            'description' => 'Read the protected file.',
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);

        Storage::disk('public')->put('automated-task-attachments/protected.pdf', 'private content');

        $attachment = MainDailySessionMainTaskAttachment::create([
            'main_task_id' => $task->id,
            'type' => 'file',
            'title' => 'Protected PDF',
            'path' => 'automated-task-attachments/protected.pdf',
            'url' => null,
            'file_size' => 16,
            'sort_order' => 1,
        ]);

        $this->actingAs($teacher);

        Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->assertSee('Protected attachment task');

        $this->withoutMiddleware();

        $this->get(route('daily-sessions.template-attachment.show', [
            'template' => $template->id,
            'attachment' => $attachment->id,
        ]))->assertOk();

        $this->get(route('daily-sessions.template-attachment.file', [
            'template' => $template->id,
            'attachment' => $attachment->id,
        ]))->assertOk();

        $this->actingAs($otherTeacher);

        Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->assertDontSee('Protected attachment task');

        $this->get(route('daily-sessions.template-attachment.show', [
            'template' => $template->id,
            'attachment' => $attachment->id,
        ]))->assertNotFound();

        $this->get(route('daily-sessions.template-attachment.file', [
            'template' => $template->id,
            'attachment' => $attachment->id,
        ]))->assertNotFound();
    }

    public function test_teacher_can_access_authoring_without_any_active_students(): void
    {
        Storage::fake('public');

        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher);

        Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->set('draftTemplate.title', 'Pre-enrollment template')
            ->call('createTemplate');

        $template = MainDailySessionTemplate::query()->firstOrFail();

        $task = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $template->id,
            'title' => 'Protected attachment task',
            'description' => 'Read the protected file.',
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);

        Storage::disk('public')->put('automated-task-attachments/pre-enrollment.pdf', 'private content');

        $attachment = MainDailySessionMainTaskAttachment::create([
            'main_task_id' => $task->id,
            'type' => 'file',
            'title' => 'Protected PDF',
            'path' => 'automated-task-attachments/pre-enrollment.pdf',
            'url' => null,
            'file_size' => 16,
            'sort_order' => 1,
        ]);

        DB::table('students_subjects')->delete();

        Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->assertSee('Protected attachment task');

        $this->withoutMiddleware();

        $this->get(route('daily-sessions.template-attachment.show', [
            'template' => $template->id,
            'attachment' => $attachment->id,
        ]))->assertOk();
    }

    public function test_board_exposes_read_only_generated_history_entry_point_and_future_only_copy(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        MainDailySessionTemplate::create([
            'title' => 'History ready template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'active',
        ]);

        $this->actingAs($teacher);

        Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->assertSee('View generated history')
            ->assertSee('Version changes only affect new generations');
    }

    public function test_board_dispatches_history_panel_event_from_direct_action(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'History dispatch template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'active',
        ]);

        $this->actingAs($teacher);

        Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->call('openHistoryPanel', $template->id)
            ->assertDispatched('open-automated-task-history-panel');
    }

    public function test_board_dispatches_assignment_modal_event_from_direct_action(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Assignment dispatch template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'active',
        ]);

        $this->actingAs($teacher);

        Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->call('openAssignmentModal', $template->id)
            ->assertDispatched('open-automated-task-assignment-modal');
    }

    public function test_board_dispatches_main_task_modal_event_from_direct_action(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Main task dispatch template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $this->actingAs($teacher);

        Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->call('openMainTaskModal', $template->id)
            ->assertDispatched('open-automated-task-main-task-modal');
    }

    public function test_archive_and_restore_switch_scopes_without_page_refresh(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Scope switch template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'active',
        ]);

        $this->actingAs($teacher);

        Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->assertSet('templateScope', 'working')
            ->assertSee('Scope switch template')
            ->call('archiveTemplate', $template->id)
            ->assertRedirect();

        $template->refresh();
        $this->assertSame('archived', $template->status);

        Livewire::withQueryParams(['automated_scope' => 'archived'])
            ->test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->assertSet('templateScope', 'archived')
            ->assertSee('Scope switch template')
            ->call('restoreTemplate', $template->id)
            ->assertRedirect();

        $template->refresh();
        $this->assertSame('draft', $template->status);

        Livewire::withQueryParams(['automated_scope' => 'working'])
            ->test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->assertSet('templateScope', 'working')
            ->assertSee('Scope switch template');
    }

    public function test_archived_template_details_are_read_only_until_restored(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Archived details template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'archived',
        ]);

        $this->actingAs($teacher);

        Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->call('setTemplateScope', 'archived')
            ->assertSee('Archived details template')
            ->assertSee('Restore')
            ->assertDontSee('Save details')
            ->set("templateForms.{$template->id}.title", 'Should not persist while archived')
            ->set("templateForms.{$template->id}.recurrence_interval", 7)
            ->call('saveTemplate', $template->id);

        $template->refresh();

        $this->assertSame('Archived details template', $template->title);
        $this->assertSame(1, $template->recurrence_interval);
        $this->assertSame('archived', $template->status);
    }

    public function test_weekly_template_save_normalizes_text_weekdays_to_numeric(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher);

        Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->set('draftTemplate.title', 'Weekly normalized')
            ->set('draftTemplate.recurrence_kind', 'weekly')
            ->set('draftTemplate.recurrence_weekdays', ['tue', 'thu', 'sat'])
            ->call('createTemplate');

        $template = MainDailySessionTemplate::query()->firstOrFail();

        $this->assertSame('2,4,6', $template->recurrence_weekdays);

        $board = Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']]);
        $form = $board->instance()->templateForms[$template->id];

        $this->assertSame(['tue', 'thu', 'sat'], $form['recurrence_weekdays']);
    }

    public function test_weekly_template_save_normalizes_interval_to_one(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher);

        Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->set('draftTemplate.title', 'Weekly interval normalized')
            ->set('draftTemplate.recurrence_kind', 'weekly')
            ->set('draftTemplate.recurrence_interval', 3)
            ->set('draftTemplate.recurrence_weekdays', ['mon'])
            ->call('createTemplate');

        $template = MainDailySessionTemplate::query()->firstOrFail();

        $this->assertSame(1, $template->recurrence_interval);
    }

    public function test_monthly_template_save_normalizes_interval_to_one(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher);

        Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->set('draftTemplate.title', 'Monthly interval normalized')
            ->set('draftTemplate.recurrence_kind', 'monthly')
            ->set('draftTemplate.recurrence_interval', 5)
            ->set('draftTemplate.recurrence_day_of_month', 15)
            ->call('createTemplate');

        $template = MainDailySessionTemplate::query()->firstOrFail();

        $this->assertSame(1, $template->recurrence_interval);
    }

    public function test_daily_template_save_preserves_interval_greater_than_one(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher);

        Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->set('draftTemplate.title', 'Daily interval preserved')
            ->set('draftTemplate.recurrence_kind', 'daily')
            ->set('draftTemplate.recurrence_interval', 3)
            ->call('createTemplate');

        $template = MainDailySessionTemplate::query()->firstOrFail();

        $this->assertSame(3, $template->recurrence_interval);
    }

    public function test_existing_numeric_weekday_template_loads_as_text_keys_in_form(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        MainDailySessionTemplate::create([
            'title' => 'Legacy numeric weekdays',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'weekly',
            'recurrence_weekdays' => '1,3,5',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $this->actingAs($teacher);

        $board = Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']]);
        $template = MainDailySessionTemplate::query()->firstOrFail();
        $form = $board->instance()->templateForms[$template->id];

        $this->assertSame(['mon', 'wed', 'fri'], $form['recurrence_weekdays']);
    }

    public function test_legacy_text_weekday_template_loads_correctly_and_saves_as_numeric(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        MainDailySessionTemplate::create([
            'title' => 'Legacy text weekdays',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'weekly',
            'recurrence_weekdays' => 'mon,wed',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $this->actingAs($teacher);

        $board = Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']]);
        $template = MainDailySessionTemplate::query()->firstOrFail();
        $form = $board->instance()->templateForms[$template->id];

        $this->assertSame(['mon', 'wed'], $form['recurrence_weekdays']);

        $board->call('saveTemplate', $template->id);

        $template->refresh();
        $this->assertSame('1,3', $template->recurrence_weekdays);

        $board2 = Livewire::test(AutomatedTasksBoard::class, ['subjectId' => $context['subject_id']]);
        $form2 = $board2->instance()->templateForms[$template->id];

        $this->assertSame(['mon', 'wed'], $form2['recurrence_weekdays']);
    }

    public function test_main_task_modal_uses_toggle_link_and_youtube_forms(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Toggle form template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $modal = Livewire::test(AutomatedTaskMainTaskModal::class)
            ->call('open', $template->id)
            ->assertSet('showLinkForm', false)
            ->assertSet('showYoutubeForm', false)
            ->call('clickAddLink')
            ->assertSet('showLinkForm', true)
            ->assertSet('showYoutubeForm', false)
            ->call('clickAddYoutube')
            ->assertSet('showYoutubeForm', true)
            ->assertSet('showLinkForm', false);
    }

    public function test_main_task_modal_adds_link_through_toggle_form(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Link add template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $modal = Livewire::test(AutomatedTaskMainTaskModal::class)
            ->call('open', $template->id)
            ->call('clickAddLink')
            ->set('linkTitle', 'Passage PDF')
            ->set('linkUrl', 'https://example.com/passage.pdf')
            ->call('addLink');

        $this->assertCount(1, $modal->instance()->links);
        $this->assertSame('Passage PDF', $modal->instance()->links[0]['title']);
        $this->assertSame('https://example.com/passage.pdf', $modal->instance()->links[0]['url']);
        $this->assertSame('', $modal->instance()->linkTitle);
        $this->assertSame('', $modal->instance()->linkUrl);
    }

    public function test_main_task_modal_save_requires_uncommitted_link_to_be_added_first(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Flush pending template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $modal = Livewire::test(AutomatedTaskMainTaskModal::class)
            ->call('open', $template->id)
            ->set('title', 'Flush task')
            ->set('taskTypeId', 1)
            ->call('clickAddLink')
            ->set('linkTitle', 'Left in form')
            ->set('linkUrl', 'https://example.com/leftover')
            ->call('save')
            ->assertHasErrors(['links_pending']);

        $this->assertNull($template->fresh()->mainTasks()->first());
    }

    public function test_main_task_modal_saves_added_youtube_with_optional_title(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Optional YT title template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $modal = Livewire::test(AutomatedTaskMainTaskModal::class)
            ->call('open', $template->id)
            ->set('title', 'YT optional title task')
            ->set('taskTypeId', 1)
            ->call('clickAddYoutube')
            ->set('youtubeTitle', '')
            ->set('youtubeUrl', 'https://www.youtube.com/watch?v=abc123')
            ->call('addYoutube')
            ->call('save');

        $task = $template->fresh()->mainTasks()->first();
        $this->assertNotNull($task);

        $attachment = $task->attachments()->where('type', 'youtube')->first();
        $this->assertNotNull($attachment);
        $this->assertSame('YouTube', $attachment->title);
        $this->assertSame('https://www.youtube.com/watch?v=abc123', $attachment->url);
    }

    public function test_main_task_modal_open_resets_toggle_forms(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Reset toggle template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        Livewire::test(AutomatedTaskMainTaskModal::class)
            ->call('open', $template->id)
            ->call('clickAddLink')
            ->assertSet('showLinkForm', true)
            ->assertSet('showYoutubeForm', false)
            ->call('clickAddYoutube')
            ->assertSet('showYoutubeForm', true)
            ->assertSet('showLinkForm', false)
            ->call('open', $template->id)
            ->assertSet('showLinkForm', false)
            ->assertSet('showYoutubeForm', false);
    }

    public function test_main_task_modal_computed_existing_files_splits_by_type(): void
    {
        Storage::fake('public');

        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Computed files template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $task = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $template->id,
            'title' => 'Computed task',
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);

        MainDailySessionMainTaskAttachment::create([
            'main_task_id' => $task->id,
            'type' => 'file',
            'title' => 'Doc.pdf',
            'path' => 'automated-task-attachments/doc.pdf',
            'url' => null,
            'file_size' => 100,
            'sort_order' => 1,
        ]);

        MainDailySessionMainTaskAttachment::create([
            'main_task_id' => $task->id,
            'type' => 'link',
            'title' => 'External Link',
            'path' => null,
            'url' => 'https://example.com',
            'file_size' => null,
            'sort_order' => 2,
        ]);

        MainDailySessionMainTaskAttachment::create([
            'main_task_id' => $task->id,
            'type' => 'youtube',
            'title' => 'Video',
            'path' => null,
            'url' => 'https://youtube.com/watch?v=123',
            'file_size' => null,
            'sort_order' => 3,
        ]);

        $modal = Livewire::test(AutomatedTaskMainTaskModal::class)
            ->call('open', $template->id, $task->id);

        $this->assertCount(1, $modal->instance()->existingFiles);
        $this->assertCount(1, $modal->instance()->existingLinks);
        $this->assertCount(1, $modal->instance()->existingYoutubes);
        $this->assertSame('Doc.pdf', $modal->instance()->existingFiles[0]['title']);
        $this->assertSame('External Link', $modal->instance()->existingLinks[0]['title']);
        $this->assertSame('Video', $modal->instance()->existingYoutubes[0]['title']);

        $modal->assertSee('Doc.pdf')
            ->assertSee('Saved file')
            ->assertSee('External Link')
            ->assertSee('Saved link')
            ->assertSee('Video')
            ->assertSee('Saved YouTube link');
    }

    public function test_main_task_modal_remove_file_clears_pending_upload(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Remove file template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $fakeFile = \Illuminate\Http\UploadedFile::fake()->create('test.pdf', 100);

        $modal = Livewire::test(AutomatedTaskMainTaskModal::class)
            ->call('open', $template->id)
            ->set('files', [$fakeFile])
            ->assertSee('test.pdf')
            ->assertSee('New file')
            ->call('removeFile', 0)
            ->assertSet('files', [])
            ->assertSet('finalFiles', []);
    }

    public function test_main_task_modal_can_attach_library_resources_in_selected_order(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $sectionId = $this->createLibrarySection($teacher->id, $context['subject_id']);
        $firstResourceId = $this->createLibraryFileResource(
            $teacher->id,
            $context['subject_id'],
            $sectionId,
            'Unit 1 Video',
            'library/unit-1.mp4'
        );
        $secondResourceId = $this->createLibraryFileResource(
            $teacher->id,
            $context['subject_id'],
            $sectionId,
            'Unit 2 Video',
            'library/unit-2.mp4'
        );

        $this->actingAs($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Library routine template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        Livewire::test(AutomatedTaskMainTaskModal::class)
            ->call('open', $template->id)
            ->assertSee('Choose from Library')
            ->call('useLibraryResources', [(string) $secondResourceId, (string) $firstResourceId])
            ->set('title', 'Ordered Library task')
            ->set('taskTypeId', 1)
            ->call('save');

        $task = $template->fresh()->mainTasks()->firstOrFail();
        $attachments = $task->attachments()->get();

        $this->assertSame(['Unit 2 Video', 'Unit 1 Video'], $attachments->pluck('title')->all());
        $this->assertSame([1, 2], $attachments->pluck('sort_order')->all());
        $this->assertSame(['library/unit-2.mp4', 'library/unit-1.mp4'], $attachments->pluck('path')->all());
    }

    public function test_automated_task_snapshot_preserves_versioned_routine_library_attachment_order(): void
    {
        $this->createAutomatedTaskGenerationRuntimeTables();

        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
        $sectionId = $this->createLibrarySection($teacher->id, $context['subject_id']);
        $firstResourceId = $this->createLibraryFileResource(
            $teacher->id,
            $context['subject_id'],
            $sectionId,
            'First Library File',
            'library/first.pdf'
        );
        $secondResourceId = $this->createLibraryFileResource(
            $teacher->id,
            $context['subject_id'],
            $sectionId,
            'Second Library File',
            'library/second.pdf'
        );

        $this->actingAs($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Generated order template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $version = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Default',
            'sort_order' => 1,
        ]);

        Livewire::test(AutomatedTaskMainTaskModal::class)
            ->call('open', $template->id)
            ->call('useLibraryResources', [(string) $secondResourceId, (string) $firstResourceId])
            ->set('title', 'Generated ordered task')
            ->set('taskTypeId', 1)
            ->call('save');

        $task = $template->fresh()->mainTasks()->firstOrFail();

        DB::table('main_daily_session_version_tasks')->insert([
            'version_id' => $version->id,
            'main_task_id' => $task->id,
            'description_override' => null,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
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

        $assignment = MainDailySessionStudentAssignment::create([
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
            'version_id' => $version->id,
            'effective_from_date' => now()->toDateString(),
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(AutomatedTaskSnapshotWriter::class)->writeSnapshot(
            $student['student_id'],
            $assignment->fresh(['template', 'version']),
            now(),
            1,
            $context['class_id']
        );

        $generatedAttachments = AttachmentFile::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $this->assertSame(['Second Library File', 'First Library File'], $generatedAttachments->pluck('title')->all());
        $this->assertSame([1, 2], $generatedAttachments->pluck('sort_order')->all());
        $this->assertSame(['library/second.pdf', 'library/first.pdf'], $generatedAttachments->pluck('path')->all());
    }

    public function test_versioned_routine_vocabulary_attachment_generates_playable_game_assignment(): void
    {
        $this->createAutomatedTaskGenerationRuntimeTables();
        $this->createVocabularyGameTestingTables();

        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext(
            $teacher,
            BookingSubjectProvisioning::SUBJECT_LANGUAGE_AND_LITERATURE
        );
        $student = $this->enrollStudent($context, 'Mira', 'Lane', 'Nadia');
        $setId = DB::table('vocabulary_sets')->insertGetId([
            'parent_id' => null,
            'title' => 'Cambridge Unit 1',
            'description' => 'Unit words',
            'node_type' => 'playable',
            'set_type' => 'teacher',
            'source_kind' => 'custom',
            'source_key' => 'teacher-cambridge-unit-1',
            'owner_user_id' => $teacher->id,
            'visibility' => 'private',
            'sort_order' => 1,
            'created_by_user_id' => $teacher->id,
            'updated_by_user_id' => $teacher->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $template = MainDailySessionTemplate::create([
            'title' => 'Vocabulary Routine',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'active',
        ]);
        $version = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Default',
            'sort_order' => 1,
        ]);
        $task = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $template->id,
            'title' => 'Play vocabulary',
            'description' => null,
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);

        DB::table('main_daily_session_version_tasks')->insert([
            'version_id' => $version->id,
            'main_task_id' => $task->id,
            'description_override' => null,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        MainDailySessionMainTaskAttachment::create([
            'main_task_id' => $task->id,
            'type' => 'link',
            'title' => 'Vocab Game: Cambridge Unit 1',
            'description' => 'Unit words',
            'path' => VocabularyGameAttachmentBuilder::sourcePath($setId),
            'url' => null,
            'file_size' => null,
            'sort_order' => 1,
        ]);

        $assignment = MainDailySessionStudentAssignment::create([
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
            'version_id' => $version->id,
            'effective_from_date' => '2026-05-26',
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(AutomatedTaskSnapshotWriter::class)->writeSnapshot(
            $student['student_id'],
            $assignment->fresh(['template', 'version']),
            Carbon::parse('2026-05-26'),
            1,
            $context['class_id']
        );

        $gameAssignmentId = DB::table('vocabulary_game_assignments')->value('id');
        $this->assertDatabaseHas('vocabulary_game_assignments', [
            'vocabulary_set_id' => $setId,
            'assigned_by_user_id' => $teacher->id,
            'audience_type' => VocabularyGameAssignment::AUDIENCE_STUDENT,
            'audience_id' => $student['student_id'],
            'difficulty_policy' => VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE,
            'status' => VocabularyGameAssignment::STATUS_ACTIVE,
        ]);
        $this->assertDatabaseHas('attachment_files', [
            'title' => 'Vocab Game: Cambridge Unit 1',
            'type' => 'link',
            'path' => route('vocabulary.games.assignment', ['assignment' => $gameAssignmentId]),
        ]);
    }

    private function createLibrarySection(int $ownerUserId, int $subjectId): int
    {
        return (int) LibrarySection::create([
            'owner_user_id' => $ownerUserId,
            'subject_id' => $subjectId,
            'parent_id' => null,
            'title' => 'Routine Library',
            'description' => null,
            'status' => 'active',
            'sort_order' => 1,
            'created_by_user_id' => $ownerUserId,
        ])->id;
    }

    private function createLibraryFileResource(
        int $ownerUserId,
        int $subjectId,
        int $sectionId,
        string $title,
        string $path
    ): int {
        return (int) LibraryResource::create([
            'owner_user_id' => $ownerUserId,
            'subject_id' => $subjectId,
            'library_section_id' => $sectionId,
            'resource_type' => 'file',
            'title' => $title,
            'description' => null,
            'status' => 'active',
            'storage_disk' => 'public',
            'file_path' => $path,
            'original_filename' => basename($path),
            'mime_type' => 'application/octet-stream',
            'file_size' => 1234,
            'external_url' => null,
            'sort_order' => 1,
            'created_by_user_id' => $ownerUserId,
        ])->id;
    }
}
