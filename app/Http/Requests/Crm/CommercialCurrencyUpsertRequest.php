<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommercialCurrencyUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currencyId = $this->route('currency')?->id;

        return [
            'code' => [
                'required',
                'string',
                'size:3',
                Rule::unique('crm_commercial_currencies', 'code')->ignore($currencyId),
            ],
            'name' => ['required', 'string', 'max:120'],
            'symbol' => ['required', 'string', 'max:12'],
            'symbol_position' => ['required', Rule::in(['before', 'after'])],
            'precision' => ['required', 'integer', 'min:0', 'max:4'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'name' => trim((string) $this->input('name')),
            'symbol' => trim((string) $this->input('symbol')),
        ]);
    }
}
