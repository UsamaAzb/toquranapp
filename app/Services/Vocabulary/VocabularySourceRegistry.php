<?php

namespace App\Services\Vocabulary;

use App\Models\Camb_category;
use App\Models\Camb_word;
use App\Models\Category_group_word;
use App\Models\Hangman_category;
use App\Models\Phonics_level;
use App\Models\VocabularySet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class VocabularySourceRegistry
{
    public function difficultyKey(int $difficultyLevelId): string
    {
        return 'difficulty:'.$difficultyLevelId;
    }

    public function groupKey(int $categoryId): string
    {
        return 'group:'.$categoryId;
    }

    public function phonicsKey(int $categoryId): string
    {
        return 'phonics:category:'.$categoryId;
    }

    public function cambridgeCategoryKey(int $categoryId): string
    {
        return 'cambridge:'.$categoryId;
    }

    public function cambridgeUnitKey(int $categoryId, string|int $unit): string
    {
        return $this->cambridgeCategoryKey($categoryId).':unit:'.$unit;
    }

    public function cambridgeLessonKey(int $categoryId, string|int $unit, string|int $lesson): string
    {
        return $this->cambridgeUnitKey($categoryId, $unit).':lesson:'.$lesson;
    }

    public function hangmanKey(int $categoryId): string
    {
        return 'hangman:'.$categoryId;
    }

    public function rootKey(string $sourceKind): string
    {
        return $sourceKind.':root';
    }

    public function surpriseKey(string $poolSlug): string
    {
        return 'surprise:'.$poolSlug;
    }

    public function findProxy(string $sourceKind, string $sourceKey): ?VocabularySet
    {
        return VocabularySet::query()
            ->where('source_kind', $sourceKind)
            ->where('source_key', $sourceKey)
            ->first();
    }

    public function ensureProxy(
        string $sourceKind,
        string $sourceKey,
        string $title,
        string $nodeType = VocabularySet::NODE_PLAYABLE,
        ?int $parentId = null,
    ): VocabularySet {
        return VocabularySet::query()->updateOrCreate(
            [
                'source_kind' => $sourceKind,
                'source_key' => $sourceKey,
            ],
            [
                'title' => $title,
                'node_type' => $nodeType,
                'set_type' => VocabularySet::TYPE_SYSTEM,
                'visibility' => VocabularySet::VISIBILITY_SYSTEM,
                'parent_id' => $parentId,
            ]
        );
    }

    /**
     * @param  iterable<int>  $setIds
     * @return array<int, array{children_count:int, word_count:int}>
     */
    public function batchMetadata(iterable $setIds): array
    {
        $ids = collect($setIds)->map(fn ($id): int => (int) $id)->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $sets = VocabularySet::query()
            ->whereIn('id', $ids)
            ->withCount(['children', 'memberships as stored_word_count'])
            ->get(['id', 'source_kind', 'source_key', 'node_type']);

        $provider = app(VocabularyWordProvider::class);

        return $sets->mapWithKeys(fn (VocabularySet $set): array => [
            $set->id => [
                'children_count' => (int) $set->children_count,
                'word_count' => $set->canBeLaunched()
                    ? $provider->countWordRecordsForSet($set)
                    : (int) $set->stored_word_count,
            ],
        ])->all();
    }

    /**
     * @return Collection<int, VocabularySet>
     */
    public function childrenFor(VocabularySet $set): Collection
    {
        return $set->children()->get();
    }

    public function ensureLegacySourceProxies(): void
    {
        if (! Schema::hasTable('vocabulary_sets')) {
            return;
        }

        Cache::remember('vocabulary.legacy_source_proxies.synced', 60, function (): int {
            $this->syncLegacySourceProxies();

            return now(config('app.timezone'))->getTimestamp();
        });
    }

    private function syncLegacySourceProxies(): void
    {
        $this->ensureHangmanProxies();
        $this->ensureDifficultyProxies();
        $this->ensureGroupProxies();
        $this->ensurePhonicsProxies();
        $this->ensureCambridgeProxies();
    }

    private function ensureHangmanProxies(): void
    {
        if (! Schema::hasTable('hangman_category')) {
            return;
        }

        $root = $this->ensureProxy(
            VocabularySet::SOURCE_LEGACY_HANGMAN,
            $this->rootKey(VocabularySet::SOURCE_LEGACY_HANGMAN),
            'Legacy Floatie',
            VocabularySet::NODE_FOLDER,
        );

        Hangman_category::query()
            ->where('active', 1)
            ->orderBy('order')
            ->orderBy('name')
            ->limit(80)
            ->get()
            ->each(fn (Hangman_category $category): VocabularySet => $this->ensureProxy(
                VocabularySet::SOURCE_LEGACY_HANGMAN,
                $this->hangmanKey((int) $category->id),
                (string) $category->name,
                VocabularySet::NODE_PLAYABLE,
                (int) $root->id,
            ));
    }

    private function ensureDifficultyProxies(): void
    {
        $root = $this->ensureProxy(
            VocabularySet::SOURCE_LEGACY_DIFFICULTY,
            $this->rootKey(VocabularySet::SOURCE_LEGACY_DIFFICULTY),
            'Difficulty',
            VocabularySet::NODE_FOLDER,
        );

        collect(range(1, 6))
            ->each(fn (int $difficulty): VocabularySet => $this->ensureProxy(
                VocabularySet::SOURCE_LEGACY_DIFFICULTY,
                $this->difficultyKey($difficulty),
                'Level '.$difficulty,
                VocabularySet::NODE_PLAYABLE,
                (int) $root->id,
            ));
    }

    private function ensureGroupProxies(): void
    {
        if (! Schema::hasTable('category_group_word')) {
            return;
        }

        $root = $this->ensureProxy(
            VocabularySet::SOURCE_LEGACY_GROUP,
            $this->rootKey(VocabularySet::SOURCE_LEGACY_GROUP),
            'Word Group',
            VocabularySet::NODE_FOLDER,
        );

        Category_group_word::query()
            ->where('active', 1)
            ->orderBy('order')
            ->orderBy('name')
            ->limit(80)
            ->get()
            ->each(fn (Category_group_word $category): VocabularySet => $this->ensureProxy(
                VocabularySet::SOURCE_LEGACY_GROUP,
                $this->groupKey((int) $category->id),
                (string) $category->name,
                VocabularySet::NODE_PLAYABLE,
                (int) $root->id,
            ));
    }

    private function ensurePhonicsProxies(): void
    {
        if (! Schema::hasTable('phonics_levels')) {
            return;
        }

        $root = $this->ensureProxy(
            VocabularySet::SOURCE_LEGACY_PHONICS,
            $this->rootKey(VocabularySet::SOURCE_LEGACY_PHONICS),
            'Phonics',
            VocabularySet::NODE_FOLDER,
        );

        Phonics_level::query()
            ->whereNotNull('name')
            ->orderBy('level')
            ->orderBy('order')
            ->get()
            ->each(fn (Phonics_level $level): VocabularySet => $this->ensureProxy(
                VocabularySet::SOURCE_LEGACY_PHONICS,
                $this->phonicsKey((int) $level->id),
                (string) $level->name,
                VocabularySet::NODE_PLAYABLE,
                (int) $root->id,
            ));
    }

    private function ensureCambridgeProxies(): void
    {
        if (! Schema::hasTable('camb_words')) {
            return;
        }

        $root = $this->ensureProxy(
            VocabularySet::SOURCE_LEGACY_CAMBRIDGE,
            $this->rootKey(VocabularySet::SOURCE_LEGACY_CAMBRIDGE),
            'Cambridge',
            VocabularySet::NODE_FOLDER,
        );
        $categoryTitleColumn = $this->cambridgeCategoryTitleColumn();
        $categoryTitles = $categoryTitleColumn !== null
            ? Camb_category::query()->pluck($categoryTitleColumn, 'id')
            : collect();

        Camb_word::query()
            ->select(['camb_cat_id', 'unit', 'lesson'])
            ->whereNotNull('camb_cat_id')
            ->whereNotNull('unit')
            ->whereNotNull('lesson')
            ->orderBy('camb_cat_id')
            ->orderBy('unit')
            ->orderBy('lesson')
            ->get()
            ->unique(fn (Camb_word $word): string => (int) $word->camb_cat_id.'|'.(string) $word->unit.'|'.(string) $word->lesson)
            ->each(function (Camb_word $word) use ($categoryTitles, $root): void {
                $categoryId = (int) $word->camb_cat_id;
                $categoryTitle = (string) ($categoryTitles[$categoryId] ?? 'Cambridge '.$categoryId);
                $category = $this->ensureProxy(
                    VocabularySet::SOURCE_LEGACY_CAMBRIDGE,
                    $this->cambridgeCategoryKey($categoryId),
                    $categoryTitle,
                    VocabularySet::NODE_FOLDER,
                    (int) $root->id,
                );
                $unit = $this->ensureProxy(
                    VocabularySet::SOURCE_LEGACY_CAMBRIDGE,
                    $this->cambridgeUnitKey($categoryId, (string) $word->unit),
                    $categoryTitle.' - Unit '.(string) $word->unit,
                    VocabularySet::NODE_FOLDER,
                    (int) $category->id,
                );
                $this->ensureProxy(
                    VocabularySet::SOURCE_LEGACY_CAMBRIDGE,
                    $this->cambridgeLessonKey($categoryId, (string) $word->unit, (string) $word->lesson),
                    'Lesson '.(string) $word->lesson,
                    VocabularySet::NODE_PLAYABLE,
                    (int) $unit->id,
                );
            });
    }

    private function cambridgeCategoryTitleColumn(): ?string
    {
        if (! Schema::hasTable('camb_categories')) {
            return null;
        }

        foreach (['name', 'title', 'word'] as $column) {
            if (Schema::hasColumn('camb_categories', $column)) {
                return $column;
            }
        }

        return null;
    }
}
