<?php

namespace App\Services\Vocabulary;

use App\Models\Camb_word;
use App\Models\Cambradge_word_api;
use App\Models\Group_word;
use App\Models\Hangman_word;
use App\Models\Phonics_word;
use App\Models\VocabularySet;
use App\Models\VocabularySetWord;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VocabularyWordProvider
{
    public function __construct(
        private readonly VocabularyAudioResolver $audioResolver,
        private readonly WrongOptionGenerator $wrongOptionGenerator,
    ) {}

    /**
     * @return Collection<int, Cambradge_word_api>
     */
    public function searchWords(string $search = '', int $limit = 40): Collection
    {
        return Cambradge_word_api::query()
            ->when(trim($search) !== '', function ($query) use ($search): void {
                $query->where('word', 'like', '%'.trim($search).'%');
            })
            ->orderBy('word')
            ->limit($limit)
            ->get();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function playableWordsForSet(VocabularySet $set, string $game, int $limit = 25): array
    {
        if (! $set->canBeLaunched()) {
            return [];
        }

        $limit = $this->launchLimit($set, $limit);

        if ($set->source_kind === VocabularySet::SOURCE_LEGACY_HANGMAN) {
            return $this->playableLegacyHangmanWords($set, $game, $limit);
        }

        if (in_array($set->source_kind, [
            VocabularySet::SOURCE_LEGACY_CAMBRIDGE,
            VocabularySet::SOURCE_LEGACY_PHONICS,
            VocabularySet::SOURCE_LEGACY_GROUP,
            VocabularySet::SOURCE_LEGACY_DIFFICULTY,
        ], true)) {
            return $this->playableMappedLegacyWords($set, $game, $limit);
        }

        $words = $set->source_kind === VocabularySet::SOURCE_CUSTOM
            ? $this->customSetWordsForLaunch($set, $limit)
            : $set->words()
                ->limit($limit)
                ->get();

        return $words
            ->map(fn (Cambradge_word_api $word): ?array => $this->payloadForWord($word, $game))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Cambradge_word_api>
     */
    public function browseWordRecordsForSet(VocabularySet $set, int $limit = 80): Collection
    {
        if (! $set->canBeLaunched()) {
            return collect();
        }

        if ($set->source_kind === VocabularySet::SOURCE_CUSTOM) {
            return $set->words()
                ->limit($limit)
                ->get();
        }

        if (! in_array($set->source_kind, [
            VocabularySet::SOURCE_LEGACY_CAMBRIDGE,
            VocabularySet::SOURCE_LEGACY_PHONICS,
            VocabularySet::SOURCE_LEGACY_GROUP,
            VocabularySet::SOURCE_LEGACY_DIFFICULTY,
        ], true)) {
            return collect();
        }

        $ids = $this->mappedLegacySoundIds($set, $limit);

        if ($ids === []) {
            return collect();
        }

        $wordsById = Cambradge_word_api::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(fn (int $id): ?Cambradge_word_api => $wordsById->get($id))
            ->filter()
            ->values();
    }

    public function countWordRecordsForSet(VocabularySet $set): int
    {
        if (! $set->canBeLaunched()) {
            return 0;
        }

        if ($set->source_kind === VocabularySet::SOURCE_CUSTOM) {
            return $set->words()->count();
        }

        if (! in_array($set->source_kind, [
            VocabularySet::SOURCE_LEGACY_CAMBRIDGE,
            VocabularySet::SOURCE_LEGACY_PHONICS,
            VocabularySet::SOURCE_LEGACY_GROUP,
            VocabularySet::SOURCE_LEGACY_DIFFICULTY,
        ], true)) {
            return 0;
        }

        return count($this->mappedLegacySoundIds($set, null));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function payloadForWord(Cambradge_word_api $word, string $game): ?array
    {
        $audio = $this->audioResolver->resolve($word);

        if (! $audio->available()) {
            return null;
        }

        $text = trim((string) $word->word);
        $wrongOptions = $this->wrongOptionGenerator->spellingOptions($text, $word->wrong_spelling ?? null);

        if (in_array($game, ['missing_letter', 'spelling_choice'], true) && count($wrongOptions) < 1) {
            return null;
        }

        return [
            'id' => (int) $word->id,
            'text' => $text,
            'displayText' => $this->displayText($text),
            'isAllCaps' => $this->isAllCapsWord($text),
            'hint' => '',
            'audioUrl' => $audio->url,
            'wrongOptions' => $wrongOptions,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function playableLegacyHangmanWords(VocabularySet $set, string $game, int $limit): array
    {
        if (! preg_match('/\Ahangman:(\d+)\z/', (string) $set->source_key, $matches)) {
            return [];
        }

        $words = Hangman_word::query()
            ->where('category_id', (int) $matches[1])
            ->inRandomOrder()
            ->limit(80)
            ->get()
            ->take($limit);

        return $words
            ->map(function (Hangman_word $legacyWord) use ($game): ?array {
                $text = trim((string) $legacyWord->word);

                if ($text === '') {
                    return null;
                }

                $audio = $this->audioResolver->resolveByText($text);

                if (! $audio->available()) {
                    return null;
                }

                $wrongOptions = $this->wrongOptionGenerator->spellingOptions($text);

                if (in_array($game, ['missing_letter', 'spelling_choice'], true) && count($wrongOptions) < 1) {
                    return null;
                }

                return [
                    'id' => null,
                    'text' => $text,
                    'displayText' => $this->displayText($text),
                    'isAllCaps' => $this->isAllCapsWord($text),
                    'hint' => '',
                    'audioUrl' => $audio->url,
                    'wrongOptions' => $wrongOptions,
                    'legacy' => true,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Cambradge_word_api>
     */
    private function customSetWordsForLaunch(VocabularySet $set, int $fallbackLimit): Collection
    {
        $limit = max($fallbackLimit, (int) config('vocabulary.games.custom_source_word_limit', 30));
        $membershipCount = $set->memberships()->count();

        if ($membershipCount <= $limit) {
            return $set->words()
                ->limit($limit)
                ->get();
        }

        $wordIds = VocabularySetWord::query()
            ->where('vocabulary_set_id', (int) $set->id)
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('word_id')
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->values();

        $words = Cambradge_word_api::query()
            ->whereIn('id', $wordIds)
            ->get()
            ->keyBy('id');

        return $wordIds
            ->map(fn (int $wordId): ?Cambradge_word_api => $words->get($wordId))
            ->filter()
            ->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function playableMappedLegacyWords(VocabularySet $set, string $game, int $limit): array
    {
        $randomize = $this->shouldRandomizeSet($set);
        $ids = $this->mappedLegacySoundIds($set, $randomize ? null : $limit);

        if ($ids === []) {
            return [];
        }

        if ($randomize) {
            shuffle($ids);
        }

        return Cambradge_word_api::query()
            ->whereIn('id', array_slice($ids, 0, $limit))
            ->get()
            ->map(fn (Cambradge_word_api $word): ?array => $this->payloadForWord($word, $game))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    private function mappedLegacySoundIds(VocabularySet $set, ?int $limit): array
    {
        return match ($set->source_kind) {
            VocabularySet::SOURCE_LEGACY_PHONICS => $this->legacySoundIds(
                Phonics_word::query()->where('category_id', $this->categoryIdFromKey((string) $set->source_key)),
                $limit
            ),
            VocabularySet::SOURCE_LEGACY_GROUP => $this->legacySoundIds(
                Group_word::query()->where('category_id', $this->categoryIdFromKey((string) $set->source_key)),
                $limit
            ),
            VocabularySet::SOURCE_LEGACY_DIFFICULTY => $this->legacyDifficultySoundIds(
                $this->categoryIdFromKey((string) $set->source_key),
                $limit
            ),
            VocabularySet::SOURCE_LEGACY_CAMBRIDGE => $this->cambridgeSoundIds((string) $set->source_key, $limit),
            default => [],
        };
    }

    private function categoryIdFromKey(string $sourceKey): int
    {
        if (preg_match('/(\d+)\z/', $sourceKey, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    /**
     * @return list<int>
     */
    private function legacySoundIds($query, ?int $limit = 80): array
    {
        return $query
            ->whereNotNull('camb_sound_id')
            ->when($limit !== null, fn ($query) => $query->limit($limit))
            ->pluck('camb_sound_id')
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    private function legacyDifficultySoundIds(int $difficultyId, ?int $limit = 120): array
    {
        if ($difficultyId <= 0) {
            return [];
        }

        return Cambradge_word_api::query()
            ->where('difficulty_levels', (string) $difficultyId)
            ->when($limit !== null, fn ($query) => $query->limit($limit))
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    private function launchLimit(VocabularySet $set, int $fallback): int
    {
        return $this->shouldRandomizeSet($set)
            ? (int) config('vocabulary.games.random_source_word_limit', 15)
            : $fallback;
    }

    private function shouldRandomizeSet(VocabularySet $set): bool
    {
        return in_array($set->source_kind, [
            VocabularySet::SOURCE_LEGACY_CAMBRIDGE,
            VocabularySet::SOURCE_LEGACY_PHONICS,
            VocabularySet::SOURCE_LEGACY_GROUP,
            VocabularySet::SOURCE_LEGACY_DIFFICULTY,
            VocabularySet::SOURCE_LEGACY_HANGMAN,
        ], true);
    }

    /**
     * @return list<int>
     */
    private function cambridgeSoundIds(string $sourceKey, ?int $limit = 80): array
    {
        if (! preg_match('/\Acambridge:(\d+):unit:([^:]+):lesson:([^:]+)\z/', $sourceKey, $matches)) {
            return [];
        }

        return $this->legacySoundIds(
            Camb_word::query()
                ->where('camb_cat_id', (int) $matches[1])
                ->where('unit', $matches[2])
                ->where('lesson', $matches[3]),
            $limit
        );
    }

    /**
     * @param  list<string>  $words
     * @return list<array<string, mixed>>
     */
    public function customPayload(array $words, string $game): array
    {
        return collect($words)
            ->map(fn (string $word): string => trim($word))
            ->filter()
            ->map(function (string $word) use ($game): ?array {
                $audio = $this->audioResolver->resolveByText($word);
                $wrongOptions = $this->wrongOptionGenerator->spellingOptions($word);

                if (in_array($game, ['missing_letter', 'spelling_choice'], true) && count($wrongOptions) < 1) {
                    return null;
                }

                return [
                    'id' => null,
                    'text' => $word,
                    'displayText' => $this->displayText($word),
                    'isAllCaps' => $this->isAllCapsWord($word),
                    'hint' => '',
                    'audioUrl' => $audio->url,
                    'wrongOptions' => $wrongOptions,
                    'custom' => true,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function isAllCapsWord(string $word): bool
    {
        return in_array(Str::upper($word), config('vocabulary.games.all_caps_words', []), true);
    }

    private function displayText(string $word): string
    {
        if ($this->isAllCapsWord($word)) {
            return Str::upper($word);
        }

        return Str::ucfirst(Str::lower($word));
    }
}
