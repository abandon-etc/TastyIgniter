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
     * transaction, returning the provider reference to store as
     * external_payment_id. Must be idempotent per transaction.
     */
    public function createPayment(PaymentTransaction $transaction): string;

    /**
     * Verify a webhook's raw body and signature. Implementations must
     * verify before parsing, and must never log or store the raw body,
     * signature, or any credential.
     */
    public function verifyWebhookSignature(string $rawBody, array $headers): bool;
}
