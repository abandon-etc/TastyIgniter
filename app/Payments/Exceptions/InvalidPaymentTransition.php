<?php

declare(strict_types=1);

namespace App\Payments\Exceptions;

use LogicException;

final class InvalidPaymentTransition extends LogicException
{
    public static function between(string $from, string $to): self
    {
        return new self(sprintf('A payment cannot move from %s to %s.', $from, $to));
    }
}
