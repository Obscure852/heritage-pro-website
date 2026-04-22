<?php

namespace App\Services\Crm;

use App\Models\Contact;
use App\Models\CrmRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CommercialDocumentValidationService
{
    public function validateQuote(array $payload): array
    {
        return $this->validate($payload, 'quote');
    }

    public function validateInvoice(array $payload): array
    {
        return $this->validate($payload, 'invoice');
    }

    public function validate(array $payload, string $documentType): array
    {
        $statusOptions = $documentType === 'invoice'
            ? array_keys(config('heritage_crm.invoice_statuses', []))
            : array_keys(config('heritage_crm.quote_statuses', []));

        $dateField = $documentType === 'invoice' ? 'invoice_date' : 'quote_date';
        $numberField = $documentType === 'invoice' ? 'invoice_number' : 'quote_number';

        $validator = Validator::make($payload, [
            'owner_id' => ['nullable', 'exists:users,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'contact_id' => ['required', 'exists:contacts,id'],
            'request_id' => ['nullable', 'exists:requests,id'],
            $numberField => ['required', 'string', 'max:40'],
            'status' => ['required', Rule::in($statusOptions)],
            $dateField => ['required', 'date'],
            'valid_until' => $documentType === 'quote'
                ? ['required', 'date', 'after_or_equal:' . $dateField]
                : ['nullable', 'date'],
        ]);

        $validator->after(function ($validator) use ($payload) {
            $leadId = $payload['lead_id'] ?? null;
            $customerId = $payload['customer_id'] ?? null;

            if ((blank($leadId) && blank($customerId)) || (filled($leadId) && filled($customerId))) {
                $validator->errors()->add('account_context', 'Exactly one of lead_id or customer_id is required.');

                return;
            }

            $contact = isset($payload['contact_id'])
                ? Contact::query()->find($payload['contact_id'])
                : null;

            if ($contact === null) {
                return;
            }

            if (filled($leadId) && (int) $contact->lead_id !== (int) $leadId) {
                $validator->errors()->add('contact_id', 'The selected contact must belong to the selected lead.');
            }

            if (filled($customerId) && (int) $contact->customer_id !== (int) $customerId) {
                $validator->errors()->add('contact_id', 'The selected contact must belong to the selected customer.');
            }

            if (blank($payload['request_id'] ?? null)) {
                return;
            }

            $request = CrmRequest::query()->find($payload['request_id']);

            if ($request === null) {
                return;
            }

            if ($request->type !== 'sales') {
                $validator->errors()->add('request_id', 'Only sales requests may be linked to commercial documents.');
            }

            if (filled($leadId) && (int) $request->lead_id !== (int) $leadId) {
                $validator->errors()->add('request_id', 'The linked request must belong to the selected lead.');
            }

            if (filled($customerId) && (int) $request->customer_id !== (int) $customerId) {
                $validator->errors()->add('request_id', 'The linked request must belong to the selected customer.');
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
