<?php

declare(strict_types=1);

namespace Abandon\Birthday\Models;

final class BirthdaySlotHoldStatus
{
    public const string ACTIVE = 'active';

    public const string RELEASED = 'released';

    public const string EXPIRED = 'expired';

    /** @return array<int, string> */
    public static function all(): array
    {
        return [self::ACTIVE, self::RELEASED, self::EXPIRED];
    }

    public static function isValid(string $status): bool
    {
        return in_array($status, self::all(), true);
    }
}
