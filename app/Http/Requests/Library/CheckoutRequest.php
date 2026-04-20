<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-library');
    }

    public function rules(): array {
        return [
            'accession_number' => ['required', 'string', 'exists:copies,accession_number'],
            'borrower_type' => ['required', 'string', 'in:student,user'],
            'borrower_id' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array {
        return [
            'accession_number.exists' => 'No copy found with this accession number.',
            'borrower_type.in' => 'Borrower type must be either student or staff.',
        ];
    }
}
