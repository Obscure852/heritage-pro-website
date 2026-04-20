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

class CollectionSummaryExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(
        protected array $summary,
        protected array $byMethod,
        protected string $startDate,
        protected string $endDate
    ) {}

    public function collection(): Collection
    {
        $rows = [];

        // Summary section header
        $rows[] = ['Summary', '', '', ''];
        $rows[] = ['Total Collected', 'P ' . number_format((float) ($this->summary['total_collected'] ?? 0), 2), '', ''];
        $rows[] = ['Payment Count', $this->summary['payment_count'] ?? 0, '', ''];
        $rows[] = ['Period', "{$this->startDate} to {$this->endDate}", '', ''];
        $rows[] = ['', '', '', ''];

        // By Method section header
        $rows[] = ['Collections by Payment Method', '', '', ''];
        $rows[] = ['Method', 'Count', 'Amount', 'Percentage'];

        foreach ($this->byMethod as $data) {
            $methodName = $data['payment_method'] ?? 'unknown';
            $rows[] = [
                ucfirst(str_replace('_', ' ', $methodName)),
                $data['payment_count'] ?? 0,
                'P ' . number_format((float) ($data['total_amount'] ?? 0), 2),
                ($data['percentage'] ?? 0) . '%'
            ];
        }

        // Totals row
        $totalCount = 0;
        $totalAmount = '0.00';
        foreach ($this->byMethod as $data) {
            $totalCount += $data['payment_count'] ?? 0;
            $totalAmount = bcadd($totalAmount, (string) ($data['total_amount'] ?? 0), 2);
        }
        $rows[] = ['TOTAL', $totalCount, 'P ' . number_format((float) $totalAmount, 2), '100%'];

        return collect($rows);
    }

    public function headings(): array
    {
        return ['Collection Summary Report', '', '', ''];
    }

    public function styles(Worksheet $sheet): void
    {
        // Title row
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Summary section label
        $sheet->getStyle('A2')->getFont()->setBold(true);

        // By Method section header
        $sheet->getStyle('A7')->getFont()->setBold(true);

        // Column headers
        $sheet->getStyle('A8:D8')->getFont()->setBold(true);
        $sheet->getStyle('A8:D8')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');

        // Totals row (last row)
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A{$lastRow}:D{$lastRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$lastRow}:D{$lastRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E8E8E8');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 15,
            'C' => 20,
            'D' => 15
        ];
    }

    public function title(): string
    {
        return 'Collection Summary';
    }
}
