@php($isFooter = false)
@extends('layouts/layoutMaster')
@section('title', $attachment->title ?? 'Attachment')
@push('styles')
  <link rel="preload" as="image" href="{{ $bgUrl ?? asset('images/journey/background34.webp') }}">
<style>
  .layout-wrapper {
    position: relative;
    min-height: 100vh;
  }

  .layout-wrapper::before {
    content: "";
    position: fixed;
    inset: 0;
    background: url('{{ $bgUrl ?? asset('images/journey/background34.webp') }}') no-repeat center center;
    background-size: cover;
    z-index: -1;
  }

  .layout-page,
  .content-wrapper {
    background: transparent;
  }

  .content-wrapper > .container-fluid.flex-grow-1.container-p-y,
  .content-wrapper > .container-xxl.flex-grow-1.container-p-y {
    background: transparent;
    max-width: none;
    padding-top: 0;
  }

  .background-container {
    min-height: calc(100vh - 5rem);
    display: flex;
    justify-content: center;
    align-items: flex-start;
    width: 100%;
    padding: 1.5rem 1rem 2rem;
  }

  .journey-attachment-shell {
    width: min(1080px, 100%);
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .weekly-progress {
    position: static;
    align-self: flex-end;
    background: rgba(255, 255, 255, 0.92);
    border-radius: 12px;
    padding: 18px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    font-size: 0.9rem;
    font-weight: 600;
  }

  .island-progress {
    width: clamp(80px, 18vw, 140px);
    height: 6px;
  }

  .inside-island-progress {
    height: 7px;
  }

  .journey-attachment-panel {
    width: min(1080px, 100%);
    background: rgba(255,255,255,.78);
    backdrop-filter: blur(6px);
    border-radius: 1.5rem;
    padding: 1.25rem;
    border: 1px solid rgba(255,255,255,.42);
    box-shadow: 0 20px 40px rgba(0,0,0,.12);
  }

  .journey-attachment-title {
    font-weight: 700;
    color: #333;
    line-height: 1.3;
    font-size: 1.2rem;
  }

  .journey-attachment-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
  }

  .journey-back-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    white-space: nowrap;
  }

  .journey-attachment-frame {
    border-radius: 1rem;
    overflow: hidden;
  }

  .journey-attachment-frame iframe {
    width: 100%;
    border: 0;
  }

  .journey-attachment-pdf-frame {
    height: min(90vh, 1100px);
  }

  .journey-attachment-video-frame {
    background: rgba(17, 24, 39, 0.92);
  }

  .journey-attachment-video {
    width: 100%;
    max-height: min(72vh, 820px);
    background: #111827;
    object-fit: contain;
    display: block;
    margin: 0 auto;
  }

  .journey-attachment-image {
    max-width: 100%;
    width: auto;
    max-height: min(75vh, 900px);
    object-fit: contain;
  }

  .journey-attachment-fallback {
    min-height: 18rem;
    background: rgba(33, 37, 41, 0.88);
    color: #fff;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem;
  }

  @media (max-width: 992px) {
    .background-container {
      align-items: stretch;
      padding-top: 1rem;
    }

    .weekly-progress {
      align-self: stretch;
      padding: 14px;
    }
  }

  @media (max-width: 768px) {
    .journey-attachment-actions {
      width: 100%;
    }

    .journey-back-button {
      width: 100%;
    }
  }

  @media (max-width: 500px) {
    .background-container {
      padding-top: 5.5rem;
      padding-left: 0.5rem;
      padding-right: 0.5rem;
    }

    .journey-attachment-panel {
      padding: 0.875rem;
      border-radius: 1.25rem;
    }

    .journey-attachment-title {
      font-size: 1.1rem;
    }
  }

  @media (max-width: 380px) {
    .journey-attachment-title {
      font-size: 1rem;
    }
  }


</style>
@endpush
@section('content')
<div class="row justify-content-center">
  @include('layouts/progress_bar')
