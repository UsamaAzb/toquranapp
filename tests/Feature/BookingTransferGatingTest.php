<?php

namespace Tests\Feature;

use App\Enums\FamilyLifecycleStatus;
use App\Livewire\Admin\Booking\BookingChildEdit;
use App\Livewire\Admin\Students\SubjectManager;
use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\ParentModel;
use App\Models\Services_type;
use App\Models\TeacherSubjectClass;
use App\Models\User;
use App\Services\BookingParentIdentityResolver;
use App\Services\BookingTransferService;
use App\Support\BookingTransferReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Livewire\Livewire;
use Mockery;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BookingTransferGatingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'array']);

        $this->createTransferTestTables();
        $this->seedTransferReferenceTables();
    }

    public function test_transfer_blocked_when_evaluation_outcome_not_fit(): void
    {
        $this->actingAs(User::factory()->create());

        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'undecided',
            'meeting_disposition' => 'completed',
        ]);

        $transferService = Mockery::mock(BookingTransferService::class);
        $transferService->shouldNotReceive('transferChild');
        $this->app->instance(BookingTransferService::class, $transferService);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->call('transfer', $child->id)
            ->assertHasErrors(['transfer']);

        $child->refresh();

        $this->assertSame('not_transferred', $child->transfer_status);
        $this->assertNull($child->student_id);
    }

    public function test_transfer_blocked_when_already_transferred(): void
    {
        $this->actingAs(User::factory()->create());

        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'transferred',
            'student_id' => 99,
        ]);

        $transferService = Mockery::mock(BookingTransferService::class);
        $transferService->shouldNotReceive('transferChild');
        $this->app->instance(BookingTransferService::class, $transferService);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->call('transfer', $child->id)
            ->assertHasErrors(['transfer']);
    }

    public function test_transfer_blocked_when_meeting_disposition_not_terminal(): void
    {
        $this->actingAs(User::factory()->create());

        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => null,
        ]);

        $transferService = Mockery::mock(BookingTransferService::class);
        $transferService->shouldNotReceive('transferChild');
        $this->app->instance(BookingTransferService::class, $transferService);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->call('transfer', $child->id)
            ->assertHasErrors(['transfer']);
    }

    public function test_transfer_succeeds_when_fit_not_transferred_and_meeting_is_terminal(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $transferService = $this->makeTransferServiceDouble();
        $this->app->instance(BookingTransferService::class, $transferService);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->call('transfer', $child->id)
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.bookings.transferred'));

        $child->refresh();
        $booking = $child->booking()->first();

        $this->assertSame('transferred', $child->transfer_status);
        $this->assertNotNull($child->student_id);
        $this->assertNotNull($booking?->parent_id);
        $this->assertDatabaseHas('students', [
            'id' => $child->student_id,
            'parent_id' => $booking?->parent_id,
        ]);
        $this->assertDatabaseHas('booking_child_audit_log', [
            'booking_child_id' => $child->id,
            'field_name' => 'transfer_status',
            'to_value' => 'transferred',
            'changed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('booking_parent_identity_resolutions', [
            'stage' => 'booking_transfer',
            'outcome' => 'create_new_parent',
            'booking_child_id' => $child->id,
            'target_parent_id' => $booking?->parent_id,
        ]);
    }

    public function test_transfer_succeeds_when_fit_pending_transfer_status_and_meeting_is_terminal(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'cancelled',
            'transfer_status' => 'pending',
        ]);

        $transferService = $this->makeTransferServiceDouble();
        $this->app->instance(BookingTransferService::class, $transferService);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->call('transfer', $child->id)
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.bookings.transferred'));

        $child->refresh();

        $this->assertSame('transferred', $child->transfer_status);
        $this->assertNotNull($child->student_id);
    }

    public function test_transfer_creates_all_grade_level_subjects_with_to_quran_core_subjects_active(): void
    {
        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $transferService = $this->makeTransferServiceDoubleWithRealClassProvisioning();

        $transferService->transferChild($child);

        $child->refresh();
        $studentId = $child->student_id;
        $this->assertNotNull($studentId);

        $gradeLevelSubjects = DB::table('grade_level_subjects')
            ->where('grade_level_id', 1)
            ->whereIn('subject_id', [1, 2, 3, 4, 15, 16, 18])
            ->pluck('id', 'subject_id');

        $this->assertCount(7, $gradeLevelSubjects);

        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $gradeLevelSubjects[1],
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $gradeLevelSubjects[15],
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $gradeLevelSubjects[16],
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $gradeLevelSubjects[2],
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $gradeLevelSubjects[3],
            'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $gradeLevelSubjects[4],
            'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $gradeLevelSubjects[18],
            'status' => 'inactive',
        ]);

        $this->assertSame(
            7,
            DB::table('students_subjects')->where('student_id', $studentId)->count()
        );

        $this->assertDatabaseHas('teacher_subject_classes', [
            'subject_id' => 1,
            'subject_name' => 'Quran Memorization',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('teacher_subject_classes', [
            'subject_id' => 2,
            'subject_name' => 'Quranic Arabic',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('teacher_subject_classes', [
            'subject_id' => 15,
            'subject_name' => 'My Deen Journey',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('teacher_subject_classes', [
            'subject_id' => 16,
            'subject_name' => 'Well Being',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('teacher_subject_classes', [
            'subject_id' => 18,
            'subject_name' => 'WTA',
            'status' => 'inactive',
        ]);
    }

    public function test_transfer_writes_current_academic_year_to_new_class_subjects_and_gifts(): void
    {
        DB::table('academic_years')->update(['is_current' => 0]);
        DB::table('academic_years')->insert([
            'id' => 2,
            'title' => 'Next Academic Year',
            'is_current' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $this->makeTransferServiceDoubleWithRealClassProvisioning(stubGifts: false)->transferChild($child);

        $studentId = (int) $child->fresh()->student_id;

        $this->assertDatabaseHas('classes', [
            'id' => DB::table('students')->where('id', $studentId)->value('current_class_id'),
            'academic_year_id' => 2,
        ]);
        $this->assertSame(7, DB::table('students_subjects')->where('student_id', $studentId)->where('academic_year_id', 2)->count());
        $this->assertSame(10, DB::table('student_gifts')->where('student_id', $studentId)->where('academic_year_id', 2)->count());
    }

    public function test_transfer_activates_subjects_selected_from_public_service_interests(): void
    {
        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
            'service_interests' => ['Arabic Language', 'Sanad Ijazah Program'],
        ]);

        $this->makeTransferServiceDoubleWithRealClassProvisioning()->transferChild($child);

        $studentId = (int) $child->fresh()->student_id;
        $gradeLevelSubjects = DB::table('grade_level_subjects')
            ->where('grade_level_id', 1)
            ->whereIn('subject_id', [3, 4])
            ->pluck('id', 'subject_id');

        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $gradeLevelSubjects[3],
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $gradeLevelSubjects[4],
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('teacher_subject_classes', [
            'subject_id' => 3,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('teacher_subject_classes', [
            'subject_id' => 4,
            'status' => 'active',
        ]);
    }

    public function test_transfer_syncs_missing_subjects_for_reused_student_with_current_class(): void
    {
        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'email' => 'mariam@example.test',
            'phone' => '201000111222',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);
        $classId = DB::table('classes')->insertGetId([
            'title' => 'Youssef - Year 6',
            'grade_level_id' => 1,
            'academic_year_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $studentId = DB::table('students')->insertGetId([
            'parent_id' => $parent->id,
            'first_name' => 'Youssef',
            'age' => 11,
            'grade_level_id' => 1,
            'program_id' => 10,
            'current_school' => 'Old School',
            'school_system' => 'British',
            'service_type_id' => Services_type::query()->value('id'),
            'current_class_id' => $classId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('student_classes_history')->insert([
            'student_id' => $studentId,
            'class_id' => $classId,
            'from_date' => now()->toDateString(),
            'status' => 'current',
        ]);

        $child = $this->createBookingChild([
            'parent_id' => $parent->id,
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
        ], [
            'student_id' => $studentId,
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $this->makeTransferServiceDoubleWithRealClassProvisioning()->transferChild($child);

        $child->refresh();
        $gradeLevelSubjects = DB::table('grade_level_subjects')
            ->where('grade_level_id', 1)
            ->whereIn('subject_id', [1, 2, 3, 4, 15, 16, 18])
            ->pluck('id', 'subject_id');

        $this->assertSame($studentId, (int) $child->student_id);
        $this->assertDatabaseHas('students', [
            'id' => $studentId,
            'current_class_id' => $classId,
        ]);
        $this->assertSame(7, DB::table('students_subjects')->where('student_id', $studentId)->count());
        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $gradeLevelSubjects[1],
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $gradeLevelSubjects[15],
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $gradeLevelSubjects[16],
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $gradeLevelSubjects[18],
            'status' => 'inactive',
        ]);
    }

    public function test_transfer_assigns_configured_default_teacher_to_new_subject_classes(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('teacher', 'web');

        $defaultTeacher = User::factory()->create([
            'name' => 'Launch Default Teacher',
            'email' => 'drosamaqandil@gmail.com',
            'status' => 'active',
        ]);
        $defaultTeacher->assignRole('teacher');
        config(['toquran.default_teacher_email' => $defaultTeacher->email]);

        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $this->makeTransferServiceDoubleWithRealClassProvisioning()->transferChild($child);

        $studentId = (int) $child->fresh()->student_id;
        $classId = (int) DB::table('students')->where('id', $studentId)->value('current_class_id');

        $this->assertDatabaseHas('teacher_subject_classes', [
            'user_teacher_coteacher_id' => $defaultTeacher->id,
            'teacher_name' => 'Launch Default Teacher',
            'class_id' => $classId,
            'subject_id' => 1,
            'subject_name' => 'Quran Memorization',
            'status' => 'active',
        ]);

        $this->assertSame(
            7,
            DB::table('teacher_subject_classes')
                ->where('user_teacher_coteacher_id', $defaultTeacher->id)
                ->where('class_id', $classId)
                ->count()
        );
    }

    public function test_transfer_blocks_same_name_legacy_student_without_explicit_student_link(): void
    {
        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'email' => 'mariam@example.test',
            'phone' => '201000111222',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);
        $studentId = DB::table('students')->insertGetId([
            'parent_id' => $parent->id,
            'first_name' => 'Youssef',
            'age' => 9,
            'grade_level_id' => 1,
            'program_id' => 10,
            'current_school' => 'Existing School',
            'school_system' => 'British',
            'service_type_id' => Services_type::query()->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $child = $this->createBookingChild([
            'parent_id' => $parent->id,
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
        ], [
            'child_name' => 'Youssef',
            'child_age' => 12,
            'current_school' => 'New Booking School',
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        try {
            $this->makeTransferServiceDoubleWithRealClassProvisioning()->transferChild($child);
            $this->fail('Expected transfer to block instead of reusing a same-name student without an explicit link.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('child identity', strtolower($exception->getMessage()));
        }

        $child->refresh();
        $student = DB::table('students')->where('id', $studentId)->first();

        $this->assertSame('not_transferred', $child->transfer_status);
        $this->assertNull($child->student_id);
        $this->assertSame(9, (int) $student->age);
        $this->assertSame('Existing School', $student->current_school);
        $this->assertSame(FamilyLifecycleStatus::Active->value, $parent->fresh()->lifecycle_status);
    }

    public function test_transfer_preserves_previous_class_history_when_reused_student_moves_class(): void
    {
        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'email' => 'mariam@example.test',
            'phone' => '201000111222',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);
        DB::table('grade_levels')->insert([
            'id' => 2,
            'title' => 'Year 7',
            'active' => 1,
            'level_order' => 2,
            'program_id' => 10,
            'code' => 'year-7',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        foreach ([1, 2, 3, 4, 15, 16, 18] as $subjectId) {
            DB::table('grade_level_subjects')->insert([
                'grade_level_id' => 2,
                'subject_id' => $subjectId,
                'academic_year_id' => 1,
                'type' => 'standard',
                'status' => 'active',
                'created_by_user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $oldClassId = DB::table('classes')->insertGetId([
            'title' => 'Youssef - Year 6',
            'grade_level_id' => 1,
            'academic_year_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $studentId = DB::table('students')->insertGetId([
            'parent_id' => $parent->id,
            'first_name' => 'Youssef',
            'age' => 12,
            'grade_level_id' => 1,
            'program_id' => 10,
            'current_school' => 'Old School',
            'school_system' => 'British',
            'service_type_id' => Services_type::query()->value('id'),
            'current_class_id' => $oldClassId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('student_classes_history')->insert([
            'student_id' => $studentId,
            'class_id' => $oldClassId,
            'from_date' => now()->subMonth()->toDateString(),
            'status' => 'current',
        ]);

        $child = $this->createBookingChild([
            'parent_id' => $parent->id,
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
        ], [
            'student_id' => $studentId,
            'child_grade' => 2,
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $this->makeTransferServiceDoubleWithRealClassProvisioning()->transferChild($child);

        $newClassId = DB::table('classes')
            ->where('title', 'Youssef - Year 7')
            ->where('grade_level_id', 2)
            ->value('id');

        $this->assertNotNull($newClassId);
        $this->assertDatabaseHas('students', [
            'id' => $studentId,
            'current_class_id' => $newClassId,
            'grade_level_id' => 2,
        ]);
        $this->assertDatabaseHas('student_classes_history', [
            'student_id' => $studentId,
            'class_id' => $oldClassId,
            'status' => 'past',
        ]);
        $this->assertDatabaseHas('student_classes_history', [
            'student_id' => $studentId,
            'class_id' => $newClassId,
            'status' => 'current',
        ]);
        $this->assertSame(
            now()->toDateString(),
            substr((string) DB::table('student_classes_history')
                ->where('student_id', $studentId)
                ->where('class_id', $oldClassId)
                ->value('to_date'), 0, 10)
        );
        $this->assertSame(
            now()->toDateString(),
            substr((string) DB::table('student_classes_history')
                ->where('student_id', $studentId)
                ->where('class_id', $newClassId)
                ->value('from_date'), 0, 10)
        );
        $this->assertSame(
            1,
            DB::table('student_classes_history')
                ->where('student_id', $studentId)
                ->where('status', 'current')
                ->count()
        );
    }

    public function test_admin_can_activate_transferred_student_subject_and_teacher_access_syncs(): void
    {
        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $this->makeTransferServiceDoubleWithRealClassProvisioning()->transferChild($child);

        $studentId = $child->fresh()->student_id;
        $wtaGradeLevelSubjectId = DB::table('grade_level_subjects')
            ->where('grade_level_id', 1)
            ->where('subject_id', 18)
            ->value('id');
        $wtaStudentSubject = DB::table('students_subjects')
            ->where('student_id', $studentId)
            ->where('grade_level_subject_id', $wtaGradeLevelSubjectId)
            ->first();

        $this->assertSame('inactive', $wtaStudentSubject->status);
        $this->assertDatabaseHas('teacher_subject_classes', [
            'class_subject_id' => $wtaStudentSubject->class_subject_id,
            'status' => 'inactive',
        ]);

        Livewire::actingAs($this->subjectManagerAdmin())
            ->test(SubjectManager::class, ['studentId' => $studentId])
            ->call('toggleSubject', $wtaStudentSubject->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('students_subjects', [
            'id' => $wtaStudentSubject->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('teacher_subject_classes', [
            'class_subject_id' => $wtaStudentSubject->class_subject_id,
            'status' => 'active',
        ]);

        DB::table('students')
            ->where('id', $studentId)
            ->update(['account_status' => 'active']);

        $visibleTeacherSubjectIds = TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', 3)
            ->availableForTeacher()
            ->withActiveStudentSubject($studentId)
            ->pluck('subject_id')
            ->map(fn ($subjectId) => (int) $subjectId)
            ->all();

        $this->assertContains(18, $visibleTeacherSubjectIds);

        Livewire::actingAs($this->subjectManagerAdmin())
            ->test(SubjectManager::class, ['studentId' => $studentId])
            ->call('toggleSubject', $wtaStudentSubject->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('students_subjects', [
            'id' => $wtaStudentSubject->id,
            'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('teacher_subject_classes', [
            'class_subject_id' => $wtaStudentSubject->class_subject_id,
            'status' => 'inactive',
        ]);
    }

    public function test_subject_access_syncs_newly_active_grade_subject_when_admin_opens_manager(): void
    {
        DB::table('subjects')->insert([
            'id' => 21,
            'title' => 'Newly Active Subject',
            'type' => 'standard',
            'program_id' => 10,
            'code' => 'new-active',
            'active' => 0,
            'row_status' => 'current',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('grade_level_subjects')->insert([
            'grade_level_id' => 1,
            'subject_id' => 21,
            'academic_year_id' => 1,
            'type' => 'standard',
            'status' => 'active',
            'created_by_user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $this->makeTransferServiceDoubleWithRealClassProvisioning()->transferChild($child);

        $studentId = $child->fresh()->student_id;
        $languageAndLiteratureGradeLevelSubjectId = DB::table('grade_level_subjects')
            ->where('grade_level_id', 1)
            ->where('subject_id', 1)
            ->value('id');
        $newGradeLevelSubjectId = DB::table('grade_level_subjects')
            ->where('grade_level_id', 1)
            ->where('subject_id', 21)
            ->value('id');

        DB::table('teacher_subject_classes')->where('subject_id', 1)->delete();
        DB::table('students_subjects')
            ->where('student_id', $studentId)
            ->where('grade_level_subject_id', $languageAndLiteratureGradeLevelSubjectId)
            ->delete();

        $this->assertDatabaseMissing('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $languageAndLiteratureGradeLevelSubjectId,
        ]);
        $this->assertDatabaseMissing('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $newGradeLevelSubjectId,
        ]);

        DB::table('subjects')->where('id', 21)->update(['active' => 1]);

        Livewire::actingAs($this->subjectManagerAdmin())
            ->test(SubjectManager::class, ['studentId' => $studentId])
            ->assertSee('Newly Active Subject');

        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $newGradeLevelSubjectId,
            'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $languageAndLiteratureGradeLevelSubjectId,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('teacher_subject_classes', [
            'subject_id' => 21,
            'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('teacher_subject_classes', [
            'subject_id' => 1,
            'status' => 'active',
        ]);
    }

    public function test_transfer_uses_only_active_current_grade_level_subject_configuration(): void
    {
        DB::table('subjects')->insert([
            'id' => 19,
            'title' => 'Retired Subject',
            'type' => 'standard',
            'program_id' => 10,
            'code' => 'retired-subject',
            'active' => 1,
            'row_status' => 'current',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('subjects')->insert([
            'id' => 20,
            'title' => 'Hidden Subject',
            'type' => 'standard',
            'program_id' => 10,
            'code' => 'hidden-subject',
            'active' => 0,
            'row_status' => 'current',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('grade_level_subjects')->insert([
            [
                'grade_level_id' => 1,
                'subject_id' => 19,
                'academic_year_id' => 1,
                'type' => 'standard',
                'status' => 'archived',
                'created_by_user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'grade_level_id' => 1,
                'subject_id' => 20,
                'academic_year_id' => 1,
                'type' => 'standard',
                'status' => 'active',
                'created_by_user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $this->makeTransferServiceDoubleWithRealClassProvisioning()->transferChild($child);

        $studentId = $child->fresh()->student_id;
        $retiredGradeLevelSubjectId = DB::table('grade_level_subjects')
            ->where('grade_level_id', 1)
            ->where('subject_id', 19)
            ->value('id');
        $hiddenGradeLevelSubjectId = DB::table('grade_level_subjects')
            ->where('grade_level_id', 1)
            ->where('subject_id', 20)
            ->value('id');

        $this->assertDatabaseMissing('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $retiredGradeLevelSubjectId,
        ]);
        $this->assertDatabaseMissing('students_subjects', [
            'student_id' => $studentId,
            'grade_level_subject_id' => $hiddenGradeLevelSubjectId,
        ]);
    }

    public function test_transfer_blocks_linked_parent_contact_drift_until_confirmed(): void
    {
        $this->actingAs(User::factory()->create());

        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'email' => 'mariam@example.test',
            'phone' => '201000111222',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $child = $this->createBookingChild([
            'parent_id' => $parent->id,
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201555666777',
        ], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $this->assertStringContainsString(
            'differs from the linked parent account',
            BookingTransferReadiness::blockedReason($child)
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('differs from the linked parent account');

        $this->makeTransferServiceDouble(stubEmailMethods: false)->transferChild($child);
    }

    public function test_confirm_linked_parent_contact_update_unblocks_sibling_transfer(): void
    {
        $this->actingAs(User::factory()->create());

        $parentUser = User::factory()->create([
            'email' => 'old.mariam@example.test',
            'phone' => '201000111222',
        ]);
        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'email' => 'mariam@example.test',
            'phone' => '201000111222',
            'user_id' => $parentUser->id,
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $child = $this->createBookingChild([
            'parent_id' => $parent->id,
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201555666777',
        ], [
            'child_name' => 'Jana',
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $transferService = $this->makeTransferServiceDouble();
        $transferService->confirmLinkedParentContactUpdate($child, 'Verified same parent by call.');

        $this->assertSame('201555666777', $parent->fresh()->phone);
        $this->assertSame('mariam@example.test', $parentUser->fresh()->email);
        $this->assertSame('201555666777', $parentUser->fresh()->phone);
        $this->assertNull(BookingTransferReadiness::blockedReason($child->fresh('booking')));
        $this->assertDatabaseHas('booking_parent_identity_resolutions', [
            'stage' => 'booking_transfer',
            'outcome' => 'update_linked_parent_contact',
            'booking_child_id' => $child->id,
            'target_parent_id' => $parent->id,
            'contact_action' => 'replace_phone',
            'resolution_note' => 'Verified same parent by call.',
        ]);

        $result = $transferService->transferChild($child->fresh('booking'));

        $this->assertSame($parent->id, $result['parent_id']);
        $this->assertSame('transferred', $child->fresh()->transfer_status);
        $this->assertDatabaseHas('booking_parent_identity_resolutions', [
            'stage' => 'booking_transfer',
            'outcome' => 'use_linked_parent',
            'booking_child_id' => $child->id,
            'target_parent_id' => $parent->id,
        ]);
    }

    public function test_confirm_linked_parent_contact_update_rejects_email_collision_with_existing_family_account(): void
    {
        $this->actingAs(User::factory()->create());

        $parentUser = User::factory()->create([
            'email' => 'old.mariam@example.test',
            'phone' => '201000111222',
        ]);
        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'email' => 'mariam@example.test',
            'phone' => '201000111222',
            'user_id' => $parentUser->id,
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);
        User::factory()->create([
            'email' => 'collision@example.test',
            'phone' => '201999888777',
        ]);

        $child = $this->createBookingChild([
            'parent_id' => $parent->id,
            'parent_email' => 'collision@example.test',
            'parent_phone' => '201555666777',
        ], [
            'child_name' => 'Jana',
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $resolver = Mockery::mock(BookingParentIdentityResolver::class);
        $resolver->shouldReceive('resolveTransferTarget')
            ->once()
            ->withArgs(fn (BookingChild $resolverChild): bool => $resolverChild->is($child))
            ->andReturn([
                'allowed' => false,
                'outcome' => 'update_linked_parent_contact',
                'target_parent_id' => $parent->id,
                'requires_contact_update' => true,
                'blocked_reason' => 'Transfer is blocked because booking contact differs from the linked parent account. Confirm a contact update before transfer.',
                'audit_payload' => [
                    'stage' => 'booking_transfer',
                    'outcome' => 'update_linked_parent_contact',
                    'booking_child_id' => $child->id,
                    'target_parent_id' => $parent->id,
                ],
            ]);
        $resolver->shouldNotReceive('recordResolution');

        try {
            (new BookingTransferService($resolver))->confirmLinkedParentContactUpdate($child, 'Verified same parent by call.');
            $this->fail('Expected linked parent contact update to reject a colliding email.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('Submitted parent email is already used by another family account.', $exception->getMessage());
        }

        $this->assertSame('mariam@example.test', $parent->fresh()->email);
        $this->assertSame('201000111222', $parent->fresh()->phone);
        $this->assertSame('old.mariam@example.test', $parentUser->fresh()->email);
        $this->assertSame('201000111222', $parentUser->fresh()->phone);
        $this->assertDatabaseMissing('booking_parent_identity_resolutions', [
            'stage' => 'booking_transfer',
            'outcome' => 'update_linked_parent_contact',
            'booking_child_id' => $child->id,
            'resolution_note' => 'Verified same parent by call.',
        ]);
    }

    public function test_transfer_links_existing_parent_when_unlinked_booking_contact_matches(): void
    {
        $this->actingAs(User::factory()->create());

        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'email' => 'mariam@example.test',
            'phone' => '201000111222',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $result = $this->makeTransferServiceDouble()->transferChild($child);

        $this->assertSame($parent->id, $result['parent_id']);
        $this->assertSame($parent->id, $child->fresh('booking')->booking->parent_id);
        $this->assertSame(1, ParentModel::query()->count());
        $this->assertDatabaseHas('booking_parent_identity_resolutions', [
            'stage' => 'booking_transfer',
            'outcome' => 'link_existing_parent_by_contact',
            'booking_child_id' => $child->id,
            'target_parent_id' => $parent->id,
        ]);
    }

    public function test_transfer_blocks_split_parent_contact_collision_and_records_audit_row(): void
    {
        $this->actingAs(User::factory()->create());

        $emailParent = ParentModel::create([
            'first_name' => 'Email',
            'last_name' => 'Parent',
            'email' => 'mariam@example.test',
            'phone' => '201000111222',
        ]);
        $phoneParent = ParentModel::create([
            'first_name' => 'Phone',
            'last_name' => 'Parent',
            'email' => 'other@example.test',
            'phone' => '201555666777',
        ]);

        $child = $this->createBookingChild([
            'parent_email' => $emailParent->email,
            'parent_phone' => $phoneParent->phone,
        ], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        try {
            $this->makeTransferServiceDouble(stubEmailMethods: false)->transferChild($child);
            $this->fail('Expected split parent contact collision to block transfer.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('different parent accounts', $exception->getMessage());
        }

        $this->assertSame('not_transferred', $child->fresh()->transfer_status);
        $this->assertDatabaseHas('booking_parent_identity_resolutions', [
            'stage' => 'booking_transfer',
            'outcome' => 'blocked_conflict',
            'booking_child_id' => $child->id,
            'conflicting_parent_id' => $emailParent->id,
            'conflict_summary' => 'Submitted email and phone match different parent accounts.',
        ]);
    }

    public function test_transfer_email_failure_does_not_rollback_records(): void
    {
        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $transferService = $this->makeTransferServiceDouble(stubEmailMethods: false);
        $transferService->transferChild($child);

        $child->refresh();
        $booking = $child->booking()->first();

        $this->assertSame('transferred', $child->transfer_status);
        $this->assertNotNull($child->student_id);
        $this->assertNotNull($booking?->parent_id);
        $this->assertDatabaseMissing('booking_child_emails', [
            'booking_child_id' => $child->id,
            'email_type' => 'transfer_welcome',
        ]);
        $this->assertDatabaseMissing('booking_child_emails', [
            'booking_child_id' => $child->id,
            'email_type' => 'transfer_admin',
        ]);
    }

    public function test_transfer_does_not_dispatch_retired_transfer_emails_after_commit(): void
    {
        $child = $this->createBookingChild([], [
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'transfer_status' => 'not_transferred',
        ]);

        $transferService = $this->makeTransferServiceDouble(stubEmailMethods: true);

        DB::transaction(function () use ($transferService, $child): void {
            $transferService->transferChild($child);
        });

        $this->assertDatabaseMissing('booking_child_emails', [
            'booking_child_id' => $child->id,
            'email_type' => 'transfer_welcome',
        ]);
        $this->assertDatabaseMissing('booking_child_emails', [
            'booking_child_id' => $child->id,
            'email_type' => 'transfer_admin',
        ]);
    }

    protected function makeTransferServiceDouble(
        bool $stubEmailMethods = true,
        ?callable $onWelcome = null,
        ?callable $onAdmin = null
    ): BookingTransferService {
        $transferService = Mockery::mock(BookingTransferService::class, [new BookingParentIdentityResolver])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $transferService->shouldReceive('resolvePrimaryServiceType')
            ->andReturn(Services_type::query()->firstOrFail());
        $transferService->shouldReceive('ensureStudentHasClass')
            ->andReturnNull();
        $transferService->shouldReceive('createOrGetUserForParent')
            ->andReturn([
                'user' => (object) ['id' => 501],
                'plain_password' => null,
                'username' => 'parent-user',
            ]);
        $transferService->shouldReceive('createOrGetUserForStudent')
            ->andReturn([
                'user' => (object) ['id' => 601],
                'plain_password' => null,
                'username' => 'student-user',
            ]);
        $transferService->shouldReceive('seedTenDefaultGifts')
            ->andReturn(['ok' => true]);
        $transferService->shouldReceive('seedBehaviors')
            ->andReturn('ok');

        if ($stubEmailMethods) {
            $transferService->shouldNotReceive('sendTransferWelcomeEmail');
            $transferService->shouldNotReceive('sendTransferAdminEmail');
        }

        return $transferService;
    }

    protected function makeTransferServiceDoubleWithRealClassProvisioning(bool $stubGifts = true): BookingTransferService
    {
        $transferService = Mockery::mock(BookingTransferService::class, [new BookingParentIdentityResolver])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $transferService->shouldReceive('resolvePrimaryServiceType')
            ->andReturn(Services_type::query()->firstOrFail());
        $transferService->shouldReceive('createOrGetUserForParent')
            ->andReturn([
                'user' => (object) ['id' => 501],
                'plain_password' => null,
                'username' => 'parent-user',
            ]);
        $transferService->shouldReceive('createOrGetUserForStudent')
            ->andReturn([
                'user' => (object) ['id' => 601],
                'plain_password' => null,
                'username' => 'student-user',
            ]);
        if ($stubGifts) {
            $transferService->shouldReceive('seedTenDefaultGifts')
                ->andReturn(['ok' => true]);
        }
        $transferService->shouldReceive('seedBehaviors')
            ->andReturn('ok');
        $transferService->shouldNotReceive('sendTransferWelcomeEmail');
        $transferService->shouldNotReceive('sendTransferAdminEmail');

        return $transferService;
    }

    protected function createBookingChild(array $bookingOverrides = [], array $childOverrides = []): BookingChild
    {
        $booking = Booking::create(array_merge([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'booking_reference' => 'BK-2001',
            'consultation_type' => 'online',
            'consultation_date' => '2026-04-12',
            'consultation_time' => '10:30',
            'follow_up_date' => null,
            'current_school' => 'Legacy School',
            'school_system' => 'British',
            'service_interest' => 'Help Me Study',
            'status' => 'confirmed',
            'notes' => 'Booking note',
        ], $bookingOverrides));

        return BookingChild::create(array_merge([
            'booking_id' => $booking->id,
            'child_name' => 'Youssef',
            'child_age' => 11,
            'child_grade' => 1,
            'school_system' => 'British',
            'service_interests' => ['Help Me Study'],
            'consultation_status' => 'confirmed',
            'workflow_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'meeting_disposition_reason' => null,
            'evaluation_status' => 'fit',
            'evaluation_outcome' => 'fit',
            'consultation_type' => 'online',
            'meeting_link' => 'https://meet.example.com/transfer-child',
            'meeting_address' => null,
            'transfer_status' => 'not_transferred',
            'followup_date' => null,
            'current_school' => 'Current School',
            'student_id' => null,
            'notes' => 'Child note',
            'scheduled_date' => '2026-04-12',
            'scheduled_time' => '10:30',
            'sort_order' => 1,
            'updated_by' => null,
        ], $childOverrides));
    }

    protected function createTransferTestTables(): void
    {
        if (! Schema::hasTable('academic_years')) {
            Schema::create('academic_years', function ($table) {
                $table->id();
                $table->string('title')->nullable();
                $table->boolean('is_current')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('users')) {
            Schema::create('users', function ($table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('email')->nullable()->unique();
                $table->string('phone')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->text('decryp_password')->nullable();
                $table->text('recoverable_password_encrypted')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }
        if (! Schema::hasColumn('users', 'first_name')) {
            Schema::table('users', fn ($table) => $table->string('first_name')->nullable());
        }
        if (! Schema::hasColumn('users', 'last_name')) {
            Schema::table('users', fn ($table) => $table->string('last_name')->nullable());
        }
        if (! Schema::hasColumn('users', 'phone')) {
            Schema::table('users', fn ($table) => $table->string('phone')->nullable());
        }
        if (! Schema::hasColumn('users', 'status')) {
            Schema::table('users', fn ($table) => $table->string('status')->nullable());
        }
        if (! Schema::hasColumn('users', 'decryp_password')) {
            Schema::table('users', fn ($table) => $table->text('decryp_password')->nullable());
        }
        if (! Schema::hasColumn('users', 'recoverable_password_encrypted')) {
            Schema::table('users', fn ($table) => $table->text('recoverable_password_encrypted')->nullable());
        }

        if (! Schema::hasTable('bookings')) {
            Schema::create('bookings', function ($table) {
                $table->id();
                $table->string('parent_name')->nullable();
                $table->string('parent_email')->nullable();
                $table->string('parent_phone')->nullable();
                $table->string('child_name')->nullable();
                $table->unsignedTinyInteger('child_age')->nullable();
                $table->unsignedInteger('child_grade')->nullable();
                $table->string('current_school')->nullable();
                $table->string('school_system')->nullable();
                $table->text('primary_challenges')->nullable();
                $table->string('service_interest')->nullable();
                $table->date('preferred_date')->nullable();
                $table->string('preferred_time')->nullable();
                $table->string('consultation_type')->nullable();
                $table->date('consultation_date')->nullable();
                $table->text('main_concerns')->nullable();
                $table->string('how_heard')->nullable();
                $table->string('status')->nullable();
                $table->text('notes')->nullable();
                $table->string('contact_method')->nullable();
                $table->string('booking_reference')->nullable();
                $table->boolean('terms')->nullable();
                $table->text('teacher_notes')->nullable();
                $table->string('consultation_time')->nullable();
                $table->boolean('transfer')->nullable()->default(false);
                $table->dateTime('follow_up_date')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->text('meeting_address')->nullable();
                $table->string('meeting_link')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_children')) {
            Schema::create('booking_children', function ($table) {
                $table->id();
                $table->unsignedBigInteger('booking_id');
                $table->string('child_name');
                $table->unsignedTinyInteger('child_age');
                $table->unsignedInteger('child_grade')->nullable();
                $table->string('school_system')->nullable();
                $table->json('service_interests');
                $table->string('consultation_status')->nullable();
                $table->string('workflow_status')->default('pending');
                $table->string('meeting_disposition')->nullable();
                $table->string('meeting_disposition_reason', 500)->nullable();
                $table->string('evaluation_status')->nullable();
                $table->string('evaluation_outcome')->default('undecided');
                $table->string('consultation_type')->default('undecided');
                $table->string('meeting_link', 500)->nullable();
                $table->text('meeting_address')->nullable();
                $table->string('transfer_status')->default('not_transferred');
                $table->dateTime('followup_date')->nullable();
                $table->string('current_school')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->text('notes')->nullable();
                $table->date('scheduled_date')->nullable();
                $table->string('scheduled_time')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_child_emails')) {
            Schema::create('booking_child_emails', function ($table) {
                $table->id();
                $table->unsignedBigInteger('booking_child_id');
                $table->string('email_type');
                $table->string('status')->default('not_sent');
                $table->dateTime('last_attempt_at')->nullable();
                $table->dateTime('last_sent_at')->nullable();
                $table->text('last_error_message')->nullable();
                $table->unsignedBigInteger('triggered_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_child_audit_log')) {
            Schema::create('booking_child_audit_log', function ($table) {
                $table->id();
                $table->unsignedBigInteger('booking_child_id');
                $table->string('field_name');
                $table->text('from_value')->nullable();
                $table->text('to_value')->nullable();
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->dateTime('changed_at');
            });
        }

        if (! Schema::hasTable('grade_levels')) {
            Schema::create('grade_levels', function ($table) {
                $table->id();
                $table->string('title');
                $table->boolean('active')->default(true);
                $table->integer('level_order')->default(0);
                $table->unsignedBigInteger('program_id')->nullable();
                $table->string('code')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('services_types')) {
            Schema::create('services_types', function ($table) {
                $table->id();
                $table->string('title');
                $table->string('value');
                $table->text('info')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('subjects')) {
            Schema::create('subjects', function ($table) {
                $table->integer('id')->primary();
                $table->string('title');
                $table->string('type')->default('standard');
                $table->unsignedBigInteger('program_id')->default(10);
                $table->string('code')->nullable();
                $table->string('icon')->nullable();
                $table->boolean('active')->default(true);
                $table->string('row_status')->default('current');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('grade_level_subjects')) {
            Schema::create('grade_level_subjects', function ($table) {
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
            Schema::create('classes', function ($table) {
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

        if (! Schema::hasTable('class_subjects')) {
            Schema::create('class_subjects', function ($table) {
                $table->id();
                $table->unsignedBigInteger('class_id');
                $table->unsignedBigInteger('grade_level_subject_id');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('student_classes_history')) {
            Schema::create('student_classes_history', function ($table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('class_id');
                $table->date('from_date');
                $table->date('to_date')->nullable();
                $table->string('status')->default('current');
            });
        }

        if (! Schema::hasTable('students_subjects')) {
            Schema::create('students_subjects', function ($table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('grade_level_subject_id');
                $table->unsignedBigInteger('academic_year_id');
                $table->date('enrolled_at');
                $table->string('status')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
            });
        }

        if (! Schema::hasTable('student_gifts')) {
            Schema::create('student_gifts', function ($table) {
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

        if (! Schema::hasTable('teacher_subject_classes')) {
            Schema::create('teacher_subject_classes', function ($table) {
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

        if (! Schema::hasTable('parents')) {
            Schema::create('parents', function ($table) {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->string('user_name')->nullable();
                $table->unsignedBigInteger('family_support_id')->nullable();
                $table->string('image')->nullable();
                $table->boolean('active')->default(true);
                $table->string('lifecycle_status')->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasColumn('parents', 'lifecycle_status')) {
            Schema::table('parents', fn ($table) => $table->string('lifecycle_status')->nullable());
        }

        if (! Schema::hasTable('students')) {
            Schema::create('students', function ($table) {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('student_email')->nullable();
                $table->string('student_phone')->nullable();
                $table->unsignedTinyInteger('age')->nullable();
                $table->unsignedBigInteger('grade_level_id')->nullable();
                $table->unsignedBigInteger('program_id')->nullable();
                $table->string('current_school')->nullable();
                $table->string('school_system')->nullable();
                $table->unsignedBigInteger('service_type_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_name')->nullable();
                $table->string('password')->nullable();
                $table->string('status')->default('active');
                $table->string('account_status')->nullable();
                $table->unsignedBigInteger('current_class_id')->nullable();
                $table->date('birth_date')->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasColumn('students', 'status')) {
            Schema::table('students', fn ($table) => $table->string('status')->default('active'));
        }
        if (! Schema::hasColumn('students', 'account_status')) {
            Schema::table('students', fn ($table) => $table->string('account_status')->nullable());
        }

        if (! Schema::hasTable('account_histories')) {
            Schema::create('account_histories', function ($table) {
                $table->id();
                $table->unsignedBigInteger('parent_id');
                $table->string('event_type', 80);
                $table->string('reason_code', 80)->nullable();
                $table->unsignedBigInteger('actor_user_id')->nullable();
                $table->string('actor_role', 50)->nullable();
                $table->string('subject_type')->default('family');
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('booking_parent_identity_resolutions')) {
            Schema::create('booking_parent_identity_resolutions', function ($table) {
                $table->id();
                $table->string('stage');
                $table->string('outcome');
                $table->unsignedBigInteger('booking_intake_review_id')->nullable();
                $table->unsignedBigInteger('booking_intake_review_child_id')->nullable();
                $table->unsignedBigInteger('booking_id')->nullable();
                $table->unsignedBigInteger('booking_child_id')->nullable();
                $table->unsignedBigInteger('matched_booking_id')->nullable();
                $table->unsignedBigInteger('target_parent_id')->nullable();
                $table->unsignedBigInteger('conflicting_parent_id')->nullable();
                $table->string('submitted_parent_email')->nullable();
                $table->string('submitted_parent_phone')->nullable();
                $table->string('previous_parent_email')->nullable();
                $table->string('previous_parent_phone')->nullable();
                $table->string('resolved_parent_email')->nullable();
                $table->string('resolved_parent_phone')->nullable();
                $table->string('contact_action')->default('none');
                $table->text('child_identity_summary')->nullable();
                $table->text('conflict_summary')->nullable();
                $table->text('resolution_note')->nullable();
                $table->unsignedBigInteger('resolved_by')->nullable();
                $table->dateTime('resolved_at');
            });
        }
    }

    protected function seedTransferReferenceTables(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('teacher', 'web');
        Role::findOrCreate('admin', 'web');

        $defaultTeacherEmail = 'default.teacher@example.test';
        if (! User::query()->whereKey(3)->exists()) {
            DB::table('users')->insert([
                'id' => 3,
                'name' => 'Default Test Teacher',
                'email' => $defaultTeacherEmail,
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $defaultTeacher = User::query()->findOrFail(3);
        $defaultTeacher->assignRole('teacher');
        config(['toquran.default_teacher_email' => $defaultTeacher->email]);

        if (! DB::table('academic_years')->where('is_current', 1)->exists()) {
            DB::table('academic_years')->insert([
                'id' => 1,
                'title' => 'Current Academic Year',
                'is_current' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (DB::table('grade_levels')->count() === 0) {
            DB::table('grade_levels')->insert([
                'title' => 'Year 6',
                'active' => 1,
                'level_order' => 1,
                'program_id' => 10,
                'code' => 'Y6',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (DB::table('services_types')->count() === 0) {
            DB::table('services_types')->insert([
                [
                    'title' => 'Quran Memorization',
                    'value' => 'Quran Memorization',
                    'info' => null,
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Quranic Arabic',
                    'value' => 'Quranic Arabic',
                    'info' => null,
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Arabic Language',
                    'value' => 'Arabic Language',
                    'info' => null,
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'My Deen Journey',
                    'value' => 'My Deen Journey',
                    'info' => null,
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Paid Parental Consultation',
                    'value' => 'Paid Parental Consultation',
                    'info' => null,
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Sanad Ijazah',
                    'value' => 'Sanad Ijazah',
                    'info' => null,
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        foreach ([
            1 => ['title' => 'Quran Memorization', 'code' => 'quran-memorization'],
            2 => ['title' => 'Quranic Arabic', 'code' => 'quranic-arabic'],
            3 => ['title' => 'Arabic Language', 'code' => 'arabic-language'],
            4 => ['title' => 'Sanad Program', 'code' => 'sanad-program'],
            15 => ['title' => 'My Deen Journey', 'code' => 'my-deen-journey'],
            16 => ['title' => 'Well Being', 'code' => 'well-being'],
            18 => ['title' => 'WTA', 'code' => 'wta'],
        ] as $subjectId => $subject) {
            DB::table('subjects')->updateOrInsert(
                ['id' => $subjectId],
                [
                    'title' => $subject['title'],
                    'type' => 'standard',
                    'program_id' => 10,
                    'code' => $subject['code'],
                    'active' => 1,
                    'row_status' => 'current',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('grade_level_subjects')->updateOrInsert(
                [
                    'grade_level_id' => 1,
                    'subject_id' => $subjectId,
                ],
                [
                    'academic_year_id' => 1,
                    'type' => 'standard',
                    'status' => 'active',
                    'created_by_user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function subjectManagerAdmin(): User
    {
        $admin = User::factory()->create([
            'status' => 'active',
        ]);

        $admin->assignRole('admin');

        return $admin;
    }
}
