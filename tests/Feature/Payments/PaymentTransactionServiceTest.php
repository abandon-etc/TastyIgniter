<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Payments\Contracts\Payable;
use App\Payments\Exceptions\InvalidPaymentTransition;
use App\Payments\Models\PaymentTransaction;
use App\Payments\PaymentStatus;
use App\Payments\PaymentTransactionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use LogicException;
use Tests\TestCase;

final class PaymentTransactionServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_pending_snapshots_the_payable_in_integer_minor_units(): void
    {
        $transaction = $this->service()->createPending($this->payable(), 'fake', 'key-create-1');

        $this->assertSame(PaymentStatus::PENDING, $transaction->status);
        $this->assertSame('birthday_bookings', $transaction->payable_type);
        $this->assertSame(31500, $transaction->amount_minor);
        $this->assertSame('CAD', $transaction->currency);
        $this->assertSame(0, $transaction->refunded_amount_minor);
        $this->assertNotEmpty($transaction->public_id);
        $this->assertTrue($transaction->wasRecentlyCreated);
    }

    public function test_the_same_idempotency_key_returns_the_same_transaction(): void
    {
        $first = $this->service()->createPending($this->payable(), 'fake', 'key-idem-1');
        $second = $this->service()->createPending($this->payable(), 'fake', 'key-idem-1');

        $this->assertTrue($first->is($second));
        $this->assertFalse($second->wasRecentlyCreated);
        $this->assertSame(1, PaymentTransaction::query()->where('idempotency_key', 'key-idem-1')->count());
    }

    public function test_a_key_reused_for_a_different_operation_fails_loudly(): void
    {
        $this->service()->createPending($this->payable(), 'fake', 'key-reuse-1');

        $this->expectException(ValidationException::class);
        $this->service()->createPending($this->payable(amountMinor: 99), 'fake', 'key-reuse-1');
    }

    public function test_transitions_follow_the_state_machine_and_stamp_times(): void
    {
        $service = $this->service();
        $transaction = $service->createPending($this->payable(), 'fake', 'key-flow-1');

        $transaction = $service->transition($transaction, PaymentStatus::AUTHORIZED);
        $this->assertSame(PaymentStatus::AUTHORIZED, $transaction->status);
        $this->assertNotNull($transaction->authorized_at);

        $transaction = $service->transition($transaction, PaymentStatus::SUCCEEDED);
        $this->assertSame(PaymentStatus::SUCCEEDED, $transaction->status);
        $this->assertNotNull($transaction->succeeded_at);
    }

    public function test_an_illegal_transition_is_refused(): void
    {
        $service = $this->service();
        $transaction = $service->createPending($this->payable(), 'fake', 'key-illegal-1');
        $transaction = $service->transition($transaction, PaymentStatus::FAILED);

        $this->expectException(InvalidPaymentTransition::class);
        $service->transition($transaction, PaymentStatus::SUCCEEDED);
    }

    public function test_ledger_rows_refuse_updates_outside_the_services_and_all_deletes(): void
    {
        $transaction = $this->service()->createPending($this->payable(), 'fake', 'key-guard-1');

        try {
            $transaction->status = PaymentStatus::SUCCEEDED;
            $transaction->save();
            $this->fail('A direct model update must be refused.');
        } catch (LogicException $e) {
            $this->assertStringContainsString('payment services', $e->getMessage());
        }

        $this->expectException(LogicException::class);
        $transaction->fresh()->delete();
    }

    public function test_cumulative_refunded_amount_cannot_exceed_the_transaction_amount(): void
    {
        $service = $this->service();
        $transaction = $service->createPending($this->payable(), 'fake', 'key-refunded-1');
        $transaction = $service->transition($transaction, PaymentStatus::SUCCEEDED);

        $transaction = $service->addRefundedAmount($transaction, 30000);
        $this->assertSame(30000, $transaction->refunded_amount_minor);

        $this->expectException(ValidationException::class);
        $service->addRefundedAmount($transaction, 1501);
    }

    private function service(): PaymentTransactionService
    {
        return $this->app->make(PaymentTransactionService::class);
    }

    private function payable(int $amountMinor = 31500): Payable
    {
        return new class($amountMinor) implements Payable
        {
            public function __construct(private readonly int $amountMinor)
            {
            }

            public function getPayableType(): string
            {
                return 'birthday_bookings';
            }

            public function getPayableId(): int
            {
                return 424242;
            }

            public function getAmountMinor(): int
            {
                return $this->amountMinor;
            }

            public function getCurrency(): string
            {
                return 'cad';
            }

            public function getPaymentDescription(): string
            {
                return 'Test booking';
            }
        };
    }
}
