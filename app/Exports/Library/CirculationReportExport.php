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

class CirculationReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    public function __construct(
        protected Collection $records,
        protected Carbon $startDate,
        protected Carbon $endDate
    ) {}

    public function collection(): Collection {
        return $this->records->map(function ($record) {
            return [
                $record['checkout_date'],
                $record['return_date'],
                $record['book_title'],
                $record['accession_number'],
                $record['borrower_name'],
                $record['borrower_type'],
                $record['status'],
            ];
        });
    }

    public function headings(): array {
        return ['Checkout Date', 'Return Date', 'Book Title', 'Accession No', 'Borrower', 'Type', 'Status'];
    }

    public function styles(Worksheet $sheet): void {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');
    }

    public function columnWidths(): array {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 35,
            'D' => 15,
            'E' => 25,
            'F' => 10,
            'G' => 15,
        ];
    }

    public function title(): string {
        return 'Circulation ' . $this->startDate->format('Ymd') . '-' . $this->endDate->format('Ymd');
    }
}
