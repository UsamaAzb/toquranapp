
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

  .behavior-agreement-picker {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
  }

  .behavior-agreement-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  .behavior-agreement-chip,
  .behavior-agreement-option {
    border: 1px solid #d8dbe7;
    background: #fff;
    color: #697085;
    font-weight: 600;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
  }

  .behavior-agreement-chip {
    min-height: 2.15rem;
    padding: 0.42rem 0.85rem;
    border-radius: 0.42rem;
  }

  .behavior-agreement-list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.55rem;
    max-height: 13.5rem;
    overflow-y: auto;
    padding: 0.1rem 0.2rem 0.1rem 0;
  }

  .behavior-agreement-option {
    min-height: 3.25rem;
    width: 100%;
    border-radius: 0.5rem;
    padding: 0.7rem 0.8rem;
    text-align: left;
    line-height: 1.35;
    white-space: normal;
  }

  .behavior-agreement-chip:hover,
  .behavior-agreement-option:hover {
    border-color: #aeb4c8;
    box-shadow: 0 0.25rem 0.75rem rgba(75, 70, 92, 0.08);
  }

  .behavior-agreement-chip.is-selected,
  .behavior-agreement-option.is-selected {
    color: #2f3144;
    border-color: currentColor;
    box-shadow: 0 0.35rem 1rem rgba(75, 70, 92, 0.1);
  }

  .behavior-agreement-chip.is-neutral {
    background: #6f7382;
    border-color: #6f7382;
    color: #fff;
  }

  .behavior-agreement-chip.is-success,
  .behavior-agreement-option.is-success {
    background: color-mix(in sRGB, #fff 84%, var(--bs-success));
    color: var(--bs-success);
  }

  .behavior-agreement-chip.is-warning,
  .behavior-agreement-option.is-warning {
    background: color-mix(in sRGB, #fff 82%, var(--bs-warning));
    color: #d97912;
  }

  .behavior-agreement-chip.is-danger,
  .behavior-agreement-option.is-danger {
    background: color-mix(in sRGB, #fff 84%, var(--bs-danger));
    color: var(--bs-danger);
  }

  @media (max-width: 575.98px) {
    .behavior-agreement-list {
      grid-template-columns: 1fr;
      max-height: 14.5rem;
    }

    .behavior-agreement-option {
      min-height: 2.9rem;
      padding: 0.62rem 0.72rem;
    }
  }
    </style>
@php
  $popupColor = $this->behaviorTone;
  $selectableModalBehaviors = $this->selectableModalBehaviors;
  $selected = $this->selectedModalBehavior;
  $t = $this->selectedBehaviorType;
  $opts = $this->behaviorPointOptions;
  $customizedPunishmentAgreements = collect($punishmentAgreements)
      ->filter(fn ($agreement) => strtolower(trim($agreement['title'] ?? '')) === 'customized')
      ->values();
  $standardPunishmentAgreements = collect($punishmentAgreements)
      ->reject(fn ($agreement) => strtolower(trim($agreement['title'] ?? '')) === 'customized')
      ->values();
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
        <button
          type="button"
          class="btn-close"
          aria-label="Close"
          data-bs-dismiss="modal"
          wire:click="cancelBehaviorDescription"></button>
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
            <div class="behavior-agreement-picker">
              <div class="behavior-agreement-toolbar">
              <button class="behavior-agreement-chip {{ $selectedPunishmentAgreementId ? '' : 'is-selected is-neutral' }}"
                      type="button"
                      :class="selectedPunishmentAgreementId ? '' : 'is-selected is-neutral'"
                      x-on:click="selectedPunishmentAgreementId = null">None</button>

              @foreach($customizedPunishmentAgreements as $ag)
                <button wire:key="teacher-punishment-agreement-{{ $pendingType }}-{{ $ag['id'] }}"
                        class="behavior-agreement-chip"
                        :class="Number(selectedPunishmentAgreementId) === {{ (int)$ag['id'] }} ? 'is-selected is-{{ $popupColor }}' : ''"
                        type="button" x-on:click="selectedPunishmentAgreementId = {{ (int)$ag['id'] }}">
                  {{ $ag['title'] }}
                </button>
              @endforeach

              @unless($hasCustomizedPunishmentAgreement)
                <button class="behavior-agreement-chip"
                        :class="Number(selectedPunishmentAgreementId) === -1 ? 'is-selected is-{{ $popupColor }}' : ''"
                        type="button" x-on:click="selectedPunishmentAgreementId = -1">
                  Customized
                </button>
              @endunless
              </div>

              <div class="behavior-agreement-list">
                @foreach($standardPunishmentAgreements as $ag)
                  <button wire:key="teacher-standard-punishment-agreement-{{ $pendingType }}-{{ $ag['id'] }}"
                          class="behavior-agreement-option"
                          :class="Number(selectedPunishmentAgreementId) === {{ (int)$ag['id'] }} ? 'is-selected is-{{ $popupColor }}' : ''"
                          type="button" x-on:click="selectedPunishmentAgreementId = {{ (int)$ag['id'] }}">
                    {{ $ag['title'] }}
                  </button>
                @endforeach
              </div>
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
        <button
          class="btn btn-label-secondary"
          type="button"
          data-bs-dismiss="modal"
          wire:click="cancelBehaviorDescription">Cancel</button>
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
