<?php

declare(strict_types=1);

namespace Tests\Feature\BirthdayBooking;

use Abandon\Birthday\Models\BirthdayAddon;
use Abandon\Birthday\Models\BirthdayBooking;
use Abandon\Birthday\Models\BirthdayPackage;
use Abandon\Birthday\Services\BirthdayAddonService;
use Abandon\Birthday\Services\BirthdayBookingService;
use Abandon\Birthday\Services\BirthdayPackageService;
use Carbon\CarbonImmutable;
use Igniter\Local\Models\Location;
use Igniter\User\Models\Customer;
use Igniter\User\Models\User;
use Igniter\User\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

final class BirthdayBookingAdminTest extends TestCase
{
    use DatabaseTransactions;

    private BirthdayBooking $booking;

    private BirthdayPackage $package;

    private BirthdayAddon $addon;

    protected function setUp(): void
    {
        parent::setUp();

        View::share('site_logo', 'no_photo.png');
        config()->set('birthday_booking.timezone', 'America/Toronto');

        $customer = Customer::factory()->create([
            'first_name' => 'Snapshot',
            'last_name' => 'Customer',
            'email' => 'admin-booking-test@example.invalid',
            'password' => 'test-only-password',
            'telephone' => '5145550100',
            'is_activated' => true,
            'status' => true,
        ]);
        $location = Location::factory()->create([
            'location_name' => 'Snapshot Location',
            'location_status' => true,
        ]);
        $this->package = app(BirthdayPackageService::class)->save(new BirthdayPackage, [
            'name' => 'Historical Package',
            'description' => 'Historical package description',
            'included_items_text' => "Private room\nTables",
            'price' => '250.00',
            'currency' => 'CAD',
            'is_default' => true,
            'is_enabled' => true,
            'sort_order' => 1,
        ]);
        $this->addon = app(BirthdayAddonService::class)->save(new BirthdayAddon, [
            'name' => 'Historical Add-on',
            'description' => 'Historical add-on description',
            'price' => '15.00',
            'currency' => 'CAD',
            'is_enabled' => true,
            'sort_order' => 1,
        ]);
        $this->booking = app(BirthdayBookingService::class)->createCatalogPricedBooking(
            $customer,
            $location,
            '2026-07-12',
            '12-16',
            12,
            [$this->addon->getKey()],
            [],
            CarbonImmutable::create(2026, 7, 10, 12, 0, 0, 'America/Toronto'),
        );
    }

    public function test_super_user_can_view_read_only_booking_list(): void
    {
        $user = $this->superUser();

        $this->actingAs($user, 'igniter-admin')
            ->get(route('abandon.birthday.bookings'))
            ->assertOk()
            ->assertSee($this->booking->public_id)
            ->assertSee('Historical Package')
            ->assertSee('$265.00')
            ->assertSee('CAD')
            ->assertSee('Catalog priced')
            ->assertDontSee('abandon/birthday/bookings/create')
            ->assertDontSee('abandon.birthday::default');
    }

    public function test_super_user_can_view_read_only_snapshot_detail(): void
    {
        $user = $this->superUser();

        $this->actingAs($user, 'igniter-admin')
            ->get(route('abandon.birthday.bookings', ['slug' => 'preview/'.$this->booking->getKey()]))
            ->assertOk()
            ->assertSee('Snapshot Customer')
            ->assertSee('admin-booking-test@example.invalid')
            ->assertSee('+15145550100')
            ->assertSee('Snapshot Location')
            ->assertSee('Historical Package')
            ->assertSee('Private room')
            ->assertSee('Historical Add-on')
            ->assertSee('$250.00')
            ->assertSee('$15.00')
            ->assertSee('$265.00')
            ->assertSee('CAD')
            ->assertSee('Catalog priced')
            ->assertDontSee('data-request="onSave"', false)
            ->assertDontSee('data-request="onDelete"', false)
            ->assertDontSee('abandon.birthday::default');
    }

    public function test_admin_pages_display_snapshots_after_catalog_sources_change(): void
    {
        DB::table('birthday_packages')->where('birthday_package_id', $this->package->getKey())->update([
            'name' => 'Current Package Name',
            'price_minor' => 99999,
        ]);
        DB::table('birthday_addons')->where('birthday_addon_id', $this->addon->getKey())->update([
            'name' => 'Current Add-on Name',
            'price_minor' => 88888,
        ]);

        $this->actingAs($this->superUser(), 'igniter-admin')
            ->get(route('abandon.birthday.bookings', ['slug' => 'preview/'.$this->booking->getKey()]))
            ->assertOk()
            ->assertSee('Historical Package')
            ->assertSee('Historical Add-on')
            ->assertSee('$265.00')
            ->assertDontSee('Current Package Name')
            ->assertDontSee('Current Add-on Name')
            ->assertDontSee('$999.99')
            ->assertDontSee('$888.88');
    }

    public function test_booking_permission_is_independent_and_unprivileged_staff_are_denied(): void
    {
        $bookingStaff = $this->staffWithPermissions(['Admin.BirthdayBookings' => 1]);
        $catalogStaff = $this->staffWithPermissions(['Admin.BirthdayPackages' => 1, 'Admin.BirthdayAddons' => 1]);

        $this->actingAs($bookingStaff, 'igniter-admin')
            ->getJson(route('abandon.birthday.bookings'))
            ->assertOk();
        $this->actingAs($bookingStaff, 'igniter-admin')
            ->getJson(route('abandon.birthday.packages'))
            ->assertStatus(406);
        $this->actingAs($bookingStaff, 'igniter-admin')
            ->getJson(route('abandon.birthday.addons'))
            ->assertStatus(406);
        $this->actingAs($catalogStaff, 'igniter-admin')
            ->getJson(route('abandon.birthday.bookings'))
            ->assertStatus(406);
    }

    public function test_create_edit_and_delete_actions_are_not_exposed(): void
    {
        $user = $this->superUser();

        foreach (['create', 'edit/'.$this->booking->getKey()] as $slug) {
            $this->actingAs($user, 'igniter-admin')
                ->getJson(route('abandon.birthday.bookings', ['slug' => $slug]))
                ->assertStatus(406);
        }

        $this->actingAs($user, 'igniter-admin')
            ->postJson(
                route('abandon.birthday.bookings', ['slug' => 'edit/'.$this->booking->getKey()]),
                [],
                ['X-IGNITER-REQUEST-HANDLER' => 'onDelete'],
            )
            ->assertStatus(406);
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
}
