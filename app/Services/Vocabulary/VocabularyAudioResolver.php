<?php

namespace App\Services\Vocabulary;

use App\Models\Cambradge_word_api;
use Illuminate\Support\Str;

class VocabularyAudioResolver
{
    public const SOURCE_PRIMARY_US = 'primary_us';

    public const SOURCE_DICTIONARY_US = 'dictionary_us';

    public const SOURCE_OWNER_RECORDING = 'owner_recording';

    public const SOURCE_MISSING = 'missing';

    public function resolve(Cambradge_word_api $word): VocabularyAudioResolution
    {
        $wordText = (string) $word->word;
        $primaryName = $this->safeBasename((string) $word->us_sound);
        $fallbackNames = $primaryName !== null
            ? [$primaryName]
            : $this->filenameCandidatesFromWord($wordText);
        $missingPrimary = false;

        if ($primaryName !== null) {
            $primary = $this->resolveInPublicPath($primaryName, (string) config('vocabulary.audio.primary_us_path'));

            if ($primary !== null) {
                return new VocabularyAudioResolution($primary['url'], $primary['path'], self::SOURCE_PRIMARY_US);
            }

            $missingPrimary = true;
        }

        foreach ([
            self::SOURCE_PRIMARY_US => (string) config('vocabulary.audio.primary_us_path'),
            self::SOURCE_DICTIONARY_US => (string) config('vocabulary.audio.dictionary_us_path'),
            self::SOURCE_OWNER_RECORDING => (string) config('vocabulary.audio.owner_recording_path'),
        ] as $source => $basePath) {
            foreach ($fallbackNames as $fallbackName) {
                $resolved = $this->resolveInPublicPath($fallbackName, $basePath);

                if ($resolved !== null) {
                    return new VocabularyAudioResolution($resolved['url'], $resolved['path'], $source, $missingPrimary);
                }
            }
        }

        return new VocabularyAudioResolution(null, null, self::SOURCE_MISSING, $missingPrimary);
    }

    public function resolveByText(string $word): VocabularyAudioResolution
    {
        $record = Cambradge_word_api::query()
            ->whereRaw('LOWER(TRIM(word)) = ?', [Str::lower(trim($word))])
            ->first();

        if ($record instanceof Cambradge_word_api) {
            return $this->resolve($record);
        }

        $fallbackNames = $this->filenameCandidatesFromWord($word);

        if ($fallbackNames === []) {
            return new VocabularyAudioResolution(null, null, self::SOURCE_MISSING);
        }

        foreach ([
            self::SOURCE_PRIMARY_US => (string) config('vocabulary.audio.primary_us_path'),
            self::SOURCE_DICTIONARY_US => (string) config('vocabulary.audio.dictionary_us_path'),
            self::SOURCE_OWNER_RECORDING => (string) config('vocabulary.audio.owner_recording_path'),
        ] as $source => $basePath) {
            foreach ($fallbackNames as $fallbackName) {
                $resolved = $this->resolveInPublicPath($fallbackName, $basePath);

                if ($resolved !== null) {
                    return new VocabularyAudioResolution($resolved['url'], $resolved['path'], $source);
                }
            }
        }

        return new VocabularyAudioResolution(null, null, self::SOURCE_MISSING);
    }

    public function safeBasename(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $basename = basename(str_replace('\\', '/', $value));

        if (! preg_match('/\A[A-Za-z0-9._ -]+\.[a-z0-9]+\z/i', $basename)) {
            return null;
        }

        if (! in_array(Str::lower(pathinfo($basename, PATHINFO_EXTENSION)), $this->allowedAudioExtensions(), true)) {
            return null;
        }

        return $basename;
    }

    public function filenameFromWord(string $word): ?string
    {
        $slug = Str::of($word)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();

        return $slug === '' ? null : $slug.'.mp3';
    }

    /**
     * @return list<string>
     */
    private function filenameCandidatesFromWord(string $word): array
    {
        $word = trim($word);
        $underscore = $this->filenameFromWord($word);
        $dash = $underscore ? str_replace('_', '-', $underscore) : null;
        $joined = $underscore ? str_replace('_', '', $underscore) : null;
        $literal = Str::of($word)
            ->lower()
            ->replaceMatches('/[^a-z0-9 -]+/', '')
            ->trim()
            ->toString();

        $literal = $literal === '' ? null : $literal.'.mp3';

        $baseNames = collect([$underscore, $dash, $joined, $literal])
            ->filter()
            ->map(fn (string $filename): string => Str::of($filename)->beforeLast('.mp3')->toString())
            ->filter()
            ->values()
            ->all();

        return collect($baseNames)
            ->flatMap(fn (string $stem): array => collect($this->allowedAudioExtensions())
                ->map(fn (string $extension): string => $stem.'.'.$extension)
                ->all())
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array{url:string,path:string}|null
     */
    private function resolveInPublicPath(string $filename, string $basePath): ?array
    {
        $relativePath = trim($basePath, '/').'/'.$filename;
        $absolutePath = public_path(str_replace('/', DIRECTORY_SEPARATOR, $relativePath));

        if (! is_file($absolutePath)) {
            return null;
        }

        return [
            'url' => '/'.ltrim(str_replace('\\', '/', $relativePath), '/'),
            'path' => $relativePath,
        ];
    }

    /**
     * @return list<string>
     */
    private function allowedAudioExtensions(): array
    {
        return collect(config('vocabulary.dictionary.allowed_url_extensions', ['mp3']))
            ->map(fn (string $extension): string => Str::lower($extension))
            ->unique()
            ->values()
            ->all();
    }
}
