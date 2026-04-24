<?php

namespace Tests\Unit\Crm;

use App\Services\Crm\CommercialDocumentCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CommercialDocumentCalculatorTest extends TestCase
{
    public function test_it_calculates_line_discounts_document_discounts_and_tax(): void
    {
        $calculator = new CommercialDocumentCalculator();

        $result = $calculator->calculate([
            [
                'quantity' => 2,
                'unit_price' => 100,
                'discount_type' => 'percent',
                'discount_value' => 10,
                'tax_rate' => 14,
            ],
            [
                'quantity' => 1,
                'unit_price' => 50,
                'discount_type' => 'none',
                'discount_value' => 0,
                'tax_rate' => 0,
            ],
        ], 'fixed', 23);

        $this->assertSame(23.0, $result['document_discount_amount']);
        $this->assertSame(207.0, $result['subtotal_amount']);
        $this->assertSame(22.68, $result['tax_amount']);
        $this->assertSame(229.68, $result['total_amount']);

        $this->assertSame(38.0, $result['lines'][0]['discount_amount']);
        $this->assertSame(162.0, $result['lines'][0]['net_amount']);
        $this->assertSame(22.68, $result['lines'][0]['tax_amount']);
        $this->assertSame(184.68, $result['lines'][0]['total_amount']);

        $this->assertSame(5.0, $result['lines'][1]['discount_amount']);
        $this->assertSame(45.0, $result['lines'][1]['net_amount']);
        $this->assertSame(0.0, $result['lines'][1]['tax_amount']);
        $this->assertSame(45.0, $result['lines'][1]['total_amount']);
    }

    public function test_it_rejects_unsupported_discount_types(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new CommercialDocumentCalculator())->discountAmount(100, 'bogus', 10);
    }

    public function test_it_applies_document_tax_after_discounts_and_allocates_without_rounding_drift(): void
    {
        $calculator = new CommercialDocumentCalculator();

        $result = $calculator->calculate([
            [
                'quantity' => 1,
                'unit_price' => 99.99,
                'discount_type' => 'none',
                'discount_value' => 0,
                'tax_rate' => 0,
            ],
            [
                'quantity' => 2,
                'unit_price' => 50,
                'discount_type' => 'fixed',
                'discount_value' => 10,
                'tax_rate' => 0,
            ],
        ], 'none', 0, 2, 'document', 14);

        $this->assertSame('document', $result['tax_scope']);
        $this->assertSame(14.0, $result['document_tax_rate']);
        $this->assertSame(189.99, $result['subtotal_amount']);
        $this->assertSame(26.6, $result['tax_amount']);
        $this->assertSame(216.59, $result['total_amount']);
        $this->assertSame(26.6, round(array_sum(array_column($result['lines'], 'tax_amount')), 2));
    }
}
