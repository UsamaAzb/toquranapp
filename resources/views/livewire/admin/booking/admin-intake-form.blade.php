<div>
  @if ($isOpen)
    <div
      class="modal fade show d-block admin-intake-modal"
      tabindex="-1"
      role="dialog"
      aria-modal="true"
      style="background: rgba(17, 24, 39, 0.35); overflow-y: auto;"
    >
      <div class="modal-dialog modal-xl modal-dialog-scrollable admin-intake-modal__dialog" style="min-height: calc(100vh - 3.5rem);">
        <div class="modal-content">
          <div class="modal-header">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <h5 class="modal-title mb-0">New Intake</h5>
              <details class="intake-info intake-info--inline">
                <summary class="intake-info__trigger" aria-label="Open intake info">
                  <i class="icon-base ti tabler-info-circle icon-18px"></i>
                </summary>
                <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                  Create intake for a new family or add a genuinely new child to an existing family.
                </div>
              </details>
            </div>
            <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
          </div>

          <form wire:submit="save">
            <div class="modal-body admin-intake-modal__body" data-admin-intake-modal-body style="max-height: calc(100vh - 13rem); overflow-y: auto;">
              @error('form')
                <div class="alert alert-danger">{{ $message }}</div>
              @enderror

              <div class="row g-4">
                <div class="col-12">
                  <div class="card border shadow-none h-100">
                    <div class="card-body">
                      <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3 mb-4">
                        <div class="min-w-0">
                          <span class="badge bg-label-primary mb-2">Parent</span>
                          <div class="d-flex flex-wrap align-items-center gap-2">
                            <h6 class="mb-0">Shared family contact</h6>
                            <details class="intake-info intake-info--inline">
                              <summary class="intake-info__trigger" aria-label="Open parent contact info">
                                <i class="icon-base ti tabler-info-circle icon-18px"></i>
                              </summary>
                              <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                                Use at least one parent contact so duplicate and sibling detection can run before any write.
                              </div>
                            </details>
                          </div>
                        </div>
                      </div>

                      <div class="row g-3">
                        <div class="col-12">
                          <label class="form-label d-block">Family Mode</label>
                          <div class="d-flex flex-wrap gap-2">
                            <label class="btn btn-sm {{ $intakeMode === 'new' ? 'btn-primary' : 'btn-label-primary' }}">
                              <input type="radio" class="d-none" wire:model.live="intakeMode" value="new">
                              New family
                            </label>
                            <label class="btn btn-sm {{ $intakeMode === 'existing' ? 'btn-primary' : 'btn-label-primary' }}">
                              <input type="radio" class="d-none" wire:model.live="intakeMode" value="existing">
                              Existing family
                            </label>
                          </div>
                        </div>

                        @if ($intakeMode === 'existing')
                          <div class="col-12">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                              <label class="form-label mb-0">Find Existing Family</label>
                              <details class="intake-info intake-info--inline">
                                <summary class="intake-info__trigger" aria-label="Open existing family search info">
                                  <i class="icon-base ti tabler-info-circle icon-18px"></i>
                                </summary>
                                <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                                  Search by parent phone, email, or name, then select a family to auto-fill the parent contact.
                                </div>
                              </details>
                            </div>
                            <input
                              type="text"
                              class="form-control @error('selectedExistingBookingId') is-invalid @enderror"
                              wire:model.live.debounce.250ms="existingFamilySearch"
                              placeholder="Search by parent phone, email, or name"
                            >
                            @error('selectedExistingBookingId')
                              <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                          </div>

                          @if ($selectedExistingFamilySummary)
                            <div class="col-12">
                              <div class="alert alert-primary d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-0 admin-intake-family-card">
                                <div class="min-w-0 w-100">
                                  <div class="fw-semibold admin-intake-break">{{ $selectedExistingFamilySummary['label'] }}</div>
                                  @if ($selectedExistingFamilySummary['sublabel'] !== '')
                                    <div class="small text-body-secondary admin-intake-break">{{ $selectedExistingFamilySummary['sublabel'] }}</div>
                                  @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-label-secondary admin-intake-family-card__action" wire:click="clearExistingFamilySelection">
                                  Change Family
                                </button>
                              </div>
                            </div>
                          @elseif ($existingFamilySearch !== '' || count($existingFamilyOptions) > 0)
                            <div class="col-12">
                              <div class="list-group admin-intake-family-results">
                                @forelse ($existingFamilyOptions as $existingFamilyOption)
                                  <button
                                    type="button"
                                    class="list-group-item list-group-item-action admin-intake-family-results__item"
                                    wire:click="selectExistingFamily({{ $existingFamilyOption['id'] }})"
                                    wire:key="family-option-{{ $existingFamilyOption['id'] }}"
                                  >
                                    <div class="fw-semibold admin-intake-break">{{ $existingFamilyOption['label'] }}</div>
                                    @if ($existingFamilyOption['sublabel'] !== '')
                                      <div class="small text-body-secondary admin-intake-break">{{ $existingFamilyOption['sublabel'] }}</div>
                                    @endif
                                  </button>
                                @empty
                                  <div class="list-group-item text-body-secondary">
                                    No matching family found. Check the phone or email, or switch back to New family.
                                  </div>
                                @endforelse
                              </div>
                            </div>
                          @endif
                        @endif

                        <div class="col-md-4">
                          <label class="form-label">Parent Name</label>
                          <input
                            type="text"
                            class="form-control @error('parent_name') is-invalid @enderror"
                            wire:model.blur="parentName"
                            @disabled($intakeMode === 'existing')
                          >
                          @error('parent_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                        <div class="col-md-4">
                          <label class="form-label">Parent Email</label>
                          <input
                            type="email"
                            class="form-control @error('parent_email') is-invalid @enderror"
                            wire:model.blur="parentEmail"
                            @disabled($intakeMode === 'existing')
                          >
                          @error('parent_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                        <div class="col-md-4">
                          <label class="form-label">Parent Phone</label>
                          <input
                            type="text"
                            class="form-control @error('parent_phone') is-invalid @enderror"
                            wire:model.blur="parentPhone"
                            @disabled($intakeMode === 'existing')
                          >
                          @error('parent_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                        <div class="col-12">
                          <label class="form-label">Notes</label>
                          <textarea rows="3" class="form-control @error('notes') is-invalid @enderror" wire:model.blur="notes"></textarea>
                          @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-12">
                  <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-3">
                    <div class="min-w-0">
                      <span class="badge bg-label-warning mb-2">Children</span>
                      <div class="d-flex flex-wrap align-items-center gap-2">
                        <h6 class="mb-0">Child rows</h6>
                        <details class="intake-info intake-info--inline">
                          <summary class="intake-info__trigger" aria-label="Open child rows info">
                            <i class="icon-base ti tabler-info-circle icon-18px"></i>
                          </summary>
                          <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                            Each child is checked independently for duplicate, repeat, mismatch, and sibling routing.
                          </div>
                        </details>
                      </div>
                    </div>
                    <button type="button" class="btn btn-label-primary" wire:click="addChild">
                      Add Child
                    </button>
                  </div>

                  @error('children')
                    <div class="alert alert-danger">{{ $message }}</div>
                  @enderror

                  <div class="d-flex flex-column gap-3">
                    @foreach ($children as $index => $child)
                      <div class="card border shadow-none" wire:key="admin-intake-child-{{ $index }}" data-admin-intake-child-card>
                        <div class="card-body">
                          <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div class="min-w-0">
                              <div class="d-flex flex-wrap align-items-center gap-2">
                                <h6 class="mb-0">Child {{ $index + 1 }}</h6>
                                <details class="intake-info intake-info--inline">
                                  <summary class="intake-info__trigger" aria-label="Open child row info">
                                    <i class="icon-base ti tabler-info-circle icon-18px"></i>
                                  </summary>
                                  <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                                    Primary identity first, then grade, school system, and service interests.
                                  </div>
                                </details>
                              </div>
                            </div>
                            @if (count($children) > 1)
                              <button type="button" class="btn btn-sm btn-label-danger" wire:click="removeChild({{ $index }})">
                                Remove
                              </button>
                            @endif
                          </div>

                          <div class="row g-3">
                            <div class="col-md-4">
                              <label class="form-label">Child Name</label>
                              <input type="text" class="form-control @error("children.$index.child_name") is-invalid @enderror" wire:model.blur="children.{{ $index }}.child_name">
                              @error("children.$index.child_name")
                                <div class="invalid-feedback">{{ $message }}</div>
                              @enderror
                            </div>
                            <div class="col-md-2">
                              <label class="form-label">Age</label>
                              <input type="text" class="form-control @error("children.$index.child_age") is-invalid @enderror" wire:model.blur="children.{{ $index }}.child_age">
                              @error("children.$index.child_age")
                                <div class="invalid-feedback">{{ $message }}</div>
                              @enderror
                            </div>
                            <div class="col-md-3">
                              <label class="form-label">Grade</label>
                              <select class="form-select @error("children.$index.child_grade") is-invalid @enderror" wire:model.live="children.{{ $index }}.child_grade">
                                <option value="">Select grade</option>
                                @foreach ($gradeTitles as $gradeId => $gradeTitle)
                                  <option value="{{ $gradeId }}">{{ $gradeTitle }}</option>
                                @endforeach
                              </select>
                              @error("children.$index.child_grade")
                                <div class="invalid-feedback">{{ $message }}</div>
                              @enderror
                            </div>
                            <div class="col-md-6">
                              <label class="form-label">School System</label>
                              <select class="form-select @error("children.$index.school_system") is-invalid @enderror" wire:model.live="children.{{ $index }}.school_system">
                                <option value="">Select school system</option>
                                @foreach ($schoolSystemOptions as $schoolSystemValue => $schoolSystemLabel)
                                  <option value="{{ $schoolSystemValue }}">{{ $schoolSystemLabel }}</option>
                                @endforeach
                              </select>
                              @error("children.$index.school_system")
                                <div class="invalid-feedback">{{ $message }}</div>
                              @enderror
                            </div>
                            <div class="col-12">
                              <label class="form-label">Service Interests</label>
                              <div class="admin-intake-service-grid">
                                @forelse ($serviceOptions as $serviceOption)
                                  <label class="form-check m-0 admin-intake-service-option" wire:key="admin-intake-child-{{ $index }}-service-{{ $serviceOption['value'] }}">
                                    <input
                                      class="form-check-input"
                                      type="checkbox"
                                      value="{{ $serviceOption['value'] }}"
                                      wire:model.live="children.{{ $index }}.service_interests"
                                    >
                                    <span class="form-check-label admin-intake-break">{{ $serviceOption['label'] }}</span>
                                  </label>
                                @empty
                                  <div class="alert alert-warning mb-0">
                                    No active child-facing services are configured.
                                  </div>
                                @endforelse
                              </div>
                              @error("children.$index.service_interests")
                                <div class="text-danger small mt-1">{{ $message }}</div>
                              @enderror
                              @error("children.$index.service_interests.*")
                                <div class="text-danger small mt-1">{{ $message }}</div>
                              @enderror
                            </div>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer admin-intake-modal__footer">
              <button type="button" class="btn btn-label-secondary admin-intake-modal__footer-btn" wire:click="closeModal">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary admin-intake-modal__footer-btn">
                Save Intake
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal-backdrop fade show"></div>
  @endif

  @once
    @push('styles')
      <style>
        .admin-intake-modal__dialog {
          max-width: min(1140px, calc(100vw - 1.5rem));
        }

        .admin-intake-modal .modal-content {
          max-height: calc(100vh - 3.5rem);
          overflow: hidden;
        }

        .admin-intake-modal__body {
          overscroll-behavior: contain;
          padding-bottom: 5.5rem;
        }

        .admin-intake-modal__footer {
          background: #fff;
          gap: 0.75rem;
          position: sticky;
          bottom: 0;
          padding-top: 0.25rem;
          z-index: 2;
        }

        .admin-intake-modal__footer > * {
          margin: 0;
        }

        .admin-intake-break {
          overflow-wrap: anywhere;
          word-break: break-word;
        }

        .min-w-0 {
          min-width: 0;
        }

        .admin-intake-family-card,
        .admin-intake-family-results__item {
          min-width: 0;
        }

        .admin-intake-family-results__item {
          text-align: left;
        }

        .admin-intake-family-card__action {
          flex-shrink: 0;
        }

        .admin-intake-service-grid {
          display: grid;
          gap: 0.75rem 1rem;
          grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .admin-intake-service-option {
          align-items: flex-start;
          display: flex;
          gap: 0.5rem;
          min-width: 0;
        }

        .admin-intake-service-option .form-check-input {
          flex-shrink: 0;
          margin: 0.2rem 0 0;
        }

        .admin-intake-service-option .form-check-label {
          min-width: 0;
        }

        .admin-intake-modal__body [data-admin-intake-child-card]:last-child {
          margin-bottom: 0.5rem;
        }

        @media (max-width: 767.98px) {
          .admin-intake-modal {
            padding: 0.5rem 0;
          }

          .admin-intake-modal__dialog {
            margin: 0.5rem;
            max-width: calc(100vw - 1rem);
            min-height: calc(100vh - 1rem) !important;
          }

          .admin-intake-modal .modal-content {
            max-height: calc(100vh - 1rem);
          }

          .admin-intake-modal__body {
            max-height: calc(100vh - 10rem) !important;
            padding-bottom: 7rem;
          }

          .admin-intake-family-card__action,
          .admin-intake-modal__footer-btn {
            width: 100%;
          }

          .admin-intake-modal__footer {
            align-items: stretch;
            flex-direction: column-reverse;
            gap: 0.75rem;
            justify-content: stretch;
          }

          .admin-intake-service-grid {
            grid-template-columns: 1fr;
          }
        }
      </style>
    @endpush

    @push('scripts')
      <script>
        window.addEventListener('admin-intake-form:child-added', () => {
          requestAnimationFrame(() => {
            const body = document.querySelector('[data-admin-intake-modal-body]');
            const cards = body ? body.querySelectorAll('[data-admin-intake-child-card]') : [];
            const lastCard = cards.length ? cards[cards.length - 1] : null;

            if (body && lastCard) {
              const nextTop = lastCard.offsetTop - body.offsetTop - 16;
              body.scrollTo({ top: Math.max(nextTop, 0), behavior: 'smooth' });
            }
          });
        });
      </script>
    @endpush
  @endonce
</div>
