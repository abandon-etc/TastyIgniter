<?php

declare(strict_types=1);

namespace Abandon\Birthday\Models;

use Illuminate\Database\Eloquent\Builder;
use Igniter\Flame\Database\Model;

class BirthdayAddon extends Model
{
    protected $attributes = [
        'currency' => 'CAD',
        'price_minor' => 0,
        'is_enabled' => true,
        'sort_order' => 0,
    ];

    protected $table = 'birthday_addons';

    protected $primaryKey = 'birthday_addon_id';

    protected $guarded = [];

    protected $casts = [
        'price_minor' => 'integer',
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
        'archived_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $addon): void {
            if (strtoupper((string) $addon->currency) !== 'CAD') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'currency' => trans('abandon.birthday::default.validation.currency'),
                ]);
            }

            $addon->currency = 'CAD';
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
}
