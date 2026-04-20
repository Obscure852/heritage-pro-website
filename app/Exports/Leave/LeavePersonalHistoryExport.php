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

class LeavePersonalHistoryExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths {
    /**
     * Create a new export instance.
     *
     * @param Collection $requests User's leave request history
     * @param string $userName The user's name
     * @param int $year The leave year
     */
    public function __construct(
        protected Collection $requests,
        protected string $userName,
        protected int $year
    ) {}

    /**
     * Return collection of rows for the export.
     *
     * @return Collection
     */
    public function collection(): Collection {
        $rows = [];

        // Summary header
        $totalRequests = $this->requests->count();
        $approvedCount = $this->requests->where('status', 'approved')->count();
        $pendingCount = $this->requests->where('status', 'pending')->count();
        $rejectedCount = $this->requests->where('status', 'rejected')->count();
        $cancelledCount = $this->requests->where('status', 'cancelled')->count();
        $totalDays = $this->requests->where('status', 'approved')->sum('total_days');

        $rows[] = ['Summary', '', '', '', '', '', '', ''];
        $rows[] = ['Total Requests', $totalRequests, '', '', '', '', '', ''];
        $rows[] = ['Approved', $approvedCount, '', '', '', '', '', ''];
        $rows[] = ['Pending', $pendingCount, '', '', '', '', '', ''];
        $rows[] = ['Rejected', $rejectedCount, '', '', '', '', '', ''];
        $rows[] = ['Cancelled', $cancelledCount, '', '', '', '', '', ''];
        $rows[] = ['Total Days Taken', number_format((float) $totalDays, 1), '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', '', ''];

        // Column headers
        $rows[] = ['Request ID', 'Leave Type', 'Start Date', 'End Date', 'Days', 'Status', 'Submitted', 'Approved By'];

        // Data rows
        foreach ($this->requests as $request) {
            $approvedBy = '-';
            if ($request->approver) {
                $approvedBy = $request->approver->full_name ?? ($request->approver->firstname . ' ' . $request->approver->lastname);
            }

            $rows[] = [
                $request->public_id ?? $request->id,
                $request->leaveType->name ?? 'Unknown',
                $request->start_date ? $request->start_date->format('Y-m-d') : '',
                $request->end_date ? $request->end_date->format('Y-m-d') : '',
                number_format((float) $request->total_days, 1),
                ucfirst($request->status),
                $request->created_at ? $request->created_at->format('Y-m-d') : '',
                $approvedBy,
            ];
        }

        if ($this->requests->isEmpty()) {
            $rows[] = ['No leave requests found for this period', '', '', '', '', '', '', ''];
        }

        return collect($rows);
    }

    /**
     * Return the headings row.
     *
     * @return array
     */
    public function headings(): array {
        return ["Leave History for {$this->userName} - {$this->year}", '', '', '', '', '', '', ''];
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

        // Column headers (row 10)
        $sheet->getStyle('A10:H10')->getFont()->setBold(true);
        $sheet->getStyle('A10:H10')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('F2F2F2');

        // Apply status color coding
        $highestRow = $sheet->getHighestRow();
        for ($row = 11; $row <= $highestRow; $row++) {
            $status = strtolower($sheet->getCell("F{$row}")->getValue() ?? '');
            switch ($status) {
                case 'approved':
                    $sheet->getStyle("F{$row}")->getFont()->getColor()->setRGB('16A34A');
                    break;
                case 'pending':
                    $sheet->getStyle("F{$row}")->getFont()->getColor()->setRGB('CA8A04');
                    break;
                case 'rejected':
                    $sheet->getStyle("F{$row}")->getFont()->getColor()->setRGB('DC2626');
                    break;
                case 'cancelled':
                    $sheet->getStyle("F{$row}")->getFont()->getColor()->setRGB('6B7280');
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
            'A' => 15,
            'B' => 15,
            'C' => 12,
            'D' => 12,
            'E' => 8,
            'F' => 12,
            'G' => 15,
            'H' => 20,
        ];
    }

    /**
     * Return the sheet title.
     *
     * @return string
     */
    public function title(): string {
        return 'Leave History';
    }
}
