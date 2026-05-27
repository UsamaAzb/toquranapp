@if ($imageEditorOpen)
  <div class="vm-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="wordImageTitle">
    <div class="vm-modal-panel" style="max-width: 38rem;">
      <div class="p-3 p-md-4 border-bottom vm-toolbar">
        <div>
          <h5 id="wordImageTitle" class="mb-1">Word image</h5>
          <p class="mb-0 text-muted small">Upload or replace the picture for this vocabulary word.</p>
        </div>
        <button class="btn btn-outline-secondary" type="button" wire:click="$set('imageEditorOpen', false)">
          <i class="icon-base ti tabler-x me-1"></i>
          Close
        </button>
      </div>

      <div class="p-3 p-md-4">
        @if ($imagePreviewUrl)
          <div class="border rounded p-3 mb-3 text-center">
            <img src="{{ $imagePreviewUrl }}" alt="" style="max-width: 100%; max-height: 16rem; object-fit: contain;">
            <div class="mt-3">
              <button class="btn btn-outline-danger btn-sm" type="button" wire:click="removeCurrentWordImage">
                <i class="icon-base ti tabler-trash me-1"></i>
                Remove image
              </button>
            </div>
          </div>
        @endif

        <label class="form-label" for="wordImageUpload">Image file</label>
        <input id="wordImageUpload" class="form-control" type="file" accept="image/*" wire:model="wordImageUpload">
        <div class="text-muted small mt-2" wire:loading wire:target="wordImageUpload">
          Uploading image...
        </div>
        @error('wordImageUpload') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

        <div class="d-flex flex-wrap gap-2 justify-content-end mt-4">
          <button class="btn btn-outline-secondary" type="button" wire:click="$set('imageEditorOpen', false)">Cancel</button>
          <button class="btn btn-primary" type="button" wire:click="saveWordImage" wire:loading.attr="disabled" wire:target="wordImageUpload,saveWordImage">
            <i class="icon-base ti tabler-upload me-1"></i>
            Save image
          </button>
        </div>
      </div>
    </div>
  </div>
@endif
