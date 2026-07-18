<?php

declare(strict_types=1);

namespace Tests\Feature\BirthdayBooking;

use Abandon\Birthday\Exceptions\BirthdaySlotHoldException;
use Abandon\Birthday\Exceptions\BirthdaySlotUnavailableException;
use Abandon\Birthday\Models\BirthdayBooking;
use Abandon\Birthday\Models\BirthdayBookingStatus;
use Abandon\Birthday\Models\BirthdayPackage;
use Abandon\Birthday\Models\BirthdaySlotHold;
use Abandon\Birthday\Models\BirthdaySlotHoldStatus;
use Abandon\Birthday\Services\BirthdayBookingService;
use Abandon\Birthday\Services\BirthdayPackageService;
use Abandon\Birthday\Services\BirthdayPricingSnapshotService;
use Abandon\Birthday\Services\BirthdaySlotHoldService;
use App\BirthdayBooking\BirthdayRules;
use App\BirthdayBooking\BirthdayTelephone;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Igniter\Local\Models\Location;
use Igniter\User\Models\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class BirthdaySlotHoldServiceTest extends TestCase
{
    use DatabaseTransactions;

    private CarbonImmutable $now;

    private Customer $customer;

    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('birthday_booking.timezone', 'America/Toronto');
        $this->now = CarbonImmutable::create(2026, 7, 10, 16, 0, 0, 'UTC');
        $this->customer = Customer::factory()->create([
            'first_name' => 'Hold',
            'last_name' => 'Test',
            'email' => 'hold-test@example.invalid',
            'password' => 'test-only-password',
            'telephone' => '5145550100',
            'is_activated' => true,
            'status' => true,
        ]);
        $this->location = Location::factory()->create([
            'location_name' => 'Hold Test Location',
            'location_status' => true,
        ]);
        app(BirthdayPackageService::class)->save(new BirthdayPackage, [
            'name' => 'Hold Test Package',
            'description' => 'Test only',
            'included_items_text' => 'Room',
            'price' => '250.00',
            'currency' => 'CAD',
            'is_default' => true,
            'is_enabled' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_acquire_is_explicit_exactly_fifteen_minutes_and_idempotent_without_renewal(): void
    {
        $booking = $this->booking();
        $this->assertSame(0, BirthdaySlotHold::query()->count());

        $hold = $this->holds()->acquire($booking, $this->now);
        $again = $this->holds()->acquire($booking, $this->now->addMinutes(5));

        $this->assertSame($hold->getKey(), $again->getKey());
        $this->assertSame($hold->public_id, $again->public_id);
        $this->assertSame($hold->acquired_at->toIso8601String(), $again->acquired_at->toIso8601String());
        $this->assertSame($hold->expires_at->toIso8601String(), $again->expires_at->toIso8601String());
        $this->assertSame(900, (int) $hold->acquired_at->diffInSeconds($hold->expires_at));
        $this->assertSame('UTC', $hold->acquired_at->getTimezone()->getName());
        $this->assertSame('UTC', $hold->expires_at->getTimezone()->getName());
        $this->assertSame(1, BirthdaySlotHold::query()->count());
        $this->assertTrue($this->holds()->isActiveForBooking($booking, $this->now->addMinutes(14)->addSeconds(59)));
        $this->assertFalse($this->holds()->isActiveForBooking($booking, $this->now->addMinutes(15)));
        $this->assertFalse($this->holds()->isActiveForBooking($booking, $this->now->addMinutes(15)->addSecond()));
        $this->assertSame(BirthdaySlotHoldStatus::EXPIRED, $hold->effectiveStatus($this->now->addMinutes(15)));

        $afterExpiry = $this->holds()->acquire($booking, $this->now->addMinutes(15));
        $this->assertSame($hold->getKey(), $afterExpiry->getKey());
        $this->assertNotSame($hold->public_id, $afterExpiry->public_id);
        $this->holds()->release(
            $booking,
            BirthdaySlotHoldService::REASON_USER_ABANDONED,
            $this->now->addMinutes(16),
        );
        $afterRelease = $this->holds()->acquire($booking, $this->now->addMinutes(17));
        $this->assertSame($hold->getKey(), $afterRelease->getKey());
        $this->assertNotSame($afterExpiry->public_id, $afterRelease->public_id);
        $this->assertSame(1, BirthdaySlotHold::query()->count());
    }

    public function test_hold_duration_is_utc_based_across_daylight_and_standard_time(): void
    {
        foreach ([
            CarbonImmutable::create(2026, 3, 8, 6, 55, 0, 'UTC'),
            CarbonImmutable::create(2026, 11, 1, 5, 55, 0, 'UTC'),
        ] as $index => $instant) {
            $booking = $this->booking('2026-07-'.(12 + $index), $index === 0 ? '12-16' : '16-20');
            $hold = $this->holds()->acquire($booking, $instant);

            $this->assertSame(900, (int) $hold->acquired_at->diffInSeconds($hold->expires_at));
        }
    }

    public function test_active_conflict_is_readable_and_does_not_leak_database_or_customer_details(): void
    {
        $first = $this->booking();
        $second = $this->booking();
        $this->holds()->acquire($first, $this->now);

        try {
            $this->holds()->acquire($second, $this->now);
            $this->fail('Expected the occupied slot to be rejected.');
        } catch (BirthdaySlotUnavailableException $exception) {
            $this->assertStringContainsString('no longer available', $exception->getMessage());
            $this->assertStringNotContainsString('SQLSTATE', $exception->getMessage());
            $this->assertStringNotContainsString((string) $first->public_id, $exception->getMessage());
            $this->assertStringNotContainsString((string) $first->contact_email_snapshot, $exception->getMessage());
        }

        $this->assertSame(1, BirthdaySlotHold::query()->count());
    }

    public function test_expired_and_released_rows_are_reclaimed_atomically_with_new_identity_and_window(): void
    {
        $first = $this->booking();
        $second = $this->booking();
        $hold = $this->holds()->acquire($first, $this->now);

        $reclaimed = $this->holds()->acquire($second, $this->now->addMinutes(15));
        $this->assertSame($hold->getKey(), $reclaimed->getKey());
        $this->assertNotSame($hold->public_id, $reclaimed->public_id);
        $this->assertSame($second->getKey(), $reclaimed->birthday_booking_id);
        $this->assertSame($this->now->addMinutes(30)->toIso8601String(), $reclaimed->expires_at->toIso8601String());

        $released = $this->holds()->release(
            $second,
            BirthdaySlotHoldService::REASON_USER_ABANDONED,
            $this->now->addMinutes(16),
        );
        $third = $this->booking();
        $reacquired = $this->holds()->acquire($third, $this->now->addMinutes(17));

        $this->assertSame(BirthdaySlotHoldStatus::RELEASED, $released->status);
        $this->assertSame($hold->getKey(), $reacquired->getKey());
        $this->assertNotSame($released->public_id, $reacquired->public_id);
        $this->assertNull($reacquired->released_at);
        $this->assertNull($reacquired->expired_at);
        $this->assertNull($reacquired->release_reason);
        $this->assertSame(1, BirthdaySlotHold::query()->count());
    }

    public function test_release_is_owner_only_reason_bounded_and_idempotent(): void
    {
        $owner = $this->booking();
        $other = $this->booking();
        $hold = $this->holds()->acquire($owner, $this->now);

        $this->expectHoldError(
            fn () => $this->holds()->releaseHold(
                $other,
                $hold,
                BirthdaySlotHoldService::REASON_USER_ABANDONED,
                $this->now,
            ),
            'cannot release',
        );
        $this->expectHoldError(fn () => $this->holds()->release($owner, 'arbitrary'), 'supported');

        $released = $this->holds()->release(
            $owner,
            BirthdaySlotHoldService::REASON_USER_ABANDONED,
            $this->now->addMinute(),
        );
        $again = $this->holds()->release(
            $owner,
            BirthdaySlotHoldService::REASON_MANUAL_TEST_CLEANUP,
            $this->now->addMinutes(2),
        );

        $this->assertSame($hold->getKey(), $again->getKey());
        $this->assertSame(BirthdaySlotHoldStatus::RELEASED, $again->status);
        $this->assertSame(BirthdaySlotHoldService::REASON_USER_ABANDONED, $again->release_reason);
        $this->assertSame($released->released_at->toIso8601String(), $again->released_at->toIso8601String());
        $this->assertSame($hold->expires_at->toIso8601String(), $again->expires_at->toIso8601String());
    }

    public function test_cancel_releases_hold_in_the_same_transaction_and_cancelled_booking_cannot_reacquire(): void
    {
        $booking = $this->booking();
        $this->holds()->acquire($booking, $this->now);

        $cancelled = app(BirthdayBookingService::class)->cancel($booking, $this->now->addMinute());
        $hold = $cancelled->slotHold;

        $this->assertSame(BirthdayBookingStatus::CANCELLED, $cancelled->status);
        $this->assertSame(BirthdaySlotHoldStatus::RELEASED, $hold->status);
        $this->assertSame(BirthdaySlotHoldService::REASON_BOOKING_CANCELLED, $hold->release_reason);
        $this->expectHoldError(fn () => $this->holds()->acquire($cancelled, $this->now->addMinutes(2)), 'cannot acquire');

        $withoutHold = app(BirthdayBookingService::class)->cancel($this->booking(), $this->now->addMinutes(3));
        $this->assertSame(BirthdayBookingStatus::CANCELLED, $withoutHold->status);
        $this->assertNull($withoutHold->slotHold);

        $expiredBooking = $this->booking('2026-07-13', '16-20');
        $this->holds()->acquire($expiredBooking, $this->now);
        $expiredBooking = app(BirthdayBookingService::class)->cancel(
            $expiredBooking,
            $this->now->addMinutes(15),
        );
        $this->assertSame(BirthdayBookingStatus::CANCELLED, $expiredBooking->status);
        $this->assertSame(BirthdaySlotHoldStatus::EXPIRED, $expiredBooking->slotHold->status);
    }

    public function test_cancel_rolls_back_when_hold_release_fails(): void
    {
        $booking = $this->booking();
        $this->holds()->acquire($booking, $this->now);
        $failingHolds = new class extends BirthdaySlotHoldService
        {
            public function release(
                BirthdayBooking $booking,
                string $reason,
                ?CarbonInterface $now = null,
            ): ?BirthdaySlotHold {
                parent::release($booking, $reason, $now);

                throw new BirthdaySlotHoldException('Test release failure.');
            }
        };
        $service = new BirthdayBookingService(
            app(BirthdayRules::class),
            app(BirthdayTelephone::class),
            app(BirthdayPricingSnapshotService::class),
            $failingHolds,
        );

        $this->expectException(BirthdaySlotHoldException::class);

        try {
            $service->cancel($booking, $this->now->addMinute());
        } finally {
            $this->assertSame(BirthdayBookingStatus::CATALOG_PRICED, $booking->fresh()->status);
            $this->assertTrue($booking->fresh()->slotHold->isActiveAt($this->now->addMinute()));
        }
    }

    public function test_cleanup_is_idempotent_but_correctness_does_not_depend_on_it(): void
    {
        $first = $this->booking();
        $second = $this->booking('2026-07-13', '16-20');
        $due = $this->holds()->acquire($first, $this->now);
        $future = $this->holds()->acquire($second, $this->now->addHour());

        $this->assertSame(1, $this->holds()->expireDue($this->now->addMinutes(15)));
        $this->assertSame(0, $this->holds()->expireDue($this->now->addMinutes(15)));
        $this->assertSame(BirthdaySlotHoldStatus::EXPIRED, $due->fresh()->status);
        $this->assertSame(BirthdaySlotHoldStatus::ACTIVE, $future->fresh()->status);

        $third = $this->booking();
        $reclaimed = $this->holds()->acquire($third, $this->now->addMinutes(16));
        $this->assertSame($due->getKey(), $reclaimed->getKey());
    }

    public function test_cleanup_command_is_repeatable_and_validates_limit(): void
    {
        $booking = $this->booking();
        $this->holds()->acquire($booking, $this->now);
        DB::table('birthday_slot_holds')->where('birthday_booking_id', $booking->getKey())->update([
            'expires_at' => CarbonImmutable::now('UTC')->subMinute()->format('Y-m-d H:i:s'),
        ]);

        $this->artisan('birthday:expire-slot-holds', ['--limit' => 100])
            ->expectsOutputToContain('Expired 1 Birthday slot hold(s).')
            ->assertSuccessful();
        $this->artisan('birthday:expire-slot-holds', ['--limit' => 100])
            ->expectsOutputToContain('Expired 0 Birthday slot hold(s).')
            ->assertSuccessful();
        $this->artisan('birthday:expire-slot-holds', ['--limit' => 0])->assertFailed();
    }

    public function test_invalid_unsaved_missing_cancelled_and_tampered_bookings_are_rejected(): void
    {
        $this->expectHoldError(fn () => $this->holds()->acquire(new BirthdayBooking, $this->now), 'not valid');

        $missing = new BirthdayBooking;
        $missing->setAttribute('birthday_booking_id', 999999999);
        $missing->exists = true;
        $this->expectHoldError(fn () => $this->holds()->acquire($missing, $this->now), 'not valid');

        $cancelled = app(BirthdayBookingService::class)->cancel($this->booking(), $this->now);
        $this->expectHoldError(fn () => $this->holds()->acquire($cancelled, $this->now), 'cannot acquire');

        $tampered = $this->booking('2026-07-13', '12-16');
        DB::table('birthday_bookings')->where('birthday_booking_id', $tampered->getKey())->update(['slot_code' => 'invalid']);
        $this->expectHoldError(fn () => $this->holds()->acquire($tampered, $this->now), 'not valid');
    }

    public function test_one_booking_cannot_hold_a_second_slot_and_direct_model_mutation_is_blocked(): void
    {
        $booking = $this->booking();
        $hold = $this->holds()->acquire($booking, $this->now);
        DB::table('birthday_bookings')->where('birthday_booking_id', $booking->getKey())->update(['slot_code' => '16-20']);
        $booking = $booking->fresh();

        $this->expectHoldError(fn () => $this->holds()->acquire($booking, $this->now), 'more than one');

        $hold = $hold->fresh();
        $hold->expires_at = $hold->expires_at->addMinute();
        $this->expectValidationError(fn () => $hold->save(), 'slot_hold');
        $this->expectValidationError(fn () => $hold->fresh()->delete(), 'slot_hold');
    }

    public function test_hold_actions_do_not_create_reservations_orders_or_payment_records(): void
    {
        $counts = collect(['reservations', 'orders', 'payments', 'payment_logs'])
            ->mapWithKeys(fn (string $table): array => [$table => DB::table($table)->count()])
            ->all();
        $booking = $this->booking();
        $this->holds()->acquire($booking, $this->now);
        $this->holds()->release($booking, BirthdaySlotHoldService::REASON_MANUAL_TEST_CLEANUP, $this->now->addMinute());

        foreach ($counts as $table => $count) {
            $this->assertSame($count, DB::table($table)->count());
        }
    }

    private function booking(string $date = '2026-07-12', string $slot = '12-16'): BirthdayBooking
    {
        return app(BirthdayBookingService::class)->createCatalogPricedBooking(
            $this->customer,
            $this->location,
            $date,
            $slot,
            12,
            [],
            [],
            CarbonImmutable::create(2026, 7, 10, 12, 0, 0, 'America/Toronto'),
        );
    }

    private function holds(): BirthdaySlotHoldService
    {
        return app(BirthdaySlotHoldService::class);
    }

    private function expectHoldError(callable $callback, string $message): void
    {
        try {
            $callback();
            $this->fail('Expected a Birthday slot hold domain error.');
        } catch (BirthdaySlotHoldException $exception) {
            $this->assertStringContainsString($message, $exception->getMessage());
            $this->assertStringNotContainsString('SQLSTATE', $exception->getMessage());
        }
    }

    private function expectValidationError(callable $callback, string $field): void
    {
        try {
            $callback();
            $this->fail('Expected validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
        }
    }
}
