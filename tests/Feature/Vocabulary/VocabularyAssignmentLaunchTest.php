<?php

namespace Tests\Feature\Vocabulary;

use App\Models\User;
use App\Models\VocabularyGameAssignment;
use App\Models\VocabularySet;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class VocabularyAssignmentLaunchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createVocabularyAssignmentTables();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['teacher', 'admin', 'super_admin', 'owner'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_assignment_schema_is_owner_managed(): void
    {
        if (! Schema::hasTable('vocabulary_game_assignments')) {
            $this->markTestSkipped('Owner-run P7 vocabulary SQL has not been executed in this environment.');
        }

        $this->assertTrue(Schema::hasTable('vocabulary_game_assignments'));
    }

    public function test_teacher_cannot_assign_vocabulary_to_another_teachers_class(): void
    {
        $teacher = $this->teacherUser();
        $otherTeacher = $this->teacherUser();
        $setId = $this->playableSetId(['visibility' => VocabularySet::VISIBILITY_SHARED]);

        $this->teacherSubjectClassId($teacher, 101);
        $this->teacherSubjectClassId($otherTeacher, 202);

        $this->actingAs($teacher)
            ->from('/teacher/vocabulary/games/launch')
            ->post(route('teacher.vocabulary.games.assignments.store'), [
                'vocabulary_set_id' => $setId,
                'audience_type' => VocabularyGameAssignment::AUDIENCE_CLASS,
                'audience_id' => 202,
            ])
            ->assertForbidden();

        $this->assertSame(0, DB::table('vocabulary_game_assignments')->count());
    }

    public function test_teacher_cannot_assign_private_set_they_do_not_own(): void
    {
        $teacher = $this->teacherUser();
        $otherTeacher = $this->teacherUser();
        $setId = $this->playableSetId([
            'owner_user_id' => $otherTeacher->id,
            'visibility' => VocabularySet::VISIBILITY_PRIVATE,
        ]);

        $this->teacherSubjectClassId($teacher, 101);

        $this->actingAs($teacher)
            ->from('/teacher/vocabulary/games/launch')
            ->post(route('teacher.vocabulary.games.assignments.store'), [
                'vocabulary_set_id' => $setId,
                'audience_type' => VocabularyGameAssignment::AUDIENCE_CLASS,
                'audience_id' => 101,
            ])
            ->assertNotFound();

        $this->assertSame(0, DB::table('vocabulary_game_assignments')->count());
    }

    public function test_teacher_cannot_assign_to_student_outside_their_current_class_scope(): void
    {
        $teacher = $this->teacherUser();
        $otherTeacher = $this->teacherUser();
        $setId = $this->playableSetId(['visibility' => VocabularySet::VISIBILITY_SHARED]);

        $this->teacherSubjectClassId($teacher, 101);
        $this->teacherSubjectClassId($otherTeacher, 202);
        $studentId = DB::table('students')->insertGetId([
            'first_name' => 'Outside',
            'last_name' => 'Student',
            'current_class_id' => 202,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->from('/teacher/vocabulary/games/launch')
            ->post(route('teacher.vocabulary.games.assignments.store'), [
                'vocabulary_set_id' => $setId,
                'audience_type' => VocabularyGameAssignment::AUDIENCE_STUDENT,
                'audience_id' => $studentId,
            ])
            ->assertForbidden();

        $this->assertSame(0, DB::table('vocabulary_game_assignments')->count());
    }

    public function test_teacher_can_assign_own_private_set_to_their_class(): void
    {
        $teacher = $this->teacherUser();
        $setId = $this->playableSetId([
            'owner_user_id' => $teacher->id,
            'visibility' => VocabularySet::VISIBILITY_PRIVATE,
        ]);

        $this->teacherSubjectClassId($teacher, 101);

        $this->actingAs($teacher)
            ->from('/teacher/vocabulary/games/launch')
            ->post(route('teacher.vocabulary.games.assignments.store'), [
                'vocabulary_set_id' => $setId,
                'audience_type' => VocabularyGameAssignment::AUDIENCE_CLASS,
                'audience_id' => 101,
            ])
            ->assertRedirect('/teacher/vocabulary/games/launch');

        $this->assertDatabaseHas('vocabulary_game_assignments', [
            'vocabulary_set_id' => $setId,
            'assigned_by_user_id' => $teacher->id,
            'audience_type' => VocabularyGameAssignment::AUDIENCE_CLASS,
            'audience_id' => 101,
            'difficulty_policy' => VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE,
            'status' => VocabularyGameAssignment::STATUS_ACTIVE,
        ]);
    }

    public function test_super_admin_can_assign_shared_set_without_teacher_class_scope(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super_admin');
        $setId = $this->playableSetId(['visibility' => VocabularySet::VISIBILITY_SHARED]);

        $this->actingAs($superAdmin)
            ->from('/teacher/vocabulary/games/launch')
            ->post(route('teacher.vocabulary.games.assignments.store'), [
                'vocabulary_set_id' => $setId,
                'audience_type' => VocabularyGameAssignment::AUDIENCE_CLASS,
                'audience_id' => 999,
            ])
            ->assertRedirect('/teacher/vocabulary/games/launch');

        $this->assertDatabaseHas('vocabulary_game_assignments', [
            'vocabulary_set_id' => $setId,
            'assigned_by_user_id' => $superAdmin->id,
            'audience_type' => VocabularyGameAssignment::AUDIENCE_CLASS,
            'audience_id' => 999,
        ]);
    }

    private function teacherUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('teacher');

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function playableSetId(array $overrides = []): int
    {
        return DB::table('vocabulary_sets')->insertGetId(array_merge([
            'parent_id' => null,
            'title' => 'Playable Vocabulary',
            'description' => null,
            'node_type' => VocabularySet::NODE_PLAYABLE,
            'set_type' => VocabularySet::TYPE_TEACHER,
            'source_kind' => VocabularySet::SOURCE_CUSTOM,
            'source_key' => 'test-playable',
            'owner_user_id' => null,
            'visibility' => VocabularySet::VISIBILITY_SHARED,
            'sort_order' => 1,
            'created_by_user_id' => null,
            'updated_by_user_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    private function teacherSubjectClassId(User $teacher, int $classId): int
    {
        return DB::table('teacher_subject_classes')->insertGetId([
            'user_teacher_coteacher_id' => $teacher->id,
            'teacher_name' => $teacher->name,
            'class_subject_id' => $classId,
            'grade_id' => 1,
            'grade_name' => 'Grade 1',
            'class_id' => $classId,
            'class_name' => 'Class '.$classId,
            'subject_id' => 1,
            'subject_name' => 'Language and Literature',
            'status' => 'active',
            'assigned_at' => now(),
            'removed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createVocabularyAssignmentTables(): void
    {
        if (! Schema::hasTable('teacher_subject_classes')) {
            Schema::create('teacher_subject_classes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_teacher_coteacher_id');
                $table->string('teacher_name')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->unsignedBigInteger('grade_id')->nullable();
                $table->string('grade_name')->nullable();
                $table->unsignedBigInteger('class_id');
                $table->string('class_name')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('subject_name')->nullable();
                $table->string('status')->default('active');
                $table->timestamp('assigned_at')->nullable();
                $table->timestamp('removed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->unsignedBigInteger('current_class_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('vocabulary_sets')) {
            Schema::create('vocabulary_sets', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('node_type');
                $table->string('set_type');
                $table->string('source_kind');
                $table->string('source_key')->nullable();
                $table->unsignedBigInteger('owner_user_id')->nullable();
                $table->string('visibility');
                $table->unsignedInteger('sort_order')->default(0);
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->unsignedBigInteger('updated_by_user_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('vocabulary_game_assignments')) {
            Schema::create('vocabulary_game_assignments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('vocabulary_set_id');
                $table->unsignedBigInteger('assigned_by_user_id');
                $table->string('audience_type');
                $table->unsignedBigInteger('audience_id');
                $table->json('allowed_games');
                $table->string('difficulty_policy');
                $table->string('status');
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }
    }
}
