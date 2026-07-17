<?php

declare(strict_types=1);

namespace Tests\Feature\BirthdayBooking;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class BirthdayCatalogMigrationTest extends TestCase
{
    public function test_additive_catalog_tables_columns_and_indexes_exist(): void
    {
        $this->assertTrue(Schema::hasTable('birthday_packages'));
        $this->assertTrue(Schema::hasTable('birthday_addons'));
        $this->assertTrue(Schema::hasColumns('birthday_packages', [
            'birthday_package_id',
            'name',
            'included_items',
            'price_minor',
            'currency',
            'is_default',
            'is_enabled',
            'sort_order',
            'archived_at',
            'default_guard',
        ]));
        $this->assertTrue(Schema::hasColumns('birthday_addons', [
            'birthday_addon_id',
            'name',
            'price_minor',
            'currency',
            'is_enabled',
            'sort_order',
            'archived_at',
        ]));

        $packageIndexes = collect(Schema::getIndexes('birthday_packages'))->pluck('name');
        $addonIndexes = collect(Schema::getIndexes('birthday_addons'))->pluck('name');

        $this->assertContains('birthday_packages_available_index', $packageIndexes);
        $this->assertContains('birthday_packages_default_unique', $packageIndexes);
        $this->assertContains('birthday_addons_available_index', $addonIndexes);
    }

    public function test_migration_down_is_limited_to_the_two_additive_catalog_tables(): void
    {
        $migration = file_get_contents(base_path(
            'vendor/abandon-etc/ti-ext-birthday/database/migrations/2026_07_15_000000_create_birthday_catalog_tables.php',
        ));

        $this->assertSame(2, substr_count($migration, 'Schema::dropIfExists'));
        $this->assertStringContainsString("Schema::dropIfExists('birthday_addons')", $migration);
        $this->assertStringContainsString("Schema::dropIfExists('birthday_packages')", $migration);
        foreach (['reservations', 'orders', 'payments', 'payment_logs', 'customers'] as $existingTable) {
            $this->assertStringNotContainsString("Schema::table('{$existingTable}'", $migration);
        }
    }
}
