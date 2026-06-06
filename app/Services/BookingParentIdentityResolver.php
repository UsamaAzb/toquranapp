<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\BookingIntakeReview;
use App\Models\BookingIntakeReviewChild;
use App\Models\BookingParentIdentityResolution;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use App\Support\PhoneNormalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingParentIdentityResolver
{
    public function normalizeEmail(?string $email): string
    {
        return strtolower(trim((string) $email));
    }

    public function normalizePhone(?string $phone): string
    {
        return PhoneNormalizer::normalize($phone);
    }

    public function recordResolution(array $payload): ?BookingParentIdentityResolution
    {
        if (! Schema::hasTable('booking_parent_identity_resolutions')) {
            return null;
        }

        return BookingParentIdentityResolution::create($payload + [
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);
    }

    public function findParentByContacts(?string $email, ?string $phone): array
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $normalizedPhone = $this->normalizePhone($phone);
        $emailIds = collect();
        $phoneIds = collect();

        if ($normalizedEmail !== '' && $this->tableHasColumn('parents', 'email')) {
            $emailIds = ParentModel::query()
                ->whereRaw($this->normalizedEmailExpression('email').' = ?', [$normalizedEmail])
                ->pluck('id')
                ->map(fn ($id): int => (int) $id);
        }

        if ($normalizedPhone !== '' && $this->tableHasColumn('parents', 'phone')) {
            $phoneIds = ParentModel::query()
                ->whereRaw($this->normalizedPhoneExpression('phone').' = ?', [$normalizedPhone])
                ->pluck('id')
                ->map(fn ($id): int => (int) $id);

            if ($this->parentPhoneNumbersAvailable()) {
                $phoneIds = $phoneIds->merge(
                    DB::table('parent_phone_numbers')
                        ->where('normalized_phone', $normalizedPhone)
                        ->pluck('parent_id')
                        ->map(fn ($id): int => (int) $id)
                );
            }
        }

        return $this->contactMatchResult('parent', $normalizedEmail, $normalizedPhone, $emailIds, $phoneIds) + [
            'multi_phone_available' => $this->parentPhoneNumbersAvailable(),
        ];
    }

    public function findUserByContacts(?string $email, ?string $phone): array
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $normalizedPhone = $this->normalizePhone($phone);
        $emailIds = collect();
        $phoneIds = collect();

        if ($normalizedEmail !== '' && $this->tableHasColumn('users', 'email')) {
            $emailIds = User::query()
                ->whereRaw($this->normalizedEmailExpression('email').' = ?', [$normalizedEmail])
                ->pluck('id')
                ->map(fn ($id): int => (int) $id);
            $emailIds = $this->parentAccountUserIds($emailIds);
        }

        if ($normalizedPhone !== '' && $this->tableHasColumn('users', 'phone')) {
            $phoneIds = User::query()
                ->whereRaw($this->normalizedPhoneExpression('phone').' = ?', [$normalizedPhone])
                ->pluck('id')
                ->map(fn ($id): int => (int) $id);
            $phoneIds = $this->parentAccountUserIds($phoneIds);
        }

        $result = $this->contactMatchResult('user', $normalizedEmail, $normalizedPhone, $emailIds, $phoneIds);

        return [
            'normalized_email' => $result['normalized_email'],
            'normalized_phone' => $result['normalized_phone'],
            'email_user_ids' => $result['email_parent_ids'],
            'phone_user_ids' => $result['phone_parent_ids'],
            'all_user_ids' => $result['all_parent_ids'],
            'resolved_user_id' => $result['resolved_parent_id'],
            'blocked_reason' => $result['blocked_reason'] ? str_replace('parent', 'user', $result['blocked_reason']) : null,
        ];
    }

    public function findBookingFamilyByContacts(?string $email, ?string $phone): array
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $normalizedPhone = $this->normalizePhone($phone);
        $emailIds = collect();
        $phoneIds = collect();

        if ($normalizedEmail !== '') {
            $emailIds = Booking::query()
                ->whereRaw($this->normalizedEmailExpression('parent_email').' = ?', [$normalizedEmail])
                ->pluck('id')
                ->map(fn ($id): int => (int) $id);
        }

        if ($normalizedPhone !== '') {
            $phoneIds = Booking::query()
                ->whereRaw($this->normalizedPhoneExpression('parent_phone').' = ?', [$normalizedPhone])
                ->pluck('id')
                ->map(fn ($id): int => (int) $id);
        }

        $allIds = $emailIds->merge($phoneIds)->unique()->values();
        $blockedReason = null;

        if ($normalizedEmail !== '' && $normalizedPhone !== '' && $emailIds->isNotEmpty() && $phoneIds->isNotEmpty() && $emailIds->intersect($phoneIds)->isEmpty()) {
            $blockedReason = 'Email and phone match different booking families.';
        }

        $canonical = app(BookingIntakeDetectionService::class)->resolveExistingFamilyBooking($email, $phone);

        return [
            'normalized_email' => $normalizedEmail,
            'normalized_phone' => $normalizedPhone,
            'email_booking_ids' => $emailIds->unique()->values()->all(),
            'phone_booking_ids' => $phoneIds->unique()->values()->all(),
            'all_booking_ids' => $allIds->all(),
            'resolved_booking_id' => $canonical?->id,
            'resolved_parent_id' => $canonical?->parent_id,
            'blocked_reason' => $blockedReason,
        ];
    }

    public function childCollisionSummary(Collection $submittedChildren, ?int $bookingId = null, ?int $parentId = null): array
    {
        $existingChildren = collect();

        if ($bookingId) {
            $existingChildren = $existingChildren->merge(
                BookingChild::query()
                    ->where('booking_id', $bookingId)
                    ->get(['id', 'child_name', 'child_grade', 'child_age', 'school_system'])
                    ->map(fn (BookingChild $child): array => [
                        'source' => 'booking_child',
                        'id' => $child->id,
                        'name' => $child->child_name,
                        'grade' => $child->child_grade,
                        'age' => $child->child_age,
                        'school' => $child->school_system,
                    ])
            );
        }

        if ($parentId) {
            $studentColumns = ['id'];

            if ($this->tableHasColumn('students', 'first_name')) {
                $studentColumns[] = 'first_name';
            }

            if ($this->tableHasColumn('students', 'grade_level_id')) {
                $studentColumns[] = 'grade_level_id';
            }

            if ($this->tableHasColumn('students', 'grade_name')) {
                $studentColumns[] = 'grade_name';
            }

            if ($this->tableHasColumn('students', 'age')) {
                $studentColumns[] = 'age';
            }

            if ($this->tableHasColumn('students', 'current_school')) {
                $studentColumns[] = 'current_school';
            }

            if ($this->tableHasColumn('students', 'school_system')) {
                $studentColumns[] = 'school_system';
            }

            $existingChildren = $existingChildren->merge(
                Student::query()
                    ->where('parent_id', $parentId)
                    ->get($studentColumns)
                    ->map(fn (Student $student): array => [
                        'source' => 'student',
                        'id' => $student->id,
                        'name' => $student->first_name,
                        'grade' => $student->grade_level_id ?? $student->grade_name,
                        'age' => $student->age,
                        'school' => $student->current_school ?? $student->school_system,
                    ])
            );
        }

        $duplicates = [];
        $ambiguous = [];

        foreach ($submittedChildren as $submittedChild) {
            $submitted = [
                'id' => $submittedChild instanceof BookingIntakeReviewChild ? $submittedChild->id : data_get($submittedChild, 'id'),
                'name' => $this->childValue($submittedChild, 'child_name'),
                'grade' => $this->childValue($submittedChild, 'child_grade'),
                'age' => $this->childValue($submittedChild, 'child_age'),
                'school' => $this->childValue($submittedChild, 'school_system'),
            ];
            $submittedName = $this->normalizeChildName($submitted['name']);

            if ($submittedName === '') {
                continue;
            }

            foreach ($existingChildren as $existing) {
                if ($submittedName !== $this->normalizeChildName($existing['name'] ?? null)) {
                    continue;
                }

                $match = [
                    'submitted' => $submitted,
                    'existing' => $existing,
                ];

                if ($this->supportingChildFieldMatches($submitted, $existing)) {
                    $duplicates[] = $match;
                } else {
                    $ambiguous[] = $match;
                }
            }
        }

        return [
            'has_duplicate_like' => $duplicates !== [],
            'has_ambiguous_same_name' => $ambiguous !== [],
            'duplicates' => $duplicates,
            'ambiguous' => $ambiguous,
        ];
    }

    protected function supportingChildFieldMatches(array $submitted, array $existing): bool
    {
        foreach (['grade', 'age', 'school'] as $field) {
            $left = $this->normalizeComparableChildField($submitted[$field] ?? null);
            $right = $this->normalizeComparableChildField($existing[$field] ?? null);

            if ($left !== '' && $right !== '' && $left === $right) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeChildName(?string $name): string
    {
        return preg_replace('/\s+/', ' ', strtolower(trim((string) $name))) ?? '';
    }

    protected function normalizeComparableChildField(mixed $value): string
    {
        return preg_replace('/\s+/', ' ', strtolower(trim((string) $value))) ?? '';
    }

    protected function childValue(mixed $child, string $key): mixed
    {
        if ($child instanceof BookingIntakeReviewChild) {
            return $child->{$key};
        }

        return data_get($child, $key);
    }

    public function resolvePromotionOutcome(BookingIntakeReview $review, Collection $reviewChildren, ?string $requestedOutcome = null): array
    {
        $children = $reviewChildren->values();
        $base = $this->basePromotionResult($review);

        if ($children->isEmpty()) {
            return array_merge($base, [
                'allowed' => false,
                'outcome' => 'blocked_conflict',
                'requires_data_correction' => true,
                'blocked_reason' => 'At least one approved child is required before promotion.',
            ]);
        }

        $blockedChild = $children->first(fn (BookingIntakeReviewChild $child): bool => in_array($child->review_reason, ['blocked_parent', 'duplicate_child', 'repeat_submission'], true));

        if ($blockedChild) {
            return array_merge($base, [
                'allowed' => false,
                'outcome' => 'blocked_conflict',
                'requires_data_correction' => true,
                'blocked_reason' => 'Duplicate, repeat, or blocked-parent review rows must be corrected into a sibling/new-customer case or dismissed.',
                'child_collision_summary' => [
                    'blocked_review_child_id' => $blockedChild->id,
                    'blocked_review_reason' => $blockedChild->review_reason,
                ],
            ]);
        }

        $parentMatch = $this->findParentByContacts($review->parent_email, $review->parent_phone);
        $bookingMatch = $this->findBookingFamilyByContacts($review->parent_email, $review->parent_phone);

        if ($parentMatch['blocked_reason']) {
            return array_merge($base, [
                'allowed' => false,
                'outcome' => 'blocked_conflict',
                'conflicting_parent_ids' => $parentMatch['all_parent_ids'],
                'blocked_reason' => $parentMatch['blocked_reason'],
            ]);
        }

        if ($bookingMatch['blocked_reason']) {
            return array_merge($base, [
                'allowed' => false,
                'outcome' => 'blocked_conflict',
                'conflicting_booking_ids' => $bookingMatch['all_booking_ids'],
                'blocked_reason' => $bookingMatch['blocked_reason'],
            ]);
        }

        $reasons = $children->pluck('review_reason')->unique()->values();

        if ($requestedOutcome === 'verified_contact_update') {
            return $this->verifiedContactPromotionResult($review, $children, $bookingMatch, $parentMatch, $base);
        }

        if ($reasons->contains('suspected_contact_mismatch')) {
            return array_merge($base, [
                'allowed' => false,
                'outcome' => 'blocked_conflict',
                'requires_admin_confirmation' => true,
                'requires_contact_update' => true,
                'target_booking_id' => $this->singleMatchedBookingId($review, $children),
                'blocked_reason' => 'Contact mismatch requires the explicit verified contact update action before promotion.',
            ]);
        }

        if ($reasons->every(fn (string $reason): bool => $reason === 'clean_new_customer')) {
            if ($parentMatch['resolved_parent_id'] || $bookingMatch['resolved_booking_id']) {
                return array_merge($base, [
                    'allowed' => false,
                    'outcome' => 'blocked_conflict',
                    'target_booking_id' => $bookingMatch['resolved_booking_id'],
                    'target_parent_id' => $parentMatch['resolved_parent_id'],
                    'blocked_reason' => 'Clean-new promotion is blocked because the submitted contact now matches an existing family.',
                ]);
            }

            return array_merge($base, [
                'allowed' => true,
                'outcome' => 'clean_new_family',
                'audit_payload' => $this->promotionAuditPayload($review, 'clean_new_family'),
            ]);
        }

        if ($reasons->every(fn (string $reason): bool => $reason === 'existing_family_new_child')) {
            $targetBookingId = $bookingMatch['resolved_booking_id'] ?: $this->singleMatchedBookingId($review, $children);
            $targetBooking = $targetBookingId ? Booking::query()->find($targetBookingId) : null;
            $targetParentId = $targetBooking?->parent_id ?: $parentMatch['resolved_parent_id'];
            $collisionSummary = $this->childCollisionSummary($children, $targetBooking?->id, $targetParentId);

            if ($collisionSummary['has_duplicate_like'] || $collisionSummary['has_ambiguous_same_name']) {
                return array_merge($base, [
                    'allowed' => false,
                    'outcome' => 'blocked_conflict',
                    'target_booking_id' => $targetBooking?->id,
                    'target_parent_id' => $targetParentId,
                    'requires_data_correction' => true,
                    'blocked_reason' => 'Submitted child still looks duplicate-like for the resolved family. Correct the child data or dismiss the row.',
                    'child_collision_summary' => $collisionSummary,
                ]);
            }

            if (! $targetBooking && ! $targetParentId) {
                return array_merge($base, [
                    'allowed' => false,
                    'outcome' => 'blocked_conflict',
                    'blocked_reason' => 'Existing-family promotion could not resolve a target family.',
                ]);
            }

            return array_merge($base, [
                'allowed' => true,
                'outcome' => 'new_sibling_existing_family',
                'target_booking_id' => $targetBooking?->id,
                'target_parent_id' => $targetParentId,
                'child_collision_summary' => $collisionSummary,
                'audit_payload' => $this->promotionAuditPayload($review, 'new_sibling_existing_family', $targetBooking?->id, $targetParentId, $collisionSummary),
            ]);
        }

        return array_merge($base, [
            'allowed' => false,
            'outcome' => 'blocked_conflict',
            'requires_data_correction' => true,
            'blocked_reason' => 'Approved child rows must resolve to one compatible promotion outcome.',
        ]);
    }

    protected function verifiedContactPromotionResult(
        BookingIntakeReview $review,
        Collection $children,
        array $bookingMatch,
        array $parentMatch,
        array $base
    ): array {
        $targetBookingId = $this->singleMatchedBookingId($review, $children) ?: $bookingMatch['resolved_booking_id'];
        $targetBooking = $targetBookingId ? Booking::query()->find($targetBookingId) : null;
        $targetParentId = $targetBooking?->parent_id;

        if (! $targetBooking) {
            $targetParentId = $parentMatch['resolved_parent_id'];
        }

        if (! $targetBooking && ! $targetParentId) {
            return array_merge($base, [
                'allowed' => false,
                'outcome' => 'blocked_conflict',
                'requires_admin_confirmation' => true,
                'requires_contact_update' => true,
                'blocked_reason' => 'Verified contact update requires one resolved existing family.',
            ]);
        }

        $conflictingParentIds = collect($parentMatch['all_parent_ids'])
            ->reject(fn (int $id): bool => $targetParentId !== null && $id === (int) $targetParentId)
            ->values()
            ->all();
        $conflictingBookingIds = collect($bookingMatch['all_booking_ids'])
            ->reject(fn (int $id): bool => $targetBooking !== null && $id === (int) $targetBooking->id)
            ->values()
            ->all();

        if ($conflictingParentIds !== [] || $conflictingBookingIds !== []) {
            return array_merge($base, [
                'allowed' => false,
                'outcome' => 'blocked_conflict',
                'requires_admin_confirmation' => true,
                'requires_contact_update' => true,
                'target_booking_id' => $targetBooking?->id,
                'target_parent_id' => $targetParentId,
                'conflicting_parent_ids' => $conflictingParentIds,
                'conflicting_booking_ids' => $conflictingBookingIds,
                'blocked_reason' => 'Verified contact update is blocked because the submitted contact matches a different family.',
            ]);
        }

        $targetParent = $targetParentId ? ParentModel::query()->find($targetParentId) : null;
        $previousEmail = $targetParent?->email ?: $targetBooking?->parent_email;
        $previousPhone = $targetParent?->phone ?: $targetBooking?->parent_phone;
        $contactAction = $this->contactUpdateAction($previousEmail, $previousPhone, $review->parent_email, $review->parent_phone);

        return array_merge($base, [
            'allowed' => true,
            'outcome' => 'verified_contact_update',
            'target_booking_id' => $targetBooking?->id,
            'target_parent_id' => $targetParentId,
            'requires_admin_confirmation' => true,
            'requires_contact_update' => $contactAction !== 'none',
            'previous_parent_email' => $previousEmail,
            'previous_parent_phone' => $previousPhone,
            'resolved_parent_email' => $review->parent_email,
            'resolved_parent_phone' => $review->parent_phone,
            'contact_action' => $contactAction,
            'audit_payload' => $this->promotionAuditPayload($review, 'verified_contact_update', $targetBooking?->id, $targetParentId, null, $contactAction, $previousEmail, $previousPhone),
        ]);
    }

    protected function singleMatchedBookingId(BookingIntakeReview $review, Collection $children): ?int
    {
        $ids = $children
            ->pluck('matched_booking_id')
            ->push($review->matched_booking_id)
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        return $ids->count() === 1 ? $ids->first() : null;
    }

    protected function basePromotionResult(BookingIntakeReview $review): array
    {
        return [
            'allowed' => false,
            'outcome' => 'blocked_conflict',
            'target_booking_id' => null,
            'target_parent_id' => null,
            'conflicting_booking_ids' => [],
            'conflicting_parent_ids' => [],
            'conflicting_user_ids' => [],
            'requires_admin_confirmation' => false,
            'requires_contact_update' => false,
            'requires_data_correction' => false,
            'multi_phone_available' => $this->parentPhoneNumbersAvailable(),
            'blocked_reason' => null,
            'child_collision_summary' => null,
            'previous_parent_email' => null,
            'previous_parent_phone' => null,
            'resolved_parent_email' => $review->parent_email,
            'resolved_parent_phone' => $review->parent_phone,
            'contact_action' => 'none',
            'audit_payload' => [],
        ];
    }

    protected function promotionAuditPayload(
        BookingIntakeReview $review,
        string $outcome,
        ?int $targetBookingId = null,
        ?int $targetParentId = null,
        ?array $childCollisionSummary = null,
        string $contactAction = 'none',
        ?string $previousEmail = null,
        ?string $previousPhone = null
    ): array {
        return [
            'stage' => 'intake_review_promotion',
            'outcome' => $outcome,
            'booking_intake_review_id' => $review->id,
            'matched_booking_id' => $targetBookingId ?: $review->matched_booking_id,
            'target_parent_id' => $targetParentId,
            'submitted_parent_email' => $review->parent_email,
            'submitted_parent_phone' => $review->parent_phone,
            'previous_parent_email' => $previousEmail,
            'previous_parent_phone' => $previousPhone,
            'resolved_parent_email' => $review->parent_email,
            'resolved_parent_phone' => $review->parent_phone,
            'contact_action' => $contactAction,
            'child_identity_summary' => $childCollisionSummary ? json_encode($childCollisionSummary, JSON_UNESCAPED_UNICODE) : null,
        ];
    }

    public function resolveTransferTarget(BookingChild $child): array
    {
        $child->loadMissing('booking');
        $booking = $child->booking;
        $base = $this->baseTransferResult($child);

        if (! $booking) {
            return array_merge($base, [
                'allowed' => false,
                'outcome' => 'blocked_conflict',
                'blocked_reason' => 'Child row is not linked to a booking.',
            ]);
        }

        $parentMatch = $this->findParentByContacts($booking->parent_email, $booking->parent_phone);
        $userMatch = $this->findUserByContacts($booking->parent_email, $booking->parent_phone);

        if ($booking->parent_id) {
            return $this->resolveLinkedParentTransfer($child, $booking, $parentMatch, $userMatch, $base);
        }

        if ($parentMatch['blocked_reason']) {
            return array_merge($base, [
                'allowed' => false,
                'outcome' => 'blocked_conflict',
                'conflicting_parent_ids' => $parentMatch['all_parent_ids'],
                'blocked_reason' => $parentMatch['blocked_reason'],
                'audit_payload' => $this->transferAuditPayload($child, 'blocked_conflict', null, $parentMatch['all_parent_ids'][0] ?? null, $parentMatch['blocked_reason']),
            ]);
        }

        if ($parentMatch['resolved_parent_id']) {
            $parent = ParentModel::query()->find($parentMatch['resolved_parent_id']);
            $conflictingUserIds = $parent ? $this->conflictingUserIdsForParent($userMatch, $parent) : [];

            if ($conflictingUserIds !== []) {
                $reason = 'Transfer is blocked because booking contact conflicts with another parent user account.';

                return array_merge($base, [
                    'allowed' => false,
                    'outcome' => 'blocked_conflict',
                    'target_parent_id' => $parent?->id,
                    'conflicting_user_ids' => $conflictingUserIds,
                    'blocked_reason' => $reason,
                    'audit_payload' => $this->transferAuditPayload($child, 'blocked_conflict', $parent?->id, null, $reason),
                ]);
            }

            return array_merge($base, [
                'allowed' => true,
                'outcome' => 'link_existing_parent_by_contact',
                'target_parent_id' => (int) $parentMatch['resolved_parent_id'],
                'resolved_parent_email' => $parent?->email,
                'resolved_parent_phone' => $parent?->phone,
                'audit_payload' => $this->transferAuditPayload($child, 'link_existing_parent_by_contact', (int) $parentMatch['resolved_parent_id']),
            ]);
        }

        if ($userMatch['blocked_reason'] || $userMatch['all_user_ids'] !== []) {
            $reason = $userMatch['blocked_reason'] ?: 'Transfer is blocked because booking contact matches an existing parent user account without a resolved parent row.';

            return array_merge($base, [
                'allowed' => false,
                'outcome' => 'blocked_conflict',
                'conflicting_user_ids' => $userMatch['all_user_ids'],
                'blocked_reason' => $reason,
                'audit_payload' => $this->transferAuditPayload($child, 'blocked_conflict', null, null, $reason),
            ]);
        }

        return array_merge($base, [
            'allowed' => true,
            'outcome' => 'create_new_parent',
            'audit_payload' => $this->transferAuditPayload($child, 'create_new_parent'),
        ]);
    }

    protected function resolveLinkedParentTransfer(BookingChild $child, Booking $booking, array $parentMatch, array $userMatch, array $base): array
    {
        $linkedParent = ParentModel::query()->find($booking->parent_id);

        if (! $linkedParent) {
            return array_merge($base, [
                'allowed' => false,
                'outcome' => 'blocked_conflict',
                'target_parent_id' => (int) $booking->parent_id,
                'blocked_reason' => 'Transfer is blocked because the linked parent account no longer exists.',
                'audit_payload' => $this->transferAuditPayload($child, 'blocked_conflict', (int) $booking->parent_id, null, 'Linked parent row is missing.'),
            ]);
        }

        $conflictingParentIds = collect($parentMatch['all_parent_ids'])
            ->reject(fn (int $id): bool => $id === (int) $linkedParent->id)
            ->values()
            ->all();
        $conflictingUserIds = $this->conflictingUserIdsForParent($userMatch, $linkedParent);

        if ($conflictingParentIds !== [] || $conflictingUserIds !== []) {
            $reason = 'Transfer is blocked because booking contact conflicts with another parent account.';

            return array_merge($base, [
                'allowed' => false,
                'outcome' => 'blocked_conflict',
                'target_parent_id' => (int) $linkedParent->id,
                'conflicting_parent_ids' => $conflictingParentIds,
                'conflicting_user_ids' => $conflictingUserIds,
                'blocked_reason' => $reason,
                'audit_payload' => $this->transferAuditPayload($child, 'blocked_conflict', (int) $linkedParent->id, $conflictingParentIds[0] ?? null, $reason),
            ]);
        }

        $contactAction = $this->contactUpdateAction($linkedParent->email, $linkedParent->phone, $booking->parent_email, $booking->parent_phone);

        if ($contactAction !== 'none') {
            $reason = 'Transfer is blocked because booking contact differs from the linked parent account. Confirm a contact update before transfer.';

            return array_merge($base, [
                'allowed' => false,
                'outcome' => 'update_linked_parent_contact',
                'target_parent_id' => (int) $linkedParent->id,
                'requires_admin_confirmation' => true,
                'requires_contact_update' => true,
                'blocked_reason' => $reason,
                'previous_parent_email' => $linkedParent->email,
                'previous_parent_phone' => $linkedParent->phone,
                'resolved_parent_email' => $booking->parent_email,
                'resolved_parent_phone' => $booking->parent_phone,
                'contact_action' => $contactAction,
                'audit_payload' => $this->transferAuditPayload($child, 'update_linked_parent_contact', (int) $linkedParent->id, null, $reason, $contactAction),
            ]);
        }

        return array_merge($base, [
            'allowed' => true,
            'outcome' => 'use_linked_parent',
            'target_parent_id' => (int) $linkedParent->id,
            'previous_parent_email' => $linkedParent->email,
            'previous_parent_phone' => $linkedParent->phone,
            'resolved_parent_email' => $linkedParent->email ?: $booking->parent_email,
            'resolved_parent_phone' => $linkedParent->phone ?: $booking->parent_phone,
            'audit_payload' => $this->transferAuditPayload($child, 'use_linked_parent', (int) $linkedParent->id),
        ]);
    }

    protected function baseTransferResult(BookingChild $child): array
    {
        $booking = $child->booking;

        return [
            'allowed' => false,
            'outcome' => 'blocked_conflict',
            'target_parent_id' => null,
            'conflicting_parent_ids' => [],
            'conflicting_user_ids' => [],
            'requires_admin_confirmation' => false,
            'requires_contact_update' => false,
            'multi_phone_available' => $this->parentPhoneNumbersAvailable(),
            'blocked_reason' => null,
            'previous_parent_email' => null,
            'previous_parent_phone' => null,
            'resolved_parent_email' => $booking?->parent_email,
            'resolved_parent_phone' => $booking?->parent_phone,
            'contact_action' => 'none',
            'audit_payload' => [],
        ];
    }

    protected function transferAuditPayload(
        BookingChild $child,
        string $outcome,
        ?int $targetParentId = null,
        ?int $conflictingParentId = null,
        ?string $conflictSummary = null,
        string $contactAction = 'none'
    ): array {
        $booking = $child->booking;
        $targetParent = $targetParentId ? ParentModel::query()->find($targetParentId) : null;

        return [
            'stage' => 'booking_transfer',
            'outcome' => $outcome,
            'booking_id' => $booking?->id,
            'booking_child_id' => $child->id,
            'matched_booking_id' => $booking?->id,
            'target_parent_id' => $targetParentId,
            'conflicting_parent_id' => $conflictingParentId,
            'submitted_parent_email' => $booking?->parent_email,
            'submitted_parent_phone' => $booking?->parent_phone,
            'previous_parent_email' => $targetParent?->email,
            'previous_parent_phone' => $targetParent?->phone,
            'resolved_parent_email' => $booking?->parent_email ?: $targetParent?->email,
            'resolved_parent_phone' => $booking?->parent_phone ?: $targetParent?->phone,
            'contact_action' => $contactAction,
            'child_identity_summary' => json_encode([
                'booking_child_id' => $child->id,
                'child_name' => $child->child_name,
            ], JSON_UNESCAPED_UNICODE),
            'conflict_summary' => $conflictSummary,
        ];
    }

    protected function conflictingUserIdsForParent(array $userMatch, ParentModel $parent): array
    {
        if ($userMatch['blocked_reason']) {
            return $userMatch['all_user_ids'];
        }

        if ($userMatch['all_user_ids'] === []) {
            return [];
        }

        $linkedUserId = (int) ($parent->user_id ?? 0);

        return collect($userMatch['all_user_ids'])
            ->reject(fn (int $id): bool => $linkedUserId > 0 && $id === $linkedUserId)
            ->values()
            ->all();
    }

    protected function parentAccountUserIds(Collection $userIds): Collection
    {
        $userIds = $userIds
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return collect();
        }

        $parentLinkedIds = collect();

        if ($this->tableHasColumn('parents', 'user_id')) {
            $parentLinkedIds = DB::table('parents')
                ->whereIn('user_id', $userIds->all())
                ->pluck('user_id')
                ->map(fn ($id): int => (int) $id);
        }

        $parentRoleIds = collect();

        if (
            Schema::hasTable('roles')
            && Schema::hasTable('model_has_roles')
            && $this->tableHasColumn('roles', 'name')
            && $this->tableHasColumn('model_has_roles', 'model_id')
        ) {
            $parentRoleIds = DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', 'parent')
                ->whereIn('model_has_roles.model_id', $userIds->all())
                ->where('model_has_roles.model_type', User::class)
                ->pluck('model_has_roles.model_id')
                ->map(fn ($id): int => (int) $id);
        }

        return $parentLinkedIds
            ->merge($parentRoleIds)
            ->unique()
            ->values();
    }

    protected function contactUpdateAction(?string $previousEmail, ?string $previousPhone, ?string $nextEmail, ?string $nextPhone): string
    {
        $previousEmail = $this->normalizeEmail($previousEmail);
        $nextEmail = $this->normalizeEmail($nextEmail);
        $previousPhone = $this->normalizePhone($previousPhone);
        $nextPhone = $this->normalizePhone($nextPhone);

        $emailNeedsUpdate = $nextEmail !== '' && $previousEmail !== '' && $previousEmail !== $nextEmail;
        $phoneNeedsUpdate = $nextPhone !== '' && $previousPhone !== '' && $previousPhone !== $nextPhone;

        return match (true) {
            $emailNeedsUpdate && $phoneNeedsUpdate => 'replace_email_and_phone',
            $emailNeedsUpdate => 'replace_email',
            $phoneNeedsUpdate => 'replace_phone',
            default => 'none',
        };
    }

    protected function contactMatchResult(string $label, string $normalizedEmail, string $normalizedPhone, Collection $emailIds, Collection $phoneIds): array
    {
        $emailIds = $emailIds->unique()->values();
        $phoneIds = $phoneIds->unique()->values();
        $allIds = $emailIds->merge($phoneIds)->unique()->values();
        $blockedReason = null;

        if ($emailIds->count() > 1) {
            $blockedReason = "Submitted email matches more than one {$label} account.";
        } elseif ($phoneIds->count() > 1) {
            $blockedReason = "Submitted phone matches more than one {$label} account.";
        } elseif ($normalizedEmail !== '' && $normalizedPhone !== '' && $emailIds->isNotEmpty() && $phoneIds->isNotEmpty() && $emailIds->first() !== $phoneIds->first()) {
            $blockedReason = "Submitted email and phone match different {$label} accounts.";
        } elseif ($allIds->count() > 1) {
            $blockedReason = "Submitted contact matches more than one {$label} account.";
        }

        return [
            'normalized_email' => $normalizedEmail,
            'normalized_phone' => $normalizedPhone,
            'email_parent_ids' => $emailIds->all(),
            'phone_parent_ids' => $phoneIds->all(),
            'all_parent_ids' => $allIds->all(),
            'resolved_parent_id' => $blockedReason === null && $allIds->count() === 1 ? $allIds->first() : null,
            'blocked_reason' => $blockedReason,
        ];
    }

    protected function normalizedEmailExpression(string $column): string
    {
        return "LOWER(TRIM(COALESCE({$column}, '')))";
    }

    protected function normalizedPhoneExpression(string $column): string
    {
        return PhoneNormalizer::sqlExpression($column, DB::connection()->getDriverName());
    }

    protected function tableHasColumn(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }

    protected function parentPhoneNumbersAvailable(): bool
    {
        return $this->tableHasColumn('parent_phone_numbers', 'normalized_phone')
            && $this->tableHasColumn('parent_phone_numbers', 'parent_id');
    }
}
