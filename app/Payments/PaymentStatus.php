<?php

declare(strict_types=1);

namespace App\Payments;

/**
 * The internal payment state vocabulary of
 * BIRTHDAY_PAYMENT_ARCHITECTURE_DESIGN.md §10. Gateway-specific names map
 * onto these; nothing outside this map is a legal transition.
 */
final class PaymentStatus
{
    public const string PENDING = 'pending';

    public const string REQUIRES_ACTION = 'requires_action';

    public const string AUTHORIZED = 'authorized';

    public const string SUCCEEDED = 'succeeded';

    public const string FAILED = 'failed';

    public const string CANCELLED = 'cancelled';

    public const string REFUND_PENDING = 'refund_pending';

    public const string PARTIALLY_REFUNDED = 'partially_refunded';

    public const string REFUNDED = 'refunded';

    /** @var array<string, array<int, string>> */
    private const array TRANSITIONS = [
        self::PENDING => [self::REQUIRES_ACTION, self::AUTHORIZED, self::SUCCEEDED, self::FAILED, self::CANCELLED],
        self::REQUIRES_ACTION => [self::AUTHORIZED, self::SUCCEEDED, self::FAILED, self::CANCELLED],
        self::AUTHORIZED => [self::SUCCEEDED, self::FAILED, self::CANCELLED],
        // A refund that fails moves the transaction back to succeeded; the
        // refund row keeps its own failed record.
        self::SUCCEEDED => [self::REFUND_PENDING, self::PARTIALLY_REFUNDED, self::REFUNDED],
        self::REFUND_PENDING => [self::SUCCEEDED, self::PARTIALLY_REFUNDED, self::REFUNDED],
        self::PARTIALLY_REFUNDED => [self::REFUND_PENDING, self::REFUNDED],
        self::FAILED => [],
        self::CANCELLED => [],
        self::REFUNDED => [],
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

    public static function isTerminal(string $status): bool
    {
        return self::isValid($status) && self::TRANSITIONS[$status] === [];
    }
}
