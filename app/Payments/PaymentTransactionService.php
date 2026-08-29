<?php

declare(strict_types=1);

namespace App\Payments;

use App\Payments\Contracts\Payable;
use App\Payments\Exceptions\InvalidPaymentTransition;
use App\Payments\Models\PaymentTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentTransactionService
{
    public function __construct(private readonly PaymentIdempotencyService $idempotency)
    {
    }

    /**
     * Create — or, under a reused idempotency key, return — the pending
     * transaction for a payable. A key reused for a *different* operation
     * (other payable, gateway, amount, or currency) is a programming
     * error and fails loudly rather than silently returning money state
     * that does not match the request.
     */
    public function createPending(
        Payable $payable,
        string $gatewayCode,
        string $idempotencyKey,
        ?array $safeMetadata = null,
    ): PaymentTransaction {
        $transaction = $this->idempotency->resolve($idempotencyKey, fn (): PaymentTransaction => PaymentTransaction::query()->create([
            'payable_type' => $payable->getPayableType(),
            'payable_id' => $payable->getPayableId(),
            'gateway_code' => $gatewayCode,
            'idempotency_key' => $idempotencyKey,
            'amount_minor' => $payable->getAmountMinor(),
            'currency' => strtoupper($payable->getCurrency()),
            'status' => PaymentStatus::PENDING,
            'safe_metadata' => $safeMetadata,
        ]));

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

        return $transaction;
    }

    /**
     * Move a transaction along the design §10 state machine. The current
     * status is re-read under a row lock so concurrent transitions
     * serialize instead of clobbering each other; the returned model is
     * the fresh, transitioned row.
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
     * Record refunded money against the transaction's cumulative total.
     * The cumulative amount can never exceed the transaction amount.
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

            if ($fresh->refunded_amount_minor + $amountMinor > $fresh->amount_minor) {
                throw ValidationException::withMessages([
                    'payment_transaction' => 'Cumulative refunds cannot exceed the transaction amount.',
                ]);
            }

            return PaymentTransaction::serviceWrite(function () use ($fresh, $amountMinor): PaymentTransaction {
                $fresh->refunded_amount_minor += $amountMinor;
                $fresh->save();

                return $fresh;
            });
        });
    }
}
