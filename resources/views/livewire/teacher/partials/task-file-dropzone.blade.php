@php
    $resetKey = $resetKey ?? '';
    $pendingReorderEnabled = (bool) ($pendingReorderEnabled ?? false);
    $useUnifiedAttachmentTray = (bool) ($useUnifiedAttachmentTray ?? false);
    $draftReorderEnabled = (bool) ($draftReorderEnabled ?? true);
    $draftAttachmentItems = $draftAttachmentItems ?? [];
    $libraryPickerEnabled = (bool) ($libraryPickerEnabled ?? false);
    $showExternalAttachmentRows = (bool) ($showExternalAttachmentRows ?? false);
@endphp

@once
  <style>
    .w14-task-attachment-modal .modal-dialog.modal-dialog-scrollable .modal-content {
      max-height: calc(100vh - 3.5rem);
      min-width: 0;
      overflow: hidden;
    }

    .w14-task-attachment-modal .modal-dialog.modal-dialog-scrollable .modal-body {
      overflow-x: hidden;
      overflow-y: auto;
      overscroll-behavior: contain;
    }

    .w14-attachment-panel {
      display: grid;
      gap: 0.85rem;
      min-width: 0;
    }

    .w14-attachment-input {
      border: 1px dashed color-mix(in sRGB, var(--bs-border-color) 82%, var(--bs-primary));
      border-radius: 0.85rem;
      background: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 94%, var(--bs-primary));
      min-width: 0;
      padding: 1rem;
    }

    .w14-attachment-input .form-control {
      max-width: 100%;
      min-width: 0;
    }

    .w14-attachment-primary-row {
      display: flex;
      align-items: stretch;
      gap: 0.65rem;
      min-width: 0;
    }

    .w14-attachment-primary-row .form-control {
      flex: 1 1 auto;
    }

    .w14-attachment-primary-row .btn {
      flex: 0 0 auto;
      white-space: nowrap;
    }

    .w14-attachment-help {
      margin-top: 0.5rem;
      color: var(--bs-secondary-color);
      font-size: 0.82rem;
    }

    .w14-attachment-list {
      display: grid;
      gap: 0.65rem;
    }

    .w14-attachment-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      padding: 0.75rem 0.85rem;
      border: 1px solid var(--bs-border-color);
      border-radius: 0.8rem;
      background: var(--bs-paper-bg, var(--bs-card-bg));
      min-width: 0;
    }

    .w14-attachment-item.sortable-ghost {
      opacity: 0.65;
    }

    .w14-attachment-main {
      display: flex;
      align-items: center;
      flex: 1 1 auto;
      gap: 0.65rem;
      min-width: 0;
      width: 0;
    }

    .w14-attachment-drag-handle {
      width: 1.75rem;
      height: 1.75rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      flex: 0 0 auto;
      color: var(--bs-secondary-color);
      cursor: grab;
    }

    .w14-attachment-drag-handle:active {
      cursor: grabbing;
    }

    .w14-attachment-main > .min-w-0 {
      flex: 1 1 auto;
      min-width: 0;
      width: 0;
    }

    .w14-attachment-icon {
      width: 2rem;
      height: 2rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 0.65rem;
      background: rgba(var(--bs-primary-rgb), 0.12);
      color: var(--bs-primary);
      flex: 0 0 auto;
    }

    .w14-attachment-icon--file {
      background: rgba(var(--bs-primary-rgb), 0.12);
      color: var(--bs-primary);
    }

    .w14-attachment-icon--link {
      background: rgba(var(--bs-success-rgb), 0.14);
      color: var(--bs-success);
    }

    .w14-attachment-icon--youtube {
      background: rgba(var(--bs-danger-rgb), 0.13);
      color: var(--bs-danger);
    }

    .w14-attachment-icon--pending {
      background: rgba(var(--bs-info-rgb), 0.14);
      color: var(--bs-info);
    }

    .w14-attachment-item > .btn {
      flex: 0 0 auto;
    }

    .w14-attachment-title {
      display: block;
      max-width: 100%;
      color: var(--bs-heading-color);
      font-weight: 600;
      text-decoration: none;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .w14-attachment-title.w14-attachment-title-btn {
      border: 0;
      background: transparent;
      padding: 0;
      text-align: left;
      cursor: pointer;
    }

    .w14-attachment-title.w14-attachment-title-btn:hover,
    .w14-attachment-title.w14-attachment-title-btn:focus-visible {
      color: var(--bs-primary);
    }

    .w14-attachment-meta {
      display: block;
      color: var(--bs-secondary-color);
      font-size: 0.78rem;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    @media (max-width: 575.98px) {
      .w14-task-attachment-modal .modal-dialog {
        margin-left: 0.75rem;
        margin-right: 0.75rem;
        max-width: none;
        width: calc(100% - 1.5rem);
      }

      .w14-attachment-input {
        padding: 0.75rem;
      }

      .w14-attachment-primary-row {
        flex-direction: column;
      }

      .w14-attachment-primary-row .btn {
        width: 100%;
        justify-content: center;
      }

      .w14-attachment-item {
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.65rem;
      }

      .w14-attachment-icon {
        height: 1.75rem;
        width: 1.75rem;
      }
    }
  </style>
@endonce

@once
  @push('scripts')
    <script>
      window.w14TaskUploadState = function(config) {
        return {
          uploading: false,
          uploadFailed: false,
          uploadComplete: false,
          progress: 0,
          files: [],
          warning: '',
          allowedExtensions: config.allowedExtensions || [],
          maxFileBytes: config.maxFileBytes || 0,
          setFiles(event) {
            const selectedFiles = Array.from(event.target.files || []);
            const keptFiles = selectedFiles.filter((file) => this.fileAllowed(file));
            const blockedFiles = selectedFiles.filter((file) => !this.fileAllowed(file));
            let displayedFiles = selectedFiles;
            let selectionCleared = false;

            if (window.DataTransfer) {
              const dataTransfer = new DataTransfer();
              keptFiles.forEach((file) => dataTransfer.items.add(file));
              event.target.files = dataTransfer.files;
            } else if (blockedFiles.length > 0) {
              event.target.value = '';
              displayedFiles = blockedFiles;
              selectionCleared = true;
            }

            this.files = displayedFiles.map((file) => ({
              name: file.name,
              size: this.formatSize(file.size),
              allowed: this.fileAllowed(file),
              reason: this.blockReason(file),
            }));
            this.warning = this.warningMessage(blockedFiles, selectionCleared);
            this.uploading = false;
            this.uploadFailed = false;
            this.uploadComplete = false;
            this.progress = 0;
          },
          uploadStarted() {
            this.uploading = true;
            this.uploadFailed = false;
            this.uploadComplete = false;
            this.progress = 0;
            this.$wire.setUploadsInProgress(true);
          },
          uploadFinished() {
            this.uploading = false;
            this.uploadComplete = true;
            this.progress = 100;
            this.$wire.setUploadsInProgress(false);
            setTimeout(() => this.keepBlockedFilesOnly(), 150);
          },
          uploadErrored() {
            this.uploading = false;
            this.uploadFailed = true;
            this.uploadComplete = false;
            this.$wire.setUploadsInProgress(false);
          },
          removeListedFile(index) {
            this.files.splice(index, 1);
            this.warning = '';
            if (this.$refs.taskFilesInput && this.files.length === 0) {
              this.$refs.taskFilesInput.value = '';
            }
          },
          clearSelection() {
            this.resetUploadList();
            if (this.$refs.taskFilesInput) {
              this.$refs.taskFilesInput.value = '';
            }
          },
          fileAllowed(file) {
            const extensionAllowed =
              this.allowedExtensions.length === 0 ||
              this.allowedExtensions.includes(this.fileExtension(file));
            const sizeAllowed = this.maxFileBytes <= 0 || file.size <= this.maxFileBytes;

            return extensionAllowed && sizeAllowed;
          },
          fileExtension(file) {
            const name = String(file.name || '');
            const dotIndex = name.lastIndexOf('.');
            return dotIndex >= 0 ? name.slice(dotIndex + 1).toLowerCase() : '';
          },
          blockReason(file) {
            if (this.allowedExtensions.length > 0 && !this.allowedExtensions.includes(this.fileExtension(file))) return 'Unsupported';
            if (this.maxFileBytes > 0 && file.size > this.maxFileBytes) return 'Too large';
            return '';
          },
          warningMessage(blockedFiles, selectionCleared) {
            if (blockedFiles.length === 0) return '';
            if (selectionCleared) return 'Remove unsupported or oversized files, then choose the supported files again.';
            return blockedFiles.length + ' unsupported or oversized file' + (blockedFiles.length === 1 ? ' was' : 's were') + ' removed before upload.';
          },
          formatSize(bytes) {
            if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(1) + ' GB';
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
            return Math.max(1, Math.round(bytes / 1024)) + ' KB';
          },
          statusLabel(file) {
            if (!file.allowed) return file.reason || 'Blocked';
            if (this.uploadFailed) return 'Upload failed';
            if (this.uploadComplete) return 'Ready to add';
            if (this.uploading) return 'Uploading';
            return 'Selected';
          },
          resetUploadList() {
            this.uploading = false;
            this.uploadFailed = false;
            this.uploadComplete = false;
            this.progress = 0;
            this.files = [];
            this.warning = '';
          },
          keepBlockedFilesOnly() {
            this.uploadComplete = false;
            this.progress = 0;
            this.files = this.files.filter((file) => !file.allowed);
            if (this.files.length === 0) {
              this.warning = '';
            }
          }
        };
      };

      window.w14InitTaskAttachmentSortable = function(el, taskId, locked) {
        if (!el || locked || !taskId) return;
        if (el.dataset.w14SortableReady === '1') return;
        if (typeof window.Sortable === 'undefined') {
          console.warn('Sortable.js not loaded');
          return;
        }

        el.dataset.w14SortableReady = '1';

        new window.Sortable(el, {
          animation: 150,
          handle: '.w14-attachment-drag-handle',
          draggable: '[data-attachment-id]',
          ghostClass: 'sortable-ghost',
          filter: 'a, button:not(.w14-attachment-drag-handle), input, textarea, select',
          preventOnFilter: true,
          onEnd() {
            const ids = Array.from(el.querySelectorAll('[data-attachment-id]'))
              .map(node => Number.parseInt(node.getAttribute('data-attachment-id'), 10))
              .filter(Number.isFinite);

            Livewire.dispatch('reorder-session-task-attachments', { taskId, orderedIds: ids });
          }
        });
      };

      window.w14InitTaskPendingFileSortable = function(el, locked) {
        if (!el || locked) return;
        if (el.dataset.w14SortableReady === '1') return;
        if (typeof window.Sortable === 'undefined') {
          console.warn('Sortable.js not loaded');
          return;
        }

        el.dataset.w14SortableReady = '1';

        new window.Sortable(el, {
          animation: 150,
          handle: '.w14-attachment-drag-handle',
          draggable: '[data-pending-file-key]',
          ghostClass: 'sortable-ghost',
          filter: 'a, button:not(.w14-attachment-drag-handle), input, textarea, select',
          preventOnFilter: true,
          onEnd() {
            const keys = Array.from(el.querySelectorAll('[data-pending-file-key]'))
              .map(node => node.getAttribute('data-pending-file-key'))
              .filter(Boolean);

            Livewire.find(el.closest('[wire\\:id]')?.getAttribute('wire:id'))?.call('reorderPendingFiles', keys);
          }
        });
      };

      window.w14InitTaskDraftAttachmentSortable = function(el, locked) {
        if (!el || locked) return;
        if (typeof window.Sortable === 'undefined') {
          console.warn('Sortable.js not loaded');
          return;
        }

        if (!el.querySelector('[data-draft-attachment-key]')) {
          return;
        }

        if (el.dataset.w14SortableReady === '1' && el._w14SortableInstance) {
          el._w14SortableInstance.destroy();
          delete el._w14SortableInstance;
        }

        el.dataset.w14SortableReady = '1';

        el._w14SortableInstance = new window.Sortable(el, {
          animation: 150,
          handle: '.w14-attachment-drag-handle',
          draggable: '[data-draft-attachment-key]',
          ghostClass: 'sortable-ghost',
          chosenClass: 'sortable-chosen',
          dragClass: 'sortable-drag',
          filter: 'a, button:not(.w14-attachment-drag-handle), input, textarea, select',
          preventOnFilter: true,
          forceFallback: true,
          fallbackOnBody: true,
          fallbackTolerance: 4,
          scroll: true,
          bubbleScroll: true,
          onEnd() {
            const keys = Array.from(el.querySelectorAll('[data-draft-attachment-key]'))
              .map(node => node.getAttribute('data-draft-attachment-key'))
              .filter(Boolean);

            Livewire.find(el.closest('[wire\\:id]')?.getAttribute('wire:id'))?.call('reorderAttachmentDraftItems', keys);
          }
        });
      };
    </script>
  @endpush
@endonce

@once
  @push('scripts')
    <script>
      document.addEventListener('livewire:init', () => {
        Livewire.hook('morph.updated', ({ el }) => {
          if (!el || !el.querySelectorAll) return;

          el.querySelectorAll('[data-w14-draft-sortable-host]').forEach((host) => {
            const list = host.querySelector('.draft-attachments-sortable');
            const locked = host.getAttribute('data-w14-draft-sortable-locked') === '1';

            window.w14InitTaskDraftAttachmentSortable && window.w14InitTaskDraftAttachmentSortable(list, locked);
          });
        });
      });
    </script>
  @endpush
@endonce

<div class="w14-attachment-panel" wire:key="task-attachments-{{ md5((string) $resetKey) }}">
  <div
    class="w14-attachment-input"
    x-data="window.w14TaskUploadState({
      allowedExtensions: @js($allowedFileExtensions ?? []),
      maxFileBytes: {{ (int) ($maxFileBytes ?? 0) }}
    })"
    x-on:livewire-upload-start="uploadStarted()"
    x-on:livewire-upload-finish="uploadFinished()"
    x-on:livewire-upload-error="uploadErrored()"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
  >
    <div class="w14-attachment-primary-row">
      <input id="{{ $inputId }}"
             x-ref="taskFilesInput"
             type="file"
             class="form-control"
             multiple
             wire:model="files"
             x-on:change.capture="setFiles($event)"
             wire:loading.attr="disabled"
             wire:target="files"
             accept="{{ $fileAcceptAttribute ?? '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.gif,.mp4,.mov,.m4v,.webm,.ogg,.mp3,.wav' }}"
             @disabled($locked)>

      @if($libraryPickerEnabled)
        <button type="button" class="btn btn-outline-primary" wire:click="openLibraryPicker" @disabled($locked || ! ($libraryPickerCanOpen ?? ! empty($sessionId ?? null)))>
          <i class="ti tabler-books me-1"></i>
          <span>Choose from Library</span>
        </button>
      @endif
    </div>

    <div class="w14-attachment-help">
      Documents, images, audio, and video. Max 50 MB per file.
    </div>

    <div class="w14-attachment-list mt-3" x-show="files.length" x-cloak>
      <template x-for="file in files" :key="file.name + '-' + file.size">
        <div class="w14-attachment-item">
          <div class="w14-attachment-main">
            <span class="w14-attachment-icon w14-attachment-icon--pending"><i class="ti tabler-file-upload"></i></span>
            <div class="min-w-0">
              <span class="w14-attachment-title" x-text="file.name"></span>
              <span class="w14-attachment-meta" x-text="file.size"></span>
            </div>
          </div>
          <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <span
              class="badge"
              x-bind:class="! file.allowed ? 'bg-label-danger' : (uploadFailed ? 'bg-label-danger' : (uploadComplete ? 'bg-label-success' : 'bg-label-secondary'))"
              x-text="statusLabel(file)"
            ></span>
            <button
              type="button"
              class="btn btn-sm btn-icon btn-outline-danger"
              x-show="! uploading && ! file.allowed"
              x-on:click="removeListedFile(files.indexOf(file))"
              aria-label="Remove blocked file">
              <i class="ti tabler-x"></i>
            </button>
          </div>
        </div>
      </template>
    </div>

    <div class="text-warning small mt-2" x-show="warning" x-text="warning" x-cloak></div>
    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" x-show="files.length && ! uploading" x-on:click="clearSelection()" x-cloak>
      Clear selection
    </button>

    <div class="mt-2" x-show="uploading" x-cloak>
      <div class="d-flex justify-content-between small text-body-secondary mb-1">
        <span>Uploading selected files...</span>
        <span x-text="progress + '%'"></span>
      </div>
      <div class="progress" style="height: 0.45rem;">
        <div class="progress-bar" role="progressbar" x-bind:style="'width: ' + progress + '%'"></div>
      </div>
    </div>

    @error('files')
      <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
    @error('files.*')
      <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
    @error('finalFiles.*')
      <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
  </div>

  @if($showExternalAttachmentRows)
    <div>
      @include('livewire.teacher.partials.task-external-attachment-rows', [
        'linkTitleModel' => $linkTitleModel ?? 'link_title_input',
        'linkUrlModel' => $linkUrlModel ?? 'link_url_input',
        'youtubeTitleModel' => $youtubeTitleModel ?? 'youtube_title_input',
        'youtubeUrlModel' => $youtubeUrlModel ?? 'youtube_url_input',
        'busyTargets' => $busyTargets ?? 'files,save,updateTask,addLink,addYoutube',
        'locked' => $locked,
      ])
    </div>
  @endif

  @if($useUnifiedAttachmentTray)
    @if(count($draftAttachmentItems))
      <div
        class="w14-attachment-list"
        data-w14-draft-sortable-host
        data-w14-draft-sortable-locked="{{ $locked ? '1' : '0' }}"
        x-data
        x-init="
          const initDraftSortable = () => window.w14InitTaskDraftAttachmentSortable && window.w14InitTaskDraftAttachmentSortable($el.querySelector('.draft-attachments-sortable'), {{ ($locked || ! $draftReorderEnabled) ? 'true' : 'false' }});
          initDraftSortable();
          setTimeout(initDraftSortable, 100);
          setTimeout(initDraftSortable, 500);
        ">
        <div class="draft-attachments-sortable d-flex flex-column gap-2">
          @foreach($draftAttachmentItems as $item)
            <div class="w14-attachment-item" wire:key="draft-attachment-{{ md5($item['key']) }}" data-draft-attachment-key="{{ $item['key'] }}">
              @if($draftReorderEnabled)
                <button type="button"
                        class="btn btn-sm btn-text-secondary w14-attachment-drag-handle"
                        title="Drag to reorder"
                        aria-label="Drag {{ $item['title'] }} to reorder"
                        @disabled($locked)>
                  <i class="ti tabler-grip-vertical"></i>
                </button>
              @endif
              <div class="w14-attachment-main">
                <span class="w14-attachment-icon {{ $item['iconClass'] }}"><i class="ti {{ $item['icon'] }}"></i></span>
                <div class="min-w-0">
                  @if(($item['kind'] ?? '') === 'existing' && !in_array(($item['type'] ?? 'file'), ['link', 'youtube', 'vocabulary_game'], true) && !empty($item['attachmentId']) && !empty($item['sessionId']) && !empty($item['taskId']))
                    <button
                      type="button"
                      class="w14-attachment-title w14-attachment-title-btn"
                      title="{{ $item['title'] }}"
                      wire:click="openAttachmentStudyViewer({{ (int) $item['sessionId'] }}, {{ (int) $item['taskId'] }}, {{ (int) $item['attachmentId'] }})">
                      {{ $item['title'] }}
                    </button>
                  @elseif(!empty($item['url']))
                    <a class="w14-attachment-title" href="{{ $item['url'] }}" target="_blank" title="{{ $item['title'] }}">{{ $item['title'] }}</a>
                  @else
                    <span class="w14-attachment-title" title="{{ $item['title'] }}">{{ $item['title'] }}</span>
                  @endif
                  <span class="w14-attachment-meta" title="{{ trim(($item['meta'] ?? '').' '.($item['detail'] ?? '')) }}">
                    {{ $item['meta'] ?? 'Attachment' }}@if(!empty($item['detail'])) &middot; {{ $item['detail'] }} @endif
                  </span>
                  @if(!empty($item['size']))
                    <span class="w14-attachment-meta">{{ number_format($item['size'] / 1024, 1) }} KB</span>
                  @endif
                </div>
              </div>
              <button type="button"
                      class="btn btn-sm btn-icon btn-outline-danger flex-shrink-0"
                      wire:click="removeDraftAttachmentItem(@js($item['key']))"
                      wire:loading.attr="disabled"
                      aria-label="Remove {{ $item['title'] }}"
                      @disabled($locked)>
                <i class="ti tabler-x"></i>
              </button>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  @elseif(
      count($existingAttachments ?? []) ||
      count($finalFiles ?? []) ||
      count($links ?? []) ||
      count($youtubes ?? [])
  )
    <div
      class="w14-attachment-list"
      x-data
      x-init="
        const initSortable = () => {
          window.w14InitTaskAttachmentSortable && window.w14InitTaskAttachmentSortable($el.querySelector('.existing-attachments-sortable'), {{ (int) ($taskId ?? 0) }}, {{ $locked ? 'true' : 'false' }});
          window.w14InitTaskPendingFileSortable && window.w14InitTaskPendingFileSortable($el.querySelector('.pending-files-sortable'), {{ ($locked || ! $pendingReorderEnabled) ? 'true' : 'false' }});
        };
        initSortable();
        setTimeout(initSortable, 100);
        setTimeout(initSortable, 500);
      ">
      @if(count($existingAttachments ?? []))
        <div class="existing-attachments-sortable d-flex flex-column gap-2">
          @foreach($existingAttachments ?? [] as $attachment)
            @php
              $attachmentType = $attachment['type'] ?? 'file';
              $attachmentIcon = match ($attachmentType) {
                'vocabulary_game' => 'tabler-balloon',
                'youtube' => 'tabler-brand-youtube',
                'link' => 'tabler-link',
                default => 'tabler-file-description',
              };
              $attachmentIconClass = match ($attachmentType) {
                'vocabulary_game' => 'w14-attachment-icon--link',
                'youtube' => 'w14-attachment-icon--youtube',
                'link' => 'w14-attachment-icon--link',
                default => 'w14-attachment-icon--file',
              };
              $attachmentLabel = match ($attachmentType) {
                'vocabulary_game' => 'Vocab Games link',
                'youtube' => 'Saved YouTube link',
                'link' => 'Saved link',
                default => 'Saved file',
              };
            @endphp
            <div class="w14-attachment-item" wire:key="existing-attachment-{{ $attachment['id'] }}" data-attachment-id="{{ $attachment['id'] }}">
              <button type="button"
                      class="btn btn-sm btn-text-secondary w14-attachment-drag-handle"
                      title="Drag to reorder"
                      aria-label="Drag {{ $attachment['title'] }} to reorder"
                      @disabled($locked)>
                <i class="ti tabler-grip-vertical"></i>
              </button>
              <div class="w14-attachment-main">
                <span class="w14-attachment-icon {{ $attachmentIconClass }}"><i class="ti {{ $attachmentIcon }}"></i></span>
                <div class="min-w-0">
                  <a class="w14-attachment-title" href="{{ $attachment['url'] }}" target="_blank">{{ $attachment['title'] }}</a>
                  <span class="w14-attachment-meta">{{ $attachmentLabel }}</span>
                  @if(! empty($attachment['size']))
                    <span class="w14-attachment-meta">{{ number_format($attachment['size'] / 1024, 1) }} KB</span>
                  @endif
                </div>
              </div>
              <button type="button"
                      class="btn btn-sm btn-text-danger"
                      wire:click="markAttachmentForDeletion({{ $attachment['id'] }})"
                      wire:loading.attr="disabled"
                      @disabled($locked)>
                <i class="icon-base ti tabler-trash"></i>
              </button>
            </div>
          @endforeach
        </div>
      @endif

      @if(count($finalFiles ?? []))
        <div
          class="pending-files-sortable d-flex flex-column gap-2"
          x-data
          x-init="
            const initPendingSortable = () => window.w14InitTaskPendingFileSortable && window.w14InitTaskPendingFileSortable($el, {{ ($locked || ! $pendingReorderEnabled) ? 'true' : 'false' }});
            initPendingSortable();
            setTimeout(initPendingSortable, 100);
            setTimeout(initPendingSortable, 500);
          ">
          @foreach($finalFiles ?? [] as $index => $upload)
            @php
              if (is_object($upload) && method_exists($upload, 'getFilename')) {
                  $pendingFileKey = (string) $upload->getFilename();
              } elseif (is_object($upload) && method_exists($upload, 'getClientOriginalName')) {
                  $pendingFileKey = (string) $upload->getClientOriginalName();
              } else {
                  $pendingFileKey = 'pending-file-'.$index;
              }
            @endphp
            <div class="w14-attachment-item" wire:key="new-file-{{ $pendingFileKey }}" data-pending-file-key="{{ $pendingFileKey }}">
              @if($pendingReorderEnabled)
                <button type="button"
                        class="btn btn-sm btn-text-secondary w14-attachment-drag-handle"
                        title="Drag to reorder"
                        aria-label="Drag {{ $upload->getClientOriginalName() }} to reorder"
                        @disabled($locked)>
                  <i class="ti tabler-grip-vertical"></i>
                </button>
              @endif
              <div class="w14-attachment-main">
                <span class="w14-attachment-icon w14-attachment-icon--pending"><i class="ti tabler-file-upload"></i></span>
                <div class="min-w-0">
                  <span class="w14-attachment-title">{{ $upload->getClientOriginalName() }}</span>
                  <span class="w14-attachment-meta">New file &middot; {{ number_format(($upload->getSize() ?? 0) / 1024, 1) }} KB</span>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <span class="badge bg-label-success">Ready to add</span>
                <button type="button"
                        class="btn btn-sm btn-icon btn-outline-danger"
                        wire:click="removeFile({{ $index }})"
                        wire:loading.attr="disabled"
                        aria-label="Remove {{ $upload->getClientOriginalName() }}"
                        @disabled($locked)>
                  <i class="ti tabler-x"></i>
                </button>
              </div>
            </div>
          @endforeach
        </div>
      @endif

      @foreach($links ?? [] as $index => $linkAttachment)
        <div class="w14-attachment-item" wire:key="new-link-{{ $index }}">
          <div class="w14-attachment-main">
            <span class="w14-attachment-icon w14-attachment-icon--link"><i class="ti tabler-link"></i></span>
            <div class="min-w-0">
              <a class="w14-attachment-title" href="{{ $linkAttachment['url'] }}" target="_blank">{{ $linkAttachment['title'] }}</a>
              <span class="w14-attachment-meta">New link</span>
            </div>
          </div>
          <button type="button"
                  class="btn btn-sm btn-text-danger"
                  wire:click="removeLink({{ $index }})"
                  wire:loading.attr="disabled"
                  @disabled($locked)>
            <i class="icon-base ti tabler-trash"></i>
          </button>
        </div>
      @endforeach

      @foreach($youtubes ?? [] as $index => $youtubeAttachment)
        <div class="w14-attachment-item" wire:key="new-youtube-{{ $index }}">
          <div class="w14-attachment-main">
            <span class="w14-attachment-icon w14-attachment-icon--youtube"><i class="ti tabler-brand-youtube"></i></span>
            <div class="min-w-0">
              <a class="w14-attachment-title" href="{{ $youtubeAttachment['url'] }}" target="_blank">{{ $youtubeAttachment['title'] }}</a>
              <span class="w14-attachment-meta">New YouTube link</span>
            </div>
          </div>
          <button type="button"
                  class="btn btn-sm btn-text-danger"
                  wire:click="removeYoutube({{ $index }})"
                  wire:loading.attr="disabled"
                  @disabled($locked)>
            <i class="icon-base ti tabler-trash"></i>
          </button>
        </div>
      @endforeach
    </div>
  @endif
</div>
