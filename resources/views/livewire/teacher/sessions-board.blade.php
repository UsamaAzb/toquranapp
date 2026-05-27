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

  <div class="card-body p-3 mt-5 sessions-board-shell">
    <div class="accordion mt-2 accordion-header-primary" id="sessionsAccordion">
      @forelse($sessions as $s)
        @php
          $itemId = 'sess-'.$s['id'];
          $heading = 'heading-'.$itemId;
          $collapse = 'collapse-'.$itemId;
          $taskCount = $s['task_count'];
          $isPublished = $s['is_published'];
        @endphp

        <div id="sess-{{ $s['id'] }}" class="accordion-item session-card {{ $s['card_state_class'] }} mb-4" wire:key="session-{{ $s['id'] }}">
          <h2 class="accordion-header session-card-header" id="{{ $heading }}">
            <div class="session-card-head-grid">
              <div
                class="d-flex flex-column session-title-wrap"
                x-data="{ title: @js($s['title']) }"
                @focus-title.window="if ($event.detail.id === {{ $s['id'] }}) $nextTick(() => $refs['titleInput{{ $s['id'] }}']?.focus())">
                <div class="session-title-row">
                  @if($editingId === $s['id'])
                    <input
                      x-ref="titleInput{{ $s['id'] }}"
                      type="text"
                      inputmode="text"
                      enterkeyhint="done"
                      class="form-control-plaintext p-0 m-0 fw-semibold session-title-input session-title-text"
                      style="outline:0; box-shadow:none; min-width:1px; font-size:18px;"
                      x-model="title"
                      x-on:keydown.enter.prevent="$wire.finishEdit(String(title ?? ''))"
                      x-on:keydown.escape.prevent="$wire.finishEdit(String(title ?? ''))"
                      x-on:blur="$wire.finishEdit(String(title ?? ''))"
                    />
                  @else
                    <h5
                      class="mb-0 fw-semibold text-truncate session-title-text"
                      wire:click.stop="startEdit({{ $s['id'] }})"
                      style="cursor:text;">
                      {{ $s['title'] }}
                    </h5>
                  @endif

                  <div class="session-actions">
                    <button
                      class="{{ in_array($s['id'], $open) ? '' : 'collapsed' }} btn btn-sm btn-outline-primary waves-effect session-toggle-btn"
                      type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#{{ $collapse }}"
                      aria-expanded="{{ in_array($s['id'], $open) ? 'true' : 'false' }}"
                      aria-controls="{{ $collapse }}"
                      aria-label="Tasks">
                      <span class="session-toggle-label">Tasks</span>
                      <span class="icon-md ti tabler-caret-down session-toggle-icon"></span>
                    </button>

                    @if($isPublished)
                        <span
                          class="badge bg-label-success d-inline-flex align-items-center session-status-icon-btn session-status-icon-passive"
                          role="status"
                          aria-live="polite"
                          title="Published"
                          aria-label="Published">
                        <i class="ti tabler-brand-telegram session-status-icon"></i>
                        <span class="visually-hidden">Published</span>
                      </span>
                    @else
                      <button
                        x-data
                        type="button"
                        class="btn btn-sm btn-outline-danger waves-effect d-inline-flex align-items-center session-status-icon-btn"
                        x-on:click.stop="window.w14ConfirmDeleteDraftSession($wire, {{ $s['id'] }}, @js($s['title']))"
                        wire:loading.attr="disabled"
                        wire:target="deleteDraftSession({{ $s['id'] }})"
                        title="Delete draft session"
                        aria-label="Delete draft session">
                        <i class="ti tabler-trash session-status-icon"></i>
                        <span class="visually-hidden">Delete draft session</span>
                      </button>
                      <button
                        x-data
                        type="button"
                        class="btn btn-sm btn-primary waves-effect waves-light d-inline-flex align-items-center session-status-icon-btn"
                        x-on:click.stop="window.w14ConfirmPublishSession($wire, {{ $s['id'] }}, @js($s['title']), @js($s['date']), @js($s['is_past_draft']))"
                        wire:loading.attr="disabled"
                        wire:target="publishSession({{ $s['id'] }})"
                        title="Publish"
                        aria-label="Publish">
                        <i class="ti tabler-upload session-status-icon"></i>
                        <span class="visually-hidden">Publish</span>
                      </button>
                    @endif
                  </div>
                </div>

                <div class="session-meta-row">
                  <small class="session-date">
                    <i class="ti tabler-calendar-event me-1"></i>
                    {{ \Carbon\Carbon::parse($s['date'])->format('d M') }}
                  </small>

                  <div class="session-progress-inline">
                    <small class="fw-medium session-task-description mb-0">
                      {{ $taskCount }} {{ \Illuminate\Support\Str::plural('Task', $taskCount) }}
                    </small>

                    <div class="progress island-progress" aria-hidden="true">
                      <div
                        class="progress-bar inside-island-progress"
                        style="width: {{ $taskCount > 0 ? 100 : 0 }}%;">
                      </div>
                    </div>
                  </div>

                  <span class="session-review-hint {{ $isPublished ? 'session-publish-hint-published' : 'session-publish-hint-draft' }}">
                    <i class="ti {{ $isPublished ? 'tabler-circle-check' : 'tabler-edit' }}"></i>
                    <span class="session-review-hint-label">{{ $isPublished ? 'Published' : 'Draft' }}</span>
                  </span>
                </div>
              </div>
            </div>
          </h2>

          <div
            id="{{ $collapse }}"
            class="accordion-collapse collapse {{ in_array($s['id'], $open) ? 'show' : '' }}"
            aria-labelledby="{{ $heading }}"
            data-bs-parent="#sessionsAccordion">
            <div class="accordion-body p-3 border-top">
              <div class="session-task-panel">
                @if(($s['materials_status'] ?? null) !== 'published')
                  <div
                    x-data
                    class="session-add-task-strip d-flex align-items-center gap-2 px-3 py-2"
                    role="button"
                    tabindex="0"
                    title="Add task to this session"
                    aria-label="Add task to this session"
                    x-on:click.stop="Livewire.dispatch('open-session-task-modal', { sessionId: {{ $s['id'] }} })"
                    x-on:keydown.enter.prevent.stop="Livewire.dispatch('open-session-task-modal', { sessionId: {{ $s['id'] }} })"
                    x-on:keydown.space.prevent.stop="Livewire.dispatch('open-session-task-modal', { sessionId: {{ $s['id'] }} })">
                    <span
                      class="btn btn-outline-primary rounded-circle p-0 d-inline-flex align-items-center justify-content-center session-add-task-btn"
                      aria-hidden="true">
                      <i class="ti tabler-plus"></i>
                    </span>
                    <span class="small session-add-task-label">Add task</span>
                  </div>
                @endif

                <div class="p-3" x-data x-init="window.w14InitSessionTaskSortable($el.querySelector('.tasks-sortable'), {{ $s['id'] }})">
                  <div class="tasks-sortable">
                    @forelse($s['tasks'] as $t)
                      <div id="task-{{ $t['id'] }}" class="session-task-row" data-task-id="{{ $t['id'] }}" wire:key="task-{{ $t['id'] }}">
                        <ul class="session-task-list">
                          <li class="session-task-item">
                            @php
                              $defaultPoints = (int) ($t['default_points'] ?? 0);
                            @endphp

                            <div
                              class="session-task-event"
                              x-data
                              x-on:click.stop="Livewire.dispatch('open-session-task-edit-modal', { taskId: {{ $t['id'] }} })"
                              style="cursor: pointer;">
                              <div class="session-task-topline">
                                <span
                                  class="session-task-dot {{ $t['dot_class'] }} drag-handle"
                                  title="Drag to reorder"
                                  aria-label="Drag to reorder"
                                  x-on:mousedown.stop>
                                </span>

                                <strong class="text-truncate flex-grow-1 session-task-title">
                                  {{ ucfirst($t['title'] ?? 'Task') }}
                                </strong>

                                <div class="session-task-top-meta">
                                  @if($defaultPoints > 0)
                                    <span class="flex-shrink-0 session-task-points">
                                      {{ $defaultPoints }} pts
                                    </span>
                                  @endif
                                  @if(! $isPublished)
                                    <button
                                      x-data
                                      type="button"
                                      class="btn btn-sm btn-icon btn-outline-danger"
                                      data-no-drag
                                      title="Delete draft task"
                                      aria-label="Delete draft task"
                                      wire:loading.attr="disabled"
                                      wire:target="deleteDraftTask({{ $t['id'] }})"
                                      x-on:click.stop="window.w14ConfirmDeleteDraftTask($wire, {{ $t['id'] }}, @js($t['title'] ?? 'this task'))">
                                      <i class="ti tabler-trash"></i>
                                    </button>
                                  @endif
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
                                        :attachment="$f"
                                        :session-id="$s['id']"
                                        :task-id="$t['id']"
                                        :variant-index="$loop->index"
                                        role="teacher" />
                                    @endforeach
                                  </div>
                                @endif
                              </div>
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
      @empty
        <div class="text-muted p-3">No sessions yet.</div>
      @endforelse
    </div>
  </div>

  {{-- Image Attachment Modal (Images only) --}}
  <div class="modal fade" id="imageAttachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="imageAttachmentTitle">Image</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <img id="imageAttachmentImg" src="" class="img-fluid teacher-session-image-preview" alt="">
        </div>
      </div>
    </div>
  </div>

  <script>
    window.w14ConfirmPublishSession = async function($wire, sessionId, title, date, isPastDraft) {
      if (window.w14PublishConfirmOpen) return;
      window.w14PublishConfirmOpen = true;

      const sessionTitle = String(title || 'this session');
      const dateText = String(date || '');
      const oldDraft = Boolean(isPastDraft);
      let confirmed = false;

      try {
        if (window.Swal && typeof window.Swal.fire === 'function') {
          const result = await window.Swal.fire({
            title: oldDraft ? 'Move date and publish?' : 'Publish this session?',
            text: oldDraft
              ? `"${sessionTitle}" is dated ${dateText}. Publishing will move it to today so students see it at the top.`
              : `Students will be able to see "${sessionTitle}".`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: oldDraft ? 'Move date and publish' : 'Publish',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            customClass: {
              confirmButton: 'btn btn-primary',
              cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
          });

          confirmed = !!result.isConfirmed;
        } else {
          confirmed = window.confirm(oldDraft
            ? `Move "${sessionTitle}" from ${dateText} to today and publish it for students?`
            : `Publish "${sessionTitle}" for students?`);
        }

        if (confirmed) {
          await $wire.publishSession(sessionId, oldDraft);
        }
      } finally {
        window.w14PublishConfirmOpen = false;
      }
    };

    window.w14ConfirmDeleteDraftSession = async function($wire, sessionId, title) {
      if (window.w14DeleteDraftSessionConfirmOpen) return;
      window.w14DeleteDraftSessionConfirmOpen = true;
      const sessionTitle = String(title || 'this session');
      let confirmed = false;

      try {
        if (window.Swal && typeof window.Swal.fire === 'function') {
          const result = await window.Swal.fire({
            title: 'Delete draft session?',
            text: `"${sessionTitle}" and its unpublished tasks will be removed.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            customClass: {
              confirmButton: 'btn btn-danger',
              cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
          });

          confirmed = !!result.isConfirmed;
        } else {
          confirmed = window.confirm(`Delete draft session "${sessionTitle}"?`);
        }

        if (confirmed) {
          await $wire.deleteDraftSession(sessionId);
        }
      } finally {
        window.w14DeleteDraftSessionConfirmOpen = false;
      }
    };

    window.w14ConfirmDeleteDraftTask = async function($wire, taskId, title) {
      if (window.w14DeleteDraftTaskConfirmOpen) return;
      window.w14DeleteDraftTaskConfirmOpen = true;
      const taskTitle = String(title || 'this task');
      let confirmed = false;

      try {
        if (window.Swal && typeof window.Swal.fire === 'function') {
          const result = await window.Swal.fire({
            title: 'Delete draft task?',
            text: `"${taskTitle}" will be removed from this unpublished session.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            customClass: {
              confirmButton: 'btn btn-danger',
              cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
          });

          confirmed = !!result.isConfirmed;
        } else {
          confirmed = window.confirm(`Delete draft task "${taskTitle}"?`);
        }

        if (confirmed) {
          await $wire.deleteDraftTask(taskId);
        }
      } finally {
        window.w14DeleteDraftTaskConfirmOpen = false;
      }
    };

    window.w14InitSessionTaskSortable = function(el, sessionId, ctx) {
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

          Livewire.dispatch('reorder-session-tasks', { sessionId, orderedIds: ids });

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
  if (window.w14TeacherSessionsImageAttachmentInitialized) return;
  window.w14TeacherSessionsImageAttachmentInitialized = true;

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
