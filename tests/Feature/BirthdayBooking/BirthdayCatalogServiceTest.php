<?php

declare(strict_types=1);

namespace Tests\Feature\BirthdayBooking;

use Abandon\Birthday\Models\BirthdayAddon;
use Abandon\Birthday\Models\BirthdayPackage;
use Abandon\Birthday\Services\BirthdayAddonService;
use Abandon\Birthday\Services\BirthdayPackageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

final class BirthdayCatalogServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_package_service_filters_orders_and_switches_the_default_atomically(): void
    {
        $service = app(BirthdayPackageService::class);
        $first = $service->save(new BirthdayPackage, $this->packageAttributes('First', 20, true));
        $second = $service->save(new BirthdayPackage, $this->packageAttributes('Second', 10, false));
        $service->save(new BirthdayPackage, $this->packageAttributes('Disabled', 1, false, false));

        $this->assertSame(['Second', 'First'], $service->available()->pluck('name')->all());
        $this->assertTrue($service->availableDefault()?->is($first));

        $service->save($second, ['is_default' => true, 'is_enabled' => true]);

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
        $this->assertSame(1, BirthdayPackage::query()->where('default_guard', 1)->count());
    }

    public function test_failed_default_switch_rolls_back_and_preserves_the_previous_default(): void
    {
        $service = app(BirthdayPackageService::class);
        $first = $service->save(new BirthdayPackage, $this->packageAttributes('First', 10, true));
        $second = $service->save(new BirthdayPackage, $this->packageAttributes('Second', 20, false));

        try {
            $service->runAtomicSave(function () use ($service, $second): void {
                $second->fill($service->prepareSaveData($second, ['is_default' => true, 'is_enabled' => true]))->save();
                throw new RuntimeException('force rollback');
            });
            $this->fail('Expected the transaction to roll back.');
        } catch (RuntimeException $exception) {
            $this->assertSame('force rollback', $exception->getMessage());
        }

        $this->assertTrue($first->fresh()->is_default);
        $this->assertFalse($second->fresh()->is_default);
    }

    public function test_unique_guard_conflict_returns_a_readable_field_error_and_keeps_one_default(): void
    {
        $service = app(BirthdayPackageService::class);
        $service->save(new BirthdayPackage, $this->packageAttributes('First', 10, true));

        try {
            $service->runAtomicSave(function (): void {
                DB::table('birthday_packages')->insert([
                    'name' => 'Concurrent loser',
                    'price_minor' => 100,
                    'currency' => 'CAD',
                    'is_default' => true,
                    'is_enabled' => true,
                    'sort_order' => 0,
                    'default_guard' => 1,
                ]);
            });
            $this->fail('Expected the unique guard to reject the second default.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('is_default', $exception->errors());
            $this->assertStringNotContainsString('birthday_packages_default_unique', $exception->errors()['is_default'][0]);
            $this->assertStringNotContainsString('SQL', $exception->errors()['is_default'][0]);
        }

        $this->assertSame(1, BirthdayPackage::query()->where('default_guard', 1)->count());
    }

    public function test_archive_restore_and_no_default_state_are_safe(): void
    {
        $service = app(BirthdayPackageService::class);
        $package = $service->save(new BirthdayPackage, $this->packageAttributes('Default', 10, true));

        $service->archive($package);
        $this->assertNull($service->availableDefault());
        $this->assertFalse($package->fresh()->is_enabled);

        $service->restore($package->fresh());
        $this->assertTrue($package->fresh()->is_enabled);
        $this->assertFalse($package->fresh()->is_default);
        $this->assertNull($service->availableDefault());
    }

    public function test_addon_service_filters_orders_archives_and_restores_without_quantity(): void
    {
        $service = app(BirthdayAddonService::class);
        $second = $service->save(new BirthdayAddon, $this->addonAttributes('Second', 20));
        $first = $service->save(new BirthdayAddon, $this->addonAttributes('First', 10));
        $service->save(new BirthdayAddon, $this->addonAttributes('Disabled', 1, false));

        $this->assertSame(['First', 'Second'], $service->available()->pluck('name')->all());
        $this->assertArrayNotHasKey('quantity', $first->getAttributes());

        $service->archive($second);
        $this->assertSame(['First'], $service->available()->pluck('name')->all());

        $service->restore($second->fresh());
        $this->assertSame(['First', 'Second'], $service->available()->pluck('name')->all());
    }

    private function packageAttributes(string $name, int $sortOrder, bool $default, bool $enabled = true): array
    {
        return [
            'name' => $name,
            'description' => null,
            'included_items_text' => "Room\nTables",
            'price' => '250.50',
            'currency' => 'CAD',
            'is_default' => $default,
            'is_enabled' => $enabled,
            'sort_order' => $sortOrder,
        ];
    }

    private function addonAttributes(string $name, int $sortOrder, bool $enabled = true): array
    {
        return [
            'name' => $name,
            'description' => null,
            'price' => '15.00',
            'currency' => 'CAD',
            'is_enabled' => $enabled,
            'sort_order' => $sortOrder,
        ];
    }
}
