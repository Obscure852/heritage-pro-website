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

class OutstandingByGradeExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths
{
    public function __construct(
        protected array $data,
        protected ?string $termName = null
    ) {}

    public function collection(): Collection
    {
        $rows = [];

        $totalStudents = 0;
        $totalOutstanding = '0.00';

        foreach ($this->data as $grade) {
            $rows[] = [
                $grade['grade_name'],
                $grade['student_count'],
                'P ' . number_format((float) $grade['total_outstanding'], 2),
                'P ' . number_format((float) $grade['average_per_student'], 2),
            ];

            $totalStudents += $grade['student_count'];
            $totalOutstanding = bcadd($totalOutstanding, (string) $grade['total_outstanding'], 2);
        }

        // Totals row
        $averageOverall = $totalStudents > 0
            ? bcdiv($totalOutstanding, (string) $totalStudents, 2)
            : '0.00';

        $rows[] = [
            'TOTAL',
            $totalStudents,
            'P ' . number_format((float) $totalOutstanding, 2),
            'P ' . number_format((float) $averageOverall, 2),
        ];

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Grade',
            'Student Count',
            'Total Outstanding',
            'Average per Student'
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        // Header row styling
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getFill()
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
            'A' => 20,
            'B' => 18,
            'C' => 22,
            'D' => 22
        ];
    }

    public function title(): string
    {
        return 'Outstanding by Grade';
    }
}
