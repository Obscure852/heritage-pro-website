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

class BorrowerActivityExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    public function __construct(
        protected $data,
        protected string $mode,
        protected Carbon $startDate,
        protected Carbon $endDate
    ) {}

    public function collection(): Collection {
        if ($this->mode === 'individual') {
            return collect($this->data['records'])->map(function ($record) {
                return [
                    $record['checkout_date'],
                    $record['return_date'],
                    $record['book_title'],
                    $record['accession_number'],
                    $record['status'],
                    $record['fine_amount'],
                ];
            });
        }

        // Aggregate mode
        return collect($this->data)->map(function ($record) {
            return [
                $record['borrower_name'],
                $record['borrower_type'],
                $record['total_checkouts'],
                $record['total_returns'],
                $record['currently_active'],
                $record['overdue_count'],
            ];
        });
    }

    public function headings(): array {
        if ($this->mode === 'individual') {
            return ['Checkout Date', 'Return Date', 'Book Title', 'Accession No', 'Status', 'Fine Amount'];
        }

        return ['Borrower', 'Type', 'Total Checkouts', 'Returns', 'Active Loans', 'Overdue'];
    }

    public function styles(Worksheet $sheet): void {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');
    }

    public function columnWidths(): array {
        if ($this->mode === 'individual') {
            return [
                'A' => 15,
                'B' => 15,
                'C' => 35,
                'D' => 15,
                'E' => 15,
                'F' => 12,
            ];
        }

        return [
            'A' => 30,
            'B' => 10,
            'C' => 16,
            'D' => 10,
            'E' => 14,
            'F' => 10,
        ];
    }

    public function title(): string {
        return 'Borrower Activity ' . $this->startDate->format('Ymd');
    }
}
