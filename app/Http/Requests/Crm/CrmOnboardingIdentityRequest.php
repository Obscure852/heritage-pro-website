<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmOnboardingIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = CrmUserProfileRules::identity($this->user());
        $rules['gender'] = ['required', Rule::in(['male', 'female'])];

        return $rules;
    }
}
