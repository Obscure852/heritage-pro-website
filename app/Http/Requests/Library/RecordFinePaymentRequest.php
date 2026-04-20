<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class RecordFinePaymentRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-library');
    }

    public function rules(): array {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array {
        return [
            'amount.required' => 'Payment amount is required.',
            'amount.min' => 'Payment amount must be at least P0.01.',
            'amount.max' => 'Payment amount cannot exceed P999,999.99.',
        ];
    }
}
