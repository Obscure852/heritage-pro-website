<?php

namespace App\Http\Requests\Documents;

use App\Services\Documents\DocumentSettingService;
use Illuminate\Foundation\Http\FormRequest;

class StoreVersionRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * Delegates to DocumentPolicy::uploadVersion which blocks uploads
     * when document status is pending_review or under_review (VER-08).
     */
    public function authorize(): bool {
        return $this->user()->can('uploadVersion', $this->route('document'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array {
        $settingService = app(DocumentSettingService::class);
        $maxKb = $settingService->get('storage.max_file_size_mb', 50) * 1024;
        $allowedExtensions = array_values(array_filter(array_map(
            static fn($extension) => strtolower(trim((string) $extension)),
            (array) $settingService->get('allowed_extensions', config('documents.allowed_extensions', []))
        )));

        $fileRules = ['required', 'file', 'max:' . $maxKb];
        if (!empty($allowedExtensions)) {
            $fileRules[] = 'mimes:' . implode(',', $allowedExtensions);
        }

        return [
            'file' => $fileRules,
            'version_type' => ['required', 'in:major,minor'],
            'version_notes' => ['nullable', 'string', 'max:5000'],
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
        $extensions = implode(', ', (array) $settingService->get('allowed_extensions', config('documents.allowed_extensions', [])));

        return [
            'file.required' => 'Please select a file to upload.',
            'file.max' => "The file must not exceed {$maxMb}MB.",
            'file.mimes' => "Only the following file types are allowed: {$extensions}.",
            'version_type.required' => 'Please select a version type (major or minor).',
            'version_type.in' => 'Version type must be either major or minor.',
        ];
    }
}
