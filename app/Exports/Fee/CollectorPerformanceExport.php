<?php

namespace App\Exports\Fee;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CollectorPerformanceExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(
        protected array $performance,
        protected ?string $termName = null
    ) {}

    public function collection(): Collection
    {
        $rows = [];

        foreach ($this->performance as $collector) {
            $rows[] = [
                $collector['collector_name'],
                'P ' . number_format((float) $collector['total_collected'], 2),
                $collector['payment_count'],
                'P ' . number_format((float) $collector['average_payment'], 2),
                'P ' . number_format((float) ($collector['by_method']['cash']['total_amount'] ?? 0), 2),
                'P ' . number_format((float) ($collector['by_method']['bank_transfer']['total_amount'] ?? 0), 2),
                'P ' . number_format((float) ($collector['by_method']['mobile_money']['total_amount'] ?? 0), 2),
                'P ' . number_format((float) ($collector['by_method']['cheque']['total_amount'] ?? 0), 2),
            ];
        }

        // Calculate totals
        $totalCollected = '0.00';
        $totalCount = 0;
        $totalCash = '0.00';
        $totalBank = '0.00';
        $totalMobile = '0.00';
        $totalCheque = '0.00';

        foreach ($this->performance as $collector) {
            $totalCollected = bcadd($totalCollected, (string) $collector['total_collected'], 2);
            $totalCount += $collector['payment_count'];
            $totalCash = bcadd($totalCash, (string) ($collector['by_method']['cash']['total_amount'] ?? 0), 2);
            $totalBank = bcadd($totalBank, (string) ($collector['by_method']['bank_transfer']['total_amount'] ?? 0), 2);
            $totalMobile = bcadd($totalMobile, (string) ($collector['by_method']['mobile_money']['total_amount'] ?? 0), 2);
            $totalCheque = bcadd($totalCheque, (string) ($collector['by_method']['cheque']['total_amount'] ?? 0), 2);
        }

        $averagePayment = $totalCount > 0 ? bcdiv($totalCollected, (string) $totalCount, 2) : '0.00';

        // Totals row
        $rows[] = [
            'TOTAL',
            'P ' . number_format((float) $totalCollected, 2),
            $totalCount,
            'P ' . number_format((float) $averagePayment, 2),
            'P ' . number_format((float) $totalCash, 2),
            'P ' . number_format((float) $totalBank, 2),
            'P ' . number_format((float) $totalMobile, 2),
            'P ' . number_format((float) $totalCheque, 2),
        ];

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Collector',
            'Total Collected',
            'Payment Count',
            'Average Payment',
            'Cash',
            'Bank Transfer',
            'Mobile Money',
            'Cheque'
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        // Header row styling
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');

        // Totals row (last row)
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A{$lastRow}:H{$lastRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$lastRow}:H{$lastRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E8E8E8');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 18,
            'C' => 15,
            'D' => 18,
            'E' => 15,
            'F' => 18,
            'G' => 18,
            'H' => 15
        ];
    }

    public function title(): string
    {
        return 'Collector Performance';
    }
}
