<?php

declare(strict_types=1);

namespace App\Payments\Exceptions;

use RuntimeException;

/**
 * The registration gate's refusal (step F). The reason code is for flow
 * control — send the customer to login or to the verification notice —
 * and the message is developer-facing; storefront wording is the theme's
 * job, in French first.
 */
final class PaymentAccessDenied extends RuntimeException
{
    public const string LOGIN_REQUIRED = 'login_required';

    public const string VERIFICATION_REQUIRED = 'verification_required';

    private function __construct(private readonly string $reason, string $message)
    {
        parent::__construct($message);
    }

    public static function loginRequired(): self
    {
        return new self(self::LOGIN_REQUIRED, 'A customer account is required before entering a payment flow.');
    }

    public static function verificationRequired(): self
    {
        return new self(self::VERIFICATION_REQUIRED, 'The customer e-mail address must be verified before entering a payment flow.');
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
