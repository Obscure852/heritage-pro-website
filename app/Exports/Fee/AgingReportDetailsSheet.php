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

class AgingReportDetailsSheet implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(
        protected array $details
    ) {}

    public function collection(): Collection
    {
        $rows = [];

        foreach ($this->details as $student) {
            $rows[] = [
                $student['student_name'],
                $student['student_id'],
                $student['oldest_invoice_date'] ?? '-',
                $student['days_overdue'],
                'P ' . number_format((float) $student['total_outstanding'], 2),
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Student Name',
            'Student ID',
            'Oldest Invoice Date',
            'Days Overdue',
            'Balance'
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        // Header row styling
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 15,
            'C' => 20,
            'D' => 15,
            'E' => 18
        ];
    }

    public function title(): string
    {
        return 'Details';
    }
}
