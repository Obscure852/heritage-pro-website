<?php

namespace App\Services\Crm\Imports\Processors;

use App\Models\CrmImportRun;
use App\Models\CrmImportRunRow;
use App\Models\CrmSector;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use App\Services\Crm\Imports\Contracts\CrmImportEntityProcessor;
use App\Support\CountryList;
use Illuminate\Validation\Rule;

class CustomerImportProcessor extends AbstractCrmImportProcessor implements CrmImportEntityProcessor
{
    public function entity(): string
    {
        return 'customers';
    }

    public function previewRow(array $row, User $initiator): array
    {
        $ownerEmail = $this->normalizeString($row['owner_email'] ?? null);
        $owner = $this->allowedOwnerByEmail($ownerEmail);

        $payload = [
            'owner_id' => $owner?->id ?: $initiator->id,
            'owner_email' => $ownerEmail,
            'company_name' => $this->normalizeString($row['company_name'] ?? null),
            'industry' => CrmSector::normalizeName($this->normalizeString($row['industry'] ?? null)),
            'website' => $this->normalizeString($row['website'] ?? null),
            'email' => $this->normalizeString($row['email'] ?? null),
            'phone' => $this->normalizeString($row['phone'] ?? null),
            'fax' => $this->normalizeString($row['fax'] ?? null),
            'country' => CountryList::normalizeName($this->normalizeString($row['country'] ?? null)),
            'region' => $this->normalizeString($row['region'] ?? null),
            'location' => $this->normalizeString($row['location'] ?? null),
            'postal_address' => $this->normalizeString($row['postal_address'] ?? null),
            'status' => $this->normalizeCustomerStatus($row['status'] ?? null) ?: 'active',
            'purchased_at' => $this->normalizeDate($row['purchased_at'] ?? null),
            'notes' => $this->normalizeString($row['notes'] ?? null),
        ];

        $errors = $this->validationErrors($payload, [
            'company_name' => ['required', 'string', 'max:160'],
            'industry' => ['nullable', 'string', 'max:120', Rule::in(CrmSector::activeNames())],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:60'],
            'fax' => ['nullable', 'string', 'max:60'],
            'country' => ['nullable', 'string', 'max:120', Rule::in(CountryList::names())],
            'region' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:160'],
            'postal_address' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'onboarding', 'inactive'])],
            'purchased_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($ownerEmail !== null && $owner === null) {
            $errors[] = 'Owner email must belong to an active CRM user.';
        }

        if (($row['purchased_at'] ?? null) !== null && $row['purchased_at'] !== '' && $payload['purchased_at'] === null) {
            $errors[] = 'Purchase date must use DD/MM/YYYY format.';
        }

        $existing = $payload['company_name']
            ? Customer::withTrashed()->where('company_name', $payload['company_name'])->first()
            : null;

        return [
            'normalized_key' => $payload['company_name'] ?: null,
            'payload' => $payload,
            'action' => $errors === [] ? ($existing ? 'update' : 'create') : 'error',
            'validation_errors' => $errors,
        ];
    }

    public function processRow(CrmImportRun $run, CrmImportRunRow $row): array
    {
        $payload = $row->payload ?? [];
        $customer = Customer::withTrashed()->where('company_name', $payload['company_name'])->first();
        $ownerId = $payload['owner_id'] ?: $run->initiated_by_id;
        $convertedAt = $payload['purchased_at'] ?: now();

        $customerAttributes = [
            'owner_id' => $ownerId,
            'company_name' => $payload['company_name'],
            'industry' => $payload['industry'],
            'website' => $payload['website'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'fax' => $payload['fax'],
            'country' => $payload['country'],
            'region' => $payload['region'],
            'location' => $payload['location'],
            'postal_address' => $payload['postal_address'],
            'status' => $payload['status'],
            'purchased_at' => $payload['purchased_at'],
            'notes' => $payload['notes'],
        ];

        if ($customer) {
            if ($customer->trashed()) {
                $customer->restore();
            }

            if (! $customer->lead_id) {
                $customerAttributes['lead_id'] = $this->createSourceLead($customerAttributes, $convertedAt)->id;
            } else {
                $this->syncSourceLead($customer->lead, $customerAttributes, $convertedAt);
            }

            $customer->update($customerAttributes);

            return [
                'action' => 'update',
                'record_id' => $customer->id,
            ];
        }

        $sourceLead = $this->createSourceLead($customerAttributes, $convertedAt);
        $customer = Customer::query()->create($customerAttributes + [
            'lead_id' => $sourceLead->id,
        ]);

        return [
            'action' => 'create',
            'record_id' => $customer->id,
        ];
    }

    private function createSourceLead(array $attributes, mixed $convertedAt): Lead
    {
        return Lead::query()->create([
            'owner_id' => $attributes['owner_id'],
            'company_name' => $attributes['company_name'],
            'industry' => $attributes['industry'],
            'website' => $attributes['website'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'],
            'fax' => $attributes['fax'],
            'country' => $attributes['country'],
            'region' => $attributes['region'],
            'location' => $attributes['location'],
            'postal_address' => $attributes['postal_address'],
            'status' => 'converted',
            'converted_at' => $convertedAt,
            'notes' => $attributes['notes'],
        ]);
    }

    private function syncSourceLead(?Lead $lead, array $attributes, mixed $convertedAt): void
    {
        if (! $lead) {
            return;
        }

        $lead->update([
            'owner_id' => $attributes['owner_id'],
            'company_name' => $attributes['company_name'],
            'industry' => $attributes['industry'],
            'website' => $attributes['website'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'],
            'fax' => $attributes['fax'],
            'country' => $attributes['country'],
            'region' => $attributes['region'],
            'location' => $attributes['location'],
            'postal_address' => $attributes['postal_address'],
            'status' => 'converted',
            'converted_at' => $lead->converted_at ?: $convertedAt,
            'notes' => $attributes['notes'],
        ]);
    }

    private function normalizeCustomerStatus(mixed $value): ?string
    {
        $status = $this->normalizeString($value);

        if ($status === null) {
            return null;
        }

        $canonical = strtolower(str_replace([' ', '-', '_'], '', $status));

        foreach (config('heritage_crm.customer_statuses', []) as $key => $label) {
            if ($canonical === strtolower(str_replace([' ', '-', '_'], '', (string) $key))
                || $canonical === strtolower(str_replace([' ', '-', '_'], '', (string) $label))) {
                return (string) $key;
            }
        }

        return match ($canonical) {
            'new' => 'onboarding',
            default => $status,
        };
    }
}
