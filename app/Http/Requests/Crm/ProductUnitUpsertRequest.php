<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUnitUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unitId = $this->route('unit')?->id;

        return [
            'name' => ['required', 'string', 'max:80'],
            'label' => [
                'required',
                'string',
                'max:40',
                'regex:/^[a-z0-9][a-z0-9 _-]*$/i',
                Rule::unique('crm_product_units', 'label')->ignore($unitId),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'label' => strtolower(trim((string) $this->input('label'))),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
