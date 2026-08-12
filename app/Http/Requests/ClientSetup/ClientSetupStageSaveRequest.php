<?php

namespace App\Http\Requests\ClientSetup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientSetupStageSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data' => ['required_without_all:payload,payload_json', 'array', 'max:500'],
            'payload' => ['required_without_all:data,payload_json', 'array', 'max:200'],
            'payload_json' => ['required_without_all:data,payload', 'nullable', 'string', 'max:100000'],
            'status' => ['required', Rule::in(config('client_setup.stage_statuses', []))],
            'action' => ['sometimes', Rule::in(['save', 'continue', 'exit'])],
        ];
    }
}
