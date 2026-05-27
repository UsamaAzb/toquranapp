@push('styles')
  @once
    @include('components.sessions.board-styles')

    <style>
      .dt-board {
        --w14-session-surface: var(--bs-paper-bg, var(--bs-card-bg));
        --w14-session-text: var(--bs-heading-color);
        --w14-session-muted: var(--bs-secondary-color);
        --w14-session-border: var(--bs-border-color);
        --w14-session-soft-border: color-mix(in sRGB, var(--bs-border-color) 76%, transparent);
        --w14-session-primary-soft: rgba(var(--bs-primary-rgb), 0.09);
        --w14-session-primary-border: rgba(var(--bs-primary-rgb), 0.18);
        --w14-session-primary-hover: rgba(var(--bs-primary-rgb), 0.08);
        --w14-session-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.06);
      }

      [data-bs-theme="dark"] .dt-board {
        --w14-session-surface: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 92%, white 8%);
        --w14-session-muted: color-mix(in sRGB, var(--bs-heading-color) 76%, var(--bs-primary));
        --w14-session-soft-border: rgba(var(--bs-primary-rgb), 0.22);
        --w14-session-primary-border: rgba(var(--bs-primary-rgb), 0.28);
        --w14-session-primary-hover: rgba(var(--bs-primary-rgb), 0.14);
        --w14-session-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.22);
      }

      .dt-board .dt-task-card {
        border: 1px solid rgba(67, 89, 113, 0.12);
      }

      .dt-board .dt-task-card + .dt-task-card {
        margin-top: 1rem;
      }

      .dt-board .dt-task-header {
        background: rgba(67, 89, 113, 0.018);
        border-bottom: 1px solid rgba(67, 89, 113, 0.055);
        margin-bottom: 20px;
      }

      .dt-board .dt-status-line {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 0.5rem;
      }

      .dt-board .dt-attachment-chip {
        align-items: center;
        border: 1px solid rgba(67, 89, 113, 0.14);
        border-radius: 0.5rem;
        color: inherit;
        display: inline-flex;
        gap: 0.45rem;
        max-width: 100%;
        min-height: 2.25rem;
        padding: 0.4rem 0.6rem;
        text-decoration: none;
      }

      .dt-board .dt-attachment-chip span {
        display: inline-block;
        max-width: 12rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .dt-board .dt-attachment-chip-wrap .session-attachment-chip {
        max-width: min(240px, 100%);
      }

      .dt-board .atask-task-attachments {
        padding: 0;
      }

      .dt-board .atask-attachment-timeline {
        display: grid;
        gap: 0;
      }

      .dt-board .atask-attachment-group {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: 1.5rem minmax(0, 1fr);
        padding: 0.85rem 0;
        position: relative;
      }

      .dt-board .atask-attachment-group::before {
        background: var(--w14-session-soft-border);
        bottom: -0.15rem;
        content: "";
        left: 0.45rem;
        position: absolute;
        top: 2rem;
        width: 1px;
      }

      .dt-board .atask-attachment-group:last-child::before {
        display: none;
      }

      .dt-board .atask-attachment-dot {
        border-radius: 999px;
        box-shadow: 0 0 0 4px var(--w14-session-surface);
        height: 0.78rem;
        margin-left: 0.09rem;
        margin-top: 0.32rem;
        width: 0.78rem;
        z-index: 1;
      }

      .dt-board .atask-attachment-dot--files {
        background: var(--bs-primary);
      }

      .dt-board .atask-attachment-dot--youtube {
        background: var(--bs-success);
      }

      .dt-board .atask-attachment-dot--links {
        background: var(--bs-danger);
      }

      .dt-board .atask-attachment-group-body {
        min-width: 0;
      }

      .dt-board .atask-attachment-group-head {
        align-items: center;
        display: flex;
        gap: 0.5rem;
        justify-content: space-between;
        margin-bottom: 0.6rem;
        min-width: 0;
      }

      .dt-board .atask-attachment-chip-wrap {
        min-width: 0;
      }

      .dt-board .dt-version-row {
        border: 1px solid rgba(67, 89, 113, 0.12);
        border-radius: 0.5rem;
      }

      .dt-board .dt-version-row + .dt-version-row {
        margin-top: 0.75rem;
      }

      .dt-board .dt-version-editor {
        background: var(--bs-body-bg);
        border-top: 1px solid rgba(67, 89, 113, 0.1);
      }

      .dt-board .dt-create-panel,
      .dt-board .dt-settings-panel {
        background: var(--bs-body-bg);
        border: 1px solid rgba(67, 89, 113, 0.12);
        border-radius: 0.5rem;
      }

      .dt-board .w14-attachment-input {
        background: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 94%, var(--bs-primary));
        border: 1px dashed color-mix(in sRGB, var(--bs-border-color) 82%, var(--bs-primary));
        border-radius: 0.85rem;
        min-width: 0;
        padding: 1rem;
      }

      .dt-board .w14-attachment-input .form-control {
        max-width: 100%;
        min-width: 0;
      }

      .dt-board .w14-attachment-help {
        color: var(--bs-secondary-color);
        font-size: 0.82rem;
        margin-top: 0.5rem;
      }

      @media (max-width: 575.98px) {
        .dt-board .w14-attachment-input {
          padding: 0.75rem;
        }
      }
    </style>
  @endonce
