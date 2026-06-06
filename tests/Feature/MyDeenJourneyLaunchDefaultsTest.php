<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentGift;
use App\Support\MyDeenJourneyLaunchDefaults;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MyDeenJourneyLaunchDefaultsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createLaunchDefaultTables();
    }

    public function test_it_creates_missing_launch_reward_queue_for_existing_student(): void
    {
        $student = $this->createStudentWithCurrentYear();

        $this->assertSame(0, DB::table('student_gifts')->where('student_id', $student->id)->count());

        app(MyDeenJourneyLaunchDefaults::class)->ensureRewardQueue($student->id, 1);

        $this->assertSame(10, DB::table('student_gifts')->where('student_id', $student->id)->count());
        $this->assertSame(1, DB::table('student_gifts')->where('student_id', $student->id)->where('status', StudentGift::STATUS_PENDING)->count());
        $this->assertSame(9, DB::table('student_gifts')->where('student_id', $student->id)->where('status', StudentGift::STATUS_WAITING)->count());
        $this->assertSame(100, (int) DB::table('student_gifts')->where('student_id', $student->id)->min('points_required'));
        $this->assertSame(1000, (int) DB::table('student_gifts')->where('student_id', $student->id)->max('points_required'));
    }

    public function test_it_creates_missing_behavior_templates_and_meeting_agreements_for_existing_student(): void
    {
        $student = $this->createStudentWithCurrentYear();

        DB::table('reward_discipline_transfer')->insert([
            [
                'title' => 'Good Job',
                'status' => 'active',
                'points' => 1,
                'description' => 'Positive starter behavior.',
                'type' => 'Positive',
                'sort' => 10,
                'teacher_desc' => 0,
                'selected' => 0,
            ],
            [
                'title' => 'Oops!',
                'status' => 'active',
                'points' => 1,
                'description' => 'Minor slip starter behavior.',
                'type' => 'Slip',
                'sort' => 20,
                'teacher_desc' => 0,
                'selected' => 0,
            ],
        ]);

        DB::table('punishments_suggestions')->insert([
            'punishment_type_id' => 1,
            'suggestion_text' => 'Lose 2-3 reward points',
        ]);

        app(MyDeenJourneyLaunchDefaults::class)->ensureBehaviorTemplates($student->id);

        $this->assertDatabaseHas('discipline_icons', [
            'path' => 'images/discipline/34-ud0vRyQq.png',
        ]);
        $this->assertSame(2, DB::table('reward_discipline_points')->where('student_id', $student->id)->count());
        $this->assertDatabaseHas('reward_discipline_points', [
            'student_id' => $student->id,
            'title' => 'Good Job',
            'type' => 'Positive',
            'points' => 1,
            'status' => 'active',
            'discipline_icon_path' => 'images/discipline/34-ud0vRyQq.png',
            'teacher_desc' => 1,
        ]);
        $this->assertDatabaseHas('reward_discipline_points', [
            'student_id' => $student->id,
            'title' => 'Oops!',
            'type' => 'Slip',
            'points' => 1,
            'status' => 'active',
            'discipline_icon_path' => 'images/discipline/42-CcVNxBRq.png',
            'teacher_desc' => 1,
        ]);
        $this->assertSame(0, DB::table('reward_discipline_points')
            ->where('student_id', $student->id)
            ->whereNull('discipline_icon_id')
            ->count());
        $this->assertDatabaseHas('punishment_agreements', [
            'student_id' => $student->id,
            'punishment_type_id' => 1,
            'title' => 'Lose 2-3 reward points',
            'status' => 'active',
        ]);
    }

    public function test_it_heals_existing_launch_behavior_rows_that_still_use_the_fallback_heart_icon(): void
    {
        $student = $this->createStudentWithCurrentYear();

        DB::table('discipline_icons')->insert([
            [
                'id' => 1,
                'path' => 'images/discipline/respect.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('reward_discipline_points')->insert([
            'title' => 'Good Job',
            'status' => 'active',
            'student_id' => $student->id,
            'points' => 1,
            'description' => 'Existing old copied row.',
            'type' => 'Positive',
            'discipline_icon_id' => 1,
            'discipline_icon_path' => 'images/discipline/respect.png',
            'sort' => 10,
            'teacher_desc' => 0,
            'selected' => 0,
        ]);

        app(MyDeenJourneyLaunchDefaults::class)->ensureBehaviorTemplates($student->id);

        $this->assertDatabaseHas('reward_discipline_points', [
            'student_id' => $student->id,
            'title' => 'Good Job',
            'discipline_icon_path' => 'images/discipline/34-ud0vRyQq.png',
        ]);
    }

    public function test_it_heals_existing_launch_popup_category_rows_that_were_copied_as_instant_actions(): void
    {
        $student = $this->createStudentWithCurrentYear();

        DB::table('discipline_icons')->insert([
            [
                'id' => 1,
                'path' => 'images/discipline/respect.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('reward_discipline_points')->insert([
            [
                'title' => 'Good Job',
                'status' => 'active',
                'student_id' => $student->id,
                'points' => 1,
                'description' => 'Existing positive category copied as instant row.',
                'type' => 'Positive',
                'discipline_icon_id' => 1,
                'discipline_icon_path' => 'images/discipline/respect.png',
                'sort' => 10,
                'teacher_desc' => 0,
                'selected' => 0,
            ],
            [
                'title' => 'Oops!',
                'status' => 'active',
                'student_id' => $student->id,
                'points' => 1,
                'description' => 'Existing slip category copied as instant row.',
                'type' => 'Slip',
                'discipline_icon_id' => 1,
                'discipline_icon_path' => 'images/discipline/respect.png',
                'sort' => 10,
                'teacher_desc' => 0,
                'selected' => 0,
            ],
            [
                'title' => 'Serious Matter',
                'status' => 'active',
                'student_id' => $student->id,
                'points' => 5,
                'description' => 'Existing red flag category copied as instant row.',
                'type' => 'No Way',
                'discipline_icon_id' => 1,
                'discipline_icon_path' => 'images/discipline/respect.png',
                'sort' => 20,
                'teacher_desc' => 0,
                'selected' => 0,
            ],
        ]);

        app(MyDeenJourneyLaunchDefaults::class)->ensureBehaviorTemplates($student->id);

        $this->assertDatabaseHas('reward_discipline_points', [
            'student_id' => $student->id,
            'title' => 'Good Job',
            'type' => 'Positive',
            'teacher_desc' => 1,
        ]);
        $this->assertDatabaseHas('reward_discipline_points', [
            'student_id' => $student->id,
            'title' => 'Oops!',
            'type' => 'Slip',
            'teacher_desc' => 1,
        ]);
        $this->assertDatabaseHas('reward_discipline_points', [
            'student_id' => $student->id,
            'title' => 'Serious Matter',
            'type' => 'No Way',
            'teacher_desc' => 1,
        ]);
    }

    private function createStudentWithCurrentYear(): Student
    {
        DB::table('academic_years')->insert([
            'id' => 1,
            'title' => 'Launch Year',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Student::create([
            'first_name' => 'Smoke',
            'last_name' => 'Student',
            'status' => 'active',
            'account_status' => 'active',
        ]);
    }

    private function createLaunchDefaultTables(): void
    {
        if (! Schema::hasTable('academic_years')) {
            Schema::create('academic_years', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_current')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('status')->nullable();
                $table->string('account_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('student_gifts')) {
            Schema::create('student_gifts', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('student_id');
                $table->string('gift_name')->nullable();
                $table->string('gift_image')->nullable();
                $table->unsignedBigInteger('gift_id')->nullable();
                $table->integer('points_required')->nullable();
                $table->string('status')->nullable();
                $table->unsignedBigInteger('approved_by_id')->nullable();
                $table->string('approved_by_name')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('reached_at')->nullable();
                $table->timestamp('redeemed_at')->nullable();
                $table->integer('gift_order')->nullable();
            });
        }

        if (! Schema::hasTable('reward_discipline_transfer')) {
            Schema::create('reward_discipline_transfer', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->string('status')->default('active');
                $table->integer('points')->default(1);
                $table->text('description')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('type');
                $table->unsignedBigInteger('discipline_icon_id')->nullable();
                $table->string('discipline_icon_path')->nullable();
                $table->integer('sort')->nullable();
                $table->boolean('teacher_desc')->default(false);
                $table->boolean('selected')->default(false);
            });
        }

        if (! Schema::hasTable('discipline_icons')) {
            Schema::create('discipline_icons', function (Blueprint $table): void {
                $table->id();
                $table->string('path');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('reward_discipline_points')) {
            Schema::create('reward_discipline_points', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->string('status')->default('active');
                $table->unsignedBigInteger('student_id')->nullable();
                $table->integer('points')->default(1);
                $table->text('description')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->string('type');
                $table->unsignedBigInteger('discipline_icon_id')->nullable();
                $table->string('discipline_icon_path')->nullable();
                $table->integer('sort')->nullable();
                $table->boolean('teacher_desc')->default(false);
                $table->boolean('selected')->default(false);
            });
        }

        if (! Schema::hasTable('punishments_suggestions')) {
            Schema::create('punishments_suggestions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('punishment_type_id')->nullable();
                $table->string('suggestion_text');
            });
        }

        if (! Schema::hasTable('punishment_agreements')) {
            Schema::create('punishment_agreements', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->string('title');
                $table->unsignedBigInteger('punishment_type_id')->nullable();
                $table->string('status')->default('active');
            });
        }
    }
}
