<?php

declare(strict_types=1);

namespace App\Payments;

use App\Payments\Exceptions\PaymentAccessDenied;
use Igniter\User\Facades\Auth;
use Igniter\User\Models\Customer;

/**
 * The registration gate of step F (design §14): browse anonymously,
 * but no payment flow opens without a logged-in, enabled customer —
 * and, while payments.require_verified_email is on, a verified
 * (activated) e-mail address. Server-side and mandatory: every payment
 * entry point calls this gate before any hold is bound, any transaction
 * is created, or any gateway is touched. UI checks are hints, never the
 * enforcement.
 *
 * Entry points call assertMayEnterPayment(), which reads the
 * authenticated customer from the guard itself. Passing a customer in
 * would let a caller hand over a Customer loaded from the payable — a
 * row that proves nothing about who is at the keyboard — so the
 * explicit-customer form is deliberately separate and named for what it
 * is.
 */
class PaymentAccessGate
{
    /** Assert the *authenticated* customer may enter a payment flow. */
    public function assertMayEnterPayment(): Customer
    {
        $customer = Auth::customer();

        $this->assertCustomerMayEnterPayment($customer instanceof Customer ? $customer : null);

        /** @var Customer $customer */
        return $customer;
    }

    public function mayEnterPayment(): bool
    {
        try {
            $this->assertMayEnterPayment();

            return true;
        } catch (PaymentAccessDenied) {
            return false;
        }
    }

    /**
     * Apply the same rules to a customer the caller already holds. Only
     * for paths where the customer's identity is established by
     * something other than the storefront session (tests, admin-side
     * tooling); customer-facing entry points use assertMayEnterPayment().
     */
    public function assertCustomerMayEnterPayment(?Customer $customer): void
    {
        if ($customer === null || !$customer->enabled()) {
            throw PaymentAccessDenied::loginRequired();
        }

        if (config('payments.require_verified_email', true) && !$customer->is_activated) {
            throw PaymentAccessDenied::verificationRequired();
        }
    }
}
