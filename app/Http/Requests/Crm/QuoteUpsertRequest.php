<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuoteUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lead_id' => ['nullable', 'exists:leads,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'contact_id' => ['required', 'exists:contacts,id'],
            'request_id' => ['nullable', 'exists:requests,id'],
            'currency_id' => ['required', 'exists:crm_commercial_currencies,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'quote_date' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:quote_date'],
            'document_discount_type' => ['required', Rule::in(array_keys(config('heritage_crm.commercial_discount_types', [])))],
            'document_discount_value' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['nullable', 'exists:crm_products,id'],
            'items.*.item_name' => ['nullable', 'string', 'max:255'],
            'items.*.item_description' => ['nullable', 'string', 'max:5000'],
            'items.*.unit_label' => ['nullable', 'string', 'max:40'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999999.99'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount_type' => ['required', Rule::in(array_keys(config('heritage_crm.commercial_discount_types', [])))],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $items = array_map(function (array $item) {
            return [
                'product_id' => $item['product_id'] ?? null,
                'item_name' => isset($item['item_name']) ? trim((string) $item['item_name']) : null,
                'item_description' => isset($item['item_description']) ? trim((string) $item['item_description']) : null,
                'unit_label' => isset($item['unit_label']) ? trim((string) $item['unit_label']) : null,
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['unit_price'] ?? 0,
                'tax_rate' => $item['tax_rate'] ?? 0,
                'discount_type' => $item['discount_type'] ?? 'none',
                'discount_value' => $item['discount_value'] ?? 0,
            ];
        }, $this->input('items', []));

        $this->merge([
            'subject' => trim((string) $this->input('subject')),
            'notes' => trim((string) $this->input('notes')),
            'terms' => trim((string) $this->input('terms')),
            'document_discount_value' => $this->input('document_discount_value', 0),
            'items' => $items,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $index => $item) {
                $rowNumber = $index + 1;
                $hasProduct = filled($item['product_id'] ?? null);
                $hasName = filled($item['item_name'] ?? null);
                $hasUnitLabel = filled($item['unit_label'] ?? null);

                if (! $hasProduct && ! $hasName) {
                    $validator->errors()->add("items.$index.item_name", 'Line ' . $rowNumber . ' requires a catalog product or item name.');
                }

                if (! $hasProduct && ! $hasUnitLabel) {
                    $validator->errors()->add("items.$index.unit_label", 'Line ' . $rowNumber . ' requires a unit label.');
                }
            }
        });
    }
}
