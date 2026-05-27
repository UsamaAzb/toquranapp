<?php

namespace Tests\Unit;

use App\Models\DifferentiatedTask;
use App\Models\DifferentiatedTaskVersion;
use App\Models\User;
use App\Services\DifferentiatedTaskPublishValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

class DifferentiatedTaskPublishValidatorTest extends TestCase
{
    use CreatesAutomatedTaskTestingSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createDifferentiatedTaskSchema();
        $this->seedTaskTypes();
    }

    public function test_publish_requires_two_meaningful_versions(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $task = DifferentiatedTask::create([
            'title' => 'Differentiated reading',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'task_type_id' => 1,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        DifferentiatedTaskVersion::create([
            'differentiated_task_id' => $task->id,
            'display_name' => 'Version 1',
            'description' => 'Ready work',
            'sort_order' => 1,
        ]);
        DifferentiatedTaskVersion::create([
            'differentiated_task_id' => $task->id,
            'display_name' => 'Version 2',
            'description' => null,
            'sort_order' => 2,
        ]);

        $result = app(DifferentiatedTaskPublishValidator::class)->validate($task->fresh());

        $this->assertFalse($result->passes);
        $this->assertStringContainsString('At least two task versions', implode(' ', $result->errors));
    }

    public function test_publish_passes_with_two_meaningful_versions_and_valid_recurrence(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $task = DifferentiatedTask::create([
            'title' => 'Differentiated writing',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'task_type_id' => 1,
            'recurrence_kind' => 'weekly',
            'recurrence_weekdays' => 'mon,wed',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        foreach (['Support', 'Stretch'] as $index => $name) {
            DifferentiatedTaskVersion::create([
                'differentiated_task_id' => $task->id,
                'display_name' => $name,
                'description' => 'Ready work '.$name,
                'sort_order' => $index + 1,
            ]);
        }

        $result = app(DifferentiatedTaskPublishValidator::class)->validate($task->fresh());

        $this->assertTrue($result->passes);
        $this->assertSame([], $result->errors);
    }
}
