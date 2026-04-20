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

class LeaveTeamSummaryExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    /**
     * Create a new export instance.
     *
     * @param array $summary Team summary data
     * @param Collection $upcomingLeave Upcoming leave for team
     * @param int $year The leave year
     */
    public function __construct(
        protected array $summary,
        protected Collection $upcomingLeave,
        protected int $year
    ) {}

    /**
     * Return collection of rows for the export.
     *
     * @return Collection
     */
    public function collection(): Collection {
        $rows = [];

        // Team Summary header
        $rows[] = ['Team Summary', '', '', '', '', ''];
        $rows[] = ['Year', $this->year, '', '', '', ''];
        $rows[] = ['Team Size', $this->summary['team_size'] ?? 0, '', '', '', ''];
        $rows[] = ['Pending Requests', $this->summary['pending_requests'] ?? 0, '', '', '', ''];
        $rows[] = ['', '', '', '', '', ''];

        // Balances by type section
        $rows[] = ['Balances by Leave Type', '', '', '', '', ''];
        $rows[] = ['Leave Type', 'Entitled', 'Used', 'Pending', 'Available', ''];

        $balancesByType = $this->summary['balances_by_type'] ?? [];
        $totalEntitled = 0;
        $totalUsed = 0;
        $totalPending = 0;
        $totalAvailable = 0;

        foreach ($balancesByType as $balance) {
            $rows[] = [
                $balance['leave_type_name'] ?? 'Unknown',
                number_format((float) ($balance['total_entitled'] ?? 0), 1),
                number_format((float) ($balance['total_used'] ?? 0), 1),
                number_format((float) ($balance['total_pending'] ?? 0), 1),
                number_format((float) ($balance['total_available'] ?? 0), 1),
                '',
            ];

            $totalEntitled += (float) ($balance['total_entitled'] ?? 0);
            $totalUsed += (float) ($balance['total_used'] ?? 0);
            $totalPending += (float) ($balance['total_pending'] ?? 0);
            $totalAvailable += (float) ($balance['total_available'] ?? 0);
        }

        // Balances totals
        $rows[] = [
            'TOTAL',
            number_format($totalEntitled, 1),
            number_format($totalUsed, 1),
            number_format($totalPending, 1),
            number_format($totalAvailable, 1),
            '',
        ];

        $rows[] = ['', '', '', '', '', ''];

        // Upcoming leave section
        $rows[] = ['Upcoming Leave (Next 30 Days)', '', '', '', '', ''];
        $rows[] = ['Staff Name', 'Leave Type', 'Start Date', 'End Date', 'Days', ''];

        foreach ($this->upcomingLeave as $leave) {
            $rows[] = [
                $leave['user_name'] ?? 'Unknown',
                $leave['leave_type'] ?? 'Unknown',
                $leave['start_date'] ?? '',
                $leave['end_date'] ?? '',
                number_format((float) ($leave['total_days'] ?? 0), 1),
                '',
            ];
        }

        if ($this->upcomingLeave->isEmpty()) {
            $rows[] = ['No upcoming leave scheduled', '', '', '', '', ''];
        }

        return collect($rows);
    }

    /**
     * Return the headings row.
     *
     * @return array
     */
    public function headings(): array {
        return ['Team Leave Summary - ' . $this->year, '', '', '', '', ''];
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

        // Team Summary section label
        $sheet->getStyle('A2')->getFont()->setBold(true);

        // Balances by type section header
        $sheet->getStyle('A7')->getFont()->setBold(true);

        // Balances column headers (row 8)
        $sheet->getStyle('A8:E8')->getFont()->setBold(true);
        $sheet->getStyle('A8:E8')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');

        // Find totals row for balances (search for "TOTAL" in column A)
        $highestRow = $sheet->getHighestRow();
        for ($row = 9; $row <= $highestRow; $row++) {
            if ($sheet->getCell("A{$row}")->getValue() === 'TOTAL') {
                $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:E{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E8E8E8');
                break;
            }
        }

        // Upcoming leave section header
        for ($row = 1; $row <= $highestRow; $row++) {
            if ($sheet->getCell("A{$row}")->getValue() === 'Upcoming Leave (Next 30 Days)') {
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);

                // Column headers for upcoming leave
                $headerRow = $row + 1;
                $sheet->getStyle("A{$headerRow}:E{$headerRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$headerRow}:E{$headerRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F2F2F2');
                break;
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
            'A' => 25,
            'B' => 15,
            'C' => 12,
            'D' => 12,
            'E' => 12,
            'F' => 10,
        ];
    }

    /**
     * Return the sheet title.
     *
     * @return string
     */
    public function title(): string {
        return 'Team Summary';
    }
}
