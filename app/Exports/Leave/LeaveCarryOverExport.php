<?php

namespace App\Exports\Leave;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LeaveCarryOverExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    /**
     * Create a new export instance.
     *
     * @param Collection $carryover Carry-over data
     * @param int $fromYear Source year
     * @param int $toYear Target year
     */
    public function __construct(
        protected Collection $carryover,
        protected int $fromYear,
        protected int $toYear
    ) {}

    /**
     * Return collection of rows for the export.
     *
     * @return Collection
     */
    public function collection(): Collection {
        $rows = [];

        // Summary header
        $totalCarried = $this->carryover->sum('carried_over');
        $totalForfeited = $this->carryover->sum('forfeited');

        $rows[] = ['Summary', '', '', '', '', ''];
        $rows[] = ['Period', "{$this->fromYear} to {$this->toYear}", '', '', '', ''];
        $rows[] = ['Total Carried Over', number_format((float) $totalCarried, 1) . ' days', '', '', '', ''];
        $rows[] = ['Total Forfeited', number_format((float) $totalForfeited, 1) . ' days', '', '', '', ''];
        $rows[] = ['Total Records', $this->carryover->count(), '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        // Column headers
        $rows[] = ['Staff Name', 'Leave Type', 'Previous Balance', 'Limit', 'Carried Over', 'Forfeited'];

        // Data rows
        $sumPrevious = 0;
        $sumCarried = 0;
        $sumForfeited = 0;

        foreach ($this->carryover as $item) {
            $rows[] = [
                $item['user_name'] ?? 'Unknown',
                $item['leave_type_name'] ?? 'Unknown',
                number_format((float) ($item['previous_year_balance'] ?? 0), 1),
                number_format((float) ($item['carry_over_limit'] ?? 0), 1),
                number_format((float) ($item['carried_over'] ?? 0), 1),
                number_format((float) ($item['forfeited'] ?? 0), 1),
            ];

            $sumPrevious += (float) ($item['previous_year_balance'] ?? 0);
            $sumCarried += (float) ($item['carried_over'] ?? 0);
            $sumForfeited += (float) ($item['forfeited'] ?? 0);
        }

        // Totals row
        $rows[] = [
            'TOTAL',
            '',
            number_format($sumPrevious, 1),
            '',
            number_format($sumCarried, 1),
            number_format($sumForfeited, 1),
        ];

        return collect($rows);
    }

    /**
     * Return the headings row.
     *
     * @return array
     */
    public function headings(): array {
        return ["Carry-Over Report {$this->fromYear} to {$this->toYear}", '', '', '', '', ''];
    }

    /**
     * Apply styles to the worksheet.
     *
     * @param Worksheet $sheet
     * @return void
     */
    public function styles(Worksheet $sheet): void {
        // Title row
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Summary section label
        $sheet->getStyle('A2')->getFont()->setBold(true);

        // Column headers (row 8)
        $sheet->getStyle('A8:F8')->getFont()->setBold(true);
        $sheet->getStyle('A8:F8')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');

        // Totals row (last row)
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A{$lastRow}:F{$lastRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$lastRow}:F{$lastRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E8E8E8');

        // Highlight forfeited column where forfeited > 0
        $dataStartRow = 9;
        $dataEndRow = $lastRow - 1;
        for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
            $forfeitedValue = $sheet->getCell("F{$row}")->getValue();
            $numericValue = str_replace(',', '', $forfeitedValue);
            if (is_numeric($numericValue) && (float) $numericValue > 0) {
                $sheet->getStyle("F{$row}")->getFont()->getColor()->setRGB('DC2626');
            }
        }
    }

    /**
     * Define column widths.
     *
     * @return array
     */
    public function columnWidths(): array {
        return [
            'A' => 30,
            'B' => 20,
            'C' => 15,
            'D' => 12,
            'E' => 15,
            'F' => 12,
        ];
    }

    /**
     * Return the sheet title.
     *
     * @return string
     */
    public function title(): string {
        return 'Carry Over';
    }
}
