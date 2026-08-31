<?php

declare(strict_types=1);

namespace App\Payments\Exceptions;

use LogicException;

/**
 * Step D ships the refund ledger and interfaces only. Executing a refund
 * against a provider waits for step I, after the Quebec
 * refund/cancellation research (QUEBEC_REFUND_CANCELLATION_RESEARCH.md)
 * is answered by the owner's professional advisor and step I is approved.
 */
final class RefundExecutionPending extends LogicException
{
    public static function make(): self
    {
        return new self('Refund execution is not implemented: it is step I, gated on the Quebec refund research and its own approval.');
    }
}
