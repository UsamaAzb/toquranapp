<?php

namespace Tests\Feature;

use App\Livewire\Admin\Students\SubjectManager;
use App\Livewire\Admin\TeacherClassAssignments;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TeacherClassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'array']);

        $this->prepareTeacherAssignmentSchema();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'admin', 'customer_support', 'teacher', 'parent', 'student'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }
    }

    public function test_admin_can_assign_active_teacher_to_to_quran_class(): void
    {
        $admin = $this->staffUser('admin');
        $teacher = $this->staffUser('teacher', ['name' => 'Quran Teacher']);
        $classId = $this->createClass('Quran Beginner Cohort');

        Livewire::actingAs($admin)
            ->test(TeacherClassAssignments::class)
            ->set('teacherId', $teacher->id)
            ->set('classId', $classId)
            ->set('subjectId', 1)
            ->call('assignTeacher')
            ->assertHasNoErrors()
            ->assertSee('Teacher assignment saved.');

        $this->assertDatabaseHas('class_subjects', [
            'class_id' => $classId,
            'grade_level_subject_id' => $this->gradeLevelSubjectId(1),
        ]);

        $this->assertDatabaseHas('teacher_subject_classes', [
            'user_teacher_coteacher_id' => $teacher->id,
            'class_id' => $classId,
            'subject_id' => 1,
            'subject_name' => 'Quran Memorization',
            'status' => 'current',
            'removed_at' => null,
        ]);
    }

    public function test_customer_support_and_teacher_cannot_open_assignment_page(): void
    {
        $this->actingAs($this->staffUser('customer_support'))
            ->get(route('admin.teacher-class-assignments.index'))
            ->assertForbidden();

        $this->actingAs($this->staffUser('teacher'))
            ->get(route('admin.teacher-class-assignments.index'))
            ->assertForbidden();
    }

    public function test_assignment_rejects_inactive_or_non_teacher_users(): void
    {
        $admin = $this->staffUser('admin');
        $inactiveTeacher = $this->staffUser('teacher', ['status' => 'inactive']);
        $support = $this->staffUser('customer_support');
        $classId = $this->createClass('Quran Beginner Cohort');

        Livewire::actingAs($admin)
            ->test(TeacherClassAssignments::class)
            ->set('teacherId', $inactiveTeacher->id)
            ->set('classId', $classId)
            ->set('subjectId', 1)
            ->call('assignTeacher')
            ->assertHasErrors(['teacherId']);

        Livewire::actingAs($admin)
            ->test(TeacherClassAssignments::class)
            ->set('teacherId', $support->id)
            ->set('classId', $classId)
            ->set('subjectId', 1)
            ->call('assignTeacher')
            ->assertHasErrors(['teacherId']);
    }

    public function test_inactive_week14_subjects_are_not_shown_for_launch_assignment(): void
    {
        $admin = $this->staffUser('admin');
        $this->staffUser('teacher', ['name' => 'Quran Teacher']);
        $this->createClass('Quran Beginner Cohort');

        DB::table('subjects')->insert([
            'id' => 20,
            'title' => 'Mathematics',
            'type' => 'standard',
            'program_id' => 1,
            'code' => 'MATH',
            'active' => false,
            'row_status' => 'inactive',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(TeacherClassAssignments::class)
            ->assertSee('Quran Memorization')
            ->assertSee('Arabic Language')
            ->assertSee('Quranic Arabic')
            ->assertSee('Sanad Program')
            ->assertSee('Islamic Studies')
            ->assertSee('Quran Literature')
            ->assertSee('My Deen Journey')
            ->assertSee('Well Being')
            ->assertDontSee('Mathematics');
    }

    public function test_teacher_class_page_only_shows_assigned_current_classes_with_active_student_subjects(): void
    {
        $admin = $this->staffUser('admin');
        $teacher = $this->staffUser('teacher', ['name' => 'Assigned Teacher']);
        $otherTeacher = $this->staffUser('teacher', ['name' => 'Other Teacher']);
        $visibleClassId = $this->createClass('Visible Quran Class');
        $hiddenClassId = $this->createClass('Hidden Quran Class');

        $visibleAssignmentId = $this->assignThroughComponent($admin, $teacher->id, $visibleClassId, 1);
        $hiddenAssignmentId = $this->assignThroughComponent($admin, $otherTeacher->id, $hiddenClassId, 1);

        $this->activateStudentSubjectForAssignment($visibleAssignmentId, [
            'first_name' => 'Visible',
            'last_name' => 'Student',
        ]);
        $this->activateStudentSubjectForAssignment($hiddenAssignmentId, [
            'first_name' => 'Hidden',
            'last_name' => 'Student',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.classes'))
            ->assertOk()
            ->assertSee('Visible Student')
            ->assertSee('Visible Quran Class')
            ->assertDontSee('Hidden Quran Class');
    }

    public function test_teacher_class_cards_hide_archived_students(): void
    {
        $admin = $this->staffUser('admin');
        $teacher = $this->staffUser('teacher', ['name' => 'Assigned Teacher']);
        $classId = $this->createClass('Archived Student Quran Class');
        $assignmentId = $this->assignThroughComponent($admin, $teacher->id, $classId, 1);

        $this->activateStudentSubjectForAssignment($assignmentId, [
            'first_name' => 'Archived',
            'last_name' => 'Student',
            'account_status' => 'archived',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.classes'))
            ->assertOk()
            ->assertDontSee('Archived Student')
            ->assertDontSee('Archived Student Quran Class');
    }

    public function test_deactivated_assignment_is_hidden_from_teacher_scope(): void
    {
        $admin = $this->staffUser('admin');
        $teacher = $this->staffUser('teacher', ['name' => 'Assigned Teacher']);
        $classId = $this->createClass('Visible Quran Class');
        $assignmentId = $this->assignThroughComponent($admin, $teacher->id, $classId, 1);

        $this->activateStudentSubjectForAssignment($assignmentId, [
            'first_name' => 'Visible',
            'last_name' => 'Student',
        ]);

        Livewire::actingAs($admin)
            ->test(TeacherClassAssignments::class)
            ->call('deactivateAssignment', $assignmentId)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teacher_subject_classes', [
            'id' => $assignmentId,
            'status' => 'inactive',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.classes'))
            ->assertOk()
            ->assertDontSee('Visible Quran Class');
    }

    public function test_sidebar_links_teacher_assignments_for_admins_only(): void
    {
        $this->actingAs($this->staffUser('admin'))
            ->get(route('admin.teacher-class-assignments.index'))
            ->assertOk()
            ->assertSee('Teacher Assignments')
            ->assertSee('Class Subject')
            ->assertDontSee('Learning Class');

        $this->actingAs($this->staffUser('customer_support'))
            ->get(route('admin.teacher-class-assignments.index'))
            ->assertForbidden();
    }

    public function test_admin_can_assign_teacher_from_student_subject_access(): void
    {
        $admin = $this->staffUser('admin');
        $teacher = $this->staffUser('teacher', [
            'name' => 'Student Subject Teacher',
            'email' => 'subject-teacher@example.test',
        ]);
        $oldTeacher = $this->staffUser('teacher', [
            'name' => 'Previous Teacher',
            'email' => 'previous-teacher@example.test',
        ]);
        $classId = $this->createClass('Student Account Quran Class');
        $classSubjectId = DB::table('class_subjects')->insertGetId([
            'class_id' => $classId,
            'grade_level_subject_id' => $this->gradeLevelSubjectId(1),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $studentId = DB::table('students')->insertGetId([
            'first_name' => 'Student',
            'last_name' => 'Account',
            'grade_level_id' => 2,
            'grade_name' => 'Beginner',
            'current_class_id' => $classId,
            'account_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $studentSubjectId = DB::table('students_subjects')->insertGetId([
            'student_id' => $studentId,
            'grade_level_subject_id' => $this->gradeLevelSubjectId(1),
            'academic_year_id' => 1,
            'enrolled_at' => now()->toDateString(),
            'status' => 'active',
            'class_subject_id' => $classSubjectId,
        ]);
        $oldAssignmentId = DB::table('teacher_subject_classes')->insertGetId([
            'user_teacher_coteacher_id' => $oldTeacher->id,
            'teacher_name' => $oldTeacher->name,
            'class_subject_id' => $classSubjectId,
            'grade_id' => 2,
            'grade_name' => 'Beginner',
            'class_id' => $classId,
            'class_name' => 'Student Account Quran Class',
            'subject_id' => 1,
            'subject_name' => 'Quran Memorization',
            'status' => 'current',
            'assigned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(SubjectManager::class, ['studentId' => $studentId])
            ->assertSee('Student Subject Teacher')
            ->call('assignTeacherToSubject', $studentSubjectId, $teacher->id)
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('teacher_subject_classes', [
            'id' => $oldAssignmentId,
            'user_teacher_coteacher_id' => $teacher->id,
            'teacher_name' => 'Student Subject Teacher',
            'class_subject_id' => $classSubjectId,
            'subject_id' => 1,
            'status' => 'current',
            'removed_at' => null,
        ]);
    }

    public function test_customer_support_cannot_assign_teacher_from_student_subject_access(): void
    {
        $support = $this->staffUser('customer_support');
        $teacher = $this->staffUser('teacher', [
            'name' => 'Student Subject Teacher',
            'email' => 'subject-teacher-forbidden@example.test',
        ]);
        $classId = $this->createClass('Student Account Quran Class');
        $classSubjectId = DB::table('class_subjects')->insertGetId([
            'class_id' => $classId,
            'grade_level_subject_id' => $this->gradeLevelSubjectId(1),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $studentId = DB::table('students')->insertGetId([
            'first_name' => 'Student',
            'last_name' => 'Forbidden',
            'student_email' => 'student-forbidden@example.test',
            'grade_level_id' => 2,
            'grade_name' => 'Beginner',
            'current_class_id' => $classId,
            'account_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $studentSubjectId = DB::table('students_subjects')->insertGetId([
            'student_id' => $studentId,
            'grade_level_subject_id' => $this->gradeLevelSubjectId(1),
            'academic_year_id' => 1,
            'enrolled_at' => now()->toDateString(),
            'status' => 'active',
            'class_subject_id' => $classSubjectId,
        ]);

        Livewire::actingAs($support)
            ->test(SubjectManager::class, ['studentId' => $studentId])
            ->assertForbidden();

        $this->assertDatabaseMissing('teacher_subject_classes', [
            'user_teacher_coteacher_id' => $teacher->id,
            'class_subject_id' => $classSubjectId,
            'subject_id' => 1,
        ]);
    }

    private function assignThroughComponent(User $admin, int $teacherId, int $classId, int $subjectId): int
    {
        Livewire::actingAs($admin)
            ->test(TeacherClassAssignments::class)
            ->set('teacherId', $teacherId)
            ->set('classId', $classId)
            ->set('subjectId', $subjectId)
            ->call('assignTeacher')
            ->assertHasNoErrors();

        return (int) DB::table('teacher_subject_classes')
            ->where('user_teacher_coteacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->value('id');
    }

    private function activateStudentSubjectForAssignment(int $assignmentId, array $studentAttributes = []): void
    {
        $assignment = DB::table('teacher_subject_classes')->where('id', $assignmentId)->first();
        $studentId = (int) DB::table('students')->insertGetId(array_merge([
            'first_name' => 'Smoke',
            'last_name' => 'Student',
            'student_email' => "student-{$assignmentId}@example.test",
            'grade_level_id' => 2,
            'grade_name' => 'Beginner',
            'current_class_id' => $assignment->class_id,
            'account_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ], $studentAttributes));

        DB::table('students_subjects')->insert([
            'student_id' => $studentId,
            'grade_level_subject_id' => $this->gradeLevelSubjectId((int) $assignment->subject_id),
            'academic_year_id' => 1,
            'enrolled_at' => now()->toDateString(),
            'status' => 'active',
            'class_subject_id' => $assignment->class_subject_id,
        ]);
    }

    private function gradeLevelSubjectId(int $subjectId): int
    {
        return (int) DB::table('grade_level_subjects')
            ->where('grade_level_id', 2)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', 1)
            ->value('id');
    }

    private function createClass(string $title): int
    {
        return (int) DB::table('classes')->insertGetId([
            'title' => $title,
            'grade_level_id' => 2,
            'grade_name' => 'Beginner',
            'status' => 'active',
            'type' => 'main',
            'academic_year_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function staffUser(string $roleName, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'status' => 'active',
        ], $attributes));

        $user->assignRole($roleName);

        return $user;
    }

    private function prepareTeacherAssignmentSchema(): void
    {
        if (! Schema::hasColumn('users', 'status')) {
            Schema::table('users', fn (Blueprint $table) => $table->string('status')->default('active'));
        }

        if (! Schema::hasTable('subjects')) {
            Schema::create('subjects', function (Blueprint $table): void {
                $table->integer('id')->primary();
                $table->string('title');
                $table->string('type')->default('standard');
                $table->unsignedBigInteger('program_id')->default(1);
                $table->string('code')->nullable();
                $table->string('icon')->nullable();
                $table->boolean('active')->default(true);
                $table->string('row_status')->default('current');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('grade_level_subjects')) {
            Schema::create('grade_level_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('grade_level_id');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->string('type')->default('standard');
                $table->string('status')->default('active');
                $table->unsignedBigInteger('created_by_user_id')->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('classes')) {
            Schema::create('classes', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->unsignedBigInteger('grade_level_id')->nullable();
                $table->string('grade_name')->nullable();
                $table->string('class_img')->nullable();
                $table->string('status')->default('active');
                $table->string('type')->default('main');
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('grade_levels')) {
            Schema::create('grade_levels', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->string('code')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('academic_years')) {
            Schema::create('academic_years', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->boolean('is_current')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('class_subjects')) {
            Schema::create('class_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('class_id');
                $table->unsignedBigInteger('grade_level_subject_id');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students_subjects')) {
            Schema::create('students_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('grade_level_subject_id');
                $table->unsignedBigInteger('academic_year_id');
                $table->date('enrolled_at');
                $table->string('status')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('parents')) {
            Schema::create('parents', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('grade_level_id')->nullable();
                $table->unsignedBigInteger('current_class_id')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('student_email')->nullable();
                $table->string('grade_name')->nullable();
                $table->string('account_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('teacher_subject_classes')) {
            Schema::create('teacher_subject_classes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_teacher_coteacher_id')->nullable();
                $table->string('teacher_name')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->unsignedBigInteger('grade_id');
                $table->string('grade_name')->nullable();
                $table->unsignedBigInteger('class_id');
                $table->string('class_name')->nullable();
                $table->string('class_img')->nullable();
                $table->unsignedBigInteger('subject_id');
                $table->string('subject_name')->nullable();
                $table->string('status')->default('current');
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('removed_at')->nullable();
                $table->timestamps();
            });
        }

        $subjects = [
            1 => ['Quran Memorization', 'QURAN_MEM'],
            2 => ['Quranic Arabic', 'QURAN_AR'],
            3 => ['Arabic Language', 'ARABIC_LANG'],
            4 => ['Sanad Program', 'SANAD'],
            15 => ['My Deen Journey', 'MDJ'],
            16 => ['Well Being', 'WELL_BEING'],
            17 => ['Islamic Studies', 'ISLAMIC_STUDIES'],
            18 => ['Quran Literature', 'QURAN_LIT'],
        ];

        foreach ($subjects as $id => [$title, $code]) {
            DB::table('subjects')->updateOrInsert(
                ['id' => $id],
                [
                    'title' => $title,
                    'type' => 'standard',
                    'program_id' => 1,
                    'code' => $code,
                    'active' => true,
                    'row_status' => 'current',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('grade_level_subjects')->updateOrInsert(
                [
                    'grade_level_id' => 2,
                    'subject_id' => $id,
                    'academic_year_id' => 1,
                ],
                [
                    'type' => 'standard',
                    'status' => 'active',
                    'created_by_user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        DB::table('grade_levels')->updateOrInsert(
            ['id' => 2],
            [
                'title' => 'Beginner',
                'code' => 'beginner',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('academic_years')->updateOrInsert(
            ['id' => 1],
            [
                'name' => '2026-2027',
                'is_current' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
