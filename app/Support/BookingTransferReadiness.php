<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\FamilyLifecycleStatus;
use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\ParentModel;
use App\Models\Services_type;
use App\Services\BookingParentIdentityResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingTransferReadiness
{
    public static function unresolvedServiceValues(BookingChild $child, ?Booking $booking = null): array
    {
        $rawValues = self::effectiveRawServiceValues($child, $booking);

        if ($rawValues === []) {
            return [];
        }

        if (! Schema::hasTable('services_types')) {
            return [];
        }

        $normalized = collect($rawValues)
            ->map(fn (string $value) => BookingServiceInterest::normalize($value))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return $rawValues;
        }

        $knownValues = Services_type::query()
            ->whereIn('value', $normalized->all())
            ->pluck('value')
            ->map(fn (string $value) => BookingServiceInterest::normalize($value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return collect($rawValues)
            ->filter(function (string $rawValue) use ($knownValues) {
                return ! in_array(BookingServiceInterest::normalize($rawValue), $knownValues, true);
            })
            ->values()
            ->all();
    }

    public static function blockedReason(BookingChild $child, ?Booking $booking = null, bool $includeIdentityCheck = true): ?string
    {
        $booking ??= $child->booking;

        if ($child->transfer_status === 'transferred') {
            return 'This child has already been transferred.';
        }

        if ($child->evaluation_outcome !== 'fit') {
            return 'Transfer is only available when Evaluation Outcome is Fit.';
        }

        if (! in_array($child->meeting_disposition, ['completed', 'cancelled', 'no_meeting_required'], true)) {
            return 'Transfer requires Meeting Disposition = Completed, Cancelled, or No Meeting Required.';
        }

        if (self::effectiveRawServiceValues($child, $booking) === []) {
            return 'Transfer is blocked until at least one service interest is selected.';
        }

        $unresolvedServiceValues = self::unresolvedServiceValues($child, $booking);

        if ($unresolvedServiceValues !== []) {
            return 'Transfer is blocked until the service interest mapping is corrected.';
        }

        $gradeLevelId = self::effectiveGradeLevelId($child, $booking);

        if (! $gradeLevelId) {
            return 'Transfer is blocked until a grade level is selected.';
        }

        $subjectPlan = BookingSubjectProvisioning::planForGradeLevel($gradeLevelId);

        if ($subjectPlan === []) {
            return 'Transfer is blocked until grade-level subjects are configured for this grade.';
        }

        $missingGradeLevelSubjects = BookingSubjectProvisioning::missingRequiredActiveGradeLevelSubjects($gradeLevelId);

        if ($missingGradeLevelSubjects !== []) {
            return 'Transfer is blocked because required active-by-default subjects are missing for: '.implode(', ', $missingGradeLevelSubjects).'.';
        }

        if (blank($booking?->parent_name) || blank($child->child_name)) {
            return 'Transfer is blocked because required parent or child details are missing.';
        }

        if (! $includeIdentityCheck) {
            return null;
        }

        $identityResolution = app(BookingParentIdentityResolver::class)->resolveTransferTarget($child);

        if (! ($identityResolution['allowed'] ?? false)) {
            return $identityResolution['blocked_reason'] ?? 'Transfer is blocked until parent identity is reconciled.';
        }

        if (! self::existingFamilyHasValidLifecycleStatus($identityResolution)) {
            return 'Existing family must be classified before transfer.';
        }

        return null;
    }

    public static function canConfirmLinkedParentContactUpdate(BookingChild $child, ?Booking $booking = null): bool
    {
        $booking ??= $child->booking;

        if (! $booking?->parent_id) {
            return false;
        }

        $identityResolution = app(BookingParentIdentityResolver::class)->resolveTransferTarget($child);

        return ($identityResolution['outcome'] ?? null) === 'update_linked_parent_contact'
            && (bool) ($identityResolution['requires_contact_update'] ?? false);
    }

    public static function canTransfer(BookingChild $child, ?Booking $booking = null): bool
    {
        return self::blockedReason($child, $booking) === null;
    }

    public static function effectiveRawServiceValues(BookingChild $child, ?Booking $booking = null): array
    {
        $childValues = collect($child->service_interests ?? [])
            ->map(fn ($value) => is_string($value) ? trim($value) : null)
            ->filter()
            ->values();

        if ($childValues->isNotEmpty()) {
            return $childValues->all();
        }

        $booking ??= $child->booking;

        if (blank($booking?->service_interest)) {
            return [];
        }

        return collect(explode(',', (string) $booking->service_interest))
            ->map(fn (string $value) => trim($value))
            ->filter()
            ->values()
            ->all();
    }

    public static function effectiveSchoolSystem(BookingChild $child, ?Booking $booking = null): ?string
    {
        $booking ??= $child->booking;

        return SchoolSystemOptions::normalize($child->school_system ?: $booking?->school_system)
            ?? SchoolSystemOptions::OTHER;
    }

    public static function effectiveGradeLevelId(BookingChild $child, ?Booking $booking = null): ?int
    {
        $booking ??= $child->booking;
        $gradeLevelId = $child->child_grade ?: $booking?->child_grade;

        return $gradeLevelId ? (int) $gradeLevelId : self::defaultGradeLevelId();
    }

    public static function defaultGradeLevelId(): ?int
    {
        if (! Schema::hasTable('grade_levels')) {
            return null;
        }

        $query = DB::table('grade_levels');

        if (Schema::hasColumn('grade_levels', 'active')) {
            $query->where(function ($activeQuery): void {
                $activeQuery->whereNull('active')->orWhere('active', 1);
            });
        }

        $defaultId = (clone $query)
            ->where(function ($defaultQuery): void {
                $defaultQuery->whereRaw('LOWER(title) = ?', ['beginner']);

                if (Schema::hasColumn('grade_levels', 'code')) {
                    $defaultQuery->orWhereRaw('LOWER(code) = ?', ['beginner']);
                }
            })
            ->value('id');

        if ($defaultId) {
            return (int) $defaultId;
        }

        return (int) $query
            ->when(Schema::hasColumn('grade_levels', 'level_order'), fn ($gradeQuery) => $gradeQuery->orderBy('level_order'))
            ->orderBy('id')
            ->value('id') ?: null;
    }

    private static function existingFamilyHasValidLifecycleStatus(array $identityResolution): bool
    {
        if (($identityResolution['outcome'] ?? null) === 'create_new_parent') {
            return true;
        }

        $parentId = $identityResolution['target_parent_id'] ?? null;

        if (! $parentId || ! Schema::hasColumn('parents', 'lifecycle_status')) {
            return true;
        }

        $status = ParentModel::query()
            ->whereKey($parentId)
            ->value('lifecycle_status');

        return in_array($status, self::familyLifecycleStatuses(), true);
    }

    /** @return list<string> */
    private static function familyLifecycleStatuses(): array
    {
        return array_map(
            static fn (FamilyLifecycleStatus $status): string => $status->value,
            FamilyLifecycleStatus::cases()
        );
    }
}
