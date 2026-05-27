@include('components.task-approval-work-styles')

@php
  $totalTasks = collect($sections)->sum(fn ($section) => count($section['tasks'] ?? []));
  $selectedCount = collect($selected)->filter()->count();
  $hasSelectedPointErrors = $this->hasSelectedPointErrors();
@endphp

<div class="w14-approval-work">
  <section class="card w14-approval-hero mb-4" aria-labelledby="approval-title">
    <div class="card-body">
      <div class="d-flex align-items-center gap-3 min-w-0">
        <div class="avatar avatar-lg flex-shrink-0">
          <span class="avatar-initial rounded bg-label-warning">
            <i class="icon-base ti tabler-clipboard-check icon-28px"></i>
          </span>
        </div>
        <div class="min-w-0">
          <div class="w14-approval-eyebrow">Parent review</div>
          <h1 class="w14-approval-title" id="approval-title">Review tasks for {{ $studentName }}</h1>
        </div>
      </div>

      <div class="w14-approval-hero__actions">
        <span class="badge bg-label-primary rounded-pill px-3 py-2">
          {{ count($sections) }} {{ \Illuminate\Support\Str::plural('subject', count($sections)) }}
        </span>
        <span class="badge {{ $totalTasks > 0 ? 'bg-label-warning' : 'bg-label-secondary' }} rounded-pill px-3 py-2">
          {{ $totalTasks }} in review
        </span>
        @if($totalTasks > 0)
          <button type="button" class="btn btn-label-primary" wire:click="toggleAll(true)">
            <i class="icon-base ti tabler-checks icon-16px me-1"></i>
            Select all
          </button>
          <button type="button" class="btn btn-label-secondary" wire:click="toggleAll(false)">
            <i class="icon-base ti tabler-minus icon-16px me-1"></i>
            Clear all
          </button>
        @endif
        <a href="{{ route('parent.students') }}" class="btn btn-label-secondary w14-approval-back-btn">
          <i class="icon-base ti tabler-arrow-left icon-16px me-1"></i>
          My children
        </a>
      </div>
    </div>
  </section>

  @if($result)
    <div class="alert alert-info" role="status">
      <div class="fw-semibold">Approved {{ $result['approved'] }}. Skipped {{ $result['skipped'] }}.</div>
      @if(! empty($result['skipped_rows']))
        <ul class="w14-approval-result-list">
          @foreach($result['skipped_rows'] as $row)
            <li>Task #{{ $row['id'] }} was skipped: {{ str_replace('_', ' ', $row['reason']) }}.</li>
          @endforeach
        </ul>
      @endif
    </div>
  @endif

  @forelse($sections as $section)
    @php
      $sectionTasks = $section['tasks'] ?? [];
      $sectionSelected = collect($sectionTasks)
        ->filter(fn ($task) => (bool) ($selected[$task['pivot_id']] ?? false))
        ->count();
      $sectionHasPointErrors = $this->subjectHasSelectedPointErrors($section['subject_id']);
    @endphp

    <section class="card w14-approval-section mb-3" wire:key="approval-subject-{{ $section['subject_id'] }}" x-data="{ open: false }">
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
              <span class="h5 mb-0 w14-approval-subject-title" title="{{ $section['subject_title'] }}">{{ $section['subject_title'] }}</span>
            </span>
          </button>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-label-warning rounded-pill">{{ count($sectionTasks) }} in review</span>
            <span class="badge rounded-pill w14-approval-selected-badge">{{ $sectionSelected }} selected</span>
          </div>
        </div>

        <div class="w14-approval-section__actions">
          <button type="button" class="btn btn-sm btn-label-primary" wire:click="toggleSubject({{ $section['subject_id'] }}, true)">
            <i class="icon-base ti tabler-checks icon-16px me-1"></i>
            Select all
          </button>
          <button type="button" class="btn btn-sm btn-label-secondary" wire:click="toggleSubject({{ $section['subject_id'] }}, false)">
            <i class="icon-base ti tabler-minus icon-16px me-1"></i>
            Clear
          </button>
          <button
            type="button"
            class="btn btn-sm btn-primary w14-approval-subject-approve"
            wire:click="approveSubject({{ $section['subject_id'] }})"
            wire:loading.attr="disabled"
            wire:target="approveSubject({{ $section['subject_id'] }})"
            @disabled($sectionSelected === 0 || $sectionHasPointErrors)>
            <span wire:loading.remove wire:target="approveSubject({{ $section['subject_id'] }})">
              <i class="icon-base ti tabler-check icon-16px me-1"></i>
              Approve
            </span>
            <span wire:loading wire:target="approveSubject({{ $section['subject_id'] }})">
              <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Approving
            </span>
          </button>
        </div>
      </div>

      <div class="card-body p-0" x-show="open" x-cloak>
        @foreach($sectionTasks as $task)
          @php
            $pivotId = $task['pivot_id'];
            $currentPoints = $points[$pivotId] ?? $task['default_points'];
            $pointError = $pointErrors[$pivotId] ?? null;
          @endphp
          <article class="w14-approval-task" wire:key="approval-task-{{ $task['pivot_id'] }}">
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
        @endforeach
      </div>
    </section>
  @empty
    <section class="card">
      <div class="card-body w14-approval-empty">
        <div class="avatar">
          <span class="avatar-initial rounded bg-label-success">
            <i class="icon-base ti tabler-checks icon-28px"></i>
          </span>
        </div>
        <h2 class="h5 mb-2">No tasks in review.</h2>
        <p class="text-muted mb-0">When {{ $studentName }} submits work for review, it will appear here.</p>
      </div>
    </section>
  @endforelse

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
