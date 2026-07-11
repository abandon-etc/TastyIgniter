<?php

namespace App\BirthdayBooking;

use Carbon\CarbonImmutable;
use Igniter\Reservation\Models\Reservation;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

final class BirthdayAvailabilityService
{
    public function __construct(
        private readonly BirthdayRules $rules,
    ) {}

    /**
     * @return array<int, array{code: string, label: string, start: string, end: string, capacity: int, available: bool}>
     */
    public function forDate(int|string $locationId, string $date): array
    {
        $normalizedDate = $this->rules->normalizeDate($date)->toDateString();
        $occupied = Reservation::query()
            ->where('location_id', $locationId)
            ->whereDate('reserve_date', $normalizedDate)
            ->where('birthday_booking', true)
            ->whereIn('status_id', $this->rules->occupyingStatusIds())
            ->whereNotNull('birthday_slot_code')
            ->pluck('birthday_slot_code')
            ->all();

        return array_values(array_map(function (BirthdaySlot $slot) use ($occupied): array {
            return [
                'code' => $slot->code,
                'label' => $slot->label,
                'start' => $slot->start,
                'end' => $slot->end,
                'capacity' => $slot->capacity,
                'available' => ! in_array($slot->code, $occupied, true),
            ];
        }, BirthdaySlot::all()));
    }

    public function assertAvailable(Reservation $reservation): void
    {
        if (! $reservation->birthday_slot_key || ! in_array((int) $reservation->status_id, $this->rules->occupyingStatusIds(), true)) {
            return;
        }

        $query = Reservation::query()
            ->where('location_id', $reservation->location_id)
            ->where('birthday_booking', true)
            ->where('birthday_slot_key', $reservation->birthday_slot_key)
            ->whereIn('status_id', $this->rules->occupyingStatusIds());

        if ($reservation->exists) {
            $query->where($reservation->getKeyName(), '!=', $reservation->getKey());
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'reserve_time' => trans('birthday_booking.slot_unavailable'),
            ]);
        }
    }

    public function assertDateAndSlot(string $date, string $slotCode, ?CarbonImmutable $now = null): BirthdaySlot
    {
        $this->rules->normalizeDate($date, $now);

        try {
            return BirthdaySlot::find($slotCode);
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages([
                'slot' => trans('birthday_booking.invalid_slot'),
            ]);
        }
    }

    public static function isSlotConflict(QueryException $exception): bool
    {
        return str_contains(strtolower($exception->getMessage()), 'birthday_reservation_slot_unique');
    }
}
