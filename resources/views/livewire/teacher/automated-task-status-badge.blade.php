@props([
  'label',
  'tone' => 'secondary',
  'count' => null,
  'iconOnly' => false,
  'bare' => false,
])

@php
  $icon = match ($label) {
    'Active' => 'tabler-check',
    'Paused' => 'tabler-player-pause',
    'Archived' => 'tabler-archive',
    'Not subscribed' => 'tabler-user-off',
    'Assigned' => 'tabler-users',
    'Pending Activation' => 'tabler-clock-hour-4',
    'Suspended' => 'tabler-ban',
    'Draft' => 'tabler-file-pencil',
    default => null,
  };

  $showText = ! $iconOnly || $label === 'Draft';
@endphp

@if($bare)
  <span class="automation-inline-signal automation-inline-signal--{{ $tone }}" @if($iconOnly) title="{{ $label }}" aria-label="{{ $label }}" @endif>
    @if($icon)
      <i class="icon-base ti {{ $icon }}{{ ($showText || $count !== null) ? ' me-1' : '' }}"></i>
    @endif
    @if($count !== null)
      <span>{{ $count }}</span>
    @endif
    @if($showText)
      <span>{{ $label }}</span>
    @endif
  </span>
@else
<span class="badge bg-label-{{ $tone }} rounded-pill" @if($iconOnly) title="{{ $label }}" aria-label="{{ $label }}" @endif>
  @if($icon)
    <i class="icon-base ti {{ $icon }}{{ ($showText || $count !== null) ? ' me-1' : '' }}" style="font-size: 0.75rem;"></i>
  @endif
  @if($count !== null)
    {{ $count }}
  @endif
  @if($showText)
    {{ $label }}
  @endif
</span>
@endif
