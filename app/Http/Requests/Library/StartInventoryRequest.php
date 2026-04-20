<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class StartInventoryRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-library');
    }

    public function rules(): array {
        return [
            'scope_type' => ['required', 'in:all,location,genre'],
            'scope_value' => ['nullable', 'string', 'max:100', 'required_unless:scope_type,all'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array {
        return [
            'scope_type.required' => 'Please select a scope type for the inventory.',
            'scope_type.in' => 'Invalid scope type selected.',
            'scope_value.required_unless' => 'Please select a scope value when the scope type is not "All Books".',
        ];
    }
}
