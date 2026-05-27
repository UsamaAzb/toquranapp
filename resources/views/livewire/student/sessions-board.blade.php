<div wire:poll.10s.visible="refreshTaskState">
  @push('styles')
    @once
      @include('components.sessions.board-styles')
    @endonce
  @endpush
  @push('scripts')
    @once
      @include('components.sessions.board-scripts')
    @endonce
  @endpush

  <div
    class="accordion sessions-board-shell"
    id="student-sessions-accordion"
    x-data="{ openSession: @js((int) ($open[0] ?? 0)) }">
    @forelse($sessions as $s)
      @php
        $heading = "heading-{$s['id']}";
        $collapse = "collapse-{$s['id']}";
        $taskCount = count($s['tasks']);
        $progressPct = $taskCount > 0 ? round($s['completedCount'] / $taskCount * 100) : 0;
        $inReviewCount = collect($s['tasks'])->filter(fn ($task) => in_array($task['pivot']['status'] ?? null, ['in_review', 'pending'], true))->count();
        $hasInReview = $inReviewCount > 0;
        $cardStateClass = $taskCount === 0
          ? 'session-card-state-empty'
          : ($hasInReview
            ? 'session-card-state-review'
            : ($progressPct >= 100
            ? 'session-card-state-complete'
            : ($progressPct > 0 ? 'session-card-state-progress' : 'session-card-state-ready')));
      @endphp

      <div id="session-{{ $s['id'] }}" class="accordion-item session-card {{ $cardStateClass }} mb-4" wire:key="session-{{ $s['id'] }}">
        <h2 class="accordion-header session-card-header" id="{{ $heading }}">
          <div class="session-card-head-grid">
            <div class="d-flex flex-column session-title-wrap">
              <div class="session-title-row">
                <h5 class="mb-0 fw-semibold text-truncate session-title-text">{{ $s['title'] }}</h5>

                <div class="session-actions">
                  <button
                    class="btn btn-sm btn-outline-primary waves-effect session-toggle-btn"
                    :class="{ 'collapsed': openSession !== {{ $s['id'] }} }"
                    type="button"
                    @click="openSession = openSession === {{ $s['id'] }} ? 0 : {{ $s['id'] }}"
                    :aria-expanded="openSession === {{ $s['id'] }} ? 'true' : 'false'"
                    aria-controls="{{ $collapse }}"
                    aria-label="Tasks">
                    <span class="session-toggle-label">Tasks</span>
                    <span class="icon-md ti tabler-caret-down session-toggle-icon"></span>
                  </button>

                  <a href="{{ url('student/tasks/'.$s['id'].'/journey/'.$studentId) }}"
                     class="btn btn-sm btn-primary waves-effect waves-light d-inline-flex align-items-center session-journey-icon-btn"
                     title="Open study island"
                     aria-label="Open study island">
                    <i class="ti tabler-beach session-journey-icon"></i>
                    <span class="visually-hidden">Open study island</span>
                  </a>
                </div>
              </div>

              <div class="session-meta-row">
                <small class="session-date">
                  <i class="ti tabler-calendar-event me-1"></i>
                  {{ \Carbon\Carbon::parse($s['date'])->format('d M') }}
                </small>

                <div class="session-progress-inline">
                  <small class="fw-medium session-task-description mb-0">
                    {{ $s['completedCount'] }}/{{ $taskCount }} Tasks
                  </small>

                      <div class="progress island-progress">
                    <div
                        class="progress-bar inside-island-progress"
                      role="progressbar"
                      aria-valuemin="0"
                      aria-valuemax="100"
                      aria-valuenow="{{ $progressPct }}"
                      style="width: {{ $progressPct }}%;">
                    </div>
                  </div>
                </div>

              </div>
            </div>

          </div>
        </h2>

        <div
          id="{{ $collapse }}"
          class="accordion-collapse"
          x-show="openSession === {{ $s['id'] }}"
          x-cloak
          aria-labelledby="{{ $heading }}">
          <div class="accordion-body p-3 border-top">
            <div class="session-task-panel">
              @php
                $colors = ['primary', 'success', 'danger', 'info'];
              @endphp

              @forelse($s['tasks'] as $i => $t)
                @php
                  $taskStatus = $t['pivot']['status'] ?? null;
                  $isCompleted = $taskStatus === 'completed';
                  $isInReview = in_array($taskStatus, ['in_review', 'pending'], true);
                  $taskRowClass = $isCompleted ? 'session-task-row-completed' : ($isInReview ? 'session-task-row-review' : 'session-task-row-ready');
                @endphp

                <div id="task-{{ $t['id'] }}" class="session-task-row {{ $taskRowClass }}" wire:key="student-session-task-{{ $s['id'] }}-{{ $t['id'] }}-{{ $t['pivot']['status'] ?? 'assigned' }}-{{ $t['pivot']['approval_source'] ?? 'none' }}">
                  <ul class="session-task-list">
                    <li class="session-task-item">
                      @php
                        $dotClass = 'session-task-dot-' . $colors[$i % count($colors)];
                        $defaultPoints = (int) ($t['default_points'] ?? 0);
                        $approvalSource = $t['pivot']['approval_source'] ?? null;
                        $sourceDetail = match ($approvalSource) {
                          'trusted_child_auto' => 'Approved by trusted child auto',
                          'parent_direct_completion', 'parent_approval' => 'Approved by Parent',
                          'teacher_approval' => 'Approved by Teacher',
                          'student_pin' => 'Approved by PIN',
                          default => null,
                        };
                        $sourceBorderClass = match ($approvalSource) {
                          'student_pin' => 'session-task-source-pin',
                          'parent_direct_completion', 'parent_approval' => 'session-task-source-parent',
                          'teacher_approval' => 'session-task-source-teacher',
                          'trusted_child_auto' => 'session-task-source-auto',
                          default => 'session-task-source-default',
                        };
                      @endphp

                      <div class="session-task-event">
                        <div class="session-task-topline">
                          <span class="session-task-dot {{ $dotClass }}">
                          </span>

                          <strong class="text-truncate flex-grow-1 session-task-title">
                            {{ ucfirst($t['title'] ?? 'Task') }}
                          </strong>

                          <div class="session-task-top-meta">
                            @if($defaultPoints > 0)
                              <span class="flex-shrink-0 session-task-points {{ $isCompleted ? 'session-task-points-earned' : '' }}">
                                {{ $defaultPoints }} pts
                              </span>
                            @endif

                            <div class="session-task-action-row session-task-action-row-top">
                              @if($isCompleted)
                                @if($sourceDetail)
                                  <div class="dropdown">
                                    <button
                                      type="button"
                                      class="session-task-state-pill session-task-complete-pill {{ $sourceBorderClass }}"
                                      data-bs-toggle="dropdown"
                                      aria-expanded="false"
                                      aria-label="{{ $sourceDetail }}">
                                      <i class="ti tabler-circle-check"></i><span class="session-task-state-label">Completed</span>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end p-2 shadow-sm">
                                      <span class="small text-muted text-nowrap">{{ $sourceDetail }}</span>
                                    </div>
                                  </div>
                                @else
                                  <span class="session-task-state-pill session-task-complete-pill {{ $sourceBorderClass }}">
                                    <i class="ti tabler-circle-check"></i><span class="session-task-state-label">Completed</span>
                                  </span>
                                @endif
                              @elseif($isInReview)
                                <span class="session-task-state-pill session-task-review-pill">
                                  <i class="ti tabler-hourglass"></i>
                                  <span class="session-task-state-label">In review</span>
                                </span>
                              @endif

                              @include('components.sessions.task-actions', [
                                't' => $t,
                                'isCompleted' => $isCompleted,
                                'isInReview' => $isInReview,
                                'variant' => 'top',
                              ])
                            </div>
                          </div>
                        </div>

                      </div>

                      <div class="session-task-body">
                        @if(trim($t['desc'] ?? '') !== '')
                          <div class="session-task-brief">
                            <div class="session-task-brief-heading">
                              <span class="session-task-brief-icon" aria-hidden="true">
                                <i class="ti tabler-map-pin"></i>
                              </span>
                              <span class="session-task-brief-label">Task brief</span>
                            </div>
                            <p class="mb-0 session-task-description session-task-brief-description">{!! nl2br(e(trim($t['desc'] ?? ''))) !!}</p>
                          </div>
                        @endif

                        @if (!empty($t['files']))
                          <div class="session-task-attachments d-flex flex-wrap gap-2 mt-2">
                            @foreach($t['files'] as $f)
                              <x-sessions.attachment-chip
                                wire:key="student-session-attachment-{{ (int) $s['id'] }}-{{ (int) $t['id'] }}-{{ (int) ($f['id'] ?? $loop->index) }}"
                                :attachment="$f"
                                :session-id="$s['id']"
                                :task-id="$t['id']"
                                :student-id="$studentId"
                                :variant-index="$loop->index"
                                role="student" />
                            @endforeach
                          </div>
                        @endif

                        @if(!$isCompleted && !$isInReview)
                          <div class="session-task-action-row session-task-action-row-phone">
                            @include('components.sessions.task-actions', [
                              't' => $t,
                              'isCompleted' => $isCompleted,
                              'isInReview' => $isInReview,
                              'variant' => 'phone',
                            ])
                          </div>
                        @endif
                      </div>
                    </li>
                  </ul>
                </div>
              @empty
                <div class="text-muted">No tasks yet.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="text-muted p-3">No sessions yet.</div>
    @endforelse
  </div>

  <livewire:student.attachment-study-viewer
    :student-id="$studentId"
    surface="session"
    wire:key="student-session-attachment-study-viewer-{{ $studentId }}" />

  <div class="modal fade" id="studentSessionParentTaskPointsModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="closeParentTaskPointsModal"></button>
        </div>
        <form wire:submit.prevent="confirmParentTaskPoints" autocomplete="off">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Task Points</label>
              <input
                type="number"
                class="form-control @error('parentTaskPoints') is-invalid @enderror"
                wire:model.live="parentTaskPoints"
                min="0"
                @if($parentTaskMaxPoints) max="{{ $parentTaskMaxPoints }}" @endif>
              @error('parentTaskPoints') <div class="invalid-feedback">{{ $message }}</div> @enderror
              @if($parentTaskMaxPoints)
                <small class="text-primary">Max points for this task: {{ $parentTaskMaxPoints }}</small>
              @endif
            </div>
          </div>
          <div class="modal-footer d-flex justify-content-end">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal" wire:click="closeParentTaskPointsModal">Cancel</button>
            <button type="submit" class="btn btn-primary">OK</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Image Attachment Modal (Images only) --}}
  <div class="modal fade" id="studentSessionCompletePinModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="closePinModal"></button>
        </div>
        <form wire:submit.prevent="confirmTaskCompletionWithPin" autocomplete="off">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Task Points</label>
              <input
                type="number"
                class="form-control"
                value="{{ $currentTaskDefaultPoint ?? 0 }}"
                min="0"
                @if($currentTaskMaxPoint) max="{{ $currentTaskMaxPoint }}" @endif
                readonly>
              @if($currentTaskMaxPoint)
                <small class="text-primary">Max points for this task: {{ $currentTaskMaxPoint }}</small>
              @endif
            </div>

            <div class="mb-3">
              <label class="form-label">PIN</label>
              <input
                id="studentSessionPinField"
                type="text"
                class="form-control @error('pinInput') is-invalid @enderror"
                wire:model.live="pinInput"
                maxlength="4"
                autocomplete="off"
                inputmode="numeric"
                pattern="\d*"
                @keydown.enter.prevent
                style="-webkit-text-security: disc; text-security: disc;">
              @error('pinInput') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            @if($pinErrorMessage)
              <div class="alert alert-danger py-2">{{ $pinErrorMessage }}</div>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="imageAttachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="imageAttachmentTitle">Image</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="p-2 text-center">
          <img id="imageAttachmentImg" src="" class="img-fluid student-session-image-preview" alt="">
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function () {
  if (window.w14StudentSessionsImageAttachmentInitialized) return;
  window.w14StudentSessionsImageAttachmentInitialized = true;

  const bindStudentSessionPinModal = () => {
    if (!window.Livewire || window.w14StudentSessionsPinModalBound) return;
    window.w14StudentSessionsPinModalBound = true;

    const pinModalEl = document.getElementById('studentSessionCompletePinModal');
    const parentTaskPointsModalEl = document.getElementById('studentSessionParentTaskPointsModal');

    if (pinModalEl) {
      Livewire.on('open-student-session-pin-modal', () => {
        const modal = bootstrap.Modal.getOrCreateInstance(pinModalEl);
        modal.show();
        setTimeout(() => document.getElementById('studentSessionPinField')?.focus(), 200);
      });

      Livewire.on('close-student-session-pin-modal', () => {
        bootstrap.Modal.getOrCreateInstance(pinModalEl).hide();
      });
    }

    if (parentTaskPointsModalEl) {
      Livewire.on('open-student-session-parent-task-points-modal', () => {
        bootstrap.Modal.getOrCreateInstance(parentTaskPointsModalEl).show();
      });

      Livewire.on('close-student-session-parent-task-points-modal', () => {
        bootstrap.Modal.getOrCreateInstance(parentTaskPointsModalEl).hide();
      });
    }
  };

  if (window.Livewire) {
    bindStudentSessionPinModal();
  }

  document.addEventListener('livewire:init', bindStudentSessionPinModal, { once: true });
  document.addEventListener('livewire:initialized', bindStudentSessionPinModal, { once: true });

  const modalEl = document.getElementById('imageAttachmentModal');
  const titleEl = document.getElementById('imageAttachmentTitle');
  const imgEl   = document.getElementById('imageAttachmentImg');

  if (!modalEl || !titleEl || !imgEl) return;

  document.addEventListener('click', function (e) {
    const link = e.target.closest('.image-attachment');
    if (!link) return;

    e.preventDefault();
    e.stopPropagation();

    const src   = link.getAttribute('data-img-src') || '';
    const title = link.getAttribute('data-img-title') || 'Image';

    titleEl.textContent = title;
    imgEl.src = src;

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }, true);

  modalEl.addEventListener('hidden.bs.modal', function () {
    imgEl.src = '';
  });
})();
</script>
@endpush
