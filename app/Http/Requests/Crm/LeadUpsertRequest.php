<?php

namespace App\Http\Requests\Crm;

use App\Models\CrmSector;
use App\Support\CountryList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $lead = $this->route('lead');
        $allowedStatuses = ['active', 'qualified', 'lost'];

        if ($lead && ($lead->converted_at !== null || $lead->status === 'converted')) {
            $allowedStatuses[] = 'converted';
        }

        return [
            'owner_id' => ['nullable', 'exists:users,id'],
            'company_name' => ['required', 'string', 'max:160'],
            'industry' => ['nullable', 'string', 'max:120', Rule::in($this->allowedSectors($lead))],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:60'],
            'fax' => ['nullable', 'string', 'max:60'],
            'country' => ['nullable', 'string', 'max:120', Rule::in($this->allowedCountries($lead))],
            'location' => ['nullable', 'string', 'max:160'],
            'postal_address' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in($allowedStatuses)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'fax' => trim((string) $this->input('fax')) ?: null,
            'industry' => CrmSector::normalizeName($this->input('industry')),
            'country' => CountryList::normalizeName($this->input('country')),
            'location' => trim((string) $this->input('location')) ?: null,
            'postal_address' => trim((string) $this->input('postal_address')) ?: null,
            'region' => trim((string) $this->input('region')) ?: null,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $lead = $this->route('lead');

            if (
                $this->input('status') === 'converted'
                && (! $lead || ($lead->converted_at === null && $lead->status !== 'converted'))
            ) {
                $validator->errors()->add('status', 'Leads can only be marked converted through the conversion workflow.');
            }
        });
    }

    private function allowedCountries($lead): array
    {
        $countries = CountryList::names();

        if ($lead?->country) {
            $countries[] = $lead->country;
        }

        return array_values(array_unique($countries));
    }

    private function allowedSectors($lead): array
    {
        $sectors = CrmSector::activeNames();

        if ($lead?->industry) {
            $sectors[] = $lead->industry;
        }

        return array_values(array_unique($sectors));
    }
}
