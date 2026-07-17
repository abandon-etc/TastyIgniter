<?php

declare(strict_types=1);

namespace Tests\Feature\BirthdayBooking;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class BirthdayBookingMigrationTest extends TestCase
{
    public function test_additive_booking_tables_columns_and_indexes_exist(): void
    {
        $this->assertTrue(Schema::hasTable('birthday_bookings'));
        $this->assertTrue(Schema::hasTable('birthday_booking_addons'));
        $this->assertTrue(Schema::hasColumns('birthday_bookings', [
            'birthday_booking_id',
            'public_id',
            'customer_id',
            'location_id',
            'event_date',
            'slot_code',
            'starts_at',
            'ends_at',
            'timezone',
            'guest_count',
            'status',
            'currency',
            'package_id',
            'package_name_snapshot',
            'package_description_snapshot',
            'package_included_items_snapshot',
            'package_price_minor_snapshot',
            'addons_subtotal_minor',
            'catalog_subtotal_minor',
            'contact_first_name_snapshot',
            'contact_last_name_snapshot',
            'contact_email_snapshot',
            'contact_telephone_snapshot',
            'pricing_version',
            'priced_at',
            'cancelled_at',
        ]));
        $this->assertTrue(Schema::hasColumns('birthday_booking_addons', [
            'birthday_booking_addon_id',
            'birthday_booking_id',
            'addon_id',
            'addon_name_snapshot',
            'addon_description_snapshot',
            'addon_price_minor_snapshot',
            'sort_order_snapshot',
        ]));
        $this->assertFalse(Schema::hasColumn('birthday_booking_addons', 'quantity'));

        $bookingIndexes = collect(Schema::getIndexes('birthday_bookings'))->keyBy('name');
        $addonIndexes = collect(Schema::getIndexes('birthday_booking_addons'))->keyBy('name');

        $this->assertTrue($bookingIndexes->get('birthday_bookings_public_id_unique')['unique']);
        $this->assertFalse($bookingIndexes->get('birthday_bookings_slot_lookup_index')['unique']);
        $this->assertTrue($addonIndexes->get('birthday_booking_addons_source_unique')['unique']);
        $this->assertArrayHasKey('birthday_bookings_customer_index', $bookingIndexes->all());
        $this->assertArrayHasKey('birthday_bookings_status_index', $bookingIndexes->all());
        $this->assertArrayHasKey('birthday_bookings_priced_at_index', $bookingIndexes->all());
        $this->assertArrayHasKey('birthday_booking_addons_booking_index', $addonIndexes->all());
    }

    public function test_migration_is_limited_to_two_new_tables_and_has_a_reversible_down_path(): void
    {
        $migration = file_get_contents(base_path(
            'vendor/abandon-etc/ti-ext-birthday/database/migrations/2026_07_17_000000_create_birthday_booking_tables.php',
        ));

        $this->assertSame(2, substr_count($migration, 'Schema::create'));
        $this->assertSame(2, substr_count($migration, 'Schema::dropIfExists'));
        $this->assertStringContainsString("Schema::dropIfExists('birthday_booking_addons')", $migration);
        $this->assertStringContainsString("Schema::dropIfExists('birthday_bookings')", $migration);
        $this->assertStringNotContainsString('->enum(', $migration);
        $this->assertStringNotContainsString("unique(['location_id', 'event_date', 'slot_code']", $migration);

        foreach ([
            'reservations',
            'orders',
            'payments',
            'payment_logs',
            'customers',
            'locations',
            'birthday_packages',
            'birthday_addons',
        ] as $existingTable) {
            $this->assertStringNotContainsString("Schema::table('{$existingTable}'", $migration);
        }
    }
}
