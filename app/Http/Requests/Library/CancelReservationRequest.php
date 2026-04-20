<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class CancelReservationRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('manage-library');
    }

    public function rules(): array {
        return [
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
