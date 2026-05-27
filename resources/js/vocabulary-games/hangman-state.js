const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

const DIFFICULTY = {
  sprout: { label: 'Sprout', mistakes: 6, distractors: 6, ordered: false, repeatedAll: true, timerPerLetter: 0 },
  climber: { label: 'Climber', mistakes: 6, distractors: 6, ordered: true, repeatedAll: false, timerPerLetter: 0 },
  champion: { label: 'Champion', mistakes: 6, distractors: 0, ordered: true, repeatedAll: false, timerPerLetter: 6 }
};

const WIN_VARIANTS = ['fly', 'dance', 'spark'];
const LOSS_VARIANTS = ['land', 'rain', 'sleepy'];

function cleanWord(word) {
  return String(word || '').trim();
}

function visibleCharacters(word) {
  return cleanWord(word).toUpperCase().split('');
}

function nonSpaceLength(word) {
  return visibleCharacters(word).filter((char) => char !== ' ').length;
}

function displayWord(word, allCapsWords = []) {
  const raw = cleanWord(word);
  if (allCapsWords.includes(raw.toUpperCase())) return raw.toUpperCase();
  const lower = raw.toLowerCase();
  return lower.charAt(0).toUpperCase() + lower.slice(1);
}

function shuffled(items) {
  const copy = items.slice();
  for (let index = copy.length - 1; index > 0; index -= 1) {
    const target = Math.floor(Math.random() * (index + 1));
    [copy[index], copy[target]] = [copy[target], copy[index]];
  }
  return copy;
}

function buildKeys(word, meta) {
  const upper = cleanWord(word).toUpperCase();
  const letters = Array.from(new Set(upper.replace(/[^A-Z]/g, '').split('').filter(Boolean)));
  const hasDash = upper.includes('-');

  if (meta.timerPerLetter > 0) {
    return hasDash ? ALPHABET.concat('-') : ALPHABET.slice();
  }

  const wrong = shuffled(ALPHABET.filter((letter) => !letters.includes(letter))).slice(0, meta.distractors);
  const keys = shuffled(letters.concat(wrong));
  if (hasDash) keys.splice(Math.floor(Math.random() * (keys.length + 1)), 0, '-');

  return keys;
}

function hintCount(difficulty, word) {
  const length = nonSpaceLength(word);
  if (difficulty === 'sprout') return length <= 5 ? 1 : 2;
  if (difficulty === 'climber') return length <= 5 ? 0 : 1;
  return 0;
}

function randomItem(items) {
  return items[Math.floor(Math.random() * items.length)];
}

