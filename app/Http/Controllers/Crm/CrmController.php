<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\SalesStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

abstract class CrmController extends Controller
{
    protected function crmUser(): User
    {
        return Auth::user();
    }

    protected function scopeOwned(Builder $query, string $ownerColumn = 'owner_id'): Builder
    {
        if ($this->crmUser()->isRep()) {
            $query->where($ownerColumn, $this->crmUser()->id);
        }

        return $query;
    }

    protected function normalizeOwnerId(?int $ownerId): int
    {
        if ($this->crmUser()->canManageOperationalRecords()) {
            return $ownerId ?: $this->crmUser()->id;
        }

        return $this->crmUser()->id;
    }

    protected function authorizeAdminUsers(): void
    {
        abort_unless($this->crmUser()->canManageCrmUsers(), 403);
    }

    protected function authorizeAdminSettings(): void
    {
        abort_unless($this->crmUser()->canManageCrmSettings(), 403);
    }

    protected function authorizeRecordAccess(?int $ownerId): void
    {
        abort_unless($this->crmUser()->canAccessOwnedRecord($ownerId), 403);
    }

    protected function owners(): Collection
    {
        $query = User::query()
            ->where('active', true)
            ->whereIn('role', array_keys(config('heritage_crm.roles')))
            ->orderBy('email');

        if ($this->crmUser()->isRep()) {
            $query->whereKey($this->crmUser()->id);
        }

        return $query->get();
    }

    protected function leadsForSelect(): Collection
    {
        $query = Lead::query()
            ->select(['id', 'company_name', 'owner_id', 'status'])
            ->whereNull('converted_at')
            ->orderBy('company_name');

        return $this->scopeOwned($query)->get();
    }

    protected function customersForSelect(): Collection
    {
        $query = Customer::query()
            ->select(['id', 'company_name', 'owner_id', 'status'])
            ->orderBy('company_name');

        return $this->scopeOwned($query)->get();
    }

    protected function contactsForSelect(): Collection
    {
        $query = Contact::query()
            ->select(['id', 'name', 'lead_id', 'customer_id', 'owner_id'])
            ->where(function ($builder) {
                $builder->whereNotNull('lead_id')
                    ->orWhereNotNull('customer_id');
            })
            ->orderBy('name');

        return $this->scopeOwned($query)->get();
    }

    protected function availableIntegrations(): Collection
    {
        return Integration::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    protected function availableSalesStages(): Collection
    {
        return SalesStage::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->get();
    }

    protected function resolveLead(?int $leadId): ?Lead
    {
        return $leadId ? Lead::query()->findOrFail($leadId) : null;
    }

    protected function resolveCustomer(?int $customerId): ?Customer
    {
        return $customerId ? Customer::query()->findOrFail($customerId) : null;
    }

    protected function resolveContact(?int $contactId): ?Contact
    {
        return $contactId ? Contact::query()->findOrFail($contactId) : null;
    }

    protected function authorizeLinkedRecords(
        ?Lead $lead = null,
        ?Customer $customer = null,
        ?Contact $contact = null,
        ?CrmRequest $crmRequest = null
    ): void {
        foreach ([$lead, $customer, $contact, $crmRequest] as $record) {
            if ($record !== null) {
                $this->authorizeRecordAccess($record->owner_id);
            }
        }
    }

    protected function syncedOwnerId(?Lead $lead, ?Customer $customer, ?int $requestedOwnerId): int
    {
        if ($lead?->owner_id) {
            return (int) $lead->owner_id;
        }

        if ($customer?->owner_id) {
            return (int) $customer->owner_id;
        }

        return $this->normalizeOwnerId($requestedOwnerId);
    }
}
