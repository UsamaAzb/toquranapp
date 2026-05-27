<?php

namespace Tests\Unit;

use App\Enums\AccountHistoryEventType;
use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Enums\LifecycleReason;
use App\Jobs\SendChildActivationEmailJob;
use App\Jobs\SendParentActivationEmailJob;
use App\Models\EmailDeliveryClaim;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use App\Services\AccountHistoryService;
use App\Services\CredentialService;
use App\Services\EmailDeliveryClaimService;
use App\Services\FamilyLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use InvalidArgumentException;
use RuntimeException;
use Tests\Support\InteractsWithFamilyLifecycleTables;
use Tests\TestCase;

class FamilyLifecycleServiceTest extends TestCase
{
    use InteractsWithFamilyLifecycleTables;
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamilyLifecycleTables();
        $this->actor = User::factory()->create(['status' => 'active']);
        $this->seedFamilyLifecyclePermissions($this->actor);
        $this->actingAs($this->actor);
    }

    public function test_family_activation_queues_parent_and_already_active_child_activation_email(): void
    {
        Queue::fake();

        [$parent, $parentUser] = $this->createFamily(FamilyLifecycleStatus::PendingActivation->value);
        [$child] = $this->createChild($parent, ChildAccountStatus::Active->value);

        app(FamilyLifecycleService::class)->activateFamily(
            $parent,
            LifecycleReason::SetupComplete->value,
            $this->actor->id,
            'admin'
        );

        $this->assertSame(FamilyLifecycleStatus::Active->value, $parent->fresh()->lifecycle_status);
        $this->assertSame('active', $parentUser->fresh()->status);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::FamilyActivated->value,
            'reason_code' => LifecycleReason::SetupComplete->value,
        ]);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentActivationEmailQueued->value,
            'subject_type' => 'parent',
        ]);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildActivationEmailQueued->value,
            'subject_type' => 'child',
            'subject_id' => $child->id,
        ]);

        Queue::assertPushed(SendParentActivationEmailJob::class);
        Queue::assertPushed(SendChildActivationEmailJob::class);
    }

    public function test_family_activation_requires_linked_parent_user_before_state_change(): void
    {
        Queue::fake();

        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'email' => 'missing-user-parent@example.test',
            'lifecycle_status' => FamilyLifecycleStatus::PendingActivation->value,
        ]);

        try {
            app(FamilyLifecycleService::class)->activateFamily(
                $parent,
                LifecycleReason::SetupComplete->value,
                $this->actor->id,
                'admin'
            );

            $this->fail('Expected activation to reject a family without a linked parent user.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('Family must have a linked parent user before activation.', $exception->getMessage());
        }

        $this->assertSame(FamilyLifecycleStatus::PendingActivation->value, $parent->fresh()->lifecycle_status);
        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::FamilyActivated->value,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_family_activation_requires_linked_users_for_already_active_children(): void
    {
        Queue::fake();

        [$parent] = $this->createFamily(FamilyLifecycleStatus::PendingActivation->value);
        $child = Student::create([
            'first_name' => 'Youssef',
            'parent_id' => $parent->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::Active->value,
        ]);

        try {
            app(FamilyLifecycleService::class)->activateFamily(
                $parent,
                LifecycleReason::SetupComplete->value,
                $this->actor->id,
                'admin'
            );

            $this->fail('Expected activation to reject an active child without a linked user.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('Child must have a linked user before activation.', $exception->getMessage());
        }

        $this->assertSame(FamilyLifecycleStatus::PendingActivation->value, $parent->fresh()->lifecycle_status);
        $this->assertSame(ChildAccountStatus::Active->value, $child->fresh()->account_status);
        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::FamilyActivated->value,
        ]);
        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildActivationEmailQueued->value,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_child_activation_while_family_pending_does_not_queue_child_email(): void
    {
        Queue::fake();

        [$parent] = $this->createFamily(FamilyLifecycleStatus::PendingActivation->value);
        [$child, $childUser] = $this->createChild($parent, ChildAccountStatus::PendingActivation->value);

        app(FamilyLifecycleService::class)->activateChild(
            $child,
            LifecycleReason::SetupComplete->value,
            $this->actor->id,
            'admin'
        );

        $this->assertSame(ChildAccountStatus::Active->value, $child->fresh()->account_status);
        $this->assertSame('active', $childUser->fresh()->status);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildActivated->value,
            'reason_code' => LifecycleReason::SetupComplete->value,
        ]);
        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildActivationEmailQueued->value,
        ]);

        Queue::assertNotPushed(SendChildActivationEmailJob::class);
    }

    public function test_child_activation_requires_linked_user_before_state_change(): void
    {
        Queue::fake();

        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $child = Student::create([
            'first_name' => 'Youssef',
            'parent_id' => $parent->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::PendingActivation->value,
        ]);

        try {
            app(FamilyLifecycleService::class)->activateChild(
                $child,
                LifecycleReason::SetupComplete->value,
                $this->actor->id,
                'admin'
            );

            $this->fail('Expected child activation to reject a child without a linked user.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('Child must have a linked user before activation.', $exception->getMessage());
        }

        $this->assertSame(ChildAccountStatus::PendingActivation->value, $child->fresh()->account_status);
        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildActivated->value,
            'subject_id' => $child->id,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_restore_returns_prior_status_without_activation_email(): void
    {
        Queue::fake();

        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);

        $service = app(FamilyLifecycleService::class);
        $service->archiveFamily($parent, LifecycleReason::AdminDecision->value, $this->actor->id, 'admin');
        $service->restoreFamily($parent->fresh(), LifecycleReason::ArchivedByMistake->value, $this->actor->id, 'admin');

        $this->assertSame(FamilyLifecycleStatus::Active->value, $parent->fresh()->lifecycle_status);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::FamilyRestored->value,
            'reason_code' => LifecycleReason::ArchivedByMistake->value,
            'new_value' => FamilyLifecycleStatus::Active->value,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_family_suspension_does_not_cascade_child_user_status(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        [, $childUser] = $this->createChild($parent, ChildAccountStatus::Active->value);

        app(FamilyLifecycleService::class)->suspendFamily(
            $parent,
            LifecycleReason::SupportHold->value,
            $this->actor->id,
            'admin'
        );

        $this->assertSame(FamilyLifecycleStatus::Suspended->value, $parent->fresh()->lifecycle_status);
        $this->assertSame('active', $childUser->fresh()->status);
    }

    public function test_invalid_family_activation_transition_is_rejected(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Family lifecycle transition not allowed from 'active'.");

        app(FamilyLifecycleService::class)->activateFamily(
            $parent,
            LifecycleReason::SetupComplete->value,
            $this->actor->id,
            'admin'
        );
    }

    public function test_unclassified_family_status_uses_clear_transition_diagnostic(): void
    {
        [$parent] = $this->createFamily(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Family lifecycle transition not allowed from 'unclassified'.");

        app(FamilyLifecycleService::class)->activateFamily(
            $parent,
            LifecycleReason::SetupComplete->value,
            $this->actor->id,
            'admin'
        );
    }

    public function test_invalid_reason_for_lifecycle_action_is_rejected(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::PendingActivation->value);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid reason 'support_hold' for action 'activate'.");

        app(FamilyLifecycleService::class)->activateFamily(
            $parent,
            LifecycleReason::SupportHold->value,
            $this->actor->id,
            'admin'
        );
    }

    public function test_blank_reason_is_rejected_before_any_state_change(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid reason '' for action 'suspend'.");

        app(FamilyLifecycleService::class)->suspendFamily(
            $parent,
            '   ',
            $this->actor->id,
            'admin'
        );
    }

    public function test_reason_is_normalized_before_account_history_write(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);

        app(FamilyLifecycleService::class)->suspendFamily(
            $parent,
            '  '.LifecycleReason::SupportHold->value.'  ',
            $this->actor->id,
            'admin'
        );

        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::FamilySuspended->value,
            'reason_code' => LifecycleReason::SupportHold->value,
        ]);
        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::FamilySuspended->value,
            'reason_code' => '  '.LifecycleReason::SupportHold->value.'  ',
        ]);
    }

    public function test_child_invalid_transition_is_rejected(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        [$child] = $this->createChild($parent, ChildAccountStatus::PendingActivation->value);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Child account transition not allowed from 'pending_activation'.");

        app(FamilyLifecycleService::class)->suspendChild(
            $child,
            LifecycleReason::SupportHold->value,
            $this->actor->id,
            'admin'
        );
    }

    public function test_family_restore_falls_back_to_pending_activation_when_archive_origin_is_invalid(): void
    {
        Queue::fake();

        [$parent, $parentUser] = $this->createFamily(FamilyLifecycleStatus::Archived->value);

        app(AccountHistoryService::class)->record($parent->id, AccountHistoryEventType::FamilyArchived->value, [
            'subject_type' => 'family',
            'subject_id' => $parent->id,
            'old_value' => 'retired_forever',
        ]);

        app(FamilyLifecycleService::class)->restoreFamily(
            $parent,
            LifecycleReason::ArchivedByMistake->value,
            $this->actor->id,
            'admin'
        );

        $this->assertSame(FamilyLifecycleStatus::PendingActivation->value, $parent->fresh()->lifecycle_status);
        $this->assertSame('inactive', $parentUser->fresh()->status);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::FamilyRestored->value,
            'new_value' => FamilyLifecycleStatus::PendingActivation->value,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_child_restore_falls_back_to_pending_activation_when_archive_origin_is_invalid(): void
    {
        Queue::fake();

        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        [$child, $childUser] = $this->createChild($parent, ChildAccountStatus::Archived->value);

        app(AccountHistoryService::class)->record($parent->id, AccountHistoryEventType::ChildArchived->value, [
            'subject_type' => 'child',
            'subject_id' => $child->id,
            'old_value' => 'retired_forever',
        ]);

        app(FamilyLifecycleService::class)->restoreChild(
            $child,
            LifecycleReason::ReturningFamily->value,
            $this->actor->id,
            'admin'
        );

        $this->assertSame(ChildAccountStatus::PendingActivation->value, $child->fresh()->account_status);
        $this->assertSame('inactive', $childUser->fresh()->status);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildRestored->value,
            'subject_type' => 'child',
            'subject_id' => $child->id,
            'new_value' => ChildAccountStatus::PendingActivation->value,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_family_transition_rechecks_current_locked_status_before_writing(): void
    {
        Queue::fake();

        [$parent] = $this->createFamily(FamilyLifecycleStatus::PendingActivation->value);
        $stalePendingParent = $parent->fresh();
        $parent->forceFill(['lifecycle_status' => FamilyLifecycleStatus::Active->value])->save();

        try {
            app(FamilyLifecycleService::class)->activateFamily(
                $stalePendingParent,
                LifecycleReason::SetupComplete->value,
                $this->actor->id,
                'admin'
            );

            $this->fail('Expected stale family activation to be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame("Family lifecycle transition not allowed from 'active'.", $exception->getMessage());
        }

        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::FamilyActivated->value,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_child_transition_rechecks_current_locked_status_before_writing(): void
    {
        Queue::fake();

        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        [$child] = $this->createChild($parent, ChildAccountStatus::PendingActivation->value);
        $stalePendingChild = $child->fresh();
        $child->forceFill(['account_status' => ChildAccountStatus::Active->value])->save();

        try {
            app(FamilyLifecycleService::class)->activateChild(
                $stalePendingChild,
                LifecycleReason::SetupComplete->value,
                $this->actor->id,
                'admin'
            );

            $this->fail('Expected stale child activation to be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame("Child account transition not allowed from 'active'.", $exception->getMessage());
        }

        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildActivated->value,
            'subject_type' => 'child',
            'subject_id' => $child->id,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_recoverable_credential_helper_requires_decryptable_value(): void
    {
        $user = User::factory()->create();
        DB::table('users')
            ->where('id', $user->id)
            ->update(['recoverable_password_encrypted' => 'not-a-valid-encrypted-payload']);

        $user->refresh();

        $this->assertNull(app(CredentialService::class)->reveal($user));
        $this->assertFalse(app(CredentialService::class)->hasRecoverableCredential($user));
    }

    public function test_parent_activation_email_content_matches_activation_requirements(): void
    {
        [$parent, $parentUser] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $parent->forceFill(['email' => 'parent-login@example.test'])->save();

        $html = view('emails.parent-activation', [
            'parent' => $parent->fresh(),
            'user' => $parentUser,
            'password' => 'ParentPass123',
            'loginUrl' => 'https://week14.test/login',
            'passwordResetUrl' => 'https://week14.test/forgot-password',
            'isResend' => false,
        ])->render();

        $this->assertStringContainsString('https://week14.test/login', $html);
        $this->assertStringContainsString('parent-login@example.test', $html);
        $this->assertStringNotContainsString($parentUser->name, $html);
        $this->assertStringContainsString('https://week14.test/forgot-password', $html);
        $this->assertStringContainsString('parent dashboard', $html);
        $this->assertStringContainsString('Task Completion PIN', $html);
        $this->assertStringContainsString('1414', $html);
    }

    public function test_child_activation_email_content_matches_activation_requirements(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        [$child, $childUser] = $this->createChild($parent, ChildAccountStatus::Active->value);

        $html = view('emails.child-activation', [
            'parent' => $parent,
            'student' => $child,
            'user' => $childUser,
            'password' => 'ChildPass123',
            'activeServices' => ['Help Me Study'],
            'loginUrl' => 'https://week14.test/login',
            'passwordResetUrl' => 'https://week14.test/forgot-password',
            'isResend' => false,
        ])->render();

        $this->assertStringContainsString('https://week14.test/login', $html);
        $this->assertStringContainsString('https://week14.test/forgot-password', $html);
        $this->assertStringContainsString($childUser->email, $html);
        $this->assertStringContainsString(e($childUser->name), $html);
        $this->assertStringContainsString('parent dashboard', $html);
        $this->assertStringContainsString('Reward System PIN', $html);
        $this->assertStringContainsString('Help Me Study', $html);
    }

    public function test_parent_activation_email_job_skips_duplicate_first_activation_send(): void
    {
        Mail::fake();

        [$parent, $parentUser] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $parentUser->forceFill(['recoverable_password_encrypted' => 'Secret123'])->save();

        app(AccountHistoryService::class)->record($parent->id, AccountHistoryEventType::ParentActivationEmailSent->value, [
            'subject_type' => 'parent',
            'subject_id' => $parent->id,
        ]);

        (new SendParentActivationEmailJob($parent->id))->handle(
            app(CredentialService::class),
            app(AccountHistoryService::class),
            app(EmailDeliveryClaimService::class)
        );

        Mail::assertNothingSent();
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentActivationEmailSkipped->value,
            'subject_type' => 'parent',
            'subject_id' => $parent->id,
        ]);
    }

    public function test_parent_activation_email_job_reclaims_stale_delivery_claim_and_sends_email(): void
    {
        Mail::fake();

        [$parent, $parentUser] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $parentUser->forceFill(['recoverable_password_encrypted' => 'Secret123'])->save();

        app(EmailDeliveryClaimService::class)->claim(
            SendParentActivationEmailJob::firstClaimKey($parent->id),
            $parent->id,
            'parent',
            $parent->id,
            AccountHistoryEventType::ParentActivationEmailSent->value,
            ['subject_user_id' => $parentUser->id]
        );
        \Illuminate\Support\Facades\DB::table('email_delivery_claims')
            ->where('claim_key', SendParentActivationEmailJob::firstClaimKey($parent->id))
            ->update([
                'claimed_at' => now()->subMinutes(10),
                'completed_at' => null,
            ]);

        (new SendParentActivationEmailJob($parent->id))->handle(
            app(CredentialService::class),
            app(AccountHistoryService::class),
            app(EmailDeliveryClaimService::class)
        );

        $this->assertDatabaseHas('email_delivery_claims', [
            'claim_key' => SendParentActivationEmailJob::firstClaimKey($parent->id),
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentActivationEmailSent->value,
        ]);
    }

    public function test_child_activation_email_job_rechecks_family_and_child_state_before_sending(): void
    {
        Mail::fake();

        [$parent] = $this->createFamily(FamilyLifecycleStatus::PendingActivation->value);
        [$child, $childUser] = $this->createChild($parent, ChildAccountStatus::Active->value);
        $childUser->forceFill(['recoverable_password_encrypted' => 'Kid12345'])->save();

        (new SendChildActivationEmailJob($child->id))->handle(
            app(CredentialService::class),
            app(AccountHistoryService::class),
            app(EmailDeliveryClaimService::class)
        );

        Mail::assertNothingSent();
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildActivationEmailSkipped->value,
            'subject_type' => 'child',
            'subject_id' => $child->id,
        ]);
    }

    public function test_child_activation_email_job_reclaims_stale_delivery_claim_and_sends_email(): void
    {
        Mail::fake();

        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        [$child, $childUser] = $this->createChild($parent, ChildAccountStatus::Active->value);
        $childUser->forceFill(['recoverable_password_encrypted' => 'Kid12345'])->save();

        app(EmailDeliveryClaimService::class)->claim(
            SendChildActivationEmailJob::firstClaimKey($child->id),
            $parent->id,
            'child',
            $child->id,
            AccountHistoryEventType::ChildActivationEmailSent->value,
            ['subject_user_id' => $childUser->id]
        );
        \Illuminate\Support\Facades\DB::table('email_delivery_claims')
            ->where('claim_key', SendChildActivationEmailJob::firstClaimKey($child->id))
            ->update([
                'claimed_at' => now()->subMinutes(10),
                'completed_at' => null,
            ]);

        (new SendChildActivationEmailJob($child->id))->handle(
            app(CredentialService::class),
            app(AccountHistoryService::class),
            app(EmailDeliveryClaimService::class)
        );

        $this->assertDatabaseHas('email_delivery_claims', [
            'claim_key' => SendChildActivationEmailJob::firstClaimKey($child->id),
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildActivationEmailSent->value,
            'subject_id' => $child->id,
        ]);
    }

    public function test_delivery_claim_owner_token_blocks_stale_worker_terminal_update(): void
    {
        [$parent, $parentUser] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $claimKey = SendParentActivationEmailJob::firstClaimKey($parent->id);
        $deliveryClaims = app(EmailDeliveryClaimService::class);

        $deliveryClaims->claim(
            $claimKey,
            $parent->id,
            'parent',
            $parent->id,
            AccountHistoryEventType::ParentActivationEmailSent->value,
            [
                'subject_user_id' => $parentUser->id,
                EmailDeliveryClaimService::OWNER_TOKEN_METADATA_KEY => 'original-worker',
            ]
        );
        DB::table('email_delivery_claims')
            ->where('claim_key', $claimKey)
            ->update(['claimed_at' => now()->subMinutes(10)]);

        $this->assertTrue($deliveryClaims->claim(
            $claimKey,
            $parent->id,
            'parent',
            $parent->id,
            AccountHistoryEventType::ParentActivationEmailSent->value,
            [
                'subject_user_id' => $parentUser->id,
                EmailDeliveryClaimService::OWNER_TOKEN_METADATA_KEY => 'reclaim-worker',
            ]
        ));

        $this->assertFalse($deliveryClaims->markSent($claimKey, ['attempt' => 'original'], 'original-worker'));
        $this->assertDatabaseHas('email_delivery_claims', [
            'claim_key' => $claimKey,
            'status' => 'claimed',
        ]);

        $this->assertTrue($deliveryClaims->markSent($claimKey, ['attempt' => 'reclaim'], 'reclaim-worker'));
        $this->assertDatabaseHas('email_delivery_claims', [
            'claim_key' => $claimKey,
            'status' => 'sent',
        ]);
    }

    public function test_delivery_claim_owner_token_blocks_tokenless_terminal_update(): void
    {
        [$parent, $parentUser] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $claimKey = SendParentActivationEmailJob::firstClaimKey($parent->id);
        $deliveryClaims = app(EmailDeliveryClaimService::class);

        $deliveryClaims->claim(
            $claimKey,
            $parent->id,
            'parent',
            $parent->id,
            AccountHistoryEventType::ParentActivationEmailSent->value,
            [
                'subject_user_id' => $parentUser->id,
                EmailDeliveryClaimService::OWNER_TOKEN_METADATA_KEY => 'owned-worker',
            ]
        );

        $this->assertFalse($deliveryClaims->markSent($claimKey, ['attempt' => 'tokenless']));
        $this->assertDatabaseHas('email_delivery_claims', [
            'claim_key' => $claimKey,
            'status' => 'claimed',
            'completed_at' => null,
        ]);

        $this->assertTrue($deliveryClaims->markSent($claimKey, ['attempt' => 'owned'], 'owned-worker'));
        $this->assertDatabaseHas('email_delivery_claims', [
            'claim_key' => $claimKey,
            'status' => 'sent',
        ]);
    }

    public function test_delivery_claim_owner_token_blocks_tokenless_stale_reclaim(): void
    {
        [$parent, $parentUser] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $claimKey = SendParentActivationEmailJob::firstClaimKey($parent->id);
        $deliveryClaims = app(EmailDeliveryClaimService::class);

        $deliveryClaims->claim(
            $claimKey,
            $parent->id,
            'parent',
            $parent->id,
            AccountHistoryEventType::ParentActivationEmailSent->value,
            [
                'subject_user_id' => $parentUser->id,
                EmailDeliveryClaimService::OWNER_TOKEN_METADATA_KEY => 'original-worker',
            ]
        );
        DB::table('email_delivery_claims')
            ->where('claim_key', $claimKey)
            ->update(['claimed_at' => now()->subMinutes(10)]);

        $this->assertFalse($deliveryClaims->claim(
            $claimKey,
            $parent->id,
            'parent',
            $parent->id,
            AccountHistoryEventType::ParentActivationEmailSent->value,
            ['subject_user_id' => $parentUser->id]
        ));

        $claim = EmailDeliveryClaim::where('claim_key', $claimKey)->firstOrFail();

        $this->assertSame('claimed', $claim->status);
        $this->assertNull($claim->completed_at);
        $this->assertSame('original-worker', $claim->metadata[EmailDeliveryClaimService::OWNER_TOKEN_METADATA_KEY]);
        $this->assertArrayNotHasKey('reclaim_count', $claim->metadata);
        $this->assertArrayNotHasKey('reclaimed_at', $claim->metadata);
        $this->assertFalse($deliveryClaims->markSent($claimKey, ['attempt' => 'tokenless']));
        $this->assertTrue($deliveryClaims->markSent($claimKey, ['attempt' => 'original'], 'original-worker'));
    }

    public function test_parent_activation_email_job_does_not_swallow_sent_history_failures(): void
    {
        Mail::fake();

        [$parent, $parentUser] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        $parentUser->forceFill(['recoverable_password_encrypted' => 'Secret123'])->save();

        $job = new SendParentActivationEmailJob($parent->id);
        $failingHistory = new class extends AccountHistoryService
        {
            public function record(int $parentId, string $eventType, array $options = []): \App\Models\AccountHistory
            {
                throw new RuntimeException('History write failed.');
            }
        };

        try {
            $job->handle(
                app(CredentialService::class),
                $failingHistory,
                app(EmailDeliveryClaimService::class)
            );

            $this->fail('Expected activation job to surface Account History write failures.');
        } catch (RuntimeException $exception) {
            $this->assertSame('History write failed.', $exception->getMessage());
            $job->failed($exception);
        }

        $this->assertDatabaseHas('email_delivery_claims', [
            'claim_key' => SendParentActivationEmailJob::firstClaimKey($parent->id),
            'status' => 'failed',
        ]);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentActivationEmailFailed->value,
            'subject_type' => 'parent',
            'subject_id' => $parent->id,
        ]);
        $this->assertDatabaseMissing('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentActivationEmailSent->value,
            'subject_type' => 'parent',
            'subject_id' => $parent->id,
        ]);
    }

    public function test_child_activation_email_job_failure_does_not_write_orphan_history_when_student_is_deleted(): void
    {
        [$parent] = $this->createFamily(FamilyLifecycleStatus::Active->value);
        [$child] = $this->createChild($parent, ChildAccountStatus::Active->value);

        $childId = $child->id;
        $child->delete();

        (new SendChildActivationEmailJob($childId))->failed(new RuntimeException('SMTP failed'));

        $this->assertDatabaseMissing('account_histories', [
            'event_type' => AccountHistoryEventType::ChildActivationEmailFailed->value,
            'subject_type' => 'child',
            'subject_id' => $childId,
        ]);
    }

    private function createFamily(?string $status): array
    {
        $userStatus = $status === FamilyLifecycleStatus::Active->value ? 'active' : 'inactive';

        if ($status === null) {
            $userStatus = 'inactive';
        }

        $user = User::factory()->create([
            'status' => $userStatus,
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

    private function createChild(ParentModel $parent, string $status): array
    {
        $user = User::factory()->create([
            'status' => $status === ChildAccountStatus::Active->value ? 'active' : 'inactive',
        ]);

        $child = Student::create([
            'first_name' => 'Youssef',
            'parent_id' => $parent->id,
            'user_id' => $user->id,
            'status' => 'active',
            'account_status' => $status,
        ]);

        return [$child, $user];
    }
}
