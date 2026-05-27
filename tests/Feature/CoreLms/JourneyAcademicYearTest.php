<?php

namespace Tests\Feature\CoreLms;

use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Livewire\Student\Journey;
use App\Livewire\Student\SessionsBoard;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\StudentGiftPointsHistory;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class JourneyAcademicYearTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createJourneyAcademicYearTables();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('student');
    }

    public function test_journey_completion_writes_the_current_academic_year_to_reward_tables(): void
    {
        DB::table('academic_years')->insert([
            [
                'id' => 1,
                'title' => '2025',
                'is_current' => 0,
            ],
            [
                'id' => 2,
                'title' => '2026',
                'is_current' => 1,
            ],
        ]);

        $user = User::factory()->create();
        $user->assignRole('student');

        $parent = ParentModel::create([
            'first_name' => 'Parent',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $student = Student::create([
            'first_name' => 'Mariam',
            'parent_id' => $parent->id,
            'user_id' => $user->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        DB::table('subjects')->insert([
            'id' => 10,
            'title' => 'Language and Literature',
        ]);

        DB::table('class_sessions')->insert([
            'id' => 19,
            'title' => 'Spicy Journey',
            'subject_id' => 10,
            'class_subject_id' => 10,
            'date' => now()->toDateString(),
        ]);

        DB::table('students_subjects')->insert([
            'student_id' => $student->id,
            'class_subject_id' => 10,
            'status' => 'active',
        ]);

        DB::table('session_tasks')->insert([
            'id' => 44,
            'class_session_id' => 19,
            'title' => 'Chilli Con Carne',
            'description' => 'Task description',
            'sort' => 1,
            'default_points' => 5,
            'max_points' => 10,
        ]);

        DB::table('task_pin_hashes')->insert([
            'user_id' => $user->id,
            'pin_hash' => Hash::make('1234'),
            'pin_unhash' => '1234',
            'updated_at' => now(),
        ]);

        DB::table('student_gifts')->insert([
            'student_id' => $student->id,
            'academic_year_id' => 2,
            'points_required' => 50,
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        Livewire::test(Journey::class, ['sessionId' => 19, 'studentId' => $student->id])
            ->call('openCompleteModal', 44)
            ->set('pinInput', '1234')
            ->call('confirmTaskCompletionWithPin');

        $this->assertDatabaseHas('student_gift_points_history', [
            'student_id' => $student->id,
            'academic_year_id' => 2,
            'points' => 5,
            'sign' => 'plus',
        ]);

        $this->assertDatabaseHas('reward_points_ledger', [
            'student_id' => $student->id,
            'academic_year_id' => 2,
            'subject_id' => 10,
            'source_type' => 'task',
            'source_id' => 44,
            'points_delta' => 5,
        ]);

        $this->assertDatabaseHas('reward_totals', [
            'student_id' => $student->id,
            'academic_year_id' => 2,
            'subject_id' => 10,
            'total_points' => 5,
        ]);

        $this->assertSame(1, StudentGiftPointsHistory::query()->count());
    }

    public function test_pin_completion_modal_clamps_default_points_to_task_max(): void
    {
        DB::table('academic_years')->insert([
            'id' => 1,
            'title' => '2026',
            'is_current' => 1,
        ]);

        $user = User::factory()->create();
        $user->assignRole('student');

        $parent = ParentModel::create([
            'first_name' => 'Parent',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $student = Student::create([
            'first_name' => 'Mariam',
            'parent_id' => $parent->id,
            'user_id' => $user->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        DB::table('subjects')->insert([
            'id' => 10,
            'title' => 'Language and Literature',
        ]);

        DB::table('class_sessions')->insert([
            'id' => 19,
            'title' => 'Point Clamp Journey',
            'subject_id' => 10,
            'class_subject_id' => 10,
            'date' => now()->toDateString(),
        ]);

        DB::table('students_subjects')->insert([
            'student_id' => $student->id,
            'class_subject_id' => 10,
            'status' => 'active',
        ]);

        DB::table('session_tasks')->insert([
            'id' => 44,
            'class_session_id' => 19,
            'title' => 'Legacy bad points',
            'description' => 'Task description',
            'sort' => 1,
            'default_points' => 20,
            'max_points' => 5,
        ]);

        $this->actingAs($user);

        Livewire::test(Journey::class, ['sessionId' => 19, 'studentId' => $student->id])
            ->call('openCompleteModal', 44)
            ->assertSet('currentTaskMaxPoint', 5)
            ->assertSet('currentTaskDefaultPoint', 5);
    }

    public function test_sessions_board_pin_modal_clamps_default_points_to_task_max(): void
    {
        DB::table('academic_years')->insert([
            'id' => 1,
            'title' => '2026',
            'is_current' => 1,
        ]);

        $user = User::factory()->create();
        $user->assignRole('student');

        $parent = ParentModel::create([
            'first_name' => 'Parent',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $student = Student::create([
            'first_name' => 'Mariam',
            'parent_id' => $parent->id,
            'user_id' => $user->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        DB::table('subjects')->insert([
            'id' => 10,
            'title' => 'Language and Literature',
        ]);

        DB::table('class_sessions')->insert([
            'id' => 19,
            'title' => 'Point Clamp Session',
            'subject_id' => 10,
            'class_subject_id' => 10,
            'date' => now()->toDateString(),
        ]);

        $studentSubjectId = DB::table('students_subjects')->insertGetId([
            'student_id' => $student->id,
            'class_subject_id' => 10,
            'status' => 'active',
        ]);

        DB::table('session_materials')->insert([
            'session_id' => 19,
            'status' => 'published',
        ]);

        DB::table('session_tasks')->insert([
            'id' => 44,
            'class_session_id' => 19,
            'title' => 'Legacy bad points',
            'description' => 'Task description',
            'sort' => 1,
            'default_points' => 20,
            'max_points' => 5,
        ]);

        $this->actingAs($user);

        Livewire::test(SessionsBoard::class, [
            'studentSubjectId' => $studentSubjectId,
            'studentId' => $student->id,
        ])
            ->call('openCompleteModal', 44)
            ->assertSet('currentTaskMaxPoint', 5)
            ->assertSet('currentTaskDefaultPoint', 5);
    }

    public function test_journey_completion_keeps_reward_totals_separate_per_subject_and_academic_year(): void
    {
        DB::table('academic_years')->insert([
            [
                'id' => 1,
                'title' => '2025',
                'is_current' => 1,
            ],
            [
                'id' => 2,
                'title' => '2026',
                'is_current' => 0,
            ],
        ]);

        $user = User::factory()->create();
        $user->assignRole('student');

        $parent = ParentModel::create([
            'first_name' => 'Parent',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $student = Student::create([
            'first_name' => 'Mariam',
            'parent_id' => $parent->id,
            'user_id' => $user->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        DB::table('subjects')->insert([
            ['id' => 10, 'title' => 'Language and Literature'],
            ['id' => 11, 'title' => 'Mathematics'],
        ]);

        DB::table('class_sessions')->insert([
            [
                'id' => 19,
                'title' => 'Reading Journey',
                'subject_id' => 10,
                'class_subject_id' => 10,
                'date' => now()->toDateString(),
            ],
            [
                'id' => 20,
                'title' => 'Math Journey',
                'subject_id' => 11,
                'class_subject_id' => 11,
                'date' => now()->toDateString(),
            ],
        ]);

        DB::table('students_subjects')->insert([
            [
                'student_id' => $student->id,
                'class_subject_id' => 10,
                'status' => 'active',
            ],
            [
                'student_id' => $student->id,
                'class_subject_id' => 11,
                'status' => 'active',
            ],
        ]);

        DB::table('session_tasks')->insert([
            [
                'id' => 44,
                'class_session_id' => 19,
                'title' => 'Reading task',
                'description' => 'Task description',
                'sort' => 1,
                'default_points' => 5,
                'max_points' => 10,
            ],
            [
                'id' => 45,
                'class_session_id' => 20,
                'title' => 'Math task',
                'description' => 'Task description',
                'sort' => 1,
                'default_points' => 7,
                'max_points' => 10,
            ],
        ]);

        DB::table('task_pin_hashes')->insert([
            'user_id' => $user->id,
            'pin_hash' => Hash::make('1234'),
            'pin_unhash' => '1234',
            'updated_at' => now(),
        ]);

        DB::table('student_gifts')->insert([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'points_required' => 50,
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        Livewire::test(Journey::class, ['sessionId' => 19, 'studentId' => $student->id])
            ->call('openCompleteModal', 44)
            ->set('pinInput', '1234')
            ->call('confirmTaskCompletionWithPin');

        DB::table('academic_years')->where('id', 1)->update(['is_current' => 0]);
        DB::table('academic_years')->where('id', 2)->update(['is_current' => 1]);

        Livewire::test(Journey::class, ['sessionId' => 20, 'studentId' => $student->id])
            ->call('openCompleteModal', 45)
            ->set('pinInput', '1234')
            ->call('confirmTaskCompletionWithPin');

        $this->assertDatabaseHas('reward_totals', [
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'subject_id' => 10,
            'total_points' => 5,
        ]);

        $this->assertDatabaseHas('reward_totals', [
            'student_id' => $student->id,
            'academic_year_id' => 2,
            'subject_id' => 11,
            'total_points' => 7,
        ]);

        $this->assertSame(2, DB::table('reward_totals')->count());
    }

    public function test_journey_does_not_render_javascript_scheme_in_link_attachment_href(): void
    {
        DB::table('academic_years')->insert([
            'id' => 1,
            'title' => '2026',
            'is_current' => 1,
        ]);

        $user = User::factory()->create();
        $user->assignRole('student');

        $parent = ParentModel::create([
            'first_name' => 'Parent',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $student = Student::create([
            'first_name' => 'Test',
            'parent_id' => $parent->id,
            'user_id' => $user->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        DB::table('subjects')->insert([
            'id' => 10,
            'title' => 'Subject',
        ]);

        DB::table('class_sessions')->insert([
            'id' => 30,
            'title' => 'XSS Session',
            'subject_id' => 10,
            'class_subject_id' => 10,
            'date' => now()->toDateString(),
        ]);

        DB::table('students_subjects')->insert([
            'student_id' => $student->id,
            'class_subject_id' => 10,
            'status' => 'active',
        ]);

        DB::table('session_tasks')->insert([
            'id' => 60,
            'class_session_id' => 30,
            'title' => 'Task with malicious link',
            'description' => 'Test',
            'sort' => 1,
            'default_points' => 5,
            'max_points' => 10,
        ]);

        DB::table('attachment_files')->insert([
            'id' => 200,
            'session_task_id' => 60,
            'type' => 'link',
            'path' => 'javascript:alert(1)',
            'title' => 'Malicious Link',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(Journey::class, [
            'sessionId' => 30,
            'studentId' => $student->id,
        ]);

        $component->call('openTask', 60);

        $html = $component->html();

        $this->assertStringNotContainsString('href="javascript:alert(1)"', $html);
        $this->assertStringNotContainsString("href='javascript:alert(1)'", $html);
        $this->assertStringContainsString('Malicious Link', $html);
    }

    public function test_journey_link_attachment_opens_shared_viewer_from_task_modal(): void
    {
        DB::table('academic_years')->insert([
            'id' => 1,
            'title' => '2026',
            'is_current' => 1,
        ]);

        $user = User::factory()->create();
        $user->assignRole('student');

        $parent = ParentModel::create([
            'first_name' => 'Parent',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $student = Student::create([
            'first_name' => 'Test',
            'parent_id' => $parent->id,
            'user_id' => $user->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        DB::table('subjects')->insert([
            'id' => 10,
            'title' => 'Subject',
        ]);

        DB::table('class_sessions')->insert([
            'id' => 30,
            'title' => 'Return Target Session',
            'subject_id' => 10,
            'class_subject_id' => 10,
            'date' => now()->toDateString(),
        ]);

        DB::table('students_subjects')->insert([
            'student_id' => $student->id,
            'class_subject_id' => 10,
            'status' => 'active',
        ]);

        DB::table('session_tasks')->insert([
            'id' => 60,
            'class_session_id' => 30,
            'title' => 'Task with legacy link',
            'description' => 'Test',
            'sort' => 1,
            'default_points' => 5,
            'max_points' => 10,
        ]);

        DB::table('attachment_files')->insert([
            'id' => 200,
            'session_task_id' => 60,
            'type' => 'link',
            'path' => url('/reading/listen-read?d=7'),
            'title' => 'Legacy Listen Read',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(Journey::class, [
            'sessionId' => 30,
            'studentId' => $student->id,
        ]);

        $component->call('openTask', 60);

        $html = html_entity_decode($component->html(), ENT_QUOTES | ENT_HTML5);

        $this->assertStringContainsString('Legacy Listen Read', $html);
        $this->assertStringContainsString('Start island', $html);
        $this->assertStringContainsString('openAttachmentStudyViewer(60)', $html);
        $this->assertStringContainsString('openAttachmentStudyViewer(60, 200)', $html);
        $this->assertStringNotContainsString('/reading/listen-read?d=7&return_to=', $html);

        $component
            ->call('openAttachmentStudyViewer', 60, 200)
            ->assertDispatched('close-task-modal')
            ->assertDispatched('open-attachment-study-viewer');
    }

    public function test_journey_vocabulary_game_attachment_opens_shared_viewer_from_task_modal(): void
    {
        DB::table('academic_years')->insert([
            'id' => 1,
            'title' => '2026',
            'is_current' => 1,
        ]);

        $user = User::factory()->create();
        $user->assignRole('student');

        $parent = ParentModel::create([
            'first_name' => 'Parent',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $student = Student::create([
            'first_name' => 'Test',
            'parent_id' => $parent->id,
            'user_id' => $user->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        DB::table('subjects')->insert([
            'id' => 10,
            'title' => 'Subject',
        ]);

        DB::table('class_sessions')->insert([
            'id' => 30,
            'title' => 'Vocabulary Journey Session',
            'subject_id' => 10,
            'class_subject_id' => 10,
            'date' => now()->toDateString(),
        ]);

        DB::table('students_subjects')->insert([
            'student_id' => $student->id,
            'class_subject_id' => 10,
            'status' => 'active',
        ]);

        DB::table('session_tasks')->insert([
            'id' => 60,
            'class_session_id' => 30,
            'title' => 'Task with vocabulary game',
            'description' => 'Test',
            'sort' => 1,
            'default_points' => 5,
            'max_points' => 10,
        ]);

        $gameUrl = route('vocabulary.games.assignment', ['assignment' => 123]);

        DB::table('attachment_files')->insert([
            'id' => 201,
            'session_task_id' => 60,
            'type' => 'link',
            'path' => $gameUrl,
            'title' => 'Vocab Game: Lesson 1',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(Journey::class, [
            'sessionId' => 30,
            'studentId' => $student->id,
        ]);

        $component->call('openTask', 60);

        $html = html_entity_decode($component->html(), ENT_QUOTES | ENT_HTML5);

        $this->assertStringContainsString('Vocab Game: Lesson 1', $html);
        $this->assertStringContainsString('openAttachmentStudyViewer(60, 201)', $html);
        $this->assertStringNotContainsString('href="'.$gameUrl.'"', $html);

        $component
            ->call('openAttachmentStudyViewer', 60, 201)
            ->assertDispatched('close-task-modal')
            ->assertDispatched('open-attachment-study-viewer');
    }

    private function createJourneyAcademicYearTables(): void
    {
        if (! Schema::hasTable('academic_years')) {
            Schema::create('academic_years', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->boolean('is_current')->default(false);
                $table->timestamps();
            });
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

        if (! Schema::hasTable('subjects')) {
            Schema::create('subjects', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('class_sessions')) {
            Schema::create('class_sessions', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->unsignedBigInteger('main_daily_session_template_id')->nullable();
                $table->date('date')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('class_sessions', 'student_id')) {
            Schema::table('class_sessions', fn (Blueprint $table) => $table->unsignedBigInteger('student_id')->nullable());
        }

        if (! Schema::hasColumn('class_sessions', 'main_daily_session_template_id')) {
            Schema::table('class_sessions', fn (Blueprint $table) => $table->unsignedBigInteger('main_daily_session_template_id')->nullable());
        }

        if (! Schema::hasTable('students_subjects')) {
            Schema::create('students_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->string('status')->nullable();
            });
        }

        if (! Schema::hasTable('session_materials')) {
            Schema::create('session_materials', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_id')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('session_tasks')) {
            Schema::create('session_tasks', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('class_session_id');
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->integer('sort')->nullable();
                $table->integer('default_points')->default(0);
                $table->integer('max_points')->default(10);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('attachment_files')) {
            Schema::create('attachment_files', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_task_id')->nullable();
                $table->string('type')->nullable();
                $table->string('path')->nullable();
                $table->string('title')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('session_task_student')) {
            Schema::create('session_task_student', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_task_id');
                $table->unsignedBigInteger('student_id');
                $table->timestamp('submitted_at')->nullable();
                $table->integer('student_points')->nullable();
                $table->timestamp('review_submitted_at')->nullable();
                $table->unsignedBigInteger('review_submitted_by_id')->nullable();
                $table->string('review_submission_source')->nullable();
                $table->string('approval_source')->nullable();
                $table->unsignedBigInteger('approved_by_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->boolean('trusted_auto_approval_snapshot')->default(false);
                $table->timestamp('trusted_auto_approval_due_at')->nullable();
                $table->unsignedBigInteger('trusted_auto_approval_granted_by_id')->nullable();
                $table->string('status')->nullable();
                $table->string('flag')->nullable();
            });
        }

        if (! Schema::hasTable('student_task_approval_events')) {
            Schema::create('student_task_approval_events', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_task_student_id');
                $table->unsignedBigInteger('session_task_id');
                $table->unsignedBigInteger('student_id');
                $table->string('event_type');
                $table->unsignedBigInteger('actor_user_id')->nullable();
                $table->string('actor_role')->nullable();
                $table->string('source');
                $table->integer('points')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('student_task_approval_settings')) {
            Schema::create('student_task_approval_settings', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->boolean('trusted_auto_approval_enabled')->default(false);
                $table->unsignedBigInteger('updated_by_user_id')->nullable();
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
                $table->unsignedBigInteger('granted_by')->nullable();
                $table->timestamp('granted_at')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->text('comment')->nullable();
                $table->string('sign')->nullable();
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

        if (! Schema::hasTable('task_pin_hashes')) {
            Schema::create('task_pin_hashes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('pin_hash');
                $table->string('pin_unhash')->nullable();
                $table->timestamp('updated_at')->nullable();
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
    }
}
