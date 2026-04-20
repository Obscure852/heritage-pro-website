<?php

namespace App\Http\Requests\Documents;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuotaRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true; // Authorization handled by Gate in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'quota_mb' => ['required_without:is_unlimited', 'nullable', 'integer', 'min:1'],
            'is_unlimited' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'quota_mb.required_without' => 'Please specify a quota size or select unlimited.',
            'quota_mb.min' => 'Quota must be at least 1 MB.',
        ];
    }
}
