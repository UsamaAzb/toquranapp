<?php

declare(strict_types=1);

namespace App\Support;

final class SchoolSystemOptions
{
    public const IB = 'IB';
    public const AMERICAN = 'American';
    public const BRITISH = 'British';
    public const EGYPTIAN = 'Egyptian';
    public const OTHER = 'Other';

    public static function values(): array
    {
        return [
            self::IB,
            self::AMERICAN,
            self::BRITISH,
            self::EGYPTIAN,
            self::OTHER,
        ];
    }

    public static function labels(): array
    {
        return [
            self::IB => 'IB System',
            self::AMERICAN => 'American System',
            self::BRITISH => 'British System',
            self::EGYPTIAN => 'Egyptian System',
            self::OTHER => 'Other',
        ];
    }

    public static function normalize(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        return match ($normalized) {
            'ib', 'ib system', 'international baccalaureate' => self::IB,
            'american', 'american system' => self::AMERICAN,
            'british', 'british system' => self::BRITISH,
            'egyptian', 'egyptian system' => self::EGYPTIAN,
            'other', 'other system' => self::OTHER,
            default => null,
        };
    }

    public static function display(mixed $value): ?string
    {
        $normalized = self::normalize($value);

        if ($normalized === null) {
            return null;
        }

        return self::labels()[$normalized] ?? $normalized;
    }
}
