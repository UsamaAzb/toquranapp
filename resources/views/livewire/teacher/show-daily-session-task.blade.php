<div>
    @php use Illuminate\Support\Str; @endphp

  <div class="modal w14-task-attachment-modal @if($show) show @endif" tabindex="-1"
       style="display: {{ $show ? 'block' : 'none' }}; background: rgba(0,0,0,0.5);"
       aria-modal="true" role="dialog">
      
      
      
      
    <div class="modal-dialog modal-lg modal-dialog-centered  modal-dialog-scrollable">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">
            {{ $isEdit ? 'Edit Task' : 'Add Task to Automated Task Set #' . $dailySessionId }}
          </h5>
          <button type="button" class="btn-close" wire:click="$set('show', false)"
                  wire:loading.attr="disabled" wire:target="save,updateTask,addLink,addYoutube,files"></button>
        </div>

        <div class="modal-body">
          {{-- النوع من DB --}}
          <div class="mb-3">
            <label class="form-label">Type</label>
            <select class="form-select"
          wire:model.live="task_type_id"      @disabled($locked)>
    <option value="">Select</option>
    @foreach($taskTypes as $t)
      <option value="{{ $t['id'] }}">{{ $t['title'] }}</option>
    @endforeach
  </select>
            @error('task_type_id') <div class="text-danger small">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" wire:model.defer="title" @disabled($locked)>
            @error('title') <div class="text-danger small">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" rows="3" wire:model.defer="description" @disabled($locked)></textarea>
            @error('description') <div class="text-danger small">{{ $message }}</div> @enderror
          </div>
          @error('content') <div class="text-danger small mb-3">{{ $message }}</div> @enderror

          {{-- النقاط --}}
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Default Points</label>
              <input type="number" min="0" class="form-control" wire:model="default_points" @disabled($locked)>
              @error('default_points') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Max Points</label>
              <input type="number" min="0" class="form-control" wire:model="max_points" @disabled($locked)>
              @error('max_points') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
          </div>




{{--          @if($this->isFileType)
      <div class="mb-3 mt-3">
        <label class="form-label">Files</label>
        <input type="file" class="form-control" wire:model="files" multiple @disabled($locked)>
        @error('files') <div class="text-danger small">{{ $message }}</div> @enderror
        @error('files.*') <div class="text-danger small">{{ $message }}</div> @enderror
        <div wire:loading wire:target="files" class="small text-muted mt-1">Uploading...</div>
        
        
      </div>
    @endif
    
    
    @if($isEdit && count($existingFiles))
  <div class="mt-2">
    <div class="small text-muted mb-1">Existing files:</div>
    <ul class="list-unstyled">
      @foreach($existingFiles as $f)
        <li class="d-flex align-items-center gap-2 mb-1">
          <i class="icon-base  ti tabler-file-description"></i>
          <a href="{{ $f['url'] }}" target="_blank" class="break_text">{{ $f['title'] }}</a>
          @if(!empty($f['size']))
            <span class="text-muted small">
              ({{ number_format($f['size']/1024, 1) }} KB)
            </span>
          @endif
        </li>
      @endforeach
    </ul>
  </div>
@endif

    
    @if($this->isLinkType)
    
    <div class="mb-3 mt-3">
        <label class="form-label">Link Title</label>
        <input type="text" class="form-control"  wire:model.defer="attach_title" @disabled($locked)>
        @error('attach_title') <div class="text-danger small">{{ $message }}</div> @enderror
      </div>
    
    
      <div class="mb-3 mt-3">
        <label class="form-label">Link URL</label>
        <input type="url" class="form-control" placeholder="https://example.com/page" wire:model.defer="link" @disabled($locked)>
        @error('link') <div class="text-danger small">{{ $message }}</div> @enderror
      </div>
    @endif

    @if($this->isYoutubeType)
    
    
       <div class="mb-3 mt-3">
        <label class="form-label">Youtube title</label>
        <input type="text" class="form-control"  wire:model.defer="attach_title" @disabled($locked)>
        @error('link') <div class="text-danger small">{{ $message }}</div> @enderror
      </div>
    
      <div class="mb-3 mt-3">
        <label class="form-label">YouTube URL</label>
        <input type="url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." wire:model.defer="youtube" @disabled($locked)>
        @error('youtube') <div class="text-danger small">{{ $message }}</div> @enderror
      </div>
    @endif
--}}


<hr>
<h5 class="mt-3">Attachments</h5>

@include('livewire.teacher.partials.task-file-dropzone', [
  'inputId' => 'dailyTaskFilesInput',
  'resetKey' => ($show ? 'open' : 'closed').'-'.($isEdit ? 'edit-'.$taskId : 'create-'.$dailySessionId),
  'uploaderScope' => 'daily-task-uploader',
])

<div class="mt-3">
  @include('livewire.teacher.partials.task-external-attachment-rows', [
    'linkTitleModel' => 'link_title_input',
    'linkUrlModel' => 'link_url_input',
    'youtubeTitleModel' => 'youtube_title_input',
    'youtubeUrlModel' => 'youtube_url_input',
    'busyTargets' => 'files,save,updateTask,addLink,addYoutube',
    'locked' => $locked,
  ])
</div>









        </div>

        <div class="modal-footer">
          @if($uploadsInProgress)
            <div class="small text-muted me-auto">Wait for uploads to finish before saving.</div>
          @endif
          <button type="button" class="btn btn-text-secondary" wire:click="$set('show', false)"
                  wire:loading.attr="disabled" wire:target="save,updateTask,addLink,addYoutube,files">Close</button>
          <button type="button" class="btn btn-primary" wire:click="{{ $isEdit ? 'updateTask' : 'save' }}"
                  wire:loading.attr="disabled" wire:target="save,updateTask,addLink,addYoutube,files"
                  @disabled($locked || $uploadsInProgress)>

           {{ $isEdit ? 'Update Task' : 'Save Task' }}
          </button>
        </div>

      </div>
    </div>
    
    
    
  </div>
</div>
