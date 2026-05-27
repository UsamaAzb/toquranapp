@props([
  'title' => null,
  'meta' => null,
  'actions' => null,
])

<section {{ $attributes->class([
  'rounded-w14-app-lg border border-w14-app-border bg-w14-app-surface p-4 text-w14-app-text shadow-w14-app-card',
]) }}>
  @if($title || $meta || isset($actions))
    <header class="mb-4 flex flex-wrap items-start justify-between gap-3">
      <div class="min-w-0">
        @if($meta)
          <p class="mb-1 text-xs font-bold uppercase text-w14-app-muted">{{ $meta }}</p>
        @endif

        @if($title)
          <h2 class="m-0 text-base font-bold leading-tight text-w14-app-text">{{ $title }}</h2>
        @endif
      </div>

      @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
          {{ $actions }}
        </div>
      @endisset
    </header>
  @endif

  {{ $slot }}
</section>
