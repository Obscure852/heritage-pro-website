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
use Carbon\Carbon;

class MostBorrowedExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    public function __construct(
        protected Collection $records,
        protected Carbon $startDate,
        protected Carbon $endDate
    ) {}

    public function collection(): Collection {
        $rank = 0;
        return $this->records->map(function ($record) use (&$rank) {
            $rank++;
            return [
                $rank,
                $record->title,
                $record->genre ?: 'Uncategorized',
                $record->grade_name ?: '-',
                $record->checkout_count,
                $record->unique_borrowers,
            ];
        });
    }

    public function headings(): array {
        return ['#', 'Title', 'Category', 'Grade', 'Checkouts', 'Unique Borrowers'];
    }

    public function styles(Worksheet $sheet): void {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');
    }

    public function columnWidths(): array {
        return [
            'A' => 6,
            'B' => 40,
            'C' => 15,
            'D' => 15,
            'E' => 12,
            'F' => 18,
        ];
    }

    public function title(): string {
        return 'Most Borrowed ' . $this->startDate->format('Ymd');
    }
}
