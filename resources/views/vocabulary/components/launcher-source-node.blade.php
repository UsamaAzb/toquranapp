@php
  $metadata = $sourceMetadata[$source->id] ?? [];
  $wordCount = (int) ($metadata['word_count'] ?? $source->memberships_count ?? 0);
  $childCount = (int) ($metadata['children_count'] ?? $source->children_count ?? 0);
@endphp

<article class="vl-source-node {{ $source->isFolder() ? 'is-folder' : 'is-playable' }}">
  <div class="vl-source-icon">
    <i class="icon-base ti {{ $source->isFolder() ? 'tabler-folder-search' : 'tabler-list-details' }}"></i>
  </div>

  <div class="vl-source-main">
    <div class="d-flex flex-wrap align-items-center gap-2">
      @if ($source->isFolder())
        <span class="badge bg-label-primary">Folder</span>
      @endif
      <h6 class="mb-0">{{ $source->title }}</h6>
    </div>
    <div class="text-muted small mt-1">
      @if ($source->isFolder())
        {{ $childCount }} {{ \Illuminate\Support\Str::plural('item', $childCount) }} inside
      @else
        {{ $wordCount }} {{ \Illuminate\Support\Str::plural('word', $wordCount) }}
      @endif
    </div>
  </div>

  <div class="vl-source-actions">
    @if ($source->isFolder())
      <a class="btn btn-sm btn-outline-primary" href="{{ route('teacher.vocabulary.games.launch', array_filter(['source_id' => $source->id, 'teacher_subject_class_id' => $selectedContextId ?: null])) }}">
        Open
        <i class="icon-base ti tabler-arrow-right ms-1"></i>
      </a>
    @else
      <form class="vl-play-form" method="GET" action="{{ route('vocabulary.games.source', ['source' => $source->id]) }}">
        @if ($selectedContextId)
          <input type="hidden" name="teacher_subject_class_id" value="{{ $selectedContextId }}">
        @endif
        <button class="btn btn-sm btn-primary" type="submit">
          <i class="icon-base ti tabler-player-play me-1"></i>
          Play
        </button>
      </form>
    @endif
  </div>
</article>
