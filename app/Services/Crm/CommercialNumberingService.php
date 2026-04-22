<?php

namespace App\Services\Crm;

use App\Models\CrmCommercialCurrency;
use App\Models\CrmCommercialSetting;
use Illuminate\Support\Facades\DB;

class CommercialNumberingService
{
    public function nextQuoteNumber(): string
    {
        return $this->nextNumber('quote');
    }

    public function nextInvoiceNumber(): string
    {
        return $this->nextNumber('invoice');
    }

    public function formatNumber(string $prefix, int $sequence): string
    {
        $number = str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);

        return trim($prefix) !== '' ? trim($prefix) . '-' . $number : $number;
    }

    private function nextNumber(string $type): string
    {
        $sequenceColumn = $type === 'invoice' ? 'invoice_next_sequence' : 'quote_next_sequence';
        $prefixColumn = $type === 'invoice' ? 'invoice_prefix' : 'quote_prefix';

        return DB::transaction(function () use ($prefixColumn, $sequenceColumn) {
            $settings = CrmCommercialSetting::query()->lockForUpdate()->first();

            if ($settings === null) {
                $defaultCurrencyId = CrmCommercialCurrency::query()
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->value('id');

                $settings = CrmCommercialSetting::query()->create([
                    'default_currency_id' => $defaultCurrencyId,
                    'quote_prefix' => 'QT',
                    'quote_next_sequence' => 1,
                    'invoice_prefix' => 'INV',
                    'invoice_next_sequence' => 1,
                    'default_tax_rate' => 0,
                    'allow_line_discounts' => true,
                    'allow_document_discounts' => true,
                ]);
            }

            $sequence = (int) $settings->{$sequenceColumn};
            $formatted = $this->formatNumber((string) $settings->{$prefixColumn}, $sequence);

            $settings->forceFill([
                $sequenceColumn => $sequence + 1,
            ])->save();

            return $formatted;
        });
    }
}
