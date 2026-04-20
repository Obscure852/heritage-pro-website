<?php

namespace App\Exports\StaffAttendance;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class HoursWorkedExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    /**
     * Create a new export instance.
     *
     * @param Collection $records Collection of hours worked data
     * @param Carbon $startDate Start date of report range
     * @param Carbon $endDate End date of report range
     */
    public function __construct(
        protected Collection $records,
        protected Carbon $startDate,
        protected Carbon $endDate
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
                'Days Worked' => $record['days_worked'],
                'Total Hours' => number_format($record['total_hours'], 2),
                'Avg Hours/Day' => number_format($record['average_hours'], 2),
                'Overtime' => number_format($record['overtime_hours'], 2),
            ];
        });
    }

    /**
     * Return the headings row.
     *
     * @return array
     */
    public function headings(): array {
        return ['Staff Name', 'Department', 'Days Worked', 'Total Hours', 'Avg Hours/Day', 'Overtime'];
    }

    /**
     * Apply styles to the worksheet.
     *
     * @param Worksheet $sheet
     * @return void
     */
    public function styles(Worksheet $sheet): void {
        // Header row bold with gray background
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()
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
            'C' => 12,
            'D' => 12,
            'E' => 14,
            'F' => 12,
        ];
    }

    /**
     * Return the sheet title.
     *
     * @return string
     */
    public function title(): string {
        return 'Hours ' . $this->startDate->format('Ymd') . '-' . $this->endDate->format('Ymd');
    }
}
