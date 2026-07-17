<?php

declare(strict_types=1);

namespace Abandon\Birthday\Models;

use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class BirthdayBookingAddon extends Model
{
    protected $table = 'birthday_booking_addons';

    protected $primaryKey = 'birthday_booking_addon_id';

    protected $guarded = [];

    protected $casts = [
        'birthday_booking_id' => 'integer',
        'addon_id' => 'integer',
        'addon_price_minor_snapshot' => 'integer',
        'sort_order_snapshot' => 'integer',
    ];

    public $timestamps = true;

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw ValidationException::withMessages([
                'addon' => trans('abandon.birthday::default.booking_errors.addon_immutable'),
            ]);
        });

        static::deleting(function (): void {
            throw ValidationException::withMessages([
                'addon' => trans('abandon.birthday::default.booking_errors.addon_no_delete'),
            ]);
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(BirthdayBooking::class, 'birthday_booking_id', 'birthday_booking_id');
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(BirthdayAddon::class, 'addon_id', 'birthday_addon_id');
    }

    public function getPriceDisplayAttribute(): string
    {
        return PriceValue::formatCad((int) $this->addon_price_minor_snapshot);
    }
}
