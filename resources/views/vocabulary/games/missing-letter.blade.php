@php
  $configData = Helper::appClasses();
  $isEmbeddedViewer = (bool) data_get($payload, 'viewer.embedded', false);
  $backLabel = $payload['routes']['backLabel'] ?? 'Vocab Games';
  $breadcrumb_links = $payload['routes']['navbarBreadcrumbs'] ?? [
    $backLabel => $payload['routes']['backToGames'] ?? route('vocabulary.games.hub'),
    'Missing Letter' => null,
  ];
@endphp

@extends($isEmbeddedViewer ? 'layouts/blankLayout' : 'layouts/layoutMaster')

@section('title', 'Missing Letter')

@section('page-style')
  @vite(['resources/css/vocabulary-games.css'])
@endsection

@section('content')
  <div class="container-fluid vocab-game {{ $isEmbeddedViewer ? 'vocab-game--embedded' : '' }}" x-data='vocabularyMissingLetter(@json($payload))' x-init="init()">
    @include('vocabulary.components.game-shell', ['payload' => $payload])
    <audio x-ref="audio" preload="none"></audio>

    <div class="vg-stage">
      @include('vocabulary.components.floatie-scene')
      <section class="vg-play">
        <div class="vg-controls justify-content-between">
          <div class="vg-actions vg-difficulty-group" role="group" aria-label="Difficulty">
            <template x-for="key in ['sprout','climber','champion']" :key="key">
              <button class="vg-difficulty" type="button" :aria-pressed="difficulty === key" @click="difficulty = key; startRound()">
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
        </div>
        <div class="display-5 fw-bold text-center" x-text="maskedWord()"></div>
        <div class="vg-choices">
          <template x-for="choice in choices" :key="choice">
            <button class="vg-key" type="button" :class="choiceClass(choice)" :aria-disabled="controlsLocked || choiceDisabled(choice)" :disabled="choiceDisabled(choice)" @click="answer(choice)" x-text="choiceLabel(choice)"></button>
          </template>
        </div>
        <div class="vg-feedback" x-text="feedback"></div>
      </section>
    </div>

    @include('vocabulary.components.result-overlay', ['payload' => $payload])
  </div>
@endsection
