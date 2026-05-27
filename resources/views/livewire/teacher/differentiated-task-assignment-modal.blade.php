<div>
  @if($show && $task)
    <div class="modal fade show d-block dt-assignment-modal" data-dt-assignment-build="assign-daily-session-resolver-20260430" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="dt-assignment-title" wire:keydown.escape="close" x-data x-init="setTimeout(() => $el.focus(), 50)">
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable dt-assignment-dialog">
        <div class="modal-content">
          <div class="modal-header d-flex justify-content-between align-items-start gap-3">
          <div>
            <div class="d-flex align-items-center gap-2">
              <h5 id="dt-assignment-title" class="card-title mb-0">
                {{ $activeVersion ? 'Manage students for '.$activeVersion->display_name : 'Manage students' }}
              </h5>
              <details class="intake-info intake-info--inline">
                <summary class="intake-info__trigger" aria-label="About version student manager">
                  <i class="icon-base ti tabler-info-circle icon-18px"></i>
                </summary>
                <div class="intake-info__panel">
                  Selected students become assigned to this version on save. Future-only changes apply to new generated tasks.
                </div>
              </details>
            </div>
            <div class="small text-muted mt-1">{{ $task->title }}</div>
          </div>
          <button type="button" class="btn btn-text-secondary rounded-pill btn-icon" wire:click="close" aria-label="Close">
            <i class="ti tabler-x"></i>
          </button>
          </div>

          <div class="modal-body">
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0 ps-3">
                @foreach($errors->all() as $message)
                  <li>{{ $message }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <div class="row g-3 align-items-end mb-4">
            <div class="col-12 col-lg-8">
              <label class="form-label" for="dt-assignment-search">Search students</label>
              <input type="text"
                     id="dt-assignment-search"
                     class="form-control"
                     wire:model.live.debounce.300ms="search"
                     placeholder="Search by student, parent, or ID">
            </div>
            <div class="col-12 col-lg-4">
              <label class="form-label">Selected version</label>
              <div class="form-control bg-label-light text-truncate">{{ $activeVersion?->display_name ?? 'No version' }}</div>
            </div>
          </div>

          @if($activeVersion)
            <div class="row g-4">
              @foreach($sections as $sectionKey => $section)
                <div class="col-12">
                  <div class="card automated-task-section-card h-100 shadow-none dt-assignment-section">
                    <div class="card-header border-0 pb-0">
                      <div class="automated-task-section-header">
                        <div class="automated-task-section-title-group">
                          <h6 class="card-title automated-task-section-title">{{ $section['title'] }}</h6>
                          <details class="intake-info intake-info--inline">
                            <summary class="intake-info__trigger" aria-label="About {{ strtolower($section['title']) }}">
                              <i class="icon-base ti tabler-info-circle icon-16px"></i>
                            </summary>
                            <div class="intake-info__panel">{{ $section['description'] }}</div>
                          </details>
                        </div>
                        <span class="badge bg-label-primary rounded-pill automated-task-section-count px-3 py-2">{{ count($section['rows']) }}</span>
                      </div>
                    </div>
                    <div class="card-body pt-0">
                      @if(empty($section['rows']))
                        <div class="text-muted small py-2">No students match this section right now.</div>
                      @else
                        <div class="automated-task-student-grid">
                          @foreach($section['rows'] as $row)
                            @php
                              $studentId = $row['student_id'];
                              $selected = (bool) data_get($selectedStudentIds, $studentId, false);
                              $metaLine = $row['class_name'].' | #'.$studentId;
                            @endphp
                            <label class="automated-task-student-card {{ $selected ? 'is-selected' : '' }}" wire:key="dt-assignment-{{ $sectionKey }}-{{ $studentId }}">
                              <div class="automated-task-student-card-header">
                                <div class="automated-task-student-copy">
                                  <div class="fw-semibold automated-task-student-name" title="{{ $row['student_with_father'] }}">{{ $row['display_name'] }}</div>
                                  <div class="small text-muted automated-task-student-meta" title="{{ $metaLine }}">{{ $metaLine }}</div>
                                </div>
                                <input type="checkbox" class="form-check-input mt-1" wire:model.live="selectedStudentIds.{{ $studentId }}">
                              </div>

                              <div class="d-flex flex-wrap gap-1 mt-2">
                                @if($sectionKey === 'assigned_elsewhere')
                                  <span class="badge bg-label-warning text-truncate" style="max-width: 100%;" title="{{ $row['current_version_name'] }}">{{ $row['current_version_name'] }}</span>
                                @endif
                                @if($row['delivered_today'])
                                  <span class="badge bg-label-info">Delivered today - future only</span>
                                @endif
                              </div>
                            </label>
                          @endforeach
                        </div>
                      @endif
                    </div>
                  </div>
                  </div>
              @endforeach
            </div>
          @endif
          </div>

          <div class="modal-footer d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-label-secondary" wire:click="close">Close</button>
          <button type="button" class="btn btn-primary" wire:click="saveBulk" wire:loading.attr="disabled" wire:target="saveBulk" @disabled(! $activeVersion)>
            <span wire:loading.remove wire:target="saveBulk">Save</span>
            <span wire:loading wire:target="saveBulk">
              <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
              Saving
            </span>
          </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show"></div>
  @endif
</div>

@push('styles')
  @once
    <style>
      .dt-assignment-modal {
        z-index: 1095;
      }

      .dt-assignment-dialog {
        max-width: min(88rem, calc(100vw - 2rem));
      }

      .dt-assignment-section {
        border: 1px solid rgba(67, 89, 113, 0.12);
      }

      .dt-assignment-modal .automated-task-section-header {
        align-items: flex-start;
        display: flex;
        gap: 0.75rem;
        justify-content: space-between;
        margin-bottom: 0.75rem;
      }

      .dt-assignment-modal .automated-task-section-title-group {
        align-items: center;
        display: flex;
        flex: 1 1 auto;
        gap: 0.5rem;
        min-width: 0;
      }

      .dt-assignment-modal .automated-task-section-title {
        margin-bottom: 0;
        min-width: 0;
      }

      .dt-assignment-modal .automated-task-section-count {
        flex: 0 0 auto;
        margin-top: 0;
      }

      .dt-assignment-modal .automated-task-student-grid {
        display: grid;
        gap: 0.875rem;
        grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr));
      }

      .dt-assignment-modal .automated-task-student-card {
        background: var(--bs-body-bg);
        border: 1px solid rgba(67, 89, 113, 0.14);
        border-radius: 1rem;
        cursor: pointer;
        display: block;
        height: 100%;
        min-width: 0;
        overflow: hidden;
        padding: 1rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
      }

      .dt-assignment-modal .automated-task-student-card-header {
        align-items: flex-start;
        display: flex;
        gap: 0.75rem;
        justify-content: space-between;
        min-width: 0;
      }

      .dt-assignment-modal .automated-task-student-copy {
        flex: 1 1 auto;
        min-width: 0;
        width: 0;
      }

      .dt-assignment-modal .automated-task-student-name,
      .dt-assignment-modal .automated-task-student-meta {
        display: block;
        max-width: 100%;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .dt-assignment-modal .automated-task-student-card.is-selected {
        border-color: rgba(var(--bs-primary-rgb), 0.45);
        box-shadow: 0 0.5rem 1.25rem rgba(var(--bs-primary-rgb), 0.12);
        transform: translateY(-1px);
      }

      .dt-assignment-modal .automated-task-student-card .form-check-input {
        flex: 0 0 auto;
        height: 1.05rem;
        width: 1.05rem;
      }

      .dt-assignment-modal .intake-info {
        display: inline-block;
        position: relative;
      }

      .dt-assignment-modal .intake-info--inline {
        vertical-align: middle;
      }

      .dt-assignment-modal .intake-info__trigger {
        align-items: center;
        background: transparent;
        border: 0;
        border-radius: 999px;
        color: var(--bs-secondary-color);
        cursor: pointer;
        display: inline-flex;
        justify-content: center;
        list-style: none;
        min-height: 1.75rem;
        min-width: 1.75rem;
        padding: 0.125rem;
      }

      .dt-assignment-modal .intake-info__trigger::-webkit-details-marker {
        display: none;
      }

      .dt-assignment-modal .intake-info[open] .intake-info__trigger,
      .dt-assignment-modal .intake-info__trigger:hover {
        color: var(--bs-primary);
      }

      .dt-assignment-modal .intake-info__panel {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.14);
        color: var(--bs-body-color);
        font-size: 0.8125rem;
        line-height: 1.45;
        max-width: min(24rem, calc(100vw - 2rem));
        min-width: 220px;
        padding: 0.625rem 0.75rem;
        position: absolute;
        right: 0;
        top: calc(100% + 0.25rem);
        z-index: 1100;
      }

      @media (max-width: 374.98px) {
        .dt-assignment-modal .automated-task-student-grid {
          grid-template-columns: minmax(0, 1fr);
        }

        .dt-assignment-modal .automated-task-student-card {
          padding: 0.875rem;
        }
      }
    </style>
  @endonce
@endpush
