<?php

namespace App\Services;

use App\Models\EmailDeliveryClaim;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class EmailDeliveryClaimService
{
    public const OWNER_TOKEN_METADATA_KEY = 'claim_owner_token';

    private const STALE_CLAIM_AFTER_MINUTES = 5;

    public function claim(
        string $claimKey,
        int $parentId,
        string $subjectType,
        int $subjectId,
        string $eventType,
        array $metadata = []
    ): bool {
        try {
            EmailDeliveryClaim::create([
                'claim_key' => $claimKey,
                'parent_id' => $parentId,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'event_type' => $eventType,
                'status' => 'claimed',
                'metadata' => $metadata === [] ? null : $metadata,
                'claimed_at' => now(),
            ]);

            return true;
        } catch (QueryException $exception) {
            if ($this->isUniqueConstraintViolation($exception)) {
                return $this->reclaimIfStale($claimKey, $metadata);
            }

            throw $exception;
        }
    }

    public function markSent(string $claimKey, array $metadata = [], ?string $ownerToken = null): bool
    {
        return $this->markStatus($claimKey, 'sent', $metadata, $ownerToken);
    }

    public function markSkipped(string $claimKey, array $metadata = [], ?string $ownerToken = null): bool
    {
        return $this->markStatus($claimKey, 'skipped', $metadata, $ownerToken);
    }

    public function markFailed(string $claimKey, array $metadata = [], ?string $ownerToken = null): bool
    {
        return $this->markStatus($claimKey, 'failed', $metadata, $ownerToken);
    }

    public function statusFor(string $claimKey): ?string
    {
        return EmailDeliveryClaim::where('claim_key', $claimKey)->value('status');
    }

    private function markStatus(string $claimKey, string $status, array $metadata = [], ?string $ownerToken = null): bool
    {
        return DB::transaction(function () use ($claimKey, $metadata, $ownerToken, $status): bool {
            $claim = EmailDeliveryClaim::query()
                ->where('claim_key', $claimKey)
                ->lockForUpdate()
                ->first();

            if (! $claim || $claim->status !== 'claimed' || $claim->completed_at !== null) {
                return false;
            }

            $claimMetadata = $claim->metadata ?? [];
            $claimOwnerToken = $claimMetadata[self::OWNER_TOKEN_METADATA_KEY] ?? null;

            if ($claimOwnerToken !== null && $claimOwnerToken !== $ownerToken) {
                return false;
            }

            $claim->forceFill([
                'status' => $status,
                'metadata' => $this->mergeMetadata($claim->metadata, $metadata),
                'completed_at' => now(),
            ])->save();

            return true;
        });
    }

    private function mergeMetadata(?array $existing, array $incoming): ?array
    {
        $merged = array_merge($existing ?? [], $incoming);

        return $merged === [] ? null : $merged;
    }

    private function reclaimIfStale(string $claimKey, array $metadata = []): bool
    {
        return DB::transaction(function () use ($claimKey, $metadata): bool {
            $claim = EmailDeliveryClaim::query()
                ->where('claim_key', $claimKey)
                ->lockForUpdate()
                ->first();

            if (! $claim || $claim->status !== 'claimed' || $claim->completed_at !== null) {
                return false;
            }

            $staleBefore = now()->subMinutes(self::STALE_CLAIM_AFTER_MINUTES);

            if ($claim->claimed_at !== null && $claim->claimed_at->gt($staleBefore)) {
                return false;
            }

            $existingMetadata = $claim->metadata ?? [];

            if ($this->ownerTokenFromMetadata($existingMetadata) !== null
                && $this->ownerTokenFromMetadata($metadata) === null) {
                return false;
            }

            $reclaimCount = (int) ($existingMetadata['reclaim_count'] ?? 0) + 1;
            $reclaimMetadata = array_merge($metadata, [
                'reclaim_count' => $reclaimCount,
                'reclaimed_at' => now()->toIso8601String(),
            ]);

            $claim->forceFill([
                'status' => 'claimed',
                'metadata' => $this->mergeMetadata($existingMetadata, $reclaimMetadata),
                'claimed_at' => now(),
                'completed_at' => null,
            ])->save();

            return true;
        });
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;
        $driverCode = (string) ($exception->errorInfo[1] ?? '');
        $message = $exception->getMessage();

        return $sqlState === '23000'
            || $driverCode === '19'
            || str_contains($message, 'UNIQUE constraint failed')
            || str_contains($message, 'Duplicate entry');
    }

    private function ownerTokenFromMetadata(array $metadata): ?string
    {
        $ownerToken = $metadata[self::OWNER_TOKEN_METADATA_KEY] ?? null;

        if (! is_string($ownerToken) || $ownerToken === '') {
            return null;
        }

        return $ownerToken;
    }
}
