@php
  $source = $payload['source'] ?? [];
  $routes = $payload['routes'] ?? [];
  $isEmbeddedViewer = (bool) data_get($payload, 'viewer.embedded', false);
  $difficulty = $payload['difficulty'] ?? 'sprout';
  $difficultyLabels = config('vocabulary.games.difficulty_labels', []);
  $gameTitles = [
    'hangman' => 'Floatie',
    'missing_letter' => 'Missing Letter',
    'spelling_choice' => 'Correct Spelling',
  ];
  $gameIcons = [
    'hangman' => 'ti tabler-balloon',
    'missing_letter' => 'ti tabler-alphabet-latin',
    'spelling_choice' => 'ti tabler-checks',
  ];
  $gameTabs = collect($routes['gameTabs'] ?? [])
    ->filter(fn ($url, $key): bool => isset($gameTitles[$key]) && filled($url));
  $activeGame = $payload['game'] ?? 'hangman';
  $gameTitle = $gameTitles[$payload['game'] ?? 'hangman'] ?? 'Vocabulary Game';
  $backLabel = $routes['backLabel'] ?? 'Games';
  $surfaceBreadcrumbs = collect($routes['surfaceBreadcrumbs'] ?? [
    $backLabel => $routes['backToGames'] ?? route('vocabulary.games.hub'),
    $source['title'] ?? 'Vocabulary' => null,
  ]);
  $wordCount = count($payload['words'] ?? []);
  $dotCount = min($wordCount, (int) config('vocabulary.games.progress_dot_cap', 10));
@endphp

<div class="vg-surface">
  <nav class="vg-breadcrumbs small text-muted mb-3" aria-label="Breadcrumb">
    @foreach ($surfaceBreadcrumbs as $label => $url)
      @if ($url && ! $isEmbeddedViewer)
        <a href="{{ $url }}">{{ $label }}</a>
      @else
        <span>{{ $label }}</span>
      @endif
      @unless ($loop->last)
        <span>/</span>
      @endunless
    @endforeach
  </nav>

  <header class="vg-header">
    <div class="d-flex flex-wrap align-items-center gap-2">
      <span class="vg-pill">{{ $gameTitle }}</span>
      <h1 class="vg-title">{{ $source['title'] ?? 'Vocabulary Game' }}</h1>
    </div>
    <div class="vg-progress-wrap">
      <span class="vg-progress-text" x-text="progressText ?? ''"></span>
      @if ($dotCount > 1)
        <div class="vg-progress-dots" aria-label="Word progress">
          @for ($dot = 0; $dot < $dotCount; $dot++)
            <span class="vg-progress-dot" :class="{ active: index > {{ $dot }} }"></span>
          @endfor
        </div>
      @endif
    </div>
  </header>

  @if ($gameTabs->count() > 1)
    <nav class="vg-game-tabs" aria-label="Choose vocabulary game">
      @foreach ($gameTabs as $gameKey => $tabUrl)
        <a
          class="vg-game-tab {{ $activeGame === $gameKey ? 'active' : '' }}"
          href="{{ $tabUrl }}"
          :href="@js($tabUrl).concat(@js(str_contains($tabUrl, '?') ? '&' : '?'), 'word_index=', index)"
          aria-current="{{ $activeGame === $gameKey ? 'page' : 'false' }}"
        >
          <i class="{{ $gameIcons[$gameKey] ?? 'ti tabler-play' }}" aria-hidden="true"></i>
          <span>{{ $gameTitles[$gameKey] }}</span>
        </a>
      @endforeach
    </nav>
  @endif

  {{ $slot ?? '' }}
</div>
