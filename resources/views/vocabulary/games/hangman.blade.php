@php
  $configData = Helper::appClasses();
  $isEmbeddedViewer = (bool) data_get($payload, 'viewer.embedded', false);
  $payload['allCapsWords'] = config('vocabulary.games.all_caps_words', []);
  $backLabel = $payload['routes']['backLabel'] ?? 'Vocab Games';
  $breadcrumb_links = $payload['routes']['navbarBreadcrumbs'] ?? [
    $backLabel => $payload['routes']['backToGames'] ?? route('vocabulary.games.hub'),
    'Floatie' => null,
  ];
@endphp

@extends($isEmbeddedViewer ? 'layouts/blankLayout' : 'layouts/layoutMaster')

@section('title', 'Floatie')

@section('page-style')
  @vite(['resources/css/vocabulary-games.css'])
@endsection

@section('content')
  <div class="container-fluid vocab-game {{ $isEmbeddedViewer ? 'vocab-game--embedded' : '' }}" x-data='vocabularyHangman(@json($payload))' x-init="init()">
    @include('vocabulary.components.game-shell', ['payload' => $payload])

    <audio x-ref="audio" preload="none"></audio>

    <div class="vg-stage">
      @include('vocabulary.components.floatie-scene')

      <section class="vg-play">
        <div class="vg-controls justify-content-between">
          <div class="vg-actions vg-difficulty-group" role="group" aria-label="Difficulty">
            <template x-for="key in ['sprout','climber','champion']" :key="key">
              <button class="vg-difficulty" type="button" :aria-pressed="difficulty === key" @click="difficulty = key; startWord()">
                <i class="ti" :class="{ 'tabler-leaf': key === 'sprout', 'tabler-mountain': key === 'climber', 'tabler-medal': key === 'champion' }"></i>
                <span x-text="difficultyLabel(key)"></span>
              </button>
            </template>
          </div>
          <div class="vg-case-switch" role="group" aria-label="Letter case">
            <button class="vg-case-button" type="button" :aria-pressed="caseMode === 'upper'" @click="caseMode = 'upper'">AA</button>
            <button class="vg-case-button" type="button" :aria-pressed="caseMode === 'lower'" @click="caseMode = 'lower'">aa</button>
          </div>
        </div>

        <div class="vg-audio" :class="{ 'is-locked': controlsLocked, 'is-nudging': audioNudge }">
          <button class="vg-audio-button" type="button" @click="playAudio" :class="{ 'is-nudging': audioNudge }" :aria-label="hasAudio ? 'Play word audio' : 'Start teacher-led word'">
            <i class="ti tabler-volume"></i>
          </button>
          <div>
            <div class="fw-bold" x-text="hasAudio ? 'Tap to hear the word' : 'Teacher says the word'"></div>
            <div class="small text-muted" x-text="statusMessage"></div>
          </div>
          <template x-if="feedbackToast">
            <div class="vg-feedback-toast"
              :class="{ 'is-wrong': feedbackToast.kind === 'wrong' }"
              x-text="feedbackToast.message"></div>
          </template>
        </div>

        <div class="vg-slots" aria-label="Word">
          <template x-for="(char, slotIndex) in chars" :key="slotIndex">
            <span class="vg-slot" :class="{ 'is-gap': char === ' ' }" x-text="char === ' ' ? '' : (filled[slotIndex] || '')"></span>
          </template>
        </div>

        <div class="vg-keyboard" aria-label="Keyboard">
          <template x-for="key in keys" :key="key">
            <button class="vg-key" type="button" :class="keyClass(key)" :aria-disabled="controlsLocked || disabledKeys.includes(key)" :disabled="status !== 'playing' || disabledKeys.includes(key)" @click="choose(key)" x-text="keyLabel(key)"></button>
          </template>
        </div>

        <div class="vg-actions justify-content-center">
          <button class="vg-tool vg-tool-hint" type="button" :disabled="controlsLocked" @click="useHint">
            <i class="ti tabler-bulb me-1"></i>
            <span>Hint</span>
            <span class="vg-tool-pill" x-show="difficulty !== 'champion'" x-text="hintsLeft + ' left'"></span>
          </button>
          <button class="vg-tool" type="button" @click="restartWord">
            <i class="ti tabler-refresh me-1"></i>
            Restart word
          </button>
        </div>

        <div class="vg-feedback" x-text="statusMessage"></div>
      </section>

    </div>

    <template x-if="status === 'loading'">
      <div class="vg-loading" role="status" aria-live="polite">
        <div class="vg-loading-card">
          <span class="vg-loading-spinner" aria-hidden="true"></span>
          <span>Getting your next word ready...</span>
        </div>
      </div>
    </template>

    @include('vocabulary.components.result-overlay', ['payload' => $payload])
  </div>
@endsection
