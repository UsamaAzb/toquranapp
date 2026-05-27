@props([
  'node' => [],
])

@php
  $children = $node['children'] ?? [];
  $isPlayable = (bool) ($node['is_playable'] ?? false);
  $nodeId = (int) ($node['id'] ?? 0);
  $wordCount = (int) ($node['word_count'] ?? 0);
  $childCount = count($children);
@endphp

<article class="vg-student-card {{ $isPlayable ? 'is-playable' : 'is-folder' }}">
  <div class="vg-student-icon">
    <i class="icon-base ti {{ $isPlayable ? 'tabler-list-details' : 'tabler-folder' }}"></i>
  </div>

  <div class="vg-student-main">
    @unless ($isPlayable)
      <div class="vg-student-label">Folder</div>
    @endunless
    <h3>{{ $node['title'] ?? 'Vocabulary' }}</h3>
    <p>
      @if ($isPlayable)
        {{ $wordCount }} {{ \Illuminate\Support\Str::plural('word', $wordCount) }}
      @else
        {{ $childCount }} {{ \Illuminate\Support\Str::plural('item', $childCount) }} inside
      @endif
    </p>
  </div>

  <div class="vg-student-actions">
    @if ($isPlayable)
      <form class="vg-student-play" method="GET" action="{{ route('vocabulary.games.source', ['source' => $nodeId]) }}">
        <button class="btn btn-sm btn-primary" type="submit" @disabled($wordCount < 1)>
          <i class="icon-base ti tabler-player-play me-1"></i>
          Play
        </button>
      </form>
    @else
      <button class="btn btn-sm btn-outline-primary" type="button" wire:click="openSet({{ $nodeId }})">
        Open
        <i class="icon-base ti tabler-arrow-right ms-1"></i>
      </button>
    @endif
  </div>
</article>
