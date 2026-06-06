<div class="row g-6">
  <div class="col-12">
    <div
      class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-6">
      <div>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <h4 class="mb-0">Booking Admin</h4>
          <details class="intake-info intake-info--inline">
            <summary class="intake-info__trigger" aria-label="Open booking admin info">
              <i class="icon-base ti tabler-info-circle icon-18px"></i>
            </summary>
            <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
              Parent-grouped child queue for Sprint 3. Child workflow editing and guarded per-child transfer are both available from this queue.
            </div>
          </details>
        </div>
      </div>
      <div class="queue-page-actions queue-page-actions--with-create">
        <button
          type="button"
          class="btn btn-primary rounded-pill btn-icon"
          wire:click="$dispatch('admin-intake-form:open')"
          title="New Intake"
          aria-label="New Intake"
        >
          <i class="icon-base ti tabler-plus icon-20px"></i>
        </button>
        <a href="{{ route('admin.bookings.transferred') }}" class="btn btn-label-secondary">
          Transferred Children
        </a>
        <a href="{{ route('admin.bookings.intake-review') }}" class="btn btn-label-secondary queue-page-link">
          Intake Review
          @if ($pendingIntakeReviewCount > 0)
            <span class="queue-page-link__badge">{{ $pendingIntakeReviewCount > 99 ? '99+' : $pendingIntakeReviewCount }}</span>
          @endif
        </a>
      </div>
    </div>
  </div>

  @php
    $primaryStats = collect($stats)->where('group', 'primary')->values();
    $secondaryStats = collect($stats)->where('group', 'secondary')->values();
  @endphp

  <livewire:admin.booking.admin-intake-form />

  @if (session()->has('success'))
    <div class="col-12">
      <div class="alert alert-success alert-dismissible mb-0" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
  @endif

  @if (session()->has('warning'))
    <div class="col-12">
      <div class="alert alert-warning alert-dismissible mb-0 d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3" role="alert">
        <div>{{ session('warning') }}</div>
        @if (session()->has('intake_review_id'))
          <a href="{{ route('admin.bookings.intake-review') }}" class="btn btn-sm btn-outline-warning">
            Open Intake Review
          </a>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
  @endif

  @foreach ($primaryStats as $stat)
    <div class="col-12 col-sm-6 col-xl">
      @php
        $statIcon = match ($stat['filterValue']) {
            'pending_active' => 'tabler-loader-2',
            'confirmed_upcoming' => 'tabler-calendar-check',
            'followup_due' => 'tabler-clock-exclamation',
            'potential_later' => 'tabler-clock-hour-4',
            'questionnaire_waiting' => 'tabler-message-question',
            'transfer_ready' => 'tabler-shield-check',
            default => 'tabler-chart-bar',
        };
      @endphp
      <button type="button"
        class="card card-border-shadow-{{ $stat['tone'] }} h-100 w-100 text-start {{ $stat['active'] ? 'border border-2 border-' . $stat['tone'] . ' shadow-lg' : 'border-0' }}"
        wire:click="filterBy('{{ $stat['field'] }}', '{{ $stat['filterValue'] }}')">
        <div class="card-body">
          <div class="d-flex align-items-center mb-1">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-{{ $stat['tone'] }}">
                <i class="icon-base ti {{ $statIcon }} icon-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ number_format($stat['value']) }}</h4>
          </div>

          <div class="d-flex flex-wrap align-items-center gap-2">
            <p class="mb-0">{{ $stat['label'] }}</p>
            <details class="intake-info intake-info--inline">
              <summary class="intake-info__trigger" aria-label="Open {{ $stat['label'] }} info">
                <i class="icon-base ti tabler-info-circle icon-18px"></i>
              </summary>
              <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                {{ $stat['hint'] }}
              </div>
            </details>
          </div>

          @if ($stat['active'])
            <span class="badge bg-{{ $stat['tone'] }} mt-2">Active filter</span>
          @endif
        </div>
      </button>
    </div>
  @endforeach

  <div class="col-12">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
      <div>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <h6 class="mb-0">Closed Outcomes</h6>
          <details class="intake-info intake-info--inline">
            <summary class="intake-info__trigger" aria-label="Open closed outcomes info">
              <i class="icon-base ti tabler-info-circle icon-18px"></i>
            </summary>
            <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
              Completed and cancelled work stay visible in secondary views without inflating the active backlog cards.
            </div>
          </details>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2">
        @foreach ($secondaryStats as $stat)
          <button type="button"
            class="btn {{ $stat['active'] ? 'btn-' . $stat['tone'] : 'btn-label-' . $stat['tone'] }}"
            wire:click="filterBy('{{ $stat['field'] }}', '{{ $stat['filterValue'] }}')">
            {{ $stat['label'] }} ({{ number_format($stat['value']) }})
          </button>
        @endforeach
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-header border-bottom">
        <div class="row m-3 my-0 justify-content-between g-3">
          <div class="col-12 col-lg">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <h5 class="card-title mb-0">{{ $queueView['title'] }}</h5>
              <details class="intake-info intake-info--inline">
                <summary class="intake-info__trigger" aria-label="Open queue view info">
                  <i class="icon-base ti tabler-info-circle icon-18px"></i>
                </summary>
                <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                  {{ $queueView['description'] }}
                </div>
              </details>
            </div>
          </div>
          <div class="col-12 col-lg-auto">
            <div class="booking-toolbar d-flex flex-column flex-sm-row align-items-stretch gap-2">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="icon-base ti tabler-search icon-18px"></i></span>
                <input type="search" class="form-control" placeholder="Search parent name, email, or phone"
                  wire:model.live.debounce.300ms="search">
              </div>

              <select class="form-select w-auto" wire:model.live="perPage">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
              </select>

              @if ($search !== '' || $filterQueueState !== 'all' || $filterEvaluation !== 'all' || $perPage !== 10)
                <button type="button" class="btn btn-label-secondary" wire:click="resetListFilters">
                  Reset
                </button>
              @endif

              <button type="button" class="btn btn-label-secondary d-inline-flex align-items-center gap-1 booking-toolbar__export"
                wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv"
                title="Export visible filtered results to CSV">
                <span wire:loading.remove wire:target="exportCsv"><i class="icon-base ti tabler-download icon-18px me-1"></i>Export CSV</span>
                <span wire:loading wire:target="exportCsv">
                  <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                  Exporting...
                </span>
              </button>
            </div>
          </div>
        </div>

        @if ($filterQueueState !== 'all' || $filterEvaluation !== 'all')
          <div class="d-flex flex-wrap gap-2 mt-3">
            @if ($filterQueueState !== 'all')
              <span class="badge bg-label-primary">Queue view filtered</span>
            @endif
            @if ($filterEvaluation !== 'all')
              <span class="badge bg-label-info">Evaluation: {{ $filterEvaluation }}</span>
            @endif
          </div>
        @endif

        <div class="d-flex flex-wrap align-items-center gap-3 mt-3 small text-body-secondary">
          <span class="d-inline-flex align-items-center gap-2">
            <span class="fw-semibold text-heading">Email legend:</span>
            <details class="intake-info intake-info--inline">
              <summary class="intake-info__trigger" aria-label="Open email legend info">
                <i class="icon-base ti tabler-info-circle icon-18px"></i>
              </summary>
              <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                Chip color shows delivery state.
              </div>
            </details>
          </span>
          <span class="booking-email-legend-item">
            <span class="booking-email-chip booking-email-chip--secondary">PC</span>
            <span>Parent</span>
          </span>
          <span class="booking-email-legend-item">
            <span class="booking-email-chip booking-email-chip--secondary">AC</span>
            <span>Admin</span>
          </span>
          <span class="booking-email-legend-item">
            <span class="booking-email-chip booking-email-chip--secondary">QP</span>
            <span>Questionnaire</span>
          </span>
          <span class="booking-email-legend-item">
            <span class="booking-email-chip booking-email-chip--secondary">TW</span>
            <span>Welcome</span>
          </span>
          <span class="booking-email-legend-item">
            <span class="booking-email-chip booking-email-chip--secondary">TA</span>
            <span>Transfer</span>
          </span>
        </div>
      </div>

      <div class="card-body p-0">
        @if ($bookings->isEmpty())
          <div class="p-5 text-center">
            <div
              class="mx-auto mb-3 d-inline-flex align-items-center justify-content-center rounded-circle bg-label-secondary"
              style="width:64px;height:64px;">
              <i class="bx bx-search-alt fs-2"></i>
            </div>
            <h5 class="mb-2">No matching results</h5>
            <p class="text-body-secondary mb-3">No bookings matched the current search or filters.</p>
            <button type="button" class="btn btn-label-secondary" wire:click="resetListFilters">
              Clear Search
            </button>
          </div>
        @else
          <div class="table-responsive d-none d-xl-block">
            <table class="table align-middle mb-0">
              <thead class="table-dark">
                <tr>
                  <th style="min-width: 240px;">Child</th>
                  <th style="min-width: 280px;">Milestones</th>
                  <th style="min-width: 220px;">Consultation</th>
                  <th style="min-width: 220px;">Services</th>
                  <th style="min-width: 180px;" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($bookings as $booking)
                  @php
                    $parentWords = collect(preg_split('/\s+/', trim((string) ($booking->parent_name ?: ''))))
                        ->filter()
                        ->values();
                    $parentInitials = strtoupper(
                        ($parentWords->isNotEmpty() ? mb_substr((string) $parentWords->first(), 0, 1) : 'P') .
                            ($parentWords->count() > 1 ? mb_substr((string) $parentWords->last(), 0, 1) : ''),
                    );
                    $listReturnUrl = route(
                        'admin.bookings.livewire',
                        array_filter(
                            [
                                'search' => $search !== '' ? $search : null,
                                'filterQueueState' => $filterQueueState !== 'all' ? $filterQueueState : null,
                                'filterEvaluation' => $filterEvaluation !== 'all' ? $filterEvaluation : null,
                                'perPage' => $perPage !== 10 ? $perPage : null,
                                'page' => $bookings->currentPage() > 1 ? $bookings->currentPage() : null,
                            ],
                            fn($value) => $value !== null && $value !== '',
                        ),
                    );
                    $parentEditUrl = route('admin.bookings.parent.edit', [
                        'booking' => $booking->id,
                        'return' => $listReturnUrl,
                    ]);
                  @endphp
                  <tr class="bg-lighter" wire:key="booking-{{ $booking->id }}-header">
                    <td colspan="5" class="py-3 px-4">
                      <div
                        class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                        <div class="d-flex align-items-center">
                          <div class="avatar avatar-sm me-3">
                            <span class="avatar-initial rounded-circle bg-label-primary">{{ $parentInitials }}</span>
                          </div>
                          <div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                              <h6 class="mb-0">{{ $booking->parent_name ?: 'Unnamed parent' }}</h6>
                              @if ($booking->shouldShowExistingFamilyBadge)
                                @if ($booking->familyContextLink)
                                  <a href="{{ $booking->familyContextLink['url'] }}"
                                     class="badge bg-label-info text-decoration-none"
                                     title="{{ $booking->familyContextLink['label'] }}">
                                    Existing family <i class="ti tabler-external-link" style="font-size:0.75em;"></i>
                                  </a>
                                @endif
                              @endif
                              @if ($booking->hasSiblingContext)
                                @if ($booking->siblingContextLink)
                                  <a href="{{ $booking->siblingContextLink['url'] }}"
                                     class="badge bg-label-warning text-decoration-none"
                                     title="{{ $booking->siblingContextLink['label'] }}">
                                    Sibling intake <i class="ti tabler-external-link" style="font-size:0.75em;"></i>
                                  </a>
                                @else
                                  <span class="badge bg-label-warning">Sibling intake</span>
                                @endif
                              @endif
                              @if ($booking->showsNoChildrenState)
                                <span class="badge bg-label-danger">No child rows</span>
                              @endif
                            </div>
                            <div class="small text-body-secondary">
                              {{ $booking->parent_email ?: '-' }} | {{ $booking->parent_phone ?: '-' }} | Ref:
                              {{ $booking->booking_reference ?: '-' }}
                            </div>
                          </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                          <span class="badge bg-label-primary">{{ $booking->displayChildCount }} visible child
                            row{{ $booking->displayChildCount === 1 ? '' : 's' }}</span>
                          <a href="{{ $parentEditUrl }}" class="btn btn-text-secondary rounded-pill btn-icon"
                            aria-label="Edit parent {{ $booking->parent_name ?: 'booking' }}">
                            <i class="icon-base ti tabler-edit icon-22px"></i>
                          </a>
                        </div>
                      </div>
                    </td>
                  </tr>

                  @if ($booking->showsNoChildrenState)
                    <tr class="bg-lighter" wire:key="booking-{{ $booking->id }}-no-children">
                      <td colspan="5" class="px-4 py-4">
                        <div class="d-flex align-items-start gap-3">
                          <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                              <i class="icon-base ti tabler-alert-triangle icon-28px"></i>
                            </span>
                          </div>
                          <div>
                            <h6 class="mb-1">No child rows</h6>
                            <p class="mb-3 text-body-secondary">This booking has parent-level data, but child-level
                              work cannot continue until the child structure is fixed.</p>
                            <div class="d-flex flex-wrap gap-2">
                              <a href="{{ $parentEditUrl }}" class="btn btn-label-primary btn-sm">
                                Edit Parent
                              </a>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                  @else
                    @foreach ($booking->displayChildren as $child)
                      @php
                        $focusChild = data_get($child, 'id');
                        $transferBlockedReason = $focusChild
                            ? $this->transferBlockedReasonFor($child, $booking)
                            : 'Transfer is only available after this booking is normalized into a real child row.';
                        $displayTransferBlockedReason =
                            $transferBlockedReason === 'Transfer is only available when Evaluation Outcome is Fit.'
                                ? null
                                : $transferBlockedReason;
                        $unresolvedTransferServices = $focusChild
                            ? $this->unresolvedTransferServicesFor($child, $booking)
                            : [];
                        $childEmailStatuses = $focusChild ? $emailStatuses->get($focusChild, collect()) : collect();
                        $childWireKey = $focusChild
                            ? 'booking-' . $booking->id . '-child-' . $focusChild
                            : 'booking-' . $booking->id . '-legacy-child-' . $loop->index;
                        $childName = (string) data_get($child, 'child_name', '');
                        $childWords = collect(preg_split('/\s+/', trim($childName)))
                            ->filter()
                            ->values();
                        $childInitials = strtoupper(
                            ($childWords->isNotEmpty() ? mb_substr((string) $childWords->first(), 0, 1) : 'C') .
                                ($childWords->count() > 1 ? mb_substr((string) $childWords->last(), 0, 1) : ''),
                        );
                        $childSchoolSystem = data_get($child, 'school_system', $booking->school_system) ?: '-';
                        $childGradeLabel = $this->gradeLabel(data_get($child, 'child_grade'), $gradeTitles);
                        $usesLaunchCompatibilityDefaults = $childSchoolSystem === 'Other' && $childGradeLabel === 'Beginner';
                        $childMeta = '-';
                        if (! $usesLaunchCompatibilityDefaults) {
                            $childMeta = collect([
                                $childSchoolSystem !== '-' ? $childSchoolSystem : null,
                                $childGradeLabel !== '-' ? $childGradeLabel : null,
                            ])
                                ->filter()
                                ->implode(' | ') ?:
                            '-';
                        }
                        $consultationTypeLabel = $this->consultationTypeLabel(
                            data_get($child, 'consultation_type', $booking->consultation_type),
                        );
                        $consultationDate = $this->formatDate(
                            data_get($child, 'scheduled_date', $booking->consultation_date),
                        );
                        $consultationTime = $this->formatTime(
                            data_get($child, 'scheduled_time', $booking->consultation_time),
                        );
                        $consultationLines = collect([
                            $consultationTypeLabel !== 'Undecided' ? $consultationTypeLabel : null,
                            $consultationDate !== '-' ? $consultationDate : null,
                            $consultationTime !== '-' ? $consultationTime : null,
                        ])
                            ->filter()
                            ->values();
                        $serviceSummary = $this->serviceInterestSummary($child, $booking);
                        $serviceLabels = collect(explode(',', $serviceSummary))
                            ->map(fn($service) => trim($service))
                            ->filter()
                            ->values();
                        $workflowStatus = data_get($child, 'workflow_status', data_get($child, 'consultation_status'));
                        $meetingDisposition = data_get($child, 'meeting_disposition');
                        $evaluationStatus = data_get(
                            $child,
                            'evaluation_outcome',
                            data_get($child, 'evaluation_status'),
                        );
                        $transferStatus = data_get($child, 'transfer_status');
                        $milestone = match (true) {
                            $transferStatus === 'transferred' => [
                                'label' => 'Transferred',
                                'class' => 'bg-label-primary',
                                'icon' => 'tabler-user-check',
                            ],
                            $transferBlockedReason === null => [
                                'label' => 'Ready to transfer',
                                'class' => 'bg-label-success',
                                'icon' => 'tabler-transfer',
                            ],
                            $unresolvedTransferServices !== [] => [
                                'label' => 'Fix service before transfer',
                                'class' => 'bg-label-warning',
                                'icon' => 'tabler-alert-triangle',
                            ],
                            $evaluationStatus === 'unfit' => [
                                'label' => 'Evaluation unfit',
                                'class' => 'bg-label-danger',
                                'icon' => 'tabler-school-off',
                            ],
                            $evaluationStatus === 'PL' => [
                                'label' => 'Potential later',
                                'class' => 'bg-label-warning',
                                'icon' => 'tabler-clock-up',
                            ],
                            $evaluationStatus === 'fit' => [
                                'label' => 'Evaluation fit',
                                'class' => 'bg-label-success',
                                'icon' => 'tabler-school',
                            ],
                            $meetingDisposition === 'completed' => [
                                'label' => 'Meeting completed',
                                'class' => 'bg-label-success',
                                'icon' => 'tabler-circle-check',
                            ],
                            $meetingDisposition === 'cancelled' => [
                                'label' => 'Meeting cancelled',
                                'class' => 'bg-label-danger',
                                'icon' => 'tabler-circle-x',
                            ],
                            $meetingDisposition === 'no_meeting_required' => [
                                'label' => 'No meeting required',
                                'class' => 'bg-label-info',
                                'icon' => 'tabler-video-off',
                            ],
                            $workflowStatus === 'followup_required' => [
                                'label' => 'Follow-up required',
                                'class' => 'bg-label-warning',
                                'icon' => 'tabler-clock-up',
                            ],
                            $workflowStatus === 'confirmed' => [
                                'label' => 'Meeting confirmed',
                                'class' => 'bg-label-success',
                                'icon' => 'tabler-calendar-check',
                            ],
                            $workflowStatus === 'questionnaire_sent' => [
                                'label' => 'Questionnaire sent',
                                'class' => 'bg-label-info',
                                'icon' => 'tabler-message-question',
                            ],
                            $workflowStatus === 'questionnaire_answer_received' => [
                                'label' => 'Questionnaire answered',
                                'class' => 'bg-label-primary',
                                'icon' => 'tabler-message-check',
                            ],
                            $workflowStatus === 'cancelled' => [
                                'label' => 'Cancelled / closed',
                                'class' => 'bg-label-danger',
                                'icon' => 'tabler-circle-x',
                            ],
                            default => [
                                'label' => 'Pending',
                                'class' => 'bg-label-secondary',
                                'icon' => 'tabler-loader-2',
                            ],
                        };
                        $listReturnUrl = route(
                            'admin.bookings.livewire',
                            array_filter(
                                [
                                    'search' => $search !== '' ? $search : null,
                                    'filterQueueState' => $filterQueueState !== 'all' ? $filterQueueState : null,
                                    'filterEvaluation' => $filterEvaluation !== 'all' ? $filterEvaluation : null,
                                    'perPage' => $perPage !== 10 ? $perPage : null,
                                    'page' => $bookings->currentPage() > 1 ? $bookings->currentPage() : null,
                                ],
                                fn($value) => $value !== null && $value !== '',
                            ),
                        );
                      @endphp
                      <tr wire:key="{{ $childWireKey }}">
                        <td>
                          <div class="d-flex justify-content-start align-items-center">
                            <div class="avatar-wrapper">
                              <div class="avatar avatar-sm me-4">
                                <span
                                  class="avatar-initial rounded-circle bg-label-primary">{{ $childInitials }}</span>
                              </div>
                            </div>
                            <div class="d-flex flex-column">
                              <span
                                class="text-heading text-truncate fw-medium">{{ data_get($child, 'child_name', 'Unnamed child') }}</span>
                              <small class="text-body-secondary">{{ $childMeta }}</small>
                              @if ($focusChild && $child->updated_by)
                                <small class="text-body-secondary"
                                  title="{{ $child->updated_at?->format('d M Y H:i') }}">Updated {{ $child->updated_at?->diffForHumans() }} by {{ $child->updatedByUser?->name ?? 'unknown' }}</small>
                              @endif
                            </div>
                          </div>
                        </td>
                        <td>
                          <span
                            class="badge rounded-pill {{ $milestone['class'] }} d-inline-flex align-items-center gap-1">
                            <i class="icon-base ti {{ $milestone['icon'] }} icon-16px"></i>
                            {{ $milestone['label'] }}
                          </span>

                          @if (filled(data_get($child, 'meeting_disposition_reason')))
                            <div class="small text-body-secondary">
                              <strong>Reason:</strong> {{ data_get($child, 'meeting_disposition_reason') }}
                            </div>
                          @endif

                          @if ($focusChild && $displayTransferBlockedReason && $unresolvedTransferServices === [])
                            <div class="small text-body-secondary mt-2">
                              <strong>Transfer:</strong> {{ $displayTransferBlockedReason }}
                            </div>
                          @endif

                          @if ($focusChild)
                            <div class="d-flex flex-wrap gap-2 mt-3" aria-label="Email status indicators">
                              @foreach ($childEmailStatuses as $emailType => $emailStatus)
                                @php
                                  $emailBadge = $this->emailStatusBadge($emailStatus, $emailType);
                                  $emailState = $this->isRetiredEmailType($emailType) ? 'retired' : ($emailStatus?->status ?? 'not_sent');
                                  $emailTone = match ($emailState) {
                                      'failed' => 'danger',
                                      'sent', 'resent' => 'success',
                                      'queued' => 'info',
                                      default => 'secondary',
                                  };
                                  $emailCode = match ($emailType) {
                                      'confirmation_parent' => 'PC',
                                      'confirmation_admin' => 'AC',
                                      'questionnaire_parent' => 'QP',
                                      'transfer_welcome' => 'TW',
                                      'transfer_admin' => 'TA',
                                      default => strtoupper(substr($this->emailTypeShortLabel($emailType), 0, 2)),
                                  };
                                @endphp
                                <span class="booking-email-code booking-email-code--{{ $emailTone }}"
                                  aria-label="{{ $this->emailTypeShortLabel($emailType) }}: {{ $emailBadge['label'] }}"
                                  title="{{ $this->emailTypeShortLabel($emailType) }}: {{ $emailBadge['label'] }}">
                                  <span class="booking-email-code__dot booking-email-code__dot--{{ $emailTone }}"></span>
                                  <span>{{ $emailCode }}</span>
                                </span>
                              @endforeach
                            </div>
                          @else
                            <div class="small text-body-secondary">
                              Email tracking starts after this booking is normalized into a real child row.
                            </div>
                          @endif
                        </td>
                        <td>
                          <div class="small d-flex flex-column gap-1 text-body-secondary">
                            @forelse ($consultationLines as $line)
                              <span @class(['text-heading' => $loop->first])>{{ $line }}</span>
                            @empty
                              <span class="text-body-secondary">Undecided</span>
                            @endforelse
                          </div>
                        </td>
                        <td>
                          <div class="d-flex flex-wrap gap-2">
                            @foreach ($serviceLabels as $serviceLabel)
                              <span
                                class="badge bg-label-{{ $serviceSummary === 'Need Guidance' ? 'secondary' : 'primary' }}">{{ $serviceLabel }}</span>
                            @endforeach
                          </div>
                        </td>
                        <td class="text-end">
                          <div class="d-flex align-items-center justify-content-end gap-1">
                            @if ($focusChild)
                              <a href="{{ route('admin.bookings.children.edit', ['bookingChild' => $focusChild, 'return' => $listReturnUrl]) }}"
                                class="btn btn-text-secondary rounded-pill btn-icon"
                                aria-label="View and edit child workflow for {{ data_get($child, 'child_name', 'this child') }}"
                                title="View and edit child workflow">
                                <i class="icon-base ti tabler-eye icon-22px"></i>
                              </a>
                            @else
                              <button type="button" class="btn btn-text-secondary rounded-pill btn-icon" disabled
                                aria-label="Child row required before workflow details are available" title="Child row required">
                                <i class="icon-base ti tabler-lock icon-22px"></i>
                              </button>
                            @endif
                            @if ($focusChild)
                              @if ($transferBlockedReason === null)
                                <a href="{{ route('admin.bookings.children.edit', ['bookingChild' => $focusChild, 'return' => $listReturnUrl, 'openTransfer' => 1]) }}"
                                  class="btn btn-label-success rounded-pill btn-icon"
                                  aria-label="Transfer {{ data_get($child, 'child_name', 'this child') }}"
                                  title="Transfer {{ data_get($child, 'child_name', 'this child') }}">
                                  <i class="icon-base ti tabler-transfer icon-22px"></i>
                                </a>
                              @elseif ($unresolvedTransferServices !== [])
                                <a href="{{ route('admin.bookings.children.edit', ['bookingChild' => $focusChild, 'return' => $listReturnUrl]) }}"
                                  class="btn btn-label-warning rounded-pill btn-icon"
                                  aria-label="Fix service before transfer for {{ data_get($child, 'child_name', 'this child') }}: {{ implode(', ', $unresolvedTransferServices) }}"
                                  title="Fix service before transfer">
                                  <i class="icon-base ti tabler-alert-triangle icon-22px"></i>
                                </a>
                              @else
                                <button type="button" class="btn btn-label-secondary rounded-pill btn-icon" disabled
                                  aria-label="Transfer locked for {{ data_get($child, 'child_name', 'this child') }}: {{ $transferBlockedReason }}"
                                  title="Transfer locked">
                                  <i class="icon-base ti tabler-lock icon-22px"></i>
                                </button>
                              @endif
                            @else
                              <button type="button" class="btn btn-label-secondary rounded-pill btn-icon" disabled
                                aria-label="Transfer locked: child row is not normalized" title="Transfer locked">
                                <i class="icon-base ti tabler-lock icon-22px"></i>
                              </button>
                            @endif
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  @endif
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="d-xl-none p-3 p-md-4">
            <div class="d-flex flex-column gap-3">
              @foreach ($bookings as $booking)
                @php
                  $parentWords = collect(preg_split('/\s+/', trim((string) ($booking->parent_name ?: ''))))
                      ->filter()
                      ->values();
                  $parentInitials = strtoupper(
                      ($parentWords->isNotEmpty() ? mb_substr((string) $parentWords->first(), 0, 1) : 'P') .
                          ($parentWords->count() > 1 ? mb_substr((string) $parentWords->last(), 0, 1) : ''),
                  );
                  $listReturnUrl = route(
                      'admin.bookings.livewire',
                      array_filter(
                          [
                              'search' => $search !== '' ? $search : null,
                              'filterQueueState' => $filterQueueState !== 'all' ? $filterQueueState : null,
                              'filterEvaluation' => $filterEvaluation !== 'all' ? $filterEvaluation : null,
                              'perPage' => $perPage !== 10 ? $perPage : null,
                              'page' => $bookings->currentPage() > 1 ? $bookings->currentPage() : null,
                          ],
                          fn($value) => $value !== null && $value !== '',
                      ),
                  );
                  $parentEditUrl = route('admin.bookings.parent.edit', [
                      'booking' => $booking->id,
                      'return' => $listReturnUrl,
                  ]);
                @endphp

                <div class="border rounded-3 overflow-hidden mobile-family-card" :class="{ 'mobile-family-card--open': open }" wire:key="mobile-booking-{{ $booking->id }}" x-data="{ open: true }">
                  <div class="mobile-family-card__header p-3">
                    <div class="mobile-family-card__top d-flex flex-column flex-sm-row align-items-start justify-content-between gap-3">
                      <div class="mobile-family-card__identity d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary flex-shrink-0 mobile-family-card__toggle"
                          @click="open = ! open"
                          :aria-label="(open ? 'Collapse booking ' : 'Expand booking ') + @js($booking->parent_name ?: 'family')">
                          <i class="icon-base ti tabler-chevron-down icon-18px" :class="open ? 'tabler-chevron-down' : 'tabler-chevron-right'"></i>
                        </button>

                        <div class="avatar avatar-sm flex-shrink-0">
                          <span class="avatar-initial rounded-circle bg-label-primary">{{ $parentInitials }}</span>
                        </div>

                        <div class="mobile-family-card__meta min-w-0 flex-grow-1">
                          <div class="mobile-family-card__title-row d-flex flex-wrap align-items-center gap-2 mb-1">
                            <span class="text-heading fw-medium d-block">{{ $booking->parent_name ?: 'Unnamed parent' }}</span>
                            <span class="mobile-family-card__context-badges d-inline-flex flex-wrap align-items-center gap-2">
                              @if ($booking->shouldShowExistingFamilyBadge)
                                @if ($booking->familyContextLink)
                                  <a href="{{ $booking->familyContextLink['url'] }}"
                                     class="badge bg-label-info text-decoration-none"
                                     title="{{ $booking->familyContextLink['label'] }}">
                                    Existing family <i class="ti tabler-external-link" style="font-size:0.75em;"></i>
                                  </a>
                                @endif
                              @endif
                              @if ($booking->hasSiblingContext)
                                @if ($booking->siblingContextLink)
                                  <a href="{{ $booking->siblingContextLink['url'] }}"
                                     class="badge bg-label-warning text-decoration-none"
                                     title="{{ $booking->siblingContextLink['label'] }}">
                                    Sibling intake <i class="ti tabler-external-link" style="font-size:0.75em;"></i>
                                  </a>
                                @else
                                  <span class="badge bg-label-warning">Sibling intake</span>
                                @endif
                              @endif
                              @if ($booking->showsNoChildrenState)
                                <span class="badge bg-label-danger">No child rows</span>
                              @endif
                            </span>
                          </div>
                          <div class="small text-body-secondary text-break">{{ $booking->parent_email ?: '-' }}</div>
                          <div class="small text-body-secondary">{{ $booking->parent_phone ?: '-' }}</div>
                          <div class="small text-body-secondary">Ref: {{ $booking->booking_reference ?: '-' }}</div>
                        </div>
                      </div>

                      <div class="mobile-family-card__actions d-none d-sm-inline-flex align-items-center gap-1 flex-shrink-0">
                        <span class="badge bg-label-primary">{{ $booking->displayChildCount }} visible child row{{ $booking->displayChildCount === 1 ? '' : 's' }}</span>
                        <a href="{{ $parentEditUrl }}" class="btn btn-sm btn-icon btn-text-secondary"
                          aria-label="Edit parent {{ $booking->parent_name ?: 'booking' }}">
                          <i class="icon-base ti tabler-edit icon-20px"></i>
                        </a>
                      </div>
                    </div>

                    <div class="table-responsive mt-3">
                      <table class="table mobile-details-table mb-0">
                        <tbody>
                          <tr>
                            <td>Children:</td>
                            <td>{{ $booking->displayChildCount }} visible child row{{ $booking->displayChildCount === 1 ? '' : 's' }}</td>
                          </tr>
                          <tr>
                            <td>Context:</td>
                            <td>
                              <div class="d-flex flex-wrap gap-1">
                                @if ($booking->shouldShowExistingFamilyBadge)
                                  @if ($booking->familyContextLink)
                                    <a href="{{ $booking->familyContextLink['url'] }}"
                                       class="badge bg-label-info text-decoration-none"
                                       title="{{ $booking->familyContextLink['label'] }}">
                                      Existing family <i class="ti tabler-external-link" style="font-size:0.75em;"></i>
                                    </a>
                                  @endif
                                @endif
                                @if ($booking->hasSiblingContext)
                                  @if ($booking->siblingContextLink)
                                    <a href="{{ $booking->siblingContextLink['url'] }}"
                                       class="badge bg-label-warning text-decoration-none"
                                       title="{{ $booking->siblingContextLink['label'] }}">
                                      Sibling intake <i class="ti tabler-external-link" style="font-size:0.75em;"></i>
                                    </a>
                                  @else
                                    <span class="badge bg-label-warning">Sibling intake</span>
                                  @endif
                                @endif
                                @if ($booking->showsNoChildrenState)
                                  <span class="badge bg-label-danger">No child rows</span>
                                @endif
                                @if (!$booking->hasExistingFamilyContext && !$booking->hasSiblingContext && !$booking->showsNoChildrenState)
                                  <span class="text-body-secondary">Standard intake</span>
                                @endif
                              </div>
                            </td>
                          </tr>
                          <tr class="d-sm-none">
                            <td>Actions:</td>
                            <td>
                              <div class="d-inline-flex align-items-center gap-1 flex-wrap">
                                <a href="{{ $parentEditUrl }}" class="btn btn-sm btn-icon btn-text-secondary"
                                  aria-label="Edit parent {{ $booking->parent_name ?: 'booking' }}">
                                  <i class="icon-base ti tabler-edit icon-20px"></i>
                                </a>
                              </div>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  @if ($booking->showsNoChildrenState)
                      <div class="mobile-family-card__children border-top" x-show="open">
                        <div class="mobile-child-card p-3">
                          <div class="d-flex align-items-start gap-3">
                            <div class="avatar">
                              <span class="avatar-initial rounded bg-label-warning">
                                <i class="icon-base ti tabler-alert-triangle icon-24px"></i>
                              </span>
                            </div>
                            <div class="min-w-0">
                              <div class="text-heading fw-medium mb-1">No child rows</div>
                              <div class="small text-body-secondary mb-3">This booking has parent-level data, but child-level work cannot continue until the child structure is fixed.</div>
                              <div class="d-flex flex-wrap gap-2">
                                <a href="{{ $parentEditUrl }}" class="btn btn-label-primary btn-sm">
                                  Edit Parent
                                </a>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    @else
                      <div class="mobile-family-card__children border-top" x-show="open">
                        @foreach ($booking->displayChildren as $child)
                          @php
                            $focusChild = data_get($child, 'id');
                            $transferBlockedReason = $focusChild
                                ? $this->transferBlockedReasonFor($child, $booking)
                                : 'Transfer is only available after this booking is normalized into a real child row.';
                            $displayTransferBlockedReason =
                                $transferBlockedReason === 'Transfer is only available when Evaluation Outcome is Fit.'
                                    ? null
                                    : $transferBlockedReason;
                            $unresolvedTransferServices = $focusChild
                                ? $this->unresolvedTransferServicesFor($child, $booking)
                                : [];
                            $childEmailStatuses = $focusChild ? $emailStatuses->get($focusChild, collect()) : collect();
                            $childWireKey = $focusChild
                                ? 'mobile-booking-' . $booking->id . '-child-' . $focusChild
                                : 'mobile-booking-' . $booking->id . '-legacy-child-' . $loop->index;
                            $childName = (string) data_get($child, 'child_name', '');
                            $childWords = collect(preg_split('/\s+/', trim($childName)))
                                ->filter()
                                ->values();
                            $childInitials = strtoupper(
                                ($childWords->isNotEmpty() ? mb_substr((string) $childWords->first(), 0, 1) : 'C') .
                                    ($childWords->count() > 1 ? mb_substr((string) $childWords->last(), 0, 1) : ''),
                            );
                            $childSchoolSystem = data_get($child, 'school_system', $booking->school_system) ?: '-';
                            $childGradeLabel = $this->gradeLabel(data_get($child, 'child_grade'), $gradeTitles);
                            $usesLaunchCompatibilityDefaults = $childSchoolSystem === 'Other' && $childGradeLabel === 'Beginner';
                            $childMeta = '-';
                            if (! $usesLaunchCompatibilityDefaults) {
                                $childMeta = collect([
                                    $childSchoolSystem !== '-' ? $childSchoolSystem : null,
                                    $childGradeLabel !== '-' ? $childGradeLabel : null,
                                ])->filter()->implode(' | ') ?: '-';
                            }
                            $consultationTypeLabel = $this->consultationTypeLabel(
                                data_get($child, 'consultation_type', $booking->consultation_type),
                            );
                            $consultationDate = $this->formatDate(
                                data_get($child, 'scheduled_date', $booking->consultation_date),
                            );
                            $consultationTime = $this->formatTime(
                                data_get($child, 'scheduled_time', $booking->consultation_time),
                            );
                            $consultationLines = collect([
                                $consultationTypeLabel !== 'Undecided' ? $consultationTypeLabel : null,
                                $consultationDate !== '-' ? $consultationDate : null,
                                $consultationTime !== '-' ? $consultationTime : null,
                            ])->filter()->values();
                            $serviceSummary = $this->serviceInterestSummary($child, $booking);
                            $serviceLabels = collect(explode(',', $serviceSummary))
                                ->map(fn($service) => trim($service))
                                ->filter()
                                ->values();
                            $workflowStatus = data_get($child, 'workflow_status', data_get($child, 'consultation_status'));
                            $meetingDisposition = data_get($child, 'meeting_disposition');
                            $evaluationStatus = data_get(
                                $child,
                                'evaluation_outcome',
                                data_get($child, 'evaluation_status'),
                            );
                            $transferStatus = data_get($child, 'transfer_status');
                            $milestone = match (true) {
                                $transferStatus === 'transferred' => [
                                    'label' => 'Transferred',
                                    'class' => 'bg-label-primary',
                                    'icon' => 'tabler-user-check',
                                ],
                                $transferBlockedReason === null => [
                                    'label' => 'Ready to transfer',
                                    'class' => 'bg-label-success',
                                    'icon' => 'tabler-transfer',
                                ],
                                $unresolvedTransferServices !== [] => [
                                    'label' => 'Fix service before transfer',
                                    'class' => 'bg-label-warning',
                                    'icon' => 'tabler-alert-triangle',
                                ],
                                $evaluationStatus === 'unfit' => [
                                    'label' => 'Evaluation unfit',
                                    'class' => 'bg-label-danger',
                                    'icon' => 'tabler-school-off',
                                ],
                                $evaluationStatus === 'PL' => [
                                    'label' => 'Potential later',
                                    'class' => 'bg-label-warning',
                                    'icon' => 'tabler-clock-up',
                                ],
                                $evaluationStatus === 'fit' => [
                                    'label' => 'Evaluation fit',
                                    'class' => 'bg-label-success',
                                    'icon' => 'tabler-school',
                                ],
                                $meetingDisposition === 'completed' => [
                                    'label' => 'Meeting completed',
                                    'class' => 'bg-label-success',
                                    'icon' => 'tabler-circle-check',
                                ],
                                $meetingDisposition === 'cancelled' => [
                                    'label' => 'Meeting cancelled',
                                    'class' => 'bg-label-danger',
                                    'icon' => 'tabler-circle-x',
                                ],
                                $meetingDisposition === 'no_meeting_required' => [
                                    'label' => 'No meeting required',
                                    'class' => 'bg-label-info',
                                    'icon' => 'tabler-video-off',
                                ],
                                $workflowStatus === 'followup_required' => [
                                    'label' => 'Follow-up required',
                                    'class' => 'bg-label-warning',
                                    'icon' => 'tabler-clock-up',
                                ],
                                $workflowStatus === 'confirmed' => [
                                    'label' => 'Meeting confirmed',
                                    'class' => 'bg-label-success',
                                    'icon' => 'tabler-calendar-check',
                                ],
                                $workflowStatus === 'questionnaire_sent' => [
                                    'label' => 'Questionnaire sent',
                                    'class' => 'bg-label-info',
                                    'icon' => 'tabler-message-question',
                                ],
                                $workflowStatus === 'questionnaire_answer_received' => [
                                    'label' => 'Questionnaire answered',
                                    'class' => 'bg-label-primary',
                                    'icon' => 'tabler-message-check',
                                ],
                                $workflowStatus === 'cancelled' => [
                                    'label' => 'Cancelled / closed',
                                    'class' => 'bg-label-danger',
                                    'icon' => 'tabler-circle-x',
                                ],
                                default => [
                                    'label' => 'Pending',
                                    'class' => 'bg-label-secondary',
                                    'icon' => 'tabler-loader-2',
                                ],
                            };
                          @endphp

                          <div class="mobile-child-card p-3 {{ $loop->last ? '' : 'border-bottom' }}" wire:key="{{ $childWireKey }}">
                            <div class="mobile-child-card__top d-flex align-items-start justify-content-between gap-3 mb-3">
                              <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
                                <div class="avatar avatar-sm flex-shrink-0">
                                  <span class="avatar-initial rounded-circle bg-label-primary">{{ $childInitials }}</span>
                                </div>
                                <div class="min-w-0">
                                  <div class="text-heading fw-medium">{{ data_get($child, 'child_name', 'Unnamed child') }}</div>
                                  <div class="small text-body-secondary">{{ $childMeta }}</div>
                                  @if ($focusChild && $child->updated_by)
                                    <div class="small text-body-secondary"
                                      title="{{ $child->updated_at?->format('d M Y H:i') }}">Updated {{ $child->updated_at?->diffForHumans() }} by {{ $child->updatedByUser?->name ?? 'unknown' }}</div>
                                  @endif
                                </div>
                              </div>
                              <span class="mobile-child-card__milestone badge {{ $milestone['class'] }} d-inline-flex align-items-center gap-1 flex-shrink-0">
                                <i class="icon-base ti {{ $milestone['icon'] }} icon-14px"></i>
                                {{ $milestone['label'] }}
                              </span>
                            </div>

                            <div class="table-responsive mt-2">
                              <table class="table mobile-details-table mobile-details-table--child mb-0">
                                <tbody>
                                  @if (filled(data_get($child, 'meeting_disposition_reason')))
                                    <tr>
                                      <td>Reason:</td>
                                      <td>{{ data_get($child, 'meeting_disposition_reason') }}</td>
                                    </tr>
                                  @endif
                                  @if ($focusChild && $displayTransferBlockedReason && $unresolvedTransferServices === [])
                                    <tr>
                                      <td>Transfer:</td>
                                      <td>{{ $displayTransferBlockedReason }}</td>
                                    </tr>
                                  @endif
                                  <tr>
                                    <td>Emails:</td>
                                    <td>
                                      @if ($focusChild)
                                        <div class="d-inline-flex flex-wrap align-items-center gap-2">
                                          @foreach ($childEmailStatuses as $emailType => $emailStatus)
                                            @php
                                              $emailBadge = $this->emailStatusBadge($emailStatus, $emailType);
                                              $emailState = $this->isRetiredEmailType($emailType) ? 'retired' : ($emailStatus?->status ?? 'not_sent');
                                              $emailTone = match ($emailState) {
                                                  'failed' => 'danger',
                                                  'sent', 'resent' => 'success',
                                                  'queued' => 'info',
                                                  default => 'secondary',
                                              };
                                              $emailCode = match ($emailType) {
                                                  'confirmation_parent' => 'PC',
                                                  'confirmation_admin' => 'AC',
                                                  'questionnaire_parent' => 'QP',
                                                  'transfer_welcome' => 'TW',
                                                  'transfer_admin' => 'TA',
                                                  default => strtoupper(substr($this->emailTypeShortLabel($emailType), 0, 2)),
                                              };
                                            @endphp
                                            <span class="booking-email-code booking-email-code--{{ $emailTone }}"
                                              aria-label="{{ $this->emailTypeShortLabel($emailType) }}: {{ $emailBadge['label'] }}"
                                              title="{{ $this->emailTypeShortLabel($emailType) }}: {{ $emailBadge['label'] }}">
                                              <span class="booking-email-code__dot booking-email-code__dot--{{ $emailTone }}"></span>
                                              <span>{{ $emailCode }}</span>
                                            </span>
                                          @endforeach
                                        </div>
                                      @else
                                        <span class="text-body-secondary">Email tracking starts after this booking is normalized into a real child row.</span>
                                      @endif
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>Consultation:</td>
                                    <td>
                                      <div class="d-flex flex-column gap-1">
                                        @forelse ($consultationLines as $line)
                                          <span @class(['text-heading' => $loop->first, 'small text-body-secondary' => !$loop->first])>{{ $line }}</span>
                                        @empty
                                          <span class="text-body-secondary">Undecided</span>
                                        @endforelse
                                      </div>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>Services:</td>
                                    <td>
                                      <div class="d-inline-flex flex-wrap justify-content-center gap-1">
                                        @foreach ($serviceLabels as $serviceLabel)
                                          <span class="badge bg-label-{{ $serviceSummary === 'Need Guidance' ? 'secondary' : 'primary' }}">{{ $serviceLabel }}</span>
                                        @endforeach
                                      </div>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>Actions:</td>
                                    <td>
                                      <div class="d-inline-flex align-items-center gap-1 flex-wrap">
                                        @if ($focusChild)
                                          <a href="{{ route('admin.bookings.children.edit', ['bookingChild' => $focusChild, 'return' => $listReturnUrl]) }}"
                                            class="btn btn-sm btn-icon btn-text-secondary"
                                            aria-label="View and edit child workflow for {{ data_get($child, 'child_name', 'this child') }}">
                                            <i class="icon-base ti tabler-eye icon-20px"></i>
                                          </a>
                                        @else
                                          <button type="button" class="btn btn-sm btn-icon btn-text-secondary" disabled
                                            aria-label="Child row required before workflow details are available">
                                            <i class="icon-base ti tabler-lock icon-20px"></i>
                                          </button>
                                        @endif
                                        @if ($focusChild)
                                          @if ($transferBlockedReason === null)
                                            <a href="{{ route('admin.bookings.children.edit', ['bookingChild' => $focusChild, 'return' => $listReturnUrl, 'openTransfer' => 1]) }}"
                                              class="btn btn-sm btn-icon btn-label-success rounded-circle"
                                              aria-label="Transfer {{ data_get($child, 'child_name', 'this child') }}">
                                              <i class="icon-base ti tabler-transfer icon-20px"></i>
                                            </a>
                                          @elseif ($unresolvedTransferServices !== [])
                                            <a href="{{ route('admin.bookings.children.edit', ['bookingChild' => $focusChild, 'return' => $listReturnUrl]) }}"
                                              class="btn btn-sm btn-icon btn-label-warning rounded-circle"
                                              aria-label="Fix service before transfer for {{ data_get($child, 'child_name', 'this child') }}: {{ implode(', ', $unresolvedTransferServices) }}">
                                              <i class="icon-base ti tabler-alert-triangle icon-20px"></i>
                                            </a>
                                          @else
                                            <button type="button" class="btn btn-sm btn-icon btn-label-secondary rounded-circle" disabled
                                              aria-label="Transfer locked for {{ data_get($child, 'child_name', 'this child') }}: {{ $transferBlockedReason }}">
                                              <i class="icon-base ti tabler-lock icon-20px"></i>
                                            </button>
                                          @endif
                                        @else
                                          <button type="button" class="btn btn-sm btn-icon btn-label-secondary rounded-circle" disabled
                                            aria-label="Transfer locked: child row is not normalized">
                                            <i class="icon-base ti tabler-lock icon-20px"></i>
                                          </button>
                                        @endif
                                      </div>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    @endif
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>

      @if (!$bookings->isEmpty())
        <div
          class="card-footer d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
          <div class="small text-body-secondary">
            Showing {{ $bookings->firstItem() }}-{{ $bookings->lastItem() }} of {{ $bookings->total() }}
            booking{{ $bookings->total() === 1 ? '' : 's' }}.
          </div>
          {{ $bookings->links() }}
        </div>
      @endif
    </div>
  </div>
  @include('livewire.admin.booking.partials.shared-page-ui')
  <style>
    .booking-email-chip {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 2.2rem;
      height: 2.2rem;
      border-radius: 999px;
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      border: 1px solid transparent;
      box-shadow: 0 0.125rem 0.35rem color-mix(in srgb, var(--bs-body-color) 8%, transparent);
      flex-shrink: 0;
    }

    .booking-email-chip--secondary {
      background: color-mix(in srgb, var(--bs-secondary-bg-subtle, #f2f3f5) 70%, #fff 30%);
      border-color: color-mix(in srgb, var(--bs-secondary) 18%, transparent);
      color: var(--bs-secondary-color);
    }

    .booking-email-chip--success {
      background: color-mix(in srgb, var(--bs-success-bg-subtle, #def7e8) 70%, #fff 30%);
      border-color: color-mix(in srgb, var(--bs-success) 24%, transparent);
      color: color-mix(in srgb, var(--bs-success) 78%, #000 22%);
    }

    .booking-email-chip--info {
      background: color-mix(in srgb, var(--bs-info-bg-subtle, #d8f4fd) 72%, #fff 28%);
      border-color: color-mix(in srgb, var(--bs-info) 24%, transparent);
      color: color-mix(in srgb, var(--bs-info) 78%, #000 22%);
    }

    .booking-email-chip--danger {
      background: color-mix(in srgb, var(--bs-danger-bg-subtle, #fce1e5) 72%, #fff 28%);
      border-color: color-mix(in srgb, var(--bs-danger) 24%, transparent);
      color: color-mix(in srgb, var(--bs-danger) 78%, #000 22%);
    }

    .booking-email-code {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.35rem;
      min-width: 1.8rem;
      color: var(--bs-secondary-color);
      font-size: 0.98rem;
      font-weight: 600;
      letter-spacing: 0.01em;
      line-height: 1;
    }

    .booking-email-code__dot {
      display: inline-block;
      width: 0.56rem;
      height: 0.56rem;
      border-radius: 999px;
      flex-shrink: 0;
      border: 1px solid rgba(255, 255, 255, 0.9);
      box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.08);
      vertical-align: middle;
    }

    .booking-email-code__dot--secondary {
      background-color: #b7bfcc;
    }

    .booking-email-code__dot--success {
      background-color: #28c76f;
    }

    .booking-email-code__dot--info {
      background-color: #00cfe8;
    }

    .booking-email-code__dot--danger {
      background-color: #ea5455;
    }

    .booking-email-code--secondary {
      color: #7a8699 !important;
    }

    .booking-email-code--success {
      color: #1f9d57 !important;
    }

    .booking-email-code--info {
      color: #008ea1 !important;
    }

    .booking-email-code--danger {
      color: #d63b3c !important;
    }

    .booking-email-legend-item {
      display: inline-flex;
      align-items: center;
      gap: 0.55rem;
    }

    .mobile-family-card {
      background: var(--bs-body-bg);
      border-left-width: 3px !important;
      transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .mobile-family-card--open {
      border-left-color: var(--bs-primary) !important;
      box-shadow: 0 0.35rem 1rem color-mix(in srgb, var(--bs-primary) 7%, transparent);
    }

    .mobile-family-card__header {
      background: color-mix(in srgb, var(--bs-primary-bg-subtle, #eef5ff) 35%, #fff 65%);
      position: relative;
    }

    .mobile-family-card__actions {
      align-self: flex-end;
    }

    .booking-toolbar .input-group {
      min-width: min(22rem, 42vw);
    }

    .booking-toolbar .form-select {
      min-width: 7rem;
    }

    .booking-toolbar__export {
      justify-content: center;
      min-width: 8rem;
      white-space: nowrap;
    }

    .mobile-family-card__meta .small {
      overflow-wrap: anywhere;
    }

    .mobile-details-table {
      --bs-table-bg: transparent;
      --bs-table-striped-bg: transparent;
      --bs-table-hover-bg: transparent;
      --bs-table-border-color: color-mix(in srgb, var(--bs-border-color) 72%, transparent);
      margin-bottom: 0;
    }

    .mobile-details-table > :not(caption) > * > * {
      padding: 0.85rem 0.9rem;
      vertical-align: middle;
      background: transparent;
    }

    .mobile-details-table td:first-child {
      width: 98px;
      color: var(--bs-secondary-color);
      font-weight: 500;
      white-space: nowrap;
    }

    .mobile-details-table td:last-child {
      text-align: center;
    }

    .mobile-details-table td:last-child .d-inline-flex,
    .mobile-details-table td:last-child .d-flex,
    .mobile-details-table td:last-child .users-list {
      width: 100%;
      justify-content: center;
    }

    .mobile-details-table--child td:first-child {
      width: 92px;
    }

    @media (min-width: 768px) and (max-width: 1199.98px) {
      .mobile-family-card__top {
        align-items: center !important;
      }

      .mobile-family-card__actions {
        align-self: center;
      }
    }

    @media (max-width: 767.98px) {
      .mobile-family-card__header .btn.btn-sm.btn-icon,
      .mobile-family-card__children .btn.btn-sm.btn-icon {
        width: 2.5rem;
        height: 2.5rem;
      }

      .mobile-family-card__toggle {
        position: absolute;
        inset-block-start: 0.65rem;
        inset-inline-end: 0.65rem;
        width: 1.75rem !important;
        height: 1.75rem !important;
        min-width: 1.75rem !important;
        border-radius: 999px;
        background: color-mix(in srgb, var(--bs-primary) 8%, transparent);
        color: var(--bs-primary) !important;
        z-index: 1;
      }

      .mobile-family-card__toggle:hover,
      .mobile-family-card__toggle:focus {
        background: color-mix(in srgb, var(--bs-primary) 14%, transparent);
      }

      .mobile-family-card__toggle i {
        font-size: 0.9rem !important;
      }

      .mobile-family-card__header .mobile-family-card__top {
        padding-inline-end: 2rem;
      }

      .mobile-details-table--child td:last-child {
        text-align: center;
      }

      .mobile-details-table--child td:last-child .d-inline-flex,
      .mobile-details-table--child td:last-child .d-flex,
      .mobile-details-table--child td:last-child .users-list {
        justify-content: center;
      }

      .mobile-child-card .badge {
        white-space: normal;
        line-height: 1.35;
      }
    }

    @media (max-width: 575.98px) {
      .queue-page-actions {
        display: grid;
        gap: 0.6rem;
        grid-template-columns: 2.75rem minmax(0, 1fr);
        width: 100%;
      }

      .queue-page-actions--with-create > .btn:first-child {
        grid-row: 1 / span 2;
      }

      .queue-page-actions > .btn:not(:first-child),
      .queue-page-actions > a {
        justify-self: stretch;
        width: 100%;
      }

      .booking-toolbar {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: start;
        column-gap: 0.6rem;
        row-gap: 0.75rem !important;
      }

      .booking-toolbar .input-group {
        grid-column: 1 / -1;
        min-width: 0;
      }

      .booking-toolbar .form-select {
        width: 100% !important;
        min-width: 0;
        font-size: 0.875rem;
      }

      .booking-toolbar__export {
        min-width: 0;
        padding-inline: 0.85rem;
      }

      .mobile-family-card__actions {
        align-self: start;
        justify-content: flex-end;
      }

      .mobile-family-card__top {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: start;
      }

      .mobile-details-table > :not(caption) > * > * {
        padding: 0.7rem 0.85rem;
      }
    }

    @media (max-width: 374.98px) {
      .mobile-family-card__top {
        display: block !important;
      }

      .mobile-family-card__identity {
        align-items: flex-start !important;
      }

      .mobile-family-card__context-badges {
        display: none !important;
      }

      .mobile-family-card__actions {
        display: flex !important;
        justify-content: center;
        margin-top: 0.75rem;
      }

      .mobile-details-table td:first-child,
      .mobile-details-table--child td:first-child {
        width: 72px;
      }

      .mobile-details-table > :not(caption) > * > * {
        padding: 0.6rem 0.55rem;
      }

      .mobile-details-table--child .badge {
        font-size: 0.66rem;
        padding: 0.28rem 0.42rem;
      }

      .mobile-details-table--child .btn.btn-sm.btn-icon {
        width: 1.9rem !important;
        height: 1.9rem !important;
        min-width: 1.9rem !important;
      }

      .mobile-details-table--child .btn.btn-sm.btn-icon i {
        font-size: 0.95rem !important;
      }

      .mobile-details-table--child td:last-child .d-inline-flex,
      .mobile-details-table--child td:last-child .d-flex {
        gap: 0.3rem !important;
      }

      .mobile-details-table--child tbody tr:last-child td:last-child .d-inline-flex {
        flex-wrap: nowrap !important;
      }

      .mobile-child-card__top {
        flex-direction: column;
        align-items: flex-start !important;
      }

      .mobile-child-card__milestone {
        align-self: flex-start;
      }

      .booking-email-code {
        font-size: 0.95rem;
      }
    }

  </style>
</div>
