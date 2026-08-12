<?php

namespace App\Http\Requests\ClientSetup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientSetupMigrationUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kind' => ['required', Rule::in(array_keys(config('client_setup.migration_templates', [])))],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:' . (int) config('client_setup.migration_upload_max_kb', 20480)],
        ];
    }
}
