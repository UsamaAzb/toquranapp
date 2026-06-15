<?php

namespace Tests\Feature\CoreLms;

use App\Models\DifferentiatedTask;
use App\Models\DifferentiatedTaskAttachment;
use App\Models\DifferentiatedTaskStudentAssignment;
use App\Models\DifferentiatedTaskStudentGenerationState;
use App\Models\DifferentiatedTaskVersion;
use App\Models\DifferentiatedTaskVersionAttachment;
use App\Models\User;
use App\Models\VocabularyGameAssignment;
use App\Services\Library\GeneralLibraryAttachmentAdapter;
use App\Services\Library\LibraryToDifferentiatedAttachmentWriter;
use App\Services\DifferentiatedTaskPublisher;
use App\Services\DifferentiatedTaskSnapshotWriter;
use App\Services\Vocabulary\VocabularyGameAttachmentBuilder;
use App\Support\BookingSubjectProvisioning;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

class DifferentiatedTaskSnapshotWriterTest extends TestCase
{
    use CreatesAutomatedTaskTestingSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createDifferentiatedTaskSchema();
        $this->seedTaskTypes();
    }

    public function test_snapshot_writer_creates_existing_learner_rows_and_is_idempotent(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('dt-source/sample.pdf', 'pdf content');

        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');

        $task = DifferentiatedTask::create([
            'title' => 'Snapshot DT',
            'description' => 'Base task',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'task_type_id' => 1,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'status' => 'active',
            'published_at' => Carbon::parse('2026-04-30 09:00:00'),
        ]);

        $version = DifferentiatedTaskVersion::create([
            'differentiated_task_id' => $task->id,
            'display_name' => 'Support',
            'description' => 'Support version',
            'sort_order' => 1,
        ]);

        $attachment = DifferentiatedTaskAttachment::create([
            'differentiated_task_id' => $task->id,
            'type' => 'file',
            'title' => 'Sample PDF',
            'path' => 'dt-source/sample.pdf',
            'file_size' => 11,
            'sort_order' => 1,
        ]);

        DifferentiatedTaskVersionAttachment::create([
            'version_id' => $version->id,
            'attachment_id' => $attachment->id,
            'sort_order' => 1,
        ]);

        $assignment = DifferentiatedTaskStudentAssignment::create([
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'version_id' => $version->id,
            'effective_from_date' => '2026-04-30',
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
        ]);

        $writer = app(DifferentiatedTaskSnapshotWriter::class);

        $this->assertTrue($writer->generateForStudent(
            $student['student_id'],
            $task->id,
            $version->id,
            $assignment->id,
            Carbon::parse('2026-04-30')
        ));

        $this->assertFalse($writer->generateForStudent(
            $student['student_id'],
            $task->id,
            $version->id,
            $assignment->id,
            Carbon::parse('2026-04-30')
        ));

        $this->assertDatabaseCount('class_sessions', 1);
        $this->assertDatabaseCount('session_tasks', 1);
        $this->assertDatabaseCount('session_task_student', 1);
        $this->assertDatabaseCount('attachment_files', 1);

        $this->assertDatabaseHas('class_sessions', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'generated_for_date' => '2026-04-30',
        ]);

        $this->assertDatabaseHas('session_tasks', [
            'source_differentiated_task_id_snapshot' => $task->id,
            'source_differentiated_task_version_id_snapshot' => $version->id,
            'source_differentiated_task_assignment_id_snapshot' => $assignment->id,
            'version_display_name_snapshot' => 'Support',
        ]);

        $copiedPath = DB::table('attachment_files')->value('path');
        $this->assertStringStartsWith('automated-tasks/differentiated/', $copiedPath);
        Storage::disk('public')->assertExists($copiedPath);

        $classSessionId = DB::table('class_sessions')->value('id');
        $classSessionDuplicateBlocked = false;
        $sessionTaskDuplicateBlocked = false;

        try {
            DB::table('class_sessions')->insert([
                'differentiated_task_id' => $task->id,
                'student_id' => $student['student_id'],
                'generated_for_date' => '2026-04-30',
            ]);
        } catch (QueryException) {
            $classSessionDuplicateBlocked = true;
        }

        try {
            DB::table('session_tasks')->insert([
                'class_session_id' => $classSessionId,
                'source_differentiated_task_id_snapshot' => $task->id,
            ]);
        } catch (QueryException) {
            $sessionTaskDuplicateBlocked = true;
        }

        $this->assertTrue($classSessionDuplicateBlocked);
        $this->assertTrue($sessionTaskDuplicateBlocked);
    }

    public function test_general_library_attachment_must_be_selected_on_differentiated_version_before_delivery(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('local')->put('general-library/fatiha.pdf', 'pdf content');

        $teacher = User::factory()->create();
        Role::findOrCreate('teacher', 'web');
        $teacher->assignRole('teacher');
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');

        $task = DifferentiatedTask::create([
            'title' => 'Recitation support',
            'description' => 'Practice assigned recitation.',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'task_type_id' => 1,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'status' => 'active',
            'published_at' => Carbon::parse('2026-04-30 09:00:00'),
        ]);
        $version = DifferentiatedTaskVersion::create([
            'differentiated_task_id' => $task->id,
            'display_name' => 'Support',
            'description' => 'Support version',
            'sort_order' => 1,
        ]);
        $resourceId = DB::table('general_library_resources')->insertGetId([
            'general_library_folder_id' => null,
            'resource_type' => 'file',
            'title' => 'Al-Fatiha tracing sheet',
            'description' => 'Read and trace.',
            'status' => 'active',
            'storage_disk' => 'local',
            'file_path' => 'general-library/fatiha.pdf',
            'file_size' => 11,
            'sort_order' => 1,
            'created_by_user_id' => $teacher->id,
            'updated_by_user_id' => $teacher->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue(app(LibraryToDifferentiatedAttachmentWriter::class)->writeOneForTaskAtSortOrder(
            $task,
            GeneralLibraryAttachmentAdapter::GENERAL_PREFIX.$resourceId,
            (int) $teacher->id,
            (int) $context['subject_id'],
            1
        ));
        $this->assertFalse(app(LibraryToDifferentiatedAttachmentWriter::class)->writeOneForTaskAtSortOrder(
            $task,
            'series__story__999',
            (int) $teacher->id,
            (int) $context['subject_id'],
            2
        ));

        $pooledAttachment = DifferentiatedTaskAttachment::query()
            ->where('differentiated_task_id', $task->id)
            ->firstOrFail();

        Storage::disk('public')->assertExists($pooledAttachment->path);
        $this->assertSame(1, DifferentiatedTaskAttachment::query()
            ->where('differentiated_task_id', $task->id)
            ->count());

        $assignment = DifferentiatedTaskStudentAssignment::create([
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'version_id' => $version->id,
            'effective_from_date' => '2026-04-30',
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
        ]);

        $this->assertTrue(app(DifferentiatedTaskSnapshotWriter::class)->generateForStudent(
            $student['student_id'],
            $task->id,
            $version->id,
            $assignment->id,
            Carbon::parse('2026-04-30')
        ));
        $this->assertDatabaseCount('attachment_files', 0);

        DifferentiatedTaskVersionAttachment::create([
            'version_id' => $version->id,
            'attachment_id' => $pooledAttachment->id,
            'sort_order' => 1,
        ]);

        $this->assertTrue(app(DifferentiatedTaskSnapshotWriter::class)->generateForStudent(
            $student['student_id'],
            $task->id,
            $version->id,
            $assignment->id,
            Carbon::parse('2026-05-01')
        ));

        $generatedPath = DB::table('attachment_files')->value('path');

        $this->assertStringStartsWith('automated-tasks/differentiated/', $generatedPath);
        Storage::disk('public')->assertExists($generatedPath);
    }

    public function test_snapshot_writer_preserves_differentiated_version_attachment_order(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('dt-source/first.pdf', 'first content');
        Storage::disk('public')->put('dt-source/second.pdf', 'second content');

        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
        [$task, $version, $assignment] = $this->createPublisherReadyDifferentiatedTask(
            $teacher,
            $context,
            $student['student_id']
        );

        $first = DifferentiatedTaskAttachment::create([
            'differentiated_task_id' => $task->id,
            'type' => 'file',
            'title' => 'First PDF',
            'path' => 'dt-source/first.pdf',
            'file_size' => 11,
            'sort_order' => 1,
        ]);

        $second = DifferentiatedTaskAttachment::create([
            'differentiated_task_id' => $task->id,
            'type' => 'file',
            'title' => 'Second PDF',
            'path' => 'dt-source/second.pdf',
            'file_size' => 12,
            'sort_order' => 2,
        ]);

        DifferentiatedTaskVersionAttachment::create([
            'version_id' => $version->id,
            'attachment_id' => $second->id,
            'sort_order' => 1,
        ]);

        DifferentiatedTaskVersionAttachment::create([
            'version_id' => $version->id,
            'attachment_id' => $first->id,
            'sort_order' => 2,
        ]);

        app(DifferentiatedTaskSnapshotWriter::class)->generateForStudent(
            $student['student_id'],
            $task->id,
            $version->id,
            $assignment->id,
            Carbon::parse('2026-04-30')
        );

        $rows = DB::table('attachment_files')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['title', 'sort_order']);

        $this->assertSame(['Second PDF', 'First PDF'], $rows->pluck('title')->all());
        $this->assertSame([1, 2], $rows->pluck('sort_order')->all());
    }

    public function test_snapshot_writer_materializes_vocabulary_attachment_as_student_game_assignment(): void
    {
        $this->createVocabularyGameTestingTables();

        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext(
            $teacher,
            BookingSubjectProvisioning::SUBJECT_LANGUAGE_AND_LITERATURE
        );
        $student = $this->enrollStudent($context, 'Mira', 'Lane', 'Nadia');
        [$task, $version, $assignment] = $this->createPublisherReadyDifferentiatedTask(
            $teacher,
            $context,
            $student['student_id']
        );
        $setId = DB::table('vocabulary_sets')->insertGetId([
            'parent_id' => null,
            'title' => 'Phonics Set A',
            'description' => 'Short vowel words',
            'node_type' => 'playable',
            'set_type' => 'teacher',
            'source_kind' => 'custom',
            'source_key' => 'teacher-phonics-set-a',
            'owner_user_id' => $teacher->id,
            'visibility' => 'private',
            'sort_order' => 1,
            'created_by_user_id' => $teacher->id,
            'updated_by_user_id' => $teacher->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $attachment = DifferentiatedTaskAttachment::create([
            'differentiated_task_id' => $task->id,
            'type' => 'link',
            'title' => 'Vocab Game: Phonics Set A',
            'description' => 'Short vowel words',
            'path' => VocabularyGameAttachmentBuilder::sourcePath($setId),
            'url' => null,
            'file_size' => null,
            'sort_order' => 1,
        ]);

        DifferentiatedTaskVersionAttachment::create([
            'version_id' => $version->id,
            'attachment_id' => $attachment->id,
            'sort_order' => 1,
        ]);

        app(DifferentiatedTaskSnapshotWriter::class)->generateForStudent(
            $student['student_id'],
            $task->id,
            $version->id,
            $assignment->id,
            Carbon::parse('2026-04-30')
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
            'title' => 'Vocab Game: Phonics Set A',
            'type' => 'link',
            'path' => route('vocabulary.games.assignment', ['assignment' => $gameAssignmentId]),
        ]);
    }


    public function test_publisher_skips_missing_source_attachment_without_retrying_forever(): void
    {
        Storage::fake('public');

        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
        [$task, $version, $assignment] = $this->createPublisherReadyDifferentiatedTask($teacher, $context, $student['student_id']);

        $attachment = DifferentiatedTaskAttachment::create([
            'differentiated_task_id' => $task->id,
            'type' => 'file',
            'title' => 'Missing PDF',
            'path' => 'dt-source/missing.pdf',
            'file_size' => 11,
            'sort_order' => 1,
        ]);

        DifferentiatedTaskVersionAttachment::create([
            'version_id' => $version->id,
            'attachment_id' => $attachment->id,
            'sort_order' => 1,
        ]);

        app(DifferentiatedTaskPublisher::class)->generateForStudent(
            $student['student_id'],
            Carbon::parse('2026-04-30')
        );

        $this->assertDatabaseHas('class_sessions', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'generated_for_date' => '2026-04-30',
        ]);

        $this->assertDatabaseHas('session_tasks', [
            'source_differentiated_task_id_snapshot' => $task->id,
            'source_differentiated_task_version_id_snapshot' => $version->id,
            'source_differentiated_task_assignment_id_snapshot' => $assignment->id,
        ]);

        $this->assertDatabaseCount('attachment_files', 0);
        $this->assertDatabaseHas('differentiated_task_student_generation_states', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'last_generated_date' => '2026-04-30',
        ]);
    }

    public function test_publisher_generates_for_trial_status_student_when_lifecycle_and_visibility_are_valid(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Yara', 'Trial', 'Mona');
        [$task, $version, $assignment] = $this->createPublisherReadyDifferentiatedTask($teacher, $context, $student['student_id']);

        DB::table('students')
            ->where('id', $student['student_id'])
            ->update(['status' => 'trial', 'account_status' => 'active']);

        app(DifferentiatedTaskPublisher::class)->generateForStudent(
            $student['student_id'],
            Carbon::parse('2026-04-30')
        );

        $this->assertDatabaseHas('class_sessions', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'generated_for_date' => '2026-04-30',
        ]);

        $this->assertDatabaseHas('session_tasks', [
            'source_differentiated_task_id_snapshot' => $task->id,
            'source_differentiated_task_version_id_snapshot' => $version->id,
            'source_differentiated_task_assignment_id_snapshot' => $assignment->id,
        ]);
    }

    public function test_publisher_advances_fence_when_student_loses_subject_visibility(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Lina', 'Hart', 'Nora');
        [$task] = $this->createPublisherReadyDifferentiatedTask($teacher, $context, $student['student_id']);

        DB::table('students_subjects')
            ->where('student_id', $student['student_id'])
            ->update(['status' => 'inactive']);

        app(DifferentiatedTaskPublisher::class)->generateForStudent(
            $student['student_id'],
            Carbon::parse('2026-04-30')
        );

        $this->assertDatabaseMissing('class_sessions', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'generated_for_date' => '2026-04-30',
        ]);

        $this->assertDatabaseHas('differentiated_task_student_generation_states', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'last_generated_date' => '2026-04-30',
        ]);
    }

    public function test_publisher_skips_when_student_subject_class_link_is_missing(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Mariam', 'Osama', 'Heba');
        [$task] = $this->createPublisherReadyDifferentiatedTask($teacher, $context, $student['student_id']);

        DB::table('students_subjects')
            ->where('student_id', $student['student_id'])
            ->update(['class_subject_id' => null]);

        app(DifferentiatedTaskPublisher::class)->generateForStudent(
            $student['student_id'],
            Carbon::parse('2026-04-30')
        );

        $this->assertDatabaseMissing('class_sessions', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'generated_for_date' => '2026-04-30',
        ]);

        $this->assertDatabaseHas('differentiated_task_student_generation_states', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'last_generated_date' => '2026-04-30',
        ]);
    }

    public function test_publisher_skips_student_from_unowned_class_subject(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudentInAnotherClassForSubject($context, 'Nour', 'Ali', 'Sara');
        [$task] = $this->createPublisherReadyDifferentiatedTask($teacher, $context, $student['student_id']);

        app(DifferentiatedTaskPublisher::class)->generateForStudent(
            $student['student_id'],
            Carbon::parse('2026-04-30')
        );

        $this->assertDatabaseMissing('class_sessions', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'generated_for_date' => '2026-04-30',
        ]);

        $this->assertDatabaseHas('differentiated_task_student_generation_states', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'last_generated_date' => '2026-04-30',
        ]);
    }

    public function test_publisher_skips_when_current_class_does_not_match_students_subject_class_subject(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Hana', 'Drift', 'Mona');
        $otherContext = $this->createAdditionalOwnedClassForSubject($teacher, $context);
        [$task] = $this->createPublisherReadyDifferentiatedTask($teacher, $context, $student['student_id']);

        DB::table('students')
            ->where('id', $student['student_id'])
            ->update(['current_class_id' => $otherContext['class_id']]);

        app(DifferentiatedTaskPublisher::class)->generateForStudent(
            $student['student_id'],
            Carbon::parse('2026-04-30')
        );

        $this->assertDatabaseMissing('class_sessions', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'generated_for_date' => '2026-04-30',
        ]);

        $this->assertDatabaseHas('differentiated_task_student_generation_states', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'last_generated_date' => '2026-04-30',
        ]);
    }

    public function test_publisher_skips_when_subject_enrollment_is_missing(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $student = $this->enrollStudent($context, 'Farah', 'Yousef', 'Mona');
        [$task] = $this->createPublisherReadyDifferentiatedTask($teacher, $context, $student['student_id']);

        DB::table('students_subjects')
            ->where('student_id', $student['student_id'])
            ->delete();

        app(DifferentiatedTaskPublisher::class)->generateForStudent(
            $student['student_id'],
            Carbon::parse('2026-04-30')
        );

        $this->assertDatabaseMissing('class_sessions', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'generated_for_date' => '2026-04-30',
        ]);

        $this->assertDatabaseHas('differentiated_task_student_generation_states', [
            'student_id' => $student['student_id'],
            'differentiated_task_id' => $task->id,
            'last_generated_date' => '2026-04-30',
        ]);
    }

    private function createPublisherReadyDifferentiatedTask(User $teacher, array $context, int $studentId): array
    {
        $task = DifferentiatedTask::create([
            'title' => 'Publisher DT',
            'description' => 'Base task',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'task_type_id' => 1,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'default_points' => 5,
            'max_points' => 10,
            'status' => 'active',
            'published_at' => Carbon::parse('2026-04-30 09:00:00'),
        ]);

        $version = DifferentiatedTaskVersion::create([
            'differentiated_task_id' => $task->id,
            'display_name' => 'Support',
            'description' => 'Support version',
            'sort_order' => 1,
        ]);

        $assignment = DifferentiatedTaskStudentAssignment::create([
            'student_id' => $studentId,
            'differentiated_task_id' => $task->id,
            'version_id' => $version->id,
            'effective_from_date' => '2026-04-30',
            'effective_to_date' => null,
            'assigned_by_user_id' => $teacher->id,
        ]);

        DifferentiatedTaskStudentGenerationState::create([
            'student_id' => $studentId,
            'differentiated_task_id' => $task->id,
            'is_active' => 1,
            'start_date' => '2026-04-30',
        ]);

        return [$task, $version, $assignment];
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

    private function createAdditionalOwnedClassForSubject(User $teacher, array $context): array
    {
        $classId = DB::table('classes')->insertGetId([
            'title' => 'Class C',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classSubjectId = DB::table('class_subjects')->insertGetId([
            'class_id' => $classId,
            'grade_level_subject_id' => $context['grade_level_subject_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $teacherSubjectClassId = DB::table('teacher_subject_classes')->insertGetId([
            'user_teacher_coteacher_id' => $teacher->id,
            'class_subject_id' => $classSubjectId,
            'class_id' => $classId,
            'subject_id' => $context['subject_id'],
            'grade_id' => 1,
            'class_name' => 'Grade 8 C',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            ...$context,
            'class_id' => $classId,
            'class_subject_id' => $classSubjectId,
            'teacher_subject_class_id' => $teacherSubjectClassId,
        ];
    }
}
