<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\CrmUserDepartmentUpsertRequest;
use App\Http\Requests\Crm\CrmUserFilterUpsertRequest;
use App\Http\Requests\Crm\CrmUserPositionUpsertRequest;
use App\Models\CrmUserDepartment;
use App\Models\CrmUserFilter;
use App\Models\CrmUserPosition;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserSettingController extends CrmController
{
    public function index(): RedirectResponse
    {
        $this->authorizeAdminUsers();

        return redirect()->route('crm.users.settings.departments');
    }

    public function departments(Request $request): View
    {
        return $this->renderIndex($request, 'departments');
    }

    public function storeDepartment(CrmUserDepartmentUpsertRequest $request): RedirectResponse
    {
        $this->authorizeAdminUsers();

        CrmUserDepartment::query()->create([
            'name' => $request->validated('name'),
            'sort_order' => $request->validated('sort_order') ?: (CrmUserDepartment::query()->count() + 1),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('crm.users.settings.departments')
            ->with('crm_success', 'Department created successfully.');
    }

    public function updateDepartment(CrmUserDepartmentUpsertRequest $request, CrmUserDepartment $crmUserDepartment): RedirectResponse
    {
        $this->authorizeAdminUsers();

        $crmUserDepartment->update([
            'name' => $request->validated('name'),
            'sort_order' => $request->validated('sort_order') ?: $crmUserDepartment->sort_order,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('crm.users.settings.departments')
            ->with('crm_success', 'Department updated successfully.');
    }

    public function destroyDepartment(CrmUserDepartment $crmUserDepartment): RedirectResponse
    {
        $this->authorizeAdminUsers();
        $crmUserDepartment->delete();

        return redirect()
            ->route('crm.users.settings.departments')
            ->with('crm_success', 'Department deleted permanently.');
    }

    public function positions(Request $request): View
    {
        return $this->renderIndex($request, 'positions');
    }

    public function storePosition(CrmUserPositionUpsertRequest $request): RedirectResponse
    {
        $this->authorizeAdminUsers();

        CrmUserPosition::query()->create([
            'name' => $request->validated('name'),
            'sort_order' => $request->validated('sort_order') ?: (CrmUserPosition::query()->count() + 1),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('crm.users.settings.positions')
            ->with('crm_success', 'Position created successfully.');
    }

    public function updatePosition(CrmUserPositionUpsertRequest $request, CrmUserPosition $crmUserPosition): RedirectResponse
    {
        $this->authorizeAdminUsers();

        $crmUserPosition->update([
            'name' => $request->validated('name'),
            'sort_order' => $request->validated('sort_order') ?: $crmUserPosition->sort_order,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('crm.users.settings.positions')
            ->with('crm_success', 'Position updated successfully.');
    }

    public function destroyPosition(CrmUserPosition $crmUserPosition): RedirectResponse
    {
        $this->authorizeAdminUsers();
        $crmUserPosition->delete();

        return redirect()
            ->route('crm.users.settings.positions')
            ->with('crm_success', 'Position deleted permanently.');
    }

    public function customFilters(Request $request): View
    {
        return $this->renderIndex($request, 'filters');
    }

    public function storeFilter(CrmUserFilterUpsertRequest $request): RedirectResponse
    {
        $this->authorizeAdminUsers();

        CrmUserFilter::query()->create([
            'name' => $request->validated('name'),
            'sort_order' => $request->validated('sort_order') ?: (CrmUserFilter::query()->count() + 1),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('crm.users.settings.filters')
            ->with('crm_success', 'Custom filter created successfully.');
    }

    public function updateFilter(CrmUserFilterUpsertRequest $request, CrmUserFilter $crmUserFilter): RedirectResponse
    {
        $this->authorizeAdminUsers();

        $crmUserFilter->update([
            'name' => $request->validated('name'),
            'sort_order' => $request->validated('sort_order') ?: $crmUserFilter->sort_order,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('crm.users.settings.filters')
            ->with('crm_success', 'Custom filter updated successfully.');
    }

    public function destroyFilter(CrmUserFilter $crmUserFilter): RedirectResponse
    {
        $this->authorizeAdminUsers();
        $crmUserFilter->delete();

        return redirect()
            ->route('crm.users.settings.filters')
            ->with('crm_success', 'Custom filter deleted permanently.');
    }

    private function renderIndex(Request $request, string $activeSection): View
    {
        $this->authorizeAdminUsers();
        $editId = (int) $request->query('edit', 0);

        return view('crm.users.settings.index', [
            'activeSection' => $activeSection,
            'departments' => CrmUserDepartment::query()->withCount('users')->orderBy('sort_order')->orderBy('name')->get(),
            'positions' => CrmUserPosition::query()->withCount('users')->orderBy('sort_order')->orderBy('name')->get(),
            'filters' => CrmUserFilter::query()->withCount('users')->orderBy('sort_order')->orderBy('name')->get(),
            'editDepartment' => $activeSection === 'departments' && $editId > 0 ? CrmUserDepartment::query()->find($editId) : null,
            'editPosition' => $activeSection === 'positions' && $editId > 0 ? CrmUserPosition::query()->find($editId) : null,
            'editFilter' => $activeSection === 'filters' && $editId > 0 ? CrmUserFilter::query()->find($editId) : null,
        ]);
    }
}
