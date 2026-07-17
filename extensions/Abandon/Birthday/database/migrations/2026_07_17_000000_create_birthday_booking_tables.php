<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_bookings', function (Blueprint $table): void {
            $table->bigIncrements('birthday_booking_id');
            $table->uuid('public_id')->unique('birthday_bookings_public_id_unique');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('location_id');
            $table->date('event_date');
            $table->string('slot_code', 32);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('timezone', 64);
            $table->unsignedSmallInteger('guest_count');
            $table->string('status', 32);
            $table->char('currency', 3)->default('CAD');
            $table->unsignedBigInteger('package_id');
            $table->string('package_name_snapshot');
            $table->text('package_description_snapshot')->nullable();
            $table->json('package_included_items_snapshot')->nullable();
            $table->unsignedBigInteger('package_price_minor_snapshot');
            $table->unsignedBigInteger('addons_subtotal_minor')->default(0);
            $table->unsignedBigInteger('catalog_subtotal_minor');
            $table->string('contact_first_name_snapshot');
            $table->string('contact_last_name_snapshot');
            $table->string('contact_email_snapshot');
            $table->string('contact_telephone_snapshot', 32)->nullable();
            $table->unsignedSmallInteger('pricing_version')->default(1);
            $table->timestamp('priced_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->foreign('customer_id', 'birthday_bookings_customer_foreign')
                ->references('customer_id')->on('customers')->restrictOnDelete();
            $table->foreign('location_id', 'birthday_bookings_location_foreign')
                ->references('location_id')->on('locations')->restrictOnDelete();
            $table->foreign('package_id', 'birthday_bookings_package_foreign')
                ->references('birthday_package_id')->on('birthday_packages')->restrictOnDelete();
            $table->index('customer_id', 'birthday_bookings_customer_index');
            $table->index(['location_id', 'event_date', 'slot_code'], 'birthday_bookings_slot_lookup_index');
            $table->index('status', 'birthday_bookings_status_index');
            $table->index('priced_at', 'birthday_bookings_priced_at_index');
        });

        Schema::create('birthday_booking_addons', function (Blueprint $table): void {
            $table->bigIncrements('birthday_booking_addon_id');
            $table->unsignedBigInteger('birthday_booking_id');
            $table->unsignedBigInteger('addon_id');
            $table->string('addon_name_snapshot');
            $table->text('addon_description_snapshot')->nullable();
            $table->unsignedBigInteger('addon_price_minor_snapshot');
            $table->unsignedInteger('sort_order_snapshot')->default(0);
            $table->timestamps();

            $table->foreign('birthday_booking_id', 'birthday_booking_addons_booking_foreign')
                ->references('birthday_booking_id')->on('birthday_bookings')->restrictOnDelete();
            $table->foreign('addon_id', 'birthday_booking_addons_addon_foreign')
                ->references('birthday_addon_id')->on('birthday_addons')->restrictOnDelete();
            $table->index('birthday_booking_id', 'birthday_booking_addons_booking_index');
            $table->unique(
                ['birthday_booking_id', 'addon_id'],
                'birthday_booking_addons_source_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_booking_addons');
        Schema::dropIfExists('birthday_bookings');
    }
};
