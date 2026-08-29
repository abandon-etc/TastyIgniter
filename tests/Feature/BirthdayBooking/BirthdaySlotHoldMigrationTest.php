<?php

declare(strict_types=1);

namespace Tests\Feature\BirthdayBooking;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class BirthdaySlotHoldMigrationTest extends TestCase
{
    public function test_additive_hold_table_has_required_columns_indexes_and_foreign_keys(): void
    {
        $this->assertTrue(Schema::hasTable('birthday_slot_holds'));
        $this->assertTrue(Schema::hasColumns('birthday_slot_holds', [
            'birthday_slot_hold_id',
            'public_id',
            'birthday_booking_id',
            'location_id',
            'event_date',
            'slot_code',
            'status',
            'acquired_at',
            'expires_at',
            'released_at',
            'expired_at',
            'release_reason',
            'created_at',
            'updated_at',
        ]));

        $indexes = collect(Schema::getIndexes('birthday_slot_holds'))->keyBy('name');
        $this->assertTrue($indexes->get('birthday_slot_holds_public_id_unique')['unique']);
        $this->assertTrue($indexes->get('birthday_slot_holds_slot_unique')['unique']);
        $this->assertSame(
            ['location_id', 'event_date', 'slot_code'],
            $indexes->get('birthday_slot_holds_slot_unique')['columns'],
        );
        $this->assertTrue($indexes->get('birthday_slot_holds_booking_unique')['unique']);
        $this->assertArrayHasKey('birthday_slot_holds_status_index', $indexes->all());
        $this->assertArrayHasKey('birthday_slot_holds_expires_index', $indexes->all());

        // Schema::getForeignKeys reports physical table names, which carry
        // the connection's table prefix — unlike Schema::hasTable above,
        // which takes the logical name. First exposed when this test first
        // ran against a real database (CI, 2026-08-29).
        $prefix = Schema::getConnection()->getTablePrefix();
        $foreignKeys = collect(Schema::getForeignKeys('birthday_slot_holds'))->keyBy('name');
        $this->assertSame($prefix.'birthday_bookings', $foreignKeys->get('birthday_slot_holds_booking_foreign')['foreign_table']);
        $this->assertSame($prefix.'locations', $foreignKeys->get('birthday_slot_holds_location_foreign')['foreign_table']);
        $this->assertSame('restrict', strtolower($foreignKeys->get('birthday_slot_holds_booking_foreign')['on_delete']));
        $this->assertSame('restrict', strtolower($foreignKeys->get('birthday_slot_holds_location_foreign')['on_delete']));
    }

    public function test_migration_only_creates_and_drops_the_hold_table(): void
    {
        $migration = file_get_contents(base_path(
            'vendor/abandon-etc/ti-ext-birthday/database/migrations/2026_07_18_000000_create_birthday_slot_holds_table.php',
        ));

        $this->assertSame(1, substr_count($migration, 'Schema::create'));
        $this->assertSame(1, substr_count($migration, 'Schema::dropIfExists'));
        $this->assertStringContainsString("Schema::create('birthday_slot_holds'", $migration);
        $this->assertStringContainsString("Schema::dropIfExists('birthday_slot_holds')", $migration);
        $this->assertStringNotContainsString('->enum(', $migration);

        foreach (['birthday_bookings', 'reservations', 'orders', 'payments', 'payment_logs'] as $existingTable) {
            $this->assertStringNotContainsString("Schema::table('{$existingTable}'", $migration);
        }
    }
}
