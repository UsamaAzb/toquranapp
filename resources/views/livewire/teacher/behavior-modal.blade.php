
<div>
    <style>
          .points-theme-success .point-pill input:checked + .point-circle{
    border-width: 2px;
    background-color: color-mix(in sRGB, var(--bs-paper-bg) var(--bs-bg-label-tint-amount), var(--bs-success));
  }
  .points-theme-warning .point-pill input:checked + .point-circle{
    border-width: 2px;
    background-color: color-mix(in sRGB, var(--bs-paper-bg) var(--bs-bg-label-tint-amount), var(--bs-warning));
  }
  .points-theme-danger .point-pill input:checked + .point-circle{
    border-width: 2px;
    background-color: color-mix(in sRGB, var(--bs-paper-bg) var(--bs-bg-label-tint-amount), var(--bs-danger));
  }
    </style>
@php
  $popupColor = $this->behaviorTone;
  $selectableModalBehaviors = $this->selectableModalBehaviors;
  $selected = $this->selectedModalBehavior;
  $t = $this->selectedBehaviorType;
  $opts = $this->behaviorPointOptions;
@endphp

<div
  wire:ignore.self
  class="modal fade"
  id="behaviorDescModal"
  tabindex="-1"
  aria-hidden="true"
  x-data="{
    pointsInput: @entangle('pointsInput'),
    selectedPunishmentAgreementId: @entangle('selectedPunishmentAgreementId')
  }"
>
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Add Behavior for <span class="text-primary">{{ $studentName }}</span></h5>
        <button type="button" class="btn-close" aria-label="Close" wire:click="cancelBehaviorDescription"></button>
      </div>

      <div class="modal-body">

        @if($categoryTitle)
          <p class="mb-2 text-body-secondary">{{ $categoryTitle }}</p>
        @endif

        <div class="mb-3"> 
        <label class="form-label">Behavior</label>
        <select id="behaviorSelect"
                class="form-select"
                wire:model.change="selectedBehaviorId"
                @disabled(empty($selectableModalBehaviors))>
          <option value="">Select behavior</option>
          @forelse($selectableModalBehaviors as $b)
              <option value="{{ (int) $b['id'] }}">{{ $b['title'] }}</option>
          @empty
            <option value="" disabled>No behaviors found.</option>
          @endforelse
        </select>
        @if(!empty($selected['title']))
          <div class="d-flex align-items-center gap-2 mt-2 small text-body-secondary">
            @if(!empty($selected['icon_path']))
              <img src="{{ $this->behaviorAssetUrl($selected['icon_path']) }}" style="height:22px;width:22px;" alt="">
            @endif
            <span>{{ $selected['title'] }}</span>
          </div>
        @endif
        @error('selectedBehaviorId')
          <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        </div>
          
          
          

       {{-- <div class="mb-3">
          <label class="form-label">Points</label>
          <div class="d-flex flex-wrap gap-2">
            @foreach($opts as $opt)
              <label wire:key="p-{{ $t }}-{{ $opt }}">
                <input type="radio" class="d-none" wire:model="pointsInput" value="{{ $opt }}">
                <span class="btn btn-sm btn-outline-{{ $popupColor }} rounded-circle">
                  {{ $t==='Positive' ? $opt : ('-'.$opt) }}
                </span>
              </label>
            @endforeach
          </div>
        </div>--}}
<div class="mb-3">
  <label class="form-label">Points</label>

  <div class="d-flex flex-wrap gap-2 points-theme-{{ $popupColor }}">
    @foreach($opts as $opt)
      <label class="point-pill"
             wire:key="points-{{ $pendingType }}-{{ $selectedBehaviorId ?? 'none' }}-{{ $opt }}">
        <input type="radio" class="d-none" x-model.number="pointsInput" value="{{ $opt }}">

        <span class="btn btn-icon btn-sm rounded-circle
          {{ (int)$pointsInput === (int)$opt ? 'btn-outline-'.$popupColor : 'btn-outline-'.$popupColor }}
          point-circle">
          {{ $t === 'Positive' ? $opt : ('-'.$opt) }}
        </span>
      </label>
    @endforeach
  </div>
  @error('pointsInput')
    <div class="text-danger small mt-1">{{ $message }}</div>
  @enderror
</div>
        @if(in_array($pendingType, ['Slip','No Way']))
          <hr>
          <div class="mb-2">
            <label class="form-label">Agreements</label>
            <div class="d-flex flex-wrap gap-2">
              <button class="btn btn-sm {{ $selectedPunishmentAgreementId ? 'btn-outline-secondary' : 'btn-secondary' }}"
                      type="button"
                      :class="selectedPunishmentAgreementId ? 'btn-outline-secondary' : 'btn-secondary'"
                      x-on:click="selectedPunishmentAgreementId = null">None</button>

              @foreach($punishmentAgreements as $ag)
                <button wire:key="teacher-punishment-agreement-{{ $pendingType }}-{{ $ag['id'] }}"
                        class="btn btn-sm"
                        :class="Number(selectedPunishmentAgreementId) === {{ (int)$ag['id'] }} ? 'btn-{{ $popupColor }}' : 'btn-outline-secondary'"
                        type="button" x-on:click="selectedPunishmentAgreementId = {{ (int)$ag['id'] }}">
                  {{ $ag['title'] }}
                </button>
              @endforeach

              @unless($hasCustomizedPunishmentAgreement)
                <button class="btn btn-sm"
                        :class="Number(selectedPunishmentAgreementId) === -1 ? 'btn-{{ $popupColor }}' : 'btn-outline-secondary'"
                        type="button" x-on:click="selectedPunishmentAgreementId = -1">
                  Customized
                </button>
              @endunless
            </div>
            @error('descriptionInput') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
          </div>
        @endif

        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea class="form-control" rows="3" wire:model.blur="descriptionInput"></textarea>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-primary" type="button" wire:click="confirmBehaviorWithDescription">Save</button>
      </div>

    </div>
  </div>
</div>
  
@if($recentAward)
  <div
   wire:key="behavior-toast-{{ $recentAward['id'] }}"
    x-data="{ show: true }"
    x-show="show"
    x-transition.duration.400ms
    x-init="setTimeout(() => { show = false; $wire.clearRecentAward(); }, 1500)"
    class="behavior-toast position-fixed top-50 start-50 translate-middle"
    style="z-index: 2000;"
  >
    <div
      class="card border-0 shadow-lg text-center px-5 py-4"
      style="border-radius: 0.45rem; background-color: #ffffff; width: min(21rem, calc(100vw - 2rem));"
    >
      <div class="d-flex flex-column align-items-center justify-content-center gap-3">
        <h4 class="fw-bold mb-1" style="color: #2b2b2b;">
          {{ $recentAward['student_name'] }}
        </h4>

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
  if (window.w14BehaviorModalInitialized) return;
  window.w14BehaviorModalInitialized = true;

  function cleanup() {
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');
  }

  window.addEventListener('open-teacher-behavior-modal', () => {
    const el = document.getElementById('behaviorDescModal');
    if (!el) return;
    requestAnimationFrame(() => {
      bootstrap.Modal.getOrCreateInstance(el, { backdrop: 'static' }).show();
    });
  });

  window.addEventListener('close-teacher-behavior-modal', () => {
    const el = document.getElementById('behaviorDescModal');
    if (!el) return;
    bootstrap.Modal.getInstance(el)?.hide();
    setTimeout(cleanup, 150);
  });

  const el = document.getElementById('behaviorDescModal');
  if (el) el.addEventListener('hidden.bs.modal', () => setTimeout(cleanup, 50));
});
</script>

</div>
