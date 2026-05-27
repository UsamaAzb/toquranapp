<div class="atask-version-task-panel" wire:key="version-task-row-{{ $version->id }}-{{ $mainTask->id }}">
  <div class="atask-version-task-header">
    <div class="atask-version-task-shell">
      <div class="atask-version-task-main">
        <div class="atask-version-task-title-line mb-2">
          <div class="form-check atask-version-task-check">
            <input
              class="form-check-input"
              type="checkbox"
              id="version-{{ $version->id }}-task-{{ $mainTask->id }}"
              wire:model.live="versionTaskForms.{{ $version->id }}.{{ $mainTask->id }}.enabled">
            <label class="form-check-label fw-semibold text-truncate d-inline-block" for="version-{{ $version->id }}-task-{{ $mainTask->id }}" title="{{ $mainTask->title }}" style="max-width: min(100%, 26rem);">
              {{ $mainTask->title }}
            </label>
          </div>
        @if($form['enabled'] ?? false)
          @include('livewire.teacher.automated-task-validation-badge', [
            'passes' => $diagnosis['passes'] ?? false,
            'messages' => $diagnosis['errors'] ?? [],
            'bare' => true,
          ])
        @endif
        @if($mainTask->taskType)
          <span class="badge bg-label-info rounded-pill">{{ $mainTask->taskType->title }}</span>
        @endif
      </div>

      @if(filled($mainTask->description))
        <p class="text-muted mb-2 atask-task-description">{{ $mainTask->description }}</p>
      @endif

      @if($mainTask->attachments->isNotEmpty())
        <div class="d-flex flex-wrap gap-2 mb-2">
          @foreach($mainTask->attachments as $attachment)
            <x-sessions.attachment-chip
              wire:key="version-{{ $version->id }}-main-task-{{ $mainTask->id }}-attachment-{{ $attachment->id }}"
              :attachment="[
                'id' => $attachment->id,
                'type' => $attachment->type,
                'name' => $attachment->title ?: 'Attachment',
                'path' => $attachment->path ?? $attachment->url ?? '',
                'url' => $attachment->isFile()
                  ? \Illuminate\Support\Facades\Storage::disk('public')->url((string) $attachment->path)
                  : (string) ($attachment->url ?? $attachment->path),
              ]"
              :template-id="$template->id"
              :variant-index="$loop->index" />
          @endforeach
        </div>
      @endif
    </div>

    <div class="atask-version-task-action">
      @php($saveKey = $version->id.':'.$mainTask->id)
      @if(isset($savedVersionTaskKeys[$saveKey]))
        <span class="automation-inline-signal automation-inline-signal--success me-2" title="Saved" aria-label="Saved">
          <i class="icon-base ti tabler-circle-check"></i>
        </span>
      @endif
      <button
        type="button"
        class="btn btn-sm btn-outline-primary rounded-pill text-nowrap"
        wire:click="saveVersionTask({{ $version->id }}, {{ $mainTask->id }})"
        wire:loading.attr="disabled"
        wire:target="saveVersionTask({{ $version->id }}, {{ $mainTask->id }})">
        <span wire:loading.remove wire:target="saveVersionTask({{ $version->id }}, {{ $mainTask->id }})">Save</span>
        <span wire:loading wire:target="saveVersionTask({{ $version->id }}, {{ $mainTask->id }})">
          <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
          Saving...
        </span>
      </button>
    </div>
  </div>

  @if($form['enabled'] ?? false)
    <div class="row g-3 mt-1">
      <div class="col-lg-3">
        <label class="form-label">Points</label>
        <div class="text-muted small pt-2">
          Default {{ (int) ($mainTask->default_points ?? 0) }} / Max {{ (int) ($mainTask->max_points ?? 0) }}
        </div>
      </div>

      <div class="col-lg-9">
         <label class="form-label" for="version-{{ $version->id }}-task-{{ $mainTask->id }}-description-override">Description override</label>
         <textarea
           id="version-{{ $version->id }}-task-{{ $mainTask->id }}-description-override"
           rows="3"
          class="form-control"
          wire:model.live.debounce.300ms="versionTaskForms.{{ $version->id }}.{{ $mainTask->id }}.description_override"
          placeholder="Leave blank to reuse the template task description."></textarea>
        <div class="small text-muted mt-2">Add version-specific wording only when this row needs different student-facing instructions.</div>
      </div>

      @if(! empty($diagnosis['errors']))
        <div class="col-12">
          <div class="alert alert-warning mb-0">
            <ul class="mb-0 ps-3">
              @foreach($diagnosis['errors'] as $message)
                <li>{{ $message }}</li>
              @endforeach
            </ul>
          </div>
        </div>
       @endif
     </div>
   @endif
  </div>
</div>
