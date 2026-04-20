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

class StudentStatementSummarySheet implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(
        protected array $statement,
        protected string $studentName
    ) {}

    public function collection(): Collection
    {
        $summary = $this->statement['summary'] ?? [];
        $student = $this->statement['student'] ?? null;

        $rows = [];

        // Student info section
        $rows[] = ['Student Information', ''];
        $rows[] = ['Name', $this->studentName];
        if ($student) {
            $rows[] = ['Student Number', $student->student_number ?? '-'];
            $rows[] = ['Grade', $student->currentGrade?->name ?? '-'];
        }
        $rows[] = ['', ''];

        // Account summary section
        $rows[] = ['Account Summary', ''];
        $rows[] = ['Total Invoiced', 'P ' . number_format((float) ($summary['total_invoiced'] ?? 0), 2)];
        $rows[] = ['Total Paid', 'P ' . number_format((float) ($summary['total_paid'] ?? 0), 2)];
        $rows[] = ['Balance Due', 'P ' . number_format((float) ($summary['balance'] ?? 0), 2)];
        $rows[] = ['', ''];

        // Statistics
        $invoiceCount = count($this->statement['invoices'] ?? []);
        $paymentCount = count($this->statement['payments'] ?? []);
        $rows[] = ['Statistics', ''];
        $rows[] = ['Total Invoices', $invoiceCount];
        $rows[] = ['Total Payments', $paymentCount];

        return collect($rows);
    }

    public function headings(): array
    {
        return ['Student Statement', ''];
    }

    public function styles(Worksheet $sheet): void
    {
        // Title
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Section headers
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A7')->getFont()->setBold(true);
        $sheet->getStyle('A12')->getFont()->setBold(true);

        // Balance row highlight
        $sheet->getStyle('A10:B10')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFF3CD');
        $sheet->getStyle('A10:B10')->getFont()->setBold(true);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 30
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }
}
