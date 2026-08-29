<?php

declare(strict_types=1);

namespace Tests\Unit\Payments;

use App\Payments\PaymentStatus;
use PHPUnit\Framework\TestCase;

final class PaymentStatusTest extends TestCase
{
    public function test_the_design_section_10_vocabulary_is_complete(): void
    {
        $this->assertSame([
            'pending', 'requires_action', 'authorized', 'succeeded',
            'refund_pending', 'partially_refunded', 'failed', 'cancelled',
            'refunded',
        ], PaymentStatus::all());
    }

    public function test_forward_transitions_are_allowed(): void
    {
        foreach ([
            ['pending', 'requires_action'],
            ['pending', 'succeeded'],
            ['pending', 'failed'],
            ['requires_action', 'authorized'],
            ['authorized', 'succeeded'],
            ['succeeded', 'refund_pending'],
            ['refund_pending', 'succeeded'],
            ['refund_pending', 'refunded'],
            ['partially_refunded', 'refunded'],
        ] as [$from, $to]) {
            $this->assertTrue(PaymentStatus::canTransition($from, $to), "$from -> $to must be allowed");
        }
    }

    public function test_backward_and_terminal_transitions_are_refused(): void
    {
        foreach ([
            ['succeeded', 'pending'],
            ['failed', 'succeeded'],
            ['cancelled', 'pending'],
            ['refunded', 'refund_pending'],
            ['authorized', 'requires_action'],
            ['pending', 'refunded'],
        ] as [$from, $to]) {
            $this->assertFalse(PaymentStatus::canTransition($from, $to), "$from -> $to must be refused");
        }
    }

    public function test_terminal_states_are_exactly_failed_cancelled_refunded(): void
    {
        $terminals = array_values(array_filter(PaymentStatus::all(), PaymentStatus::isTerminal(...)));

        $this->assertSame(['failed', 'cancelled', 'refunded'], $terminals);
    }

    public function test_unknown_statuses_are_invalid_and_cannot_transition(): void
    {
        $this->assertFalse(PaymentStatus::isValid('paid'));
        $this->assertFalse(PaymentStatus::canTransition('paid', 'succeeded'));
        $this->assertFalse(PaymentStatus::isTerminal('paid'));
    }
}
