<?php

namespace Tests\Feature\CoreLms;

use App\Livewire\Teacher\SeriesTasksBoard;
use App\Models\SeriesTask;
use App\Models\SeriesTaskStudentAssignment;
use App\Models\SeriesTaskStudentGenerationState;
use App\Models\SeriesTaskVersion;
use App\Models\SeriesTaskVersionItem;
use App\Models\User;
use App\Services\SeriesLibrarySourceResolver;
use App\Services\SeriesTaskAssignmentService;
use App\Services\SeriesTaskPublisher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

class SeriesTaskGenerationTest extends TestCase
{
    use CreatesAutomatedTaskTestingSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSeriesTaskSchema();
        $this->seedTaskTypes();
        Role::findOrCreate('teacher');
    }

    public function test_source_resolver_accepts_ready_sources_and_blocks_audio_until_owner_approval(): void
    {
        $satParentId = DB::table('sat')->insertGetId([
            'title' => 'SAT Reading',
            'slug' => 'sat-reading',
            'sort' => 1,
        ]);
        $satChildId = DB::table('sat')->insertGetId([
            'parent_id' => $satParentId,
            'title' => 'Inference Drill',
            'slug' => 'inference-drill',
            'sort' => 1,
        ]);
        $audioUnitId = DB::table('audio_units')->insertGetId([
            'title' => 'Audio Basics',
            'order' => 1,
            'active' => 1,
        ]);

        $resolver = app(SeriesLibrarySourceResolver::class);
        $satItems = $resolver->orderedItems(SeriesLibrarySourceResolver::TYPE_SAT, $satParentId);

        $this->assertTrue($resolver->sourceIsSelectable(SeriesLibrarySourceResolver::TYPE_SAT, $satParentId));
        $this->assertFalse($resolver->sourceIsSelectable(SeriesLibrarySourceResolver::TYPE_AUDIO_LEVEL, $audioUnitId));
        $this->assertSame($satChildId, $satItems[0]->sourceId);
        $this->assertStringContainsString('course/sat/sat-reading/inference-drill', $satItems[0]->url);
    }

    public function test_source_resolver_includes_owned_new_library_folders_and_items(): void
    {
        $teacher = User::factory()->create();
        $otherTeacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $sectionId = $this->createLibrarySection($teacher, $context, 'Unit Videos', 2);
        $firstResourceId = $this->createLibraryResource($teacher, $context, $sectionId, 'Unit 1', 'library/unit-1.mp4', 2);
        $secondResourceId = $this->createLibraryResource($teacher, $context, $sectionId, 'Unit 2', 'library/unit-2.mp4', 1);
        $otherSectionId = $this->createLibrarySection($otherTeacher, $context, 'Other Teacher Folder', 1);
        $this->createLibraryResource($otherTeacher, $context, $otherSectionId, 'Hidden Unit', 'library/hidden.mp4', 1);

        $resolver = app(SeriesLibrarySourceResolver::class);
        $collections = collect($resolver->allCollections((int) $teacher->id, (int) $context['subject_id']));
        $items = $resolver->orderedItems(SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION, $sectionId);

        $this->assertTrue($resolver->sourceIsSelectable(
            SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION,
            $sectionId,
            (int) $teacher->id,
            (int) $context['subject_id']
        ));
        $this->assertFalse($resolver->sourceIsSelectable(
            SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION,
            $otherSectionId,
            (int) $teacher->id,
            (int) $context['subject_id']
        ));
        $this->assertTrue($collections->contains(fn ($collection): bool => $collection->id === $sectionId));
        $this->assertFalse($collections->contains(fn ($collection): bool => $collection->id === $otherSectionId));
        $this->assertSame([$secondResourceId, $firstResourceId], collect($items)->pluck('sourceId')->all());
        $this->assertSame(['library_resource', 'library_resource'], collect($items)->pluck('sourceType')->all());
    }

    public function test_source_resolver_browses_new_library_subfolders_before_selecting_series_items(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $parentSectionId = $this->createLibrarySection($teacher, $context, 'Parent Folder', 1);
        $childSectionId = $this->createLibrarySection($teacher, $context, 'Child Folder', 1, $parentSectionId);
        $nestedResourceId = $this->createLibraryResource($teacher, $context, $childSectionId, 'Nested PDF', 'library/nested.pdf', 1);

        $resolver = app(SeriesLibrarySourceResolver::class);
        $items = $resolver->orderedItems(SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION, $parentSectionId);
        $collections = collect($resolver->allCollections((int) $teacher->id, (int) $context['subject_id']));
        $childCollections = collect($resolver->allCollections((int) $teacher->id, (int) $context['subject_id'], $parentSectionId));
        $childItems = $resolver->orderedItems(SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION, $childSectionId);

        $this->assertFalse($resolver->sourceIsSelectable(
            SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION,
            $parentSectionId,
            (int) $teacher->id,
            (int) $context['subject_id']
        ));
        $this->assertTrue($resolver->sourceIsSelectable(
            SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION,
            $childSectionId,
            (int) $teacher->id,
            (int) $context['subject_id']
        ));
        $this->assertSame([], collect($items)->pluck('sourceId')->all());
        $this->assertSame([$nestedResourceId], collect($childItems)->pluck('sourceId')->all());
        $this->assertFalse($resolver->itemBelongsToCollection(
            SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION,
            $parentSectionId,
            'library_resource',
            $nestedResourceId
        ));
        $this->assertTrue($collections->contains(fn ($collection): bool => $collection->id === $parentSectionId));
        $this->assertFalse($collections->contains(fn ($collection): bool => $collection->id === $childSectionId));
        $this->assertTrue($childCollections->contains(fn ($collection): bool => $collection->id === $childSectionId));
    }

    public function test_board_can_create_series_task_from_new_library_folder_and_sync_pathway_items(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $sectionId = $this->createLibrarySection($teacher, $context, 'Series Folder', 1);
        $firstResourceId = $this->createLibraryResource($teacher, $context, $sectionId, 'First Unit', 'library/first.pdf', 1);
        $secondResourceId = $this->createLibraryResource($teacher, $context, $sectionId, 'Second Unit', 'library/second.pdf', 2);

        $this->actingAs($teacher);

        Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->assertSet('seriesReleasePolicyEnabled', true)
            ->set('draftTask.title', 'New Library Series')
            ->set('draftTask.collection_key', SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION.":{$sectionId}")
            ->set('draftTask.release_policy', 'wait_for_completion')
            ->assertSet('draftTask.release_policy', 'wait_for_completion')
            ->call('createTask')
            ->assertHasNoErrors();

        $task = SeriesTask::query()->where('title', 'New Library Series')->firstOrFail();
        $version = $task->versions()->firstOrFail();

        Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->call('syncVersionItems', $version->id)
            ->assertHasNoErrors();

        $this->assertSame(SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION, $task->library_collection_type);
        $this->assertSame($sectionId, (int) $task->library_collection_id);
        $this->assertSame('wait_for_completion', $task->release_policy);
        $this->assertDatabaseHas('series_task_version_items', [
            'version_id' => $version->id,
            'library_source_type' => 'library_resource',
            'library_source_id' => $firstResourceId,
            'sequence_position' => 1,
        ]);
        $this->assertDatabaseHas('series_task_version_items', [
            'version_id' => $version->id,
            'library_source_type' => 'library_resource',
            'library_source_id' => $secondResourceId,
            'sequence_position' => 2,
        ]);
    }

    public function test_publisher_generates_snapshot_from_new_library_resource_source(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 08:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Rami', 'Cole', 'Sara');
            [$task, $version, $item, $resourceId] = $this->createAssignableLibrarySeriesTask($teacher, $context, active: true);

            app(SeriesTaskAssignmentService::class)->assign(
                $student['student_id'],
                (int) $task->id,
                (int) $version->id,
                1,
                (int) $teacher->id,
                (int) $context['subject_id']
            );

            app(SeriesTaskPublisher::class)->generateForStudent(
                $student['student_id'],
                Carbon::parse('2026-05-04')->startOfDay()
            );

            $this->assertDatabaseHas('session_tasks', [
                'source_series_task_id_snapshot' => $task->id,
                'source_series_task_version_id_snapshot' => $version->id,
                'source_series_task_version_item_id_snapshot' => $item->id,
                'source_series_library_type_snapshot' => 'library_resource',
                'source_series_library_id_snapshot' => $resourceId,
            ]);
            $this->assertDatabaseHas('attachment_files', [
                'title' => 'Library PDF',
                'type' => 'file',
                'path' => 'library/series-pdf.pdf',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_assignment_service_writes_versioned_assignment_state_and_history(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 08:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');
            [$task, $version] = $this->createAssignableSeriesTask($teacher, $context);

            app(SeriesTaskAssignmentService::class)->assign(
                $student['student_id'],
                (int) $task->id,
                (int) $version->id,
                1,
                (int) $teacher->id,
                (int) $context['subject_id']
            );

            $this->assertDatabaseHas('series_task_student_assignments', [
                'student_id' => $student['student_id'],
                'series_task_id' => $task->id,
                'version_id' => $version->id,
                'start_sequence_position' => 1,
            ]);

            $assignment = SeriesTaskStudentAssignment::query()
                ->where('student_id', $student['student_id'])
                ->where('series_task_id', $task->id)
                ->firstOrFail();

            $this->assertSame('2026-05-04', $assignment->effective_from_date->toDateString());
            $this->assertNull($assignment->effective_to_date);

            $this->assertDatabaseHas('series_task_student_generation_states', [
                'student_id' => $student['student_id'],
                'series_task_id' => $task->id,
                'current_version_id' => $version->id,
                'next_sequence_position' => 1,
                'is_active' => 1,
            ]);

            $this->assertDatabaseHas('series_task_student_assignment_history', [
                'student_id' => $student['student_id'],
                'series_task_id' => $task->id,
                'event_type' => 'assign',
                'to_version_id' => $version->id,
                'to_sequence_position' => 1,
                'actor_user_id' => $teacher->id,
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_version_assigned_student_count_uses_current_effective_assignments(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 10:00:00', config('app.timezone')));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $currentStudent = $this->enrollStudent($context, 'Mira', 'Stone', 'Parent One');
            $futureStudent = $this->enrollStudent($context, 'Nour', 'Lake', 'Parent Two');
            $endedStudent = $this->enrollStudent($context, 'Omar', 'Vale', 'Parent Three');
            [$task, $version] = $this->createAssignableSeriesTask($teacher, $context);

            SeriesTaskStudentAssignment::create([
                'student_id' => $currentStudent['student_id'],
                'series_task_id' => $task->id,
                'version_id' => $version->id,
                'start_sequence_position' => 1,
                'effective_from_date' => '2026-05-01',
                'effective_to_date' => '2026-05-05',
                'assigned_by_user_id' => $teacher->id,
            ]);

            SeriesTaskStudentAssignment::create([
                'student_id' => $futureStudent['student_id'],
                'series_task_id' => $task->id,
                'version_id' => $version->id,
                'start_sequence_position' => 1,
                'effective_from_date' => '2026-05-05',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            SeriesTaskStudentAssignment::create([
                'student_id' => $endedStudent['student_id'],
                'series_task_id' => $task->id,
                'version_id' => $version->id,
                'start_sequence_position' => 1,
                'effective_from_date' => '2026-05-01',
                'effective_to_date' => '2026-05-03',
                'assigned_by_user_id' => $teacher->id,
            ]);

            $this->assertSame(1, $version->fresh()->assignedStudentCount());
            $this->assertSame(1, $version->fresh()->load('studentAssignments')->assignedStudentCount());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_board_assignment_counts_use_current_effective_assignments(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 10:00:00', config('app.timezone')));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $currentStudent = $this->enrollStudent($context, 'Mira', 'Stone', 'Parent One');
            $futureStudent = $this->enrollStudent($context, 'Nour', 'Lake', 'Parent Two');
            [$task, $version] = $this->createAssignableSeriesTask($teacher, $context);

            SeriesTaskStudentAssignment::create([
                'student_id' => $currentStudent['student_id'],
                'series_task_id' => $task->id,
                'version_id' => $version->id,
                'start_sequence_position' => 1,
                'effective_from_date' => '2026-05-01',
                'effective_to_date' => '2026-05-05',
                'assigned_by_user_id' => $teacher->id,
            ]);
            SeriesTaskStudentAssignment::create([
                'student_id' => $futureStudent['student_id'],
                'series_task_id' => $task->id,
                'version_id' => $version->id,
                'start_sequence_position' => 1,
                'effective_from_date' => '2026-05-05',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            $this->actingAs($teacher);

            Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']])
                ->assertSee('1 assigned')
                ->assertDontSee('2 assigned');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_board_shows_teacher_friendly_source_labels(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $storyId = DB::table('stories')->insertGetId([
            'title' => 'Classic Reader',
            'description' => null,
            'sort' => 1,
            'active' => 1,
        ]);

        $this->createAssignableSeriesTask($teacher, $context);
        $this->createAssignableLibrarySeriesTask($teacher, $context);
        SeriesTask::create([
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'task_type_id' => 1,
            'title' => 'Literature task',
            'description' => null,
            'library_collection_type' => SeriesLibrarySourceResolver::TYPE_STORY,
            'library_collection_id' => $storyId,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'sequence_behavior' => 'stop_at_end',
            'release_policy' => 'continuous',
            'default_points' => 5,
            'max_points' => 10,
            'status' => 'draft',
        ]);
        SeriesTask::create([
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'task_type_id' => 1,
            'title' => 'Level Up task',
            'description' => null,
            'library_collection_type' => SeriesLibrarySourceResolver::TYPE_LEVEL_UP,
            'library_collection_id' => null,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'sequence_behavior' => 'stop_at_end',
            'release_policy' => 'continuous',
            'default_points' => 5,
            'max_points' => 10,
            'status' => 'draft',
        ]);

        $this->actingAs($teacher);

        Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->assertSee('SAT')
            ->assertSee('Literature')
            ->assertSee('Level Up')
            ->assertSee('Series Library Folder');
    }

    public function test_publisher_generates_idempotent_snapshot_and_completes_stop_at_end_state(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 08:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Lina', 'Hart', 'Nora');
            [$task, $version, $item] = $this->createAssignableSeriesTask($teacher, $context, active: true);

            app(SeriesTaskAssignmentService::class)->assign(
                $student['student_id'],
                (int) $task->id,
                (int) $version->id,
                1,
                (int) $teacher->id,
                (int) $context['subject_id']
            );

            $publisher = app(SeriesTaskPublisher::class);
            $publisher->generateForStudent($student['student_id'], Carbon::parse('2026-05-04')->startOfDay());
            $publisher->generateForStudent($student['student_id'], Carbon::parse('2026-05-04')->startOfDay());

            $this->assertDatabaseCount('class_sessions', 1);
            $this->assertDatabaseHas('class_sessions', [
                'student_id' => $student['student_id'],
                'series_task_id' => $task->id,
                'generated_for_date' => '2026-05-04',
            ]);
            $this->assertDatabaseHas('session_tasks', [
                'source_series_task_id_snapshot' => $task->id,
                'source_series_task_version_id_snapshot' => $version->id,
                'source_series_task_version_item_id_snapshot' => $item->id,
                'source_series_library_type_snapshot' => 'sat',
                'source_series_library_id_snapshot' => $item->library_source_id,
            ]);
            $this->assertDatabaseHas('session_task_student', [
                'student_id' => $student['student_id'],
                'status' => 'assigned',
            ]);

            $state = SeriesTaskStudentGenerationState::query()
                ->where('student_id', $student['student_id'])
                ->where('series_task_id', $task->id)
                ->firstOrFail();

            $this->assertSame('2026-05-04', $state->last_generated_date->toDateString());
            $this->assertNotNull($state->completed_at);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_series_continuous_release_posts_next_item_even_when_previous_is_assigned(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 08:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Lina', 'Flow', 'Nora');
            [$task, $version] = $this->createAssignableSeriesTask($teacher, $context, active: true, releasePolicy: 'continuous', itemCount: 2);

            app(SeriesTaskAssignmentService::class)->assign(
                $student['student_id'],
                (int) $task->id,
                (int) $version->id,
                1,
                (int) $teacher->id,
                (int) $context['subject_id']
            );

            $publisher = app(SeriesTaskPublisher::class);
            $publisher->generateForStudent($student['student_id'], Carbon::parse('2026-05-04')->startOfDay());
            $publisher->generateForStudent($student['student_id'], Carbon::parse('2026-05-05')->startOfDay());

            $this->assertSame(2, DB::table('class_sessions')->where('series_task_id', $task->id)->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_series_wait_for_completion_allows_first_item_without_previous_task(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 08:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Lina', 'First', 'Nora');
            [$task, $version] = $this->createAssignableSeriesTask($teacher, $context, active: true, releasePolicy: 'wait_for_completion', itemCount: 2);

            app(SeriesTaskAssignmentService::class)->assign(
                $student['student_id'],
                (int) $task->id,
                (int) $version->id,
                1,
                (int) $teacher->id,
                (int) $context['subject_id']
            );

            app(SeriesTaskPublisher::class)->generateForStudent($student['student_id'], Carbon::parse('2026-05-04')->startOfDay());

            $this->assertSame(1, DB::table('class_sessions')->where('series_task_id', $task->id)->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_series_wait_for_completion_blocks_next_item_while_previous_is_assigned(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 08:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Lina', 'Hold', 'Nora');
            [$task, $version] = $this->createAssignableSeriesTask($teacher, $context, active: true, releasePolicy: 'wait_for_completion', itemCount: 2);

            app(SeriesTaskAssignmentService::class)->assign(
                $student['student_id'],
                (int) $task->id,
                (int) $version->id,
                1,
                (int) $teacher->id,
                (int) $context['subject_id']
            );

            $publisher = app(SeriesTaskPublisher::class);
            $publisher->generateForStudent($student['student_id'], Carbon::parse('2026-05-04')->startOfDay());
            $publisher->generateForStudent($student['student_id'], Carbon::parse('2026-05-05')->startOfDay());

            $state = SeriesTaskStudentGenerationState::query()
                ->where('student_id', $student['student_id'])
                ->where('series_task_id', $task->id)
                ->firstOrFail();

            $this->assertSame(1, DB::table('class_sessions')->where('series_task_id', $task->id)->count());
            $this->assertSame(2, (int) $state->next_sequence_position);
            $this->assertSame('2026-05-04', $state->last_generated_date->toDateString());
            $this->assertNull($state->completed_at);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_series_wait_for_completion_blocks_next_item_while_previous_is_in_review(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 08:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Lina', 'Review', 'Nora');
            [$task, $version] = $this->createAssignableSeriesTask($teacher, $context, active: true, releasePolicy: 'wait_for_completion', itemCount: 2);

            app(SeriesTaskAssignmentService::class)->assign(
                $student['student_id'],
                (int) $task->id,
                (int) $version->id,
                1,
                (int) $teacher->id,
                (int) $context['subject_id']
            );

            $publisher = app(SeriesTaskPublisher::class);
            $publisher->generateForStudent($student['student_id'], Carbon::parse('2026-05-04')->startOfDay());
            DB::table('session_task_student')
                ->where('student_id', $student['student_id'])
                ->update(['status' => 'in_review']);

            $publisher->generateForStudent($student['student_id'], Carbon::parse('2026-05-05')->startOfDay());

            $this->assertSame(1, DB::table('class_sessions')->where('series_task_id', $task->id)->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_series_wait_for_completion_posts_after_previous_task_is_completed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 08:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Lina', 'Done', 'Nora');
            [$task, $version] = $this->createAssignableSeriesTask($teacher, $context, active: true, releasePolicy: 'wait_for_completion', itemCount: 2);

            app(SeriesTaskAssignmentService::class)->assign(
                $student['student_id'],
                (int) $task->id,
                (int) $version->id,
                1,
                (int) $teacher->id,
                (int) $context['subject_id']
            );

            $publisher = app(SeriesTaskPublisher::class);
            $publisher->generateForStudent($student['student_id'], Carbon::parse('2026-05-04')->startOfDay());
            DB::table('session_task_student')
                ->where('student_id', $student['student_id'])
                ->update(['status' => 'completed']);

            $publisher->generateForStudent($student['student_id'], Carbon::parse('2026-05-05')->startOfDay());

            $this->assertSame(2, DB::table('class_sessions')->where('series_task_id', $task->id)->count());
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_publisher_pauses_blocked_generation_without_advancing_sequence(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 08:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Mina', 'Reed', 'Sara');
            [$task, $version, $item] = $this->createAssignableSeriesTask($teacher, $context, active: true);

            app(SeriesTaskAssignmentService::class)->assign(
                $student['student_id'],
                (int) $task->id,
                (int) $version->id,
                1,
                (int) $teacher->id,
                (int) $context['subject_id']
            );

            $item->update(['is_active' => 0]);

            $publisher = app(SeriesTaskPublisher::class);
            $publisher->generateForStudent($student['student_id'], Carbon::parse('2026-05-04')->startOfDay());
            $publisher->generateForStudent($student['student_id'], Carbon::parse('2026-05-04')->startOfDay());

            $this->assertDatabaseCount('class_sessions', 0);

            $state = SeriesTaskStudentGenerationState::query()
                ->where('student_id', $student['student_id'])
                ->where('series_task_id', $task->id)
                ->firstOrFail();

            $this->assertSame(1, (int) $state->next_sequence_position);
            $this->assertNull($state->last_generated_date);
            $this->assertSame('2026-05-04', $state->paused_through_date->toDateString());
            $this->assertNull($state->completed_at);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_publisher_does_not_advance_sequence_when_snapshot_context_is_missing(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 08:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Yara', 'Cole', 'Dina');
            [$task, $version] = $this->createAssignableSeriesTask($teacher, $context, active: true);

            app(SeriesTaskAssignmentService::class)->assign(
                $student['student_id'],
                (int) $task->id,
                (int) $version->id,
                1,
                (int) $teacher->id,
                (int) $context['subject_id']
            );

            $newClassId = DB::table('classes')->insertGetId([
                'title' => 'Moved class',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('students')
                ->where('id', $student['student_id'])
                ->update(['current_class_id' => $newClassId]);

            app(SeriesTaskPublisher::class)->generateForStudent(
                $student['student_id'],
                Carbon::parse('2026-05-04')->startOfDay()
            );

            $this->assertDatabaseCount('class_sessions', 0);

            $state = SeriesTaskStudentGenerationState::query()
                ->where('student_id', $student['student_id'])
                ->where('series_task_id', $task->id)
                ->firstOrFail();

            $this->assertSame(1, (int) $state->next_sequence_position);
            $this->assertNull($state->last_generated_date);
            $this->assertSame('2026-05-04', $state->paused_through_date->toDateString());
            $this->assertNull($state->completed_at);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_teacher_routes_are_limited_to_owned_subjects(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');
        $otherTeacher = User::factory()->create();
        $otherTeacher->assignRole('teacher');
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher)
            ->get(route('series-tasks.subjects', ['auth_role' => 'teacher']))
            ->assertOk()
            ->assertSee('Series Tasks');

        $this->actingAs($teacher)
            ->get(route('series-tasks.board', ['auth_role' => 'teacher', 'subject' => $context['subject_id']]))
            ->assertOk()
            ->assertSee('Series Tasks');

        $this->actingAs($otherTeacher)
            ->get(route('series-tasks.board', ['auth_role' => 'teacher', 'subject' => $context['subject_id']]))
            ->assertForbidden();
    }

    public function test_board_rejects_forged_item_from_another_library_source(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        [$task, $version] = $this->createAssignableSeriesTask($teacher, $context);
        $storyId = DB::table('stories')->insertGetId([
            'title' => 'Story source',
            'description' => null,
            'sort' => 1,
            'active' => 1,
        ]);
        $chapterId = DB::table('story_chapters')->insertGetId([
            'story_id' => $storyId,
            'title' => 'Wrong source chapter',
            'slug' => 'story/wrong-source-chapter',
            'sort' => 1,
        ]);

        $this->actingAs($teacher);

        Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->set("itemSelections.{$version->id}", "story_chapter:{$chapterId}")
            ->call('addItemToVersion', $version->id)
            ->assertHasErrors(['item']);

        $this->assertDatabaseMissing('series_task_version_items', [
            'version_id' => $version->id,
            'library_source_type' => 'story_chapter',
            'library_source_id' => $chapterId,
        ]);
    }

    public function test_board_batch_adds_multiple_library_items_without_duplicates(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        [$task, $version, $existingItem] = $this->createAssignableSeriesTask($teacher, $context);
        $secondSatChildId = DB::table('sat')->insertGetId([
            'parent_id' => $task->library_collection_id,
            'title' => 'Evidence Drill',
            'slug' => 'evidence-drill',
            'sort' => 2,
        ]);

        $this->actingAs($teacher);

        Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->set("itemSelections.{$version->id}", [
                "sat:{$existingItem->library_source_id}" => true,
                "sat:{$secondSatChildId}" => true,
            ])
            ->call('addItemsToVersion', $version->id)
            ->assertHasNoErrors();

        $this->assertDatabaseCount('series_task_version_items', 2);
        $this->assertDatabaseHas('series_task_version_items', [
            'version_id' => $version->id,
            'library_source_type' => 'sat',
            'library_source_id' => $secondSatChildId,
            'sequence_position' => 2,
        ]);
    }

    public function test_board_can_clear_all_selected_library_items_without_reselecting_defaults(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        [$task, $version, $item, $resourceId] = $this->createAssignableLibrarySeriesTask($teacher, $context);
        $itemKey = 'library_resource:'.$resourceId;

        $this->actingAs($teacher);

        Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->set("itemSelections.{$version->id}", [
                $itemKey => false,
            ])
            ->call('syncVersionItems', $version->id)
            ->assertHasNoErrors()
            ->assertSet("itemSelections.{$version->id}.{$itemKey}", false);

        $this->assertDatabaseMissing('series_task_version_items', [
            'id' => $item->id,
        ]);
        $result = app(\App\Services\SeriesTaskPublishValidator::class)->validate($task->fresh(['versions.items']));

        $this->assertTrue($result->fails());
        $this->assertContains('Add at least one active Library item to a pathway before activation.', $result->errors);
    }

    public function test_publish_validator_ignores_unassigned_preparation_pathways_without_items(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        [$task] = $this->createAssignableLibrarySeriesTask($teacher, $context);

        SeriesTaskVersion::create([
            'series_task_id' => $task->id,
            'display_name' => 'Unused pathway',
            'sort_order' => 2,
        ]);

        $result = app(\App\Services\SeriesTaskPublishValidator::class)->validate($task->fresh(['versions.items', 'versions.studentAssignments']));

        $this->assertFalse($result->fails());
    }

    public function test_board_adds_new_pathway_at_top_of_series(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        [$task] = $this->createAssignableSeriesTask($teacher, $context);

        SeriesTaskVersion::create([
            'series_task_id' => $task->id,
            'display_name' => 'Pathway B',
            'sort_order' => 2,
        ]);

        $this->actingAs($teacher);

        Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->call('addVersion', $task->id)
            ->assertHasNoErrors();

        $orderedNames = $task->fresh('versions')->versions->pluck('display_name')->all();

        $this->assertSame(['Pathway 3', 'Pathway A', 'Pathway B'], $orderedNames);
        $this->assertDatabaseHas('series_task_versions', [
            'series_task_id' => $task->id,
            'display_name' => 'Pathway 3',
            'sort_order' => 1,
        ]);
        $this->assertDatabaseHas('series_task_versions', [
            'series_task_id' => $task->id,
            'display_name' => 'Pathway A',
            'sort_order' => 2,
        ]);
    }

    public function test_board_deletes_unassigned_pathway_and_renumbers_remaining_pathways(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        [$task, $versionA] = $this->createAssignableSeriesTask($teacher, $context);
        $versionB = SeriesTaskVersion::create([
            'series_task_id' => $task->id,
            'display_name' => 'Pathway B',
            'sort_order' => 2,
        ]);

        $this->actingAs($teacher);

        Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->call('deleteVersion', $versionA->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('series_task_versions', [
            'id' => $versionA->id,
        ]);
        $this->assertDatabaseHas('series_task_versions', [
            'id' => $versionB->id,
            'sort_order' => 1,
        ]);
    }

    public function test_board_deletes_assigned_pathway_and_stops_future_generation(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 08:00:00'));

        try {
            $teacher = User::factory()->create();
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Nadia', 'Ray', 'Mona');
            [$task, $version] = $this->createAssignableSeriesTask($teacher, $context, active: true);

            app(SeriesTaskAssignmentService::class)->assign(
                $student['student_id'],
                (int) $task->id,
                (int) $version->id,
                1,
                (int) $teacher->id,
                (int) $context['subject_id']
            );

            $this->actingAs($teacher);

            Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']])
                ->call('deleteVersion', $version->id)
                ->assertHasNoErrors()
                ->assertSet('boardFeedback.message', 'Pathway deleted. Assigned students will stop receiving future tasks from it.');

            $this->assertDatabaseMissing('series_task_versions', [
                'id' => $version->id,
            ]);
            $this->assertDatabaseMissing('series_task_student_assignments', [
                'student_id' => $student['student_id'],
                'series_task_id' => $task->id,
                'version_id' => $version->id,
            ]);
            $this->assertDatabaseHas('series_task_student_generation_states', [
                'student_id' => $student['student_id'],
                'series_task_id' => $task->id,
                'current_version_id' => null,
                'is_active' => 0,
            ]);
            $this->assertDatabaseHas('series_task_student_assignment_history', [
                'student_id' => $student['student_id'],
                'series_task_id' => $task->id,
                'event_type' => 'version_deleted',
                'from_version_id' => $version->id,
                'from_version_display_name' => 'Pathway A',
                'actor_user_id' => $teacher->id,
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_publish_validator_rejects_existing_item_outside_selected_collection(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        [$task, $version] = $this->createAssignableSeriesTask($teacher, $context);
        $storyId = DB::table('stories')->insertGetId([
            'title' => 'Story source',
            'description' => null,
            'sort' => 1,
            'active' => 1,
        ]);
        $chapterId = DB::table('story_chapters')->insertGetId([
            'story_id' => $storyId,
            'title' => 'Wrong source chapter',
            'slug' => 'story/wrong-source-chapter',
            'sort' => 1,
        ]);

        SeriesTaskVersionItem::create([
            'version_id' => $version->id,
            'library_source_type' => 'story_chapter',
            'library_source_id' => $chapterId,
            'library_title_snapshot' => 'Wrong source chapter',
            'library_url_snapshot' => url('reading/listen-read/story/wrong-source-chapter'),
            'sequence_position' => 2,
            'is_active' => 1,
        ]);

        $result = app(\App\Services\SeriesTaskPublishValidator::class)->validate($task->fresh(['versions.items']));

        $this->assertTrue($result->fails());
        $this->assertContains(
            'Library item Wrong source chapter does not belong to the selected Library source.',
            $result->errors
        );
    }

    public function test_active_task_source_cannot_be_changed_by_tampered_livewire_state(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        [$task] = $this->createAssignableSeriesTask($teacher, $context, active: true);
        $storyId = DB::table('stories')->insertGetId([
            'title' => 'Story source',
            'description' => null,
            'sort' => 1,
            'active' => 1,
        ]);

        $this->actingAs($teacher);

        Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->set("taskForms.{$task->id}.title", 'Updated active title')
            ->set("taskForms.{$task->id}.collection_key", "story:{$storyId}")
            ->call('saveTask', $task->id)
            ->assertHasNoErrors();

        $task->refresh();

        $this->assertSame('Updated active title', $task->title);
        $this->assertSame(SeriesLibrarySourceResolver::TYPE_SAT, $task->library_collection_type);
    }

    public function test_series_source_picker_can_open_type_folder_go_back_and_clear_selection(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $satParentId = DB::table('sat')->insertGetId([
            'title' => 'SAT Reading',
            'slug' => 'sat-reading',
            'sort' => 1,
        ]);
        DB::table('sat')->insert([
            'parent_id' => $satParentId,
            'title' => 'Inference Drill',
            'slug' => 'inference-drill',
            'sort' => 1,
        ]);

        $this->actingAs($teacher);

        Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->call('openCollectionPicker', 'draft')
            ->call('enterLegacyCollectionTypes')
            ->assertSet('collectionPickerType', '__legacy')
            ->call('enterCollectionType', SeriesLibrarySourceResolver::TYPE_SAT)
            ->assertSet('collectionPickerType', SeriesLibrarySourceResolver::TYPE_SAT)
            ->call('goToCollectionTypes')
            ->assertSet('collectionPickerType', '__legacy')
            ->call('enterCollectionType', SeriesLibrarySourceResolver::TYPE_SAT)
            ->call('chooseCollection', SeriesLibrarySourceResolver::TYPE_SAT.":{$satParentId}")
            ->assertSet('draftTask.collection_key', SeriesLibrarySourceResolver::TYPE_SAT.":{$satParentId}")
            ->call('openCollectionPicker', 'draft')
            ->call('clearCollectionSelection')
            ->assertSet('draftTask.collection_key', '')
            ->assertSet('collectionPickerTarget', null);
    }

    public function test_series_source_picker_refreshes_new_library_folders_when_opened(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $this->actingAs($teacher);

        $component = Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']]);
        $this->assertFalse(collect($component->get('collections'))->contains(
            fn (array $collection): bool => $collection['title'] === 'Late Folder'
        ));

        $sectionId = $this->createLibrarySection($teacher, $context, 'Late Folder', 1);
        $this->createLibraryResource($teacher, $context, $sectionId, 'Late PDF', 'library/late.pdf', 1);

        $component->call('openCollectionPicker', 'draft');
        $collections = collect($component->get('collections'));

        $this->assertTrue($collections->contains(
            fn (array $collection): bool => $collection['title'] === 'Late Folder'
                && $collection['type_label'] === 'My Library folders'
        ));
    }

    public function test_series_source_picker_browses_new_library_folders_before_selection(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $parentSectionId = $this->createLibrarySection($teacher, $context, 'Course Folder', 1);
        $childSectionId = $this->createLibrarySection($teacher, $context, 'Unit Folder', 1, $parentSectionId);
        $this->createLibraryResource($teacher, $context, $childSectionId, 'Unit PDF', 'library/unit.pdf', 1);

        $this->actingAs($teacher);

        Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->call('openCollectionPicker', 'draft')
            ->call('enterLibrarySection', $parentSectionId)
            ->assertSet('collectionPickerType', SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION)
            ->assertSet('libraryPickerSectionId', $parentSectionId)
            ->assertSet('collections.0.title', 'Unit Folder')
            ->call('enterLibrarySection', $childSectionId)
            ->assertSet('libraryPickerSectionId', $childSectionId)
            ->call('chooseCollection', SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION.":{$childSectionId}")
            ->assertSet('draftTask.collection_key', SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION.":{$childSectionId}");
    }

    public function test_board_rejects_default_points_greater_than_max_points(): void
    {
        $teacher = User::factory()->create();
        $context = $this->createTeacherSubjectContext($teacher);
        $satParentId = DB::table('sat')->insertGetId([
            'title' => 'SAT Reading',
            'slug' => 'sat-reading',
            'sort' => 1,
        ]);

        $this->actingAs($teacher);

        Livewire::test(SeriesTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->set('draftTask.title', 'Invalid points')
            ->set('draftTask.collection_key', SeriesLibrarySourceResolver::TYPE_SAT.":{$satParentId}")
            ->set('draftTask.default_points', 20)
            ->set('draftTask.max_points', 5)
            ->call('createTask')
            ->assertHasErrors(['default_points']);

        $this->assertDatabaseMissing('series_tasks', [
            'title' => 'Invalid points',
        ]);
    }

    private function createAssignableSeriesTask(
        User $teacher,
        array $context,
        bool $active = false,
        string $releasePolicy = 'continuous',
        int $itemCount = 1
    ): array
    {
        $satParentId = DB::table('sat')->insertGetId([
            'title' => 'SAT Reading',
            'slug' => 'sat-reading',
            'sort' => 1,
        ]);
        $satChildId = DB::table('sat')->insertGetId([
            'parent_id' => $satParentId,
            'title' => 'Inference Drill',
            'slug' => 'inference-drill',
            'sort' => 1,
        ]);
        $secondSatChildId = null;

        if ($itemCount > 1) {
            $secondSatChildId = DB::table('sat')->insertGetId([
                'parent_id' => $satParentId,
                'title' => 'Evidence Drill',
                'slug' => 'evidence-drill',
                'sort' => 2,
            ]);
        }

        $task = SeriesTask::create([
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'task_type_id' => 1,
            'title' => 'SAT Path',
            'description' => 'Practice in order.',
            'library_collection_type' => SeriesLibrarySourceResolver::TYPE_SAT,
            'library_collection_id' => $satParentId,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'sequence_behavior' => 'stop_at_end',
            'release_policy' => $releasePolicy,
            'default_points' => 5,
            'max_points' => 10,
            'status' => $active ? 'active' : 'draft',
            'published_at' => $active ? Carbon::parse('2026-05-04 00:00:00') : null,
        ]);

        $version = SeriesTaskVersion::create([
            'series_task_id' => $task->id,
            'display_name' => 'Pathway A',
            'sort_order' => 1,
        ]);

        $item = SeriesTaskVersionItem::create([
            'version_id' => $version->id,
            'library_source_type' => 'sat',
            'library_source_id' => $satChildId,
            'library_title_snapshot' => 'Inference Drill',
            'library_url_snapshot' => url('course/sat/sat-reading/inference-drill'),
            'sequence_position' => 1,
            'is_active' => 1,
        ]);

        if ($secondSatChildId !== null) {
            SeriesTaskVersionItem::create([
                'version_id' => $version->id,
                'library_source_type' => 'sat',
                'library_source_id' => $secondSatChildId,
                'library_title_snapshot' => 'Evidence Drill',
                'library_url_snapshot' => url('course/sat/sat-reading/evidence-drill'),
                'sequence_position' => 2,
                'is_active' => 1,
            ]);
        }

        return [$task, $version, $item];
    }

    private function createAssignableLibrarySeriesTask(User $teacher, array $context, bool $active = false): array
    {
        $sectionId = $this->createLibrarySection($teacher, $context, 'Series Library Folder', 1);
        $resourceId = $this->createLibraryResource($teacher, $context, $sectionId, 'Library PDF', 'library/series-pdf.pdf', 1);

        $task = SeriesTask::create([
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'task_type_id' => 1,
            'title' => 'Library Series',
            'description' => 'New Library resources in order.',
            'library_collection_type' => SeriesLibrarySourceResolver::TYPE_LIBRARY_SECTION,
            'library_collection_id' => $sectionId,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'sequence_behavior' => 'stop_at_end',
            'release_policy' => 'continuous',
            'default_points' => 5,
            'max_points' => 10,
            'status' => $active ? 'active' : 'draft',
            'published_at' => $active ? Carbon::parse('2026-05-04 00:00:00') : null,
        ]);

        $version = SeriesTaskVersion::create([
            'series_task_id' => $task->id,
            'display_name' => 'Pathway A',
            'sort_order' => 1,
        ]);

        $item = SeriesTaskVersionItem::create([
            'version_id' => $version->id,
            'library_source_type' => 'library_resource',
            'library_source_id' => $resourceId,
            'library_title_snapshot' => 'Library PDF',
            'library_url_snapshot' => null,
            'library_summary_snapshot' => 'Series source file.',
            'sequence_position' => 1,
            'is_active' => 1,
        ]);

        return [$task, $version, $item, $resourceId];
    }

    private function createLibrarySection(User $teacher, array $context, string $title, int $sortOrder, ?int $parentId = null): int
    {
        return (int) DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => $context['subject_id'],
            'parent_id' => $parentId,
            'title' => $title,
            'description' => null,
            'status' => 'active',
            'sort_order' => $sortOrder,
            'created_by_user_id' => $teacher->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createLibraryResource(
        User $teacher,
        array $context,
        int $sectionId,
        string $title,
        string $path,
        int $sortOrder
    ): int {
        return (int) DB::table('library_resources')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => $context['subject_id'],
            'library_section_id' => $sectionId,
            'resource_type' => 'file',
            'title' => $title,
            'description' => 'Series source file.',
            'status' => 'active',
            'storage_disk' => 'public',
            'file_path' => $path,
            'original_filename' => basename($path),
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
            'external_url' => null,
            'sort_order' => $sortOrder,
            'created_by_user_id' => $teacher->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
