<?php

namespace App\Http\Requests\Crm;

use App\Models\User;
use Illuminate\Validation\Rule;

class CrmUserProfileRules
{
    public static function identity(?User $user = null): array
    {
        $emailRule = Rule::unique('users', 'email')->whereNull('deleted_at');

        if ($user?->id !== null) {
            $emailRule = $emailRule->ignore($user->id);
        }

        return [
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160', $emailRule],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', Rule::in(array_keys(config('heritage_crm.user_genders', [])))],
            'nationality' => ['required', 'string', 'max:120'],
            'id_number' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:40'],
            'avatar_cropped_image' => ['nullable', 'string'],
        ];
    }

    public static function work(?User $user = null): array
    {
        $reportsToRules = ['required', 'integer', 'exists:users,id'];

        if ($user?->id !== null) {
            $reportsToRules[] = Rule::notIn([$user->id]);
        }

        return [
            'employment_status' => ['required', Rule::in(array_keys(config('heritage_crm.user_employment_statuses', [])))],
            'department_id' => ['required', 'integer', 'exists:crm_user_departments,id'],
            'position_id' => ['required', 'integer', 'exists:crm_user_positions,id'],
            'reports_to_user_id' => $reportsToRules,
            'personal_payroll_number' => ['nullable', 'string', 'max:80'],
            'date_of_appointment' => ['required', 'date'],
        ];
    }
}
