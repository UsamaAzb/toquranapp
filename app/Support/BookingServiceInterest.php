<?php

declare(strict_types=1);

namespace App\Support;

class BookingServiceInterest
{
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($value) {
            'IB Private Classes', 'IB Private Tutoring' => 'Quran Memorization',
            'SAT / ACT Preparation' => 'Quranic Arabic',
            'Help Me Study' => 'My Deen Journey',
            'Help Me Read' => 'Quranic Arabic',
            default => $value,
        };
    }

    public static function display(?string $value): string
    {
        return match (self::normalize($value)) {
            'Quran Memorization' => 'Quran Memorization',
            'Quranic Arabic' => 'Quranic Arabic',
            'My Deen Journey' => 'My Deen Journey',
            'Paid Parental Consultation' => 'Paid Parental Consultation',
            'Sanad Ijazah' => 'Sanad Ijazah',
            default => $value ?: 'Need Guidance',
        };
    }

    public static function isChildFacingOption(array $option): bool
    {
        $haystacks = [
            strtolower(trim((string) ($option['value'] ?? ''))),
            strtolower(trim((string) ($option['label'] ?? ''))),
        ];

        foreach ($haystacks as $haystack) {
            if ($haystack === '') {
                continue;
            }

            if ($haystack === 'not sure' || $haystack === 'not sure yet') {
                return false;
            }

            if (str_contains($haystack, 'parents') && str_contains($haystack, 'course')) {
                return false;
            }

            if (str_contains($haystack, 'teachers') && str_contains($haystack, 'course')) {
                return false;
            }
        }

        return true;
    }
}
