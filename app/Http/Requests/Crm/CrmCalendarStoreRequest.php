<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmCalendarStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'member_user_ids' => collect((array) $this->input('member_user_ids', []))
                ->filter(fn ($memberId) => filled($memberId))
                ->map(fn ($memberId) => (int) $memberId)
                ->values()
                ->all(),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['required', 'string', Rule::in(config('heritage_crm.calendar_default_colors', []))],
            'member_user_ids' => ['nullable', 'array', 'max:12'],
            'member_user_ids.*' => ['integer', 'exists:users,id'],
        ];
    }
}
