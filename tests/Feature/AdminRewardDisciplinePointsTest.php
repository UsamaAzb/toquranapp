<?php

namespace Tests\Feature;

use App\Livewire\Admin\Students\RewardDisciplinePoints;
use App\Models\RewardDisciplinePoint;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class AdminRewardDisciplinePointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRewardDisciplineTables();
    }

    public function test_reorder_ignores_popup_and_global_rows_and_keeps_popup_first(): void
    {
        DB::table('students')->insert([
            'id' => 1,
            'first_name' => 'Karim',
            'last_name' => 'Learner',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $global = RewardDisciplinePoint::create([
            'title' => 'Global Slip',
            'status' => 'active',
            'student_id' => null,
            'points' => 1,
            'type' => 'Slip',
            'sort' => 5,
            'teacher_desc' => 0,
            'selected' => 0,
        ]);

        $popup = RewardDisciplinePoint::create([
            'title' => 'Oops!',
            'status' => 'active',
            'student_id' => 1,
            'points' => 1,
            'type' => 'Slip',
            'sort' => 999,
            'teacher_desc' => 1,
            'selected' => 0,
        ]);

        $firstNormal = RewardDisciplinePoint::create([
            'title' => 'Not Ready',
            'status' => 'active',
            'student_id' => 1,
            'points' => 1,
            'type' => 'Slip',
            'sort' => 20,
            'teacher_desc' => 0,
            'selected' => 0,
        ]);

        $secondNormal = RewardDisciplinePoint::create([
            'title' => 'Distracted',
            'status' => 'active',
            'student_id' => 1,
            'points' => 1,
            'type' => 'Slip',
            'sort' => 30,
            'teacher_desc' => 0,
            'selected' => 0,
        ]);

        $component = Livewire::test(RewardDisciplinePoints::class, ['studentId' => 1])
            ->call('reorderBehaviors', 'Slip', [
                $secondNormal->id,
                $popup->id,
                $global->id,
                $firstNormal->id,
            ]);

        $this->assertSame(999, (int) $popup->fresh()->sort);
        $this->assertSame(5, (int) $global->fresh()->sort);
        $this->assertSame(15, (int) $secondNormal->fresh()->sort);
        $this->assertSame(25, (int) $firstNormal->fresh()->sort);

        $titles = collect($component->instance()->slipBehaviors)
            ->pluck('title')
            ->values()
            ->all();

        $this->assertSame('Oops!', $titles[0]);
        $this->assertSame(['Oops!', 'Global Slip', 'Distracted', 'Not Ready'], $titles);
    }

    private function createRewardDisciplineTables(): void
    {
        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->timestamps();
        });

        Schema::create('discipline_icons', function (Blueprint $table): void {
            $table->id();
            $table->string('path');
            $table->timestamps();
        });

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
}
