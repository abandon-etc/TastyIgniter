<?php

declare(strict_types=1);

namespace Abandon\Birthday\Http\Requests;

use Igniter\System\Classes\FormRequest;
use Override;

class BirthdayAddonRequest extends FormRequest
{
    #[Override]
    public function attributes(): array
    {
        return [
            'name' => trans('abandon.birthday::default.label_name'),
            'description' => trans('abandon.birthday::default.label_description'),
            'price' => trans('abandon.birthday::default.label_price'),
            'currency' => trans('abandon.birthday::default.label_currency'),
        ];
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'between:2,255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'regex:/^(?:0|[1-9]\d*)(?:\.\d{1,2})?$/'],
            'currency' => ['nullable', 'in:CAD'],
            'is_enabled' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:2147483647'],
        ];
    }
}
