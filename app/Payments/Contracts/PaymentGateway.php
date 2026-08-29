<?php

declare(strict_types=1);

namespace App\Payments\Contracts;

use App\Payments\Models\PaymentTransaction;

/**
 * The gateway adapter contract (design §7). Step D ships the contract
 * only: no adapter exists yet, and none may perform a live call. The
 * first implementation is the step E fake/test-mode gateway; adapters
 * wrapping installed PayRegister gateways come later and stay app-owned.
 */
interface PaymentGateway
{
    /** The internal gateway code stored on transactions, e.g. 'fake'. */
    public function code(): string;

    /**
     * Begin a provider payment for an already-created local pending
     * transaction, returning the provider reference. The caller stores it
     * through PaymentTransactionService::attachExternalReference() — the
     * only sanctioned write path for external_payment_id, which also
     * handles the provider-scoped uniqueness race. Must be idempotent per
     * transaction.
     */
    public function createPayment(PaymentTransaction $transaction): string;

    /**
     * Verify a webhook's raw body and signature. Implementations must
     * verify before parsing, and must never log or store the raw body,
     * signature, or any credential.
     *
     * @param array<string, string> $headers Header names lowercased,
     *     each value the header's first (and for signatures only)
     *     string value. Callers normalize before passing; adapters must
     *     look up lowercase names only.
     */
    public function verifyWebhookSignature(string $rawBody, array $headers): bool;
}
