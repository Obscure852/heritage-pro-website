<?php

namespace App\Http\Requests\Invigilation;

use App\Models\Invigilation\InvigilationSessionRoom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvigilationSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-invigilation') ?? false;
    }

    public function rules(): array
    {
        return [
            'grade_subject_id' => ['required', 'integer', 'exists:grade_subject,id'],
            'paper_label' => ['nullable', 'string', 'max:255'],
            'exam_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'day_of_cycle' => ['nullable', 'integer', 'min:1', 'max:6'],
            'notes' => ['nullable', 'string'],
            'initial_room_venue_id' => ['nullable', 'integer', 'exists:venues,id'],
            'initial_room_source_type' => ['nullable', Rule::in(array_keys(InvigilationSessionRoom::sourceTypes()))],
            'initial_room_klass_subject_id' => ['nullable', 'integer', 'exists:klass_subject,id'],
            'initial_room_optional_subject_id' => ['nullable', 'integer', 'exists:optional_subjects,id'],
            'initial_room_group_label' => ['nullable', 'string', 'max:255'],
            'initial_room_candidate_count' => ['nullable', 'integer', 'min:0'],
            'initial_room_required_invigilators' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }
}
