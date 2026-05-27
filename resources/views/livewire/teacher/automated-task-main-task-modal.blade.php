<div>
  <div class="modal w14-task-attachment-modal @if($show) show @endif" tabindex="-1"
       style="display: {{ $show ? 'block' : 'none' }}; background: rgba(0,0,0,0.5);"
       aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">
            {{ $mainTaskId ? 'Edit Task' : 'Add Main Task' }}
          </h5>
          <button type="button" class="btn-close" wire:click="close"
                  wire:loading.attr="disabled" wire:target="save,addLink,addYoutube,files"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Type</label>
            <select class="form-select" wire:model.live="taskTypeId">
              @foreach($taskTypes as $t)
                <option value="{{ $t['id'] }}">{{ $t['title'] }}</option>
              @endforeach
            </select>
            @error('taskTypeId') <div class="text-danger small">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" wire:model="title">
            @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" rows="3" wire:model="description"
                      placeholder="Optional base description. Versions can override this later."></textarea>
            @error('description') <div class="text-danger small">{{ $message }}</div> @enderror
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Default Points</label>
              <input type="number" min="0" class="form-control" wire:model="defaultPoints">
              @error('defaultPoints') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Max Points</label>
              <input type="number" min="0" class="form-control" wire:model="maxPoints">
              @error('maxPoints') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
          </div>

          <hr>
          <h5 class="mt-3">Attachments</h5>

          @include('livewire.teacher.partials.task-file-dropzone', [
            'inputId' => 'automatedTaskFilesInput',
            'resetKey' => ($show ? 'open' : 'closed').'-'.($mainTaskId ? 'edit-'.$mainTaskId : 'create-'.($templateId ?? 'new')),
            'uploaderScope' => 'automated-task-uploader',
            'fileAcceptAttribute' => $taskFileAcceptAttribute ?? '',
            'allowedFileExtensions' => $taskFileAllowedExtensions ?? [],
            'maxFileBytes' => $taskFileMaxBytes ?? 0,
            'pendingReorderEnabled' => true,
            'useUnifiedAttachmentTray' => true,
            'draftReorderEnabled' => false,
            'draftAttachmentItems' => $this->attachmentDraftItems,
            'libraryPickerEnabled' => true,
            'libraryPickerCanOpen' => ! empty($templateId),
            'showExternalAttachmentRows' => true,
            'busyTargets' => 'files,save,addLink,addYoutube',
            'locked' => $locked,
          ])
        </div>

        <div class="modal-footer">
          @if($uploadsInProgress)
            <div class="small text-muted me-auto">Wait for uploads to finish before saving.</div>
          @endif
          <button type="button" class="btn btn-text-secondary" wire:click="close"
                  wire:loading.attr="disabled"
                  wire:target="save,addLink,addYoutube,files">Close</button>
          <button type="button" class="btn btn-primary" wire:click="save"
                  wire:loading.attr="disabled"
                  wire:target="save,addLink,addYoutube,files"
                  @disabled($uploadsInProgress)>
            <span wire:loading.remove wire:target="save">{{ $mainTaskId ? 'Update Task' : 'Save Task' }}</span>
            <span wire:loading wire:target="save"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...</span>
          </button>
        </div>

      </div>
    </div>
  </div>

  <livewire:teacher.library-picker wire:key="automated-task-library-picker" />
</div>
