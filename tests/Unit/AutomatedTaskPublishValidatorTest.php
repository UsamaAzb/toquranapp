<?php

namespace Tests\Unit;

use App\Models\MainDailySessionMainTask;
use App\Models\MainDailySessionMainTaskAttachment;
use App\Models\MainDailySessionStudentAssignment;
use App\Models\MainDailySessionSubscription;
use App\Models\MainDailySessionTemplate;
use App\Models\MainDailySessionVersion;
use App\Models\MainDailySessionVersionTask;
use App\Models\User;
use App\Services\AutomatedTaskPublishValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

class AutomatedTaskPublishValidatorTest extends TestCase
{
    use CreatesAutomatedTaskTestingSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAutomatedTaskSchema();
        $this->seedTaskTypes();
    }

    public function test_diagnose_version_requires_meaningful_content_when_a_selected_task_has_no_description_or_attachment(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Validator template',
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
            'title' => 'Task without content',
            'description' => null,
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);

        $versionTask = MainDailySessionVersionTask::create([
            'version_id' => $version->id,
            'main_task_id' => $task->id,
            'description_override' => null,
            'sort_order' => 1,
        ]);

        $diagnosis = app(AutomatedTaskPublishValidator::class)->diagnoseVersion(
            $versionTask->version()->with([
                'versionTasks.mainTask.attachments',
            ])->firstOrFail()
        );

        $this->assertFalse($diagnosis['passes']);
        $this->assertNotEmpty($diagnosis['errors']);
        $this->assertStringContainsString('needs a description', $diagnosis['errors'][0]);
    }

    public function test_publish_ignores_preparation_versions_without_active_assigned_students(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');

        $template = MainDailySessionTemplate::create([
            'title' => 'Publish validator template',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $participatingVersion = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Published now',
            'sort_order' => 1,
        ]);

        $preparationVersion = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $template->id,
            'display_name' => 'Preparation only',
            'sort_order' => 2,
        ]);

        $task = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $template->id,
            'title' => 'Reading',
            'description' => 'Base description',
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);

        MainDailySessionVersionTask::create([
            'version_id' => $participatingVersion->id,
            'main_task_id' => $task->id,
            'description_override' => 'Ready to publish',
            'sort_order' => 1,
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

        MainDailySessionStudentAssignment::create([
            'student_id' => $student['student_id'],
            'main_daily_session_template_id' => $template->id,
            'version_id' => $participatingVersion->id,
            'effective_from_date' => now()->toDateString(),
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = app(AutomatedTaskPublishValidator::class)->validate($template->fresh());

        $this->assertTrue($result->passes);
        $this->assertSame([], $result->errors);
        $this->assertSame(0, $preparationVersion->versionTasks()->count());
        $this->assertStringNotContainsString('Preparation only', implode(' ', $result->errors));
    }

    public function test_meaningful_content_passes_when_main_task_has_attachment_without_description_override(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $template = MainDailySessionTemplate::create([
            'title' => 'Attachment validator template',
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
            'title' => 'Attachment-only task',
            'description' => null,
            'task_type_id' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'sort_order' => 1,
        ]);

        MainDailySessionMainTaskAttachment::create([
            'main_task_id' => $task->id,
            'type' => 'file',
            'title' => 'Worksheet',
            'path' => 'automated/worksheet.pdf',
            'file_size' => 100,
            'sort_order' => 1,
        ]);

        MainDailySessionVersionTask::create([
            'version_id' => $version->id,
            'main_task_id' => $task->id,
            'description_override' => null,
            'sort_order' => 1,
        ]);

        $diagnosis = app(AutomatedTaskPublishValidator::class)->diagnoseVersion(
            $version->fresh()->load('versionTasks.mainTask.attachments')
        );

        $this->assertTrue($diagnosis['passes']);
        $this->assertSame([], $diagnosis['errors']);
    }
}
