function randomItem(items) {
  if (!Array.isArray(items) || items.length === 0) return null;
  return items[Math.floor(Math.random() * items.length)];
}

const SynthFx = (() => {
  let context = null;

  function ensureContext() {
    if (!context && typeof AudioContext !== 'undefined') {
      try {
        context = new AudioContext();
      } catch (error) {
        context = null;
      }
    }

    return context;
  }

  async function tone({ freq = 440, type = 'sine', dur = 0.2, vol = 0.14, slide = 0 }) {
    const audioContext = ensureContext();
    if (!audioContext) return;

    if (audioContext.state === 'suspended') {
      try {
        await audioContext.resume();
      } catch (error) {
        return;
      }
    }

    const oscillator = audioContext.createOscillator();
    const gain = audioContext.createGain();
    oscillator.type = type;
    oscillator.frequency.value = freq;

    if (slide) {
      oscillator.frequency.exponentialRampToValueAtTime(
        Math.max(40, freq + slide),
        audioContext.currentTime + dur
      );
    }

    gain.gain.value = 0;
    gain.gain.linearRampToValueAtTime(vol, audioContext.currentTime + 0.02);
    gain.gain.exponentialRampToValueAtTime(0.0001, audioContext.currentTime + dur);
    oscillator.connect(gain);
    gain.connect(audioContext.destination);
    oscillator.start();
    oscillator.stop(audioContext.currentTime + dur + 0.02);
  }

  return {
    pop: () => void tone({ freq: 320, type: 'triangle', dur: 0.18, slide: -180, vol: 0.16 }),
    ding: () => {
      void tone({ freq: 740, dur: 0.12, vol: 0.1 });
      window.setTimeout(() => void tone({ freq: 988, dur: 0.18, vol: 0.1 }), 80);
    },
    hint: () => void tone({ freq: 880, dur: 0.16, type: 'triangle', vol: 0.09 }),
    nudge: () => void tone({ freq: 520, dur: 0.09, type: 'triangle', vol: 0.08 }),
    win: () => [523, 659, 784, 1047].forEach((freq, index) => {
      window.setTimeout(() => void tone({ freq, dur: 0.2, vol: 0.12 }), index * 110);
    }),
    sad: () => {
      void tone({ freq: 360, type: 'sine', dur: 0.25, slide: -160, vol: 0.11 });
      window.setTimeout(() => void tone({ freq: 280, dur: 0.35, slide: -120, vol: 0.11 }), 220);
    }
  };
})();

const audioCache = new Map();

async function playAudioUrl(url) {
  if (!url) return false;

  let audio = audioCache.get(url);

  try {
    if (!audio) {
      audio = new Audio(url);
      audio.preload = 'metadata';
      audio.addEventListener('error', () => {
        audioCache.delete(url);
      }, { once: true });
      audioCache.set(url, audio);
    }

    audio.pause();
    audio.currentTime = 0;
    await audio.play();

    return true;
  } catch (error) {
    audioCache.delete(url);

    return false;
  }
}

async function playAsset(assetValue) {
  const selected = Array.isArray(assetValue) ? randomItem(assetValue) : assetValue;
  return playAudioUrl(selected);
}

window.vocabularyGameSoundFx = {
  async correct(assets = {}) {
    if (await playAsset(assets.correctSound)) return;
    SynthFx.ding();
  },

  async wrong(assets = {}) {
    if (await playAsset(assets.errorSound)) return;
    SynthFx.pop();
  },

  async hint(assets = {}) {
    if (await playAsset(assets.hintSound)) return;
    SynthFx.hint();
  },

  async nudge(assets = {}) {
    if (await playAsset(assets.nudgeSound)) return;
    SynthFx.nudge();
  },

  async pop(assets = {}) {
    if (await playAsset(assets.popSounds || assets.popSound)) return;
    SynthFx.pop();
  },

  async win(assets = {}) {
    if (await playAsset(assets.winSounds || assets.completeSound || assets.cheerSound)) return;
    SynthFx.win();
  },

  async loss(assets = {}) {
    if (await playAsset(assets.lossSounds || assets.errorSound)) return;
    SynthFx.sad();
  }
};
