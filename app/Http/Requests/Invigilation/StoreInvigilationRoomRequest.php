<?php

namespace App\Http\Requests\Invigilation;

use App\Models\Invigilation\InvigilationSessionRoom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvigilationRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-invigilation') ?? false;
    }

    public function rules(): array
    {
        return [
            'venue_id' => ['required', 'integer', 'exists:venues,id'],
            'source_type' => ['required', Rule::in(array_keys(InvigilationSessionRoom::sourceTypes()))],
            'klass_subject_id' => ['nullable', 'integer', 'exists:klass_subject,id'],
            'optional_subject_id' => ['nullable', 'integer', 'exists:optional_subjects,id'],
            'group_label' => ['nullable', 'string', 'max:255'],
            'candidate_count' => ['nullable', 'integer', 'min:0'],
            'required_invigilators' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }
}
