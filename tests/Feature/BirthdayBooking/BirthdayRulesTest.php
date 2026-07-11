<?php

namespace Tests\Feature\BirthdayBooking;

use App\BirthdayBooking\BirthdayAvailabilityService;
use App\BirthdayBooking\BirthdayReservationRules;
use App\BirthdayBooking\BirthdayRules;
use App\BirthdayBooking\BirthdaySlot;
use Carbon\CarbonImmutable;
use Igniter\Reservation\Models\Reservation;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class BirthdayRulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'birthday_booking.timezone' => 'America/Toronto',
            'birthday_booking.min_advance_days' => 2,
            'birthday_booking.max_advance_days' => 60,
            'birthday_booking.slots' => [
                [
                    'code' => '12-16',
                    'start' => '12:00',
                    'end' => '16:00',
                    'label' => 'birthday_booking.slots.12_16',
                    'capacity' => 1,
                ],
                [
                    'code' => '16-20',
                    'start' => '16:00',
                    'end' => '20:00',
                    'label' => 'birthday_booking.slots.16_20',
                    'capacity' => 1,
                ],
            ],
        ]);
    }

    public function test_it_exposes_only_the_two_fixed_slots(): void
    {
        $slots = BirthdaySlot::all();

        $this->assertSame(['12-16', '16-20'], array_keys($slots));
        $this->assertSame(240, $slots['12-16']->durationMinutes());
        $this->assertSame(240, $slots['16-20']->durationMinutes());
        $this->assertNull(BirthdaySlot::fromStartTime('12:05'));
        $this->assertNull(BirthdaySlot::fromStartTime('16:15'));
    }

    public function test_date_window_is_two_to_sixty_days_in_the_venue_timezone(): void
    {
        $now = CarbonImmutable::create(2026, 7, 10, 23, 30, 0, 'America/Toronto');
        [$earliest, $latest] = (new BirthdayRules)->dateWindow($now);

        $this->assertSame('2026-07-12', $earliest->toDateString());
        $this->assertSame('2026-09-08', $latest->toDateString());
    }

    public function test_date_validation_accepts_only_the_inclusive_window(): void
    {
        $rules = new BirthdayRules;
        $now = CarbonImmutable::create(2026, 7, 10, 12, 0, 0, 'America/Toronto');

        $this->assertSame('2026-07-12', $rules->normalizeDate('2026-07-12', $now)->toDateString());
        $this->assertSame('2026-09-08', $rules->normalizeDate('2026-09-08', $now)->toDateString());

        $this->expectException(ValidationException::class);
        $rules->normalizeDate('2026-07-11', $now);
    }

    public function test_date_after_the_maximum_window_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        (new BirthdayRules)->normalizeDate(
            '2026-09-09',
            CarbonImmutable::create(2026, 7, 10, 12, 0, 0, 'America/Toronto'),
        );
    }

    public function test_non_birthday_reservation_is_ignored_when_rules_are_enabled(): void
    {
        $reservation = new Reservation;
        $reservation->setRawAttributes([
            'birthday_booking' => false,
            'reserve_date' => '2026-07-11',
            'reserve_time' => '12:05:00',
            'duration' => 45,
            'table_id' => 7,
        ], true);
        $before = $reservation->getAttributes();

        $this->birthdayReservationRules()->handleSaving($reservation);

        $this->assertSame($before, $reservation->getAttributes());
    }

    public function test_cancelled_birthday_reservation_releases_key_without_date_window_validation(): void
    {
        $reservation = new Reservation;
        $reservation->setRawAttributes([
            'birthday_booking' => 1,
            'location_id' => 1,
            'reserve_date' => '2026-07-11',
            'reserve_time' => '12:00:00',
            'status_id' => 999,
            'birthday_slot_code' => '12-16',
            'birthday_slot_key' => '1|2026-07-11|12-16',
        ], true);
        $reservation->exists = true;

        $this->birthdayReservationRules()->handleSaving($reservation);

        $this->assertNull($reservation->birthday_slot_key);
        $this->assertSame('12-16', $reservation->birthday_slot_code);
    }

    public function test_birthday_unique_constraint_is_recognized_as_a_slot_conflict(): void
    {
        $exception = new QueryException(
            'mysql',
            'insert into reservations',
            [],
            new RuntimeException('Duplicate entry for key birthday_reservation_slot_unique'),
        );

        $this->assertTrue(BirthdayAvailabilityService::isSlotConflict($exception));
    }

    private function birthdayReservationRules(): BirthdayReservationRules
    {
        return new BirthdayReservationRules(
            new BirthdayRules,
            new BirthdayAvailabilityService(new BirthdayRules),
        );
    }
}
