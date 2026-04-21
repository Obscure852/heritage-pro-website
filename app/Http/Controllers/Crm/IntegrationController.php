<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\IntegrationUpsertRequest;
use App\Models\Integration;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class IntegrationController extends CrmController
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'owner_id' => (string) $request->query('owner_id', ''),
            'kind' => (string) $request->query('kind', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $integrations = Integration::query()
            ->with('owner')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $query->where(function ($integrationQuery) use ($filters) {
                    $integrationQuery->where('name', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('school_code', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('base_url', 'like', '%' . $filters['q'] . '%')
                        ->orWhere('webhook_url', 'like', '%' . $filters['q'] . '%');
                });
            })
            ->when($filters['owner_id'] !== '', function ($query) use ($filters) {
                $query->where('owner_id', (int) $filters['owner_id']);
            })
            ->when($filters['kind'] !== '', function ($query) use ($filters) {
                $query->where('kind', $filters['kind']);
            })
            ->when($filters['status'] !== '', function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('crm.integrations.index', [
            'integrations' => $integrations,
            'owners' => $this->owners(),
            'integrationKinds' => config('heritage_crm.integration_kinds'),
            'integrationStatuses' => config('heritage_crm.integration_statuses'),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        $this->authorizeAdminSettings();

        return view('crm.integrations.create', $this->formData());
    }

    public function show(Integration $integration): View
    {
        return view('crm.integrations.show', [
            'integration' => $integration->load('owner'),
            'integrationKinds' => config('heritage_crm.integration_kinds'),
            'integrationStatuses' => config('heritage_crm.integration_statuses'),
            'canManage' => $this->crmUser()->canManageCrmSettings(),
        ]);
    }

    public function edit(Integration $integration): View
    {
        $this->authorizeAdminSettings();

        return view('crm.integrations.edit', array_merge($this->formData(), [
            'integration' => $integration,
        ]));
    }

    public function store(IntegrationUpsertRequest $request): RedirectResponse
    {
        $this->authorizeAdminSettings();

        $data = $request->validated();
        $data['owner_id'] = $data['owner_id'] ?? $this->crmUser()->id;

        $integration = Integration::query()->create($data);

        return redirect()
            ->route('crm.integrations.show', $integration)
            ->with('crm_success', 'Integration created successfully.');
    }

    public function update(IntegrationUpsertRequest $request, Integration $integration): RedirectResponse
    {
        $this->authorizeAdminSettings();

        $integration->update($request->validated());

        return redirect()
            ->route('crm.integrations.edit', $integration)
            ->with('crm_success', 'Integration updated successfully.');
    }

    public function destroy(Integration $integration): RedirectResponse
    {
        $this->authorizeAdminSettings();

        $integration->forceDelete();

        return redirect()
            ->route('crm.integrations.index')
            ->with('crm_success', 'Integration deleted permanently.');
    }

    private function formData(): array
    {
        return [
            'owners' => $this->owners(),
            'integrationKinds' => config('heritage_crm.integration_kinds'),
            'integrationStatuses' => config('heritage_crm.integration_statuses'),
        ];
    }
}