window.vocabularyHangman = function vocabularyHangman(payload) {
  return {
    payload: payload || {},
    words: payload.words || [],
    index: Math.max(0, Number(payload.initialIndex || 0)),
    difficulty: payload.difficulty || 'sprout',
    status: 'loading',
    filled: [],
    mistakes: 0,
    keys: [],
    disabledKeys: [],
    keyStates: {},
    statusMessage: '',
    feedbackToast: null,
    audioPrimed: false,
    caseMode: 'lower',
    hintsLeft: 0,
    timer: null,
    timerHandle: null,
    feedbackHandle: null,
    loadingHandle: null,
    flashHandle: null,
    audioNudgeHandle: null,
    audioNudge: false,
    resultTitle: '',
    resultMessage: '',
    resultWord: '',
    resultVariant: '',

    init() {
      this.startWord();
    },

    get word() {
      return this.words[this.index] || {};
    },

    get chars() {
      return visibleCharacters(this.word.text);
    },

    get prettyWord() {
      return this.word.displayText || displayWord(this.word.text, this.payload.allCapsWords || []);
    },

    get displayChars() {
      return String(this.prettyWord || '').split('');
    },

    get meta() {
      return DIFFICULTY[this.difficulty] || DIFFICULTY.sprout;
    },

    difficultyLabel(key) {
      return (DIFFICULTY[key] || DIFFICULTY.sprout).label;
    },

    get hasAudio() {
      return Boolean(this.word.audioUrl);
    },

    get isCustom() {
      return Boolean(this.payload.custom || this.word.custom);
    },

    get controlsLocked() {
      return this.status !== 'playing' || !this.audioPrimed;
    },

    get progressText() {
      return `Word ${Math.min(this.index + 1, this.words.length || 1)} of ${this.words.length || 1}`;
    },

    get progressDots() {
      return Array.from({ length: Math.min(this.words.length, 10) }, (_, dot) => dot);
    },

    startWord() {
      window.clearInterval(this.timerHandle);
      window.clearTimeout(this.loadingHandle);
      this.timerHandle = null;
      this.status = 'loading';
      this.resultTitle = '';
      this.resultMessage = '';
      this.resultWord = '';
      this.resultVariant = '';
      this.feedbackToast = null;

      if (!this.words[this.index]) {
        this.finish('set_complete');
        return;
      }

      this.filled = this.chars.map((char) => (char === ' ' ? ' ' : ''));
      this.mistakes = 0;
      this.disabledKeys = [];
      this.keyStates = {};
      this.statusMessage = this.isCustom && !this.hasAudio ? 'Tap start when the teacher says the word.' : 'Tap the sound button first.';
      this.audioPrimed = false;
      this.keys = buildKeys(this.word.text, this.meta);
      this.hintsLeft = hintCount(this.difficulty, this.word.text);
      this.timer = this.meta.timerPerLetter > 0 ? this.meta.timerPerLetter * nonSpaceLength(this.word.text) : null;

      this.loadingHandle = window.setTimeout(() => {
        this.status = 'playing';
        if (this.audioPrimed) this.startTimer();
      }, 450);
    },

    async playAudio() {
      this.statusMessage = this.hasAudio ? 'Listen, then choose.' : 'Teacher says the word, then play.';
      const element = this.$refs.audio;
      if (element && this.word.audioUrl) {
        element.src = this.word.audioUrl;
        try {
          await element.play();
          this.audioPrimed = true;
        } catch (error) {
          this.statusMessage = 'Audio is not available. Ask your teacher to say the word.';
          this.audioPrimed = this.isCustom;
          this.flashFeedback('wrong', 'Audio is not available.');
        }
      } else {
        this.audioPrimed = this.isCustom;
      }
      if (this.audioPrimed) this.startTimer();
    },

    startTimer() {
      if (!this.timer || this.timerHandle || this.status !== 'playing') return;
      this.timerHandle = window.setInterval(() => {
        this.timer -= 1;
        if (this.timer <= 0) {
          this.finish('timeout');
        }
      }, 1000);
    },

    choose(key) {
      if (this.controlsLocked) {
        this.nudgeSoundPrompt();
        return;
      }
      const target = String(key).toUpperCase();
      const nextIndex = this.nextHiddenIndex(target);

      if (nextIndex === -1) {
        this.keyStates[target] = 'wrong';
        if (!this.disabledKeys.includes(target)) this.disabledKeys.push(target);
        this.mistake(`Try a different ${target === '-' ? 'dash' : 'letter'}.`);
        return;
      }

      if (this.meta.ordered && nextIndex !== this.firstHiddenIndex()) {
        this.flashTemporaryWrong(target);
        this.mistake('Not yet. Listen for the next sound.');
        return;
      }

      if (this.meta.repeatedAll) {
        this.chars.forEach((char, index) => {
          if (char === target) this.filled[index] = this.slotChar(index);
        });
      } else {
        this.filled[nextIndex] = this.slotChar(nextIndex);
      }

      if (!this.chars.some((char, index) => char !== ' ' && !this.filled[index])) {
        this.keyStates[target] = 'correct';
        this.flashFeedback('correct', 'Nice!');
        this.finish('win');
        return;
      }

      if (this.nextHiddenIndex(target) === -1) {
        if (!this.disabledKeys.includes(target)) this.disabledKeys.push(target);
        this.keyStates[target] = 'correct';
      } else {
        this.keyStates[target] = 'partial';
      }

      window.vocabularyGameSoundFx?.correct(this.payload.assets || {});
      this.statusMessage = 'Choose the next letter.';
      this.flashFeedback('correct', randomItem(['Nice!', 'Great listening!', 'Keep going!']));
    },

    useHint() {
      if (this.controlsLocked) {
        this.nudgeSoundPrompt();
        return;
      }
      if (this.difficulty === 'champion') {
        this.flashFeedback('correct', 'Champion mode has no letter hints. You can do it!', 1600);
        window.vocabularyGameSoundFx?.hint(this.payload.assets || {});
        return;
      }
      if (this.hintsLeft <= 0) {
        this.flashFeedback('wrong', 'No hints left for this word.');
        return;
      }

      const target = this.hintTarget();
      if (!target) return;

      this.hintsLeft -= 1;
      this.keyStates[target] = 'hinted';
      this.statusMessage = `Try the ${this.keyLabel(target)} key.`;
      this.flashFeedback('correct', `Try the "${this.keyLabel(target)}" key!`, 1600);
      window.vocabularyGameSoundFx?.hint(this.payload.assets || {});
    },

    hintTarget() {
      const first = this.firstHiddenIndex();
      if (first === -1) return null;

      if (this.meta.ordered) {
        return this.chars[first];
      }

      const remaining = Array.from(new Set(this.chars.filter((char, index) => {
        return char !== ' ' && !this.filled[index] && this.keyStates[char] !== 'hinted';
      })));

      return randomItem(remaining.length ? remaining : [this.chars[first]]);
    },

    nextWord() {
      if (this.index >= this.words.length - 1) {
        this.finish('set_complete');
        return;
      }
      this.index += 1;
      this.startWord();
    },

    restartWord() {
      this.startWord();
    },

    restartSet() {
      this.index = 0;
      this.startWord();
    },

    closeResult() {
      if (this.status === 'set_complete') {
        this.status = 'complete_closed';
        this.statusMessage = 'Good job! You finished this vocabulary set.';
        this.feedbackToast = null;
      }
    },

    finish(status) {
      window.clearInterval(this.timerHandle);
      window.clearTimeout(this.loadingHandle);
      this.timerHandle = null;
      this.loadingHandle = null;
      this.status = status;
      this.resultWord = this.prettyWord;

      if (status === 'win') {
        this.resultTitle = 'Good job!';
        this.resultMessage = 'The word was';
        this.resultVariant = randomItem(WIN_VARIANTS);
        window.vocabularyGameSoundFx?.win(this.payload.assets || {});
      } else if (status === 'set_complete') {
        this.resultTitle = 'Good job!';
        this.resultMessage = 'You finished this vocabulary set.';
        this.resultWord = '';
        this.resultVariant = randomItem(WIN_VARIANTS);
        window.vocabularyGameSoundFx?.win(this.payload.assets || {});
      } else {
        this.resultTitle = status === 'timeout' ? 'Time is up' : 'Try again';
        this.resultMessage = 'The word was';
        this.resultVariant = randomItem(LOSS_VARIANTS);
        window.vocabularyGameSoundFx?.loss(this.payload.assets || {});
      }
    },

    mistake(message) {
      this.mistakes += 1;
      this.statusMessage = message;
      window.vocabularyGameSoundFx?.pop(this.payload.assets || {});
      this.flashFeedback('wrong', message);
      if (this.mistakes >= this.meta.mistakes) {
        this.finish('loss');
      }
    },

    nudgeSoundPrompt() {
      this.statusMessage = 'Tap the sound button first.';
      this.flashFeedback('wrong', 'Tap the sound button first.', 1200);
      window.vocabularyGameSoundFx?.nudge(this.payload.assets || {});
      this.audioNudge = true;
      window.clearTimeout(this.audioNudgeHandle);
      this.audioNudgeHandle = window.setTimeout(() => {
        this.audioNudge = false;
      }, 650);
    },

    flashTemporaryWrong(target) {
      this.keyStates[target] = 'wrong';
      window.clearTimeout(this.flashHandle);
      this.flashHandle = window.setTimeout(() => {
        if (this.keyStates[target] === 'wrong' && !this.disabledKeys.includes(target)) {
          delete this.keyStates[target];
        }
      }, 650);
    },

    flashFeedback(kind, message, duration = 1100) {
      this.feedbackToast = { kind, message };
      window.clearTimeout(this.feedbackHandle);
      this.feedbackHandle = window.setTimeout(() => {
        this.feedbackToast = null;
      }, duration);
    },

    firstHiddenIndex() {
      return this.chars.findIndex((char, index) => char !== ' ' && !this.filled[index]);
    },

    nextHiddenIndex(target) {
      return this.chars.findIndex((char, index) => char === target && !this.filled[index]);
    },

    slotChar(index) {
      const source = this.displayChars[index] || this.chars[index] || '';
      return source;
    },

    displaySlot(index) {
      return this.slotChar(index);
    },

    keyLabel(key) {
      const char = String(key).toUpperCase();
      if (char === '-') return '-';
      return this.caseMode === 'lower' ? char.toLowerCase() : char;
    },

    keyClass(key) {
      return {
        'vg-key-correct': this.keyStates[key] === 'correct',
        'vg-key-partial': this.keyStates[key] === 'partial',
        'vg-key-wrong': this.keyStates[key] === 'wrong',
        'vg-key-hinted': this.keyStates[key] === 'hinted',
        'vg-key-dash': key === '-'
      };
    },

    floatieOffset() {
      return `${Math.min(4.5, this.mistakes * 0.75)}rem`;
    }
  };
};
