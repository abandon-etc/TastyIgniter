<?php

declare(strict_types=1);

namespace Abandon\Birthday\Services;

use Abandon\Birthday\Models\BirthdayAddon;
use Illuminate\Support\Facades\DB;

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
