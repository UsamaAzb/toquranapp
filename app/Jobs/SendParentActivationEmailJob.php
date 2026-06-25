<?php

namespace App\Jobs;

use App\Enums\AccountHistoryEventType;
use App\Enums\FamilyLifecycleStatus;
use App\Models\AccountHistory;
use App\Models\ParentModel;
use App\Services\AccountHistoryService;
use App\Services\CredentialService;
use App\Services\EmailDeliveryClaimService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SendParentActivationEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    // tries = 1: delivery claim ownership makes automatic retries unsafe; failed() writes the audit trail.
    public int $tries = 1;

    public function __construct(
        public int $parentId,
        public bool $resend = false,
        public ?string $deliveryClaimKey = null,
        public ?string $claimOwnerToken = null,
    ) {
        $this->deliveryClaimKey ??= $this->resend
            ? self::resendClaimKey($this->parentId)
            : self::firstClaimKey($this->parentId);
        $this->claimOwnerToken ??= (string) Str::uuid();
    }

    public function handle(
        CredentialService $credentials,
        AccountHistoryService $history,
        EmailDeliveryClaimService $deliveryClaims
    ): void {
        $claimKey = $this->deliveryClaimKey;

        $sendContext = DB::transaction(function () use ($credentials, $history, $deliveryClaims, $claimKey): ?array {
            $parent = ParentModel::whereKey($this->parentId)
                ->lockForUpdate()
                ->first();

            $parent?->loadMissing('user');

            if (! $parent || ! $parent->user) {
                $this->recordSkipped($history, $this->parentId, 'missing_parent_or_user');

                return null;
            }

            if (! $this->resend && $this->alreadySent($parent->id)) {
                $this->recordSkipped($history, $parent->id, 'duplicate_sent_guard');

                return null;
            }

            $recipientEmail = $this->parentRecipientEmail($parent);

            if ($recipientEmail === null) {
                $this->recordSkipped($history, $parent->id, 'missing_parent_email');

                return null;
            }

            if (! $deliveryClaims->claim(
                $claimKey,
                $parent->id,
                'parent',
                $parent->id,
                AccountHistoryEventType::ParentActivationEmailSent->value,
                [
                    'resend' => $this->resend,
                    'subject_user_id' => $parent->user->id,
                    'recipient_email' => $recipientEmail,
                    EmailDeliveryClaimService::OWNER_TOKEN_METADATA_KEY => $this->claimOwnerToken,
                ]
            )) {
                if ($deliveryClaims->statusFor($claimKey) === 'claimed') {
                    $this->recordSkipped($history, $parent->id, 'delivery_claim_in_progress', [
                        'claim_key' => $claimKey,
                    ]);
                }

                return null;
            }

            if ($parent->lifecycle_status !== FamilyLifecycleStatus::Active->value) {
                $this->recordSkipped($history, $parent->id, 'family_not_active', [
                    'current_status' => $parent->lifecycle_status,
                ]);
                $deliveryClaims->markSkipped($claimKey, [
                    'skip_reason' => 'family_not_active',
                    'current_status' => $parent->lifecycle_status,
                    'resend' => $this->resend,
                ], $this->claimOwnerToken);

                return null;
            }

            $plainPassword = $credentials->reveal($parent->user);

            if ($plainPassword === null) {
                $this->recordSkipped($history, $parent->id, 'missing_recoverable_credential');
                $deliveryClaims->markSkipped($claimKey, [
                    'skip_reason' => 'missing_recoverable_credential',
                    'resend' => $this->resend,
                ], $this->claimOwnerToken);

                return null;
            }

            return [
                'parent' => $parent,
                'user_id' => $parent->user->id,
                'password' => $plainPassword,
                'recipient_email' => $recipientEmail,
            ];
        });

        if ($sendContext === null) {
            return;
        }

        Mail::send('emails.parent-activation', [
            'parent' => $sendContext['parent'],
            'user' => $sendContext['parent']->user,
            'password' => $sendContext['password'],
            'loginUrl' => $this->loginUrl(),
            'passwordResetUrl' => $this->passwordResetUrl($sendContext['recipient_email']),
            'isResend' => $this->resend,
        ], function ($message) use ($sendContext): void {
            $message->to($sendContext['recipient_email'], $sendContext['parent']->full_name)
                ->subject($this->resend
                    ? 'To Quran family activation email resent'
                    : 'Your To Quran family account is active');
        });

        DB::transaction(function () use ($claimKey, $deliveryClaims, $history, $sendContext): void {
            if (! $deliveryClaims->markSent($claimKey, [
                'resend' => $this->resend,
                'subject_user_id' => $sendContext['user_id'],
                'recipient_email' => $sendContext['recipient_email'],
            ], $this->claimOwnerToken)) {
                throw new RuntimeException('Activation email delivery claim is no longer owned by this job.');
            }

            $history->record($sendContext['parent']->id, AccountHistoryEventType::ParentActivationEmailSent->value, [
                'subject_type' => 'parent',
                'subject_id' => $sendContext['parent']->id,
                'metadata' => [
                    'subject_user_id' => $sendContext['user_id'],
                    'resend' => $this->resend,
                    'recipient_email' => $sendContext['recipient_email'],
                ],
            ]);
        });
    }

    public function failed(Throwable $exception): void
    {
        $deliveryClaims = app(EmailDeliveryClaimService::class);
        $claimStatus = $deliveryClaims->statusFor($this->deliveryClaimKey);

        if ($claimStatus === 'sent') {
            return;
        }

        if ($claimStatus !== 'sent' && ! $deliveryClaims->markFailed($this->deliveryClaimKey, [
            'error' => $exception->getMessage(),
            'resend' => $this->resend,
        ], $this->claimOwnerToken)) {
            return;
        }

        app(AccountHistoryService::class)->record($this->parentId, AccountHistoryEventType::ParentActivationEmailFailed->value, [
            'subject_type' => 'parent',
            'subject_id' => $this->parentId,
            'metadata' => [
                'error' => $exception->getMessage(),
                'resend' => $this->resend,
            ],
        ]);
    }

    private function alreadySent(int $parentId): bool
    {
        return AccountHistory::where('parent_id', $parentId)
            ->where('event_type', AccountHistoryEventType::ParentActivationEmailSent->value)
            ->where('subject_type', 'parent')
            ->where('subject_id', $parentId)
            ->exists();
    }

    private function recordSkipped(AccountHistoryService $history, int $parentId, string $reason, array $metadata = []): void
    {
        if ($parentId <= 0) {
            return;
        }

        $history->record($parentId, AccountHistoryEventType::ParentActivationEmailSkipped->value, [
            'subject_type' => 'parent',
            'subject_id' => $parentId,
            'metadata' => $metadata + [
                'skip_reason' => $reason,
                'resend' => $this->resend,
            ],
        ]);
    }

    private function loginUrl(): string
    {
        return Route::has('login') ? route('login') : url('/login');
    }

    private function parentRecipientEmail(ParentModel $parent): ?string
    {
        $userEmail = trim((string) $parent->user?->email);

        if ($userEmail !== '') {
            return $userEmail;
        }

        $parentEmail = trim((string) $parent->email);

        return $parentEmail !== '' ? $parentEmail : null;
    }

    private function passwordResetUrl(string $email): string
    {
        $query = $email !== '' ? ['email' => $email] : [];

        return Route::has('password.request')
            ? route('password.request', $query)
            : url('/forgot-password'.($query === [] ? '' : '?'.http_build_query($query)));
    }

    public static function firstClaimKey(int $parentId): string
    {
        return "parent_activation:first:{$parentId}";
    }

    public static function resendClaimKey(int $parentId): string
    {
        return 'parent_activation:resend:'.$parentId.':'.Str::uuid();
    }
}
