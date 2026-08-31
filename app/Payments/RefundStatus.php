<?php

declare(strict_types=1);

namespace App\Payments;

final class RefundStatus
{
    public const string PENDING = 'pending';

    public const string SUCCEEDED = 'succeeded';

    public const string FAILED = 'failed';

    /** @return array<int, string> */
    public static function all(): array
    {
        return [self::PENDING, self::SUCCEEDED, self::FAILED];
    }

    public static function isValid(string $status): bool
    {
        return in_array($status, self::all(), true);
    }
}
