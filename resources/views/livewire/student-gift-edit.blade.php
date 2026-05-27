<div>
  <div class="offcanvas offcanvas-end"
       id="editGiftOffcanvas"
       tabindex="-1"
       aria-labelledby="editGiftLabel"
       data-bs-backdrop="static"
       data-bs-keyboard="false"
       wire:ignore.self
       wire:key="edit-gift-offcanvas">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="editGiftLabel">Edit Gift</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
      <form wire:submit.prevent="save" enctype="multipart/form-data" class="row g-3">
        <div class="col-12">
          <label class="form-label">Gift Name</label>
          <input type="text" class="form-control" wire:model="gift_name">
          @error('gift_name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="col-12">
          <label class="form-label">Points Required</label>
          <input type="number" min="0" class="form-control" wire:model="points_required">
          @if($isPendingTarget)
            <div class="form-text">Changing the active target can make this reward ready immediately. It must stay above earned rewards and before upcoming rewards.</div>
          @endif
          @error('points_required') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
        <div class="col-12">
          <label class="form-label">Status</label>
          <input type="text" class="form-control" value="{{ $isPendingTarget ? 'Pending' : 'Upcoming' }}" disabled>
          @error('status') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
        <div class="col-12">
          <label class="form-label">Gift Image </label>
          <input type="file" class="form-control" wire:model="gift_image" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
          <div wire:loading wire:target="gift_image" class="form-text">Uploading...</div>
          @error('gift_image') <small class="text-danger">{{ $message }}</small> @enderror

          {{-- المعاينة الحالية أو الجديدة --}}
          <div class="mt-2 gift-upload-preview">
            @if ($gift_image)
              <img src="{{ $gift_image->temporaryUrl() }}" alt="Gift preview">
            @elseif (!empty($current_image))
              <img src="{{ \App\Models\StudentGift::imageUrlFor($current_image) }}" alt="Current gift image">
              @else
                <img src="{{ \App\Models\StudentGift::imageUrlFor(null) }}" alt="Default gift image">

            @endif
          </div>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
            <span wire:loading.remove>Save Changes</span>
            <span wire:loading>Saving...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
  document.addEventListener('livewire:init', () => {
    if (window.w14StudentGiftEditInitialized) return;
    window.w14StudentGiftEditInitialized = true;

    Livewire.on('show-edit-offcanvas', () => {
      const el = document.getElementById('editGiftOffcanvas');
      if (el) bootstrap.Offcanvas.getOrCreateInstance(el).show();
    });

    Livewire.on('hide-edit-offcanvas', () => {
      const el = document.getElementById('editGiftOffcanvas');
      if (el) bootstrap.Offcanvas.getOrCreateInstance(el).hide();
    });

    Livewire.on('gift-updated', () => {
      // تحديث سريع للقائمة بعد الحفظ
      window.location.reload();
    });
  });
</script>
