<?php

namespace App\Http\Requests\Documents;

use Illuminate\Foundation\Http\FormRequest;

class SubmitForReviewRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
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
            'reviewer_ids' => ['required', 'array', 'min:1'],
            'reviewer_ids.*' => ['required', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'deadline' => ['nullable', 'date', 'after:today'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * Adds custom validation to prevent the document owner from being assigned as a reviewer (WFL-06).
     */
    public function withValidator($validator): void {
        $validator->after(function ($validator) {
            $document = $this->route('document');
            $reviewerIds = $this->input('reviewer_ids', []);

            if ($document && in_array($document->owner_id, array_map('intval', $reviewerIds), true)) {
                $validator->errors()->add('reviewer_ids', 'The document owner cannot be assigned as a reviewer.');
            }
        });
    }
}
