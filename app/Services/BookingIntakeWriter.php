<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\BookingIntakeReview;
use App\Models\BookingIntakeReviewChild;
use App\Models\ParentModel;
use App\Support\BookingServiceInterest;
use App\Support\SchoolSystemOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookingIntakeWriter
{
    public function __construct(
        protected BookingIntakeDetectionService $bookingIntakeDetectionService,
        protected BookingParentIdentityResolver $parentIdentityResolver
    ) {}

    public function createFromDetectionPayload(array $data, array $detection): Booking
    {
        return DB::transaction(function () use ($data, $detection): Booking {
            if (($detection['route'] ?? null) !== 'normal') {
                throw new InvalidArgumentException('Normal intake writes are only allowed for non-review submissions.');
            }

            $children = collect($detection['child_reviews'] ?? [])
                ->values();

            if ($children->isEmpty()) {
                throw new InvalidArgumentException('At least one child is required to create a booking intake.');
            }

            $selectedExistingBooking = $this->resolveSelectedExistingBooking($data['selected_existing_booking_id'] ?? null);
            $forcedTargetBooking = $selectedExistingBooking && $this->bookingHasActiveChildren($selectedExistingBooking)
                ? $selectedExistingBooking
                : null;

            return $this->persistChildren(
                parentName: $data['parent_name'] ?? null,
                parentEmail: $data['parent_email'] ?? null,
                parentPhone: $data['parent_phone'] ?? null,
                notes: $data['notes'] ?? null,
                children: $children,
                forcedTargetBooking: $forcedTargetBooking,
                forcedReferenceBooking: $selectedExistingBooking
            );
        });
    }

    public function promoteReviewChildren(BookingIntakeReview $review, Collection $approvedChildren, ?string $requestedOutcome = null, ?string $resolutionNote = null): Booking
    {
        return DB::transaction(function () use ($review, $approvedChildren, $requestedOutcome, $resolutionNote): Booking {
            if ($approvedChildren->isEmpty()) {
                throw new InvalidArgumentException('At least one approved child is required before promotion.');
            }

            $resolution = $this->parentIdentityResolver->resolvePromotionOutcome($review, $approvedChildren, $requestedOutcome);

            if (! ($resolution['allowed'] ?? false)) {
                throw new InvalidArgumentException($resolution['blocked_reason'] ?? 'Parent identity could not be resolved for promotion.');
            }

            $this->applyPromotionContactResolution($review, $resolution);
            $targetBooking = $this->resolvedActiveTargetBooking($resolution);

            return $this->persistChildren(
                parentName: $review->parent_name,
                parentEmail: $review->parent_email,
                parentPhone: $review->parent_phone,
                notes: $review->notes,
                children: $approvedChildren->values(),
                forcedTargetBooking: $targetBooking,
                forcedParentId: $resolution['target_parent_id'] ?? null,
                resolution: $resolution,
                resolutionNote: $resolutionNote
            );
        });
    }

    protected function persistChildren(
        ?string $parentName,
        ?string $parentEmail,
        ?string $parentPhone,
        ?string $notes,
        Collection $children,
        ?Booking $forcedTargetBooking = null,
        ?Booking $forcedReferenceBooking = null,
        ?int $forcedParentId = null,
        ?array $resolution = null,
        ?string $resolutionNote = null
    ): Booking {
        $targetBooking = $forcedTargetBooking ?: $this->resolveAppendTargetBooking($children, $parentEmail, $parentPhone);
        $referenceBooking = $forcedReferenceBooking ?: $this->resolveReferenceBooking($children, $targetBooking, $parentEmail, $parentPhone);

        if ($targetBooking) {
            $booking = $targetBooking;
        } else {
            $booking = $this->createBookingContainer(
                parentName: $parentName,
                parentEmail: $parentEmail,
                parentPhone: $parentPhone,
                notes: $notes,
                children: $children,
                referenceBooking: $referenceBooking,
                forcedParentId: $forcedParentId
            );
        }

        $startingSortOrder = BookingChild::query()
            ->where('booking_id', $booking->id)
            ->lockForUpdate()
            ->max('sort_order') ?? 0;

        foreach ($children->values() as $index => $child) {
            BookingChild::create([
                'booking_id' => $booking->id,
                'child_name' => $this->childString($child, 'child_name'),
                'child_age' => $this->childValue($child, 'child_age'),
                'child_grade' => $this->childValue($child, 'child_grade'),
                'school_system' => SchoolSystemOptions::normalize($this->childValue($child, 'school_system')),
                'service_interests' => $this->normalizedServiceInterests($child),
                'consultation_status' => 'pending',
                'workflow_status' => 'pending',
                'meeting_disposition' => null,
                'meeting_disposition_reason' => null,
                'evaluation_status' => null,
                'evaluation_outcome' => 'undecided',
                'consultation_type' => 'undecided',
                'meeting_link' => null,
                'meeting_address' => null,
                'transfer_status' => 'not_transferred',
                'followup_date' => null,
                'current_school' => null,
                'student_id' => null,
                'notes' => null,
                'scheduled_date' => null,
                'scheduled_time' => null,
                'sort_order' => $startingSortOrder + $index + 1,
                'updated_by' => auth()->id(),
            ]);
        }

        if ($resolution) {
            $this->parentIdentityResolver->recordResolution(array_merge($resolution['audit_payload'] ?? [], [
                'booking_id' => $booking->id,
                'matched_booking_id' => $referenceBooking?->id,
                'target_parent_id' => $resolution['target_parent_id'] ?? $booking->parent_id,
                'resolution_note' => $resolutionNote,
            ]));
        }

        return $booking->fresh('children');
    }

    protected function createBookingContainer(
        ?string $parentName,
        ?string $parentEmail,
        ?string $parentPhone,
        ?string $notes,
        Collection $children,
        ?Booking $referenceBooking,
        ?int $forcedParentId = null
    ): Booking {
        $primaryChild = $children->first();
        $legacyServices = $children
            ->flatMap(fn ($child) => $this->normalizedServiceInterests($child))
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        return Booking::create([
            'parent_name' => $this->normalizedText($parentName),
            'parent_email' => $this->normalizedText($parentEmail),
            'parent_phone' => $this->normalizedText($parentPhone),
            'child_name' => $this->childString($primaryChild, 'child_name'),
            'child_age' => $this->childValue($primaryChild, 'child_age'),
            'child_grade' => $this->childValue($primaryChild, 'child_grade'),
            'school_system' => SchoolSystemOptions::normalize($this->childValue($primaryChild, 'school_system')),
            'service_interest' => $legacyServices !== '' ? $legacyServices : null,
            'consultation_type' => null,
            'consultation_date' => null,
            'consultation_time' => null,
            'status' => 'pending',
            'notes' => $this->normalizedText($notes),
            'booking_reference' => null,
            'transfer' => 0,
            'follow_up_date' => null,
            'meeting_address' => null,
            'meeting_link' => null,
            'parent_id' => $forcedParentId ?: $referenceBooking?->parent_id,
        ]);
    }

    protected function resolveSelectedExistingBooking(mixed $bookingId): ?Booking
    {
        if (blank($bookingId)) {
            return null;
        }

        return Booking::query()
            ->lockForUpdate()
            ->find((int) $bookingId);
    }

    protected function applyPromotionContactResolution(BookingIntakeReview $review, array $resolution): void
    {
        if (($resolution['outcome'] ?? null) !== 'verified_contact_update') {
            return;
        }

        $parentEmail = $this->normalizedText($review->parent_email);
        $parentPhone = $this->normalizedText($review->parent_phone);
        $targetParentId = $resolution['target_parent_id'] ?? null;

        if ($targetParentId) {
            $parent = ParentModel::query()
                ->lockForUpdate()
                ->find($targetParentId);

            if ($parent) {
                if ($parentEmail !== null) {
                    $parent->email = $parentEmail;
                }

                if ($parentPhone !== null) {
                    $parent->phone = $parentPhone;
                }

                $parent->save();
            }
        }

        $targetBooking = $this->resolvedActiveTargetBooking($resolution);

        if ($targetBooking) {
            if ($parentEmail !== null) {
                $targetBooking->parent_email = $parentEmail;
            }

            if ($parentPhone !== null) {
                $targetBooking->parent_phone = $parentPhone;
            }

            $targetBooking->save();
        }
    }

    protected function resolvedActiveTargetBooking(array $resolution): ?Booking
    {
        $targetBookingId = $resolution['target_booking_id'] ?? null;

        if (! $targetBookingId) {
            return null;
        }

        $booking = Booking::query()
            ->withCount([
                'children as active_children_count' => function ($query) {
                    $query->where(function ($childQuery) {
                        $childQuery->where('transfer_status', '!=', 'transferred')
                            ->orWhereNull('transfer_status');
                    });
                },
            ])
            ->lockForUpdate()
            ->find($targetBookingId);

        if (! $booking || (int) $booking->active_children_count === 0) {
            return null;
        }

        return $booking;
    }

    protected function resolveAppendTargetBooking(Collection $children, ?string $parentEmail, ?string $parentPhone): ?Booking
    {
        if ($children->isEmpty() || ! $children->every(fn ($child) => $this->childString($child, 'review_reason') === 'existing_family_new_child')) {
            return null;
        }

        $canonicalBooking = $this->bookingIntakeDetectionService->resolveExistingFamilyBooking($parentEmail, $parentPhone);

        if ($canonicalBooking && $this->bookingHasActiveChildren($canonicalBooking)) {
            return $canonicalBooking->fresh();
        }

        $matchedBookingIds = $children
            ->map(fn ($child) => $this->childValue($child, 'matched_booking_id'))
            ->filter()
            ->unique()
            ->values();

        if ($matchedBookingIds->count() !== 1) {
            return null;
        }

        $booking = Booking::query()
            ->withCount([
                'children as active_children_count' => function ($query) {
                    $query->where(function ($childQuery) {
                        $childQuery->where('transfer_status', '!=', 'transferred')
                            ->orWhereNull('transfer_status');
                    });
                },
            ])
            ->lockForUpdate()
            ->find($matchedBookingIds->first());

        if (! $booking || (int) $booking->active_children_count === 0) {
            return null;
        }

        return $booking;
    }

    protected function resolveReferenceBooking(Collection $children, ?Booking $targetBooking, ?string $parentEmail, ?string $parentPhone): ?Booking
    {
        if ($targetBooking) {
            return $targetBooking;
        }

        $canonicalBooking = $this->bookingIntakeDetectionService->resolveExistingFamilyBooking($parentEmail, $parentPhone);

        if ($canonicalBooking) {
            return $canonicalBooking;
        }

        $bookingId = $children
            ->map(fn ($child) => $this->childValue($child, 'matched_booking_id'))
            ->filter()
            ->first();

        if (! $bookingId) {
            return null;
        }

        return Booking::query()
            ->lockForUpdate()
            ->find($bookingId);
    }

    protected function normalizedServiceInterests(mixed $child): Collection
    {
        return collect($this->childValue($child, 'service_interests') ?? [])
            ->map(fn ($service) => BookingServiceInterest::normalize(is_string($service) ? trim($service) : $service))
            ->filter()
            ->values();
    }

    protected function bookingHasActiveChildren(Booking $booking): bool
    {
        return $booking->children()
            ->where(function ($query) {
                $query->where('transfer_status', '!=', 'transferred')
                    ->orWhereNull('transfer_status');
            })
            ->exists();
    }

    protected function childString(mixed $child, string $key): ?string
    {
        return $this->normalizedText($this->childValue($child, $key));
    }

    protected function childValue(mixed $child, string $key): mixed
    {
        if ($child instanceof BookingIntakeReviewChild) {
            return $child->{$key};
        }

        return data_get($child, $key);
    }

    protected function normalizedText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
