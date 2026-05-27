@extends('layouts/layoutMaster')

@section('title', $resource->title ?? 'Library File')

@section('content')
<style>
  .library-file-shell {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .library-file-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
  }

  .library-file-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
  }

  .library-file-frame {
    overflow: hidden;
    border: 1px solid var(--bs-border-color);
    border-radius: 0.75rem;
    background: var(--bs-paper-bg);
  }

  .library-file-frame iframe {
    width: 100%;
    min-height: 78vh;
    border: 0;
  }

  .library-file-image {
    max-width: 100%;
    width: auto;
    max-height: min(76vh, 900px);
    object-fit: contain;
  }

  .library-file-video {
    width: 100%;
    max-height: min(76vh, 900px);
    object-fit: contain;
    display: block;
    margin: 0 auto;
    background: #111827;
  }

  .library-file-fallback {
    min-height: 18rem;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem;
    background: #24272b;
    color: #fff;
  }

  @media (max-width: 768px) {
    .library-file-header > div,
    .library-file-actions {
      width: 100%;
    }

    .library-file-actions .btn {
      flex: 1 1 auto;
    }
  }
</style>

<div class="library-file-shell">
  <div class="library-file-header">
    <div>
      <span class="badge bg-label-info mb-2">Library file</span>
      <h5 class="mb-1">{{ $resource->title }}</h5>
      @if($resource->original_filename)
        <div class="text-body-secondary">{{ $resource->original_filename }}</div>
      @endif
    </div>

    <div class="library-file-actions">
      <a href="{{ $folderUrl }}" class="btn btn-sm btn-outline-secondary">Back to folder</a>
      @if($fileAvailable)
        <a href="{{ $downloadUrl }}" class="btn btn-sm btn-outline-primary">Download</a>
      @endif
    </div>
  </div>

  @if(! $fileAvailable)
    <div class="alert alert-warning mb-0" role="alert">
      This Library file is currently unavailable. The Library record is still safe, but the stored file could not be found.
    </div>
  @elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true))
    <div class="library-file-frame text-center p-3">
      <img src="{{ $fileUrl }}" class="library-file-image" alt="{{ $resource->title }}">
    </div>
  @elseif($ext === 'pdf')
    <div class="library-file-frame">
      <iframe src="{{ $fileUrl }}#page=1&view=FitH&toolbar=1&navpanes=0&scrollbar=1"></iframe>
    </div>
  @elseif(in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'm4v'], true))
    @php
      $videoMimeType = match($ext) {
        'webm' => 'video/webm',
        'ogg' => 'video/ogg',
        'mov' => 'video/quicktime',
        'm4v' => 'video/x-m4v',
        default => 'video/mp4',
      };
    @endphp
    <div class="library-file-frame">
      <video controls playsinline preload="metadata" class="library-file-video">
        <source src="{{ $fileUrl }}" type="{{ $videoMimeType }}">
      </video>
    </div>
  @else
    <div class="library-file-frame">
      <div class="library-file-fallback">
        <div>
          <h6 class="text-white mb-2">Preview unavailable for this file</h6>
          <p class="text-white-50 mb-4">This file can be downloaded from the Library.</p>
          <a href="{{ $downloadUrl }}" class="btn btn-primary">Download file</a>
        </div>
      </div>
    </div>
  @endif
</div>
@endsection
