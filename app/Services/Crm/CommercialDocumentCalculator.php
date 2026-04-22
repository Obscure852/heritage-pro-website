<?php

namespace App\Services\Crm;

use InvalidArgumentException;

class CommercialDocumentCalculator
{
    public function calculate(
        array $lines,
        string $documentDiscountType = 'none',
        float|int|string|null $documentDiscountValue = 0,
        int $precision = 2
    ): array {
        $normalizedLines = array_values(array_map(function (array $line) use ($precision) {
            $grossAmount = $this->round(
                $this->decimal($line['quantity'] ?? 0) * $this->decimal($line['unit_price'] ?? 0),
                $precision
            );
            $lineDiscountAmount = $this->discountAmount(
                $grossAmount,
                (string) ($line['discount_type'] ?? 'none'),
                $line['discount_value'] ?? 0,
                $precision
            );
            $lineNetBeforeDocumentDiscount = $this->round($grossAmount - $lineDiscountAmount, $precision);

            return [
                'quantity' => $this->decimal($line['quantity'] ?? 0),
                'unit_price' => $this->decimal($line['unit_price'] ?? 0),
                'discount_type' => (string) ($line['discount_type'] ?? 'none'),
                'discount_value' => $this->decimal($line['discount_value'] ?? 0),
                'tax_rate' => $this->decimal($line['tax_rate'] ?? 0),
                'gross_amount' => $grossAmount,
                'line_discount_amount' => $lineDiscountAmount,
                'net_before_document_discount' => $lineNetBeforeDocumentDiscount,
            ];
        }, $lines));

        $subtotalBeforeDocumentDiscount = $this->round(array_sum(array_column($normalizedLines, 'net_before_document_discount')), $precision);
        $documentDiscountAmount = $this->discountAmount(
            $subtotalBeforeDocumentDiscount,
            $documentDiscountType,
            $documentDiscountValue,
            $precision
        );
        $allocatedDocumentDiscounts = $this->allocateAmount(
            array_column($normalizedLines, 'net_before_document_discount'),
            $documentDiscountAmount,
            $precision
        );

        $calculatedLines = [];
        $subtotalAmount = 0.0;
        $taxAmount = 0.0;
        $totalAmount = 0.0;

        foreach ($normalizedLines as $index => $line) {
            $allocatedDocumentDiscount = $allocatedDocumentDiscounts[$index] ?? 0.0;
            $discountAmount = $this->round($line['line_discount_amount'] + $allocatedDocumentDiscount, $precision);
            $netAmount = $this->round($line['gross_amount'] - $discountAmount, $precision);
            $lineTaxAmount = $this->round($netAmount * ($line['tax_rate'] / 100), $precision);
            $lineTotalAmount = $this->round($netAmount + $lineTaxAmount, $precision);

            $subtotalAmount = $this->round($subtotalAmount + $netAmount, $precision);
            $taxAmount = $this->round($taxAmount + $lineTaxAmount, $precision);
            $totalAmount = $this->round($totalAmount + $lineTotalAmount, $precision);

            $calculatedLines[] = [
                'gross_amount' => $line['gross_amount'],
                'discount_amount' => $discountAmount,
                'line_discount_amount' => $line['line_discount_amount'],
                'document_discount_amount' => $allocatedDocumentDiscount,
                'net_amount' => $netAmount,
                'tax_amount' => $lineTaxAmount,
                'total_amount' => $lineTotalAmount,
                'discount_type' => $line['discount_type'],
                'discount_value' => $line['discount_value'],
                'tax_rate' => $line['tax_rate'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
            ];
        }

        return [
            'lines' => $calculatedLines,
            'document_discount_type' => $documentDiscountType,
            'document_discount_value' => $this->decimal($documentDiscountValue),
            'document_discount_amount' => $documentDiscountAmount,
            'subtotal_amount' => $subtotalAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ];
    }

    public function discountAmount(
        float|int|string|null $baseAmount,
        string $discountType,
        float|int|string|null $discountValue,
        int $precision = 2
    ): float {
        $baseAmount = max(0, $this->decimal($baseAmount));
        $discountValue = max(0, $this->decimal($discountValue));

        return match ($discountType) {
            'none', '' => 0.0,
            'fixed' => min($baseAmount, $this->round($discountValue, $precision)),
            'percent' => min($baseAmount, $this->round($baseAmount * ($discountValue / 100), $precision)),
            default => throw new InvalidArgumentException('Unsupported discount type [' . $discountType . '].'),
        };
    }

    private function allocateAmount(array $baseAmounts, float $discountAmount, int $precision): array
    {
        $discountAmount = max(0, $this->round($discountAmount, $precision));
        $totalBaseAmount = $this->round(array_sum($baseAmounts), $precision);

        if ($discountAmount === 0.0 || $totalBaseAmount === 0.0 || $baseAmounts === []) {
            return array_fill(0, count($baseAmounts), 0.0);
        }

        $allocated = [];
        $remaining = $discountAmount;
        $lastIndex = array_key_last($baseAmounts);

        foreach ($baseAmounts as $index => $baseAmount) {
            $baseAmount = max(0, $this->round((float) $baseAmount, $precision));

            if ($index === $lastIndex) {
                $allocated[$index] = min($baseAmount, $this->round($remaining, $precision));

                continue;
            }

            $share = $this->round($discountAmount * ($baseAmount / $totalBaseAmount), $precision);
            $share = min($baseAmount, $share, $remaining);

            $allocated[$index] = $share;
            $remaining = $this->round($remaining - $share, $precision);
        }

        return $allocated;
    }

    private function decimal(float|int|string|null $value): float
    {
        return round((float) ($value ?? 0), 6);
    }

    private function round(float|int $value, int $precision): float
    {
        return round((float) $value, $precision);
    }
}
