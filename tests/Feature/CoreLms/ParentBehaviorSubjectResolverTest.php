<?php

namespace Tests\Feature\CoreLms;

use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Livewire\Parent\BehaviorModal;
use App\Models\ParentModel;
use App\Models\RewardDisciplinePoint;
use App\Models\Student;
use App\Models\User;
use App\Support\ParentBehaviorSubjectResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ParentBehaviorSubjectResolverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createResolverTables();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('parent');
    }

    public function test_parent_behavior_resolves_to_well_being_even_when_mdj_context_is_passed(): void
    {
        Config::set('toquran.parent_behavior_subject_id', 16);

        DB::table('academic_years')->updateOrInsert(
            ['id' => 1],
            [
                'title' => 'Launch Year',
                'is_current' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('subjects')->insert([
            [
                'id' => 15,
                'title' => 'My Deen Journey',
                'active' => 1,
                'row_status' => 'current',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 16,
                'title' => 'Well Being',
                'active' => 1,
                'row_status' => 'current',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $student = Student::create([
            'first_name' => 'Omar',
            'current_class_id' => 10,
            'grade_level_id' => 7,
            'status' => 'active',
            'account_status' => 'active',
        ]);

        DB::table('grade_level_subjects')->insert([
            [
                'id' => 150,
                'grade_level_id' => 7,
                'subject_id' => 15,
                'academic_year_id' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 160,
                'grade_level_id' => 7,
                'subject_id' => 16,
                'academic_year_id' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('class_subjects')->insert([
            [
                'id' => 1500,
                'class_id' => 10,
                'grade_level_subject_id' => 150,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 1600,
                'class_id' => 10,
                'grade_level_subject_id' => 160,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('students_subjects')->insert([
            [
                'student_id' => $student->id,
                'grade_level_subject_id' => 150,
                'academic_year_id' => 1,
                'class_subject_id' => 1500,
                'status' => 'active',
            ],
            [
                'student_id' => $student->id,
                'grade_level_subject_id' => 160,
                'academic_year_id' => 1,
                'class_subject_id' => 1600,
                'status' => 'active',
            ],
        ]);

        DB::table('teacher_subject_classes')->insert([
            [
                'id' => 15000,
                'user_teacher_coteacher_id' => 5,
                'class_subject_id' => 1500,
                'class_id' => 10,
                'grade_id' => 7,
                'subject_id' => 15,
                'subject_name' => 'My Deen Journey',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 16000,
                'user_teacher_coteacher_id' => 5,
                'class_subject_id' => 1600,
                'class_id' => 10,
                'grade_id' => 7,
                'subject_id' => 16,
                'subject_name' => 'Well Being',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $resolved = app(ParentBehaviorSubjectResolver::class)
            ->resolveForStudent($student->id, 15000);

        $this->assertNotNull($resolved);
        $this->assertSame(16000, (int) $resolved->id);
        $this->assertSame(16, (int) $resolved->subject_id);
    }

    public function test_parent_behavior_modal_write_persists_well_being_subject_from_mdj_context(): void
    {
        Config::set('toquran.parent_behavior_subject_id', 16);

        $this->seedMdjAndWellBeingSubjectFixture();
        $student = $this->createParentOwnedStudent();

        $behavior = RewardDisciplinePoint::create([
            'title' => 'Good Adab',
            'type' => 'Positive',
            'points' => 4,
            'status' => 'active',
            'teacher_desc' => 0,
            'selected' => 0,
            'sort' => 10,
        ]);

        $this->actingAs($student->parent->user);

        Livewire::test(BehaviorModal::class)
            ->call('openAddBehaviorModal', $student->id, 'Positive', 15000, 1)
            ->set('selectedBehaviorId', $behavior->id)
            ->set('pointsInput', 4)
            ->set('descriptionInput', 'Parent noted this after the family check-in.')
            ->call('confirmBehaviorWithDescription')
            ->assertHasNoErrors();

        $disciplineRow = DB::table('student_session_discipline')
            ->where('student_id', $student->id)
            ->where('student_reward_discipline_id', $behavior->id)
            ->first();

        $this->assertNotNull($disciplineRow);
        $this->assertSame(16000, (int) $disciplineRow->teacher_subject_classes_id);

        $ledgerRow = DB::table('reward_points_ledger')
            ->where('student_id', $student->id)
            ->where('source_type', 'discipline')
            ->first();

        $this->assertNotNull($ledgerRow);
        $this->assertSame(16, (int) $ledgerRow->subject_id);
        $this->assertSame(4, (int) $ledgerRow->points_delta);
    }

    private function createResolverTables(): void
    {
        if (! Schema::hasTable('academic_years')) {
            Schema::create('academic_years', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->boolean('is_current')->default(false);
                $table->timestamps();
            });
        }

        if (! DB::table('academic_years')->where('is_current', 1)->exists()) {
            DB::table('academic_years')->insert([
                'id' => 1,
                'title' => 'Launch Year',
                'is_current' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! Schema::hasTable('subjects')) {
            Schema::create('subjects', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->boolean('active')->default(true);
                $table->string('row_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('current_class_id')->nullable();
                $table->unsignedBigInteger('grade_level_id')->nullable();
                $table->string('status')->nullable();
                $table->string('account_status')->nullable();
                $table->timestamps();
            });
        }

        foreach ([
            'parent_id',
            'user_id',
            'current_class_id',
            'grade_level_id',
        ] as $column) {
            if (! Schema::hasColumn('students', $column)) {
                Schema::table('students', fn (Blueprint $table) => $table->unsignedBigInteger($column)->nullable());
            }
        }

        if (! Schema::hasColumn('students', 'account_status')) {
            Schema::table('students', fn (Blueprint $table) => $table->string('account_status')->nullable());
        }

        if (! Schema::hasTable('parents')) {
            Schema::create('parents', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('email')->nullable();
                $table->boolean('active')->default(true);
                $table->string('lifecycle_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('grade_level_subjects')) {
            Schema::create('grade_level_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('grade_level_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('class_subjects')) {
            Schema::create('class_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('grade_level_subject_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students_subjects')) {
            Schema::create('students_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('grade_level_subject_id')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->string('status')->nullable();
            });
        }

        if (! Schema::hasTable('teacher_subject_classes')) {
            Schema::create('teacher_subject_classes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_teacher_coteacher_id')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('grade_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('subject_name')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        $this->createBehaviorWriteTables();
    }

    private function createBehaviorWriteTables(): void
    {
        if (! Schema::hasTable('reward_discipline_points')) {
            Schema::create('reward_discipline_points', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->string('title')->nullable();
                $table->integer('points')->default(0);
                $table->string('status')->nullable();
                $table->text('description')->nullable();
                $table->string('type')->nullable();
                $table->unsignedBigInteger('discipline_icon_id')->nullable();
                $table->string('discipline_icon_path')->nullable();
                $table->integer('sort')->nullable();
                $table->boolean('teacher_desc')->default(false);
                $table->boolean('selected')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('student_session_discipline')) {
            Schema::create('student_session_discipline', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->unsignedBigInteger('discipline_icon_id')->nullable();
                $table->string('discipline_icon_path')->nullable();
                $table->unsignedBigInteger('student_reward_discipline_id')->nullable();
                $table->unsignedBigInteger('teacher_subject_classes_id')->nullable();
                $table->unsignedBigInteger('student_id');
                $table->integer('points')->default(0);
                $table->text('description')->nullable();
                $table->string('type')->nullable();
                $table->timestamps();
            });
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

        if (! Schema::hasTable('reward_points_ledger')) {
            Schema::create('reward_points_ledger', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->string('source_type');
                $table->unsignedBigInteger('source_id');
                $table->integer('points_delta')->default(0);
                $table->string('sign')->nullable();
                $table->unsignedBigInteger('granted_by')->nullable();
                $table->timestamp('granted_at')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->text('comment')->nullable();
            });
        }

        if (! Schema::hasTable('reward_totals')) {
            Schema::create('reward_totals', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->integer('total_points')->default(0);
                $table->timestamp('created_at')->nullable();
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
                $table->integer('gift_order')->nullable();
            });
        }
    }

    private function seedMdjAndWellBeingSubjectFixture(): void
    {
        DB::table('subjects')->insert([
            [
                'id' => 15,
                'title' => 'My Deen Journey',
                'active' => 1,
                'row_status' => 'current',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 16,
                'title' => 'Well Being',
                'active' => 1,
                'row_status' => 'current',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('grade_level_subjects')->insert([
            [
                'id' => 150,
                'grade_level_id' => 7,
                'subject_id' => 15,
                'academic_year_id' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 160,
                'grade_level_id' => 7,
                'subject_id' => 16,
                'academic_year_id' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('class_subjects')->insert([
            [
                'id' => 1500,
                'class_id' => 10,
                'grade_level_subject_id' => 150,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 1600,
                'class_id' => 10,
                'grade_level_subject_id' => 160,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('teacher_subject_classes')->insert([
            [
                'id' => 15000,
                'user_teacher_coteacher_id' => 5,
                'class_subject_id' => 1500,
                'class_id' => 10,
                'grade_id' => 7,
                'subject_id' => 15,
                'subject_name' => 'My Deen Journey',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 16000,
                'user_teacher_coteacher_id' => 5,
                'class_subject_id' => 1600,
                'class_id' => 10,
                'grade_id' => 7,
                'subject_id' => 16,
                'subject_name' => 'Well Being',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function createParentOwnedStudent(): Student
    {
        $parentUser = User::factory()->create();
        $parentUser->assignRole('parent');

        $studentUser = User::factory()->create();

        $parent = ParentModel::create([
            'first_name' => 'Parent',
            'user_id' => $parentUser->id,
            'email' => $parentUser->email,
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $student = Student::create([
            'first_name' => 'Omar',
            'parent_id' => $parent->id,
            'user_id' => $studentUser->id,
            'current_class_id' => 10,
            'grade_level_id' => 7,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        DB::table('students_subjects')->insert([
            [
                'student_id' => $student->id,
                'grade_level_subject_id' => 150,
                'academic_year_id' => 1,
                'class_subject_id' => 1500,
                'status' => 'active',
            ],
            [
                'student_id' => $student->id,
                'grade_level_subject_id' => 160,
                'academic_year_id' => 1,
                'class_subject_id' => 1600,
                'status' => 'active',
            ],
        ]);

        return $student->fresh(['parent.user']);
    }
}
