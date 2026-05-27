<?php

namespace App\Services\Vocabulary;

class VocabularyAudioResolution
{
    public function __construct(
        public readonly ?string $url,
        public readonly ?string $path,
        public readonly string $source,
        public readonly bool $missingPrimary = false,
    ) {}

    public function available(): bool
    {
        return filled($this->url);
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'path' => $this->path,
            'source' => $this->source,
            'missing_primary' => $this->missingPrimary,
            'available' => $this->available(),
        ];
    }
}
