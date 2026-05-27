<?php

namespace Tests\Unit\Vocabulary;

use App\Services\Vocabulary\WrongOptionGenerator;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class MissingLetterGameTest extends TestCase
{
    public function test_missing_letter_options_include_correct_once_and_no_duplicates(): void
    {
        $options = app(WrongOptionGenerator::class)->missingLetterOptions('car', 'a', 3);

        $this->assertContains('a', $options);
        $this->assertCount(count(array_unique($options)), $options);
    }

    public function test_timed_choice_difficulty_seconds_are_configured(): void
    {
        $this->assertSame(10, config('vocabulary.games.timed_choices.sprout'));
        $this->assertSame(7, config('vocabulary.games.timed_choices.climber'));
        $this->assertSame(4, config('vocabulary.games.timed_choices.champion'));
    }

    public function test_missing_letter_mask_and_answer_stay_aligned_for_phrases(): void
    {
        if (! $this->nodeIsAvailable()) {
            $this->markTestSkipped('Node.js is not available for the Missing Letter state-machine check.');
        }

        $script = <<<'JS'
const fs = require('fs');
const vm = require('vm');

const code = fs.readFileSync('resources/js/vocabulary-games/missing-letter-state.js', 'utf8');
const window = {
  clearInterval() {},
  setInterval() { return null; },
  clearTimeout() {},
  setTimeout() { return null; },
  vocabularyGameSoundFx: {},
};
window.window = window;

const sandbox = { window };
vm.createContext(sandbox);
vm.runInContext(code, sandbox);

const cases = [
  ['cat', 'c_t', 'a'],
  ['ice cream', 'ice c_eam', 'r'],
  ['ice-cream', 'ice-c_eam', 'r'],
  ["rock 'n' roll", "rock '_' roll", 'n'],
  ['umbrella', 'umbr_lla', 'e'],
];

const results = cases.map(([text]) => {
  const game = sandbox.window.vocabularyMissingLetter({
    words: [{ text, displayText: text, wrongOptions: [] }],
    difficulty: 'sprout',
    assets: {},
  });

  game.startRound();

  return [text, game.maskedWord(), game.correctChoice(), game.choices.includes(game.correctChoice())];
});

process.stdout.write(JSON.stringify(results));
JS;

        $process = new Process(['node', '-e', $script], base_path());
        $process->run();

        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());

        $this->assertSame([
            ['cat', 'c_t', 'a', true],
            ['ice cream', 'ice c_eam', 'r', true],
            ['ice-cream', 'ice-c_eam', 'r', true],
            ["rock 'n' roll", "rock '_' roll", 'n', true],
            ['umbrella', 'umbr_lla', 'e', true],
        ], json_decode($process->getOutput(), true));
    }

    private function nodeIsAvailable(): bool
    {
        $process = new Process(['node', '--version'], base_path());
        $process->run();

        return $process->isSuccessful();
    }
}
