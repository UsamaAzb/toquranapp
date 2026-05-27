@include('components.task-approval-work-styles')

@php
  $totalTasks = count($tasks);
  $selectedCount = collect($selected)->filter()->count();
  $hasSelectedPointErrors = $this->hasSelectedPointErrors();
@endphp

<div class="w14-approval-work">
  <section class="card w14-approval-hero mb-4" aria-labelledby="teacher-approval-title">
    <div class="card-body">
      <div class="d-flex align-items-center gap-3 min-w-0">
        <div class="avatar avatar-lg flex-shrink-0">
          <span class="avatar-initial rounded bg-label-primary">
            <i class="icon-base ti tabler-clipboard-check icon-28px"></i>
          </span>
        </div>
        <div class="min-w-0">
          <div class="w14-approval-eyebrow">Teacher review</div>
          <h1 class="w14-approval-title" id="teacher-approval-title">Review {{ $studentName }}</h1>
        </div>
      </div>

      <div class="w14-approval-hero__actions">
        <span class="badge bg-label-primary rounded-pill px-3 py-2">{{ $subjectTitle }}</span>
        <span class="badge {{ $totalTasks > 0 ? 'bg-label-warning' : 'bg-label-secondary' }} rounded-pill px-3 py-2">
          {{ $totalTasks }} in review
        </span>
      </div>
    </div>
  </section>

  @if($result)
    <div class="alert alert-info" role="status">
      <div class="fw-semibold">Approved {{ $result['approved'] }}. Skipped {{ $result['skipped'] }}.</div>
    </div>
  @endif

  <section class="card w14-approval-section" x-data="{ open: false }">
    <div class="card-header">
      <div class="min-w-0">
        <button
          type="button"
          class="btn btn-text-secondary p-0 border-0 text-start w14-approval-subject-toggle"
          @click="open = !open"
          :aria-expanded="open.toString()">
          <span class="w14-approval-subject-toggle__inner mb-1">
            <span class="avatar avatar-sm">
              <span class="avatar-initial rounded bg-label-primary">
                <i
                  class="icon-base ti tabler-chevron-right icon-16px w14-approval-chevron"
                  :class="{ 'w14-approval-chevron--open': open }"
                  aria-hidden="true"></i>
              </span>
            </span>
            <span class="h5 mb-0 w14-approval-subject-title" title="{{ $subjectTitle }}">{{ $subjectTitle }}</span>
          </span>
        </button>
        <div class="d-flex flex-wrap gap-2">
          <span class="badge bg-label-warning rounded-pill">{{ $totalTasks }} in review</span>
          <span class="badge rounded-pill w14-approval-selected-badge">{{ $selectedCount }} selected</span>
        </div>
      </div>

      @if($totalTasks > 0)
        <div class="w14-approval-section__actions">
          <button type="button" class="btn btn-sm btn-label-primary" wire:click="toggleAllTasks(true)">
            <i class="icon-base ti tabler-checks icon-16px me-1"></i>
            Select all
          </button>
          <button type="button" class="btn btn-sm btn-label-secondary" wire:click="toggleAllTasks(false)">
            <i class="icon-base ti tabler-minus icon-16px me-1"></i>
            Clear
          </button>
        </div>
      @endif
    </div>

    <div class="card-body p-0" x-show="open" x-cloak>
      @forelse($tasks as $task)
        @php
          $pivotId = $task['pivot_id'];
          $currentPoints = $points[$pivotId] ?? $task['default_points'];
          $pointError = $pointErrors[$pivotId] ?? null;
        @endphp
        <article class="w14-approval-task" wire:key="teacher-approval-task-{{ $task['pivot_id'] }}">
          <div class="w14-approval-task__check">
            <input
              type="checkbox"
              class="form-check-input"
              wire:model.live="selected.{{ $task['pivot_id'] }}"
              aria-label="Select {{ $task['title'] }}">
          </div>

          <div class="min-w-0">
            @if($task['details_url'])
              <a
                href="{{ $task['details_url'] }}"
                class="fw-semibold d-block w14-approval-task-title"
                title="Open task details">
                {{ $task['title'] }}
              </a>
            @else
              <span class="fw-semibold d-block w14-approval-task-title">{{ $task['title'] }}</span>
            @endif

            <div class="w14-approval-task__meta">
              @if($task['review_submitted_at'])
                <span class="badge bg-label-secondary w14-approval-meta-badge">
                  <i class="icon-base ti tabler-clock icon-14px me-1"></i>
                  <span class="w14-approval-meta-badge__text">{{ $task['review_submitted_at'] }}</span>
                </span>
              @endif
              <span class="badge bg-label-info w14-approval-meta-badge" title="{{ $task['session_title'] }}">
                <i class="icon-base ti tabler-calendar-event icon-14px me-1"></i>
                <span class="w14-approval-meta-badge__text">{{ $task['session_title_short'] }}</span>
              </span>
              @if($task['session_date'])
                <span class="badge bg-label-secondary w14-approval-meta-badge">
                  <i class="icon-base ti tabler-calendar icon-14px me-1"></i>
                  <span class="w14-approval-meta-badge__text">{{ $task['session_date'] }}</span>
                </span>
              @endif
            </div>
          </div>

          <div class="w14-approval-task__controls">
            <span class="badge bg-label-primary px-3 py-2 w14-approval-points-badge">
              {{ $currentPoints === '' || $currentPoints === null ? 'No points' : $currentPoints.' pts' }}
            </span>
            <button
              type="button"
              class="btn btn-sm btn-icon btn-label-secondary"
              wire:click="togglePointEditor({{ $task['pivot_id'] }})"
              aria-label="Edit effort points for {{ $task['title'] }}">
              <i class="icon-base ti tabler-pencil icon-16px"></i>
            </button>
          </div>

          @if($editingPoints[$task['pivot_id']] ?? false)
            <div class="w14-approval-task__editor">
              <input
                type="number"
                class="form-control form-control-sm w14-approval-points-input {{ $pointError ? 'is-invalid' : '' }}"
                min="0"
                max="{{ $task['max_points'] }}"
                wire:model.live="points.{{ $task['pivot_id'] }}"
                aria-label="Effort points for {{ $task['title'] }}">
              <span class="text-muted small">Default {{ $task['default_points'] }}. Max {{ $task['max_points'] }}.</span>
              @if($pointError)
                <span class="invalid-feedback d-block">{{ $pointError }}</span>
              @endif
            </div>
          @endif
        </article>
      @empty
        <div class="w14-approval-empty">
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-success">
              <i class="icon-base ti tabler-checks icon-28px"></i>
            </span>
          </div>
          <h2 class="h5 mb-2">No tasks in review.</h2>
          <p class="text-muted mb-0">Submitted {{ $subjectTitle }} tasks for {{ $studentName }} will appear here.</p>
        </div>
      @endforelse
    </div>
  </section>

  @if($totalTasks > 0)
    <section class="card w14-approval-submit shadow-sm mt-4">
      <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <div class="fw-semibold">{{ $selectedCount }} selected</div>
          <div class="text-muted small">Only selected in-review tasks will be approved.</div>
        </div>
        <div class="w14-approval-submit__actions">
          <button
            type="button"
            class="btn btn-primary"
            wire:click="approveSelected"
            wire:loading.attr="disabled"
            @disabled($selectedCount === 0 || $hasSelectedPointErrors)>
            <span wire:loading.remove wire:target="approveSelected">
              <i class="icon-base ti tabler-check icon-16px me-1"></i>
              Approve selected
            </span>
            <span wire:loading wire:target="approveSelected">
              <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Approving
            </span>
          </button>
        </div>
      </div>
    </section>
  @endif
</div>
