<?php

declare(strict_types=1);

namespace Abandon\Birthday\Models;

use Abandon\Birthday\Casts\UtcDateTime;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Relations\BelongsTo;
use Igniter\Local\Models\Location;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BirthdaySlotHold extends Model
{
    protected $table = 'birthday_slot_holds';

    protected $primaryKey = 'birthday_slot_hold_id';

    protected $guarded = [];

    protected $attributes = [
        'status' => BirthdaySlotHoldStatus::ACTIVE,
    ];

    protected $casts = [
        'birthday_booking_id' => 'integer',
        'location_id' => 'integer',
        'event_date' => 'date:Y-m-d',
        'acquired_at' => UtcDateTime::class,
        'expires_at' => UtcDateTime::class,
        'released_at' => UtcDateTime::class,
        'expired_at' => UtcDateTime::class,
    ];

    public $timestamps = true;

    protected static function booted(): void
    {
        static::creating(function (self $hold): void {
            $hold->public_id ??= (string) Str::uuid();

            if ($hold->status !== BirthdaySlotHoldStatus::ACTIVE
                || ! $hold->acquired_at
                || ! $hold->expires_at
                || $hold->expires_at->lessThanOrEqualTo($hold->acquired_at)
            ) {
                throw ValidationException::withMessages([
                    'slot_hold' => trans('abandon.birthday::default.hold_errors.invalid_state'),
                ]);
            }
        });

        static::updating(function (): void {
            throw ValidationException::withMessages([
                'slot_hold' => trans('abandon.birthday::default.hold_errors.service_only'),
            ]);
        });

        static::deleting(function (): void {
            throw ValidationException::withMessages([
                'slot_hold' => trans('abandon.birthday::default.hold_errors.no_delete'),
            ]);
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(BirthdayBooking::class, 'birthday_booking_id', 'birthday_booking_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }

    public function isActiveAt(?CarbonInterface $now = null): bool
    {
        $instant = $now
            ? CarbonImmutable::instance($now)->setTimezone('UTC')
            : CarbonImmutable::now('UTC');

        return $this->status === BirthdaySlotHoldStatus::ACTIVE
            && $this->expires_at !== null
            && $this->expires_at->greaterThan($instant);
    }

    public function effectiveStatus(?CarbonInterface $now = null): string
    {
        if ($this->status === BirthdaySlotHoldStatus::ACTIVE && ! $this->isActiveAt($now)) {
            return BirthdaySlotHoldStatus::EXPIRED;
        }

        return (string) $this->status;
    }

    public function getEffectiveStatusLabelAttribute(): string
    {
        return trans('abandon.birthday::default.hold_statuses.'.$this->effectiveStatus());
    }

    public function getAcquiredAtDisplayAttribute(): string
    {
        return $this->formatUtc($this->acquired_at);
    }

    public function getExpiresAtDisplayAttribute(): string
    {
        return $this->formatUtc($this->expires_at);
    }

    public function getReleasedAtDisplayAttribute(): string
    {
        return $this->formatUtc($this->released_at);
    }

    public function getExpiredAtDisplayAttribute(): string
    {
        return $this->formatUtc($this->expired_at);
    }

    private function formatUtc(?CarbonInterface $instant): string
    {
        return $instant?->setTimezone('UTC')->format('Y-m-d H:i:s').' UTC' ?: '';
    }
}
