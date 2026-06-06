<?php

namespace App\Livewire\Admin\Booking;

use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\BookingIntakeReview;
use App\Models\BookingIntakeReviewChild;
use App\Services\BookingIntakeDetectionService;
use App\Services\BookingIntakeWriter;
use App\Services\BookingParentIdentityResolver;
use App\Support\SchoolSystemOptions;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class IntakeReviewQueue extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    protected array $reviewConflictStateCache = [];

    protected array $reviewSummaryCache = [];

    protected array $verifiedContactActionStateCache = [];

    protected array $finalSubmissionActionStateCache = [];

    protected array $childContextLinksCache = [];

    protected array $contactMatchSnapshotCache = [];

    protected array $bookingModelCache = [];

    protected array $matchedChildModelCache = [];

    public string $search = '';

    public string $reasonFilter = 'all';

    public int $perPage = 10;

    public array $childResolutionNotes = [];

    public array $submissionNotes = [];

    public ?int $correctionReviewChildId = null;

    public array $correctionForm = [];

    public ?int $contactCorrectionReviewId = null;

    public array $contactCorrectionForm = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'reasonFilter' => ['except' => 'all'],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        $this->normalizePerPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingReasonFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->normalizePerPage();
        $this->resetPage();
    }

    public function resetListFilters(): void
    {
        $this->search = '';
        $this->reasonFilter = 'all';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function applyQuickFilter(string $filter): void
    {
        $allowed = ['all', 'blocked_parent', 'duplicate_repeat', 'mixed_children', 'suspected_contact_mismatch'];

        if (! in_array($filter, $allowed, true)) {
            return;
        }

        $this->reasonFilter = $filter;
        $this->resetPage();
    }

    public function setChildResolution(int $reviewChildId, string $resolutionStatus): void
    {
        if (! in_array($resolutionStatus, ['promote_child', 'dismiss_child', 'pending_decision'], true)) {
            abort(404);
        }

        $reviewChildMeta = BookingIntakeReviewChild::query()
            ->select(['id', 'booking_intake_review_id'])
            ->findOrFail($reviewChildId);

        $note = $this->normalizedText($this->childResolutionNotes[$reviewChildId] ?? null);

        if ($resolutionStatus === 'dismiss_child' && blank($note)) {
            $this->addError("childResolutionNotes.{$reviewChildId}", 'Enter a child note before marking this child dismissed.');

            return;
        }

        $approvedForVerifiedContactUpdate = false;

        try {
            DB::transaction(function () use ($reviewChildMeta, $reviewChildId, $resolutionStatus, $note, &$approvedForVerifiedContactUpdate): void {
                $review = BookingIntakeReview::query()
                    ->lockForUpdate()
                    ->findOrFail($reviewChildMeta->booking_intake_review_id);

                if ($review->status !== 'pending_review') {
                    throw new \InvalidArgumentException('This review row has already been finalized.');
                }

                $reviewChild = BookingIntakeReviewChild::query()
                    ->whereKey($reviewChildId)
                    ->where('booking_intake_review_id', $review->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $resolutionStatus === 'promote_child'
                    && in_array($reviewChild->review_reason, ['blocked_parent', 'duplicate_child', 'repeat_submission'], true)
                ) {
                    throw new \InvalidArgumentException('This child cannot be approved as-is. Correct the child/contact data so it becomes a sibling or new customer, or dismiss it.');
                }

                $approvedForVerifiedContactUpdate = $resolutionStatus === 'promote_child'
                    && $reviewChild->review_reason === 'suspected_contact_mismatch';

                $reviewChild->update([
                    'resolution_status' => $resolutionStatus,
                    'resolution_note' => $resolutionStatus === 'pending_decision' ? null : $note,
                ]);
            });
        } catch (\InvalidArgumentException $exception) {
            $this->addError("reviewActions.{$reviewChildMeta->booking_intake_review_id}", $exception->getMessage());

            return;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError("reviewActions.{$reviewChildMeta->booking_intake_review_id}", 'Child decision failed before it could be saved.');

            return;
        }

        if ($resolutionStatus === 'pending_decision') {
            unset($this->childResolutionNotes[$reviewChildId]);
        }

        session()->flash(
            'success',
            match ($resolutionStatus) {
                'promote_child' => $approvedForVerifiedContactUpdate
                    ? 'Child approved for verified contact update.'
                    : 'Child marked for promotion into the normal booking queue.',
                'dismiss_child' => 'Child dismissed from this review submission.',
                default => 'Child decision reset to pending.',
            }
        );
    }

    public function openCorrectionModal(int $reviewChildId): void
    {
        $reviewChild = BookingIntakeReviewChild::query()
            ->with('review')
            ->findOrFail($reviewChildId);

        if ($reviewChild->review?->status !== 'pending_review') {
            $this->addError("reviewActions.{$reviewChild->booking_intake_review_id}", 'This review item has already been processed.');

            return;
        }

        $this->resetErrorBag();
        $this->correctionReviewChildId = $reviewChild->id;
        $this->correctionForm = [
            'child_name' => $reviewChild->child_name,
            'child_age' => $reviewChild->child_age,
            'child_grade' => $reviewChild->child_grade,
            'school_system' => $reviewChild->school_system,
        ];
    }

    public function closeCorrectionModal(): void
    {
        $this->correctionReviewChildId = null;
        $this->correctionForm = [];
        $this->resetErrorBag('correctionForm');
    }

    public function openContactCorrectionModal(int $reviewId): void
    {
        $review = BookingIntakeReview::query()->findOrFail($reviewId);

        if ($review->status !== 'pending_review') {
            $this->addError("reviewActions.{$review->id}", 'This review item has already been processed.');

            return;
        }

        $this->resetErrorBag();
        $this->contactCorrectionReviewId = $review->id;
        $this->contactCorrectionForm = [
            'parent_email' => $review->parent_email,
            'parent_phone' => $review->parent_phone,
        ];
    }

    public function closeContactCorrectionModal(): void
    {
        $this->contactCorrectionReviewId = null;
        $this->contactCorrectionForm = [];
        $this->resetErrorBag('contactCorrectionForm');
    }

    public function saveContactCorrection(): void
    {
        if (! $this->contactCorrectionReviewId) {
            return;
        }

        $this->resetErrorBag('contactCorrectionForm');

        $parentEmail = $this->normalizedText($this->contactCorrectionForm['parent_email'] ?? null);
        $parentPhone = $this->normalizedText($this->contactCorrectionForm['parent_phone'] ?? null);

        if (! $parentEmail && ! $parentPhone) {
            $this->addError('contactCorrectionForm.parent_phone', 'Keep at least one parent contact value.');

            return;
        }

        try {
            DB::transaction(function () use ($parentEmail, $parentPhone): void {
                $review = BookingIntakeReview::query()
                    ->with('reviewChildren')
                    ->lockForUpdate()
                    ->findOrFail($this->contactCorrectionReviewId);

                if ($review->status !== 'pending_review') {
                    throw new \InvalidArgumentException('This review item has already been processed.');
                }

                $review->parent_email = $parentEmail;
                $review->parent_phone = $parentPhone;
                $review->save();

                foreach ($review->reviewChildren as $reviewChild) {
                    if ($reviewChild->resolution_status === 'dismiss_child') {
                        continue;
                    }

                    $resolution = $this->resolveCorrectedChildOutcome($review, $reviewChild);
                    $outcomeChanged = $reviewChild->review_reason !== $resolution['review_reason']
                        || (int) ($reviewChild->matched_booking_id ?? 0) !== (int) ($resolution['matched_booking_id'] ?? 0);

                    $reviewChild->update([
                        'review_reason' => $resolution['review_reason'],
                        'review_detail' => $resolution['review_detail'],
                        'matched_booking_id' => $resolution['matched_booking_id'],
                        'matched_child_id' => null,
                        'resolution_status' => $outcomeChanged ? 'pending_decision' : $reviewChild->resolution_status,
                        'resolution_note' => $outcomeChanged ? null : $reviewChild->resolution_note,
                    ]);
                }

                $this->syncReviewDetectionSummary($review);
            });
        } catch (\InvalidArgumentException $exception) {
            $this->addError('contactCorrectionForm.general', $exception->getMessage());

            return;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('contactCorrectionForm.general', 'Parent contact correction failed before it could be saved.');

            return;
        }

        $this->closeContactCorrectionModal();
        session()->flash('success', 'Parent contact correction saved and review rows re-checked.');
    }

    public function saveCorrection(bool $approveAfterCorrection = false): void
    {
        if (! $this->correctionReviewChildId) {
            return;
        }

        $this->resetErrorBag('correctionForm');
        $approvedAfterCorrection = false;
        $requiresContactActionAfterCorrection = false;

        $childName = $this->normalizedText($this->correctionForm['child_name'] ?? null);
        $childAge = $this->normalizedText($this->correctionForm['child_age'] ?? null);
        $childGrade = $this->normalizedText($this->correctionForm['child_grade'] ?? null);
        $schoolSystem = SchoolSystemOptions::normalize($this->correctionForm['school_system'] ?? null) ?? SchoolSystemOptions::OTHER;

        if (! $childName) {
            $this->addError('correctionForm.child_name', 'Enter the corrected child name.');

            return;
        }

        try {
            DB::transaction(function () use (
                $childName,
                $childAge,
                $childGrade,
                $schoolSystem,
                $approveAfterCorrection,
                &$approvedAfterCorrection,
                &$requiresContactActionAfterCorrection
            ): void {
                $reviewChild = BookingIntakeReviewChild::query()
                    ->whereKey($this->correctionReviewChildId)
                    ->lockForUpdate()
                    ->firstOrFail();
                $review = BookingIntakeReview::query()
                    ->lockForUpdate()
                    ->findOrFail($reviewChild->booking_intake_review_id);

                if ($review->status !== 'pending_review') {
                    throw new \InvalidArgumentException('This review item has already been processed.');
                }

                $updatedChildrenPayload = $this->updatedChildrenPayload($review, $reviewChild->child_index, [
                    'child_name' => $childName,
                    'child_age' => $childAge,
                    'child_grade' => $childGrade,
                    'school_system' => $schoolSystem,
                    'service_interests' => $reviewChild->service_interests ?? [],
                ]);

                $reviewPreview = clone $review;
                $reviewPreview->children_payload = $updatedChildrenPayload;

                if ((int) $reviewChild->child_index === 0) {
                    $reviewPreview->child_name = $childName;
                    $reviewPreview->child_age = $childAge;
                    $reviewPreview->child_grade = $childGrade;
                    $reviewPreview->school_system = $schoolSystem;
                }

                $reviewChildPreview = clone $reviewChild;
                $reviewChildPreview->fill([
                    'child_name' => $childName,
                    'child_age' => $childAge,
                    'child_grade' => $childGrade,
                    'school_system' => $schoolSystem,
                ]);

                $resolution = $this->resolveCorrectedChildOutcome($reviewPreview, $reviewChildPreview);
                $requiresContactActionAfterCorrection = $approveAfterCorrection
                    && ($resolution['review_reason'] ?? null) === 'suspected_contact_mismatch';
                $approvedAfterCorrection = $approveAfterCorrection && ! $requiresContactActionAfterCorrection;

                $review->children_payload = $updatedChildrenPayload;

                if ((int) $reviewChild->child_index === 0) {
                    $review->child_name = $childName;
                    $review->child_age = $childAge;
                    $review->child_grade = $childGrade;
                    $review->school_system = $schoolSystem;
                }

                $review->save();

                $reviewChild->fill([
                    'child_name' => $childName,
                    'child_age' => $childAge,
                    'child_grade' => $childGrade,
                    'school_system' => $schoolSystem,
                ]);
                $reviewChild->save();

                $reviewChild->update([
                    'review_reason' => $resolution['review_reason'],
                    'review_detail' => $resolution['review_detail'],
                    'matched_booking_id' => $resolution['matched_booking_id'],
                    'matched_child_id' => null,
                    'resolution_status' => $approvedAfterCorrection ? 'promote_child' : 'pending_decision',
                    'resolution_note' => null,
                ]);

                $this->syncReviewDetectionSummary($review);
            });
        } catch (\InvalidArgumentException $exception) {
            $this->restoreCorrectionFormFromStoredRow();
            $this->addError('correctionForm.child_name', $exception->getMessage());

            return;
        } catch (\Throwable $exception) {
            report($exception);
            $this->restoreCorrectionFormFromStoredRow();
            $this->addError('correctionForm.child_name', 'Correction failed before it could be saved.');

            return;
        }

        unset($this->childResolutionNotes[$this->correctionReviewChildId]);
        $this->closeCorrectionModal();
        session()->flash('success', match (true) {
            $approvedAfterCorrection => 'Correction saved and child approved for promotion.',
            $requiresContactActionAfterCorrection => 'Correction saved. Contact verification is still required before promotion.',
            default => 'Correction saved. The child can now be approved or dismissed.',
        });
    }

    public function finalizePromotion(int $reviewId): void
    {
        $this->resetErrorBag("reviewActions.{$reviewId}");

        $note = $this->normalizedText($this->submissionNotes[$reviewId] ?? null);

        if (blank($note)) {
            $this->addError("submissionNotes.{$reviewId}", 'Add a submission note before promoting approved children.');

            return;
        }

        try {
            $booking = DB::transaction(function () use ($reviewId, $note) {
                $review = BookingIntakeReview::query()
                    ->lockForUpdate()
                    ->findOrFail($reviewId);

                if ($review->status !== 'pending_review') {
                    throw new \InvalidArgumentException('This review item has already been processed.');
                }

                $reviewChildren = BookingIntakeReviewChild::query()
                    ->where('booking_intake_review_id', $review->id)
                    ->orderBy('child_index')
                    ->lockForUpdate()
                    ->get();

                if ($reviewChildren->contains(fn (BookingIntakeReviewChild $child) => $child->resolution_status === 'pending_decision')) {
                    throw new \InvalidArgumentException('Resolve every child row before finalizing promotion.');
                }

                $approvedChildren = $reviewChildren
                    ->where('resolution_status', 'promote_child')
                    ->values();

                if ($approvedChildren->isEmpty()) {
                    throw new \InvalidArgumentException('At least one child must be approved before a booking can be created.');
                }

                $booking = app(BookingIntakeWriter::class)->promoteReviewChildren($review, $approvedChildren, null, $note);

                $review->update([
                    'status' => 'promoted_to_queue',
                    'resulting_booking_id' => $booking->id,
                    'resolution_note' => $note,
                    'resolved_by' => auth()->id(),
                    'resolved_at' => now(),
                    'open_submission_fingerprint' => null,
                ]);

                return $booking;
            });
        } catch (\InvalidArgumentException $exception) {
            $this->addError("reviewActions.{$reviewId}", $exception->getMessage());

            return;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError("reviewActions.{$reviewId}", 'Promotion failed before the booking queue could be updated.');

            return;
        }

        unset($this->submissionNotes[$reviewId]);
        session()->flash('success', 'Review item promoted to the booking queue.');

        $this->dispatch('intake-review-promoted', reviewId: $reviewId, bookingId: $booking->id);
    }

    public function finalizeVerifiedContactUpdate(int $reviewId): void
    {
        $this->resetErrorBag("reviewActions.{$reviewId}");

        $note = $this->normalizedText($this->submissionNotes[$reviewId] ?? null);

        if (blank($note)) {
            $this->addError("submissionNotes.{$reviewId}", 'Add a final note before confirming the contact update.');

            return;
        }

        try {
            $booking = DB::transaction(function () use ($reviewId, $note) {
                $review = BookingIntakeReview::query()
                    ->lockForUpdate()
                    ->findOrFail($reviewId);

                if ($review->status !== 'pending_review') {
                    throw new \InvalidArgumentException('This review item has already been processed.');
                }

                $reviewChildren = BookingIntakeReviewChild::query()
                    ->where('booking_intake_review_id', $review->id)
                    ->orderBy('child_index')
                    ->lockForUpdate()
                    ->get();

                if ($reviewChildren->contains(fn (BookingIntakeReviewChild $child) => $child->resolution_status === 'pending_decision')) {
                    throw new \InvalidArgumentException('Resolve every child row before confirming the contact update.');
                }

                $approvedChildren = $reviewChildren
                    ->where('resolution_status', 'promote_child')
                    ->values();

                if ($approvedChildren->isEmpty()) {
                    throw new \InvalidArgumentException('At least one child must be approved before a booking can be created.');
                }

                $booking = app(BookingIntakeWriter::class)->promoteReviewChildren($review, $approvedChildren, 'verified_contact_update', $note);

                $review->update([
                    'status' => 'promoted_to_queue',
                    'resulting_booking_id' => $booking->id,
                    'resolution_note' => $note,
                    'resolved_by' => auth()->id(),
                    'resolved_at' => now(),
                    'open_submission_fingerprint' => null,
                ]);

                return $booking;
            });
        } catch (\InvalidArgumentException $exception) {
            $this->addError("reviewActions.{$reviewId}", $exception->getMessage());

            return;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError("reviewActions.{$reviewId}", 'Contact update promotion failed before the booking queue could be updated.');

            return;
        }

        unset($this->submissionNotes[$reviewId]);
        session()->flash('success', 'Verified contact update applied and approved children promoted.');

        $this->dispatch('intake-review-promoted', reviewId: $reviewId, bookingId: $booking->id);
    }

    public function dismissSubmission(int $reviewId): void
    {
        $this->resetErrorBag("reviewActions.{$reviewId}");

        $note = $this->normalizedText($this->submissionNotes[$reviewId] ?? null);

        if (blank($note)) {
            $this->addError("submissionNotes.{$reviewId}", 'Add a submission note before dismissing this review.');

            return;
        }

        try {
            DB::transaction(function () use ($reviewId, $note) {
                $review = BookingIntakeReview::query()
                    ->with('reviewChildren')
                    ->lockForUpdate()
                    ->findOrFail($reviewId);

                if ($review->status !== 'pending_review') {
                    throw new \InvalidArgumentException('This review item has already been processed.');
                }

                $review->reviewChildren()->update([
                    'resolution_status' => 'dismiss_child',
                    'resolution_note' => $note,
                ]);

                app(BookingParentIdentityResolver::class)->recordResolution([
                    'stage' => 'intake_review_promotion',
                    'outcome' => 'dismissed',
                    'booking_intake_review_id' => $review->id,
                    'matched_booking_id' => $review->matched_booking_id,
                    'submitted_parent_email' => $review->parent_email,
                    'submitted_parent_phone' => $review->parent_phone,
                    'contact_action' => 'none',
                    'conflict_summary' => $review->detection_detail,
                    'resolution_note' => $note,
                ]);

                $review->update([
                    'status' => 'dismissed',
                    'resolution_note' => $note,
                    'resolved_by' => auth()->id(),
                    'resolved_at' => now(),
                    'open_submission_fingerprint' => null,
                ]);
            });
        } catch (\InvalidArgumentException $exception) {
            $this->addError("reviewActions.{$reviewId}", $exception->getMessage());

            return;
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError("reviewActions.{$reviewId}", 'Dismissal failed before the queue could be updated.');

            return;
        }

        unset($this->submissionNotes[$reviewId]);
        session()->flash('success', 'Review submission dismissed.');
    }

    public function render()
    {
        $this->resetRenderCaches();
        $reviews = $this->reviewsPage();
        $this->primeReviewRenderState(collect($reviews->items()));

        return view('livewire.admin.booking.intake-review-queue', [
            'reviews' => $reviews,
            'stats' => $this->stats(),
            'reasonOptions' => $this->reasonOptions(),
            'schoolSystemOptions' => SchoolSystemOptions::labels(),
        ])->layout('components.layouts.app', ['title' => 'Intake Review Queue']);
    }

    public function reviewReasonBadge(string $reason): array
    {
        return match ($reason) {
            'blocked_parent' => ['label' => 'Blocked Parent', 'class' => 'bg-label-danger', 'icon' => 'tabler-shield-x'],
            'duplicate_child' => ['label' => 'Duplicate Child', 'class' => 'bg-label-warning', 'icon' => 'tabler-copy'],
            'repeat_submission' => ['label' => 'Repeat Submission', 'class' => 'bg-label-info', 'icon' => 'tabler-rotate-2'],
            'existing_family_new_child' => ['label' => 'Existing Family / New Child', 'class' => 'bg-label-primary', 'icon' => 'tabler-users'],
            'mixed_children' => ['label' => 'Mixed Submission', 'class' => 'bg-label-warning', 'icon' => 'tabler-arrows-shuffle'],
            'suspected_contact_mismatch' => ['label' => 'Contact Mismatch', 'class' => 'bg-label-danger', 'icon' => 'tabler-alert-triangle'],
            default => ['label' => 'Clean New Customer', 'class' => 'bg-label-secondary', 'icon' => 'tabler-user-plus'],
        };
    }

    public function resolutionBadge(string $status): array
    {
        return match ($status) {
            'promote_child' => ['label' => 'Approved', 'class' => 'bg-label-success'],
            'dismiss_child' => ['label' => 'Dismissed', 'class' => 'bg-label-danger'],
            default => ['label' => 'Pending Decision', 'class' => 'bg-label-secondary'],
        };
    }

    public function formatDateTime(mixed $value): string
    {
        if (blank($value)) {
            return '-';
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('d M Y g:i A');
        }

        try {
            return Carbon::parse((string) $value)->format('d M Y g:i A');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public function serviceSummary(BookingIntakeReviewChild $reviewChild): string
    {
        $services = collect($reviewChild->service_interests ?? [])
            ->map(fn ($service) => Booking::displayServiceInterest(is_string($service) ? trim($service) : $service))
            ->filter()
            ->values();

        return $services->implode(', ') ?: 'Need Guidance';
    }

    public function reviewSummary(BookingIntakeReview $review): array
    {
        if (array_key_exists($review->id, $this->reviewSummaryCache)) {
            return $this->reviewSummaryCache[$review->id];
        }

        return $this->reviewSummaryCache[$review->id] = [
            'pending' => $review->reviewChildren->where('resolution_status', 'pending_decision')->count(),
            'approved' => $review->reviewChildren->where('resolution_status', 'promote_child')->count(),
            'dismissed' => $review->reviewChildren->where('resolution_status', 'dismiss_child')->count(),
        ];
    }

    public function reviewNeedsVerifiedContactUpdate(BookingIntakeReview $review): bool
    {
        $reviewChildren = $review->relationLoaded('reviewChildren')
            ? $review->reviewChildren
            : $review->reviewChildren()->orderBy('child_index')->get();

        return $reviewChildren->contains(
            fn (BookingIntakeReviewChild $reviewChild): bool => $reviewChild->review_reason === 'suspected_contact_mismatch'
                && $reviewChild->resolution_status !== 'dismiss_child'
        );
    }

    public function reviewConflictState(BookingIntakeReview $review): array
    {
        if (array_key_exists($review->id, $this->reviewConflictStateCache)) {
            return $this->reviewConflictStateCache[$review->id];
        }

        $state = [
            'reason_key' => $review->detection_reason,
            'detail' => $review->detection_detail ?: 'No detection detail recorded.',
            'detail_contexts' => [],
            'detail_info' => null,
            'is_split_contact_conflict' => false,
            'contact_action_blocked' => false,
            'contact_action_reason' => null,
        ];

        if (! $this->reviewNeedsVerifiedContactUpdate($review)) {
            return $this->reviewConflictStateCache[$review->id] = $state;
        }

        $contactMatchSnapshot = $this->contactMatchSnapshot($review);
        $bookingMatch = $contactMatchSnapshot['booking_match'];

        if (($bookingMatch['blocked_reason'] ?? null) === 'Email and phone match different booking families.') {
            return $this->reviewConflictStateCache[$review->id] = array_merge($state, [
                'detail' => null,
                'detail_contexts' => $this->splitContactConflictContexts($bookingMatch),
                'detail_info' => 'Email and phone point to different booking families. Contact actions stay disabled until the correct family is confirmed.',
                'is_split_contact_conflict' => true,
                'contact_action_blocked' => true,
                'contact_action_reason' => $bookingMatch['blocked_reason'],
            ]);
        }

        $parentMatch = $contactMatchSnapshot['parent_match'];
        $blockedReason = $bookingMatch['blocked_reason'] ?? $parentMatch['blocked_reason'] ?? null;

        if ($blockedReason) {
            return $this->reviewConflictStateCache[$review->id] = array_merge($state, [
                'contact_action_blocked' => true,
                'contact_action_reason' => $blockedReason,
            ]);
        }

        return $this->reviewConflictStateCache[$review->id] = $state;
    }

    public function verifiedContactActionState(BookingIntakeReview $review): array
    {
        if (array_key_exists($review->id, $this->verifiedContactActionStateCache)) {
            return $this->verifiedContactActionStateCache[$review->id];
        }

        $reviewConflictState = $this->reviewConflictState($review);

        if ($reviewConflictState['contact_action_blocked']) {
            return $this->verifiedContactActionStateCache[$review->id] = [
                'replace_disabled' => true,
                'replace_reason' => $reviewConflictState['contact_action_reason'],
            ];
        }

        $reviewChildren = $review->relationLoaded('reviewChildren')
            ? $review->reviewChildren
            : $review->reviewChildren()->orderBy('child_index')->get();

        if ($reviewChildren->contains(fn (BookingIntakeReviewChild $child): bool => $child->resolution_status === 'pending_decision')) {
            return $this->verifiedContactActionStateCache[$review->id] = [
                'replace_disabled' => true,
                'replace_reason' => 'Decide every child row before using Contact Action.',
            ];
        }

        $approvedChildren = $reviewChildren
            ->where('resolution_status', 'promote_child')
            ->values();

        if ($approvedChildren->isEmpty()) {
            return $this->verifiedContactActionStateCache[$review->id] = [
                'replace_disabled' => true,
                'replace_reason' => 'Approve at least one child before using Contact Action.',
            ];
        }

        $resolution = app(BookingParentIdentityResolver::class)
            ->resolvePromotionOutcome($review, $approvedChildren, 'verified_contact_update');

        return $this->verifiedContactActionStateCache[$review->id] = [
            'replace_disabled' => ! ($resolution['allowed'] ?? false) && filled($resolution['blocked_reason'] ?? null),
            'replace_reason' => $resolution['blocked_reason'] ?? null,
        ];
    }

    public function finalSubmissionActionState(BookingIntakeReview $review): array
    {
        if (array_key_exists($review->id, $this->finalSubmissionActionStateCache)) {
            return $this->finalSubmissionActionStateCache[$review->id];
        }

        $reviewChildren = $review->relationLoaded('reviewChildren')
            ? $review->reviewChildren
            : $review->reviewChildren()->orderBy('child_index')->get();

        if ($reviewChildren->contains(fn (BookingIntakeReviewChild $child): bool => $child->resolution_status === 'pending_decision')) {
            return $this->finalSubmissionActionStateCache[$review->id] = [
                'actions_disabled' => true,
                'reason' => 'Decide every child row before using the final submission actions.',
                'promotion_disabled' => true,
                'promotion_reason' => 'Decide every child row before using the final submission actions.',
            ];
        }

        if ($reviewChildren->where('resolution_status', 'promote_child')->isEmpty()) {
            return $this->finalSubmissionActionStateCache[$review->id] = [
                'actions_disabled' => false,
                'reason' => null,
                'promotion_disabled' => true,
                'promotion_reason' => 'Approve at least one child before finalizing this submission.',
            ];
        }

        return $this->finalSubmissionActionStateCache[$review->id] = [
            'actions_disabled' => false,
            'reason' => null,
            'promotion_disabled' => false,
            'promotion_reason' => null,
        ];
    }

    public function reviewChildReasonBadge(BookingIntakeReview $review, BookingIntakeReviewChild $reviewChild): array
    {
        $reviewConflictState = $this->reviewConflictState($review);

        if (
            ($reviewConflictState['is_split_contact_conflict'] ?? false)
            && $reviewChild->review_reason === 'suspected_contact_mismatch'
        ) {
            return [
                'label' => 'Split Contact',
                'class' => 'bg-label-warning',
                'icon' => 'tabler-git-fork',
            ];
        }

        return $this->reviewReasonBadge($reviewChild->review_reason);
    }

    public function contextLinksForReviewChild(BookingIntakeReview $review, BookingIntakeReviewChild $reviewChild): array
    {
        if (array_key_exists($reviewChild->id, $this->childContextLinksCache)) {
            return $this->childContextLinksCache[$reviewChild->id];
        }

        $reviewConflictState = $this->reviewConflictState($review);

        if (($reviewConflictState['is_split_contact_conflict'] ?? false) && $reviewChild->review_reason === 'suspected_contact_mismatch') {
            return $this->childContextLinksCache[$reviewChild->id] = $reviewConflictState['detail_contexts'];
        }

        $matchedContext = $this->matchedContextFor($reviewChild);

        return $this->childContextLinksCache[$reviewChild->id] = ($matchedContext ? [$matchedContext] : []);
    }

    public function matchedContextFor(BookingIntakeReviewChild $reviewChild): ?array
    {
        if ($reviewChild->matched_child_id) {
            $matchedChild = $this->matchedChildById((int) $reviewChild->matched_child_id);

            if (! $matchedChild) {
                return null;
            }

            if ($matchedChild->transfer_status === 'transferred') {
                return $this->transferredContextPayload($matchedChild);
            }

            if ($matchedChild->booking) {
                return $this->activeBookingContextPayload($matchedChild->booking, $matchedChild);
            }
        }

        if (! $reviewChild->matched_booking_id) {
            return null;
        }

        $matchedBooking = $this->bookingById((int) $reviewChild->matched_booking_id);

        if (! $matchedBooking) {
            return null;
        }

        return $this->activeBookingContextPayload($matchedBooking);
    }

    protected function reviewsPage(): LengthAwarePaginator
    {
        return $this->baseQuery()->paginate($this->perPage);
    }

    protected function baseQuery(): Builder
    {
        $query = BookingIntakeReview::query()
            ->with([
                'reviewChildren' => fn ($childQuery) => $childQuery->orderBy('child_index'),
                'resolvedBy',
            ])
            ->where('status', 'pending_review');

        $search = trim($this->search);

        if ($search !== '') {
            $query->where(function (Builder $searchQuery) use ($search) {
                $searchQuery->where('parent_name', 'like', "%{$search}%")
                    ->orWhere('parent_email', 'like', "%{$search}%")
                    ->orWhere('parent_phone', 'like', "%{$search}%")
                    ->orWhere('detection_reason', 'like', "%{$search}%")
                    ->orWhere('detection_detail', 'like', "%{$search}%")
                    ->orWhereHas('reviewChildren', function (Builder $childQuery) use ($search) {
                        $childQuery->where('child_name', 'like', "%{$search}%")
                            ->orWhere('review_reason', 'like', "%{$search}%")
                            ->orWhere('review_detail', 'like', "%{$search}%");
                    });
            });
        }

        if ($this->reasonFilter !== 'all') {
            if ($this->reasonFilter === 'duplicate_repeat') {
                $query->whereIn('detection_reason', ['duplicate_child', 'repeat_submission']);
            } elseif ($this->reasonFilter === 'suspected_contact_mismatch') {
                $this->applyContactMismatchScope($query);
            } else {
                $query->where('detection_reason', $this->reasonFilter);
            }
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    protected function stats(): array
    {
        $counts = BookingIntakeReview::query()
            ->where('status', 'pending_review')
            ->selectRaw('detection_reason, COUNT(*) as aggregate_count')
            ->groupBy('detection_reason')
            ->pluck('aggregate_count', 'detection_reason');
        $contactMismatchCount = $this->applyContactMismatchScope(
            BookingIntakeReview::query()->where('status', 'pending_review')
        )->count();

        $openReviews = (int) $counts->sum();

        return [
            $this->statCard('Open Reviews', $openReviews, 'primary', 'tabler-alert-square-rounded', 'all'),
            $this->statCard('Blocked Parents', (int) ($counts['blocked_parent'] ?? 0), 'danger', 'tabler-shield-x', 'blocked_parent'),
            $this->statCard('Duplicate / Repeat', (int) (($counts['duplicate_child'] ?? 0) + ($counts['repeat_submission'] ?? 0)), 'warning', 'tabler-copy', 'duplicate_repeat'),
            $this->statCard('Mixed Child Payloads', (int) ($counts['mixed_children'] ?? 0), 'info', 'tabler-arrows-shuffle', 'mixed_children'),
            $this->statCard('Contact Mismatches', $contactMismatchCount, 'danger', 'tabler-alert-triangle', 'suspected_contact_mismatch'),
        ];
    }

    protected function applyContactMismatchScope(Builder $query): Builder
    {
        return $query->where(function (Builder $reasonQuery) {
            $reasonQuery->where('detection_reason', 'suspected_contact_mismatch')
                ->orWhere(function (Builder $mixedQuery) {
                    $mixedQuery->where('detection_reason', 'mixed_children')
                        ->whereHas('reviewChildren', function (Builder $childQuery) {
                            $childQuery->where('review_reason', 'suspected_contact_mismatch')
                                ->where('resolution_status', 'pending_decision');
                        });
                });
        });
    }

    protected function statCard(string $label, int $value, string $tone, string $icon, string $filter): array
    {
        return compact('label', 'value', 'tone', 'icon', 'filter') + [
            'active' => $this->reasonFilter === $filter || ($filter === 'all' && $this->reasonFilter === 'all'),
        ];
    }

    protected function reasonOptions(): array
    {
        return [
            'all' => 'All review reasons',
            'blocked_parent' => 'Blocked parent',
            'duplicate_repeat' => 'Duplicate / Repeat',
            'mixed_children' => 'Mixed children',
            'suspected_contact_mismatch' => 'Contact mismatch',
        ];
    }

    protected function activeBookingContextPayload(Booking $booking, ?BookingChild $child = null): ?array
    {
        $search = collect([
            $booking->booking_reference,
            $booking->parent_name,
            $booking->parent_email,
            $booking->parent_phone,
            $child?->child_name,
        ])
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter()
            ->first();

        if (! $search) {
            return null;
        }

        return [
            'label' => "Open Booking #{$booking->id}",
            'url' => route('admin.bookings.livewire', ['search' => $search]),
            'child_edit_url' => $child ? route('admin.bookings.children.edit', [
                'bookingChild' => $child->id,
                'return' => route('admin.bookings.intake-review'),
            ]) : null,
            'tone' => 'secondary',
            'destination' => 'active_booking',
        ];
    }

    protected function transferredContextPayload(BookingChild $child): ?array
    {
        $booking = $child->booking;
        $search = collect([
            $child->child_name,
            $booking?->parent_name,
            $booking?->parent_email,
            $booking?->parent_phone,
            $booking?->booking_reference,
        ])
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter()
            ->first();

        if (! $search) {
            return null;
        }

        return [
            'label' => 'Open Transferred Family',
            'url' => route('admin.bookings.transferred', ['search' => $search]),
            'child_edit_url' => route('admin.bookings.children.edit', [
                'bookingChild' => $child->id,
                'return' => route('admin.bookings.intake-review'),
            ]),
            'tone' => 'secondary',
            'destination' => 'transferred_family',
        ];
    }

    protected function normalizePerPage(): void
    {
        if (! in_array($this->perPage, [10, 25, 50], true)) {
            $this->perPage = 10;
        }
    }

    protected function splitContactConflictContexts(array $bookingMatch): array
    {
        $emailBookingId = collect($bookingMatch['email_booking_ids'] ?? [])->first();
        $phoneBookingId = collect($bookingMatch['phone_booking_ids'] ?? [])->first();
        $contexts = [];

        if ($emailBookingId) {
            $emailBooking = $this->bookingById((int) $emailBookingId);

            if ($emailBooking) {
                $contexts[] = $this->bookingContextPayload($emailBooking, 'tabler-mail', 'Email match');
            }
        }

        if ($phoneBookingId) {
            $phoneBooking = $this->bookingById((int) $phoneBookingId);

            if ($phoneBooking) {
                $contexts[] = $this->bookingContextPayload($phoneBooking, 'tabler-phone', 'Phone match');
            }
        }

        return $contexts;
    }

    protected function bookingContextPayload(Booking $booking, ?string $icon = null, ?string $title = null): array
    {
        return [
            'label' => "Booking #{$booking->id}",
            'url' => route('admin.bookings.livewire', ['search' => $booking->booking_reference ?: $booking->id]),
            'child_edit_url' => null,
            'tone' => 'secondary',
            'destination' => 'active_booking',
            'icon' => $icon,
            'title' => $title,
        ];
    }

    protected function resolveCorrectedChildOutcome(BookingIntakeReview $review, BookingIntakeReviewChild $reviewChild): array
    {
        $analysis = app(BookingIntakeDetectionService::class)->analyze([
            'parent_name' => $review->parent_name,
            'parent_email' => $review->parent_email,
            'parent_phone' => $review->parent_phone,
            'children' => [[
                'child_name' => $reviewChild->child_name,
                'child_age' => $reviewChild->child_age,
                'child_grade' => $reviewChild->child_grade,
                'school_system' => $reviewChild->school_system,
                'service_interests' => $reviewChild->service_interests ?? [],
            ]],
        ]);
        $detectedChild = $analysis['child_reviews'][0] ?? null;

        if ($detectedChild && in_array($detectedChild['review_reason'] ?? null, ['blocked_parent', 'duplicate_child', 'repeat_submission'], true)) {
            throw new \InvalidArgumentException($detectedChild['review_detail'] ?? 'The corrected row still needs review before it can be approved.');
        }

        if (($detectedChild['review_reason'] ?? null) === 'suspected_contact_mismatch') {
            return [
                'review_reason' => 'suspected_contact_mismatch',
                'review_detail' => $detectedChild['review_detail'] ?? 'Admin corrected this review row into a verified contact update candidate.',
                'matched_booking_id' => $detectedChild['matched_booking_id'] ?? null,
            ];
        }

        $resolver = app(BookingParentIdentityResolver::class);
        $parentMatch = $resolver->findParentByContacts($review->parent_email, $review->parent_phone);
        $bookingMatch = $resolver->findBookingFamilyByContacts($review->parent_email, $review->parent_phone);

        if ($parentMatch['blocked_reason'] || $bookingMatch['blocked_reason']) {
            throw new \InvalidArgumentException($parentMatch['blocked_reason'] ?: $bookingMatch['blocked_reason']);
        }

        $targetBookingId = $bookingMatch['resolved_booking_id'];
        $targetBooking = $targetBookingId ? Booking::query()->find($targetBookingId) : null;
        $targetParentId = $targetBooking?->parent_id ?: $parentMatch['resolved_parent_id'];

        if ($targetBooking || $targetParentId) {
            $collisionSummary = $resolver->childCollisionSummary(collect([$reviewChild]), $targetBooking?->id, $targetParentId);

            if ($collisionSummary['has_duplicate_like'] || $collisionSummary['has_ambiguous_same_name']) {
                throw new \InvalidArgumentException('The corrected child still looks like the same existing child. Change the child/contact data further or dismiss the row.');
            }

            return [
                'review_reason' => 'existing_family_new_child',
                'review_detail' => 'Admin corrected this review row into a new sibling for the resolved existing family.',
                'matched_booking_id' => $targetBooking?->id,
            ];
        }

        return [
            'review_reason' => 'clean_new_customer',
            'review_detail' => 'Admin corrected this review row into a clean new customer.',
            'matched_booking_id' => null,
        ];
    }

    protected function syncReviewDetectionSummary(BookingIntakeReview $review): void
    {
        $review->load('reviewChildren');
        $reasons = $review->reviewChildren
            ->pluck('review_reason')
            ->unique()
            ->values();
        $reason = $reasons->count() === 1 ? $reasons->first() : 'mixed_children';
        $detail = $reason === 'mixed_children'
            ? 'Submission contains mixed child outcomes after admin correction.'
            : ($review->reviewChildren->first()?->review_detail ?? 'Admin corrected review row.');

        $review->update([
            'detection_reason' => $reason,
            'detection_detail' => $detail,
            'matched_booking_id' => $review->reviewChildren->pluck('matched_booking_id')->filter()->unique()->count() === 1
                ? $review->reviewChildren->pluck('matched_booking_id')->filter()->unique()->first()
                : null,
            'matched_child_id' => $review->reviewChildren->pluck('matched_child_id')->filter()->unique()->count() === 1
                ? $review->reviewChildren->pluck('matched_child_id')->filter()->unique()->first()
                : null,
        ]);
    }

    protected function updatedChildrenPayload(BookingIntakeReview $review, int $childIndex, array $updates): array
    {
        $payload = collect($review->children_payload ?? []);

        return $payload
            ->map(function (array $child, int $index) use ($childIndex, $updates): array {
                $currentIndex = array_key_exists('child_index', $child) ? (int) $child['child_index'] : $index;

                if ($currentIndex !== $childIndex) {
                    return $child;
                }

                return array_merge($child, $updates);
            })
            ->values()
            ->all();
    }

    protected function normalizedText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    protected function restoreCorrectionFormFromStoredRow(): void
    {
        if (! $this->correctionReviewChildId) {
            return;
        }

        $reviewChild = BookingIntakeReviewChild::query()->find($this->correctionReviewChildId);

        if (! $reviewChild) {
            return;
        }

        $this->correctionForm = [
            'child_name' => $reviewChild->child_name,
            'child_age' => $reviewChild->child_age,
            'child_grade' => $reviewChild->child_grade,
            'school_system' => $reviewChild->school_system,
        ];
    }

    protected function resetRenderCaches(): void
    {
        $this->reviewConflictStateCache = [];
        $this->reviewSummaryCache = [];
        $this->verifiedContactActionStateCache = [];
        $this->finalSubmissionActionStateCache = [];
        $this->childContextLinksCache = [];
        $this->contactMatchSnapshotCache = [];
        $this->bookingModelCache = [];
        $this->matchedChildModelCache = [];
    }

    protected function primeReviewRenderState(Collection $reviews): void
    {
        if ($reviews->isEmpty()) {
            return;
        }

        $reviewsNeedingVerifiedContact = $reviews->filter(
            fn (BookingIntakeReview $review): bool => $this->reviewNeedsVerifiedContactUpdate($review)
        );

        foreach ($reviewsNeedingVerifiedContact as $review) {
            $this->contactMatchSnapshot($review);
        }

        $bookingIds = $reviews
            ->flatMap(function (BookingIntakeReview $review): array {
                return array_filter(array_merge(
                    [$review->matched_booking_id],
                    $review->reviewChildren->pluck('matched_booking_id')->all()
                ));
            })
            ->map(fn ($id): int => (int) $id)
            ->merge(
                $reviewsNeedingVerifiedContact->flatMap(function (BookingIntakeReview $review): array {
                    $snapshot = $this->contactMatchSnapshot($review);
                    $bookingMatch = $snapshot['booking_match'];

                    return array_merge(
                        $bookingMatch['email_booking_ids'] ?? [],
                        $bookingMatch['phone_booking_ids'] ?? [],
                        $bookingMatch['all_booking_ids'] ?? [],
                        array_filter([$bookingMatch['resolved_booking_id'] ?? null])
                    );
                })->map(fn ($id): int => (int) $id)
            )
            ->unique()
            ->values();

        if ($bookingIds->isNotEmpty()) {
            $this->bookingModelCache = Booking::query()
                ->whereIn('id', $bookingIds)
                ->get()
                ->keyBy('id')
                ->all();
        }

        $matchedChildIds = $reviews
            ->flatMap(fn (BookingIntakeReview $review) => $review->reviewChildren->pluck('matched_child_id'))
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($matchedChildIds->isNotEmpty()) {
            $this->matchedChildModelCache = BookingChild::query()
                ->with('booking')
                ->whereIn('id', $matchedChildIds)
                ->get()
                ->keyBy('id')
                ->all();
        }

        foreach ($reviews as $review) {
            $this->reviewSummary($review);
            $this->reviewConflictState($review);
            $this->finalSubmissionActionState($review);

            if ($this->reviewNeedsVerifiedContactUpdate($review)) {
                $this->verifiedContactActionState($review);
            }

            foreach ($review->reviewChildren as $reviewChild) {
                $this->contextLinksForReviewChild($review, $reviewChild);
            }
        }
    }

    protected function contactMatchSnapshot(BookingIntakeReview $review): array
    {
        $key = app(BookingParentIdentityResolver::class)->normalizeEmail($review->parent_email)
            .'|'
            .app(BookingParentIdentityResolver::class)->normalizePhone($review->parent_phone);

        if (array_key_exists($key, $this->contactMatchSnapshotCache)) {
            return $this->contactMatchSnapshotCache[$key];
        }

        $resolver = app(BookingParentIdentityResolver::class);

        return $this->contactMatchSnapshotCache[$key] = [
            'booking_match' => $resolver->findBookingFamilyByContacts($review->parent_email, $review->parent_phone),
            'parent_match' => $resolver->findParentByContacts($review->parent_email, $review->parent_phone),
        ];
    }

    protected function bookingById(int $bookingId): ?Booking
    {
        if (array_key_exists($bookingId, $this->bookingModelCache)) {
            return $this->bookingModelCache[$bookingId];
        }

        $booking = Booking::query()->find($bookingId);
        $this->bookingModelCache[$bookingId] = $booking;

        return $booking;
    }

    protected function matchedChildById(int $bookingChildId): ?BookingChild
    {
        if (array_key_exists($bookingChildId, $this->matchedChildModelCache)) {
            return $this->matchedChildModelCache[$bookingChildId];
        }

        $matchedChild = BookingChild::query()
            ->with('booking')
            ->find($bookingChildId);

        $this->matchedChildModelCache[$bookingChildId] = $matchedChild;

        return $matchedChild;
    }
}
