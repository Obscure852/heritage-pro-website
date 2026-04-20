<?php

namespace App\Exports\StaffAttendance;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MonthlyAttendanceExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    /**
     * Create a new export instance.
     *
     * @param Collection $records Collection of monthly summary data
     * @param int $year The year
     * @param int $month The month
     */
    public function __construct(
        protected Collection $records,
        protected int $year,
        protected int $month
    ) {}

    /**
     * Return collection of rows for the export.
     *
     * @return Collection
     */
    public function collection(): Collection {
        return $this->records->map(function ($record) {
            return [
                'Staff Name' => $record['user_name'],
                'Department' => $record['department'] ?? '-',
                'Present' => $record['days_present'],
                'Absent' => $record['days_absent'],
                'Late' => $record['days_late'],
                'On Leave' => $record['days_on_leave'],
                'Half Day' => $record['days_half_day'],
                'Total Hours' => number_format($record['total_hours'], 2),
            ];
        });
    }

    /**
     * Return the headings row.
     *
     * @return array
     */
    public function headings(): array {
        return ['Staff Name', 'Department', 'Present', 'Absent', 'Late', 'On Leave', 'Half Day', 'Total Hours'];
    }

    /**
     * Apply styles to the worksheet.
     *
     * @param Worksheet $sheet
     * @return void
     */
    public function styles(Worksheet $sheet): void {
        // Header row bold with gray background
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');
    }

    /**
     * Define column widths.
     *
     * @return array
     */
    public function columnWidths(): array {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 10,
            'D' => 10,
            'E' => 10,
            'F' => 10,
            'G' => 10,
            'H' => 12,
        ];
    }

    /**
     * Return the sheet title.
     *
     * @return string
     */
    public function title(): string {
        $monthName = date('F', mktime(0, 0, 0, $this->month, 1));
        return "Monthly {$monthName} {$this->year}";
    }
}
