<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_slot_holds', function (Blueprint $table): void {
            $table->bigIncrements('birthday_slot_hold_id');
            $table->uuid('public_id')->unique('birthday_slot_holds_public_id_unique');
            $table->unsignedBigInteger('birthday_booking_id');
            $table->unsignedBigInteger('location_id');
            $table->date('event_date');
            $table->string('slot_code', 32);
            $table->string('status', 16);
            $table->dateTime('acquired_at');
            $table->dateTime('expires_at');
            $table->dateTime('released_at')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->string('release_reason', 64)->nullable();
            $table->timestamps();

            $table->foreign('birthday_booking_id', 'birthday_slot_holds_booking_foreign')
                ->references('birthday_booking_id')->on('birthday_bookings')->restrictOnDelete();
            $table->foreign('location_id', 'birthday_slot_holds_location_foreign')
                ->references('location_id')->on('locations')->restrictOnDelete();
            $table->unique(
                ['location_id', 'event_date', 'slot_code'],
                'birthday_slot_holds_slot_unique',
            );
            $table->unique('birthday_booking_id', 'birthday_slot_holds_booking_unique');
            $table->index('status', 'birthday_slot_holds_status_index');
            $table->index('expires_at', 'birthday_slot_holds_expires_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_slot_holds');
    }
};
