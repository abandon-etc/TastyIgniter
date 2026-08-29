<?php

declare(strict_types=1);

namespace App\Payments;

final class EventProcessingStatus
{
    public const string RECEIVED = 'received';

    public const string PROCESSED = 'processed';

    public const string SKIPPED = 'skipped';

    public const string FAILED = 'failed';

    /** @return array<int, string> */
    public static function all(): array
    {
        return [self::RECEIVED, self::PROCESSED, self::SKIPPED, self::FAILED];
    }

    public static function isValid(string $status): bool
    {
        return in_array($status, self::all(), true);
    }
}
