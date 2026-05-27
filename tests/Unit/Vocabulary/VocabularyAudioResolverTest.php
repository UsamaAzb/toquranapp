<?php

namespace Tests\Unit\Vocabulary;

use App\Models\Cambradge_word_api;
use App\Services\Vocabulary\VocabularyAudioResolver;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VocabularyAudioResolverTest extends TestCase
{
    protected function tearDown(): void
    {
        File::delete([
            public_path('camb_words_api/us_sounds/test-primary.mp3'),
            public_path('dictionary_sounds/us_sounds/test-primary.mp3'),
            public_path('dictionary_sounds/us_sounds/fallback_word.mp3'),
            public_path('camb_words_api/pcrecord/fallback_word.mp3'),
            public_path('camb_words_api/us_sounds/titanic.ogg'),
        ]);

        parent::tearDown();
    }

    public function test_primary_audio_wins_over_dictionary_fallback(): void
    {
        File::ensureDirectoryExists(public_path('camb_words_api/us_sounds'));
        File::ensureDirectoryExists(public_path('dictionary_sounds/us_sounds'));
        File::put(public_path('camb_words_api/us_sounds/test-primary.mp3'), 'ID3primary');
        File::put(public_path('dictionary_sounds/us_sounds/test-primary.mp3'), 'ID3fallback');

        $word = new Cambradge_word_api([
            'word' => 'Test Primary',
            'us_sound' => 'test-primary.mp3',
        ]);

        $resolution = app(VocabularyAudioResolver::class)->resolve($word);

        $this->assertTrue($resolution->available());
        $this->assertSame(VocabularyAudioResolver::SOURCE_PRIMARY_US, $resolution->source);
        $this->assertSame('camb_words_api/us_sounds/test-primary.mp3', $resolution->path);
    }

    public function test_missing_primary_falls_through_to_dictionary_audio(): void
    {
        File::ensureDirectoryExists(public_path('dictionary_sounds/us_sounds'));
        File::put(public_path('dictionary_sounds/us_sounds/test-primary.mp3'), 'ID3fallback');

        $word = new Cambradge_word_api([
            'word' => 'Test Primary',
            'us_sound' => 'test-primary.mp3',
        ]);

        $resolution = app(VocabularyAudioResolver::class)->resolve($word);

        $this->assertTrue($resolution->available());
        $this->assertTrue($resolution->missingPrimary);
        $this->assertSame(VocabularyAudioResolver::SOURCE_DICTIONARY_US, $resolution->source);
    }

    public function test_owner_recording_is_last_fallback(): void
    {
        File::ensureDirectoryExists(public_path('camb_words_api/pcrecord'));
        File::put(public_path('camb_words_api/pcrecord/fallback_word.mp3'), 'ID3recording');

        $word = new Cambradge_word_api([
            'word' => 'Fallback Word',
            'us_sound' => null,
        ]);

        $resolution = app(VocabularyAudioResolver::class)->resolve($word);

        $this->assertTrue($resolution->available());
        $this->assertSame(VocabularyAudioResolver::SOURCE_OWNER_RECORDING, $resolution->source);
    }

    public function test_primary_audio_can_resolve_ogg_files(): void
    {
        File::ensureDirectoryExists(public_path('camb_words_api/us_sounds'));
        File::put(public_path('camb_words_api/us_sounds/titanic.ogg'), 'OggSaudio');

        $word = new Cambradge_word_api([
            'word' => 'Titanic',
            'us_sound' => 'titanic.ogg',
        ]);

        $resolution = app(VocabularyAudioResolver::class)->resolve($word);

        $this->assertTrue($resolution->available());
        $this->assertSame(VocabularyAudioResolver::SOURCE_PRIMARY_US, $resolution->source);
        $this->assertSame('camb_words_api/us_sounds/titanic.ogg', $resolution->path);
    }
}
