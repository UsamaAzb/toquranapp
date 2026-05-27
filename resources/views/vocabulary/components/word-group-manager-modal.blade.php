@if ($wordGroupManagerOpen)
  <div class="vm-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="wordGroupManagerTitle">
    <div class="vm-modal-panel" style="max-width: 46rem;">
      <div class="p-3 p-md-4 border-bottom vm-toolbar">
        <div>
          <h5 id="wordGroupManagerTitle" class="mb-1">Manage word groups</h5>
          <p class="mb-0 text-muted small">Add a new group or rename an existing one.</p>
        </div>
        <button class="btn btn-outline-secondary" type="button" wire:click="$set('wordGroupManagerOpen', false)">
          <i class="icon-base ti tabler-x me-1"></i>
          Close
        </button>
      </div>

      <div class="p-3 p-md-4">
        <div class="row g-2 align-items-end mb-3">
          <div class="col-md-8">
            <label class="form-label" for="groupEditorName">Group title</label>
            <input id="groupEditorName" class="form-control" type="text" wire:model.blur="groupEditorName" placeholder="Fruits">
            @error('groupEditorName') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-4 d-grid">
            <button class="btn btn-primary" type="button" wire:click="saveWordGroupCategory">
              <i class="icon-base ti tabler-save me-1"></i>
              Save group
            </button>
          </div>
        </div>

        <div class="row g-2">
          @forelse ($wordGroupOptions as $groupOption)
            <div class="col-12 col-sm-6" wire:key="manage-word-group-{{ $groupOption->id }}">
              <button class="btn btn-outline-secondary w-100 text-start" type="button" wire:click="openWordGroupManager({{ $groupOption->id }})">
                <i class="icon-base ti tabler-category-2 me-1"></i>
                {{ $groupOption->name }}
              </button>
            </div>
          @empty
            <div class="col-12">
              <div class="alert alert-info mb-0">No word groups yet.</div>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
@endif
