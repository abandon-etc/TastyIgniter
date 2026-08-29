<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Moved here from database/migrations on 2026-08-28: it alters the
// Reservation extension's table, so it must run in the extension phase,
// after igniter.reservation has created ti_reservations — the root
// migration pass runs before every extension and cannot be ordered after
// one. Guarded so the already-initialized databases, whose ledger carries
// this migration under the root group, re-run it here as a no-op.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reservations')) {
            // A silent skip would be recorded as migrated and the columns
            // would never exist. Fail loudly instead: this fires only when
            // the group ordering has regressed to running before
            // igniter.reservation. See App\BirthdayBooking\BirthdayMigrationOrder.
            throw new RuntimeException(
                'ti_reservations does not exist yet: the abandon.birthday migration '
                .'group must run after the igniter extensions (BirthdayMigrationOrder).',
            );
        }

        if (Schema::hasColumn('reservations', 'birthday_booking')) {
            return; // Already applied by the original root-pass run.
        }

        Schema::table('reservations', function (Blueprint $table): void {
            $table->boolean('birthday_booking')->default(false)->after('reserve_time');
            $table->string('birthday_slot_code', 32)->nullable()->after('birthday_booking');
            $table->string('birthday_slot_key', 128)->nullable()->after('birthday_slot_code');
            $table->unique(['location_id', 'birthday_slot_key'], 'birthday_reservation_slot_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('reservations') || !Schema::hasColumn('reservations', 'birthday_booking')) {
            return;
        }

        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropUnique('birthday_reservation_slot_unique');
            $table->dropColumn(['birthday_booking', 'birthday_slot_code', 'birthday_slot_key']);
        });
    }
};
