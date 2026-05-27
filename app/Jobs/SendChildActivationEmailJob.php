<?php

namespace App\Jobs;

use App\Enums\AccountHistoryEventType;
use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Models\AccountHistory;
use App\Models\Student;
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

class SendChildActivationEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    // tries = 1: delivery claim ownership makes automatic retries unsafe; failed() writes the audit trail.
    public int $tries = 1;

    public function __construct(
        public int $studentId,
        public bool $resend = false,
        public ?string $deliveryClaimKey = null,
        public ?string $claimOwnerToken = null,
    ) {
        $this->deliveryClaimKey ??= $this->resend
            ? self::resendClaimKey($this->studentId)
            : self::firstClaimKey($this->studentId);
        $this->claimOwnerToken ??= (string) Str::uuid();
    }

    public function handle(
        CredentialService $credentials,
        AccountHistoryService $history,
        EmailDeliveryClaimService $deliveryClaims
    ): void {
        $claimKey = $this->deliveryClaimKey;

        $sendContext = DB::transaction(function () use ($credentials, $history, $deliveryClaims, $claimKey): ?array {
            $student = Student::whereKey($this->studentId)
                ->lockForUpdate()
                ->first();

            $student?->loadMissing(['parent', 'user']);
            $parent = $student?->parent;

            if (! $student) {
                return null;
            }

            if (! $parent) {
                return null;
            }

            if (! $student->user) {
                $this->recordSkipped($history, $parent->id, 'missing_child_user');

                return null;
            }

            if (! $this->resend && $this->alreadySent($parent->id, $student->id)) {
                $this->recordSkipped($history, $parent->id, 'duplicate_sent_guard');

                return null;
            }

            if (! $deliveryClaims->claim(
                $claimKey,
                $parent->id,
                'child',
                $student->id,
                AccountHistoryEventType::ChildActivationEmailSent->value,
                [
                    'resend' => $this->resend,
                    'subject_user_id' => $student->user->id,
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

            if ($parent->lifecycle_status !== FamilyLifecycleStatus::Active->value
                || $student->account_status !== ChildAccountStatus::Active->value) {
                $this->recordSkipped($history, $parent->id, 'family_or_child_not_active', [
                    'family_status' => $parent->lifecycle_status,
                    'child_status' => $student->account_status,
                ]);
                $deliveryClaims->markSkipped($claimKey, [
                    'skip_reason' => 'family_or_child_not_active',
                    'family_status' => $parent->lifecycle_status,
                    'child_status' => $student->account_status,
                    'resend' => $this->resend,
                ], $this->claimOwnerToken);

                return null;
            }

            $plainPassword = $credentials->reveal($student->user);

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
                'student' => $student,
                'user_id' => $student->user->id,
                'password' => $plainPassword,
            ];
        });

        if ($sendContext === null) {
            return;
        }

        Mail::send('emails.child-activation', [
            'parent' => $sendContext['parent'],
            'student' => $sendContext['student'],
            'user' => $sendContext['student']->user,
            'password' => $sendContext['password'],
            'activeServices' => $this->activeServices($sendContext['student']),
            'loginUrl' => $this->loginUrl(),
            'passwordResetUrl' => $this->passwordResetUrl((string) $sendContext['student']->user?->email),
            'isResend' => $this->resend,
        ], function ($message) use ($sendContext): void {
            $message->to($sendContext['parent']->email, $sendContext['parent']->full_name)
                ->subject($this->resend
                    ? "{$sendContext['student']->first_name}'s To Quran activation email resent"
                    : "{$sendContext['student']->first_name}'s To Quran account is active");
        });

        DB::transaction(function () use ($claimKey, $deliveryClaims, $history, $sendContext): void {
            if (! $deliveryClaims->markSent($claimKey, [
                'resend' => $this->resend,
                'subject_user_id' => $sendContext['user_id'],
            ], $this->claimOwnerToken)) {
                throw new RuntimeException('Activation email delivery claim is no longer owned by this job.');
            }

            $history->record($sendContext['parent']->id, AccountHistoryEventType::ChildActivationEmailSent->value, [
                'subject_type' => 'child',
                'subject_id' => $sendContext['student']->id,
                'metadata' => [
                    'subject_user_id' => $sendContext['user_id'],
                    'resend' => $this->resend,
                ],
            ]);
        });
    }

    public function failed(Throwable $exception): void
    {
        $deliveryClaims = app(EmailDeliveryClaimService::class);
        $student = Student::find($this->studentId);
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

        if ($student === null || $student->parent_id === null || (int) $student->parent_id <= 0) {
            return;
        }

        app(AccountHistoryService::class)->record((int) $student->parent_id, AccountHistoryEventType::ChildActivationEmailFailed->value, [
            'subject_type' => 'child',
            'subject_id' => $this->studentId,
            'metadata' => [
                'error' => $exception->getMessage(),
                'resend' => $this->resend,
            ],
        ]);
    }

    private function alreadySent(int $parentId, int $studentId): bool
    {
        return AccountHistory::where('parent_id', $parentId)
            ->where('event_type', AccountHistoryEventType::ChildActivationEmailSent->value)
            ->where('subject_type', 'child')
            ->where('subject_id', $studentId)
            ->exists();
    }

    /** @return list<string> */
    private function activeServices(Student $student): array
    {
        $student->loadMissing('services_type');

        return collect([$student->services_type?->title])
            ->filter(fn (?string $service): bool => filled($service))
            ->values()
            ->all();
    }

    private function recordSkipped(AccountHistoryService $history, int $parentId, string $reason, array $metadata = []): void
    {
        if ($parentId <= 0) {
            return;
        }

        $history->record($parentId, AccountHistoryEventType::ChildActivationEmailSkipped->value, [
            'subject_type' => 'child',
            'subject_id' => $this->studentId,
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

    private function passwordResetUrl(string $email): string
    {
        $query = $email !== '' ? ['email' => $email] : [];

        return Route::has('password.request')
            ? route('password.request', $query)
            : url('/forgot-password'.($query === [] ? '' : '?'.http_build_query($query)));
    }

    public static function firstClaimKey(int $studentId): string
    {
        return "child_activation:first:{$studentId}";
    }

    public static function resendClaimKey(int $studentId): string
    {
        return 'child_activation:resend:'.$studentId.':'.Str::uuid();
    }
}
