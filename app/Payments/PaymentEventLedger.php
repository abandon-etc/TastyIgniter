<?php

declare(strict_types=1);

namespace App\Payments;

use App\Payments\Models\PaymentEvent;
use App\Payments\Models\PaymentTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The unique provider-event ledger (design §13): every VERIFIED provider
 * event is recorded exactly once per gateway, and a duplicate delivery is
 * a no-op that returns the original row untouched. Deliveries that fail
 * signature verification never enter the ledger — their claimed event ids
 * are attacker-controlled, and recording one would permanently shadow the
 * genuine event in the unique namespace. Verifiers count and log invalid
 * deliveries by other means, without ids. Signature verification itself
 * belongs to the gateway adapters (step E and later); this ledger records
 * what a verifier concluded.
 */
class PaymentEventLedger
{
    /**
     * Record a verified provider event once. Insert-first: a lost unique
     * race is handled narrowly inside createOrFirst and returns the
     * winner. The returned model's wasRecentlyCreated says whether this
     * call inserted it (true) or a duplicate delivery found the original
     * (false, row untouched).
     */
    public function recordOnce(
        string $gatewayCode,
        string $externalEventId,
        string $eventType,
        bool $signatureValid,
        ?int $paymentTransactionId = null,
        ?array $safeSummary = null,
    ): PaymentEvent {
        if (!$signatureValid) {
            throw ValidationException::withMessages([
                'payment_event' => 'Unverified deliveries are not recorded: their event ids would shadow the genuine events.',
            ]);
        }

        $this->assertTransactionGatewayMatches($paymentTransactionId, $gatewayCode);

        /** @var PaymentEvent */
        return PaymentEvent::query()->createOrFirst(
            ['gateway_code' => $gatewayCode, 'external_event_id' => $externalEventId],
            [
                'event_type' => $eventType,
                'signature_valid' => true,
                'payment_transaction_id' => $paymentTransactionId,
                'safe_summary' => $safeSummary,
                'processing_status' => EventProcessingStatus::RECEIVED,
            ],
        );
    }

    public function markProcessed(PaymentEvent $event, ?int $paymentTransactionId = null): PaymentEvent
    {
        return $this->mark($event, EventProcessingStatus::PROCESSED, null, $paymentTransactionId);
    }

    public function markSkipped(PaymentEvent $event, string $safeReason): PaymentEvent
    {
        return $this->mark($event, EventProcessingStatus::SKIPPED, $safeReason);
    }

    public function markFailed(PaymentEvent $event, string $safeErrorCategory): PaymentEvent
    {
        return $this->mark($event, EventProcessingStatus::FAILED, $safeErrorCategory);
    }

    /**
     * Every mark re-reads the row under a lock and follows the
     * EventProcessingStatus transition rules, so concurrent markers
     * serialize, attempts count every processing attempt, and a handled
     * event (processed/skipped) can never be regressed. Reaching a
     * processed row clears any stale error category.
     */
    private function mark(
        PaymentEvent $event,
        string $status,
        ?string $safeError = null,
        ?int $paymentTransactionId = null,
    ): PaymentEvent {
        return DB::transaction(function () use ($event, $status, $safeError, $paymentTransactionId): PaymentEvent {
            /** @var PaymentEvent $fresh */
            $fresh = PaymentEvent::query()->lockForUpdate()->findOrFail($event->getKey());

            if (!EventProcessingStatus::canTransition($fresh->processing_status, $status)) {
                throw ValidationException::withMessages([
                    'payment_event' => sprintf(
                        'A %s event cannot be marked %s.',
                        $fresh->processing_status,
                        $status,
                    ),
                ]);
            }

            if ($paymentTransactionId !== null) {
                $this->assertTransactionGatewayMatches($paymentTransactionId, $fresh->gateway_code);
            }

            return PaymentEvent::serviceWrite(function () use ($fresh, $status, $safeError, $paymentTransactionId): PaymentEvent {
                $fresh->processing_status = $status;
                $fresh->attempts += 1;
                $fresh->processed_at = CarbonImmutable::now('UTC');
                $fresh->safe_error = $status === EventProcessingStatus::PROCESSED ? null : ($safeError ?? $fresh->safe_error);
                if ($paymentTransactionId !== null) {
                    $fresh->payment_transaction_id = $paymentTransactionId;
                }
                $fresh->save();

                return $fresh;
            });
        });
    }

    private function assertTransactionGatewayMatches(?int $paymentTransactionId, string $gatewayCode): void
    {
        if ($paymentTransactionId === null) {
            return;
        }

        $transaction = PaymentTransaction::query()->find($paymentTransactionId);

        if ($transaction === null || $transaction->gateway_code !== $gatewayCode) {
            throw ValidationException::withMessages([
                'payment_event' => 'The linked transaction does not exist or belongs to a different gateway.',
            ]);
        }
    }
}
