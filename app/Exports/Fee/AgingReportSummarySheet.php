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

class AgingReportSummarySheet implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(
        protected array $summary
    ) {}

    public function collection(): Collection
    {
        $rows = [];

        $bucketLabels = [
            'current' => 'Current (0-30 days)',
            'overdue_30' => '31-60 days overdue',
            'overdue_60' => '61-90 days overdue',
            'overdue_90' => '90+ days overdue',
        ];

        $totalCount = 0;
        $totalAmount = '0.00';

        foreach ($bucketLabels as $key => $label) {
            $bucketData = $this->summary[$key] ?? ['count' => 0, 'amount' => '0.00'];
            $rows[] = [
                $label,
                $bucketData['count'],
                'P ' . number_format((float) $bucketData['amount'], 2),
            ];

            $totalCount += $bucketData['count'];
            $totalAmount = bcadd($totalAmount, (string) $bucketData['amount'], 2);
        }

        // Totals row
        $rows[] = [
            'TOTAL',
            $totalCount,
            'P ' . number_format((float) $totalAmount, 2),
        ];

        return collect($rows);
    }

    public function headings(): array
    {
        return ['Aging Bucket', 'Count', 'Amount'];
    }

    public function styles(Worksheet $sheet): void
    {
        // Header row styling
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');

        // Totals row
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E8E8E8');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 12,
            'C' => 20
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }
}
