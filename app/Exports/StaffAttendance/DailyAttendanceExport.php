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

class DailyAttendanceExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    /**
     * Create a new export instance.
     *
     * @param Collection $records Collection of attendance records
     * @param Carbon $date The report date
     */
    public function __construct(
        protected Collection $records,
        protected Carbon $date
    ) {}

    /**
     * Return collection of rows for the export.
     *
     * @return Collection
     */
    public function collection(): Collection {
        return $this->records->map(function ($record) {
            return [
                'Staff Name' => $record->user ? ($record->user->firstname . ' ' . $record->user->lastname) : 'Unknown',
                'Department' => $record->user->department ?? '-',
                'Status' => ucfirst(str_replace('_', ' ', $record->status)),
                'Clock In' => $record->clock_in ? Carbon::parse($record->clock_in)->format('H:i') : '-',
                'Clock Out' => $record->clock_out ? Carbon::parse($record->clock_out)->format('H:i') : '-',
                'Hours Worked' => $record->hours_worked ? number_format($record->hours_worked, 2) : '-',
                'Notes' => $record->notes ?? '-',
            ];
        });
    }

    /**
     * Return the headings row.
     *
     * @return array
     */
    public function headings(): array {
        return ['Staff Name', 'Department', 'Status', 'Clock In', 'Clock Out', 'Hours Worked', 'Notes'];
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
            'B' => 20,
            'C' => 12,
            'D' => 10,
            'E' => 10,
            'F' => 14,
            'G' => 30,
        ];
    }

    /**
     * Return the sheet title.
     *
     * @return string
     */
    public function title(): string {
        return 'Daily Attendance ' . $this->date->format('Y-m-d');
    }
}
