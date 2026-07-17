<?php

declare(strict_types=1);

namespace Tests\Feature\BirthdayBooking;

use Abandon\Birthday\Extension;
use Abandon\Birthday\Services\BirthdayAddonService;
use Abandon\Birthday\Services\BirthdayPackageService;
use Igniter\Flame\Support\Facades\Igniter;
use Igniter\System\Classes\ExtensionManager;
use Igniter\System\Classes\PackageManifest;
use Tests\TestCase;

final class BirthdayCatalogExtensionTest extends TestCase
{
    public function test_composer_and_tastyigniter_discover_the_birthday_extension(): void
    {
        $installed = json_decode(file_get_contents(base_path('vendor/composer/installed.json')), true, 512, JSON_THROW_ON_ERROR);
        $packages = $installed['packages'] ?? $installed;
        $composerPackage = collect($packages)->firstWhere('name', 'abandon-etc/ti-ext-birthday');

        $this->assertNotNull($composerPackage);
        $this->assertSame('abandon.birthday', data_get($composerPackage, 'extra.tastyigniter-extension.code'));

        $manifest = app(PackageManifest::class);
        $manifest->build();
        $extensionConfig = collect($manifest->extensions())->firstWhere('code', 'abandon.birthday');

        $this->assertNotNull($extensionConfig);
        $this->assertSame('tastyigniter-extension', $extensionConfig['type']);
        $this->assertInstanceOf(Extension::class, app(ExtensionManager::class)->loadExtensions()['abandon.birthday']);
    }

    public function test_extension_registers_navigation_permissions_services_and_migrations(): void
    {
        $extension = app(ExtensionManager::class)->loadExtensions()['abandon.birthday'];
        $extension->bootingExtension();

        $this->assertArrayHasKey('birthday_packages', $extension->registerNavigation()['restaurant']['child']);
        $this->assertArrayHasKey('birthday_addons', $extension->registerNavigation()['restaurant']['child']);
        $this->assertSame(
            ['Admin.BirthdayPackages', 'Admin.BirthdayAddons'],
            array_keys($extension->registerPermissions()),
        );
        $this->assertSame(app(BirthdayPackageService::class), app(BirthdayPackageService::class));
        $this->assertSame(app(BirthdayAddonService::class), app(BirthdayAddonService::class));
        $this->assertArrayHasKey('abandon.birthday', Igniter::migrationPath());
    }
}
