window.vocabularyMissingLetter = function vocabularyMissingLetter(payload) {
  return {
    payload: payload || {},
    words: payload.words || [],
    index: Math.max(0, Number(payload.initialIndex || 0)),
    difficulty: payload.difficulty || 'sprout',
    caseMode: 'lower',
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
    current: null,
    maskedText: '',
    maskIndex: null,
    choices: [],
    choiceStates: {},
    wrongAttempts: 0,
    mistakes: 0,

    init() {
      this.startRound();
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
      this.current = this.words[this.index] || null;
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
      this.audioPrimed = false;
      this.timer = this.seconds;
      this.feedback = this.isCustom && !this.current.audioUrl
        ? 'Tap start when the teacher says the word.'
        : 'Tap the sound button first.';
      this.statusMessage = this.feedback;
      this.maskedText = this.buildMaskedWord();
      this.choices = this.buildChoices();
    },

    difficultyLabel(key) {
      return ({ sprout: 'Sprout', climber: 'Climber', champion: 'Champion' })[key] || key;
    },

    choiceLabel(choice) {
      return this.caseMode === 'upper' ? String(choice).toUpperCase() : String(choice).toLowerCase();
    },

    choiceClass(choice) {
      return {
        'vg-key-correct': this.choiceStates[choice] === 'correct',
        'vg-key-wrong': this.choiceStates[choice] === 'wrong',
      };
    },

    choiceDisabled(choice) {
      return this.roundLocked || Boolean(this.choiceStates[choice]);
    },

    buildChoices() {
      const answer = this.correctChoice();
      const wrong = (this.current.wrongOptions || [])
        .join('')
        .replace(/[^A-Za-z]/g, '')
        .split('')
        .map((letter) => letter.toLowerCase())
        .filter((letter) => letter && letter !== answer);
      return Array.from(new Set([answer].concat(wrong, ['a', 'e', 'i', 'o', 'u'].filter((letter) => letter !== answer)))).slice(0, 4).sort();
    },

    buildMaskedWord() {
      const text = String(this.current?.displayText || this.current?.text || '');
      const chars = text.split('');
      const letterPositions = chars
        .map((char, index) => (/[A-Za-z]/.test(char) ? index : -1))
        .filter((index) => index >= 0);

      if (!letterPositions.length) {
        this.maskIndex = null;
        return text;
      }

      this.maskIndex = letterPositions[Math.floor(letterPositions.length / 2)];
      chars[this.maskIndex] = '_';

      return chars.join('');
    },

    maskedWord() {
      return this.maskedText;
    },

    correctChoice() {
      const text = String(this.current?.displayText || this.current?.text || '');
      return this.maskIndex === null ? '' : String(text[this.maskIndex] || '').toLowerCase();
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
        this.feedback = 'Choose the missing letter.';
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
      const correct = this.correctChoice();

      if (choice === correct) {
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
