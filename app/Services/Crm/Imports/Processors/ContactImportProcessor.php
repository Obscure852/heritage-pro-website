<?php

namespace App\Services\Crm\Imports\Processors;

use App\Models\Contact;
use App\Models\CrmImportRun;
use App\Models\CrmImportRunRow;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;
use App\Services\Crm\ContactPrimaryAssignmentService;
use App\Services\Crm\Imports\Contracts\CrmImportEntityProcessor;

class ContactImportProcessor extends AbstractCrmImportProcessor implements CrmImportEntityProcessor
{
    public function __construct(
        private readonly ContactPrimaryAssignmentService $contactPrimaryAssignmentService
    ) {
    }

    public function entity(): string
    {
        return 'contacts';
    }

    public function previewRow(array $row, User $initiator): array
    {
        $ownerEmail = $this->normalizeString($row['owner_email'] ?? null);
        $owner = $this->allowedOwnerByEmail($ownerEmail);
        $lead = Lead::query()->where('import_reference', $this->normalizeString($row['lead_import_reference'] ?? null))->first();

        $payload = [
            'import_reference' => $this->normalizeString($row['import_reference'] ?? null),
            'lead_import_reference' => $this->normalizeString($row['lead_import_reference'] ?? null),
            'name' => $this->normalizeString($row['name'] ?? null),
            'job_title' => $this->normalizeString($row['job_title'] ?? null),
            'email' => $this->normalizeString($row['email'] ?? null),
            'phone' => $this->normalizeString($row['phone'] ?? null),
            'notes' => $this->normalizeString($row['notes'] ?? null),
            'is_primary' => $this->normalizeBoolean($row['is_primary'] ?? null) ?? false,
            'owner_email' => $ownerEmail,
            'owner_id' => $owner?->id,
        ];

        $errors = $this->validationErrors($payload, [
            'import_reference' => ['required', 'string', 'max:160'],
            'lead_import_reference' => ['required', 'string', 'max:160'],
            'name' => ['required', 'string', 'max:160'],
            'job_title' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (($row['is_primary'] ?? null) !== null && $row['is_primary'] !== '' && $this->normalizeBoolean($row['is_primary']) === null) {
            $errors[] = 'Primary flag must be a valid boolean value.';
        }

        if ($ownerEmail !== null && $owner === null) {
            $errors[] = 'Owner email must belong to an active CRM user.';
        }

        if (! $lead) {
            $errors[] = 'Lead import reference must match an existing imported lead.';
        }

        if ($lead && ($lead->status === 'converted' || $lead->converted_at !== null)) {
            $activeCustomer = Customer::query()
                ->where('lead_id', $lead->id)
                ->where('status', 'active')
                ->latest('id')
                ->first();

            if (! $activeCustomer) {
                $errors[] = 'Converted leads must have an active customer before contacts can be attached through import.';
            }
        }

        $existing = $payload['import_reference']
            ? Contact::withTrashed()->where('import_reference', $payload['import_reference'])->first()
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
        $lead = Lead::query()->where('import_reference', $payload['lead_import_reference'])->firstOrFail();
        $customer = null;

        if ($lead->status === 'converted' || $lead->converted_at !== null) {
            $customer = Customer::query()
                ->where('lead_id', $lead->id)
                ->where('status', 'active')
                ->latest('id')
                ->firstOrFail();
        }

        $contact = Contact::withTrashed()->where('import_reference', $payload['import_reference'])->first();
        $ownerId = $payload['owner_id'] ?: ($customer?->owner_id ?: $lead->owner_id ?: $run->initiated_by_id);

        $attributes = [
            'owner_id' => $ownerId,
            'import_reference' => $payload['import_reference'],
            'lead_id' => $customer ? null : $lead->id,
            'customer_id' => $customer?->id,
            'name' => $payload['name'],
            'job_title' => $payload['job_title'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'is_primary' => (bool) $payload['is_primary'],
            'notes' => $payload['notes'],
        ];

        if ($contact) {
            if ($contact->trashed()) {
                $contact->restore();
            }

            $contact->update($attributes);
            $this->contactPrimaryAssignmentService->sync($contact);

            return [
                'action' => 'update',
                'record_id' => $contact->id,
            ];
        }

        $contact = Contact::query()->create($attributes);
        $this->contactPrimaryAssignmentService->sync($contact);

        return [
            'action' => 'create',
            'record_id' => $contact->id,
        ];
    }
}
