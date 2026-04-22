<?php

namespace App\Services\Crm\Imports\Processors;

use App\Models\CrmImportRun;
use App\Models\CrmImportRunRow;
use App\Models\Lead;
use App\Models\User;
use App\Services\Crm\Imports\Contracts\CrmImportEntityProcessor;

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
            'industry' => $this->normalizeString($row['industry'] ?? null),
            'website' => $this->normalizeString($row['website'] ?? null),
            'email' => $this->normalizeString($row['email'] ?? null),
            'phone' => $this->normalizeString($row['phone'] ?? null),
            'country' => $this->normalizeString($row['country'] ?? null),
            'status' => $this->normalizeString($row['status'] ?? null) ?: 'active',
            'notes' => $this->normalizeString($row['notes'] ?? null),
        ];

        $errors = $this->validationErrors($payload, [
            'import_reference' => ['required', 'string', 'max:160'],
            'company_name' => ['required', 'string', 'max:160'],
            'industry' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:60'],
            'country' => ['nullable', 'string', 'max:120'],
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
            'country' => $payload['country'],
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
}
