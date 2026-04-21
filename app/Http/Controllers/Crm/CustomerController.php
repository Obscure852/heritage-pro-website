<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\CustomerUpsertRequest;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class CustomerController extends CrmController
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'owner_id' => (string) $request->query('owner_id', ''),
            'status' => (string) $request->query('status', ''),
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
        ]);
    }

    public function create(): View
    {
        return view('crm.customers.create', $this->formData());
    }

    public function store(CustomerUpsertRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['owner_id'] = $this->normalizeOwnerId($data['owner_id'] ?? null);

        $customer = Customer::query()->create($data);

        return redirect()
            ->route('crm.customers.show', $customer)
            ->with('crm_success', 'Customer created successfully.');
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
        ]);

        return view('crm.customers.show', [
            'customer' => $customer,
            'customerStatuses' => config('heritage_crm.customer_statuses'),
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
