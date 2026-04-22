<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\CrmUserQualificationUpsertRequest;
use App\Http\Requests\Crm\CrmUserRoleAllocationRequest;
use App\Http\Requests\Crm\CrmUserSignatureStoreRequest;
use App\Http\Requests\Crm\CrmUserStoreRequest;
use App\Http\Requests\Crm\CrmUserUpdateRequest;
use App\Models\CrmUserDepartment;
use App\Models\CrmUserFilter;
use App\Models\CrmUserModulePermission;
use App\Models\CrmUserPosition;
use App\Models\CrmUserQualification;
use App\Models\CrmUserQualificationAttachment;
use App\Models\CrmUserSignature;
use App\Models\User;
use App\Services\Crm\CrmModulePermissionService;
use App\Services\Crm\CrmUserMediaService;
use App\Services\Crm\CrmUserProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController extends CrmController
{
    public function __construct(
        private readonly CrmModulePermissionService $modulePermissionService,
        private readonly CrmUserMediaService $mediaService,
        private readonly CrmUserProfileService $profileService
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorizeModuleAccess('users', 'view');

        $filters = [
            'name' => trim((string) $request->query('name', '')),
            'department_id' => (string) $request->query('department_id', ''),
            'position_id' => (string) $request->query('position_id', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $userStatsQuery = User::query();
        $userStats = [
            ['label' => 'Total', 'value' => (clone $userStatsQuery)->count()],
            ['label' => 'Active', 'value' => (clone $userStatsQuery)->where('active', true)->count()],
            ['label' => 'Admin', 'value' => (clone $userStatsQuery)->where('role', 'admin')->count()],
            ['label' => 'Finance', 'value' => (clone $userStatsQuery)->where('role', 'finance')->count()],
            ['label' => 'Manager', 'value' => (clone $userStatsQuery)->where('role', 'manager')->count()],
            ['label' => 'Rep', 'value' => (clone $userStatsQuery)->where('role', 'rep')->count()],
        ];

        $users = User::query()
            ->with([
                'department:id,name',
                'position:id,name',
                'reportsTo' => fn ($query) => $query->select($this->userIdentitySelectColumns()),
                'customFilters:id,name',
            ])
            ->when($filters['name'] !== '', function (Builder $query) use ($filters) {
                $query->where(function (Builder $userQuery) use ($filters) {
                    $this->applyUserSearch($userQuery, $filters['name']);

                    $userQuery->orWhereHas('department', fn (Builder $departmentQuery) => $departmentQuery->where('name', 'like', '%' . $filters['name'] . '%'))
                        ->orWhereHas('position', fn (Builder $positionQuery) => $positionQuery->where('name', 'like', '%' . $filters['name'] . '%'));
                });
            })
            ->when($filters['department_id'] !== '', fn (Builder $query) => $query->where('department_id', (int) $filters['department_id']))
            ->when($filters['position_id'] !== '', fn (Builder $query) => $query->where('position_id', (int) $filters['position_id']))
            ->when($filters['status'] !== '', fn (Builder $query) => $query->where('employment_status', $filters['status']))
            ->tap(fn (Builder $query) => $this->applyUserOrdering($query))
            ->paginate(12)
            ->withQueryString();

        return view('crm.users.index', [
            'users' => $users,
            'roles' => config('heritage_crm.roles'),
            'filters' => $filters,
            'userStats' => $userStats,
            'departments' => $this->profileService->departmentOptions(),
            'positions' => $this->profileService->positionOptions(),
            'employmentStatuses' => config('heritage_crm.user_employment_statuses'),
            'canCreateUsers' => $this->crmUser()->canAccessCrmModule('users', 'edit'),
            'canAdminUsers' => $this->crmUser()->canAccessCrmModule('users', 'admin'),
        ]);
    }

    public function create(): View
    {
        $this->authorizeUserModuleEdit();

        return view('crm.users.create', $this->directoryFormData());
    }

    public function store(CrmUserStoreRequest $request): RedirectResponse
    {
        $this->authorizeUserModuleEdit();

        $user = DB::transaction(function () use ($request) {
            $user = User::query()->create([
                ...$this->profileService->fullPayload($request->validated()),
                'password' => Hash::make(Str::random(32)),
                'role' => $request->validated('role'),
                'active' => $request->boolean('active', true),
            ]);

            $user->customFilters()->sync($request->validated('custom_filter_ids', []));
            $this->modulePermissionService->syncDefaultsForRole($user);

            return $user->fresh(['customFilters']);
        });

        return redirect()
            ->route('crm.users.edit', ['user' => $user, 'tab' => 'profile'])
            ->with('crm_success', 'CRM user created successfully.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeModuleAccess('users', 'view');

        $activeTab = $this->normalizeTab($request->query('tab'));
        $qualification = null;
        $qualificationId = (int) $request->query('qualification', 0);

        if ($qualificationId > 0) {
            $qualification = $user->qualifications()
                ->with([
                    'attachments.uploadedBy' => fn ($query) => $query->select($this->userIdentitySelectColumns()),
                ])
                ->findOrFail($qualificationId);
        }

        $user->load([
            'department:id,name',
            'position:id,name',
            'reportsTo' => fn ($query) => $query->select($this->userIdentitySelectColumns()),
            'customFilters:id,name',
            'qualifications.attachments.uploadedBy' => fn ($query) => $query->select($this->userIdentitySelectColumns()),
            'signatures.uploadedBy' => fn ($query) => $query->select($this->userIdentitySelectColumns()),
            'modulePermissions',
        ]);

        $loginEvents = $user->loginEvents()->paginate(10, ['*'], 'login_page');

        return view('crm.users.edit', [
            'user' => $user,
            'roles' => config('heritage_crm.roles'),
            'departments' => $this->profileService->departmentOptions(),
            'positions' => $this->profileService->positionOptions(),
            'reportingUsers' => $this->profileService->reportingUserOptions($user),
            'customFilters' => $this->customFilterOptions(),
            'employmentStatuses' => config('heritage_crm.user_employment_statuses'),
            'genders' => config('heritage_crm.user_genders'),
            'permissionChoices' => $this->modulePermissionService->permissionChoices(),
            'modules' => $this->modulePermissionService->modules()->values(),
            'modulePermissionLevels' => $this->modulePermissionLevelsFor($user),
            'activeTab' => $activeTab,
            'editingQualification' => $qualification,
            'loginEvents' => $loginEvents,
            'loginEventTypes' => config('heritage_crm.user_login_event_types'),
            'canEditUser' => $this->crmUser()->canAccessCrmModule('users', 'edit'),
            'canAdminUsers' => $this->crmUser()->canAccessCrmModule('users', 'admin'),
        ]);
    }

    public function update(CrmUserUpdateRequest $request, User $user): RedirectResponse
    {
        $this->authorizeUserModuleEdit();

        DB::transaction(function () use ($request, $user) {
            $payload = $this->profileService->fullPayload($request->validated(), $user);

            if ($this->crmUser()->canManageCrmUsers()) {
                $payload['active'] = $request->boolean('active', $user->active);
            }

            $user->update($payload);
            $user->customFilters()->sync($request->validated('custom_filter_ids', []));
        });

        return redirect()
            ->route('crm.users.edit', ['user' => $user, 'tab' => 'profile'])
            ->with('crm_success', 'Profile updated successfully.');
    }

    public function updateRoles(CrmUserRoleAllocationRequest $request, User $user): RedirectResponse
    {
        $this->authorizeAdminUsers();

        DB::transaction(function () use ($request, $user) {
            $user->update([
                'role' => $request->validated('role'),
            ]);

            $this->modulePermissionService->syncPermissions($user, $request->validated('module_permissions', []));
        });

        return redirect()
            ->route('crm.users.edit', ['user' => $user, 'tab' => 'roles'])
            ->with('crm_success', 'Role allocation updated successfully.');
    }

    public function storeQualification(CrmUserQualificationUpsertRequest $request, User $user): RedirectResponse
    {
        $this->authorizeUserModuleEdit();

        DB::transaction(function () use ($request, $user) {
            $qualification = $user->qualifications()->create($request->safe()->except('attachments'));
            $this->mediaService->storeQualificationAttachments($qualification, $request->file('attachments', []), $this->crmUser());
        });

        return redirect()
            ->route('crm.users.edit', ['user' => $user, 'tab' => 'qualifications'])
            ->with('crm_success', 'Qualification added successfully.');
    }

    public function updateQualification(
        CrmUserQualificationUpsertRequest $request,
        User $user,
        CrmUserQualification $qualification
    ): RedirectResponse {
        $this->authorizeUserModuleEdit();
        $this->assertQualificationOwnership($user, $qualification);

        DB::transaction(function () use ($request, $qualification) {
            $qualification->update($request->safe()->except('attachments'));
            $this->mediaService->storeQualificationAttachments($qualification, $request->file('attachments', []), $this->crmUser());
        });

        return redirect()
            ->route('crm.users.edit', ['user' => $user, 'tab' => 'qualifications', 'qualification' => $qualification->id])
            ->with('crm_success', 'Qualification updated successfully.');
    }

    public function destroyQualification(User $user, CrmUserQualification $qualification): RedirectResponse
    {
        $this->authorizeUserModuleEdit();
        $this->assertQualificationOwnership($user, $qualification);
        $qualification->load('attachments');

        foreach ($qualification->attachments as $attachment) {
            $this->mediaService->deleteDocument($attachment->disk, $attachment->path);
        }

        $qualification->delete();

        return redirect()
            ->route('crm.users.edit', ['user' => $user, 'tab' => 'qualifications'])
            ->with('crm_success', 'Qualification deleted permanently.');
    }

    public function openQualificationAttachment(
        User $user,
        CrmUserQualification $qualification,
        CrmUserQualificationAttachment $attachment
    ): BinaryFileResponse {
        $this->authorizeModuleAccess('users', 'view');
        $this->assertQualificationAttachmentOwnership($user, $qualification, $attachment);

        return response()->file(
            $this->mediaService->absoluteDocumentPath($attachment->disk, $attachment->path),
            [
                'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . addslashes($attachment->original_name) . '"',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function downloadQualificationAttachment(
        User $user,
        CrmUserQualification $qualification,
        CrmUserQualificationAttachment $attachment
    ): BinaryFileResponse {
        $this->authorizeModuleAccess('users', 'view');
        $this->assertQualificationAttachmentOwnership($user, $qualification, $attachment);

        return response()->download(
            $this->mediaService->absoluteDocumentPath($attachment->disk, $attachment->path),
            $attachment->original_name,
            [
                'Content-Type' => $attachment->mime_type ?: 'application/octet-stream',
            ]
        );
    }

    public function destroyQualificationAttachment(
        User $user,
        CrmUserQualification $qualification,
        CrmUserQualificationAttachment $attachment
    ): RedirectResponse {
        $this->authorizeUserModuleEdit();
        $this->assertQualificationAttachmentOwnership($user, $qualification, $attachment);

        $this->mediaService->deleteDocument($attachment->disk, $attachment->path);
        $attachment->delete();

        return redirect()
            ->route('crm.users.edit', ['user' => $user, 'tab' => 'qualifications', 'qualification' => $qualification->id])
            ->with('crm_success', 'Qualification attachment deleted permanently.');
    }

    public function storeSignature(CrmUserSignatureStoreRequest $request, User $user): RedirectResponse
    {
        $this->authorizeUserModuleEdit();

        $this->mediaService->storeSignature(
            $user,
            $request->file('file'),
            $request->validated('label'),
            $this->crmUser()
        );

        return redirect()
            ->route('crm.users.edit', ['user' => $user, 'tab' => 'settings'])
            ->with('crm_success', 'Signature uploaded successfully.');
    }

    public function setDefaultSignature(User $user, CrmUserSignature $signature): RedirectResponse
    {
        $this->authorizeUserModuleEdit();
        $this->assertSignatureOwnership($user, $signature);

        DB::transaction(function () use ($user, $signature) {
            $user->signatures()->update(['is_default' => false]);
            $signature->update(['is_default' => true]);
        });

        return redirect()
            ->route('crm.users.edit', ['user' => $user, 'tab' => 'settings'])
            ->with('crm_success', 'Default signature updated successfully.');
    }

    public function openSignature(User $user, CrmUserSignature $signature): BinaryFileResponse
    {
        $this->authorizeModuleAccess('users', 'view');
        $this->assertSignatureOwnership($user, $signature);

        return response()->file(
            $this->mediaService->absoluteDocumentPath($signature->disk, $signature->path),
            [
                'Content-Type' => $signature->mime_type ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . addslashes($signature->original_name) . '"',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function downloadSignature(User $user, CrmUserSignature $signature): BinaryFileResponse
    {
        $this->authorizeModuleAccess('users', 'view');
        $this->assertSignatureOwnership($user, $signature);

        return response()->download(
            $this->mediaService->absoluteDocumentPath($signature->disk, $signature->path),
            $signature->original_name,
            [
                'Content-Type' => $signature->mime_type ?: 'application/octet-stream',
            ]
        );
    }

    public function destroySignature(User $user, CrmUserSignature $signature): RedirectResponse
    {
        $this->authorizeUserModuleEdit();
        $this->assertSignatureOwnership($user, $signature);

        DB::transaction(function () use ($user, $signature) {
            $this->mediaService->deleteDocument($signature->disk, $signature->path);
            $wasDefault = $signature->is_default;
            $signature->delete();

            if ($wasDefault) {
                $fallback = $user->signatures()->latest('id')->first();

                if ($fallback) {
                    $fallback->update(['is_default' => true]);
                }
            }
        });

        return redirect()
            ->route('crm.users.edit', ['user' => $user, 'tab' => 'settings'])
            ->with('crm_success', 'Signature deleted permanently.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeAdminUsers();

        if ($this->crmUser()->is($user)) {
            return redirect()
                ->route('crm.users.edit', $user)
                ->with('crm_error', 'You cannot delete the account you are currently using.');
        }

        $user->load([
            'qualifications.attachments',
            'signatures',
        ]);

        foreach ($user->qualifications as $qualification) {
            foreach ($qualification->attachments as $attachment) {
                $this->mediaService->deleteDocument($attachment->disk, $attachment->path);
            }
        }

        foreach ($user->signatures as $signature) {
            $this->mediaService->deleteDocument($signature->disk, $signature->path);
        }

        $this->mediaService->deleteAvatar($user->avatar_path);
        $user->forceDelete();

        return redirect()
            ->route('crm.users.index')
            ->with('crm_success', 'CRM user deleted permanently.');
    }

    private function profilePayload(Request $request, string $mode, ?User $user = null): array
    {
        $validated = $request->validated();
        $avatarPath = $this->mediaService->storeAvatarFromCroppedImage(
            $validated['avatar_cropped_image'] ?? null,
            $user?->avatar_path
        );

        return [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
            'id_number' => $validated['id_number'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'employment_status' => $validated['employment_status'] ?? ($mode === 'store' ? 'active' : $user?->employment_status),
            'department_id' => $validated['department_id'] ?? null,
            'position_id' => $validated['position_id'] ?? null,
            'reports_to_user_id' => $validated['reports_to_user_id'] ?? null,
            'personal_payroll_number' => $validated['personal_payroll_number'] ?? null,
            'date_of_appointment' => $validated['date_of_appointment'] ?? null,
            'avatar_path' => $avatarPath,
        ];
    }

    private function normalizeTab(mixed $value): string
    {
        $tab = is_string($value) ? trim($value) : '';

        return in_array($tab, ['profile', 'qualifications', 'roles', 'history', 'settings'], true)
            ? $tab
            : 'profile';
    }

    private function directoryFormData(?User $excludingUser = null): array
    {
        return [
            'roles' => config('heritage_crm.roles'),
            'departments' => $this->profileService->departmentOptions(),
            'positions' => $this->profileService->positionOptions(),
            'reportingUsers' => $this->profileService->reportingUserOptions($excludingUser),
            'customFilters' => $this->customFilterOptions(),
            'employmentStatuses' => config('heritage_crm.user_employment_statuses'),
            'genders' => config('heritage_crm.user_genders'),
        ];
    }

    private function customFilterOptions()
    {
        return CrmUserFilter::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function applyUserSearch(Builder $query, string $term): Builder
    {
        $columns = $this->availableUserColumns([
            'name',
            'firstname',
            'lastname',
            'username',
            'email',
            'phone',
            'id_number',
            'personal_payroll_number',
            'nationality',
        ]);

        if ($columns === []) {
            return $query;
        }

        $query->where(function (Builder $builder) use ($columns, $term) {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';
                $builder->{$method}($column, 'like', '%' . $term . '%');
            }
        });

        return $query;
    }

    private function applyUserOrdering(Builder $query, bool $prioritizeActive = true): Builder
    {
        if ($prioritizeActive && $this->userHasColumn('active')) {
            $query->orderByDesc('active');
        }

        $orderedColumns = $this->availableUserColumns([
            'name',
            'firstname',
            'lastname',
            'username',
            'email',
        ]);

        foreach ($orderedColumns as $column) {
            $query->orderBy($column);
        }

        if ($orderedColumns === []) {
            $query->orderBy('id');
        }

        return $query;
    }

    private function userIdentitySelectColumns(): array
    {
        return array_values(array_unique(array_merge(
            ['id'],
            $this->availableUserColumns(['name', 'firstname', 'lastname', 'username', 'email'])
        )));
    }

    private function availableUserColumns(array $columns): array
    {
        return array_values(array_filter($columns, fn (string $column) => $this->userHasColumn($column)));
    }

    private function userHasColumn(string $column): bool
    {
        static $columns = null;

        if ($columns === null) {
            $columns = Schema::getColumnListing((new User())->getTable());
        }

        return in_array($column, $columns, true);
    }

    private function modulePermissionLevelsFor(User $user): array
    {
        $explicitLevels = $user->modulePermissions
            ->mapWithKeys(fn (CrmUserModulePermission $permission) => [$permission->module_key => $permission->permission_level]);

        $levels = [];

        foreach ($this->modulePermissionService->modules() as $module) {
            $levels[$module['key']] = $explicitLevels[$module['key']]
                ?? $this->modulePermissionService->defaultPermissionLevelForRole($user->role, $module['key']);
        }

        return $levels;
    }

    private function assertQualificationOwnership(User $user, CrmUserQualification $qualification): void
    {
        abort_unless((int) $qualification->user_id === (int) $user->id, 404);
    }

    private function assertQualificationAttachmentOwnership(
        User $user,
        CrmUserQualification $qualification,
        CrmUserQualificationAttachment $attachment
    ): void {
        $this->assertQualificationOwnership($user, $qualification);
        abort_unless((int) $attachment->qualification_id === (int) $qualification->id, 404);
    }

    private function assertSignatureOwnership(User $user, CrmUserSignature $signature): void
    {
        abort_unless((int) $signature->user_id === (int) $user->id, 404);
    }
}
