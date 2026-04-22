<?php

namespace App\Http\Controllers\Crm;

use App\Http\Requests\Crm\ContactUpsertRequest;
use App\Models\Contact;
use App\Services\Crm\ContactPrimaryAssignmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ContactController extends CrmController
{
    public function __construct(
        private readonly ContactPrimaryAssignmentService $contactPrimaryAssignmentService
    ) {
    }

    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'owner_id' => (string) $request->query('owner_id', ''),
            'linked_to' => (string) $request->query('linked_to', ''),
            'primary' => (string) $request->query('primary', ''),
        ];

        $contactStatsQuery = $this->scopeOwned(
            Contact::query()->where(function ($query) {
                $query->whereNotNull('lead_id')
                    ->orWhereNotNull('customer_id');
            })
        );
        $contactStats = [
            ['label' => 'Total', 'value' => (clone $contactStatsQuery)->count()],
            ['label' => 'Primary', 'value' => (clone $contactStatsQuery)->where('is_primary', true)->count()],
            ['label' => 'Lead Linked', 'value' => (clone $contactStatsQuery)->whereNotNull('lead_id')->count()],
            ['label' => 'Customer Linked', 'value' => (clone $contactStatsQuery)->whereNotNull('customer_id')->count()],
        ];

        $contacts = $this->scopeOwned(
            Contact::query()
                ->where(function ($query) {
                    $query->whereNotNull('lead_id')
                        ->orWhereNotNull('customer_id');
                })
                ->with(['owner', 'lead:id,company_name', 'customer:id,company_name'])
                ->when($filters['q'] !== '', function ($query) use ($filters) {
                    $query->where(function ($contactQuery) use ($filters) {
                        $contactQuery->where('name', 'like', '%' . $filters['q'] . '%')
                            ->orWhere('job_title', 'like', '%' . $filters['q'] . '%')
                            ->orWhere('email', 'like', '%' . $filters['q'] . '%')
                            ->orWhere('phone', 'like', '%' . $filters['q'] . '%');
                    });
                })
                ->when($filters['owner_id'] !== '', function ($query) use ($filters) {
                    $query->where('owner_id', (int) $filters['owner_id']);
                })
                ->when($filters['linked_to'] === 'lead', function ($query) {
                    $query->whereNotNull('lead_id');
                })
                ->when($filters['linked_to'] === 'customer', function ($query) {
                    $query->whereNotNull('customer_id');
                })
                ->when($filters['primary'] !== '', function ($query) use ($filters) {
                    $query->where('is_primary', $filters['primary'] === '1');
                })
                ->orderByDesc('is_primary')
                ->orderBy('name')
        )->paginate(12)->withQueryString();

        return view('crm.contacts.index', [
            'contacts' => $contacts,
            'owners' => $this->owners(),
            'filters' => $filters,
            'contactStats' => $contactStats,
        ]);
    }

    public function create(): View
    {
        return view('crm.contacts.create', $this->formData());
    }

    public function store(ContactUpsertRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $lead = $this->resolveLead($data['lead_id'] ?? null);
        $customer = $this->resolveCustomer($data['customer_id'] ?? null);

        $this->authorizeLinkedRecords($lead, $customer);

        $data['owner_id'] = $this->syncedOwnerId($lead, $customer, $data['owner_id'] ?? null);
        $data['is_primary'] = $request->boolean('is_primary');

        $contact = DB::transaction(function () use ($data) {
            $contact = Contact::query()->create($data);
            $this->contactPrimaryAssignmentService->sync($contact);

            return $contact;
        });

        return redirect()
            ->route('crm.contacts.show', $contact)
            ->with('crm_success', 'Contact created successfully.');
    }

    public function show(Contact $contact): View
    {
        $this->authorizeRecordAccess($contact->owner_id);

        $contact->load([
            'owner',
            'lead:id,company_name',
            'customer:id,company_name',
        ]);

        return view('crm.contacts.show', [
            'contact' => $contact,
        ]);
    }

    public function edit(Contact $contact): View
    {
        $this->authorizeRecordAccess($contact->owner_id);

        return view('crm.contacts.edit', array_merge($this->formData(), [
            'contact' => $contact,
        ]));
    }

    public function update(ContactUpsertRequest $request, Contact $contact): RedirectResponse
    {
        $this->authorizeRecordAccess($contact->owner_id);

        $data = $request->validated();
        $lead = $this->resolveLead($data['lead_id'] ?? null);
        $customer = $this->resolveCustomer($data['customer_id'] ?? null);

        $this->authorizeLinkedRecords($lead, $customer);

        $data['owner_id'] = $this->syncedOwnerId($lead, $customer, $data['owner_id'] ?? $contact->owner_id);
        $data['is_primary'] = $request->boolean('is_primary');

        DB::transaction(function () use ($contact, $data) {
            $contact->update($data);
            $this->contactPrimaryAssignmentService->sync($contact);
        });

        return redirect()
            ->route('crm.contacts.edit', $contact)
            ->with('crm_success', 'Contact updated successfully.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $this->authorizeRecordAccess($contact->owner_id);

        $contact->forceDelete();

        return redirect()
            ->route('crm.contacts.index')
            ->with('crm_success', 'Contact deleted permanently.');
    }

    private function formData(): array
    {
        return [
            'owners' => $this->owners(),
            'leads' => $this->leadsForSelect(),
            'customers' => $this->customersForSelect(),
        ];
    }
}
