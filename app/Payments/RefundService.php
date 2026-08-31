<?php

declare(strict_types=1);

namespace App\Payments;

use App\Payments\Exceptions\RefundExecutionPending;
use App\Payments\Models\PaymentRefund;
use App\Payments\Models\PaymentTransaction;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The refund interface shell of step D: the ledger exists and refund
 * intents can be recorded with provider-scoped uniqueness, but nothing
 * executes against a provider. Execution is step I, after the Quebec
 * refund/cancellation research is answered and step I is approved.
 */
class RefundService
{
    /**
     * Record a refund intent against a refundable transaction. The
     * transaction row is locked for the duration, and the cap counts
     * BOTH applied refund money and every already-pending intent, so
     * intents can never cumulatively exceed the transaction amount.
     * Retrying with the same provider refund id returns the original row
     * only when it belongs to the same transaction with the same amount;
     * any other reuse of a provider refund id fails loudly.
     */
    public function record(
        PaymentTransaction $transaction,
        int $amountMinor,
        ?string $safeReason = null,
        ?string $externalRefundId = null,
    ): PaymentRefund {
        if ($externalRefundId !== null && trim($externalRefundId) === '') {
            throw ValidationException::withMessages([
                'payment_refund' => 'A provider refund id cannot be empty; pass null when the provider gave none.',
            ]);
        }

        try {
            return DB::transaction(function () use ($transaction, $amountMinor, $safeReason, $externalRefundId): PaymentRefund {
                /** @var PaymentTransaction $fresh */
                $fresh = PaymentTransaction::query()->lockForUpdate()->findOrFail($transaction->getKey());

                if (!PaymentStatus::isRefundable($fresh->status)) {
                    throw ValidationException::withMessages([
                        'payment_refund' => sprintf('A %s transaction is not refundable.', $fresh->status),
                    ]);
                }

                if ($externalRefundId !== null) {
                    $existing = $this->findProviderRefund($fresh->gateway_code, $externalRefundId);
                    if ($existing !== null) {
                        return $this->assertSameRefund($existing, $fresh, $amountMinor);
                    }
                }

                $pendingMinor = (int) PaymentRefund::query()
                    ->where('payment_transaction_id', $fresh->getKey())
                    ->where('status', RefundStatus::PENDING)
                    ->sum('amount_minor');

                $committed = $fresh->refunded_amount_minor + $pendingMinor + $amountMinor;
                if ($amountMinor <= 0 || !is_int($committed) || $committed > $fresh->amount_minor) {
                    throw ValidationException::withMessages([
                        'payment_refund' => 'A refund is a positive amount, and applied plus pending refunds cannot exceed the transaction amount.',
                    ]);
                }

                return PaymentRefund::query()->create([
                    'payment_transaction_id' => $fresh->getKey(),
                    'gateway_code' => $fresh->gateway_code,
                    'external_refund_id' => $externalRefundId,
                    'amount_minor' => $amountMinor,
                    'currency' => $fresh->currency,
                    'status' => RefundStatus::PENDING,
                    'safe_reason' => $safeReason,
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            // A racer inserted the same provider refund id between our
            // check and insert; hold it to the same identity rules.
            $winner = $externalRefundId !== null
                ? $this->findProviderRefund($transaction->gateway_code, $externalRefundId)
                : null;

            if ($winner !== null) {
                return $this->assertSameRefund($winner, $transaction, $amountMinor);
            }

            throw ValidationException::withMessages([
                'payment_refund' => 'This provider refund id is already recorded.',
            ]);
        }
    }

    /**
     * Deliberately unimplemented: no refund reaches a provider before
     * step I. The exception, not silence, is the contract.
     */
    public function execute(PaymentRefund $refund): never
    {
        throw RefundExecutionPending::make();
    }

    private function assertSameRefund(PaymentRefund $existing, PaymentTransaction $transaction, int $amountMinor): PaymentRefund
    {
        if ($existing->payment_transaction_id !== (int) $transaction->getKey()
            || $existing->amount_minor !== $amountMinor) {
            throw ValidationException::withMessages([
                'payment_refund' => 'This provider refund id already belongs to a different refund operation.',
            ]);
        }

        return $existing;
    }

    private function findProviderRefund(string $gatewayCode, string $externalRefundId): ?PaymentRefund
    {
        return PaymentRefund::query()
            ->where('gateway_code', $gatewayCode)
            ->where('external_refund_id', $externalRefundId)
            ->first();
    }
}
