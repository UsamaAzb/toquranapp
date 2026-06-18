<?php

namespace App\Support\SeriesTasks;

readonly class SeriesLibraryItem
{
    public function __construct(
        public string $sourceType,
        public int $sourceId,
        public string $title,
        public ?string $summary,
        public ?string $url,
        public ?string $mediaPath = null,
        public ?string $mediaType = null,
        public ?int $fileSize = null,
    ) {}

    public function hasSafeDeliveryTarget(): bool
    {
        return filled($this->url)
            || filled($this->mediaPath)
            || ($this->mediaType === 'text' && filled($this->summary));
    }
}
