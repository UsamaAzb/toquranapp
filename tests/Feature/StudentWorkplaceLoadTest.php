<?php

namespace Tests\Feature;

use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Http\Controllers\Front\Student\WorkplaceController;
use App\Models\ParentModel;
use App\Models\PunishmentAgreement;
use App\Models\Student;
use App\Models\StudentGiftPointsHistory;
use App\Models\User;
use App\Services\DailySessionPublisher;
use App\Services\DifferentiatedTaskPublisher;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentWorkplaceLoadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createStudentWorkplaceTables();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('student');
    }

    public function test_student_workplace_load_skips_generation_when_not_due_and_uses_sql_discipline_sums(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $parent = ParentModel::create([
            'first_name' => 'Parent',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $student = Student::create([
            'first_name' => 'Youssef',
            'parent_id' => $parent->id,
            'user_id' => $user->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        StudentGiftPointsHistory::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'points' => 15,
            'date' => now()->toDateString(),
            'status' => 'active',
            'sign' => '+',
        ]);

        DB::table('session_task_student')->insert([
            [
                'session_task_id' => 1,
                'student_id' => $student->id,
                'student_points' => 5,
                'status' => 'completed',
            ],
            [
                'session_task_id' => 2,
                'student_id' => $student->id,
                'student_points' => 3,
                'status' => 'assigned',
            ],
        ]);

        DB::table('student_gifts')->insert([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'points_required' => 50,
            'status' => 'pending',
        ]);

        PunishmentAgreement::create([
            'student_id' => $student->id,
            'title' => 'Focus Agreement',
            'status' => 'active',
        ]);

        DB::table('student_session_discipline')->insert([
            [
                'student_id' => $student->id,
                'points' => 7,
                'type' => 'Positive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => $student->id,
                'points' => 5,
                'type' => 'Positive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => $student->id,
                'points' => 3,
                'type' => 'Slip',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => $student->id,
                'points' => 2,
                'type' => 'No Way',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $publisher = Mockery::mock(DailySessionPublisher::class);
        $publisher->shouldReceive('needsGenerationForStudent')
            ->once()
            ->withArgs(function (int $studentId, Carbon $today) use ($student): bool {
                return $studentId === $student->id
                    && $today->format('Y-m-d H:i:sP') === now('Africa/Cairo')->startOfDay()->format('Y-m-d H:i:sP');
            })
            ->andReturn(false);
        $publisher->shouldNotReceive('generateForStudent');
        $this->app->instance(DailySessionPublisher::class, $publisher);

        $differentiatedTaskPublisher = Mockery::mock(DifferentiatedTaskPublisher::class);
        $differentiatedTaskPublisher->shouldReceive('needsGenerationForStudent')
            ->once()
            ->andReturn(false);
        $differentiatedTaskPublisher->shouldNotReceive('generateForStudent');
        $this->app->instance(DifferentiatedTaskPublisher::class, $differentiatedTaskPublisher);

        $this->actingAs($user);

        $connection = DB::connection();
        $connection->flushQueryLog();
        $connection->enableQueryLog();

        $response = app(WorkplaceController::class)->index(Request::create('/student/workplace', 'GET'));

        $disciplineQueries = collect($connection->getQueryLog())
            ->pluck('query')
            ->map(fn (string $query): string => strtolower($query))
            ->filter(fn (string $query): bool => str_contains($query, 'student_session_discipline'))
            ->values();

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertSame(12, $response->getData()['total_post_point']);
        $this->assertSame(5, $response->getData()['total_negative_point']);
        $this->assertCount(2, $disciplineQueries);
        $this->assertTrue($disciplineQueries->every(fn (string $query): bool => str_contains($query, 'sum(')));
        $this->assertTrue($disciplineQueries->every(fn (string $query): bool => ! str_contains($query, 'select *')));
    }

    public function test_student_workplace_to_do_count_matches_visible_uncompleted_tasks(): void
    {
        $user = User::factory()->create();
        $user->assignRole('student');

        $parent = ParentModel::create([
            'first_name' => 'Parent',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $student = Student::create([
            'first_name' => 'Safeya',
            'parent_id' => $parent->id,
            'user_id' => $user->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        $classSubjectId = 10;

        DB::table('students_subjects')->insert([
            'student_id' => $student->id,
            'class_subject_id' => $classSubjectId,
            'status' => 'active',
        ]);

        $sessionId = DB::table('class_sessions')->insertGetId([
            'class_subject_id' => $classSubjectId,
            'student_id' => $student->id,
            'date' => now()->toDateString(),
            'title' => 'Generated DT',
        ]);

        DB::table('session_materials')->insert([
            'session_id' => $sessionId,
            'status' => 'published',
        ]);

        $missingPivotTaskId = DB::table('session_tasks')->insertGetId([
            'class_session_id' => $sessionId,
            'title' => 'Visible missing pivot',
            'created_at' => now(),
        ]);

        $assignedTaskId = DB::table('session_tasks')->insertGetId([
            'class_session_id' => $sessionId,
            'title' => 'Visible assigned',
            'created_at' => now(),
        ]);

        $completedTaskId = DB::table('session_tasks')->insertGetId([
            'class_session_id' => $sessionId,
            'title' => 'Visible completed',
            'created_at' => now(),
        ]);

        $blankStatusTaskId = DB::table('session_tasks')->insertGetId([
            'class_session_id' => $sessionId,
            'title' => 'Visible blank status',
            'created_at' => now(),
        ]);

        DB::table('session_task_student')->insert([
            [
                'session_task_id' => $assignedTaskId,
                'student_id' => $student->id,
                'status' => 'assigned',
            ],
            [
                'session_task_id' => $completedTaskId,
                'student_id' => $student->id,
                'status' => 'completed',
            ],
            [
                'session_task_id' => $blankStatusTaskId,
                'student_id' => $student->id,
                'status' => null,
            ],
        ]);

        $this->mockGenerationPublishers($student->id);
        $this->actingAs($user);

        $response = app(WorkplaceController::class)->index(Request::create('/student/workplace', 'GET'));

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertSame(3, $response->getData()['AssignedTaskStudentCount']);
        $this->assertSame(1, $response->getData()['CompletedTaskStudentCount']);
        $this->assertDatabaseHas('session_task_student', [
            'session_task_id' => $missingPivotTaskId,
            'student_id' => $student->id,
            'status' => 'assigned',
        ]);
        $this->assertDatabaseHas('session_task_student', [
            'session_task_id' => $blankStatusTaskId,
            'student_id' => $student->id,
            'status' => 'assigned',
        ]);
    }

    private function createStudentWorkplaceTables(): void
    {
        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('status')->default('active');
                $table->string('account_status')->nullable();
                $table->unsignedBigInteger('current_class_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('students', 'parent_id')) {
            Schema::table('students', fn (Blueprint $table) => $table->unsignedBigInteger('parent_id')->nullable());
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

        if (! Schema::hasTable('academic_years')) {
            Schema::create('academic_years', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->boolean('is_current')->default(false);
                $table->timestamps();
            });
        }

        if (! DB::table('academic_years')->where('is_current', 1)->exists()) {
            DB::table('academic_years')->insert([
                'id' => 1,
                'name' => 'Test Year',
                'is_current' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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
                $table->timestamp('created_at')->nullable();
                $table->timestamp('reached_at')->nullable();
                $table->timestamp('redeemed_at')->nullable();
                $table->integer('gift_order')->nullable();
            });
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
                $table->string('flag')->nullable();
            });
        }

        if (! Schema::hasTable('class_sessions')) {
            Schema::create('class_sessions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->date('date')->nullable();
                $table->string('title')->nullable();
            });
        }

        if (! Schema::hasTable('session_materials')) {
            Schema::create('session_materials', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_id')->nullable();
                $table->string('status')->nullable();
            });
        }

        if (! Schema::hasTable('session_tasks')) {
            Schema::create('session_tasks', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('class_session_id')->nullable();
                $table->string('title')->nullable();
                $table->timestamp('created_at')->nullable();
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

        if (! Schema::hasTable('punishment_agreements')) {
            Schema::create('punishment_agreements', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->string('title')->nullable();
                $table->unsignedBigInteger('punishment_type_id')->nullable();
                $table->string('status')->nullable();
            });
        }

        if (! Schema::hasTable('student_session_discipline')) {
            Schema::create('student_session_discipline', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->integer('points')->default(0);
                $table->string('type')->nullable();
                $table->timestamps();
            });
        }
    }

    private function mockGenerationPublishers(int $studentId): void
    {
        $dailyPublisher = Mockery::mock(DailySessionPublisher::class);
        $dailyPublisher->shouldReceive('needsGenerationForStudent')
            ->once()
            ->withArgs(fn (int $receivedStudentId, Carbon $today): bool => $receivedStudentId === $studentId
                && $today->format('Y-m-d H:i:sP') === now('Africa/Cairo')->startOfDay()->format('Y-m-d H:i:sP'))
            ->andReturn(false);
        $dailyPublisher->shouldNotReceive('generateForStudent');
        $this->app->instance(DailySessionPublisher::class, $dailyPublisher);

        $differentiatedTaskPublisher = Mockery::mock(DifferentiatedTaskPublisher::class);
        $differentiatedTaskPublisher->shouldReceive('needsGenerationForStudent')
            ->once()
            ->withArgs(fn (int $receivedStudentId, Carbon $today): bool => $receivedStudentId === $studentId
                && $today->format('Y-m-d H:i:sP') === now('Africa/Cairo')->startOfDay()->format('Y-m-d H:i:sP'))
            ->andReturn(false);
        $differentiatedTaskPublisher->shouldNotReceive('generateForStudent');
        $this->app->instance(DifferentiatedTaskPublisher::class, $differentiatedTaskPublisher);
    }
}
