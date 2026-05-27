@props([
  'align' => 'end',
])

@php
  $alignmentClass = match ($align) {
    'start' => 'justify-start',
    'center' => 'justify-center',
    'between' => 'justify-between',
    default => 'justify-end',
  };
@endphp

<div {{ $attributes->class([
  'flex flex-wrap items-center gap-2',
  $alignmentClass,
]) }}>
  {{ $slot }}
</div>
