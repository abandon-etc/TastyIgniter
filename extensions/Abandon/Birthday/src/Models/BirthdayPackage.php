<?php

declare(strict_types=1);

namespace Abandon\Birthday\Models;

use Igniter\Flame\Database\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class BirthdayPackage extends Model
{
    protected $attributes = [
        'currency' => 'CAD',
        'price_minor' => 0,
        'is_default' => false,
        'is_enabled' => true,
        'sort_order' => 0,
    ];

    protected $table = 'birthday_packages';

    protected $primaryKey = 'birthday_package_id';

    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'included_items' => 'array',
        'price_minor' => 'integer',
        'is_default' => 'boolean',
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
        'archived_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $package): void {
            if ($package->is_default && (! $package->is_enabled || $package->archived_at !== null)) {
                throw ValidationException::withMessages([
                    'is_default' => trans('abandon.birthday::default.error_default_must_be_enabled'),
                ]);
            }

            $package->default_guard = $package->is_default ? 1 : null;

            if (strtoupper((string) $package->currency) !== 'CAD') {
                throw ValidationException::withMessages([
                    'currency' => trans('abandon.birthday::default.validation.currency'),
                ]);
            }

            $package->currency = 'CAD';
        });
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_enabled', true)->whereNull('archived_at');
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function getPriceAttribute(): string
    {
        $minor = max(0, (int) $this->price_minor);

        return intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }

    public function setPriceAttribute(mixed $value): void
    {
        $this->attributes['price_minor'] = PriceValue::toMinorUnits($value);
    }

    public function getIncludedItemsTextAttribute(): string
    {
        return implode(PHP_EOL, array_map('strval', $this->included_items ?? []));
    }

    public function setIncludedItemsTextAttribute(mixed $value): void
    {
        $items = preg_split('/\R/', trim((string) $value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $this->attributes['included_items'] = json_encode(array_values(array_map('trim', $items)), JSON_THROW_ON_ERROR);
    }
}
