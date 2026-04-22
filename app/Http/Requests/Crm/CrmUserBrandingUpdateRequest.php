<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class CrmUserBrandingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_logo_cropped_image' => ['nullable', 'string'],
            'login_image_cropped_image' => ['nullable', 'string'],
        ];
    }
}
