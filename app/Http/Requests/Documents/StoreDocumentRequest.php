<?php

namespace App\Http\Requests\Documents;

use App\Services\Documents\DocumentSettingService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return $this->user()->can('create', \App\Models\Document::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array {
        $settingService = app(DocumentSettingService::class);
        $maxKb = $settingService->get('storage.max_file_size_mb', 50) * 1024;
        $allowedExtensions = implode(',', $settingService->get('allowed_extensions', []));
        $sourceType = $this->input('source_type', \App\Models\Document::SOURCE_UPLOAD);

        return [
            'source_type' => ['nullable', Rule::in([\App\Models\Document::SOURCE_UPLOAD, \App\Models\Document::SOURCE_EXTERNAL_URL])],
            'file' => array_values(array_filter([
                $sourceType === \App\Models\Document::SOURCE_UPLOAD ? 'required' : 'nullable',
                'file',
                'max:' . $maxKb,
                $allowedExtensions !== '' ? 'mimes:' . $allowedExtensions : null,
            ])),
            'external_url' => [
                $sourceType === \App\Models\Document::SOURCE_EXTERNAL_URL ? 'required' : 'nullable',
                'url',
                'starts_with:http://,https://',
                'max:2048',
            ],
            'title' => [
                $sourceType === \App\Models\Document::SOURCE_EXTERNAL_URL ? 'required' : 'nullable',
                'string',
                'max:255',
            ],
            'preserve_original_name' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:5000'],
            'folder_id' => [
                'nullable',
                'integer',
                Rule::exists('document_folders', 'id')->whereNull('deleted_at'),
            ],
            'category_id' => ['nullable', 'integer', 'exists:document_categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:document_tags,id'],
            'new_tags' => ['nullable', 'array'],
            'new_tags.*' => ['string', 'max:100'],
            'expiry_date' => ['nullable', 'date', 'after:today'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        $settingService = app(DocumentSettingService::class);
        $maxMb = $settingService->get('storage.max_file_size_mb', 50);
        $extensions = implode(', ', $settingService->get('allowed_extensions', []));

        return [
            'file.required' => 'Please select a file to upload.',
            'file.max' => "The file must not exceed {$maxMb}MB.",
            'file.mimes' => "Only the following file types are allowed: {$extensions}.",
            'external_url.required' => 'Please provide the online document URL.',
            'external_url.url' => 'Please provide a valid URL.',
            'external_url.starts_with' => 'Please provide a valid HTTP or HTTPS URL.',
            'title.required' => 'Please provide a title for the online document.',
        ];
    }

    /**
     * Force JSON validation payloads for AJAX uploads used by Dropzone.
     */
    protected function failedValidation(Validator $validator): void {
        if ($this->expectsJson() || $this->ajax()) {
            throw new HttpResponseException(response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
