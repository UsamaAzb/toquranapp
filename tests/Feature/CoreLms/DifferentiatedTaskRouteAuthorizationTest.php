<?php

namespace Tests\Feature\CoreLms;

use App\Livewire\Teacher\DifferentiatedTaskAssignmentModal;
use App\Livewire\Teacher\DifferentiatedTasksBoard;
use App\Models\DifferentiatedTask;
use App\Models\DifferentiatedTaskAttachment;
use App\Models\DifferentiatedTaskStudentAssignment;
use App\Models\DifferentiatedTaskStudentGenerationState;
use App\Models\DifferentiatedTaskVersion;
use App\Models\User;
use App\Services\DifferentiatedTaskPublishValidator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

class DifferentiatedTaskRouteAuthorizationTest extends TestCase
{
    use CreatesAutomatedTaskTestingSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['admin', 'teacher', 'student', 'parent'] as $role) {
            Role::findOrCreate($role);
        }

        $this->createDifferentiatedTaskSchema();
        $this->seedTaskTypes();
    }

    public function test_teacher_can_open_differentiated_task_subject_and_board_routes(): void
    {
        $teacher = $this->userWithRole('teacher');
        $context = $this->createTeacherSubjectContext($teacher);

        $this->actingAs($teacher)
            ->get(route('differentiated-tasks.get_subjects', ['auth_role' => 'teacher']))
            ->assertOk()
            ->assertSee('Differentiated Tasks')
            ->assertSee('English');

        $this->actingAs($teacher)
            ->get(route('differentiated-tasks.get_tasks', [
                'auth_role' => 'teacher',
                'subject' => $context['subject_id'],
            ]))
            ->assertOk()
            ->assertSee('English')
            ->assertSee('Differentiated Tasks');
    }

    public function test_non_teacher_roles_cannot_open_differentiated_task_teacher_routes(): void
    {
        foreach (['admin', 'student', 'parent'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get(route('differentiated-tasks.get_subjects', ['auth_role' => 'teacher']))
                ->assertForbidden();
        }
    }

    public function test_guest_is_redirected_from_differentiated_task_teacher_routes(): void
    {
        $this->get(route('differentiated-tasks.get_subjects', ['auth_role' => 'teacher']))
            ->assertRedirect(route('login'));
    }

    public function test_teacher_with_same_subject_cannot_manage_another_teachers_task(): void
    {
        $owner = $this->userWithRole('teacher');
        $otherTeacher = $this->userWithRole('teacher');
        $context = $this->createTeacherSubjectContext($owner);
        $this->grantSameSubjectAccess($otherTeacher, $context);
        [$task, $version] = $this->createReadyDifferentiatedTask($owner, $context);

        $this->actingAs($otherTeacher);

        Livewire::test(DifferentiatedTasksBoard::class, ['subjectId' => $context['subject_id']])
            ->assertDontSee($task->title);

        $rejectedActions = 0;

        foreach ([
            fn () => Livewire::test(DifferentiatedTasksBoard::class, ['subjectId' => $context['subject_id']])
                ->call('saveTask', $task->id),
            fn () => Livewire::test(DifferentiatedTasksBoard::class, ['subjectId' => $context['subject_id']])
                ->call('publishTask', $task->id, app(DifferentiatedTaskPublishValidator::class)),
            fn () => Livewire::test(DifferentiatedTaskAssignmentModal::class)
                ->call('open', $task->id, $version->id),
        ] as $action) {
            try {
                $action();
            } catch (ModelNotFoundException) {
                $rejectedActions++;
            }
        }

        $this->assertSame(3, $rejectedActions);
    }

    public function test_task_pool_attachment_routes_require_task_creator_ownership(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('dt-pool/sample.txt', 'sample content');

        $owner = $this->userWithRole('teacher');
        $otherTeacher = $this->userWithRole('teacher');
        $context = $this->createTeacherSubjectContext($owner);
        $this->grantSameSubjectAccess($otherTeacher, $context);
        [$task] = $this->createReadyDifferentiatedTask($owner, $context);

        $attachment = DifferentiatedTaskAttachment::create([
            'differentiated_task_id' => $task->id,
            'type' => 'file',
            'title' => 'Sample',
            'path' => 'dt-pool/sample.txt',
            'file_size' => 14,
            'sort_order' => 1,
        ]);

        $showRoute = route('differentiated-tasks.attachment.show', [
            'task' => $task->id,
            'attachment' => $attachment->id,
        ]);
        $streamRoute = route('differentiated-tasks.attachment.file', [
            'task' => $task->id,
            'attachment' => $attachment->id,
        ]);

        $this->actingAs($owner)
            ->get($showRoute)
            ->assertOk()
            ->assertSee('Back to Differentiated Tasks');

        $this->actingAs($owner)
            ->get($streamRoute)
            ->assertOk();

        $this->actingAs($otherTeacher)
            ->get($showRoute)
            ->assertForbidden();

        $this->actingAs($otherTeacher)
            ->get($streamRoute)
            ->assertForbidden();
    }

    public function test_student_subject_sessions_route_generates_due_differentiated_task_rows(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-30 09:00:00', 'Africa/Cairo'));

        try {
            $teacher = $this->userWithRole('teacher');
            $studentUser = $this->userWithRole('student');
            $context = $this->createTeacherSubjectContext($teacher);
            $student = $this->enrollStudent($context, 'Ava', 'Stone', 'Mona');

            DB::table('students')
                ->where('id', $student['student_id'])
                ->update([
                    'user_id' => $studentUser->id,
                    'status' => 'trial',
                    'account_status' => 'active',
                ]);

            [$task, $version] = $this->createReadyDifferentiatedTask($teacher, $context);
            $task->update([
                'status' => 'active',
                'published_at' => Carbon::parse('2026-04-30 08:00:00', 'Africa/Cairo'),
            ]);

            $assignment = DifferentiatedTaskStudentAssignment::create([
                'student_id' => $student['student_id'],
                'differentiated_task_id' => $task->id,
                'version_id' => $version->id,
                'effective_from_date' => '2026-04-30',
                'effective_to_date' => null,
                'assigned_by_user_id' => $teacher->id,
            ]);

            DifferentiatedTaskStudentGenerationState::create([
                'student_id' => $student['student_id'],
                'differentiated_task_id' => $task->id,
                'is_active' => 1,
                'start_date' => '2026-04-30',
            ]);

            $studentSubjectId = DB::table('students_subjects')
                ->where('student_id', $student['student_id'])
                ->value('id');

            $this->actingAs($studentUser)
                ->get(url('student/classes/sessions/'.$studentSubjectId))
                ->assertOk();

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
        } finally {
            Carbon::setTestNow();
        }
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function grantSameSubjectAccess(User $teacher, array $context): void
    {
        DB::table('teacher_subject_classes')->insert([
            'user_teacher_coteacher_id' => $teacher->id,
            'class_subject_id' => $context['class_subject_id'],
            'class_id' => $context['class_id'],
            'subject_id' => $context['subject_id'],
            'grade_id' => 1,
            'class_name' => 'Grade 8',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createReadyDifferentiatedTask(User $teacher, array $context): array
    {
        $task = DifferentiatedTask::create([
            'title' => 'Owned Differentiated Task',
            'subject_id' => $context['subject_id'],
            'created_by_user_id' => $teacher->id,
            'task_type_id' => 1,
            'recurrence_kind' => 'daily',
            'recurrence_interval' => 1,
            'default_points' => 5,
            'max_points' => 10,
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
}
