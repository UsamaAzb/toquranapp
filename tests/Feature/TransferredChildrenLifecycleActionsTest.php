<?php

namespace Tests\Feature;

use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Enums\LifecycleReason;
use App\Livewire\Admin\Booking\TransferredChildren;
use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\InteractsWithFamilyLifecycleTables;
use Tests\TestCase;

class TransferredChildrenLifecycleActionsTest extends TestCase
{
    use InteractsWithFamilyLifecycleTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createBookingTransferLifecycleTables();
    }

    public function test_admin_can_activate_transferred_child_from_transferred_page(): void
    {
        [, $child] = $this->createTransferredFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::PendingActivation->value
        );
        $admin = $this->createTransferredLifecycleStaff('admin', [
            'families.children.activate',
        ]);

        $this->actingAs($admin);

        Livewire::test(TransferredChildren::class)
            ->call('openLifecycleModal', 'activate', 'child', $child->id)
            ->set('lifecycleReason', LifecycleReason::AdminApproved->value)
            ->call('confirmLifecycleAction')
            ->assertHasNoErrors();

        $this->assertSame(ChildAccountStatus::Active->value, $child->fresh()->account_status);
        $this->assertSame('active', $child->user->fresh()->status);

        Livewire::test(TransferredChildren::class)
            ->assertSee('Active')
            ->assertDontSee('Pending');
    }

    public function test_admin_can_activate_transferred_family_from_transferred_page(): void
    {
        [$parent] = $this->createTransferredFamily(
            FamilyLifecycleStatus::PendingActivation->value,
            ChildAccountStatus::PendingActivation->value
        );
        $admin = $this->createTransferredLifecycleStaff('admin', [
            'families.activate',
        ]);

        $this->actingAs($admin);

        Livewire::test(TransferredChildren::class)
            ->call('openLifecycleModal', 'activate', 'family', $parent->id)
            ->set('lifecycleReason', LifecycleReason::AdminApproved->value)
            ->call('confirmLifecycleAction')
            ->assertHasNoErrors();

        $this->assertSame(FamilyLifecycleStatus::Active->value, $parent->fresh()->lifecycle_status);
        $this->assertSame('active', $parent->user->fresh()->status);
    }

    private function createTransferredFamily(string $parentStatus, string $childStatus): array
    {
        $parentUser = User::factory()->create([
            'status' => $parentStatus === FamilyLifecycleStatus::Active->value ? 'active' : 'inactive',
            'password' => Hash::make('ParentPass123'),
            'recoverable_password_encrypted' => 'ParentPass123',
        ]);

        $parent = ParentModel::create([
            'first_name' => 'Salem',
            'last_name' => 'Family',
            'user_id' => $parentUser->id,
            'email' => $parentUser->email,
            'phone' => '01031141431',
            'user_name' => 'salem_parent',
            'lifecycle_status' => $parentStatus,
            'active' => $parentStatus === FamilyLifecycleStatus::Active->value,
        ]);

        $childUser = User::factory()->create([
            'status' => $childStatus === ChildAccountStatus::Active->value ? 'active' : 'inactive',
            'password' => Hash::make('ChildPass123'),
            'recoverable_password_encrypted' => 'ChildPass123',
        ]);

        $child = Student::create([
            'first_name' => 'Omar',
            'last_name' => 'Student',
            'parent_id' => $parent->id,
            'user_id' => $childUser->id,
            'student_email' => $childUser->email,
            'user_name' => 'OM101',
            'school_system' => 'IB',
            'status' => 'active',
            'account_status' => $childStatus,
        ]);

        $booking = Booking::create([
            'parent_name' => $parent->full_name,
            'parent_email' => $parent->email,
            'parent_phone' => $parent->phone,
            'booking_reference' => 'BK-LIFE-01',
            'consultation_type' => 'online',
            'consultation_date' => '2026-04-19',
            'consultation_time' => '18:30',
            'status' => 'confirmed',
            'parent_id' => $parent->id,
        ]);

        BookingChild::create([
            'booking_id' => $booking->id,
            'child_name' => 'Omar',
            'child_age' => 15,
            'child_grade' => 11,
            'school_system' => 'IB',
            'service_interests' => ['IB Private Tutoring'],
            'consultation_status' => 'confirmed',
            'workflow_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'evaluation_outcome' => 'fit',
            'consultation_type' => 'online',
            'transfer_status' => 'transferred',
            'current_school' => 'Transferred School',
            'student_id' => $child->id,
            'scheduled_date' => '2026-04-19',
            'scheduled_time' => '18:30',
            'sort_order' => 1,
        ]);

        return [$parent->fresh('user'), $child->fresh('user')];
    }

    private function createTransferredLifecycleStaff(string $roleName, array $permissions): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::findOrCreate($roleName, 'web');
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole($role);

        foreach ($permissions as $permissionName) {
            $user->givePermissionTo(Permission::findOrCreate($permissionName, 'web'));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user->fresh();
    }
}
