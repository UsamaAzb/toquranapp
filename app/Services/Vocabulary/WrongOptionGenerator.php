<?php

namespace App\Services\Vocabulary;

use Illuminate\Support\Str;

class WrongOptionGenerator
{
    /**
     * @return list<string>
     */
    public function parseCurated(mixed $value, string $correctWord): array
    {
        $correct = $this->normalizeChoice($correctWord);
        $rawItems = [];

        if (is_array($value)) {
            $rawItems = $value;
        } elseif (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);
            $rawItems = is_array($decoded)
                ? $decoded
                : (preg_split('/[\r\n,;|]+/', $value) ?: []);
        }

        $seen = [];
        $choices = [];

        foreach ($rawItems as $item) {
            $choice = trim((string) $item);
            $normalized = $this->normalizeChoice($choice);

            if ($choice === '' || $normalized === $correct || isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $choices[] = $choice;
        }

        return $choices;
    }

    /**
     * @return list<string>
     */
    public function spellingOptions(string $correctWord, mixed $curated = null, int $needed = 2): array
    {
        return array_map(
            static fn (array $option): string => $option['text'],
            $this->spellingOptionsDetailed($correctWord, $curated, $needed)
        );
    }

    /**
     * @return list<array{text: string, rule: string, label: string}>
     */
    public function spellingOptionsDetailed(string $correctWord, mixed $curated = null, int $needed = 2, ?string $difficulty = null): array
    {
        $curatedOptions = $this->parseCurated($curated, $correctWord);

        $options = array_map(
            static fn (string $option): array => [
                'text' => $option,
                'rule' => 'curated',
                'label' => 'curated',
            ],
            array_slice($curatedOptions, 0, $needed)
        );
        $correct = $this->normalizeChoice($correctWord);

        if (count($options) >= $needed) {
            return array_slice($options, 0, $needed);
        }

        if (strlen($correct) === 1 && preg_match('/^[a-z]$/', $correct) === 1) {
            foreach (str_split('abcdefghijklmnopqrstuvwxyz') as $letter) {
                if ($letter !== $correct) {
                    $options[] = ['text' => $letter, 'rule' => 'single_letter', 'label' => 'letter swap'];
                }
            }

            shuffle($options);

            return array_slice($options, 0, $needed);
        }

        foreach ($this->ruleCandidates($correct, $difficulty) as $candidate) {
            if (count($options) >= $needed) {
                break;
            }

            if ($this->candidateIsValid($correct, $candidate['text'], $options)) {
                $options[] = $candidate;
            }
        }

        foreach ($this->generateFallback($correctWord) as $option) {
            if (count($options) >= $needed) {
                break;
            }

            $candidate = ['text' => $option, 'rule' => 'legacy_swap', 'label' => 'letter swap'];

            if ($this->candidateIsValid($correct, $candidate['text'], $options)) {
                $options[] = $candidate;
            }
        }

        return array_slice($options, 0, $needed);
    }

    /**
     * @return list<string>
     */
    public function missingLetterOptions(string $_word, string $correctLetter, int $needed = 2): array
    {
        $correct = Str::lower($correctLetter);
        $letters = collect(str_split('abcdefghijklmnopqrstuvwxyz'))
            ->reject(fn (string $letter): bool => $letter === $correct)
            ->shuffle()
            ->take($needed)
            ->values()
            ->all();

        return array_values(array_unique(array_merge([$correct], $letters)));
    }

    /**
     * @return list<string>
     */
    private function generateFallback(string $word): array
    {
        $word = Str::lower(trim($word));
        $options = [];

        foreach ($this->genericMisspellings($word) as $candidate) {
            $options[] = $candidate;
        }

        $confusions = config('vocabulary.wrong_options.confusions', []);

        foreach ($confusions as $group) {
            foreach ($group as $from) {
                if (! str_contains($word, $from)) {
                    continue;
                }

                foreach ($group as $to) {
                    if ($to === $from) {
                        continue;
                    }

                    $candidate = preg_replace('/'.preg_quote($from, '/').'/', $to, $word, 1);

                    if (is_string($candidate) && $candidate !== $word) {
                        $options[] = $candidate;
                    }
                }
            }
        }

        if (str_ends_with($word, 'e') && strlen($word) > 3) {
            $options[] = substr($word, 0, -1);
        }

        return array_values(array_unique($options));
    }

    /**
     * @return list<string>
     */
    private function genericMisspellings(string $word): array
    {
        $length = strlen($word);

        if ($length < 3) {
            return [];
        }

        $options = [];
        $vowels = ['a', 'e', 'i', 'o', 'u', 'y'];
        $vowelAlternates = [
            'a' => ['e', 'o'],
            'e' => ['i', 'a'],
            'i' => ['e', 'y'],
            'o' => ['a', 'u'],
            'u' => ['o', 'a'],
            'y' => ['i', 'e'],
        ];

        for ($i = 1; $i < $length - 1; $i++) {
            $char = $word[$i];

            if (in_array($char, $vowels, true)) {
                $options[] = substr($word, 0, $i).substr($word, $i + 1);

                foreach ($vowelAlternates[$char] ?? [] as $alternate) {
                    $options[] = substr($word, 0, $i).$alternate.substr($word, $i + 1);
                }

                break;
            }
        }

        for ($i = 0; $i < $length - 1; $i++) {
            if ($word[$i] !== $word[$i + 1] && ctype_alpha($word[$i]) && ctype_alpha($word[$i + 1])) {
                $options[] = substr($word, 0, $i)
                    .$word[$i + 1]
                    .$word[$i]
                    .substr($word, $i + 2);

                break;
            }
        }

        for ($i = 1; $i < $length - 1; $i++) {
            $char = $word[$i];

            if (! in_array($char, $vowels, true) && ctype_alpha($char)) {
                $options[] = substr($word, 0, $i).$char.substr($word, $i);

                break;
            }
        }

        if ($length > 5) {
            $options[] = substr($word, 0, -2).substr($word, -1);
        }

        return array_values(array_unique(array_filter(
            $options,
            fn (string $candidate): bool => $candidate !== $word
        )));
    }

