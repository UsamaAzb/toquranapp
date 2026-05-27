@extends('layouts/layoutMaster')

@section('title', $attachment->title ?? 'Attachment')

@section('content')
<style>
  .attachment-preview-fallback {
    min-height: 18rem;
    background: #24272b;
    color: #fff;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem;
  }

  .session-attachment-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
  }

  .session-attachment-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
  }

  .session-back-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    white-space: nowrap;
  }

  .session-attachment-image {
    max-width: 100%;
    width: auto;
    max-height: min(75vh, 900px);
    object-fit: contain;
  }

  .session-attachment-video {
    width: 100%;
    max-height: min(72vh, 820px);
    object-fit: contain;
    display: block;
    margin: 0 auto;
    background: #111827;
  }

  @media (max-width: 768px) {
    .session-attachment-header > div,
    .session-attachment-actions {
      width: 100%;
    }

    .session-back-button {
      width: 100%;
    }
  }
</style>

@php
  $resourceReturnTarget = $sessionUrl ?? null;
  $resourceOpenUrl = $fileUrl ?? null;
  $resourceOpenInNewTab = true;

  if (!empty($resourceOpenUrl) && !empty($resourceReturnTarget)) {
    $resourceUrlString = (string) $resourceOpenUrl;

    if (\Illuminate\Support\Str::startsWith($resourceUrlString, [url('/'), '/'])) {
      $separator = str_contains($resourceUrlString, '?') ? '&' : '?';
      $resourceOpenUrl = $resourceUrlString . $separator . http_build_query(['return_to' => $resourceReturnTarget]);
      $resourceOpenInNewTab = false;
    }
  }
@endphp

<div class="row justify-content-center">
  @include('layouts/progress_bar')
</div>

<div class="container-fluid py-3">
  <div class="session-attachment-header mb-3">
    <div>
      <h5 class="mb-0">
        <i class="ti tabler-paperclip"></i>
        {{ $attachment->title ?? 'Attachment' }}
      </h5>
    </div>
    <div class="session-attachment-actions">
      @if(!empty($sessionUrl))
        <a href="{{ $sessionUrl }}" class="btn btn-outline-primary session-back-button">
          <i class="ti tabler-arrow-left"></i>
          <span>Back to Session Tasks</span>
        </a>
      @endif
    </div>
  </div>

  @if(!($fileAvailable ?? true))
    <div class="alert alert-warning mb-0" role="alert">
      This file is currently unavailable. Your task is still visible, but the stored file could not be found.
    </div>
  @elseif(in_array($ext, ['jpg','jpeg','png','gif','webp','svg'], true))
    <button class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#imageModal">
      View Image
    </button>

    <div class="modal fade" id="imageModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $attachment->title }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center">
            <img src="{{ $fileUrl }}"
                 class="img-fluid session-attachment-image"
                 alt="">
          </div>
        </div>
      </div>
    </div>
  @elseif($type === 'youtube' && !empty($embedUrl))
    <div class="ratio ratio-16x9">
      <iframe src="{{ $embedUrl }}"
              allowfullscreen
              loading="lazy"></iframe>
    </div>
  @elseif($type === 'youtube' && !empty($fileUrl))
    <a href="{{ $resourceOpenUrl }}" @if($resourceOpenInNewTab) target="_blank" rel="noopener noreferrer" @endif class="btn btn-danger">
      Open Video
    </a>
  @elseif($type === 'youtube')
    <div class="alert alert-warning mb-0" role="alert">
      This video link is unavailable.
    </div>
  @elseif($type === 'link' && !empty($fileUrl))
    <a href="{{ $resourceOpenUrl }}" @if($resourceOpenInNewTab) target="_blank" rel="noopener noreferrer" @endif class="btn btn-success">
      Open Link
    </a>
  @elseif($type === 'link')
    <div class="alert alert-warning mb-0" role="alert">
      This attachment link is unavailable.
    </div>
  @elseif(in_array($ext, ['doc','docx','ppt','pptx','xls','xlsx'], true))
    <div class="attachment-preview-fallback">
      <div>
        <h6 class="text-white mb-2">Preview unavailable for this file</h6>
        <p class="text-white-50 mb-4">
          This document can be opened or downloaded here.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
          <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-light">
            Open File
          </a>
          @if($downloadUrl)
            <a href="{{ $downloadUrl }}" class="btn btn-primary">
              Download File
            </a>
          @endif
        </div>
      </div>
    </div>
  @elseif($ext === 'pdf')
    <iframe
      src="{{ $fileUrl }}#page=1&view=FitH&toolbar=1&navpanes=0&scrollbar=1"
      style="width:100%; height:90vh; border:0">
    </iframe>
  @elseif(in_array($ext, ['mp4','webm','ogg','mov','m4v'], true))
    @php
      $videoMimeType = match($ext) {
        'webm' => 'video/webm',
        'ogg' => 'video/ogg',
        'mov' => 'video/quicktime',
        'm4v' => 'video/x-m4v',
        default => 'video/mp4',
      };
    @endphp
    <video controls playsinline preload="metadata" class="session-attachment-video">
      <source src="{{ $fileUrl }}" type="{{ $videoMimeType }}">
    </video>
  @else
    <a href="{{ $downloadUrl ?? $fileUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
      Download File
    </a>
  @endif
</div>
@endsection
