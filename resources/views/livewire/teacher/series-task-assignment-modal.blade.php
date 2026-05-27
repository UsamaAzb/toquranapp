<div>
  <style>
    .series-assignment-modal,
    .series-assignment-modal * {
      min-width: 0;
    }

    .series-assignment-title,
    .series-assignment-subtitle,
    .series-assignment-name,
    .series-assignment-meta,
    .series-assignment-state {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .series-assignment-section {
      border-block-start: 1px solid var(--bs-border-color);
      padding-block-start: 1rem;
    }

    .series-assignment-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: .75rem;
    }

    .series-assignment-row {
      border: 1px solid var(--bs-border-color);
      border-radius: .5rem;
      padding: .875rem;
      background: var(--bs-paper-bg);
    }

    .series-assignment-row.is-here {
      border-color: rgba(var(--bs-success-rgb), .35);
      background: rgba(var(--bs-success-rgb), .06);
    }

    .series-assignment-row.is-unassigned {
      border-color: rgba(var(--bs-info-rgb), .32);
      background: rgba(var(--bs-info-rgb), .055);
    }

    .series-assignment-row.is-elsewhere {
      border-color: rgba(var(--bs-warning-rgb), .38);
      background: rgba(var(--bs-warning-rgb), .07);
    }

    @media (max-width: 991.98px) {
      .series-assignment-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 575.98px) {
      .series-assignment-modal .modal-dialog {
        margin: .5rem;
      }

      .series-assignment-modal .modal-header,
      .series-assignment-modal .modal-body,
      .series-assignment-modal .modal-footer {
        padding-inline: .875rem;
      }

      .series-assignment-grid {
        grid-template-columns: minmax(0, 1fr);
      }

      .series-assignment-modal .modal-footer {
        align-items: stretch;
        flex-direction: column-reverse;
      }

      .series-assignment-modal .modal-footer .btn {
        inline-size: 100%;
        min-block-size: 2.75rem;
      }
    }
  </style>

  @if($show && $task && $activeVersion)
    <div class="modal fade show d-block series-assignment-modal" tabindex="-1" role="dialog" aria-modal="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <div class="min-w-0">
              <h5 class="modal-title series-assignment-title" title="{{ $task->title }}">{{ $task->title }}</h5>
              <div class="text-body-secondary small series-assignment-subtitle" title="{{ $activeVersion->display_name }}">
                {{ $activeVersion->display_name }}
              </div>
            </div>
            <button type="button" class="btn-close" wire:click="close" wire:loading.attr="disabled" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            @error('assignment')
              <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            <div class="row g-3 align-items-end mb-4">
              <div class="col-12 col-md-6">
                <label class="form-label">Search students</label>
                <input type="search" class="form-control" wire:model.live.debounce.300ms="search">
              </div>
              <div class="col-12 col-md-6">
                <div class="alert alert-info mb-0 py-2 d-flex gap-2">
                  <i class="ti tabler-info-circle mt-1"></i>
                  <span>Changes apply to future generation. Delivered work stays unchanged.</span>
                </div>
              </div>
            </div>

            <div class="d-grid gap-4">
              @foreach($sections as $key => $section)
                @php
                  $tone = match ($key) {
                    'assigned_here' => 'success',
                    'assigned_elsewhere' => 'warning',
                    default => 'info',
                  };
                  $rowClass = match ($key) {
                    'assigned_here' => 'is-here',
                    'assigned_elsewhere' => 'is-elsewhere',
                    default => 'is-unassigned',
                  };
                @endphp
                <section class="series-assignment-section" wire:key="series-assignment-section-{{ $key }}">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div class="min-w-0">
                      <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0">{{ $section['title'] }}</h6>
                        <span class="badge bg-label-{{ $tone }}">{{ count($section['rows']) }}</span>
                      </div>
                      <div class="text-body-secondary small">{{ $section['description'] }}</div>
                    </div>
                  </div>

                  <div class="series-assignment-grid">
                    @forelse($section['rows'] as $row)
                      <div class="series-assignment-row {{ $rowClass }}" wire:key="series-assignment-student-{{ $key }}-{{ $row['student_id'] }}">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                          <div class="min-w-0">
                            <div class="fw-medium series-assignment-name" title="{{ $row['student_with_father'] }}">{{ $row['student_with_father'] }}</div>
                            <div class="text-body-secondary small series-assignment-meta" title="{{ $row['class_name'] }}">{{ $row['class_name'] }}</div>
                          </div>
                          <div class="form-check flex-shrink-0">
                            <input
                              class="form-check-input"
                              type="checkbox"
                              wire:model.live="selectedStudentIds.{{ $row['student_id'] }}"
                              aria-label="Select {{ $row['display_name'] }}">
                          </div>
                        </div>

                        @if($key === 'assigned_elsewhere')
                          <div class="mt-3">
                            <span class="badge bg-label-warning series-assignment-state" title="{{ $row['current_version_name'] }}">
                              {{ $row['current_version_name'] }}
                            </span>
                          </div>
                        @elseif($row['delivered_today'])
                          <div class="text-warning small mt-3">Future changes apply tomorrow.</div>
                        @endif

                        <label class="form-label small mt-3">Start / next item</label>
                        <select class="form-select form-select-sm" wire:model.live="startPositions.{{ $row['student_id'] }}">
                          @foreach($positionOptions as $item)
                            <option value="{{ $item->sequence_position }}">
                              {{ $item->sequence_position }}. {{ $item->library_title_snapshot }}
                            </option>
                          @endforeach
                        </select>
                      </div>
                    @empty
                      <div class="bg-lighter rounded p-3 text-body-secondary small">No students in this section.</div>
                    @endforelse
                  </div>
                </section>
              @endforeach
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" wire:click="close" wire:loading.attr="disabled">Cancel</button>
            <button
              type="button"
              class="btn btn-primary"
              wire:click="saveBulk"
              wire:loading.attr="disabled"
              wire:target="saveBulk">
              <span class="spinner-border spinner-border-sm me-1" wire:loading wire:target="saveBulk" aria-hidden="true"></span>
              <i class="ti tabler-device-floppy me-1" wire:loading.remove wire:target="saveBulk"></i>
              Save
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show"></div>
  @endif
</div>
