@extends('layouts/layoutMaster')

@section('title', 'Teaching Library')
@section('meta_description', 'Open reusable classroom resources, videos, reading materials, and teacher tools from one library hub.')

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sortablejs/sortable.js'])
@endsection

@section('content')
<style>
  .library-shell {
    --library-soft-primary: rgba(var(--bs-primary-rgb), 0.1);
    --library-soft-border: color-mix(in srgb, var(--bs-border-color) 78%, var(--bs-primary));
    --library-card-shadow: 0 0.75rem 1.75rem rgba(47, 43, 61, 0.08);
  }

  .library-shell [x-cloak] {
    display: none !important;
  }

  [data-bs-theme="dark"] .library-shell {
    --library-soft-primary: rgba(var(--bs-primary-rgb), 0.22);
    --library-soft-border: color-mix(in srgb, var(--bs-border-color) 70%, var(--bs-primary));
    --library-card-shadow: 0 0.75rem 1.75rem rgba(0, 0, 0, 0.22);
  }

  .library-hero {
    position: relative;
    overflow: hidden;
    border: 1px solid var(--library-soft-border);
    background:
      radial-gradient(circle at top right, rgba(var(--bs-primary-rgb), 0.18), transparent 34%),
      linear-gradient(135deg, var(--bs-paper-bg), color-mix(in srgb, var(--bs-paper-bg) 86%, var(--bs-primary)));
  }

  .library-hero::after {
    content: "";
    position: absolute;
    inline-size: 13rem;
    block-size: 13rem;
    inset-inline-end: -4rem;
    inset-block-start: -5rem;
    border-radius: 999px;
    border: 2rem solid rgba(var(--bs-primary-rgb), 0.08);
    pointer-events: none;
  }

  .library-resource-card {
    border: 1px solid var(--bs-border-color);
    box-shadow: var(--library-card-shadow);
    transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
  }

  .library-resource-card--folder {
    border-color: rgba(var(--bs-primary-rgb), 0.28);
    background:
      linear-gradient(90deg, rgba(var(--bs-primary-rgb), 0.08), transparent 42%),
      var(--bs-paper-bg);
  }

  .library-resource-card--file {
    border-color: color-mix(in srgb, var(--bs-border-color) 70%, var(--bs-info));
  }

  .library-resource-card--link {
    border-color: color-mix(in srgb, var(--bs-border-color) 70%, var(--bs-success));
  }

  .library-resource-card--archived {
    border-style: dashed;
    opacity: 0.86;
    background:
      linear-gradient(135deg, color-mix(in srgb, var(--bs-paper-bg) 84%, var(--bs-secondary)) 0%, var(--bs-paper-bg) 70%);
  }

  .library-resource-card:hover,
  .library-resource-card:focus-within {
    transform: translateY(-3px);
    border-color: rgba(var(--bs-primary-rgb), 0.32);
    box-shadow: 0 1rem 2rem rgba(47, 43, 61, 0.12);
  }

  [data-bs-theme="dark"] .library-resource-card:hover,
  [data-bs-theme="dark"] .library-resource-card:focus-within {
    box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.32);
  }

  .library-resource-icon {
    inline-size: 2.75rem;
    block-size: 2.75rem;
    border-radius: 0.85rem;
  }

  .library-resource-link {
    color: inherit;
  }

  .library-resource-link--disabled {
    display: block;
    cursor: default;
  }

  .library-resource-link--disabled .library-resource-card {
    opacity: 0.72;
  }

  .library-resource-link--disabled .library-resource-card:hover,
  .library-resource-link--disabled .library-resource-card:focus-within {
    transform: none;
    border-color: var(--bs-border-color);
    box-shadow: var(--library-card-shadow);
  }

  .library-resource-link:focus-visible {
    outline: 3px solid rgba(var(--bs-primary-rgb), 0.28);
    outline-offset: 4px;
    border-radius: 1rem;
  }

  .library-resource-description {
    display: -webkit-box;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
  }

  .library-resource-title {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .library-card-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    margin-top: auto;
  }

  .library-card-icon-actions {
    position: absolute;
    inset-block-start: 1.25rem;
    inset-inline-end: 1.25rem;
    z-index: 3;
    display: flex;
    gap: 0.35rem;
    padding: 0.25rem;
    border-radius: 0.5rem;
    background: color-mix(in srgb, var(--bs-paper-bg) 92%, transparent);
    box-shadow: 0 0.5rem 1rem rgba(47, 43, 61, 0.08);
  }

  .library-card-icon-actions .btn {
    inline-size: 2rem;
    block-size: 2rem;
    padding: 0;
  }

  .library-card-type-badge {
    margin-inline-start: auto;
  }

  .library-open-link {
    z-index: 1;
  }

  .library-shell .modal {
    text-align: start;
  }

  .library-shell .modal .library-manager {
    box-shadow: none;
    margin-bottom: 0 !important;
  }

  .library-quick-add-modal .modal-dialog {
    max-width: min(74rem, calc(100vw - 3rem));
  }

  @media (max-width: 576px) {
    .library-card-icon-actions {
      inset-block-start: 0.75rem;
      inset-inline-end: 0.75rem;
    }
  }

  .library-folder-breadcrumb {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    align-items: center;
    margin-bottom: 0.35rem;
    font-size: 0.875rem;
  }

  .library-root-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 0.5rem;
  }

  .library-page-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 0.5rem;
  }

  .library-sort-handle {
    position: absolute;
    inset-block-start: 1.25rem;
    inset-inline-start: 1.25rem;
    z-index: 4;
    cursor: grab;
    box-shadow: 0 0.5rem 1rem rgba(47, 43, 61, 0.08);
  }

  .library-sort-handle:active {
    cursor: grabbing;
  }

  .library-shell.is-organizing .library-resource-card[data-sortable-card="1"] {
    border-color: rgba(var(--bs-primary-rgb), 0.55);
  }

  .library-shell.is-organizing .library-open-link {
    pointer-events: none;
  }

  .library-sortable-ghost .library-resource-card {
    opacity: 0.55;
  }

  .library-sortable-chosen .library-resource-card {
    transform: scale(0.99);
  }

  .library-sort-status {
    min-height: 1.35rem;
  }

  .library-page-actions .dropdown-toggle.library-icon-dropdown::after {
    display: none;
  }
