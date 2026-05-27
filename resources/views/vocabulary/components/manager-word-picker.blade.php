@if ($wordPickerOpen)
  @php
    $pickerCanAddToSelected = $selectedSet
      && $selectedSet->source_kind === \App\Models\VocabularySet::SOURCE_CUSTOM
      && $selectedSet->isPlayable()
      && ((int) $selectedSet->owner_user_id === (int) auth()->id() || auth()->user()?->hasAnyRole(['admin', 'super_admin', 'owner']));
  @endphp
  <div class="vm-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="wordPickerTitle">
    <div class="vm-modal-panel">
      <div class="p-3 p-md-4 border-bottom vm-toolbar">
        <div>
          <h5 id="wordPickerTitle" class="mb-1">Select vocabulary words</h5>
          <p class="mb-0 text-muted small">
            @if ($pickerCanAddToSelected)
              Search the vocabulary bank, tick the words you want in this list, then save once.
            @else
              Search the existing vocabulary table or add a new word with audio.
            @endif
          </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
          @if ($pickerCanAddToSelected)
            <button class="btn btn-primary" type="button" wire:click="savePickerSelectedWords">
              <i class="icon-base ti tabler-plus me-1"></i>
              Add selected
            </button>
          @endif
          <button class="btn btn-outline-secondary" type="button" wire:click="closeWordPicker">
            <i class="icon-base ti tabler-x me-1"></i>
            Close
          </button>
        </div>
      </div>

      <div class="p-3 p-md-4">
        <div class="input-group mb-3">
          <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
          <input class="form-control" type="search" wire:model.live.debounce.350ms="wordSearch" placeholder="Search the vocabulary bank">
        </div>

        @if ($newWordFeedback)
          <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="status">
            <i class="icon-base ti tabler-circle-check"></i>
            <span>{{ $newWordFeedback }}</span>
          </div>
        @endif

        @if (trim($wordSearch) === '')
          <div class="alert alert-info mb-3">Type at least part of a word to search the vocabulary bank.</div>
        @else
          <div class="d-grid gap-2 mb-3">
            @forelse ($searchResults as $row)
              <div class="vm-picker-word" wire:key="search-word-{{ $row['id'] }}">
                @if ($pickerCanAddToSelected)
                  <label class="d-flex align-items-center gap-3 flex-grow-1 mb-0">
                    <input class="form-check-input m-0" type="checkbox" wire:model="pickerSelectedWordIds.{{ $row['id'] }}">
                    <span class="min-w-0">
                      <span class="fw-semibold d-block vm-word-title">{{ $row['word'] }}</span>
                      <span class="small {{ ($pickerSelectedWordIds[$row['id']] ?? false) ? 'text-primary' : 'text-muted' }}">
                        {{ ($pickerSelectedWordIds[$row['id']] ?? false) ? 'Selected for this list' : 'Available' }}
                      </span>
                    </span>
                  </label>
                @else
                  <div class="min-w-0 flex-grow-1">
                    <div class="fw-semibold vm-word-title">{{ $row['word'] }}</div>
                    <span class="badge {{ $row['audio']['available'] ? 'bg-label-success text-success' : 'bg-label-warning text-warning' }}">
                      {{ $row['audio']['available'] ? str_replace('_', ' ', $row['audio']['source']) : 'No audio yet' }}
                    </span>
                  </div>
                  <div class="small text-muted text-end">
                    Already in word bank
                  </div>
                @endif
              </div>
            @empty
              <div class="alert alert-warning mb-0">No vocabulary words match this search.</div>
            @endforelse
          </div>
        @endif

        @if ($canReplaceAudio && ! $pickerCanAddToSelected)
          <div class="border rounded p-3 bg-label-warning">
            <div class="fw-semibold mb-1">Add a new vocabulary word</div>
            <div class="small text-muted mb-3">Use this only when the word is not already in the vocabulary bank.</div>
            <div class="row g-2">
              <div class="col-md-3">
                <label class="form-label" for="newWordText">Word</label>
                <input id="newWordText" class="form-control" type="text" wire:model.blur="newWordText" placeholder="finish">
                @error('newWordText') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-3">
                <label class="form-label" for="newWordAudioMode">Audio source</label>
                <select id="newWordAudioMode" class="form-select" wire:model.live="newWordAudioMode" @disabled($newWordWithoutAudio)>
                  <option value="cambridge">Cambridge path</option>
                  <option value="url">Complete URL</option>
                  <option value="upload">Upload audio</option>
                </select>
              </div>
              <div class="col-md-4">
                @if ($newWordWithoutAudio)
                  <label class="form-label">Sound</label>
                  <div class="form-control bg-label-secondary">No sound yet</div>
                @elseif ($newWordAudioMode === 'cambridge')
                  <label class="form-label" for="newWordAudioPartialPath">Cambridge media path</label>
                  <input id="newWordAudioPartialPath" class="form-control" type="text" wire:model.blur="newWordAudioPartialPath" placeholder="/us/media/learner-english/us_pron/f/fin/finis/finish.mp3">
                  @error('newWordAudioPartialPath') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @elseif ($newWordAudioMode === 'url')
                  <label class="form-label" for="newWordAudioCompleteUrl">Complete audio URL</label>
                  <input id="newWordAudioCompleteUrl" class="form-control" type="url" wire:model.blur="newWordAudioCompleteUrl" placeholder="https://dictionary.cambridge.org/.../word.mp3">
                  @error('newWordAudioCompleteUrl') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @else
                  <label class="form-label" for="newWordAudioUpload">Upload audio</label>
                  <input id="newWordAudioUpload" class="form-control" type="file" accept="audio/*,.mp3,.mpeg,.mpga,.ogg,.oga,.wav,.m4a,.aac" wire:model="newWordAudioUpload">
                  @error('newWordAudioUpload') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @endif
              </div>
              <div class="col-md-2 d-flex flex-column justify-content-end gap-2">
                <div class="form-check">
                  <input id="newWordWithoutAudio" class="form-check-input" type="checkbox" wire:model.live="newWordWithoutAudio">
                  <label class="form-check-label small" for="newWordWithoutAudio">No sound yet</label>
                </div>
                <button class="btn btn-warning w-100" type="button" wire:click="createWordWithAudio" wire:loading.attr="disabled" wire:target="createWordWithAudio,newWordAudioUpload">
                  <span wire:loading.remove wire:target="createWordWithAudio">Add word</span>
                  <span wire:loading wire:target="createWordWithAudio">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Adding...
                  </span>
                </button>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
@endif
