<?php

declare(strict_types=1);

namespace Abandon\Birthday\Services;

use Abandon\Birthday\Exceptions\BirthdaySlotHoldException;
use Abandon\Birthday\Exceptions\BirthdaySlotUnavailableException;
use Abandon\Birthday\Models\BirthdayBooking;
use Abandon\Birthday\Models\BirthdayBookingStatus;
use Abandon\Birthday\Models\BirthdaySlotHold;
use Abandon\Birthday\Models\BirthdaySlotHoldStatus;
use App\BirthdayBooking\BirthdaySlot;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Igniter\Local\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BirthdaySlotHoldService
{
    public const int DURATION_SECONDS = 900;

    public const string REASON_BOOKING_CANCELLED = 'booking_cancelled';

    public const string REASON_USER_ABANDONED = 'user_abandoned';

    public const string REASON_MANUAL_TEST_CLEANUP = 'manual_test_cleanup';

    private const int MAX_ATTEMPTS = 3;

    /** @var array<int, string> */
    private const array RELEASE_REASONS = [
        self::REASON_BOOKING_CANCELLED,
        self::REASON_USER_ABANDONED,
        self::REASON_MANUAL_TEST_CLEANUP,
    ];

    public function acquire(BirthdayBooking $booking, ?CarbonInterface $now = null): BirthdaySlotHold
    {
        $instant = $this->normalizeInstant($now);

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            try {
                return DB::transaction(
                    fn (): BirthdaySlotHold => $this->acquireLocked($booking, $instant),
                    1,
                );
            } catch (QueryException $exception) {
                if (! $this->isRetryableRace($exception)) {
                    throw $exception;
                }

                if ($attempt === self::MAX_ATTEMPTS) {
                    throw $this->mappedRaceFailure($exception);
                }

                usleep($attempt * 10_000);
            }
        }

        throw new BirthdaySlotHoldException(trans('abandon.birthday::default.hold_errors.try_again'));
    }

    public function release(
        BirthdayBooking $booking,
        string $reason,
        ?CarbonInterface $now = null,
    ): ?BirthdaySlotHold {
        if (! in_array($reason, self::RELEASE_REASONS, true)) {
            throw new BirthdaySlotHoldException(trans('abandon.birthday::default.hold_errors.invalid_release_reason'));
        }

        $instant = $this->normalizeInstant($now);

        return DB::transaction(function () use ($booking, $reason, $instant): ?BirthdaySlotHold {
            $persisted = $this->lockBooking($booking);
            $hold = BirthdaySlotHold::query()
                ->where('birthday_booking_id', $persisted->getKey())
                ->lockForUpdate()
                ->first();

            if (! $hold) {
                return null;
            }

            return $this->releaseLocked($hold, $reason, $instant);
        }, 3);
    }

    public function releaseHold(
        BirthdayBooking $booking,
        BirthdaySlotHold $hold,
        string $reason,
        ?CarbonInterface $now = null,
    ): BirthdaySlotHold {
        if (! in_array($reason, self::RELEASE_REASONS, true)) {
            throw new BirthdaySlotHoldException(trans('abandon.birthday::default.hold_errors.invalid_release_reason'));
        }

        $instant = $this->normalizeInstant($now);

        return DB::transaction(function () use ($booking, $hold, $reason, $instant): BirthdaySlotHold {
            $persisted = $this->lockBooking($booking);
            $persistedHold = BirthdaySlotHold::query()->lockForUpdate()->find($hold->getKey());

            if (! $persistedHold
                || (int) $persistedHold->birthday_booking_id !== (int) $persisted->getKey()
            ) {
                throw new BirthdaySlotHoldException(trans('abandon.birthday::default.hold_errors.not_owner'));
            }

            return $this->releaseLocked($persistedHold, $reason, $instant);
        }, 3);
    }

    public function expireDue(?CarbonInterface $now = null, int $limit = 500): int
    {
        if ($limit < 1 || $limit > 5000) {
            throw new BirthdaySlotHoldException(trans('abandon.birthday::default.hold_errors.invalid_limit'));
        }

        $instant = $this->normalizeInstant($now);

        return DB::transaction(function () use ($instant, $limit): int {
            $ids = BirthdaySlotHold::query()
                ->where('status', BirthdaySlotHoldStatus::ACTIVE)
                ->where('expires_at', '<=', $this->databaseTime($instant))
                ->orderBy('birthday_slot_hold_id')
                ->limit($limit)
                ->lockForUpdate()
                ->pluck('birthday_slot_hold_id');

            if ($ids->isEmpty()) {
                return 0;
            }

            return DB::table('birthday_slot_holds')
                ->whereIn('birthday_slot_hold_id', $ids)
                ->where('status', BirthdaySlotHoldStatus::ACTIVE)
                ->where('expires_at', '<=', $this->databaseTime($instant))
                ->update([
                    'status' => BirthdaySlotHoldStatus::EXPIRED,
                    'expired_at' => $this->databaseTime($instant),
                    'updated_at' => $this->databaseTime($instant),
                ]);
        }, 3);
    }

    public function isActiveForBooking(BirthdayBooking $booking, ?CarbonInterface $now = null): bool
    {
        try {
            $this->assertActiveForBooking($booking, $now);

            return true;
        } catch (BirthdaySlotHoldException) {
            return false;
        }
    }

    public function assertActiveForBooking(
        BirthdayBooking $booking,
        ?CarbonInterface $now = null,
    ): BirthdaySlotHold {
        $persisted = $this->findBooking($booking);
        $hold = BirthdaySlotHold::query()
            ->where('birthday_booking_id', $persisted->getKey())
            ->first();

        if (! $hold
            || ! $this->sameSlot($hold, $persisted)
            || ! $hold->isActiveAt($this->normalizeInstant($now))
        ) {
            throw new BirthdaySlotHoldException(trans('abandon.birthday::default.hold_errors.not_active'));
        }

        return $hold;
    }

    private function acquireLocked(BirthdayBooking $booking, CarbonImmutable $instant): BirthdaySlotHold
    {
        $persisted = $this->lockBooking($booking);
        $slot = $this->resolveSlot($persisted);

        if ($persisted->status !== BirthdayBookingStatus::CATALOG_PRICED) {
            throw new BirthdaySlotHoldException(trans('abandon.birthday::default.hold_errors.booking_ineligible'));
        }

        if (! Location::query()->whereKey($persisted->location_id)->exists()) {
            throw new BirthdaySlotHoldException(trans('abandon.birthday::default.hold_errors.booking_invalid'));
        }

        $bookingHold = BirthdaySlotHold::query()
            ->where('birthday_booking_id', $persisted->getKey())
            ->lockForUpdate()
            ->first();

        if ($bookingHold) {
            if (! $this->sameSlot($bookingHold, $persisted)) {
                throw new BirthdaySlotHoldException(trans('abandon.birthday::default.hold_errors.second_hold'));
            }

            if ($bookingHold->isActiveAt($instant)) {
                return $bookingHold;
            }

            return $this->reclaim($bookingHold, $persisted, $instant);
        }

        $slotHold = $this->slotQuery($persisted, $slot->code)->lockForUpdate()->first();

        if (! $slotHold) {
            return BirthdaySlotHold::query()->create($this->activeAttributes($persisted, $instant));
        }

        if ($slotHold->isActiveAt($instant)) {
            throw new BirthdaySlotUnavailableException(trans('abandon.birthday::default.hold_errors.slot_unavailable'));
        }

        return $this->reclaim($slotHold, $persisted, $instant);
    }

    private function reclaim(
        BirthdaySlotHold $hold,
        BirthdayBooking $booking,
        CarbonImmutable $instant,
    ): BirthdaySlotHold {
        $this->updateHold($hold, $this->activeAttributes($booking, $instant), $instant);

        return $hold->fresh();
    }

    private function releaseLocked(
        BirthdaySlotHold $hold,
        string $reason,
        CarbonImmutable $instant,
    ): BirthdaySlotHold {
        if ($hold->status === BirthdaySlotHoldStatus::RELEASED) {
            return $hold;
        }

        if (! $hold->isActiveAt($instant)) {
            if ($hold->status === BirthdaySlotHoldStatus::ACTIVE) {
                $this->updateHold($hold, [
                    'status' => BirthdaySlotHoldStatus::EXPIRED,
                    'expired_at' => $instant,
                ], $instant);
            }

            return $hold->fresh();
        }

        $this->updateHold($hold, [
            'status' => BirthdaySlotHoldStatus::RELEASED,
            'released_at' => $instant,
            'release_reason' => $reason,
        ], $instant);

        return $hold->fresh();
    }

    /** @return array<string, mixed> */
    private function activeAttributes(BirthdayBooking $booking, CarbonImmutable $instant): array
    {
        return [
            'public_id' => (string) Str::uuid(),
            'birthday_booking_id' => $booking->getKey(),
            'location_id' => $booking->location_id,
            'event_date' => $booking->event_date->format('Y-m-d'),
            'slot_code' => $booking->slot_code,
            'status' => BirthdaySlotHoldStatus::ACTIVE,
            'acquired_at' => $instant,
            'expires_at' => $instant->addSeconds(self::DURATION_SECONDS),
            'released_at' => null,
            'expired_at' => null,
            'release_reason' => null,
        ];
    }

    /** @param array<string, mixed> $attributes */
    private function updateHold(
        BirthdaySlotHold $hold,
        array $attributes,
        CarbonImmutable $updatedAt,
    ): void {
        $values = collect($attributes)->map(function (mixed $value): mixed {
            return $value instanceof CarbonInterface ? $this->databaseTime($value) : $value;
        })->all();
        $values['updated_at'] = $this->databaseTime($updatedAt);

        DB::table('birthday_slot_holds')
            ->where('birthday_slot_hold_id', $hold->getKey())
            ->update($values);
    }

    private function lockBooking(BirthdayBooking $booking): BirthdayBooking
    {
        $persisted = BirthdayBooking::query()->lockForUpdate()->find($booking->getKey());

        if (! $persisted) {
            throw new BirthdaySlotHoldException(trans('abandon.birthday::default.hold_errors.booking_invalid'));
        }

        return $persisted;
    }

    private function findBooking(BirthdayBooking $booking): BirthdayBooking
    {
        $persisted = BirthdayBooking::query()->find($booking->getKey());

        if (! $persisted) {
            throw new BirthdaySlotHoldException(trans('abandon.birthday::default.hold_errors.booking_invalid'));
        }

        return $persisted;
    }

    private function resolveSlot(BirthdayBooking $booking): BirthdaySlot
    {
        try {
            return BirthdaySlot::find((string) $booking->slot_code);
        } catch (\InvalidArgumentException) {
            throw new BirthdaySlotHoldException(trans('abandon.birthday::default.hold_errors.booking_invalid'));
        }
    }

    private function slotQuery(BirthdayBooking $booking, string $slotCode): Builder
    {
        return BirthdaySlotHold::query()
            ->where('location_id', $booking->location_id)
            ->whereDate('event_date', $booking->event_date->format('Y-m-d'))
            ->where('slot_code', $slotCode);
    }

    private function sameSlot(BirthdaySlotHold $hold, BirthdayBooking $booking): bool
    {
        return (int) $hold->location_id === (int) $booking->location_id
            && $hold->event_date->format('Y-m-d') === $booking->event_date->format('Y-m-d')
            && $hold->slot_code === $booking->slot_code;
    }

    private function normalizeInstant(?CarbonInterface $instant): CarbonImmutable
    {
        return ($instant
            ? CarbonImmutable::instance($instant)
            : CarbonImmutable::now('UTC'))
            ->setTimezone('UTC')
            ->setMicrosecond(0);
    }

    private function databaseTime(CarbonInterface $instant): string
    {
        return CarbonImmutable::instance($instant)->setTimezone('UTC')->format('Y-m-d H:i:s');
    }

    private function isRetryableRace(QueryException $exception): bool
    {
        return in_array((int) ($exception->errorInfo[1] ?? 0), [1062, 1205, 1213], true);
    }

    private function mappedRaceFailure(QueryException $exception): BirthdaySlotHoldException
    {
        if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
            return new BirthdaySlotUnavailableException(
                trans('abandon.birthday::default.hold_errors.slot_unavailable'),
            );
        }

        return new BirthdaySlotHoldException(trans('abandon.birthday::default.hold_errors.try_again'));
    }
}
