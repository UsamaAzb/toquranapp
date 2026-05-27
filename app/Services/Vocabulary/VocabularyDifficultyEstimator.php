<?php

namespace App\Services\Vocabulary;

use Illuminate\Support\Str;

class VocabularyDifficultyEstimator
{
    public function estimate(string $word): string
    {
        return $this->estimateWithReason($word)['level'];
    }

    /**
     * @return array{level: string, reason: string}
     */
    public function estimateWithReason(string $word): array
    {
        $normalized = Str::of($word)
            ->lower()
            ->replaceMatches('/[^a-z\s-]+/', '')
            ->squish()
            ->toString();

        if ($normalized === '') {
            return ['level' => '1', 'reason' => 'blank word'];
        }

        $lettersOnly = preg_replace('/[^a-z]/', '', $normalized) ?: '';
        $letters = strlen($lettersOnly);
        $highFrequency = in_array($lettersOnly, $this->highFrequencyWords(), true);
        $starterWord = in_array($lettersOnly, $this->starterWords(), true);
        $compound = $this->isCompound($normalized, $lettersOnly);
        $syllables = $this->syllableCount($lettersOnly);
        $silentPattern = $this->silentLetterPattern($lettersOnly);
        $irregularVowel = preg_match('/(ea|ie|ei|ough|augh)/', $lettersOnly) === 1;
        $academicSuffix = $this->academicSuffix($lettersOnly);
        $shortUnfamiliar = $letters >= 4 && $letters <= 5 && ! $highFrequency && ! $starterWord;
        $commonLevel = $this->commonWordLevels()[$normalized] ?? null;
        $score = 1;
        $signals = [];

        if ($letters === 1) {
            $signals[] = 'single-letter word';
        } else {
            $signals[] = $letters.' letters';
        }

        $signals[] = $syllables === 1 ? '1 syllable' : $syllables.' syllables';

        if ($highFrequency) {
            $signals[] = 'high-frequency';
        }

        if ($starterWord) {
            $signals[] = 'starter word';
        }

        if ($compound) {
            $signals[] = 'compound word';
        }

        if ($silentPattern !== null) {
            $signals[] = 'silent-letter pattern ('.$silentPattern.')';
        }

        if ($irregularVowel) {
            $signals[] = 'irregular vowel pattern';
        }

        if ($academicSuffix !== null) {
            $signals[] = 'academic suffix '.$academicSuffix;
        }

        if ($commonLevel !== null) {
            $signals[] = 'common word override';

            return [
                'level' => $commonLevel,
                'reason' => implode(', ', array_values(array_unique($signals))),
            ];
        }

        if (! $compound) {
            $score += match (true) {
                $letters <= 4 => 0,
                $letters <= 7 => 1,
                $letters <= 10 => 2,
                default => 3,
            };
        }

        $score += match (true) {
            $syllables <= 1 => 0,
            $syllables === 2 => 1,
            $syllables === 3 => 2,
            default => 3,
        };

        if ($highFrequency) {
            $score--;
        }

        if ($starterWord) {
            $score--;
        }

        if ($shortUnfamiliar) {
            $score += 2;
        }

        if ($compound) {
            $score++;
        }

        if ($silentPattern !== null) {
            $score += $letters <= 4 ? 2 : 1;
        }

        if ($irregularVowel) {
            $score++;
        }

        if ($academicSuffix !== null) {
            $score++;
        }

        if ($shortUnfamiliar) {
            $signals[] = 'short uncommon word';
        }

        return [
            'level' => (string) max(1, min(6, $score)),
            'reason' => implode(', ', array_values(array_unique($signals))),
        ];
    }

    private function syllableCount(string $word): int
    {
        if ($word === '') {
            return 1;
        }

        preg_match_all('/[aeiouy]+/', $word, $matches);
        $count = count($matches[0] ?? []);

        if ($count > 1 && str_ends_with($word, 'e')) {
            $count--;
        }

        if ($count > 1 && preg_match('/(ed|es)$/', $word) === 1 && ! preg_match('/[tdsxz](ed|es)$/', $word)) {
            $count--;
        }

        return max(1, $count);
    }

    private function isCompound(string $normalized, string $lettersOnly): bool
    {
        if (str_contains($normalized, ' ') || str_contains($normalized, '-')) {
            return true;
        }

        return in_array($lettersOnly, $this->compoundWords(), true);
    }

    private function silentLetterPattern(string $word): ?string
    {
        return match (true) {
            preg_match('/^kn/', $word) === 1 => 'silent k',
            preg_match('/^wr/', $word) === 1 => 'silent w',
            preg_match('/mb$/', $word) === 1 => 'silent b',
            preg_match('/[aeo]l[mk]/', $word) === 1 => 'silent l',
            preg_match('/(ight|ough|augh)/', $word) === 1 => 'silent gh',
            preg_match('/gn[oa]me|mn$/', $word) === 1 => 'silent consonant',
            default => null,
        };
    }

    private function academicSuffix(string $word): ?string
    {
        foreach (['tion', 'sion', 'ity', 'ance', 'ence', 'ment', 'able', 'ible', 'ous', 'ate', 'ize', 'ise'] as $suffix) {
            if (str_ends_with($word, $suffix)) {
                return '-'.$suffix;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function highFrequencyWords(): array
    {
        return require __DIR__.'/Difficulty/data/high_frequency.php';
    }

    /**
     * @return array<string, string>
     */
    private function commonWordLevels(): array
    {
        return require __DIR__.'/Difficulty/data/common_word_levels.php';
    }

    /**
     * @return list<string>
     */
    private function starterWords(): array
    {
        return require __DIR__.'/Difficulty/data/starter_words.php';
    }

    /**
     * @return list<string>
     */
    private function compoundWords(): array
    {
        return require __DIR__.'/Difficulty/data/compounds.php';
    }
}
