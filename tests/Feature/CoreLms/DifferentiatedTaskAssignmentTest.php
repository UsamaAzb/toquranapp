<?php

namespace Tests\Feature\CoreLms;

use App\Livewire\Teacher\DifferentiatedTaskAssignmentModal;
use App\Livewire\Teacher\DifferentiatedTasksBoard;
use App\Models\DifferentiatedTask;
use App\Models\DifferentiatedTaskStudentAssignment;
use App\Models\DifferentiatedTaskStudentGenerationState;
use App\Models\DifferentiatedTaskVersion;
use App\Models\LibraryResource;
use App\Models\LibrarySection;
use App\Models\User;
use App\Services\DifferentiatedTaskAssignmentService;
use App\Services\DifferentiatedTaskPublishValidator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

class DifferentiatedTaskAssignmentTest extends TestCase
{
    use CreatesAutomatedTaskTestingSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createDifferentiatedTaskSchema();
        $this->seedTaskTypes();
    }

    public function test_assignment_modal_groups_students_and_saves_version_membership_changes(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-30 10:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $assignedHereStudent = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
            $assignedElsewhereStudent = $this->enrollStudent($context, 'Lina', 'Hart', 'Nora');
            $unassignedStudent = $this->enrollStudent($context, 'Omar', 'Reed', 'Pia');
            $suspendedStudent = $this->enrollStudent($context, 'Yara', 'West', 'Dina');

            DB::table('students')
                ->whereIn('id', [
                    $assignedHereStudent['student_id'],
                    $assignedElsewhereStudent['student_id'],
                    $unassignedStudent['student_id'],
                ])
                ->update(['status' => 'trial']);

            DB::table('students')
                ->where('id', $suspendedStudent['student_id'])
                ->update(['account_status' => 'suspended']);

            [$task, $versionA, $versionB] = $this->createAssignableDifferentiatedTask($teacher, $context);

            DifferentiatedTaskStudentAssignment::create([
                'student_id' => $suspendedStudent['student_id'],
                'differentiated_task_id' => $task->id,
                'version_id' => $versionA->id,
                'effective_from_date' => '2026-04-20',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            DifferentiatedTaskStudentAssignment::create([
                'student_id' => $assignedHereStudent['student_id'],
                'differentiated_task_id' => $task->id,
                'version_id' => $versionA->id,
                'effective_from_date' => '2026-04-20',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            DifferentiatedTaskStudentAssignment::create([
                'student_id' => $assignedElsewhereStudent['student_id'],
                'differentiated_task_id' => $task->id,
                'version_id' => $versionB->id,
                'effective_from_date' => '2026-04-20',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            $this->actingAs($teacher);

            $component = Livewire::test(DifferentiatedTaskAssignmentModal::class)
                ->call('open', $task->id, $versionA->id)
                ->assertSet('activeVersionId', $versionA->id)
                ->assertSet("selectedStudentIds.{$assignedHereStudent['student_id']}", true)
                ->assertSet("selectedStudentIds.{$assignedElsewhereStudent['student_id']}", false)
                ->assertSet("selectedStudentIds.{$unassignedStudent['student_id']}", false)
                ->assertSeeHtml('modal-dialog')
                ->assertSeeHtml('automated-task-student-card')
                ->assertDontSee('Yara West');

            $rendered = $component->instance()->render()->getData();

            $this->assertCount(1, $rendered['sections']['assigned_here']['rows']);
            $this->assertCount(1, $rendered['sections']['assigned_elsewhere']['rows']);
            $this->assertCount(1, $rendered['sections']['unassigned']['rows']);

            $filtered = $component
                ->set('search', 'Omar')
                ->instance()
                ->render()
                ->getData();

            $this->assertCount(0, $filtered['sections']['assigned_here']['rows']);
            $this->assertCount(0, $filtered['sections']['assigned_elsewhere']['rows']);
            $this->assertCount(1, $filtered['sections']['unassigned']['rows']);

            $component->set("selectedStudentIds.{$assignedHereStudent['student_id']}", false)
                ->set("selectedStudentIds.{$assignedElsewhereStudent['student_id']}", true)
                ->set("selectedStudentIds.{$unassignedStudent['student_id']}", true)
                ->call('saveBulk');

            $this->assertDatabaseMissing('differentiated_task_student_assignments', [
                'student_id' => $assignedHereStudent['student_id'],
                'differentiated_task_id' => $task->id,
                'version_id' => $versionA->id,
                'effective_to_date' => null,
            ]);

            $movedAssignment = DifferentiatedTaskStudentAssignment::query()
                ->where('student_id', $assignedElsewhereStudent['student_id'])
                ->where('differentiated_task_id', $task->id)
                ->where('version_id', $versionA->id)
                ->whereNull('effective_to_date')
                ->firstOrFail();

            $availableAssignment = DifferentiatedTaskStudentAssignment::query()
                ->where('student_id', $unassignedStudent['student_id'])
                ->where('differentiated_task_id', $task->id)
                ->where('version_id', $versionA->id)
                ->whereNull('effective_to_date')
                ->firstOrFail();

            $this->assertSame('2026-04-30', $movedAssignment->effective_from_date->toDateString());
            $this->assertSame('2026-04-30', $availableAssignment->effective_from_date->toDateString());

            $this->assertDatabaseMissing('differentiated_task_student_assignments', [
                'student_id' => $assignedElsewhereStudent['student_id'],
                'differentiated_task_id' => $task->id,
                'version_id' => $versionB->id,
                'effective_to_date' => null,
            ]);

            $this->assertSame(1, DifferentiatedTaskStudentAssignment::query()
                ->where('student_id', $assignedElsewhereStudent['student_id'])
                ->where('differentiated_task_id', $task->id)
                ->whereNull('effective_to_date')
                ->count());

            $state = \App\Models\DifferentiatedTaskStudentGenerationState::query()
                ->where('student_id', $unassignedStudent['student_id'])
                ->where('differentiated_task_id', $task->id)
                ->firstOrFail();

            $this->assertTrue((bool) $state->is_active);
            $this->assertSame('2026-04-30', $state->start_date->toDateString());

            $this->assertDatabaseHas('differentiated_task_student_assignments', [
                'student_id' => $suspendedStudent['student_id'],
                'differentiated_task_id' => $task->id,
                'version_id' => $versionA->id,
                'effective_to_date' => null,
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_assignment_modal_rejects_stale_invalid_selected_students(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $staleStudent = $this->enrollStudent($context, 'Yara', 'West', 'Dina');
        [$task, $versionA] = $this->createAssignableDifferentiatedTask($teacher, $context);

        DB::table('students')
            ->where('id', $staleStudent['student_id'])
            ->update(['account_status' => 'suspended']);

        $this->actingAs($teacher);

        Livewire::test(DifferentiatedTaskAssignmentModal::class)
            ->call('open', $task->id, $versionA->id)
            ->set("selectedStudentIds.{$staleStudent['student_id']}", true)
            ->call('saveBulk')
            ->assertHasErrors(['assignment'])
            ->assertSee('This student is not available for assignment.');

        $this->assertDatabaseMissing('differentiated_task_student_assignments', [
            'student_id' => $staleStudent['student_id'],
            'differentiated_task_id' => $task->id,
            'version_id' => $versionA->id,
            'effective_to_date' => null,
        ]);
    }

    public function test_assignment_modal_shows_compact_grade_metadata_without_repeating_student_name(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Doha', 'Asmaa', 'doha');
        [$task, $versionA] = $this->createAssignableDifferentiatedTask($teacher, $context);

        DB::table('classes')
            ->where('id', $context['class_id'])
            ->update(['title' => 'doha-Grade 2']);

        $this->actingAs($teacher);

        Livewire::test(DifferentiatedTaskAssignmentModal::class)
            ->call('open', $task->id, $versionA->id)
            ->assertSee('Doha Asmaa')
            ->assertSee('Grade 2 | #'.$student['student_id'])
            ->assertDontSee('doha-Grade 2 | #'.$student['student_id']);
    }

    public function test_assignment_modal_excludes_student_when_subject_class_link_is_missing(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Mariam', 'Osama', 'Heba');
        [$task, $versionA] = $this->createAssignableDifferentiatedTask($teacher, $context);

        DB::table('students_subjects')
            ->where('student_id', $student['student_id'])
            ->update(['class_subject_id' => null]);

        $this->actingAs($teacher);

        Livewire::test(DifferentiatedTaskAssignmentModal::class)
            ->call('open', $task->id, $versionA->id)
            ->assertSet("selectedStudentIds.{$student['student_id']}", null)
            ->assertDontSee('Mariam Osama');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This student is not available in the selected subject.');

        app(DifferentiatedTaskAssignmentService::class)->assign(
            $student['student_id'],
            $task->id,
            $versionA->id,
            $teacher->id,
            $context['subject_id']
        );
    }

    public function test_assignment_modal_excludes_student_from_unowned_class_subject(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $otherClassStudent = $this->enrollStudentInAnotherClassForSubject($context, 'Nour', 'Ali', 'Sara');
        [$task, $versionA] = $this->createAssignableDifferentiatedTask($teacher, $context);

        $this->actingAs($teacher);

        Livewire::test(DifferentiatedTaskAssignmentModal::class)
            ->call('open', $task->id, $versionA->id)
            ->assertSet("selectedStudentIds.{$otherClassStudent['student_id']}", null)
            ->assertDontSee('Nour Ali');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This student is not available in the selected subject.');

        app(DifferentiatedTaskAssignmentService::class)->assign(
            $otherClassStudent['student_id'],
            $task->id,
            $versionA->id,
            $teacher->id,
            $context['subject_id']
        );
    }

    public function test_archived_task_cannot_open_assignment_modal_or_receive_direct_assignments(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
        [$task, $versionA] = $this->createAssignableDifferentiatedTask($teacher, $context);
        $task->update(['status' => 'archived']);

        $this->actingAs($teacher);

        Livewire::test(DifferentiatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->call('openAssignmentModal', $task->id, $versionA->id)
            ->assertHasErrors(['assignment'])
            ->assertSee('Restore this Differentiated Task before editing assignments.');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Archived Differentiated Tasks cannot be assigned.');

        app(DifferentiatedTaskAssignmentService::class)->assign(
            $student['student_id'],
            $task->id,
            $versionA->id,
            $teacher->id,
            $context['subject_id']
        );
    }

    public function test_board_blocks_deleting_draft_task_with_assignment_state(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
        [$task, $versionA] = $this->createAssignableDifferentiatedTask($teacher, $context);

        DifferentiatedTaskStudentAssignment::create([
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'version_id' => $versionA->id,
            'effective_from_date' => now()->toDateString(),
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher);

        Livewire::test(DifferentiatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->call('deleteTask', $task->id)
            ->assertHasErrors(['task']);

        $this->assertDatabaseHas('differentiated_tasks', [
            'id' => $task->id,
        ]);

        $this->assertDatabaseHas('differentiated_task_student_assignments', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'version_id' => $versionA->id,
        ]);
    }

    public function test_board_create_task_seeds_default_versions_and_renders_subject_context(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher);

        Livewire::test(DifferentiatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->assertSee('English')
            ->set('createTaskOpen', true)
            ->set('draftTask.title', 'Reading English Books')
            ->set('draftTask.description', 'Reading work for varied learners')
            ->set('draftTask.task_type_id', 1)
            ->call('createTask')
            ->assertHasNoErrors()
            ->assertSee('Reading English Books')
            ->assertSee('Worksheet')
            ->assertSee('Reading work for varied learners');

        $task = DifferentiatedTask::query()
            ->where('title', 'Reading English Books')
            ->firstOrFail();

        $this->assertSame('draft', $task->status);
        $this->assertNull($task->published_at);

        $this->assertDatabaseHas('differentiated_task_versions', [
            'differentiated_task_id' => $task->id,
            'display_name' => 'Version 1',
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('differentiated_task_versions', [
            'differentiated_task_id' => $task->id,
            'display_name' => 'Version 2',
            'sort_order' => 2,
        ]);
    }

    public function test_board_uploads_task_files_when_file_picker_changes(): void
    {
        Storage::fake('public');

        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        [$task] = $this->createAssignableDifferentiatedTask($teacher, $context);

        $this->actingAs($teacher);

        Livewire::test(DifferentiatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->set("taskFilesByTask.{$task->id}", [
                UploadedFile::fake()->create('small-note.pdf', 8, 'application/pdf'),
            ])
            ->assertHasNoErrors();

        $attachment = $task->attachments()->where('type', 'file')->firstOrFail();

        $this->assertSame('small-note.pdf', $attachment->title);
        $this->assertNotNull($attachment->path);
        Storage::disk('public')->assertExists($attachment->path);
    }

    public function test_board_can_add_library_resources_to_differentiated_attachment_pool_in_selected_order(): void
    {
        $teacher = User::factory()->create();
        $otherTeacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        [$task] = $this->createAssignableDifferentiatedTask($teacher, $context);

        $section = $this->createLibrarySection($teacher, $context['subject_id']);
        $first = $this->createLibraryResource($teacher, $section, 'Library Video', 'library/video.mp4');
        $second = $this->createLibraryResource($teacher, $section, 'Library PDF', 'library/worksheet.pdf');
        $otherOwner = $this->createLibraryResource($otherTeacher, $section, 'Other Teacher PDF', 'library/other.pdf');

        $this->actingAs($teacher);

        Livewire::test(DifferentiatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->call('openLibraryPicker', $task->id)
            ->call('useLibraryResources', [(string) $second->id, (string) $first->id, (string) $otherOwner->id])
            ->assertHasNoErrors();

        $attachments = $task->attachments()->orderBy('sort_order')->get();

        $this->assertCount(2, $attachments);
        $this->assertSame(['Library PDF', 'Library Video'], $attachments->pluck('title')->all());
        $this->assertSame([1, 2], $attachments->pluck('sort_order')->all());
        $this->assertDatabaseMissing('differentiated_task_attachments', [
            'differentiated_task_id' => $task->id,
            'title' => 'Other Teacher PDF',
        ]);
    }

    public function test_board_publish_sets_activation_fence_without_generating_snapshots(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-30 11:30:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            [$task] = $this->createAssignableDifferentiatedTask($teacher, $context);

            $this->actingAs($teacher);

            Livewire::test(DifferentiatedTasksBoard::class, ['subjectId' => $context['subject_id']])
                ->call('publishTask', $task->id, app(DifferentiatedTaskPublishValidator::class))
                ->assertHasNoErrors();

            $task->refresh();

            $this->assertSame('active', $task->status);
            $this->assertSame('2026-04-30 11:30:00', $task->published_at->format('Y-m-d H:i:s'));
            $this->assertDatabaseMissing('class_sessions', [
                'differentiated_task_id' => $task->id,
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_same_version_save_does_not_shift_generation_state_start_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-20 09:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
            [$task, $versionA] = $this->createAssignableDifferentiatedTask($teacher, $context);
            $service = app(DifferentiatedTaskAssignmentService::class);

            $service->assign($student['student_id'], $task->id, $versionA->id, $teacher->id, $context['subject_id']);

            Carbon::setTestNow(Carbon::parse('2026-04-30 09:00:00'));

            $service->assign($student['student_id'], $task->id, $versionA->id, $teacher->id, $context['subject_id']);

            $state = DifferentiatedTaskStudentGenerationState::query()
                ->where('student_id', $student['student_id'])
                ->where('differentiated_task_id', $task->id)
                ->firstOrFail();

            $this->assertTrue((bool) $state->is_active);
            $this->assertSame('2026-04-20', $state->start_date->toDateString());

            $this->assertSame(1, DifferentiatedTaskStudentAssignment::query()
                ->where('student_id', $student['student_id'])
                ->where('differentiated_task_id', $task->id)
                ->whereNull('effective_to_date')
                ->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_unassign_can_close_assignment_for_now_suspended_student(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-20 09:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Lina', 'Hart', 'Nora');
            [$task, $versionA] = $this->createAssignableDifferentiatedTask($teacher, $context);
            $service = app(DifferentiatedTaskAssignmentService::class);

            $service->assign($student['student_id'], $task->id, $versionA->id, $teacher->id, $context['subject_id']);

            DB::table('students')
                ->where('id', $student['student_id'])
                ->update(['account_status' => 'suspended']);

            Carbon::setTestNow(Carbon::parse('2026-04-30 09:00:00'));

            $service->unassign($student['student_id'], $task->id, $teacher->id);

            $assignment = DifferentiatedTaskStudentAssignment::query()
                ->where('student_id', $student['student_id'])
                ->where('differentiated_task_id', $task->id)
                ->where('version_id', $versionA->id)
                ->firstOrFail();

            $this->assertSame('2026-04-20', $assignment->effective_from_date->toDateString());
            $this->assertSame('2026-04-29', $assignment->effective_to_date->toDateString());

            $state = DifferentiatedTaskStudentGenerationState::query()
                ->where('student_id', $student['student_id'])
                ->where('differentiated_task_id', $task->id)
                ->firstOrFail();

            $this->assertFalse((bool) $state->is_active);
            $this->assertSame('2026-04-29', $state->end_date->toDateString());
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createAssignableDifferentiatedTask(User $teacher, array $context): array
    {
        $task = DifferentiatedTask::create([
            'title' => 'Assignable DT',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'task_type_id' => 1,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'status' => 'draft',
        ]);

        $versionA = DifferentiatedTaskVersion::create([
            'differentiated_task_id' => $task->id,
            'display_name' => 'Version A',
            'description' => 'Ready A',
            'sort_order' => 1,
        ]);

        $versionB = DifferentiatedTaskVersion::create([
            'differentiated_task_id' => $task->id,
            'display_name' => 'Version B',
            'description' => 'Ready B',
            'sort_order' => 2,
        ]);

        return [$task, $versionA, $versionB];
    }

    private function createLibrarySection(User $teacher, int $subjectId): LibrarySection
    {
        return LibrarySection::create([
            'owner_user_id' => $teacher->id,
            'subject_id' => $subjectId,
            'parent_id' => null,
            'title' => 'Differentiated Library',
            'description' => null,
            'status' => LibrarySection::STATUS_ACTIVE,
            'sort_order' => 1,
            'created_by_user_id' => $teacher->id,
        ]);
    }

    private function createLibraryResource(
        User $teacher,
        LibrarySection $section,
        string $title,
        string $path
    ): LibraryResource {
        return LibraryResource::create([
            'owner_user_id' => $teacher->id,
            'subject_id' => $section->subject_id,
            'library_section_id' => $section->id,
            'resource_type' => LibraryResource::TYPE_FILE,
            'title' => $title,
            'description' => null,
            'status' => LibraryResource::STATUS_ACTIVE,
            'storage_disk' => 'public',
            'file_path' => $path,
            'original_filename' => basename($path),
            'mime_type' => 'application/octet-stream',
            'file_size' => 123,
            'external_url' => null,
            'sort_order' => 1,
            'created_by_user_id' => $teacher->id,
        ]);
    }

    private function enrollStudentInAnotherClassForSubject(
        array $context,
        string $firstName,
        string $lastName,
        string $parentFirstName
    ): array {
        $otherClassId = DB::table('classes')->insertGetId([
            'title' => 'Class B',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherClassSubjectId = DB::table('class_subjects')->insertGetId([
            'class_id' => $otherClassId,
            'grade_level_subject_id' => $context['grade_level_subject_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $student = $this->enrollStudent([
            ...$context,
            'class_id' => $otherClassId,
            'class_subject_id' => $otherClassSubjectId,
        ], $firstName, $lastName, $parentFirstName);

        return [
            ...$student,
            'class_id' => $otherClassId,
            'class_subject_id' => $otherClassSubjectId,
        ];
    }
}