@endpush

<div class="dt-board">
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
      <h4 class="mb-1">Differentiated Tasks</h4>
      <div class="fw-semibold text-body mb-1">{{ $subjectName }}</div>
      <p class="mb-0 text-body-secondary">One scheduled automated task with student-specific task versions.</p>
    </div>
    <button type="button" class="btn btn-primary" wire:click="toggleCreateTaskForm">
      <i class="ti tabler-plus me-1"></i>
      Add Differentiated Task
    </button>
  </div>

  @if($boardFeedback)
    <div class="alert alert-{{ $boardFeedback['tone'] ?? 'info' }} alert-dismissible" role="alert">
      {{ $boardFeedback['message'] ?? '' }}
      <button type="button" class="btn-close" wire:click="dismissBoardFeedback" aria-label="Close"></button>
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0 ps-3">
        @foreach($errors->all() as $message)
          <li>{{ $message }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
      <a href="{{ $this->scopeUrl('working') }}"
         class="nav-link {{ $taskScope === 'working' ? 'active' : '' }}"
         wire:click.prevent="setTaskScope('working')">
        Working
        <span class="badge bg-label-secondary ms-1">{{ $scopeCounts['working'] ?? 0 }}</span>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ $this->scopeUrl('archived') }}"
         class="nav-link {{ $taskScope === 'archived' ? 'active' : '' }}"
         wire:click.prevent="setTaskScope('archived')">
        Archived
        <span class="badge bg-label-secondary ms-1">{{ $scopeCounts['archived'] ?? 0 }}</span>
      </a>
    </li>
  </ul>

  @if($createTaskOpen)
    @php
      $draftRecurrenceKind = $draftTask['recurrence_kind'] ?? 'daily';
    @endphp
    <div class="dt-create-panel p-3 mb-4">
      <div class="row g-3">
        <div class="col-12 col-lg-5">
          <label class="form-label">Title</label>
          <input type="text" class="form-control" wire:model="draftTask.title" autocomplete="off">
        </div>
        <div class="col-12 col-lg-3">
          <label class="form-label">Task type</label>
          <select class="form-select" wire:model="draftTask.task_type_id">
            @foreach($taskTypes as $type)
              <option value="{{ $type['id'] }}">{{ $type['title'] }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-lg-2">
          <label class="form-label">Default pts</label>
          <input type="number" min="0" class="form-control" wire:model="draftTask.default_points">
        </div>
        <div class="col-6 col-lg-2">
          <label class="form-label">Max pts</label>
          <input type="number" min="0" class="form-control" wire:model="draftTask.max_points">
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea class="form-control" rows="2" wire:model="draftTask.description"></textarea>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Recurrence</label>
          <select class="form-select" wire:model.live="draftTask.recurrence_kind">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
        </div>
        @if($draftRecurrenceKind === 'daily')
          <div class="col-12 col-md-3">
            <label class="form-label">Interval</label>
            <input type="number" min="1" max="12" class="form-control" wire:model="draftTask.recurrence_interval">
          </div>
        @elseif($draftRecurrenceKind === 'weekly')
          <div class="col-12 col-md-6">
            <label class="form-label">Weekly days</label>
            <div class="d-flex flex-wrap gap-2">
              @foreach(['mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat', 'sun' => 'Sun'] as $key => $label)
                <label class="form-check form-check-inline m-0">
                  <input type="checkbox" class="form-check-input" wire:model="draftTask.recurrence_weekdays" value="{{ $key }}">
                  <span class="form-check-label">{{ $label }}</span>
                </label>
              @endforeach
            </div>
          </div>
        @elseif($draftRecurrenceKind === 'monthly')
          <div class="col-12 col-md-3">
            <label class="form-label">Month day</label>
            <input type="number" min="1" max="31" class="form-control" wire:model="draftTask.recurrence_day_of_month">
          </div>
        @endif
        <div class="col-12 d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-label-secondary" wire:click="toggleCreateTaskForm">Cancel</button>
          <button type="button" class="btn btn-primary" wire:click="createTask" wire:loading.attr="disabled" wire:target="createTask">
            <span wire:loading.remove wire:target="createTask">Create</span>
            <span wire:loading wire:target="createTask" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </div>
  @endif

  @forelse($tasks as $task)
    @php
      $taskForm = $taskForms[$task->id] ?? [];
      $taskRecurrenceKind = $taskForm['recurrence_kind'] ?? $task->recurrence_kind;
      $taskAssignmentCount = $taskAssignmentCounts[$task->id] ?? 0;
      $snapshotCount = $snapshotCountsByTask[$task->id] ?? 0;
      $validVersionCount = $task->versions->filter(fn($version) => $version->hasMeaningfulContent())->count();
      $isExpanded = isset($expandedTasks[$task->id]);
      $statusTone = match($task->status) {
        'active' => 'success',
        'archived' => 'secondary',
        default => 'warning',
      };
      $recurrenceSummary = match($task->recurrence_kind) {
        'weekly' => 'Weekly'.($task->recurrence_weekdays ? ' on '.$task->recurrence_weekdays : ''),
        'monthly' => 'Monthly on day '.($task->recurrence_day_of_month ?: '?'),
        default => 'Every '.max(1, (int) $task->recurrence_interval).' day(s)',
      };
    @endphp

    <div class="card dt-task-card" wire:key="dt-task-{{ $task->id }}">
      <div class="card-header dt-task-header">
        <div class="d-flex flex-wrap justify-content-between gap-3">
          <div style="min-width:0;">
            <div class="d-flex align-items-center gap-2 mb-1">
              <h5 class="mb-0 text-truncate">{{ $task->title }}</h5>
              <span class="badge bg-label-{{ $statusTone }}">{{ ucfirst($task->status) }}</span>
            </div>
            <div class="dt-status-line text-body-secondary small">
              <span><i class="ti tabler-category-2 me-1"></i>{{ $task->taskType?->title ?? 'Task' }}</span>
              <span><i class="ti tabler-repeat me-1"></i>{{ $recurrenceSummary }}</span>
              <span><i class="ti tabler-stack-2 me-1"></i>{{ $task->versions->count() }} versions</span>
              <span><i class="ti tabler-users me-1"></i>{{ $taskAssignmentCount }} assigned</span>
              <span><i class="ti tabler-send me-1"></i>{{ $snapshotCount }} delivered</span>
              <span><i class="ti tabler-checklist me-1"></i>{{ $validVersionCount }} ready</span>
            </div>
          </div>
          <div class="d-flex align-items-start gap-1">
            @if($task->status !== 'archived')
              <button type="button" class="btn btn-sm btn-icon btn-text-secondary" wire:click="openSettings({{ $task->id }})" aria-label="Settings">
                <i class="ti tabler-settings"></i>
              </button>
              @if($task->status === 'active')
                <button type="button" class="btn btn-sm btn-outline-warning" wire:click="unpublishTask({{ $task->id }})">Send to draft</button>
              @else
                <button type="button"
                        class="btn btn-sm btn-primary"
                        wire:click="publishTask({{ $task->id }})"
                        wire:loading.attr="disabled"
                        wire:target="publishTask({{ $task->id }})">
                  <span wire:loading.remove wire:target="publishTask({{ $task->id }})">Activate</span>
                  <span wire:loading wire:target="publishTask({{ $task->id }})">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Activating...
                  </span>
                </button>
              @endif
              <button type="button" class="btn btn-sm btn-icon btn-text-secondary" wire:click="archiveTask({{ $task->id }})" wire:confirm="Archive this Differentiated Task? Generated student work stays unchanged." aria-label="Archive">
                <i class="ti tabler-archive"></i>
              </button>
            @else
              <button type="button" class="btn btn-sm btn-outline-primary" wire:click="restoreTask({{ $task->id }})">Restore</button>
            @endif
            @if($task->status === 'draft')
              <button type="button" class="btn btn-sm btn-icon btn-text-danger" wire:click="deleteTask({{ $task->id }})" wire:confirm="Delete this draft Differentiated Task?" aria-label="Delete">
                <i class="ti tabler-trash"></i>
              </button>
            @endif
            <button type="button" class="btn btn-sm btn-icon btn-text-secondary" data-bs-toggle="collapse" data-bs-target="#dt-task-body-{{ $task->id }}" aria-expanded="{{ $isExpanded ? 'true' : 'false' }}" aria-label="Toggle task">
              <i class="ti tabler-chevron-down"></i>
            </button>
          </div>
        </div>
      </div>

      <div id="dt-task-body-{{ $task->id }}" class="collapse {{ $isExpanded ? 'show' : '' }}">
        <div class="card-body">
          @if(! empty($publishErrors[$task->id]))
            <div class="alert alert-warning">
              <div class="fw-semibold mb-2">Activation is blocked</div>
              <ul class="mb-0 ps-3">
                @foreach($publishErrors[$task->id] as $message)
                  <li>{{ $message }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @if(isset($settingsOpen[$task->id]))
            <div class="dt-settings-panel p-3 mb-4">
              <div class="row g-3">
                <div class="col-12 col-lg-5">
                  <label class="form-label">Title</label>
                  <input type="text" class="form-control" wire:model="taskForms.{{ $task->id }}.title" autocomplete="off">
                </div>
                <div class="col-12 col-lg-3">
                  <label class="form-label">Task type</label>
                  <select class="form-select" wire:model="taskForms.{{ $task->id }}.task_type_id">
                    @foreach($taskTypes as $type)
                      <option value="{{ $type['id'] }}">{{ $type['title'] }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-6 col-lg-2">
                  <label class="form-label">Default pts</label>
                  <input type="number" min="0" class="form-control" wire:model="taskForms.{{ $task->id }}.default_points">
                </div>
                <div class="col-6 col-lg-2">
                  <label class="form-label">Max pts</label>
                  <input type="number" min="0" class="form-control" wire:model="taskForms.{{ $task->id }}.max_points">
                </div>
                <div class="col-12">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" rows="2" wire:model="taskForms.{{ $task->id }}.description"></textarea>
                </div>
                <div class="col-12 col-md-3">
                  <label class="form-label">Recurrence</label>
                  <select class="form-select" wire:model.live="taskForms.{{ $task->id }}.recurrence_kind">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                  </select>
                </div>
                @if($taskRecurrenceKind === 'daily')
                  <div class="col-12 col-md-3">
                    <label class="form-label">Interval</label>
                    <input type="number" min="1" max="12" class="form-control" wire:model="taskForms.{{ $task->id }}.recurrence_interval">
                  </div>
                @elseif($taskRecurrenceKind === 'weekly')
                  <div class="col-12 col-md-6">
                    <label class="form-label">Weekly days</label>
                    <div class="d-flex flex-wrap gap-2">
                      @foreach(['mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat', 'sun' => 'Sun'] as $key => $label)
                        <label class="form-check form-check-inline m-0">
                          <input type="checkbox" class="form-check-input" wire:model="taskForms.{{ $task->id }}.recurrence_weekdays" value="{{ $key }}">
                          <span class="form-check-label">{{ $label }}</span>
                        </label>
                      @endforeach
                    </div>
                  </div>
                @elseif($taskRecurrenceKind === 'monthly')
                  <div class="col-12 col-md-3">
                    <label class="form-label">Month day</label>
                    <input type="number" min="1" max="31" class="form-control" wire:model="taskForms.{{ $task->id }}.recurrence_day_of_month">
                  </div>
                @endif
                <div class="col-12 d-flex justify-content-end gap-2">
                  <button type="button" class="btn btn-label-secondary" wire:click="closeSettings({{ $task->id }})">Cancel</button>
                  <button type="button" class="btn btn-primary" wire:click="saveTask({{ $task->id }})">Save settings</button>
                </div>
              </div>
            </div>
          @endif

          <div class="mb-4">
            <div class="small text-body-secondary mb-1">Task description</div>
            <p class="mb-0 text-body-secondary">{{ filled($task->description) ? $task->description : 'No description added yet.' }}</p>
          </div>

          <div class="row g-4">
            <div class="col-12 col-xl-5">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Attachment Pool</h6>
                <span class="badge bg-label-secondary">{{ $task->attachments->count() }}</span>
              </div>

              <div class="mb-3">
                @if($task->attachments->isEmpty())
                  <p class="text-body-secondary mb-0">No attachments in the pool yet.</p>
                @else
                  @php
                    $attachmentGroups = [
                      ['key' => 'files', 'label' => 'Files', 'types' => ['file'], 'items' => $task->attachments->where('type', 'file')],
                      ['key' => 'youtube', 'label' => 'YouTube', 'types' => ['youtube'], 'items' => $task->attachments->where('type', 'youtube')],
                      ['key' => 'links', 'label' => 'Links', 'types' => ['link'], 'items' => $task->attachments->where('type', 'link')],
                    ];
                  @endphp

                  <div class="atask-task-attachments atask-attachment-timeline">
                    @foreach($attachmentGroups as $group)
                      @if($group['items']->isNotEmpty())
                        <div class="atask-attachment-group">
                          <span class="atask-attachment-dot atask-attachment-dot--{{ $group['key'] }}" aria-hidden="true"></span>
                          <div class="atask-attachment-group-body">
                            <div class="atask-attachment-group-head">
                              <span class="fw-semibold">{{ $group['label'] }}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2 atask-attachment-chip-wrap">
                              @foreach($group['items'] as $attachment)
                                <div class="d-inline-flex align-items-center gap-1 dt-attachment-chip-wrap">
                                  <x-sessions.attachment-chip
                                    :attachment="[
                                      'id' => $attachment->id,
                                      'type' => $attachment->type,
                                      'name' => $attachment->title ?: 'Attachment',
                                      'path' => $attachment->path ?? $attachment->url ?? '',
                                      'url' => $attachment->isFile()
                                        ? \Illuminate\Support\Facades\Storage::disk('public')->url((string) $attachment->path)
                                        : (string) ($attachment->url ?? $attachment->path),
                                    ]"
                                    :differentiated-task-id="$task->id"
                                    :variant-index="$loop->index" />
                                  <button type="button"
                                          class="btn btn-sm btn-icon btn-text-danger"
                                          wire:click="deleteAttachment({{ $attachment->id }})"
                                          wire:loading.attr="disabled"
                                          wire:target="deleteAttachment({{ $attachment->id }})"
                                          aria-label="Remove attachment">
                                    <i class="ti tabler-x"></i>
                                  </button>
                                </div>
                              @endforeach
                            </div>
                          </div>
                        </div>
                      @endif
                    @endforeach
                  </div>
                @endif
              </div>

              @if($task->status !== 'archived')
                <div class="row g-2">
                  <div class="col-12">
                    <div class="w14-attachment-input">
                      <label class="form-label">Files</label>
                      <input id="dtTaskFilesInput-{{ $task->id }}"
                             type="file"
                             class="form-control"
                             wire:model="taskFilesByTask.{{ $task->id }}"
                             wire:loading.attr="disabled"
                             wire:target="taskFilesByTask.{{ $task->id }}"
                             multiple
                             accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.gif,.mp4,.mov,.m4v,.webm,.ogg,.mp3,.wav">
                      <div class="w14-attachment-help">
                        Documents, images, audio, and video. Max 50 MB per file.
                      </div>
                      <span class="small text-body-secondary" wire:loading wire:target="taskFilesByTask.{{ $task->id }}">
                        Uploading selected files...
                      </span>
                      @error('uploads')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                      @enderror
                      @error('uploads.*')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                      @enderror
                    </div>
                    <div class="progress mt-2" style="height: 6px;" wire:loading wire:target="taskFilesByTask.{{ $task->id }}">
                      <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                    </div>
                  </div>
                  <div class="col-12">
                    <button type="button"
                            class="btn btn-outline-primary w-100"
                            wire:click="openLibraryPicker({{ $task->id }})"
                            wire:loading.attr="disabled"
                            wire:target="openLibraryPicker({{ $task->id }})">
                      <i class="ti tabler-books me-1"></i>
                      Choose from Library
                    </button>
                  </div>
                  <div class="col-12">
                    @include('livewire.teacher.partials.task-external-attachment-rows', [
                      'linkTitleModel' => 'attachmentForms.'.$task->id.'.link_title',
                      'linkUrlModel' => 'attachmentForms.'.$task->id.'.link_url',
                      'youtubeTitleModel' => 'attachmentForms.'.$task->id.'.youtube_title',
                      'youtubeUrlModel' => 'attachmentForms.'.$task->id.'.youtube_url',
                      'addLinkAction' => 'addLinkAttachment('.$task->id.')',
                      'addYoutubeAction' => 'addYoutubeAttachment('.$task->id.')',
                      'linkPendingError' => 'attachmentForms.'.$task->id.'.link',
                      'youtubePendingError' => 'attachmentForms.'.$task->id.'.youtube',
                      'linkTitleError' => 'link_title',
                      'linkUrlError' => 'link_url',
                      'youtubeTitleError' => 'youtube_title',
                      'youtubeUrlError' => 'youtube_url',
                      'busyTargets' => 'addLinkAttachment,addYoutubeAttachment,taskFilesByTask.'.$task->id,
                    ])
                  </div>
                </div>
              @endif
            </div>

            <div class="col-12 col-xl-7">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h6 class="mb-0">Versions</h6>
                  @if($validVersionCount < 2)
                    <div class="small text-body-secondary mt-1">Assignments unlock after two ready versions.</div>
                  @endif
                </div>
                @if($task->status !== 'archived')
                  <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addVersion({{ $task->id }})">
                    <i class="ti tabler-plus me-1"></i>
                    Add version
                  </button>
                @endif
              </div>

              @forelse($task->versions as $version)
                @php
                  $diagnosis = $versionDiagnostics[$task->id][$version->id] ?? ['passes' => false, 'errors' => []];
                  $assignedCount = $versionAssignmentCounts[$version->id] ?? 0;
                  $versionForm = $versionForms[$version->id] ?? ['selected_attachment_ids' => []];
                @endphp
                <div class="dt-version-row" wire:key="dt-version-{{ $version->id }}">
                  <div class="p-3 d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div style="min-width:0;">
                      <div class="d-flex align-items-center gap-2 mb-1">
                        <h6 class="mb-0 text-truncate">{{ $version->display_name }}</h6>
                        @if($diagnosis['passes'])
                          <span class="badge bg-label-success">Ready</span>
                        @else
                          <span class="badge bg-label-warning">Needs content</span>
                        @endif
                      </div>
                      <div class="text-body-secondary small">
                        {{ $version->selectedAttachments->count() }} selected attachments - {{ $assignedCount }} assigned
                      </div>
                    </div>
                    <div class="d-flex gap-1">
                      @if($task->status === 'archived')
                        <span class="badge bg-label-secondary align-self-center">Restore to assign</span>
                      @else
                        <button type="button"
                                class="btn btn-sm btn-outline-info"
                                wire:click="openAssignmentModal({{ $task->id }}, {{ $version->id }})"
                                title="{{ $validVersionCount >= 2 && $diagnosis['passes'] ? 'Manage students for this version.' : 'At least two ready versions are required before assignment.' }}">
                          <i class="ti tabler-user-plus me-1"></i>
                          Assign
                        </button>
                      @endif
                      @if($task->status !== 'archived')
                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary" wire:click="openVersionEditor({{ $version->id }})" aria-label="Edit version">
                          <i class="ti tabler-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-icon btn-text-danger" wire:click="deleteVersion({{ $version->id }})" wire:confirm="Delete this version? Current assignments move to unassigned for future generation. Already delivered student work stays unchanged." aria-label="Delete version">
                          <i class="ti tabler-trash"></i>
                        </button>
                      @endif
                    </div>
                  </div>

                  @if(isset($versionEditorsOpen[$version->id]))
                    <div class="dt-version-editor p-3">
                      <div class="row g-3">
                        <div class="col-12 col-md-5">
                          <label class="form-label">Display name</label>
                          <input type="text"
                                 class="form-control"
                                 wire:model="versionForms.{{ $version->id }}.display_name"
                                 autocomplete="off"
                                 name="dt-version-display-name-{{ $version->id }}"
                                 inputmode="text">
                        </div>
                        <div class="col-12 col-md-7">
                          <label class="form-label">Description</label>
                          <textarea class="form-control" rows="2" wire:model="versionForms.{{ $version->id }}.description" autocomplete="off"></textarea>
                        </div>
                        <div class="col-12">
                          <label class="form-label">Selected attachments</label>
                          @if($task->attachments->isEmpty())
                            <div class="text-body-secondary">Add attachments to the pool before selecting them for this version.</div>
                          @else
                            <div class="d-flex flex-wrap gap-3">
                              @foreach($task->attachments as $attachment)
                                <label class="form-check m-0">
                                  <input type="checkbox"
                                         class="form-check-input"
                                         wire:model="versionForms.{{ $version->id }}.selected_attachment_ids"
                                         value="{{ $attachment->id }}">
                                  <span class="form-check-label">{{ $attachment->title ?: 'Attachment' }}</span>
                                </label>
                              @endforeach
                            </div>
                          @endif
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2">
                          <button type="button" class="btn btn-label-secondary" wire:click="closeVersionEditor({{ $version->id }})">Cancel</button>
                          <button type="button" class="btn btn-primary" wire:click="saveVersion({{ $version->id }})">Save version</button>
                        </div>
                      </div>
                    </div>
                  @endif
                </div>
              @empty
                <p class="text-body-secondary mb-0">No versions yet.</p>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="card">
      <div class="card-body text-center py-5 text-body-secondary">
        No Differentiated Tasks yet for this subject.
      </div>
    </div>
  @endforelse

  <livewire:teacher.differentiated-task-assignment-modal wire:key="differentiated-task-assignment-modal" />
  <livewire:teacher.library-picker wire:key="differentiated-task-library-picker" />
</div>
