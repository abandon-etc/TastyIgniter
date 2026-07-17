<?php

declare(strict_types=1);

namespace Abandon\Birthday\Models;

use InvalidArgumentException;

final class PriceValue
{
    public const int MAX_MINOR_UNITS = 4294967295;

    public const string MAX_AMOUNT = '42949672.95';

    public static function toMinorUnits(mixed $value): int
    {
        $normalized = trim((string) $value);
        if ($normalized === '' || ! preg_match('/^(?:0|[1-9]\d*)(?:\.\d{1,2})?$/', $normalized)) {
            throw new InvalidArgumentException('Price must be a non-negative CAD amount with at most two decimals.');
        }

        $parts = explode('.', $normalized, 2);
        $whole = $parts[0];
        $fraction = $parts[1] ?? '';
        $minor = ltrim($whole.str_pad($fraction, 2, '0'), '0') ?: '0';
        $maximum = (string) self::MAX_MINOR_UNITS;

        if (strlen($minor) > strlen($maximum) || (strlen($minor) === strlen($maximum) && strcmp($minor, $maximum) > 0)) {
            throw new InvalidArgumentException('Price is too large.');
        }

        return (int) $minor;
    }

    public static function addMinorUnits(int ...$amounts): int
    {
        $total = 0;
        foreach ($amounts as $amount) {
            if ($amount < 0 || $total > PHP_INT_MAX - $amount) {
                throw new InvalidArgumentException('Price subtotal is outside the supported range.');
            }

            $total += $amount;
        }

        return $total;
    }

    public static function formatMinorUnits(int $minor): string
    {
        if ($minor < 0) {
            throw new InvalidArgumentException('Price cannot be negative.');
        }

        return intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function formatCad(int $minor): string
    {
        return '$'.self::formatMinorUnits($minor);
    }
}
