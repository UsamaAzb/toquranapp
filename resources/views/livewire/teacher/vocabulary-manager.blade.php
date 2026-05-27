<div class="card mb-6 vocabulary-manager">
  <style>
    .vocabulary-manager .vm-toolbar,
    .vocabulary-manager .vm-actions,
    .vocabulary-manager .vm-word-actions {
      display: flex;
      flex-wrap: wrap;
      gap: .5rem;
      align-items: center;
    }

    .vocabulary-manager .vm-toolbar {
      justify-content: space-between;
    }

    .vocabulary-manager .vm-shell {
      display: grid;
      grid-template-columns: minmax(14rem, 18rem) minmax(0, 1fr);
      min-height: 34rem;
    }

    .vocabulary-manager .vm-shell.vm-shell-single {
      grid-template-columns: minmax(0, 1fr);
    }

    .vocabulary-manager .vm-landing-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr));
      gap: .875rem;
    }

    .vocabulary-manager .vm-custom-grid {
      grid-template-columns: repeat(auto-fill, minmax(16rem, 1fr));
    }

    .vocabulary-manager .vm-root-card {
      border: 1px solid var(--bs-border-color);
      border-radius: .75rem;
      padding: 1rem;
      background: var(--bs-paper-bg);
      color: inherit;
      display: block;
      text-decoration: none;
    }

    .vocabulary-manager .vm-card-actions {
      display: flex;
      flex-wrap: wrap;
      gap: .25rem;
      justify-content: flex-end;
      max-width: 8.75rem;
    }

    .vocabulary-manager .vm-inline-breadcrumb {
      display: flex;
      flex-wrap: wrap;
      gap: .4rem;
      align-items: center;
      margin-bottom: 1rem;
      color: var(--bs-secondary-color);
      font-size: .875rem;
      font-weight: 700;
    }

    .vocabulary-manager .vm-inline-breadcrumb a {
      color: var(--bs-primary);
      text-decoration: none;
    }

    .vocabulary-manager .vm-inline-breadcrumb a:hover {
      text-decoration: underline;
    }

    .vocabulary-manager .vm-inline-breadcrumb .is-current {
      color: var(--bs-heading-color);
    }

    .vocabulary-manager .vm-root-icon {
      inline-size: 2.5rem;
      block-size: 2.5rem;
      border-radius: .75rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .vocabulary-manager .vm-sidebar {
      border-right: 1px solid var(--bs-border-color);
      background: var(--bs-body-bg);
    }

    .vocabulary-manager .vm-set-button {
      width: 100%;
      border: 0;
      border-bottom: 1px solid var(--bs-border-color);
      background: transparent;
      text-align: left;
      padding: .875rem 1rem;
    }

    .vocabulary-manager .vm-set-button.active {
      background: rgba(var(--bs-primary-rgb), .09);
      color: var(--bs-primary);
    }

    .vocabulary-manager .vm-word-row {
      display: grid;
      grid-template-columns: auto minmax(0, 1fr) auto;
      gap: .75rem;
      align-items: center;
      padding: .75rem;
      border: 1px solid var(--bs-border-color);
      border-radius: .5rem;
      background: var(--bs-paper-bg);
    }

    .vocabulary-manager .vm-folder-row {
      border-color: rgba(var(--bs-info-rgb), .35);
      background: linear-gradient(90deg, rgba(var(--bs-info-rgb), .08), var(--bs-paper-bg) 60%);
      padding: 1rem;
    }

    .vocabulary-manager .vm-folder-row .vm-card-handle {
      background: rgba(var(--bs-info-rgb), .12);
      color: var(--bs-info);
    }

    .vocabulary-manager .vm-list-row {
      background: var(--bs-paper-bg);
    }

    .vocabulary-manager .vm-item-main {
      min-width: 0;
      color: inherit;
      text-decoration: none;
      text-align: left;
    }

    .vocabulary-manager .vm-item-main:hover .vm-word-title {
      color: var(--bs-primary);
    }

    .vocabulary-manager .vm-item-meta {
      display: flex;
      flex-wrap: wrap;
      gap: .35rem;
      align-items: center;
    }

    .vocabulary-manager .vm-bank-table {
      width: 100%;
    }

    .vocabulary-manager .vm-bank-wrap {
      overflow-x: auto;
    }

    .vocabulary-manager .vm-cell-button {
      border: 0;
      padding: 0;
      background: transparent;
      color: inherit;
      text-align: left;
      max-width: 100%;
    }

    .vocabulary-manager .vm-audio-button {
      inline-size: 2.25rem;
      block-size: 2.25rem;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .vocabulary-manager .vm-group-stack {
      display: grid;
      justify-items: start;
      gap: .25rem;
    }

    .vocabulary-manager .vm-chip-cloud {
      display: flex;
      flex-wrap: wrap;
      gap: .5rem;
      align-items: center;
    }

    .vocabulary-manager .vm-word-chip {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      min-height: 2.25rem;
      padding: .45rem .7rem;
      border: 1px solid var(--bs-border-color);
      border-radius: .5rem;
      background: var(--bs-paper-bg);
      font-weight: 600;
    }

    .vocabulary-manager .vm-picker-word {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .75rem;
      border: 1px solid var(--bs-border-color);
      border-radius: .5rem;
      padding: .75rem;
      background: var(--bs-paper-bg);
    }

    .vocabulary-manager .vm-card-handle {
      inline-size: 2rem;
      block-size: 2rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: var(--bs-secondary-color);
      background: var(--bs-body-bg);
      border-radius: .375rem;
    }

    .vocabulary-manager .vm-access-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr));
      gap: .75rem;
    }

    .vocabulary-manager .vm-word-title,
    .vocabulary-manager .vm-set-title {
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .vocabulary-manager .vm-thumb {
      inline-size: 2.5rem;
      block-size: 2.5rem;
      border-radius: .5rem;
      object-fit: cover;
      border: 1px solid var(--bs-border-color);
    }

    .vocabulary-manager .vm-modal-backdrop {
      position: fixed;
      inset: 0;
      z-index: 1080;
      display: grid;
      place-items: center;
      padding: 1rem;
      background: rgba(20, 20, 30, .45);
    }

    .vocabulary-manager .vm-modal-panel {
      width: min(62rem, 100%);
      max-height: min(44rem, calc(100vh - 2rem));
      overflow: auto;
      border-radius: .75rem;
      background: var(--bs-paper-bg);
      box-shadow: 0 1rem 3rem rgba(0, 0, 0, .2);
    }

    .vocabulary-manager .vm-search-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr));
      gap: .75rem;
    }

    @media (max-width: 768px) {
      .vocabulary-manager .vm-shell {
        grid-template-columns: 1fr;
      }

      .vocabulary-manager .vm-sidebar {
        border-right: 0;
        border-bottom: 1px solid var(--bs-border-color);
      }

      .vocabulary-manager .vm-word-row {
        grid-template-columns: auto minmax(0, 1fr);
      }

      .vocabulary-manager .vm-word-actions,
      .vocabulary-manager .vm-actions {
        width: 100%;
      }

      .vocabulary-manager .vm-word-row > .vm-actions {
        grid-column: 1 / -1;
        justify-content: stretch;
      }

      .vocabulary-manager .vm-word-actions .btn,
      .vocabulary-manager .vm-actions .btn {
        flex: 1 1 auto;
      }
    }
  </style>

  @if ($viewMode !== 'source')
  <div class="card-header vm-toolbar">
    <div>
      <h5 class="mb-1">
        @if ($viewMode === 'word_bank')
          Vocabulary Management
        @elseif ($viewMode === 'source' && $selectedSet)
          {{ $selectedSet->title }}
        @else
          Library Vocabulary
        @endif
      </h5>
      <p class="mb-0 text-muted small">Manage the word bank, DB-backed roots, folders, lists, access, and vocabulary games.</p>
    </div>
      <div class="vm-actions">
      @if ($viewMode === 'landing')
        <button class="btn btn-primary" type="button" wire:click="openSetCreator(null, 'folder')">
          <i class="icon-base ti tabler-folder-plus me-1"></i>
          Add course folder
        </button>
      @endif
      <a class="btn btn-outline-primary" href="{{ route('teacher.vocabulary.games.launch') }}">
        <i class="icon-base ti tabler-player-play me-1"></i>
        Vocab Games
      </a>
      @if (auth()->user()?->hasAnyRole(['admin', 'super_admin', 'owner']))
        <button class="btn btn-outline-secondary" type="button" wire:click="toggleLegacyReport">
          <i class="icon-base ti tabler-report-search me-1"></i>
          Legacy report
        </button>
      @endif
    </div>
  </div>
  @endif

  @unless ($schemaReady)
    <div class="card-body">
      <div class="alert alert-warning mb-0">
        <strong>Manual SQL needed.</strong>
        The vocabulary tables are not available yet. Review and run
        <code>database/manual/patches/2026-05-19-p7-vocabulary-intervention.sql</code>
        in phpMyAdmin, then refresh this page.
      </div>
    </div>
  @else
    @if ($viewMode === 'landing')
    <div class="card-body border-bottom">
      <div class="row g-3 align-items-stretch">
        <div class="col-12 col-xl-4">
          <a class="vm-root-card h-100" href="{{ route('teacher.library.vocabulary', ['mode' => 'word_bank']) }}">
            <div class="d-flex align-items-start justify-content-between gap-3">
              <div>
                <span class="vm-root-icon bg-label-info text-info mb-3">
                  <i class="icon-base ti tabler-database fs-4"></i>
                </span>
                <h5 class="mb-2">Vocabulary Management</h5>
                <p class="text-muted small mb-0">Open the word bank to edit words, wrong options, audio, images, and custom folders.</p>
              </div>
              <span class="badge bg-label-info">Edit</span>
            </div>
          </a>
        </div>
        <div class="col-12 col-xl-8">
          <div class="vm-landing-grid">
            @foreach ($rootCards as $rootCard)
              <a class="vm-root-card text-start" href="{{ $rootCard['first_set_id'] ? route('teacher.library.vocabulary', ['mode' => 'source', 'set' => $rootCard['first_set_id']]) : '#' }}">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <span class="vm-root-icon bg-label-{{ $rootCard['tone'] }} text-{{ $rootCard['tone'] }}">
                    <i class="{{ $rootCard['icon'] }} fs-4"></i>
                  </span>
                  <span class="badge bg-label-secondary">{{ $rootCard['count'] }} lists</span>
                </div>
                <h6 class="mt-3 mb-1">{{ $rootCard['title'] }}</h6>
                <p class="text-muted small mb-0">{{ $rootCard['description'] }}</p>
              </a>
            @endforeach
          </div>
        </div>
      </div>
      <div class="mt-4">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
          <div>
            <h6 class="mb-1">My vocabulary folders</h6>
            <p class="text-muted small mb-0">Course folders and custom vocabulary folders you create appear here.</p>
          </div>
          <button class="btn btn-sm btn-outline-primary" type="button" wire:click="openSetCreator(null, 'folder')">
            <i class="icon-base ti tabler-folder-plus me-1"></i>
            Add folder
          </button>
        </div>
        @if ($customRootCards !== [])
          <div class="vm-landing-grid vm-custom-grid">
            @foreach ($customRootCards as $folderCard)
              <div class="vm-root-card text-start">
                <div class="d-flex align-items-start justify-content-between gap-3">
                  <span class="vm-root-icon bg-label-{{ $folderCard['tone'] }} text-{{ $folderCard['tone'] }}">
                    <i class="{{ $folderCard['icon'] }} fs-4"></i>
                  </span>
                  <div class="vm-card-actions">
                    <button class="btn btn-sm btn-icon btn-outline-primary" type="button" wire:click.stop="openAccessManagerFor({{ $folderCard['id'] }})" title="Manage access" aria-label="Manage access">
                      <i class="icon-base ti tabler-users-group"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-outline-secondary" type="button" wire:click.stop="openSetEditorFor({{ $folderCard['id'] }})" title="Edit title" aria-label="Edit title">
                      <i class="icon-base ti tabler-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-outline-danger" type="button" wire:click.stop="deleteSet({{ $folderCard['id'] }})" wire:confirm="Delete this editable vocabulary copy? This cannot be undone." title="Delete" aria-label="Delete">
                      <i class="icon-base ti tabler-trash"></i>
                    </button>
                  </div>
                </div>
                <h6 class="mt-3 mb-1">{{ $folderCard['title'] }}</h6>
                <p class="text-muted small mb-0">{{ $folderCard['description'] !== '' ? $folderCard['description'] : ucfirst($folderCard['visibility']).' folder' }}</p>
                <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                  <span class="badge bg-label-secondary">{{ $folderCard['count'] }} items</span>
                  <a class="btn btn-sm btn-text-primary" href="{{ route('teacher.library.vocabulary', ['mode' => 'source', 'set' => $folderCard['id']]) }}">
                    Open folder
                    <i class="icon-base ti tabler-arrow-right ms-1"></i>
                  </a>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="border rounded p-4 text-center text-muted">
            No custom vocabulary folders yet. Use <strong>Add folder</strong> to create your first one.
          </div>
        @endif
      </div>
    </div>
    @endif

    @if ($viewMode === 'landing')
      <div class="card-body">
        <div class="alert alert-info mb-0">
          Choose <strong>Vocabulary Management</strong> to edit the master word bank, or open Cambridge, Phonics, Word Group, or Legacy Floatie to browse folders and lists.
        </div>
      </div>
    @endif

    @if ($viewMode === 'word_bank')
    <div class="card-body" id="vocabulary-word-bank">
      <div class="vm-toolbar mb-3">
        <div>
          <h5 class="mb-1">Word Bank</h5>
          <p class="text-muted small mb-0">Search the master vocabulary table, hear audio, edit wrong options, set word difficulty, and spot missing media.</p>
        </div>
        <div class="vm-actions">
          <button class="btn btn-outline-primary" type="button" wire:click="openWordPicker">
            <i class="icon-base ti tabler-plus me-1"></i>
            Add word or audio
          </button>
          <button class="btn btn-outline-secondary" type="button" wire:click="openWordGroupManager">
            <i class="icon-base ti tabler-category-2 me-1"></i>
            Word groups
          </button>
        </div>
      </div>

      <div class="card border shadow-none mb-3">
        <div class="card-body py-3">
          <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
            <div>
              <div class="fw-semibold">Filter word bank</div>
              <div class="text-muted small">Bulk actions below use these filters. The preview auto-selects up to 500 rows; untick rows you want to keep, or use Previous/Next 500 to move between batches.</div>
            </div>
          </div>
          <div class="row g-2">
            <div class="col-12 col-lg-5">
              <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
                <input class="form-control" type="search" wire:model.live.debounce.350ms="wordSearch" placeholder="Search words, e.g. animal or exam">
              </div>
            </div>
            <div class="col-12 col-md-4 col-lg-3">
              <select class="form-select form-select-sm" wire:model.live="wordBankDifficultyFilter">
                <option value="">All word difficulty levels</option>
                @for ($level = 1; $level <= 6; $level++)
                  <option value="{{ $level }}">Level {{ $level }}</option>
                @endfor
              </select>
            </div>
            <div class="col-12 col-md-8 col-lg-4">
              <select class="form-select form-select-sm" wire:model.live="wordBankGroupFilter">
                <option value="">All word groups</option>
                @foreach ($wordGroupOptions as $groupOption)
                  <option value="{{ $groupOption->id }}">{{ $groupOption->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="vm-actions mb-3">
          <button class="btn btn-outline-warning" type="button" wire:click="previewBulkVocabularyQuality('wrong_options')">
            <i class="icon-base ti tabler-refresh me-1"></i>
            Regenerate wrong options
          </button>
          <button class="btn btn-outline-warning" type="button" wire:click="previewBulkVocabularyQuality('difficulty')">
            <i class="icon-base ti tabler-chart-bar me-1"></i>
            Re-estimate levels
          </button>
          <button class="btn btn-outline-success" type="button" wire:click="previewBulkVocabularyQuality('accept_difficulty')" title="Mark suggested/generated difficulty levels as reviewed by you.">
            <i class="icon-base ti tabler-circle-check me-1"></i>
            Mark levels reviewed
          </button>
      </div>

      @if ($bulkPreviewOpen)
        <div class="alert alert-warning">
          <div class="d-flex flex-wrap justify-content-between gap-3">
            <div>
              <div class="fw-semibold">{{ $bulkPreview['label'] ?? 'Bulk update' }}</div>
              <div class="small">Filter: {{ $bulkPreview['filter'] ?? 'Current filter' }}</div>
              <div class="small">Batch: {{ $bulkPreview['batch_label'] ?? 'Rows 1 - 500' }}</div>
              @php
                $bulkSelectedCount = count(array_filter($bulkSelectedWordIds));
              @endphp
              <div class="small">
                {{ $bulkSelectedCount }} selected from {{ $bulkPreview['update_count'] ?? 0 }} previewed words.
                @if (($bulkPreview['action'] ?? '') === 'accept_difficulty')
                  Skipping {{ $bulkPreview['skip_count'] ?? 0 }} manual edits.
                @else
                  Untick any word you do not want to change.
                @endif
              </div>
              @if (! empty($bulkPreview['too_many']))
                <div class="small mt-1">More words exist after this batch. Use Next 500 to review them.</div>
              @endif
              @if (($bulkPreview['action'] ?? '') === 'accept_difficulty')
                <div class="small mt-1">This only means "I reviewed these generated levels; keep them unless I change them by hand later."</div>
              @endif
            </div>
            <div class="d-flex gap-2 align-items-start">
              <button class="btn btn-warning" type="button" wire:click="confirmBulkVocabularyQuality" wire:loading.attr="disabled" @disabled($bulkSelectedCount < 1)>
                Confirm
              </button>
              <button class="btn btn-label-secondary" type="button" wire:click="$set('bulkPreviewOpen', false)">Cancel</button>
            </div>
          </div>
          @if (! empty($bulkPreview['preview']))
            <div class="d-flex flex-wrap gap-2 mt-3">
              <button class="btn btn-sm btn-label-secondary" type="button" wire:click="previousBulkPreviewBatch" @disabled(empty($bulkPreview['has_previous']))>Previous 500</button>
              <button class="btn btn-sm btn-label-secondary" type="button" wire:click="nextBulkPreviewBatch" @disabled(empty($bulkPreview['too_many']))>Next 500</button>
              <button class="btn btn-sm btn-label-secondary" type="button" wire:click="selectAllBulkPreviewRows">Select all in preview</button>
              <button class="btn btn-sm btn-label-secondary" type="button" wire:click="clearBulkPreviewRows">Unselect all</button>
            </div>
            <div class="table-responsive mt-3">
              <table class="table table-sm mb-0">
                <thead>
                  <tr>
                    <th style="width: 56px;">Use</th>
                    <th>Word</th>
                    <th>Before</th>
                    <th>After</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($bulkPreview['preview'] as $previewRow)
                    <tr>
                      <td>
                        <input class="form-check-input" type="checkbox" wire:model.live="bulkSelectedWordIds.{{ $previewRow['id'] }}" aria-label="Use {{ $previewRow['word'] }}">
                      </td>
                      <td class="fw-semibold">{{ $previewRow['word'] }}</td>
                      <td class="small">{{ \Illuminate\Support\Str::limit($previewRow['before'] ?: 'blank', 80) }}</td>
                      <td class="small">{{ \Illuminate\Support\Str::limit($previewRow['after'] ?: 'blank', 100) }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      @endif

      <div class="vm-bank-wrap">
        <table class="table table-sm align-middle vm-bank-table">
          <thead>
            <tr>
              <th>Word</th>
              <th>Wrong options</th>
              <th>Word difficulty</th>
              <th>Groups</th>
              <th>Audio</th>
              <th>Image</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($wordBankRows as $row)
              <tr wire:key="word-bank-{{ $row['id'] }}">
                <td class="fw-semibold">{{ $row['word'] }}</td>
                <td>
                  <div class="d-flex align-items-start gap-2">
                    <button class="vm-cell-button" type="button" wire:click="openWrongOptions({{ $row['id'] }})">
                      <span class="d-block small {{ $row['wrong_options_generated'] ? 'text-info' : 'text-body' }}">
                        {{ $row['wrong_options_preview'] !== '' ? \Illuminate\Support\Str::limit($row['wrong_options_preview'], 48) : 'No stored options' }}
                      </span>
                      @if ($row['wrong_options_generated'])
                        <span class="badge bg-label-info">generated</span>
                      @elseif ($row['wrong_options_suggested'] ?? false)
                        <span class="badge bg-label-warning">suggested, not saved</span>
                      @elseif ($row['wrong_options_missing'] ?? false)
                        <span class="badge bg-label-secondary">missing</span>
                      @endif
                      @foreach ($row['wrong_option_rules'] as $ruleLabel)
                        <span class="badge bg-label-info" wire:key="wrong-rule-{{ $row['id'] }}-{{ \Illuminate\Support\Str::slug($ruleLabel) }}">{{ $ruleLabel }}</span>
                      @endforeach
                    </button>
                    <button class="btn btn-sm btn-icon btn-outline-warning flex-shrink-0" type="button" wire:key="regen-wrong-{{ $row['id'] }}" wire:click.stop.prevent="regenerateWrongOptionsForWord({{ $row['id'] }})" wire:loading.attr="disabled" wire:target="regenerateWrongOptionsForWord({{ $row['id'] }})" title="Regenerate options for {{ $row['word'] }}" aria-label="Regenerate options for {{ $row['word'] }}">
                      <i class="icon-base ti tabler-refresh"></i>
                    </button>
                  </div>
                  @if ($lastWrongOptionsRefreshWordId === $row['id'])
                    <div class="text-success small mt-1">{{ \Illuminate\Support\Str::limit($lastWrongOptionsRefreshMessage, 120) }}</div>
                  @endif
                </td>
                <td>
                  <div class="d-flex align-items-start gap-2">
                    <div class="flex-grow-1">
                      <select class="form-select form-select-sm" wire:change="updateWordDifficulty({{ $row['id'] }}, $event.target.value)">
                        <option value="" @selected($row['difficulty'] === '')>Blank</option>
                        @for ($level = 1; $level <= 6; $level++)
                          <option value="{{ $level }}" @selected($row['difficulty'] === (string) $level)>Level {{ $level }}</option>
                        @endfor
                      </select>
                      @if ($row['difficulty_inferred'])
                        <span class="badge bg-label-warning mt-1">suggested Level {{ $row['suggested_difficulty'] }}</span>
                        @if ($row['difficulty_reason'] !== '')
                          <div class="text-muted small mt-1">{{ $row['difficulty_reason'] }}</div>
                        @endif
                      @endif
                    </div>
                    <button class="btn btn-sm btn-icon btn-outline-warning flex-shrink-0" type="button" wire:key="regen-difficulty-{{ $row['id'] }}" wire:click.stop.prevent="reestimateWordDifficulty({{ $row['id'] }})" wire:loading.attr="disabled" wire:target="reestimateWordDifficulty({{ $row['id'] }})" title="Re-estimate difficulty for {{ $row['word'] }}" aria-label="Re-estimate difficulty for {{ $row['word'] }}">
                      <i class="icon-base ti tabler-refresh"></i>
                    </button>
                  </div>
                </td>
                <td>
                  <button class="vm-cell-button" type="button" wire:click="openGroupEditor({{ $row['id'] }})">
                    @if ($row['groups'] !== [])
                      <span class="vm-group-stack">
                      @foreach (array_slice($row['groups'], 0, 3) as $groupLabel)
                        <span class="badge bg-label-primary">{{ $groupLabel }}</span>
                      @endforeach
                      @if (count($row['groups']) > 3)
                        <span class="badge bg-label-secondary">+{{ count($row['groups']) - 3 }}</span>
                      @endif
                      </span>
                    @else
                      <span class="text-muted small">Add groups</span>
                    @endif
                  </button>
                </td>
                <td>
                  @if ($row['audio']['available'])
                    <div class="d-flex align-items-center gap-2">
                      <button class="btn btn-sm btn-primary vm-audio-button" type="button" onclick="this.nextElementSibling.play()" title="Play word audio">
                        <i class="icon-base ti tabler-player-play"></i>
                      </button>
                      <audio preload="none" src="{{ $row['audio']['url'] }}"></audio>
                      <span class="badge bg-label-success">{{ str_replace('_', ' ', $row['audio']['source']) }}</span>
                      @if ($canReplaceAudio)
                        <button class="btn btn-sm btn-icon btn-outline-warning" type="button" wire:click="openAudioReplacement({{ $row['id'] }})" title="Replace audio">
                          <i class="icon-base ti tabler-music"></i>
                        </button>
                      @endif
                    </div>
                  @else
                    <button class="btn btn-sm btn-outline-warning" type="button" wire:click="openAudioReplacement({{ $row['id'] }})">
                      <i class="icon-base ti tabler-music me-1"></i>
                      Add audio
                    </button>
                  @endif
                </td>
                <td>
                  <button class="vm-cell-button" type="button" wire:click="openImageEditor({{ $row['id'] }})">
                    @if ($row['image_url'])
                      <img class="vm-thumb" src="{{ $row['image_url'] }}" alt="">
                    @else
                      <span class="badge bg-label-secondary">Add image</span>
                    @endif
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">No vocabulary words match this search.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if ($wordBankRows->count() >= $wordBankLimit)
        <div class="text-center mt-3">
          <button class="btn btn-outline-primary" type="button" wire:click="loadMoreWordBank">
            Load more
          </button>
        </div>
      @endif
    </div>
    @endif

    @if ($viewMode === 'source')
    <div class="vm-shell vm-shell-single">
      <main class="p-3 p-md-4">
        @if ($selectedSet)
          <nav class="vm-inline-breadcrumb" aria-label="Vocabulary folder path">
            <a href="{{ route('teacher.library.vocabulary') }}" wire:navigate>Vocabulary</a>
            @foreach ($selectedBreadcrumbs as $crumb)
              <span>/</span>
              @if ((int) $crumb['id'] === (int) $selectedSet->id)
                <span class="is-current">{{ $crumb['title'] }}</span>
              @else
                <a href="{{ route('teacher.library.vocabulary', ['mode' => 'source', 'set' => $crumb['id']]) }}" wire:navigate>{{ $crumb['title'] }}</a>
              @endif
            @endforeach
          </nav>
          @php
            $selectedCanEdit = (int) $selectedSet->owner_user_id === (int) auth()->id() || auth()->user()?->hasAnyRole(['admin', 'super_admin', 'owner']);
            $selectedIsEditableCustom = $selectedSet->source_kind === \App\Models\VocabularySet::SOURCE_CUSTOM && $selectedCanEdit;
            $selectedCanEditWords = $selectedIsEditableCustom && $selectedSet->isPlayable();
            $selectedIsLegacySource = $selectedSet->source_kind !== \App\Models\VocabularySet::SOURCE_CUSTOM;
            $canCopyToMyFolders = ! $selectedIsEditableCustom && $selectedSet->source_kind !== \App\Models\VocabularySet::SOURCE_LEGACY_DIFFICULTY;
            $canAddFolderHere = $selectedIsEditableCustom && $selectedSet->isFolder() && $sourceChildType !== \App\Models\VocabularySet::NODE_PLAYABLE;
            $canAddListHere = $selectedIsEditableCustom && $selectedSet->isFolder() && $sourceChildType !== \App\Models\VocabularySet::NODE_FOLDER;
          @endphp
          <div class="vm-toolbar mb-3">
            <div class="min-w-0">
              <h5 class="mb-1 vm-set-title">{{ $selectedSet->title }}</h5>
              <div class="text-muted small">
                {{ $selectedWords->count() }} selected words &middot; {{ ucfirst($selectedSet->visibility) }}
                &middot; {{ $selectedSet->isFolder() ? 'Folder' : 'Playable list' }}
                @if ($selectedIsLegacySource)
                  &middot; DB-backed source
                @endif
              </div>
            </div>
            <div class="vm-actions">
              @if ($selectedCanEditWords)
                <button class="btn btn-primary btn-icon" type="button" wire:click="openWordPicker" title="Add words" aria-label="Add words">
                  <i class="icon-base ti tabler-plus"></i>
                </button>
                @if (! $listWordEditMode)
                  <button class="btn btn-outline-secondary btn-icon" type="button" wire:click="startListWordEdit" title="Edit selected words" aria-label="Edit selected words">
                    <i class="icon-base ti tabler-checks"></i>
                  </button>
                @endif
              @endif
              @if ($selectedSet->canBeLaunched())
                <button class="btn btn-outline-primary btn-icon" type="button" wire:click="openLaunchChooserFor({{ $selectedSet->id }})" title="Start game" aria-label="Start game">
                  <i class="icon-base ti tabler-player-play"></i>
                </button>
              @endif
              @if ($canAddListHere)
                <button class="btn btn-primary" type="button" wire:click="openSetCreator({{ $selectedSet->id }}, 'playable')">
                  <i class="icon-base ti tabler-list-details me-1"></i>
                  Add lesson/list
                </button>
              @endif
              @if ($canAddFolderHere)
                <button class="btn btn-outline-primary" type="button" wire:click="openSetCreator({{ $selectedSet->id }}, 'folder')">
                  <i class="icon-base ti tabler-folder-plus me-1"></i>
                  Add folder
                </button>
              @endif
              @if ($canCopyToMyFolders)
                <button class="btn btn-outline-secondary" type="button" wire:click="cloneSet({{ $selectedSet->id }})">
                  <i class="icon-base ti tabler-copy me-1"></i>
                  Copy to My folders
                </button>
              @endif
              @if ($selectedSet->isFolder() || $selectedSet->isPlayable())
                <button class="btn btn-outline-primary btn-icon" type="button" wire:click="openAccessManager" title="Manage access" aria-label="Manage access">
                  <i class="icon-base ti tabler-users-group"></i>
                </button>
              @endif
              @if ($selectedIsEditableCustom)
                <button class="btn btn-outline-secondary btn-icon" type="button" wire:click="openSetEditor" title="Edit title" aria-label="Edit title">
                  <i class="icon-base ti tabler-pencil"></i>
                </button>
                <button class="btn btn-outline-danger btn-icon" type="button" wire:click="deleteSet({{ $selectedSet->id }})" wire:confirm="Delete this editable vocabulary copy? This cannot be undone." title="Delete" aria-label="Delete">
                  <i class="icon-base ti tabler-trash"></i>
                </button>
              @endif
            </div>
          </div>

          @if ($selectedSet->isFolder() || $selectedWords->isNotEmpty())
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
              <input class="form-control" type="search" wire:model.live.debounce.350ms="sourceSearch" placeholder="Search this folder or list">
            </div>
          @endif

          @if ($setEditorOpen)
            <div class="border rounded p-3 mb-3 bg-label-secondary">
              <div class="row g-2 align-items-end">
                <div class="col-12 col-md-5">
                  <label class="form-label" for="editSetTitle">Title</label>
                  <input id="editSetTitle" class="form-control" type="text" wire:model.blur="editSetTitle">
                  @error('editSetTitle') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-5">
                  <label class="form-label" for="editSetDescription">Description</label>
                  <input id="editSetDescription" class="form-control" type="text" wire:model.blur="editSetDescription">
                  @error('editSetDescription') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                  <button class="btn btn-primary flex-grow-1" type="button" wire:click="saveSetEditor">Save</button>
                  <button class="btn btn-label-secondary" type="button" wire:click="$set('setEditorOpen', false)">Cancel</button>
                </div>
              </div>
            </div>
          @endif

          @if ($selectedIsLegacySource)
            <div class="alert alert-warning small">
              This is a read-only DB-backed source from the old vocabulary data.
              @if ($canCopyToMyFolders)
                Use <strong>Copy to My folders</strong> only when you want your own editable version.
              @endif
            </div>
          @endif

          @if ($selectedSet->isFolder())
            <div class="d-grid gap-2">
              @forelse ($selectedChildren as $child)
                <div class="vm-word-row {{ $child->isFolder() ? 'vm-folder-row' : 'vm-list-row' }}" wire:key="vocab-child-{{ $child->id }}">
                  <span class="vm-card-handle" title="Drag handle">
                    <i class="icon-base ti {{ $child->isFolder() ? 'tabler-folder' : 'tabler-grip-vertical' }}"></i>
                  </span>
                  <a class="vm-item-main" href="{{ route('teacher.library.vocabulary', ['mode' => 'source', 'set' => $child->id]) }}" wire:navigate>
                    <div class="fw-semibold fs-6 vm-word-title">{{ $child->title }}</div>
                    <div class="small text-muted vm-item-meta">
                      <span>{{ $child->isFolder() ? 'Folder' : 'List' }}</span>
                      @if (! $child->isFolder())
                        <span>{{ (int) ($selectedChildMetadata[$child->id]['word_count'] ?? $child->memberships_count) }} words</span>
                      @endif
                    </div>
                  </a>
                  <div class="vm-actions flex-shrink-0">
                    @if($child->canBeLaunched())
                      <button class="btn btn-sm btn-icon btn-outline-primary" type="button" wire:click.stop="openLaunchChooserFor({{ $child->id }})" title="Start game" aria-label="Start game">
                        <i class="icon-base ti tabler-player-play"></i>
                      </button>
                    @endif
                    <button class="btn btn-sm btn-icon btn-outline-primary" type="button" wire:click.stop="openAccessManagerFor({{ $child->id }})" title="Manage access" aria-label="Manage access">
                      <i class="icon-base ti tabler-users-group"></i>
                    </button>
                    @if($selectedIsEditableCustom)
                      <button class="btn btn-sm btn-icon btn-outline-secondary" type="button" wire:click.stop="openSetEditorFor({{ $child->id }})" title="Edit title" aria-label="Edit title">
                        <i class="icon-base ti tabler-pencil"></i>
                      </button>
                      @if ($child->source_kind === \App\Models\VocabularySet::SOURCE_CUSTOM)
                        <button class="btn btn-sm btn-icon btn-outline-danger" type="button" wire:click.stop="deleteSet({{ $child->id }})" wire:confirm="Delete this editable vocabulary item? This cannot be undone." title="Delete" aria-label="Delete">
                          <i class="icon-base ti tabler-trash"></i>
                        </button>
                      @else
                        <button class="btn btn-sm btn-icon btn-outline-danger" type="button" wire:click.stop="archiveSet({{ $child->id }})" wire:confirm="Archive this vocabulary item?" title="Archive" aria-label="Archive">
                          <i class="icon-base ti tabler-archive"></i>
                        </button>
                      @endif
                    @endif
                    <a class="btn btn-sm btn-icon btn-text-primary" href="{{ route('teacher.library.vocabulary', ['mode' => 'source', 'set' => $child->id]) }}" wire:navigate title="Open" aria-label="Open">
                      <i class="icon-base ti tabler-arrow-right"></i>
                    </a>
                  </div>
                </div>
              @empty
                <div class="border rounded p-4 text-center text-muted">
                  This folder is empty. Use <strong>Add folder</strong> or <strong>Add lesson/list</strong> to build it.
                </div>
              @endforelse
            </div>
          @else
            @if ($listWordEditMode && $selectedCanEditWords)
              <div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                  <div class="fw-semibold">Edit selected words</div>
                  <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm" type="button" wire:click="saveListWordEdit">
                      <i class="icon-base ti tabler-save me-1"></i>
                      Save list
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" type="button" wire:click="cancelListWordEdit">Cancel</button>
                  </div>
                </div>
                <div class="vm-chip-cloud">
                  @foreach ($selectedWords as $row)
                    <label class="vm-word-chip" wire:key="edit-list-word-{{ $row['id'] }}">
                      <input class="form-check-input m-0" type="checkbox" wire:model="editableListWordIds.{{ $row['id'] }}">
                      <span>{{ $row['word'] }}</span>
                    </label>
                  @endforeach
                </div>
              </div>
            @endif

            <div class="vm-chip-cloud">
              @forelse ($selectedWords as $index => $row)
                <span class="vm-word-chip" wire:key="vocab-word-chip-{{ $row['id'] }}-{{ $index }}">
                  <span class="vm-card-handle" title="Drag handle">
                    <i class="icon-base ti tabler-grip-vertical"></i>
                  </span>
                  <span>{{ $row['word'] }}</span>
                </span>
              @empty
                <div class="border rounded p-4 text-center text-muted w-100">
                  No words selected yet. Use the <strong>+</strong> button to add words from the word bank.
                </div>
              @endforelse
            </div>
            @if (! $listWordEditMode && $selectedWords->count() >= $sourceWordLimit)
              <div class="text-center mt-3">
                <button class="btn btn-outline-primary" type="button" wire:click="loadMoreSourceWords">
                  Load more
                </button>
              </div>
            @endif
          @endif
        @else
          <div class="border rounded p-4 text-center text-muted">Create or choose a vocabulary folder.</div>
        @endif
      </main>
    </div>
    @endif

    @include('vocabulary.components.access-manager')
    @include('vocabulary.components.legacy-hangman-report')
    @include('vocabulary.components.manager-word-picker')
    @include('vocabulary.components.set-creator-modal')
    @include('vocabulary.components.wrong-options-modal')
    @include('vocabulary.components.audio-replacement-modal')
    @include('vocabulary.components.word-image-modal')
    @include('vocabulary.components.word-groups-modal')
    @include('vocabulary.components.word-group-manager-modal')

  @endunless
</div>
