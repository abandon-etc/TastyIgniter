<?php

namespace App\BirthdayBooking;

use Igniter\Admin\Widgets\Form;
use Igniter\Reservation\Models\Reservation;
use Illuminate\Support\Facades\Event;
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
            $this->syncAdminMarker($reservation);
            $this->handleSaving($reservation);
        });

        Reservation::saved(function (Reservation $reservation): void {
            if ($this->isBirthday($reservation)) {
                // Birthday availability is venue-based, not table-based.
                $reservation->addReservationTables([]);
            }
        });

        Event::listen('admin.form.extendFields', function (Form $form): void {
            if (! $form->model instanceof Reservation) {
                return;
            }

            $form->addTabFields([
                'birthday_booking' => [
                    'label' => 'birthday_booking.admin_birthday_booking',
                    'type' => 'switch',
                    'span' => 'left',
                    'comment' => 'birthday_booking.admin_birthday_booking_comment',
                ],
            ]);
        });
    }

    public function handleSaving(Reservation $reservation): void
    {
        if (! $this->isBirthday($reservation)) {
            if ($this->wasBirthday($reservation)) {
                $reservation->birthday_slot_code = null;
                $reservation->birthday_slot_key = null;
            }

            return;
        }

        $slot = BirthdaySlot::fromStartTime($reservation->reserve_time);
        if (! $slot) {
            throw ValidationException::withMessages([
                'reserve_time' => trans('birthday_booking.invalid_slot_time'),
            ]);
        }

        $date = $this->requiresDateWindow($reservation)
            ? $this->rules->normalizeDate($reservation->reserve_date)
            : $this->rules->parseDate($reservation->reserve_date);
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

    public function isBirthday(Reservation $reservation): bool
    {
        return in_array($reservation->getAttribute('birthday_booking'), [true, 1, '1'], true);
    }

    private function requiresDateWindow(Reservation $reservation): bool
    {
        return ! $reservation->exists
            || $reservation->isDirty(['birthday_booking', 'location_id', 'reserve_date', 'reserve_time']);
    }

    private function wasBirthday(Reservation $reservation): bool
    {
        return in_array($reservation->getRawOriginal('birthday_booking'), [true, 1, '1'], true);
    }

    private function syncAdminMarker(Reservation $reservation): void
    {
        if (! app()->runningInAdmin() || ! request()->has('reservation.birthday_booking')) {
            return;
        }

        $reservation->birthday_booking = request()->boolean('reservation.birthday_booking');
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
