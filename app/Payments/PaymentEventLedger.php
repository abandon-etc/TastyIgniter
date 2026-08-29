<?php

declare(strict_types=1);

namespace App\Payments;

use App\Payments\Models\PaymentEvent;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

/**
 * The unique provider-event ledger (design §13): every provider event is
 * recorded exactly once per gateway, and a duplicate delivery is a no-op
 * that returns the original row untouched. Webhook signature
 * VERIFICATION is not here — it belongs to the gateway adapters (step E
 * and later); this ledger records what a verifier concluded.
 */
class PaymentEventLedger
{
    /**
     * Record a provider event once. The returned model's
     * wasRecentlyCreated says whether this call inserted it (true) or a
     * duplicate delivery found the original (false, row untouched).
     */
    public function recordOnce(
        string $gatewayCode,
        string $externalEventId,
        string $eventType,
        bool $signatureValid,
        ?int $paymentTransactionId = null,
        ?array $safeSummary = null,
    ): PaymentEvent {
        $existing = $this->find($gatewayCode, $externalEventId);
        if ($existing !== null) {
            return $existing;
        }

        try {
            return PaymentEvent::query()->create([
                'gateway_code' => $gatewayCode,
                'external_event_id' => $externalEventId,
                'event_type' => $eventType,
                'signature_valid' => $signatureValid,
                'payment_transaction_id' => $paymentTransactionId,
                'safe_summary' => $safeSummary,
                'processing_status' => EventProcessingStatus::RECEIVED,
            ]);
        } catch (QueryException $e) {
            $winner = $this->find($gatewayCode, $externalEventId);
            if ($winner !== null) {
                return $winner;
            }

            throw $e;
        }
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

    private function mark(
        PaymentEvent $event,
        string $status,
        ?string $safeError = null,
        ?int $paymentTransactionId = null,
    ): PaymentEvent {
        if (!EventProcessingStatus::isValid($status)) {
            throw ValidationException::withMessages([
                'payment_event' => sprintf('Unknown event processing status [%s].', $status),
            ]);
        }

        return PaymentEvent::serviceWrite(function () use ($event, $status, $safeError, $paymentTransactionId): PaymentEvent {
            $event->processing_status = $status;
            $event->attempts += 1;
            $event->processed_at = CarbonImmutable::now('UTC');
            if ($safeError !== null) {
                $event->safe_error = $safeError;
            }
            if ($paymentTransactionId !== null) {
                $event->payment_transaction_id = $paymentTransactionId;
            }
            $event->save();

            return $event;
        });
    }

    private function find(string $gatewayCode, string $externalEventId): ?PaymentEvent
    {
        return PaymentEvent::query()
            ->where('gateway_code', $gatewayCode)
            ->where('external_event_id', $externalEventId)
            ->first();
    }
}
