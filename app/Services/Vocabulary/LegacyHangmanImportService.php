<?php

namespace App\Services\Vocabulary;

use App\Models\Child_hangman_category;
use App\Models\Child_word;
use App\Models\Hangman_category;
use App\Models\Hangman_word;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class LegacyHangmanImportService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function report(): Collection
    {
        if (! Schema::hasTable('hangman_category') && ! Schema::hasTable('child_hangman_category')) {
            return collect();
        }

        $adult = Schema::hasTable('hangman_category')
            ? Hangman_category::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Hangman_category $category): array => [
                    'source' => 'hangman_category',
                    'id' => (int) $category->id,
                    'name' => (string) $category->name,
                    'words_count' => Hangman_word::query()->where('category_id', $category->id)->count(),
                ])
            : collect();

        $child = Schema::hasTable('child_hangman_category')
            ? Child_hangman_category::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Child_hangman_category $category): array => [
                    'source' => 'child_hangman_category',
                    'id' => (int) $category->id,
                    'name' => (string) $category->name,
                    'words_count' => Child_word::query()->where('child_category_id', $category->id)->count(),
                ])
            : collect();

        return $adult->concat($child)->values();
    }
}
