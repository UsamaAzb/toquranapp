<div>
  @if($show && $template)
    <div class="automated-task-overlay" tabindex="-1" wire:keydown.escape="close" x-data x-init="setTimeout(() => $el.focus(), 50)">
      <div class="card automated-task-overlay-card" style="max-width: min(82rem, calc(100vw - 2rem));" role="dialog" aria-modal="true" aria-labelledby="history-panel-title">
        <div class="card-header d-flex justify-content-between align-items-center gap-3">
          <div class="d-flex align-items-center gap-2">
            <h5 id="history-panel-title" class="card-title mb-0">Generated history</h5>
            <details class="intake-info intake-info--inline">
              <summary class="intake-info__trigger" aria-label="About generated history">
                <i class="icon-base ti tabler-info-circle icon-18px"></i>
              </summary>
              <div class="intake-info__panel">
                Version changes apply to future generations only. This panel shows the student-facing snapshots as they were originally generated.
              </div>
            </details>
          </div>
          <button type="button" class="btn btn-text-secondary rounded-pill btn-icon" wire:click="close" aria-label="Close generated history">
            <i class="ti tabler-x"></i>
          </button>
        </div>

        <div class="card-body">
          <details class="mb-4">
            <summary class="fw-semibold text-primary">How this history works</summary>
            <div class="small text-muted mt-2">
              This panel reads the generated class session, task, pivot, and copied attachment rows. It does not read mutable template descriptions or current version names for display.
            </div>
          </details>

          <div class="row g-4">
            <div class="col-12 col-xl-4">
              <div class="card bg-label-light shadow-none border h-100">
                <div class="card-header d-flex align-items-center gap-2">
                  <h6 class="card-title mb-0">Students with generated rows</h6>
                  <details class="intake-info intake-info--inline">
                    <summary class="intake-info__trigger" aria-label="About student list">
                      <i class="icon-base ti tabler-info-circle icon-18px"></i>
                    </summary>
                    <div class="intake-info__panel">
                      Open a student to inspect frozen snapshots.
                    </div>
                  </details>
                </div>
                <div class="card-body">
                  <label class="form-label">Search history</label>
                  <input type="text" class="form-control mb-3" wire:model.live.debounce.300ms="search" placeholder="Search student, parent, or ID">

                  <div class="list-group">
                    @forelse($students as $student)
                      <button
                        type="button"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-start gap-3 {{ $selectedStudent?->id === $student->id ? 'active' : '' }}"
                        wire:click="selectStudent({{ $student->id }})"
                        wire:key="history-student-{{ $student->id }}">
                        <span>
                          <span class="fw-semibold d-block">{{ $student->display_name }}</span>
                          <small>{{ $student->parent?->display_name ?? 'No parent linked' }} | {{ $student->currentClass?->title ?? 'No current class' }}</small>
                        </span>
                        <span class="badge bg-label-secondary rounded-pill">#{{ $student->id }}</span>
                      </button>
                    @empty
                      <div class="text-muted text-center py-4">No generated rows yet. Publish this template and wait for the scheduler to produce student-facing tasks.</div>
                    @endforelse
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-xl-8">
              <div class="card shadow-none border h-100">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between gap-2">
                  <div class="d-flex align-items-center gap-2">
                    <h6 class="card-title mb-0">{{ $selectedStudent?->display_name ?? 'No student selected' }}</h6>
                    <details class="intake-info intake-info--inline">
                      <summary class="intake-info__trigger" aria-label="About snapshots">
                        <i class="icon-base ti tabler-info-circle icon-18px"></i>
                      </summary>
                      <div class="intake-info__panel">
                        Read-only generated snapshots for {{ $template->title }}.
                      </div>
                    </details>
                  </div>
                  <span class="badge bg-label-info rounded-pill align-self-start">Future changes only</span>
                </div>
                <div class="card-body">
                  @if($sessions && $sessions->count() > 0)
                    <div class="d-flex flex-column gap-3">
                      @foreach($sessions as $session)
                        <div class="border rounded-3 p-3" wire:key="history-session-{{ $session->id }}">
                          <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                            <div>
                              <div class="fw-semibold">{{ optional($session->generated_for_date)->format('Y-m-d') ?: $session->generated_for_date }}</div>
                              <div class="small text-muted">Session #{{ $session->id }}</div>
                            </div>
                            <span class="badge bg-label-secondary rounded-pill align-self-start">Read-only</span>
                          </div>

                          <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                              <thead>
                                <tr>
                                  <th>Task snapshot</th>
                                  <th>Version snapshot</th>
                                  <th>Copied attachments</th>
                                  <th>Status</th>
                                </tr>
                              </thead>
                              <tbody>
                                @foreach($session->tasks as $task)
                                  @include('livewire.teacher.automated-task-generated-history-row', [
                                    'session' => $session,
                                    'task' => $task,
                                  ])
                                @endforeach
                              </tbody>
                            </table>
                          </div>
                        </div>
                      @endforeach
                    </div>

                    <div class="mt-3">
                      {{ $sessions->links() }}
                    </div>
                  @else
                    <div class="bg-lighter rounded p-4 text-muted">No generated snapshots are available for this student yet. Publish the template and wait for the scheduler to run.</div>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
          <button type="button" class="btn btn-text-secondary rounded-pill" wire:click="close">Close</button>
        </div>
      </div>
    </div>
  @endif
</div>
