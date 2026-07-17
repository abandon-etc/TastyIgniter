<?php

declare(strict_types=1);

namespace Abandon\Birthday\Http\Controllers;

use Abandon\Birthday\Http\Requests\BirthdayAddonRequest;
use Abandon\Birthday\Models\BirthdayAddon;
use Abandon\Birthday\Services\BirthdayAddonService;
use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Admin\Http\Actions\FormController;
use Igniter\Admin\Http\Actions\ListController;
use Igniter\Admin\Widgets\Lists;
use Illuminate\Http\RedirectResponse;

class Addons extends AdminController
{
    public array $implement = [ListController::class, FormController::class];

    public array $listConfig = [
        'list' => [
            'model' => BirthdayAddon::class,
            'title' => 'abandon.birthday::default.text_addons',
            'emptyMessage' => 'abandon.birthday::default.text_empty_addons',
            'defaultSort' => ['sort_order', 'ASC'],
            'configFile' => 'addon',
        ],
    ];

    public array $formConfig = [
        'name' => 'abandon.birthday::default.text_addon',
        'model' => BirthdayAddon::class,
        'request' => BirthdayAddonRequest::class,
        'create' => [
            'title' => 'lang:igniter::admin.form.create_title',
            'redirect' => 'abandon/birthday/addons/edit/{birthday_addon_id}',
            'redirectClose' => 'abandon/birthday/addons',
            'redirectNew' => 'abandon/birthday/addons/create',
        ],
        'edit' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'abandon/birthday/addons/edit/{birthday_addon_id}',
            'redirectClose' => 'abandon/birthday/addons',
            'redirectNew' => 'abandon/birthday/addons/create',
        ],
        'preview' => [
            'title' => 'lang:igniter::admin.form.preview_title',
            'back' => 'abandon/birthday/addons',
        ],
        'configFile' => 'addon',
    ];

    protected null|string|array $requiredPermissions = 'Admin.BirthdayAddons';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('birthday_addons', 'restaurant');
    }

    public function listExtendQuery($query, ?string $alias = null): void
    {
        request()->boolean('show_archived')
            ? $query->whereNotNull('archived_at')
            : $query->whereNull('archived_at');
    }

    public function listExtendColumns(Lists $list): void
    {
        if (request()->boolean('show_archived')) {
            $list->getColumn('edit')->attributes['href'] .= '?show_archived=1';
        }
    }

    public function formExtendQuery($query): void
    {
        request()->boolean('show_archived')
            ? $query->whereNotNull('archived_at')
            : $query->whereNull('archived_at');
    }

    public function edit_onArchive(string $context, string $recordId): RedirectResponse
    {
        $addon = $this->asExtension(FormController::class)->formFindModelObject($recordId);
        app(BirthdayAddonService::class)->archive($addon);
        flash()->success(trans('abandon.birthday::default.archive_success'));

        return $this->redirect('abandon/birthday/addons');
    }

    public function edit_onRestore(string $context, string $recordId): RedirectResponse
    {
        $addon = BirthdayAddon::query()->whereNotNull('archived_at')->find($recordId);
        if (! $addon) {
            flash()->warning(trans('abandon.birthday::default.restore_not_archived'));

            return $this->redirect('abandon/birthday/addons');
        }

        app(BirthdayAddonService::class)->restore($addon);
        flash()->success(trans('abandon.birthday::default.restore_success'));

        return $this->redirect('abandon/birthday/addons?show_archived=1');
    }
}
