<?php

namespace App\BirthdayBooking;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

final class BirthdayRules
{
    public function timezone(): string
    {
        return (string) config('birthday_booking.timezone', 'America/Toronto');
    }

    /**
     * @return array{CarbonImmutable, CarbonImmutable}
     */
    public function dateWindow(?CarbonImmutable $now = null): array
    {
        $today = ($now ?: CarbonImmutable::now($this->timezone()))->setTimezone($this->timezone())->startOfDay();

        return [
            $today->addDays((int) config('birthday_booking.min_advance_days', 2)),
            $today->addDays((int) config('birthday_booking.max_advance_days', 60)),
        ];
    }

    public function normalizeDate(string|CarbonInterface $date, ?CarbonImmutable $now = null): CarbonImmutable
    {
        if ($date instanceof CarbonInterface) {
            $normalized = CarbonImmutable::instance($date)->setTimezone($this->timezone())->startOfDay();
        } else {
            $normalized = CarbonImmutable::createFromFormat('!Y-m-d', $date, $this->timezone());
            if (! $normalized || $normalized->format('Y-m-d') !== $date) {
                throw ValidationException::withMessages([
                    'date' => trans('birthday_booking.invalid_date'),
                ]);
            }
        }

        [$earliest, $latest] = $this->dateWindow($now);
        if ($normalized->lt($earliest) || $normalized->gt($latest)) {
            throw ValidationException::withMessages([
                'date' => trans('birthday_booking.date_out_of_range', [
                    'earliest' => $earliest->toDateString(),
                    'latest' => $latest->toDateString(),
                ]),
            ]);
        }

        return $normalized;
    }

    /**
     * @return array<int, int>
     */
    public function occupyingStatusIds(): array
    {
        return collect([
            setting('default_reservation_status'),
            setting('confirmed_reservation_status'),
        ])->filter(fn ($statusId): bool => is_numeric($statusId) && (int) $statusId > 0)
            ->map(fn ($statusId): int => (int) $statusId)
            ->unique()
            ->values()
            ->all();
    }

    public function slotKey(int|string $locationId, string $date, BirthdaySlot $slot): string
    {
        return sprintf('%s|%s|%s', $locationId, $date, $slot->code);
    }
}
