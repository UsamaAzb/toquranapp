@php
  $isEmbeddedViewer = (bool) data_get($payload, 'viewer.embedded', false);
@endphp

<template x-if="['win','loss','timeout','set_complete','correct','incorrect'].includes(status)">
  <div class="vg-result" role="dialog" aria-modal="true" aria-live="polite">
    <div class="vg-result-panel" :class="{ 'is-celebration': ['win','correct','set_complete'].includes(status) }" tabindex="-1">
      <template x-if="['win','correct','set_complete'].includes(status)">
        <div class="vg-result-confetti" aria-hidden="true">
          <span></span><span></span><span></span><span></span><span></span><span></span>
        </div>
      </template>
      <div class="vg-result-hero" :class="{ 'is-soft': ['loss','timeout','incorrect'].includes(status) }">
        <i class="ti"
          :class="{
            'tabler-confetti': ['win','correct'].includes(status),
            'tabler-trophy': status === 'set_complete',
            'tabler-alarm': status === 'timeout',
            'tabler-cloud-rain': ['loss','incorrect'].includes(status)
          }"
          aria-hidden="true"></i>
      </div>
      <h2 class="h4 mb-2" x-text="resultTitle || (status === 'correct' ? 'Correct!' : status === 'incorrect' ? 'Try again' : status === 'set_complete' ? 'Good job!' : 'Good effort')"></h2>
      <template x-if="resultWord">
        <p class="vg-result-word-line">
          <span x-text="resultMessage || 'The word was'"></span>
          <strong x-text="resultWord"></strong>
        </p>
      </template>
      <template x-if="!resultWord">
        <p class="text-muted" x-text="resultMessage || statusMessage"></p>
      </template>
      <div class="d-flex flex-wrap gap-2 justify-content-center">
        <template x-if="status === 'win' || status === 'correct'">
          <button class="btn btn-primary" type="button" @click="typeof nextWord === 'function' ? nextWord() : nextRound()">
            Next
          </button>
        </template>
        <template x-if="status === 'loss' || status === 'timeout' || status === 'incorrect'">
          <button class="btn btn-primary" type="button" @click="typeof restartWord === 'function' ? restartWord() : startRound()">
            Try again
          </button>
        </template>
        <template x-if="status === 'set_complete'">
          <button class="btn btn-primary" type="button" @click="restartSet()">
            Play again
          </button>
        </template>
        @unless ($isEmbeddedViewer)
          <template x-if="status === 'set_complete'">
            <a class="btn btn-outline-secondary" href="{{ $payload['routes']['backToGames'] ?? route('vocabulary.games.hub') }}">
              Back to {{ $payload['routes']['backLabel'] ?? 'Games' }}
            </a>
          </template>
        @endif
      </div>
    </div>
  </div>
</template>
