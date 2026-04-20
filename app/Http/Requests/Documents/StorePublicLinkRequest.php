<?php

namespace App\Http\Requests\Documents;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicLinkRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * Authorization is handled via policy in the controller.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        $maxDays = config('documents.public.max_link_expiry_days', 365);
        $maxDate = now()->addDays($maxDays)->format('Y-m-d');

        return [
            'expires_at' => ['required', 'date', 'after:today', "before_or_equal:{$maxDate}"],
            'password' => ['nullable', 'string', 'min:8'],
            'allow_download' => ['required', 'boolean'],
            'max_views' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        $maxDays = config('documents.public.max_link_expiry_days', 365);

        return [
            'expires_at.required' => 'An expiry date is required for public links.',
            'expires_at.date' => 'The expiry date must be a valid date.',
            'expires_at.after' => 'The expiry date must be in the future.',
            'expires_at.before_or_equal' => "The expiry date cannot be more than {$maxDays} days from today.",
            'password.min' => 'The password must be at least 8 characters.',
            'allow_download.required' => 'Please specify whether downloads are allowed.',
            'allow_download.boolean' => 'The download permission must be true or false.',
            'max_views.integer' => 'The view limit must be a whole number.',
            'max_views.min' => 'The view limit must be at least 1.',
        ];
    }
}
