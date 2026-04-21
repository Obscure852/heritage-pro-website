<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\DevelopmentRequestUpsertRequest;
use App\Models\DevelopmentRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class DevController extends CrmController
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'owner_id' => (string) $request->query('owner_id', ''),
            'status' => (string) $request->query('status', ''),
            'priority' => (string) $request->query('priority', ''),
        ];

        $items = $this->scopeOwned(
            DevelopmentRequest::query()
                ->with(['owner', 'lead:id,company_name', 'customer:id,company_name', 'contact:id,name'])
                ->when($filters['q'] !== '', function ($query) use ($filters) {
                    $query->where(function ($itemQuery) use ($filters) {
                        $itemQuery->where('title', 'like', '%' . $filters['q'] . '%')
                            ->orWhere('requested_by', 'like', '%' . $filters['q'] . '%')
                            ->orWhere('target_module', 'like', '%' . $filters['q'] . '%')
                            ->orWhere('description', 'like', '%' . $filters['q'] . '%');
                    });
                })
                ->when($filters['owner_id'] !== '', function ($query) use ($filters) {
                    $query->where('owner_id', (int) $filters['owner_id']);
                })
                ->when($filters['status'] !== '', function ($query) use ($filters) {
                    $query->where('status', $filters['status']);
                })
                ->when($filters['priority'] !== '', function ($query) use ($filters) {
                    $query->where('priority', $filters['priority']);
                })
                ->latest()
        )->paginate(12)->withQueryString();

        return view('crm.dev.index', [
            'items' => $items,
            'owners' => $this->owners(),
            'developmentStatuses' => config('heritage_crm.development_statuses'),
            'developmentPriorities' => config('heritage_crm.development_priorities'),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('crm.dev.create', $this->formData());
    }

    public function store(DevelopmentRequestUpsertRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $lead = $this->resolveLead($data['lead_id'] ?? null);
        $customer = $this->resolveCustomer($data['customer_id'] ?? null);
        $contact = $this->resolveContact($data['contact_id'] ?? null);

        $this->authorizeLinkedRecords($lead, $customer, $contact);

        $data['owner_id'] = $this->syncedOwnerId($lead, $customer, $data['owner_id'] ?? null);

        $item = DevelopmentRequest::query()->create($data);

        return redirect()
            ->route('crm.dev.show', $item)
            ->with('crm_success', 'Development request logged successfully.');
    }

    public function show(DevelopmentRequest $developmentRequest): View
    {
        $this->authorizeRecordAccess($developmentRequest->owner_id);

        $developmentRequest->load([
            'owner',
            'lead:id,company_name',
            'customer:id,company_name',
            'contact:id,name',
        ]);

        return view('crm.dev.show', [
            'developmentRequest' => $developmentRequest,
            'developmentStatuses' => config('heritage_crm.development_statuses'),
            'developmentPriorities' => config('heritage_crm.development_priorities'),
        ]);
    }

    public function edit(DevelopmentRequest $developmentRequest): View
    {
        $this->authorizeRecordAccess($developmentRequest->owner_id);

        return view('crm.dev.edit', array_merge($this->formData(), [
            'developmentRequest' => $developmentRequest,
        ]));
    }

    public function update(DevelopmentRequestUpsertRequest $request, DevelopmentRequest $developmentRequest): RedirectResponse
    {
        $this->authorizeRecordAccess($developmentRequest->owner_id);

        $data = $request->validated();
        $lead = $this->resolveLead($data['lead_id'] ?? null);
        $customer = $this->resolveCustomer($data['customer_id'] ?? null);
        $contact = $this->resolveContact($data['contact_id'] ?? null);

        $this->authorizeLinkedRecords($lead, $customer, $contact);

        $data['owner_id'] = $this->syncedOwnerId($lead, $customer, $data['owner_id'] ?? $developmentRequest->owner_id);

        $developmentRequest->update($data);

        return redirect()
            ->route('crm.dev.edit', $developmentRequest)
            ->with('crm_success', 'Development request updated successfully.');
    }

    public function destroy(DevelopmentRequest $developmentRequest): RedirectResponse
    {
        $this->authorizeRecordAccess($developmentRequest->owner_id);

        $developmentRequest->forceDelete();

        return redirect()
            ->route('crm.dev.index')
            ->with('crm_success', 'Development request deleted permanently.');
    }

    private function formData(): array
    {
        return [
            'owners' => $this->owners(),
            'leads' => $this->leadsForSelect(),
            'customers' => $this->customersForSelect(),
            'contacts' => $this->contactsForSelect(),
            'developmentStatuses' => config('heritage_crm.development_statuses'),
            'developmentPriorities' => config('heritage_crm.development_priorities'),
        ];
    }
}