</style>

<script>
  window.w14TeacherLibraryPage = function(config) {
    return {
      manageLibrary: Boolean(config.manageLibrary),
      organizeLibrary: false,
      canSort: Boolean(config.canSort),
      sortMessage: '',
      sortable: null,
      sortReadyTimer: null,

      init() {
        this.$watch('organizeLibrary', (enabled) => {
          if (enabled) {
            this.$nextTick(() => this.initLibrarySortable());
            return;
          }

          this.destroyLibrarySortable();
        });
      },

      toggleOrganizeLibrary() {
        if (!this.canSort) {
          return;
        }

        this.organizeLibrary = !this.organizeLibrary;
        this.sortMessage = this.organizeLibrary
          ? 'Drag using the handle, then release to save the new order.'
          : '';
      },

      initLibrarySortable(startedAt = Date.now()) {
        const grid = document.getElementById('teacher-library-sort-grid');

        if (!grid || !this.organizeLibrary) {
          return;
        }

        if (typeof window.Sortable === 'undefined') {
          if (Date.now() - startedAt < 5000) {
            window.clearTimeout(this.sortReadyTimer);
            this.sortReadyTimer = window.setTimeout(() => this.initLibrarySortable(startedAt), 50);
            return;
          }

          this.sortMessage = 'Drag sorting could not load. Refresh the page and try again.';
          return;
        }

        if (this.sortable) {
          this.sortable.destroy();
        }

        this.sortable = window.Sortable.create(grid, {
          animation: 150,
          draggable: '[data-library-sortable="1"]',
          handle: '.library-sort-handle',
          filter: 'a, button:not(.library-sort-handle), input, textarea, select, [data-no-drag]',
          preventOnFilter: true,
          ghostClass: 'library-sortable-ghost',
          chosenClass: 'library-sortable-chosen',
          onStart: () => {
            this.sortMessage = 'Release to save the new Library order.';
          },
          onEnd: () => this.persistLibraryOrder()
        });
      },

      destroyLibrarySortable() {
        window.clearTimeout(this.sortReadyTimer);

        if (this.sortable) {
          this.sortable.destroy();
          this.sortable = null;
        }
      },

      collectLibraryItems() {
        const grid = document.getElementById('teacher-library-sort-grid');

        if (!grid) {
          return [];
        }

        return Array.from(grid.querySelectorAll('[data-library-sortable="1"]'))
          .map((node) => ({
            type: node.dataset.libraryType,
            id: Number.parseInt(node.dataset.libraryId || '0', 10)
          }))
          .filter((item) => item.type && item.id > 0);
      },

      async persistLibraryOrder() {
        this.sortMessage = 'Saving Library order...';

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let response = null;

        try {
          response = await fetch(config.reorderUrl, {
            method: 'PATCH',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrf,
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              parent_id: config.parentId,
              subject_id: config.subjectId,
              items: this.collectLibraryItems()
            })
          });
        } catch (error) {
          this.sortMessage = 'Library order could not be saved. Check your connection and try again.';
          return;
        }

        if (!response.ok) {
          const data = await response.json().catch(() => ({}));
          const shouldRefresh = [404, 409, 410, 419, 422].includes(response.status);
          this.sortMessage = data.message
            || (shouldRefresh
              ? 'Library order could not be saved. Refreshing Library state...'
              : 'Library order could not be saved. No changes were saved.');

          if (shouldRefresh) {
            window.setTimeout(() => window.location.reload(), 1400);
          }

          return;
        }

        this.sortMessage = 'Library order saved.';
      }
    };
  };
