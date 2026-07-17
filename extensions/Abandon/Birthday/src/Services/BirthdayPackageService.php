<?php

declare(strict_types=1);

namespace Abandon\Birthday\Services;

use Abandon\Birthday\Models\BirthdayPackage;
use Abandon\Birthday\Models\PriceValue;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final class BirthdayPackageService
{
    private const string DEFAULT_GUARD_INDEX = 'birthday_packages_default_unique';

    public function availableDefault(): ?BirthdayPackage
    {
        return BirthdayPackage::query()
            ->available()
            ->where('is_default', true)
            ->first();
    }

    public function available(): Collection
    {
        return BirthdayPackage::query()->available()->orderBy('sort_order')->orderBy('birthday_package_id')->get();
    }

    public function archive(BirthdayPackage $package): void
    {
        DB::transaction(function () use ($package): void {
            $package->forceFill([
                'is_default' => false,
                'is_enabled' => false,
                'archived_at' => now(),
            ])->save();
        });
    }

    public function save(BirthdayPackage $package, array $attributes): BirthdayPackage
    {
        return $this->runAtomicSave(function () use ($package, $attributes): BirthdayPackage {
            try {
                $package->fill($this->prepareSaveData($package, $attributes));
            } catch (InvalidArgumentException) {
                throw ValidationException::withMessages([
                    'price' => trans('abandon.birthday::default.validation.price', [
                        'max' => PriceValue::MAX_AMOUNT,
                    ]),
                ]);
            }

            $package->save();

            return $package->refresh();
        });
    }

    public function prepareSaveData(BirthdayPackage $package, array $attributes): array
    {
        $isDefault = (bool) ($attributes['is_default'] ?? $package->is_default);
        $isEnabled = (bool) ($attributes['is_enabled'] ?? $package->is_enabled);
        $archivedAt = $attributes['archived_at'] ?? $package->archived_at;

        if (! $isDefault) {
            $attributes['default_guard'] = null;

            return $attributes;
        }

        if (! $isEnabled || $archivedAt !== null) {
            throw ValidationException::withMessages([
                'is_default' => trans('abandon.birthday::default.error_default_must_be_enabled'),
            ]);
        }

        // Lock candidates in primary-key order so concurrent switches use the same lock order.
        BirthdayPackage::query()
            ->select($package->getKeyName())
            ->orderBy($package->getKeyName())
            ->lockForUpdate()
            ->get();

        BirthdayPackage::query()
            ->where('is_default', true)
            ->when($package->exists, fn ($query) => $query->where($package->getKeyName(), '!=', $package->getKey()))
            ->update(['is_default' => false, 'default_guard' => null]);

        $attributes['default_guard'] = 1;

        return $attributes;
    }

    public function runAtomicSave(Closure $callback): mixed
    {
        try {
            return DB::transaction($callback, 3);
        } catch (QueryException $exception) {
            if (! self::isDefaultGuardConflict($exception)) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'is_default' => trans('abandon.birthday::default.error_default_conflict'),
            ]);
        }
    }

    public static function isDefaultGuardConflict(QueryException $exception): bool
    {
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);

        return in_array($driverCode, [19, 1062], true)
            && str_contains($exception->getMessage(), self::DEFAULT_GUARD_INDEX);
    }

    public function restore(BirthdayPackage $package): void
    {
        DB::transaction(function () use ($package): void {
            $package->forceFill([
                'is_enabled' => true,
                'archived_at' => null,
                'is_default' => false,
            ])->save();
        });
    }
}
