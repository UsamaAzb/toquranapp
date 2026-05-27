<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\BookingIntakeReview;
use App\Models\BookingParentBlock;
use App\Support\BookingServiceInterest;
use App\Support\PhoneNormalizer;
use App\Support\SchoolSystemOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BookingIntakeDetectionService
{
    public function analyze(array $data): array
    {
        $normalizedEmail = strtolower(trim((string) ($data['parent_email'] ?? '')));
        $normalizedPhone = PhoneNormalizer::normalize($data['parent_phone'] ?? null);
        $submittedChildren = $this->normalizeSubmittedChildren($data);
        $childReviews = [];
        $blockedParent = null;

        if ($normalizedEmail !== '' || $normalizedPhone !== '') {
            $blockedParent = BookingParentBlock::query()
                ->where(function (Builder $query) use ($normalizedEmail, $normalizedPhone) {
                    if ($normalizedEmail !== '') {
                        $query->orWhere('normalized_email', $normalizedEmail);
                    }

                    if ($normalizedPhone !== '') {
                        $query->orWhereRaw($this->normalizedPhoneExpression('normalized_phone').' = ?', [$normalizedPhone]);
                    }
                })
                ->first();
        }

        if ($blockedParent) {
            foreach ($submittedChildren as $index => $submittedChild) {
                $childReviews[] = [
                    'child_index' => $index,
                    'child_name' => $submittedChild['child_name'] ?? null,
                    'child_age' => $submittedChild['child_age'] ?? null,
                    'child_grade' => $submittedChild['child_grade'] ?? null,
                    'school_system' => $submittedChild['school_system'] ?? null,
                    'service_interests' => $submittedChild['service_interests'] ?? null,
                    'review_reason' => 'blocked_parent',
                    'review_detail' => "Parent email/phone matches blocked intake source #{$blockedParent->id}.",
                    'matched_booking_id' => null,
                    'matched_child_id' => null,
                ];
            }

            return [
                'route' => 'review',
                'reason' => 'blocked_parent',
                'detail' => "Parent email/phone matches blocked intake source #{$blockedParent->id}.",
                'matched_booking_id' => null,
                'matched_child_id' => null,
                'child_reviews' => $childReviews,
            ];
        }

        if (! $this->hasExactFullParentIdentityBooking($normalizedEmail, $normalizedPhone)) {
            $contactMismatch = $this->resolveSuspectedContactMismatchBooking($normalizedEmail, $normalizedPhone);

            if ($contactMismatch) {
                $detail = $this->contactMismatchDetail($contactMismatch);

                foreach ($submittedChildren as $index => $submittedChild) {
                    $childReviews[] = [
                        'child_index' => $index,
                        'child_name' => $submittedChild['child_name'] ?? null,
                        'child_age' => $submittedChild['child_age'] ?? null,
                        'child_grade' => $submittedChild['child_grade'] ?? null,
                        'school_system' => $submittedChild['school_system'] ?? null,
                        'service_interests' => $submittedChild['service_interests'] ?? null,
                        'review_reason' => 'suspected_contact_mismatch',
                        'review_detail' => $detail,
                        'matched_booking_id' => $contactMismatch->id,
                        'matched_child_id' => null,
                    ];
                }

                return [
                    'route' => 'review',
                    'reason' => 'suspected_contact_mismatch',
                    'detail' => $detail,
                    'matched_booking_id' => $contactMismatch->id,
                    'matched_child_id' => null,
                    'child_reviews' => $childReviews,
                ];
            }
        }

        foreach ($submittedChildren as $index => $submittedChild) {
            $normalizedChild = strtolower(trim((string) ($submittedChild['child_name'] ?? '')));

            if ($normalizedChild === '') {
                continue;
            }

            $duplicate = $this->resolveDuplicateChildMatch($normalizedEmail, $normalizedPhone, $normalizedChild);

            if ($duplicate) {
                $isRepeat = $duplicate->transfer_status === 'transferred'
                    || in_array($duplicate->meeting_disposition, ['completed', 'cancelled', 'no_meeting_required'], true);

                $childReviews[] = [
                    'child_index' => $index,
                    'child_name' => $submittedChild['child_name'] ?? null,
                    'child_age' => $submittedChild['child_age'] ?? null,
                    'child_grade' => $submittedChild['child_grade'] ?? null,
                    'school_system' => $submittedChild['school_system'] ?? null,
                    'service_interests' => $submittedChild['service_interests'] ?? null,
                    'review_reason' => $isRepeat ? 'repeat_submission' : 'duplicate_child',
                    'review_detail' => $isRepeat
                        ? "Repeat submission: child {$submittedChild['child_name']} matches a previously known historical record (booking_child #{$duplicate->id})."
                        : "Duplicate child: exact parent identity + child name matches an active consultation (booking_child #{$duplicate->id}, status: ".($duplicate->workflow_status ?: $duplicate->consultation_status).').',
                    'matched_booking_id' => $duplicate->booking_id,
                    'matched_child_id' => $duplicate->id,
                ];

                continue;
            }

            $existingFamily = $this->resolveExistingFamilyBooking($normalizedEmail, $normalizedPhone);

            $childReviews[] = [
                'child_index' => $index,
                'child_name' => $submittedChild['child_name'] ?? null,
                'child_age' => $submittedChild['child_age'] ?? null,
                'child_grade' => $submittedChild['child_grade'] ?? null,
                'school_system' => $submittedChild['school_system'] ?? null,
                'service_interests' => $submittedChild['service_interests'] ?? null,
                'review_reason' => $existingFamily ? 'existing_family_new_child' : 'clean_new_customer',
                'review_detail' => $existingFamily
                    ? "Exact parent identity matches existing booking #{$existingFamily->id}; genuinely new child."
                    : 'No duplicate, repeat, sibling, or blocked-parent match detected.',
                'matched_booking_id' => $existingFamily->id ?? null,
                'matched_child_id' => null,
            ];
        }

        $flaggedChildren = collect($childReviews)
            ->filter(fn (array $childReview) => in_array($childReview['review_reason'], ['duplicate_child', 'repeat_submission', 'blocked_parent', 'suspected_contact_mismatch'], true))
            ->values();

        $summaryReason = $flaggedChildren->isEmpty()
            ? null
            : ($flaggedChildren->count() !== count($childReviews) || $flaggedChildren->pluck('review_reason')->unique()->count() > 1
                ? 'mixed_children'
                : $flaggedChildren->first()['review_reason']);

        return [
            'route' => $flaggedChildren->isEmpty() ? 'normal' : 'review',
            'reason' => $summaryReason,
            'detail' => $summaryReason === 'mixed_children'
                ? 'Submission contains a mix of review-first and normal child outcomes; resolve per child.'
                : ($flaggedChildren->first()['review_detail'] ?? null),
            'matched_booking_id' => $flaggedChildren->first()['matched_booking_id'] ?? null,
            'matched_child_id' => $flaggedChildren->first()['matched_child_id'] ?? null,
            'child_reviews' => $childReviews,
        ];
    }

    public function resolveExistingFamilyBooking(?string $parentEmail, ?string $parentPhone): ?Booking
    {
        $normalizedEmail = strtolower(trim((string) $parentEmail));
        $normalizedPhone = PhoneNormalizer::normalize($parentPhone);

        if ($normalizedEmail === '' && $normalizedPhone === '') {
            return null;
        }

        return Booking::query()
            ->where(function (Builder $query) use ($normalizedEmail, $normalizedPhone) {
                $this->applyExactParentIdentityMatch($query, $normalizedEmail, $normalizedPhone);
            })
            ->withCount([
                'children as active_children_count' => function ($query) {
                    $query->where(function (Builder $childQuery) {
                        $childQuery->where('transfer_status', '!=', 'transferred')
                            ->orWhereNull('transfer_status');
                    });
                },
            ])
            ->orderByRaw('CASE WHEN active_children_count > 0 THEN 0 ELSE 1 END')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    protected function hasExactFullParentIdentityBooking(string $normalizedEmail, string $normalizedPhone): bool
    {
        if ($normalizedEmail === '' || $normalizedPhone === '') {
            return false;
        }

        return Booking::query()
            ->whereRaw($this->normalizedEmailExpression('parent_email').' = ?', [$normalizedEmail])
            ->whereRaw($this->normalizedPhoneExpression('parent_phone').' = ?', [$normalizedPhone])
            ->exists();
    }

    protected function resolveSuspectedContactMismatchBooking(string $normalizedEmail, string $normalizedPhone): ?Booking
    {
        if ($normalizedEmail === '' || $normalizedPhone === '') {
            return null;
        }

        $parentEmailExpression = $this->normalizedEmailExpression('parent_email');
        $parentPhoneExpression = $this->normalizedPhoneExpression('parent_phone');

        $emailOverlapCondition = "{$parentEmailExpression} = ? AND {$parentPhoneExpression} != '' AND {$parentPhoneExpression} != ?";
        $phoneOverlapCondition = "{$parentPhoneExpression} = ? AND {$parentEmailExpression} != '' AND {$parentEmailExpression} != ?";

        return Booking::query()
            ->select('bookings.*')
            ->selectRaw(
                "CASE WHEN {$emailOverlapCondition} THEN 'email' WHEN {$phoneOverlapCondition} THEN 'phone' ELSE NULL END as contact_mismatch_kind",
                [$normalizedEmail, $normalizedPhone, $normalizedPhone, $normalizedEmail]
            )
            ->where(function (Builder $query) use ($emailOverlapCondition, $phoneOverlapCondition, $normalizedEmail, $normalizedPhone) {
                $query->whereRaw($emailOverlapCondition, [$normalizedEmail, $normalizedPhone])
                    ->orWhereRaw($phoneOverlapCondition, [$normalizedPhone, $normalizedEmail]);
            })
            ->withCount([
                'children as active_children_count' => function ($query) {
                    $query->where(function (Builder $childQuery) {
                        $childQuery->where('transfer_status', '!=', 'transferred')
                            ->orWhereNull('transfer_status');
                    });
                },
            ])
            ->orderByRaw('CASE WHEN active_children_count > 0 THEN 0 ELSE 1 END')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    protected function contactMismatchDetail(Booking $booking): string
    {
        if ($booking->getAttribute('contact_mismatch_kind') === 'phone') {
            return "Contact mismatch: submitted phone matches existing booking #{$booking->id} but email differs. Admin verification required before child intake proceeds.";
        }

        return "Contact mismatch: submitted email matches existing booking #{$booking->id} but phone differs. Admin verification required before child intake proceeds.";
    }

    protected function applyExactParentIdentityMatch(Builder $query, string $normalizedEmail, string $normalizedPhone): void
    {
        $hasEmail = $normalizedEmail !== '';
        $hasPhone = $normalizedPhone !== '';
        $parentPhoneExpression = $this->normalizedPhoneExpression('parent_phone');
        $parentEmailExpression = $this->normalizedEmailExpression('parent_email');
        $blankPhoneExpression = $this->blankNormalizedPhoneExpression('parent_phone');
        $blankEmailExpression = $this->blankNormalizedEmailExpression('parent_email');

        if ($hasEmail && $hasPhone) {
            $query->where(function (Builder $query) use (
                $normalizedEmail,
                $normalizedPhone,
                $parentEmailExpression,
                $parentPhoneExpression,
                $blankPhoneExpression,
                $blankEmailExpression
            ) {
                $query->where(function (Builder $query) use ($normalizedEmail, $normalizedPhone, $parentEmailExpression, $parentPhoneExpression) {
                    $query->whereRaw("{$parentEmailExpression} = ?", [$normalizedEmail])
                        ->whereRaw("{$parentPhoneExpression} = ?", [$normalizedPhone]);
                })->orWhere(function (Builder $query) use ($normalizedEmail, $parentEmailExpression, $blankPhoneExpression) {
                    $query->whereRaw("{$parentEmailExpression} = ?", [$normalizedEmail])
                        ->where(function (Builder $query) use ($blankPhoneExpression) {
                            $query->whereNull('parent_phone')
                                ->orWhereRaw($blankPhoneExpression);
                        });
                })->orWhere(function (Builder $query) use ($normalizedPhone, $parentPhoneExpression, $blankEmailExpression) {
                    $query->whereRaw("{$parentPhoneExpression} = ?", [$normalizedPhone])
                        ->where(function (Builder $query) use ($blankEmailExpression) {
                            $query->whereNull('parent_email')
                                ->orWhereRaw($blankEmailExpression);
                        });
                });
            });

            return;
        }

        if ($hasEmail) {
            $query->whereRaw("{$parentEmailExpression} = ?", [$normalizedEmail]);

            return;
        }

        if ($hasPhone) {
            $query->whereRaw("{$parentPhoneExpression} = ?", [$normalizedPhone]);

            return;
        }

        $query->whereRaw('1 = 0');
    }

    public function submissionFingerprint(array $data): string
    {
        $normalizedEmail = strtolower(trim((string) ($data['parent_email'] ?? '')));
        $normalizedPhone = PhoneNormalizer::normalize($data['parent_phone'] ?? null);
        $submittedChildren = $this->normalizedFingerprintChildren($data);

        return hash('sha256', json_encode([
            'parent_email' => $normalizedEmail !== '' ? $normalizedEmail : null,
            'parent_phone' => $normalizedPhone !== '' ? $normalizedPhone : null,
            'children' => $submittedChildren,
        ], JSON_UNESCAPED_UNICODE));
    }

    public function withSubmissionFingerprintLock(array $data, callable $callback): mixed
    {
        return DB::transaction(function () use ($data, $callback) {
            $fingerprint = $this->submissionFingerprint($data);
            $lockPayload = $this->submissionLockPayload($data, $fingerprint);
            $timestamp = now();

            DB::table('booking_intake_submission_locks')->insertOrIgnore($lockPayload + [
                'first_seen_at' => $timestamp,
                'last_seen_at' => $timestamp,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $lock = DB::table('booking_intake_submission_locks')
                ->where('submission_fingerprint', $fingerprint)
                ->lockForUpdate()
                ->first();

            if (! $lock) {
                throw new RuntimeException('Unable to acquire intake submission fingerprint lock.');
            }

            DB::table('booking_intake_submission_locks')
                ->where('id', $lock->id)
                ->update($lockPayload + [
                    'last_seen_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

            return $callback($fingerprint);
        });
    }

    protected function submissionLockPayload(array $data, string $fingerprint): array
    {
        $normalizedEmail = strtolower(trim((string) ($data['parent_email'] ?? '')));
        $normalizedPhone = PhoneNormalizer::normalize($data['parent_phone'] ?? null);
        $submittedChildren = $this->normalizedFingerprintChildren($data);

        return [
            'submission_fingerprint' => $fingerprint,
            'parent_email' => $normalizedEmail !== '' ? $normalizedEmail : null,
            'parent_phone' => $normalizedPhone !== '' ? $normalizedPhone : null,
            'child_names_hash' => hash('sha256', json_encode($submittedChildren, JSON_UNESCAPED_UNICODE)),
        ];
    }

    protected function normalizedFingerprintChildren(array $data): array
    {
        $submittedChildren = array_map(function (array $child): array {
            return [
                'child_name' => strtolower(trim((string) ($child['child_name'] ?? ''))),
            ];
        }, $this->normalizeSubmittedChildren($data));

        usort($submittedChildren, fn (array $left, array $right) => strcmp($left['child_name'], $right['child_name']));

        return $submittedChildren;
    }

    /**
     * Production intake callers must invoke this from within withSubmissionFingerprintLock().
     *
     * The duplicate-key recovery below assumes the normalized submission lock is already
     * serializing public/admin intake for the same parent-child fingerprint. Feature tests
     * may call this method directly to exercise review persistence branches in isolation.
     */
    public function writeReviewRecord(array $data, array $detection): BookingIntakeReview
    {
        if (($detection['route'] ?? null) !== 'review' || blank($detection['reason'] ?? null)) {
            throw new \InvalidArgumentException('Review records can only be written for flagged review-first submissions.');
        }

        return DB::transaction(function () use ($data, $detection) {
            $submittedChildren = $this->normalizeSubmittedChildren($data);
            $primaryChild = $submittedChildren[0] ?? [];
            $fingerprint = $this->submissionFingerprint($data);
            $childReviews = $detection['child_reviews'] ?? [];

            $payload = [
                'parent_name' => $data['parent_name'] ?? null,
                'parent_email' => $data['parent_email'] ?? null,
                'parent_phone' => $data['parent_phone'] ?? null,
                'child_name' => $primaryChild['child_name'] ?? ($data['child_name'] ?? null),
                'child_age' => $primaryChild['child_age'] ?? ($data['child_age'] ?? null),
                'child_grade' => $primaryChild['child_grade'] ?? ($data['child_grade'] ?? null),
                'school_system' => $primaryChild['school_system'] ?? ($data['school_system'] ?? null),
                'service_interests' => $primaryChild['service_interests'] ?? ($data['service_interests'] ?? null),
                'children_payload' => $submittedChildren,
                'child_count' => count($submittedChildren),
                'notes' => $data['notes'] ?? null,
                'detection_reason' => $detection['reason'],
                'detection_detail' => $detection['detail'],
                'matched_booking_id' => $detection['matched_booking_id'],
                'matched_child_id' => $detection['matched_child_id'],
            ];

            $existingOpenReview = BookingIntakeReview::query()
                ->where('open_submission_fingerprint', $fingerprint)
                ->where('status', 'pending_review')
                ->lockForUpdate()
                ->first();

            if ($existingOpenReview) {
                return $this->refreshOpenReviewIfUnstarted($existingOpenReview, $payload, $childReviews);
            }

            try {
                $review = $this->createReviewWithChildren($payload, $childReviews, $fingerprint);
            } catch (QueryException $exception) {
                if (! $this->isDuplicateKeyException($exception)) {
                    throw $exception;
                }

                $review = BookingIntakeReview::query()
                    ->where('open_submission_fingerprint', $fingerprint)
                    ->lockForUpdate()
                    ->first();

                if (! $review) {
                    throw $exception;
                }

                if ($review->status === 'pending_review') {
                    return $this->refreshOpenReviewIfUnstarted($review, $payload, $childReviews);
                }

                $review->update(['open_submission_fingerprint' => null]);

                return $this->createReviewWithChildren($payload, $childReviews, $fingerprint);
            }

            return $review;
        });
    }

    protected function resolveDuplicateChildMatch(string $normalizedEmail, string $normalizedPhone, string $normalizedChild): ?BookingChild
    {
        return BookingChild::query()
            ->whereHas('booking', function (Builder $query) use ($normalizedEmail, $normalizedPhone) {
                $this->applyExactParentIdentityMatch($query, $normalizedEmail, $normalizedPhone);
            })
            ->whereRaw('LOWER(TRIM(child_name)) = ?', [$normalizedChild])
            ->orderByRaw(
                "CASE WHEN transfer_status = 'transferred' OR meeting_disposition IN (?, ?, ?) THEN 1 ELSE 0 END",
                ['completed', 'cancelled', 'no_meeting_required']
            )
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();
    }

    protected function refreshOpenReviewIfUnstarted(BookingIntakeReview $review, array $payload, array $childReviews): BookingIntakeReview
    {
        if ($review->reviewChildren()
            ->where('resolution_status', '!=', 'pending_decision')
            ->exists()
        ) {
            return $review->fresh('reviewChildren');
        }

        $review->update($payload);
        $review->reviewChildren()->delete();

        foreach ($childReviews as $childReview) {
            $review->reviewChildren()->create($childReview);
        }

        return $review->fresh('reviewChildren');
    }

    protected function createReviewWithChildren(array $payload, array $childReviews, string $fingerprint): BookingIntakeReview
    {
        $review = BookingIntakeReview::create($payload + [
            'open_submission_fingerprint' => $fingerprint,
            'status' => 'pending_review',
        ]);

        foreach ($childReviews as $childReview) {
            $review->reviewChildren()->create($childReview);
        }

        return $review->fresh('reviewChildren');
    }

    protected function normalizeSubmittedChildren(array $data): array
    {
        if (! empty($data['children']) && is_array($data['children'])) {
            return array_values(array_map(function (array $child): array {
                return [
                    'child_name' => $child['child_name'] ?? null,
                    'child_age' => $child['child_age'] ?? null,
                    'child_grade' => $child['child_grade'] ?? null,
                    'school_system' => SchoolSystemOptions::normalize($child['school_system'] ?? null),
                    'service_interests' => $this->normalizeServiceInterests($child['service_interests'] ?? []),
                ];
            }, $data['children']));
        }

        return [[
            'child_name' => $data['child_name'] ?? null,
            'child_age' => $data['child_age'] ?? null,
            'child_grade' => $data['child_grade'] ?? null,
            'school_system' => SchoolSystemOptions::normalize($data['school_system'] ?? null),
            'service_interests' => $this->normalizeServiceInterests($data['service_interests'] ?? []),
        ]];
    }

    protected function normalizeServiceInterests(array|string|null $serviceInterests): array
    {
        $values = is_array($serviceInterests) ? $serviceInterests : [$serviceInterests];

        return collect($values)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter()
            ->map(fn ($value) => BookingServiceInterest::normalize($value))
            ->filter()
            ->values()
            ->all();
    }

    protected function normalizedEmailExpression(string $column): string
    {
        return "LOWER(TRIM(COALESCE({$column}, '')))";
    }

    protected function blankNormalizedEmailExpression(string $column): string
    {
        return "TRIM(COALESCE({$column}, '')) = ''";
    }

    protected function normalizedPhoneExpression(string $column): string
    {
        return PhoneNormalizer::sqlExpression($column, DB::connection()->getDriverName());
    }

    protected function blankNormalizedPhoneExpression(string $column): string
    {
        return $this->normalizedPhoneExpression($column)." = ''";
    }

    protected function isDuplicateKeyException(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);

        // 1062 = MySQL/MariaDB duplicate entry
        // 19 / 2067 = SQLite SQLITE_CONSTRAINT / SQLITE_CONSTRAINT_UNIQUE
        return $sqlState === '23000'
            && in_array($driverCode, [1062, 19, 2067], true);
    }
}
