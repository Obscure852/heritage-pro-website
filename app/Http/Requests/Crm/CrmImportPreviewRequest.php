<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmImportPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entity' => ['required', Rule::in(array_keys(config('heritage_crm.imports.entities', [])))],
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ];
    }
}
