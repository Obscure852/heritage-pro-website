<?php

namespace App\Http\Requests\Crm;

use App\Models\CrmCommercialCurrency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommercialSettingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'default_currency_id' => [
                'required',
                Rule::exists('crm_commercial_currencies', 'id')->where(
                    fn ($query) => $query->where('is_active', true)
                ),
            ],
            'quote_prefix' => ['required', 'string', 'max:20'],
            'quote_next_sequence' => ['required', 'integer', 'min:1', 'max:999999999'],
            'invoice_prefix' => ['required', 'string', 'max:20'],
            'invoice_next_sequence' => ['required', 'integer', 'min:1', 'max:999999999'],
            'default_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'allow_line_discounts' => ['nullable', 'boolean'],
            'allow_document_discounts' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $currencyId = $this->integer('default_currency_id');

            if ($currencyId < 1) {
                return;
            }

            $currency = CrmCommercialCurrency::query()->find($currencyId);

            if ($currency === null || ! $currency->is_active) {
                $validator->errors()->add('default_currency_id', 'The default currency must be active.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'quote_prefix' => strtoupper(trim((string) $this->input('quote_prefix'))),
            'invoice_prefix' => strtoupper(trim((string) $this->input('invoice_prefix'))),
        ]);
    }
}
