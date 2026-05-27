<?php

namespace App\Services;

/**
 * Value object returned by AutomatedTaskPublishValidator::validate().
 */
readonly class PublishValidationResult
{
    public function __construct(
        public bool $passes,
        /** @var string[] */
        public array $errors = [],
    ) {}

    public function fails(): bool
    {
        return ! $this->passes;
    }

    public function firstError(): ?string
    {
        return $this->errors[0] ?? null;
    }
}
