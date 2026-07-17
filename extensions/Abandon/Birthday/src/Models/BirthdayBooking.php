<?php

declare(strict_types=1);

namespace Abandon\Birthday\Models;

use App\BirthdayBooking\BirthdaySlot;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Relations\BelongsTo;
use Igniter\Flame\Database\Relations\HasMany;
use Igniter\Local\Models\Location;
use Igniter\User\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BirthdayBooking extends Model
{
    public const int PRICING_VERSION = 1;

    public const array IMMUTABLE_FIELDS = [
        'public_id',
        'customer_id',
        'location_id',
        'event_date',
        'slot_code',
        'starts_at',
        'ends_at',
        'timezone',
        'guest_count',
        'currency',
        'package_id',
        'package_name_snapshot',
        'package_description_snapshot',
        'package_included_items_snapshot',
        'package_price_minor_snapshot',
        'addons_subtotal_minor',
        'catalog_subtotal_minor',
        'contact_first_name_snapshot',
        'contact_last_name_snapshot',
        'contact_email_snapshot',
        'contact_telephone_snapshot',
        'pricing_version',
        'priced_at',
    ];

    protected $table = 'birthday_bookings';

    protected $primaryKey = 'birthday_booking_id';

    protected $guarded = [];

    protected $attributes = [
        'status' => BirthdayBookingStatus::CATALOG_PRICED,
        'currency' => 'CAD',
        'pricing_version' => self::PRICING_VERSION,
        'addons_subtotal_minor' => 0,
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'location_id' => 'integer',
        'event_date' => 'date:Y-m-d',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'guest_count' => 'integer',
        'package_id' => 'integer',
        'package_included_items_snapshot' => 'array',
        'package_price_minor_snapshot' => 'integer',
        'addons_subtotal_minor' => 'integer',
        'catalog_subtotal_minor' => 'integer',
        'pricing_version' => 'integer',
        'priced_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public $timestamps = true;

    protected static function booted(): void
    {
        static::creating(function (self $booking): void {
            $booking->public_id ??= (string) Str::uuid();
            $booking->assertStateIsValid();
        });

        static::updating(function (self $booking): void {
            if ($booking->isDirty(self::IMMUTABLE_FIELDS)) {
                throw ValidationException::withMessages([
                    'booking' => trans('abandon.birthday::default.booking_errors.immutable'),
                ]);
            }

            $booking->assertStateIsValid();
        });

        static::deleting(function (): void {
            throw ValidationException::withMessages([
                'booking' => trans('abandon.birthday::default.booking_errors.no_delete'),
            ]);
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(BirthdayPackage::class, 'package_id', 'birthday_package_id');
    }

    public function addons(): HasMany
    {
        return $this->hasMany(BirthdayBookingAddon::class, 'birthday_booking_id', 'birthday_booking_id');
    }

    public function getContactNameAttribute(): string
    {
        return trim($this->contact_first_name_snapshot.' '.$this->contact_last_name_snapshot);
    }

    public function getCustomerNameAttribute(): string
    {
        return (string) ($this->customer?->full_name ?? '');
    }

    public function getLocationNameAttribute(): string
    {
        return (string) ($this->location?->location_name ?? '');
    }

    public function getSlotLabelAttribute(): string
    {
        try {
            return trans(BirthdaySlot::find((string) $this->slot_code)->label);
        } catch (\InvalidArgumentException) {
            return (string) $this->slot_code;
        }
    }

    public function getPackageIncludedItemsTextAttribute(): string
    {
        return implode(PHP_EOL, array_map('strval', $this->package_included_items_snapshot ?? []));
    }

    public function getPackagePriceDisplayAttribute(): string
    {
        return PriceValue::formatCad((int) $this->package_price_minor_snapshot);
    }

    public function getAddonsSubtotalDisplayAttribute(): string
    {
        return PriceValue::formatCad((int) $this->addons_subtotal_minor);
    }

    public function getCatalogSubtotalDisplayAttribute(): string
    {
        return PriceValue::formatCad((int) $this->catalog_subtotal_minor);
    }

    public function getStatusLabelAttribute(): string
    {
        return trans('abandon.birthday::default.booking_statuses.'.(string) $this->status);
    }

    public function getAddonSummaryAttribute(): string
    {
        $addons = $this->relationLoaded('addons') ? $this->addons : $this->addons()->get();

        if ($addons->isEmpty()) {
            return trans('abandon.birthday::default.text_no_booking_addons');
        }

        return $addons->map(fn (BirthdayBookingAddon $addon): string => sprintf(
            '%s - %s CAD',
            $addon->addon_name_snapshot,
            $addon->price_display,
        ))->implode(PHP_EOL);
    }

    private function assertStateIsValid(): void
    {
        if (! BirthdayBookingStatus::isValid((string) $this->status)) {
            throw ValidationException::withMessages([
                'status' => trans('abandon.birthday::default.booking_errors.invalid_status'),
            ]);
        }

        if ($this->status === BirthdayBookingStatus::CANCELLED && $this->cancelled_at === null) {
            throw ValidationException::withMessages([
                'cancelled_at' => trans('abandon.birthday::default.booking_errors.cancelled_at_required'),
            ]);
        }

        if ($this->status === BirthdayBookingStatus::CATALOG_PRICED && $this->cancelled_at !== null) {
            throw ValidationException::withMessages([
                'cancelled_at' => trans('abandon.birthday::default.booking_errors.cancelled_at_forbidden'),
            ]);
        }

        if ($this->exists
            && $this->isDirty('status')
            && $this->getOriginal('status') === BirthdayBookingStatus::CANCELLED
        ) {
            throw ValidationException::withMessages([
                'status' => trans('abandon.birthday::default.booking_errors.cancelled_is_final'),
            ]);
        }
    }
}
