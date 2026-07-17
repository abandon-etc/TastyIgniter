<?php

declare(strict_types=1);

namespace Abandon\Birthday\Http\Controllers;

use Abandon\Birthday\Http\Requests\BirthdayPackageRequest;
use Abandon\Birthday\Models\BirthdayPackage;
use Abandon\Birthday\Services\BirthdayPackageService;
use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Facades\AdminMenu;
use Igniter\Admin\Http\Actions\FormController;
use Igniter\Admin\Http\Actions\ListController;
use Igniter\Admin\Widgets\Lists;
use Illuminate\Http\RedirectResponse;

class Packages extends AdminController
{
    public array $implement = [ListController::class, FormController::class];

    public array $listConfig = [
        'list' => [
            'model' => BirthdayPackage::class,
            'title' => 'abandon.birthday::default.text_packages',
            'emptyMessage' => 'abandon.birthday::default.text_empty_packages',
            'defaultSort' => ['sort_order', 'ASC'],
            'configFile' => 'package',
        ],
    ];

    public array $formConfig = [
        'name' => 'abandon.birthday::default.text_package',
        'model' => BirthdayPackage::class,
        'create' => [
            'title' => 'lang:igniter::admin.form.create_title',
            'redirect' => 'abandon/birthday/packages/edit/{birthday_package_id}',
            'redirectClose' => 'abandon/birthday/packages',
            'redirectNew' => 'abandon/birthday/packages/create',
        ],
        'edit' => [
            'title' => 'lang:igniter::admin.form.edit_title',
            'redirect' => 'abandon/birthday/packages/edit/{birthday_package_id}',
            'redirectClose' => 'abandon/birthday/packages',
            'redirectNew' => 'abandon/birthday/packages/create',
        ],
        'preview' => [
            'title' => 'lang:igniter::admin.form.preview_title',
            'back' => 'abandon/birthday/packages',
        ],
        'configFile' => 'package',
    ];

    protected null|string|array $requiredPermissions = 'Admin.BirthdayPackages';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('birthday_packages', 'restaurant');
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

    public function create_onSave(?string $context = null): ?RedirectResponse
    {
        return app(BirthdayPackageService::class)->runAtomicSave(
            fn () => $this->asExtension(FormController::class)->create_onSave($context),
        );
    }

    public function edit_onSave(?string $context = null, mixed $recordId = null): ?RedirectResponse
    {
        return app(BirthdayPackageService::class)->runAtomicSave(
            fn () => $this->asExtension(FormController::class)->edit_onSave($context, $recordId),
        );
    }

    public function edit_onArchive(string $context, string $recordId): RedirectResponse
    {
        $package = $this->asExtension(FormController::class)->formFindModelObject($recordId);
        app(BirthdayPackageService::class)->archive($package);
        flash()->success(trans('abandon.birthday::default.archive_success'));

        return $this->redirect('abandon/birthday/packages');
    }

    public function edit_onRestore(string $context, string $recordId): RedirectResponse
    {
        $package = BirthdayPackage::query()->whereNotNull('archived_at')->find($recordId);
        if (! $package) {
            flash()->warning(trans('abandon.birthday::default.restore_not_archived'));

            return $this->redirect('abandon/birthday/packages');
        }

        app(BirthdayPackageService::class)->restore($package);
        flash()->success(trans('abandon.birthday::default.restore_success'));

        return $this->redirect('abandon/birthday/packages?show_archived=1');
    }

    public function formValidate($model, $form): array
    {
        $data = $this->validateFormRequest(BirthdayPackageRequest::class, function ($request) use ($form): void {
            $request->merge($form->getSaveData());
        });

        return app(BirthdayPackageService::class)->prepareSaveData($model, $data);
    }
}
