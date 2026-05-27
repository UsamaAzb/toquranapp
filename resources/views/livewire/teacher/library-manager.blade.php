<div class="{{ $quickAdd ? 'library-manager' : 'card mb-6 library-manager' }}">
  <style>
    .library-manager .library-toolbar {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      align-items: end;
      justify-content: space-between;
    }

    .library-manager .library-toolbar-main {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      align-items: end;
      min-width: 0;
    }

    .library-manager .library-subject-select {
      min-width: min(18rem, 100%);
    }

    .library-manager .library-row {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 0.75rem;
      align-items: center;
      padding: 0.875rem;
      border: 1px solid var(--bs-border-color);
      border-radius: 0.5rem;
      background: var(--bs-paper-bg);
    }

    .library-manager .library-row-title {
      min-width: 0;
    }

    .library-manager .library-row-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      justify-content: flex-end;
    }

    .library-manager .library-create-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 1rem;
    }

    .library-manager .library-folder-menu {
      max-height: 18rem;
      overflow-y: auto;
      width: min(28rem, calc(100vw - 2rem));
    }

    .library-manager .library-quick-grid {
      display: grid;
      grid-template-columns: minmax(0, 1.2fr) minmax(18rem, 0.8fr);
      gap: 1rem;
      align-items: start;
    }

    .library-manager .library-quick-grid--single {
      grid-template-columns: minmax(0, 1fr);
    }

    .library-manager .library-quick-panel {
      border: 1px solid var(--bs-border-color);
      border-radius: 0.75rem;
      padding: 1rem;
      background: var(--bs-paper-bg);
    }

    .library-manager .library-quick-panel--wide {
      grid-row: span 2;
    }

    .library-manager .library-upload-row,
    .library-manager .library-link-row {
      border: 1px solid var(--bs-border-color);
      border-radius: 0.5rem;
      background: var(--bs-body-bg);
    }

    .library-manager .library-upload-row {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      min-width: 0;
      padding: 0.625rem 0.75rem;
    }

    .library-manager .library-link-row {
      padding: 0.75rem;
    }

    .library-manager .library-quick-savebar {
      position: sticky;
      bottom: 0;
      z-index: 2;
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      align-items: center;
      justify-content: space-between;
      margin-top: 1rem;
      padding: 0.875rem 0 0;
      border-top: 1px solid var(--bs-border-color);
      background: var(--bs-paper-bg);
    }

    @media (max-width: 768px) {
      .library-manager .library-toolbar,
      .library-manager .library-toolbar-main,
      .library-manager .library-row-actions {
        width: 100%;
      }

      .library-manager .library-row {
        grid-template-columns: 1fr;
      }

      .library-manager .library-create-grid {
        grid-template-columns: 1fr;
      }

      .library-manager .library-quick-grid {
        grid-template-columns: 1fr;
      }

      .library-manager .library-quick-panel--wide {
        grid-row: auto;
      }

      .library-manager .library-row-actions .btn,
      .library-manager .library-toolbar .btn {
        flex: 1 1 auto;
      }
    }
  </style>
  <script>
    window.w14LibraryUploadState = function(config) {
      return {
        uploading: false,
        uploadFailed: false,
        uploadComplete: false,
        progress: 0,
        files: [],
        warning: '',
        allowedExtensions: config.allowedExtensions || [],
        maxFileBytes: config.maxFileBytes || 0,
        maxBatchBytes: config.maxBatchBytes || 0,
        setFiles(event) {
          const selectedFiles = Array.from(event.target.files || []);
          const totalAllowedSize = selectedFiles
            .filter((file) => this.fileAllowed(file))
            .reduce((total, file) => total + file.size, 0);
          const batchTooLarge = totalAllowedSize > this.maxBatchBytes;
          const keptFiles = batchTooLarge ? [] : selectedFiles.filter((file) => this.fileAllowed(file));
          const blockedFiles = batchTooLarge ? selectedFiles : selectedFiles.filter((file) => !this.fileAllowed(file));

          if (window.DataTransfer) {
            const dataTransfer = new DataTransfer();
            keptFiles.forEach((file) => dataTransfer.items.add(file));
            event.target.files = dataTransfer.files;
          } else if (blockedFiles.length > 0) {
            event.target.value = '';
          }

          let uploadIndex = 0;
          this.files = selectedFiles.map((file) => {
            const allowed = !batchTooLarge && this.fileAllowed(file);

            return {
              name: file.name,
              size: this.formatSize(file.size),
              allowed: allowed,
              uploadIndex: allowed ? uploadIndex++ : null,
              reason: this.blockReason(file, batchTooLarge),
            };
          });
          this.warning = this.warningMessage(blockedFiles, batchTooLarge);
          this.uploading = false;
          this.uploadFailed = false;
          this.uploadComplete = false;
          this.progress = 0;
        },
        removeListedFile(index) {
          const file = this.files[index];
          if (file && file.allowed && this.$wire) {
            this.$wire.removeResourceFileAt(file.uploadIndex);
          }
          this.files.splice(index, 1);
          this.reindexUploadFiles();
          this.warning = '';
        },
        reindexUploadFiles() {
          let uploadIndex = 0;
          this.files = this.files.map((file) => ({
            ...file,
            uploadIndex: file.allowed ? uploadIndex++ : null,
          }));
        },
        clearSelection() {
          this.resetUploadList(true);
        },
        fileAllowed(file) {
          return file.size <= this.maxFileBytes && this.allowedExtensions.includes(this.fileExtension(file));
        },
        fileExtension(file) {
          const name = String(file.name || '');
          const dotIndex = name.lastIndexOf('.');
          return dotIndex >= 0 ? name.slice(dotIndex + 1).toLowerCase() : '';
        },
        blockReason(file, batchTooLarge) {
          if (batchTooLarge) return 'Batch too large';
          if (!this.allowedExtensions.includes(this.fileExtension(file))) return 'Unsupported';
          if (file.size > this.maxFileBytes) return 'Too large';
          return '';
        },
        warningMessage(blockedFiles, batchTooLarge) {
          if (batchTooLarge) {
            return 'The selected files are larger than the current batch upload limit. Select fewer files at once.';
          }
          if (blockedFiles.length === 0) return '';
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
          if (this.uploadComplete) return 'Ready to save';
          if (this.uploading) return 'Uploading';
          return 'Selected';
        },
        resetUploadList(clearWire = false) {
          this.uploading = false;
          this.uploadFailed = false;
          this.uploadComplete = false;
          this.progress = 0;
          this.files = [];
          this.warning = '';
          if (clearWire && this.$wire) {
            this.$wire.set('resourceFiles', []);
          }
          if (this.$refs.quickResourceFilesInput) {
            this.$refs.quickResourceFilesInput.value = '';
          }
          if (this.$refs.resourceFilesInput) {
            this.$refs.resourceFilesInput.value = '';
          }
        }
      };
    };
  </script>

  @unless($quickAdd)
  <div class="card-header">
    <div class="library-toolbar">
      <div>
        <h5 class="mb-1">My Library</h5>
        <div class="text-body-secondary small">Teacher-owned folders and resources for task/session reuse.</div>
      </div>

      <div class="library-toolbar-main">
        <div class="library-subject-select">
          <label class="form-label" for="library-subject">Subject</label>
          <select id="library-subject" class="form-select" wire:model.live="selectedSubjectId">
            @forelse($subjects as $subject)
              <option value="{{ $subject['id'] }}">{{ $subject['title'] }}</option>
            @empty
              <option value="">No teacher subjects available</option>
            @endforelse
          </select>
          @error('selectedSubjectId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <button type="button" class="btn btn-sm {{ $showArchived ? 'btn-warning' : 'btn-outline-secondary' }}" wire:click="$toggle('showArchived')">
          <span>{{ $showArchived ? 'Hide archived' : 'Show archived' }}</span>
        </button>
      </div>
    </div>
  </div>
  @endunless

  <div class="{{ $quickAdd ? '' : 'card-body' }}">
    @if(empty($subjects))
      <div class="alert alert-warning mb-0">
        No active/current teacher subject assignment was found for this account.
      </div>
    @else
      @if($quickAdd)
        <div class="library-quick-add">
          <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
            <div class="min-w-0">
              <span class="badge bg-label-primary mb-2">Adding to folder</span>
              <h5 class="mb-1 text-truncate">{{ $currentSection?->title ?? 'Library folder' }}</h5>
              <div class="text-body-secondary small">Add only to this folder. New cards appear here after saving.</div>
            </div>
          </div>

          <div class="library-quick-grid {{ $quickAddAllowsResources && $quickAddAllowsSection ? '' : 'library-quick-grid--single' }}">
            @if($quickAddAllowsResources)
            <div class="library-quick-panel {{ $quickAddAllowsSection ? 'library-quick-panel--wide' : '' }}">
              <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                <div>
                  <h6 class="mb-1">Files</h6>
                  <div class="text-body-secondary small">Select files now, then save everything together.</div>
                </div>
                <span class="badge bg-label-info">Files</span>
              </div>

              <div class="mb-3">
                <label class="form-label" for="library-resource-title">Title</label>
                <input id="library-resource-title" type="text" class="form-control" wire:model.blur="resourceTitle">
                <div class="form-text">Optional for one file. Multiple files use their filenames as titles.</div>
                @error('resourceTitle') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>

              <div class="mb-3">
                <label class="form-label" for="library-resource-description">Description</label>
                <textarea id="library-resource-description" class="form-control" rows="2" maxlength="300" wire:model.blur="resourceDescription"></textarea>
                @error('resourceDescription') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>

              <div
                x-data="window.w14LibraryUploadState({
                  allowedExtensions: @js($allowedFileExtensions),
                  maxFileBytes: {{ (int) $uploadLimit['fileBytes'] }},
                  maxBatchBytes: {{ (int) $uploadLimit['batchBytes'] }}
                })"
                x-on:livewire-upload-start="uploading = true; uploadFailed = false; uploadComplete = false; progress = 0"
                x-on:livewire-upload-finish="uploading = false; uploadComplete = true"
                x-on:livewire-upload-error="uploading = false; uploadFailed = true; uploadComplete = false"
                x-on:livewire-upload-progress="progress = $event.detail.progress"
                x-on:library-resource-form-reset.window="resetUploadList(true)"
              >
                <label class="form-label" for="library-quick-files">Files</label>
                <input id="library-quick-files" x-ref="quickResourceFilesInput" type="file" class="form-control" wire:model="resourceFiles" x-on:change.capture="setFiles($event)" accept="{{ $fileAcceptAttribute }}" multiple>
                <div class="form-text">Maximum file size: {{ $uploadLimit['label'] }} each.</div>
                @if($uploadLimit['serverIsLower'])
                  <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small">
                    PHP is currently limiting uploads to {{ $uploadLimit['label'] }}. The Library app is ready for {{ $uploadLimit['appLabel'] }}, but PHP/XAMPP must be raised before larger files can upload.
                  </div>
                @endif
                <div class="d-flex flex-column gap-2 mt-2" x-show="files.length" x-cloak>
                  <template x-for="file in files" x-bind:key="file.name + file.size">
                    <div class="library-upload-row">
                      <span class="badge bg-label-info">File</span>
                      <span class="fw-semibold text-truncate flex-grow-1" x-text="file.name"></span>
                      <span class="small text-body-secondary" x-text="file.size"></span>
                      <span
                        class="badge"
                        x-bind:class="! file.allowed ? 'bg-label-danger' : (uploadFailed ? 'bg-label-danger' : (uploadComplete ? 'bg-label-success' : 'bg-label-secondary'))"
                        x-text="statusLabel(file)"
                      ></span>
                      <button
                        type="button"
                        class="btn btn-sm btn-icon btn-outline-danger"
                        x-show="! uploading && (! file.allowed || uploadComplete)"
                        x-on:click="removeListedFile(files.indexOf(file))"
                        aria-label="Remove blocked file">
                        <i class="ti tabler-x"></i>
                      </button>
                    </div>
                  </template>
                </div>
                <div class="text-warning small mt-2" x-show="warning" x-text="warning" x-cloak></div>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" x-show="files.length" x-on:click="clearSelection()" x-cloak>
                  Clear selection
                </button>
                <div class="mt-2" x-show="uploading" x-cloak>
                  <div class="d-flex justify-content-between small text-body-secondary mb-1">
                    <span>Uploading files...</span>
                    <span x-text="progress + '%'"></span>
                  </div>
                  <div class="progress" style="height: .5rem;">
                    <div class="progress-bar" role="progressbar" x-bind:style="'width: ' + progress + '%'" aria-label="Library upload progress"></div>
                  </div>
                </div>
              </div>

              @error('resourceFiles') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              @error('resourceFiles.*') <div class="text-danger small mt-1">The selected files did not finish uploading, so none were kept. Select them again and try once more.</div> @enderror
              @error('file') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="library-quick-panel">
              <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                <div>
                  <h6 class="mb-1">Links</h6>
                  <div class="text-body-secondary small">Add regular links or YouTube links to the pending list.</div>
                </div>
                <span class="badge bg-label-success">Links</span>
              </div>

              @include('livewire.teacher.partials.task-external-attachment-rows', [
                'linkTitleModel' => 'quickLinkTitle',
                'linkUrlModel' => 'quickLinkUrl',
                'youtubeTitleModel' => 'quickYoutubeTitle',
                'youtubeUrlModel' => 'quickYoutubeUrl',
                'addLinkAction' => 'addQuickLink',
                'addYoutubeAction' => 'addQuickYoutube',
                'linkPendingError' => 'quickLinkPending',
                'youtubePendingError' => 'quickYoutubePending',
                'busyTargets' => 'saveQuickAdd,addQuickLink,addQuickYoutube,resourceFiles',
              ])

              @if($quickLinks || $quickYoutubes)
                <div class="d-flex flex-column gap-2 mt-3">
                  @foreach($quickLinks as $index => $link)
                    <div class="library-upload-row" wire:key="quick-link-pending-{{ $index }}">
                      <span class="badge bg-label-success">Link</span>
                      <span class="fw-semibold text-truncate flex-grow-1">{{ $link['title'] }}</span>
                      <button type="button" class="btn btn-sm btn-icon btn-outline-danger" wire:click="removeQuickLink({{ $index }})" aria-label="Remove {{ $link['title'] }}">
                        <i class="ti tabler-trash"></i>
                      </button>
                    </div>
                  @endforeach
                  @foreach($quickYoutubes as $index => $youtube)
                    <div class="library-upload-row" wire:key="quick-youtube-pending-{{ $index }}">
                      <span class="badge bg-label-danger">YouTube</span>
                      <span class="fw-semibold text-truncate flex-grow-1">{{ $youtube['title'] }}</span>
                      <button type="button" class="btn btn-sm btn-icon btn-outline-danger" wire:click="removeQuickYoutube({{ $index }})" aria-label="Remove {{ $youtube['title'] }}">
                        <i class="ti tabler-trash"></i>
                      </button>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
            @endif

            @if($quickAddAllowsSection)
            <div class="library-quick-panel">
              <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                <div>
                  <h6 class="mb-1">New subfolder</h6>
                  <div class="text-body-secondary small">Create a folder inside this folder.</div>
                </div>
                <span class="badge bg-label-primary">Folder</span>
              </div>

              <div class="mb-3">
                <label class="form-label" for="library-section-title">Folder title</label>
                <input id="library-section-title" type="text" class="form-control" wire:model.blur="sectionTitle">
                @error('sectionTitle') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @error('parent_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label" for="library-section-description">Description</label>
                <textarea id="library-section-description" class="form-control" rows="2" maxlength="300" wire:model.blur="sectionDescription"></textarea>
                @error('sectionDescription') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>
              <div class="form-text">This folder is created when you click Save subfolder.</div>
            </div>
            @endif
          </div>

          @error('quickAdd') <div class="text-danger small mt-3">{{ $message }}</div> @enderror
          <div class="library-quick-savebar">
            <div class="text-body-secondary small">
              {{ $quickAddAllowsResources && $quickAddAllowsSection
                  ? 'Files, links, YouTube links, and a new subfolder are saved together.'
                  : ($quickAddAllowsResources ? 'Files, links, and YouTube links are saved together.' : 'This subfolder is saved inside the current Library folder.') }}
            </div>
            <button
              type="button"
              class="btn btn-primary"
              wire:click="saveQuickAdd"
              wire:loading.attr="disabled"
              wire:target="saveQuickAdd,resourceFiles"
            >
              <span wire:loading.remove wire:target="saveQuickAdd">{{ $quickAddAllowsResources ? 'Save sources' : 'Save subfolder' }}</span>
              <span wire:loading wire:target="saveQuickAdd">Saving...</span>
            </button>
          </div>
        </div>
      @else
      @unless($quickAdd)
      <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="goToRoot">
          <span>Root</span>
        </button>

        @foreach($breadcrumbs as $crumb)
          <span class="text-body-secondary">/</span>
          <button type="button" class="btn btn-sm btn-text-secondary" wire:click="enterSection({{ $crumb['id'] }})">
            {{ $crumb['title'] }}
          </button>
        @endforeach

        @if($currentSection)
          <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" wire:click="goToParent">
            <span>Back</span>
          </button>
        @endif
      </div>
      @endunless

      <div class="library-create-grid {{ $quickAdd ? '' : 'mb-5' }}">
        <div class="border rounded p-3">
          <h6 class="mb-3">New subfolder</h6>
          <div class="mb-3">
            <label class="form-label" for="library-section-title">Folder title</label>
            <input id="library-section-title" type="text" class="form-control" wire:model.blur="sectionTitle">
            @error('sectionTitle') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            @error('parent_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>
          <div class="mb-3">
            <label class="form-label" for="library-section-description">Description</label>
            <textarea id="library-section-description" class="form-control" rows="2" maxlength="300" wire:model.blur="sectionDescription"></textarea>
            @error('sectionDescription') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>
          <button type="button" class="btn btn-sm btn-primary" wire:click="createSection" wire:loading.attr="disabled" wire:target="createSection">
            <span>Create folder</span>
          </button>
        </div>

        <div class="border rounded p-3">
          <h6 class="mb-3">New resource</h6>
          @if($quickAdd)
            <div class="text-body-secondary small mb-3">Files and links will be saved inside this folder.</div>
          @endif
          <div class="row g-3">
            @unless($quickAdd)
            <div class="col-12 col-lg-6">
              <label class="form-label" for="library-resource-section">Folder</label>
              <div class="dropdown" x-data="{ open: @entangle('resourceFolderDropdownOpen').live }" @click.outside="open = false" @keydown.escape.window="open = false">
                <button id="library-resource-section"
                        type="button"
                        class="form-select text-start"
                        @click.prevent="open = ! open"
                        :aria-expanded="open ? 'true' : 'false'">
                  {{ $selectedResourceFolderLabel }}
                </button>
                <div class="dropdown-menu library-folder-menu p-2" :class="{ 'show': open }" aria-labelledby="library-resource-section">
                  <input
                    id="library-resource-folder-search"
                    type="search"
                    class="form-control form-control-sm mb-2"
                    wire:model.live.debounce.250ms="resourceFolderSearch"
                    placeholder="Search folders"
                    @click.stop
                  >
                  <button type="button" class="dropdown-item rounded" wire:click="chooseResourceSection" @click="open = false">
                    Choose folder
                  </button>
                  @forelse($resourceFolderOptions as $folderOption)
                    <button type="button" class="dropdown-item rounded text-wrap" wire:click="chooseResourceSection({{ $folderOption['id'] }})" wire:key="folder-option-{{ $folderOption['id'] }}" @click="open = false">
                      {{ $folderOption['label'] }}
                    </button>
                  @empty
                    <div class="dropdown-item-text text-body-secondary small">No matching active folders found.</div>
                  @endforelse
                </div>
              </div>
              @if($resourceFolderSearch !== '' && empty($resourceFolderOptions))
                <div class="form-text text-warning">No matching active folders found.</div>
              @elseif(count($resourceFolderOptions) >= 50)
                <div class="form-text">Showing the first 50 matching folders. Keep typing to narrow the list.</div>
              @endif
              @error('resourceSectionId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              @error('library_section_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            @endunless

            <div class="col-12 {{ $quickAdd ? '' : 'col-lg-6' }}">
              <label class="form-label" for="library-resource-kind">Type</label>
              <select id="library-resource-kind" class="form-select" wire:model.live="resourceKind">
                <option value="file">File</option>
                <option value="link">Link</option>
                <option value="youtube">YouTube link</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label" for="library-resource-title">Resource title</label>
              <input id="library-resource-title" type="text" class="form-control" wire:model.blur="resourceTitle">
              @if($resourceKind === 'file')
                <div class="form-text">Optional for one file. Multiple selected files use their filenames as titles.</div>
              @endif
              @error('resourceTitle') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
              <label class="form-label" for="library-resource-description">Description</label>
              <textarea id="library-resource-description" class="form-control" rows="2" maxlength="300" wire:model.blur="resourceDescription"></textarea>
            </div>

            @if($resourceKind !== 'file')
              <div class="col-12">
                <label class="form-label" for="library-resource-url">{{ $resourceKind === 'youtube' ? 'YouTube URL' : 'Link URL' }}</label>
                <input id="library-resource-url" type="url" class="form-control" wire:model.blur="externalUrl">
                @error('externalUrl') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @error('external_url') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>
            @else
              <div class="col-12">
                <label class="form-label" for="library-resource-files">Files</label>
                <div
                  x-data="window.w14LibraryUploadState({
                    allowedExtensions: @js($allowedFileExtensions),
                    maxFileBytes: {{ (int) $uploadLimit['fileBytes'] }},
                    maxBatchBytes: {{ (int) $uploadLimit['batchBytes'] }}
                  })"
                  x-on:livewire-upload-start="uploading = true; uploadFailed = false; uploadComplete = false; progress = 0"
                  x-on:livewire-upload-finish="uploading = false; uploadComplete = true"
                  x-on:livewire-upload-error="uploading = false; uploadFailed = true; uploadComplete = false"
                  x-on:livewire-upload-progress="progress = $event.detail.progress"
                  x-on:library-resource-form-reset.window="resetUploadList(true)"
                >
                  <input id="library-resource-files" x-ref="resourceFilesInput" type="file" class="form-control" wire:model="resourceFiles" x-on:change.capture="setFiles($event)" accept="{{ $fileAcceptAttribute }}" multiple>
                  <div class="form-text">Select one or more files. Current maximum file size: {{ $uploadLimit['label'] }} each.</div>
                  @if($uploadLimit['serverIsLower'])
                    <div class="alert alert-warning py-2 px-3 mt-2 mb-0 small">
                      PHP is currently limiting uploads to {{ $uploadLimit['label'] }}. The Library app is ready for {{ $uploadLimit['appLabel'] }}, but PHP/XAMPP must be raised before larger files can upload.
                    </div>
                  @endif
                  <div class="d-flex flex-column gap-2 mt-2" x-show="files.length" x-cloak>
                    <template x-for="file in files" x-bind:key="file.name + file.size">
                      <div class="d-flex align-items-center gap-2 border rounded px-3 py-2">
                        <span class="badge bg-label-info">File</span>
                        <span class="fw-semibold text-truncate flex-grow-1" x-text="file.name"></span>
                        <span class="small text-body-secondary" x-text="file.size"></span>
                        <span
                          class="badge"
                          x-bind:class="! file.allowed ? 'bg-label-danger' : (uploadFailed ? 'bg-label-danger' : (uploadComplete ? 'bg-label-success' : 'bg-label-secondary'))"
                          x-text="statusLabel(file)"
                        ></span>
                        <button
                          type="button"
                          class="btn btn-sm btn-icon btn-outline-danger"
                          x-show="! uploading && (! file.allowed || uploadComplete)"
                          x-on:click="removeListedFile(files.indexOf(file))"
                          aria-label="Remove blocked file">
                          <i class="ti tabler-x"></i>
                        </button>
                      </div>
                    </template>
                  </div>
                  <div class="text-warning small mt-2" x-show="warning" x-text="warning" x-cloak></div>
                  <button type="button" class="btn btn-sm btn-outline-secondary mt-2" x-show="files.length" x-on:click="clearSelection()" x-cloak>
                    Clear selection
                  </button>
                  <div class="mt-2" x-show="uploading" x-cloak>
                    <div class="d-flex justify-content-between small text-body-secondary mb-1">
                      <span>Uploading Library files...</span>
                      <span x-text="progress + '%'"></span>
                    </div>
                    <div class="progress" style="height: .5rem;">
                      <div class="progress-bar" role="progressbar" x-bind:style="'width: ' + progress + '%'" aria-label="Library upload progress"></div>
                    </div>
                  </div>
                </div>
                @error('resourceFiles') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @error('resourceFiles.*') <div class="text-danger small mt-1">The selected files did not finish uploading, so none were kept. Select them again and try once more.</div> @enderror
                @error('file') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>
            @endif
          </div>

          <button
            type="button"
            class="btn btn-sm btn-primary mt-3"
            wire:click="createResource"
            wire:loading.attr="disabled"
            wire:target="createResource,resourceFiles"
            @disabled($resourceKind === 'file' && count($resourceFiles) === 0)
          >
            <span>Add resources</span>
          </button>
        </div>
      </div>

      @unless($quickAdd)
      @if($editingSectionId)
        <div class="alert alert-primary mb-4">
          <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-5">
              <label class="form-label" for="library-edit-title">Rename folder</label>
              <input id="library-edit-title" type="text" class="form-control" wire:model.blur="editingSectionTitle">
              @error('editingSectionTitle') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-lg-5">
              <label class="form-label" for="library-edit-description">Description</label>
              <input id="library-edit-description" type="text" class="form-control" maxlength="300" wire:model.blur="editingSectionDescription">
            </div>
            <div class="col-12 col-lg-2 d-flex gap-2">
              <button type="button" class="btn btn-primary flex-fill" wire:click="saveSection">Save</button>
              <button type="button" class="btn btn-outline-secondary flex-fill" wire:click="cancelSectionEdit">Cancel</button>
            </div>
          </div>
        </div>
      @endif

      @if($editingResourceId)
        <div class="alert alert-primary mb-4">
          <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-4">
              <label class="form-label" for="library-edit-resource-title">Edit resource</label>
              <input id="library-edit-resource-title" type="text" class="form-control" wire:model.blur="editingResourceTitle">
              @error('editingResourceTitle') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 col-lg-4">
              <label class="form-label" for="library-edit-resource-description">Description</label>
              <input id="library-edit-resource-description" type="text" class="form-control" maxlength="300" wire:model.blur="editingResourceDescription">
            </div>
            @if($editingResourceKind === 'link')
              <div class="col-12 col-lg-4">
                <label class="form-label" for="library-edit-resource-url">Link URL</label>
                <input id="library-edit-resource-url" type="url" class="form-control" wire:model.blur="editingExternalUrl">
                @error('editingExternalUrl') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @error('external_url') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>
            @else
              <div class="col-12 col-lg-4">
                <label class="form-label" for="library-edit-resource-file">Replace file</label>
                <div
                  x-data="{ uploading: false, progress: 0 }"
                  x-on:livewire-upload-start="uploading = true; progress = 0"
                  x-on:livewire-upload-finish="uploading = false"
                  x-on:livewire-upload-error="uploading = false"
                  x-on:livewire-upload-progress="progress = $event.detail.progress"
                >
                  <input id="library-edit-resource-file" type="file" class="form-control" wire:model="editingResourceFile">
                  <div class="form-text">Leave empty to keep the current file. Maximum file size: 500 MB.</div>
                  <div class="mt-2" x-show="uploading" x-cloak>
                    <div class="d-flex justify-content-between small text-body-secondary mb-1">
                      <span>Uploading replacement file...</span>
                      <span x-text="progress + '%'"></span>
                    </div>
                    <div class="progress" style="height: .5rem;">
                      <div class="progress-bar" role="progressbar" x-bind:style="'width: ' + progress + '%'" aria-label="Replacement upload progress"></div>
                    </div>
                  </div>
                </div>
                @error('editingResourceFile') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @error('file') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>
            @endif
            <div class="col-12 d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-primary" wire:click="saveResource" wire:loading.attr="disabled" wire:target="saveResource,editingResourceFile">Save resource</button>
              <button type="button" class="btn btn-outline-secondary" wire:click="cancelResourceEdit">Cancel</button>
            </div>
          </div>
        </div>
      @endif

      <div class="row g-4">
        <div class="col-12 col-xl-5">
          <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
            <h6 class="mb-0">Folders</h6>
            <span class="badge bg-label-secondary">{{ $sections->count() }}</span>
          </div>

          <div class="d-flex flex-column gap-2">
            @forelse($sections as $section)
              <div class="library-row" wire:key="library-section-{{ $section->id }}">
                <div class="library-row-title">
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold text-truncate">{{ $section->title }}</span>
                    <span class="badge bg-label-{{ $section->isArchived() ? 'secondary' : 'success' }}">{{ $section->status }}</span>
                  </div>
                  @if($section->description)
                    <div class="small text-body-secondary text-truncate">{{ $section->description }}</div>
                  @endif
                </div>
                <div class="library-row-actions">
                  @if($section->isActive())
                    <button type="button" class="btn btn-sm btn-outline-primary" wire:click="enterSection({{ $section->id }})">
                      Open
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="editSection({{ $section->id }})">
                      Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning" wire:click="archiveSection({{ $section->id }})" wire:confirm="Archive this folder for new selection?">
                      Archive
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deleteSection({{ $section->id }})" wire:confirm="Delete this empty Library folder?">
                      Delete
                    </button>
                  @else
                    <button type="button" class="btn btn-sm btn-outline-success" wire:click="restoreSection({{ $section->id }})">
                      Restore
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deleteSection({{ $section->id }})" wire:confirm="Delete this empty Library folder?">
                      Delete
                    </button>
                  @endif
                  @error('section_delete_'.$section->id) <div class="text-danger small w-100 text-end">{{ $message }}</div> @enderror
                </div>
              </div>
            @empty
              <div class="text-body-secondary small border rounded p-3">No folders in this location yet.</div>
            @endforelse
          </div>
        </div>

        <div class="col-12 col-xl-7">
          <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
            <h6 class="mb-0">Resources</h6>
            <span class="badge bg-label-secondary">{{ $resources->count() }}</span>
          </div>

          <div class="d-flex flex-column gap-2">
            @forelse($resources as $resource)
              <div class="library-row" wire:key="library-resource-{{ $resource->id }}">
                <div class="library-row-title">
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold text-truncate">{{ $resource->title }}</span>
                    <span class="badge bg-label-primary">{{ $resource->resource_type }}</span>
                    <span class="badge bg-label-{{ $resource->isArchived() ? 'secondary' : 'success' }}">{{ $resource->status }}</span>
                  </div>
                  <div class="small text-body-secondary text-truncate">
                    {{ $resource->isFile() ? ($resource->original_filename ?? $resource->file_path) : $resource->external_url }}
                  </div>
                </div>
                <div class="library-row-actions">
                  @if($resource->isArchived())
                    <button type="button" class="btn btn-sm btn-outline-success" wire:click="restoreResource({{ $resource->id }})">
                      Restore
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deleteResource({{ $resource->id }})" wire:confirm="Delete this unused Library resource?">
                      Delete
                    </button>
                  @else
                    <a href="{{ route('teacher.library.resources.open', [
                         'resource' => $resource,
                         'return_to' => url('teacher/library?folder='.$resource->library_section_id),
                       ]) }}"
                       class="btn btn-sm btn-outline-primary"
                       target="_blank"
                       rel="noopener">
                      Open
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="editResource({{ $resource->id }})">
                      Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning" wire:click="archiveResource({{ $resource->id }})" wire:confirm="Archive this resource for new selection?">
                      Archive
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deleteResource({{ $resource->id }})" wire:confirm="Delete this unused Library resource?">
                      Delete
                    </button>
                  @endif
                  @error('resource_delete_'.$resource->id) <div class="text-danger small w-100 text-end">{{ $message }}</div> @enderror
                </div>
              </div>
            @empty
              <div class="text-body-secondary small border rounded p-3">No resources in this folder yet.</div>
            @endforelse
          </div>
        </div>
      </div>
      @endunless
      @endif
    @endif
  </div>
</div>
