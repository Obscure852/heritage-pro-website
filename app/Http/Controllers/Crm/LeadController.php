<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\LeadUpsertRequest;
use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class LeadController extends CrmController
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'owner_id' => (string) $request->query('owner_id', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $leadStatsQuery = $this->scopeOwned(Lead::query());
        $leadStats = [
            ['label' => 'Total', 'value' => (clone $leadStatsQuery)->count()],
            ['label' => 'Active', 'value' => (clone $leadStatsQuery)->where('status', 'active')->count()],
            ['label' => 'Qualified', 'value' => (clone $leadStatsQuery)->where('status', 'qualified')->count()],
            ['label' => 'Converted', 'value' => (clone $leadStatsQuery)->where('status', 'converted')->count()],
        ];

        $leads = $this->scopeOwned(
            Lead::query()
                ->with(['owner'])
                ->withCount(['contacts', 'requests'])
                ->when($filters['q'] !== '', function ($query) use ($filters) {
                    $query->where(function ($leadQuery) use ($filters) {
                        $leadQuery->where('company_name', 'like', '%' . $filters['q'] . '%')
                            ->orWhere('industry', 'like', '%' . $filters['q'] . '%')
                            ->orWhere('email', 'like', '%' . $filters['q'] . '%')
                            ->orWhere('phone', 'like', '%' . $filters['q'] . '%')
                            ->orWhere('country', 'like', '%' . $filters['q'] . '%');
                    });
                })
                ->when($filters['owner_id'] !== '', function ($query) use ($filters) {
                    $query->where('owner_id', (int) $filters['owner_id']);
                })
                ->when($filters['status'] !== '', function ($query) use ($filters) {
                    $query->where('status', $filters['status']);
                })
                ->orderByRaw("case status when 'qualified' then 1 when 'active' then 2 when 'converted' then 3 else 4 end")
                ->orderBy('company_name')
        )->paginate(12)->withQueryString();

        return view('crm.leads.index', [
            'leads' => $leads,
            'owners' => $this->owners(),
            'leadStatuses' => config('heritage_crm.lead_statuses'),
            'filters' => $filters,
            'leadStats' => $leadStats,
        ]);
    }

    public function create(): View
    {
        return view('crm.leads.create', $this->formData());
    }

    public function store(LeadUpsertRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['owner_id'] = $this->normalizeOwnerId($data['owner_id'] ?? null);

        $lead = Lead::query()->create($data);

        return redirect()
            ->route('crm.leads.show', $lead)
            ->with('crm_success', 'Lead created successfully.');
    }

    public function show(Lead $lead): View
    {
        $this->authorizeRecordAccess($lead->owner_id);

        $lead->load([
            'owner',
            'contacts.owner',
            'requests.owner',
            'requests.salesStage:id,name',
            'requests.contact:id,name',
            'quotes' => fn ($query) => $query
                ->with(['contact:id,name', 'request:id,title'])
                ->withCount('items')
                ->latest('quote_date')
                ->latest('id'),
            'invoices' => fn ($query) => $query
                ->with(['contact:id,name', 'request:id,title'])
                ->withCount('items')
                ->latest('invoice_date')
                ->latest('id'),
        ]);

        $customer = Customer::query()
            ->where('lead_id', $lead->id)
            ->latest('id')
            ->first();

        return view('crm.leads.show', [
            'lead' => $lead,
            'customer' => $customer,
            'leadStatuses' => config('heritage_crm.lead_statuses'),
            'quoteStatuses' => config('heritage_crm.quote_statuses'),
            'invoiceStatuses' => config('heritage_crm.invoice_statuses'),
        ]);
    }

    public function edit(Lead $lead): View
    {
        $this->authorizeRecordAccess($lead->owner_id);

        return view('crm.leads.edit', array_merge($this->formData(), [
            'lead' => $lead,
        ]));
    }

    public function update(LeadUpsertRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorizeRecordAccess($lead->owner_id);

        $data = $request->validated();
        $data['owner_id'] = $this->normalizeOwnerId($data['owner_id'] ?? $lead->owner_id);

        if ($lead->converted_at !== null || $lead->status === 'converted') {
            $data['status'] = 'converted';
            $data['converted_at'] = $lead->converted_at ?: now();
        } else {
            $data['converted_at'] = null;
        }

        $lead->update($data);

        return redirect()
            ->route('crm.leads.edit', $lead)
            ->with('crm_success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorizeRecordAccess($lead->owner_id);

        if ($lead->customers()->exists()) {
            return redirect()
                ->route('crm.leads.show', $lead)
                ->with('crm_error', 'Converted leads with an active customer record cannot be deleted.');
        }

        $lead->forceDelete();

        return redirect()
            ->route('crm.leads.index')
            ->with('crm_success', 'Lead deleted permanently.');
    }

    public function convert(Lead $lead): RedirectResponse
    {
        $this->authorizeRecordAccess($lead->owner_id);

        $existingCustomer = Customer::query()
            ->where('lead_id', $lead->id)
            ->latest('id')
            ->first();

        if ($existingCustomer !== null) {
            return redirect()
                ->route('crm.customers.show', $existingCustomer)
                ->with('crm_success', 'This lead was already converted.');
        }

        $customer = DB::transaction(function () use ($lead) {
            $convertedAt = $lead->converted_at ?: now();

            $customer = Customer::query()->create([
                'owner_id' => $lead->owner_id,
                'lead_id' => $lead->id,
                'company_name' => $lead->company_name,
                'industry' => $lead->industry,
                'website' => $lead->website,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'country' => $lead->country,
                'status' => 'active',
                'purchased_at' => $convertedAt,
                'notes' => $lead->notes,
            ]);

            $lead->contacts()->update([
                'customer_id' => $customer->id,
                'lead_id' => null,
            ]);

            $lead->requests()->update([
                'customer_id' => $customer->id,
            ]);

            $lead->update([
                'status' => 'converted',
                'converted_at' => $convertedAt,
            ]);

            return $customer;
        });

        return redirect()
            ->route('crm.customers.show', $customer)
            ->with('crm_success', 'Lead converted to customer successfully.');
    }

    private function formData(): array
    {
        return [
            'owners' => $this->owners(),
            'leadStatuses' => collect(config('heritage_crm.lead_statuses'))
                ->except(['converted'])
                ->all(),
        ];
    }
}
