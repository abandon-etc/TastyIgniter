<?php

declare(strict_types=1);

namespace Tests\Unit\BirthdayBooking;

use Abandon\Birthday\Models\BirthdaySlotHold;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

final class BirthdaySlotHoldDisplayTest extends TestCase
{
    public function test_optional_timestamps_are_blank_and_present_timestamps_include_utc(): void
    {
        $hold = new BirthdaySlotHold;
        $hold->released_at = null;
        $hold->expired_at = null;
        $hold->acquired_at = CarbonImmutable::create(2026, 7, 18, 12, 30, 45, 'America/Toronto');

        $this->assertSame('', $hold->released_at_display);
        $this->assertSame('', $hold->expired_at_display);
        $this->assertSame('2026-07-18 16:30:45 UTC', $hold->acquired_at_display);
    }
}
