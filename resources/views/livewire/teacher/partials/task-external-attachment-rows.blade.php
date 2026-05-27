@php
  $linkTitleModel = $linkTitleModel ?? 'linkTitle';
  $linkUrlModel = $linkUrlModel ?? 'linkUrl';
  $youtubeTitleModel = $youtubeTitleModel ?? 'youtubeTitle';
  $youtubeUrlModel = $youtubeUrlModel ?? 'youtubeUrl';
  $addLinkAction = $addLinkAction ?? 'addLink';
  $addYoutubeAction = $addYoutubeAction ?? 'addYoutube';
  $linkPendingError = $linkPendingError ?? 'links_pending';
  $youtubePendingError = $youtubePendingError ?? 'youtubes_pending';
  $linkTitleError = $linkTitleError ?? $linkTitleModel;
  $linkUrlError = $linkUrlError ?? $linkUrlModel;
  $youtubeTitleError = $youtubeTitleError ?? $youtubeTitleModel;
  $youtubeUrlError = $youtubeUrlError ?? $youtubeUrlModel;
  $busyTargets = $busyTargets ?? 'save,addLink,addYoutube,files';
  $locked = $locked ?? false;
@endphp

@once
  <style>
    .w14-external-attachment-rows {
      display: grid;
      gap: 0.75rem;
      min-width: 0;
    }

    .w14-external-attachment-row {
      min-width: 0;
    }

    .w14-external-attachment-row .input-group {
      min-width: 0;
    }

    .w14-external-attachment-row .form-control {
      min-width: 0;
    }

    .w14-external-attachment-row .btn {
      flex: 0 0 auto;
      min-width: 2.75rem;
    }

    @media (max-width: 575.98px) {
      .w14-external-attachment-row .input-group {
        align-items: stretch;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
      }

      .w14-external-attachment-row .input-group > .form-control,
      .w14-external-attachment-row .input-group > .btn {
        border-radius: 0;
        width: 100%;
      }

      .w14-external-attachment-row .input-group > .form-control:first-child {
        border-bottom-left-radius: var(--bs-border-radius);
        border-top-left-radius: var(--bs-border-radius);
      }

      .w14-external-attachment-row .input-group > .btn:last-child {
        border-bottom-right-radius: var(--bs-border-radius);
        border-top-right-radius: var(--bs-border-radius);
      }
    }
  </style>
@endonce

<div class="w14-external-attachment-rows">
  <div class="w14-external-attachment-row">
    <label class="form-label">Link</label>
    @error($linkPendingError)
      <div class="text-danger small mb-1">{{ $message }}</div>
    @enderror
    <div class="input-group">
      <input type="text"
             class="form-control"
             placeholder="Title"
             wire:model="{{ $linkTitleModel }}"
             @disabled($locked)>
      <input type="url"
             class="form-control"
             placeholder="https://..."
             wire:model="{{ $linkUrlModel }}"
             @disabled($locked)>
      <button type="button"
              class="btn btn-outline-primary"
              wire:click="{{ $addLinkAction }}"
              wire:loading.attr="disabled"
              wire:target="{{ $busyTargets }}"
              aria-label="Add link"
              title="Add link"
              @disabled($locked)>
        <i class="ti tabler-plus"></i>
      </button>
    </div>
    @error($linkTitleError)
      <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
    @error($linkUrlError)
      <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
  </div>

  <div class="w14-external-attachment-row">
    <label class="form-label">YouTube</label>
    @error($youtubePendingError)
      <div class="text-danger small mb-1">{{ $message }}</div>
    @enderror
    <div class="input-group">
      <input type="text"
             class="form-control"
             placeholder="Title"
             wire:model="{{ $youtubeTitleModel }}"
             @disabled($locked)>
      <input type="url"
             class="form-control"
             placeholder="https://..."
             wire:model="{{ $youtubeUrlModel }}"
             @disabled($locked)>
      <button type="button"
              class="btn btn-outline-primary"
              wire:click="{{ $addYoutubeAction }}"
              wire:loading.attr="disabled"
              wire:target="{{ $busyTargets }}"
              aria-label="Add YouTube"
              title="Add YouTube"
              @disabled($locked)>
        <i class="ti tabler-plus"></i>
      </button>
    </div>
    @error($youtubeTitleError)
      <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
    @error($youtubeUrlError)
      <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
  </div>
</div>
