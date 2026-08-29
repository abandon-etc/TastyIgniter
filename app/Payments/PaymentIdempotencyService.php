<?php

declare(strict_types=1);

namespace App\Payments;

use App\Payments\Models\PaymentTransaction;
use Closure;
use Illuminate\Database\QueryException;

/**
 * Durable business idempotency (design §13): one idempotency key resolves
 * to exactly one payment transaction, across requests and instances,
 * backed by the database unique constraint rather than session state.
 */
class PaymentIdempotencyService
{
    /**
     * Return the transaction already holding $key, or create it via
     * $create. A concurrent creator losing the unique-constraint race
     * re-reads the winner's row instead of failing.
     *
     * @param Closure(): PaymentTransaction $create
     */
    public function resolve(string $key, Closure $create): PaymentTransaction
    {
        $existing = PaymentTransaction::query()->where('idempotency_key', $key)->first();
        if ($existing !== null) {
            return $existing;
        }

        try {
            return $create();
        } catch (QueryException $e) {
            $winner = PaymentTransaction::query()->where('idempotency_key', $key)->first();
            if ($winner !== null) {
                return $winner;
            }

            throw $e;
        }
    }
}
