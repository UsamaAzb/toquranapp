<?php

namespace App\Support\ToQuranAutomationCatalog;

use RuntimeException;

final class AdhkarDuaBankCatalog
{
    private const SOURCE_FILE = 'to_quran_adhkar_dua_banks.md';

    /** @var array<int, array<string, mixed>>|null */
    private ?array $items = null;

    /** @return array<int, array<string, mixed>> */
    public function morningItems(): array
    {
        return $this->itemsForPrefix('MOR');
    }

    /** @return array<int, array<string, mixed>> */
    public function eveningItems(): array
    {
        return $this->itemsForPrefix('EVE');
    }

    /** @return array<int, array<string, mixed>> */
    public function duaItems(): array
    {
        return $this->itemsForPrefix('DUA');
    }

    /** @return array<int, array<string, mixed>> */
    private function itemsForPrefix(string $prefix): array
    {
        return array_values(array_filter(
            $this->items(),
            fn (array $item): bool => str_starts_with((string) $item['code'], $prefix.'-')
        ));
    }

    /** @return array<int, array<string, mixed>> */
    private function items(): array
    {
        if ($this->items !== null) {
            return $this->items;
        }

        $path = base_path(self::SOURCE_FILE);

        if (! is_file($path)) {
            throw new RuntimeException('Adhkar and dua bank source file is missing: '.self::SOURCE_FILE);
        }

        $content = file_get_contents($path);

        if (! is_string($content) || $content === '') {
            throw new RuntimeException('Adhkar and dua bank source file is empty: '.self::SOURCE_FILE);
        }

        preg_match_all(
            '/^## ((?:MOR|EVE|DUA)-\d{3}) - (.+?)\R(.*?)(?=^## (?:MOR|EVE|DUA)-\d{3} - |\z)/ms',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        $this->items = array_map(fn (array $match): array => $this->parseItem($match), $matches);

        if (count($this->items) !== 99) {
            throw new RuntimeException('Expected 99 adhkar/dua bank entries, found '.count($this->items).'.');
        }

        return $this->items;
    }

    /** @param array<int, string> $match */
    private function parseItem(array $match): array
    {
        $fields = [];
        preg_match_all('/^- \*\*(.+?):\*\*\s*(.*)$/m', $match[3], $fieldMatches, PREG_SET_ORDER);

        foreach ($fieldMatches as $fieldMatch) {
            $fields[strtolower(str_replace(' ', '_', trim($fieldMatch[1])))] = trim($fieldMatch[2]);
        }

        $code = trim($match[1]);
        $title = trim($match[2]);
        $codeNumber = $this->codeNumber($code);
        $sortOrder = (int) ($fields['sort_order'] ?? 0);

        if ($sortOrder <= 0) {
            $sortOrder = $codeNumber;
        }

        return [
            'code' => $code,
            'code_number' => $codeNumber,
            'key' => strtolower(str_replace('-', '_', $code)),
            'title' => $title,
            'level' => $fields['level'] ?? null,
            'category' => $fields['category'] ?? null,
            'repeat' => $fields['repeat'] ?? null,
            'arabic' => $fields['arabic'] ?? null,
            'english_meaning' => $fields['english_meaning'] ?? null,
            'quran_ref' => $fields['quran_ref'] ?? null,
            'source' => $fields['source'] ?? null,
            'source_url' => $fields['source_url'] ?? null,
            'sort_order' => $sortOrder,
        ];
    }

    private function codeNumber(string $code): int
    {
        if (! preg_match('/^(?:MOR|EVE|DUA)-(\d{3})$/', $code, $matches)) {
            throw new RuntimeException("Invalid adhkar/dua bank code '{$code}'.");
        }

        return (int) $matches[1];
    }
}
