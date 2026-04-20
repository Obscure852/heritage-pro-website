<?php

namespace App\Http\Requests\StaffAttendance;

use App\Models\StaffAttendance\AttendanceDevice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for storing a new attendance device.
 */
class StoreDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $connectivityMode = $this->input('connectivity_mode', AttendanceDevice::MODE_PULL);
        $isPullMode = $connectivityMode === AttendanceDevice::MODE_PULL;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'type' => [
                'required',
                'string',
                Rule::in([
                    AttendanceDevice::TYPE_HIKVISION,
                    AttendanceDevice::TYPE_ZKTECO,
                ]),
            ],
            'connectivity_mode' => [
                'required',
                'string',
                Rule::in([
                    AttendanceDevice::MODE_PULL,
                    AttendanceDevice::MODE_PUSH,
                    AttendanceDevice::MODE_AGENT,
                ]),
            ],
            'ip_address' => [
                $isPullMode ? 'required' : 'nullable',
                'nullable',
                'ip',
            ],
            'port' => [
                $isPullMode ? 'required' : 'nullable',
                'nullable',
                'integer',
                'min:1',
                'max:65535',
            ],
            'username' => [
                $isPullMode ? 'required' : 'nullable',
                'nullable',
                'string',
                'max:255',
            ],
            'password' => [
                $isPullMode ? 'required' : 'nullable',
                'nullable',
                'string',
                'max:255',
            ],
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
            ],
            'location' => [
                'nullable',
                'string',
                'max:255',
            ],
            'timezone' => [
                'required',
                'string',
                'timezone',
            ],
            'is_active' => [
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Device name is required.',
            'name.max' => 'Device name cannot exceed 255 characters.',
            'type.required' => 'Please select a device type.',
            'type.in' => 'The selected device type is invalid.',
            'connectivity_mode.required' => 'Please select a connectivity mode.',
            'connectivity_mode.in' => 'The selected connectivity mode is invalid.',
            'ip_address.required' => 'IP address is required for pull mode.',
            'ip_address.ip' => 'Please enter a valid IP address.',
            'port.required' => 'Port number is required for pull mode.',
            'port.integer' => 'Port must be a number.',
            'port.min' => 'Port must be at least 1.',
            'port.max' => 'Port cannot exceed 65535.',
            'username.required' => 'Username is required for pull mode.',
            'username.max' => 'Username cannot exceed 255 characters.',
            'password.required' => 'Password is required for pull mode.',
            'password.max' => 'Password cannot exceed 255 characters.',
            'serial_number.max' => 'Serial number cannot exceed 255 characters.',
            'location.max' => 'Location cannot exceed 255 characters.',
            'timezone.required' => 'Please select a timezone.',
            'timezone.timezone' => 'Please select a valid timezone.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default for is_active if not provided
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }

        // Set default connectivity mode if not provided
        if (!$this->has('connectivity_mode')) {
            $this->merge(['connectivity_mode' => AttendanceDevice::MODE_PULL]);
        }
    }
}
