<?php

namespace Tests\Unit\Vocabulary;

use App\Models\Cambradge_word_api;
use App\Models\VocabularySet;
use App\Services\Vocabulary\VocabularyAudioResolution;
use App\Services\Vocabulary\VocabularyAudioResolver;
use App\Services\Vocabulary\VocabularyWordProvider;
use App\Services\Vocabulary\WrongOptionGenerator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VocabularyWordProviderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createVocabularyTables();
    }

    public function test_custom_launch_samples_up_to_custom_limit_for_long_lists(): void
    {
        config(['vocabulary.games.custom_source_word_limit' => 30]);

        $set = VocabularySet::query()->create([
            'title' => 'Long Custom List',
            'node_type' => VocabularySet::NODE_PLAYABLE,
            'set_type' => VocabularySet::TYPE_TEACHER,
            'source_kind' => VocabularySet::SOURCE_CUSTOM,
            'visibility' => VocabularySet::VISIBILITY_PRIVATE,
        ]);

        for ($index = 1; $index <= 40; $index++) {
            $wordId = DB::table('cambradge_words_api')->insertGetId([
                'word' => 'word '.$index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('vocabulary_set_words')->insert([
                'vocabulary_set_id' => (int) $set->id,
                'word_id' => $wordId,
                'position' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $provider = new VocabularyWordProvider(new class extends VocabularyAudioResolver
        {
            public function resolve(Cambradge_word_api $word): VocabularyAudioResolution
            {
                return new VocabularyAudioResolution('/audio.mp3', 'audio.mp3', self::SOURCE_PRIMARY_US);
            }
        }, app(WrongOptionGenerator::class));

        $words = $provider->playableWordsForSet($set, 'hangman', 25);

        $this->assertCount(30, $words);
    }

    private function createVocabularyTables(): void
    {
        Schema::create('vocabulary_sets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('node_type');
            $table->string('set_type');
            $table->string('source_kind');
            $table->string('source_key')->nullable();
            $table->unsignedBigInteger('owner_user_id')->nullable();
            $table->string('visibility');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->unsignedBigInteger('updated_by_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('vocabulary_set_words', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vocabulary_set_id');
            $table->unsignedBigInteger('word_id');
            $table->unsignedInteger('position')->default(0);
            $table->unsignedBigInteger('added_by_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('cambradge_words_api', function (Blueprint $table): void {
            $table->id();
            $table->string('word');
            $table->string('image')->nullable();
            $table->string('us_sound')->nullable();
            $table->string('uk_sound')->nullable();
            $table->string('difficulty_levels')->nullable();
            $table->text('wrong_spelling')->nullable();
            $table->timestamps();
        });
    }
}
