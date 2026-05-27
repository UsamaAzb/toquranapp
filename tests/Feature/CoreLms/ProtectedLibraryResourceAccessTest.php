<?php

namespace Tests\Feature\CoreLms;

use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProtectedLibraryResourceAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequiredTables();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['teacher', 'student', 'parent'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_assigned_student_and_linked_parent_can_open_library_sourced_attachment(): void
    {
        $fixture = $this->createProtectedAttachmentFixture();

        $this->actingAs($fixture['student_user'])
            ->get(route('student.sessions.attachment.show', [
                'session' => $fixture['session_id'],
                'attachment' => $fixture['attachment_id'],
            ]))
            ->assertOk()
            ->assertSee('Protected Library PDF');

        $this->actingAs($fixture['parent_user'])
            ->get(route('student.sessions.attachment.show', [
                'session' => $fixture['session_id'],
                'attachment' => $fixture['attachment_id'],
                'student_id' => $fixture['student_id'],
            ]))
            ->assertOk()
            ->assertSee('Protected Library PDF');

        $this->actingAs($fixture['parent_user'])
            ->get(route('student.journey.attachment.show', [
                'session' => $fixture['session_id'],
                'attachment' => $fixture['attachment_id'],
                'student_id' => $fixture['student_id'],
            ]))
            ->assertOk()
            ->assertSee('Protected Library PDF');
    }

    public function test_unrelated_learners_and_teacher_cannot_open_student_attachment(): void
    {
        $fixture = $this->createProtectedAttachmentFixture();

        $this->actingAs($fixture['unrelated_student_user'])
            ->get(route('student.sessions.attachment.show', [
                'session' => $fixture['session_id'],
                'attachment' => $fixture['attachment_id'],
            ]))
            ->assertForbidden();

        $this->actingAs($fixture['unrelated_parent_user'])
            ->get(route('student.sessions.attachment.show', [
                'session' => $fixture['session_id'],
                'attachment' => $fixture['attachment_id'],
                'student_id' => $fixture['student_id'],
            ]))
            ->assertNotFound();

        $this->actingAs($fixture['teacher_user'])
            ->get(route('student.sessions.attachment.show', [
                'session' => $fixture['session_id'],
                'attachment' => $fixture['attachment_id'],
            ]))
            ->assertForbidden();
    }

    public function test_learner_attachment_page_uses_protected_route_not_raw_storage_url(): void
    {
        $fixture = $this->createProtectedAttachmentFixture();
        $protectedUrl = route('student.sessions.attachment.file', [
            'session' => $fixture['session_id'],
            'attachment' => $fixture['attachment_id'],
        ]);
        $journeyProtectedUrl = route('student.journey.attachment.file', [
            'session' => $fixture['session_id'],
            'attachment' => $fixture['attachment_id'],
        ]);

        $this->actingAs($fixture['student_user'])
            ->get(route('student.sessions.attachment.show', [
                'session' => $fixture['session_id'],
                'attachment' => $fixture['attachment_id'],
            ]))
            ->assertOk()
            ->assertSee($protectedUrl, false)
            ->assertDontSee('/storage/test-protected-library-resource/protected-library.pdf', false);

        $this->actingAs($fixture['student_user'])
            ->get($protectedUrl)
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->actingAs($fixture['student_user'])
            ->get(route('student.journey.attachment.show', [
                'session' => $fixture['session_id'],
                'attachment' => $fixture['attachment_id'],
            ]))
            ->assertOk()
            ->assertSee($journeyProtectedUrl, false)
            ->assertDontSee('/storage/test-protected-library-resource/protected-library.pdf', false);

        $this->actingAs($fixture['student_user'])
            ->get($journeyProtectedUrl)
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_session_and_journey_routes_share_task_assignment_authorization(): void
    {
        $fixture = $this->createProtectedAttachmentFixture();

        $this->actingAs($fixture['student_user'])
            ->get(route('student.sessions.attachment.show', [
                'session' => $fixture['session_id'],
                'attachment' => $fixture['attachment_id'],
            ]))
            ->assertOk();

        $this->actingAs($fixture['student_user'])
            ->get(route('student.journey.attachment.show', [
                'session' => $fixture['session_id'],
                'attachment' => $fixture['attachment_id'],
            ]))
            ->assertOk();

        $this->actingAs($fixture['same_subject_unassigned_student_user'])
            ->get(route('student.sessions.attachment.show', [
                'session' => $fixture['session_id'],
                'attachment' => $fixture['attachment_id'],
            ]))
            ->assertForbidden();

        $this->actingAs($fixture['same_subject_unassigned_student_user'])
            ->get(route('student.journey.attachment.show', [
                'session' => $fixture['session_id'],
                'attachment' => $fixture['attachment_id'],
            ]))
            ->assertForbidden();

        $this->actingAs($fixture['same_subject_unassigned_student_user'])
            ->get(route('student.sessions.attachment.file', [
                'session' => $fixture['session_id'],
                'attachment' => $fixture['attachment_id'],
            ]))
            ->assertForbidden();

        $this->actingAs($fixture['same_subject_unassigned_student_user'])
            ->get(route('student.journey.attachment.file', [
                'session' => $fixture['session_id'],
                'attachment' => $fixture['attachment_id'],
            ]))
            ->assertForbidden();
    }

    /**
     * @return array{
     *     teacher_user: User,
     *     student_user: User,
     *     parent_user: User,
     *     unrelated_student_user: User,
     *     unrelated_parent_user: User,
     *     same_subject_unassigned_student_user: User,
     *     student_id: int,
     *     session_id: int,
     *     attachment_id: int
     * }
     */
    private function createProtectedAttachmentFixture(): array
    {
        Storage::persistentFake('public');
        Storage::disk('public')->deleteDirectory('test-protected-library-resource');
        Storage::disk('public')->put('test-protected-library-resource/protected-library.pdf', '%PDF-1.4 protected library');

        $teacherUser = User::factory()->create();
        $studentUser = User::factory()->create();
        $parentUser = User::factory()->create();
        $unrelatedStudentUser = User::factory()->create();
        $unrelatedParentUser = User::factory()->create();
        $sameSubjectUnassignedStudentUser = User::factory()->create();

        $teacherUser->assignRole('teacher');
        $studentUser->assignRole('student');
        $parentUser->assignRole('parent');
        $unrelatedStudentUser->assignRole('student');
        $unrelatedParentUser->assignRole('parent');
        $sameSubjectUnassignedStudentUser->assignRole('student');

        $parent = ParentModel::create([
            'first_name' => 'Linked Parent',
            'user_id' => $parentUser->id,
            'active' => true,
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $unrelatedParent = ParentModel::create([
            'first_name' => 'Other Parent',
            'user_id' => $unrelatedParentUser->id,
            'active' => true,
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $student = Student::create([
            'first_name' => 'Assigned',
            'last_name' => 'Student',
            'parent_id' => $parent->id,
            'user_id' => $studentUser->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        Student::create([
            'first_name' => 'Unrelated',
            'last_name' => 'Student',
            'parent_id' => $unrelatedParent->id,
            'user_id' => $unrelatedStudentUser->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        $sameSubjectUnassignedStudent = Student::create([
            'first_name' => 'Same Subject',
            'last_name' => 'Unassigned',
            'parent_id' => $unrelatedParent->id,
            'user_id' => $sameSubjectUnassignedStudentUser->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        DB::table('subjects')->updateOrInsert(
            ['id' => 20],
            ['title' => 'Language and Literature', 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('grade_level_subjects')->updateOrInsert(
            ['id' => 30],
            [
                'subject_id' => 20,
                'grade_level_id' => 1,
                'academic_year_id' => 1,
                'type' => 'standard',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('students_subjects')->updateOrInsert(
            ['id' => 40],
            [
                'student_id' => $student->id,
                'grade_level_subject_id' => 30,
                'academic_year_id' => 1,
                'enrolled_at' => now()->toDateString(),
                'status' => 'active',
                'class_subject_id' => 50,
            ]
        );

        DB::table('students_subjects')->updateOrInsert(
            ['id' => 41],
            [
                'student_id' => $sameSubjectUnassignedStudent->id,
                'grade_level_subject_id' => 30,
                'academic_year_id' => 1,
                'enrolled_at' => now()->toDateString(),
                'status' => 'active',
                'class_subject_id' => 50,
            ]
        );

        DB::table('class_sessions')->updateOrInsert(
            ['id' => 60],
            [
                'title' => 'Library Session',
                'subject_id' => 20,
                'class_subject_id' => 50,
                'teacher_subject_classes_id' => 70,
                'student_id' => null,
                'date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('session_tasks')->updateOrInsert(
            ['id' => 80],
            [
                'class_session_id' => 60,
                'title' => 'Library Task',
                'description' => 'Task with a library file snapshot.',
                'assign_to_all' => 'custom',
                'sort' => 1,
                'default_points' => 5,
                'max_points' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('session_task_student')->updateOrInsert(
            [
                'session_task_id' => 80,
                'student_id' => $student->id,
            ],
            [
                'status' => 'assigned',
                'assign_to_all' => false,
                'student_points' => 0,
                'submitted_at' => null,
            ]
        );

        DB::table('attachment_files')->updateOrInsert(
            ['id' => 90],
            [
                'session_task_id' => 80,
                'type' => 'file',
                'path' => 'test-protected-library-resource/protected-library.pdf',
                'title' => 'Protected Library PDF',
                'description' => 'Snapshot from the teacher Library.',
                'file_size' => Storage::disk('public')->size('test-protected-library-resource/protected-library.pdf'),
                'subject_id' => 20,
                'teacher_subject_class_id' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return [
            'teacher_user' => $teacherUser,
            'student_user' => $studentUser,
            'parent_user' => $parentUser,
            'unrelated_student_user' => $unrelatedStudentUser,
            'unrelated_parent_user' => $unrelatedParentUser,
            'same_subject_unassigned_student_user' => $sameSubjectUnassignedStudentUser,
            'student_id' => $student->id,
            'session_id' => 60,
            'attachment_id' => 90,
        ];
    }

    private function createRequiredTables(): void
    {
        if (! Schema::hasTable('academic_years')) {
            Schema::create('academic_years', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->string('title')->nullable();
                $table->boolean('is_current')->default(false);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->timestamps();
            });
        }

        DB::table('academic_years')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'Current Academic Year',
                'title' => 'Current Academic Year',
                'is_current' => true,
                'start_date' => now()->startOfYear()->toDateString(),
                'end_date' => now()->endOfYear()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        if (! Schema::hasTable('parents')) {
            Schema::create('parents', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->boolean('active')->default(false);
                $table->string('lifecycle_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('parents', 'active')) {
            Schema::table('parents', fn (Blueprint $table) => $table->boolean('active')->default(false));
        }

        if (! Schema::hasColumn('parents', 'lifecycle_status')) {
            Schema::table('parents', fn (Blueprint $table) => $table->string('lifecycle_status')->nullable());
        }

        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('status')->default('active');
                $table->string('account_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('students', 'parent_id')) {
            Schema::table('students', fn (Blueprint $table) => $table->unsignedBigInteger('parent_id')->nullable());
        }

        if (! Schema::hasColumn('students', 'last_name')) {
            Schema::table('students', fn (Blueprint $table) => $table->string('last_name')->nullable());
        }

        if (! Schema::hasColumn('students', 'account_status')) {
            Schema::table('students', fn (Blueprint $table) => $table->string('account_status')->nullable());
        }

        if (! Schema::hasTable('subjects')) {
            Schema::create('subjects', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('grade_level_subjects')) {
            Schema::create('grade_level_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('grade_level_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->string('type')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students_subjects')) {
            Schema::create('students_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('grade_level_subject_id')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->date('enrolled_at')->nullable();
                $table->string('status')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
            });
        }

        if (! Schema::hasTable('class_sessions')) {
            Schema::create('class_sessions', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->unsignedBigInteger('teacher_subject_classes_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->date('date')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('class_sessions', 'student_id')) {
            Schema::table('class_sessions', fn (Blueprint $table) => $table->unsignedBigInteger('student_id')->nullable());
        }

        if (! Schema::hasTable('session_tasks')) {
            Schema::create('session_tasks', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('class_session_id');
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('assign_to_all')->nullable();
                $table->integer('sort')->nullable();
                $table->integer('default_points')->default(0);
                $table->integer('max_points')->default(10);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('session_tasks', 'assign_to_all')) {
            Schema::table('session_tasks', fn (Blueprint $table) => $table->string('assign_to_all')->nullable());
        }

        if (! Schema::hasTable('session_task_student')) {
            Schema::create('session_task_student', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_task_id')->nullable();
                $table->unsignedBigInteger('student_id');
                $table->integer('student_points')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->boolean('assign_to_all')->nullable();
                $table->string('status')->nullable();
            });
        }

        if (! Schema::hasTable('attachment_files')) {
            Schema::create('attachment_files', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_task_id')->nullable();
                $table->string('type')->nullable();
                $table->string('path', 2048)->nullable();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('teacher_subject_class_id')->nullable();
                $table->timestamps();
            });
        }

        // Ensure attachment_files has all columns needed when this test runs beside older lightweight fixtures.
        foreach (['description' => 'text', 'file_size' => 'unsignedBigInteger', 'subject_id' => 'unsignedBigInteger', 'teacher_subject_class_id' => 'unsignedBigInteger'] as $column => $type) {
            if (! Schema::hasColumn('attachment_files', $column)) {
                Schema::table('attachment_files', function (Blueprint $table) use ($column, $type): void {
                    $table->{$type}($column)->nullable();
                });
            }
        }

        if (! Schema::hasTable('student_gift_points_history')) {
            Schema::create('student_gift_points_history', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->integer('points')->default(0);
                $table->date('date')->nullable();
                $table->string('status')->nullable();
                $table->string('sign')->nullable();
            });
        }

        if (! Schema::hasTable('student_gifts')) {
            Schema::create('student_gifts', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('gift_id')->nullable();
                $table->string('gift_name')->nullable();
                $table->string('gift_image')->nullable();
                $table->integer('points_required')->nullable();
                $table->string('status')->nullable();
                $table->unsignedBigInteger('approved_by_id')->nullable();
                $table->string('approved_by_name')->nullable();
                $table->timestamp('approval_timestamp')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('reached_at')->nullable();
                $table->timestamp('redeemed_at')->nullable();
            });
        }
    }
}
