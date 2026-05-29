<div class="row g-6">
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

  <div class="col-12">
    <div class="card shadow-lg">
      <div class="card-body p-3 mt-2 daily-sessions-board-shell">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="fw-semibold session-section-title">Automated Task Groups</div>
          <livewire:teacher.add-main-daily-session :subject-id="$subjectId" />
        </div>

        <div class="accordion mt-2 accordion-header-primary" id="mainDailyAccordion">
          @forelse($sessions as $m)
            @php
              $mainId = $m['id'];
              $mainItem = 'main-'.$mainId;
              $mainHeading = 'heading-'.$mainItem;
            @endphp

            <div class="accordion-item main-daily-card mb-5" wire:key="main-{{ $mainId }}">
              <h2 class="accordion-header" id="{{ $mainHeading }}">
                <div class="d-flex align-items-center justify-content-between gap-3 flex-nowrap">
                  <div
                    class="d-flex flex-column session-title-wrap"
                    x-data="{ editing:false, title:@js($m['title']) }">
                    <template x-if="editing">
                      <input
                        x-ref="mainTitleInput"
                        type="text"
                        inputmode="text"
                        enterkeyhint="done"
                        class="form-control-plaintext p-0 m-0 fw-semibold session-title-input session-title-text"
                        style="outline:0; box-shadow:none; min-width:1px; font-size:18px;"
                        x-model="title"
                        @keydown.enter.prevent="editing=false; $wire.updateMainTitle({{ $mainId }}, String(title ?? ''))"
                        @blur="editing=false; $wire.updateMainTitle({{ $mainId }}, String(title ?? ''))"
                      />
                    </template>

                    <template x-if="!editing">
                      <h5
                        class="mb-0 fw-semibold text-primary text-truncate session-title-text"
                        style="cursor:text;"
                        @click="editing=true; $nextTick(()=> $refs.mainTitleInput?.focus())">
                        {{ $m['title'] }}
                      </h5>
                    </template>
                  </div>

                  <div class="d-flex align-items-center gap-2 flex-shrink-0 session-actions">
                    <livewire:teacher.add-daily-session
                      :subject-id="$subjectId"
                      :main-daily-session-id="$mainId"
                      :wire:key="'add-daily-'.$mainId"
                    />
                  </div>
                </div>
              </h2>

              <div class="accordion-body pt-3">
                @if(!empty($m['daily_sessions']))
                  <div class="accordion accordion-flush" id="dailyAccordion-{{ $mainId }}">
                    @foreach($m['daily_sessions'] as $d)
                      @php
                        $dailyId = $d['id'];
                        $dailyItem = 'daily-'.$dailyId;
                        $dailyHeading = 'heading-'.$dailyItem;
                        $dailyCollapse = 'collapse-'.$dailyItem;
                      @endphp

                      <div id="daily-{{ $dailyId }}" class="accordion-item session-card mb-3" wire:key="daily-{{ $dailyId }}">
                        <h2 class="accordion-header session-card-header" id="{{ $dailyHeading }}">
                          <div class="d-flex align-items-center justify-content-between gap-3 flex-nowrap">
                            <div
                              class="d-flex flex-column session-title-wrap"
                              x-data="{ editing:false, title:@js($d['title']) }">
                              <template x-if="editing">
                                <input
                                  x-ref="dailyTitleInput"
                                  type="text"
                                  inputmode="text"
                                  enterkeyhint="done"
                                  class="form-control-plaintext p-0 m-0 fw-semibold session-title-input session-title-text"
                                  style="outline:0; box-shadow:none; min-width:1px; font-size:16px;"
                                  x-model="title"
                                  @keydown.enter.prevent="editing=false; $wire.updateDailyTitle({{ $dailyId }}, String(title ?? ''))"
                                  @blur="editing=false; $wire.updateDailyTitle({{ $dailyId }}, String(title ?? ''))"
                                />
                              </template>

                              <template x-if="!editing">
                                <h6
                                  class="mb-0 fw-semibold text-truncate session-title-text"
                                  style="cursor:text;"
                                  @click="editing=true; $nextTick(()=> $refs.dailyTitleInput?.focus())">
                                  {{ $d['title'] }}
                                </h6>
                              </template>
                            </div>

                            <div class="d-flex align-items-center gap-2 flex-shrink-0 session-actions">
                              <button
                                class="{{ isset($openDaily[$dailyId]) ? '' : 'collapsed' }} btn btn-sm btn-outline-primary waves-effect session-toggle-btn"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#{{ $dailyCollapse }}"
                                aria-expanded="{{ isset($openDaily[$dailyId]) ? 'true' : 'false' }}"
                                aria-controls="{{ $dailyCollapse }}">
                                Tasks
                                <span class="icon-md ti tabler-caret-down ms-1"></span>
                              </button>

                              <button
                                type="button"
                                class="btn btn-sm btn-outline-success d-inline-flex align-items-center session-status-icon-btn"
                                x-on:click.stop="Livewire.dispatch('open-assign-daily-session', { dailySessionId: {{ $dailyId }} })"
                                title="Assign to students"
                                aria-label="Assign to students">
                                <i class="ti tabler-user-plus session-status-icon"></i>
                                <span class="visually-hidden">Assign to students</span>
                              </button>
                            </div>
                          </div>
                        </h2>

                        <div
                          id="{{ $dailyCollapse }}"
                          class="accordion-collapse collapse {{ isset($openDaily[$dailyId]) ? 'show' : '' }}"
                          aria-labelledby="{{ $dailyHeading }}"
                          data-bs-parent="#dailyAccordion-{{ $mainId }}">
                          <div class="accordion-body p-3 border-top">
                            <div class="session-task-panel">
                              <div
                                x-data
                                class="session-add-task-strip d-flex align-items-center gap-2 px-3 py-2"
                                role="button"
                                tabindex="0"
                                title="Add task to this automated task set"
                                aria-label="Add task to this automated task set"
                                x-on:click.stop="Livewire.dispatch('open-daily-session-task-modal', { dailySessionId: {{ $dailyId }} })"
                                x-on:keydown.enter.prevent.stop="Livewire.dispatch('open-daily-session-task-modal', { dailySessionId: {{ $dailyId }} })"
                                x-on:keydown.space.prevent.stop="Livewire.dispatch('open-daily-session-task-modal', { dailySessionId: {{ $dailyId }} })">
                                <span
                                  class="btn btn-outline-primary rounded-circle p-0 d-inline-flex align-items-center justify-content-center session-add-task-btn"
                                  aria-hidden="true">
                                  <i class="ti tabler-plus"></i>
                                </span>
                                <span class="small session-add-task-label">Add task</span>
                              </div>

                              <div class="p-3" x-data x-init="window.w14InitDailySessionTaskSortable($el.querySelector('.tasks-sortable'), {{ $dailyId }})">
                                <div class="tasks-sortable">
                                  @php
                                    $colors = ['primary', 'success', 'danger', 'info'];
                                  @endphp

                                  @forelse(($d['tasks'] ?? []) as $i => $t)
                                    <div id="task-{{ $t['id'] }}" class="session-task-row" data-task-id="{{ $t['id'] }}" wire:key="daily-task-{{ $t['id'] }}">
                                      <ul class="timeline mb-0">
                                        <li class="timeline-item timeline-item-transparent ps-6 border-dashed">
                                          @php
                                            $colorClass = 'timeline-point-' . $colors[$i % count($colors)];
                                            $defaultPoints = (int) ($t['default_points'] ?? 0);
                                            $taskDesc = trim($t['desc'] ?? '');
                                          @endphp

                                          <span
                                            class="timeline-indicator-advanced timeline-point {{ $colorClass }} border-0 shadow-none drag-handle"
                                            style="cursor: grab;"
                                            x-on:mousedown.stop>
                                          </span>

                                          <div
                                            class="timeline-event ps-1"
                                            x-on:click.stop="Livewire.dispatch('open-daily-session-task-edit-modal', { taskId: {{ $t['id'] }} })"
                                            style="cursor: pointer;">
                                            <div class="d-flex align-items-baseline justify-content-between gap-2 flex-nowrap mb-1">
                                              <strong class="text-truncate flex-grow-1 session-task-title">
                                                {{ $t['title'] ?? 'Untitled' }}
                                              </strong>

                                              @if($defaultPoints > 0)
                                                <span class="text-danger fw-semibold flex-shrink-0 session-task-points">
                                                  {{ $defaultPoints }} pts
                                                </span>
                                              @endif
                                            </div>

                                            @if($taskDesc !== '')
                                              <div class="session-task-brief">
                                                <div class="session-task-brief-heading">
                                                  <span class="session-task-brief-icon" aria-hidden="true">
                                                    <i class="ti tabler-map-pin"></i>
                                                  </span>
                                                  <span class="session-task-brief-label">Task brief</span>
                                                </div>
                                                <p class="mb-0 session-task-description session-task-brief-description">{!! nl2br(e($taskDesc)) !!}</p>
                                              </div>
                                            @endif

                                            @if(!empty($t['attachments']))
                                              <div class="d-flex flex-wrap gap-2 mt-2">
                                                @foreach($t['attachments'] as $f)
                                                  <x-sessions.attachment-chip
                                                    :attachment="$f"
                                                    :daily-session-id="$dailyId"
                                                    :variant-index="$loop->index"
                                                    role="teacher" />
                                                @endforeach
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
                        </div>
                      </div>
                    @endforeach
                  </div>
                @else
                  <div class="text-muted">No automated task sets yet.</div>
                @endif
              </div>
            </div>
          @empty
            <div class="text-muted">No automated task groups yet.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  <livewire:teacher.show-daily-session-task :wire:key="'daily-task-modal'" />

  <div class="modal fade" id="imageAttachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="imageAttachmentTitle">Image</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <img id="imageAttachmentImg" src="" class="img-fluid teacher-daily-image-preview" alt="">
        </div>
      </div>
    </div>
  </div>

  <livewire:teacher.assign-daily-session />

  <script>
    window.w14InitDailySessionTaskSortable = function(el, sessionId, ctx) {
      if (!el) return;
      if (typeof window.Sortable === 'undefined') {
        console.warn('Sortable.js not loaded');
        return;
      }

      const state = ctx ?? {};

      const sortable = new window.Sortable(el, {
        animation: 150,
        handle: '.drag-handle',
        filter: 'a, button, input, textarea, select, [data-no-drag]',
        preventOnFilter: true,

        onStart() {
          state.dragged = true;
          const acc = el.closest('.accordion');
          acc && acc.classList.add('dragging');
        },

        onEnd(evt) {
          const ids = Array.from(el.querySelectorAll('[data-task-id]'))
            .map(n => Number.parseInt(n.getAttribute('data-task-id'), 10));

          Livewire.dispatch('reorder-daily-session-tasks', { dailySessionId: sessionId, orderedIds: ids });

          setTimeout(() => { state.dragged = false; }, 0);

          const acc = el.closest('.accordion');
          acc && acc.classList.remove('dragging');
        }
      });
    };
  </script>
</div>

@push('scripts')
<script>
(function () {
  if (window.w14TeacherDailySessionsImageAttachmentInitialized) return;
  window.w14TeacherDailySessionsImageAttachmentInitialized = true;

  const modalEl = document.getElementById('imageAttachmentModal');
  const titleEl = document.getElementById('imageAttachmentTitle');
  const imgEl = document.getElementById('imageAttachmentImg');

  if (!modalEl || !titleEl || !imgEl) return;

  document.addEventListener('click', function (e) {
    const link = e.target.closest('.image-attachment');
    if (!link) return;

    e.preventDefault();
    e.stopPropagation();

    const src = link.getAttribute('data-img-src') || '';
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
