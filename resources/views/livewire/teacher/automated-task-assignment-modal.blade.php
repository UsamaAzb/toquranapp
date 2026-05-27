<div>
  @if($show && $template)
    <div class="automated-task-overlay" tabindex="-1" wire:keydown.escape="close" x-data x-init="setTimeout(() => $el.focus(), 50)">
      <div class="card automated-task-overlay-card" style="max-width: min(88rem, calc(100vw - 2rem));" role="dialog" aria-modal="true" aria-labelledby="assignment-modal-title">
        <div class="card-header d-flex justify-content-between align-items-center gap-3">
          <div>
            <div class="d-flex align-items-center gap-2">
              <h5 id="assignment-modal-title" class="card-title mb-0">
                {{ $activeVersion ? 'Manage students for '.$activeVersion->display_name : 'Manage students' }}
              </h5>
              <details class="intake-info intake-info--inline">
                <summary class="intake-info__trigger" aria-label="About version student manager">
                  <i class="icon-base ti tabler-info-circle icon-18px"></i>
                </summary>
                <div class="intake-info__panel">
                  Selected unassigned students receive this version. Selected students assigned to another version MOVE here. Unselecting students already assigned here removes them from this version for future generation.
                </div>
              </details>
            </div>
            <div class="small text-muted mt-1">{{ $template->title }}</div>
          </div>
          <button type="button" class="btn btn-text-secondary rounded-pill btn-icon" wire:click="close" aria-label="Close">
            <i class="ti tabler-x"></i>
          </button>
        </div>

        <div class="card-body">
          @error('bulk_assignment')
            <div class="alert alert-warning mb-4">{{ $message }}</div>
          @enderror

          @if(! $activeVersion)
            <div class="alert alert-warning mb-4">
              <div class="fw-semibold mb-1">Add a version first</div>
              <div class="small mb-0">
                This template needs at least one version before version-based bulk assignment can be used.
              </div>
            </div>
          @elseif(! $activeVersionReady)
            <div class="alert alert-warning mb-4">
              <div class="fw-semibold mb-1">This version is not ready for new assignments yet</div>
              <div class="small mb-0">
                You can still review current membership and remove students from this version, but assigning or moving students into it will stay blocked until the version has meaningful content.
              </div>
            </div>
          @endif

          <div class="row g-3 align-items-end mb-4">
            <div class="col-12 col-xl-7">
              <label class="form-label" for="assignment-search">Search students</label>
              <input type="text" id="assignment-search" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Search by student, parent, class, or ID">
            </div>
            <div class="col-12 col-xl-5">
              <label class="form-label">Current version</label>
              <div class="form-control bg-label-light d-flex align-items-center justify-content-between gap-2">
                <span class="text-truncate">{{ $activeVersion?->display_name ?? 'No version yet' }}</span>
                @if($activeVersion)
                  @include('livewire.teacher.automated-task-validation-badge', ['passes' => $activeVersionReady, 'messages' => $activeVersionReady ? [] : ['This version still needs meaningful content before new assignments can be saved.'], 'compact' => true])
                @endif
              </div>
            </div>
          </div>

          @if($activeVersion)
            <div class="row g-4">
              @foreach($bulkSections as $sectionKey => $section)
                <div class="col-12">
                <div class="card automated-task-section-card h-100 shadow-none">
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
                            <label class="automated-task-student-card {{ $selected ? 'is-selected' : '' }}" wire:key="bulk-student-card-{{ $sectionKey }}-{{ $studentId }}">
                              <div class="automated-task-student-card-header">
                                <div class="automated-task-student-copy">
                                  <div class="fw-semibold automated-task-student-name" title="{{ $row['student_with_father'] }}">{{ $row['display_name'] }}</div>
                                  <div class="small text-muted automated-task-student-meta" title="{{ $metaLine }}">{{ $metaLine }}</div>
                                  @if($sectionKey === 'assigned_elsewhere')
                                    <div class="small text-muted automated-task-student-meta" title="{{ $row['current_version_name'] }}">
                                      Current: {{ $row['current_version_name'] }}
                                    </div>
                                  @endif
                                  @if($row['duplicate_name'])
                                    <div class="small text-muted automated-task-student-meta" title="{{ $row['parent_name'] }}">{{ $row['parent_name'] }}</div>
                                  @endif
                                </div>
                                @if($sectionKey === 'assigned_elsewhere')
                                  <span class="badge bg-label-warning rounded-pill me-2">MOVE</span>
                                @endif
                                <input
                                  class="form-check-input mt-1"
                                  type="checkbox"
                                  wire:model.live="selectedStudentIds.{{ $studentId }}">
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

        <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
          <div></div>
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-text-secondary rounded-pill" wire:click="close">Close</button>
            <button
              type="button"
              class="btn btn-primary rounded-pill"
              wire:click="saveBulk"
              wire:loading.attr="disabled"
              wire:target="saveBulk"
              @disabled(! $activeVersion)>
              <span wire:loading.remove wire:target="saveBulk">Save</span>
              <span wire:loading wire:target="saveBulk"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
