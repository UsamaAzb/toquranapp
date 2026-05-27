<?php

namespace App\Services;

use App\Models\BookingChild;
use App\Models\BookingChildEmail;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class BookingChildEmailService
{
    public const TRACKED_EMAIL_TYPES = [
        'confirmation_parent',
        'confirmation_admin',
        'questionnaire_parent',
        'transfer_welcome',
        'transfer_admin',
    ];

    public const RETIRED_EMAIL_TYPES = [
        'transfer_welcome',
        'transfer_admin',
    ];

    public function emailTypeLabel(string $emailType, bool $compact = false): string
    {
        $label = match ($emailType) {
            'confirmation_parent' => $compact ? 'Parent Confirm' : 'Parent Confirmation',
            'confirmation_admin' => $compact ? 'Admin Confirm' : 'Admin Confirmation',
            'questionnaire_parent' => $compact ? 'Questionnaire' : 'Questionnaire Parent',
            'transfer_welcome' => 'Transfer Welcome',
            'transfer_admin' => 'Transfer Admin',
            default => $emailType,
        };

        return $this->isRetiredEmailType($emailType)
            ? $label.' (Retired)'
            : $label;
    }

    public function emailStatusBadge(?BookingChildEmail $status, ?string $emailType = null): array
    {
        if ($emailType !== null && $this->isRetiredEmailType($emailType)) {
            return ['label' => 'Retired', 'class' => 'bg-label-secondary'];
        }

        return match ($status?->status ?? 'not_sent') {
            'queued' => ['label' => 'Queued', 'class' => 'bg-label-info'],
            'sent' => ['label' => 'Sent', 'class' => 'bg-label-success'],
            'resent' => ['label' => 'Resent', 'class' => 'bg-label-success'],
            'failed' => ['label' => 'Failed', 'class' => 'bg-label-danger'],
            default => ['label' => 'Not Sent', 'class' => 'bg-label-secondary'],
        };
    }

    public function isRetiredEmailType(string $emailType): bool
    {
        return in_array($emailType, self::RETIRED_EMAIL_TYPES, true);
    }

    public function latestStatusesForChild(BookingChild $child): Collection
    {
        return $this->latestStatusesForChildren([$child])->get($child->id, $this->emptyStatuses($child));
    }

    public function latestStatus(BookingChild $child, string $emailType): ?BookingChildEmail
    {
        $this->assertSupportedType($emailType);

        return $this->latestStatusesForChild($child)->get($emailType);
    }

    public function latestStatusesForChildren(iterable $children): Collection
    {
        $childrenCollection = collect($children)
            ->filter(fn ($child) => $child instanceof BookingChild)
            ->values();

        if ($childrenCollection->isEmpty()) {
            return collect();
        }

        $statusesByChild = $childrenCollection->mapWithKeys(function (BookingChild $child) {
            return [$child->id => $this->statusesFromLoadedEmails($child)];
        });

        $missingIds = $childrenCollection
            ->reject(fn (BookingChild $child) => $child->relationLoaded('emails'))
            ->pluck('id')
            ->filter()
            ->values();

        if ($missingIds->isEmpty()) {
            return $statusesByChild;
        }

        $fetchedStatuses = BookingChildEmail::query()
            ->whereIn('booking_child_id', $missingIds)
            ->orderBy('booking_child_id')
            ->orderByDesc('last_attempt_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('booking_child_id')
            ->map(fn (Collection $emails) => $this->statusesFromEmailCollection($emails));

        return $statusesByChild->map(function (Collection $statuses, int $childId) use ($fetchedStatuses) {
            return $fetchedStatuses->get($childId, $statuses);
        });
    }

    public function resend(BookingChild $child, string $emailType): void
    {
        $this->assertSupportedType($emailType);

        $child->loadMissing('booking');

        if (! $child->booking) {
            throw new InvalidArgumentException('The selected child is not linked to a booking.');
        }

        match ($emailType) {
            'confirmation_parent' => app(BookingConfirmationService::class)
                ->sendConfirmationParentEmail($child->booking, $child, true),
            'confirmation_admin' => app(BookingConfirmationService::class)
                ->sendConfirmationAdminEmail($child->booking, $child, true),
            'transfer_welcome' => $this->resendTransferEmail($child, 'transfer_welcome'),
            'transfer_admin' => $this->resendTransferEmail($child, 'transfer_admin'),
            'questionnaire_parent' => throw new InvalidArgumentException('Questionnaire email resend is reserved for a later sprint.'),
            default => throw new InvalidArgumentException("Unsupported email type [{$emailType}]."),
        };
    }

    public function createAttemptRow(?BookingChild $child, string $emailType): ?BookingChildEmail
    {
        $this->assertSupportedType($emailType);

        if (! $child) {
            return null;
        }

        return BookingChildEmail::create([
            'booking_child_id' => $child->id,
            'email_type' => $emailType,
            'status' => 'queued',
            'last_attempt_at' => now(),
            'triggered_by' => auth()->id(),
        ]);
    }

    public function markAttemptSent(?BookingChildEmail $attempt, bool $isResend): void
    {
        if (! $attempt) {
            return;
        }

        $attempt->update([
            'status' => $isResend ? 'resent' : 'sent',
            'last_sent_at' => now(),
            'last_error_message' => null,
        ]);
    }

    public function markAttemptFailed(?BookingChildEmail $attempt, string $message): void
    {
        if (! $attempt) {
            return;
        }

        $attempt->update([
            'status' => 'failed',
            'last_error_message' => $message,
        ]);
    }

    protected function resendTransferEmail(BookingChild $child, string $emailType): void
    {
        unset($child, $emailType);

        session()->flash('info', 'Transfer emails are retired. Use the Family Workspace to send activation emails.');
    }

    protected function assertSupportedType(string $emailType): void
    {
        if (! in_array($emailType, self::TRACKED_EMAIL_TYPES, true)) {
            throw new InvalidArgumentException("Unsupported email type [{$emailType}].");
        }
    }

    protected function statusesFromLoadedEmails(BookingChild $child): Collection
    {
        if (! $child->relationLoaded('emails')) {
            return $this->emptyStatuses($child);
        }

        return $this->statusesFromEmailCollection($child->emails, $child);
    }

    protected function statusesFromEmailCollection(Collection $emails, BookingChild|int|null $child = null): Collection
    {
        $childId = $child instanceof BookingChild
            ? $child->id
            : ($child ?? $emails->first()?->booking_child_id);
        $latest = $emails
            ->sortByDesc(fn (BookingChildEmail $email) => sprintf(
                '%s-%010d',
                optional($email->last_attempt_at)->format('YmdHis.u') ?: '',
                $email->id
            ))
            ->values()
            ->unique('email_type')
            ->keyBy('email_type');

        return $this->emptyStatuses($childId)->mapWithKeys(function ($value, string $emailType) use ($latest) {
            return [$emailType => $latest->get($emailType, $value)];
        });
    }

    protected function emptyStatuses(BookingChild|int|null $child = null): Collection
    {
        $childId = $child instanceof BookingChild ? $child->id : $child;

        return collect(self::TRACKED_EMAIL_TYPES)->mapWithKeys(function (string $emailType) use ($childId) {
            return [$emailType => $this->defaultStatusRecord($emailType, $childId)];
        });
    }

    protected function defaultStatusRecord(string $emailType, ?int $childId): BookingChildEmail
    {
        return new BookingChildEmail([
            'booking_child_id' => $childId,
            'email_type' => $emailType,
            'status' => 'not_sent',
            'last_attempt_at' => null,
            'last_sent_at' => null,
            'last_error_message' => null,
            'triggered_by' => null,
        ]);
    }
}
