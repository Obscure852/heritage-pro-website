<?php

namespace App\Services\Crm;

use App\Http\Requests\Crm\CrmUserProfileRules;
use App\Models\CrmUserDepartment;
use App\Models\CrmUserPosition;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class CrmUserProfileService
{
    public function __construct(
        private readonly CrmUserMediaService $mediaService
    ) {
    }

    public function fullPayload(array $validated, ?User $user = null): array
    {
        return array_merge(
            $this->identityPayload($validated, $user),
            $this->workPayload($validated)
        );
    }

    public function syncFullProfile(User $user, array $validated): void
    {
        $user->forceFill($this->fullPayload($validated, $user))->save();
    }

    public function syncIdentity(User $user, array $validated): void
    {
        $user->forceFill($this->identityPayload($validated, $user))->save();
    }

    public function syncWork(User $user, array $validated): void
    {
        $user->forceFill($this->workPayload($validated))->save();
    }

    public function departmentOptions(): Collection
    {
        return CrmUserDepartment::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function positionOptions(): Collection
    {
        return CrmUserPosition::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function reportingUserOptions(?User $excludingUser = null): Collection
    {
        return User::query()
            ->where('active', true)
            ->when($excludingUser !== null, fn (Builder $query) => $query->whereKeyNot($excludingUser->id))
            ->orderBy('email')
            ->get($this->reportingUserSelectColumns());
    }

    public function canSkipIdentity(User $user): bool
    {
        $rules = Arr::except(CrmUserProfileRules::identity($user), ['avatar_cropped_image']);

        return Validator::make([
            'name' => $user->name,
            'email' => $user->email,
            'date_of_birth' => optional($user->date_of_birth)->format('Y-m-d'),
            'gender' => $user->gender,
            'nationality' => $user->nationality,
            'id_number' => $user->id_number,
            'phone' => $user->phone,
        ], $rules)->passes();
    }

    public function canSkipWork(User $user): bool
    {
        return Validator::make([
            'employment_status' => $user->employment_status,
            'department_id' => $user->department_id,
            'position_id' => $user->position_id,
            'reports_to_user_id' => $user->reports_to_user_id,
            'personal_payroll_number' => $user->personal_payroll_number,
            'date_of_appointment' => optional($user->date_of_appointment)->format('Y-m-d'),
        ], CrmUserProfileRules::work($user))->passes();
    }

    private function identityPayload(array $validated, ?User $user = null): array
    {
        return [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'],
            'nationality' => $validated['nationality'],
            'id_number' => $validated['id_number'],
            'phone' => $validated['phone'],
            'avatar_path' => $this->mediaService->storeAvatarFromCroppedImage(
                $validated['avatar_cropped_image'] ?? null,
                $user?->avatar_path
            ),
        ];
    }

    private function workPayload(array $validated): array
    {
        $payload = [
            'employment_status' => $validated['employment_status'],
            'department_id' => $validated['department_id'],
            'position_id' => $validated['position_id'],
            'reports_to_user_id' => $validated['reports_to_user_id'],
            'personal_payroll_number' => $validated['personal_payroll_number'] ?? null,
            'date_of_appointment' => $validated['date_of_appointment'],
        ];

        if (Schema::hasColumn('users', 'department')) {
            $payload['department'] = CrmUserDepartment::query()
                ->whereKey($validated['department_id'])
                ->value('name');
        }

        if (Schema::hasColumn('users', 'position')) {
            $payload['position'] = CrmUserPosition::query()
                ->whereKey($validated['position_id'])
                ->value('name');
        }

        if (Schema::hasColumn('users', 'reporting_to')) {
            $payload['reporting_to'] = $validated['reports_to_user_id'];
        }

        return $payload;
    }

    private function reportingUserSelectColumns(): array
    {
        return array_values(array_filter([
            'id',
            'email',
            'active',
            Schema::hasColumn('users', 'firstname') ? 'firstname' : null,
            Schema::hasColumn('users', 'lastname') ? 'lastname' : null,
            Schema::hasColumn('users', 'username') ? 'username' : null,
            Schema::hasColumn('users', 'name') ? 'name' : null,
        ]));
    }
}
