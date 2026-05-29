<?php

namespace Tests\Feature;

use App\Enums\AccountHistoryEventType;
use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\ClassModel;
use App\Models\ParentModel;
use App\Models\Services_type;
use App\Models\Student;
use App\Models\User;
use App\Services\BookingParentIdentityResolver;
use App\Services\BookingTransferService;
use App\Support\BookingTransferReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\Support\InteractsWithFamilyLifecycleTables;
use Tests\TestCase;

class BookingTransferLifecycleInitTest extends TestCase
{
    use InteractsWithFamilyLifecycleTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createBookingTransferLifecycleTables();
        Role::findOrCreate('parent');
        Role::findOrCreate('student');

        Services_type::create([
            'title' => 'My Deen Journey',
            'value' => 'My Deen Journey',
            'active' => true,
        ]);
        \Illuminate\Support\Facades\DB::table('grade_levels')->insert([
            'id' => 1,
            'title' => 'Grade 1',
            'level_order' => 1,
            'program_id' => 10,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([
            1 => 'Quran Memorization',
            2 => 'Quranic Arabic',
            3 => 'Arabic Language',
            4 => 'Sanad Program',
            15 => 'My Deen Journey',
            16 => 'Well Being',
        ] as $subjectId => $title) {
            \Illuminate\Support\Facades\DB::table('subjects')->insert([
                'id' => $subjectId,
                'title' => $title,
                'type' => 'standard',
                'program_id' => 10,
                'code' => strtolower(str_replace(' ', '-', $title)),
                'active' => true,
                'row_status' => 'current',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Illuminate\Support\Facades\DB::table('grade_level_subjects')->insert([
                'grade_level_id' => 1,
                'subject_id' => $subjectId,
                'academic_year_id' => 1,
                'type' => 'standard',
                'status' => 'active',
                'created_by_user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function test_transfer_initializes_pending_lifecycle_and_records_history_without_transfer_email(): void
    {
        Mail::fake();

        $child = $this->createTransferReadyChild();
        $result = $this->transferService()->transferChild($child);

        $parent = ParentModel::with('user')->findOrFail($result['parent_id']);
        $student = $child->fresh()->student()->with('user')->firstOrFail();

        $this->assertSame(FamilyLifecycleStatus::PendingActivation->value, $parent->lifecycle_status);
        $this->assertFalse((bool) $parent->active);
        $this->assertSame('inactive', $parent->user?->status);
        $this->assertSame(ChildAccountStatus::PendingActivation->value, $student->account_status);
        $this->assertSame('inactive', $student->user?->status);
        $this->assertNotNull($parent->user?->recoverable_password_encrypted);
        $this->assertNotNull($student->user?->recoverable_password_encrypted);
        $this->assertSame('ToQuran', $student->user?->recoverable_password_encrypted);
        $this->assertSame($student->user?->name.'@app.toquran.org', $student->user?->email);
        $this->assertNull($parent->user?->decryp_password);
        $this->assertNull($student->user?->decryp_password);
        $this->assertArrayNotHasKey('parent_temp_password', $result);
        $this->assertArrayNotHasKey('student_temp_password', $result);

        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildTransferredIntoFamily->value,
            'subject_type' => 'child',
            'subject_id' => $student->id,
        ]);
        $this->assertDatabaseMissing('booking_child_emails', [
            'booking_child_id' => $child->id,
            'email_type' => 'transfer_welcome',
        ]);
        $this->assertDatabaseMissing('booking_child_emails', [
            'booking_child_id' => $child->id,
            'email_type' => 'transfer_admin',
        ]);

        Mail::assertNothingSent();
    }

    public function test_sibling_transfer_preserves_existing_active_family_status(): void
    {
        Mail::fake();

        $parentUser = User::factory()->create(['email' => 'mariam@example.test', 'status' => 'active']);
        $parentUser->assignRole('parent');
        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'user_id' => $parentUser->id,
            'email' => 'mariam@example.test',
            'phone' => '201000111222',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $child = $this->createTransferReadyChild([
            'parent_id' => $parent->id,
            'parent_email' => $parent->email,
            'parent_phone' => $parent->phone,
        ], [
            'child_name' => 'Jana',
        ]);

        $result = $this->transferService()->transferChild($child);
        $student = $child->fresh()->student()->with('user')->firstOrFail();

        $this->assertSame($parent->id, $result['parent_id']);
        $this->assertSame(FamilyLifecycleStatus::Active->value, $parent->fresh()->lifecycle_status);
        $this->assertTrue((bool) $parent->fresh()->active);
        $this->assertSame('active', $parentUser->fresh()->status);
        $this->assertSame(ChildAccountStatus::PendingActivation->value, $student->account_status);

        Mail::assertNothingSent();
    }

    public function test_sibling_transfer_preserves_existing_non_active_family_lifecycle_and_parent_user_status(): void
    {
        foreach ([
            FamilyLifecycleStatus::Suspended->value => 'suspended',
            FamilyLifecycleStatus::Archived->value => 'inactive',
        ] as $familyStatus => $userStatus) {
            Mail::fake();

            $parentUser = User::factory()->create([
                'email' => "mariam-{$familyStatus}@example.test",
                'status' => $userStatus,
            ]);
            $parentUser->assignRole('parent');
            $parent = ParentModel::create([
                'first_name' => 'Mariam',
                'last_name' => 'Hany',
                'user_id' => $parentUser->id,
                'email' => $parentUser->email,
                'phone' => '201000111222'.$parentUser->id,
                'lifecycle_status' => $familyStatus,
            ]);

            $child = $this->createTransferReadyChild([
                'parent_id' => $parent->id,
                'parent_email' => $parent->email,
                'parent_phone' => $parent->phone,
            ], [
                'child_name' => 'Jana '.$parentUser->id,
            ]);

            $this->transferService()->transferChild($child);

            $this->assertSame($familyStatus, $parent->fresh()->lifecycle_status);
            $this->assertSame($userStatus, $parentUser->fresh()->status);
            $this->assertSame(ChildAccountStatus::PendingActivation->value, $child->fresh()->student->account_status);

            Mail::assertNothingSent();
        }
    }

    public function test_sibling_transfer_blocks_unclassified_existing_family_without_status_sync(): void
    {
        foreach ([null, 'legacy_active'] as $index => $familyStatus) {
            Mail::fake();

            $parentUser = User::factory()->create([
                'email' => "mariam-unclassified-{$index}@example.test",
                'status' => 'active',
            ]);
            $parentUser->assignRole('parent');

            $parent = ParentModel::create([
                'first_name' => 'Mariam',
                'last_name' => 'Hany',
                'user_id' => $parentUser->id,
                'email' => $parentUser->email,
                'phone' => '201000111333'.$index,
            ]);
            DB::table('parents')
                ->where('id', $parent->id)
                ->update([
                    'lifecycle_status' => $familyStatus,
                    'active' => true,
                ]);
            $parent->refresh();

            $child = $this->createTransferReadyChild([
                'parent_id' => $parent->id,
                'parent_email' => $parent->email,
                'parent_phone' => $parent->phone,
            ], [
                'child_name' => 'Jana '.$index,
            ]);

            $this->assertSame(
                'Existing family must be classified before transfer.',
                BookingTransferReadiness::blockedReason($child)
            );

            try {
                $this->transferService()->transferChild($child);
                $this->fail('Expected transfer to block until the existing family is classified.');
            } catch (\InvalidArgumentException $exception) {
                $this->assertSame('Existing family must be classified before transfer.', $exception->getMessage());
            }

            $this->assertSame('not_transferred', $child->fresh()->transfer_status);
            $this->assertNull($child->fresh()->student_id);
            $this->assertSame($familyStatus, $parent->fresh()->lifecycle_status);
            $this->assertTrue((bool) $parent->fresh()->active);
            $this->assertSame('active', $parentUser->fresh()->status);
            $this->assertDatabaseMissing('account_histories', [
                'parent_id' => $parent->id,
                'event_type' => AccountHistoryEventType::ChildTransferredIntoFamily->value,
            ]);

            Mail::assertNothingSent();
        }
    }

    public function test_transfer_child_user_status_mapping_preserves_suspended_lifecycle_status(): void
    {
        $student = Student::create([
            'first_name' => 'Youssef',
            'status' => 'active',
            'account_status' => ChildAccountStatus::Suspended->value,
        ]);

        $method = new \ReflectionMethod($this->transferService(), 'childUserStatus');
        $method->setAccessible(true);

        $this->assertSame('suspended', $method->invoke($this->transferService(), $student));
    }

    public function test_transfer_blocks_when_same_name_child_already_has_lifecycle_managed_account(): void
    {
        Mail::fake();

        $parentUser = User::factory()->create([
            'email' => 'mariam@example.test',
            'status' => 'active',
        ]);
        $parentUser->assignRole('parent');

        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'user_id' => $parentUser->id,
            'email' => 'mariam@example.test',
            'phone' => '201000111222',
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $existingChildUser = User::factory()->create([
            'email' => 'youssef@example.test',
            'status' => 'active',
        ]);
        $existingChildUser->assignRole('student');

        $existingChild = Student::create([
            'parent_id' => $parent->id,
            'user_id' => $existingChildUser->id,
            'first_name' => 'Youssef',
            'age' => 11,
            'grade_level_id' => 1,
            'program_id' => 10,
            'current_school' => 'Current School',
            'school_system' => 'British',
            'service_type_id' => Services_type::query()->value('id'),
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        $bookingChild = $this->createTransferReadyChild([
            'parent_id' => $parent->id,
            'parent_email' => $parent->email,
            'parent_phone' => $parent->phone,
        ], [
            'child_name' => 'Youssef',
        ]);

        try {
            $this->transferService()->transferChild($bookingChild);
            $this->fail('Expected transfer to block when a lifecycle-managed child with the same name already exists.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('child identity', strtolower($exception->getMessage()));
        }

        $bookingChild->refresh();
        $existingChild->refresh();
        $existingChildUser->refresh();

        $this->assertSame('not_transferred', $bookingChild->transfer_status);
        $this->assertNull($bookingChild->student_id);
        $this->assertSame(ChildAccountStatus::Active->value, $existingChild->account_status);
        $this->assertSame('active', $existingChildUser->status);
        Mail::assertNothingSent();
    }

    private function transferService(): BookingTransferService
    {
        return new class(new BookingParentIdentityResolver) extends BookingTransferService
        {
            protected function resolvePrimaryServiceType(BookingChild $child, Booking $booking): Services_type
            {
                return Services_type::query()->firstOrFail();
            }

            protected function ensureStudentHasClass(\App\Models\Student $student, BookingChild $child, Booking $booking): ?ClassModel
            {
                return null;
            }

            protected function seedTenDefaultGifts(int $studentId): array
            {
                return ['ok' => true];
            }

            protected function seedBehaviors(int $studentId): string
            {
                return 'ok';
            }
        };
    }

    private function createTransferReadyChild(array $bookingOverrides = [], array $childOverrides = []): BookingChild
    {
        $booking = Booking::create(array_merge([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'booking_reference' => 'BK-LIFE-1001',
            'current_school' => 'Legacy School',
            'school_system' => 'British',
            'service_interest' => 'My Deen Journey',
            'status' => 'confirmed',
        ], $bookingOverrides));

        return BookingChild::create(array_merge([
            'booking_id' => $booking->id,
            'child_name' => 'Youssef',
            'child_age' => 11,
            'child_grade' => 1,
            'school_system' => 'British',
            'service_interests' => ['My Deen Journey'],
            'consultation_status' => 'confirmed',
            'workflow_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'evaluation_status' => 'fit',
            'evaluation_outcome' => 'fit',
            'consultation_type' => 'online',
            'transfer_status' => 'not_transferred',
            'current_school' => 'Current School',
            'scheduled_date' => '2026-04-12',
            'scheduled_time' => '10:30',
            'sort_order' => 1,
        ], $childOverrides));
    }
}
