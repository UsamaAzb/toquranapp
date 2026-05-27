<div class="row g-6">
  <div class="col-12">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-6">
      <div>
        <h4 class="mb-1">Intake Review Queue</h4>
        <p class="text-body-secondary mb-0">Review flagged submissions before they enter the active queue.</p>
      </div>
      <div class="queue-page-actions">
        <a href="{{ route('admin.bookings.livewire') }}" class="btn btn-label-secondary">
          Active Queue
        </a>
        <a href="{{ route('admin.bookings.transferred') }}" class="btn btn-label-secondary">
          Transferred Children
        </a>
      </div>
    </div>
  </div>

  @foreach ($stats as $stat)
    <div class="col-12 col-sm-6 col-xl" wire:key="stat-{{ $stat['filter'] }}">
      <button
        type="button"
        class="card card-border-shadow-{{ $stat['tone'] }} h-100 w-100 text-start border-0 bg-transparent p-0 intake-stat-card {{ $stat['active'] ? 'intake-stat-card--active' : '' }}"
        wire:click="applyQuickFilter('{{ $stat['filter'] }}')"
        aria-pressed="{{ $stat['active'] ? 'true' : 'false' }}"
      >
        <div class="card-body">
          <div class="d-flex align-items-center mb-1">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-{{ $stat['tone'] }}">
                <i class="icon-base ti {{ $stat['icon'] }} icon-28px"></i>
              </span>
            </div>
            <h4 class="mb-0">{{ number_format($stat['value']) }}</h4>
          </div>
          <p class="mb-0">{{ $stat['label'] }}</p>
        </div>
      </button>
    </div>
  @endforeach

  @if (session()->has('success'))
    <div class="col-12">
      <div class="alert alert-success alert-dismissible mb-0" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
  @endif

  <div class="col-12">
    <div class="card">
      <div class="card-header border-bottom">
        <div class="row m-3 my-0 justify-content-between g-3">
          <div class="col-12 col-lg">
            <h5 class="card-title mb-1">Pending Review-First Submissions</h5>
          </div>
          <div class="col-12 col-lg-auto">
            <div class="booking-toolbar d-flex flex-column flex-sm-row align-items-stretch gap-2">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="icon-base ti tabler-search icon-18px"></i></span>
                <input type="search" class="form-control" placeholder="Search parent, child, or review detail" wire:model.live.debounce.300ms="search">
              </div>

              <select class="form-select w-auto" wire:model.live="reasonFilter">
                @foreach ($reasonOptions as $value => $label)
                  <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
              </select>

              <select class="form-select w-auto" wire:model.live="perPage">
                <option value="10">10 reviews</option>
                <option value="25">25 reviews</option>
                <option value="50">50 reviews</option>
              </select>

              @if ($search !== '' || $reasonFilter !== 'all' || $perPage !== 10)
                <button type="button" class="btn btn-label-secondary" wire:click="resetListFilters">
                  Reset
                </button>
              @endif
            </div>
          </div>
        </div>
      </div>

      <div class="card-body p-0">
        @if ($reviews->isEmpty())
          <div class="p-5 text-center">
            <div class="mx-auto mb-3 d-inline-flex align-items-center justify-content-center rounded-circle bg-label-secondary" style="width:64px;height:64px;">
              <i class="icon-base ti tabler-checks icon-32px"></i>
            </div>
            <h5 class="mb-2">No pending review items</h5>
            <p class="text-body-secondary mb-3">Nothing currently needs review-first triage in this queue.</p>
            @if ($search !== '' || $reasonFilter !== 'all')
              <button type="button" class="btn btn-label-secondary" wire:click="resetListFilters">
                Clear Filters
              </button>
            @endif
          </div>
        @else
          <div class="p-3 p-lg-4">
            <div class="d-flex flex-column gap-4">
              @foreach ($reviews as $review)
                @php
                  $reviewConflictState = $this->reviewConflictState($review);
                  $reasonMeta = $this->reviewReasonBadge($reviewConflictState['reason_key']);
                  $summary = $this->reviewSummary($review);
                @endphp

                <div class="card border shadow-none" wire:key="review-{{ $review->id }}">
                  <div class="card-header border-bottom intake-review-header">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
                      <div class="d-flex flex-column gap-2">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                          <h6 class="mb-0">{{ $review->parent_name ?: 'Unnamed parent' }}</h6>
                          <button type="button" class="btn btn-sm btn-icon btn-warning text-white" wire:click="openContactCorrectionModal({{ $review->id }})" title="Edit parent contact" aria-label="Edit parent contact">
                            <i class="icon-base ti tabler-pencil icon-16px"></i>
                          </button>
                          <span class="badge {{ $reasonMeta['class'] }} d-inline-flex align-items-center gap-1">
                            <i class="icon-base ti {{ $reasonMeta['icon'] }} icon-14px"></i>
                            {{ $reasonMeta['label'] }}
                          </span>
                          <span class="badge bg-label-secondary">#{{ $review->id }}</span>
                        </div>
                        <div class="small text-body-secondary">
                          {{ $review->parent_email ?: '-' }} | {{ $review->parent_phone ?: '-' }} | Submitted {{ $this->formatDateTime($review->created_at) }}
                          @if ($reviewConflictState['detail_info'] || $reviewConflictState['detail'])
                            <details class="intake-info intake-info--inline ms-2">
                              <summary class="intake-info__trigger" aria-label="Open review info">
                                <i class="icon-base ti tabler-info-circle icon-18px"></i>
                              </summary>
                              <div class="intake-info__panel intake-info__panel--header">
                                {{ $reviewConflictState['detail_info'] ?: $reviewConflictState['detail'] }}
                              </div>
                            </details>
                          @endif
                        </div>
                      </div>

                      <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-label-secondary">Pending {{ $summary['pending'] }}</span>
                        <span class="badge bg-label-success">Approved {{ $summary['approved'] }}</span>
                        <span class="badge bg-label-danger">Dismissed {{ $summary['dismissed'] }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="card-body">
                    @error("reviewActions.$review->id")
                      <div class="alert alert-danger mb-3" role="alert">
                        {{ $message }}
                      </div>
                    @enderror

                    <div class="table-responsive intake-review-table-wrap d-none d-md-block">
                      <table class="table align-middle mb-0">
                        <thead class="table-light">
                          <tr>
                            <th style="min-width: 220px;">Child</th>
                            <th style="min-width: 220px;">Review Status</th>
                            <th style="min-width: 200px;">Context</th>
                            <th style="min-width: 220px;">Child Decision Note</th>
                            <th style="min-width: 160px;" class="text-center">Decision Status</th>
                            <th style="min-width: 190px;" class="text-end">Mark Child</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($review->reviewChildren as $reviewChild)
                            @php
                              $childReasonMeta = $this->reviewChildReasonBadge($review, $reviewChild);
                              $resolutionMeta = $this->resolutionBadge($reviewChild->resolution_status);
                              $contextLinks = $this->contextLinksForReviewChild($review, $reviewChild);
                              $childApprovalBlocked = in_array($reviewChild->review_reason, ['blocked_parent', 'duplicate_child', 'repeat_submission'], true);
                              $approvalLabel = $reviewChild->review_reason === 'suspected_contact_mismatch'
                                ? 'Approve for contact update'
                                : 'Approve';
                            @endphp
                            <tr wire:key="review-{{ $review->id }}-child-{{ $reviewChild->id }}">
                              <td>
                                <div class="d-flex flex-column">
                                  <span class="text-heading fw-medium">{{ $reviewChild->child_name ?: 'Unnamed child' }}</span>
                                  <small class="text-body-secondary">
                                    {{ collect([$reviewChild->child_grade ? 'Grade '.$reviewChild->child_grade : null, $reviewChild->school_system])->filter()->implode(' | ') ?: '-' }}
                                  </small>
                                </div>
                              </td>
                              <td>
                                <span class="badge {{ $childReasonMeta['class'] }} d-inline-flex align-items-center gap-1">
                                  <i class="icon-base ti {{ $childReasonMeta['icon'] }} icon-14px"></i>
                                  {{ $childReasonMeta['label'] }}
                                </span>
                              </td>
                              <td>
                                <div class="d-inline-flex flex-column align-items-start gap-2">
                                  @foreach ($contextLinks as $context)
                                    @if ($context['child_edit_url'] ?? null)
                                      <a href="{{ $context['child_edit_url'] }}" class="btn btn-sm btn-label-secondary text-nowrap">
                                        Open Account #{{ $reviewChild->matched_child_id }}
                                      </a>
                                    @endif
                                    <a href="{{ $context['url'] }}" class="btn btn-sm btn-label-{{ $context['tone'] }} text-nowrap d-inline-flex align-items-center gap-1" title="{{ $context['title'] ?? '' }}">
                                      @if (!empty($context['icon']))
                                        <i class="icon-base ti {{ $context['icon'] }} icon-14px"></i>
                                      @endif
                                      {{ $context['label'] }}
                                    </a>
                                  @endforeach
                                  @if ($contextLinks === [])
                                    <span class="small text-body-secondary">-</span>
                                  @endif
                                </div>
                              </td>
                              <td>
                                <textarea class="form-control form-control-sm" rows="2" placeholder="Child note - required when marking dismissed" aria-label="Child decision note" wire:model.live.debounce.300ms="childResolutionNotes.{{ $reviewChild->id }}"></textarea>
                                @if ($reviewChild->resolution_status !== 'pending_decision')
                                  <div class="small text-body-secondary mt-2">
                                    <strong>Saved child note:</strong> {{ $reviewChild->resolution_note ?: 'No saved child note yet.' }}
                                  </div>
                                @endif
                                @error("childResolutionNotes.$reviewChild->id")
                                  <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                              </td>
                              <td class="text-center">
                                <span class="badge {{ $resolutionMeta['class'] }}">{{ $resolutionMeta['label'] }}</span>
                              </td>
                              <td class="text-end">
                                <div class="intake-child-actions d-inline-flex flex-wrap justify-content-end align-items-center gap-1">
                                  <button type="button" class="btn btn-sm btn-icon btn-warning text-white intake-child-action" wire:click="openCorrectionModal({{ $reviewChild->id }})" title="Edit child row" aria-label="Edit child row">
                                    <i class="icon-base ti tabler-pencil icon-16px"></i>
                                  </button>
                                  @if (!$childApprovalBlocked)
                                    <button type="button" class="btn btn-sm btn-icon btn-success text-white intake-child-action" wire:click="setChildResolution({{ $reviewChild->id }}, 'promote_child')" title="{{ $approvalLabel }}" aria-label="{{ $approvalLabel }}">
                                      <i class="icon-base ti tabler-circle-check-filled icon-16px"></i>
                                    </button>
                                  @endif
                                  <button type="button" class="btn btn-sm btn-icon btn-danger text-white intake-child-action" wire:click="setChildResolution({{ $reviewChild->id }}, 'dismiss_child')" title="Dismiss child" aria-label="Dismiss child">
                                    <i class="icon-base ti tabler-circle-x icon-16px"></i>
                                  </button>
                                  @if ($reviewChild->resolution_status !== 'pending_decision')
                                    <button type="button" class="btn btn-sm btn-icon btn-label-secondary intake-child-action" wire:click="setChildResolution({{ $reviewChild->id }}, 'pending_decision')" title="Reset decision" aria-label="Reset decision">
                                      <i class="icon-base ti tabler-rotate-clockwise icon-16px"></i>
                                    </button>
                                  @endif
                                </div>
                              </td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>

                    <div class="d-flex d-md-none flex-column gap-3">
                      @foreach ($review->reviewChildren as $reviewChild)
                        @php
                          $childReasonMeta = $this->reviewChildReasonBadge($review, $reviewChild);
                          $resolutionMeta = $this->resolutionBadge($reviewChild->resolution_status);
                          $contextLinks = $this->contextLinksForReviewChild($review, $reviewChild);
                          $childApprovalBlocked = in_array($reviewChild->review_reason, ['blocked_parent', 'duplicate_child', 'repeat_submission'], true);
                          $approvalLabel = $reviewChild->review_reason === 'suspected_contact_mismatch'
                            ? 'Approve for contact update'
                            : 'Approve';
                        @endphp
                        <div class="intake-review-mobile-card border rounded-3 p-3" wire:key="review-{{ $review->id }}-child-mobile-{{ $reviewChild->id }}">
                          <div class="d-flex flex-column gap-2">
                            <div class="d-flex flex-column gap-1">
                              <span class="text-heading fw-medium">{{ $reviewChild->child_name ?: 'Unnamed child' }}</span>
                              <small class="text-body-secondary">
                                {{ collect([$reviewChild->child_grade ? 'Grade '.$reviewChild->child_grade : null, $reviewChild->school_system])->filter()->implode(' | ') ?: '-' }}
                              </small>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                              <span class="badge {{ $childReasonMeta['class'] }} d-inline-flex align-items-center gap-1">
                                <i class="icon-base ti {{ $childReasonMeta['icon'] }} icon-14px"></i>
                                {{ $childReasonMeta['label'] }}
                              </span>
                              <span class="badge {{ $resolutionMeta['class'] }}">{{ $resolutionMeta['label'] }}</span>
                            </div>

                            <div class="intake-review-mobile-block">
                              <div class="small text-body-secondary mb-2">Context</div>
                              <div class="d-flex flex-column align-items-start gap-2">
                                @foreach ($contextLinks as $context)
                                  @if ($context['child_edit_url'] ?? null)
                                    <a href="{{ $context['child_edit_url'] }}" class="btn btn-sm btn-label-secondary text-nowrap">
                                      Open Account #{{ $reviewChild->matched_child_id }}
                                    </a>
                                  @endif
                                  <a href="{{ $context['url'] }}" class="btn btn-sm btn-label-{{ $context['tone'] }} text-nowrap d-inline-flex align-items-center gap-1" title="{{ $context['title'] ?? '' }}">
                                    @if (!empty($context['icon']))
                                      <i class="icon-base ti {{ $context['icon'] }} icon-14px"></i>
                                    @endif
                                    {{ $context['label'] }}
                                  </a>
                                @endforeach
                                @if ($contextLinks === [])
                                  <span class="small text-body-secondary">No linked context yet.</span>
                                @endif
                              </div>
                            </div>

                            <div class="intake-review-mobile-block">
                              <label class="form-label small mb-1">Child Decision Note</label>
                              <textarea class="form-control form-control-sm" rows="2" placeholder="Child note - required when marking dismissed" aria-label="Child decision note" wire:model.live.debounce.300ms="childResolutionNotes.{{ $reviewChild->id }}"></textarea>
                              @if ($reviewChild->resolution_status !== 'pending_decision')
                                <div class="small text-body-secondary mt-2">
                                  <strong>Saved child note:</strong> {{ $reviewChild->resolution_note ?: 'No saved child note yet.' }}
                                </div>
                              @endif
                              @error("childResolutionNotes.$reviewChild->id")
                                <div class="text-danger small mt-1">{{ $message }}</div>
                              @enderror
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                              <button type="button" class="btn btn-sm btn-warning text-white" wire:click="openCorrectionModal({{ $reviewChild->id }})">
                                Edit Child
                              </button>
                              @if (!$childApprovalBlocked)
                                <button type="button" class="btn btn-sm btn-success" wire:click="setChildResolution({{ $reviewChild->id }}, 'promote_child')">
                                  {{ $approvalLabel }}
                                </button>
                              @endif
                              <button type="button" class="btn btn-sm btn-danger" wire:click="setChildResolution({{ $reviewChild->id }}, 'dismiss_child')">
                                Dismiss
                              </button>
                              @if ($reviewChild->resolution_status !== 'pending_decision')
                                <button type="button" class="btn btn-sm btn-label-secondary" wire:click="setChildResolution({{ $reviewChild->id }}, 'pending_decision')">
                                  Reset
                                </button>
                              @endif
                            </div>
                          </div>
                        </div>
                      @endforeach
                    </div>

                  </div>

                  <div class="card-footer border-top">
                    @php
                      $needsVerifiedContactUpdate = $this->reviewNeedsVerifiedContactUpdate($review);
                      $finalSubmissionActionState = $this->finalSubmissionActionState($review);
                      $contactActionState = $needsVerifiedContactUpdate
                        ? $this->verifiedContactActionState($review)
                        : ['replace_disabled' => false, 'replace_reason' => null];
                      $footerInfoReason = $finalSubmissionActionState['reason']
                        ?: ($needsVerifiedContactUpdate
                          ? $contactActionState['replace_reason']
                          : $finalSubmissionActionState['promotion_reason']);
                    @endphp
                    <div class="row g-3 align-items-end">
                      <div class="col-12 col-lg">
                        <label class="form-label" for="submission-note-{{ $review->id }}">Final Submission Note</label>
                        <textarea id="submission-note-{{ $review->id }}" class="form-control" rows="2" placeholder="Required before promoting or dismissing this submission" wire:model.live.debounce.300ms="submissionNotes.{{ $review->id }}"></textarea>
                        @error("submissionNotes.$review->id")
                          <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                      </div>
                      <div class="col-12 col-lg-auto">
                        @if ($footerInfoReason)
                          <div class="mb-2 text-lg-end">
                            <details class="intake-info intake-info--footer">
                              <summary class="intake-info__trigger" aria-label="Open contact action info">
                                <i class="icon-base ti tabler-info-circle icon-18px"></i>
                              </summary>
                              <div class="intake-info__panel intake-info__panel--footer">
                                {{ $footerInfoReason }}
                              </div>
                            </details>
                          </div>
                        @endif
                        <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                          @if ($finalSubmissionActionState['actions_disabled'])
                            <button type="button" class="btn btn-label-danger disabled" disabled aria-disabled="true" title="{{ $finalSubmissionActionState['reason'] }}">
                              Dismiss Entire Submission
                            </button>
                          @else
                            <button type="button" class="btn btn-label-danger" wire:click="dismissSubmission({{ $review->id }})">
                              Dismiss Entire Submission
                            </button>
                          @endif
                          @if ($needsVerifiedContactUpdate)
                            <div class="btn-group">
                              <button
                                type="button"
                                class="btn btn-primary dropdown-toggle"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                @if ($finalSubmissionActionState['actions_disabled'] || $contactActionState['replace_disabled'])
                                  disabled
                                  aria-disabled="true"
                                  title="{{ $finalSubmissionActionState['reason'] ?: $contactActionState['replace_reason'] }}"
                                @endif
                              >
                                Contact Action
                              </button>
                              <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                  @if ($contactActionState['replace_disabled'])
                                    <button type="button" class="dropdown-item disabled" disabled aria-disabled="true" title="{{ $contactActionState['replace_reason'] }}">
                                      Replace saved contact & promote
                                    </button>
                                  @else
                                    <button type="button" class="dropdown-item" wire:click="finalizeVerifiedContactUpdate({{ $review->id }})">
                                      Replace saved contact & promote
                                    </button>
                                  @endif
                                </li>
                                <li>
                                  <button type="button" class="dropdown-item disabled" disabled aria-disabled="true" title="Parent account phone slots are not wired yet">
                                    Add phone number
                                  </button>
                                </li>
                              </ul>
                            </div>
                          @else
                            @if ($finalSubmissionActionState['actions_disabled'] || $finalSubmissionActionState['promotion_disabled'])
                              <button type="button" class="btn btn-primary disabled" disabled aria-disabled="true" title="{{ $finalSubmissionActionState['reason'] ?: $finalSubmissionActionState['promotion_reason'] }}">
                                Finalize Approved Children
                              </button>
                            @else
                              <button type="button" class="btn btn-primary" wire:click="finalizePromotion({{ $review->id }})">
                                Finalize Approved Children
                              </button>
                            @endif
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>

      @if (!$reviews->isEmpty())
        <div class="card-footer d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
          <div class="small text-body-secondary">
            Showing {{ $reviews->firstItem() }}-{{ $reviews->lastItem() }} of {{ $reviews->total() }} pending review submission{{ $reviews->total() === 1 ? '' : 's' }}.
          </div>
          {{ $reviews->links() }}
        </div>
      @endif
    </div>
  </div>

  @include('livewire.admin.booking.partials.shared-page-ui')

  <style>
    .intake-review-header {
      background: #f7f8fc;
    }

    .intake-stat-card {
      transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .intake-stat-card:hover {
      transform: translateY(-1px);
    }

    .intake-stat-card--active {
      box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.42);
      border-radius: 8px;
    }

    .intake-stat-card--active .card-body {
      background: rgba(var(--bs-primary-rgb), 0.05);
      border-radius: 8px;
    }

    .intake-child-actions {
      max-width: none;
    }

    .intake-review-table-wrap {
      margin-left: -1.5rem;
      margin-right: -1.5rem;
      width: calc(100% + 3rem);
    }

    .intake-child-action {
      height: 2.25rem;
      justify-content: center;
      width: 2.25rem;
    }

    .intake-child-action--disabled {
      opacity: 0.55;
    }

    .intake-review-mobile-card {
      background: var(--bs-body-bg);
    }

    .intake-review-mobile-block {
      border-top: 1px solid color-mix(in srgb, var(--bs-border-color) 74%, transparent);
      padding-top: 0.85rem;
    }

    @media (max-width: 575.98px) {
      .queue-page-actions {
        display: grid;
        gap: 0.6rem;
        grid-template-columns: minmax(0, 1fr);
        width: 100%;
      }

      .queue-page-actions > .btn,
      .queue-page-actions > a {
        justify-self: stretch;
        width: 100%;
      }

      .booking-toolbar {
        display: grid !important;
        grid-template-columns: 1fr;
        gap: 0.75rem !important;
      }

      .booking-toolbar .form-select {
        width: 100% !important;
      }

      .intake-child-actions {
        display: flex !important;
      }

      .intake-child-action {
        width: 2.25rem;
      }

      .intake-review-table-wrap {
        margin-left: 0;
        margin-right: 0;
        width: 100%;
      }
    }
  </style>

  @if ($correctionReviewChildId)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="correction-modal-title" wire:key="intake-correction-modal">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title" id="correction-modal-title">Edit Intake Review Row</h5>
              <p class="text-body-secondary mb-0 small">Change the child details, then re-check whether this is now a sibling or clean new customer. Use Edit Parent Contact on the review header for parent email or phone changes.</p>
            </div>
            <button type="button" class="btn-close" aria-label="Close" wire:click="closeCorrectionModal"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label" for="correction-child-name">Child Name</label>
                <input id="correction-child-name" type="text" class="form-control" wire:model.live.debounce.300ms="correctionForm.child_name">
                @error('correctionForm.child_name')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label" for="correction-child-age">Age</label>
                <input id="correction-child-age" type="text" class="form-control" wire:model.live.debounce.300ms="correctionForm.child_age">
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label" for="correction-child-grade">Grade</label>
                <input id="correction-child-grade" type="text" class="form-control" wire:model.live.debounce.300ms="correctionForm.child_grade">
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label" for="correction-school-system">School System</label>
                <select id="correction-school-system" class="form-select" wire:model.live="correctionForm.school_system">
                  <option value="">Select school system</option>
                  @foreach ($schoolSystemOptions as $schoolSystemValue => $schoolSystemLabel)
                    <option value="{{ $schoolSystemValue }}">{{ $schoolSystemLabel }}</option>
                  @endforeach
                </select>
                @error('correctionForm.school_system')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" wire:click="closeCorrectionModal">
              Cancel
            </button>
            <button type="button" class="btn btn-primary" wire:click="saveCorrection(false)">
              Save
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show"></div>
  @endif

  @if ($contactCorrectionReviewId)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="contact-correction-modal-title" wire:key="intake-contact-correction-modal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title" id="contact-correction-modal-title">Edit Parent Contact</h5>
              <p class="text-body-secondary mb-0 small">Change the parent contact once for the whole review submission, then re-check all active child rows.</p>
            </div>
            <button type="button" class="btn-close" aria-label="Close" wire:click="closeContactCorrectionModal"></button>
          </div>
          <div class="modal-body">
            @error('contactCorrectionForm.general')
              <div class="alert alert-danger" role="alert">
                {{ $message }}
              </div>
            @enderror
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label" for="contact-correction-parent-email">Parent Email</label>
                <input id="contact-correction-parent-email" type="email" class="form-control" wire:model.live.debounce.300ms="contactCorrectionForm.parent_email">
                @error('contactCorrectionForm.parent_email')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12">
                <label class="form-label" for="contact-correction-parent-phone">Parent Phone</label>
                <input id="contact-correction-parent-phone" type="text" class="form-control" wire:model.live.debounce.300ms="contactCorrectionForm.parent_phone">
                @error('contactCorrectionForm.parent_phone')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" wire:click="closeContactCorrectionModal">
              Cancel
            </button>
            <button type="button" class="btn btn-primary" wire:click="saveContactCorrection">
              Save & Re-check
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show"></div>
  @endif
</div>
