<div class="dropdown">
  {{-- CodeRabbit module review: Core (Module 1) --}}
  <button
    type="button"
    class="{{ $buttonClass }}"
    data-bs-toggle="dropdown"
    aria-expanded="false"
    title="{{ $disabledReason ?? $label }}"
    aria-label="{{ $disabledReason ?? $label }}"
    @disabled($disabled)
  >
    <i class="{{ $iconClass }}"></i>
    @if($showLabel)
      {{ $label }}
    @else
      <span class="visually-hidden">{{ $label }}</span>
    @endif
  </button>

  <ul class="dropdown-menu dropdown-menu-end">
    <li>
      <button class="dropdown-item" type="button" wire:click="open('Positive')">
        Positive
      </button>
    </li>
    <li>
      <button class="dropdown-item" type="button" wire:click="open('Slip')">
        Slip
      </button>
    </li>
    <li>
      <button class="dropdown-item" type="button" wire:click="open('No Way')">
        Red Flag
      </button>
    </li>
  </ul>
</div>
