<?php

namespace App\Http\Requests\Documents;

use Illuminate\Foundation\Http\FormRequest;

class ReviewDocumentRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
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
        return [
            'action' => ['required', 'string', 'in:approve,reject,revision'],
            'comments' => ['nullable', 'required_if:action,reject', 'required_if:action,revision', 'string', 'max:2000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'comments.required_if' => 'Comments are required when rejecting or requesting revision.',
        ];
    }
}
