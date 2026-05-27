<div>
@php
  $popupColor = $this->behaviorTone;
  $selectedType = $this->selectedBehaviorType;
  $opts = $this->behaviorPointOptions;
  $selectableModalBehaviors = $this->selectableModalBehaviors;
  $selected = $this->selectedModalBehavior;
@endphp

<div
  wire:ignore.self
  class="modal fade"
  id="parentBehaviorDescModal"
  tabindex="-1"
  aria-hidden="true"
>
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <p class="text-uppercase text-muted small fw-semibold mb-1">Behavior</p>
          <h5 class="modal-title mb-0">Add behavior for <span class="text-primary">{{ $studentName }}</span></h5>
        </div>
        <button type="button" class="btn-close" aria-label="Close" wire:click="cancelBehaviorDescription"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label" for="parentBehaviorSelect">Behavior</label>
          <select
            id="parentBehaviorSelect"
            class="form-select"
            wire:model.change="selectedBehaviorId"
            @disabled(empty($selectableModalBehaviors))
          >
            <option value="">Select behavior</option>
            @forelse($selectableModalBehaviors as $behavior)
              <option value="{{ (int) $behavior['id'] }}">{{ $behavior['title'] }}</option>
            @empty
              <option value="" disabled>No behaviors found.</option>
            @endforelse
          </select>
          @if(! empty($selected['title']))
            <div class="d-flex align-items-center gap-2 mt-2 small text-body-secondary">
              @if(! empty($selected['icon_path']))
                <img
                  src="{{ $this->behaviorAssetUrl($selected['icon_path']) }}"
                  class="rounded"
                  width="24"
                  height="24"
                  loading="lazy"
                  decoding="async"
                  alt=""
                >
              @endif
              <span>{{ $selected['title'] }}</span>
            </div>
          @endif
          @error('selectedBehaviorId')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Points</label>
          <div class="d-flex flex-wrap gap-2">
            @foreach($opts as $opt)
              <label wire:key="parent-points-{{ $pendingType }}-{{ $selectedBehaviorId }}-{{ $opt }}">
                <input type="radio" class="d-none" wire:model.live="pointsInput" value="{{ $opt }}">
                <span class="btn btn-sm btn-icon rounded-circle {{ (int) $pointsInput === (int) $opt ? 'btn-'.$popupColor : 'btn-outline-'.$popupColor }}">
                  {{ $selectedType === 'Positive' ? $opt : ('-'.$opt) }}
                </span>
              </label>
            @endforeach
          </div>
          @error('pointsInput')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        @if(in_array($pendingType, ['Slip', 'No Way'], true))
          <hr>
          <div class="mb-3">
            <label class="form-label">Agreement</label>
            <div class="d-flex flex-wrap gap-2">
              <button
                class="btn btn-sm {{ $selectedPunishmentAgreementId ? 'btn-outline-secondary' : 'btn-secondary' }}"
                type="button"
                wire:click="clearPunishmentSelection"
              >
                None
              </button>

              @foreach($punishmentAgreements as $agreement)
                <button
                  wire:key="parent-punishment-agreement-{{ $pendingType }}-{{ $agreement['id'] }}"
                  class="btn btn-sm {{ (int) $selectedPunishmentAgreementId === (int) $agreement['id'] ? 'btn-'.$popupColor : 'btn-outline-secondary' }}"
                  type="button"
                  wire:click="selectPunishment({{ (int) $agreement['id'] }})"
                >
                  {{ $agreement['title'] }}
                </button>
              @endforeach
            </div>
          </div>
        @endif

        <div>
          <label class="form-label" for="parentBehaviorDescription">Description</label>
          <textarea
            id="parentBehaviorDescription"
            class="form-control"
            rows="3"
            wire:model.blur="descriptionInput"
          ></textarea>
          @error('descriptionInput')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-label-secondary" type="button" wire:click="cancelBehaviorDescription">Cancel</button>
        <button class="btn btn-primary" type="button" wire:click="confirmBehaviorWithDescription" wire:loading.attr="disabled">
          <span wire:loading.remove wire:target="confirmBehaviorWithDescription">
            <i class="ti tabler-device-floppy me-1"></i> Save
          </span>
          <span wire:loading wire:target="confirmBehaviorWithDescription">Saving...</span>
        </button>
      </div>
    </div>
  </div>
</div>

@if($recentAward)
  <div
    wire:key="parent-behavior-toast-{{ $recentAward['id'] }}"
    x-data="{ show: true }"
    x-show="show"
    x-transition.duration.300ms
    x-init="setTimeout(() => show = false, 1600)"
    class="position-fixed top-50 start-50 translate-middle"
    style="z-index: 2000;"
  >
    <div
      class="card border-0 shadow-lg text-center px-5 py-4"
      style="border-radius: 0.45rem; background-color: #ffffff; width: min(21rem, calc(100vw - 2rem));"
    >
      <div class="d-flex flex-column align-items-center justify-content-center gap-3">
        <h4 class="fw-bold mb-1" style="color: #2b2b2b;">{{ $recentAward['student_name'] }}</h4>
        <p class="fs-6 mb-0" style="color: #333;">
          {{ $recentAward['type'] === 'Positive' ? '+' : '-' }}{{ $recentAward['points'] }}
          <span style="color: {{ $recentAward['type'] === 'Positive' ? '#28a745' : '#d9534f' }};">
            for {{ $recentAward['title'] }}
          </span>
        </p>
      </div>
    </div>
  </div>
@endif

<script>
document.addEventListener('livewire:initialized', () => {
  if (window.w14ParentBehaviorModalInitialized) return;
  window.w14ParentBehaviorModalInitialized = true;

  function cleanup() {
    document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');
  }

  window.addEventListener('open-parent-behavior-modal', () => {
    const el = document.getElementById('parentBehaviorDescModal');
    if (!el) return;

    requestAnimationFrame(() => {
      bootstrap.Modal.getOrCreateInstance(el, { backdrop: 'static' }).show();
    });
  });

  window.addEventListener('close-parent-behavior-modal', () => {
    const el = document.getElementById('parentBehaviorDescModal');
    if (!el) return;

    bootstrap.Modal.getInstance(el)?.hide();
    setTimeout(cleanup, 150);
  });

  const el = document.getElementById('parentBehaviorDescModal');
  if (el) {
    el.addEventListener('hidden.bs.modal', () => setTimeout(cleanup, 50));
  }
});
</script>
</div>
