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

class FineCollectionExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    public function __construct(
        protected Collection $records,
        protected array $summary,
        protected Carbon $startDate,
        protected Carbon $endDate
    ) {}

    public function collection(): Collection {
        $rows = [];

        // Summary section
        $rows[] = ['Fine Collection Summary', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['Period', $this->startDate->format('d M Y') . ' - ' . $this->endDate->format('d M Y'), '', '', '', '', '', '', '', ''];
        $rows[] = ['Total Fines', $this->summary['fine_count'], '', '', '', '', '', '', '', ''];
        $rows[] = ['Total Assessed', 'P ' . number_format((float) $this->summary['total_assessed'], 2), '', '', '', '', '', '', '', ''];
        $rows[] = ['Total Collected', 'P ' . number_format((float) $this->summary['total_collected'], 2), '', '', '', '', '', '', '', ''];
        $rows[] = ['Total Waived', 'P ' . number_format((float) $this->summary['total_waived'], 2), '', '', '', '', '', '', '', ''];
        $rows[] = ['Total Outstanding', 'P ' . number_format((float) $this->summary['total_outstanding'], 2), '', '', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['Detail', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['Date', 'Book', 'Borrower', 'Type', 'Fine Type', 'Assessed', 'Paid', 'Waived', 'Outstanding', 'Status'];

        // Detail rows
        foreach ($this->records as $record) {
            $rows[] = [
                $record['fine_date'],
                $record['book_title'],
                $record['borrower_name'],
                $record['borrower_type'],
                $record['fine_type'],
                $record['amount'],
                $record['amount_paid'],
                $record['amount_waived'],
                $record['outstanding'],
                $record['status'],
            ];
        }

        return collect($rows);
    }

    public function headings(): array {
        return ['Fine Collection Report', '', '', '', '', '', '', '', '', ''];
    }

    public function styles(Worksheet $sheet): void {
        // Title row (A1)
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Summary labels (A2:A8)
        $sheet->getStyle('A2:A8')->getFont()->setBold(true);

        // Detail section header (A10)
        $sheet->getStyle('A10')->getFont()->setBold(true);

        // Detail column headers (A11:J11)
        $sheet->getStyle('A11:J11')->getFont()->setBold(true);
        $sheet->getStyle('A11:J11')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');
    }

    public function columnWidths(): array {
        return [
            'A' => 15,
            'B' => 35,
            'C' => 25,
            'D' => 10,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 12,
            'I' => 14,
            'J' => 10,
        ];
    }

    public function title(): string {
        return 'Fine Collection ' . $this->startDate->format('Ymd');
    }
}
