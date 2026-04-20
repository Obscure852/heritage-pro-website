<?php

namespace App\Http\Requests\Documents;

use App\Models\DocumentFolder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFolderRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return $this->user()?->can('create', DocumentFolder::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        $parentId = $this->filled('parent_id') ? (int) $this->input('parent_id') : null;
        $ownerId = (int) ($this->user()->id ?? 0);
        $repositoryType = $this->input('repository_type', DocumentFolder::REPOSITORY_PERSONAL);

        if ($parentId !== null) {
            $parentFolder = DocumentFolder::select('repository_type')->find($parentId);
            if ($parentFolder) {
                $repositoryType = $parentFolder->repository_type;
            }
        }

        $nameUniqueRule = Rule::unique('document_folders', 'name')
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
            'parent_id' => ['nullable', 'integer', 'exists:document_folders,id'],
            'repository_type' => ['nullable', 'string', Rule::in([
                'institutional', 'personal', 'shared', 'department',
            ])],
            'access_scope' => ['nullable', 'string', Rule::in(['private', 'public'])],
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
