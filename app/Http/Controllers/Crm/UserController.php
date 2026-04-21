<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\CrmUserStoreRequest;
use App\Http\Requests\Crm\CrmUserUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends CrmController
{
    public function index(Request $request): View
    {
        $this->authorizeAdminUsers();

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'role' => (string) $request->query('role', ''),
            'active' => (string) $request->query('active', ''),
        ];

        $users = User::query()
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($userQuery) use ($filters) {
                    $userQuery->where('name', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('email', 'like', '%' . $filters['q'] . '%');
                });
            })
            ->when($filters['role'] !== '', function ($query) use ($filters) {
                $query->where('role', $filters['role']);
            })
            ->when($filters['active'] !== '', function ($query) use ($filters) {
                $query->where('active', $filters['active'] === '1');
            })
            ->orderByDesc('active')
            ->orderBy('email')
            ->paginate(12)
            ->withQueryString();

        return view('crm.users.index', [
            'users' => $users,
            'roles' => config('heritage_crm.roles'),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorizeAdminUsers();

        return view('crm.users.create', [
            'roles' => config('heritage_crm.roles'),
        ]);
    }

    public function store(CrmUserStoreRequest $request): RedirectResponse
    {
        $this->authorizeAdminUsers();

        User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => $request->validated('role'),
            'active' => $request->boolean('active'),
        ]);

        return redirect()
            ->route('crm.users.index')
            ->with('crm_success', 'CRM user created successfully.');
    }

    public function edit(User $user): View
    {
        $this->authorizeAdminUsers();

        return view('crm.users.edit', [
            'user' => $user,
            'roles' => config('heritage_crm.roles'),
        ]);
    }

    public function update(CrmUserUpdateRequest $request, User $user): RedirectResponse
    {
        $this->authorizeAdminUsers();

        $payload = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'role' => $request->validated('role'),
            'active' => $request->boolean('active'),
        ];

        if ($request->filled('password')) {
            $payload['password'] = Hash::make($request->validated('password'));
        }

        $user->update($payload);

        return redirect()
            ->route('crm.users.edit', $user)
            ->with('crm_success', 'CRM user updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeAdminUsers();

        if ($this->crmUser()->is($user)) {
            return redirect()
                ->route('crm.users.edit', $user)
                ->with('crm_error', 'You cannot delete the account you are currently using.');
        }

        $user->forceDelete();

        return redirect()
            ->route('crm.users.index')
            ->with('crm_success', 'CRM user deleted permanently.');
    }
}
