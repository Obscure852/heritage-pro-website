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

class DebtorsListExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(
        protected array $debtors,
        protected ?string $termName = null
    ) {}

    public function collection(): Collection
    {
        $rows = [];

        // Summary header rows
        $totalDebtors = count($this->debtors);
        $totalOutstanding = '0.00';
        foreach ($this->debtors as $debtor) {
            $totalOutstanding = bcadd($totalOutstanding, (string) $debtor['balance'], 2);
        }

        $rows[] = ['Summary', '', '', '', '', '', ''];
        $rows[] = ['Total Debtors', $totalDebtors, '', '', '', '', ''];
        $rows[] = ['Total Outstanding', 'P ' . number_format((float) $totalOutstanding, 2), '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', ''];
        $rows[] = ['Student Name', 'Student Number', 'Grade', 'Total Invoiced', 'Total Paid', 'Balance', 'Days Overdue'];

        // Data rows
        foreach ($this->debtors as $debtor) {
            $rows[] = [
                $debtor['student_name'],
                $debtor['student_number'],
                $debtor['grade_name'],
                'P ' . number_format((float) $debtor['total_invoiced'], 2),
                'P ' . number_format((float) $debtor['total_paid'], 2),
                'P ' . number_format((float) $debtor['balance'], 2),
                $debtor['days_overdue'],
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return ['Debtors List Report', '', '', '', '', '', ''];
    }

    public function styles(Worksheet $sheet): void
    {
        // Title row
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Summary section
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getFont()->setBold(true);

        // Data column headers (row 6)
        $sheet->getStyle('A6:G6')->getFont()->setBold(true);
        $sheet->getStyle('A6:G6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 18,
            'C' => 15,
            'D' => 18,
            'E' => 18,
            'F' => 18,
            'G' => 15
        ];
    }

    public function title(): string
    {
        return 'Debtors List';
    }
}
