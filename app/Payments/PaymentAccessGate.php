<?php

declare(strict_types=1);

namespace App\Payments;

use App\Payments\Exceptions\PaymentAccessDenied;
use Igniter\User\Models\Customer;

/**
 * The registration gate of step F (design §14): browse anonymously,
 * but no payment flow opens without a logged-in, enabled customer —
 * and, while payments.require_verified_email is on, a verified
 * (activated) e-mail address. Server-side and mandatory: every payment
 * entry point calls this gate before any hold is bound, any transaction
 * is created, or any gateway is touched. UI checks are hints, never the
 * enforcement.
 */
class PaymentAccessGate
{
    public function assertMayEnterPayment(?Customer $customer): void
    {
        if ($customer === null || !$customer->enabled()) {
            throw PaymentAccessDenied::loginRequired();
        }

        if (config('payments.require_verified_email', true) && !$customer->is_activated) {
            throw PaymentAccessDenied::verificationRequired();
        }
    }

    public function mayEnterPayment(?Customer $customer): bool
    {
        try {
            $this->assertMayEnterPayment($customer);

            return true;
        } catch (PaymentAccessDenied) {
            return false;
        }
    }
}
