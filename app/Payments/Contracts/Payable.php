<?php

declare(strict_types=1);

namespace App\Payments\Contracts;

/**
 * The small payable contract of BIRTHDAY_PAYMENT_ARCHITECTURE_DESIGN.md §6.
 * The payment layer references a payable only by type and id; it never
 * creates a fake Order and never trusts a description, session-only id, or
 * URL parameter as identity.
 */
interface Payable
{
    /** For example 'orders' or 'birthday_bookings'. */
    public function getPayableType(): string;

    public function getPayableId(): int;

    /** Integer minor units; never a float. */
    public function getAmountMinor(): int;

    /** ISO 4217, for example 'CAD'. */
    public function getCurrency(): string;

    public function getPaymentDescription(): string;
}
