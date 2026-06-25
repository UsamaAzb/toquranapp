<?php

namespace Tests\Feature;

use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Enums\AccountHistoryEventType;
use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Livewire\Admin\Families\FamilyWorkspace;
use App\Mail\ChildPasswordResetLinkMail;
use App\Mail\ParentPasswordResetLinkMail;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use App\Services\AccountHistoryService;
use App\Services\CredentialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Mockery\MockInterface;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\InteractsWithFamilyLifecycleTables;
use Tests\TestCase;

class CredentialRevealTest extends TestCase
{
    use InteractsWithFamilyLifecycleTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamilyLifecycleTables();
    }

    public function test_customer_support_can_reveal_parent_credential_and_write_history(): void
    {
        [$parent, $parentUser] = $this->createActiveFamily();
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.reveal',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('openRevealModal', $parentUser->id, 'parent')
            ->assertSet('showRevealModal', true)
            ->assertSet('revealSubjectType', 'parent')
            ->assertSet('revealMasked', true)
            ->assertSet('revealedCredential', 'ParentPass123')
            ->assertDispatched('family-workspace-reveal-open');

        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentPasswordRevealed->value,
            'subject_type' => 'parent',
            'subject_id' => $parent->id,
            'actor_user_id' => $support->id,
        ]);
    }

    public function test_customer_support_can_reveal_child_credential_and_write_history(): void
    {
        [$parent, , $child, $childUser] = $this->createActiveFamily();
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.reveal',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('openRevealModal', $childUser->id, 'child')
            ->assertSet('showRevealModal', true)
            ->assertSet('revealSubjectType', 'child')
            ->assertSet('revealMasked', true)
            ->assertSet('revealedCredential', 'ChildPass123')
            ->assertDispatched('family-workspace-reveal-open');

        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildPasswordRevealed->value,
            'subject_type' => 'child',
            'subject_id' => $child->id,
            'actor_user_id' => $support->id,
        ]);
    }

    public function test_reveal_stays_disabled_when_no_recoverable_credential_exists(): void
    {
        [$parent, $parentUser] = $this->createActiveFamily(parentPassword: null);
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.reveal',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('openRevealModal', $parentUser->id, 'parent')
            ->assertSet('showRevealModal', false)
            ->assertSet('revealedCredential', null);

        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentPasswordRevealed->value,
        ]);
    }

    public function test_unauthorized_reveal_is_denied_and_audited(): void
    {
        [$parent, $parentUser] = $this->createActiveFamily();
        $teacher = $this->createWorkspaceStaff('teacher', [
            'families.view_workspace',
        ]);

        $this->actingAs($teacher);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('openRevealModal', $parentUser->id, 'parent')
            ->assertSet('showRevealModal', false)
            ->assertSet('revealedCredential', null);

        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::CredentialRevealDenied->value,
            'subject_type' => 'parent',
            'subject_id' => $parent->id,
            'actor_user_id' => $teacher->id,
        ]);
    }

    public function test_parent_password_reset_link_uses_week14_mail_and_writes_history(): void
    {
        Mail::fake();

        [$parent, $parentUser] = $this->createActiveFamily();
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.send_reset_link',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('sendPasswordResetLink', $parentUser->id, 'parent');

        Mail::assertSent(ParentPasswordResetLinkMail::class, function (ParentPasswordResetLinkMail $mail) use ($parent, $parentUser): bool {
            return $mail->hasTo($parentUser->email)
                && $mail->parent->is($parent)
                && $mail->user->is($parentUser)
                && str_contains($mail->resetUrl, '/reset-password/');
        });

        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentPasswordResetLinkSent->value,
            'subject_type' => 'parent',
            'subject_id' => $parent->id,
            'actor_user_id' => $support->id,
        ]);
    }

    public function test_parent_password_reset_link_uses_synced_parent_user_email_after_contact_update(): void
    {
        Mail::fake();

        [$parent, $parentUser] = $this->createActiveFamily();
        $parent->forceFill(['email' => 'updated.parent@example.test'])->save();
        $parentUser->forceFill(['email' => 'updated.parent@example.test'])->save();
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.send_reset_link',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent->fresh()])
            ->call('sendPasswordResetLink', $parentUser->id, 'parent');

        Mail::assertSent(ParentPasswordResetLinkMail::class, function (ParentPasswordResetLinkMail $mail): bool {
            return $mail->hasTo('updated.parent@example.test');
        });
    }

    public function test_parent_password_reset_link_does_not_write_history_when_delivery_fails(): void
    {
        [$parent, $parentUser] = $this->createActiveFamily();
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.send_reset_link',
        ]);

        Mail::shouldReceive('to')
            ->once()
            ->with($parentUser->email, $parent->full_name)
            ->andThrow(new RuntimeException('SMTP unavailable'));

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('sendPasswordResetLink', $parentUser->id, 'parent')
            ->assertSee('Unable to send the parent reset link right now. Please try again.');

        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentPasswordResetLinkFailed->value,
            'subject_type' => 'parent',
            'subject_id' => $parent->id,
            'actor_user_id' => $support->id,
        ]);
        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentPasswordResetLinkSent->value,
            'subject_type' => 'parent',
            'subject_id' => $parent->id,
        ]);
    }

    public function test_child_password_reset_link_is_sent_to_parent_account_email_and_writes_history(): void
    {
        Mail::fake();

        [$parent, $parentUser, $child, $childUser] = $this->createActiveFamily();
        $parent->forceFill(['email' => 'stale-parent@example.test'])->save();
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.send_reset_link',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('sendPasswordResetLink', $childUser->id, 'child');

        Mail::assertSent(ChildPasswordResetLinkMail::class, function (ChildPasswordResetLinkMail $mail) use ($parentUser, $parent, $child): bool {
            return $mail->hasTo($parentUser->email)
                && $mail->student->is($child)
                && $mail->parent->is($parent);
        });

        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildPasswordResetLinkSent->value,
            'subject_type' => 'child',
            'subject_id' => $child->id,
            'actor_user_id' => $support->id,
        ]);
    }

    public function test_family_workspace_child_reset_falls_back_to_parent_record_email_when_parent_user_email_is_blank(): void
    {
        Mail::fake();

        [$parent, $parentUser, $child, $childUser] = $this->createActiveFamily();
        $parent->forceFill(['email' => 'fallback-parent@example.test'])->save();
        $parentUser->forceFill(['email' => ''])->save();
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.send_reset_link',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('sendPasswordResetLink', $childUser->id, 'child');

        Mail::assertSent(ChildPasswordResetLinkMail::class, function (ChildPasswordResetLinkMail $mail) use ($parent, $child): bool {
            return $mail->hasTo($parent->email)
                && $mail->student->is($child)
                && $mail->parent->is($parent);
        });

        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildPasswordResetLinkSent->value,
            'subject_type' => 'child',
            'subject_id' => $child->id,
            'actor_user_id' => $support->id,
        ]);
    }

    public function test_family_workspace_reset_link_buttons_target_only_the_clicked_row(): void
    {
        [$parent, $parentUser, , $childUser] = $this->createActiveFamily();
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.credentials.send_reset_link',
        ]);

        $this->actingAs($support);

        $component = Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('setActiveTab', 'security');

        $html = $component->html();

        $this->assertStringContainsString(
            "wire:target=\"sendPasswordResetLink({$parentUser->id}, 'parent')\"",
            $html
        );
        $this->assertStringContainsString(
            "wire:target=\"sendPasswordResetLink({$childUser->id}, 'child')\"",
            $html
        );
    }

    public function test_child_password_reset_link_requires_child_email_before_logging_success(): void
    {
        Mail::fake();

        [$parent, , $child, $childUser] = $this->createActiveFamily();
        $childUser->forceFill(['email' => ''])->save();

        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.send_reset_link',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('sendPasswordResetLink', $childUser->id, 'child');

        Mail::assertNothingSent();
        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildPasswordResetLinkSent->value,
            'subject_type' => 'child',
            'subject_id' => $child->id,
        ]);
    }

    public function test_child_password_reset_link_handles_delivery_failures_without_logging_success(): void
    {
        [$parent, $parentUser, $child, $childUser] = $this->createActiveFamily();
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.send_reset_link',
        ]);

        Mail::shouldReceive('to')
            ->once()
            ->with($parentUser->email, $parent->full_name)
            ->andThrow(new RuntimeException('SMTP unavailable'));

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('sendPasswordResetLink', $childUser->id, 'child')
            ->assertSee('Unable to send the child reset link right now. Please try again.');

        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildPasswordResetLinkFailed->value,
            'subject_type' => 'child',
            'subject_id' => $child->id,
            'actor_user_id' => $support->id,
        ]);
        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildPasswordResetLinkSent->value,
            'subject_type' => 'child',
            'subject_id' => $child->id,
        ]);
    }

    public function test_generate_new_password_updates_hash_recoverable_value_and_reveal_modal_state(): void
    {
        [$parent, $parentUser] = $this->createActiveFamily(parentPassword: null);
        $admin = $this->createWorkspaceStaff('admin', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.generate_password',
            'families.credentials.reveal',
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('generateNewPassword', $parentUser->id, 'parent')
            ->assertSet('showRevealModal', true)
            ->assertSet('revealSubjectType', 'parent')
            ->assertDispatched('family-workspace-reveal-open');

        $plain = $component->get('revealedCredential');

        $this->assertIsString($plain);
        $this->assertNotSame('', $plain);
        $this->assertTrue(Hash::check($plain, $parentUser->fresh()->password));
        $this->assertSame($plain, app(CredentialService::class)->reveal($parentUser->fresh()));
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentPasswordResetByAdmin->value,
            'subject_type' => 'parent',
            'subject_id' => $parent->id,
            'actor_user_id' => $admin->id,
        ]);
    }

    public function test_generate_new_password_rolls_back_when_history_write_fails(): void
    {
        [$parent, $parentUser] = $this->createActiveFamily(parentPassword: 'OriginalPass123');
        $admin = $this->createWorkspaceStaff('admin', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.generate_password',
        ]);

        $this->mock(AccountHistoryService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('record')
                ->once()
                ->andThrow(new RuntimeException('Audit write failed.'));
        });

        $this->actingAs($admin);

        try {
            Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
                ->call('generateNewPassword', $parentUser->id, 'parent');

            $this->fail('Expected generateNewPassword to throw when the audit write fails.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Audit write failed.', $exception->getMessage());
        }

        $parentUser->refresh();

        $this->assertTrue(Hash::check('OriginalPass123', $parentUser->password));
        $this->assertSame('OriginalPass123', app(CredentialService::class)->reveal($parentUser));
        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentPasswordResetByAdmin->value,
            'subject_type' => 'parent',
            'subject_id' => $parent->id,
        ]);
    }

    public function test_resend_activation_email_records_history_and_sends_for_active_subjects(): void
    {
        Mail::fake();

        [$parent, $parentUser, $child, $childUser] = $this->createActiveFamily();
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.resend_activation',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('resendActivationEmail', $parentUser->id, 'parent')
            ->assertSee('Parent activation email sent.')
            ->call('resendActivationEmail', $childUser->id, 'child')
            ->assertSee("Child activation email sent for {$child->display_name}.");

        $this->assertSame(2, \App\Models\AccountHistory::where('parent_id', $parent->id)
            ->where('event_type', AccountHistoryEventType::ActivationEmailResent->value)
            ->count());
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentActivationEmailSent->value,
            'subject_type' => 'parent',
            'subject_id' => $parent->id,
        ]);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildActivationEmailSent->value,
            'subject_type' => 'child',
            'subject_id' => $child->id,
        ]);
    }

    public function test_reveal_modal_cleanup_listener_clears_component_state(): void
    {
        [$parent, $parentUser] = $this->createActiveFamily();
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.reveal',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('openRevealModal', $parentUser->id, 'parent')
            ->assertSet('showRevealModal', true)
            ->call('handleRevealModalHidden')
            ->assertSet('showRevealModal', false)
            ->assertSet('revealedCredential', null)
            ->assertSet('revealMasked', true)
            ->assertSet('revealSubjectType', null)
            ->assertSet('revealUserId', null);
    }

    public function test_workspace_hides_account_history_without_history_permission(): void
    {
        [$parent] = $this->createActiveFamily();
        \App\Models\AccountHistory::create([
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentPasswordRevealed->value,
            'subject_type' => 'parent',
            'subject_id' => $parent->id,
        ]);

        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->assertDontSee('Security & Log')
            ->assertDontSee('Activity Log')
            ->assertDontSee('parent password revealed');
    }

    public function test_workspace_hides_account_security_without_credential_permissions(): void
    {
        [$parent] = $this->createActiveFamily();
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.history.view',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->assertSee('Log')
            ->assertDontSee('Security & Log')
            ->assertDontSee('Recoverable available')
            ->assertDontSee('Send password reset link');
    }

    public function test_workspace_shows_only_allowed_credential_actions_for_partial_permissions(): void
    {
        [$parent] = $this->createActiveFamily();
        $support = $this->createWorkspaceStaff('customer_support', [
            'families.view_workspace',
            'families.history.view',
            'families.credentials.send_reset_link',
        ]);

        $this->actingAs($support);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->call('setActiveTab', 'security')
            ->assertSee('Security & Log')
            ->assertSee('Activity Log')
            ->assertSee('Send password reset link')
            ->assertDontSeeHtml('>Reveal</button>')
            ->assertDontSee('Generate new password')
            ->assertDontSee('Resend activation email')
            ->assertDontSee('Recoverable available')
            ->assertDontSee('No recoverable credential')
            ->assertSee('Restricted');
    }

    public function test_parent_self_service_password_change_updates_recoverable_password_and_history(): void
    {
        [$parent, $parentUser] = $this->createActiveFamily(parentPassword: 'OriginalPass123');
        Role::findOrCreate('parent', 'web');
        $parentUser->assignRole('parent');
        $this->actingAs($parentUser);

        app(UpdateUserPassword::class)->update($parentUser, [
            'current_password' => 'OriginalPass123',
            'password' => 'UpdatedPass123',
            'password_confirmation' => 'UpdatedPass123',
        ]);

        $parentUser->refresh();

        $this->assertTrue(Hash::check('UpdatedPass123', $parentUser->password));
        $this->assertSame('UpdatedPass123', app(CredentialService::class)->reveal($parentUser));
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentPasswordChangedByUser->value,
            'subject_type' => 'parent',
            'subject_id' => $parent->id,
            'actor_user_id' => $parentUser->id,
        ]);
    }

    public function test_fortify_reset_completion_updates_recoverable_password_encrypted(): void
    {
        [, $parentUser] = $this->createActiveFamily(parentPassword: 'BeforeReset123');

        app(ResetUserPassword::class)->reset($parentUser, [
            'password' => 'AfterReset123',
            'password_confirmation' => 'AfterReset123',
        ]);

        $parentUser->refresh();

        $this->assertTrue(Hash::check('AfterReset123', $parentUser->password));
        $this->assertSame('AfterReset123', app(CredentialService::class)->reveal($parentUser));
    }

    public function test_child_fortify_reset_allows_four_character_password(): void
    {
        [, , , $childUser] = $this->createActiveFamily(childPassword: 'BeforeReset123');

        app(ResetUserPassword::class)->reset($childUser, [
            'password' => '1234',
            'password_confirmation' => '1234',
        ]);

        $childUser->refresh();

        $this->assertTrue(Hash::check('1234', $childUser->password));
        $this->assertSame('1234', app(CredentialService::class)->reveal($childUser));
    }

    public function test_parent_fortify_reset_keeps_default_password_length_rule(): void
    {
        [, $parentUser] = $this->createActiveFamily(parentPassword: 'BeforeReset123');

        try {
            app(ResetUserPassword::class)->reset($parentUser, [
                'password' => '1234',
                'password_confirmation' => '1234',
            ]);

            $this->fail('Parent password reset should reject short passwords.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('password', $exception->errors());
        }

        $this->assertTrue(Hash::check('BeforeReset123', $parentUser->fresh()->password));
    }

    private function createActiveFamily(?string $parentPassword = 'ParentPass123', ?string $childPassword = 'ChildPass123'): array
    {
        $parentUser = User::factory()->create([
            'status' => 'active',
            'password' => Hash::make($parentPassword ?? 'FallbackParent123'),
            'recoverable_password_encrypted' => $parentPassword,
        ]);

        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'email' => 'mariam@example.test',
            'phone' => '01000000001',
            'user_id' => $parentUser->id,
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $childUser = User::factory()->create([
            'status' => 'active',
            'email' => 'child@example.test',
            'password' => Hash::make($childPassword ?? 'FallbackChild123'),
            'recoverable_password_encrypted' => $childPassword,
        ]);

        $child = Student::create([
            'first_name' => 'Youssef',
            'last_name' => 'Hany',
            'parent_id' => $parent->id,
            'user_id' => $childUser->id,
            'student_email' => $childUser->email,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        return [$parent, $parentUser, $child, $childUser];
    }

    private function createWorkspaceStaff(string $roleName, array $permissions): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::findOrCreate($roleName, 'web');

        foreach ($permissions as $permissionName) {
            $role->givePermissionTo(Permission::findOrCreate($permissionName, 'web'));
        }

        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole($role);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }
}
