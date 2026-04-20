<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class BulkCheckoutRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-library');
    }

    public function rules(): array {
        return [
            'accession_numbers' => ['required', 'array', 'min:1', 'max:20'],
            'accession_numbers.*' => ['required', 'string', 'exists:copies,accession_number'],
            'borrower_type' => ['required', 'string', 'in:student,user'],
            'borrower_id' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array {
        return [
            'accession_numbers.required' => 'At least one accession number is required.',
            'accession_numbers.max' => 'Cannot process more than 20 books at once.',
            'accession_numbers.*.exists' => 'No copy found with accession number :input.',
            'borrower_type.in' => 'Borrower type must be either student or staff.',
        ];
    }
}
