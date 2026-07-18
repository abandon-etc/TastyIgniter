<?php

declare(strict_types=1);

namespace Abandon\Birthday\Http\Controllers;

use Abandon\Birthday\Models\BirthdayBooking;
use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Admin\Http\Actions\FormController;
use Igniter\Admin\Http\Actions\ListController;

class Bookings extends AdminController
{
    public array $implement = [ListController::class, FormController::class];

    public array $listConfig = [
        'list' => [
            'model' => BirthdayBooking::class,
            'title' => 'abandon.birthday::default.text_bookings',
            'emptyMessage' => 'abandon.birthday::default.text_empty_bookings',
            'defaultSort' => ['created_at', 'DESC'],
            'configFile' => 'booking',
        ],
    ];

    public array $formConfig = [
        'name' => 'abandon.birthday::default.text_booking',
        'model' => BirthdayBooking::class,
        'preview' => [
            'title' => 'lang:igniter::admin.form.preview_title',
            'back' => 'abandon/birthday/bookings',
        ],
        'configFile' => 'booking',
    ];

    protected null|string|array $requiredPermissions = 'Admin.BirthdayBookings';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('birthday_bookings', 'restaurant');
    }

    public function listExtendQuery($query): void
    {
        $query->with(['customer', 'location', 'slotHold'])->withCount('addons');
    }

    public function formExtendQuery($query): void
    {
        $query->with(['customer', 'location', 'addons', 'slotHold']);
    }
}
