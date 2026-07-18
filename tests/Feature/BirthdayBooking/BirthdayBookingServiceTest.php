<?php

declare(strict_types=1);

namespace Tests\Feature\BirthdayBooking;

use Abandon\Birthday\Models\BirthdayAddon;
use Abandon\Birthday\Models\BirthdayBooking;
use Abandon\Birthday\Models\BirthdayBookingAddon;
use Abandon\Birthday\Models\BirthdayBookingStatus;
use Abandon\Birthday\Models\BirthdayPackage;
use Abandon\Birthday\Models\PriceValue;
use Abandon\Birthday\Services\BirthdayAddonService;
use Abandon\Birthday\Services\BirthdayBookingService;
use Abandon\Birthday\Services\BirthdayPackageService;
use Abandon\Birthday\Services\BirthdayPricingSnapshot;
use Abandon\Birthday\Services\BirthdayPricingSnapshotService;
use App\BirthdayBooking\BirthdayRules;
use App\BirthdayBooking\BirthdayTelephone;
use Carbon\CarbonImmutable;
use Igniter\Local\Models\Location;
use Igniter\User\Models\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Tests\TestCase;

final class BirthdayBookingServiceTest extends TestCase
{
    use DatabaseTransactions;

    private CarbonImmutable $now;

    private Customer $customer;

    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('birthday_booking.timezone', 'America/Toronto');
        $this->now = CarbonImmutable::create(2026, 7, 10, 12, 0, 0, 'America/Toronto');
        $this->customer = Customer::factory()->create([
            'first_name' => 'Staging',
            'last_name' => 'Customer',
            'email' => 'booking-test@example.invalid',
            'password' => 'test-only-password',
            'telephone' => '5145550100',
            'is_activated' => true,
            'status' => true,
        ]);
        $this->location = Location::factory()->create([
            'location_name' => 'Birthday Test Location',
            'location_status' => true,
        ]);
    }

    public function test_it_creates_a_catalog_priced_booking_with_package_and_contact_snapshots(): void
    {
        $package = $this->defaultPackage('Classic', '250.00');

        $booking = $this->createBooking();

        $this->assertSame(BirthdayBookingStatus::CATALOG_PRICED, $booking->status);
        $this->assertSame($this->customer->getKey(), $booking->customer_id);
        $this->assertSame($this->location->getKey(), $booking->location_id);
        $this->assertSame('2026-07-12', $booking->event_date->format('Y-m-d'));
        $this->assertSame('12-16', $booking->slot_code);
        $this->assertSame('America/Toronto', $booking->timezone);
        $this->assertSame('2026-07-12 16:00:00', $booking->starts_at->setTimezone('UTC')->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-12 20:00:00', $booking->ends_at->setTimezone('UTC')->format('Y-m-d H:i:s'));
        $this->assertSame(12, $booking->guest_count);
        $this->assertSame('CAD', $booking->currency);
        $this->assertSame($package->getKey(), $booking->package_id);
        $this->assertSame('Classic', $booking->package_name_snapshot);
        $this->assertSame('Private venue', $booking->package_description_snapshot);
        $this->assertSame(['Room', 'Tables'], $booking->package_included_items_snapshot);
        $this->assertSame(25000, $booking->package_price_minor_snapshot);
        $this->assertSame(0, $booking->addons_subtotal_minor);
        $this->assertSame(25000, $booking->catalog_subtotal_minor);
        $this->assertSame(1, $booking->pricing_version);
        $this->assertSame('Staging', $booking->contact_first_name_snapshot);
        $this->assertSame('Customer', $booking->contact_last_name_snapshot);
        $this->assertSame('booking-test@example.invalid', $booking->contact_email_snapshot);
        $this->assertSame('+15145550100', $booking->contact_telephone_snapshot);
        $this->assertNotEmpty($booking->public_id);
        $this->assertSame(36, strlen($booking->public_id));
        $this->assertCount(0, $booking->addons);
    }

    public function test_it_snapshots_addons_in_stable_order_and_uses_integer_subtotals(): void
    {
        $this->defaultPackage('Classic', '250.00');
        $late = $this->addon('Late', '20.25', 20);
        $first = $this->addon('First', '15.50', 10);

        $booking = $this->createBooking([$late->getKey(), $first->getKey()]);

        $this->assertSame(['First', 'Late'], $booking->addons->pluck('addon_name_snapshot')->all());
        $this->assertSame([1550, 2025], $booking->addons->pluck('addon_price_minor_snapshot')->all());
        $this->assertSame(3575, $booking->addons_subtotal_minor);
        $this->assertSame(28575, $booking->catalog_subtotal_minor);
        $this->assertSame('$250.00', $booking->package_price_display);
        $this->assertSame('$35.75', $booking->addons_subtotal_display);
        $this->assertSame('$285.75', $booking->catalog_subtotal_display);
        $this->assertArrayNotHasKey('quantity', $booking->addons->first()->getAttributes());
    }

    public function test_catalog_and_customer_changes_do_not_change_historical_snapshots(): void
    {
        $package = $this->defaultPackage('Original Package', '250.00');
        $addon = $this->addon('Original Add-on', '15.00', 10);
        $booking = $this->createBooking([$addon->getKey()]);

        DB::table('birthday_packages')->where('birthday_package_id', $package->getKey())->update([
            'name' => 'Changed Package',
            'price_minor' => 99999,
            'archived_at' => now(),
        ]);
        DB::table('birthday_addons')->where('birthday_addon_id', $addon->getKey())->update([
            'name' => 'Changed Add-on',
            'price_minor' => 88888,
            'archived_at' => now(),
        ]);
        $this->customer->update([
            'first_name' => 'Changed',
            'email' => 'changed@example.invalid',
            'telephone' => '4385550100',
        ]);

        $historical = $booking->fresh('addons');
        $this->assertSame('Original Package', $historical->package_name_snapshot);
        $this->assertSame(25000, $historical->package_price_minor_snapshot);
        $this->assertSame('Original Add-on', $historical->addons->first()->addon_name_snapshot);
        $this->assertSame(1500, $historical->addons->first()->addon_price_minor_snapshot);
        $this->assertSame('Staging', $historical->contact_first_name_snapshot);
        $this->assertSame('booking-test@example.invalid', $historical->contact_email_snapshot);
        $this->assertSame('+15145550100', $historical->contact_telephone_snapshot);
        $this->assertSame(26500, $historical->catalog_subtotal_minor);
    }

    public function test_booking_and_addon_snapshots_reject_updates_and_physical_deletes(): void
    {
        $this->defaultPackage('Classic', '250.00');
        $addon = $this->addon('Decorations', '15.00', 10);
        $booking = $this->createBooking([$addon->getKey()]);

        $booking->package_name_snapshot = 'Tampered';
        $this->expectValidationError(fn () => $booking->save(), 'booking');

        $snapshot = $booking->fresh('addons')->addons->first();
        $snapshot->addon_name_snapshot = 'Tampered';
        $this->expectValidationError(fn () => $snapshot->save(), 'addon');
        $this->expectValidationError(fn () => $snapshot->fresh()->delete(), 'addon');
        $this->expectValidationError(fn () => $booking->fresh()->delete(), 'booking');
    }

    public function test_cancellation_keeps_all_snapshots_and_is_idempotent(): void
    {
        $this->defaultPackage('Classic', '250.00');
        $addon = $this->addon('Decorations', '15.00', 10);
        $booking = $this->createBooking([$addon->getKey()]);

        $cancelled = app(BirthdayBookingService::class)->cancel(
            $booking,
            CarbonImmutable::create(2026, 7, 10, 18, 0, 0, 'UTC'),
        );
        $again = app(BirthdayBookingService::class)->cancel($cancelled);

        $this->assertSame(BirthdayBookingStatus::CANCELLED, $again->status);
        $this->assertSame('2026-07-10 18:00:00', $again->cancelled_at->setTimezone('UTC')->format('Y-m-d H:i:s'));
        $this->assertSame('Classic', $again->package_name_snapshot);
        $this->assertSame('Decorations', $again->addons->first()->addon_name_snapshot);
        $this->assertSame(26500, $again->catalog_subtotal_minor);

        $again->status = BirthdayBookingStatus::CATALOG_PRICED;
        $again->cancelled_at = null;
        $this->expectValidationError(fn () => $again->save(), 'status');
    }

    public function test_date_window_slots_and_dst_are_server_authoritative(): void
    {
        $this->defaultPackage('Classic', '250.00');

        $plusTwo = $this->createBooking([], '2026-07-12', '12-16');
        $plusSixty = $this->createBooking([], '2026-09-08', '16-20');

        $this->assertSame('2026-07-12 16:00:00', $plusTwo->starts_at->setTimezone('UTC')->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-08 20:00:00', $plusSixty->starts_at->setTimezone('UTC')->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-09 00:00:00', $plusSixty->ends_at->setTimezone('UTC')->format('Y-m-d H:i:s'));

        $dstNow = CarbonImmutable::create(2026, 3, 6, 12, 0, 0, 'America/Toronto');
        $dst = app(BirthdayBookingService::class)->createCatalogPricedBooking(
            $this->customer,
            $this->location,
            '2026-03-08',
            '12-16',
            10,
            [],
            [],
            $dstNow,
        );
        $this->assertSame('2026-03-08 16:00:00', $dst->starts_at->setTimezone('UTC')->format('Y-m-d H:i:s'));
        $this->assertSame('2026-03-08 20:00:00', $dst->ends_at->setTimezone('UTC')->format('Y-m-d H:i:s'));

        foreach ([
            ['2026-07-11', '12-16', 'date'],
            ['2026-09-09', '12-16', 'date'],
            ['2026-07-12', 'not-a-slot', 'slot'],
        ] as [$date, $slot, $field]) {
            $this->expectValidationError(fn () => $this->createBooking([], $date, $slot), $field);
        }
    }

    public function test_booking_instants_round_trip_as_utc_when_the_application_timezone_is_toronto(): void
    {
        $previousTimezone = date_default_timezone_get();
        $previousAppTimezone = config('app.timezone');

        try {
            date_default_timezone_set('America/Toronto');
            config()->set('app.timezone', 'America/Toronto');
            $this->defaultPackage('Classic', '250.00');

            $booking = $this->createBooking();
            $reloaded = BirthdayBooking::query()->findOrFail($booking->getKey());

            $this->assertSame('2026-07-12 16:00:00', $reloaded->starts_at->format('Y-m-d H:i:s'));
            $this->assertSame('UTC', $reloaded->starts_at->getTimezone()->getName());
            $this->assertSame('12:00', $reloaded->starts_at->setTimezone('America/Toronto')->format('H:i'));
            $this->assertSame('16:00', $reloaded->ends_at->setTimezone('America/Toronto')->format('H:i'));

            $cancelled = app(BirthdayBookingService::class)->cancel(
                $reloaded,
                CarbonImmutable::create(2026, 7, 10, 18, 0, 0, 'UTC'),
            );
            $cancelled = BirthdayBooking::query()->findOrFail($cancelled->getKey());

            $this->assertSame('2026-07-10 18:00:00', $cancelled->cancelled_at->format('Y-m-d H:i:s'));
            $this->assertSame('UTC', $cancelled->cancelled_at->getTimezone()->getName());
            $this->assertSame('UTC', $cancelled->priced_at->getTimezone()->getName());
        } finally {
            date_default_timezone_set($previousTimezone);
            config()->set('app.timezone', $previousAppTimezone);
        }
    }

    public function test_same_slot_bookings_are_allowed_and_do_not_create_other_business_objects(): void
    {
        $this->defaultPackage('Classic', '250.00');
        $counts = $this->unrelatedCounts();

        $first = $this->createBooking();
        $second = $this->createBooking();

        $this->assertNotSame($first->public_id, $second->public_id);
        $this->assertSame(2, BirthdayBooking::query()
            ->where('location_id', $this->location->getKey())
            ->whereDate('event_date', '2026-07-12')
            ->where('slot_code', '12-16')
            ->count());
        $this->assertSame($counts, $this->unrelatedCounts());
        $this->assertFalse(DB::getSchemaBuilder()->hasTable('birthday_slot_holds'));
    }

    public function test_invalid_default_package_states_reject_without_partial_booking(): void
    {
        foreach ([
            ['is_enabled' => false],
            ['archived_at' => now()],
            ['currency' => 'USD'],
        ] as $invalidState) {
            $package = $this->defaultPackage('Default', '250.00');
            DB::table('birthday_packages')->where('birthday_package_id', $package->getKey())->update($invalidState);

            $this->expectValidationError(fn () => $this->createBooking(), 'package');
            $this->assertSame(0, BirthdayBooking::query()->count());

            DB::table('birthday_packages')->where('birthday_package_id', $package->getKey())->delete();
        }

        $this->expectValidationError(fn () => $this->createBooking(), 'package');
        $this->assertSame(0, BirthdayBooking::query()->count());

        $first = $this->nonDefaultPackage('First');
        $second = $this->nonDefaultPackage('Second');
        DB::table('birthday_packages')->whereIn('birthday_package_id', [$first->getKey(), $second->getKey()])->update([
            'is_default' => true,
            'default_guard' => null,
        ]);
        $this->expectValidationError(fn () => $this->createBooking(), 'package');
        $this->assertSame(0, BirthdayBooking::query()->count());
    }

    public function test_invalid_addons_and_duplicates_roll_back_the_whole_booking(): void
    {
        $this->defaultPackage('Classic', '250.00');
        $valid = $this->addon('Valid', '10.00', 1);
        $disabled = $this->addon('Disabled', '10.00', 2, false);
        $archived = $this->addon('Archived', '10.00', 3);
        DB::table('birthday_addons')->where('birthday_addon_id', $archived->getKey())->update(['archived_at' => now()]);
        $usd = $this->addon('USD', '10.00', 4);
        DB::table('birthday_addons')->where('birthday_addon_id', $usd->getKey())->update(['currency' => 'USD']);

        foreach ([
            [$valid->getKey(), $valid->getKey()],
            [$valid->getKey(), $disabled->getKey()],
            [$valid->getKey(), $archived->getKey()],
            [$valid->getKey(), $usd->getKey()],
            [$valid->getKey(), 999999999],
        ] as $addonIds) {
            $this->expectValidationError(fn () => $this->createBooking($addonIds), 'addon_ids');
            $this->assertSame(0, BirthdayBooking::query()->count());
            $this->assertSame(0, BirthdayBookingAddon::query()->count());
        }

        $this->assertNotNull($this->createBooking([]));
    }

    public function test_snapshot_persistence_failure_rolls_back_booking_and_partial_addons(): void
    {
        $package = $this->defaultPackage('Classic', '250.00');
        $addon = $this->addon('Decorations', '15.00', 10);
        $duplicateSnapshot = [
            'id' => (int) $addon->getKey(),
            'name' => 'Decorations',
            'description' => 'Decorations description',
            'price_minor' => 1500,
            'sort_order' => 10,
        ];
        $pricing = new class($package, $duplicateSnapshot) extends BirthdayPricingSnapshotService
        {
            public function __construct(
                private readonly BirthdayPackage $package,
                private readonly array $addon,
            ) {}

            public function capture(array $addonIds): BirthdayPricingSnapshot
            {
                return new BirthdayPricingSnapshot(
                    packageId: (int) $this->package->getKey(),
                    packageName: (string) $this->package->name,
                    packageDescription: (string) $this->package->description,
                    packageIncludedItems: $this->package->included_items,
                    packagePriceMinor: 25000,
                    addons: [$this->addon, $this->addon],
                    addonsSubtotalMinor: 3000,
                    catalogSubtotalMinor: 28000,
                );
            }
        };
        $service = new BirthdayBookingService(
            app(BirthdayRules::class),
            app(BirthdayTelephone::class),
            $pricing,
        );

        try {
            $service->createCatalogPricedBooking(
                $this->customer,
                $this->location,
                '2026-07-12',
                '12-16',
                12,
                [$addon->getKey()],
                [],
                $this->now,
            );
            $this->fail('Expected the duplicate snapshot row to fail.');
        } catch (\Throwable) {
            $this->addToAssertionCount(1);
        }

        $this->assertSame(0, BirthdayBooking::query()->count());
        $this->assertSame(0, BirthdayBookingAddon::query()->count());
    }

    public function test_contact_guest_count_public_lookup_and_price_overflow_validation(): void
    {
        $this->defaultPackage('Classic', '250.00');
        $booking = $this->createBooking([], '2026-07-12', '12-16', 999, [
            'first_name' => 'Alternate',
            'last_name' => 'Contact',
            'email' => 'alternate@example.invalid',
            'telephone' => '(438) 555-0100',
        ]);

        $found = app(BirthdayBookingService::class)->findByPublicId($booking->public_id);
        $this->assertTrue($found->is($booking));
        $this->assertSame('Alternate Contact', $found->contact_name);
        $this->assertSame('+14385550100', $found->contact_telephone_snapshot);

        foreach ([0, 1000] as $guestCount) {
            $this->expectValidationError(
                fn () => $this->createBooking([], '2026-07-12', '12-16', $guestCount),
                'guest_count',
            );
        }

        $this->expectValidationError(
            fn () => $this->createBooking([], '2026-07-12', '12-16', 10, ['email' => 'invalid']),
            'email',
        );
        $this->expectValidationError(
            fn () => $this->createBooking([], '2026-07-12', '12-16', 10, ['telephone' => 'invalid']),
            'contact.telephone',
        );

        try {
            PriceValue::addMinorUnits(PHP_INT_MAX, 1);
            $this->fail('Expected overflow protection.');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringContainsString('supported range', $exception->getMessage());
        }
    }

    public function test_customer_location_and_status_state_are_validated(): void
    {
        $this->defaultPackage('Classic', '250.00');

        $this->expectValidationError(
            fn () => app(BirthdayBookingService::class)->createCatalogPricedBooking(
                new Customer,
                $this->location,
                '2026-07-12',
                '12-16',
                10,
                [],
                [],
                $this->now,
            ),
            'customer',
        );
        $this->expectValidationError(
            fn () => app(BirthdayBookingService::class)->createCatalogPricedBooking(
                $this->customer,
                new Location,
                '2026-07-12',
                '12-16',
                10,
                [],
                [],
                $this->now,
            ),
            'location',
        );

        $booking = $this->createBooking();
        $booking->status = 'paid';
        $this->expectValidationError(fn () => $booking->save(), 'status');

        $booking = $booking->fresh();
        $booking->cancelled_at = CarbonImmutable::now('UTC');
        $this->expectValidationError(fn () => $booking->save(), 'cancelled_at');

        $booking = $booking->fresh();
        $booking->status = BirthdayBookingStatus::CANCELLED;
        $this->expectValidationError(fn () => $booking->save(), 'cancelled_at');
    }

    /** @param array<int, int|string> $addonIds */
    private function createBooking(
        array $addonIds = [],
        string $date = '2026-07-12',
        string $slot = '12-16',
        int $guestCount = 12,
        array $contact = [],
    ): BirthdayBooking {
        return app(BirthdayBookingService::class)->createCatalogPricedBooking(
            $this->customer,
            $this->location,
            $date,
            $slot,
            $guestCount,
            $addonIds,
            $contact,
            $this->now,
        );
    }

    private function defaultPackage(string $name, string $price): BirthdayPackage
    {
        return app(BirthdayPackageService::class)->save(new BirthdayPackage, [
            'name' => $name,
            'description' => 'Private venue',
            'included_items_text' => "Room\nTables",
            'price' => $price,
            'currency' => 'CAD',
            'is_default' => true,
            'is_enabled' => true,
            'sort_order' => 1,
        ]);
    }

    private function nonDefaultPackage(string $name): BirthdayPackage
    {
        return app(BirthdayPackageService::class)->save(new BirthdayPackage, [
            'name' => $name,
            'description' => null,
            'included_items_text' => 'Room',
            'price' => '250.00',
            'currency' => 'CAD',
            'is_default' => false,
            'is_enabled' => true,
            'sort_order' => 1,
        ]);
    }

    private function addon(string $name, string $price, int $sortOrder, bool $enabled = true): BirthdayAddon
    {
        return app(BirthdayAddonService::class)->save(new BirthdayAddon, [
            'name' => $name,
            'description' => $name.' description',
            'price' => $price,
            'currency' => 'CAD',
            'is_enabled' => $enabled,
            'sort_order' => $sortOrder,
        ]);
    }

    private function expectValidationError(callable $callback, string $field): void
    {
        try {
            $callback();
            $this->fail('Expected validation to fail for '.$field);
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
        }
    }

    /** @return array<string, int> */
    private function unrelatedCounts(): array
    {
        return [
            'reservations' => DB::table('reservations')->count(),
            'orders' => DB::table('orders')->count(),
            'payments' => DB::table('payments')->count(),
            'payment_logs' => DB::table('payment_logs')->count(),
        ];
    }
}
