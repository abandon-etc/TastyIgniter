<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Payments\EventProcessingStatus;
use App\Payments\Models\PaymentEvent;
use App\Payments\PaymentEventLedger;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

final class PaymentEventLedgerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_a_provider_event_is_recorded_once(): void
    {
        $ledger = $this->ledger();

        $event = $ledger->recordOnce('fake', 'evt_1', 'payment.succeeded', true, null, ['amount_minor' => 31500]);

        $this->assertTrue($event->wasRecentlyCreated);
        $this->assertSame(EventProcessingStatus::RECEIVED, $event->processing_status);
        $this->assertSame(0, $event->attempts);
        $this->assertTrue($event->signature_valid);
    }

    public function test_a_duplicate_delivery_is_a_no_op_returning_the_original_untouched(): void
    {
        $ledger = $this->ledger();

        $original = $ledger->recordOnce('fake', 'evt_dup', 'payment.succeeded', true);
        $marked = $ledger->markProcessed($original);
        $this->assertSame(1, $marked->attempts);

        $duplicate = $ledger->recordOnce('fake', 'evt_dup', 'payment.succeeded', true);

        $this->assertFalse($duplicate->wasRecentlyCreated);
        $this->assertTrue($duplicate->is($original));
        $this->assertSame(EventProcessingStatus::PROCESSED, $duplicate->processing_status);
        $this->assertSame(1, $duplicate->attempts);
        $this->assertSame(1, PaymentEvent::query()->where('external_event_id', 'evt_dup')->count());
    }

    public function test_the_same_event_id_from_another_gateway_is_a_different_event(): void
    {
        $ledger = $this->ledger();

        $a = $ledger->recordOnce('fake', 'evt_shared', 'payment.succeeded', true);
        $b = $ledger->recordOnce('other', 'evt_shared', 'payment.succeeded', true);

        $this->assertTrue($b->wasRecentlyCreated);
        $this->assertFalse($b->is($a));
    }

    public function test_processing_marks_transition_status_attempts_and_error_category(): void
    {
        $ledger = $this->ledger();

        $skipped = $ledger->markSkipped($ledger->recordOnce('fake', 'evt_skip', 'noise.event', true), 'event_type_not_handled');
        $this->assertSame(EventProcessingStatus::SKIPPED, $skipped->processing_status);
        $this->assertSame('event_type_not_handled', $skipped->safe_error);
        $this->assertNotNull($skipped->processed_at);

        $failed = $ledger->markFailed($ledger->recordOnce('fake', 'evt_fail', 'payment.succeeded', true), 'transaction_not_found');
        $this->assertSame(EventProcessingStatus::FAILED, $failed->processing_status);
        $this->assertSame('transaction_not_found', $failed->safe_error);
        $this->assertSame(1, $failed->attempts);
    }

    private function ledger(): PaymentEventLedger
    {
        return $this->app->make(PaymentEventLedger::class);
    }
}
