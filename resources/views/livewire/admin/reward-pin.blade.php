<div class="card mb-6">
  <div class="card-header">
    <h5 class="mb-0">Task Completion PIN</h5>
    <span class="card-subtitle mt-0">This Pin used for confirm on redeem gift by student.</span>
  </div>

  <div class="card-body pt-0">
    <h6 class="mb-1">PIN Code</h6>

    @if (session('pinSaved'))
      <div class="alert alert-success py-2">{{ session('pinSaved') }}</div>
    @endif

    <div class="mb-4">
      <div class="d-flex w-100 action-icons">
        <input
          id="defaultInput"
          class="form-control me-4"
          type="text"
          placeholder="Minimum 4 characters long, uppercase & symbol"

          wire:model="pin"
        />
        <a href="javascript:;" class="btn btn-icon btn-text-secondary save_pin"
           wire:click="save">
           <i class="icon-base ti tabler-edit icon-22px"></i>
        </a>
      </div>
      @error('pin') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

      @if($hasPin)
        <div class="form-text">A PIN is set for your account.</div>
      @else
        <div class="form-text">No PIN set yet.</div>
      @endif
    </div>
  </div>
</div>

@push('scripts')
<script>
  (function () {
    if (window.w14RewardPinInitialized) return;
    window.w14RewardPinInitialized = true;

    document.addEventListener('livewire:initialized', () => {
      Livewire.on('pin-saved', ({ message }) => {
        window.dispatchEvent(new CustomEvent('Toast', { detail: { type: 'success', message } }));
      });
    });
  })();
</script>
@endpush
