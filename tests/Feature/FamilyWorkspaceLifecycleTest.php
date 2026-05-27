<?php

namespace Tests\Feature;

use App\Enums\AccountHistoryEventType;
use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Enums\LifecycleReason;
use App\Livewire\Admin\Families\FamilyWorkspace;
use App\Models\AccountHistory;
use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\ClassModel;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\InteractsWithFamilyLifecycleTables;
use Tests\TestCase;

class FamilyWorkspaceLifecycleTest extends TestCase
{
    use InteractsWithFamilyLifecycleTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamilyLifecycleTables();
    }

    public function test_confirm_lifecycle_action_reauthorizes_after_modal_open(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $admin = $this->createWorkspaceStaff('admin', [
            'families.view_workspace',
            'families.suspend',
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('openLifecycleModal', 'suspend', 'family', $parent->id)
            ->assertSet('showLifecycleModal', true)
            ->set('lifecycleReason', LifecycleReason::PaymentIssue->value);

        $admin->revokePermissionTo('families.suspend');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($admin->fresh());

        $component
            ->call('confirmLifecycleAction')
            ->assertForbidden();

        $this->assertSame(FamilyLifecycleStatus::Active->value, $parent->fresh()->lifecycle_status);
        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::FamilySuspended->value,
        ]);
    }

    public function test_activation_shortcuts_only_open_reason_modal_without_writing_state(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::PendingActivation->value);
        [$child] = $this->createChild($parent, ChildAccountStatus::PendingActivation->value);
        $admin = $this->createWorkspaceStaff('admin', [
            'families.view_workspace',
            'families.activate',
            'families.children.activate',
        ]);

        $this->actingAs($admin);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('activateChild', $child->id)
            ->assertSet('showLifecycleModal', true)
            ->assertSet('pendingLifecycleAction', 'activate')
            ->assertSet('pendingLifecycleTargetType', 'child');

        $this->assertSame(ChildAccountStatus::PendingActivation->value, $child->fresh()->account_status);
        $this->assertDatabaseCount('account_histories', 0);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('activateFamily')
            ->assertSet('showLifecycleModal', true)
            ->assertSet('pendingLifecycleAction', 'activate')
            ->assertSet('pendingLifecycleTargetType', 'family');

        $this->assertSame(FamilyLifecycleStatus::PendingActivation->value, $parent->fresh()->lifecycle_status);
        $this->assertDatabaseCount('account_histories', 0);
    }

    public function test_lifecycle_confirmation_without_reason_is_rejected_without_history(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $admin = $this->createWorkspaceStaff('admin', [
            'families.view_workspace',
            'families.suspend',
        ]);

        $this->actingAs($admin);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('openLifecycleModal', 'suspend', 'family', $parent->id)
            ->call('confirmLifecycleAction')
            ->assertHasErrors(['lifecycleReason']);

        $this->assertSame(FamilyLifecycleStatus::Active->value, $parent->fresh()->lifecycle_status);
        $this->assertDatabaseCount('account_histories', 0);
    }

    public function test_malformed_lifecycle_action_is_rejected_before_modal_opens(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $admin = $this->createWorkspaceStaff('admin', [
            'families.view_workspace',
        ]);

        $this->actingAs($admin);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('openLifecycleModal', 'destroy_everything', 'family', $parent->id)
            ->assertHasErrors(['lifecycleAction'])
            ->assertSet('showLifecycleModal', false);

        $this->assertDatabaseCount('account_histories', 0);
    }

    public function test_cross_family_child_submission_is_rejected_without_writes(): void
    {
        [$workspaceParent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        [$workspaceChild] = $this->createChild($workspaceParent, ChildAccountStatus::Active->value);
        [$outsiderParent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        [$outsiderChild] = $this->createChild($outsiderParent, ChildAccountStatus::Active->value);

        $admin = $this->createWorkspaceStaff('admin', [
            'families.view_workspace',
            'families.children.suspend',
        ]);

        $this->actingAs($admin);

        Livewire::test(FamilyWorkspace::class, ['parent' => $workspaceParent])
            ->set('pendingLifecycleAction', 'suspend')
            ->set('pendingLifecycleTargetType', 'child')
            ->set('pendingLifecycleTargetId', $outsiderChild->id)
            ->set('lifecycleReason', LifecycleReason::SupportHold->value)
            ->call('confirmLifecycleAction')
            ->assertHasErrors(['lifecycleAction']);

        $this->assertSame(ChildAccountStatus::Active->value, $workspaceChild->fresh()->account_status);
        $this->assertSame(ChildAccountStatus::Active->value, $outsiderChild->fresh()->account_status);
        $this->assertDatabaseCount('account_histories', 0);
    }

    public function test_support_actions_require_their_own_permissions(): void
    {
        [$parent, $parentUser, $child, $childUser] = $this->createActiveFamily();
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
        ]);

        $this->actingAs($support);
        $originalParentPasswordHash = $parentUser->password;
        $originalChildPasswordHash = $childUser->password;

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('sendPasswordResetLink', $parentUser->id, 'parent')
            ->assertForbidden();

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('generateNewPassword', $childUser->id, 'child')
            ->assertForbidden();

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('resendActivationEmail', $childUser->id, 'child')
            ->assertForbidden();

        $this->assertSame($originalParentPasswordHash, $parentUser->fresh()->password);
        $this->assertSame($originalChildPasswordHash, $childUser->fresh()->password);
        $this->assertSame(ChildAccountStatus::Active->value, $child->fresh()->account_status);
        $this->assertDatabaseCount('account_histories', 0);
    }

    public function test_account_history_blocks_eloquent_bulk_mutation_paths(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $history = AccountHistory::create([
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::FamilyActivated->value,
            'subject_type' => 'family',
            'subject_id' => $parent->id,
        ]);

        foreach ([
            fn () => AccountHistory::whereKey($history->id)->update(['event_type' => AccountHistoryEventType::FamilySuspended->value]),
            fn () => AccountHistory::whereKey($history->id)->delete(),
            fn () => AccountHistory::whereKey($history->id)->touch(),
            fn () => AccountHistory::whereKey($history->id)->increment('subject_id'),
            fn () => AccountHistory::query()->upsert([
                [
                    'id' => $history->id,
                    'parent_id' => $parent->id,
                    'event_type' => AccountHistoryEventType::FamilySuspended->value,
                ],
            ], ['id'], ['event_type']),
        ] as $mutation) {
            try {
                $mutation();
                $this->fail('Expected Account History bulk mutation to be blocked.');
            } catch (RuntimeException $exception) {
                $this->assertSame('Account History is append-only.', $exception->getMessage());
            }
        }

        $this->assertDatabaseHas('account_histories', [
            'id' => $history->id,
            'event_type' => AccountHistoryEventType::FamilyActivated->value,
        ]);
    }

    public function test_admin_can_update_parent_details_from_workspace_and_sync_login_email(): void
    {
        [$parent, $parentUser] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $parentUser->forceFill([
            'email' => 'mariam@example.test',
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'phone' => '01000000001',
        ])->save();
        $parent->forceFill([
            'email' => 'mariam@example.test',
            'phone' => '01000000001',
        ])->save();

        $admin = $this->createWorkspaceStaff('admin', [
            'families.view_workspace',
        ]);

        $this->actingAs($admin);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('openParentEditModal')
            ->assertSet('showParentEditModal', true)
            ->set('parentEditForm.first_name', 'Salma')
            ->set('parentEditForm.last_name', 'Adel')
            ->set('parentEditForm.email', 'salma.adel@example.test')
            ->set('parentEditForm.phone', '01000000099')
            ->call('saveParentEdit')
            ->assertSet('showParentEditModal', false)
            ->assertDispatched('family-workspace-parent-edit-close');

        $this->assertSame('Salma', $parent->fresh()->first_name);
        $this->assertSame('Adel', $parent->fresh()->last_name);
        $this->assertSame('salma.adel@example.test', $parent->fresh()->email);
        $this->assertSame('01000000099', $parent->fresh()->phone);
        $this->assertSame('salma.adel@example.test', $parentUser->fresh()->email);
        $this->assertSame('Salma', $parentUser->fresh()->first_name);
        $this->assertSame('Adel', $parentUser->fresh()->last_name);
        $this->assertSame('01000000099', $parentUser->fresh()->phone);
    }

    public function test_customer_support_cannot_open_parent_edit_modal(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('openParentEditModal')
            ->assertForbidden();
    }

    public function test_parent_edit_rejects_phone_that_matches_another_user_record(): void
    {
        [$parent, $parentUser] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $parentUser->forceFill([
            'email' => 'mariam@example.test',
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'phone' => '01000000001',
        ])->save();
        $parent->forceFill([
            'email' => 'mariam@example.test',
            'phone' => '01000000001',
        ])->save();

        User::create([
            'name' => 'conflict-user',
            'email' => 'conflict@example.test',
            'password' => Hash::make('secret-password'),
            'phone' => '01000000077',
            'status' => 'active',
        ]);

        $admin = $this->createWorkspaceStaff('admin', [
            'families.view_workspace',
        ]);

        $this->actingAs($admin);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('openParentEditModal')
            ->set('parentEditForm.phone', '01000000077')
            ->call('saveParentEdit')
            ->assertHasErrors(['parentEditForm.phone']);

        $this->assertSame('01000000001', $parent->fresh()->phone);
        $this->assertSame('01000000001', $parentUser->fresh()->phone);
    }

    public function test_parent_edit_requires_email_for_linked_parent_user_and_cancel_restores_original_value(): void
    {
        [$parent, $parentUser] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $parentUser->forceFill([
            'email' => 'mariam@example.test',
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'phone' => '01000000001',
        ])->save();
        $parent->forceFill([
            'email' => 'mariam@example.test',
            'phone' => '01000000001',
        ])->save();

        $admin = $this->createWorkspaceStaff('admin', [
            'families.view_workspace',
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('openParentEditModal')
            ->assertSet('showParentEditModal', true)
            ->set('parentEditForm.email', '')
            ->call('saveParentEdit')
            ->assertHasErrors(['parentEditForm.email'])
            ->assertSet('showParentEditModal', true);

        $this->assertSame('mariam@example.test', $parent->fresh()->email);
        $this->assertSame('mariam@example.test', $parentUser->fresh()->email);

        $component
            ->call('closeParentEditModal')
            ->assertSet('showParentEditModal', false)
            ->assertSet('parentEditForm', []);

        $component
            ->call('openParentEditModal')
            ->assertSet('parentEditForm.email', 'mariam@example.test');
    }

    public function test_workspace_renders_operational_tabs_and_placeholders_without_writes(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        [$child] = $this->createChild($parent, ChildAccountStatus::Active->value);
        $child->forceFill([
            'school_system' => 'American',
            'grade_name' => '7',
            'password' => 'legacy-password-should-not-render',
        ])->save();
        $parent->forceFill(['password' => 'legacy-parent-password-should-not-render'])->save();

        $admin = $this->createWorkspaceStaff('admin', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.reveal',
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->assertSet('activeTab', 'overview')
            ->assertSeeInOrder([
                'Overview',
                'Payments',
                'Consultation',
                'Communication',
                'Security & Log',
                'Notes',
            ])
            ->assertSee('Children')
            ->assertSee('American | Grade 7')
            ->assertSee('Chat')
            ->assertSee('Email')
            ->assertSee(route('admin.students.account', $child->id))
            ->assertSee(route('admin.students.security', $child->id))
            ->assertSee('familyWorkspaceModalBound')
            ->assertDontSee('legacy-password-should-not-render')
            ->assertDontSee('legacy-parent-password-should-not-render');

        $beforeHistoryCount = AccountHistory::query()->count();

        foreach (['notes', 'payments', 'communication'] as $tab) {
            $component
                ->call('setActiveTab', $tab)
                ->assertSet('activeTab', $tab)
                ->assertSee($tab === 'payments' ? 'Billing placeholder' : ucfirst($tab).' placeholder');
        }

        $this->assertSame($beforeHistoryCount, AccountHistory::query()->count());
        $this->assertSame(ChildAccountStatus::Active->value, $child->fresh()->account_status);
        $this->assertSame(FamilyLifecycleStatus::Active->value, $parent->fresh()->lifecycle_status);
    }

    public function test_customer_support_workspace_does_not_render_admin_only_student_domain_links(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        [$child] = $this->createChild($parent, ChildAccountStatus::Active->value);
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->assertSee('Workspace only')
            ->assertDontSee(route('admin.students.account', $child->id))
            ->assertDontSee(route('admin.students.security', $child->id))
            ->assertDontSee(route('admin.students.show_reward', $child->id));
    }

    public function test_consultation_history_is_loaded_only_when_the_tab_is_active(): void
    {
        $this->createBookingTransferLifecycleTables();

        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        [$child] = $this->createChild($parent, ChildAccountStatus::Active->value);
        $booking = Booking::create([
            'parent_name' => $parent->display_name,
            'parent_email' => $parent->email,
            'parent_phone' => $parent->phone,
            'booking_reference' => 'BK-US5-101',
            'consultation_type' => 'online',
            'consultation_date' => '2026-04-18',
            'consultation_time' => '10:30',
            'status' => 'confirmed',
            'parent_id' => $parent->id,
        ]);

        BookingChild::create([
            'booking_id' => $booking->id,
            'child_name' => $child->display_name,
            'child_age' => 11,
            'child_grade' => 6,
            'school_system' => 'British',
            'service_interests' => ['Help Me Study'],
            'consultation_status' => 'confirmed',
            'workflow_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'evaluation_outcome' => 'fit',
            'consultation_type' => 'online',
            'transfer_status' => 'transferred',
            'current_school' => 'Workspace School',
            'student_id' => $child->id,
            'scheduled_date' => '2026-04-18',
            'scheduled_time' => '10:30',
            'sort_order' => 1,
        ]);

        $admin = $this->createWorkspaceStaff('admin', [
            'families.view_workspace',
            'families.history.view',
        ]);

        $this->actingAs($admin);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->assertSet('activeTab', 'overview')
            ->assertDontSee('BK-US5-101')
            ->call('setActiveTab', 'consultation')
            ->assertSet('activeTab', 'consultation')
            ->assertSee('BK-US5-101')
            ->assertSee('Workspace School');
    }

    public function test_duplicate_current_class_history_warning_uses_grouped_counts_without_normalizing_rows(): void
    {
        $this->createClassHistoryTables();

        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        [$child] = $this->createChild($parent, ChildAccountStatus::Active->value);
        $class = ClassModel::create([
            'title' => 'Grade 6 Morning',
            'status' => 'active',
            'type' => 'main',
        ]);
        $child->forceFill(['current_class_id' => $class->id])->save();

        DB::table('student_classes_history')->insert([
            [
                'student_id' => $child->id,
                'class_id' => $class->id,
                'status' => 'current',
                'from_date' => '2026-04-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => $child->id,
                'class_id' => $class->id,
                'status' => 'current',
                'from_date' => '2026-04-02',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $admin = $this->createWorkspaceStaff('admin', [
            'families.view_workspace',
        ]);

        $this->actingAs($admin);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->assertSee('Grade 6 Morning')
            ->assertSee('Duplicate class history');

        $this->assertSame(2, DB::table('student_classes_history')
            ->where('student_id', $child->id)
            ->where('status', 'current')
            ->count());
    }

    private function createActiveFamily(): array
    {
        $parentUser = User::factory()->create([
            'status' => 'active',
            'password' => Hash::make('ParentPass123'),
            'recoverable_password_encrypted' => 'ParentPass123',
        ]);

        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'email' => 'mariam@example.test',
            'phone' => '01000000001',
            'user_id' => $parentUser->id,
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        [$child, $childUser] = $this->createChild($parent, ChildAccountStatus::Active->value, 'ChildPass123');

        return [$parent, $parentUser, $child, $childUser];
    }

    private function createFamily(?string $status): array
    {
        $user = User::factory()->create([
            'status' => $status === FamilyLifecycleStatus::Active->value ? 'active' : 'inactive',
        ]);

        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'email' => $user->email,
            'user_id' => $user->id,
            'lifecycle_status' => $status,
        ]);

        return [$parent, $user];
    }

    private function createChild(ParentModel $parent, string $status, string $recoverablePassword = 'ChildPass123'): array
    {
        $user = User::factory()->create([
            'status' => $status === ChildAccountStatus::Active->value ? 'active' : 'inactive',
            'password' => Hash::make($recoverablePassword),
            'recoverable_password_encrypted' => $recoverablePassword,
        ]);

        $child = Student::create([
            'first_name' => 'Youssef',
            'last_name' => 'Hany',
            'parent_id' => $parent->id,
            'user_id' => $user->id,
            'student_email' => $user->email,
            'status' => 'active',
            'account_status' => $status,
        ]);

        return [$child, $user];
    }

    private function createWorkspaceStaff(string $roleName, array $permissions): User
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

    private function createClassHistoryTables(): void
    {
        if (! Schema::hasTable('classes')) {
            Schema::create('classes', function ($table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->unsignedBigInteger('grade_level_id')->nullable();
                $table->string('grade_name')->nullable();
                $table->string('class_img')->nullable();
                $table->string('status')->default('active');
                $table->string('type')->default('main');
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('student_classes_history')) {
            Schema::create('student_classes_history', function ($table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('class_id');
                $table->string('status')->default('current');
                $table->date('from_date')->nullable();
                $table->date('to_date')->nullable();
                $table->timestamps();
            });
        }
    }
}
