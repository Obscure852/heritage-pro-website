<?php

namespace App\Services\Crm\Imports\Processors;

use App\Models\CrmImportRun;
use App\Models\CrmImportRunRow;
use App\Models\CrmSector;
use App\Models\Lead;
use App\Models\User;
use App\Services\Crm\Imports\Contracts\CrmImportEntityProcessor;
use App\Support\CountryList;
use Illuminate\Validation\Rule;

class LeadImportProcessor extends AbstractCrmImportProcessor implements CrmImportEntityProcessor
{
    public function entity(): string
    {
        return 'leads';
    }

    public function previewRow(array $row, User $initiator): array
    {
        $ownerEmail = $this->normalizeString($row['owner_email'] ?? null);
        $owner = $this->allowedOwnerByEmail($ownerEmail);

        $payload = [
            'import_reference' => $this->normalizeString($row['import_reference'] ?? null),
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
            'status' => $this->normalizeLeadStatus($row['status'] ?? null) ?: 'active',
            'notes' => $this->normalizeString($row['notes'] ?? null),
        ];

        $errors = $this->validationErrors($payload, [
            'import_reference' => ['required', 'string', 'max:160'],
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
            'status' => ['required', 'in:active,qualified,lost'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($ownerEmail !== null && $owner === null) {
            $errors[] = 'Owner email must belong to an active CRM user.';
        }

        $existing = $payload['import_reference']
            ? Lead::withTrashed()->where('import_reference', $payload['import_reference'])->first()
            : null;

        return [
            'normalized_key' => $payload['import_reference'] ?: null,
            'payload' => $payload,
            'action' => $errors === [] ? ($existing ? 'update' : 'create') : 'error',
            'validation_errors' => $errors,
        ];
    }

    public function processRow(CrmImportRun $run, CrmImportRunRow $row): array
    {
        $payload = $row->payload ?? [];
        $lead = Lead::withTrashed()->where('import_reference', $payload['import_reference'])->first();

        $attributes = [
            'owner_id' => $payload['owner_id'] ?: $run->initiated_by_id,
            'import_reference' => $payload['import_reference'],
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
            'notes' => $payload['notes'],
        ];

        if ($lead) {
            if ($lead->trashed()) {
                $lead->restore();
            }

            $attributes['status'] = $lead->status === 'converted' || $lead->converted_at !== null
                ? 'converted'
                : $payload['status'];
            $attributes['converted_at'] = $lead->status === 'converted' || $lead->converted_at !== null
                ? ($lead->converted_at ?: now())
                : null;

            $lead->update($attributes);

            return [
                'action' => 'update',
                'record_id' => $lead->id,
            ];
        }

        $lead = Lead::query()->create($attributes + [
            'status' => $payload['status'],
            'converted_at' => null,
        ]);

        return [
            'action' => 'create',
            'record_id' => $lead->id,
        ];
    }

    private function normalizeLeadStatus(mixed $value): ?string
    {
        $status = $this->normalizeString($value);

        if ($status === null) {
            return null;
        }

        $canonical = strtolower(str_replace([' ', '-', '_'], '', $status));

        foreach (config('heritage_crm.lead_statuses', []) as $key => $label) {
            if ($canonical === strtolower(str_replace([' ', '-', '_'], '', (string) $key))
                || $canonical === strtolower(str_replace([' ', '-', '_'], '', (string) $label))) {
                return (string) $key;
            }
        }

        return match ($canonical) {
            'new', 'open', 'prospect', 'pipeline' => 'active',
            default => $status,
        };
    }
}
