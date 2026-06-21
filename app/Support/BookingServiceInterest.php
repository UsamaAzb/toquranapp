<?php

declare(strict_types=1);

namespace App\Support;

class BookingServiceInterest
{
    public const QURAN_MEMORIZATION = 'Quran Memorization';

    public const QURANIC_ARABIC = 'Quranic Arabic';

    public const ARABIC_LANGUAGE = 'Arabic Language';

    public const ISLAMIC_STUDIES = 'Islamic Studies';

    public const QURAN_LITERATURE = 'Quran Literature';

    public const MY_DEEN_JOURNEY = 'My Deen Journey';

    public const PAID_PARENTAL_CONSULTATION = 'Paid Parental Consultation';

    public const SANAD_IJAZAH = 'Sanad Ijazah';

    public static function canonicalValues(): array
    {
        return [
            self::QURAN_MEMORIZATION,
            self::QURANIC_ARABIC,
            self::ARABIC_LANGUAGE,
            self::ISLAMIC_STUDIES,
            self::QURAN_LITERATURE,
            self::MY_DEEN_JOURNEY,
            self::PAID_PARENTAL_CONSULTATION,
            self::SANAD_IJAZAH,
        ];
    }

    public static function fallbackOptions(): array
    {
        return array_map(
            fn (string $value): array => ['value' => $value, 'label' => self::display($value)],
            self::childFacingValues()
        );
    }

    public static function childFacingValues(): array
    {
        return [
            self::QURAN_MEMORIZATION,
            self::QURANIC_ARABIC,
            self::ARABIC_LANGUAGE,
            self::ISLAMIC_STUDIES,
            self::QURAN_LITERATURE,
            self::MY_DEEN_JOURNEY,
            self::SANAD_IJAZAH,
        ];
    }

    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        $trimmed = self::stripChildServicePrefix($trimmed);
        $normalized = strtolower(preg_replace('/\s+/', ' ', $trimmed));

        return match ($normalized) {
            'ib private classes',
            'ib private tutoring',
            'quran',
            'quran memorization',
            'hifz',
            'memorization' => self::QURAN_MEMORIZATION,

            'sat / act preparation',
            'sat/act preparation',
            'sat or act preparation',
            'sat act preparation',
            'help me read',
            'quranic arabic' => self::QURANIC_ARABIC,

            'arabic',
            'arabic language' => self::ARABIC_LANGUAGE,

            'islamic studies',
            'islamic study',
            'islamic' => self::ISLAMIC_STUDIES,

            'quran literature',
            'qur\'an literature',
            'quranic literature',
            'quran stories' => self::QURAN_LITERATURE,

            'help me study',
            'my deen journey',
            'my deen journey (parenting system)',
            'deen journey' => self::MY_DEEN_JOURNEY,

            'paid parental consultation',
            'parental consultation',
            'paid consultation',
            'consultation' => self::PAID_PARENTAL_CONSULTATION,

            'sanad ijazah',
            'sanad ijazah program',
            'sanad',
            'ijazah' => self::SANAD_IJAZAH,

            default => $trimmed,
        };
    }

    public static function display(?string $value): string
    {
        return match (self::normalize($value)) {
            self::QURAN_MEMORIZATION => self::QURAN_MEMORIZATION,
            self::QURANIC_ARABIC => self::QURANIC_ARABIC,
            self::ARABIC_LANGUAGE => self::ARABIC_LANGUAGE,
            self::ISLAMIC_STUDIES => self::ISLAMIC_STUDIES,
            self::QURAN_LITERATURE => self::QURAN_LITERATURE,
            self::MY_DEEN_JOURNEY => self::MY_DEEN_JOURNEY,
            self::PAID_PARENTAL_CONSULTATION => self::PAID_PARENTAL_CONSULTATION,
            self::SANAD_IJAZAH => self::SANAD_IJAZAH,
            default => $value ?: 'Need Guidance',
        };
    }

    public static function isChildFacingOption(array $option): bool
    {
        $normalizedValue = self::normalize((string) ($option['value'] ?? ''));

        if ($normalizedValue === self::PAID_PARENTAL_CONSULTATION) {
            return false;
        }

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

    private static function stripChildServicePrefix(string $value): string
    {
        if (! str_contains($value, ':')) {
            return $value;
        }

        [$prefix, $candidate] = array_map('trim', explode(':', $value, 2));

        if ($prefix === '' || $candidate === '') {
            return $value;
        }

        return $candidate;
    }
}
