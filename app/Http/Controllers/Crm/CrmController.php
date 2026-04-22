<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\CrmCommercialCurrency;
use App\Models\CrmCommercialSetting;
use App\Models\CrmRequest;
use App\Models\CrmUserDepartment;
use App\Models\Customer;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\SalesStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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

    protected function authorizeUserModuleEdit(): void
    {
        abort_unless($this->crmUser()->canAccessCrmModule('users', 'edit'), 403);
    }

    protected function authorizeAdminSettings(): void
    {
        abort_unless($this->crmUser()->canManageCrmSettings(), 403);
    }

    protected function authorizeCommercialSettings(): void
    {
        abort_unless($this->crmUser()->canManageCommercialSettings(), 403);
    }

    protected function authorizeModuleAccess(string $moduleKey, string $requiredLevel = 'view'): void
    {
        abort_unless($this->crmUser()->canAccessCrmModule($moduleKey, $requiredLevel), 403);
    }

    protected function authorizeCommercialCatalogManagement(): void
    {
        abort_unless($this->crmUser()->canManageCommercialCatalog(), 403);
    }

    protected function authorizeCrmAdmin(): void
    {
        abort_unless($this->crmUser()->isAdmin(), 403);
    }

    protected function authorizeRecordAccess(?int $ownerId): void
    {
        abort_unless($this->crmUser()->canAccessOwnedRecord($ownerId), 403);
    }

    protected function authorizeCommercialRecordAccess(?int $ownerId): void
    {
        abort_unless($this->crmUser()->canAccessCommercialContextRecord($ownerId), 403);
    }

    protected function owners(): Collection
    {
        $query = User::query()
            ->where('active', true)
            ->whereIn('role', config('heritage_crm.owner_roles', ['admin', 'manager', 'rep']))
            ->orderBy('email');

        if ($this->crmUser()->isRep()) {
            $query->whereKey($this->crmUser()->id);
        }

        return $query->get();
    }

    protected function crmUsersForSelect(): Collection
    {
        $query = User::query()
            ->select($this->crmUserSelectColumns())
            ->where('active', true)
            ->whereIn('role', array_keys(config('heritage_crm.roles', [])));

        return $this->orderCrmUsersForSelect($query)->get();
    }

    protected function crmDepartmentsForSelect(): Collection
    {
        return CrmUserDepartment::query()
            ->where('is_active', true)
            ->withCount('users')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    protected function crmUserSelectColumns(): array
    {
        $columns = ['id', 'role', 'active'];

        foreach (['name', 'firstname', 'lastname', 'username', 'email', 'avatar_path', 'avatar', 'department_id', 'department'] as $column) {
            if ($this->userTableHasColumn($column)) {
                $columns[] = $column;
            }
        }

        return array_values(array_unique($columns));
    }

    protected function orderCrmUsersForSelect(Builder $query): Builder
    {
        if ($this->userTableHasColumn('name')) {
            return $query->orderBy('name');
        }

        $ordered = false;

        foreach (['firstname', 'lastname', 'username', 'email'] as $column) {
            if ($this->userTableHasColumn($column)) {
                $query->orderBy($column);
                $ordered = true;
            }
        }

        return $ordered ? $query : $query->orderBy('id');
    }

    protected function userTableHasColumn(string $column): bool
    {
        return isset($this->userTableColumns()[$column]);
    }

    protected function userTableColumns(): array
    {
        static $columns = null;

        if ($columns === null) {
            $columns = array_flip(Schema::getColumnListing((new User())->getTable()));
        }

        return $columns;
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

    protected function resolveCrmRequest(?int $requestId): ?CrmRequest
    {
        return $requestId ? CrmRequest::query()->findOrFail($requestId) : null;
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

    protected function authorizeLinkedCommercialRecords(
        ?Lead $lead = null,
        ?Customer $customer = null,
        ?Contact $contact = null,
        ?CrmRequest $crmRequest = null
    ): void {
        foreach ([$lead, $customer, $contact, $crmRequest] as $record) {
            if ($record !== null) {
                $this->authorizeCommercialRecordAccess($record->owner_id);
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

    protected function commercialSettingsRecord(): CrmCommercialSetting
    {
        $settings = CrmCommercialSetting::query()->first();

        if ($settings !== null) {
            return $settings;
        }

        $defaultCurrencyId = CrmCommercialCurrency::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->value('id');

        return CrmCommercialSetting::query()->create([
            'default_currency_id' => $defaultCurrencyId,
            'company_name' => 'Heritage Pro',
            'quote_prefix' => 'QT',
            'quote_next_sequence' => 1,
            'invoice_prefix' => 'INV',
            'invoice_next_sequence' => 1,
            'default_tax_rate' => 0,
            'allow_line_discounts' => true,
            'allow_document_discounts' => true,
        ]);
    }
}
