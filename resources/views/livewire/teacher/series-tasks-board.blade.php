<div class="series-board">
  <style>
    .series-board,
    .series-board * { min-width: 0; }
    .series-board [x-cloak] { display: none !important; }
    .series-truncate {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
    .series-card {
      border: 1px solid var(--bs-border-color);
      border-radius: .5rem;
      overflow: visible;
    }
    .series-card .dropdown-menu {
      z-index: 1085;
    }
    .series-card-header,
    .series-pathway-header {
      display: flex;
      flex-direction: column;
      gap: .45rem;
    }
    .series-compact-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .75rem;
    }
    .series-title-line,
    .series-header-actions,
    .series-action-row {
      display: flex;
      align-items: center;
      min-width: 0;
    }
    .series-title-line {
      gap: .5rem;
      flex: 1 1 auto;
    }
    .series-header-actions {
      gap: .25rem;
      flex: 0 0 auto;
    }
    .series-header-menu {
      flex: 0 0 auto;
    }
    .series-action-row {
      flex-wrap: wrap;
      gap: .5rem;
      justify-content: flex-end;
    }
    .series-icon-btn {
      inline-size: 2.1rem;
      block-size: 2.1rem;
      flex: 0 0 auto;
    }
    .series-icon-btn i,
    .series-normal-icon {
      font-size: 1.25rem;
      line-height: 1;
    }
    .series-check-status {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: var(--bs-success);
    }
    .series-meta-line {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      column-gap: .35rem;
      row-gap: .1rem;
      color: var(--bs-secondary-color);
    }
    .series-meta-token {
      font-weight: 600;
    }
    .series-meta-token.is-sequence {
      color: var(--bs-warning);
    }
    .series-meta-token.is-source {
      color: var(--bs-info);
    }
    .series-meta-token.is-assigned {
      color: var(--bs-primary);
    }
    .series-meta-token.is-count {
      color: var(--bs-success);
      font-weight: 600;
    }
    .series-meta-separator {
      color: var(--bs-secondary-color);
      opacity: .45;
    }
    .series-info {
      display: inline-block;
      position: relative;
      vertical-align: middle;
    }
    .series-info__trigger {
      align-items: center;
      background: transparent;
      border: 0;
      border-radius: 999px;
      color: var(--bs-primary);
      cursor: pointer;
      display: inline-flex;
      justify-content: center;
      list-style: none;
      min-height: 1.25rem;
      min-width: 1.25rem;
      padding: 0;
    }
    .series-info__trigger::-webkit-details-marker {
      display: none;
    }
    .series-info[open] .series-info__trigger,
    .series-info__trigger:hover {
      color: var(--bs-info);
    }
    .series-info__panel {
      background: var(--bs-body-bg);
      border: 1px solid var(--bs-border-color);
      border-radius: .5rem;
      box-shadow: 0 .625rem 1.5rem rgba(15, 23, 42, .14);
      color: var(--bs-body-color);
      font-size: .8125rem;
      font-weight: 400;
      line-height: 1.45;
      max-width: min(18rem, calc(100vw - 2rem));
      min-width: 13rem;
      padding: .625rem .75rem;
      position: absolute;
      top: calc(100% + .375rem);
      left: 50%;
      transform: translateX(-50%);
      z-index: 1090;
    }
    .rotate-180 { transform: rotate(180deg); }
    .series-source-button {
      min-block-size: 2.4rem;
      text-align: start;
    }
    .series-pathway {
      padding-block: .9rem;
      padding-inline: .25rem;
    }
    .series-pathway + .series-pathway { border-block-start: 1px solid var(--bs-border-color); }
    .series-pathways-zone {
      border-block-start: 1px solid var(--bs-border-color);
      margin-block-start: 1.15rem;
      padding-block-start: 1rem;
    }
    .series-pathways-heading {
      align-items: center;
      background: rgba(var(--bs-primary-rgb), .06);
      border: 1px solid rgba(var(--bs-primary-rgb), .12);
      border-radius: .5rem;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      margin-block-end: .35rem;
      padding: .55rem .7rem;
    }
    .series-pathways-heading h6 {
      color: var(--bs-heading-color);
      font-weight: 600;
    }
    .series-pathways-heading .series-icon-btn {
      background: rgba(var(--bs-primary-rgb), .08);
    }
    .series-picker {
      border: 1px solid var(--bs-border-color);
      border-radius: .5rem;
      padding: 1rem;
      background: var(--bs-paper-bg);
    }
    .series-picker-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .5rem;
      max-block-size: 18rem;
      overflow-y: auto;
    }
    .series-picker-option {
      display: flex;
      gap: .65rem;
      align-items: flex-start;
      border: 1px solid var(--bs-border-color);
      border-radius: .375rem;
      padding: .675rem .75rem;
      background: var(--bs-body-bg);
    }
    .series-source-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .75rem;
    }
    .series-source-modal {
      max-width: min(62rem, calc(100vw - 2rem));
    }
    .series-source-card {
      align-items: flex-start;
      border: 1px solid var(--bs-border-color);
      border-radius: .5rem;
      display: flex;
      gap: .75rem;
      min-block-size: 5.35rem;
      padding: 1rem;
      background: var(--bs-paper-bg);
      text-align: start;
      inline-size: 100%;
    }
    .series-source-card:hover:not([disabled]) {
      border-color: rgba(var(--bs-primary-rgb), .45);
      background: rgba(var(--bs-primary-rgb), .03);
    }
    .series-source-card[disabled] {
      opacity: .55;
      cursor: not-allowed;
    }
    .series-source-icon {
      align-items: center;
      background: rgba(var(--bs-primary-rgb), .1);
      border-radius: .45rem;
      color: var(--bs-primary);
      display: inline-flex;
      flex: 0 0 2.25rem;
      block-size: 2.25rem;
      inline-size: 2.25rem;
      justify-content: center;
    }
    .series-source-icon i {
      font-size: 1.25rem;
      line-height: 1;
    }
    .series-source-title,
    .series-source-description {
      display: -webkit-box;
      overflow: hidden;
      -webkit-box-orient: vertical;
    }
    .series-source-title {
      -webkit-line-clamp: 1;
      line-clamp: 1;
    }
    .series-source-description {
      -webkit-line-clamp: 2;
      line-clamp: 2;
    }
    .series-source-footer {
      align-items: center;
      display: flex;
      flex-wrap: wrap;
      gap: .75rem;
      justify-content: space-between;
    }
    @media (max-width: 767.98px) {
      .series-card > .card-body { padding: 1rem; }
      .series-compact-header { align-items: flex-start; }
      .series-action-row {
        justify-content: flex-end;
      }
      .series-picker > .d-flex > .series-action-row {
        justify-content: flex-start;
        inline-size: 100%;
      }
      .series-picker,
      .series-pathway {
        border-inline: 0;
        border-radius: 0;
        padding-inline: 0;
        background: transparent;
      }
      .series-pathways-zone {
        margin-block-start: 1rem;
      }
      .series-pathways-heading {
        margin-inline: -.15rem;
      }
      .series-picker-grid,
      .series-source-grid {
        grid-template-columns: minmax(0, 1fr);
      }
      .series-source-modal {
        max-width: calc(100vw - 2rem);
      }
    }
    @media (max-width: 430px) {
      .series-board { padding-inline: .125rem; }
      .series-page-title { font-size: 1.35rem; }
      .series-action-row .btn:not(.series-icon-btn),
      .series-picker .btn,
      .series-source-button {
        inline-size: 100%;
      }
      .series-card > .card-body { padding: .95rem; }
      .series-picker-grid { max-block-size: none; }
      .series-source-card {
        min-block-size: 4.75rem;
        padding: .85rem;
      }
      .series-source-footer {
        align-items: stretch;
        flex-direction: column;
      }
      .series-source-footer .btn {
        inline-size: 100%;
      }
    }
  </style>

  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div class="min-w-0">
      <h4 class="mb-1 series-page-title series-truncate" title="Series Tasks">Series Tasks</h4>
      <p class="mb-0 text-body-secondary series-truncate" title="Ordered Library content for {{ $subjectName }}">
        Ordered Library content for {{ $subjectName }}
      </p>
    </div>
    <button type="button" class="btn btn-primary" wire:click="toggleCreateTaskForm" wire:loading.attr="disabled" wire:target="toggleCreateTaskForm">
      <span class="spinner-border spinner-border-sm me-1" wire:loading wire:target="toggleCreateTaskForm" aria-hidden="true"></span>
      <i class="ti tabler-plus series-normal-icon me-1" wire:loading.remove wire:target="toggleCreateTaskForm"></i>
      New Series
    </button>
  </div>

  @if($boardFeedback)
    <div class="alert alert-{{ $boardFeedback['tone'] === 'warning' ? 'warning' : ($boardFeedback['tone'] === 'success' ? 'success' : 'info') }} d-flex align-items-start gap-2">
      <i class="ti tabler-info-circle mt-1"></i>
      <span>{{ $boardFeedback['message'] }}</span>
    </div>
  @endif

  <ul class="nav nav-pills flex-column flex-sm-row flex-wrap gap-2 mb-4">
    <li class="nav-item"><button type="button" class="nav-link {{ $taskScope === 'working' ? 'active' : '' }}" wire:click="setTaskScope('working')" wire:loading.attr="disabled">Working</button></li>
    <li class="nav-item"><button type="button" class="nav-link {{ $taskScope === 'archived' ? 'active' : '' }}" wire:click="setTaskScope('archived')" wire:loading.attr="disabled">Archived</button></li>
  </ul>

  @if($createTaskOpen)
    @php
      $draftSource = collect($collections)->firstWhere('key', $draftTask['collection_key'] ?? '');
    @endphp
    <div class="card series-card mb-4">
      <div class="card-header"><h5 class="card-title mb-0">New Series</h5></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-lg-4">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" wire:model.blur="draftTask.title">
            @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>
          <div class="col-12 col-lg-4">
            <label class="form-label">Library source</label>
            <button type="button" class="btn btn-label-secondary series-source-button w-100 d-flex align-items-center justify-content-between gap-2" wire:click="openCollectionPicker('draft')">
              <span class="series-truncate" title="{{ $draftSource['title'] ?? 'Choose Library source' }}">{{ $draftSource['title'] ?? 'Choose Library source' }}</span>
              <i class="ti tabler-folder-search series-normal-icon"></i>
            </button>
            @error('collection') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>
          <div class="col-6 col-lg-2">
            <label class="form-label">Default</label>
            <input type="number" min="0" max="255" class="form-control" wire:model.blur="draftTask.default_points">
            @error('default_points') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>
          <div class="col-6 col-lg-2">
            <label class="form-label">Max</label>
            <input type="number" min="0" max="255" class="form-control" wire:model.blur="draftTask.max_points">
          </div>
          <div class="col-12 col-lg-3">
            <label class="form-label">Task type</label>
            <select class="form-select" wire:model.live="draftTask.task_type_id">
              @foreach($taskTypes as $type)
                <option value="{{ $type['id'] }}">{{ $type['title'] }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12 col-lg-3">
            <label class="form-label">Recurrence</label>
            <select class="form-select" wire:model.live="draftTask.recurrence_kind">
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
            </select>
          </div>
          <div class="col-12 col-lg-3">
            <label class="form-label">Sequence</label>
            <select class="form-select" wire:model.live="draftTask.sequence_behavior">
              <option value="stop_at_end">Stop at end</option>
              <option value="loop">Loop</option>
            </select>
          </div>
          @if($seriesReleasePolicyEnabled)
            <div class="col-12 col-lg-3">
              <label class="form-label">Release</label>
              <select class="form-select" wire:model.live="draftTask.release_policy">
                <option value="continuous">Continuous</option>
                <option value="wait_for_completion">Wait for completion</option>
              </select>
            </div>
          @endif
          <div class="col-12 col-lg-{{ $seriesReleasePolicyEnabled ? '6' : '3' }}">
            <label class="form-label">Interval / day</label>
            @if(($draftTask['recurrence_kind'] ?? 'daily') === 'daily')
              <input type="number" min="1" max="31" class="form-control" wire:model.blur="draftTask.recurrence_interval">
            @elseif(($draftTask['recurrence_kind'] ?? 'daily') === 'monthly')
              <input type="number" min="1" max="31" class="form-control" wire:model.blur="draftTask.recurrence_day_of_month">
            @else
              <div class="d-flex flex-wrap gap-2">
                @foreach(['sun' => 'Sun', 'mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat'] as $dayKey => $dayLabel)
                  <label class="form-check form-check-inline mb-0"><input class="form-check-input" type="checkbox" value="{{ $dayKey }}" wire:model.live="draftTask.recurrence_weekdays"> <span class="form-check-label">{{ $dayLabel }}</span></label>
                @endforeach
              </div>
            @endif
          </div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" rows="2" wire:model.blur="draftTask.description"></textarea>
          </div>
          <div class="col-12 d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-primary" wire:click="createTask" wire:loading.attr="disabled" wire:target="createTask">
              <span class="spinner-border spinner-border-sm me-1" wire:loading wire:target="createTask" aria-hidden="true"></span>
              Create
            </button>
            <button type="button" class="btn btn-label-secondary" wire:click="toggleCreateTaskForm" wire:loading.attr="disabled">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  @endif

  <div class="d-grid gap-4">
    @forelse($tasks as $task)
      @php
        $taskExpanded = ! empty($expandedTasks[$task->id]);
        $assignedCount = $task->assignedStudentCount();
        $taskSource = collect($collections)->firstWhere('key', $taskForms[$task->id]['collection_key'] ?? '');
        $sourceLabel = $sourceLabelsByTask[$task->id] ?? ucwords(str_replace('_', ' ', (string) $task->library_collection_type));
        $releasePolicy = (string) ($taskForms[$task->id]['release_policy'] ?? $task->release_policy ?? 'continuous');
      @endphp
      <div class="card series-card" wire:key="series-task-{{ $task->id }}" x-data="{ open: @js($taskExpanded) }">
        <div class="card-body">
          <div class="series-card-header">
            <div class="series-compact-header">
              <div class="series-title-line text-start">
                <h5 class="mb-0 series-truncate" title="{{ $task->title }}">{{ $task->title }}</h5>
                @if($task->isActive())
                  <span class="series-check-status" title="Active" aria-label="Active">
                    <i class="ti tabler-circle-check series-normal-icon"></i>
                  </span>
                @else
                  <span class="badge bg-label-{{ $task->isArchived() ? 'secondary' : 'warning' }}">{{ ucfirst($task->status) }}</span>
                @endif
              </div>
              <div class="series-header-actions">
                <button type="button" class="btn btn-sm btn-icon btn-text-secondary series-icon-btn" wire:click="$toggle('settingsOpen.{{ $task->id }}')" title="Edit settings" aria-label="Edit settings"><i class="ti tabler-settings"></i></button>
                <div class="dropdown">
                  <button type="button" class="btn btn-sm btn-icon btn-text-secondary series-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Series actions">
                    <i class="ti tabler-dots-vertical"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    @if($task->isArchived())
                      <li><button type="button" class="dropdown-item text-primary" wire:click="restoreTask({{ $task->id }})" wire:loading.attr="disabled"><i class="ti tabler-rotate-clockwise me-2"></i>Restore</button></li>
                    @else
                      @if($task->isActive())
                        <li><button type="button" class="dropdown-item text-warning" wire:click="unpublishTask({{ $task->id }})" wire:loading.attr="disabled" wire:target="unpublishTask({{ $task->id }})"><i class="ti tabler-file-pencil me-2"></i>Draft</button></li>
                      @else
                        <li><button type="button" class="dropdown-item text-success" wire:click="publishTask({{ $task->id }})" wire:loading.attr="disabled" wire:target="publishTask({{ $task->id }})"><i class="ti tabler-cloud-upload me-2"></i>Activate</button></li>
                      @endif
                      <li><hr class="dropdown-divider"></li>
                      <li><button type="button" class="dropdown-item text-danger" wire:click="archiveTask({{ $task->id }})" wire:loading.attr="disabled"><i class="ti tabler-archive me-2"></i>Archive</button></li>
                    @endif
                  </ul>
                </div>
                <button type="button" class="btn btn-sm btn-icon btn-text-secondary series-icon-btn" x-on:click="open = !open" :aria-expanded="open.toString()" aria-label="Toggle details">
                  <i class="ti tabler-chevron-down" :class="{ 'rotate-180': open }"></i>
                </button>
              </div>
            </div>
            <div class="small series-meta-line" title="{{ $task->sequence_behavior === 'loop' ? 'Loop' : 'Stop' }} - {{ $seriesReleasePolicyEnabled ? ($releasePolicy === 'wait_for_completion' ? 'Wait for completion - ' : 'Continuous - ') : '' }}{{ $task->versions->count() }} pathways - {{ $assignedCount }} assigned - {{ $sourceLabel }}">
              <span class="series-meta-token is-sequence">{{ $task->sequence_behavior === 'loop' ? 'Loop' : 'Stop' }}</span>
              <span class="series-meta-separator">-</span>
              @if($seriesReleasePolicyEnabled)
                <span class="series-meta-token is-source">{{ $releasePolicy === 'wait_for_completion' ? 'Wait' : 'Continuous' }}</span>
                <span class="series-meta-separator">-</span>
              @endif
              <span class="series-meta-token is-count">{{ $task->versions->count() }} pathways</span>
              <span class="series-meta-separator">-</span>
              <span class="series-meta-token is-assigned">{{ $assignedCount }} assigned</span>
              <span class="series-meta-separator">-</span>
              <span class="series-meta-token is-source">{{ $sourceLabel }}</span>
            </div>
          </div>

          @if(! empty($publishErrors[$task->id]))
            <div class="alert alert-warning mt-3 mb-0"><ul class="mb-0 ps-3">@foreach($publishErrors[$task->id] as $error)<li>{{ $error }}</li>@endforeach</ul></div>
          @endif

          @if(! empty($legacySourceWarningsByTask[$task->id]))
            <div class="alert alert-warning mt-3 mb-0 d-flex gap-2 align-items-start">
              <i class="ti tabler-alert-triangle mt-1"></i>
              <div>
                <div class="fw-semibold">Shared Library migration needed</div>
                <div class="small">{{ $legacySourceWarningsByTask[$task->id] }} Create a new Series Task from Shared Library sources, then assign students to the new task when ready.</div>
              </div>
            </div>
          @endif

          @if(! empty($settingsOpen[$task->id]))
            <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true">
              <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <div class="min-w-0">
                      <h5 class="modal-title series-truncate" title="{{ $task->title }}">Series settings</h5>
                      <div class="text-body-secondary small series-truncate" title="{{ $task->title }}">{{ $task->title }}</div>
                    </div>
                    <button type="button" class="btn-close" wire:click="$toggle('settingsOpen.{{ $task->id }}')" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="row g-3">
                      <div class="col-12 col-lg-6"><label class="form-label">Title</label><input type="text" class="form-control" wire:model.blur="taskForms.{{ $task->id }}.title"></div>
                      <div class="col-12 col-lg-6">
                        <label class="form-label">Library source</label>
                        <button type="button" class="btn btn-label-secondary series-source-button w-100 d-flex align-items-center justify-content-between gap-2" wire:click="openCollectionPicker('task:{{ $task->id }}')" @disabled($task->isActive())>
                          <span class="series-truncate" title="{{ $taskSource['title'] ?? 'Choose Library source' }}">{{ $taskSource['title'] ?? 'Choose Library source' }}</span>
                          <i class="ti tabler-folder-search series-normal-icon"></i>
                        </button>
                      </div>
                      <div class="col-6 col-lg-3"><label class="form-label">Default</label><input type="number" class="form-control" wire:model.blur="taskForms.{{ $task->id }}.default_points">@error('default_points') <div class="text-danger small mt-1">{{ $message }}</div> @enderror</div>
                      <div class="col-6 col-lg-3"><label class="form-label">Max</label><input type="number" class="form-control" wire:model.blur="taskForms.{{ $task->id }}.max_points"></div>
                      <div class="col-12 col-lg-3"><label class="form-label">Recurrence</label><select class="form-select" wire:model.live="taskForms.{{ $task->id }}.recurrence_kind"><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly">Monthly</option></select></div>
                      <div class="col-12 col-lg-3"><label class="form-label">Sequence</label><select class="form-select" wire:model.live="taskForms.{{ $task->id }}.sequence_behavior"><option value="stop_at_end">Stop at end</option><option value="loop">Loop</option></select></div>
                      @if($seriesReleasePolicyEnabled)
                        <div class="col-12 col-lg-3"><label class="form-label">Release</label><select class="form-select" wire:model.live="taskForms.{{ $task->id }}.release_policy"><option value="continuous">Continuous</option><option value="wait_for_completion">Wait for completion</option></select></div>
                      @endif
                      <div class="col-12">
                        <label class="form-label">Interval / day</label>
                        @if(($taskForms[$task->id]['recurrence_kind'] ?? 'daily') === 'daily')
                          <input type="number" min="1" max="31" class="form-control" wire:model.blur="taskForms.{{ $task->id }}.recurrence_interval">
                        @elseif(($taskForms[$task->id]['recurrence_kind'] ?? 'daily') === 'monthly')
                          <input type="number" min="1" max="31" class="form-control" wire:model.blur="taskForms.{{ $task->id }}.recurrence_day_of_month">
                        @else
                          <div class="d-flex flex-wrap gap-2">@foreach(['sun' => 'Sun', 'mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat'] as $dayKey => $dayLabel)<label class="form-check form-check-inline mb-0"><input class="form-check-input" type="checkbox" value="{{ $dayKey }}" wire:model.live="taskForms.{{ $task->id }}.recurrence_weekdays"> <span class="form-check-label">{{ $dayLabel }}</span></label>@endforeach</div>
                        @endif
                      </div>
                      <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" rows="2" wire:model.blur="taskForms.{{ $task->id }}.description"></textarea></div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" wire:click="$toggle('settingsOpen.{{ $task->id }}')" wire:loading.attr="disabled">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveTask({{ $task->id }})" wire:loading.attr="disabled" wire:target="saveTask({{ $task->id }})"><span class="spinner-border spinner-border-sm me-1" wire:loading wire:target="saveTask({{ $task->id }})"></span>Save</button>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-backdrop fade show"></div>
          @endif

          <div x-show="open" x-cloak>
            <div class="series-pathways-zone">
              <div class="series-pathways-heading gap-2">
                <h6 class="mb-0">Pathways</h6>
                @if(! $task->isArchived())
                  <button type="button" class="btn btn-text-primary rounded-pill btn-icon series-icon-btn" wire:click="addVersion({{ $task->id }})" wire:loading.attr="disabled" title="Add pathway" aria-label="Add pathway"><i class="ti tabler-plus"></i></button>
                @endif
              </div>

              @foreach($task->versions as $version)
                @php
                  $versionExpanded = ! empty($expandedVersions[$version->id]) || ! empty($versionEditorsOpen[$version->id]);
                  $search = trim((string) ($itemSearches[$version->id] ?? ''));
                  $availableItems = collect($libraryItemsByTask[$task->id] ?? [])
                    ->filter(fn($libraryItem) => $search === '' || str_contains(mb_strtolower($libraryItem['title']), mb_strtolower($search)) || str_contains(str_replace('_', ' ', $libraryItem['source_type']), mb_strtolower($search)))
                    ->values();
                  $allItems = collect($libraryItemsByTask[$task->id] ?? []);
                  $selectedKeys = collect($itemSelections[$version->id] ?? [])->filter(fn($selected) => filter_var($selected, FILTER_VALIDATE_BOOLEAN))->keys()->sort()->values();
                  $currentKeys = $version->items->map(fn($item) => $item->library_source_type.':'.$item->library_source_id)->sort()->values();
                  $selectedCount = $selectedKeys->count();
                  $hasSelectionChanges = $selectedKeys->all() !== $currentKeys->all();
                  $versionAssignedCount = $version->assignedStudentCount();
                @endphp
                <section class="series-pathway" wire:key="series-version-{{ $version->id }}" x-data="{ open: @js($versionExpanded) }">
                  <div class="series-pathway-header">
                    <div class="series-compact-header">
                      <div class="series-title-line text-start">
                        <h6 class="mb-0 series-truncate" title="{{ $version->display_name }}">{{ $version->display_name }}</h6>
                      </div>
                      <div class="series-header-actions">
                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary series-icon-btn" x-on:click="open = true" wire:click.stop="$toggle('versionEditorsOpen.{{ $version->id }}')" title="Edit pathway" aria-label="Edit pathway"><i class="ti tabler-edit"></i></button>
                        <button type="button" class="btn btn-sm btn-icon btn-text-primary series-icon-btn" wire:click.stop="openAssignmentModal({{ $task->id }}, {{ $version->id }})" wire:loading.attr="disabled" title="Assign students" aria-label="Assign students"><i class="ti tabler-users"></i></button>
                        @if(! $task->isArchived())
                          <button type="button" class="btn btn-sm btn-icon btn-text-danger series-icon-btn" wire:click.stop="deleteVersion({{ $version->id }})" wire:confirm="Delete this pathway? Assigned students will stop receiving future tasks from it. Delivered work stays unchanged." wire:loading.attr="disabled" wire:target="deleteVersion({{ $version->id }})" title="Delete pathway" aria-label="Delete pathway"><i class="ti tabler-trash"></i></button>
                        @endif
                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary series-icon-btn" x-on:click="open = !open" :aria-expanded="open.toString()" aria-label="Toggle pathway details">
                          <i class="ti tabler-chevron-down" :class="{ 'rotate-180': open }"></i>
                        </button>
                      </div>
                    </div>
                    <div class="small series-meta-line" title="{{ $versionAssignedCount }} assigned">
                      <span class="series-meta-token is-assigned d-inline-flex align-items-center gap-1">
                        {{ $versionAssignedCount }} assigned
                        @if($task->isActive() && $versionAssignedCount > 0)
                          <details class="series-info">
                            <summary class="series-info__trigger" aria-label="Future generation note">
                              <i class="ti tabler-info-circle"></i>
                            </summary>
                            <div class="series-info__panel">
                              Updating selected items changes future generation only. Delivered student work stays unchanged.
                            </div>
                          </details>
                        @endif
                      </span>
                    </div>
                  </div>

                  <div class="mt-3" x-show="open" x-cloak>
                    @if(! empty($versionEditorsOpen[$version->id]))
                      <div class="row g-2 mb-3">
                        <div class="col-12 col-md-4"><input type="text" class="form-control" wire:model.blur="versionForms.{{ $version->id }}.display_name" aria-label="Pathway name"></div>
                        <div class="col-12 col-md-6"><input type="text" class="form-control" wire:model.blur="versionForms.{{ $version->id }}.description" placeholder="Optional note" aria-label="Pathway note"></div>
                        <div class="col-12 col-md-2 d-flex gap-2">
                          <button type="button" class="btn btn-primary flex-grow-1" wire:click="saveVersion({{ $version->id }})" wire:loading.attr="disabled" wire:target="saveVersion({{ $version->id }})">Save</button>
                        </div>
                      </div>
                    @endif

                    @if(! $task->isArchived())
                      <div class="series-picker">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                          <div class="min-w-0">
                            <h6 class="mb-0">Library items</h6>
                            <small class="text-body-secondary">{{ $selectedCount }} of {{ $allItems->count() }} selected</small>
                          </div>
                          <div class="series-action-row">
                            <button type="button" class="btn btn-sm btn-label-secondary" wire:click="selectAllVersionItems({{ $version->id }})">Select all</button>
                            <button type="button" class="btn btn-sm btn-label-secondary" wire:click="clearVersionItems({{ $version->id }})">Clear</button>
                            <button type="button" class="btn btn-sm btn-primary" wire:click="syncVersionItems({{ $version->id }})" wire:loading.attr="disabled" wire:target="syncVersionItems({{ $version->id }})" @disabled(! $hasSelectionChanges)>
                              <span class="spinner-border spinner-border-sm me-1" wire:loading wire:target="syncVersionItems({{ $version->id }})"></span>
                              Update
                            </button>
                          </div>
                        </div>
                        <input type="search" class="form-control mb-2" placeholder="Search Library items" wire:model.live.debounce.250ms="itemSearches.{{ $version->id }}">
                        <div class="series-picker-grid">
                          @forelse($availableItems as $libraryItem)
                            <label class="series-picker-option" title="{{ $libraryItem['title'] }}">
                              <input class="form-check-input mt-1" type="checkbox" wire:model.live="itemSelections.{{ $version->id }}.{{ $libraryItem['key'] }}">
                              <span class="min-w-0">
                                <span class="d-block fw-medium series-truncate">{{ $libraryItem['title'] }}</span>
                                <small class="text-body-secondary">{{ str_replace('_', ' ', $libraryItem['source_type']) }}</small>
                              </span>
                            </label>
                          @empty
                            <div class="text-body-secondary small">No matching Library items.</div>
                          @endforelse
                        </div>
                      </div>
                    @endif
                  </div>
                </section>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="bg-lighter rounded p-4 d-flex align-items-start gap-3">
        <span class="avatar-initial rounded bg-label-info d-inline-flex align-items-center justify-content-center p-2"><i class="ti tabler-route"></i></span>
        <div class="min-w-0"><h6 class="mb-1">No Series Tasks yet</h6><p class="mb-0 text-body-secondary">Create a series, choose a Library source, then update pathway Library items.</p></div>
      </div>
    @endforelse
  </div>

  <script>
    (() => {
      if (window.__seriesInfoClickAwayBound) return;
      window.__seriesInfoClickAwayBound = true;
      document.addEventListener('click', (event) => {
        document.querySelectorAll('.series-info[open]').forEach((detail) => {
          if (!detail.contains(event.target)) detail.removeAttribute('open');
        });
      });
    })();
  </script>

  @if($collectionPickerTarget)
    @php
      $legacyPickerType = $collectionPickerState['legacy_picker_type'];
      $sourceSearch = $collectionPickerState['source_search'];
      $localSourceCollections = $collectionPickerState['local_source_collections'];
      $vocabularySourceGroup = $collectionPickerState['vocabulary_source_group'];
      $legacySourceGroups = $collectionPickerState['legacy_source_groups'];
      $selectedSourceGroup = $collectionPickerState['selected_source_group'];
      $selectedSourceGroupLabel = $collectionPickerState['selected_source_group_label'];
    @endphp
    <div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered series-source-modal">
        <div class="modal-content">
          <div class="modal-header">
            <div class="min-w-0">
              <h5 class="modal-title">Choose Library source</h5>
              <div class="text-body-secondary small">Search Shared Library folders, then select the source folder for this Series Task.</div>
            </div>
            <button type="button" class="btn-close" wire:click="closeCollectionPicker" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="search" class="form-control mb-3" placeholder="Search Library folders" wire:model.live.debounce.250ms="collectionSearch">
            @if($collectionPickerType === 'general_library_folder')
              <div class="d-flex align-items-center gap-2 mb-3">
                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="goToCollectionTypes">
                  <i class="ti tabler-arrow-left"></i>
                  <span>Back</span>
                </button>
                <h6 class="mb-0">{{ $libraryPickerCurrentSection['title'] ?? 'Shared Library' }}</h6>
              </div>
              <div class="series-source-grid">
                @if(($libraryPickerCurrentSection['selectable'] ?? false) && ! filled($sourceSearch))
                  <button type="button" class="series-source-card" wire:click="chooseCollection('{{ $libraryPickerCurrentSection['key'] }}')">
                    <span class="series-source-icon bg-label-success text-success">
                      <i class="bx bx-check"></i>
                    </span>
                    <span class="min-w-0">
                      <span class="d-block fw-medium series-source-title">Use this folder</span>
                      <small class="text-body-secondary series-source-description">{{ $libraryPickerCurrentSection['direct_resource_count'] }} sources in this folder</small>
                    </span>
                  </button>
                @endif
                @foreach($localSourceCollections as $collection)
                  @php
                    $opensSubfolder = (int) ($collection['child_folder_count'] ?? 0) > 0;
                    $canUseFolder = (bool) ($collection['selectable'] ?? false);
                  @endphp
                  <button
                    type="button"
                    class="series-source-card"
                    @if($opensSubfolder)
                      wire:click="enterLibrarySection({{ (int) str_replace('general_library_folder:', '', $collection['key']) }})"
                    @elseif($canUseFolder)
                      wire:click="chooseCollection('{{ $collection['key'] }}')"
                    @endif
                    @disabled(! $opensSubfolder && ! $canUseFolder)>
                    <span class="series-source-icon">
                      <i class="bx {{ $opensSubfolder ? 'bx-folder-open' : 'bx-folder' }}"></i>
                    </span>
                    <span class="min-w-0">
                      <span class="d-block fw-medium series-source-title" title="{{ $collection['title'] }}">{{ $collection['title'] }}</span>
                      <small class="text-body-secondary series-source-description">{{ $collection['selectable'] || $opensSubfolder ? ($collection['description'] ?? 'Ready') : ($collection['blocked_reason'] ?? 'Empty folder') }}</small>
                    </span>
                  </button>
                @endforeach
                @if($localSourceCollections->isEmpty() && ! ($libraryPickerCurrentSection['selectable'] ?? false))
                  <div class="bg-lighter rounded p-3 text-body-secondary">No folders or ready sources here.</div>
                @endif
              </div>
            @elseif($collectionPickerType === $legacyPickerType)
              <div class="d-flex align-items-center gap-2 mb-3">
                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="goToCollectionTypes">
                  <i class="ti tabler-arrow-left"></i>
                  <span>Back</span>
                </button>
                <h6 class="mb-0">Additional source groups</h6>
              </div>
              <div class="series-source-grid">
                @forelse($legacySourceGroups as $type => $group)
                  <button type="button" class="series-source-card" wire:click="enterCollectionType('{{ $type }}')">
                    <span class="series-source-icon">
                      <i class="bx bx-folder"></i>
                    </span>
                    <span class="min-w-0">
                      <span class="d-block fw-medium series-source-title" title="{{ $group->first()['type_label'] ?? str_replace('_', ' ', $type) }}">{{ $group->first()['type_label'] ?? str_replace('_', ' ', $type) }}</span>
                      <small class="text-body-secondary series-source-description">{{ $group->count() }} sources</small>
                    </span>
                  </button>
                @empty
                  <div class="bg-lighter rounded p-3 text-body-secondary">No matching Library sources.</div>
                @endforelse
              </div>
            @elseif($collectionPickerType)
              <div class="d-flex align-items-center gap-2 mb-3">
                <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="goToCollectionTypes">
                  <i class="ti tabler-arrow-left"></i>
                  <span>Back</span>
                </button>
                <div class="min-w-0">
                  <h6 class="mb-0">{{ $selectedSourceGroupLabel }}</h6>
                  @if($collectionPickerType === 'vocabulary')
                    <small class="text-body-secondary">Open folders until you reach the folder that directly contains the lesson lists, then select it.</small>
                  @endif
                </div>
              </div>
              <div class="series-source-grid">
                @forelse($selectedSourceGroup as $collection)
                  @php
                    $opensVocabularyFolder = $collectionPickerType === 'vocabulary' && (int) ($collection['child_folder_count'] ?? 0) > 0;
                    $canUseSource = (bool) ($collection['selectable'] ?? false);
                  @endphp
                  <button
                    type="button"
                    class="series-source-card"
                    @if($opensVocabularyFolder)
                      wire:click="enterVocabularyFolder({{ (int) str_replace('vocabulary:', '', $collection['key']) }})"
                    @elseif($canUseSource)
                      wire:click="chooseCollection('{{ $collection['key'] }}')"
                    @endif
                    @disabled(! $opensVocabularyFolder && ! $canUseSource)>
                    <span class="series-source-icon">
                      <i class="bx {{ $opensVocabularyFolder ? 'bx-folder-open' : 'bx-folder' }}"></i>
                    </span>
                    <span class="min-w-0">
                      <span class="d-block fw-medium series-source-title" title="{{ $collection['title'] }}">{{ $collection['title'] }}</span>
                      <small class="text-body-secondary series-source-description">{{ $canUseSource || $opensVocabularyFolder ? ($collection['description'] ?? 'Ready') : ($collection['blocked_reason'] ?? 'Empty folder') }}</small>
                    </span>
                  </button>
                @empty
                  <div class="bg-lighter rounded p-3 text-body-secondary">No matching Library sources.</div>
                @endforelse
              </div>
            @else
              <div class="series-source-grid">
                @foreach($localSourceCollections as $collection)
                  @php
                    $opensSubfolder = (int) ($collection['child_folder_count'] ?? 0) > 0;
                    $canUseFolder = (bool) ($collection['selectable'] ?? false);
                  @endphp
                  <button
                    type="button"
                    class="series-source-card"
                    @if($opensSubfolder)
                      wire:click="enterLibrarySection({{ (int) str_replace('general_library_folder:', '', $collection['key']) }})"
                    @elseif($canUseFolder)
                      wire:click="chooseCollection('{{ $collection['key'] }}')"
                    @endif
                    @disabled(! $opensSubfolder && ! $canUseFolder)>
                    <span class="series-source-icon">
                      <i class="bx {{ $opensSubfolder ? 'bx-folder-open' : 'bx-folder' }}"></i>
                    </span>
                    <span class="min-w-0">
                      <span class="d-block fw-medium series-source-title" title="{{ $collection['title'] }}">{{ $collection['title'] }}</span>
                      <small class="text-body-secondary series-source-description">{{ $collection['selectable'] || $opensSubfolder ? ($collection['description'] ?? 'Ready') : ($collection['blocked_reason'] ?? 'Empty folder') }}</small>
                    </span>
                  </button>
                @endforeach
                @if($vocabularySourceGroup->isNotEmpty())
                  <button type="button" class="series-source-card" wire:click="enterCollectionType('vocabulary')">
                    <span class="series-source-icon">
                      <i class="bx bx-folder"></i>
                    </span>
                    <span class="min-w-0">
                      <span class="d-block fw-medium series-source-title">Vocabulary</span>
                      <small class="text-body-secondary series-source-description">{{ $vocabularySourceGroup->count() }} folders</small>
                    </span>
                  </button>
                @endif
                @if($legacySourceGroups->isNotEmpty())
                  <button type="button" class="series-source-card" wire:click="enterLegacyCollectionTypes">
                    <span class="series-source-icon">
                      <i class="bx bx-collection"></i>
                    </span>
                    <span class="min-w-0">
                      <span class="d-block fw-medium series-source-title">Additional source groups</span>
                      <small class="text-body-secondary series-source-description">{{ $legacySourceGroups->flatten(1)->count() }} sources</small>
                    </span>
                  </button>
                @endif
                @if($localSourceCollections->isEmpty() && $vocabularySourceGroup->isEmpty() && $legacySourceGroups->isEmpty())
                  <div class="bg-lighter rounded p-3 text-body-secondary">No matching Library sources.</div>
                @endif
              </div>
            @endif
          </div>
          <div class="modal-footer series-source-footer">
            <button type="button" class="btn btn-outline-danger me-auto" wire:click="clearCollectionSelection">
              <i class="ti tabler-x"></i>
              <span>Clear selection</span>
            </button>
            <button type="button" class="btn btn-label-secondary" wire:click="closeCollectionPicker">Cancel</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show"></div>
  @endif

  <livewire:teacher.series-task-assignment-modal wire:key="series-task-assignment-modal" />
</div>
