<?php

namespace App\Exports\Library;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CollectionDevelopmentExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    public function __construct(
        protected Collection $records
    ) {}

    public function collection(): Collection {
        return $this->records->map(function ($record) {
            return [
                $record->genre ?: 'Uncategorized',
                $record->grade_name ?: '-',
                $record->total_titles,
                $record->total_copies,
                $record->available_copies,
                $record->checked_out_copies,
                $record->lost_copies,
                $record->utilization_rate . '%',
            ];
        });
    }

    public function headings(): array {
        return ['Category', 'Grade', 'Total Titles', 'Total Copies', 'Available', 'Checked Out', 'Lost', 'Utilization %'];
    }

    public function styles(Worksheet $sheet): void {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');
    }

    public function columnWidths(): array {
        return [
            'A' => 20,
            'B' => 15,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 14,
            'G' => 10,
            'H' => 14,
        ];
    }

    public function title(): string {
        return 'Collection Development';
    }
}
