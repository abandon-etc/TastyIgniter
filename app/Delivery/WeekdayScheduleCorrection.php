<?php

declare(strict_types=1);

namespace App\Delivery;

use Igniter\Local\Classes\WorkingDay;
use Igniter\Local\Events\WorkingScheduleCreatedEvent;
use TypeError;

/**
 * Rebuilds a working schedule's periods using the stored weekday convention.
 *
 * `WorkingHour::$weekDays` declares Monday as index 0, and that is what the
 * admin writes. `WorkingHour::getDay()` resolves an index through
 * `Carbon::startOfWeek()`, which Carbon 3 takes from the locale. This site runs
 * `fr_CA`, where the week starts on Sunday, so index 0 resolves to Sunday and
 * every stored day lands one day late. A schedule saved as Monday to Friday is
 * applied as Sunday to Thursday.
 *
 * Carbon 3 removed `setWeekStartsAt`, so the week start cannot be pinned
 * globally, and `startOfWeek(MONDAY)` would have to be passed at each vendor
 * call site. Overriding `startOfWeek` for the whole application would move
 * every week boundary in the framework to fix one mapping.
 *
 * This listener instead rebuilds the periods from the stored rows, keyed by
 * `WorkingDay::days()`, which is Monday-first and agrees with `$weekDays`. It
 * never consults `startOfWeek()`, so it is unaffected by the locale.
 *
 * Known residue, accepted as the cost of not changing the week start globally:
 * `WorkingHour::getDay()` itself remains locale-shifted. Any future code that
 * calls it directly will meet the same defect. It has no other callers today.
 */
final class WeekdayScheduleCorrection
{
    public function handle(WorkingScheduleCreatedEvent $event): void
    {
        $model = $event->model;

        if (!method_exists($model, 'getWorkingHoursByType')) {
            return;
        }

        try {
            $type = $event->schedule->getType();
        } catch (TypeError) {
            // getType() is declared string but backed by a nullable property.
            // newWorkingSchedule() always sets the type before dispatching, so
            // this only guards a future caller that does not. A schedule with
            // no type cannot be matched to stored hours, so leave it alone
            // rather than throwing out of a listener and breaking the page.
            return;
        }

        $days = WorkingDay::days();

        // Every day is rewritten, including the closed ones. Setting only the
        // open days would leave the wrongly-keyed days in place, and would not
        // be idempotent if the schedule were rebuilt.
        $periods = array_fill_keys($days, []);

        foreach ($model->getWorkingHoursByType($type) ?? [] as $hour) {
            if (!$hour->isEnabled()) {
                continue;
            }

            $day = $days[(int)$hour->weekday] ?? null;

            if ($day === null) {
                continue;
            }

            $periods[$day][] = [$hour->getOpen(), $hour->getClose()];
        }

        $event->schedule->setPeriods($periods);
    }
}
