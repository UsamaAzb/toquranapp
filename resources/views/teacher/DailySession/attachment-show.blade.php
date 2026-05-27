@extends('layouts/layoutMaster')

@section('title', $attachment->title ?? 'Attachment')
@section('meta_description', 'Preview this protected daily session attachment.')

@section('content')
<style>
  .teacher-daily-attachment-shell {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .teacher-daily-attachment-header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
  }

  .teacher-daily-attachment-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
  }

  .teacher-daily-back-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    white-space: nowrap;
  }

  .teacher-daily-attachment-frame {
    border-radius: 1rem;
    overflow: hidden;
  }

  .teacher-daily-attachment-frame iframe {
    width: 100%;
    border: 0;
  }

  .teacher-daily-attachment-fallback {
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

  .teacher-daily-attachment-image {
    max-width: 100%;
    width: auto;
    max-height: min(75vh, 900px);
    object-fit: contain;
  }

  .teacher-daily-attachment-video {
    width: 100%;
    max-height: min(72vh, 820px);
    object-fit: contain;
    display: block;
    margin: 0 auto;
    background: #111827;
  }

  @media (max-width: 768px) {
    .teacher-daily-attachment-header > div,
    .teacher-daily-attachment-actions {
      width: 100%;
    }

    .teacher-daily-back-button {
      width: 100%;
    }
  }
</style>

<div class="container-fluid py-3">
  <div class="teacher-daily-attachment-shell">
    <div class="teacher-daily-attachment-header">
      <div>
        <h5 class="mb-0">
          <i class="ti tabler-paperclip"></i>
          {{ $attachment->title ?? 'Attachment' }}
        </h5>
      </div>

      <div class="teacher-daily-attachment-actions">
        @if(!empty($sessionUrl))
          <a href="{{ $sessionUrl }}" class="btn btn-outline-primary teacher-daily-back-button">
            <i class="ti tabler-arrow-left"></i>
            <span>{{ $backButtonLabel ?? 'Back to Daily Session Tasks' }}</span>
          </a>
        @endif
      </div>
    </div>

    @if(in_array($ext, ['jpg','jpeg','png','gif','webp','svg'], true))
      <div class="teacher-daily-attachment-frame text-center">
        <img src="{{ $fileUrl }}" class="img-fluid teacher-daily-attachment-image" alt="{{ $attachment->title ?? 'Attachment' }}">
      </div>
    @elseif($type === 'youtube' && !empty($embedUrl))
      <div class="teacher-daily-attachment-frame">
        <div class="ratio ratio-16x9">
          <iframe src="{{ $embedUrl }}" allowfullscreen loading="lazy"></iframe>
        </div>
      </div>
    @elseif($type === 'youtube')
      <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-danger align-self-start">
        Open Video
      </a>
    @elseif($type === 'link')
      <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-success align-self-start">
        Open Link
      </a>
    @elseif(in_array($ext, ['doc','docx','ppt','pptx','xls','xlsx'], true))
      <div class="teacher-daily-attachment-fallback">
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
      <div class="teacher-daily-attachment-frame">
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
      <div class="teacher-daily-attachment-frame">
        <video controls playsinline preload="metadata" class="teacher-daily-attachment-video">
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
