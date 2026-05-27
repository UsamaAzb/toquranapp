<?php

namespace App\Livewire\Admin\Booking;

use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\BookingChildEmail;
use App\Models\BookingIntakeReview;
use App\Models\GradeLevel;
use App\Services\BookingChildEmailService;
use App\Support\BookingSubjectProvisioning;
use App\Support\BookingTransferReadiness;
use App\Support\PhoneNormalizer;
use App\Support\SchoolSystemOptions;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class BookingList extends Component
{
    use WithPagination;

    private const PRIMARY_QUEUE_STATES = [
        'pending_active',
        'confirmed_upcoming',
        'followup_due',
        'potential_later',
        'questionnaire_waiting',
        'transfer_ready',
    ];

    private const SECONDARY_QUEUE_STATES = [
        'completed',
        'cancelled',
    ];

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $filterQueueState = 'all';

    public string $filterEvaluation = 'all';

    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterQueueState' => ['except' => 'all'],
        'filterEvaluation' => ['except' => 'all'],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        $this->normalizePerPage();
        $this->normalizeFilterQueueState();
    }

    #[On('intake-created')]
    public function refreshAfterIntake(): void
    {
        // Re-render the queue after the intake modal writes or routes a submission.
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterQueueState(): void
    {
        $this->resetPage();
    }

    public function updatingFilterEvaluation(): void
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
        $this->filterQueueState = 'all';
        $this->filterEvaluation = 'all';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function filterBy(string $field, string $value): void
    {
        if ($field === 'queue_state') {
            $this->filterQueueState = $this->filterQueueState === $value ? 'all' : $value;

            if ($this->filterQueueState === 'transfer_ready') {
                $this->filterEvaluation = 'all';
            }

            $this->resetPage();

            return;
        }

        if ($field === 'evaluation') {
            $this->filterEvaluation = $this->filterEvaluation === $value ? 'all' : $value;
            $this->resetPage();
        }
    }

    public function render()
    {
        $bookings = $this->bookingsPage();
        $gradeTitles = $this->gradeTitlesForPage($bookings);
        $emailStatuses = $this->emailStatusesForPage($bookings);

        return view('livewire.admin.booking.booking-list', [
            'bookings' => $bookings,
            'gradeTitles' => $gradeTitles,
            'stats' => $this->stats,
            'emailStatuses' => $emailStatuses,
            'queueView' => $this->queueViewMeta(),
            'pendingIntakeReviewCount' => $this->pendingIntakeReviewCount(),
        ])->layout('components.layouts.app', ['title' => 'Booking Admin']);
    }

    public function workflowBadge(?string $status): array
    {
        return match ($status) {
            'confirmed' => ['label' => 'Confirmed', 'class' => 'bg-label-success'],
            'cancelled' => ['label' => 'Cancelled / Closed', 'class' => 'bg-label-danger'],
            'followup_required' => ['label' => 'Follow-Up', 'class' => 'bg-label-warning'],
            'questionnaire_sent' => ['label' => 'Questionnaire Sent', 'class' => 'bg-label-info'],
            'questionnaire_answer_received' => ['label' => 'Questionnaire Answered', 'class' => 'bg-label-primary'],
            default => ['label' => 'Pending', 'class' => 'bg-label-secondary'],
        };
    }

    public function meetingBadge(?string $status): array
    {
        return match ($status) {
            'completed' => ['label' => 'Completed', 'class' => 'bg-label-success'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-label-danger'],
            'no_meeting_required' => ['label' => 'No Meeting Required', 'class' => 'bg-label-info'],
            default => ['label' => 'Not Set', 'class' => 'bg-label-secondary'],
        };
    }

    public function evaluationBadge(?string $status): array
    {
        return match ($status) {
            'fit' => ['label' => 'Fit', 'class' => 'bg-label-success'],
            'unfit' => ['label' => 'Unfit', 'class' => 'bg-label-danger'],
            'PL' => ['label' => 'Potential Later (PL)', 'class' => 'bg-label-warning'],
            default => ['label' => 'Undecided', 'class' => 'bg-label-secondary'],
        };
    }

    public function transferBadge(?string $status): array
    {
        return match ($status) {
            'transferred' => ['label' => 'Transferred', 'class' => 'bg-label-primary'],
            default => ['label' => 'Not Transferred', 'class' => 'bg-label-secondary'],
        };
    }

    public function emailTypeShortLabel(string $emailType): string
    {
        return app(BookingChildEmailService::class)->emailTypeLabel($emailType, compact: true);
    }

    public function emailStatusBadge(?BookingChildEmail $status, ?string $emailType = null): array
    {
        return app(BookingChildEmailService::class)->emailStatusBadge($status, $emailType);
    }

    public function isRetiredEmailType(string $emailType): bool
    {
        return app(BookingChildEmailService::class)->isRetiredEmailType($emailType);
    }

    public function consultationTypeLabel(?string $value): string
    {
        return match ($value) {
            'online' => 'Online',
            'in-person' => 'In-Person',
            default => 'Undecided',
        };
    }

    public function gradeLabel(mixed $gradeValue, array $gradeTitles): string
    {
        if (blank($gradeValue)) {
            return '-';
        }

        return $gradeTitles[(int) $gradeValue] ?? (string) $gradeValue;
    }

    public function formatSchedule(mixed $date, mixed $time): string
    {
        $formattedDate = $this->formatDate($date);
        $formattedTime = $this->formatTime($time);

        if ($formattedDate === '-' && $formattedTime === '-') {
            return '-';
        }

        if ($formattedDate === '-') {
            return $formattedTime;
        }

        if ($formattedTime === '-') {
            return $formattedDate;
        }

        return $formattedDate.' at '.$formattedTime;
    }

    public function formatDate(mixed $value): string
    {
        if (blank($value)) {
            return '-';
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('d M Y');
        }

        try {
            return Carbon::parse((string) $value)->format('d M Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public function formatTime(mixed $value): string
    {
        if (blank($value)) {
            return '-';
        }

        $normalized = trim((string) $value);

        if (preg_match('/^\d{1,2}\.\d{2}$/', $normalized)) {
            $normalized = str_replace('.', ':', $normalized);
        }

        try {
            return Carbon::parse($normalized)->format('g:i A');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public function serviceInterestSummary(mixed $child, Booking $booking): string
    {
        $services = collect(data_get($child, 'service_interests', []))
            ->map(fn ($service) => Booking::displayServiceInterest(is_string($service) ? trim($service) : $service))
            ->filter()
            ->values();

        if ($services->isEmpty() && filled($booking->service_interest)) {
            $services = collect(explode(',', (string) $booking->service_interest))
                ->map(fn ($service) => trim($service))
                ->filter()
                ->map(fn ($service) => Booking::displayServiceInterest($service))
                ->values();
        }

        return $services->implode(', ') ?: 'Need Guidance';
    }

    public function transferBlockedReasonFor(mixed $child, Booking $booking): ?string
    {
        if (! $child instanceof BookingChild) {
            return 'Transfer is only available after this booking is normalized into a real child row.';
        }

        return BookingTransferReadiness::blockedReason($child, $booking);
    }

    public function unresolvedTransferServicesFor(mixed $child, Booking $booking): array
    {
        if (! $child instanceof BookingChild) {
            return [];
        }

        return BookingTransferReadiness::unresolvedServiceValues($child, $booking);
    }

    #[Computed]
    public function stats(): array
    {
        $counts = $this->queueStateCounts();

        return [
            $this->statCard('Pending', $counts['pending_active'], 'secondary', 'bx-loader-circle', 'queue_state', 'pending_active', 'Open items waiting for a first real workflow decision.', 'primary'),
            $this->statCard('Confirmed', $counts['confirmed_upcoming'], 'success', 'bx-calendar-check', 'queue_state', 'confirmed_upcoming', 'Upcoming confirmed consultations that still need follow-through.', 'primary'),
            $this->statCard('Follow-Up Due', $counts['followup_due'], 'warning', 'bx-time-five', 'queue_state', 'followup_due', 'Overdue or due-today follow-up work that should surface first.', 'primary'),
            $this->statCard('Potential Later', $counts['potential_later'], 'warning', 'bx-time-five', 'queue_state', 'potential_later', 'Children marked PL so they stay reachable without cluttering the active backlog.', 'primary'),
            $this->statCard('Questionnaire', $counts['questionnaire_waiting'], 'info', 'bx-message-rounded-detail', 'queue_state', 'questionnaire_waiting', 'Reserved questionnaire states kept visible without mixing into pending.', 'primary'),
            $this->statCard('Fit / Ready', $counts['transfer_ready'], 'primary', 'bx-check-shield', 'queue_state', 'transfer_ready', 'Children who can be transferred right now.', 'primary'),
            $this->statCard('Completed', $counts['completed'], 'success', 'bx-badge-check', 'queue_state', 'completed', 'Closed consultations finished successfully.', 'secondary'),
            $this->statCard('Cancelled', $counts['cancelled'], 'danger', 'bx-x-circle', 'queue_state', 'cancelled', 'Closed work that should stay visible without inflating backlog.', 'secondary'),
        ];
    }

    protected function bookingsPage(): LengthAwarePaginator
    {
        $bookings = $this->orderedBookingsQuery()->paginate($this->perPage);

        $bookings->getCollection()->transform(function (Booking $booking) {
            $allChildren = $this->sortChildrenForDisplay($booking->intakeChildren()->values(), $booking);
            $displayChildren = $this->filteredDisplayChildren($booking, $allChildren);

            $booking->displayChildren = $displayChildren;
            $booking->displayChildCount = $displayChildren->count();
            $booking->showsNoChildrenState = $allChildren->isEmpty();
            $booking->hasSiblingContext = $allChildren->count() > 1;
            $booking->hasExistingFamilyContext = filled($booking->parent_id)
                || $booking->existingFamilyContextBookingIds()->isNotEmpty()
                || (bool) ($booking->has_matching_parent_identity_context ?? false);
            $booking->familyContextLink = $this->resolveFamilyContextLink($booking);
            $booking->siblingContextLink = $this->resolveSiblingContextLink($booking);
            $booking->shouldShowExistingFamilyBadge = $booking->hasExistingFamilyContext && $booking->familyContextLink !== null;

            return $booking;
        });

        return $bookings;
    }

    protected function orderedBookingsQuery(): Builder
    {
        $query = $this->baseBookingsQuery();

        $this->applySearchFilter($query);
        $this->applyOperationalFilters($query);
        $this->applyUrgencySort($query);

        return $query;
    }

    protected function filteredDisplayChildren(Booking $booking, Collection $children): Collection
    {
        if ($children->isEmpty()) {
            return $children;
        }

        return $children
            ->filter(fn ($child) => $this->childMatchesActiveFilters($child, $booking))
            ->values();
    }

    protected function childMatchesActiveFilters(mixed $child, Booking $booking): bool
    {
        if ($this->filterEvaluation !== 'all' && $this->childEvaluationOutcome($child) !== $this->filterEvaluation) {
            return false;
        }

        return match ($this->filterQueueState) {
            'pending', 'pending_active' => $this->derivedQueueStateForChild($child) === 'pending_active',
            'confirmed', 'confirmed_upcoming' => $this->derivedQueueStateForChild($child) === 'confirmed_upcoming',
            'followup_required' => in_array($this->derivedQueueStateForChild($child), ['followup_due', 'followup_future'], true),
            'followup_due' => $this->derivedQueueStateForChild($child) === 'followup_due',
            'potential_later' => $this->derivedQueueStateForChild($child) === 'potential_later',
            'questionnaire_waiting' => $this->derivedQueueStateForChild($child) === 'questionnaire_waiting',
            'transfer_ready' => $child instanceof BookingChild && BookingTransferReadiness::canTransfer($child, $booking),
            'completed' => $this->derivedQueueStateForChild($child) === 'completed',
            'cancelled' => $this->derivedQueueStateForChild($child) === 'cancelled',
            'no_children' => false,
            default => true,
        };
    }

    protected function baseBookingsQuery(): Builder
    {
        return Booking::query()
            ->select('bookings.*')
            ->selectRaw($this->existingFamilyContextExpression().' as has_matching_parent_identity_context')
            ->with([
                'children' => function ($query) {
                    $query->where(function ($childQuery) {
                        $childQuery->where('transfer_status', '!=', 'transferred')
                            ->orWhereNull('transfer_status');
                    })
                        ->with([
                            'emails' => fn ($emailQuery) => $emailQuery
                                ->orderByDesc('last_attempt_at')
                                ->orderByDesc('id'),
                            'updatedByUser',
                        ])
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->where(function ($query) {
                $query->whereHas('children', function ($childQuery) {
                    $childQuery->where(function ($transferQuery) {
                        $transferQuery->where('transfer_status', '!=', 'transferred')
                            ->orWhereNull('transfer_status');
                    });
                })->orWhere(function ($legacyQuery) {
                    $legacyQuery->whereDoesntHave('children')
                        ->where(function ($transferQuery) {
                            $transferQuery->whereNull('transfer')
                                ->orWhere('transfer', 0)
                                ->orWhere('transfer', '0');
                        });
                });
            });
    }

    protected function applySearchFilter(Builder $query): void
    {
        $search = trim($this->search);

        if ($search === '') {
            return;
        }

        $query->where(function (Builder $searchQuery) use ($search) {
            $searchQuery->where('parent_name', 'like', "%{$search}%")
                ->orWhere('parent_email', 'like', "%{$search}%")
                ->orWhere('parent_phone', 'like', "%{$search}%")
                ->orWhere('booking_reference', 'like', "%{$search}%")
                ->orWhereHas('children', function (Builder $childQuery) use ($search) {
                    $childQuery->where(function (Builder $transferQuery) {
                        $transferQuery->where('transfer_status', '!=', 'transferred')
                            ->orWhereNull('transfer_status');
                    })->where(function (Builder $fieldQuery) use ($search) {
                        $fieldQuery->where('child_name', 'like', "%{$search}%")
                            ->orWhere('current_school', 'like', "%{$search}%")
                            ->orWhere('school_system', 'like', "%{$search}%")
                            ->orWhere('notes', 'like', "%{$search}%");
                    });
                });
        });
    }

    protected function applyOperationalFilters(Builder $query): void
    {
        if ($this->filterEvaluation === 'all' && $this->filterQueueState === 'all') {
            return;
        }

        $evaluation = $this->filterEvaluation;
        $queueState = $this->filterQueueState;

        $query->where(function (Builder $filteredQuery) use ($evaluation, $queueState) {
            $filteredQuery->where(function (Builder $childBookingQuery) use ($evaluation, $queueState) {
                $childBookingQuery->whereHas('children', function (Builder $childQuery) use ($evaluation, $queueState) {
                    $this->applyCombinedChildFilters($childQuery, $queueState, $evaluation);
                });
            })->orWhere(function (Builder $legacyQuery) use ($evaluation, $queueState) {
                $legacyQuery->whereDoesntHave('children')
                    ->where(function (Builder $transferQuery) {
                        $transferQuery->whereNull('transfer')
                            ->orWhere('transfer', 0)
                            ->orWhere('transfer', '0');
                    });

                $this->applyCombinedLegacyFilters($legacyQuery, $queueState, $evaluation);
            });
        });
    }

    protected function applyCombinedChildFilters(Builder $query, string $queueState, string $evaluation): void
    {
        $this->applyNonTransferredChildScope($query);

        if ($evaluation !== 'all') {
            $query->where('evaluation_outcome', $evaluation);
        }

        if ($queueState !== 'all') {
            $this->applyChildQueueStateCondition($query, $queueState);
        }
    }

    protected function applyCombinedLegacyFilters(Builder $query, string $queueState, string $evaluation): void
    {
        if ($evaluation !== 'all') {
            $this->applyLegacyEvaluationFilter($query, $evaluation);
        }

        if ($queueState !== 'all') {
            $this->applyLegacyQueueStateFilter($query, $queueState);
        }
    }

    protected function applyQueueStateFilter(Builder $query, string $queueState): void
    {
        switch ($queueState) {
            case 'pending':
            case 'pending_active':
                $query->where(function (Builder $pendingQuery) {
                    $pendingQuery->whereHas('children', function (Builder $childQuery) {
                        $this->applyChildQueueStateFilter($childQuery, 'pending_active');
                    })->orWhere(function (Builder $legacyQuery) {
                        $legacyQuery->whereDoesntHave('children')
                            ->where('status', 'pending');
                    });
                });
                break;

            case 'confirmed':
            case 'confirmed_upcoming':
                $query->where(function (Builder $confirmedQuery) {
                    $confirmedQuery->whereHas('children', function (Builder $childQuery) {
                        $this->applyChildQueueStateFilter($childQuery, 'confirmed_upcoming');
                    })->orWhere(function (Builder $legacyQuery) {
                        $legacyQuery->whereDoesntHave('children')
                            ->where('status', 'confirmed');
                    });
                });
                break;

            case 'followup_required':
                $query->where(function (Builder $followupQuery) {
                    $followupQuery->whereHas('children', function (Builder $childQuery) {
                        $this->applyChildQueueStateFilter($childQuery, 'followup_required');
                    })->orWhere(function (Builder $legacyQuery) {
                        $legacyQuery->whereDoesntHave('children')
                            ->where('status', 'followup');
                    });
                });
                break;

            case 'followup_due':
                $query->where(function (Builder $followupDueQuery) {
                    $followupDueQuery->whereHas('children', function (Builder $childQuery) {
                        $this->applyChildQueueStateFilter($childQuery, 'followup_due');
                    })->orWhere(function (Builder $legacyQuery) {
                        $legacyQuery->whereDoesntHave('children')
                            ->where('status', 'followup')
                            ->whereDate('follow_up_date', '<=', now()->toDateString());
                    });
                });
                break;

            case 'potential_later':
                $query->where(function (Builder $potentialLaterQuery) {
                    $potentialLaterQuery->whereHas('children', function (Builder $childQuery) {
                        $this->applyChildQueueStateFilter($childQuery, 'potential_later');
                    })->orWhere(function (Builder $legacyQuery) {
                        $legacyQuery->whereDoesntHave('children')
                            ->where('status', 'PL');
                    });
                });
                break;

            case 'questionnaire_waiting':
                $query->whereHas('children', function (Builder $childQuery) {
                    $this->applyChildQueueStateFilter($childQuery, 'questionnaire_waiting');
                });
                break;

            case 'transfer_ready':
                $query->whereHas('children', function (Builder $childQuery) {
                    $this->applyChildQueueStateFilter($childQuery, 'transfer_ready');
                });
                break;

            case 'completed':
                $query->whereHas('children', function (Builder $childQuery) {
                    $this->applyChildQueueStateFilter($childQuery, 'completed');
                });
                break;

            case 'cancelled':
                $query->where(function (Builder $cancelledQuery) {
                    $cancelledQuery->whereHas('children', function (Builder $childQuery) {
                        $this->applyChildQueueStateFilter($childQuery, 'cancelled');
                    })->orWhere(function (Builder $legacyQuery) {
                        $legacyQuery->whereDoesntHave('children')
                            ->where('status', 'cancelled');
                    });
                });
                break;

            case 'no_children':
                $query->whereDoesntHave('children')
                    ->where(function (Builder $legacyQuery) {
                        $legacyQuery->whereNull('child_name')
                            ->orWhere('child_name', '');
                    });
                break;
        }
    }

    protected function applyChildQueueStateFilter(Builder $query, string $queueState): void
    {
        $this->applyNonTransferredChildScope($query);

        $this->applyChildQueueStateCondition($query, $queueState);
    }

    protected function applyNonTransferredChildScope(Builder $query): void
    {
        $query->where(function (Builder $transferQuery) {
            $transferQuery->where('transfer_status', '!=', 'transferred')
                ->orWhereNull('transfer_status');
        });
    }

    protected function applyNonPotentialLaterChildScope(Builder $query): void
    {
        $query->where(function (Builder $potentialLaterQuery) {
            $potentialLaterQuery->whereNull('evaluation_outcome')
                ->orWhere('evaluation_outcome', '!=', 'PL');
        });
    }

    protected function applyChildQueueStateCondition(Builder $query, string $queueState): void
    {
        if ($queueState !== 'potential_later') {
            $this->applyNonPotentialLaterChildScope($query);
        }

        switch ($queueState) {
            case 'pending_active':
                $query->where('workflow_status', 'pending')
                    ->where(function (Builder $stateQuery) {
                        $stateQuery->whereNull('meeting_disposition')
                            ->orWhereNotIn('meeting_disposition', ['completed', 'cancelled']);
                    });
                break;

            case 'confirmed_upcoming':
                $query->where('workflow_status', 'confirmed')
                    ->whereNull('meeting_disposition');
                break;

            case 'followup_required':
                $query->where('workflow_status', 'followup_required')
                    ->where(function (Builder $stateQuery) {
                        $stateQuery->whereNull('meeting_disposition')
                            ->orWhereNotIn('meeting_disposition', ['completed', 'cancelled']);
                    });
                break;

            case 'followup_due':
                $query->where('workflow_status', 'followup_required')
                    ->whereDate('followup_date', '<=', now()->toDateString())
                    ->where(function (Builder $stateQuery) {
                        $stateQuery->whereNull('meeting_disposition')
                            ->orWhereNotIn('meeting_disposition', ['completed', 'cancelled']);
                    });
                break;

            case 'potential_later':
                $query->where('evaluation_outcome', 'PL');
                break;

            case 'questionnaire_waiting':
                $query->whereIn('workflow_status', ['questionnaire_sent', 'questionnaire_answer_received'])
                    ->whereNull('meeting_disposition');
                break;

            case 'transfer_ready':
                $transferReadyIds = $this->transferReadyChildIds();

                if ($transferReadyIds === []) {
                    $query->whereRaw('1 = 0');

                    break;
                }

                $query->whereIn($query->getModel()->qualifyColumn('id'), $transferReadyIds);
                break;

            case 'completed':
                $query->where('meeting_disposition', 'completed');
                break;

            case 'cancelled':
                $query->where(function (Builder $cancelledQuery) {
                    $cancelledQuery->where('meeting_disposition', 'cancelled')
                        ->orWhere('workflow_status', 'cancelled');
                });
                break;
        }
    }

    protected function applyLegacyEvaluationFilter(Builder $query, string $evaluation): void
    {
        match ($evaluation) {
            'fit', 'unfit', 'PL' => $query->where('status', $evaluation),
            default => $query->whereRaw('1 = 0'),
        };
    }

    protected function applyLegacyQueueStateFilter(Builder $query, string $queueState): void
    {
        match ($queueState) {
            'pending', 'pending_active' => $query->where('status', 'pending'),
            'confirmed', 'confirmed_upcoming' => $query->where('status', 'confirmed'),
            'followup_required' => $query->where('status', 'followup'),
            'followup_due' => $query->where('status', 'followup')
                ->whereDate('follow_up_date', '<=', now()->toDateString()),
            'potential_later' => $query->where('status', 'PL'),
            'cancelled' => $query->where('status', 'cancelled'),
            default => $query->whereRaw('1 = 0'),
        };
    }

    protected function applyUrgencySort(Builder $query): void
    {
        $query->orderByRaw($this->bookingRankExpression())
            ->orderByRaw($this->bookingDateExpression())
            ->orderBy('bookings.id');
    }

    protected function bookingRankExpression(): string
    {
        $today = now()->toDateString();

        return <<<SQL
COALESCE(
    (
        SELECT MIN(
            CASE
                WHEN bc.evaluation_outcome = 'PL' THEN 55
                WHEN bc.meeting_disposition = 'cancelled' OR bc.workflow_status = 'cancelled' THEN 80
                WHEN bc.meeting_disposition = 'completed' THEN 70
                WHEN bc.workflow_status = 'followup_required'
                    AND bc.followup_date IS NOT NULL
                    AND DATE(bc.followup_date) <= '{$today}' THEN 10
                WHEN bc.workflow_status = 'confirmed'
                    AND bc.meeting_disposition IS NULL THEN 20
                WHEN bc.workflow_status = 'questionnaire_sent'
                    AND bc.meeting_disposition IS NULL THEN 30
                WHEN bc.workflow_status = 'questionnaire_answer_received'
                    AND bc.meeting_disposition IS NULL THEN 40
                WHEN bc.workflow_status = 'pending'
                    AND (bc.meeting_disposition IS NULL OR bc.meeting_disposition NOT IN ('completed', 'cancelled')) THEN 50
                WHEN bc.workflow_status = 'followup_required' THEN 60
                ELSE 65
            END
        )
        FROM booking_children bc
        WHERE bc.booking_id = bookings.id
          AND (bc.transfer_status != 'transferred' OR bc.transfer_status IS NULL)
    ),
    CASE
        WHEN bookings.status = 'PL' THEN 55
        WHEN bookings.status = 'cancelled' THEN 80
        WHEN bookings.status = 'followup'
            AND bookings.follow_up_date IS NOT NULL
            AND DATE(bookings.follow_up_date) <= '{$today}' THEN 10
        WHEN bookings.status = 'confirmed' THEN 20
        WHEN bookings.status = 'questionnaire_sent' THEN 30
        WHEN bookings.status = 'questionnaire_answer_received' THEN 40
        WHEN bookings.status = 'pending' THEN 50
        WHEN bookings.status = 'followup' THEN 60
        ELSE 65
    END
)
SQL;
    }

    protected function bookingDateExpression(): string
    {
        $childScheduleExpr = $this->sqlDateTimeExpression('bc.scheduled_date', 'bc.scheduled_time');
        $bookingScheduleExpr = $this->sqlDateTimeExpression('bookings.consultation_date', 'bookings.consultation_time');

        return <<<SQL
COALESCE(
    (
        SELECT MIN(
            CASE
                WHEN bc.workflow_status = 'followup_required' AND bc.followup_date IS NOT NULL THEN bc.followup_date
                WHEN bc.workflow_status = 'confirmed' AND bc.meeting_disposition IS NULL THEN {$childScheduleExpr}
                WHEN bc.evaluation_outcome = 'PL' AND bc.followup_date IS NOT NULL THEN bc.followup_date
                ELSE COALESCE(bc.updated_at, bc.created_at, '9999-12-31 23:59:59')
            END
        )
        FROM booking_children bc
        WHERE bc.booking_id = bookings.id
          AND (bc.transfer_status != 'transferred' OR bc.transfer_status IS NULL)
    ),
    CASE
        WHEN bookings.status = 'followup' AND bookings.follow_up_date IS NOT NULL THEN bookings.follow_up_date
        WHEN bookings.status = 'confirmed' THEN {$bookingScheduleExpr}
        WHEN bookings.status = 'PL' AND bookings.follow_up_date IS NOT NULL THEN bookings.follow_up_date
        ELSE COALESCE(bookings.updated_at, bookings.created_at, '9999-12-31 23:59:59')
    END
)
SQL;
    }

    protected function sqlDateTimeExpression(string $dateColumn, string $timeColumn): string
    {
        if ($this->usesSqliteConnection()) {
            return "COALESCE({$dateColumn}, '9999-12-31') || ' ' || COALESCE({$timeColumn}, '23:59:59')";
        }

        return "CONCAT(COALESCE({$dateColumn}, '9999-12-31'), ' ', COALESCE({$timeColumn}, '23:59:59'))";
    }

    protected function usesSqliteConnection(): bool
    {
        return Booking::query()->getConnection()->getDriverName() === 'sqlite';
    }

    protected function resolveBookingFamilyLink(Booking $booking, string $transferredLabel, string $activeLabel): ?array
    {
        $search = collect([
            $booking->parent_email,
            $booking->parent_phone,
            $booking->parent_name,
        ])
            ->map(fn ($v) => is_string($v) ? trim($v) : null)
            ->filter()
            ->first();

        if (! $search) {
            return null;
        }

        if (filled($booking->parent_id)) {
            return [
                'label' => $transferredLabel,
                'url' => route('admin.bookings.transferred', ['search' => $search]),
                'tone' => 'info',
            ];
        }

        return [
            'label' => $activeLabel,
            'url' => route('admin.bookings.livewire', ['search' => $search]),
            'tone' => 'info',
        ];
    }

    protected function resolveFamilyContextLink(Booking $booking): ?array
    {
        if (! $booking->hasExistingFamilyContext) {
            return null;
        }

        return $this->resolveBookingFamilyLink($booking, 'View Transferred Family', 'View Other Bookings');
    }

    protected function resolveSiblingContextLink(Booking $booking): ?array
    {
        if (! $booking->hasSiblingContext) {
            return null;
        }

        return $this->resolveBookingFamilyLink($booking, 'View Siblings (Transferred)', 'View Siblings');
    }

    protected function existingFamilyContextExpression(): string
    {
        $bookingEmailExpression = $this->normalizedEmailExpression('bookings.parent_email');
        $bookingPhoneExpression = $this->normalizedPhoneExpression('bookings.parent_phone');
        $matchEmailExpression = $this->normalizedEmailExpression('family_match.parent_email');
        $matchPhoneExpression = $this->normalizedPhoneExpression('family_match.parent_phone');
        $matchBlankPhoneExpression = $this->blankNormalizedPhoneExpression('family_match.parent_phone');
        $matchBlankEmailExpression = $this->blankNormalizedEmailExpression('family_match.parent_email');

        return <<<SQL
CASE
    WHEN EXISTS (
        SELECT 1
        FROM bookings AS family_match
        WHERE family_match.id != bookings.id
          AND (
                (
                    {$bookingEmailExpression} != ''
                    AND {$bookingPhoneExpression} != ''
                    AND (
                        ({$matchEmailExpression} = {$bookingEmailExpression} AND {$matchPhoneExpression} = {$bookingPhoneExpression})
                        OR ({$matchEmailExpression} = {$bookingEmailExpression} AND ({$matchBlankPhoneExpression} OR family_match.parent_phone IS NULL))
                        OR ({$matchPhoneExpression} = {$bookingPhoneExpression} AND ({$matchBlankEmailExpression} OR family_match.parent_email IS NULL))
                    )
                )
                OR (
                    {$bookingEmailExpression} != ''
                    AND {$bookingPhoneExpression} = ''
                    AND {$matchEmailExpression} = {$bookingEmailExpression}
                )
                OR (
                    {$bookingPhoneExpression} != ''
                    AND {$bookingEmailExpression} = ''
                    AND {$matchPhoneExpression} = {$bookingPhoneExpression}
                )
            )
    ) THEN 1
    ELSE 0
END
SQL;
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
        return PhoneNormalizer::sqlExpression($column, Booking::query()->getConnection()->getDriverName());
    }

    protected function blankNormalizedPhoneExpression(string $column): string
    {
        return $this->normalizedPhoneExpression($column)." = ''";
    }

    protected function queueStateCount(string $queueState): int
    {
        return $this->queueStateCounts()[$queueState] ?? 0;
    }

    protected function queueStateCounts(): array
    {
        return once(function () {
            $counts = array_fill_keys([
                'pending_active',
                'confirmed_upcoming',
                'followup_due',
                'potential_later',
                'questionnaire_waiting',
                'transfer_ready',
                'completed',
                'cancelled',
            ], 0);

            foreach ($this->childQueueStateCounts() as $queueState => $value) {
                $counts[$queueState] += $value;
            }

            foreach ($this->legacyQueueStateCounts() as $queueState => $value) {
                $counts[$queueState] += $value;
            }

            $counts['transfer_ready'] = $this->transferReadyCount();

            return $counts;
        });
    }

    protected function childQueueStateCounts(): array
    {
        $query = BookingChild::query();

        $this->applyNonTransferredChildScope($query);

        if ($this->filterEvaluation !== 'all') {
            $query->where('evaluation_outcome', $this->filterEvaluation);
        }

        $today = now()->toDateString();
        $notPotentialLater = "(evaluation_outcome IS NULL OR evaluation_outcome != 'PL')";
        $meetingOpen = "(meeting_disposition IS NULL OR meeting_disposition NOT IN ('completed', 'cancelled'))";
        $aggregate = $query
            ->selectRaw("SUM(CASE WHEN {$notPotentialLater} AND workflow_status = 'pending' AND {$meetingOpen} THEN 1 ELSE 0 END) as pending_active_count")
            ->selectRaw("SUM(CASE WHEN {$notPotentialLater} AND workflow_status = 'confirmed' AND meeting_disposition IS NULL THEN 1 ELSE 0 END) as confirmed_upcoming_count")
            ->selectRaw("SUM(CASE WHEN {$notPotentialLater} AND workflow_status = 'followup_required' AND followup_date IS NOT NULL AND DATE(followup_date) <= '{$today}' AND {$meetingOpen} THEN 1 ELSE 0 END) as followup_due_count")
            ->selectRaw("SUM(CASE WHEN evaluation_outcome = 'PL' THEN 1 ELSE 0 END) as potential_later_count")
            ->selectRaw("SUM(CASE WHEN {$notPotentialLater} AND workflow_status IN ('questionnaire_sent', 'questionnaire_answer_received') AND meeting_disposition IS NULL THEN 1 ELSE 0 END) as questionnaire_waiting_count")
            ->selectRaw("SUM(CASE WHEN {$notPotentialLater} AND meeting_disposition = 'completed' THEN 1 ELSE 0 END) as completed_count")
            ->selectRaw("SUM(CASE WHEN {$notPotentialLater} AND (meeting_disposition = 'cancelled' OR workflow_status = 'cancelled') THEN 1 ELSE 0 END) as cancelled_count")
            ->first();

        return [
            'pending_active' => (int) ($aggregate->pending_active_count ?? 0),
            'confirmed_upcoming' => (int) ($aggregate->confirmed_upcoming_count ?? 0),
            'followup_due' => (int) ($aggregate->followup_due_count ?? 0),
            'potential_later' => (int) ($aggregate->potential_later_count ?? 0),
            'questionnaire_waiting' => (int) ($aggregate->questionnaire_waiting_count ?? 0),
            'completed' => (int) ($aggregate->completed_count ?? 0),
            'cancelled' => (int) ($aggregate->cancelled_count ?? 0),
        ];
    }

    protected function legacyQueueStateCounts(): array
    {
        $query = Booking::query()
            ->whereDoesntHave('children')
            ->where(function (Builder $transferQuery) {
                $transferQuery->whereNull('transfer')
                    ->orWhere('transfer', 0)
                    ->orWhere('transfer', '0');
            });

        if ($this->filterEvaluation !== 'all') {
            $this->applyLegacyEvaluationFilter($query, $this->filterEvaluation);
        }

        $today = now()->toDateString();
        $aggregate = $query
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_active_count")
            ->selectRaw("SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_upcoming_count")
            ->selectRaw("SUM(CASE WHEN status = 'followup' AND follow_up_date IS NOT NULL AND DATE(follow_up_date) <= '{$today}' THEN 1 ELSE 0 END) as followup_due_count")
            ->selectRaw("SUM(CASE WHEN status = 'PL' THEN 1 ELSE 0 END) as potential_later_count")
            ->selectRaw("SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count")
            ->first();

        return [
            'pending_active' => (int) ($aggregate->pending_active_count ?? 0),
            'confirmed_upcoming' => (int) ($aggregate->confirmed_upcoming_count ?? 0),
            'followup_due' => (int) ($aggregate->followup_due_count ?? 0),
            'potential_later' => (int) ($aggregate->potential_later_count ?? 0),
            'questionnaire_waiting' => 0,
            'completed' => 0,
            'cancelled' => (int) ($aggregate->cancelled_count ?? 0),
        ];
    }

    protected function transferReadyCount(): int
    {
        if ($this->filterEvaluation !== 'all' && $this->filterEvaluation !== 'fit') {
            return 0;
        }

        return count($this->transferReadyChildIds());
    }

    protected function statCard(
        string $label,
        int $value,
        string $tone,
        string $icon,
        string $field,
        string $filterValue,
        string $hint,
        string $group
    ): array {
        return [
            'label' => $label,
            'value' => $value,
            'tone' => $tone,
            'icon' => $icon,
            'field' => $field,
            'filterValue' => $filterValue,
            'hint' => $hint,
            'group' => $group,
            'active' => $field === 'queue_state' && $this->filterQueueState === $filterValue,
        ];
    }

    protected function gradeTitlesForPage(LengthAwarePaginator $bookings): array
    {
        $gradeIds = $bookings->getCollection()
            ->flatMap(function (Booking $booking) {
                return collect($booking->displayChildren ?? [])
                    ->pluck('child_grade');
            })
            ->filter()
            ->map(fn ($grade) => (int) $grade)
            ->unique()
            ->values();

        if ($gradeIds->isEmpty()) {
            return [];
        }

        return GradeLevel::query()
            ->whereIn('id', $gradeIds)
            ->pluck('title', 'id')
            ->all();
    }

    protected function normalizePerPage(): void
    {
        if (! in_array($this->perPage, [10, 25, 50], true)) {
            $this->perPage = 10;
        }
    }

    protected function normalizeFilterQueueState(): void
    {
        $aliases = [
            'pending' => 'pending_active',
            'confirmed' => 'confirmed_upcoming',
            'pl' => 'potential_later',
        ];

        $this->filterQueueState = $aliases[$this->filterQueueState] ?? $this->filterQueueState;

        $allowed = array_merge(
            ['all', 'followup_required', 'no_children'],
            self::PRIMARY_QUEUE_STATES,
            self::SECONDARY_QUEUE_STATES
        );

        if (! in_array($this->filterQueueState, $allowed, true)) {
            $this->filterQueueState = 'all';
        }
    }

    protected function emailStatusesForPage(LengthAwarePaginator $bookings): Collection
    {
        $children = $bookings->getCollection()
            ->flatMap(function (Booking $booking) {
                return collect($booking->displayChildren ?? [])
                    ->filter(fn ($child) => $child instanceof BookingChild)
                    ->values();
            })
            ->values();

        return app(BookingChildEmailService::class)->latestStatusesForChildren($children);
    }

    protected function transferReadyChildIds(): array
    {
        return once(function () {
            $candidateQuery = $this->transferReadyCandidateQuery();

            if ($candidateQuery === null) {
                return [];
            }

            return $candidateQuery
                ->get()
                ->filter(fn (BookingChild $child) => BookingTransferReadiness::canTransfer($child, $child->booking))
                ->pluck('id')
                ->all();
        });
    }

    protected function transferReadyCandidateQuery(): ?Builder
    {
        $validGradeLevelIds = $this->transferReadyValidGradeLevelIds();

        if ($validGradeLevelIds === []) {
            return null;
        }

        $allowedSchoolAliases = $this->transferReadySchoolAliases();
        $gradeLevelExpression = 'COALESCE(NULLIF(booking_children.child_grade, 0), NULLIF(bookings.child_grade, 0))';
        $childSchoolExpression = "LOWER(TRIM(COALESCE(booking_children.school_system, '')))";
        $bookingSchoolExpression = "LOWER(TRIM(COALESCE(bookings.school_system, '')))";
        $blankChildServiceExpression = "(booking_children.service_interests IS NULL OR booking_children.service_interests IN ('', '[]', 'null'))";

        return BookingChild::query()
            ->join('bookings', 'bookings.id', '=', 'booking_children.booking_id')
            ->select('booking_children.*')
            ->with(['booking:id,parent_id,parent_name,parent_email,parent_phone,child_grade,current_school,school_system,service_interest'])
            ->where(function (Builder $transferQuery) {
                $transferQuery->where('booking_children.transfer_status', '!=', 'transferred')
                    ->orWhereNull('booking_children.transfer_status');
            })
            ->where('booking_children.evaluation_outcome', 'fit')
            ->whereIn('booking_children.meeting_disposition', ['completed', 'cancelled', 'no_meeting_required'])
            ->whereRaw("TRIM(COALESCE(bookings.parent_name, '')) != ''")
            ->whereRaw("TRIM(COALESCE(booking_children.child_name, '')) != ''")
            ->whereRaw($this->rawInExpression($gradeLevelExpression, $validGradeLevelIds), $validGradeLevelIds)
            ->where(function (Builder $schoolQuery) use ($allowedSchoolAliases, $childSchoolExpression, $bookingSchoolExpression) {
                $schoolQuery->whereRaw($this->rawInExpression($childSchoolExpression, $allowedSchoolAliases), $allowedSchoolAliases)
                    ->orWhere(function (Builder $fallbackSchoolQuery) use ($allowedSchoolAliases, $childSchoolExpression, $bookingSchoolExpression) {
                        $fallbackSchoolQuery->whereRaw("{$childSchoolExpression} = ''")
                            ->whereRaw($this->rawInExpression($bookingSchoolExpression, $allowedSchoolAliases), $allowedSchoolAliases);
                    });
            })
            ->where(function (Builder $serviceQuery) use ($blankChildServiceExpression) {
                $serviceQuery->where(function (Builder $childServiceQuery) {
                    $childServiceQuery->whereNotNull('booking_children.service_interests')
                        ->whereNotIn('booking_children.service_interests', ['', '[]', 'null']);
                })->orWhere(function (Builder $bookingServiceQuery) use ($blankChildServiceExpression) {
                    $bookingServiceQuery->whereRaw($blankChildServiceExpression)
                        ->whereRaw("TRIM(COALESCE(bookings.service_interest, '')) != ''");
                });
            });
    }

    protected function transferReadyValidGradeLevelIds(): array
    {
        return once(function () {
            if (! Schema::hasTable('grade_level_subjects')) {
                return [];
            }

            $requiredSubjectIds = BookingSubjectProvisioning::activeByDefaultSubjectIds();

            $query = DB::table('grade_level_subjects')
                ->where('status', 'active')
                ->whereIn('subject_id', $requiredSubjectIds);

            if (Schema::hasTable('subjects')) {
                $query->join('subjects', 'subjects.id', '=', 'grade_level_subjects.subject_id')
                    ->where('subjects.active', true)
                    ->where('subjects.row_status', 'current');
            }

            return $query
                ->select('grade_level_id')
                ->groupBy('grade_level_id')
                ->havingRaw('COUNT(DISTINCT subject_id) = ?', [count($requiredSubjectIds)])
                ->pluck('grade_level_id')
                ->map(fn ($gradeLevelId): int => (int) $gradeLevelId)
                ->all();
        });
    }

    protected function transferReadySchoolAliases(): array
    {
        return once(fn (): array => array_map('strtolower', [
            SchoolSystemOptions::IB,
            'IB System',
            'International Baccalaureate',
            SchoolSystemOptions::AMERICAN,
            'American System',
            SchoolSystemOptions::BRITISH,
            'British System',
            SchoolSystemOptions::OTHER,
            'Other System',
        ]));
    }

    protected function rawInExpression(string $expression, array $values): string
    {
        if ($values === []) {
            return '1 = 0';
        }

        return $expression.' IN ('.implode(', ', array_fill(0, count($values), '?')).')';
    }

    protected function sortChildrenForDisplay(Collection $children, Booking $booking): Collection
    {
        return $children
            ->sort(fn ($first, $second) => $this->childSortKey($first, $booking) <=> $this->childSortKey($second, $booking))
            ->values();
    }

    protected function childSortKey(mixed $child, Booking $booking): string
    {
        $workflowStatus = $this->childWorkflowStatus($child);
        $state = $this->derivedQueueStateForChild($child);

        return match ($state) {
            'followup_due' => $this->compositeSortKey(10, $this->childFollowupDate($child, $booking), $this->childSortableId($child, $booking)),
            'confirmed_upcoming' => $this->compositeSortKey(20, $this->childScheduledDateTime($child, $booking), $this->childSortableId($child, $booking)),
            'questionnaire_waiting' => $this->compositeSortKey(
                $workflowStatus === 'questionnaire_sent' ? 30 : 40,
                $this->childUpdatedAt($child, $booking),
                $this->childSortableId($child, $booking)
            ),
            'pending_active' => $this->compositeSortKey(50, $this->childUpdatedAt($child, $booking), $this->childSortableId($child, $booking)),
            'potential_later' => $this->compositeSortKey(55, $this->childFollowupDate($child, $booking), $this->childSortableId($child, $booking)),
            'followup_future' => $this->compositeSortKey(60, $this->childFollowupDate($child, $booking), $this->childSortableId($child, $booking)),
            'completed' => $this->compositeSortKey(70, $this->childUpdatedAt($child, $booking), $this->childSortableId($child, $booking)),
            'cancelled' => $this->compositeSortKey(80, $this->childUpdatedAt($child, $booking), $this->childSortableId($child, $booking)),
            default => $this->compositeSortKey(65, $this->childUpdatedAt($child, $booking), $this->childSortableId($child, $booking)),
        };
    }

    protected function compositeSortKey(int $rank, mixed $dateValue, int $sortableId): string
    {
        return sprintf('%03d|%s|%010d', $rank, $this->sortableDateKey($dateValue), $sortableId);
    }

    protected function sortableDateKey(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('YmdHis');
        }

        if (blank($value)) {
            return '99991231235959';
        }

        try {
            return Carbon::parse((string) $value)->format('YmdHis');
        } catch (\Throwable) {
            return '99991231235959';
        }
    }

    protected function childWorkflowStatus(mixed $child): string
    {
        $status = $child instanceof BookingChild
            ? ($child->workflow_status ?: $child->consultation_status)
            : data_get($child, 'workflow_status', data_get($child, 'consultation_status'));

        return match ($status) {
            'confirmed' => 'confirmed',
            'cancelled' => 'cancelled',
            'questionnaire_sent' => 'questionnaire_sent',
            'questionnaire_answer_received' => 'questionnaire_answer_received',
            'followup', 'followup_required' => 'followup_required',
            default => 'pending',
        };
    }

    protected function childEvaluationOutcome(mixed $child): string
    {
        return $child instanceof BookingChild
            ? ($child->evaluation_outcome ?: ($child->evaluation_status ?: 'undecided'))
            : (data_get($child, 'evaluation_outcome', data_get($child, 'evaluation_status')) ?: 'undecided');
    }

    protected function derivedQueueStateForChild(mixed $child): string
    {
        if ($this->childEvaluationOutcome($child) === 'PL') {
            return 'potential_later';
        }

        $meetingDisposition = $child instanceof BookingChild ? $child->meeting_disposition : data_get($child, 'meeting_disposition');

        if ($meetingDisposition === 'completed') {
            return 'completed';
        }

        if ($meetingDisposition === 'cancelled') {
            return 'cancelled';
        }

        $workflowStatus = $this->childWorkflowStatus($child);

        if ($workflowStatus === 'cancelled') {
            return 'cancelled';
        }

        if ($workflowStatus === 'followup_required') {
            $followupDate = $this->parseDateValue($child instanceof BookingChild ? $child->followup_date : data_get($child, 'followup_date'));

            if ($followupDate && $followupDate->toDateString() <= now()->toDateString()) {
                return 'followup_due';
            }

            return 'followup_future';
        }

        if ($workflowStatus === 'confirmed') {
            return 'confirmed_upcoming';
        }

        if (in_array($workflowStatus, ['questionnaire_sent', 'questionnaire_answer_received'], true)) {
            return 'questionnaire_waiting';
        }

        return 'pending_active';
    }

    protected function childFollowupDate(mixed $child, Booking $booking): mixed
    {
        if ($child instanceof BookingChild) {
            return $child->followup_date ?: $booking->follow_up_date;
        }

        return data_get($child, 'followup_date', $booking->follow_up_date);
    }

    protected function childScheduledDateTime(mixed $child, Booking $booking): ?Carbon
    {
        $date = $child instanceof BookingChild
            ? ($child->scheduled_date ?: $booking->consultation_date)
            : data_get($child, 'scheduled_date', $booking->consultation_date);
        $time = $child instanceof BookingChild
            ? ($child->scheduled_time ?: $booking->consultation_time)
            : data_get($child, 'scheduled_time', $booking->consultation_time);

        if (blank($date) && blank($time)) {
            return null;
        }

        $datePart = $this->parseDateValue($date)?->format('Y-m-d') ?? now()->format('Y-m-d');
        $timePart = $this->normalizeTimeValue($time) ?? '23:59:59';

        try {
            return Carbon::parse($datePart.' '.$timePart);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function childUpdatedAt(mixed $child, Booking $booking): mixed
    {
        return $child instanceof BookingChild
            ? ($child->updated_at ?: $child->created_at ?: $booking->updated_at ?: $booking->created_at)
            : ($booking->updated_at ?: $booking->created_at);
    }

    protected function childSortableId(mixed $child, Booking $booking): int
    {
        return (int) ($child instanceof BookingChild ? $child->id : ($booking->id * 1000));
    }

    protected function parseDateValue(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function normalizeTimeValue(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('H:i:s');
        }

        $normalized = trim((string) $value);

        if (preg_match('/^\d{1,2}\.\d{2}$/', $normalized)) {
            $normalized = str_replace('.', ':', $normalized);
        }

        try {
            return Carbon::parse($normalized)->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $bookings = $this->orderedBookingsQuery()->get();
        $filename = 'bookings-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($bookings): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'Booking Ref', 'Parent Name', 'Parent Email', 'Parent Phone',
                'Child Name', 'Child Age', 'Child Grade', 'School System', 'Current School',
                'Service Interests', 'Workflow Status', 'Meeting Disposition',
                'Evaluation Outcome', 'Transfer Status', 'Consultation Type',
                'Scheduled Date', 'Scheduled Time', 'Follow-up Date', 'Notes',
                'Last Updated', 'Updated By',
            ]);

            foreach ($bookings as $booking) {
                $children = $this->filteredDisplayChildren(
                    $booking,
                    $this->sortChildrenForDisplay($booking->intakeChildren()->values(), $booking)
                );

                foreach ($children as $child) {
                    $rawInterests = data_get($child, 'service_interests', []);
                    $serviceStr = is_array($rawInterests)
                        ? implode(', ', $rawInterests)
                        : (string) $rawInterests;

                    fputcsv($output, [
                        $booking->booking_reference ?? '',
                        $booking->parent_name ?? '',
                        $booking->parent_email ?? '',
                        $booking->parent_phone ?? '',
                        data_get($child, 'child_name', ''),
                        data_get($child, 'child_age', ''),
                        data_get($child, 'child_grade', ''),
                        data_get($child, 'school_system', ''),
                        data_get($child, 'current_school', ''),
                        $serviceStr,
                        data_get($child, 'workflow_status', ''),
                        data_get($child, 'meeting_disposition') ?? '',
                        data_get($child, 'evaluation_outcome', ''),
                        data_get($child, 'transfer_status', ''),
                        data_get($child, 'consultation_type', ''),
                        data_get($child, 'scheduled_date') ?? '',
                        data_get($child, 'scheduled_time') ?? '',
                        data_get($child, 'followup_date') ?? '',
                        data_get($child, 'notes') ?? '',
                        $child instanceof BookingChild ? ($child->updated_at?->format('Y-m-d H:i') ?? '') : '',
                        $child instanceof BookingChild ? ($child->updatedByUser?->name ?? '') : '',
                    ]);
                }
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function queueViewMeta(): array
    {
        return match ($this->filterQueueState) {
            'pending_active' => [
                'title' => 'Pending Queue',
                'description' => 'Children still waiting on their first operational step.',
            ],
            'confirmed_upcoming' => [
                'title' => 'Confirmed Upcoming',
                'description' => 'Confirmed consultations that still need active follow-through.',
            ],
            'followup_due' => [
                'title' => 'Follow-Up Due / Overdue',
                'description' => 'Only follow-up items due today or already overdue.',
            ],
            'potential_later' => [
                'title' => 'Potential Later (PL)',
                'description' => 'Children intentionally parked for later follow-up instead of the live intake backlog.',
            ],
            'questionnaire_waiting' => [
                'title' => 'Questionnaire Waiting',
                'description' => 'Reserved questionnaire workflow states kept visible for awareness.',
            ],
            'transfer_ready' => [
                'title' => 'Fit / Ready To Transfer',
                'description' => 'Children that satisfy the transfer invariant right now.',
            ],
            'completed' => [
                'title' => 'Completed Consultations',
                'description' => 'Terminal completed outcomes separated from the active backlog.',
            ],
            'cancelled' => [
                'title' => 'Cancelled Consultations',
                'description' => 'Terminal cancelled outcomes kept visible without polluting active work.',
            ],
            default => [
                'title' => 'Active Booking Queue',
                'description' => 'Urgency-sorted by operational priority, while Potential Later still has its own quick filter.',
            ],
        };
    }

    protected function pendingIntakeReviewCount(): int
    {
        if (! Schema::hasTable('booking_intake_review')) {
            return 0;
        }

        return BookingIntakeReview::query()
            ->where('status', 'pending_review')
            ->count();
    }
}
