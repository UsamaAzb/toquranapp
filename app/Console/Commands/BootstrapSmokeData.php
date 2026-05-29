<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\ClassModel;
use App\Models\ClassSubject;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\StudentClassesHistory;
use App\Models\StudentsSubject;
use App\Models\TeacherSubjectClass;
use App\Models\User;
use App\Services\CredentialService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BootstrapSmokeData extends Command
{
    protected $signature = 'toquran:bootstrap-smoke-data
        {--confirm-db= : Required. Must match the active database name.}
        {--password= : Optional shared smoke-account password for local launch testing. If omitted, a one-time password is generated.}';

    protected $description = 'Create idempotent To Quran smoke users, family, class, and teacher assignment records.';

    public function handle(CredentialService $credentials): int
    {
        $database = DB::connection()->getDatabaseName();
        $confirmedDatabase = (string) $this->option('confirm-db');

        if ($confirmedDatabase === '' || $confirmedDatabase !== $database) {
            $this->error("Refusing to create smoke data. --confirm-db must match current DB [{$database}].");

            return self::FAILURE;
        }

        if ($database !== 'u504065335_to_quran') {
            $this->error("Refusing to create launch smoke data outside u504065335_to_quran. Current DB: {$database}");

            return self::FAILURE;
        }

        $tableCount = (int) (DB::selectOne(
            'SELECT COUNT(*) AS table_count FROM information_schema.tables WHERE table_schema = DATABASE()'
        )->table_count ?? 0);

        if ($tableCount < 300) {
            $this->error("Refusing to create smoke data. Current DB has only {$tableCount} tables.");

            return self::FAILURE;
        }

        $missingReferences = $this->missingReferenceData();
        if ($missingReferences !== []) {
            $this->error('Missing required reference data: '.implode(', ', $missingReferences));

            return self::FAILURE;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $password = (string) $this->option('password');
        if ($password === '') {
            $password = Str::password(12);
            $this->warn('No --password was provided. Generated a one-time smoke password for this run:');
            $this->line($password);
        }

        $result = DB::transaction(function () use ($credentials, $password): array {
            $admin = $this->createSmokeUser($credentials, $password, 'Smoke Admin', 'admin.smoke@toquran-smoke.test', '+201090000001', 'admin');
            $support = $this->createSmokeUser($credentials, $password, 'Smoke Support', 'support.smoke@toquran-smoke.test', '+201090000002', 'customer_support');
            $secondSupport = $this->createSmokeUser($credentials, $password, 'Smoke Support Two', 'support.two.smoke@toquran-smoke.test', '+201090000006', 'customer_support');
            $teacher = $this->createSmokeUser($credentials, $password, 'Smoke Quran Teacher', 'teacher.smoke@toquran-smoke.test', '+201090000003', 'teacher');

            $class = $this->createSmokeClass();
            $gradeLevelSubjectId = $this->gradeLevelSubjectId();
            $classSubject = $this->createSmokeClassSubject($class, $gradeLevelSubjectId);
            $teacherSubjectClass = $this->createSmokeTeacherSubjectClass($teacher, $class, $classSubject);
            $families = $this->createSmokeFamilies(
                credentials: $credentials,
                password: $password,
                admin: $admin,
                support: $support,
                secondSupport: $secondSupport,
                class: $class,
                classSubject: $classSubject,
                gradeLevelSubjectId: $gradeLevelSubjectId
            );

            return [
                'admin_user_id' => $admin->id,
                'support_user_id' => $support->id,
                'second_support_user_id' => $secondSupport->id,
                'teacher_user_id' => $teacher->id,
                'class_id' => $class->id,
                'class_subject_id' => $classSubject->id,
                'teacher_subject_class_id' => $teacherSubjectClass->id,
                ...$families,
            ];
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info('Smoke data created or verified.');
        $this->table(['Record', 'ID'], collect($result)->map(fn (int $id, string $record): array => [$record, $id])->all());
        $this->warn('Smoke accounts share the configured smoke password and must be removed before deployment.');

        return self::SUCCESS;
    }

    protected function missingReferenceData(): array
    {
        $required = [
            'grade_levels.id=2 (Beginner)' => ['grade_levels', 'id', 2],
            'subjects.id=1 (Quran Memorization)' => ['subjects', 'id', 1],
            'academic_years.id=1' => ['academic_years', 'id', 1],
            'school_program.id=1' => ['school_program', 'id', 1],
            'services_types.id=1 (Quran Memorization)' => ['services_types', 'id', 1],
        ];

        $missing = [];

        foreach ($required as $label => [$table, $column, $value]) {
            if (! DB::table($table)->where($column, $value)->exists()) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    protected function createSmokeUser(
        CredentialService $credentials,
        string $password,
        string $name,
        string $email,
        string $phone,
        string $role,
        string $status = 'active'
    ): User {
        Role::findOrCreate($role, 'web');

        $user = User::query()->firstOrNew(['email' => $email]);
        $nameParts = Str::of($name)->explode(' ');

        $user->forceFill([
            'name' => $name,
            'first_name' => $nameParts->first(),
            'last_name' => $nameParts->slice(1)->implode(' ') ?: null,
            'phone' => $phone,
            'status' => $status,
            'password' => bcrypt($password),
        ])->save();

        $credentials->generateAndStore($user, $password);
        $user->syncRoles([$role]);

        return $user->fresh();
    }

    protected function createSmokeFamilies(
        CredentialService $credentials,
        string $password,
        User $admin,
        User $support,
        User $secondSupport,
        ClassModel $class,
        ClassSubject $classSubject,
        int $gradeLevelSubjectId
    ): array {
        $families = [
            [
                'key' => 'active_mixed',
                'reference' => 'SMOKE-TQ-0001',
                'parent' => [
                    'first_name' => 'Smoke Parent',
                    'last_name' => 'Aisha',
                    'email' => 'parent.aisha@toquran-smoke.test',
                    'phone' => '+201090000004',
                    'user_name' => 'smoke_parent_aisha',
                    'user_status' => 'active',
                    'lifecycle_status' => 'active',
                    'support' => $support,
                ],
                'children' => [
                    [
                        'first_name' => 'Smoke Student',
                        'last_name' => 'Omar',
                        'email' => 'student.omar@toquran-smoke.test',
                        'phone' => '+201090000005',
                        'user_name' => 'smoke_student_omar',
                        'user_status' => 'active',
                        'account_status' => 'active',
                        'subject_status' => 'active',
                        'age' => 10,
                    ],
                    [
                        'first_name' => 'Smoke Student',
                        'last_name' => 'Layla',
                        'email' => 'student.layla@toquran-smoke.test',
                        'phone' => '+201090000007',
                        'user_name' => 'smoke_student_layla',
                        'user_status' => 'suspended',
                        'account_status' => 'suspended',
                        'subject_status' => 'inactive',
                        'age' => 12,
                    ],
                ],
            ],
            [
                'key' => 'pending',
                'reference' => 'SMOKE-TQ-0002',
                'parent' => [
                    'first_name' => 'Smoke Parent',
                    'last_name' => 'Bilal',
                    'email' => 'parent.bilal@toquran-smoke.test',
                    'phone' => '+201090000008',
                    'user_name' => 'smoke_parent_bilal',
                    'user_status' => 'inactive',
                    'lifecycle_status' => 'pending_activation',
                    'support' => $secondSupport,
                ],
                'children' => [
                    [
                        'first_name' => 'Smoke Student',
                        'last_name' => 'Hana',
                        'email' => 'student.hana@toquran-smoke.test',
                        'phone' => '+201090000009',
                        'user_name' => 'smoke_student_hana',
                        'user_status' => 'inactive',
                        'account_status' => 'pending_activation',
                        'subject_status' => 'inactive',
                        'age' => 9,
                    ],
                ],
            ],
            [
                'key' => 'suspended',
                'reference' => 'SMOKE-TQ-0003',
                'parent' => [
                    'first_name' => 'Smoke Parent',
                    'last_name' => 'Dina',
                    'email' => 'parent.dina@toquran-smoke.test',
                    'phone' => '+201090000010',
                    'user_name' => 'smoke_parent_dina',
                    'user_status' => 'suspended',
                    'lifecycle_status' => 'suspended',
                    'support' => $support,
                ],
                'children' => [
                    [
                        'first_name' => 'Smoke Student',
                        'last_name' => 'Yusuf',
                        'email' => 'student.yusuf@toquran-smoke.test',
                        'phone' => '+201090000011',
                        'user_name' => 'smoke_student_yusuf',
                        'user_status' => 'active',
                        'account_status' => 'active',
                        'subject_status' => 'inactive',
                        'age' => 11,
                    ],
                    [
                        'first_name' => 'Smoke Student',
                        'last_name' => 'Mariam',
                        'email' => 'student.mariam@toquran-smoke.test',
                        'phone' => '+201090000012',
                        'user_name' => 'smoke_student_mariam',
                        'user_status' => 'suspended',
                        'account_status' => 'suspended',
                        'subject_status' => 'inactive',
                        'age' => 8,
                    ],
                ],
            ],
            [
                'key' => 'archived',
                'reference' => 'SMOKE-TQ-0004',
                'parent' => [
                    'first_name' => 'Smoke Parent',
                    'last_name' => 'Karim',
                    'email' => 'parent.karim@toquran-smoke.test',
                    'phone' => '+201090000013',
                    'user_name' => 'smoke_parent_karim',
                    'user_status' => 'inactive',
                    'lifecycle_status' => 'archived',
                    'support' => $secondSupport,
                ],
                'children' => [
                    [
                        'first_name' => 'Smoke Student',
                        'last_name' => 'Nour',
                        'email' => 'student.nour@toquran-smoke.test',
                        'phone' => '+201090000014',
                        'user_name' => 'smoke_student_nour',
                        'user_status' => 'inactive',
                        'account_status' => 'archived',
                        'subject_status' => 'inactive',
                        'age' => 13,
                    ],
                ],
            ],
        ];

        $result = [];

        foreach ($families as $family) {
            $parentUser = $this->createSmokeUser(
                $credentials,
                $password,
                "{$family['parent']['first_name']} {$family['parent']['last_name']}",
                $family['parent']['email'],
                $family['parent']['phone'],
                'parent',
                $family['parent']['user_status']
            );
            $parent = $this->createSmokeParent($parentUser, $family['parent'], $password);
            $booking = $this->createSmokeBooking($admin, $parent, $family['reference']);

            $result["{$family['key']}_parent_id"] = $parent->id;
            $result["{$family['key']}_booking_id"] = $booking->id;

            foreach ($family['children'] as $childIndex => $childSpec) {
                $studentUser = $this->createSmokeUser(
                    $credentials,
                    $password,
                    "{$childSpec['first_name']} {$childSpec['last_name']}",
                    $childSpec['email'],
                    $childSpec['phone'],
                    'student',
                    $childSpec['user_status']
                );
                $student = $this->createSmokeStudent($studentUser, $parent, $class, $childSpec, $password);

                $this->createSmokeStudentClassHistory($student, $class, $childSpec['subject_status']);
                $this->createSmokeStudentSubject($student, $gradeLevelSubjectId, $classSubject, $childSpec['subject_status']);

                $bookingChild = $this->createSmokeBookingChild($admin, $booking, $student, $childSpec, $childIndex + 1);

                $result["{$family['key']}_student_".($childIndex + 1).'_id'] = $student->id;
                $result["{$family['key']}_booking_child_".($childIndex + 1).'_id'] = $bookingChild->id;
            }
        }

        return $result;
    }

    protected function createSmokeParent(User $parentUser, array $parentSpec, string $password): ParentModel
    {
        $parent = ParentModel::query()->firstOrNew(['email' => $parentSpec['email']]);
        $parent->forceFill([
            'first_name' => $parentSpec['first_name'],
            'last_name' => $parentSpec['last_name'],
            'user_id' => $parentUser->id,
            'phone' => $parentSpec['phone'],
            'password' => $password,
            'user_name' => $parentSpec['user_name'],
            'family_support_id' => $parentSpec['support']->id,
            'active' => $parentSpec['lifecycle_status'] === 'active',
            'lifecycle_status' => $parentSpec['lifecycle_status'],
        ])->save();

        return $parent->fresh();
    }

    protected function createSmokeClass(): ClassModel
    {
        $class = ClassModel::query()->firstOrNew(['title' => '[SMOKE] Quran Beginner Cohort']);
        $class->forceFill([
            'grade_level_id' => 2,
            'grade_name' => 'Beginner',
            'status' => 'active',
            'type' => 'main',
            'academic_year_id' => 1,
        ])->save();

        return $class->fresh();
    }

    protected function gradeLevelSubjectId(): int
    {
        $gradeLevelSubjectId = (int) DB::table('grade_level_subjects')
            ->where('grade_level_id', 2)
            ->where('subject_id', 1)
            ->where('academic_year_id', 1)
            ->value('id');

        if ($gradeLevelSubjectId === 0) {
            throw new RuntimeException('Missing Beginner/Quran Memorization grade-level-subject starter row.');
        }

        return $gradeLevelSubjectId;
    }

    protected function createSmokeClassSubject(ClassModel $class, int $gradeLevelSubjectId): ClassSubject
    {
        return ClassSubject::query()->firstOrCreate([
            'class_id' => $class->id,
            'grade_level_subject_id' => $gradeLevelSubjectId,
        ]);
    }

    protected function createSmokeTeacherSubjectClass(User $teacher, ClassModel $class, ClassSubject $classSubject): TeacherSubjectClass
    {
        $teacherSubjectClass = TeacherSubjectClass::query()->firstOrNew([
            'user_teacher_coteacher_id' => $teacher->id,
            'class_subject_id' => $classSubject->id,
        ]);
        $teacherSubjectClass->forceFill([
            'teacher_name' => $teacher->name,
            'grade_id' => 2,
            'grade_name' => 'Beginner',
            'class_id' => $class->id,
            'class_name' => $class->title,
            'subject_id' => 1,
            'subject_name' => 'Quran Memorization',
            'status' => 'current',
            'assigned_at' => $teacherSubjectClass->assigned_at ?: now(),
            'removed_at' => null,
        ])->save();

        return $teacherSubjectClass->fresh();
    }

    protected function createSmokeStudent(User $studentUser, ParentModel $parent, ClassModel $class, array $childSpec, string $password): Student
    {
        $student = Student::query()->firstOrNew(['student_email' => $childSpec['email']]);
        $student->forceFill([
            'first_name' => $childSpec['first_name'],
            'last_name' => $childSpec['last_name'],
            'parent_id' => $parent->id,
            'student_phone' => $childSpec['phone'],
            'school_system' => 'Egyptian',
            'grade_level_id' => 2,
            'program_id' => 1,
            'service_type_id' => 1,
            'user_id' => $studentUser->id,
            'user_name' => $childSpec['user_name'],
            'password' => $password,
            'status' => $childSpec['user_status'] === 'active' ? 'active' : 'inactive',
            'account_status' => $childSpec['account_status'],
            'current_class_id' => $class->id,
            'enrollment_date' => today(),
            'age' => $childSpec['age'],
        ])->save();

        return $student->fresh();
    }

    protected function createSmokeStudentClassHistory(Student $student, ClassModel $class, string $subjectStatus): void
    {
        StudentClassesHistory::query()->updateOrCreate([
            'student_id' => $student->id,
            'class_id' => $class->id,
        ], [
            'from_date' => today(),
            'to_date' => $subjectStatus === 'active' ? null : today(),
            'status' => $subjectStatus === 'active' ? 'current' : 'inactive',
        ]);
    }

    protected function createSmokeStudentSubject(Student $student, int $gradeLevelSubjectId, ClassSubject $classSubject, string $status): void
    {
        StudentsSubject::query()->updateOrCreate([
            'student_id' => $student->id,
            'grade_level_subject_id' => $gradeLevelSubjectId,
            'academic_year_id' => 1,
        ], [
            'enrolled_at' => today(),
            'status' => $status,
            'class_subject_id' => $classSubject->id,
        ]);
    }

    protected function createSmokeBooking(User $admin, ParentModel $parent, string $reference): Booking
    {
        $booking = Booking::query()->firstOrNew(['booking_reference' => $reference]);
        $booking->forceFill([
            'parent_name' => $parent->display_name,
            'parent_email' => $parent->email,
            'parent_phone' => $parent->phone,
            'child_name' => 'Smoke child group',
            'child_age' => 10,
            'child_grade' => 2,
            'current_school' => 'To Quran Smoke School',
            'school_system' => 'Egyptian',
            'service_interest' => 'Quran Memorization',
            'preferred_date' => today(),
            'preferred_time' => 'evening',
            'consultation_type' => 'online',
            'consultation_date' => today(),
            'consultation_time' => '18:00',
            'main_concerns' => '[SMOKE] Launch verification family.',
            'how_heard' => 'social-media',
            'status' => 'confirmed',
            'notes' => '[SMOKE] Delete before deployment.',
            'contact_method' => 'both',
            'terms' => true,
            'transfer' => true,
            'parent_id' => $parent->id,
            'meeting_link' => 'https://meet.example.test/toquran-smoke',
            'teacher_notes' => "Created by smoke bootstrap admin user #{$admin->id}.",
        ])->save();

        return $booking->fresh();
    }

    protected function createSmokeBookingChild(User $admin, Booking $booking, Student $student, array $childSpec, int $sortOrder): BookingChild
    {
        $bookingChild = BookingChild::query()->firstOrNew([
            'booking_id' => $booking->id,
            'student_id' => $student->id,
        ]);
        $bookingChild->forceFill([
            'child_name' => $student->display_name,
            'child_age' => $childSpec['age'],
            'child_grade' => 2,
            'school_system' => 'Egyptian',
            'service_interests' => ['Quran Memorization'],
            'consultation_status' => 'confirmed',
            'workflow_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'evaluation_status' => 'fit',
            'evaluation_outcome' => 'fit',
            'consultation_type' => 'online',
            'meeting_link' => 'https://meet.example.test/toquran-smoke',
            'transfer_status' => 'transferred',
            'current_school' => 'To Quran Smoke School',
            'notes' => '[SMOKE] Delete before deployment.',
            'scheduled_date' => today(),
            'scheduled_time' => '18:00',
            'sort_order' => $sortOrder,
            'updated_by' => $admin->id,
        ])->save();

        if (! $booking->student_id) {
            $booking->forceFill([
                'student_id' => $student->id,
                'child_name' => $student->display_name,
                'child_age' => $childSpec['age'],
            ])->save();
        }

        return $bookingChild->fresh();
    }
}
