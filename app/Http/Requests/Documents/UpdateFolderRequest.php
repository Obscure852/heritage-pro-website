<?php

namespace App\Http\Requests\Documents;

use App\Models\DocumentFolder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFolderRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     * Authorization is handled by the controller/policy.
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
        $folder = $this->route('folder');
        $folderId = $folder instanceof DocumentFolder ? $folder->id : $folder;
        $parentId = $folder instanceof DocumentFolder ? $folder->parent_id : null;
        $ownerId = $folder instanceof DocumentFolder
            ? (int) $folder->owner_id
            : (int) ($this->user()->id ?? 0);
        $repositoryType = $folder instanceof DocumentFolder
            ? $folder->repository_type
            : DocumentFolder::REPOSITORY_PERSONAL;

        $nameUniqueRule = Rule::unique('document_folders', 'name')
            ->ignore($folderId)
            ->where(function ($query) use ($parentId, $ownerId, $repositoryType) {
                if ($parentId === null) {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $parentId);
                }

                $query->whereNull('deleted_at');

                if ($repositoryType === DocumentFolder::REPOSITORY_PERSONAL) {
                    $query->where('repository_type', DocumentFolder::REPOSITORY_PERSONAL)
                        ->where('owner_id', $ownerId);
                } else {
                    $query->where('repository_type', $repositoryType);
                }
            });

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[^\/\\\\:*?"<>|]+$/',
                $nameUniqueRule,
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array {
        return [
            'name.regex' => 'Folder name cannot contain / \\ : * ? " < > |',
            'name.unique' => 'A folder with this name already exists in the same location.',
        ];
    }
}
