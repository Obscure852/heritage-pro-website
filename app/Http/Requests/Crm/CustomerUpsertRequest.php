<?php

namespace App\Http\Requests\Crm;

use App\Models\CrmSector;
use App\Support\CountryList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            'owner_id' => ['nullable', 'exists:users,id'],
            'company_name' => ['required', 'string', 'max:160'],
            'industry' => ['nullable', 'string', 'max:120', Rule::in($this->allowedSectors($customer))],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:60'],
            'fax' => ['nullable', 'string', 'max:60'],
            'country' => ['nullable', 'string', 'max:120', Rule::in($this->allowedCountries($customer))],
            'location' => ['nullable', 'string', 'max:160'],
            'postal_address' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(['active', 'onboarding', 'inactive'])],
            'purchased_at' => ['nullable', 'date'],
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

    private function allowedCountries($customer): array
    {
        $countries = CountryList::names();

        if ($customer?->country) {
            $countries[] = $customer->country;
        }

        return array_values(array_unique($countries));
    }

    private function allowedSectors($customer): array
    {
        $sectors = CrmSector::activeNames();

        if ($customer?->industry) {
            $sectors[] = $customer->industry;
        }

        return array_values(array_unique($sectors));
    }
}