    /**
     * @return list<array{text: string, rule: string, label: string}>
     */
    private function ruleCandidates(string $word, ?string $difficulty): array
    {
        $rules = [];
        $informalAllowed = in_array((string) $difficulty, ['1', '2', '3', ''], true);
        $voicedTh = in_array($word, require __DIR__.'/Distractors/data/voiced_th.php', true);

        $add = static function (string $text, string $rule, string $label) use (&$rules): void {
            $rules[] = ['text' => $text, 'rule' => $rule, 'label' => $label];
        };

        if (preg_match('/[^aeiou]y$/', $word) === 1) {
            $add(substr($word, 0, -1).'e', 'final_y_to_e', 'final y');
            $add(substr($word, 0, -1).'ie', 'final_y_to_ie', 'final y');
        }

        if (preg_match('/([bcdfghjklmnpqrstvwxyz])\1/', $word) === 1) {
            $add((string) preg_replace('/([bcdfghjklmnpqrstvwxyz])\1/', '$1', $word, 1), 'double_drop', 'double letter');
        }

        $replacements = [
            'ie' => ['ei', 'ie_to_ei', 'ie/ei swap'],
            'ei' => ['ie', 'ei_to_ie', 'ie/ei swap'],
            'ea' => ['ee', 'ea_to_ee', 'vowel team'],
            'ck' => ['k', 'ck_to_k', 'ck/k swap'],
            'ph' => ['f', 'ph_to_f', 'ph/f swap'],
        ];

        foreach ($replacements as $from => [$to, $rule, $label]) {
            if (str_contains($word, $from)) {
                $add((string) preg_replace('/'.preg_quote($from, '/').'/', $to, $word, 1), $rule, $label);
            }
        }

        if (str_contains($word, 'ea')) {
            $add((string) preg_replace('/ea/', 'e', $word, 1), 'ea_drop_a', 'vowel team');
        }

        if (str_starts_with($word, 'kn')) {
            $add(substr($word, 1), 'silent_k_drop', 'silent letter');
        }

        if (str_starts_with($word, 'wr')) {
            $add(substr($word, 1), 'silent_w_drop', 'silent letter');
        }

        if (str_ends_with($word, 'mb')) {
            $add(substr($word, 0, -1), 'silent_b_drop', 'silent letter');
        }

        if (preg_match('/[aeo]l[mk]/', $word) === 1) {
            $add((string) preg_replace('/([aeo])l([mk])/', '$1$2', $word, 1), 'silent_l_drop', 'silent letter');
        }

        if (preg_match('/(ight|ought|augh)/', $word) === 1) {
            $add(str_replace('gh', '', $word), 'silent_gh_drop', 'silent letter');
        }

        if (preg_match('/[bcdfghjklmnpqrstvwxyz][aeiou][bcdfghjklmnpqrstvwxyz]e$/', $word) === 1) {
            $add(substr($word, 0, -1), 'magic_e_drop', 'magic e');
        }

        if (str_contains($word, 'th')) {
            $add((string) preg_replace('/th/', $voicedTh ? 'd' : 'f', $word, 1), $voicedTh ? 'th_to_d' : 'th_to_f', 'th sound');
        }

        if ($informalAllowed && str_ends_with($word, 'tion')) {
            $add(substr($word, 0, -4).'shun', 'tion_to_shun', 'sound spelling');
        }

        if ($informalAllowed && str_ends_with($word, 'ing')) {
            $add(substr($word, 0, -1), 'ing_to_in', 'ending sound');
        }

        if ($informalAllowed && str_ends_with($word, 'ed')) {
            $add(substr($word, 0, -2).'d', 'ed_drop_e', 'ending sound');
        }

        return $rules;
    }

    /**
     * @param  list<array{text: string, rule: string, label: string}>  $accepted
     */
    private function candidateIsValid(string $correct, string $candidate, array $accepted): bool
    {
        $normalized = $this->normalizeChoice($candidate);

        if ($normalized === '' || $normalized === $correct) {
            return false;
        }

        foreach ($accepted as $option) {
            if ($this->normalizeChoice($option['text']) === $normalized) {
                return false;
            }
        }

        if (abs(strlen($normalized) - strlen($correct)) > 2) {
            return false;
        }

        return levenshtein($normalized, $correct) <= max(3, (int) floor(strlen($correct) * 0.35));
    }

    private function normalizeChoice(string $value): string
    {
        return Str::lower(trim($value));
    }
}
