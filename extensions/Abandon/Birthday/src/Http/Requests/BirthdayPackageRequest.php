<?php

declare(strict_types=1);

namespace Abandon\Birthday\Http\Requests;

use Abandon\Birthday\Rules\BirthdayPrice;
use Igniter\System\Classes\FormRequest;
use Override;

class BirthdayPackageRequest extends FormRequest
{
    #[Override]
    public function attributes(): array
    {
        return [
            'name' => trans('abandon.birthday::default.label_name'),
            'description' => trans('abandon.birthday::default.label_description'),
            'included_items_text' => trans('abandon.birthday::default.label_included_items'),
            'price' => trans('abandon.birthday::default.label_price'),
            'currency' => trans('abandon.birthday::default.label_currency'),
        ];
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'between:2,255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'included_items_text' => ['nullable', 'string', 'max:4000'],
            'price' => ['required', new BirthdayPrice],
            'currency' => ['nullable', 'in:CAD'],
            'is_default' => ['boolean'],
            'is_enabled' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:2147483647'],
        ];
    }
}
