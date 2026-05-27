<section class="vg-scene"
  :class="{
    'is-won': ['win', 'correct', 'set_complete'].includes(status),
    'is-landed': ['loss', 'timeout', 'incorrect'].includes(status)
  }"
  aria-label="Floatie scene">
  <div class="vg-clouds" aria-hidden="true">
    <span class="vg-cloud vg-cloud-1"></span>
    <span class="vg-cloud vg-cloud-2"></span>
    <span class="vg-cloud vg-cloud-3"></span>
  </div>

  <template x-if="timer !== null && status === 'playing'">
    <div class="vg-scene-badge vg-scene-badge--timer" :class="{ 'is-low': timer <= 5 }" aria-live="polite">
      <i class="ti tabler-clock" aria-hidden="true"></i>
      <span x-text="timer + 's'"></span>
    </div>
  </template>

  <div class="vg-floatie-stack">
    <div class="vg-balloons" aria-hidden="true">
      <template x-for="number in (meta?.mistakes || 6)" :key="number">
        <span class="vg-balloon"
          :class="{ 'is-popped': number <= (mistakes || 0) }"
          :style="`--balloon-color: ${['#f2a5b6','#ffd27a','#9cd6f2','#b9e6a1','#c9b6f2','#ffb098'][(number - 1) % 6]}`"></span>
      </template>
    </div>
    <div class="vg-floatie"
      :class="{
        'is-happy': ['win', 'set_complete'].includes(status),
        'is-sad': ['loss'].includes(status),
        'is-sleepy': ['timeout'].includes(status),
        'is-win-fly': resultVariant === 'fly',
        'is-win-dance': resultVariant === 'dance',
        'is-win-spark': resultVariant === 'spark',
        'is-loss-land': resultVariant === 'land',
        'is-loss-rain': resultVariant === 'rain',
        'is-loss-sleepy': resultVariant === 'sleepy'
      }"
      :style="typeof floatieOffset === 'function' ? `--floatie-y: ${floatieOffset()}` : ''"
      aria-hidden="true">
      <span class="vg-floatie-body"></span>
      <span class="vg-floatie-cheek vg-floatie-cheek-l"></span>
      <span class="vg-floatie-cheek vg-floatie-cheek-r"></span>
      <span class="vg-floatie-eye vg-floatie-eye-l"></span>
      <span class="vg-floatie-eye vg-floatie-eye-r"></span>
      <span class="vg-floatie-mouth"></span>
    </div>
  </div>

  <div class="vg-trampoline" aria-hidden="true"></div>

  <template x-if="['win','set_complete'].includes(status)">
    <div class="vg-confetti" aria-hidden="true">
      <template x-for="number in 18" :key="number">
        <span :style="`
          left: ${(number * 37) % 100}%;
          background: ${['#f2a5b6','#ffd27a','#9cd6f2','#b9e6a1','#c9b6f2','#ffb098'][number % 6]};
          animation-delay: ${(number % 7) * .12}s;
        `"></span>
      </template>
    </div>
  </template>
</section>
