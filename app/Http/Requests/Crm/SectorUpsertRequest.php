<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectorUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sectorId = $this->route('sector')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('crm_sectors', 'name')->ignore($sectorId),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
