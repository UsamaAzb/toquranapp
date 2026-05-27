@props([
  'title',
  'copy' => null,
  'tone' => 'neutral',
  'icon' => null,
])

@php
  $toneClass = match ($tone) {
    'success' => 'border-w14-app-success bg-w14-app-surface-soft text-w14-app-success',
    'warning' => 'border-w14-app-warning bg-w14-app-bg-warm text-w14-app-warning',
    'danger' => 'border-w14-app-danger bg-w14-app-surface-soft text-w14-app-danger',
    'info' => 'border-w14-app-info bg-w14-app-surface-soft text-w14-app-info',
    default => 'border-w14-app-border bg-w14-app-surface-soft text-w14-app-brand',
  };
@endphp

<section {{ $attributes->class([
  'rounded-w14-app-lg border p-5 text-center shadow-w14-app-card',
  $toneClass,
]) }}>
  @if($icon)
    <div class="mx-auto mb-3 grid size-10 place-items-center rounded-w14-app-md bg-w14-app-surface text-xl" aria-hidden="true">
      <i class="{{ $icon }}"></i>
    </div>
  @endif

  <h2 class="m-0 text-base font-bold leading-tight text-w14-app-text">{{ $title }}</h2>

  @if($copy)
    <p class="mx-auto mt-2 max-w-prose text-sm leading-relaxed text-w14-app-muted">{{ $copy }}</p>
  @endif

  @if($slot->isNotEmpty())
    <div class="mt-4">
      {{ $slot }}
    </div>
  @endif
</section>
