@props([
  'passes' => false,
  'messages' => [],
  'compact' => false,
  'bare' => false,
])

@php
  $tone = $passes ? 'success' : 'warning';
  $icon = $passes ? 'tabler-circle-check' : 'tabler-alert-triangle';
  $issueCount = count($messages);
  $fullLabel = $passes
    ? 'Ready'
    : ($issueCount > 0 ? $issueCount.' '.\Illuminate\Support\Str::plural('issue', $issueCount) : 'Needs review');
@endphp

@if($bare)
  <span class="automation-inline-signal automation-inline-signal--{{ $tone }}" title="{{ $fullLabel }}" aria-label="{{ $fullLabel }}">
    <i class="icon-base ti {{ $icon }}{{ $issueCount > 0 ? ' me-1' : '' }}"></i>
    @if(! $passes && $issueCount > 0)
      <span>{{ $issueCount }}</span>
    @endif
  </span>
@else
  <span class="badge bg-label-{{ $tone }} rounded-pill" title="{{ $fullLabel }}" aria-label="{{ $fullLabel }}">
    <i class="icon-base ti {{ $icon }}{{ $issueCount > 0 ? ' me-1' : '' }}" style="font-size: 0.75rem;"></i>
    @if(! $passes && $issueCount > 0)
      <span>{{ $issueCount }}</span>
    @endif
  </span>
@endif
