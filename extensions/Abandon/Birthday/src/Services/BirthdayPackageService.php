<?php

declare(strict_types=1);

namespace Abandon\Birthday\Services;

use Abandon\Birthday\Models\BirthdayPackage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class BirthdayPackageService
{
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
