<?php

namespace App\Support\SeriesTasks;

readonly class SeriesTaskGenerationOutcome
{
    private function __construct(
        public string $status,
        public ?int $sequencePosition = null,
    ) {}

    public static function generated(int $sequencePosition): self
    {
        return new self('generated', $sequencePosition);
    }

    public static function skipped(): self
    {
        return new self('skipped');
    }

    public static function blocked(): self
    {
        return new self('blocked');
    }

    public static function completed(): self
    {
        return new self('completed');
    }

    public function generatedRows(): bool
    {
        return $this->status === 'generated';
    }
}
