@extends('layouts/layoutMaster')

@section('title', $attachment->title ?? 'Attachment')

@section('content')
<style>
  .teacher-attachment-shell {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .teacher-attachment-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
  }

  .teacher-attachment-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
  }

  .teacher-back-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    white-space: nowrap;
  }

  .teacher-attachment-frame {
    border-radius: 1rem;
    overflow: hidden;
  }

  .teacher-attachment-frame iframe {
    width: 100%;
    border: 0;
  }

  .teacher-attachment-fallback {
    min-height: 18rem;
    background: #24272b;
    color: #fff;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem;
  }

  .teacher-attachment-image {
    max-width: 100%;
    width: auto;
    max-height: min(75vh, 900px);
    object-fit: contain;
  }

  .teacher-attachment-video {
    width: 100%;
    max-height: min(72vh, 820px);
    object-fit: contain;
    display: block;
    margin: 0 auto;
    background: #111827;
  }

  @media (max-width: 768px) {
    .teacher-attachment-header > div,
    .teacher-attachment-actions {
      width: 100%;
    }

    .teacher-back-button {
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

<div class="container-fluid py-3">
  <div class="teacher-attachment-shell">
    <div class="teacher-attachment-header">
      <div>
        <h5 class="mb-0">
          <i class="ti tabler-paperclip"></i>
          {{ $attachment->title ?? 'Attachment' }}
        </h5>
      </div>

      <div class="teacher-attachment-actions">
        @if(!empty($sessionUrl))
          <a href="{{ $sessionUrl }}" class="btn btn-outline-primary teacher-back-button">
            <i class="ti tabler-arrow-left"></i>
            <span>Back to Session Tasks</span>
          </a>
        @endif
      </div>
    </div>

    @if(!($fileAvailable ?? true))
      <div class="alert alert-warning mb-0" role="alert">
        This file is currently unavailable. The task is still safe to open, but the stored file could not be found.
      </div>
    @elseif(in_array($ext, ['jpg','jpeg','png','gif','webp','svg'], true))
      <div class="teacher-attachment-frame text-center">
        <img src="{{ $fileUrl }}" class="img-fluid teacher-attachment-image" alt="{{ $attachment->title ?? 'Attachment' }}">
      </div>
    @elseif($type === 'youtube' && !empty($embedUrl))
      <div class="teacher-attachment-frame">
        <div class="ratio ratio-16x9">
          <iframe src="{{ $embedUrl }}" allowfullscreen loading="lazy"
            sandbox="allow-scripts allow-same-origin allow-presentation"></iframe>
        </div>
      </div>
    @elseif($type === 'youtube')
      <a href="{{ $resourceOpenUrl }}" @if($resourceOpenInNewTab) target="_blank" rel="noopener noreferrer" @endif class="btn btn-danger align-self-start">
        Open Video
      </a>
    @elseif($type === 'link')
      <a href="{{ $resourceOpenUrl }}" @if($resourceOpenInNewTab) target="_blank" rel="noopener noreferrer" @endif class="btn btn-success align-self-start">
        Open Link
      </a>
    @elseif(in_array($ext, ['doc','docx','ppt','pptx','xls','xlsx'], true))
      <div class="teacher-attachment-fallback">
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
      <div class="teacher-attachment-frame">
        <iframe src="{{ $fileUrl }}#page=1&view=FitH&toolbar=1&navpanes=0&scrollbar=1" style="height:90vh;"></iframe>
      </div>
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
      <div class="teacher-attachment-frame">
        <video controls playsinline preload="metadata" class="teacher-attachment-video">
          <source src="{{ $fileUrl }}" type="{{ $videoMimeType }}">
        </video>
      </div>
    @else
      <a href="{{ $downloadUrl ?? $fileUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary align-self-start">
        Download File
      </a>
    @endif
  </div>
</div>
@endsection
