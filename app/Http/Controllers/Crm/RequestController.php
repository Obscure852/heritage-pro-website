<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\CrmRequestUpsertRequest;
use App\Http\Requests\Crm\RequestActivityStoreRequest;
use App\Models\CrmRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class RequestController extends CrmController
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'owner_id' => (string) $request->query('owner_id', ''),
            'type' => (string) $request->query('type', ''),
            'sales_stage_id' => (string) $request->query('sales_stage_id', ''),
            'support_status' => (string) $request->query('support_status', ''),
            'outcome' => (string) $request->query('outcome', ''),
        ];

        $requests = $this->scopeOwned(
            CrmRequest::query()
                ->with([
                    'owner',
                    'lead:id,company_name',
                    'customer:id,company_name',
                    'contact:id,name',
                    'salesStage:id,name',
                ])
                ->when($filters['q'] !== '', function ($query) use ($filters) {
                    $query->where(function ($requestQuery) use ($filters) {
                        $requestQuery->where('title', 'like', '%' . $filters['q'] . '%')
                            ->orWhere('description', 'like', '%' . $filters['q'] . '%')
                            ->orWhere('next_action', 'like', '%' . $filters['q'] . '%');
                    });
                })
                ->when($filters['owner_id'] !== '', function ($query) use ($filters) {
                    $query->where('owner_id', (int) $filters['owner_id']);
                })
                ->when($filters['type'] !== '', function ($query) use ($filters) {
                    $query->where('type', $filters['type']);
                })
                ->when($filters['sales_stage_id'] !== '', function ($query) use ($filters) {
                    $query->where('sales_stage_id', (int) $filters['sales_stage_id']);
                })
                ->when($filters['support_status'] !== '', function ($query) use ($filters) {
                    $query->where('support_status', $filters['support_status']);
                })
                ->when($filters['outcome'] !== '', function ($query) use ($filters) {
                    $query->where('outcome', $filters['outcome']);
                })
                ->latest()
        )->paginate(12)->withQueryString();

        return view('crm.requests.index', [
            'requests' => $requests,
            'owners' => $this->owners(),
            'salesStages' => $this->availableSalesStages(),
            'requestTypes' => config('heritage_crm.request_types'),
            'supportStatuses' => config('heritage_crm.support_statuses'),
            'requestOutcomes' => config('heritage_crm.request_outcomes'),
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('crm.requests.create', $this->formData());
    }

    public function store(CrmRequestUpsertRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $crmRequest = CrmRequest::query()->create($this->payload($request, $data));

        return redirect()
            ->route('crm.requests.show', $crmRequest)
            ->with('crm_success', 'Request created successfully.');
    }

    public function show(CrmRequest $crmRequest): View
    {
        $this->authorizeRecordAccess($crmRequest->owner_id);

        $crmRequest->load([
            'owner',
            'lead:id,company_name',
            'customer:id,company_name',
            'contact:id,name,lead_id,customer_id',
            'salesStage:id,name',
            'activities.user',
        ]);

        return view('crm.requests.show', [
            'crmRequest' => $crmRequest,
            'requestTypes' => config('heritage_crm.request_types'),
            'supportStatuses' => config('heritage_crm.support_statuses'),
            'activityTypes' => config('heritage_crm.activity_types'),
        ]);
    }

    public function edit(CrmRequest $crmRequest): View
    {
        $this->authorizeRecordAccess($crmRequest->owner_id);

        return view('crm.requests.edit', array_merge($this->formData(), [
            'crmRequest' => $crmRequest,
        ]));
    }

    public function update(CrmRequestUpsertRequest $request, CrmRequest $crmRequest): RedirectResponse
    {
        $this->authorizeRecordAccess($crmRequest->owner_id);

        $crmRequest->update($this->payload($request, $request->validated(), $crmRequest));

        return redirect()
            ->route('crm.requests.edit', $crmRequest)
            ->with('crm_success', 'Request updated successfully.');
    }

    public function destroy(CrmRequest $crmRequest): RedirectResponse
    {
        $this->authorizeRecordAccess($crmRequest->owner_id);

        $crmRequest->forceDelete();

        return redirect()
            ->route('crm.requests.index')
            ->with('crm_success', 'Request deleted permanently.');
    }

    public function storeActivity(RequestActivityStoreRequest $request, CrmRequest $crmRequest): RedirectResponse
    {
        $this->authorizeRecordAccess($crmRequest->owner_id);

        $activity = $crmRequest->activities()->create([
            'user_id' => $this->crmUser()->id,
            ...$request->validated(),
        ]);

        $crmRequest->forceFill([
            'last_contact_at' => $activity->occurred_at,
        ])->save();

        return redirect()
            ->route('crm.requests.show', $crmRequest)
            ->with('crm_success', 'Activity logged successfully.');
    }

    private function payload(
        CrmRequestUpsertRequest $request,
        array $data,
        ?CrmRequest $existingRequest = null
    ): array {
        $lead = $this->resolveLead($data['lead_id'] ?? null);
        $customer = $this->resolveCustomer($data['customer_id'] ?? null);
        $contact = $this->resolveContact($data['contact_id'] ?? null);

        $this->authorizeLinkedRecords($lead, $customer, $contact, $existingRequest);

        if ($contact !== null) {
            $leadMismatch = $lead !== null && (int) $contact->lead_id !== (int) $lead->id;
            $customerMismatch = $customer !== null && (int) $contact->customer_id !== (int) $customer->id;

            if ($leadMismatch || $customerMismatch) {
                throw ValidationException::withMessages([
                    'contact_id' => 'The selected contact must belong to the selected lead or customer.',
                ]);
            }
        }

        $data['owner_id'] = $this->syncedOwnerId($lead, $customer, $data['owner_id'] ?? $existingRequest?->owner_id);

        if ($data['type'] === 'sales') {
            $data['support_status'] = null;
            $data['outcome'] = $data['outcome'] ?? 'pending';
        } else {
            $data['sales_stage_id'] = null;
            $data['outcome'] = null;
        }

        return $this->normalizeTimestamps($request, $data);
    }

    private function normalizeTimestamps(CrmRequestUpsertRequest $request, array $data): array
    {
        $data['next_action_at'] = $request->filled('next_action_at') ? $request->date('next_action_at') : null;
        $data['last_contact_at'] = $request->filled('last_contact_at') ? $request->date('last_contact_at') : null;
        $data['closed_at'] = $request->filled('closed_at') ? $request->date('closed_at') : null;

        return $data;
    }

    private function formData(): array
    {
        return [
            'owners' => $this->owners(),
            'leads' => $this->leadsForSelect(),
            'customers' => $this->customersForSelect(),
            'contacts' => $this->contactsForSelect(),
            'salesStages' => $this->availableSalesStages(),
            'requestTypes' => config('heritage_crm.request_types'),
            'supportStatuses' => config('heritage_crm.support_statuses'),
            'requestOutcomes' => config('heritage_crm.request_outcomes'),
        ];
    }
}
