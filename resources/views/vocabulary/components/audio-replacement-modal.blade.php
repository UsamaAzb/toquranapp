@if ($audioReplacementOpen)
  <div class="vm-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="audioReplacementTitle">
    <div class="vm-modal-panel" style="max-width: 42rem;">
      <div class="p-3 p-md-4 border-bottom vm-toolbar">
        <div>
          <h5 id="audioReplacementTitle" class="mb-1">Replace word audio</h5>
          <p class="mb-0 text-muted small">Use a Cambridge media path, a complete public audio URL, or upload an audio file.</p>
        </div>
        <button class="btn btn-outline-secondary" type="button" wire:click="$set('audioReplacementOpen', false)">
          <i class="icon-base ti tabler-x me-1"></i>
          Close
        </button>
      </div>

      <div class="p-3 p-md-4">
        <label class="form-label" for="audioInputMode">Audio source</label>
        <select id="audioInputMode" class="form-select mb-3" wire:model.live="audioInputMode">
          <option value="cambridge">Cambridge media path</option>
          <option value="url">Complete audio URL</option>
          <option value="upload">Upload audio</option>
        </select>

        @if ($audioInputMode === 'cambridge')
          <label class="form-label" for="audioPartialPath">Cambridge media path</label>
          <input id="audioPartialPath" class="form-control" type="text" wire:model.blur="audioPartialPath" placeholder="/us/media/learner-english/us_pron/f/fin/finis/finish.mp3">
          @error('audioPartialPath') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        @elseif ($audioInputMode === 'url')
          <label class="form-label" for="audioCompleteUrl">Complete audio URL</label>
          <input id="audioCompleteUrl" class="form-control" type="url" wire:model.blur="audioCompleteUrl" placeholder="https://dictionary.cambridge.org/us/media/.../word.mp3">
          @error('audioCompleteUrl') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        @else
          <label class="form-label" for="audioUpload">Upload audio</label>
          <input id="audioUpload" class="form-control" type="file" accept="audio/*,.mp3,.mpeg,.mpga,.ogg,.oga,.wav,.m4a,.aac" wire:model="audioUpload">
          @error('audioUpload') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        @endif

        <div class="d-flex flex-wrap gap-2 justify-content-end mt-4">
          <button class="btn btn-outline-secondary" type="button" wire:click="$set('audioReplacementOpen', false)">Cancel</button>
          <button class="btn btn-warning" type="button" wire:click="replaceAudio" wire:loading.attr="disabled">
            <i class="icon-base ti tabler-download me-1"></i>
            Replace audio
          </button>
        </div>
      </div>
    </div>
  </div>
@endif