</script>

@php
  $startManagingLibrary = request()->routeIs('teacher.library.manage');
@endphp

<div class="library-shell"
     x-data="w14TeacherLibraryPage({
        manageLibrary: @js($startManagingLibrary),
        canSort: @js((bool)($librarySortContext['can_sort'] ?? false)),
        parentId: @js($librarySortContext['parent_id'] ?? null),
        subjectId: @js($librarySortContext['subject_id'] ?? null),
        reorderUrl: @js(route('teacher.library.reorder'))
     })"
     x-bind:class="{ 'is-organizing': organizeLibrary }"
     x-on:library-folder-updated.window="setTimeout(() => window.location.reload(), 450)">
  <div class="card library-hero mb-6">
    <div class="card-body position-relative">
      <div class="row g-4 align-items-center">
        <div class="col-lg-8">
          <span class="badge bg-label-primary mb-3">Teaching library</span>
          <h4 class="mb-1">{{ $libraryHeroTitle ?? 'Teaching Library' }}</h4>
          <div class="text-body-secondary">{{ $libraryHeroSubtitle ?? 'Reusable resources for lessons and follow-up' }}</div>
        </div>
        <div class="col-lg-4">
          <div class="d-flex flex-wrap justify-content-lg-end gap-3">
            @if(($canManageTeacherLibrary ?? false) && (! empty($teacherFolderContext) || ! empty($rootSubjectContext) || $startManagingLibrary || empty($teacherSubjectOptions ?? [])))
              <button type="button"
                      class="btn btn-sm btn-outline-primary align-self-center"
                      x-on:click="manageLibrary = !manageLibrary"
                      x-bind:aria-expanded="manageLibrary.toString()"
                      aria-controls="teacher-library-manager">
                <span x-show="!manageLibrary">Add or edit My Library</span>
                <span x-cloak x-show="manageLibrary">Hide My Library manager</span>
              </button>
            @endif
            <div class="bg-label-primary rounded-4 p-4 text-center">
              <i class="ti tabler-library fs-1 text-primary"></i>
              <div class="fw-semibold mt-2">{{ count($library_list) }} items</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @if(($canManageTeacherLibrary ?? false) && empty($teacherFolderContext) && ! empty($rootSubjectContext))
    <div class="modal fade" id="library-add-root-folder" tabindex="-1" aria-labelledby="library-add-root-folder-title" aria-hidden="true">
      <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
          <form method="POST" action="{{ route('teacher.library.sections.store') }}">
            @csrf
            <div class="modal-header">
              <h5 class="modal-title library-resource-title" id="library-add-root-folder-title" title="Add root Library folder">Add Library folder</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              @if(! empty($rootSubjectContext))
                <input type="hidden" name="subject_id" value="{{ $rootSubjectContext['id'] }}">
                <div class="mb-3">
                  <span class="badge bg-label-primary">Subject</span>
                  <div class="fw-medium mt-2">{{ $rootSubjectContext['title'] }}</div>
                </div>
              @endif
              <div class="mb-3">
                <label class="form-label" for="library-root-title">Folder title</label>
                <input id="library-root-title" name="title" type="text" class="form-control" maxlength="255" required>
              </div>
              <div>
                <label class="form-label" for="library-root-description">Description</label>
                <textarea id="library-root-description" name="description" class="form-control" rows="3" maxlength="300"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save folder</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif

  @if(($canManageTeacherLibrary ?? false) && (! empty($teacherFolderContext) || ! empty($rootSubjectContext) || $startManagingLibrary || empty($teacherSubjectOptions ?? [])))
    <div id="teacher-library-manager"
         class="mb-6"
         x-cloak
         x-show="manageLibrary"
         x-transition>
      <livewire:teacher.library-manager :initial-section-id="request()->integer('section') ?: null" />
    </div>
  @endif

  @if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
  @endif

  @if($errors->has('library_action'))
    <div class="alert alert-warning mb-4">
      {{ $errors->first('library_action') }}
    </div>
  @endif

  @if(! empty($teacherFolderContext))
    @if($canManageTeacherLibrary ?? false)
      <div class="modal fade library-quick-add-modal" id="library-add-sources-current-{{ $teacherFolderContext['id'] }}" tabindex="-1" aria-labelledby="library-add-sources-current-{{ $teacherFolderContext['id'] }}-title" aria-hidden="true" x-data x-on:hidden.bs.modal="window.dispatchEvent(new CustomEvent('library-resource-form-reset'))">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="library-add-sources-current-{{ $teacherFolderContext['id'] }}-title">Add sources inside {{ $teacherFolderContext['title'] }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <livewire:teacher.library-manager :initial-section-id="$teacherFolderContext['id']" :quick-add="true" quick-add-mode="resources" :key="'library-quick-add-sources-'.$teacherFolderContext['id']" />
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="library-add-folder-current-{{ $teacherFolderContext['id'] }}" tabindex="-1" aria-labelledby="library-add-folder-current-{{ $teacherFolderContext['id'] }}-title" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="library-add-folder-current-{{ $teacherFolderContext['id'] }}-title">Add subfolder inside {{ $teacherFolderContext['title'] }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <livewire:teacher.library-manager :initial-section-id="$teacherFolderContext['id']" :quick-add="true" quick-add-mode="section" :key="'library-quick-add-folder-'.$teacherFolderContext['id']" />
            </div>
          </div>
        </div>
      </div>
    @endif
  @endif

  @if(($canManageTeacherLibrary ?? false) && (! empty($teacherFolderContext) || ! empty($rootSubjectContext)))
    <div class="library-page-actions mb-4">
      @if(empty($teacherFolderContext))
        <button type="button"
                class="btn btn-sm btn-icon btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#library-add-root-folder"
                title="Add folder"
                aria-label="Add folder">
          <i class="ti tabler-plus"></i>
        </button>
      @else
        <div class="btn-group">
          <button type="button"
                  class="btn btn-sm btn-icon btn-primary dropdown-toggle library-icon-dropdown"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                  title="Add"
                  aria-label="Add">
            <i class="ti tabler-plus"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <button type="button"
                      class="dropdown-item"
                      data-bs-toggle="modal"
                      data-bs-target="#library-add-sources-current-{{ $teacherFolderContext['id'] }}">
                <i class="ti tabler-files me-2"></i>
                Add sources
              </button>
            </li>
            <li>
              <button type="button"
                      class="dropdown-item"
                      data-bs-toggle="modal"
                      data-bs-target="#library-add-folder-current-{{ $teacherFolderContext['id'] }}">
                <i class="ti tabler-folder-plus me-2"></i>
                Add subfolder
              </button>
            </li>
          </ul>
        </div>
      @endif

      <button type="button"
              class="btn btn-sm btn-icon"
              x-bind:class="organizeLibrary ? 'btn-primary' : 'btn-outline-secondary'"
              x-bind:disabled="!canSort"
              x-on:click="toggleOrganizeLibrary()"
              title="{{ ($librarySortContext['can_sort'] ?? false) ? 'Drag folders and resources to reorder' : 'Open an active single-subject folder to reorder' }}"
              aria-label="Edit order">
        <i class="ti tabler-arrows-sort"></i>
      </button>

      <a href="{{ request()->fullUrlWithQuery(['show_archived' => $showArchived ? null : 1]) }}"
         class="btn btn-sm {{ $showArchived ? 'btn-warning' : 'btn-outline-secondary' }}">
        <i class="ti tabler-archive me-1"></i>
        {{ $showArchived ? 'Hide archived' : 'Show archived' }}
      </a>
    </div>
    <div class="library-sort-status text-end text-body-secondary small mb-3" x-cloak x-show="sortMessage" x-text="sortMessage"></div>
  @endif

  <div class="row g-4" id="teacher-library-sort-grid">
    @foreach($library_list as $item)
      @php
        $hasManageAction = ($canManageTeacherLibrary ?? false)
            && ($item['source'] ?? null) === 'teacher_library'
            && ! empty($item['entity_type'])
            && ! empty($item['entity_id']);
        $isArchivedCard = ! empty($item['is_archived']);
        $canSortCard = ($librarySortContext['can_sort'] ?? false) && $hasManageAction && ! $isArchivedCard;
        $itemHref = $item['href'] ?? url($item['link']);
        $modalId = $hasManageAction ? 'library-edit-'.$item['entity_type'].'-'.$item['entity_id'] : null;
      @endphp
      <div class="col-12 col-sm-6 col-xl-4"
           data-library-sortable="{{ $canSortCard ? '1' : '0' }}"
           @if($canSortCard)
             data-library-type="{{ $item['entity_type'] }}"
             data-library-id="{{ $item['entity_id'] }}"
           @endif>
        <div class="library-resource-link {{ $item['is_available'] ? '' : 'library-resource-link--disabled' }} text-decoration-none h-100"
             role="group"
             aria-label="{{ $item['title'] }}{{ $item['is_available'] ? '' : ' is available to teachers only' }}"
             @if(! $item['is_available']) aria-disabled="true" @endif>
          <div class="card library-resource-card library-resource-card--{{ $item['kind'] ?? 'resource' }} {{ $isArchivedCard ? 'library-resource-card--archived' : '' }} h-100 position-relative"
               data-sortable-card="{{ $canSortCard ? '1' : '0' }}">
            @if($canSortCard)
              <button type="button"
                      class="btn btn-sm btn-icon btn-label-secondary library-sort-handle"
                      x-cloak
                      x-show="organizeLibrary"
                      aria-label="Drag {{ $item['title'] }} to reorder">
                <i class="ti tabler-grip-vertical"></i>
              </button>
            @endif
            <div class="card-body d-flex flex-column gap-4">
              <div class="d-flex align-items-start justify-content-between gap-3">
                <span class="library-resource-icon bg-label-{{ $item['tone'] }} text-{{ $item['tone'] }} d-inline-flex align-items-center justify-content-center">
                  <i class="{{ $item['icon'] }} fs-3"></i>
                </span>
                @if(! $hasManageAction)
                  <span class="badge bg-label-{{ $item['tone'] }}">{{ $item['meta'] }}</span>
                @endif
                @if($hasManageAction)
                  <div class="library-card-icon-actions"
                       x-cloak
                       x-show="!organizeLibrary">
                    @if($isArchivedCard)
                      <form method="POST"
                            action="{{ $item['entity_type'] === 'section'
                              ? route('teacher.library.sections.restore', $item['entity_id'])
                              : route('teacher.library.resources.restore', $item['entity_id']) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-icon btn-outline-success" aria-label="Restore {{ $item['title'] }}">
                          <i class="ti tabler-restore"></i>
                        </button>
                      </form>
                    @else
                      <button type="button"
                              class="btn btn-sm btn-icon btn-outline-secondary"
                              data-bs-toggle="modal"
                              data-bs-target="#{{ $modalId }}"
                              aria-label="Edit {{ $item['title'] }}">
                        <i class="ti tabler-pencil"></i>
                      </button>
                      <form method="POST"
                            action="{{ $item['entity_type'] === 'section'
                              ? route('teacher.library.sections.archive', $item['entity_id'])
                              : route('teacher.library.resources.archive', $item['entity_id']) }}"
                            onsubmit="return confirm('Archive this {{ $item['entity_type'] === 'section' ? 'folder' : 'resource' }} for new selection?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-icon btn-outline-warning" aria-label="Archive {{ $item['title'] }}">
                          <i class="ti tabler-archive"></i>
                        </button>
                      </form>
                    @endif
                    <form method="POST"
                          action="{{ $item['entity_type'] === 'section'
                            ? route('teacher.library.sections.delete', $item['entity_id'])
                            : route('teacher.library.resources.delete', $item['entity_id']) }}"
                          onsubmit="return confirm('Delete this unused {{ $item['entity_type'] === 'section' ? 'folder' : 'resource' }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" aria-label="Delete {{ $item['title'] }}">
                        <i class="ti tabler-trash"></i>
                      </button>
                    </form>
                  </div>
                @endif
              </div>

              <div>
                <h5 class="mb-2 library-resource-title" title="{{ $item['title'] }}">{{ $item['title'] }}</h5>
                <p class="mb-0 text-body-secondary library-resource-description">{{ $item['description'] }}</p>
              </div>

              <div class="library-card-actions">
                <div class="d-flex align-items-center {{ $item['is_available'] && ! $isArchivedCard ? 'text-primary' : 'text-body-secondary' }} fw-medium">
                  <span>{{ $isArchivedCard ? 'Archived - restore to open' : ($item['is_available'] ? ($item['cta'] ?? 'Open resource') : $item['access_note']) }}</span>
                  <i class="ti {{ $item['is_available'] && ! $isArchivedCard ? 'tabler-arrow-right' : ($isArchivedCard ? 'tabler-archive' : 'tabler-lock') }} ms-2"></i>
                </div>
                @if($hasManageAction)
                  <span class="badge bg-label-{{ $isArchivedCard ? 'secondary' : $item['tone'] }} library-card-type-badge">{{ $isArchivedCard ? 'Archived' : $item['meta'] }}</span>
                @endif
              </div>
            </div>
            @if($item['is_available'] && ! $isArchivedCard)
              <a href="{{ $itemHref }}" class="stretched-link library-open-link" aria-label="Open {{ $item['title'] }}"></a>
            @endif
          </div>
        </div>

        @if($hasManageAction && ! $isArchivedCard)
          <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-title" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <form method="POST"
                      action="{{ $item['entity_type'] === 'section'
                        ? route('teacher.library.sections.update', $item['entity_id'])
                        : route('teacher.library.resources.update', $item['entity_id']) }}">
                  @csrf
                  @method('PATCH')
                  <div class="modal-header">
                    <h5 class="modal-title" id="{{ $modalId }}-title">Edit {{ $item['entity_type'] === 'section' ? 'folder' : 'resource' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label" for="{{ $modalId }}-name">Name</label>
                      <input id="{{ $modalId }}-name" name="title" type="text" class="form-control" value="{{ $item['title'] }}" maxlength="255" required>
                    </div>
                    <div>
                      <label class="form-label" for="{{ $modalId }}-description">Description</label>
                      <textarea id="{{ $modalId }}-description" name="description" class="form-control" rows="3" maxlength="300">{{ $item['entity_description'] ?? '' }}</textarea>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        @endif
      </div>
    @endforeach

    @if(empty($library_list))
      <div class="col-12">
        <div class="border rounded p-4 text-body-secondary">
          No folders or resources in this Library folder yet.
        </div>
      </div>
    @endif
  </div>
</div>
@endsection
