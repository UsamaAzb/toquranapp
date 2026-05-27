@if ($wrongOptionsOpen)
  <div class="vm-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="wrongOptionsTitle">
    <div class="vm-modal-panel" style="max-width: 42rem;">
      <div class="p-3 p-md-4 border-bottom vm-toolbar">
        <div>
          <h5 id="wrongOptionsTitle" class="mb-1">Wrong spelling suggestions</h5>
          <p class="mb-0 text-muted small">Add one option per line, or separate options with commas, semicolons, or pipes.</p>
        </div>
        <button class="btn btn-outline-secondary" type="button" wire:click="$set('wrongOptionsOpen', false)">
          <i class="icon-base ti tabler-x me-1"></i>
          Close
        </button>
      </div>

      <div class="p-3 p-md-4">
        <label class="form-label" for="wrongOptionsText">Suggested mistakes</label>
        <textarea id="wrongOptionsText" class="form-control" rows="8" wire:model.blur="wrongOptionsText" placeholder="kar&#10;cer"></textarea>
        @error('wrongOptionsText') <div class="text-danger small mt-2">{{ $message }}</div> @enderror

        <div class="d-flex flex-wrap gap-2 justify-content-end mt-4">
          <button class="btn btn-outline-secondary" type="button" wire:click="$set('wrongOptionsOpen', false)">Cancel</button>
          <button class="btn btn-primary" type="button" wire:click="saveWrongOptions">
            <i class="icon-base ti tabler-save me-1"></i>
            Save suggestions
          </button>
        </div>
      </div>
    </div>
  </div>
@endif
