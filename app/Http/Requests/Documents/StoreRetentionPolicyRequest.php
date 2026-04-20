<?php

namespace App\Http\Requests\Documents;

use App\Policies\DocumentPolicy;
use Illuminate\Foundation\Http\FormRequest;

class StoreRetentionPolicyRequest extends FormRequest {
    public function authorize(): bool {
        return DocumentPolicy::isAdmin($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'nullable|exists:document_categories,id',
            'retention_days' => 'required|integer|min:1',
            'grace_period_days' => 'required|integer|min:0',
            'action' => 'required|in:archive,delete,notify_owner',
            'is_active' => 'boolean',
        ];
    }
}
