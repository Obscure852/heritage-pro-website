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

class LeaveUtilizationExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    /**
     * Create a new export instance.
     *
     * @param array $stats Organization-level stats
     * @param Collection $distribution Leave type distribution
     * @param int $year The leave year
     */
    public function __construct(
        protected array $stats,
        protected Collection $distribution,
        protected int $year
    ) {}

    /**
     * Return collection of rows for the export.
     *
     * @return Collection
     */
    public function collection(): Collection {
        $rows = [];

        // Summary section header
        $rows[] = ['Summary', '', '', '', '', '', '', ''];
        $rows[] = ['Total Staff', $this->stats['total_staff'] ?? 0, '', '', '', '', '', ''];
        $rows[] = ['Total Entitled Days', number_format((float) ($this->stats['total_entitled'] ?? 0), 1), '', '', '', '', '', ''];
        $rows[] = ['Total Used Days', number_format((float) ($this->stats['total_used'] ?? 0), 1), '', '', '', '', '', ''];
        $rows[] = ['Total Pending Days', number_format((float) ($this->stats['total_pending'] ?? 0), 1), '', '', '', '', '', ''];
        $rows[] = ['Total Available Days', number_format((float) ($this->stats['total_available'] ?? 0), 1), '', '', '', '', '', ''];
        $rows[] = ['Utilization Rate', ($this->stats['utilization_rate'] ?? 0) . '%', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', '', ''];

        // Distribution section header
        $rows[] = ['Leave Type Distribution', '', '', '', '', '', '', ''];
        $rows[] = ['Leave Type', 'Code', 'Staff Count', 'Entitled', 'Used', 'Pending', 'Available', 'Usage %'];

        // Distribution data rows
        $totalEntitled = 0;
        $totalUsed = 0;
        $totalPending = 0;
        $totalAvailable = 0;

        foreach ($this->distribution as $item) {
            $rows[] = [
                $item['leave_type_name'] ?? 'Unknown',
                $item['leave_type_code'] ?? '',
                $item['staff_count'] ?? 0,
                number_format((float) ($item['total_entitled'] ?? 0), 1),
                number_format((float) ($item['total_used'] ?? 0), 1),
                number_format((float) ($item['total_pending'] ?? 0), 1),
                number_format((float) ($item['total_available'] ?? 0), 1),
                ($item['usage_percentage'] ?? 0) . '%',
            ];

            $totalEntitled += (float) ($item['total_entitled'] ?? 0);
            $totalUsed += (float) ($item['total_used'] ?? 0);
            $totalPending += (float) ($item['total_pending'] ?? 0);
            $totalAvailable += (float) ($item['total_available'] ?? 0);
        }

        // Totals row
        $overallUsagePercent = $totalEntitled > 0 ? round(($totalUsed / $totalEntitled) * 100, 1) : 0;
        $rows[] = [
            'TOTAL',
            '',
            '',
            number_format($totalEntitled, 1),
            number_format($totalUsed, 1),
            number_format($totalPending, 1),
            number_format($totalAvailable, 1),
            $overallUsagePercent . '%',
        ];

        return collect($rows);
    }

    /**
     * Return the headings row.
     *
     * @return array
     */
    public function headings(): array {
        return ['Leave Utilization Report - ' . $this->year, '', '', '', '', '', '', ''];
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

        // Distribution section header
        $sheet->getStyle('A10')->getFont()->setBold(true);

        // Column headers (row 11)
        $sheet->getStyle('A11:H11')->getFont()->setBold(true);
        $sheet->getStyle('A11:H11')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');

        // Totals row (last row)
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A{$lastRow}:H{$lastRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$lastRow}:H{$lastRow}")->getFill()
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
            'A' => 25,
            'B' => 10,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 12,
            'G' => 12,
            'H' => 12,
        ];
    }

    /**
     * Return the sheet title.
     *
     * @return string
     */
    public function title(): string {
        return "Leave Utilization {$this->year}";
    }
}