</div>
<div class="background-container">
  <div class="journey-attachment-shell">
    <div class="weekly-progress card shadow-sm d-flex align-items-center gap-2 mt-1">
      <small class="fw-medium">
        {{ $completedCount }}/{{ $totalCount }} islands done
      </small>

      <div class="progress island-progress">
        <div class="progress-bar bg-success inside-island-progress"
             role="progressbar"
             style="width: {{ $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0 }}%">
        </div>
      </div>
    </div>

    <div class="journey-attachment-panel">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
          @if($type === 'youtube' || $type === 'link')
            <span class="badge bg-label-{{ $type === 'youtube' ? 'danger' : 'success' }} mb-2">
              {{ $type === 'youtube' ? 'YouTube' : 'Link' }}
            </span>
          @endif
          <h5 class="mb-0 journey-attachment-title">{{ $attachment->title ?? 'Attachment' }}</h5>
        </div>
        <div class="journey-attachment-actions">
          @if(!empty($taskUrl))
            <a href="{{ $taskUrl }}" class="btn btn-outline-primary journey-back-button">
              <i class="ti tabler-arrow-left"></i>
              <span>Back to Task</span>
            </a>
          @endif
        </div>
      </div>

      {{-- IMAGE --}}
      @if(in_array($ext, ['jpg','jpeg','png','gif','webp','svg']))
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
                     class="img-fluid journey-attachment-image"
                     alt="{{ $attachment->title ?? 'Attachment image' }}">
              </div>
            </div>
          </div>
        </div>

      {{-- YOUTUBE --}}
      @elseif($type === 'youtube' && !empty($embedUrl))
        <div class="journey-attachment-frame">
          <div class="ratio ratio-16x9">
            <iframe src="{{ $embedUrl }}"
                    allowfullscreen
                    loading="lazy"></iframe>
          </div>
        </div>
      @elseif($type === 'youtube' && !empty($fileUrl))
        <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-danger">
          Open Video
        </a>
      @elseif($type === 'youtube')
        <div class="alert alert-warning mb-0" role="alert">
          This video link is unavailable.
        </div>

      {{-- EXTERNAL LINK --}}
      @elseif($type === 'link' && !empty($fileUrl))
        <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-success">
          Open Link
        </a>
      @elseif($type === 'link')
        <div class="alert alert-warning mb-0" role="alert">
          This attachment link is unavailable.
        </div>

      {{-- OFFICE FILES --}}
      @elseif(in_array($ext, ['doc','docx','ppt','pptx','xls','xlsx']))
        @if($canEmbedOfficePreview)
          <div class="journey-attachment-frame d-block">
            <iframe
              src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($fileUrl) }}"
              style="width:100%; height:90vh; border:0">
            </iframe>
          </div>
        @else
          <div class="journey-attachment-frame journey-attachment-fallback">
            <div>
              <h6 class="text-white mb-2">Preview unavailable for this file</h6>
              <p class="text-white-50 mb-4">
                This document preview needs a public file URL. You can still open or download it here.
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
        @endif

      @elseif($ext == 'pdf')
        <div class="journey-attachment-frame journey-attachment-pdf-frame iframe_pdf">
          <iframe
            src="{{ $fileUrl }}#page=1&view=FitH&toolbar=1&navpanes=0&scrollbar=1"
            class="journey-attachment-pdf-frame">
          </iframe>
        </div>

      {{-- VIDEO --}}
      @elseif(in_array($ext, ['mp4','webm','ogg','mov','m4v']))
        <div class="journey-attachment-frame journey-attachment-video-frame">
          <video controls playsinline preload="metadata" class="journey-attachment-video">
            <source src="{{ $fileUrl }}" @if($fileMimeType) type="{{ $fileMimeType }}" @endif>
          </video>
        </div>

      {{-- OTHER --}}
      @else
        <a href="{{ $downloadUrl ?? $fileUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
          Download File
        </a>
      @endif

    </div>
  </div>
</div>
@endsection
