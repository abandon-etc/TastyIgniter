<?php

declare(strict_types=1);

namespace App\Payments;

use App\Payments\Models\PaymentTransaction;

/**
 * Durable business idempotency (design §13): one idempotency key resolves
 * to exactly one payment transaction, across requests and instances,
 * backed by the database unique constraint rather than session state.
 */
class PaymentIdempotencyService
{
    /**
     * Return the transaction already holding $key, or create it with
     * $attributes. Uses Eloquent's createOrFirst: insert-first, a lost
     * unique race is caught narrowly (UniqueConstraintViolationException,
     * MySQL 1062 only) and the winner's row is returned — deadlocks and
     * every other database failure propagate instead of being mistaken
     * for a lost race. Inside an enclosing REPEATABLE READ transaction
     * the winner re-read carries the framework's documented stale-snapshot
     * caveat; the payment services call this outside such wrappers.
     */
    public function resolve(string $key, array $attributes): PaymentTransaction
    {
        /** @var PaymentTransaction */
        return PaymentTransaction::query()->createOrFirst(
            ['idempotency_key' => $key],
            $attributes,
        );
    }
}
