<?php

declare(strict_types=1);

namespace App\Support;

class PhoneNormalizer
{
    public static function normalize(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone) ?? '';

        if (preg_match('/^00201\d{9}$/', $digits) === 1) {
            return substr($digits, 4);
        }

        if (preg_match('/^201\d{9}$/', $digits) === 1) {
            return substr($digits, 2);
        }

        if (preg_match('/^01\d{9}$/', $digits) === 1) {
            return substr($digits, 1);
        }

        return $digits;
    }

    public static function sqlExpression(string $column, string $driverName): string
    {
        $digitsExpression = self::digitsOnlySqlExpression($column, $driverName);

        return <<<SQL
CASE
    WHEN LENGTH({$digitsExpression}) = 14
        AND SUBSTR({$digitsExpression}, 1, 5) = '00201'
        THEN SUBSTR({$digitsExpression}, 5)
    WHEN LENGTH({$digitsExpression}) = 12
        AND SUBSTR({$digitsExpression}, 1, 3) = '201'
        THEN SUBSTR({$digitsExpression}, 3)
    WHEN LENGTH({$digitsExpression}) = 11
        AND SUBSTR({$digitsExpression}, 1, 2) = '01'
        THEN SUBSTR({$digitsExpression}, 2)
    ELSE {$digitsExpression}
END
SQL;
    }

    protected static function digitsOnlySqlExpression(string $column, string $driverName): string
    {
        if ($driverName === 'sqlite') {
            // SQLite has no regex replace, so we chain REPLACE for the most common
            // phone separators. This intentionally does not cover every non-digit
            // character (e.g. /, #, *). Known test/prod parity gap: if a stored
            // phone contains separators outside this set, SQLite and MySQL results
            // will differ. Expand this chain if new country formats introduce
            // additional separators not covered here.
            return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE({$column}, ''), ' ', ''), '-', ''), '(', ''), ')', ''), '+', ''), '.', '')";
        }

        return "REGEXP_REPLACE(COALESCE({$column}, ''), '[^0-9]', '')";
    }
}
