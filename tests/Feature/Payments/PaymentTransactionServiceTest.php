<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Payments\Exceptions\InvalidPaymentTransition;
use App\Payments\Models\PaymentTransaction;
use App\Payments\PaymentStatus;
use App\Payments\PaymentTransactionService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use LogicException;
use Tests\TestCase;

final class PaymentTransactionServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_pending_snapshots_the_payable_in_integer_minor_units(): void
    {
        $transaction = $this->service()->createPending(new FakePayable(currency: 'cad'), 'fake', 'key-create-1');

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
        $first = $this->service()->createPending(new FakePayable(), 'fake', 'key-idem-1');
        $second = $this->service()->createPending(new FakePayable(), 'fake', 'key-idem-1');

        $this->assertTrue($first->is($second));
        $this->assertFalse($second->wasRecentlyCreated);
        $this->assertSame(1, PaymentTransaction::query()->where('idempotency_key', 'key-idem-1')->count());
    }

    public function test_a_key_reused_for_a_different_operation_fails_loudly(): void
    {
        $this->service()->createPending(new FakePayable(), 'fake', 'key-reuse-1');

        $this->expectException(ValidationException::class);
        $this->service()->createPending(new FakePayable(amountMinor: 99), 'fake', 'key-reuse-1');
    }

    public function test_a_key_resolving_to_a_terminal_transaction_fails_loudly(): void
    {
        $service = $this->service();
        $transaction = $service->createPending(new FakePayable(), 'fake', 'key-term-1');
        $service->transition($transaction, PaymentStatus::FAILED);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/failed transaction; a new attempt needs a new key/');
        $service->createPending(new FakePayable(), 'fake', 'key-term-1');
    }

    public function test_transitions_follow_the_state_machine_and_stamp_times(): void
    {
        $service = $this->service();
        $transaction = $service->createPending(new FakePayable(), 'fake', 'key-flow-1');

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
        $transaction = $service->createPending(new FakePayable(), 'fake', 'key-illegal-1');
        $transaction = $service->transition($transaction, PaymentStatus::FAILED);

        $this->expectException(InvalidPaymentTransition::class);
        $service->transition($transaction, PaymentStatus::SUCCEEDED);
    }

    public function test_refund_pending_with_recorded_money_cannot_return_to_plain_succeeded(): void
    {
        $service = $this->service();
        $transaction = $service->createPending(new FakePayable(), 'fake', 'key-partial-1');
        $transaction = $service->transition($transaction, PaymentStatus::SUCCEEDED);
        $transaction = $service->transition($transaction, PaymentStatus::REFUND_PENDING);
        $transaction = $service->addRefundedAmount($transaction, 10000);

        try {
            $service->transition($transaction, PaymentStatus::SUCCEEDED);
            $this->fail('succeeded must be refused while refund money is recorded.');
        } catch (InvalidPaymentTransition $e) {
            $this->assertStringContainsString('partially_refunded', $e->getMessage());
        }

        $transaction = $service->transition($transaction, PaymentStatus::PARTIALLY_REFUNDED);
        $this->assertSame(PaymentStatus::PARTIALLY_REFUNDED, $transaction->status);
    }

    public function test_ledger_rows_refuse_updates_outside_the_services_and_all_deletes(): void
    {
        $transaction = $this->service()->createPending(new FakePayable(), 'fake', 'key-guard-1');

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

    public function test_attach_external_reference_is_idempotent_and_conflicts_fail_loudly(): void
    {
        $service = $this->service();
        $transaction = $service->createPending(new FakePayable(), 'fake', 'key-ref-1');

        $transaction = $service->attachExternalReference($transaction, 'pi_123');
        $this->assertSame('pi_123', $transaction->external_payment_id);

        $again = $service->attachExternalReference($transaction, 'pi_123');
        $this->assertTrue($again->is($transaction));

        try {
            $service->attachExternalReference($transaction, 'pi_456');
            $this->fail('A different reference on a referenced transaction must be refused.');
        } catch (ValidationException) {
        }

        $other = $service->createPending(new FakePayable(payableId: 424243), 'fake', 'key-ref-2');
        $this->expectException(ValidationException::class);
        $service->attachExternalReference($other, 'pi_123');
    }

    public function test_refunded_money_is_recorded_only_against_refundable_states(): void
    {
        $service = $this->service();
        $pending = $service->createPending(new FakePayable(), 'fake', 'key-refstate-1');

        $this->expectException(ValidationException::class);
        $service->addRefundedAmount($pending, 100);
    }

    public function test_cumulative_refunded_amount_cannot_exceed_the_transaction_amount(): void
    {
        $service = $this->service();
        $transaction = $service->createPending(new FakePayable(), 'fake', 'key-refunded-1');
        $transaction = $service->transition($transaction, PaymentStatus::SUCCEEDED);

        $transaction = $service->addRefundedAmount($transaction, 30000);
        $this->assertSame(30000, $transaction->refunded_amount_minor);

        $this->expectException(ValidationException::class);
        $service->addRefundedAmount($transaction, 1501);
    }

    public function test_every_stored_instant_is_utc_regardless_of_the_default_timezone(): void
    {
        $previous = date_default_timezone_get();
        date_default_timezone_set('America/Toronto');

        try {
            $transaction = $this->service()->createPending(new FakePayable(), 'fake', 'key-utc-1');
            $transaction = $this->service()->transition($transaction, PaymentStatus::SUCCEEDED);

            $raw = $transaction->getAttributes();
            $nowUtc = CarbonImmutable::now('UTC');

            foreach (['created_at', 'updated_at', 'succeeded_at'] as $column) {
                $stored = CarbonImmutable::createFromFormat('!Y-m-d H:i:s', (string) $raw[$column], 'UTC');
                $this->assertLessThan(60, abs($nowUtc->diffInSeconds($stored, true)),
                    "$column must be stored as UTC; got {$raw[$column]} against UTC now {$nowUtc}");
            }
        } finally {
            date_default_timezone_set($previous);
        }
    }

    private function service(): PaymentTransactionService
    {
        return $this->app->make(PaymentTransactionService::class);
    }
}
