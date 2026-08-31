<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Payments\Contracts\Payable;

final class FakePayable implements Payable
{
    public function __construct(
        private readonly int $amountMinor = 31500,
        private readonly string $currency = 'CAD',
        private readonly int $payableId = 424242,
    ) {
    }

    public function getPayableType(): string
    {
        return 'birthday_bookings';
    }

    public function getPayableId(): int
    {
        return $this->payableId;
    }

    public function getAmountMinor(): int
    {
        return $this->amountMinor;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getPaymentDescription(): string
    {
        return 'Test booking';
    }
}
