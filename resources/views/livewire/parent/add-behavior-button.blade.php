<div class="dropdown">
  <button
    type="button"
    class="{{ $buttonClass }} dropdown-toggle"
    data-bs-toggle="dropdown"
    aria-expanded="false"
    title="{{ $disabledReason ?? $label }}"
    aria-label="{{ $this->ariaLabel }}"
    @disabled($disabled)
  >
    <i class="icon-base {{ $iconClass }} icon-16px me-1"></i>
    @if($showLabel)
      {{ $label }}
    @else
      <span class="visually-hidden">{{ $label }}</span>
    @endif
  </button>

  <ul class="dropdown-menu dropdown-menu-end">
    <li>
      <button class="dropdown-item d-flex align-items-center gap-2" type="button" wire:click="open('Positive')">
        <i class="icon-base ti tabler-thumb-up icon-16px text-success"></i>
        <span>Positive</span>
      </button>
    </li>
    <li>
      <button class="dropdown-item d-flex align-items-center gap-2" type="button" wire:click="open('Slip')">
        <i class="icon-base ti tabler-alert-triangle icon-16px text-warning"></i>
        <span>Slip</span>
      </button>
    </li>
    <li>
      <button class="dropdown-item d-flex align-items-center gap-2" type="button" wire:click="open('No Way')">
        <i class="icon-base ti tabler-circle-x icon-16px text-danger"></i>
        <span>Red Flag</span>
      </button>
    </li>
  </ul>
</div>
