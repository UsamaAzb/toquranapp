<div>
  @include('livewire.admin.booking.partials.shared-page-ui')

  @once
    <style>
      .booking-child-status-panel {
        padding: 1rem;
      }

      .booking-child-status-grid {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: repeat(4, minmax(0, 1fr));
      }

      .booking-child-status-item {
        align-items: center;
        display: flex;
        gap: 0.75rem;
        min-width: 0;
      }

      .booking-child-status-icon {
        flex: 0 0 auto;
      }

      .booking-child-status-text {
        min-width: 0;
      }

      .booking-child-status-text h6,
      .booking-child-status-text small {
        overflow-wrap: normal;
        word-break: normal;
      }

      .booking-child-section-nav {
        display: grid;
        gap: 0.5rem;
        grid-template-columns: repeat(6, minmax(0, 1fr));
      }

      .booking-child-section-nav .nav-item,
      .booking-child-section-nav .nav-link {
        min-width: 0;
        width: 100%;
      }

      .booking-child-section-nav .nav-link {
        justify-content: center;
        min-height: 2.5rem;
        padding-left: 0.625rem;
        padding-right: 0.625rem;
        white-space: nowrap;
      }

      @media (max-width: 991.98px) {
        .booking-child-section-nav {
          grid-template-columns: repeat(3, minmax(0, 1fr));
        }
      }

      @media (max-width: 767.98px) {
        .booking-child-status-grid {
          grid-template-columns: repeat(2, minmax(0, 1fr));
        }
      }

      @media (max-width: 575.98px) {
        .booking-child-status-panel {
          padding: 0.875rem;
        }

        .booking-child-section-nav {
          grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .booking-child-status-item {
          gap: 0.5rem;
        }

        .booking-child-status-item .badge {
          padding: 0.5rem !important;
        }

        .booking-child-status-item .icon-lg {
          font-size: 1.125rem;
          height: 1.125rem;
          width: 1.125rem;
        }

        .booking-child-status-text h6 {
          font-size: 0.875rem;
          line-height: 1.2;
        }

        .booking-child-status-text small {
          font-size: 0.75rem;
          line-height: 1.2;
        }
      }
    </style>
  @endonce

  @php
    $workflowSummary = match ($workflowStatus) {
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled / Closed',
        'followup_required' => 'Follow-Up Required',
        'questionnaire_sent' => 'Questionnaire Sent',
        'questionnaire_answer_received' => 'Questionnaire Answered',
        default => 'Pending',
    };
    $meetingSummary = match ($meetingDisposition) {
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_meeting_required' => 'No Meeting Required',
        default => 'Not Set',
    };
    $evaluationSummary = match ($evaluationOutcome) {
        'fit' => 'Fit',
        'unfit' => 'Unfit',
        'PL' => 'Potential Later (PL)',
        default => 'Undecided',
    };
    $transferSummary = $child->transfer_status === 'transferred' ? 'Transferred' : 'Not Transferred';
    $workflowTone = match ($workflowStatus) {
        'confirmed' => 'success',
        'cancelled' => 'danger',
        'followup_required' => 'warning',
        'questionnaire_sent', 'questionnaire_answer_received' => 'info',
        default => 'secondary',
    };
    $meetingTone = match ($meetingDisposition) {
        'completed' => 'success',
        'cancelled' => 'danger',
        'no_meeting_required' => 'info',
        default => 'secondary',
    };
    $evaluationTone = match ($evaluationOutcome) {
        'fit' => 'success',
        'unfit' => 'danger',
        'PL' => 'warning',
        default => 'secondary',
    };
    $transferTone = match (true) {
        $child->transfer_status === 'transferred' => 'info',
        $unresolvedTransferServices !== [] => 'warning',
        filled($transferBlockedReason) => 'secondary',
        default => 'success',
    };
    $transferHeadline = match (true) {
        $child->transfer_status === 'transferred' => 'Transferred',
        $unresolvedTransferServices !== [] => 'Transfer blocked by service mapping',
        filled($transferBlockedReason) => 'Transfer not ready yet',
        default => 'Ready for transfer',
    };
    $transferBody = match (true) {
        $child->transfer_status === 'transferred' => 'This child is already linked to a student record and now belongs on the Transferred Children page.',
        $unresolvedTransferServices !== [] => 'One or more raw service values no longer map cleanly to an active service type. Update the child service interests, save, then retry transfer.',
        filled($transferBlockedReason) => $transferBlockedReason,
        default => 'This child has a terminal meeting result, a fit evaluation, and a clean service mapping. Use Transfer Child when you are ready to create the linked parent and student accounts.',
    };
  @endphp

  <div class="row g-6">
    <div class="col-12">
      <div
        class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-6">
        <div class="d-flex align-items-center gap-2">
          <h4 class="mb-0">Child Workflow</h4>
          <details class="intake-info intake-info--inline">
            <summary class="intake-info__trigger" aria-label="About this page">
              <i class="icon-base ti tabler-info-circle icon-18px"></i>
            </summary>
            <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
              Update this child's workflow, consultation, services, school details, notes, and transfer readiness.
            </div>
          </details>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <a href="{{ $this->cancelUrl() }}" class="btn btn-label-secondary">
            Back to Queue
          </a>
          @if ($this->parentEditUrl())
            <a href="{{ $this->parentEditUrl() }}" class="btn btn-label-secondary">
              Edit Parent
            </a>
          @endif
          @if ($child->transfer_status !== 'transferred')
            <button type="button" class="btn {{ $canTransfer ? 'btn-success' : 'btn-label-secondary' }}"
              wire:click="openTransferModal" @disabled(!$canTransfer)
              aria-label="{{ $canTransfer ? 'Transfer child' : 'Transfer locked: ' . $transferBody }}">
              Transfer Child
            </button>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-4">
      <div class="card h-100">
        <div class="card-body pt-12">
          <h5 class="pb-4 border-bottom mb-4">Child Snapshot</h5>
          <div class="info-container">
            <h5 class="mb-1">{{ $child->child_name ?: 'Unnamed child' }}</h5>
            <p class="text-body-secondary small mb-4">Booking ref: {{ $booking?->booking_reference ?: '-' }}</p>

            <ul class="list-unstyled mb-6">
              <li class="mb-2">
                <span class="h6">Parent:</span>
                <span>{{ $booking?->parent_name ?: '-' }}</span>
              </li>
              <li class="mb-2">
                <span class="h6">Email:</span>
                <span>{{ $booking?->parent_email ?: '-' }}</span>
              </li>
              <li class="mb-2">
                <span class="h6">Phone:</span>
                <span>{{ $booking?->parent_phone ?: '-' }}</span>
              </li>
              <hr>
              <li class="mb-2">
                <span class="h6">Age:</span>
                <span>{{ $child->child_age ?: '-' }} years</span>
              </li>
              <li class="mb-2">
                <span class="h6">Grade:</span>
                <span>{{ $gradeTitles[$child->child_grade] ?? ($child->child_grade ?: '-') }}</span>
              </li>
              <li class="mb-2">
                <span class="h6">Original Consultation:</span>
                <span>{{ $originalBookingConsultationTypeLabel ?: 'Not captured' }}</span>
              </li>
              <li class="mb-2">
                <span class="h6">Original Services:</span>
                @if ($originalBookingServiceInterests !== [])
                  <div class="d-flex flex-wrap gap-2 mt-2">
                    @foreach ($originalBookingServiceInterests as $serviceLabel)
                      <span class="badge bg-label-primary">{{ $serviceLabel }}</span>
                    @endforeach
                  </div>
                @else
                  <span class="text-body-secondary">No service choice was captured on the original booking.</span>
                @endif
              </li>
              <hr>
              <li class="mb-2">
                <span class="h6">Transfer Status:</span>
                <span class="badge bg-label-{{ $transferTone }}">{{ $transferSummary }}</span>
              </li>
            </ul>
          </div>

          @if (in_array($workflowStatus, ['questionnaire_sent', 'questionnaire_answer_received'], true))
            <div class="alert alert-warning d-flex align-items-start gap-3 mb-4">
              <div class="avatar avatar-sm">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="icon-base ti tabler-alert-triangle icon-22px"></i>
                </span>
              </div>
              <div>
                <div class="fw-semibold mb-1">Reserved questionnaire state</div>
                <div class="small">
                  This child currently carries a reserved questionnaire workflow state. You can update other fields, but the Sprint 3 editor does not allow new transitions into questionnaire states.
                </div>
              </div>
            </div>
          @endif

          <div class="border rounded-3 p-4 bg-lighter">
            <div class="d-flex align-items-center mb-3">
              <div class="avatar me-3">
                <span class="avatar-initial rounded bg-label-{{ $transferTone }}">
                  <i class="icon-base ti tabler-transfer icon-28px"></i>
                </span>
              </div>
              <div>
                <h6 class="mb-0">{{ $transferHeadline }}</h6>
                <small class="text-body-secondary">{{ $transferSummary }}</small>
              </div>
            </div>
            <p class="small text-body-secondary mb-0">{{ $transferBody }}</p>
            @if ($unresolvedTransferServices !== [])
              <div class="small text-warning mt-3">
                <strong>Raw values to review:</strong> {{ implode(', ', $unresolvedTransferServices) }}
              </div>
            @endif
            @if ($canConfirmLinkedParentContactUpdate)
              <div class="mt-3">
                <label class="form-label" for="transfer-contact-update-note">Contact Update Note</label>
                <textarea id="transfer-contact-update-note" class="form-control form-control-sm" rows="2" placeholder="Required before updating the linked parent contact" wire:model.live.debounce.300ms="transferContactUpdateNote"></textarea>
                @error('transferContactUpdateNote')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                <button type="button" class="btn btn-sm btn-primary mt-2" wire:click="confirmLinkedParentContactUpdate({{ $child->id }})">
                  Update Linked Parent Contact
                </button>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-8">
      <div class="card">
        <div class="card-header border-bottom d-flex align-items-center gap-2">
          <h5 class="card-title mb-0">Workflow Editor</h5>
          <details class="intake-info intake-info--inline">
            <summary class="intake-info__trigger" aria-label="Editor save help">
              <i class="icon-base ti tabler-info-circle icon-18px"></i>
            </summary>
            <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
              Save from the latest snapshot. If another admin updates this child first, refresh before saving again.
            </div>
          </details>
        </div>

        <div class="card-body">
          @if (session()->has('success'))
            <div class="alert alert-success">
              {{ session('success') }}
              @if (session()->has('family_workspace_url'))
                <div class="mt-2">
                  <a href="{{ session('family_workspace_url') }}" class="btn btn-sm btn-success">
                    Open Family Workspace
                  </a>
                </div>
              @endif
            </div>
          @endif

          @if (session()->has('info'))
            <div class="alert alert-info">
              {{ session('info') }}
            </div>
          @endif

          @error('email')
            <div class="alert alert-danger">
              {{ $message }}
            </div>
          @enderror

          @error('stale')
            <div class="alert alert-danger">
              {{ $message }}
            </div>
          @enderror

          @error('transfer')
            <div class="alert alert-danger">
              {{ $message }}
            </div>
          @enderror

          <div class="booking-child-status-panel bg-lighter rounded mb-6">
            <div class="booking-child-status-grid">
              <div class="booking-child-status-item">
                <div class="booking-child-status-icon badge rounded bg-label-{{ $workflowTone }} p-2">
                  <i class="icon-base ti tabler-clipboard-check icon-lg"></i>
                </div>
                <div class="booking-child-status-text">
                  <h6 class="mb-0">{{ $workflowSummary }}</h6>
                  <small>Workflow</small>
                </div>
              </div>
              <div class="booking-child-status-item">
                <div class="booking-child-status-icon badge rounded bg-label-{{ $meetingTone }} p-2">
                  <i class="icon-base ti tabler-video icon-lg"></i>
                </div>
                <div class="booking-child-status-text">
                  <h6 class="mb-0">{{ $meetingSummary }}</h6>
                  <small>Meeting</small>
                </div>
              </div>
              <div class="booking-child-status-item">
                <div class="booking-child-status-icon badge rounded bg-label-{{ $evaluationTone }} p-2">
                  <i class="icon-base ti tabler-school icon-lg"></i>
                </div>
                <div class="booking-child-status-text">
                  <h6 class="mb-0">{{ $evaluationSummary }}</h6>
                  <small>Evaluation</small>
                </div>
              </div>
              <div class="booking-child-status-item">
                <div class="booking-child-status-icon badge rounded bg-label-{{ $transferTone }} p-2">
                  <i class="icon-base ti tabler-transfer icon-lg"></i>
                </div>
                <div class="booking-child-status-text">
                  <h6 class="mb-0">{{ $transferSummary }}</h6>
                  <small>Transfer</small>
                </div>
              </div>
            </div>
          </div>

          <div class="nav-align-top bg-lighter rounded p-2 mb-6">
            <ul class="nav nav-pills booking-child-section-nav mb-0" data-booking-section-nav>
              <li class="nav-item">
                <a class="nav-link active d-flex align-items-center" href="#section-workflow">
                  <i class="icon-base ti tabler-settings icon-sm me-1_5"></i>Workflow
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#section-consultation">
                  <i class="icon-base ti tabler-video icon-sm me-1_5"></i>Consultation
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#section-services">
                  <i class="icon-base ti tabler-school icon-sm me-1_5"></i>Services
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#section-email">
                  <i class="icon-base ti tabler-mail icon-sm me-1_5"></i>Email Status
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#section-notes">
                  <i class="icon-base ti tabler-notes icon-sm me-1_5"></i>Notes
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#section-audit">
                  <i class="icon-base ti tabler-history icon-sm me-1_5"></i>Audit Trail
                  @if ($auditTrailLoaded && $auditTotal > 0)
                    <span class="badge bg-label-secondary ms-1_5">{{ $auditTotal }}</span>
                  @endif
                </a>
              </li>
            </ul>
          </div>

          <form wire:submit.prevent="save" class="d-flex flex-column gap-4">
            <div class="card mb-6" id="section-workflow" style="scroll-margin-top: 6rem;">
              <div class="card-header d-flex align-items-center gap-2">
                <h5 class="card-title mb-0">Workflow &amp; Scheduling</h5>
                <details class="intake-info intake-info--inline">
                  <summary class="intake-info__trigger" aria-label="Workflow help">
                    <i class="icon-base ti tabler-info-circle icon-18px"></i>
                  </summary>
                  <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                    Use Cancelled / Closed when the booking stopped before a real meeting outcome. Meeting Disposition can stay Not Set until a real meeting path has schedule details.
                  </div>
                </details>
              </div>
              <div class="card-body">
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="d-flex align-items-center gap-1 mb-1">
                    <label class="form-label mb-0">Workflow Status</label>
                    <details class="intake-info intake-info--inline">
                      <summary class="intake-info__trigger" aria-label="Workflow status help">
                        <i class="icon-base ti tabler-info-circle icon-16px"></i>
                      </summary>
                      <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                        Choose the child's current operational state.
                      </div>
                    </details>
                  </div>
                  <select class="form-select @error('workflowStatus') is-invalid @enderror" wire:model.live="workflowStatus">
                    @foreach ($workflowOptions as $value => $label)
                      @php($disabled = $value === 'followup_required' && $evaluationOutcome === 'PL')
                      <option value="{{ $value }}" @disabled($disabled && $workflowStatus !== $value)>{{ $label }}</option>
                    @endforeach
                  </select>
                  @error('workflowStatus')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-6">
                  <div class="d-flex align-items-center gap-1 mb-1">
                    <label class="form-label mb-0">Meeting Disposition</label>
                    <details class="intake-info intake-info--inline">
                      <summary class="intake-info__trigger" aria-label="Meeting disposition help">
                        <i class="icon-base ti tabler-info-circle icon-16px"></i>
                      </summary>
                      <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                        Record the meeting result once the booking path is clear.
                      </div>
                    </details>
                  </div>
                  <select class="form-select @error('meetingDisposition') is-invalid @enderror" wire:model.live="meetingDisposition">
                    <option value="">Not Set</option>
                    @foreach ($meetingDispositionOptions as $value => $label)
                      <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  @error('meetingDisposition')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                @if ($meetingDisposition === 'no_meeting_required')
                  <div class="col-12">
                    <label class="form-label">No-Meeting Reason</label>
                    <textarea class="form-control @error('meetingDispositionReason') is-invalid @enderror" rows="2" wire:model.blur="meetingDispositionReason"></textarea>
                    @error('meetingDispositionReason')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                @endif

                @if (
                    $workflowStatus === 'confirmed' ||
                        $workflowStatus === 'followup_required' ||
                        in_array($meetingDisposition, ['completed', 'cancelled'], true) ||
                        filled($scheduledDate) ||
                        filled($scheduledTime))
                  <div class="col-12">
                    <div class="row g-4">
                      <div class="col-md-6">
                        <label class="form-label">Scheduled Date</label>
                        <div wire:ignore>
                          <input type="text"
                            class="form-control js-booking-child-picker @error('scheduledDate') is-invalid @enderror"
                            id="scheduled-date-picker-{{ $child->id }}"
                            data-picker-type="date"
                            data-property="scheduledDate"
                            data-current-value="{{ $scheduledDate }}"
                            placeholder="Month DD, YYYY"
                            autocomplete="off">
                        </div>
                        @error('scheduledDate')
                          <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                      </div>

                      <div class="col-md-6">
                        <div class="d-flex align-items-center gap-1 mb-1">
                          <label class="form-label mb-0">Scheduled Time</label>
                          <details class="intake-info intake-info--inline">
                            <summary class="intake-info__trigger" aria-label="Scheduled time help">
                              <i class="icon-base ti tabler-info-circle icon-16px"></i>
                            </summary>
                            <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                              Time slots use 15-minute intervals.
                            </div>
                          </details>
                        </div>
                        <div wire:ignore>
                          <input type="text"
                            class="form-control js-booking-child-picker @error('scheduledTime') is-invalid @enderror"
                            id="scheduled-time-picker-{{ $child->id }}"
                            data-picker-type="time"
                            data-property="scheduledTime"
                            data-current-value="{{ $scheduledTime }}"
                            placeholder="12:00 PM"
                            autocomplete="off">
                        </div>
                        @error('scheduledTime')
                          <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>
                  </div>
                @endif

                @if ($workflowStatus === 'followup_required' || $evaluationOutcome === 'PL' || filled($followupDate))
                  <div class="col-md-6">
                    <div class="d-flex align-items-center gap-1 mb-1">
                      <label class="form-label mb-0">Follow-Up Date & Time</label>
                      <details class="intake-info intake-info--inline">
                        <summary class="intake-info__trigger" aria-label="Follow-up date help">
                          <i class="icon-base ti tabler-info-circle icon-16px"></i>
                        </summary>
                        <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                          Required for Follow-Up Required and Potential Later (PL). PL should point to a future follow-up time.
                        </div>
                      </details>
                    </div>
                    <input type="datetime-local" class="form-control @error('followupDate') is-invalid @enderror" wire:model.blur="followupDate">
                    @error('followupDate')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                @endif

                <div class="col-md-6">
                  <div class="d-flex align-items-center gap-1 mb-1">
                    <label class="form-label mb-0">Evaluation Outcome</label>
                    <details class="intake-info intake-info--inline">
                      <summary class="intake-info__trigger" aria-label="Evaluation outcome help">
                        <i class="icon-base ti tabler-info-circle icon-16px"></i>
                      </summary>
                      <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                        Set the evaluation after the meeting result is known. Potential Later (PL) needs a future follow-up time.
                      </div>
                    </details>
                  </div>
                  <select class="form-select @error('evaluationOutcome') is-invalid @enderror" wire:model.live="evaluationOutcome" @disabled(!$canChooseEvaluationOutcome)>
                    @foreach ($evaluationOptions as $value => $label)
                      @php($disabled = $value === 'PL' && $workflowStatus === 'followup_required')
                      <option value="{{ $value }}" @disabled($disabled && $evaluationOutcome !== $value)>{{ $label }}</option>
                    @endforeach
                  </select>
                  @error('evaluationOutcome')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  @if (!$canChooseEvaluationOutcome)
                    <div class="small text-body-secondary mt-1">Choose Meeting Disposition first.</div>
                  @endif
                </div>
              </div>
              </div>
            </div>

            <div class="card mb-6" id="section-consultation" style="scroll-margin-top: 6rem;">
              <div class="card-header d-flex align-items-center gap-2">
                <h5 class="card-title mb-0">Consultation Details</h5>
                <details class="intake-info intake-info--inline">
                  <summary class="intake-info__trigger" aria-label="Consultation details help">
                    <i class="icon-base ti tabler-info-circle icon-18px"></i>
                  </summary>
                  <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                    Choose online or in-person, then add the matching link or address.
                  </div>
                </details>
              </div>
              <div class="card-body">
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="mb-2">
                    <div class="d-flex align-items-center gap-1">
                      <label class="form-label mb-0">Consultation Mode</label>
                      <details class="intake-info intake-info--inline">
                        <summary class="intake-info__trigger" aria-label="Consultation mode help">
                          <i class="icon-base ti tabler-info-circle icon-16px"></i>
                        </summary>
                        <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                          The chosen mode controls whether a meeting link or address is required.
                        </div>
                      </details>
                    </div>
                  </div>

                  <div class="d-flex flex-wrap gap-4">
                    <label class="form-check d-inline-flex align-items-center gap-2 mb-0">
                      <input type="radio" class="form-check-input" id="consultation-type-online" name="consultationType" wire:model.live="consultationType" value="online">
                      <span class="form-check-label">Online</span>
                    </label>

                    <label class="form-check d-inline-flex align-items-center gap-2 mb-0">
                      <input type="radio" class="form-check-input" id="consultation-type-in-person" name="consultationType" wire:model.live="consultationType" value="in-person">
                      <span class="form-check-label">In-person</span>
                    </label>
                  </div>

                  @error('consultationType')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>

                @if (in_array($consultationType, ['online', 'in-person'], true))
                  <div class="col-md-6" wire:key="consultation-detail-{{ $consultationType }}">
                    <label class="form-label">{{ $consultationType === 'in-person' ? 'Meeting Address' : 'Meeting Link' }}</label>

                    @if ($consultationType === 'online')
                      <input type="url" class="form-control @error('meetingLink') is-invalid @enderror" wire:model.blur="meetingLink" placeholder="https://...">
                      @error('meetingLink')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    @else
                      <textarea class="form-control @error('meetingAddress') is-invalid @enderror" rows="2" wire:model.blur="meetingAddress"></textarea>
                      @error('meetingAddress')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    @endif
                  </div>
                @endif
              </div>
              </div>
            </div>

            <div class="card mb-6" id="section-services" style="scroll-margin-top: 6rem;">
              <div class="card-header d-flex align-items-center gap-2">
                <h5 class="card-title mb-0">Services</h5>
                <details class="intake-info intake-info--inline">
                  <summary class="intake-info__trigger" aria-label="Services and school help">
                    <i class="icon-base ti tabler-info-circle icon-18px"></i>
                  </summary>
                  <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                    Select the To Quran services this child needs. School metadata is filled automatically for launch compatibility.
                  </div>
                </details>
              </div>
              <div class="card-body">
              <div class="row g-4">
                <div class="col-12">
                  <div class="d-flex align-items-center gap-1 mb-1">
                    <label class="form-label mb-0">Service Interests</label>
                    <details class="intake-info intake-info--inline">
                      <summary class="intake-info__trigger" aria-label="Service interests help">
                        <i class="icon-base ti tabler-info-circle icon-16px"></i>
                      </summary>
                      <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                        Select every active service this child needs.
                      </div>
                    </details>
                  </div>
                  <div class="bg-lighter rounded p-4">
                    <div class="row g-3">
                      @forelse ($serviceOptions as $service)
                        @php($selected = in_array($service['value'], $serviceInterests, true))
                        @php($fromOriginal = in_array($service['value'], $originalBookingServiceValues, true))
                        <div class="col-12 col-md-6" wire:key="service-option-{{ md5($service['value']) }}">
                          <label class="card h-100 mb-0 shadow-none {{ $selected ? 'border border-primary' : 'border border-light-subtle' }} {{ $fromOriginal ? 'bg-lighter' : '' }}">
                            <span class="card-body p-3 d-flex align-items-start gap-3">
                              <input type="checkbox" class="form-check-input mt-1" wire:model.live="serviceInterests" value="{{ $service['value'] }}" @checked($selected)>
                              <span class="d-flex flex-column gap-1">
                                <span class="fw-semibold text-body">
                                  <span>{{ $service['label'] }}</span>
                                </span>
                                <span class="small text-body-secondary">
                                  @if ($fromOriginal)
                                    Original booking choice
                                  @else
                                    Available support line for this child
                                  @endif
                                </span>
                              </span>
                            </span>
                          </label>
                        </div>
                      @empty
                        <div class="col-12">
                          <div class="alert alert-warning mb-0">
                            No active child-facing services are configured.
                          </div>
                        </div>
                      @endforelse
                    </div>
                  </div>
                  @error('serviceInterests')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @elseif ($errors->has('serviceInterests.*'))
                    <div class="invalid-feedback d-block">{{ $errors->first('serviceInterests.*') }}</div>
                  @enderror
                </div>
              </div>
              </div>
            </div>

            <div class="card mb-6" id="section-email" style="scroll-margin-top: 6rem;">
              <div class="card-header d-flex align-items-center gap-2">
                <h5 class="card-title mb-0">Email Delivery Status</h5>
                <details class="intake-info intake-info--inline">
                  <summary class="intake-info__trigger" aria-label="Email delivery help">
                    <i class="icon-base ti tabler-info-circle icon-18px"></i>
                  </summary>
                  <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                    Latest tracked delivery state for each child email.
                  </div>
                </details>
              </div>
              <div class="card-body">

              <div class="row g-3">
                @foreach ($emailStatuses as $emailType => $emailStatus)
                  @php($badge = $this->emailStatusBadge($emailStatus, $emailType))
                  <div class="col-12 col-md-6">
                    <div class="card h-100">
                      <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                          <div>
                            <div class="fw-semibold">{{ $this->emailTypeLabel($emailType) }}</div>
                          </div>
                          <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                        </div>

                        <div class="small d-flex flex-column gap-1 text-body-secondary">
                          <span><strong>Last attempt:</strong> {{ $this->formatEmailTimestamp($emailStatus?->last_attempt_at) }}</span>
                          <span><strong>Last sent:</strong> {{ $this->formatEmailTimestamp($emailStatus?->last_sent_at) }}</span>
                        </div>

                        @if (filled($emailStatus?->last_error_message))
                          <div class="small text-danger mt-2">
                            <strong>Delivery note:</strong> {{ $emailStatus?->last_error_message }}
                          </div>
                        @endif

                        @if ($this->canResendEmailType($emailType, $emailStatus))
                          <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-label-primary" wire:click="resendEmail({{ $child->id }}, '{{ $emailType }}')" wire:loading.attr="disabled" wire:target="resendEmail">
                              <span wire:loading.remove wire:target="resendEmail">Resend</span>
                              <span wire:loading wire:target="resendEmail">
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                Resending...
                              </span>
                            </button>
                          </div>
                        @endif
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
              </div>
            </div>

            <div class="card mb-6" id="section-notes" style="scroll-margin-top: 6rem;">
              <div class="card-header d-flex align-items-center gap-2">
                <h5 class="card-title mb-0">Notes &amp; Admin Context</h5>
                <details class="intake-info intake-info--inline">
                  <summary class="intake-info__trigger" aria-label="Notes help">
                    <i class="icon-base ti tabler-info-circle icon-18px"></i>
                  </summary>
                  <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                    Keep child-specific context here for the next admin.
                  </div>
                </details>
              </div>
              <div class="card-body">

              <label class="form-label">Notes</label>
              <textarea class="form-control @error('notes') is-invalid @enderror" rows="4" wire:model.blur="notes"></textarea>
              @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              </div>
            </div>

            <div class="card mb-6" id="section-audit" style="scroll-margin-top: 6rem;" x-data="{ open: {{ $auditTrailLoaded ? 'true' : 'false' }} }">
              <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                  <h5 class="card-title mb-0">Audit Trail</h5>
                  <details class="intake-info intake-info--inline">
                    <summary class="intake-info__trigger" aria-label="Audit trail help">
                      <i class="icon-base ti tabler-info-circle icon-18px"></i>
                    </summary>
                    <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                      Saved admin changes for this child's workflow fields.
                    </div>
                  </details>
                </div>
                <button type="button" class="btn btn-icon btn-text-secondary"
                  x-on:click="if (!open) { $wire.loadAuditTrail() } open = !open"
                  wire:loading.attr="disabled"
                  wire:target="loadAuditTrail"
                  :aria-label="open ? 'Collapse audit trail' : 'Expand audit trail'"
                  :title="open ? 'Collapse' : 'Expand'">
                  <i class="icon-base ti {{ $auditTrailLoaded ? 'tabler-chevron-up' : 'tabler-chevron-down' }} icon-20px" :class="open ? 'tabler-chevron-up' : 'tabler-chevron-down'"></i>
                </button>
              </div>
              <div x-show="open" x-transition>
                <div class="card-body pt-0">
                  <div wire:loading wire:target="loadAuditTrail" class="text-body-secondary mb-0">
                    Loading audit trail...
                  </div>
                  <div wire:loading.remove wire:target="loadAuditTrail">
                  @if (! $auditTrailLoaded)
                    <p class="text-body-secondary mb-0">Expand this section to load recent audit entries.</p>
                  @elseif ($auditLogs->isEmpty())
                    <p class="text-body-secondary mb-0">No changes have been recorded for this child yet.</p>
                  @else
                    <div class="table-responsive">
                      <table class="table table-sm table-hover mb-0">
                        <thead>
                          <tr>
                            <th>Field</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Changed By</th>
                            <th>When</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($auditLogs as $log)
                            <tr>
                              <td><span class="text-heading fw-medium">{{ str_replace('_', ' ', $log->field_name) }}</span></td>
                              <td><span class="text-body-secondary">{{ filled($log->from_value) ? $log->from_value : '—' }}</span></td>
                              <td>{{ filled($log->to_value) ? $log->to_value : '—' }}</td>
                              <td>{{ $log->changedBy?->name ?? '—' }}</td>
                              <td class="text-nowrap" title="{{ $log->changed_at?->format('d M Y H:i:s') }}">
                                {{ $log->changed_at?->diffForHumans() ?? '—' }}
                              </td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                    @if ($auditTotal > $auditLogLimit)
                      <p class="small text-body-secondary mt-2 mb-0">
                        Showing the {{ $auditLogLimit }} most recent entries of {{ $auditTotal }} total.
                      </p>
                    @endif
                  @endif
                  </div>
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-body d-flex flex-wrap justify-content-end gap-2">
                @if ($child->transfer_status !== 'transferred')
                  <button type="button" class="btn {{ $canTransfer ? 'btn-outline-success' : 'btn-label-secondary' }}" wire:click="openTransferModal" @disabled(!$canTransfer)
                    aria-label="{{ $canTransfer ? 'Transfer child' : 'Transfer locked: ' . $transferBody }}">
                    Transfer Child
                  </button>
                @endif
                <a href="{{ $this->cancelUrl() }}" class="btn btn-label-secondary">
                  Cancel
                </a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                  <span wire:loading.remove wire:target="save">Save Child</span>
                  <span wire:loading wire:target="save">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Saving...
                  </span>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    @if ($showTransferModal)
      <div class="modal fade show d-block" tabindex="-1" style="background: rgba(17, 24, 39, 0.55);">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Confirm Transfer</h5>
              <button type="button" class="btn-close" wire:click="cancelTransferModal" wire:loading.attr="disabled" wire:target="transfer"></button>
            </div>
            <div class="modal-body">
              <p class="mb-3">
                Transfer <strong>{{ $child->child_name ?: 'this child' }}</strong> now?
              </p>
              <div class="small text-body-secondary d-flex flex-column gap-1">
                <span>This will create or link the parent account, create or link the student record, and mark this child as pending activation.</span>
                <span>Parent and child activation emails are sent later from the Family Workspace.</span>
                <span>Use this only when the service interests and milestone state are final.</span>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-label-secondary" wire:click="cancelTransferModal" wire:loading.attr="disabled" wire:target="transfer">
                Cancel
              </button>
              <button type="button" class="btn btn-success" wire:click="transfer({{ $child->id }})" wire:loading.attr="disabled" wire:target="transfer">
                <span wire:loading.remove wire:target="transfer">Confirm Transfer</span>
                <span wire:loading wire:target="transfer">
                  <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                  Transferring...
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>

@once
  @push('scripts')
    @vite(['resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
    <script>
      (function() {
        if (window.w14BookingChildPickersBooted) {
          return;
        }

        window.w14BookingChildPickersBooted = true;

        const resolveComponent = input => {
          const componentRoot = input.closest('[wire\\:id]');
          const componentId = componentRoot ? componentRoot.getAttribute('wire:id') : null;

          if (!componentId || !window.Livewire) {
            return null;
          }

          return window.Livewire.find(componentId);
        };

        const syncLivewireProperty = (input, value) => {
          const component = resolveComponent(input);
          const property = input.dataset.property;

          if (!component || !property) {
            return;
          }

          component.set(property, value);
        };

        const hasAdjacentValidationFeedback = input => {
          const ignoredWrapper = input.closest('[wire\\:ignore]');
          const feedback = ignoredWrapper?.nextElementSibling;

          return Boolean(
            feedback?.classList.contains('invalid-feedback') &&
            feedback.textContent.trim()
          );
        };

        const syncPickerValidationState = input => {
          const picker = input?._flatpickr;

          if (!picker) {
            return;
          }

          const invalid = input.classList.contains('is-invalid') || hasAdjacentValidationFeedback(input);

          input.classList.toggle('is-invalid', invalid);
          picker.altInput?.classList.toggle('is-invalid', invalid);
        };

        const ensurePickerValue = input => {
          if (!input._flatpickr) {
            return;
          }

          const currentValue = input.dataset.currentValue || '';

          if (!currentValue) {
            input._flatpickr.clear(false);
            syncPickerValidationState(input);
            return;
          }

          if (input._flatpickr.input.value !== currentValue) {
            input._flatpickr.setDate(currentValue, false);
          }

          syncPickerValidationState(input);
        };

        const initPicker = input => {
          if (!input || typeof window.flatpickr === 'undefined' || !input.dataset.pickerType) {
            return;
          }

          if (input._flatpickr) {
            ensurePickerValue(input);
            return;
          }

          const pickerType = input.dataset.pickerType;
          const sharedConfig = {
            allowInput: true,
            static: true,
            monthSelectorType: 'static',
            onChange: function(selectedDates, dateStr) {
              input.dataset.currentValue = dateStr;
              input.classList.remove('is-invalid');
              this.altInput?.classList.remove('is-invalid');
              syncLivewireProperty(input, dateStr);
            },
            onClose: function(selectedDates, dateStr) {
              input.dataset.currentValue = dateStr;
              syncLivewireProperty(input, dateStr);
            }
          };

          const config = pickerType === 'time' ? {
            ...sharedConfig,
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            altInput: true,
            altFormat: 'h:i K',
            time_24hr: false,
            minuteIncrement: 15
          } : {
            ...sharedConfig,
            altInput: true,
            altFormat: 'F j, Y',
            dateFormat: 'Y-m-d'
          };

          window.flatpickr(input, config);
          ensurePickerValue(input);
          syncPickerValidationState(input);
        };

        const initAllBookingChildPickers = (root = document) => {
          root.querySelectorAll('.js-booking-child-picker[data-picker-type]').forEach(initPicker);
        };

        const activateSectionNavLink = link => {
          const nav = link.closest('[data-booking-section-nav]');

          if (!nav) {
            return;
          }

          window.w14BookingChildActiveSection = link.getAttribute('href');

          nav.querySelectorAll('.nav-link').forEach(navLink => {
            navLink.classList.remove('active');
          });
          link.classList.add('active');
        };

        const restoreSectionNavActive = (root = document) => {
          const activeSection = window.w14BookingChildActiveSection || window.location.hash;

          if (!activeSection || !activeSection.startsWith('#section-')) {
            return;
          }

          root.querySelectorAll('[data-booking-section-nav]').forEach(nav => {
            const link = nav.querySelector(`.nav-link[href="${activeSection}"]`);

            if (!link) {
              return;
            }

            nav.querySelectorAll('.nav-link').forEach(navLink => {
              navLink.classList.remove('active');
            });
            link.classList.add('active');
          });
        };

        const scheduleInit = () => {
          window.requestAnimationFrame(() => {
            initAllBookingChildPickers();
            restoreSectionNavActive();
          });
        };

        document.addEventListener('click', event => {
          const link = event.target.closest('[data-booking-section-nav] .nav-link[href^="#section-"]');

          if (!link) {
            return;
          }

          activateSectionNavLink(link);
        });

        document.addEventListener('DOMContentLoaded', scheduleInit);
        document.addEventListener('livewire:initialized', scheduleInit);
        document.addEventListener('livewire:navigated', scheduleInit);

        document.addEventListener('livewire:init', () => {
          Livewire.hook('morphed', ({
            el
          }) => {
            initAllBookingChildPickers(el);
            restoreSectionNavActive(el);
          });
        });
      })();
    </script>
  @endpush
@endonce
