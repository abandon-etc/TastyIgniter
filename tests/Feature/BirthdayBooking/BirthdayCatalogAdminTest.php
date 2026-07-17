<?php

declare(strict_types=1);

namespace Tests\Feature\BirthdayBooking;

use Abandon\Birthday\Models\BirthdayAddon;
use Abandon\Birthday\Models\BirthdayPackage;
use Abandon\Birthday\Services\BirthdayAddonService;
use Abandon\Birthday\Services\BirthdayPackageService;
use Igniter\User\Models\User;
use Igniter\User\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

final class BirthdayCatalogAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        View::share('site_logo', 'no_photo.png');
    }

    public function test_super_user_can_load_package_and_addon_indexes(): void
    {
        $user = $this->superUser();

        $this->actingAs($user, 'igniter-admin')->get(route('abandon.birthday.packages'))->assertOk();
        $this->actingAs($user, 'igniter-admin')->get(route('abandon.birthday.addons'))->assertOk();
    }

    public function test_super_user_can_load_package_and_addon_create_pages(): void
    {
        $user = $this->superUser();

        $this->actingAs($user, 'igniter-admin')
            ->get(route('abandon.birthday.packages', ['slug' => 'create']))
            ->assertOk();
        $this->actingAs($user, 'igniter-admin')
            ->get(route('abandon.birthday.addons', ['slug' => 'create']))
            ->assertOk();
    }

    public function test_package_permission_does_not_grant_addon_access(): void
    {
        $user = $this->staffWithPermissions(['Admin.BirthdayPackages' => 1]);

        $this->actingAs($user, 'igniter-admin')->getJson(route('abandon.birthday.packages'))->assertOk();
        $this->actingAs($user, 'igniter-admin')->getJson(route('abandon.birthday.addons'))->assertStatus(406);
    }

    public function test_addon_permission_does_not_grant_package_access(): void
    {
        $user = $this->staffWithPermissions(['Admin.BirthdayAddons' => 1]);

        $this->actingAs($user, 'igniter-admin')->getJson(route('abandon.birthday.addons'))->assertOk();
        $this->actingAs($user, 'igniter-admin')->getJson(route('abandon.birthday.packages'))->assertStatus(406);
    }

    public function test_super_user_can_create_a_package(): void
    {
        $this->actingAs($this->superUser(), 'igniter-admin')->post(
            route('abandon.birthday.packages', ['slug' => 'create']),
            ['BirthdayPackage' => $this->packageForm('Created Package')],
            $this->handlerHeaders('onSave'),
        )->assertOk();

        $this->assertTrue(BirthdayPackage::query()->where('name', 'Created Package')->exists());
    }

    public function test_super_user_can_edit_a_package(): void
    {
        $package = $this->createPackage('Original Package');

        $this->actingAs($this->superUser(), 'igniter-admin')->post(
            route('abandon.birthday.packages', ['slug' => 'edit/'.$package->getKey()]),
            ['BirthdayPackage' => $this->packageForm('Updated Package')],
            $this->handlerHeaders('onSave'),
        )->assertOk();

        $this->assertSame('Updated Package', $package->fresh()->name);
    }

    public function test_super_user_can_archive_a_package(): void
    {
        $package = $this->createPackage('Package to archive');

        $this->actingAs($this->superUser(), 'igniter-admin')->post(
            route('abandon.birthday.packages', ['slug' => 'edit/'.$package->getKey()]),
            [],
            $this->handlerHeaders('onArchive'),
        )->assertOk();

        $this->assertNotNull($package->fresh()->archived_at);
    }

    public function test_archived_package_filter_only_lists_archived_records(): void
    {
        $active = $this->createPackage('Active Package');
        $archived = $this->createPackage('Archived Package');
        app(BirthdayPackageService::class)->archive($archived);

        $this->actingAs($this->superUser(), 'igniter-admin')
            ->get(route('abandon.birthday.packages', ['show_archived' => 1]))
            ->assertOk()
            ->assertSee($archived->name)
            ->assertDontSee($active->name);
    }

    public function test_super_user_can_restore_a_package(): void
    {
        $package = $this->createPackage('Package to restore');
        app(BirthdayPackageService::class)->archive($package);

        $this->actingAs($this->superUser(), 'igniter-admin')->post(
            route('abandon.birthday.packages', [
                'slug' => 'edit/'.$package->getKey(),
                'show_archived' => 1,
            ]),
            [],
            $this->handlerHeaders('onRestore'),
        )->assertOk();

        $this->assertNull($package->fresh()->archived_at);
    }

    public function test_super_user_can_create_an_addon(): void
    {
        $this->actingAs($this->superUser(), 'igniter-admin')->post(
            route('abandon.birthday.addons', ['slug' => 'create']),
            ['BirthdayAddon' => $this->addonForm('Created Add-on')],
            $this->handlerHeaders('onSave'),
        )->assertOk();

        $this->assertTrue(BirthdayAddon::query()->where('name', 'Created Add-on')->exists());
    }

    public function test_super_user_can_edit_an_addon(): void
    {
        $addon = $this->createAddon('Original Add-on');

        $this->actingAs($this->superUser(), 'igniter-admin')->post(
            route('abandon.birthday.addons', ['slug' => 'edit/'.$addon->getKey()]),
            ['BirthdayAddon' => $this->addonForm('Updated Add-on')],
            $this->handlerHeaders('onSave'),
        )->assertOk();

        $this->assertSame('Updated Add-on', $addon->fresh()->name);
    }

    public function test_super_user_can_archive_an_addon(): void
    {
        $addon = $this->createAddon('Add-on to archive');

        $this->actingAs($this->superUser(), 'igniter-admin')->post(
            route('abandon.birthday.addons', ['slug' => 'edit/'.$addon->getKey()]),
            [],
            $this->handlerHeaders('onArchive'),
        )->assertOk();

        $this->assertNotNull($addon->fresh()->archived_at);
    }

    public function test_super_user_can_restore_an_addon(): void
    {
        $addon = $this->createAddon('Add-on to restore');
        app(BirthdayAddonService::class)->archive($addon);

        $this->actingAs($this->superUser(), 'igniter-admin')->post(
            route('abandon.birthday.addons', [
                'slug' => 'edit/'.$addon->getKey(),
                'show_archived' => 1,
            ]),
            [],
            $this->handlerHeaders('onRestore'),
        )->assertOk();

        $this->assertNull($addon->fresh()->archived_at);
    }

    public function test_max_plus_one_prices_return_field_errors_without_internal_details(): void
    {
        $this->assertPriceFieldErrors('42949672.96');
    }

    public function test_huge_prices_return_field_errors_without_internal_details(): void
    {
        $this->assertPriceFieldErrors(str_repeat('9', 200));
    }

    private function assertPriceFieldErrors(string $price): void
    {
        $user = $this->superUser();

        foreach ([
            [route('abandon.birthday.packages', ['slug' => 'create']), 'BirthdayPackage', $this->packageForm('Package')],
            [route('abandon.birthday.addons', ['slug' => 'create']), 'BirthdayAddon', $this->addonForm('Add-on')],
        ] as [$url, $formName, $form]) {
            $form['price'] = $price;
            $response = $this->actingAs($user, 'igniter-admin')->postJson(
                $url,
                [$formName => $form],
                $this->handlerHeaders('onSave'),
            );

            $response->assertStatus(406);
            $payload = $response->getContent();
            $this->assertStringContainsString('price', $payload);
            $this->assertStringNotContainsString('InvalidArgumentException', $payload);
            $this->assertStringNotContainsString('birthday_packages_default_unique', $payload);
            $this->assertStringNotContainsString('SQLSTATE', $payload);
        }
    }

    private function staffWithPermissions(array $permissions): User
    {
        $role = UserRole::factory()->create(['permissions' => $permissions]);

        return User::factory()->create([
            'user_role_id' => $role->getKey(),
            'password' => 'test-only-password',
            'is_activated' => true,
            'status' => true,
            'super_user' => false,
        ]);
    }

    private function superUser(): User
    {
        return User::factory()->superUser()->create([
            'password' => 'test-only-password',
        ]);
    }

    private function createPackage(string $name): BirthdayPackage
    {
        return app(BirthdayPackageService::class)->save(
            new BirthdayPackage,
            [
                'name' => $name,
                'description' => null,
                'included_items_text' => 'Private room',
                'price' => '250.00',
                'currency' => 'CAD',
                'is_default' => false,
                'is_enabled' => true,
                'sort_order' => 10,
            ],
        );
    }

    private function createAddon(string $name): BirthdayAddon
    {
        return app(BirthdayAddonService::class)->save(
            new BirthdayAddon,
            [
                'name' => $name,
                'description' => null,
                'price' => '15.00',
                'currency' => 'CAD',
                'is_enabled' => true,
                'sort_order' => 10,
            ],
        );
    }

    private function handlerHeaders(string $handler): array
    {
        return [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-IGNITER-REQUEST-HANDLER' => $handler,
        ];
    }

    private function packageForm(string $name): array
    {
        return [
            'name' => $name,
            'description' => 'Staging test catalog item',
            'included_items_text' => 'Private room',
            'price' => '250.00',
            'currency' => 'CAD',
            'is_default' => '0',
            'is_enabled' => '1',
            'sort_order' => '10',
        ];
    }

    private function addonForm(string $name): array
    {
        return [
            'name' => $name,
            'description' => 'Staging test catalog item',
            'price' => '15.00',
            'currency' => 'CAD',
            'is_enabled' => '1',
            'sort_order' => '10',
        ];
    }
}
