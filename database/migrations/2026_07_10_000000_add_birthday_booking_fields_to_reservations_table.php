<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->boolean('birthday_booking')->default(false)->after('reserve_time');
            $table->string('birthday_slot_code', 32)->nullable()->after('birthday_booking');
            $table->string('birthday_slot_key', 128)->nullable()->after('birthday_slot_code');
            $table->unique(['location_id', 'birthday_slot_key'], 'birthday_reservation_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropUnique('birthday_reservation_slot_unique');
            $table->dropColumn(['birthday_booking', 'birthday_slot_code', 'birthday_slot_key']);
        });
    }
};
