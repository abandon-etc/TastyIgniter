<?php

declare(strict_types=1);

namespace Tests\Feature\BirthdayBooking;

use App\BirthdayBooking\BirthdayMigrationOrder;
use Igniter\Flame\Support\Facades\Igniter;
use Tests\TestCase;

/**
 * Pins the fresh-install migration-ordering fix: the abandon.birthday
 * migration group must run after every vendor extension group, because its
 * migrations reference igniter-owned tables (reservations, customers,
 * locations) and the installed core has no dependency ordering. If a
 * vendor upgrade changes the internals BirthdayMigrationOrder relies on,
 * these tests are the loud failure.
 */
final class BirthdayMigrationOrderTest extends TestCase
{
    public function test_the_booted_application_puts_the_birthday_group_last(): void
    {
        $paths = Igniter::migrationPath();

        $this->assertArrayHasKey(BirthdayMigrationOrder::GROUP, $paths,
            'The abandon.birthday extension is not registered; the ordering fix has nothing to order.');
        $this->assertSame(BirthdayMigrationOrder::GROUP, array_key_last($paths),
            'The abandon.birthday migration group must be last so it runs after the igniter extensions.');
    }

    public function test_apply_is_idempotent_and_keeps_the_registered_path(): void
    {
        $before = Igniter::migrationPath();

        BirthdayMigrationOrder::apply();
        BirthdayMigrationOrder::apply();

        $after = Igniter::migrationPath();

        $this->assertSame($before, $after);
        $this->assertSame($before[BirthdayMigrationOrder::GROUP], $after[BirthdayMigrationOrder::GROUP]);
    }

    public function test_the_reservations_migration_lives_in_the_extension_not_the_root(): void
    {
        $file = '2026_07_10_000000_add_birthday_booking_fields_to_reservations_table.php';

        $this->assertFileExists(base_path('extensions/Abandon/Birthday/database/migrations/'.$file),
            'The reservations alteration must migrate in the extension phase.');
        $this->assertFileDoesNotExist(base_path('database/migrations/'.$file),
            'The root migration pass runs before every extension and must not touch extension tables.');
    }
}
