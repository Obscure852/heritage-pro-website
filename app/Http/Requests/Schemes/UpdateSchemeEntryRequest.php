<?php

namespace App\Http\Requests\Schemes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchemeEntryRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('update', $this->route('scheme'));
    }

    public function rules(): array {
        return [
            'syllabus_topic_id'    => ['sometimes', 'nullable', 'integer', 'exists:syllabus_topics,id'],
            'objective_ids'        => ['sometimes', 'array'],
            'objective_ids.*'      => ['integer', 'exists:syllabus_objectives,id'],
            'topic'                => ['sometimes', 'nullable', 'string', 'max:5000'],
            'sub_topic'            => ['sometimes', 'nullable', 'string', 'max:5000'],
            'learning_objectives'  => ['sometimes', 'nullable', 'string', 'max:5000'],
            'status'               => ['sometimes', 'string', Rule::in(['planned', 'in_progress', 'completed', 'skipped'])],
        ];
    }
}
