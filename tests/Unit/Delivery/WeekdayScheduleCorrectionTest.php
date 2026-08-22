<?php

declare(strict_types=1);

namespace Tests\Unit\Delivery;

use App\Delivery\WeekdayScheduleCorrection;
use Carbon\Carbon;
use Igniter\Local\Classes\WorkingSchedule;
use Igniter\Local\Contracts\WorkingHourInterface;
use Igniter\Local\Events\WorkingScheduleCreatedEvent;
use Igniter\Local\Models\Location;
use PHPUnit\Framework\TestCase;

final class WeekdayScheduleCorrectionTest extends TestCase
{
    private ?string $previousLocale = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousLocale = Carbon::getLocale();

        // The defect only exists under a locale whose week starts on Sunday.
        // Without this the schedule maps correctly by accident and the test
        // proves nothing, so the locale is asserted rather than assumed.
        Carbon::setLocale('fr_CA');

        $this->assertSame(
            'Sun',
            Carbon::now()->startOfWeek()->format('D'),
            'The locale under test does not start the week on Sunday, so the '
            .'defect cannot reproduce and this test would pass vacuously.',
        );
    }

    protected function tearDown(): void
    {
        if ($this->previousLocale !== null) {
            Carbon::setLocale($this->previousLocale);
        }

        parent::tearDown();
    }

    /**
     * The control. Without the correction, hours stored Monday to Friday are
     * applied Sunday to Thursday. If this ever stops failing, the correction
     * is being tested against a defect that no longer reproduces.
     */
    public function test_without_the_correction_the_schedule_is_shifted(): void
    {
        $schedule = $this->buildSchedule();

        $this->assertTrue($schedule->isOpenOn('sunday'), 'Expected the shifted schedule to open Sunday.');
        $this->assertFalse($schedule->isOpenOn('friday'), 'Expected the shifted schedule to close Friday.');
    }

    public function test_the_correction_restores_the_stored_days(): void
    {
        $schedule = $this->correct($this->buildSchedule());

        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday'] as $day) {
            $this->assertTrue($schedule->isOpenOn($day), sprintf('Expected %s to be open.', $day));
        }
    }

    public function test_the_correction_closes_the_days_that_are_disabled(): void
    {
        $schedule = $this->correct($this->buildSchedule());

        $this->assertFalse($schedule->isOpenOn('saturday'));
        $this->assertFalse($schedule->isOpenOn('sunday'));
    }

    /**
     * Every day is rewritten, so a second pass cannot shift anything further.
     */
    public function test_the_correction_is_idempotent(): void
    {
        $once = $this->correct($this->buildSchedule());
        $twice = $this->correct($this->correct($this->buildSchedule()));

        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $this->assertSame(
                $once->isOpenOn($day),
                $twice->isOpenOn($day),
                sprintf('Running the correction twice changed %s.', $day),
            );
        }
    }

    public function test_it_keeps_the_stored_opening_and_closing_times(): void
    {
        $schedule = $this->correct($this->buildSchedule());

        $this->assertTrue($schedule->isOpenAt(Carbon::parse('next friday 13:00')));
        $this->assertFalse($schedule->isOpenAt(Carbon::parse('next friday 21:30')));
    }

    private function buildSchedule(): WorkingSchedule
    {
        $schedule = WorkingSchedule::create(0, collect($this->storedHours()));
        // newWorkingSchedule() always sets the type before dispatching.
        $schedule->setType('delivery');

        return $schedule;
    }

    private function correct(WorkingSchedule $schedule): WorkingSchedule
    {
        $hours = $this->storedHours();

        // No constructor argument: Eloquent instantiates the model class
        // reflectively while booting, which would fail on a required parameter.
        $model = new class extends Location
        {
            public array $storedHours = [];

            public function getWorkingHoursByType($type)
            {
                return collect($this->storedHours);
            }
        };

        $model->storedHours = $hours;

        (new WeekdayScheduleCorrection)->handle(
            new WorkingScheduleCreatedEvent($model, $schedule),
        );

        return $schedule;
    }

    /**
     * Mirrors the rows read from the staging database: weekday 0 to 4 enabled,
     * 5 and 6 disabled, all 12:00 to 21:00.
     *
     * @return list<WorkingHourInterface>
     */
    private function storedHours(): array
    {
        $hours = [];

        for ($weekday = 0; $weekday <= 6; $weekday++) {
            $hours[] = new class($weekday, $weekday <= 4) implements WorkingHourInterface
            {
                public function __construct(public int $weekday, private bool $enabled) {}

                /** Mirrors WorkingHour::getDay(), which resolves through startOfWeek(). */
                public function getDay()
                {
                    return Carbon::now()->startOfWeek()->addDays($this->weekday)->format('l');
                }

                public function getOpen()
                {
                    return '12:00';
                }

                public function getClose()
                {
                    return '21:00';
                }

                public function isEnabled()
                {
                    return $this->enabled;
                }
            };
        }

        return $hours;
    }
}
