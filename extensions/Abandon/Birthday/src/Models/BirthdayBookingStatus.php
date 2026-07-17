<?php

declare(strict_types=1);

namespace Abandon\Birthday\Models;

final class BirthdayBookingStatus
{
    public const string CATALOG_PRICED = 'catalog_priced';

    public const string CANCELLED = 'cancelled';

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [self::CATALOG_PRICED, self::CANCELLED];
    }

    public static function isValid(string $status): bool
    {
        return in_array($status, self::all(), true);
    }
}
