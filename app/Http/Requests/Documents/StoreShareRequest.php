<?php

namespace App\Http\Requests\Documents;

use Illuminate\Foundation\Http\FormRequest;

class StoreShareRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     * Policy check is done in the controller.
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
            'shareable_type' => 'required|in:user,role,department',
            'shareable_id' => 'required|string',
            'permission' => 'required|in:view,comment,edit,manage',
            'message' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'shareable_type.in' => 'Share type must be user, role, or department.',
            'shareable_id.required' => 'A share target is required.',
            'permission.in' => 'Permission must be view, comment, edit, or manage.',
            'message.max' => 'Share message cannot exceed 500 characters.',
        ];
    }
}
