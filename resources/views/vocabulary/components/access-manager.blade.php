@if ($accessManagerOpen)
  <div class="vm-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="accessManagerTitle" wire:key="access-manager-{{ $accessManagerInstance }}" x-data="{ visible: true }" x-show="visible" x-transition.opacity>
    <div class="vm-modal-panel" style="max-width: 72rem;">
      <div class="p-3 p-md-4 border-bottom vm-toolbar">
        <div>
          <h5 id="accessManagerTitle" class="mb-1">Manage game access</h5>
          <p class="mb-0 text-muted small">Tick the classes that should see this vocabulary folder or list.</p>
        </div>
        <div class="d-flex flex-wrap justify-content-end gap-2">
          <button class="btn btn-primary" type="button" wire:click="saveAccessManager">
            <i class="icon-base ti tabler-device-floppy me-1"></i>
            Save access
          </button>
          <button class="btn btn-outline-secondary" type="button" x-on:click="visible = false; $wire.closeAccessManager()">
            <i class="icon-base ti tabler-x me-1"></i>
            Close
          </button>
        </div>
      </div>

      <div class="p-3 p-md-4">
        <div class="input-group mb-3">
          <span class="input-group-text"><i class="icon-base ti tabler-search"></i></span>
          <input class="form-control" type="search" wire:model.live.debounce.250ms="accessClassSearch" placeholder="Search class">
        </div>
        <p class="text-muted small mb-3">Showing a focused slice of classes for speed. Search by class, grade, or subject to find more before saving.</p>

        @php
          $enabledRows = collect($bulkAccessRows)->filter(fn ($row) => (bool) ($accessClassSelections[$row['class_id']] ?? $row['enabled']));
          $disabledRows = collect($bulkAccessRows)->reject(fn ($row) => (bool) ($accessClassSelections[$row['class_id']] ?? $row['enabled']));
        @endphp

        <div class="row g-3">
          <div class="col-12 col-lg-6">
            <h6 class="mb-2">Have access</h6>
            <div class="vm-access-grid">
              @forelse ($enabledRows as $row)
                <label class="border rounded p-2 d-flex align-items-start gap-2" wire:key="access-enabled-{{ $row['class_id'] }}">
                  <input class="form-check-input mt-1" type="checkbox" wire:model="accessClassSelections.{{ $row['class_id'] }}" @checked((bool) ($accessClassSelections[$row['class_id']] ?? $row['enabled']))>
                  <span class="min-w-0">
                    <span class="d-flex flex-wrap align-items-center gap-1">
                      <span class="fw-semibold">{{ $row['label'] }}</span>
                      @if (($row['origin'] ?? '') === 'inherited')
                        <span class="badge bg-label-success">inherited</span>
                      @endif
                      @if ($row['customized'] ?? false)
                        <span class="badge bg-label-warning">customized lessons</span>
                      @endif
                    </span>
                    @if ($row['subject'] !== '')
                      <span class="small text-muted">{{ $row['subject'] }}</span>
                    @endif
                    @if (($row['origin_label'] ?? '') !== '')
                      <span class="d-block small text-muted">{{ $row['origin_label'] }}</span>
                    @endif
                  </span>
                </label>
              @empty
                <div class="text-muted small">No classes selected yet.</div>
              @endforelse
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <h6 class="mb-2">Does not have access</h6>
            <div class="vm-access-grid">
              @forelse ($disabledRows as $row)
                <label class="border rounded p-2 d-flex align-items-start gap-2" wire:key="access-disabled-{{ $row['class_id'] }}">
                  <input class="form-check-input mt-1" type="checkbox" wire:model="accessClassSelections.{{ $row['class_id'] }}" @checked((bool) ($accessClassSelections[$row['class_id']] ?? $row['enabled']))>
                  <span class="min-w-0">
                    <span class="d-flex flex-wrap align-items-center gap-1">
                      <span class="fw-semibold">{{ $row['label'] }}</span>
                      @if ($row['customized'] ?? false)
                        <span class="badge bg-label-warning">customized lessons</span>
                      @endif
                    </span>
                    @if ($row['subject'] !== '')
                      <span class="small text-muted">{{ $row['subject'] }}</span>
                    @endif
                    @if (($row['origin_label'] ?? '') !== '')
                      <span class="d-block small text-muted">{{ $row['origin_label'] }}</span>
                    @endif
                  </span>
                </label>
              @empty
                <div class="text-muted small">All visible classes have access.</div>
              @endforelse
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <button class="btn btn-outline-secondary" type="button" x-on:click="visible = false; $wire.closeAccessManager()">Cancel</button>
          <button class="btn btn-primary" type="button" wire:click="saveAccessManager">
            <i class="icon-base ti tabler-device-floppy me-1"></i>
            Save access
          </button>
        </div>
      </div>
    </div>
  </div>
@endif
