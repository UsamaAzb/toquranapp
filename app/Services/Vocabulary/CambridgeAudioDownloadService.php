<?php

namespace App\Services\Vocabulary;

use App\Models\Cambradge_word_api;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class CambridgeAudioDownloadService
{
    public function __construct(
        private readonly HttpFactory $http,
        private readonly VocabularyAudioResolver $audioResolver,
    ) {}

    public function replaceFromPartialPath(Cambradge_word_api $word, string $partialMediaPath): string
    {
        $path = $this->validatePartialPath($partialMediaPath);
        $url = rtrim($this->baseUrl(), '/').$path;

        return $this->replaceFromUrl($word, $url);
    }

    public function replaceFromCompleteUrl(Cambradge_word_api $word, string $url): string
    {
        return $this->replaceFromUrl($word, $this->validateCompleteUrl($url));
    }

    public function replaceFromLocalFile(Cambradge_word_api $word, string $sourcePath, ?string $originalName = null): string
    {
        $filename = $this->storeFromLocalFile((string) $word->word, $sourcePath, $originalName, $word);

        return $this->saveFilenameOnWord($word, $filename);
    }

    public function storeNewFromPartialPath(string $word, string $partialMediaPath): string
    {
        $path = $this->validatePartialPath($partialMediaPath);

        return $this->storeFromUrl($word, rtrim($this->baseUrl(), '/').$path);
    }

    public function storeNewFromCompleteUrl(string $word, string $url): string
    {
        return $this->storeFromUrl($word, $this->validateCompleteUrl($url));
    }

    public function storeNewFromLocalFile(string $word, string $sourcePath, ?string $originalName = null): string
    {
        return $this->storeFromLocalFile($word, $sourcePath, $originalName);
    }

    public function deleteStoredFilename(?string $filename): void
    {
        $safeFilename = $this->audioResolver->safeBasename((string) $filename);

        if ($safeFilename === null) {
            return;
        }

        $absolutePath = $this->absolutePathForFilename($safeFilename);

        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    public function validatePartialPath(string $partialMediaPath): string
    {
        $path = trim($partialMediaPath);

        if ($path === '' || preg_match('/\Ahttps?:\/\//i', $path)) {
            throw new InvalidArgumentException('Enter the dictionary media path only, not a full URL.');
        }

        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        if (str_contains($path, '..') || ! $this->hasAllowedAudioExtension($path)) {
            throw new InvalidArgumentException('The dictionary media path must point to a supported audio file.');
        }

        return $path;
    }

    public function validateCompleteUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            throw new InvalidArgumentException('Enter the complete audio URL.');
        }

        $parts = parse_url($url);

        if (! is_array($parts) || strtolower((string) ($parts['scheme'] ?? '')) !== 'https') {
            throw new InvalidArgumentException('Use a complete HTTPS audio URL.');
        }

        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($host === '' || $this->isBlockedOnlineHost($host)) {
            throw new InvalidArgumentException('Use a public online audio URL.');
        }

        if (! $this->hasAllowedAudioExtension((string) ($parts['path'] ?? ''))) {
            throw new InvalidArgumentException('The complete URL must point to a supported audio file.');
        }

        return $url;
    }

    private function replaceFromUrl(Cambradge_word_api $word, string $url): string
    {
        $filename = $this->storeFromUrl((string) $word->word, $url, $word);

        return $this->saveFilenameOnWord($word, $filename);
    }

    private function saveFilenameOnWord(Cambradge_word_api $word, string $filename): string
    {
        try {
            $word->forceFill(['us_sound' => $filename])->save();
        } catch (Throwable $exception) {
            $this->deleteStoredFilename($filename);

            throw new RuntimeException('The audio file was stored, but the vocabulary word could not be updated.', previous: $exception);
        }

        return $this->relativePathForFilename($filename);
    }

    private function storeFromUrl(string $word, string $url, ?Cambradge_word_api $existingWord = null): string
    {
        $filename = $this->targetFilename($word, $existingWord, null, $this->extensionFromPath($url));
        $temporaryPath = $this->temporaryPathForFilename($filename);

        try {
            $this->downloadToTemporaryPath($url, $temporaryPath);
            $this->validateStoredAudio($temporaryPath, $this->extensionFromPath($filename));
            $this->moveTemporaryFileToPrimaryAudio($temporaryPath, $filename);
        } catch (Throwable $exception) {
            @unlink($temporaryPath);

            throw $exception;
        }

        return $filename;
    }

    private function storeFromLocalFile(
        string $word,
        string $sourcePath,
        ?string $originalName = null,
        ?Cambradge_word_api $existingWord = null
    ): string {
        if (! is_file($sourcePath)) {
            throw new RuntimeException('Choose an audio file to upload.');
        }

        $this->validateStoredAudio($sourcePath, $this->extensionFromPath($originalName ?: $sourcePath));
        $filename = $this->targetFilename($word, $existingWord, $originalName);
        File::ensureDirectoryExists(dirname($this->absolutePathForFilename($filename)));

        if (! copy($sourcePath, $this->absolutePathForFilename($filename))) {
            throw new RuntimeException('The audio file could not be stored.');
        }

        return $filename;
    }

    private function downloadToTemporaryPath(string $url, string $temporaryPath): void
    {
        File::ensureDirectoryExists(dirname($temporaryPath));

        $options = [
            'sink' => $temporaryPath,
            'verify' => $this->verifyOption(),
            'allow_redirects' => [
                'max' => 3,
                'strict' => true,
                'referer' => true,
            ],
        ];

        try {
            $response = $this->http
                ->timeout((int) config('vocabulary.dictionary.timeout_seconds', 10))
                ->connectTimeout(10)
                ->withHeaders([
                    'Accept' => 'audio/mpeg,audio/*,*/*',
                    'User-Agent' => 'Mozilla/5.0',
                ])
                ->withOptions($options)
                ->get($url);

            $response->throw();
        } catch (ConnectionException $exception) {
            throw new RuntimeException($this->connectionErrorMessage($exception), previous: $exception);
        } catch (RequestException $exception) {
            $status = $exception->response?->status();

            if ($status === 404) {
                throw new RuntimeException('Cambridge could not find this audio path.', previous: $exception);
            }

            throw new RuntimeException('The audio URL could not be downloaded.', previous: $exception);
        } catch (Throwable $exception) {
            if ($this->looksLikeCaBundleProblem($exception->getMessage())) {
                throw new RuntimeException(
                    'PHP/cURL has no usable CA certificate bundle for this HTTPS download. Configure CAMBRIDGE_CA_BUNDLE or upload the audio file manually.',
                    previous: $exception
                );
            }

            throw $exception;
        }

        $contentType = strtolower((string) $response->header('Content-Type'));

        if ($contentType !== '' && $this->isClearlyNonAudioContentType($contentType)) {
            throw new RuntimeException('The audio URL returned a non-audio response.');
        }
    }

    private function isClearlyNonAudioContentType(string $contentType): bool
    {
        return str_contains($contentType, 'text/html')
            || str_contains($contentType, 'text/plain')
            || str_contains($contentType, 'application/json')
            || str_contains($contentType, 'application/xml')
            || str_contains($contentType, 'text/xml')
            || str_contains($contentType, 'javascript');
    }

    private function verifyOption(): string|bool
    {
        $caBundle = trim((string) config('services.cambridge.ca_bundle'));

        if ($caBundle === '') {
            return true;
        }

        if (! is_file($caBundle)) {
            throw new RuntimeException('The configured Cambridge CA bundle file was not found. Check CAMBRIDGE_CA_BUNDLE.');
        }

        return $caBundle;
    }

    private function connectionErrorMessage(ConnectionException $exception): string
    {
        if ($this->looksLikeCaBundleProblem($exception->getMessage())) {
            return 'Local SSL certificate verification failed while downloading this audio file. Configure CAMBRIDGE_CA_BUNDLE or upload the audio file manually.';
        }

        return 'The audio file could not be downloaded from this online source.';
    }

    private function looksLikeCaBundleProblem(string $message): bool
    {
        return str_contains($message, 'cURL error 60')
            || str_contains($message, 'SSL certificate')
            || str_contains($message, 'No system CA bundle')
            || str_contains($message, 'CA bundle could be found');
    }

    private function targetFilename(
        string $word,
        ?Cambradge_word_api $existingWord = null,
        ?string $originalName = null,
        ?string $preferredExtension = null
    ): string {
        $normalizedPreferredExtension = $this->normalAllowedExtension($preferredExtension);
        $existing = $this->audioResolver->safeBasename((string) ($originalName ?: $existingWord?->us_sound));

        if ($existing !== null) {
            $existingExtension = $this->extensionFromPath($existing);

            if ($normalizedPreferredExtension !== null && $existingExtension !== $normalizedPreferredExtension) {
                return $this->availableFilename(Str::of($existing)->beforeLast('.')->toString().'.'.$normalizedPreferredExtension);
            }

            return $this->availableFilename($existing);
        }

        $extension = $normalizedPreferredExtension ?? 'mp3';
        $fallback = $this->audioResolver->filenameFromWord($word) ?? 'word-'.Str::uuid().'.mp3';
        $filename = Str::of($fallback)->beforeLast('.')->toString().'.'.$extension;

        return $this->availableFilename($filename);
    }

    private function availableFilename(string $filename): string
    {
        $candidate = $filename;
        $extension = '.'.pathinfo($filename, PATHINFO_EXTENSION);
        $base = Str::of($filename)->beforeLast($extension)->toString();
        $counter = 2;

        while (is_file($this->absolutePathForFilename($candidate))) {
            $candidate = $base.'-'.$counter.$extension;
            $counter++;
        }

        return $candidate;
    }

    private function temporaryPathForFilename(string $filename): string
    {
        return storage_path('app/tmp/cambridge-audio/'.$filename);
    }

    private function relativePathForFilename(string $filename): string
    {
        return trim((string) config('vocabulary.audio.primary_us_path'), '/').'/'.$filename;
    }

    private function absolutePathForFilename(string $filename): string
    {
        return public_path(str_replace('/', DIRECTORY_SEPARATOR, $this->relativePathForFilename($filename)));
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.cambridge.media_base_url', config('vocabulary.dictionary.base_url')), '/');
    }

    private function moveTemporaryFileToPrimaryAudio(string $temporaryPath, string $filename): void
    {
        $absolutePath = $this->absolutePathForFilename($filename);

        File::ensureDirectoryExists(dirname($absolutePath));

        if (! @rename($temporaryPath, $absolutePath)) {
            if (! @copy($temporaryPath, $absolutePath)) {
                throw new RuntimeException('The downloaded audio file could not be stored.');
            }

            @unlink($temporaryPath);
        }
    }

    private function validateStoredAudio(string $path, ?string $extension = null): void
    {
        $maxBytes = (int) config('vocabulary.dictionary.max_bytes', 2097152);

        if (! is_file($path)) {
            throw new RuntimeException('The audio download did not create a file.');
        }

        $size = filesize($path);

        if ($size === false || $size < 1000) {
            throw new RuntimeException('The audio file is empty or too small.');
        }

        if ($size > $maxBytes) {
            throw new RuntimeException('The audio file is too large.');
        }

        $handle = fopen($path, 'rb');
        $header = $handle ? (string) fread($handle, 12) : '';

        if (is_resource($handle)) {
            fclose($handle);
        }

        if (! $this->looksLikeAudio($header, $extension)) {
            throw new RuntimeException('The file did not pass audio validation.');
        }
    }

    private function looksLikeAudio(string $header, ?string $extension = null): bool
    {
        $extension = $this->normalAllowedExtension($extension);

        if (in_array($extension, ['mp3', 'mpeg', 'mpga'], true)) {
            return str_starts_with($header, 'ID3') || str_starts_with($header, "\xFF\xFB") || str_starts_with($header, "\xFF\xF3");
        }

        if (in_array($extension, ['ogg', 'oga'], true)) {
            return str_starts_with($header, 'OggS');
        }

        if ($extension === 'wav') {
            return str_starts_with($header, 'RIFF') && str_contains($header, 'WAVE');
        }

        if (in_array($extension, ['m4a', 'aac'], true)) {
            return str_contains($header, 'ftyp') || str_starts_with($header, "\xFF\xF1") || str_starts_with($header, "\xFF\xF9");
        }

        return false;
    }

    private function hasAllowedAudioExtension(string $path): bool
    {
        return $this->normalAllowedExtension($this->extensionFromPath($path)) !== null;
    }

    private function extensionFromPath(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        return Str::lower(pathinfo(parse_url($path, PHP_URL_PATH) ?: $path, PATHINFO_EXTENSION)) ?: null;
    }

    private function normalAllowedExtension(?string $extension): ?string
    {
        $extension = Str::lower(trim((string) $extension, ". \t\n\r\0\x0B"));

        if ($extension === '') {
            return null;
        }

        return collect(config('vocabulary.dictionary.allowed_url_extensions', ['mp3']))
            ->map(fn (string $allowed): string => Str::lower($allowed))
            ->contains($extension)
                ? $extension
                : null;
    }

    private function isBlockedOnlineHost(string $host): bool
    {
        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        return filter_var(
            $host,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}
