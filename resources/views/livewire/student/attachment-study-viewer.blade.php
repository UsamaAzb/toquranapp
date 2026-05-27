<div>
  @if($open)
    @php
      $mode = $this->currentItemMode;
      $media = $this->currentItemMedia;
      $isImage = $media['image'];
      $isVideo = $media['video'];
      $isAudio = $media['audio'];
      $isPdf = $media['pdf'];
      $viewerItemKey = $this->currentItemViewerKey;
    @endphp

    <div
      class="attachment-study-viewer"
      role="dialog"
      aria-modal="true"
      aria-label="Attachment viewer"
      x-data
      x-init="document.body.classList.add('attachment-study-viewer-open'); $el._cleanup = () => document.body.classList.remove('attachment-study-viewer-open')"
      x-on:keydown.escape.window="$wire.closeViewer()"
      x-on:keydown.arrow-left.window="$wire.previousAttachment()"
      x-on:keydown.arrow-right.window="$wire.nextAttachment()">
      <div class="attachment-study-bar">
        <div class="attachment-study-title-wrap">
          <span class="attachment-study-task">{{ $taskTitle }}</span>
          <h2 class="attachment-study-title">{{ $currentItem['title'] ?? 'Attachment' }}</h2>
        </div>

        <div class="attachment-study-actions">
          <button type="button" class="attachment-study-icon-btn" wire:click="previousAttachment" @disabled($this->isFirstAttachment) aria-label="Previous attachment">
            <i class="ti tabler-chevron-left"></i>
          </button>
          <span class="attachment-study-count">{{ $currentIndex + 1 }}/{{ count($items) }}</span>
          <button type="button" class="attachment-study-icon-btn" wire:click="nextAttachment" @disabled($this->isLastAttachment) aria-label="Next attachment">
            <i class="ti tabler-chevron-right"></i>
          </button>
          <button type="button" class="attachment-study-close" wire:click="closeViewer" aria-label="Close attachment viewer">
            <i class="ti tabler-x"></i>
          </button>
        </div>
      </div>

      <div
        class="attachment-study-body"
        x-data="{ frameLoading: true, slowFrame: false, slowTimer: null }"
        x-effect="
          '{{ $viewerItemKey }}';
          frameLoading = true;
          slowFrame = false;
          clearTimeout(slowTimer);
          slowTimer = setTimeout(() => slowFrame = true, 3500);
        "
        x-on:attachment-study-frame-loaded="
          frameLoading = false;
          slowFrame = false;
          clearTimeout(slowTimer);
        ">
        @if(in_array($mode, ['protected_file', 'legacy_file'], true))
          @if($isImage)
            <div
              wire:key="{{ $viewerItemKey }}-image-shell"
              class="attachment-study-image-shell"
              x-data="{ zoom: 1 }"
              x-effect="'{{ $viewerItemKey }}'; zoom = 1"
              x-on:wheel.ctrl.prevent="zoom = Math.min(4, Math.max(0.5, zoom + ($event.deltaY < 0 ? 0.25 : -0.25)))">
              <div class="attachment-study-zoom-controls" aria-label="Image zoom controls">
                <button type="button" class="attachment-study-mini-btn" x-on:click="zoom = Math.max(0.5, zoom - 0.25)" aria-label="Zoom out">
                  <i class="ti tabler-minus"></i>
                </button>
                <span class="attachment-study-zoom-level" x-text="`${Math.round(zoom * 100)}%`">100%</span>
                <button type="button" class="attachment-study-mini-btn" x-on:click="zoom = Math.min(4, zoom + 0.25)" aria-label="Zoom in">
                  <i class="ti tabler-plus"></i>
                </button>
                <button type="button" class="attachment-study-mini-btn" x-on:click="zoom = 1" aria-label="Reset zoom">
                  <i class="ti tabler-refresh"></i>
                </button>
              </div>
              <div class="attachment-study-image-pan">
                <img
                  class="attachment-study-image"
                  src="{{ $currentItem['content_url'] }}"
                  alt="{{ $currentItem['title'] ?? 'Attachment' }}"
                  x-bind:style="`transform: scale(${zoom});`">
              </div>
            </div>
          @elseif($isVideo)
            <div
              wire:key="{{ $viewerItemKey }}-video-shell"
              class="attachment-study-video-shell"
              x-data="{ videoLoading: true, slowVideo: false, videoTimer: null }"
              x-effect="
                '{{ $viewerItemKey }}';
                videoLoading = true;
                slowVideo = false;
                clearTimeout(videoTimer);
                videoTimer = setTimeout(() => slowVideo = true, 2500);
                $nextTick(() => {
                  const video = $refs.studyVideo;
                  if (video) {
                    try {
                      video.pause();
                      video.load();
                    } catch (error) {
                      slowVideo = true;
                    }
                  }
                });
              "
              x-on:attachment-study-video-ready="
                videoLoading = false;
                slowVideo = false;
                clearTimeout(videoTimer);
              ">
              <div
                x-cloak
                x-show="videoLoading"
                class="attachment-study-loading attachment-study-loading--media"
                role="status"
                aria-live="polite">
                <span class="attachment-study-spinner" aria-hidden="true"></span>
                <span x-text="slowVideo ? 'Preparing video...' : 'Loading video...'"></span>
              </div>
              <video
                x-ref="studyVideo"
                class="attachment-study-video"
                controls
                playsinline
                preload="metadata"
                src="{{ $currentItem['content_url'] }}"
                x-on:loadedmetadata="$dispatch('attachment-study-video-ready')"
                x-on:canplay="$dispatch('attachment-study-video-ready')"
                x-on:error="slowVideo = true"></video>
            </div>
          @elseif($isAudio)
            <div wire:key="{{ $viewerItemKey }}-audio" class="attachment-study-card">
              <i class="ti tabler-music attachment-study-card-icon"></i>
              <h3>{{ $currentItem['title'] ?? 'Audio attachment' }}</h3>
              <audio controls preload="metadata" src="{{ $currentItem['content_url'] }}"></audio>
            </div>
          @elseif($isPdf)
            <iframe wire:key="{{ $viewerItemKey }}-pdf" class="attachment-study-frame" src="{{ $currentItem['content_url'] }}" title="{{ $currentItem['title'] ?? 'Attachment' }}"></iframe>
          @else
            <div wire:key="{{ $viewerItemKey }}-download" class="attachment-study-card">
              <i class="ti tabler-file-description attachment-study-card-icon"></i>
              <h3>{{ $currentItem['title'] ?? 'Attachment' }}</h3>
              <p>{{ __('Preview is not available for this file type.') }}</p>
              @if(!empty($currentItem['download_url']))
                <a class="btn btn-primary" href="{{ $currentItem['download_url'] }}">{{ __('Download') }}</a>
              @endif
            </div>
          @endif
        @elseif($mode === 'youtube')
          <iframe
            wire:key="{{ $viewerItemKey }}-youtube"
            class="attachment-study-frame"
            src="{{ $currentItem['embed_url'] }}"
            title="{{ $currentItem['title'] ?? 'Video attachment' }}"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            x-on:load="$dispatch('attachment-study-frame-loaded')"
            allowfullscreen></iframe>
        @elseif($mode === 'legacy_same_origin_link')
          {{-- Transitional exception: old same-origin Library wrappers need scripts, cookies, and same-origin access to load.
              The presenter only reaches this mode after same-origin classification; see specs/2027-library-task-integration/legacy-attachment-allowlist.md before tightening this sandbox. --}}
          <div
            x-cloak
            x-show="frameLoading"
            class="attachment-study-loading"
            role="status"
            aria-live="polite">
            <span class="attachment-study-spinner" aria-hidden="true"></span>
            <span x-text="slowFrame ? 'Still loading...' : 'Loading...'"></span>
          </div>
          <iframe
            wire:key="{{ $viewerItemKey }}-legacy"
            class="attachment-study-frame"
            src="{{ $currentItem['open_url'] }}"
            title="{{ $currentItem['title'] ?? 'Library attachment' }}"
            x-on:load="$dispatch('attachment-study-frame-loaded')"
            sandbox="allow-same-origin allow-forms allow-scripts allow-popups allow-downloads"></iframe>
        @elseif($mode === 'external_link')
          <div wire:key="{{ $viewerItemKey }}-external" class="attachment-study-card">
            <i class="ti tabler-link attachment-study-card-icon"></i>
            <h3>{{ $currentItem['title'] ?? 'External link' }}</h3>
            @if(!empty($currentItem['hostname']))
              <p class="attachment-study-host">{{ $currentItem['hostname'] }}</p>
            @endif
            <a
              class="btn btn-primary"
              href="{{ $currentItem['open_url'] }}"
              target="_blank"
              rel="noopener noreferrer">
              {{ __('Open link') }}
            </a>
          </div>
        @else
          <div wire:key="{{ $viewerItemKey }}-unavailable" class="attachment-study-card">
            <i class="ti tabler-link-off attachment-study-card-icon"></i>
            <h3>{{ $currentItem['title'] ?? 'Attachment unavailable' }}</h3>
            <p>{{ $currentItem['unavailable_reason'] ?? __('This attachment is not available.') }}</p>
          </div>
        @endif
      </div>
    </div>
  @endif

  @once
    @push('styles')
      <style>
        body.attachment-study-viewer-open {
          overflow: hidden !important;
        }

        .attachment-study-viewer {
          position: fixed;
          inset: 0;
          z-index: 1095;
          display: flex;
          flex-direction: column;
          background: #f8fafc;
          color: #2f3349;
          overflow: hidden;
        }

        .attachment-study-bar {
          min-height: 56px;
          display: grid;
          grid-template-columns: minmax(0, 1fr) auto;
          align-items: center;
          gap: 12px;
          padding: 7px 12px;
          border-bottom: 1px solid rgba(47, 51, 73, 0.12);
          background: #fff;
        }

        .attachment-study-title-wrap {
          min-width: 0;
        }

        .attachment-study-task {
          display: block;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
          font-size: 11px;
          line-height: 1.2;
          color: #6d7081;
        }

        .attachment-study-title {
          margin: 2px 0 0;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
          font-size: 17px;
          line-height: 1.25;
          font-weight: 700;
        }

        .attachment-study-actions {
          display: flex;
          align-items: center;
          gap: 8px;
          flex-shrink: 0;
        }

        .attachment-study-icon-btn,
        .attachment-study-close {
          width: 40px;
          height: 40px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border: 1px solid rgba(47, 51, 73, 0.14);
          border-radius: 8px;
          background: #fff;
          color: #2f3349;
        }

        .attachment-study-icon-btn:disabled {
          opacity: 0.35;
          cursor: not-allowed;
        }

        .attachment-study-close {
          color: #0f67f5;
        }

        .attachment-study-count {
          min-width: 42px;
          text-align: center;
          font-size: 13px;
          font-weight: 700;
          color: #6d7081;
        }

        .attachment-study-body {
          position: relative;
          flex: 1;
          min-height: 0;
          display: flex;
          align-items: stretch;
          justify-content: center;
          padding: 8px;
          overflow: hidden;
        }

        .attachment-study-loading {
          position: absolute;
          inset: 8px;
          z-index: 2;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          gap: 10px;
          border-radius: 8px;
          background: rgba(248, 250, 252, 0.92);
          color: #2f3349;
          font-weight: 700;
        }

        .attachment-study-spinner {
          width: 34px;
          height: 34px;
          border: 3px solid rgba(15, 103, 245, 0.18);
          border-top-color: #0f67f5;
          border-radius: 50%;
          animation: attachmentStudySpin 0.8s linear infinite;
        }

        @keyframes attachmentStudySpin {
          to {
            transform: rotate(360deg);
          }
        }

        .attachment-study-frame,
        .attachment-study-video {
          width: 100%;
          height: 100%;
          border: 0;
          border-radius: 8px;
          background: #fff;
          overscroll-behavior: contain;
        }

        .attachment-study-video-shell {
          position: relative;
          flex: 1;
          min-width: 0;
          min-height: 0;
          display: flex;
        }

        .attachment-study-loading--media {
          pointer-events: none;
        }

        .attachment-study-image {
          max-width: 100%;
          max-height: 100%;
          object-fit: contain;
          margin: auto;
          transform-origin: center center;
          transition: transform 0.12s ease;
          user-select: none;
        }

        .attachment-study-image-shell {
          position: relative;
          flex: 1;
          min-width: 0;
          min-height: 0;
          display: flex;
        }

        .attachment-study-image-pan {
          width: 100%;
          height: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
          overflow: auto;
          overscroll-behavior: contain;
          padding: 16px;
        }

        .attachment-study-zoom-controls {
          position: absolute;
          top: 12px;
          right: 12px;
          z-index: 3;
          display: inline-flex;
          align-items: center;
          gap: 6px;
          padding: 6px;
          border: 1px solid rgba(47, 51, 73, 0.12);
          border-radius: 8px;
          background: rgba(255, 255, 255, 0.94);
          box-shadow: 0 8px 24px rgba(47, 51, 73, 0.12);
        }

        .attachment-study-mini-btn {
          width: 30px;
          height: 30px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border: 1px solid rgba(47, 51, 73, 0.14);
          border-radius: 6px;
          background: #fff;
          color: #2f3349;
        }

        .attachment-study-zoom-level {
          min-width: 44px;
          text-align: center;
          font-size: 12px;
          font-weight: 700;
          color: #6d7081;
        }

        .attachment-study-card {
          width: min(520px, 100%);
          margin: auto;
          padding: 24px;
          border: 1px solid rgba(47, 51, 73, 0.12);
          border-radius: 8px;
          background: #fff;
          text-align: center;
          box-shadow: 0 10px 30px rgba(47, 51, 73, 0.08);
        }

        .attachment-study-card h3 {
          margin: 10px 0 8px;
          font-size: 20px;
          font-weight: 700;
          overflow-wrap: anywhere;
        }

        .attachment-study-card p {
          color: #6d7081;
        }

        .attachment-study-card audio {
          width: 100%;
          margin-top: 12px;
        }

        .attachment-study-card-icon {
          font-size: 36px;
          color: #0f67f5;
        }

        .attachment-study-host {
          word-break: break-word;
        }

        @media (max-width: 575.98px) {
          .attachment-study-bar {
            min-height: 56px;
            gap: 8px;
            padding: 7px 8px;
          }

          .attachment-study-task {
            font-size: 10px;
          }

          .attachment-study-title {
            font-size: 15px;
          }

          .attachment-study-actions {
            gap: 5px;
          }

          .attachment-study-body {
            padding: 5px;
          }

          .attachment-study-icon-btn,
          .attachment-study-close {
            width: 38px;
            height: 38px;
          }

          .attachment-study-count {
            min-width: 34px;
            font-size: 12px;
          }

          .attachment-study-card {
            padding: 18px;
          }
        }

        @media (max-width: 359.98px) {
          .attachment-study-bar {
            gap: 6px;
          }

          .attachment-study-icon-btn,
          .attachment-study-close {
            width: 36px;
            height: 36px;
          }

          .attachment-study-count {
            min-width: 31px;
          }
        }
      </style>
    @endpush
    @push('scripts')
      <script>
        document.addEventListener('livewire:init', function () {
          if (window.w14AttachmentStudyViewerCleanupBound) return;
          window.w14AttachmentStudyViewerCleanupBound = true;

          Livewire.on('attachment-study-viewer-closed', function () {
            document.body.classList.remove('attachment-study-viewer-open');
          });
        });
      </script>
    @endpush
  @endonce
</div>
