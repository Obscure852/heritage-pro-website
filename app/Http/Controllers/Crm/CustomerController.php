<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\CustomerUpsertRequest;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CustomerController extends CrmController
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'owner_id' => (string) $request->query('owner_id', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $customerStatsQuery = $this->scopeOwned(Customer::query());
        $customerStats = [
            ['label' => 'Total', 'value' => (clone $customerStatsQuery)->count()],
            ['label' => 'Active', 'value' => (clone $customerStatsQuery)->where('status', 'active')->count()],
            ['label' => 'Onboarding', 'value' => (clone $customerStatsQuery)->where('status', 'onboarding')->count()],
            ['label' => 'Inactive', 'value' => (clone $customerStatsQuery)->where('status', 'inactive')->count()],
        ];

        $customers = $this->scopeOwned(
            Customer::query()
                ->with(['owner', 'lead:id,company_name'])
                ->withCount(['contacts', 'requests'])
                ->when($filters['q'] !== '', function ($query) use ($filters) {
                    $query->where(function ($customerQuery) use ($filters) {
                        $customerQuery->where('company_name', 'like', '%' . $filters['q'] . '%')
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
                ->orderBy('company_name')
        )->paginate(12)->withQueryString();

        return view('crm.customers.index', [
            'customers' => $customers,
            'owners' => $this->owners(),
            'customerStatuses' => config('heritage_crm.customer_statuses'),
            'filters' => $filters,
            'canOnboardCustomer' => $this->crmUser()->isAdmin(),
            'customerStats' => $customerStats,
        ]);
    }

    public function onboardingCreate(): View
    {
        $this->authorizeCrmAdmin();

        return view('crm.customers.onboarding-create', $this->formData());
    }

    public function onboardingStore(CustomerUpsertRequest $request): RedirectResponse
    {
        $this->authorizeCrmAdmin();

        $data = $request->validated();
        $ownerId = $this->normalizeOwnerId($data['owner_id'] ?? null);

        $customer = DB::transaction(function () use ($data, $ownerId) {
            $convertedAt = $data['purchased_at'] ?? now();

            $lead = Lead::query()->create([
                'owner_id' => $ownerId,
                'company_name' => $data['company_name'],
                'industry' => $data['industry'] ?? null,
                'website' => $data['website'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'country' => $data['country'] ?? null,
                'status' => 'converted',
                'converted_at' => $convertedAt,
                'notes' => $data['notes'] ?? null,
            ]);

            return Customer::query()->create([
                'owner_id' => $ownerId,
                'lead_id' => $lead->id,
                'company_name' => $data['company_name'],
                'industry' => $data['industry'] ?? null,
                'website' => $data['website'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'country' => $data['country'] ?? null,
                'status' => $data['status'],
                'purchased_at' => $data['purchased_at'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('crm.customers.show', $customer)
            ->with('crm_success', 'Customer imported and linked to a source lead successfully.');
    }

    public function show(Customer $customer): View
    {
        $this->authorizeRecordAccess($customer->owner_id);

        $customer->load([
            'owner',
            'lead:id,company_name',
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

        return view('crm.customers.show', [
            'customer' => $customer,
            'customerStatuses' => config('heritage_crm.customer_statuses'),
            'quoteStatuses' => config('heritage_crm.quote_statuses'),
            'invoiceStatuses' => config('heritage_crm.invoice_statuses'),
        ]);
    }

    public function edit(Customer $customer): View
    {
        $this->authorizeRecordAccess($customer->owner_id);

        return view('crm.customers.edit', array_merge($this->formData(), [
            'customer' => $customer,
        ]));
    }

    public function update(CustomerUpsertRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorizeRecordAccess($customer->owner_id);

        $data = $request->validated();
        $data['owner_id'] = $this->normalizeOwnerId($data['owner_id'] ?? $customer->owner_id);
        $data['lead_id'] = $customer->lead_id;

        $customer->update($data);

        return redirect()
            ->route('crm.customers.edit', $customer)
            ->with('crm_success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorizeRecordAccess($customer->owner_id);

        $customer->forceDelete();

        return redirect()
            ->route('crm.customers.index')
            ->with('crm_success', 'Customer deleted permanently.');
    }

    private function formData(): array
    {
        return [
            'owners' => $this->owners(),
            'customerStatuses' => config('heritage_crm.customer_statuses'),
        ];
    }
}
