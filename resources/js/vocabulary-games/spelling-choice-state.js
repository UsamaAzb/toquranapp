window.vocabularySpellingChoice = function vocabularySpellingChoice(payload) {
  return {
    payload: payload || {},
    words: payload.words || [],
    index: Math.max(0, Number(payload.initialIndex || 0)),
    difficulty: payload.difficulty || 'sprout',
    status: 'playing',
    audioPrimed: false,
    roundLocked: false,
    audioNudge: false,
    audioNudgeHandle: null,
    timer: null,
    timerHandle: null,
    feedback: '',
    statusMessage: '',
    resultTitle: '',
    resultMessage: '',
    resultWord: '',
    resultVariant: '',
    choices: [],
    choiceStates: {},
    wrongAttempts: 0,
    mistakes: 0,

    init() {
      this.startRound();
    },

    get current() {
      return this.words[this.index] || null;
    },

    get isCustom() {
      return Boolean(this.payload.custom || this.current?.custom);
    },

    get hasAudio() {
      return Boolean(this.current?.audioUrl);
    },

    get controlsLocked() {
      return this.roundLocked || !this.audioPrimed;
    },

    get seconds() {
      return { sprout: 10, climber: 7, champion: 4 }[this.difficulty || 'sprout'] || 10;
    },

    get meta() {
      return { mistakes: 6 };
    },

    get progressText() {
      return this.words.length ? `Word ${this.index + 1} of ${this.words.length}` : '';
    },

    startRound() {
      window.clearInterval(this.timerHandle);
      this.timerHandle = null;
      if (!this.current) {
        this.resultTitle = 'Good job!';
        this.resultMessage = 'You finished this vocabulary set.';
        this.resultWord = '';
        this.status = 'set_complete';
        return;
      }
      this.status = 'playing';
      this.roundLocked = false;
      this.resultTitle = '';
      this.resultMessage = '';
      this.resultWord = '';
      this.choiceStates = {};
      this.wrongAttempts = 0;
      this.mistakes = 0;
      this.timer = this.seconds;
      this.audioPrimed = false;
      this.feedback = this.isCustom && !this.current.audioUrl
        ? 'Tap start when the teacher says the word.'
        : 'Tap the sound button first.';
      this.statusMessage = this.feedback;
      this.choices = this.buildChoices();
    },

    difficultyLabel(key) {
      return ({ sprout: 'Sprout', climber: 'Climber', champion: 'Champion' })[key] || key;
    },

    buildChoices() {
      if (!this.current) return [];
      const correct = this.current.displayText || this.current.text;
      const options = Array.from(new Set([correct].concat(this.current.wrongOptions || [])));
      this.spellingDistractors(correct).forEach((option) => {
        if (options.length < 4 && !options.some((existing) => String(existing).toLowerCase() === String(option).toLowerCase())) {
          options.push(option);
        }
      });
      return this.shuffle(options.slice(0, 4));
    },

    spellingDistractors(word) {
      const raw = String(word || '').trim();
      const lower = raw.toLowerCase();
      const variants = [];

      if (lower.length > 2) {
        variants.push(lower.slice(0, -1));
        variants.push(lower.slice(0, -1) + lower.charAt(lower.length - 1) + lower.charAt(lower.length - 1));
      }

      variants.push(lower.replace(/[aeiou]/, (vowel) => ({ a: 'e', e: 'i', i: 'e', o: 'u', u: 'o' })[vowel] || 'a'));
      variants.push(lower.replace(/[bcdfghjklmnpqrstvwxyz]/, (letter) => ({ c: 'k', k: 'c', s: 'c', f: 'ph' })[letter] || `${letter}h`));

      return variants
        .map((variant) => variant.trim())
        .filter((variant) => variant && variant.toLowerCase() !== lower);
    },

    displayChoice(choice) {
      const raw = String(choice || '').trim();
      if (!raw) return '';
      if (this.isAllCapsWord(raw)) return raw.toUpperCase();

      return raw
        .toLowerCase()
        .replace(/[A-Za-z]+/g, (part) => part.charAt(0).toUpperCase() + part.slice(1));
    },

    isAllCapsWord(value) {
      const allCaps = this.payload.allCapsWords || ['DIY', 'FAQ', 'NASA', 'OK', 'TV', 'UK', 'USA'];
      return allCaps.map((word) => String(word).toUpperCase()).includes(String(value).toUpperCase());
    },

    shuffle(options) {
      const shuffled = [...options];
      for (let i = shuffled.length - 1; i > 0; i -= 1) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
      }
      return shuffled;
    },

    choiceClass(choice) {
      return {
        'vg-choice-correct': this.choiceStates[choice] === 'correct',
        'vg-choice-wrong': this.choiceStates[choice] === 'wrong',
      };
    },

    choiceDisabled(choice) {
      return this.roundLocked || Boolean(this.choiceStates[choice]);
    },

    async playAudio() {
      if (this.$refs.audio && this.current.audioUrl) {
        this.$refs.audio.src = this.current.audioUrl;
        try {
          await this.$refs.audio.play();
          this.audioPrimed = true;
        } catch (error) {
          this.feedback = 'Audio is not available. Ask your teacher to say it.';
          this.statusMessage = this.feedback;
          this.audioPrimed = this.isCustom;
        }
      } else {
        this.audioPrimed = this.isCustom;
      }
      if (this.audioPrimed) {
        this.feedback = 'Choose the correct spelling.';
        this.statusMessage = this.feedback;
        this.startTimer();
      }
    },

    startTimer() {
      if (this.timerHandle) return;
      this.timerHandle = window.setInterval(() => {
        this.timer -= 1;
        if (this.timer <= 0) this.finish('timeout');
      }, 1000);
    },

    answer(choice) {
      if (this.status !== 'playing') return;
      if (this.controlsLocked) {
        this.nudgeSoundPrompt();
        return;
      }
      const correct = String(this.current.displayText || this.current.text).toLowerCase();

      if (String(choice).toLowerCase() === correct) {
        this.choiceStates = { ...this.choiceStates, [choice]: 'correct' };
        this.feedback = 'Nice!';
        window.vocabularyGameSoundFx?.correct(this.payload.assets || {});
        this.roundLocked = true;
        window.setTimeout(() => this.finish('correct'), 450);
        return;
      }

      this.choiceStates = { ...this.choiceStates, [choice]: 'wrong' };
      this.mistakes = Math.min(this.meta.mistakes, this.mistakes + 1);
      window.vocabularyGameSoundFx?.wrong(this.payload.assets || {});

      if (this.wrongAttempts < 1) {
        this.wrongAttempts += 1;
        this.feedback = 'Try one more time.';
        return;
      }

      this.feedback = 'Try again.';
      this.roundLocked = true;
      window.setTimeout(() => this.finish('incorrect'), 450);
    },

    nudgeSoundPrompt() {
      this.feedback = 'Tap the sound button first.';
      this.statusMessage = this.feedback;
      window.vocabularyGameSoundFx?.nudge(this.payload.assets || {});
      this.audioNudge = true;
      window.clearTimeout(this.audioNudgeHandle);
      this.audioNudgeHandle = window.setTimeout(() => {
        this.audioNudge = false;
      }, 650);
    },

    finish(status) {
      window.clearInterval(this.timerHandle);
      this.timerHandle = null;
      this.status = status;
      this.resultWord = String(this.current?.displayText || this.current?.text || '');

      if (status === 'correct') {
        this.resultTitle = 'Correct!';
        this.resultMessage = 'The word was';
        window.vocabularyGameSoundFx?.win(this.payload.assets || {});
      } else if (status === 'set_complete') {
        this.resultTitle = 'Good job!';
        this.resultMessage = 'You finished this vocabulary set.';
        this.resultWord = '';
        window.vocabularyGameSoundFx?.win(this.payload.assets || {});
      } else {
        this.resultTitle = status === 'timeout' ? 'Time is up' : 'Try again';
        this.resultMessage = 'The word was';
        window.vocabularyGameSoundFx?.loss(this.payload.assets || {});
      }
    },

    nextRound() {
      if (this.index >= this.words.length - 1) {
        this.resultTitle = 'Good job!';
        this.resultMessage = 'You finished this vocabulary set.';
        this.resultWord = '';
        this.status = 'set_complete';
        return;
      }
      this.index += 1;
      this.startRound();
    },

    restartSet() {
      this.index = 0;
      this.startRound();
    },

    closeResult() {
      if (this.status === 'set_complete') {
        this.status = 'complete_closed';
        this.statusMessage = 'Good job! You finished this vocabulary set.';
      }
    }
  };
};
