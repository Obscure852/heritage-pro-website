<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('crmProduct')?->id;

        return [
            'code' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('crm_products', 'code')->ignore($productId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(config('heritage_crm.commercial_product_types', [])))],
            'description' => ['nullable', 'string', 'max:4000'],
            'billing_frequency' => ['required', Rule::in(array_keys(config('heritage_crm.commercial_billing_frequencies', [])))],
            'default_unit_label' => ['required', 'string', 'max:40'],
            'default_unit_price' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'cpi_increase_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'default_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:4000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => ($code = trim((string) $this->input('code'))) !== '' ? strtoupper($code) : null,
            'name' => trim((string) $this->input('name')),
            'default_unit_label' => trim((string) $this->input('default_unit_label')),
        ]);
    }
}
