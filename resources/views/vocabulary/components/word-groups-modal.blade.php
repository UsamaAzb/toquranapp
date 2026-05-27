@if ($groupEditorOpen)
  <div class="vm-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="wordGroupsTitle">
    <div class="vm-modal-panel" style="max-width: 42rem;">
      <div class="p-3 p-md-4 border-bottom vm-toolbar">
        <div>
          <h5 id="wordGroupsTitle" class="mb-1">Word groups</h5>
          <p class="mb-0 text-muted small">Choose every topic group this word belongs to, such as fruits, colors, or animals.</p>
        </div>
        <button class="btn btn-outline-secondary" type="button" wire:click="$set('groupEditorOpen', false)">
          <i class="icon-base ti tabler-x me-1"></i>
          Close
        </button>
      </div>

      <div class="p-3 p-md-4">
        @if (($wordGroupOptions ?? collect())->isEmpty())
          <div class="alert alert-warning mb-0">No word groups are available yet.</div>
        @else
          <div class="row g-2">
            @foreach ($wordGroupOptions as $groupOption)
              <div class="col-12 col-sm-6 col-lg-4" wire:key="word-group-option-{{ $groupOption->id }}">
                <label class="border rounded p-2 d-flex align-items-center gap-2 h-100">
                  <input class="form-check-input m-0" type="checkbox" wire:model="wordGroupIds" value="{{ $groupOption->id }}">
                  <span class="fw-semibold">{{ $groupOption->name }}</span>
                </label>
              </div>
            @endforeach
          </div>
          @error('wordGroupIds') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
          @error('wordGroupIds.*') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
        @endif

        <div class="d-flex flex-wrap gap-2 justify-content-end mt-4">
          <button class="btn btn-outline-secondary" type="button" wire:click="$set('groupEditorOpen', false)">Cancel</button>
          <button class="btn btn-primary" type="button" wire:click="saveWordGroups" wire:loading.attr="disabled">
            <i class="icon-base ti tabler-save me-1"></i>
            Save groups
          </button>
        </div>
      </div>
    </div>
  </div>
@endif
