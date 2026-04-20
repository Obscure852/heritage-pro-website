<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class BulkCheckinRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-library');
    }

    public function rules(): array {
        return [
            'accession_numbers' => ['required', 'array', 'min:1', 'max:50'],
            'accession_numbers.*' => ['required', 'string', 'exists:copies,accession_number'],
        ];
    }

    public function messages(): array {
        return [
            'accession_numbers.required' => 'At least one accession number is required.',
            'accession_numbers.max' => 'Cannot process more than 50 books at once.',
            'accession_numbers.*.exists' => 'No copy found with accession number :input.',
        ];
    }
}
