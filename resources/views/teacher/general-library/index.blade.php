@extends('layouts/layoutMaster')

@section('title', 'Library')
@section('meta_description', 'Shared To Quran Library for Quran videos, Arabic sources, and reusable teacher materials.')

@section('content')
<style>
  .tq-library-shell {
    --tq-library-border: color-mix(in srgb, var(--bs-border-color) 76%, var(--bs-primary));
    --tq-library-shadow: 0 .7rem 1.4rem rgba(47,43,61,.07);
  }

  .tq-library-hero {
    border: 1px solid var(--tq-library-border);
    background:
      radial-gradient(circle at 88% 12%, rgba(211,162,61,.16), transparent 30%),
      linear-gradient(135deg, var(--bs-paper-bg), color-mix(in srgb, var(--bs-paper-bg) 90%, var(--bs-primary)));
  }

  .tq-library-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(18rem, 100%), 1fr));
    gap: 1rem;
  }

  .tq-library-grid.is-sparse {
    grid-template-columns: repeat(auto-fill, minmax(min(17rem, 100%), 24rem));
  }

  .tq-library-card {
    min-height: 10.5rem;
    border: 1px solid var(--bs-border-color);
    box-shadow: var(--tq-library-shadow);
    transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease;
  }

  .tq-library-card:hover,
  .tq-library-card:focus-within {
    border-color: rgba(var(--bs-primary-rgb), .34);
    transform: translateY(-1px);
  }

  .tq-library-card.is-sortable {
    cursor: grab;
  }

  .tq-library-card.is-dragging {
    opacity: .55;
    transform: scale(.985);
  }

  .tq-library-drag-handle {
    display: none;
  }

  .tq-library-grid.tq-library-reorder-on .tq-library-drag-handle {
    display: inline-flex;
  }

  .tq-library-icon {
    inline-size: 2.75rem;
    block-size: 2.75rem;
    border-radius: .75rem;
    flex: 0 0 auto;
  }

  .tq-library-card-top {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr);
    gap: .75rem;
    align-items: start;
  }

  .tq-library-card-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    align-items: flex-start;
    gap: .35rem;
    min-width: 0;
  }

  .tq-library-title {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .tq-library-description {
    display: -webkit-box;
    overflow: hidden;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
  }

  .tq-source-modal .modal-content,
  .tq-edit-modal .modal-content {
    border: 0;
    box-shadow: 0 1.5rem 3rem rgba(47,43,61,.18);
  }

  .tq-source-modal .modal-dialog {
    max-width: min(72rem, calc(100vw - 1.5rem));
    height: min(52rem, calc(100vh - 1.5rem));
    height: min(52rem, calc(100dvh - 1.5rem));
  }

  .tq-source-modal .modal-content {
    display: flex;
    flex-direction: column;
    height: 100%;
    max-height: 100%;
    overflow: hidden;
  }

  .tq-source-modal form {
    display: flex;
    min-height: 0;
    height: 100%;
    flex-direction: column;
  }

  .tq-source-modal .modal-header,
  .tq-source-modal .modal-footer {
    flex: 0 0 auto;
  }

  .tq-source-modal .modal-header,
  .tq-edit-modal .modal-header {
    position: relative;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.25rem 3rem 1rem 1.5rem;
  }

  .tq-source-modal .modal-header .btn-close,
  .tq-edit-modal .modal-header .btn-close {
    position: absolute;
    top: .9rem;
    right: .9rem;
    left: auto;
    flex: 0 0 auto;
    margin: 0;
    opacity: .68;
    transform: none;
    z-index: 2;
  }

  .tq-source-modal .modal-header .btn-close:hover,
  .tq-source-modal .modal-header .btn-close:focus,
  .tq-edit-modal .modal-header .btn-close:hover,
  .tq-edit-modal .modal-header .btn-close:focus {
    opacity: 1;
    transform: none;
  }

  .tq-source-modal .modal-body {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    padding-block: 1.25rem;
    scrollbar-gutter: stable;
  }

  .tq-source-panel {
    border: 1px solid color-mix(in srgb, var(--bs-border-color) 82%, var(--bs-body-color));
    border-radius: .75rem;
    padding: 1rem;
    background: var(--bs-paper-bg);
  }

  .tq-source-panel + .tq-source-panel {
    margin-top: 1rem;
  }

  .tq-source-row {
    display: grid;
    grid-template-columns: minmax(10rem, 18rem) minmax(0, 1fr) auto;
    gap: .5rem;
    align-items: end;
  }

  .tq-source-queued {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-top: .75rem;
  }

  .tq-source-queued .badge {
    max-width: 100%;
    white-space: normal;
    text-align: start;
  }

  .tq-source-section-mark {
    inline-size: 2.75rem;
    block-size: 2.75rem;
    border-radius: .75rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    font-size: 1.2rem;
  }

  .tq-source-file-mark {
    background: color-mix(in srgb, var(--bs-info) 13%, var(--bs-paper-bg));
    color: var(--bs-info);
  }

  .tq-source-link-mark {
    background: color-mix(in srgb, var(--bs-success) 13%, var(--bs-paper-bg));
    color: var(--bs-success);
  }

  .tq-upload-progress {
    min-width: min(22rem, 100%);
  }

  .tq-upload-progress .progress {
    height: .35rem;
  }

  .tq-upload-file-list {
    display: grid;
    gap: .45rem;
    margin-top: .85rem;
  }

  .tq-upload-file-item {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    gap: .6rem;
    align-items: center;
    padding: .55rem .65rem;
    border: 1px solid var(--bs-border-color);
    border-radius: .65rem;
    background: color-mix(in srgb, var(--bs-paper-bg) 94%, var(--bs-body-bg));
  }

  .tq-upload-file-item.is-error {
    border-color: color-mix(in srgb, var(--bs-danger) 50%, var(--bs-border-color));
    background: color-mix(in srgb, var(--bs-danger) 8%, var(--bs-paper-bg));
  }

  .tq-upload-file-title {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 700;
  }

  .tq-upload-file-note {
    font-size: .75rem;
    color: var(--bs-secondary-color);
  }

  .tq-library-viewer-modal .modal-dialog {
    margin: 0;
    max-width: none;
  }

  .tq-library-viewer-modal .modal-content {
    min-height: 100vh;
    border: 0;
    border-radius: 0;
    background: #f8fafc;
  }

  .tq-library-viewer-bar {
    min-height: 56px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
    gap: .75rem;
    padding: .45rem .75rem;
    border-bottom: 1px solid rgba(47,43,61,.12);
    background: var(--bs-paper-bg);
  }

  .tq-viewer-title-wrap {
    min-width: 0;
  }

  .tq-viewer-kind {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: .72rem;
    color: var(--bs-secondary-color);
  }

  .tq-viewer-title {
    margin: .1rem 0 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 1.05rem;
    line-height: 1.25;
    font-weight: 700;
  }

  .tq-viewer-actions {
    display: flex;
    align-items: center;
    gap: .45rem;
  }

  .tq-viewer-icon-btn {
    inline-size: 2.5rem;
    block-size: 2.5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(47,43,61,.14);
    border-radius: .5rem;
    background: var(--bs-paper-bg);
    color: var(--bs-body-color);
  }

  .tq-viewer-icon-btn:disabled {
    opacity: .35;
    cursor: not-allowed;
  }

  .tq-viewer-count {
    min-width: 2.6rem;
    text-align: center;
    font-size: .85rem;
    font-weight: 700;
    color: var(--bs-secondary-color);
  }

  .tq-viewer-stage {
    position: relative;
    height: calc(100vh - 56px);
    overflow: hidden;
    background: #edf3fb;
  }

  .tq-viewer-frame-wrap {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: .55rem;
    overflow: hidden;
  }

  .tq-library-viewer-frame {
    inline-size: 100%;
    block-size: 100%;
    border: 0;
    border-radius: .35rem;
    background: var(--bs-paper-bg);
  }

  .tq-library-viewer-frame.is-document {
    inline-size: min(100%, 84rem);
    box-shadow: 0 1rem 2.5rem rgba(47,43,61,.16);
  }

  .tq-library-viewer-frame.is-youtube,
  .tq-library-viewer-media {
    inline-size: min(100%, 78rem, calc((100vh - 4.5rem) * 16 / 9));
    block-size: auto;
    aspect-ratio: 16 / 9;
    border-radius: .45rem;
  }

  .tq-library-viewer-image {
    display: block;
    max-inline-size: 100%;
    max-block-size: 100%;
    object-fit: contain;
    border-radius: .35rem;
    box-shadow: 0 1rem 2.5rem rgba(47,43,61,.16);
    transform-origin: center center;
    will-change: transform;
    transition: transform .16s ease;
  }

  .tq-library-viewer-image.is-hidden {
    display: none;
  }

  .tq-library-viewer-media {
    display: block;
    max-inline-size: 100%;
    max-block-size: 100%;
    background: #000;
    object-fit: contain;
    box-shadow: 0 1rem 2.5rem rgba(47,43,61,.16);
  }

  .tq-library-viewer-media.is-hidden {
    display: none;
  }

  .tq-viewer-spinner {
    position: absolute;
    inset: 0;
    display: grid;
    place-items: center;
    background: color-mix(in srgb, var(--bs-body-bg) 74%, transparent);
    z-index: 2;
  }

  .tq-viewer-spinner.is-hidden {
    display: none;
  }

  .tq-viewer-zoom-controls {
    position: absolute;
    top: .75rem;
    right: .75rem;
    z-index: 3;
    display: none;
    align-items: center;
    gap: .4rem;
    padding: .35rem;
    border: 1px solid rgba(47,43,61,.12);
    border-radius: .55rem;
    background: rgba(255,255,255,.94);
    box-shadow: 0 .5rem 1.25rem rgba(47,43,61,.12);
  }

  .tq-viewer-zoom-controls.is-visible {
    display: inline-flex;
  }

  .tq-viewer-zoom-label {
    min-width: 2.8rem;
    text-align: center;
    font-size: .75rem;
    font-weight: 700;
  }

  .tq-viewer-external-panel {
    min-height: 100%;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
  }

  .tq-viewer-external-panel.is-visible {
    display: flex;
  }

  @media (min-width: 1400px) {
    .tq-library-grid:not(.is-sparse) {
      grid-template-columns: repeat(auto-fit, minmax(19rem, 1fr));
    }
  }

  @media (max-width: 991.98px) {
    .tq-library-grid:not(.is-sparse) {
      grid-template-columns: repeat(auto-fit, minmax(min(16.5rem, 100%), 1fr));
    }
  }

  @media (min-width: 640px) and (max-width: 991.98px) {
    .tq-library-grid,
    .tq-library-grid.is-sparse,
    .tq-library-grid:not(.is-sparse) {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }

  @media (max-width: 575.98px) {
    .tq-library-grid {
      grid-template-columns: 1fr;
    }

    .tq-library-card {
      min-height: auto;
    }

    .tq-library-card-top {
      grid-template-columns: auto minmax(0, 1fr);
      gap: .65rem;
    }

    .tq-library-card-actions .badge {
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .tq-library-icon {
      inline-size: 2.5rem;
      block-size: 2.5rem;
    }

    .tq-source-row {
      grid-template-columns: 1fr;
    }

    .tq-source-modal .modal-dialog {
      height: calc(100vh - 1rem);
      height: calc(100dvh - 1rem);
      height: calc(100svh - 1rem);
      max-height: calc(100vh - 1rem);
      max-height: calc(100dvh - 1rem);
      max-height: calc(100svh - 1rem);
      width: calc(100vw - 1rem);
      max-width: calc(100vw - 1rem);
      margin: .5rem;
      align-items: stretch;
    }

    .tq-source-modal .modal-content {
      height: 100%;
      max-height: 100%;
    }

    .tq-source-modal .modal-header,
    .tq-edit-modal .modal-header {
      padding: 1rem 3rem .75rem 1rem;
    }

    .tq-source-modal .modal-header .btn-close,
    .tq-edit-modal .modal-header .btn-close {
      top: .7rem;
      right: .7rem;
    }

    .tq-source-modal .modal-body {
      flex: 1 1 auto;
      min-height: 0;
      max-height: none;
      overflow-y: auto;
      padding: 1rem;
      padding-bottom: 1.25rem;
    }

    .tq-source-modal .modal-footer {
      flex: 0 0 auto;
      display: grid;
      grid-template-columns: minmax(0, .9fr) minmax(0, 1.25fr);
      gap: .55rem;
      padding: .75rem 1rem;
      padding-bottom: calc(.75rem + env(safe-area-inset-bottom));
      background: var(--bs-paper-bg);
      box-shadow: 0 -.5rem 1.25rem rgba(47,43,61,.08);
      z-index: 4;
    }

    .tq-source-modal .modal-footer .btn {
      width: 100%;
      min-height: 2.75rem;
      margin: 0;
      white-space: nowrap;
    }

    .tq-source-modal .tq-save-extra-label {
      display: none;
    }

    .tq-source-modal [data-source-footer-note],
    .tq-source-modal .modal-footer [data-upload-progress] {
      display: none !important;
    }

    .tq-library-viewer-bar {
      gap: .5rem;
      padding: .4rem .5rem;
    }

    .tq-viewer-actions {
      gap: .25rem;
    }

    .tq-viewer-icon-btn {
      inline-size: 2.25rem;
      block-size: 2.25rem;
    }

    .tq-viewer-count {
      min-width: 2rem;
      font-size: .75rem;
    }

    .tq-viewer-stage {
      height: calc(100vh - 54px);
    }

    .tq-viewer-frame-wrap {
      padding: .4rem;
      overflow: hidden;
    }

    .tq-library-viewer-frame.is-youtube,
    .tq-library-viewer-media {
      inline-size: 100%;
      max-inline-size: 100%;
    }

    .tq-library-viewer-image {
      max-inline-size: 100%;
      max-block-size: calc(100vh - 7.5rem);
      max-block-size: calc(100dvh - 7.5rem);
      max-block-size: calc(100svh - 7.5rem);
    }
  }
</style>

@php
  $user = auth()->user();
  $canManageEverything = $user?->hasAnyRole(['admin', 'super_admin']);
  $currentFolderId = $folder?->id;
  $currentTitle = $folder?->title ?? 'To Quran Library';
  $rootUrl = route($libraryRouteName);
  $resourceList = $resources->values();
  $itemCount = $folders->count() + $resourceList->count();
@endphp

<div class="tq-library-shell">
  <div class="card tq-library-hero mb-5">
    <div class="card-body">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <span class="badge bg-label-primary mb-3">Shared Library</span>
          <h4 class="mb-1">{{ $currentTitle }}</h4>
          <div class="text-body-secondary">
            @if($folder?->isSourcesOnly())
              Final source folder for reusable Library materials.
            @elseif($folder)
              Shared Library folder. Add subfolders or sources according to the folder structure.
            @else
              Quran videos, Arabic sources, and reusable teaching materials for all teachers.
            @endif
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-center">
          @if($canReorderPageHere)
            <button type="button" class="btn btn-sm btn-outline-secondary" data-library-reorder-toggle>
              <i class="ti tabler-arrows-sort me-1"></i>
              Reorder
            </button>
          @endif

          @if($folder)
            <a href="{{ $folder->parent_id ? route($libraryRouteName, ['folder' => (int) $folder->parent_id]) : $rootUrl }}" class="btn btn-sm btn-outline-secondary">
              <i class="ti tabler-arrow-left me-1"></i>
              Back
            </a>
          @endif

          @if($canCreateSubfolderHere)
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tq-library-folder-modal">
              <i class="ti tabler-folder-plus me-1"></i>
              Folder
            </button>
          @endif

          @if($canCreateSourceHere)
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tq-library-resource-modal">
              <i class="ti tabler-plus me-1"></i>
              Source
            </button>
          @endif
        </div>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-warning">{{ $errors->first() }}</div>
  @endif

  @if(! $canCreateSubfolderHere && $folder)
    <div class="alert alert-info">
      This folder is a final destination for sources, so it does not accept subfolders.
    </div>
  @endif

  @if(! $canCreateSourceHere && $folder)
    <div class="alert alert-info">
      Add sources inside one of this folder's child folders.
    </div>
  @endif

  @if($canCreateSubfolderHere)
    <div class="modal fade tq-edit-modal" id="tq-library-folder-modal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form method="POST" action="{{ route('teacher.general-library.folders.store') }}" data-submit-once>
            @csrf
            <input type="hidden" name="parent_id" value="{{ $currentFolderId }}">
            <div class="modal-header">
              <div>
                <div class="small fw-semibold text-uppercase text-body-secondary">Folder</div>
                <h5 class="modal-title">Add folder</h5>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <label class="form-label" for="library-folder-title">Title</label>
              <input id="library-folder-title" name="title" class="form-control mb-3" maxlength="255" required>

              <label class="form-label" for="library-folder-description">Description</label>
              <textarea id="library-folder-description" name="description" class="form-control mb-3" rows="3" maxlength="500"></textarea>

              <label class="form-check border rounded p-3 d-flex gap-3 align-items-start" for="library-folder-sources-only">
                <input id="library-folder-sources-only" class="form-check-input ms-0" type="checkbox" name="content_mode" value="sources_only">
                <span>
                  <span class="d-block fw-semibold">Final source folder</span>
                  <span class="d-block small text-body-secondary">Use this when the folder should contain sources only for later series assignment.</span>
                </span>
              </label>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif

  @if($canCreateSourceHere)
    <div class="modal fade tq-source-modal" id="tq-library-resource-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <form method="POST" action="{{ route('teacher.general-library.resources.store') }}" enctype="multipart/form-data" data-library-source-form data-upload-url="{{ route('teacher.general-library.resources.upload-temp') }}" data-delete-upload-url="{{ route('teacher.general-library.resources.upload-temp.delete') }}">
            @csrf
            <input type="hidden" name="folder_id" value="{{ $currentFolderId }}">
            <input type="hidden" name="resource_kind" value="batch">
            <div class="modal-header">
              <div>
                <div class="small fw-semibold text-uppercase text-body-secondary">Adding to folder</div>
                <h5 class="modal-title">{{ $currentTitle }}</h5>
                <div class="small text-body-secondary">Files, links, and YouTube links are saved together.</div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="tq-source-panel">
                <div class="d-flex gap-3 mb-3">
                  <span class="tq-source-section-mark tq-source-file-mark">
                    <i class="ti tabler-file-upload"></i>
                  </span>
                  <div class="min-w-0">
                    <h6 class="mb-1">Files</h6>
                    <div class="small text-body-secondary">Select one or more protected files.</div>
                  </div>
                </div>
                <label class="form-label" for="library-resource-title">Title</label>
                <input id="library-resource-title" name="title" class="form-control mb-2" maxlength="255">
                <div class="form-text mb-3">Optional for one file. Multiple files use their filenames as titles.</div>
                <label class="form-label" for="library-resource-description">Description</label>
                <textarea id="library-resource-description" name="description" class="form-control mb-3" rows="2" maxlength="500"></textarea>
                <label class="form-label" for="library-resource-files">Files</label>
                <input id="library-resource-files" type="file" class="form-control" accept="{{ \App\Services\Library\LibraryResourceValidator::acceptAttribute() }}" data-library-file-input multiple>
                <div class="form-text">Maximum file size: {{ \App\Services\Library\LibraryResourceValidator::MAX_UPLOAD_KB / 1024 }} MB each.</div>
                <div class="tq-upload-progress d-none mt-3" data-file-upload-progress>
                  <div class="d-flex justify-content-between gap-3 small fw-semibold mb-1">
                    <span data-file-upload-progress-label>Uploading files...</span>
                    <span data-file-upload-progress-percent>0%</span>
                  </div>
                  <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" data-file-upload-progress-bar style="width: 0%"></div>
                  </div>
                </div>
                <div class="alert alert-warning d-none mt-3 mb-0" data-upload-error></div>
                <div class="tq-upload-file-list" data-upload-file-list></div>
              </div>

              <div class="tq-source-panel">
                <div class="d-flex gap-3 mb-3">
                  <div>
                    <h6 class="mb-1">Links</h6>
                    <div class="small text-body-secondary">Queue regular links or YouTube links, then save once.</div>
                  </div>
                </div>
                <div class="tq-source-row mb-3" data-library-queue-row="link">
                  <div>
                    <label class="form-label" for="library-link-title">Link title</label>
                    <input id="library-link-title" class="form-control" data-library-queue-title maxlength="255">
                  </div>
                  <div>
                    <label class="form-label" for="library-link-url">URL</label>
                    <input id="library-link-url" class="form-control" data-library-queue-url maxlength="2048" placeholder="https://...">
                  </div>
                  <button type="button" class="btn btn-outline-primary" data-library-queue-add="link" aria-label="Add link">
                    <i class="ti tabler-plus"></i>
                  </button>
                </div>
                <div class="tq-source-row" data-library-queue-row="youtube">
                  <div>
                    <label class="form-label" for="library-youtube-title">YouTube title</label>
                    <input id="library-youtube-title" class="form-control" data-library-queue-title maxlength="255">
                  </div>
                  <div>
                    <label class="form-label" for="library-youtube-url">YouTube URL</label>
                    <input id="library-youtube-url" class="form-control" data-library-queue-url maxlength="2048" placeholder="https://youtu.be/...">
                  </div>
                  <button type="button" class="btn btn-outline-primary" data-library-queue-add="youtube" aria-label="Add YouTube">
                    <i class="ti tabler-plus"></i>
                  </button>
                </div>
                <div class="tq-source-queued" data-library-queue-list></div>
              </div>
            </div>
            <div class="modal-footer">
              <div class="tq-upload-progress d-none me-auto" data-upload-progress>
                <div class="d-flex justify-content-between gap-3 small fw-semibold mb-1">
                  <span data-upload-progress-label>Preparing sources...</span>
                </div>
                <div class="progress">
                  <div class="progress-bar progress-bar-striped progress-bar-animated" data-upload-progress-bar style="width: 0%"></div>
                </div>
              </div>
              <span class="small text-body-secondary me-auto" data-source-footer-note>New cards appear here after saving.</span>
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">
                <i class="ti tabler-device-floppy me-1"></i>
                Save <span class="tq-save-extra-label">sources</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endif

  @if($folders->isEmpty() && $resources->isEmpty())
    <div class="card">
      <div class="card-body text-center py-5">
        <i class="ti tabler-library fs-1 text-primary"></i>
        <h5 class="mt-3 mb-1">No Library materials yet</h5>
        <p class="text-body-secondary mb-0">Add a folder or source to start the shared Library.</p>
      </div>
    </div>
  @endif

  <div class="tq-library-grid {{ $itemCount <= 2 ? 'is-sparse' : '' }}" data-library-grid data-reorder-url="{{ route('teacher.general-library.items.reorder') }}" data-folder-id="{{ $currentFolderId }}">
    @foreach($folders as $item)
      @php $canEdit = $canManageEverything || (int) $item->created_by_user_id === (int) $user?->id; @endphp
      <div class="card tq-library-card h-100" data-library-item-card data-library-item-type="folder" data-library-item-id="{{ $item->id }}">
        <div class="card-body d-flex flex-column gap-3">
          <div class="tq-library-card-top">
            <span class="tq-library-icon bg-label-primary d-inline-flex align-items-center justify-content-center">
              <i class="ti tabler-folder text-primary"></i>
            </span>
            <div class="tq-library-card-actions">
              <button type="button" class="btn btn-sm btn-icon btn-outline-secondary tq-library-drag-handle" title="Drag to reorder" aria-label="Drag {{ $item->title }}">
                <i class="ti tabler-grip-vertical"></i>
              </button>
              @if($item->source_label === 'Original')
                <span class="badge bg-label-primary">Original</span>
              @endif
              @if($item->isSourcesOnly())
                <span class="badge bg-label-info">Sources only</span>
              @endif
              @if($canEdit)
                <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" title="Edit" aria-label="Edit {{ $item->title }}" data-bs-toggle="modal" data-bs-target="#tq-library-folder-edit-{{ $item->id }}">
                  <i class="ti tabler-pencil"></i>
                </button>
                <form method="POST" action="{{ route('teacher.general-library.folders.archive', $item) }}">
                  @csrf
                  @method('PATCH')
                  <button class="btn btn-sm btn-icon btn-outline-secondary" title="Archive" aria-label="Archive {{ $item->title }}">
                    <i class="ti tabler-archive"></i>
                  </button>
                </form>
                <form method="POST" action="{{ route('teacher.general-library.folders.delete', $item) }}" onsubmit="return confirm('Delete this folder? Folders with items will be archived safely instead.');">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-icon btn-outline-danger" title="Delete" aria-label="Delete {{ $item->title }}">
                    <i class="ti tabler-trash"></i>
                  </button>
                </form>
              @endif
            </div>
          </div>
          <div>
            <h6 class="tq-library-title mb-1">{{ $item->title }}</h6>
            <div class="small text-body-secondary tq-library-description">
              {{ $item->description ?: $item->active_children_count.' folders, '.$item->active_resources_count.' sources' }}
            </div>
          </div>
          <a class="btn btn-sm btn-outline-primary mt-auto" href="{{ route($libraryRouteName, ['folder' => (int) $item->id]) }}">Open</a>
        </div>
      </div>

      @if($canEdit)
        <div class="modal fade tq-edit-modal" id="tq-library-folder-edit-{{ $item->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <form method="POST" action="{{ route('teacher.general-library.folders.update', $item) }}">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                  <div>
                    <div class="small fw-semibold text-uppercase text-body-secondary">Folder</div>
                    <h5 class="modal-title">Edit folder</h5>
                  </div>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <label class="form-label" for="library-folder-edit-title-{{ $item->id }}">Title</label>
                  <input id="library-folder-edit-title-{{ $item->id }}" name="title" class="form-control mb-3" maxlength="255" value="{{ $item->title }}" required>
                  <label class="form-label" for="library-folder-edit-description-{{ $item->id }}">Description</label>
                  <textarea id="library-folder-edit-description-{{ $item->id }}" name="description" class="form-control mb-3" rows="3" maxlength="500">{{ $item->description }}</textarea>
                  <label class="form-check border rounded p-3 d-flex gap-3 align-items-start" for="library-folder-edit-sources-only-{{ $item->id }}">
                    <input id="library-folder-edit-sources-only-{{ $item->id }}" class="form-check-input ms-0" type="checkbox" name="content_mode" value="sources_only" @checked($item->isSourcesOnly())>
                    <span>
                      <span class="d-block fw-semibold">Final source folder</span>
                      <span class="d-block small text-body-secondary">Folders with sources stay sources-only.</span>
                    </span>
                  </label>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Save</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      @endif
    @endforeach

    @foreach($resourceList as $index => $item)
      @php
        $canEdit = $canManageEverything || (int) $item->created_by_user_id === (int) $user?->id;
        $icon = $item->isFile() ? 'ti tabler-file' : ($item->isYoutube() ? 'ti tabler-brand-youtube' : 'ti tabler-link');
        $tone = $item->isFile() ? 'info' : ($item->isYoutube() ? 'danger' : 'success');
        $embedUrl = $item->isYoutube() ? \App\Helpers\Helpers::trustedVideoEmbedUrl((string) $item->external_url) : null;
        $viewerSrc = $item->isYoutube() ? $embedUrl : ($item->isFile() ? route('teacher.general-library.resources.file', $item) : null);
        $fileExtension = strtolower(pathinfo((string) ($item->original_filename ?: $item->title ?: $item->file_path), PATHINFO_EXTENSION));
        $mimeType = strtolower((string) $item->mime_type);
        $viewerKind = match (true) {
          $item->isYoutube() => 'youtube',
          $item->isFile() && ($mimeType === 'application/pdf' || $fileExtension === 'pdf') => 'pdf',
          $item->isFile() && (str_starts_with($mimeType, 'image/') || in_array($fileExtension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) => 'image',
          $item->isFile() && (str_starts_with($mimeType, 'video/') || in_array($fileExtension, ['mp4', 'webm'], true)) => 'video',
          $item->isFile() && (str_starts_with($mimeType, 'audio/') || in_array($fileExtension, ['mp3', 'm4a', 'wav'], true)) => 'audio',
          $item->isFile() => 'file',
          default => 'link',
        };
        $viewerKindLabel = match ($viewerKind) {
          'youtube' => 'YouTube source',
          'pdf' => 'PDF source',
          'image' => 'Image source',
          'video' => 'Video source',
          'audio' => 'Audio source',
          'file' => 'File source',
          default => 'Link source',
        };
      @endphp
      <div class="card tq-library-card h-100" data-library-item-card data-library-item-type="resource" data-library-item-id="{{ $item->id }}">
        <div class="card-body d-flex flex-column gap-3">
          <div class="tq-library-card-top">
            <span class="tq-library-icon bg-label-{{ $tone }} d-inline-flex align-items-center justify-content-center">
              <i class="{{ $icon }} text-{{ $tone }}"></i>
            </span>
            <div class="tq-library-card-actions">
              <button type="button" class="btn btn-sm btn-icon btn-outline-secondary tq-library-drag-handle" title="Drag to reorder" aria-label="Drag {{ $item->title }}">
                <i class="ti tabler-grip-vertical"></i>
              </button>
              @if($item->source_label === 'Original')
                <span class="badge bg-label-primary">Original</span>
              @endif
              @if($canEdit)
                <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" title="Edit" aria-label="Edit {{ $item->title }}" data-bs-toggle="modal" data-bs-target="#tq-library-resource-edit-{{ $item->id }}">
                  <i class="ti tabler-pencil"></i>
                </button>
                <form method="POST" action="{{ route('teacher.general-library.resources.archive', $item) }}">
                  @csrf
                  @method('PATCH')
                  <button class="btn btn-sm btn-icon btn-outline-secondary" title="Archive" aria-label="Archive {{ $item->title }}">
                    <i class="ti tabler-archive"></i>
                  </button>
                </form>
                <form method="POST" action="{{ route('teacher.general-library.resources.delete', $item) }}" onsubmit="return confirm('Delete this source? Assigned copies will be archived safely instead of broken.');">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-icon btn-outline-danger" title="Delete" aria-label="Delete {{ $item->title }}">
                    <i class="ti tabler-trash"></i>
                  </button>
                </form>
              @endif
            </div>
          </div>
          <div>
            <h6 class="tq-library-title mb-1">{{ $item->title }}</h6>
            <div class="small text-body-secondary tq-library-description">
              {{ $item->description ?: ($item->original_filename ?: $item->external_url) }}
            </div>
          </div>
          <button
            type="button"
            class="btn btn-sm btn-outline-primary mt-auto"
            data-library-viewer-open
            data-viewer-index="{{ $index }}"
            data-viewer-title="{{ $item->title }}"
            data-viewer-kind="{{ $viewerKind }}"
            data-viewer-kind-label="{{ $viewerKindLabel }}"
            data-viewer-context="{{ $currentTitle }}"
            data-viewer-src="{{ (string) $viewerSrc }}"
            data-viewer-external-url="{{ (string) $item->external_url }}">
            Open
          </button>
        </div>
      </div>

      @if($canEdit)
        <div class="modal fade tq-edit-modal" id="tq-library-resource-edit-{{ $item->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
              <form method="POST" action="{{ route('teacher.general-library.resources.update', $item) }}">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                  <div class="d-flex align-items-center gap-3">
                    <span class="tq-library-icon bg-label-{{ $tone }} d-inline-flex align-items-center justify-content-center">
                      <i class="{{ $icon }} text-{{ $tone }}"></i>
                    </span>
                    <div>
                      <div class="small fw-semibold text-uppercase text-body-secondary">{{ ucfirst($item->resource_type) }} source</div>
                      <h5 class="modal-title">Edit source</h5>
                    </div>
                  </div>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label" for="library-resource-edit-title-{{ $item->id }}">Title</label>
                      <input id="library-resource-edit-title-{{ $item->id }}" name="title" class="form-control" maxlength="255" value="{{ $item->title }}" required>
                    </div>
                    @if(! $item->isFile())
                      <div class="col-md-6">
                        <label class="form-label" for="library-resource-edit-url-{{ $item->id }}">{{ $item->isYoutube() ? 'YouTube URL' : 'Link URL' }}</label>
                        <input id="library-resource-edit-url-{{ $item->id }}" name="external_url" class="form-control" maxlength="2048" value="{{ $item->external_url }}" required>
                      </div>
                    @endif
                    <div class="col-12">
                      <label class="form-label" for="library-resource-edit-description-{{ $item->id }}">Description</label>
                      <textarea id="library-resource-edit-description-{{ $item->id }}" name="description" class="form-control" rows="3" maxlength="500">{{ $item->description }}</textarea>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Save</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      @endif
    @endforeach
  </div>

  <div class="modal fade tq-library-viewer-modal" id="tq-library-viewer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
      <div class="modal-content">
        <div class="tq-library-viewer-bar">
          <div class="tq-viewer-title-wrap">
            <span class="tq-viewer-kind" data-viewer-kind-label>Source</span>
            <h5 class="tq-viewer-title" data-viewer-title>Library source</h5>
          </div>
          <div class="tq-viewer-actions">
            <button type="button" class="tq-viewer-icon-btn" data-viewer-prev aria-label="Previous source">
              <i class="ti tabler-chevron-left"></i>
            </button>
            <span class="tq-viewer-count" data-viewer-count>0/0</span>
            <button type="button" class="tq-viewer-icon-btn" data-viewer-next aria-label="Next source">
              <i class="ti tabler-chevron-right"></i>
            </button>
            <button type="button" class="tq-viewer-icon-btn" data-bs-dismiss="modal" aria-label="Close source viewer">
              <i class="ti tabler-x"></i>
            </button>
          </div>
        </div>
        <div class="tq-viewer-stage">
          <div class="tq-viewer-spinner is-hidden" data-viewer-spinner>
            <div class="text-center">
              <div class="spinner-border text-primary" role="status" aria-label="Loading source"></div>
              <div class="fw-semibold mt-2">Still loading...</div>
            </div>
          </div>
          <div class="tq-viewer-zoom-controls" data-viewer-zoom-controls aria-label="File zoom controls">
            <button type="button" class="tq-viewer-icon-btn" data-viewer-zoom="-" aria-label="Zoom out">
              <i class="ti tabler-minus"></i>
            </button>
            <span class="tq-viewer-zoom-label" data-viewer-zoom-label>100%</span>
            <button type="button" class="tq-viewer-icon-btn" data-viewer-zoom="+" aria-label="Zoom in">
              <i class="ti tabler-plus"></i>
            </button>
            <button type="button" class="tq-viewer-icon-btn" data-viewer-zoom="reset" aria-label="Reset zoom">
              <i class="ti tabler-refresh"></i>
            </button>
          </div>
          <div class="tq-viewer-frame-wrap" data-viewer-frame-wrap>
            <img class="tq-library-viewer-image is-hidden" data-viewer-image alt="">
            <video class="tq-library-viewer-media is-hidden" data-viewer-video controls playsinline preload="metadata"></video>
            <audio class="tq-library-viewer-media is-hidden" data-viewer-audio controls preload="metadata"></audio>
            <iframe class="tq-library-viewer-frame" data-viewer-frame src="about:blank" title="Library source" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="eager"></iframe>
          </div>
          <div class="tq-viewer-external-panel" data-viewer-external-panel>
            <div class="border rounded p-4 text-center bg-body">
              <i class="ti tabler-external-link fs-1 text-primary"></i>
              <h5 class="mt-3 mb-2">Open this source</h5>
              <p class="text-body-secondary mb-2">This website may not allow in-app preview.</p>
              <a href="#" target="_blank" rel="noopener" data-viewer-external-link>Open link</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  document.querySelectorAll('[data-submit-once]').forEach((form) => {
    form.addEventListener('submit', () => {
      form.querySelectorAll('button[type="submit"]').forEach((button) => {
        button.disabled = true;
      });
    });
  });

  document.querySelectorAll('[data-library-source-form]').forEach((form) => {
    const queueList = form.querySelector('[data-library-queue-list]');
    const fileInput = form.querySelector('[data-library-file-input]');
    const uploadProgress = form.querySelector('[data-file-upload-progress]');
    const uploadProgressBar = form.querySelector('[data-file-upload-progress-bar]');
    const uploadProgressPercent = form.querySelector('[data-file-upload-progress-percent]');
    const uploadProgressLabel = form.querySelector('[data-file-upload-progress-label]');
    const uploadError = form.querySelector('[data-upload-error]');
    const uploadFileList = form.querySelector('[data-upload-file-list]');
    const saveProgress = form.querySelector('[data-upload-progress]');
    const saveProgressBar = form.querySelector('[data-upload-progress-bar]');
    const saveProgressLabel = form.querySelector('[data-upload-progress-label]');
    const footerNote = form.querySelector('[data-source-footer-note]');
    const submitButton = form.querySelector('button[type="submit"]');
    const modalElement = form.closest('.modal');
    const modalBody = modalElement?.querySelector('.modal-body');
    const allowedExtensions = (fileInput?.getAttribute('accept') || '')
      .split(',')
      .map((extension) => extension.trim().replace('.', '').toLowerCase())
      .filter(Boolean);
    const maxBytes = {{ \App\Services\Library\LibraryResourceValidator::MAX_UPLOAD_KB * 1024 }};
    let stagedFiles = [];
    let blockedFiles = [];
    let selectedUploadFiles = [];
    let activeUpload = null;
    let sourceSubmitted = false;

    const formatSize = (bytes) => {
      if (bytes >= 1024 * 1024) {
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
      }

      return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    };

    const stagedTokens = () => Array.from(form.querySelectorAll('input[name="uploaded_files[]"]'))
      .map((input) => input.value)
      .filter(Boolean);

    const deleteStagedUploads = (tokens) => {
      if (!tokens.length || !form.dataset.deleteUploadUrl) {
        return;
      }

      fetch(form.dataset.deleteUploadUrl, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ uploaded_files: tokens }),
        keepalive: true,
      }).catch(() => {});
    };

    const resetUploadUi = (options = {}) => {
      if (options.deleteStaged) {
        deleteStagedUploads(stagedTokens());
      }
      stagedFiles = [];
      blockedFiles = [];
      selectedUploadFiles = [];
      activeUpload?.abort();
      activeUpload = null;
      form.querySelectorAll('input[name="uploaded_files[]"]').forEach((input) => input.remove());
      uploadFileList && (uploadFileList.innerHTML = '');
      uploadError?.classList.add('d-none');
      uploadError && (uploadError.textContent = '');
      uploadProgress?.classList.add('d-none');
      if (uploadProgressBar) {
        uploadProgressBar.style.width = '0%';
      }
      if (uploadProgressPercent) {
        uploadProgressPercent.textContent = '0%';
      }
      if (fileInput) {
        fileInput.value = '';
      }
    };

    const resetSourceModal = () => {
      activeUpload?.abort();
      form.reset();
      queueList && (queueList.innerHTML = '');
      resetUploadUi({ deleteStaged: !sourceSubmitted });
      sourceSubmitted = false;
      saveProgress?.classList.add('d-none');
      if (saveProgressBar) {
        saveProgressBar.style.width = '0%';
      }
      footerNote?.classList.remove('d-none');
      if (submitButton) {
        submitButton.disabled = false;
      }
      if (modalBody) {
        modalBody.scrollTop = 0;
      }
    };

    const addHiddenStagedToken = (token) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'uploaded_files[]';
      input.value = token;
      form.appendChild(input);
    };

    const renderFileList = (options = {}) => {
      if (!uploadFileList) {
        return;
      }

      uploadFileList.innerHTML = '';
      const rows = [
        ...blockedFiles.map((file) => ({ ...file, status: 'blocked' })),
        ...selectedUploadFiles.map((file) => ({ name: file.name, size: file.size, status: options.uploading ? 'uploading' : 'waiting' })),
        ...stagedFiles.map((file) => ({ ...file, status: 'ready' })),
      ];

      rows.forEach((file) => {
        const isBlocked = file.status === 'blocked';
        const isUploading = file.status === 'uploading';
        const isReady = file.status === 'ready';
        const item = document.createElement('div');
        item.className = `tq-upload-file-item${isBlocked ? ' is-error' : ''}`;
        item.innerHTML = `
          <i class="ti ${isBlocked ? 'tabler-alert-triangle text-warning' : (isReady ? 'tabler-file-check text-success' : 'tabler-cloud-upload text-info')}"></i>
          <div class="min-w-0">
            <div class="tq-upload-file-title"></div>
            <div class="tq-upload-file-note"></div>
          </div>
          <span class="badge ${isBlocked ? 'bg-label-warning' : (isReady ? 'bg-label-success' : 'bg-label-info')}">${isBlocked ? 'Blocked' : (isReady ? 'Ready' : (isUploading ? 'Uploading' : 'Queued'))}</span>
        `;
        item.querySelector('.tq-upload-file-title').textContent = file.name;
        item.querySelector('.tq-upload-file-note').textContent = isBlocked ? file.reason : formatSize(file.size || 0);
        uploadFileList.appendChild(item);
      });
    };

    const splitSelectedFiles = (files) => files.reduce((result, file) => {
        const extension = file.name.includes('.') ? file.name.split('.').pop().toLowerCase() : '';
        if (!allowedExtensions.includes(extension)) {
          result.blocked.push({ name: file.name, reason: 'Unsupported file type' });
          return result;
        }

        if (file.size > maxBytes) {
          result.blocked.push({ name: file.name, reason: `Larger than ${formatSize(maxBytes)}` });
          return result;
        }

        result.allowed.push(file);
        return result;
      }, { allowed: [], blocked: [] });

    const uploadFiles = (files) => {
      resetUploadUi({ deleteStaged: true });
      const splitFiles = splitSelectedFiles(files);
      selectedUploadFiles = splitFiles.allowed;
      blockedFiles = splitFiles.blocked;
      renderFileList();

      if (blockedFiles.length > 0 && selectedUploadFiles.length === 0) {
        if (uploadError) {
          uploadError.textContent = 'Remove unsupported or oversized files, then choose the files again.';
          uploadError.classList.remove('d-none');
        }
        return;
      }

      if (selectedUploadFiles.length === 0) {
        return;
      }

      const formData = new FormData();
      selectedUploadFiles.forEach((file) => formData.append('resource_files[]', file));

      const xhr = new XMLHttpRequest();
      activeUpload = xhr;
      uploadProgress?.classList.remove('d-none');
      if (uploadError) {
        uploadError.textContent = blockedFiles.length > 0
          ? 'Unsupported or oversized files were blocked. Supported files are uploading.'
          : '';
        uploadError.classList.toggle('d-none', blockedFiles.length === 0);
      }
      uploadProgressLabel && (uploadProgressLabel.textContent = 'Uploading files...');
      renderFileList({ uploading: true });

      xhr.upload.addEventListener('progress', (event) => {
        if (!event.lengthComputable) {
          return;
        }

        const percent = Math.max(1, Math.min(99, Math.round((event.loaded / event.total) * 100)));
        if (uploadProgressBar) {
          uploadProgressBar.style.width = `${percent}%`;
        }
        if (uploadProgressPercent) {
          uploadProgressPercent.textContent = `${percent}%`;
        }
      });

      xhr.addEventListener('load', () => {
        activeUpload = null;
        if (xhr.status < 200 || xhr.status >= 300) {
          let message = 'The files were not uploaded. Please check the file types and sizes.';
          try {
            const response = JSON.parse(xhr.responseText);
            message = response.message || response.errors?.resource_files?.[0] || message;
          } catch (error) {}

          uploadProgress?.classList.add('d-none');
          if (uploadError) {
            uploadError.textContent = message;
            uploadError.classList.remove('d-none');
          }
          blockedFiles = [
            ...blockedFiles,
            ...selectedUploadFiles.map((file) => ({ name: file.name, reason: 'Upload rejected' })),
          ];
          selectedUploadFiles = [];
          renderFileList();
          return;
        }

        let response = {};
        try {
          response = JSON.parse(xhr.responseText);
        } catch (error) {
          response = {};
        }
        stagedFiles = response.files || [];
        blockedFiles = [
          ...blockedFiles,
          ...(response.blocked || []),
        ];
        selectedUploadFiles = [];
        stagedFiles.forEach((file) => addHiddenStagedToken(file.token));
        renderFileList();
        if (uploadProgressBar) {
          uploadProgressBar.style.width = '100%';
        }
        if (uploadProgressPercent) {
          uploadProgressPercent.textContent = '100%';
        }
        uploadProgressLabel && (uploadProgressLabel.textContent = stagedFiles.length > 0 ? 'Files ready' : 'No supported files uploaded');
        if (uploadError) {
          uploadError.textContent = blockedFiles.length > 0
            ? 'Unsupported or oversized files were blocked. Supported files are ready to save.'
            : '';
          uploadError.classList.toggle('d-none', blockedFiles.length === 0);
        }
      });

      xhr.addEventListener('error', () => {
        activeUpload = null;
        uploadProgress?.classList.add('d-none');
        if (uploadError) {
          uploadError.textContent = 'The upload could not finish. Please try again.';
          uploadError.classList.remove('d-none');
        }
        blockedFiles = [
          ...blockedFiles,
          ...selectedUploadFiles.map((file) => ({ name: file.name, reason: 'Upload could not finish' })),
        ];
        selectedUploadFiles = [];
        renderFileList();
      });

      xhr.addEventListener('abort', () => {
        activeUpload = null;
        selectedUploadFiles = [];
        uploadProgress?.classList.add('d-none');
        renderFileList();
      });

      xhr.open('POST', form.dataset.uploadUrl);
      xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
      xhr.setRequestHeader('Accept', 'application/json');
      xhr.send(formData);
    };

    const addQueued = (kind) => {
      const row = form.querySelector(`[data-library-queue-row="${kind}"]`);
      const titleInput = row?.querySelector('[data-library-queue-title]');
      const urlInput = row?.querySelector('[data-library-queue-url]');
      const title = titleInput?.value.trim() || '';
      const url = urlInput?.value.trim() || '';
      if (!title || !url) {
        row?.classList.add('was-validated');
        titleInput?.focus();
        return;
      }

      const titleName = kind === 'youtube' ? 'youtube_titles[]' : 'link_titles[]';
      const urlName = kind === 'youtube' ? 'youtube_urls[]' : 'link_urls[]';
      const item = document.createElement('span');
      item.className = `badge ${kind === 'youtube' ? 'bg-label-danger' : 'bg-label-success'} d-inline-flex align-items-center gap-2`;

      const label = document.createElement('span');
      label.textContent = title;
      const removeButton = document.createElement('button');
      removeButton.type = 'button';
      removeButton.className = 'btn btn-xs btn-icon p-0 border-0';
      removeButton.setAttribute('aria-label', 'Remove queued source');
      removeButton.innerHTML = '<i class="ti tabler-x"></i>';
      const titleHidden = document.createElement('input');
      titleHidden.type = 'hidden';
      titleHidden.name = titleName;
      titleHidden.value = title;
      const urlHidden = document.createElement('input');
      urlHidden.type = 'hidden';
      urlHidden.name = urlName;
      urlHidden.value = url;

      item.append(label, removeButton, titleHidden, urlHidden);
      removeButton.addEventListener('click', () => item.remove());
      queueList?.appendChild(item);
      titleInput.value = '';
      urlInput.value = '';
      titleInput.focus();
    };

    form.querySelectorAll('[data-library-queue-add]').forEach((button) => {
      button.addEventListener('click', () => addQueued(button.dataset.libraryQueueAdd));
    });

    fileInput?.addEventListener('change', () => {
      uploadFiles(Array.from(fileInput.files || []));
    });

    modalElement?.addEventListener('shown.bs.modal', () => {
      if (modalBody) {
        modalBody.scrollTop = 0;
      }
    });

    form.addEventListener('submit', (event) => {
      if (activeUpload) {
        event.preventDefault();
        if (uploadError) {
          uploadError.textContent = 'Please wait for the file upload to finish before saving.';
          uploadError.classList.remove('d-none');
        }
        return;
      }

      footerNote?.classList.add('d-none');
      saveProgress?.classList.remove('d-none');
      if (saveProgressLabel) {
        saveProgressLabel.textContent = 'Saving sources...';
      }
      if (saveProgressBar) {
        saveProgressBar.style.width = '100%';
      }
      if (submitButton) {
        submitButton.disabled = true;
      }
      sourceSubmitted = true;
    });

    modalElement?.addEventListener('hidden.bs.modal', resetSourceModal);
  });

  const viewerButtons = Array.from(document.querySelectorAll('[data-library-viewer-open]'));
  const viewerModalElement = document.getElementById('tq-library-viewer');
  if (viewerButtons.length && viewerModalElement) {
    const viewerModal = bootstrap.Modal.getOrCreateInstance(viewerModalElement);
    const titleNode = viewerModalElement.querySelector('[data-viewer-title]');
    const kindNode = viewerModalElement.querySelector('[data-viewer-kind-label]');
    const countNode = viewerModalElement.querySelector('[data-viewer-count]');
    const prevButton = viewerModalElement.querySelector('[data-viewer-prev]');
    const nextButton = viewerModalElement.querySelector('[data-viewer-next]');
    const spinner = viewerModalElement.querySelector('[data-viewer-spinner]');
    const frame = viewerModalElement.querySelector('[data-viewer-frame]');
    const image = viewerModalElement.querySelector('[data-viewer-image]');
    const video = viewerModalElement.querySelector('[data-viewer-video]');
    const audio = viewerModalElement.querySelector('[data-viewer-audio]');
    const frameWrap = viewerModalElement.querySelector('[data-viewer-frame-wrap]');
    const zoomControls = viewerModalElement.querySelector('[data-viewer-zoom-controls]');
    const zoomLabel = viewerModalElement.querySelector('[data-viewer-zoom-label]');
    const externalPanel = viewerModalElement.querySelector('[data-viewer-external-panel]');
    const externalLink = viewerModalElement.querySelector('[data-viewer-external-link]');
    let currentIndex = 0;
    let zoom = 1;
    let currentKind = '';
    let pendingIndex = null;
    let loadTimer = null;

    const items = viewerButtons.map((button, index) => ({
      index,
      title: button.dataset.viewerTitle || 'Library source',
      kind: button.dataset.viewerKind || 'link',
      kindLabel: button.dataset.viewerKindLabel || 'Source',
      context: button.dataset.viewerContext || '',
      src: button.dataset.viewerSrc || '',
      externalUrl: button.dataset.viewerExternalUrl || '',
    }));

    const setZoom = (nextZoom) => {
      zoom = Math.max(.6, Math.min(2.4, nextZoom));
      if (image) {
        image.style.transform = currentKind === 'image' ? `scale(${zoom})` : '';
      }
      if (zoomLabel) {
        zoomLabel.textContent = `${Math.round(zoom * 100)}%`;
      }
    };

    const youtubeEmbedUrl = (src) => {
      try {
        const url = new URL(src, window.location.origin);
        url.searchParams.set('playsinline', '1');
        url.searchParams.set('rel', '0');
        url.searchParams.set('modestbranding', '1');
        url.searchParams.set('enablejsapi', '1');
        url.searchParams.set('origin', window.location.origin);

        return url.toString();
      } catch (error) {
        return src;
      }
    };

    const resetFrame = () => {
      if (loadTimer) {
        window.clearTimeout(loadTimer);
        loadTimer = null;
      }
      if (frame) {
        frame.src = 'about:blank';
        frame.classList.remove('d-none');
      }
      if (image) {
        image.src = '';
        image.alt = '';
        image.style.transform = '';
        image.classList.add('is-hidden');
      }
      if (video) {
        video.pause();
        video.removeAttribute('src');
        video.load();
        video.classList.add('is-hidden');
      }
      if (audio) {
        audio.pause();
        audio.removeAttribute('src');
        audio.load();
        audio.classList.add('is-hidden');
      }
      spinner?.classList.add('is-hidden');
    };

    const loadFrame = (item) => {
      if (!frame) {
        return;
      }

      spinner?.classList.remove('is-hidden');
      frame.title = item.title;
      frame.src = 'about:blank';
      frame.src = item.kind === 'youtube' ? youtubeEmbedUrl(item.src) : item.src;
    };

    const loadImage = (item) => {
      if (!image) {
        loadFrame(item);
        return;
      }

      spinner?.classList.remove('is-hidden');
      frame?.classList.add('d-none');
      image.alt = item.title;
      image.classList.remove('is-hidden');
      image.src = item.src;
    };

    const loadMedia = (item) => {
      const media = item.kind === 'audio' ? audio : video;
      if (!media) {
        loadFrame(item);
        return;
      }

      spinner?.classList.remove('is-hidden');
      frame?.classList.add('d-none');
      image?.classList.add('is-hidden');
      media.src = item.src;
      media.classList.remove('is-hidden');
      media.load();
    };

    const renderViewer = (index) => {
      const item = items[index];
      if (!item) {
        return;
      }

      currentIndex = index;
      currentKind = item.kind;
      resetFrame();
      setZoom(1);
      titleNode.textContent = item.title;
      kindNode.textContent = item.context || item.kindLabel;
      countNode.textContent = `${index + 1}/${items.length}`;
      prevButton.disabled = index === 0;
      nextButton.disabled = index === items.length - 1;
      zoomControls?.classList.toggle('is-visible', item.kind === 'image');
      frame?.classList.remove('is-document', 'is-youtube');
      frame?.classList.remove('d-none');
      image?.classList.add('is-hidden');
      video?.classList.add('is-hidden');
      audio?.classList.add('is-hidden');
      if (item.kind === 'pdf' || item.kind === 'file') {
        frame?.classList.add('is-document');
      } else if (item.kind === 'youtube') {
        frame?.classList.add('is-youtube');
      }
      externalPanel?.classList.remove('is-visible');
      frameWrap?.classList.remove('d-none');

      if (!item.src) {
        frameWrap?.classList.add('d-none');
        if (externalLink) {
          externalLink.href = item.externalUrl || '#';
        }
        externalPanel?.classList.add('is-visible');
        return;
      }

      if (item.kind === 'image') {
        loadImage(item);
        return;
      }

      if (item.kind === 'video' || item.kind === 'audio') {
        loadMedia(item);
        return;
      }

      loadFrame(item);
    };

    viewerButtons.forEach((button, index) => {
      button.addEventListener('click', () => {
        pendingIndex = index;
        if (viewerModalElement.classList.contains('show')) {
          renderViewer(index);
          pendingIndex = null;
          return;
        }
        viewerModal.show();
      });
    });

    prevButton?.addEventListener('click', () => renderViewer(currentIndex - 1));
    nextButton?.addEventListener('click', () => renderViewer(currentIndex + 1));
    viewerModalElement.addEventListener('shown.bs.modal', () => {
      if (pendingIndex !== null) {
        const index = pendingIndex;
        pendingIndex = null;
        renderViewer(index);
      }
    });
    viewerModalElement.addEventListener('hidden.bs.modal', () => {
      pendingIndex = null;
      resetFrame();
    });
    frame?.addEventListener('load', () => {
      if (frame.getAttribute('src') !== 'about:blank') {
        spinner?.classList.add('is-hidden');
      }
    });
    image?.addEventListener('load', () => {
      spinner?.classList.add('is-hidden');
    });
    image?.addEventListener('error', () => {
      spinner?.classList.add('is-hidden');
    });
    video?.addEventListener('loadedmetadata', () => {
      spinner?.classList.add('is-hidden');
    });
    video?.addEventListener('canplay', () => {
      spinner?.classList.add('is-hidden');
    });
    audio?.addEventListener('loadedmetadata', () => {
      spinner?.classList.add('is-hidden');
    });

    viewerModalElement.querySelectorAll('[data-viewer-zoom]').forEach((button) => {
      button.addEventListener('click', () => {
        if (button.dataset.viewerZoom === 'reset') {
          setZoom(1);
          return;
        }
        setZoom(zoom + (button.dataset.viewerZoom === '+' ? .15 : -.15));
      });
    });
  }

  const grid = document.querySelector('[data-library-grid]');
  const toggle = document.querySelector('[data-library-reorder-toggle]');
  if (grid && toggle) {
    let reorderOn = false;
    let dragging = null;

    const cards = () => Array.from(grid.querySelectorAll('[data-library-item-card]'));
    const setReorder = (enabled) => {
      reorderOn = enabled;
      grid.classList.toggle('tq-library-reorder-on', enabled);
      toggle.classList.toggle('btn-primary', enabled);
      toggle.classList.toggle('btn-outline-secondary', !enabled);
      toggle.innerHTML = enabled
        ? '<i class="ti tabler-device-floppy me-1"></i> Save order'
        : '<i class="ti tabler-arrows-sort me-1"></i> Reorder';
      cards().forEach((card) => {
        card.draggable = enabled;
        card.classList.toggle('is-sortable', enabled);
      });
    };

    const saveOrder = async () => {
      const items = cards()
        .map((card) => ({
          type: card.dataset.libraryItemType,
          id: Number(card.dataset.libraryItemId),
        }))
        .filter((item) => item.type && item.id > 0);
      const response = await fetch(grid.dataset.reorderUrl, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          folder_id: grid.dataset.folderId || null,
          items,
        }),
      });

      if (!response.ok) {
        throw new Error('Order failed');
      }
    };

    toggle.addEventListener('click', async () => {
      if (!reorderOn) {
        setReorder(true);
        return;
      }

      toggle.disabled = true;
      try {
        await saveOrder();
        window.location.reload();
      } catch (error) {
        toggle.disabled = false;
        alert('The source order could not be saved. Please try again.');
      }
    });

    grid.addEventListener('dragstart', (event) => {
      if (!reorderOn) {
        event.preventDefault();
        return;
      }
      dragging = event.target.closest('[data-library-item-card]');
      dragging?.classList.add('is-dragging');
    });

    grid.addEventListener('dragend', () => {
      dragging?.classList.remove('is-dragging');
      dragging = null;
    });

    grid.addEventListener('dragover', (event) => {
      if (!reorderOn || !dragging) {
        return;
      }
      event.preventDefault();
      const target = event.target.closest('[data-library-item-card]');
      if (!target || target === dragging) {
        return;
      }
      const box = target.getBoundingClientRect();
      const after = event.clientY > box.top + box.height / 2 || event.clientX > box.left + box.width / 2;
      grid.insertBefore(dragging, after ? target.nextSibling : target);
    });
  }
});
</script>
@endsection
