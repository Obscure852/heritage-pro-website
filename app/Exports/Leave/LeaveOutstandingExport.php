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

class LeaveOutstandingExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    /**
     * Create a new export instance.
     *
     * @param Collection $balances Outstanding balances data
     * @param int $year The leave year
     * @param string|null $leaveTypeName Optional leave type filter name
     */
    public function __construct(
        protected Collection $balances,
        protected int $year,
        protected ?string $leaveTypeName = null
    ) {}

    /**
     * Return collection of rows for the export.
     *
     * @return Collection
     */
    public function collection(): Collection {
        $rows = [];

        // Summary header rows
        $filterText = $this->leaveTypeName ? "Filtered by: {$this->leaveTypeName}" : 'All Leave Types';
        $totalRecords = $this->balances->count();
        $totalOutstanding = $this->balances->sum('available');

        $rows[] = ['Summary', '', '', '', '', '', ''];
        $rows[] = ['Year', $this->year, '', '', '', '', ''];
        $rows[] = ['Filter', $filterText, '', '', '', '', ''];
        $rows[] = ['Total Records', $totalRecords, '', '', '', '', ''];
        $rows[] = ['Total Outstanding Days', number_format((float) $totalOutstanding, 1), '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', ''];

        // Column headers
        $rows[] = ['Staff Name', 'Department', 'Leave Type', 'Entitled', 'Used', 'Pending', 'Available'];

        // Data rows
        $totalEntitled = 0;
        $totalUsed = 0;
        $totalPending = 0;
        $totalAvailable = 0;

        foreach ($this->balances as $balance) {
            $rows[] = [
                $balance['user_name'] ?? 'Unknown',
                $balance['department'] ?? '-',
                $balance['leave_type_name'] ?? 'Unknown',
                number_format((float) ($balance['entitled'] ?? 0), 1),
                number_format((float) ($balance['used'] ?? 0), 1),
                number_format((float) ($balance['pending'] ?? 0), 1),
                number_format((float) ($balance['available'] ?? 0), 1),
            ];

            $totalEntitled += (float) ($balance['entitled'] ?? 0);
            $totalUsed += (float) ($balance['used'] ?? 0);
            $totalPending += (float) ($balance['pending'] ?? 0);
            $totalAvailable += (float) ($balance['available'] ?? 0);
        }

        // Totals row
        $rows[] = [
            'TOTAL',
            '',
            '',
            number_format($totalEntitled, 1),
            number_format($totalUsed, 1),
            number_format($totalPending, 1),
            number_format($totalAvailable, 1),
        ];

        return collect($rows);
    }

    /**
     * Return the headings row.
     *
     * @return array
     */
    public function headings(): array {
        return ['Outstanding Balances Report - ' . $this->year, '', '', '', '', '', ''];
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
        $sheet->getStyle('A8:G8')->getFont()->setBold(true);
        $sheet->getStyle('A8:G8')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');

        // Totals row (last row)
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A{$lastRow}:G{$lastRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$lastRow}:G{$lastRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E8E8E8');
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
            'C' => 20,
            'D' => 12,
            'E' => 12,
            'F' => 12,
            'G' => 12,
        ];
    }

    /**
     * Return the sheet title.
     *
     * @return string
     */
    public function title(): string {
        return 'Outstanding Balances';
    }
}
