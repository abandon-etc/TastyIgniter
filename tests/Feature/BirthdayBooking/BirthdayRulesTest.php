<?php

namespace Tests\Feature\BirthdayBooking;

use App\BirthdayBooking\BirthdayRules;
use App\BirthdayBooking\BirthdaySlot;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
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
}
