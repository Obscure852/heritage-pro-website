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

class OverdueReportExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    public function __construct(
        protected Collection $records
    ) {}

    public function collection(): Collection {
        return $this->records->map(function ($record) {
            return [
                $record['book_title'],
                $record['accession_number'],
                $record['borrower_name'],
                $record['borrower_type'],
                $record['checkout_date'],
                $record['due_date'],
                $record['days_overdue'],
                $record['fine_amount'],
            ];
        });
    }

    public function headings(): array {
        return ['Book Title', 'Accession No', 'Borrower', 'Type', 'Checkout Date', 'Due Date', 'Days Overdue', 'Fine Amount (P)'];
    }

    public function styles(Worksheet $sheet): void {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');
    }

    public function columnWidths(): array {
        return [
            'A' => 35,
            'B' => 15,
            'C' => 25,
            'D' => 10,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 15,
        ];
    }

    public function title(): string {
        return 'Overdue Report ' . now()->format('Ymd');
    }
}
