<?php

namespace App\Console\Commands\Vocabulary;

use App\Models\Cambradge_word_api;
use App\Services\Vocabulary\VocabularyDifficultyEstimator;
use App\Services\Vocabulary\WrongOptionGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class EnrichWordBank extends Command
{
    protected $signature = 'vocabulary:enrich
        {--dry-run : Preview changes without writing}
        {--write : Apply changes. Required for DB writes}
        {--difficulty= : Limit to one current difficulty level}
        {--group= : Limit to a group_words category id}
        {--search= : Limit to words matching this search term}
        {--limit=200 : Maximum rows to inspect}
        {--include-manual : Also rewrite rows previously marked manual/reviewed}';

    protected $description = 'Preview or apply generated wrong options and difficulty metadata for the vocabulary word bank.';

    public function handle(WrongOptionGenerator $wrongOptions, VocabularyDifficultyEstimator $difficultyEstimator): int
    {
        if (! $this->metadataReady()) {
            $this->error('Vocabulary quality columns are missing. Ask the owner to run the Phase 1 manual SQL patch first.');

            return self::FAILURE;
        }

        $write = (bool) $this->option('write');
        $includeManual = (bool) $this->option('include-manual');
        $limit = max(1, min(50000, (int) $this->option('limit')));
        $query = Cambradge_word_api::query()->orderBy('id');

        if (in_array((string) $this->option('difficulty'), ['1', '2', '3', '4', '5', '6'], true)) {
            $query->where('difficulty_levels', (string) $this->option('difficulty'));
        }

        if (trim((string) $this->option('search')) !== '') {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim((string) $this->option('search'))).'%';
            $query->where('word', 'like', $term);
        }

        if ((int) $this->option('group') > 0 && Schema::hasTable('group_words')) {
            $query->whereIn('id', \App\Models\Group_word::query()
                ->where('category_id', (int) $this->option('group'))
                ->select('camb_sound_id'));
        }

        $rows = $query->limit($limit)->get();
        $updated = 0;
        $preview = [];

        foreach ($rows as $word) {
            $changes = [];

            if ($includeManual || (string) ($word->wrong_spelling_source ?? 'legacy_unknown') !== 'manual') {
                $difficulty = trim((string) ($word->difficulty_levels ?? ''));
                $generated = $wrongOptions->spellingOptionsDetailed((string) $word->word, null, 3, $difficulty);

                if ($generated !== []) {
                    $changes['wrong_spelling'] = implode("\n", array_column($generated, 'text'));
                    $changes['wrong_spelling_rules'] = $generated;
                    $changes['wrong_spelling_source'] = 'generated';
                }
            }

            if ($includeManual || (string) ($word->difficulty_source ?? 'legacy_unknown') !== 'manual') {
                $estimate = $difficultyEstimator->estimateWithReason((string) $word->word);
                $changes['difficulty_levels'] = $estimate['level'];
                $changes['difficulty_reason'] = $estimate['reason'];
                $changes['difficulty_source'] = 'generated';
            }

            if ($changes === []) {
                continue;
            }

            $updated++;

            if (count($preview) < 20) {
                $preview[] = [
                    (int) $word->id,
                    (string) $word->word,
                    (string) ($changes['difficulty_levels'] ?? $word->difficulty_levels ?? ''),
                    str_replace("\n", ', ', (string) ($changes['wrong_spelling'] ?? $word->wrong_spelling ?? '')),
                ];
            }

            if ($write) {
                $word->forceFill($changes)->save();
            }
        }

        $this->info(($write ? 'Updated' : 'Would update').' '.$updated.' row(s).');

        if ($preview !== []) {
            $this->table(['ID', 'Word', 'Level', 'Wrong options'], $preview);
        }

        if (! $write) {
            $this->warn('Dry run only. Re-run with --write to apply changes.');
        }

        return self::SUCCESS;
    }

    private function metadataReady(): bool
    {
        return Schema::hasTable('cambradge_words_api')
            && Schema::hasColumn('cambradge_words_api', 'wrong_spelling_rules')
            && Schema::hasColumn('cambradge_words_api', 'wrong_spelling_source')
            && Schema::hasColumn('cambradge_words_api', 'difficulty_reason')
            && Schema::hasColumn('cambradge_words_api', 'difficulty_source');
    }
}
