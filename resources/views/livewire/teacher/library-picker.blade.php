<div>
  @if($open)
    <div class="modal fade show d-block library-picker-modal" tabindex="-1" role="dialog" aria-modal="true" wire:keydown.escape="close">
      <style>
        .library-picker-modal {
          background: rgba(0,0,0,.5);
        }

        .library-picker-modal .modal-content {
          background: color-mix(in srgb, var(--bs-body-bg) 96%, var(--bs-primary));
        }

        .library-picker-modal .library-picker-workspace {
          max-width: 82rem;
          margin-inline: auto;
        }

        .library-picker-modal .library-picker-toolbar {
          position: sticky;
          top: 0;
          z-index: 2;
          padding-block: .75rem 1rem;
          background: color-mix(in srgb, var(--bs-body-bg) 96%, var(--bs-primary));
        }

        .library-picker-modal .library-picker-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(min(18rem, 100%), 1fr));
          gap: .75rem;
        }

        .library-picker-modal .library-picker-row {
          display: grid;
          grid-template-columns: auto minmax(0, 1fr);
          gap: .75rem;
          align-items: center;
          padding: .75rem;
          border: 1px solid var(--bs-border-color);
          border-radius: .5rem;
          background: var(--bs-paper-bg);
          min-height: 4.25rem;
        }

        .library-picker-modal .library-picker-row:hover {
          border-color: rgba(var(--bs-primary-rgb), .35);
        }

        .library-picker-modal .library-picker-title {
          min-width: 0;
        }

        .library-picker-modal .library-picker-actions {
          display: flex;
          flex-wrap: wrap;
          gap: .5rem;
          justify-content: flex-end;
        }

        .library-picker-modal .library-picker-selected {
          display: grid;
          gap: .75rem;
          margin-top: .75rem;
          padding: .875rem;
          border: 1px solid color-mix(in srgb, var(--bs-border-color) 72%, var(--bs-primary));
          border-radius: .75rem;
          background: color-mix(in srgb, var(--bs-paper-bg) 92%, var(--bs-primary));
        }

        .library-picker-modal .library-picker-selected-list {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(min(21rem, 100%), 1fr));
          gap: .5rem;
          min-width: 0;
        }

        .library-picker-modal .library-picker-selected-chip {
          display: flex;
          align-items: center;
          gap: .5rem;
          min-width: 0;
          width: 100%;
          max-width: 100%;
          padding: .45rem .55rem .45rem .75rem;
          border: 1px solid var(--bs-border-color);
          border-radius: .5rem;
          background: var(--bs-paper-bg);
        }

        .library-picker-modal .library-picker-selected-chip-body {
          flex: 1 1 auto;
          min-width: 0;
          overflow: hidden;
        }

        .library-picker-modal .library-picker-selected-chip .btn {
          flex: 0 0 auto;
        }

        @media (max-width: 576px) {
          .library-picker-modal .library-picker-row {
            grid-template-columns: 1fr;
          }

          .library-picker-modal .library-picker-actions,
          .library-picker-modal .library-picker-actions .btn {
            width: 100%;
          }
        }
      </style>

      <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title mb-0">Choose from Library</h5>
              <div class="small text-body-secondary">Open a folder, then tick the resources you want to attach.</div>
            </div>
            <button type="button" class="btn-close" wire:click="close"></button>
          </div>

          <div class="modal-body">
            <div class="library-picker-workspace">
              <div class="library-picker-toolbar">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                  <button type="button" class="btn btn-sm btn-outline-primary" wire:click="goToRoot">
                    <span>Root</span>
                  </button>
                  @foreach($breadcrumbs as $crumb)
                    <span class="text-body-secondary">/</span>
                    @if($crumb['id'] === null)
                      <span class="btn btn-sm btn-text-secondary disabled">{{ $crumb['title'] }}</span>
                    @else
                      <button type="button" class="btn btn-sm btn-text-secondary" wire:click="enterSection({{ $crumb['id'] }})">
                        {{ $crumb['title'] }}
                      </button>
                    @endif
                  @endforeach
                  @if($currentSection || $showLegacySources)
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" wire:click="goToParent">
                      <span>Back</span>
                    </button>
                  @endif
                </div>

                <label class="form-label" for="library-picker-search">Search resources</label>
                <input id="library-picker-search" type="search" class="form-control form-control-lg" wire:model.live.debounce.250ms="search" placeholder="Search title, file name, or link">

                @if($selectedItems)
                  <div class="library-picker-selected">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                      <div>
                        <div class="fw-semibold">Selected resources</div>
                        <div class="small text-body-secondary">{{ $selectedCount }} selected across Library folders</div>
                      </div>
                      <button type="button" class="btn btn-sm btn-text-secondary" wire:click="clearSelection">Clear</button>
                    </div>
                    <div class="library-picker-selected-list">
                      @foreach($selectedItems as $selectedItem)
                        <div class="library-picker-selected-chip" wire:key="picker-selected-{{ $selectedItem['id'] }}">
                          <div class="library-picker-selected-chip-body">
                            <div class="fw-semibold text-truncate">{{ $selectedItem['title'] }}</div>
                            <div class="small text-body-secondary text-truncate">{{ $selectedItem['context'] }} - {{ $selectedItem['meta'] }}</div>
                          </div>
                          <button type="button" class="btn btn-sm btn-icon btn-outline-danger" wire:click="removeSelection('{{ $selectedItem['id'] }}')" aria-label="Remove {{ $selectedItem['title'] }}">
                            <i class="ti tabler-x"></i>
                          </button>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endif
              </div>

              @if((blank($search) && ($sections->isNotEmpty() || $generalFolders->isNotEmpty() || (($legacyFolderAvailable || $vocabularyFolderAvailable) && ! $showLegacySources && $currentSection === null))) || $legacyTypes || $legacyCollections)
                <div class="mb-4">
                  <div class="small fw-semibold text-body-secondary mb-2">Folders</div>
                  <div class="library-picker-grid">
                    @if($vocabularyFolderAvailable && ! $showLegacySources && $currentSection === null)
                      <div class="library-picker-row" wire:key="picker-vocabulary-folder">
                        <span class="badge bg-label-primary">Folder</span>
                        <div class="library-picker-title">
                          <div class="fw-semibold text-truncate">Vocabulary</div>
                          <div class="small text-body-secondary text-truncate">Vocabulary folders, copied folders, and playable lists.</div>
                        </div>
                        <div class="library-picker-actions">
                          <button type="button" class="btn btn-sm btn-outline-primary" wire:click="enterVocabularySources">
                            Open
                          </button>
                        </div>
                      </div>
                    @endif
                    @if($legacyFolderAvailable && ! $showLegacySources && $currentSection === null)
                      <div class="library-picker-row" wire:key="picker-legacy-folder">
                        <span class="badge bg-label-info">Folder</span>
                        <div class="library-picker-title">
                          <div class="fw-semibold text-truncate">Legacy Library Sources</div>
                          <div class="small text-body-secondary text-truncate">Listen & Read, Peer Coach, videos, courses, and other existing Library pages.</div>
                        </div>
                        <div class="library-picker-actions">
                          <button type="button" class="btn btn-sm btn-outline-primary" wire:click="enterLegacySources">
                            Open
                          </button>
                        </div>
                      </div>
                    @endif
                    @foreach($sections as $section)
                      <div class="library-picker-row" wire:key="picker-section-{{ $section->id }}">
                        <span class="badge bg-label-primary">Folder</span>
                        <div class="library-picker-title">
                          <div class="fw-semibold text-truncate">{{ $section->title }}</div>
                          @if($section->description)
                            <div class="small text-body-secondary text-truncate">{{ $section->description }}</div>
                          @endif
                        </div>
                        <div class="library-picker-actions">
                          <button type="button" class="btn btn-sm btn-outline-primary" wire:click="enterSection({{ $section->id }})">
                            Open
                          </button>
                        </div>
                      </div>
                    @endforeach
                    @foreach($generalFolders as $folder)
                      <div class="library-picker-row" wire:key="picker-general-folder-{{ $folder->id }}">
                        <span class="badge bg-label-primary">Folder</span>
                        <div class="library-picker-title">
                          <div class="fw-semibold text-truncate">{{ $folder->title }}</div>
                          @if($folder->description)
                            <div class="small text-body-secondary text-truncate">{{ $folder->description }}</div>
                          @endif
                        </div>
                        <div class="library-picker-actions">
                          <button type="button" class="btn btn-sm btn-outline-primary" wire:click="enterGeneralFolder({{ $folder->id }})">
                            Open
                          </button>
                        </div>
                      </div>
                    @endforeach
                    @foreach($legacyTypes as $legacyType)
                      <div class="library-picker-row" wire:key="picker-legacy-type-{{ $legacyType['type'] }}">
                        <span class="badge bg-label-info">Folder</span>
                        <div class="library-picker-title">
                          <div class="fw-semibold text-truncate">{{ $legacyType['title'] }}</div>
                          <div class="small text-body-secondary text-truncate">{{ $legacyType['count'] }} sources</div>
                        </div>
                        <div class="library-picker-actions">
                          <button type="button" class="btn btn-sm btn-outline-primary" wire:click="enterLegacyCollectionType('{{ $legacyType['type'] }}')">
                            Open
                          </button>
                        </div>
                      </div>
                    @endforeach
                    @foreach($legacyCollections as $legacyCollection)
                      @php
                        $isVocabularyCollection = ($legacyCollection['type'] ?? '') === 'vocabulary';
                        $opensVocabularyFolder = $isVocabularyCollection && (int) ($legacyCollection['child_folder_count'] ?? 0) > 0;
                        $canChooseLegacyCollection = ! $opensVocabularyFolder && (bool) ($legacyCollection['selectable'] ?? true);
                      @endphp
                      <div class="library-picker-row" wire:key="picker-legacy-collection-{{ $legacyCollection['key'] }}">
                        <span class="badge {{ $isVocabularyCollection ? 'bg-label-primary' : 'bg-label-info' }}">Folder</span>
                        <div class="library-picker-title">
                          <div class="fw-semibold text-truncate">{{ $legacyCollection['title'] }}</div>
                          @if($legacyCollection['description'])
                            <div class="small text-body-secondary text-truncate">{{ $legacyCollection['description'] }}</div>
                          @endif
                        </div>
                        <div class="library-picker-actions">
                          @if($opensVocabularyFolder)
                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="enterLegacyVocabularyFolder({{ (int) $legacyCollection['id'] }})">
                              Open
                            </button>
                          @elseif($canChooseLegacyCollection)
                            <button type="button" class="btn btn-sm btn-outline-primary" wire:click="enterLegacyCollection('{{ $legacyCollection['key'] }}')">
                              Open
                            </button>
                          @else
                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                              Empty
                            </button>
                          @endif
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              @endif

              @if($resources->isNotEmpty() || $generalResources->isNotEmpty() || ! empty($legacyResources) || $currentSection || $legacyCollectionChosen || filled($search))
              <div>
                <div class="small fw-semibold text-body-secondary mb-2">Resources</div>
                <div class="library-picker-grid">
                  @foreach($resources as $resource)
                    <label class="library-picker-row" wire:key="picker-resource-{{ $resource->id }}" for="library-picker-resource-{{ $resource->id }}">
                      <input id="library-picker-resource-{{ $resource->id }}" class="form-check-input" type="checkbox" wire:model="selected.library__{{ $resource->id }}" aria-label="Select {{ $resource->title }}">
                      <span class="library-picker-title">
                        <div class="d-flex align-items-center gap-2">
                          <span class="fw-semibold text-truncate">{{ $resource->title }}</span>
                          <span class="badge bg-label-primary">{{ $resource->resource_type }}</span>
                        </div>
                        <div class="small text-body-secondary text-truncate">
                          {{ $resource->section?->title ?? 'Library' }}
                        </div>
                      </span>
                    </label>
                  @endforeach

                  @foreach($generalResources as $resource)
                    @php($generalSelectionId = 'general__'.$resource->id)
                    <label class="library-picker-row" wire:key="picker-general-resource-{{ $resource->id }}" for="library-picker-general-resource-{{ $resource->id }}">
                      <input id="library-picker-general-resource-{{ $resource->id }}" class="form-check-input" type="checkbox" wire:model="selected.{{ $generalSelectionId }}" aria-label="Select {{ $resource->title }}">
                      <span class="library-picker-title">
                        <div class="d-flex align-items-center gap-2">
                          <span class="fw-semibold text-truncate">{{ $resource->title }}</span>
                          <span class="badge bg-label-primary">{{ $resource->resource_type }}</span>
                        </div>
                        <div class="small text-body-secondary text-truncate">
                          {{ $resource->folder?->title ?? 'Shared Library' }}
                        </div>
                      </span>
                    </label>
                  @endforeach

                  @foreach($legacyResources as $legacyResource)
                    <label class="library-picker-row" wire:key="picker-resource-{{ $legacyResource['id'] }}" for="library-picker-resource-{{ $legacyResource['id'] }}">
                      <input id="library-picker-resource-{{ $legacyResource['id'] }}" class="form-check-input" type="checkbox" wire:model="selected.{{ $legacyResource['id'] }}" aria-label="Select {{ $legacyResource['title'] }}">
                      <span class="library-picker-title">
                        <div class="d-flex align-items-center gap-2">
                          <span class="fw-semibold text-truncate">{{ $legacyResource['title'] }}</span>
                          <span class="badge bg-label-info">
                            {{ ($legacyResource['source_type'] ?? '') === 'vocabulary_list' ? 'Vocab Games lesson' : str_replace('_', ' ', $legacyResource['source_type']) }}
                          </span>
                        </div>
                        <div class="small text-body-secondary text-truncate">
                          {{ $legacyResource['description'] ?? $legacyResource['url'] }}
                        </div>
                      </span>
                    </label>
                  @endforeach

                  @if($resources->isEmpty() && $generalResources->isEmpty() && empty($legacyResources))
                    <div class="text-body-secondary small border rounded p-3 bg-body">No active resources found here.</div>
                  @endif
                </div>
              </div>
              @endif
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-text-secondary me-auto" wire:click="clearSelection">Clear selection</button>
            <button type="button" class="btn btn-outline-secondary" wire:click="close">Cancel</button>
            <button type="button" class="btn btn-primary" wire:click="applySelection">
              Attach selected
            </button>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>
