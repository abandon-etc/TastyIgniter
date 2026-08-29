<?php

declare(strict_types=1);

namespace App\Payments;

use App\Payments\Exceptions\RefundExecutionPending;
use App\Payments\Models\PaymentRefund;
use App\Payments\Models\PaymentTransaction;
use Illuminate\Database\QueryException;
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
     * Record a refund intent against a refundable transaction. Retrying
     * with the same provider refund id returns the original row (a
     * provider refund is one refund however often it is reported).
     */
    public function record(
        PaymentTransaction $transaction,
        int $amountMinor,
        ?string $safeReason = null,
        ?string $externalRefundId = null,
    ): PaymentRefund {
        if (!in_array($transaction->status, [
            PaymentStatus::SUCCEEDED,
            PaymentStatus::REFUND_PENDING,
            PaymentStatus::PARTIALLY_REFUNDED,
        ], true)) {
            throw ValidationException::withMessages([
                'payment_refund' => sprintf('A %s transaction is not refundable.', $transaction->status),
            ]);
        }

        if ($amountMinor <= 0
            || $transaction->refunded_amount_minor + $amountMinor > $transaction->amount_minor) {
            throw ValidationException::withMessages([
                'payment_refund' => 'A refund is a positive amount, and cumulative refunds cannot exceed the transaction amount.',
            ]);
        }

        if ($externalRefundId !== null) {
            $existing = $this->findProviderRefund($transaction->gateway_code, $externalRefundId);
            if ($existing !== null) {
                return $existing;
            }
        }

        try {
            return PaymentRefund::query()->create([
                'payment_transaction_id' => $transaction->getKey(),
                'gateway_code' => $transaction->gateway_code,
                'external_refund_id' => $externalRefundId,
                'amount_minor' => $amountMinor,
                'currency' => $transaction->currency,
                'status' => RefundStatus::PENDING,
                'safe_reason' => $safeReason,
            ]);
        } catch (QueryException $e) {
            if ($externalRefundId !== null) {
                $winner = $this->findProviderRefund($transaction->gateway_code, $externalRefundId);
                if ($winner !== null) {
                    return $winner;
                }
            }

            throw $e;
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

    private function findProviderRefund(string $gatewayCode, string $externalRefundId): ?PaymentRefund
    {
        return PaymentRefund::query()
            ->where('gateway_code', $gatewayCode)
            ->where('external_refund_id', $externalRefundId)
            ->first();
    }
}
