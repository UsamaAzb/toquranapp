@if ($setCreatorOpen)
  <div class="vm-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="setCreatorTitle">
    <div class="vm-modal-panel" style="max-width: 42rem;">
      <div class="p-3 p-md-4 border-bottom vm-toolbar">
        <div>
          <h5 id="setCreatorTitle" class="mb-1">Add vocabulary folder</h5>
          <p class="mb-0 text-muted small">Create a course folder or a playable lesson/list.</p>
        </div>
        <button class="btn btn-outline-secondary" type="button" wire:click="$set('setCreatorOpen', false)">
          <i class="icon-base ti tabler-x me-1"></i>
          Close
        </button>
      </div>
      <div class="p-3 p-md-4">
        <label class="form-label" for="modalSetTitle">Title</label>
        <input id="modalSetTitle" class="form-control mb-2" type="text" wire:model.blur="setTitle" placeholder="Happy words">
        @error('setTitle') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

        <label class="form-label" for="modalSetDescription">Description</label>
        <textarea id="modalSetDescription" class="form-control mb-2" rows="2" wire:model.blur="setDescription" placeholder="Optional note"></textarea>

        <div class="row g-2">
          <div class="{{ $setParentId ? 'col-md-4' : 'col-md-6' }}">
            <label class="form-label" for="modalSetNodeType">Type</label>
            <select id="modalSetNodeType" class="form-select" wire:model.live="setNodeType">
              <option value="folder">Folder</option>
              <option value="playable">Playable list / lesson</option>
            </select>
            @error('setNodeType') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>
          @if ($setParentId)
            <div class="col-md-4">
              <label class="form-label">Location</label>
              <div class="form-control bg-label-secondary">
                {{ collect($editableFolderOptions)->firstWhere('id', (int) $setParentId)['title'] ?? 'Current folder' }}
              </div>
              @error('setParentId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
          @endif
          <div class="{{ $setParentId ? 'col-md-4' : 'col-md-6' }}">
            <label class="form-label" for="modalSetVisibility">Visibility</label>
            <select id="modalSetVisibility" class="form-select" wire:model="setVisibility">
              <option value="private">Private</option>
              <option value="shared">Shared with teachers</option>
            </select>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <button class="btn btn-outline-secondary" type="button" wire:click="$set('setCreatorOpen', false)">Cancel</button>
          <button class="btn btn-primary" type="button" wire:click="createSet">
            <i class="icon-base ti tabler-folder-plus me-1"></i>
            Create
          </button>
        </div>
      </div>
    </div>
  </div>
@endif
