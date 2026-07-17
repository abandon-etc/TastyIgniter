<?php

declare(strict_types=1);

namespace Abandon\Birthday\Services;

use Abandon\Birthday\Models\BirthdayAddon;
use Abandon\Birthday\Models\PriceValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class BirthdayAddonService
{
    public function available()
    {
        return BirthdayAddon::query()->available()->orderBy('sort_order')->orderBy('birthday_addon_id')->get();
    }

    public function archive(BirthdayAddon $addon): void
    {
        DB::transaction(function () use ($addon): void {
            $addon->forceFill([
                'is_enabled' => false,
                'archived_at' => now(),
            ])->save();
        });
    }

    public function save(BirthdayAddon $addon, array $attributes): BirthdayAddon
    {
        return DB::transaction(function () use ($addon, $attributes): BirthdayAddon {
            try {
                $addon->fill($attributes);
            } catch (InvalidArgumentException) {
                throw ValidationException::withMessages([
                    'price' => trans('abandon.birthday::default.validation.price', [
                        'max' => PriceValue::MAX_AMOUNT,
                    ]),
                ]);
            }

            $addon->save();

            return $addon->refresh();
        });
    }

    public function restore(BirthdayAddon $addon): void
    {
        DB::transaction(function () use ($addon): void {
            $addon->forceFill([
                'is_enabled' => true,
                'archived_at' => null,
            ])->save();
        });
    }
}
