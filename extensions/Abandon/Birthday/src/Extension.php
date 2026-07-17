<?php

declare(strict_types=1);

namespace Abandon\Birthday;

use Abandon\Birthday\Services\BirthdayAddonService;
use Abandon\Birthday\Services\BirthdayBookingService;
use Abandon\Birthday\Services\BirthdayPackageService;
use Abandon\Birthday\Services\BirthdayPricingSnapshotService;
use Igniter\System\Classes\BaseExtension;
use Override;

class Extension extends BaseExtension
{
    #[Override]
    public function register(): void
    {
        parent::register();

        $this->app->singleton(BirthdayAddonService::class);
        $this->app->singleton(BirthdayBookingService::class);
        $this->app->singleton(BirthdayPackageService::class);
        $this->app->singleton(BirthdayPricingSnapshotService::class);
    }

    #[Override]
    public function registerNavigation(): array
    {
        return [
            'restaurant' => [
                'child' => [
                    'birthday_packages' => [
                        'priority' => 70,
                        'class' => 'birthday_packages',
                        'href' => admin_url('abandon/birthday/packages'),
                        'title' => lang('abandon.birthday::default.text_packages'),
                        'permission' => 'Admin.BirthdayPackages',
                    ],
                    'birthday_addons' => [
                        'priority' => 71,
                        'class' => 'birthday_addons',
                        'href' => admin_url('abandon/birthday/addons'),
                        'title' => lang('abandon.birthday::default.text_addons'),
                        'permission' => 'Admin.BirthdayAddons',
                    ],
                    'birthday_bookings' => [
                        'priority' => 72,
                        'class' => 'birthday_bookings',
                        'href' => admin_url('abandon/birthday/bookings'),
                        'title' => lang('abandon.birthday::default.text_bookings'),
                        'permission' => 'Admin.BirthdayBookings',
                    ],
                ],
            ],
        ];
    }

    #[Override]
    public function registerPermissions(): array
    {
        return [
            'Admin.BirthdayPackages' => [
                'label' => 'abandon.birthday::default.permission_packages',
                'group' => 'igniter::admin.permissions.name',
            ],
            'Admin.BirthdayAddons' => [
                'label' => 'abandon.birthday::default.permission_addons',
                'group' => 'igniter::admin.permissions.name',
            ],
            'Admin.BirthdayBookings' => [
                'label' => 'abandon.birthday::default.permission_bookings',
                'group' => 'igniter::admin.permissions.name',
            ],
        ];
    }
}
