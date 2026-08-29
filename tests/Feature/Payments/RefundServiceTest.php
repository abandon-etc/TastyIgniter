<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Payments\Exceptions\RefundExecutionPending;
use App\Payments\Models\PaymentRefund;
use App\Payments\PaymentStatus;
use App\Payments\PaymentTransactionService;
use App\Payments\RefundService;
use App\Payments\RefundStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class RefundServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_refund_intent_is_recorded_pending_against_a_succeeded_transaction(): void
    {
        $refund = $this->refunds()->record($this->succeededTransaction('key-r1'), 1500, 'customer_request');

        $this->assertSame(RefundStatus::PENDING, $refund->status);
        $this->assertSame(1500, $refund->amount_minor);
        $this->assertSame('CAD', $refund->currency);
        $this->assertSame('fake', $refund->gateway_code);
    }

    public function test_a_pending_transaction_is_not_refundable(): void
    {
        $transaction = $this->transactions()->createPending(new FakePayable(), 'fake', 'key-r2');

        $this->expectException(ValidationException::class);
        $this->refunds()->record($transaction, 1500);
    }

    public function test_a_refund_cannot_exceed_the_remaining_amount(): void
    {
        $transaction = $this->succeededTransaction('key-r3');
        $transaction = $this->transactions()->addRefundedAmount($transaction, 31000);

        $this->expectException(ValidationException::class);
        $this->refunds()->record($transaction, 501);
    }

    public function test_the_same_provider_refund_id_returns_the_original_row(): void
    {
        $transaction = $this->succeededTransaction('key-r4');

        $first = $this->refunds()->record($transaction, 1000, null, 're_1');
        $second = $this->refunds()->record($transaction, 1000, null, 're_1');

        $this->assertTrue($second->is($first));
        $this->assertFalse($second->wasRecentlyCreated);
        $this->assertSame(1, PaymentRefund::query()->where('external_refund_id', 're_1')->count());
    }

    public function test_pending_intents_are_counted_against_the_cap(): void
    {
        $transaction = $this->succeededTransaction('key-r6');

        $this->refunds()->record($transaction, 20000);

        $this->expectException(ValidationException::class);
        $this->refunds()->record($transaction, 11501);
    }

    public function test_a_provider_refund_id_reused_for_a_different_operation_fails_loudly(): void
    {
        $transaction = $this->succeededTransaction('key-r7');
        $this->refunds()->record($transaction, 1000, null, 're_mismatch');

        $this->expectException(ValidationException::class);
        $this->refunds()->record($transaction, 2000, null, 're_mismatch');
    }

    public function test_an_empty_provider_refund_id_is_refused(): void
    {
        $this->expectException(ValidationException::class);
        $this->refunds()->record($this->succeededTransaction('key-r8'), 1000, null, '');
    }

    public function test_execute_refuses_until_step_i(): void
    {
        $refund = $this->refunds()->record($this->succeededTransaction('key-r5'), 1000);

        $this->expectException(RefundExecutionPending::class);
        $this->refunds()->execute($refund);
    }

    private function succeededTransaction(string $key)
    {
        $service = $this->transactions();
        $transaction = $service->createPending(new FakePayable(), 'fake', $key);

        return $service->transition($transaction, PaymentStatus::SUCCEEDED);
    }

    private function transactions(): PaymentTransactionService
    {
        return $this->app->make(PaymentTransactionService::class);
    }

    private function refunds(): RefundService
    {
        return $this->app->make(RefundService::class);
    }
}
