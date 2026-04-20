<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class CheckinRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-library');
    }

    public function rules(): array {
        return [
            'accession_number' => ['required', 'string', 'exists:copies,accession_number'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array {
        return [
            'accession_number.exists' => 'No copy found with this accession number.',
        ];
    }
}
