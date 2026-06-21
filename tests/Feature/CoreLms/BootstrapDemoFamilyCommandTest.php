<?php

namespace Tests\Feature\CoreLms;

use App\Models\BookingChild;
use App\Models\ClassModel;
use App\Models\ClassSubject;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\StudentClassesHistory;
use App\Models\StudentsSubject;
use App\Models\TeacherSubjectClass;
use App\Models\User;
use App\Services\BookingTransferService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

class BootstrapDemoFamilyCommandTest extends TestCase
{
    use CreatesAutomatedTaskTestingSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->createAutomatedTaskSchema();
        $this->createAutomatedTaskGenerationRuntimeTables();
        $this->createDemoCommandSchema();
        $this->seedReferenceRows();
        $this->seedUsers();
        $this->seedBeginnerBookResources();
        $this->seedQuranRepetitionResources();
        $this->mockTransferService();
    }

    public function test_command_creates_demo_family_history_idempotently(): void
    {
        $exitCode = Artisan::call('toquran:bootstrap-demo-family', [
            '--confirm-db' => ':memory:',
        ]);

        $this->assertSame(0, $exitCode, Artisan::output());

        $this->assertSame(3, DB::table('booking_children')->count());
        $this->assertSame(3, DB::table('students')->count());

        $omar = Student::query()->where('first_name', 'Omar')->firstOrFail();
        $yusuf = Student::query()->where('first_name', 'Yusuf')->firstOrFail();

        $this->assertStudentSubjectStatus($omar, 2, 'inactive');
        $this->assertStudentSubjectStatus($omar, 3, 'active');
        $this->assertStudentSubjectStatus($yusuf, 2, 'active');
        $this->assertStudentSubjectStatus($yusuf, 3, 'inactive');

        foreach (Student::query()->pluck('id') as $studentId) {
            $this->assertSame(20, DB::table('student_gifts')->where('student_id', $studentId)->count());
            $this->assertSame(10, DB::table('student_gifts')->where('student_id', $studentId)->where('status', 'redeemed')->count());
            $this->assertGreaterThanOrEqual(1040, (int) DB::table('student_gift_points_history')->where('student_id', $studentId)->value('points'));
        }

        $this->assertGreaterThan(0, DB::table('class_sessions')->where('title', 'like', '[Demo]%')->count());
        $this->assertGreaterThan(0, DB::table('session_materials')->where('status', 'published')->count());
        $this->assertGreaterThan(0, DB::table('session_tasks')->where('description', 'like', '%TQDEMO-001%')->count());
        $this->assertGreaterThan(0, DB::table('attachment_files')->count());
        $watchTaskIds = DB::table('session_tasks')
            ->where('title', 'Watch the repetition video')
            ->pluck('id');

        $this->assertGreaterThan(0, $watchTaskIds->count());
        $this->assertSame(
            $watchTaskIds->count(),
            DB::table('attachment_files')->whereIn('session_task_id', $watchTaskIds)->count()
        );

        $counts = $this->demoCounts();

        $exitCode = Artisan::call('toquran:bootstrap-demo-family', [
            '--confirm-db' => ':memory:',
        ]);

        $this->assertSame(0, $exitCode, Artisan::output());

        $this->assertSame($counts, $this->demoCounts());
    }

    public function test_dry_run_does_not_write_demo_rows(): void
    {
        DB::table('bookings')->delete();

        $this->artisan('toquran:bootstrap-demo-family', [
            '--dry-run' => true,
            '--confirm-db' => ':memory:',
        ])->assertSuccessful();

        $this->assertSame(0, DB::table('bookings')->count());
        $this->assertSame(0, DB::table('students')->count());
    }

    private function createDemoCommandSchema(): void
    {
        $this->ensureColumn('users', 'status', fn (Blueprint $table) => $table->string('status')->nullable());
        $this->ensureColumn('users', 'phone', fn (Blueprint $table) => $table->string('phone')->nullable());
        $this->ensureColumn('users', 'first_name', fn (Blueprint $table) => $table->string('first_name')->nullable());
        $this->ensureColumn('users', 'last_name', fn (Blueprint $table) => $table->string('last_name')->nullable());
        $this->ensureColumn('parents', 'phone', fn (Blueprint $table) => $table->string('phone')->nullable());
        $this->ensureColumn('parents', 'user_name', fn (Blueprint $table) => $table->string('user_name')->nullable());
        $this->ensureColumn('parents', 'password', fn (Blueprint $table) => $table->string('password')->nullable());

        if (! Schema::hasTable('grade_levels')) {
            Schema::create('grade_levels', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        $this->ensureColumn('subjects', 'active', fn (Blueprint $table) => $table->boolean('active')->default(true));
        $this->ensureColumn('subjects', 'row_status', fn (Blueprint $table) => $table->string('row_status')->default('current'));
        $this->ensureColumn('grade_level_subjects', 'grade_level_id', fn (Blueprint $table) => $table->unsignedBigInteger('grade_level_id')->nullable());
        $this->ensureColumn('students', 'age', fn (Blueprint $table) => $table->unsignedTinyInteger('age')->nullable());
        $this->ensureColumn('students', 'grade_level_id', fn (Blueprint $table) => $table->unsignedBigInteger('grade_level_id')->nullable());
        $this->ensureColumn('students', 'service_type_id', fn (Blueprint $table) => $table->unsignedBigInteger('service_type_id')->nullable());
        $this->ensureColumn('classes', 'grade_level_id', fn (Blueprint $table) => $table->unsignedBigInteger('grade_level_id')->nullable());
        $this->ensureColumn('classes', 'grade_name', fn (Blueprint $table) => $table->string('grade_name')->nullable());
        $this->ensureColumn('classes', 'type', fn (Blueprint $table) => $table->string('type')->nullable());
        $this->ensureColumn('classes', 'academic_year_id', fn (Blueprint $table) => $table->unsignedBigInteger('academic_year_id')->nullable());
        $this->ensureColumn('students_subjects', 'enrolled_at', fn (Blueprint $table) => $table->date('enrolled_at')->nullable());
        $this->ensureColumn('teacher_subject_classes', 'teacher_name', fn (Blueprint $table) => $table->string('teacher_name')->nullable());
        $this->ensureColumn('teacher_subject_classes', 'grade_name', fn (Blueprint $table) => $table->string('grade_name')->nullable());
        $this->ensureColumn('teacher_subject_classes', 'subject_name', fn (Blueprint $table) => $table->string('subject_name')->nullable());
        $this->ensureColumn('teacher_subject_classes', 'assigned_at', fn (Blueprint $table) => $table->timestamp('assigned_at')->nullable());
        $this->ensureColumn('teacher_subject_classes', 'removed_at', fn (Blueprint $table) => $table->timestamp('removed_at')->nullable());

        $this->ensureColumn('session_task_student', 'review_submitted_at', fn (Blueprint $table) => $table->timestamp('review_submitted_at')->nullable());
        $this->ensureColumn('session_task_student', 'review_submitted_by_id', fn (Blueprint $table) => $table->unsignedBigInteger('review_submitted_by_id')->nullable());
        $this->ensureColumn('session_task_student', 'review_submission_source', fn (Blueprint $table) => $table->string('review_submission_source')->nullable());
        $this->ensureColumn('session_task_student', 'approval_source', fn (Blueprint $table) => $table->string('approval_source')->nullable());
        $this->ensureColumn('session_task_student', 'approved_by_id', fn (Blueprint $table) => $table->unsignedBigInteger('approved_by_id')->nullable());
        $this->ensureColumn('session_task_student', 'approved_at', fn (Blueprint $table) => $table->timestamp('approved_at')->nullable());
        $this->ensureColumn('session_task_student', 'trusted_auto_approval_snapshot', fn (Blueprint $table) => $table->boolean('trusted_auto_approval_snapshot')->default(false));
        $this->ensureColumn('session_task_student', 'trusted_auto_approval_due_at', fn (Blueprint $table) => $table->timestamp('trusted_auto_approval_due_at')->nullable());
        $this->ensureColumn('session_task_student', 'trusted_auto_approval_granted_by_id', fn (Blueprint $table) => $table->unsignedBigInteger('trusted_auto_approval_granted_by_id')->nullable());

        if (! Schema::hasTable('services_types')) {
            Schema::create('services_types', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->string('value')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table): void {
                $table->id();
                $table->string('booking_reference')->nullable()->unique();
                $table->string('parent_name')->nullable();
                $table->string('parent_email')->nullable();
                $table->string('parent_phone')->nullable();
                $table->string('child_name')->nullable();
                $table->unsignedTinyInteger('child_age')->nullable();
                $table->unsignedBigInteger('child_grade')->nullable();
                $table->string('current_school')->nullable();
                $table->string('school_system')->nullable();
                $table->string('service_interest')->nullable();
                $table->string('contact_method')->nullable();
                $table->string('consultation_type')->nullable();
                $table->string('status')->default('pending');
                $table->boolean('terms')->default(false);
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_children')) {
            Schema::create('booking_children', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('booking_id');
                $table->string('child_name')->nullable();
                $table->unsignedTinyInteger('child_age')->nullable();
                $table->unsignedBigInteger('child_grade')->nullable();
                $table->string('school_system')->nullable();
                $table->string('current_school')->nullable();
                $table->json('service_interests')->nullable();
                $table->string('consultation_type')->nullable();
                $table->string('consultation_status')->nullable();
                $table->string('evaluation_outcome')->nullable();
                $table->string('meeting_disposition')->nullable();
                $table->string('transfer_status')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('student_classes_history')) {
            Schema::create('student_classes_history', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('class_id');
                $table->date('from_date')->nullable();
                $table->date('to_date')->nullable();
                $table->string('status')->nullable();
            });
        }

        if (! Schema::hasTable('reward_points_ledger')) {
            Schema::create('reward_points_ledger', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('academic_year_id');
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('subject_id');
                $table->string('source_type');
                $table->unsignedBigInteger('source_id')->nullable();
                $table->integer('points_delta');
                $table->string('sign')->nullable();
                $table->unsignedBigInteger('granted_by');
                $table->timestamp('granted_at')->nullable();
                $table->string('comment')->nullable();
                $table->unique(['student_id', 'academic_year_id', 'source_type', 'source_id'], 'uq_rpl_student_year_source');
            });
        }

        if (! Schema::hasTable('reward_totals')) {
            Schema::create('reward_totals', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('academic_year_id');
                $table->integer('total_points');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('student_session_discipline')) {
            Schema::create('student_session_discipline', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->unsignedBigInteger('discipline_icon_id');
                $table->text('discipline_icon_path');
                $table->unsignedBigInteger('student_reward_discipline_id')->nullable();
                $table->unsignedBigInteger('class_session_id')->nullable();
                $table->unsignedBigInteger('teacher_subject_classes_id');
                $table->unsignedBigInteger('student_id');
                $table->integer('points');
                $table->text('description')->nullable();
                $table->string('type');
                $table->timestamps();
            });
        }
    }

    private function ensureColumn(string $table, string $column, callable $definition): void
    {
        if (Schema::hasTable($table) && ! Schema::hasColumn($table, $column)) {
            Schema::table($table, $definition);
        }
    }

    private function seedReferenceRows(): void
    {
        DB::table('grade_levels')->insert(['id' => 2, 'title' => 'Beginner', 'active' => true]);

        foreach ([
            1 => 'Quran Memorization',
            2 => 'Quranic Arabic',
            3 => 'Arabic Language',
            15 => 'My Deen Journey',
            16 => 'Well Being',
        ] as $id => $title) {
            DB::table('subjects')->updateOrInsert(
                ['id' => $id],
                ['title' => $title, 'active' => true, 'row_status' => 'current']
            );

            DB::table('grade_level_subjects')->updateOrInsert(
                ['id' => $id + 100],
                [
                    'grade_level_id' => 2,
                    'subject_id' => $id,
                    'academic_year_id' => 1,
                    'type' => 'standard',
                    'status' => 'active',
                ]
            );
        }

        foreach ([
            'Quran Memorization',
            'Quranic Arabic',
            'Arabic Language',
            'Islamic Studies',
            'Quran Literature',
            'My Deen Journey',
            'Sanad Ijazah',
        ] as $index => $service) {
            DB::table('services_types')->updateOrInsert(
                ['value' => $service],
                ['id' => $index + 1, 'title' => $service, 'active' => true]
            );
        }

        DB::table('task_types')->updateOrInsert(
            ['id' => 7],
            ['title' => 'Assignment', 'table_name' => 'attachment_files', 'default_points' => 5, 'max_points' => 10]
        );
    }

    private function seedUsers(): void
    {
        Role::findOrCreate('teacher');
        Role::findOrCreate('super_admin');

        $teacher = User::factory()->create([
            'email' => 'drosamaqandil@gmail.com',
            'name' => 'Demo Teacher',
            'status' => 'active',
        ]);
        $teacher->assignRole('teacher');

        $superadmin = User::factory()->create([
            'email' => 'osama.elazab22@gmail.com',
            'name' => 'Owner Superadmin',
            'status' => 'active',
        ]);
        $superadmin->assignRole('super_admin');
    }

    private function seedBeginnerBookResources(): void
    {
        $folderId = DB::table('general_library_folders')->insertGetId([
            'title' => "Tajweed Beginner's Book",
            'description' => 'Demo folder',
            'status' => 'active',
            'source_label' => 'Demo',
            'content_mode' => 'mixed',
            'sort_order' => 1,
            'created_by_user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $titles = [
            'Arabic Alphabet',
            'Letter Recognition',
            'Letter Position 1',
            'Letter Position 2',
            'Letter Position 3',
            'Connecting Letters 1',
            'Connecting Letters 2',
            'Fathah',
            'Words with Fathah',
            'Kasrah',
        ];

        for ($i = 1; $i <= 84; $i++) {
            $title = $titles[($i - 1) % count($titles)].' '.($i % 2 === 0 ? 'Video' : 'PDF');
            $path = "demo-beginner-book/file-{$i}.pdf";
            Storage::disk('public')->put($path, 'demo');

            DB::table('general_library_resources')->insert([
                'general_library_folder_id' => $folderId,
                'resource_type' => 'file',
                'title' => $title,
                'description' => 'Demo resource',
                'status' => 'active',
                'source_label' => 'Demo',
                'storage_disk' => 'public',
                'file_path' => $path,
                'original_filename' => $title.'.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => $i === 84 ? 182688592 : 1,
                'sort_order' => $i,
                'created_by_user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedQuranRepetitionResources(): void
    {
        $rootId = DB::table('general_library_folders')->insertGetId([
            'title' => 'Quran Repetition',
            'description' => 'Demo Quran repetition folder',
            'status' => 'active',
            'source_label' => 'Demo',
            'content_mode' => 'mixed',
            'sort_order' => 2,
            'created_by_user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $selections = [
            '001. Al-Faatiha' => ['Ayahs 1-3', 'Ayahs 4-6', 'Ayah 7'],
            '112. Al-Ikhlaas' => ['Full Surah'],
            '113. Al-Falaq' => ['Ayahs 1-3', 'Ayahs 4-5'],
            '114. An-Naas' => ['Ayahs 1-3', 'Ayahs 4-6'],
        ];

        foreach ($selections as $folderTitle => $resources) {
            $folderId = DB::table('general_library_folders')->insertGetId([
                'parent_id' => $rootId,
                'title' => $folderTitle,
                'description' => 'Demo surah repetition folder',
                'status' => 'active',
                'source_label' => 'Demo',
                'content_mode' => 'sources',
                'sort_order' => 1,
                'created_by_user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($resources as $index => $title) {
                DB::table('general_library_resources')->insert([
                    'general_library_folder_id' => $folderId,
                    'resource_type' => 'youtube',
                    'title' => $title,
                    'description' => 'Demo Quran repetition video',
                    'status' => 'active',
                    'source_label' => 'Demo',
                    'external_url' => 'https://www.youtube.com/watch?v=demo'.md5($folderTitle.$title),
                    'sort_order' => $index + 1,
                    'created_by_user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function mockTransferService(): void
    {
        $this->mock(BookingTransferService::class, function ($mock): void {
            $mock->shouldReceive('transferChild')
                ->andReturnUsing(function (BookingChild $child): array {
                    $booking = $child->booking;
                    $parent = ParentModel::query()->firstOrCreate(
                        ['email' => $booking->parent_email],
                        ['first_name' => 'Demo Parent', 'last_name' => 'Amina', 'phone' => $booking->parent_phone, 'active' => true]
                    );

                    $student = Student::query()->create([
                        'first_name' => explode(' ', (string) $child->child_name, 2)[0],
                        'last_name' => 'Demo',
                        'parent_id' => $parent->id,
                        'age' => $child->child_age,
                        'grade_level_id' => 2,
                        'status' => 'active',
                        'account_status' => 'active',
                    ]);

                    $class = ClassModel::query()->create([
                        'title' => $student->first_name.' - Beginner',
                        'grade_level_id' => 2,
                        'grade_name' => 'Beginner',
                        'status' => 'active',
                        'type' => 'main',
                        'academic_year_id' => 1,
                    ]);

                    $student->current_class_id = $class->id;
                    $student->save();

                    StudentClassesHistory::query()->create([
                        'student_id' => $student->id,
                        'class_id' => $class->id,
                        'from_date' => now()->toDateString(),
                        'status' => 'current',
                    ]);

                    foreach (DB::table('grade_level_subjects')->where('grade_level_id', 2)->get() as $gradeLevelSubject) {
                        $subjectId = (int) $gradeLevelSubject->subject_id;
                        $classSubject = ClassSubject::query()->create([
                            'class_id' => $class->id,
                            'grade_level_subject_id' => $gradeLevelSubject->id,
                        ]);

                        $active = in_array($subjectId, [1, 2, 15, 16], true)
                            || in_array($this->serviceForSubject($subjectId), (array) $child->service_interests, true);

                        StudentsSubject::query()->create([
                            'student_id' => $student->id,
                            'grade_level_subject_id' => $gradeLevelSubject->id,
                            'academic_year_id' => 1,
                            'enrolled_at' => now()->toDateString(),
                            'status' => $active ? 'active' : 'inactive',
                            'class_subject_id' => $classSubject->id,
                        ]);

                        TeacherSubjectClass::query()->create([
                            'user_teacher_coteacher_id' => User::query()->where('email', 'drosamaqandil@gmail.com')->value('id'),
                            'teacher_name' => 'Demo Teacher',
                            'class_subject_id' => $classSubject->id,
                            'class_id' => $class->id,
                            'class_name' => $class->title,
                            'subject_id' => $subjectId,
                            'subject_name' => DB::table('subjects')->where('id', $subjectId)->value('title'),
                            'grade_id' => 2,
                            'grade_name' => 'Beginner',
                            'status' => $active ? 'active' : 'inactive',
                            'assigned_at' => now(),
                        ]);
                    }

                    $child->student_id = $student->id;
                    $child->transfer_status = 'transferred';
                    $child->save();

                    return ['student' => $student, 'parent' => $parent];
                });
        });
    }

    private function serviceForSubject(int $subjectId): ?string
    {
        return match ($subjectId) {
            1 => 'Quran Memorization',
            2 => 'Quranic Arabic',
            3 => 'Arabic Language',
            15 => 'My Deen Journey',
            default => null,
        };
    }

    private function assertStudentSubjectStatus(Student $student, int $subjectId, string $status): void
    {
        $actual = DB::table('students_subjects')
            ->join('grade_level_subjects', 'grade_level_subjects.id', '=', 'students_subjects.grade_level_subject_id')
            ->where('students_subjects.student_id', $student->id)
            ->where('grade_level_subjects.subject_id', $subjectId)
            ->value('students_subjects.status');

        $this->assertSame($status, $actual);
    }

    /**
     * @return array<string, int>
     */
    private function demoCounts(): array
    {
        return [
            'bookings' => DB::table('bookings')->count(),
            'children' => DB::table('booking_children')->count(),
            'students' => DB::table('students')->count(),
            'sessions' => DB::table('class_sessions')->count(),
            'tasks' => DB::table('session_tasks')->count(),
            'pivots' => DB::table('session_task_student')->count(),
            'attachments' => DB::table('attachment_files')->count(),
            'gifts' => DB::table('student_gifts')->count(),
            'ledger' => DB::table('reward_points_ledger')->count(),
            'behavior' => DB::table('student_session_discipline')->count(),
        ];
    }
}
