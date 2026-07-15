<?php

declare(strict_types=1);

namespace Abandon\Birthday\Models;

use InvalidArgumentException;

final class PriceValue
{
    public static function toMinorUnits(mixed $value): int
    {
        $normalized = trim((string) $value);
        if ($normalized === '' || ! preg_match('/^(?:0|[1-9]\d*)(?:\.\d{1,2})?$/', $normalized)) {
            throw new InvalidArgumentException('Price must be a non-negative CAD amount with at most two decimals.');
        }

        [$whole, $fraction = ''] = explode('.', $normalized, 2) + ['', ''];
        $minor = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
        if ($minor > 4294967295) {
            throw new InvalidArgumentException('Price is too large.');
        }

        return $minor;
    }
}
