@props([
  'label' => null,
  'topbar' => null,
  'footer' => null,
])

<main {{ $attributes->class(['w14-app-zone']) }} @if($label) aria-label="{{ $label }}" @endif>
  <div class="w14-app-shell">
    @isset($topbar)
      <header class="w14-app-topbar">
        {{ $topbar }}
      </header>
    @endisset

    {{ $slot }}

    @isset($footer)
      <footer class="mt-6">
        {{ $footer }}
      </footer>
    @endisset
  </div>
</main>
