<?php

namespace App\Http\Requests\Documents;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true; // Authorization handled via Gate in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        $categoryId = $this->route('category')->id ?? $this->route('category');

        return [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'parent_id' => [
                'nullable',
                'integer',
                'exists:document_categories,id',
                function ($attribute, $value, $fail) use ($categoryId) {
                    if ((int) $value === (int) $categoryId) {
                        $fail('A category cannot be its own parent.');
                    }
                },
            ],
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
            'retention_days' => 'nullable|integer|min:1',
            'requires_approval' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }
}
