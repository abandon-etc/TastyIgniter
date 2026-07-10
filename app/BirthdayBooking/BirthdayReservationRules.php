<?php

namespace App\BirthdayBooking;

use Igniter\Reservation\Models\Reservation;
use Illuminate\Validation\ValidationException;

final class BirthdayReservationRules
{
    public function __construct(
        private readonly BirthdayRules $rules,
        private readonly BirthdayAvailabilityService $availability,
    ) {}

    public function register(): void
    {
        Reservation::saving(function (Reservation $reservation): void {
            $this->prepare($reservation);
        });

        Reservation::saved(function (Reservation $reservation): void {
            if ($reservation->birthday_slot_code) {
                // Birthday availability is venue-based, not table-based.
                $reservation->addReservationTables([]);
            }
        });
    }

    public function prepare(Reservation $reservation): void
    {
        $slot = BirthdaySlot::fromStartTime($reservation->reserve_time);
        if (! $slot) {
            throw ValidationException::withMessages([
                'reserve_time' => trans('birthday_booking.invalid_slot_time'),
            ]);
        }

        $date = $this->rules->normalizeDate($reservation->reserve_date);
        $statusId = $this->statusId($reservation);
        $reservation->birthday_slot_code = $slot->code;
        $reservation->duration = $slot->durationMinutes();
        $reservation->table_id = 0;
        $reservation->status_id = $statusId;
        $reservation->birthday_slot_key = in_array($statusId, $this->rules->occupyingStatusIds(), true)
            ? $this->rules->slotKey($reservation->location_id, $date->toDateString(), $slot)
            : null;

        $this->availability->assertAvailable($reservation);
    }

    private function statusId(Reservation $reservation): int
    {
        $statusId = (int) $reservation->getAttribute('status_id');
        if ($statusId > 0) {
            return $statusId;
        }

        $status = $reservation->getAttribute('status');
        if (is_numeric($status)) {
            return (int) $status;
        }

        return (int) setting('default_reservation_status');
    }
}
