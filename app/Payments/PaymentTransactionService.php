<?php

declare(strict_types=1);

namespace App\Payments;

use App\Payments\Contracts\Payable;
use App\Payments\Exceptions\InvalidPaymentTransition;
use App\Payments\Models\PaymentTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentTransactionService
{
    public function __construct(private readonly PaymentIdempotencyService $idempotency)
    {
    }

    /**
     * Create — or, under a reused idempotency key, return — the
     * transaction for a payable. Two loud failure modes protect the key
     * space: a key reused for a *different* operation (other payable,
     * gateway, amount, or currency) throws, and a key resolving to a
     * transaction already in a terminal state (failed, cancelled,
     * refunded) throws — a retry after a terminal outcome is a new
     * business operation and needs a new key. A non-terminal resolution
     * is returned in its current status: callers must read status rather
     * than assume pending.
     */
    public function createPending(
        Payable $payable,
        string $gatewayCode,
        string $idempotencyKey,
        ?array $safeMetadata = null,
    ): PaymentTransaction {
        $transaction = $this->idempotency->resolve($idempotencyKey, [
            'payable_type' => $payable->getPayableType(),
            'payable_id' => $payable->getPayableId(),
            'gateway_code' => $gatewayCode,
            'idempotency_key' => $idempotencyKey,
            'amount_minor' => $payable->getAmountMinor(),
            'currency' => strtoupper($payable->getCurrency()),
            'status' => PaymentStatus::PENDING,
            'safe_metadata' => $safeMetadata,
        ]);

        if ($transaction->payable_type !== $payable->getPayableType()
            || $transaction->payable_id !== $payable->getPayableId()
            || $transaction->gateway_code !== $gatewayCode
            || $transaction->amount_minor !== $payable->getAmountMinor()
            || $transaction->currency !== strtoupper($payable->getCurrency())
        ) {
            throw ValidationException::withMessages([
                'payment_transaction' => 'This idempotency key already belongs to a different payment operation.',
            ]);
        }

        if (PaymentStatus::isTerminal($transaction->status)) {
            throw ValidationException::withMessages([
                'payment_transaction' => sprintf(
                    'This idempotency key belongs to a %s transaction; a new attempt needs a new key.',
                    $transaction->status,
                ),
            ]);
        }

        return $transaction;
    }

    /**
     * Move a transaction along the design §10 state machine. The current
     * status is re-read under a row lock so concurrent transitions
     * serialize instead of clobbering each other; the returned model is
     * the fresh, transitioned row. Leaving refund_pending for succeeded
     * is additionally checked against recorded refund money: with
     * refunded_amount_minor above zero the truthful return state is
     * partially_refunded, and succeeded is refused.
     */
    public function transition(PaymentTransaction $transaction, string $to): PaymentTransaction
    {
        if (!PaymentStatus::isValid($to)) {
            throw InvalidPaymentTransition::between((string) $transaction->status, $to);
        }

        return DB::transaction(function () use ($transaction, $to): PaymentTransaction {
            /** @var PaymentTransaction $fresh */
            $fresh = PaymentTransaction::query()->lockForUpdate()->findOrFail($transaction->getKey());

            if (!PaymentStatus::canTransition($fresh->status, $to)) {
                throw InvalidPaymentTransition::between($fresh->status, $to);
            }

            if ($to === PaymentStatus::SUCCEEDED
                && $fresh->status === PaymentStatus::REFUND_PENDING
                && $fresh->refunded_amount_minor > 0) {
                throw new InvalidPaymentTransition(
                    'A transaction with recorded refund money returns to partially_refunded, not succeeded.',
                );
            }

            return PaymentTransaction::serviceWrite(function () use ($fresh, $to): PaymentTransaction {
                $fresh->status = $to;

                $stamp = match ($to) {
                    PaymentStatus::AUTHORIZED => 'authorized_at',
                    PaymentStatus::SUCCEEDED => 'succeeded_at',
                    PaymentStatus::FAILED => 'failed_at',
                    PaymentStatus::CANCELLED => 'cancelled_at',
                    PaymentStatus::REFUNDED => 'refunded_at',
                    default => null,
                };
                if ($stamp !== null && $fresh->{$stamp} === null) {
                    $fresh->{$stamp} = CarbonImmutable::now('UTC');
                }

                $fresh->save();

                return $fresh;
            });
        });
    }

    /**
     * Attach the provider's payment reference to a transaction — the
     * write path the PaymentGateway contract's createPayment() return
     * value goes through. Idempotent for the same reference; a different
     * reference on an already-referenced transaction, or a reference
     * already recorded on another transaction of the same gateway, fails
     * loudly.
     */
    public function attachExternalReference(PaymentTransaction $transaction, string $externalPaymentId): PaymentTransaction
    {
        if (trim($externalPaymentId) === '') {
            throw ValidationException::withMessages([
                'payment_transaction' => 'A provider payment reference cannot be empty.',
            ]);
        }

        try {
            return DB::transaction(function () use ($transaction, $externalPaymentId): PaymentTransaction {
                /** @var PaymentTransaction $fresh */
                $fresh = PaymentTransaction::query()->lockForUpdate()->findOrFail($transaction->getKey());

                if ($fresh->external_payment_id === $externalPaymentId) {
                    return $fresh;
                }

                if ($fresh->external_payment_id !== null) {
                    throw ValidationException::withMessages([
                        'payment_transaction' => 'This transaction already carries a different provider payment reference.',
                    ]);
                }

                return PaymentTransaction::serviceWrite(function () use ($fresh, $externalPaymentId): PaymentTransaction {
                    $fresh->external_payment_id = $externalPaymentId;
                    $fresh->save();

                    return $fresh;
                });
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'payment_transaction' => 'This provider payment reference is already recorded on another transaction.',
            ]);
        }
    }

    /**
     * Record refunded money against the transaction's cumulative total,
     * only while the transaction is in a refundable state. The cumulative
     * amount can never exceed the transaction amount; the sum is checked
     * as an integer so it cannot pass through float promotion.
     */
    public function addRefundedAmount(PaymentTransaction $transaction, int $amountMinor): PaymentTransaction
    {
        if ($amountMinor <= 0) {
            throw ValidationException::withMessages([
                'payment_transaction' => 'A refunded amount is a positive integer in minor units.',
            ]);
        }

        return DB::transaction(function () use ($transaction, $amountMinor): PaymentTransaction {
            /** @var PaymentTransaction $fresh */
            $fresh = PaymentTransaction::query()->lockForUpdate()->findOrFail($transaction->getKey());

            if (!PaymentStatus::isRefundable($fresh->status)) {
                throw ValidationException::withMessages([
                    'payment_transaction' => sprintf('Refund money cannot be recorded against a %s transaction.', $fresh->status),
                ]);
            }

            $sum = $fresh->refunded_amount_minor + $amountMinor;
            if (!is_int($sum) || $sum > $fresh->amount_minor) {
                throw ValidationException::withMessages([
                    'payment_transaction' => 'Cumulative refunds cannot exceed the transaction amount.',
                ]);
            }

            return PaymentTransaction::serviceWrite(function () use ($fresh, $sum): PaymentTransaction {
                $fresh->refunded_amount_minor = $sum;
                $fresh->save();

                return $fresh;
            });
        });
    }
}
