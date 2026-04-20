<?php

namespace App\Http\Requests\Documents;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return $this->user()->can('update', $this->route('document'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array {
        $document = $this->route('document');

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'integer', 'exists:document_categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['string'],
            'expiry_date' => ['nullable', 'date', 'after:today'],
            'external_url' => [
                $document && $document->isExternalUrl() ? 'required' : 'nullable',
                'url',
                'starts_with:http://,https://',
                'max:2048',
            ],
        ];
    }
}
