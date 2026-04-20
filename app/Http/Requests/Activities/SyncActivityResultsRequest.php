<?php

namespace App\Http\Requests\Activities;

use App\Models\Activities\ActivityResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncActivityResultsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scope' => ['required', Rule::in(array_keys(ActivityResult::participantTypes()))],
            'results' => ['nullable', 'array'],
            'results.*.selected' => ['nullable', 'boolean'],
            'results.*.result_label' => ['nullable', 'string', 'max:255'],
            'results.*.placement' => ['nullable', 'integer', 'min:1', 'max:999'],
            'results.*.points' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'results.*.award_name' => ['nullable', 'string', 'max:255'],
            'results.*.score_value' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'results.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
