<?php

declare(strict_types=1);

namespace Abandon\Birthday\Services;

use Abandon\Birthday\Models\BirthdayAddon;
use Abandon\Birthday\Models\BirthdayPackage;
use Abandon\Birthday\Models\PriceValue;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BirthdayPricingSnapshotService
{
    /**
     * Capture one consistent catalog view. The caller must invoke this method
     * inside the same database transaction that persists the Booking.
     *
     * @param  array<int, int|string>  $addonIds
     */
    public function capture(array $addonIds): BirthdayPricingSnapshot
    {
        $normalizedAddonIds = $this->normalizeAddonIds($addonIds);
        $package = $this->lockAvailableDefaultPackage();
        $addons = $this->lockAvailableAddons($normalizedAddonIds);

        $addonSnapshots = $addons->map(fn (BirthdayAddon $addon): array => [
            'id' => (int) $addon->getKey(),
            'name' => (string) $addon->name,
            'description' => $addon->description !== null ? (string) $addon->description : null,
            'price_minor' => (int) $addon->price_minor,
            'sort_order' => (int) $addon->sort_order,
        ])->values()->all();

        $addonsSubtotal = PriceValue::addMinorUnits(...array_column($addonSnapshots, 'price_minor'));
        $packagePrice = (int) $package->price_minor;

        return new BirthdayPricingSnapshot(
            packageId: (int) $package->getKey(),
            packageName: (string) $package->name,
            packageDescription: $package->description !== null ? (string) $package->description : null,
            packageIncludedItems: array_values(array_map('strval', $package->included_items ?? [])),
            packagePriceMinor: $packagePrice,
            addons: $addonSnapshots,
            addonsSubtotalMinor: $addonsSubtotal,
            catalogSubtotalMinor: PriceValue::addMinorUnits($packagePrice, $addonsSubtotal),
        );
    }

    private function lockAvailableDefaultPackage(): BirthdayPackage
    {
        $packages = BirthdayPackage::query()
            ->where('is_default', true)
            ->orderBy('birthday_package_id')
            ->lockForUpdate()
            ->get();

        if ($packages->count() !== 1) {
            throw ValidationException::withMessages([
                'package' => trans('abandon.birthday::default.booking_errors.default_package_required'),
            ]);
        }

        /** @var BirthdayPackage $package */
        $package = $packages->first();
        if (! $package->is_enabled || $package->archived_at !== null || strtoupper((string) $package->currency) !== 'CAD') {
            throw ValidationException::withMessages([
                'package' => trans('abandon.birthday::default.booking_errors.default_package_unavailable'),
            ]);
        }

        return $package;
    }

    /**
     * @param  array<int, int>  $addonIds
     * @return Collection<int, BirthdayAddon>
     */
    private function lockAvailableAddons(array $addonIds): Collection
    {
        if ($addonIds === []) {
            return collect();
        }

        $addons = BirthdayAddon::query()
            ->whereIn('birthday_addon_id', $addonIds)
            ->orderBy('sort_order')
            ->orderBy('birthday_addon_id')
            ->lockForUpdate()
            ->get();

        if ($addons->count() !== count($addonIds)) {
            $this->invalidAddons();
        }

        foreach ($addons as $addon) {
            if (! $addon->is_enabled || $addon->archived_at !== null || strtoupper((string) $addon->currency) !== 'CAD') {
                $this->invalidAddons();
            }
        }

        return $addons;
    }

    /**
     * @param  array<int, int|string>  $addonIds
     * @return array<int, int>
     */
    private function normalizeAddonIds(array $addonIds): array
    {
        $normalized = [];
        foreach ($addonIds as $addonId) {
            if (! is_int($addonId) && (! is_string($addonId) || ! ctype_digit($addonId))) {
                $this->invalidAddons();
            }

            $id = (int) $addonId;
            if ($id < 1) {
                $this->invalidAddons();
            }

            $normalized[] = $id;
        }

        if (count($normalized) !== count(array_unique($normalized))) {
            throw ValidationException::withMessages([
                'addon_ids' => trans('abandon.birthday::default.booking_errors.duplicate_addons'),
            ]);
        }

        return $normalized;
    }

    private function invalidAddons(): never
    {
        throw ValidationException::withMessages([
            'addon_ids' => trans('abandon.birthday::default.booking_errors.invalid_addons'),
        ]);
    }
}
