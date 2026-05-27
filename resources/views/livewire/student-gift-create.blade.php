<div>
  @once
    @push('styles')
      <style>
        .gift-upload-preview {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 8rem;
          height: 8rem;
          padding: 0.5rem;
          border: 1px solid var(--bs-border-color);
          border-radius: 0.5rem;
          background: var(--bs-body-bg);
        }

        .gift-upload-preview img {
          width: 100%;
          height: 100%;
          object-fit: contain;
        }
      </style>
    @endpush
  @endonce

  {{-- CodeRabbit module review: Core (Module 1) --}}
  {{-- Trigger button can live anywhere on your page --}}
  <button type="button" class="btn btn-primary mb-3" data-bs-toggle="offcanvas" data-bs-target="#addGiftOffcanvas">
    Add Gift
  </button>

  {{-- Offcanvas container --}}
  <div class="offcanvas offcanvas-end" id="addGiftOffcanvas" tabindex="-1" aria-labelledby="addGiftLabel"
    data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self wire:key="add-gift-offcanvas">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="addGiftLabel">Add Gift</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
      @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <form wire:submit.prevent="save" enctype="multipart/form-data" class="row g-3">

        {{-- Hidden defaults (not shown) --}}

        {{-- Visible inputs --}}
        <div class="col-12">
          <label class="form-label">Gift Name</label>
          <input type="text" class="form-control" wire:model.defer="gift_name" placeholder="e.g., Small Toy">
          @error('gift_name')
            <small class="text-danger">{{ $message }}</small>
          @enderror
        </div>

        <div class="col-12">
          <label class="form-label">Points Required</label>
          <input type="number" class="form-control" min="0" wire:model.defer="points_required"
            placeholder="e.g., 20">
          @error('points_required')
            <small class="text-danger">{{ $message }}</small>
          @enderror
        </div>

        <input type="hidden" wire:model="status">
        <div class="col-12">
          <label class="form-label">Status</label>
          <input type="text" class="form-control" value="Upcoming" disabled>
          @error('status')
            <small class="text-danger">{{ $message }}</small>
          @enderror
        </div>

        <div class="col-12">
          <label class="form-label">Gift Image</label>
          <input type="file" class="form-control" wire:model="gift_image" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
          @error('gift_image')
            <small class="text-danger">{{ $message }}</small>
          @enderror
          <div wire:loading wire:target="gift_image" class="form-text">Uploading...</div>

          @if ($gift_image)
            <div class="mt-2 gift-upload-preview">
              <img src="{{ $gift_image->temporaryUrl() }}" alt="Gift preview">
            </div>
          @endif
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
            <span wire:loading.remove>Save</span>
            <span wire:loading>Saving...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
  {{-- Close offcanvas after save --}}

  <script>
    (function () {
      if (window.w14StudentGiftCreateInitialized) return;
      window.w14StudentGiftCreateInitialized = true;

      document.addEventListener('livewire:init', () => {
        Livewire.on('student-gift:saved', () => {
          const el = document.getElementById('addGiftOffcanvas');
          if (!el) return;
          const oc = bootstrap.Offcanvas.getOrCreateInstance(el);
          oc.hide();
          window.location.reload();
        });
      });
    })();
  </script>


</div>
