<?php

namespace App\BirthdayBooking;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use InvalidArgumentException;

final class BirthdaySlot
{
    public function __construct(
        public readonly string $code,
        public readonly string $start,
        public readonly string $end,
        public readonly string $label,
        public readonly int $capacity,
    ) {}

    /**
     * @return array<string, self>
     */
    public static function all(): array
    {
        $slots = [];
        foreach (config('birthday_booking.slots', []) as $definition) {
            $slot = new self(
                code: $definition['code'],
                start: $definition['start'],
                end: $definition['end'],
                label: $definition['label'],
                capacity: (int) $definition['capacity'],
            );
            $slots[$slot->code] = $slot;
        }

        return $slots;
    }

    public static function find(string $code): self
    {
        return self::all()[$code] ?? throw new InvalidArgumentException('Unknown birthday booking slot.');
    }

    public static function fromStartTime(string|CarbonInterface|null $time): ?self
    {
        if ($time === null) {
            return null;
        }

        $value = $time instanceof CarbonInterface ? $time->format('H:i') : substr($time, 0, 5);

        foreach (self::all() as $slot) {
            if ($slot->start === $value) {
                return $slot;
            }
        }

        return null;
    }

    public function durationMinutes(): int
    {
        $start = CarbonImmutable::createFromFormat('!H:i', $this->start);
        $end = CarbonImmutable::createFromFormat('!H:i', $this->end);

        return (int) $start->diffInMinutes($end);
    }
}
