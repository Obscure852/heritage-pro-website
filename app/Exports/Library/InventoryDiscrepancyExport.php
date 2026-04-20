<?php

namespace App\Exports\Library;

use App\Models\Library\InventorySession;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventoryDiscrepancyExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    public function __construct(
        protected Collection $discrepancies,
        protected InventorySession $session
    ) {}

    public function collection(): Collection {
        return $this->discrepancies->map(function ($copy) {
            return [
                $copy->accession_number,
                $copy->book->title ?? '-',
                $copy->book->location ?? '-',
                $copy->book->genre ?? '-',
                ucfirst(str_replace('_', ' ', $copy->status)),
            ];
        });
    }

    public function headings(): array {
        return ['Accession No', 'Book Title', 'Location', 'Genre', 'Last Known Status'];
    }

    public function styles(Worksheet $sheet): void {
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');
    }

    public function columnWidths(): array {
        return [
            'A' => 18,
            'B' => 40,
            'C' => 20,
            'D' => 20,
            'E' => 18,
        ];
    }

    public function title(): string {
        return 'Discrepancies Session ' . $this->session->id;
    }
}
