<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class PaymentLedgerMigrationTest extends TestCase
{
    public function test_transactions_table_has_required_columns_and_constraints(): void
    {
        $this->assertTrue(Schema::hasTable('payment_transactions'));
        $this->assertTrue(Schema::hasColumns('payment_transactions', [
            'payment_transaction_id', 'public_id', 'payable_type', 'payable_id',
            'gateway_code', 'external_payment_id', 'idempotency_key',
            'amount_minor', 'currency', 'status',
            'authorized_at', 'succeeded_at', 'failed_at', 'cancelled_at', 'refunded_at',
            'refunded_amount_minor', 'safe_metadata', 'created_at', 'updated_at',
        ]));

        $indexes = collect(Schema::getIndexes('payment_transactions'))->keyBy('name');
        $this->assertTrue($indexes->get('payment_transactions_idempotency_unique')['unique']);
        $this->assertTrue($indexes->get('payment_transactions_external_unique')['unique']);
        $this->assertSame(
            ['gateway_code', 'external_payment_id'],
            $indexes->get('payment_transactions_external_unique')['columns'],
        );
        $this->assertSame(
            ['payable_type', 'payable_id'],
            $indexes->get('payment_transactions_payable_index')['columns'],
        );
    }

    public function test_event_and_refund_tables_reference_transactions_with_provider_scoped_uniqueness(): void
    {
        $this->assertTrue(Schema::hasTable('payment_events'));
        $this->assertTrue(Schema::hasTable('payment_refunds'));

        // Schema::getForeignKeys reports physical table names, which carry
        // the connection prefix (the lesson of PR #126).
        $prefix = Schema::getConnection()->getTablePrefix();

        $eventForeigns = collect(Schema::getForeignKeys('payment_events'))->keyBy('name');
        $this->assertSame(
            $prefix.'payment_transactions',
            $eventForeigns->get('payment_events_transaction_foreign')['foreign_table'],
        );
        $this->assertSame('restrict', strtolower($eventForeigns->get('payment_events_transaction_foreign')['on_delete']));

        $refundForeigns = collect(Schema::getForeignKeys('payment_refunds'))->keyBy('name');
        $this->assertSame(
            $prefix.'payment_transactions',
            $refundForeigns->get('payment_refunds_transaction_foreign')['foreign_table'],
        );
        $this->assertSame('restrict', strtolower($refundForeigns->get('payment_refunds_transaction_foreign')['on_delete']));

        $eventIndexes = collect(Schema::getIndexes('payment_events'))->keyBy('name');
        $this->assertTrue($eventIndexes->get('payment_events_provider_event_unique')['unique']);
        $this->assertSame(
            ['gateway_code', 'external_event_id'],
            $eventIndexes->get('payment_events_provider_event_unique')['columns'],
        );

        $refundIndexes = collect(Schema::getIndexes('payment_refunds'))->keyBy('name');
        $this->assertTrue($refundIndexes->get('payment_refunds_provider_refund_unique')['unique']);
    }

    public function test_migration_only_creates_the_three_ledger_tables(): void
    {
        $migration = file_get_contents(base_path(
            'database/migrations/2026_08_29_000000_create_payment_ledger_tables.php',
        ));

        $this->assertSame(3, substr_count($migration, 'Schema::create'));
        $this->assertSame(3, substr_count($migration, 'Schema::dropIfExists'));

        // Self-contained by design: safe in the root migration pass, which
        // runs before every extension (PR #124). The only ->on() targets
        // are payment_transactions itself.
        $this->assertSame(2, substr_count($migration, "->on('payment_transactions')"));
        $this->assertSame(2, substr_count($migration, '->on('));

        foreach (['reservations', 'orders', 'customers', 'locations', 'birthday_bookings'] as $foreignTable) {
            $this->assertStringNotContainsString("Schema::table('{$foreignTable}'", $migration);
            $this->assertStringNotContainsString("->on('{$foreignTable}')", $migration);
        }
    }
}
