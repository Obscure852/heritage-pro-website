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

class DepartmentAttendanceExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    /**
     * Create a new export instance.
     *
     * @param Collection $records Collection of department summary data
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
                'Department' => $record['department'],
                'Total Records' => $record['total_records'],
                'Present' => $record['present'],
                'Absent' => $record['absent'],
                'Late' => $record['late'],
                'On Leave' => $record['on_leave'],
                'Attendance Rate' => $record['attendance_rate'] . '%',
            ];
        });
    }

    /**
     * Return the headings row.
     *
     * @return array
     */
    public function headings(): array {
        return ['Department', 'Total Records', 'Present', 'Absent', 'Late', 'On Leave', 'Attendance Rate'];
    }

    /**
     * Apply styles to the worksheet.
     *
     * @param Worksheet $sheet
     * @return void
     */
    public function styles(Worksheet $sheet): void {
        // Header row bold with gray background
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()
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
            'B' => 14,
            'C' => 10,
            'D' => 10,
            'E' => 10,
            'F' => 10,
            'G' => 16,
        ];
    }

    /**
     * Return the sheet title.
     *
     * @return string
     */
    public function title(): string {
        return 'Department ' . $this->startDate->format('Ymd') . '-' . $this->endDate->format('Ymd');
    }
}
