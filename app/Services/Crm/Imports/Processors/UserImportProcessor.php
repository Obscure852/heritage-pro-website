<?php

namespace App\Services\Crm\Imports\Processors;

use App\Models\CrmUserDepartment;
use App\Models\CrmUserFilter;
use App\Models\CrmUserPosition;
use App\Models\CrmImportRun;
use App\Models\CrmImportRunRow;
use App\Models\User;
use App\Services\Crm\Imports\Contracts\CrmImportEntityProcessor;
use App\Services\Crm\CrmModulePermissionService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserImportProcessor extends AbstractCrmImportProcessor implements CrmImportEntityProcessor
{
    public function __construct(
        private readonly CrmModulePermissionService $modulePermissionService
    ) {
    }

    public function entity(): string
    {
        return 'users';
    }

    public function previewRow(array $row, User $initiator): array
    {
        $payload = [
            'name' => $this->normalizeString($row['name'] ?? null),
            'email' => Str::lower((string) $this->normalizeString($row['email'] ?? null)),
            'role' => $this->normalizeRole($row['role'] ?? null),
            'active' => $this->normalizeBoolean($row['active'] ?? null),
            'date_of_birth' => $this->normalizeDate($row['date_of_birth'] ?? null),
            'gender' => $this->normalizeString($row['gender'] ?? null),
            'nationality' => $this->normalizeString($row['nationality'] ?? null),
            'id_number' => $this->normalizeString($row['id_number'] ?? null),
            'phone' => $this->normalizeString($row['phone'] ?? null),
            'employment_status' => $this->normalizeString($row['employment_status'] ?? null),
            'department' => $this->normalizeString($row['department'] ?? null),
            'position' => $this->normalizeString($row['position'] ?? null),
            'reports_to_email' => Str::lower((string) $this->normalizeString($row['reports_to_email'] ?? null)),
            'personal_payroll_number' => $this->normalizeString($row['personal_payroll_number'] ?? null),
            'date_of_appointment' => $this->normalizeDate($row['date_of_appointment'] ?? null),
            'custom_filters' => $this->normalizeDelimitedList($row['custom_filters'] ?? null),
        ];

        $errors = $this->validationErrors($payload, [
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160'],
            'role' => ['required', Rule::in(array_keys(config('heritage_crm.roles', [])))],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(array_keys(config('heritage_crm.user_genders', [])))],
            'nationality' => ['nullable', 'string', 'max:120'],
            'id_number' => ['nullable', 'string', 'max:80'],
            'phone' => ['nullable', 'string', 'max:40'],
            'employment_status' => ['nullable', Rule::in(array_keys(config('heritage_crm.user_employment_statuses', [])))],
            'department' => ['nullable', 'string', 'max:160'],
            'position' => ['nullable', 'string', 'max:160'],
            'reports_to_email' => ['nullable', 'email', 'max:160'],
            'personal_payroll_number' => ['nullable', 'string', 'max:80'],
            'date_of_appointment' => ['nullable', 'date'],
        ]);

        if (($row['active'] ?? null) !== null && $row['active'] !== '' && $payload['active'] === null) {
            $errors[] = 'Active must be a valid boolean value.';
        }

        if (($row['date_of_birth'] ?? null) !== null && $row['date_of_birth'] !== '' && $payload['date_of_birth'] === null) {
            $errors[] = 'Date of birth must use DD/MM/YYYY format.';
        }

        if (($row['date_of_appointment'] ?? null) !== null && $row['date_of_appointment'] !== '' && $payload['date_of_appointment'] === null) {
            $errors[] = 'Date of appointment must use DD/MM/YYYY format.';
        }

        if ($payload['reports_to_email']) {
            $reportsTo = User::query()->where('email', $payload['reports_to_email'])->whereNull('deleted_at')->first();

            if (! $reportsTo) {
                $errors[] = 'Reporting manager email [' . $payload['reports_to_email'] . '] does not match an existing user.';
            }
        }

        $existing = $payload['email'] !== ''
            ? User::withTrashed()->where('email', $payload['email'])->first()
            : null;

        return [
            'normalized_key' => $payload['email'] ?: null,
            'payload' => $payload,
            'action' => $errors === [] ? ($existing ? 'update' : 'create') : 'error',
            'validation_errors' => $errors,
        ];
    }

    public function processRow(CrmImportRun $run, CrmImportRunRow $row): array
    {
        $payload = $row->payload ?? [];
        $user = User::withTrashed()->where('email', $payload['email'])->first();

        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }

            $user->update([
                'name' => $payload['name'],
                'role' => $payload['role'],
                'active' => $payload['active'] ?? $user->active,
                'date_of_birth' => $payload['date_of_birth'],
                'gender' => $payload['gender'],
                'nationality' => $payload['nationality'],
                'id_number' => $payload['id_number'],
                'phone' => $payload['phone'],
                'employment_status' => $payload['employment_status'],
                'department_id' => $this->departmentId($payload['department']),
                'position_id' => $this->positionId($payload['position']),
                'reports_to_user_id' => $this->reportsToId($payload['reports_to_email']),
                'personal_payroll_number' => $payload['personal_payroll_number'],
                'date_of_appointment' => $payload['date_of_appointment'],
            ]);
            $user->customFilters()->sync($this->customFilterIds($payload['custom_filters'] ?? []));

            return [
                'action' => 'update',
                'record_id' => $user->id,
            ];
        }

        $temporaryPassword = $this->temporaryPassword();

        $user = User::query()->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => Hash::make($temporaryPassword),
            'role' => $payload['role'],
            'active' => $payload['active'] ?? true,
            'date_of_birth' => $payload['date_of_birth'],
            'gender' => $payload['gender'],
            'nationality' => $payload['nationality'],
            'id_number' => $payload['id_number'],
            'phone' => $payload['phone'],
            'employment_status' => $payload['employment_status'],
            'department_id' => $this->departmentId($payload['department']),
            'position_id' => $this->positionId($payload['position']),
            'reports_to_user_id' => $this->reportsToId($payload['reports_to_email']),
            'personal_payroll_number' => $payload['personal_payroll_number'],
            'date_of_appointment' => $payload['date_of_appointment'],
        ]);
        $user->customFilters()->sync($this->customFilterIds($payload['custom_filters'] ?? []));
        $this->modulePermissionService->syncDefaultsForRole($user);

        return [
            'action' => 'create',
            'record_id' => $user->id,
            'password_result' => [
                'name' => $user->name,
                'email' => $user->email,
                'temporary_password' => $temporaryPassword,
                'role' => $user->role,
            ],
        ];
    }

    private function temporaryPassword(): string
    {
        return Str::random(10) . random_int(10, 99) . '!';
    }

    private function normalizeRole(mixed $value): ?string
    {
        $normalized = $this->normalizeString($value);

        if ($normalized === null) {
            return null;
        }

        $candidate = $this->canonicalizeRoleValue($normalized);

        foreach (config('heritage_crm.roles', []) as $key => $label) {
            if ($candidate === $this->canonicalizeRoleValue($key) || $candidate === $this->canonicalizeRoleValue($label)) {
                return $key;
            }
        }

        return null;
    }

    private function canonicalizeRoleValue(string $value): string
    {
        return (string) Str::of($value)
            ->lower()
            ->replace([' ', '-', '_'], '');
    }

    private function departmentId(?string $name): ?int
    {
        if (! is_string($name) || $name === '') {
            return null;
        }

        $department = CrmUserDepartment::query()->firstOrCreate(
            ['name' => $name],
            [
                'sort_order' => CrmUserDepartment::query()->count() + 1,
                'is_active' => true,
            ]
        );

        return $department->id;
    }

    private function positionId(?string $name): ?int
    {
        if (! is_string($name) || $name === '') {
            return null;
        }

        $position = CrmUserPosition::query()->firstOrCreate(
            ['name' => $name],
            [
                'sort_order' => CrmUserPosition::query()->count() + 1,
                'is_active' => true,
            ]
        );

        return $position->id;
    }

    private function reportsToId(?string $email): ?int
    {
        if (! is_string($email) || $email === '') {
            return null;
        }

        return User::query()
            ->where('email', $email)
            ->whereNull('deleted_at')
            ->value('id');
    }

    private function customFilterIds(array $filterNames): array
    {
        return collect($filterNames)
            ->map(function ($name) {
                if (! is_string($name) || $name === '') {
                    return null;
                }

                $filter = CrmUserFilter::query()->firstOrCreate(
                    ['name' => $name],
                    [
                        'sort_order' => CrmUserFilter::query()->count() + 1,
                        'is_active' => true,
                    ]
                );

                return $filter->id;
            })
            ->filter()
            ->values()
            ->all();
    }
}
