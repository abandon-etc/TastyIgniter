<?php

declare(strict_types=1);

namespace App\Payments;

final class EventProcessingStatus
{
    public const string RECEIVED = 'received';

    public const string PROCESSED = 'processed';

    public const string SKIPPED = 'skipped';

    public const string FAILED = 'failed';

    /**
     * processed and skipped are terminal; a failed event may be retried
     * to any outcome. Nothing regresses a handled event.
     *
     * @var array<string, array<int, string>>
     */
    private const array TRANSITIONS = [
        self::RECEIVED => [self::PROCESSED, self::SKIPPED, self::FAILED],
        self::FAILED => [self::PROCESSED, self::SKIPPED, self::FAILED],
        self::PROCESSED => [],
        self::SKIPPED => [],
    ];

    /** @return array<int, string> */
    public static function all(): array
    {
        return array_keys(self::TRANSITIONS);
    }

    public static function isValid(string $status): bool
    {
        return array_key_exists($status, self::TRANSITIONS);
    }

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }
}
