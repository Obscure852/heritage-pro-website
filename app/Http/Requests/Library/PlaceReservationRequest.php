<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class PlaceReservationRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-library') || $this->user()->can('access-library');
    }

    public function rules(): array {
        return [
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'borrower_type' => ['required', 'string', 'in:student,user'],
            'borrower_id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array {
        return [
            'book_id.exists' => 'No book found with this ID.',
            'borrower_type.in' => 'Borrower type must be either student or staff.',
        ];
    }
}
