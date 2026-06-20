@php
  $configData = Helper::appClasses();
  $selectedContextId = (int) request('teacher_subject_class_id', $teacherSubjectClass?->id);
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Launch Vocabulary Game')

@section('page-style')
  @vite(['resources/css/vocabulary-games.css'])
@endsection

@section('content')
  <div class="container-fluid vocab-game vocab-launcher" x-data="{ tab: 'source', peeking: false, contextFilter: '', customGame: @js(old('game', 'hangman')), customDifficulty: @js(old('difficulty', 'sprout')) }">
    <div class="vg-surface">
      <header class="vg-header">
        <div>
          <span class="vg-pill">Teacher Play</span>
          <h1 class="vg-title mt-2">Start a vocabulary game</h1>
        </div>
      </header>

      <div class="vl-launch-shell">
        <section class="card vl-card vl-context-compact">
          <details class="vl-context-details">
            <summary>
              <span>
                <i class="icon-base ti tabler-filter me-1"></i>
                Class filter
              </span>
              @if ($selectedContextId && $teacherSubjectClass)
                <strong>
                  {{ $teacherSubjectClass->class_name ?: $teacherSubjectClass->class?->title }} - {{ \Illuminate\Support\Str::lower((string) $teacherSubjectClass->subject_name) === 'english' ? 'Quranic Arabic' : $teacherSubjectClass->subject_name }}
                </strong>
              @else
                <span class="text-muted">All classes</span>
              @endif
            </summary>
            <div class="vl-context-panel">
              <p class="text-muted small mb-2">Optional. Use this only when you want to play or check access in a specific class context.</p>

              @if ($selectedContextId && $teacherSubjectClass)
                <a class="btn btn-sm btn-outline-primary" href="{{ route('teacher.vocabulary.games.launch') }}">
                  <i class="icon-base ti tabler-x me-1"></i>
                  Clear filter
                </a>
              @else
                <label class="form-label" for="contextFilter">Find class or subject</label>
                <input id="contextFilter" class="form-control mb-3" type="search" x-model.debounce.150ms="contextFilter" placeholder="Search student, grade, subject">
                <div class="vl-context-list">
                  @forelse ($contexts as $context)
                    @php
                      $subjectLabel = \Illuminate\Support\Str::lower((string) $context->subject_name) === 'english' ? 'Quranic Arabic' : $context->subject_name;
                      $contextLabel = trim(($context->class_name ?: $context->class?->title).' - '.$subjectLabel);
                    @endphp
                    <a class="vl-context-option"
                      href="{{ route('teacher.vocabulary.games.launch', ['teacher_subject_class_id' => $context->id]) }}"
                      x-show="@js(strtolower($contextLabel)).includes(contextFilter.toLowerCase())">
                      <i class="icon-base ti tabler-users"></i>
                      <span>{{ $contextLabel }}</span>
                    </a>
                  @empty
                    <div class="text-muted small">No classroom contexts found.</div>
                  @endforelse
                </div>
              @endif
            </div>
          </details>
        </section>

        <section class="card vl-card vl-launch-panel">
          <div class="card-header">
            <div class="nav nav-pills gap-2">
              <button class="nav-link" type="button" :class="{ active: tab === 'source' }" @click="tab = 'source'">Existing source</button>
              <button class="nav-link" type="button" :class="{ active: tab === 'custom' }" @click="tab = 'custom'">Custom words</button>
            </div>
          </div>

          <div class="card-body" x-show="tab === 'source'">
            @if ($sources->isEmpty())
              <div class="alert alert-info mb-0">No vocabulary folders are available yet.</div>
            @else
              <div class="vl-source-browser-head">
                <div class="vl-source-crumbs">
                  <a href="{{ route('teacher.vocabulary.games.launch', array_filter(['teacher_subject_class_id' => $selectedContextId ?: null])) }}">All sources</a>
                  @foreach ($sourceBreadcrumbs as $crumb)
                    <span>/</span>
                    @if ((int) $crumb->id === (int) ($selectedSource?->id ?? 0))
                      <strong>{{ $crumb->title }}</strong>
                    @else
                      <a href="{{ route('teacher.vocabulary.games.launch', array_filter(['source_id' => $crumb->id, 'teacher_subject_class_id' => $selectedContextId ?: null])) }}">{{ $crumb->title }}</a>
                    @endif
                  @endforeach
                </div>
                @unless ($selectedContextId)
                  <div class="text-muted small">Classroom is optional. Choose one only when you want to play in a class context.</div>
                @endunless
              </div>
              <div class="vl-source-list">
                @foreach ($sources as $source)
                  @include('vocabulary.components.launcher-source-node', [
                    'source' => $source,
                    'selectedContextId' => $selectedContextId,
                    'sourceMetadata' => $sourceMetadata,
                  ])
                @endforeach
              </div>
            @endif
          </div>

          <div class="card-body" x-show="tab === 'custom'" x-cloak>
            <form method="POST" action="{{ route('teacher.vocabulary.games.custom') }}">
              @csrf
              <input type="hidden" name="teacher_subject_class_id" value="{{ $selectedContextId }}">
              <input type="hidden" name="game" :value="customGame">
              <input type="hidden" name="difficulty" :value="customDifficulty">
              @error('teacher_subject_class_id') <div class="alert alert-warning">{{ $message }}</div> @enderror

              <div class="vl-word-launcher">
                <div>
                  <div class="vl-launcher-hat">
                    <i class="ti tabler-user-cog" aria-hidden="true"></i>
                    <span>Teacher mode</span>
                  </div>
                  <h2 class="vl-launcher-title">Custom Word Launcher</h2>
                  <p class="vl-launcher-copy">Type secret words, pick a game, and play.</p>
                </div>

                <div class="vl-launcher-set">
                  <label class="vl-launcher-label" for="customWords">Secret words</label>
                  <div class="vl-secret-field">
                    <textarea id="customWords" class="vl-secret-input vl-secret" :class="{ 'is-peeking': peeking }" name="words" rows="3" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" placeholder="Type your secret words...">{{ old('words') }}</textarea>
                    <button class="vl-peek-button" type="button"
                      @pointerdown.prevent="peeking = true"
                      @pointerup.window="peeking = false"
                      @pointerleave="peeking = false"
                      @blur="peeking = false"
                      @keydown.space.prevent="peeking = true"
                      @keyup.window="peeking = false"
                      aria-label="Hold to peek">
                      <i class="ti tabler-eye"></i>
                    </button>
                  </div>
                  <p class="vl-launcher-help">Up to 40 letters. Spaces, hyphens, and apostrophes allowed. Type one word or phrase per line.</p>
                  @error('words') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="vl-launcher-set">
                  <span class="vl-launcher-label">Choose a game</span>
                  <div class="vl-game-tiles" role="group" aria-label="Choose a game">
                    <button class="vl-game-tile" type="button" :aria-pressed="customGame === 'hangman'" @click="customGame = 'hangman'">
                      <span class="vl-game-icon"><i class="ti tabler-spy" aria-hidden="true"></i></span>
                      <span>
                        <strong>Floatie</strong>
                        <small>Guess letters, save the Floatie.</small>
                      </span>
                    </button>
                    <button class="vl-game-tile" type="button" :aria-pressed="customGame === 'missing_letter'" @click="customGame = 'missing_letter'">
                      <span class="vl-game-icon"><i class="ti tabler-square-letter-x" aria-hidden="true"></i></span>
                      <span>
                        <strong>Missing Letter</strong>
                        <small>Pick the letter that fits.</small>
                      </span>
                    </button>
                    <button class="vl-game-tile" type="button" :aria-pressed="customGame === 'spelling_choice'" @click="customGame = 'spelling_choice'">
                      <span class="vl-game-icon"><i class="ti tabler-checks" aria-hidden="true"></i></span>
                      <span>
                        <strong>Correct Spelling</strong>
                        <small>Pick the word spelled right.</small>
                      </span>
                    </button>
                  </div>
                </div>

                <div class="vl-launcher-set">
                  <span class="vl-launcher-label">Pick difficulty</span>
                  <div class="vl-difficulty-pills" role="group" aria-label="Pick difficulty">
                    <button class="vl-difficulty-pill" type="button" :aria-pressed="customDifficulty === 'sprout'" @click="customDifficulty = 'sprout'">
                      <i class="ti tabler-leaf" aria-hidden="true"></i>
                      Sprout
                    </button>
                    <button class="vl-difficulty-pill" type="button" :aria-pressed="customDifficulty === 'climber'" @click="customDifficulty = 'climber'">
                      <i class="ti tabler-mountain" aria-hidden="true"></i>
                      Climber
                    </button>
                    <button class="vl-difficulty-pill" type="button" :aria-pressed="customDifficulty === 'champion'" @click="customDifficulty = 'champion'">
                      <i class="ti tabler-medal" aria-hidden="true"></i>
                      Champion
                    </button>
                  </div>
                </div>

                <div class="vl-launcher-actions">
                  <button class="vl-cancel-button" type="button" @click="tab = 'source'">
                    <i class="ti tabler-x" aria-hidden="true"></i>
                    Cancel
                  </button>
                  <button class="vl-start-button" type="submit">
                    <i class="ti tabler-player-play" aria-hidden="true"></i>
                    Start the game
                  </button>
                </div>
              </div>
            </form>
          </div>
        </section>
      </div>
    </div>
  </div>
@endsection
