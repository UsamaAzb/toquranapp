<?php

namespace Tests\Unit\Vocabulary;

use App\Services\Vocabulary\CambridgeAudioDownloadService;
use InvalidArgumentException;
use Tests\TestCase;

class CambridgeAudioDownloadServiceTest extends TestCase
{
    public function test_partial_path_accepts_cambridge_media_path(): void
    {
        $path = app(CambridgeAudioDownloadService::class)
            ->validatePartialPath('/us/media/learner-english/us_pron/f/fin/finis/finish.mp3');

        $this->assertSame('/us/media/learner-english/us_pron/f/fin/finis/finish.mp3', $path);
    }

    public function test_partial_path_accepts_cambridge_english_media_path(): void
    {
        $path = app(CambridgeAudioDownloadService::class)
            ->validatePartialPath('/us/media/english/uk_pron/u/ukt/uktip/uktippe023.mp3');

        $this->assertSame('/us/media/english/uk_pron/u/ukt/uktip/uktippe023.mp3', $path);
    }

    public function test_partial_path_accepts_any_cambridge_mp3_path(): void
    {
        $path = app(CambridgeAudioDownloadService::class)
            ->validatePartialPath('/media/custom-dictionary-source/audio/titanic.mp3');

        $this->assertSame('/media/custom-dictionary-source/audio/titanic.mp3', $path);
    }

    public function test_partial_path_accepts_audio_extensions_like_ogg(): void
    {
        $path = app(CambridgeAudioDownloadService::class)
            ->validatePartialPath('/us/media/english/uk_pron_ogg/u/ukr/ukreg/ukregen023.ogg');

        $this->assertSame('/us/media/english/uk_pron_ogg/u/ukr/ukreg/ukregen023.ogg', $path);
    }

    public function test_partial_path_denies_full_external_urls(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(CambridgeAudioDownloadService::class)
            ->validatePartialPath('https://example.com/finish.mp3');
    }

    public function test_partial_path_denies_non_mp3_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(CambridgeAudioDownloadService::class)
            ->validatePartialPath('/us/media/learner-english/us_pron/f/fin/finis/finish.html');
    }

    public function test_complete_url_accepts_public_https_mp3_host(): void
    {
        $url = app(CambridgeAudioDownloadService::class)
            ->validateCompleteUrl('https://dictionary.cambridge.org/us/media/learner-english/us_pron/f/fin/finis/finish.mp3');

        $this->assertSame(
            'https://dictionary.cambridge.org/us/media/learner-english/us_pron/f/fin/finis/finish.mp3',
            $url
        );
    }

    public function test_complete_url_accepts_other_public_dictionary_hosts(): void
    {
        $url = app(CambridgeAudioDownloadService::class)
            ->validateCompleteUrl('https://audio.example-dictionary.com/us/finish.mp3');

        $this->assertSame('https://audio.example-dictionary.com/us/finish.mp3', $url);
    }

    public function test_complete_url_accepts_public_https_ogg_audio(): void
    {
        $url = app(CambridgeAudioDownloadService::class)
            ->validateCompleteUrl('https://dictionary.cambridge.org/us/media/english/uk_pron_ogg/u/ukr/ukreg/ukregen023.ogg');

        $this->assertSame(
            'https://dictionary.cambridge.org/us/media/english/uk_pron_ogg/u/ukr/ukreg/ukregen023.ogg',
            $url
        );
    }

    public function test_complete_url_denies_non_https_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(CambridgeAudioDownloadService::class)
            ->validateCompleteUrl('http://dictionary.cambridge.org/us/media/learner-english/us_pron/f/fin/finis/finish.mp3');
    }

    public function test_complete_url_denies_local_hosts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(CambridgeAudioDownloadService::class)
            ->validateCompleteUrl('https://localhost/finish.mp3');
    }
}
