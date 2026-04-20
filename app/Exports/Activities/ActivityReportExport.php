<?php

namespace App\Exports\Activities;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ActivityReportExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(private readonly Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows->map(function (array $row) {
            return [
                $row['name'],
                $row['code'],
                $row['category'],
                $row['delivery_mode'],
                $row['status'],
                $row['term_label'],
                $row['fee_type'] ?: 'Not linked',
                $row['active_staff_assignments_count'],
                $row['active_enrollments_count'],
                $row['historical_enrollments_count'],
                $row['sessions_count'],
                $row['completed_sessions_count'],
                $row['attendance_marked_count'],
                $row['attendance_present_count'],
                $row['attendance_absent_count'],
                $row['attendance_present_rate'],
                $row['events_count'],
                $row['completed_events_count'],
                $row['results_count'],
                $row['awards_count'],
                $row['points_total'],
                $row['house_results_count'],
                $row['charge_total_count'],
                $row['charge_posted_count'],
                $row['charge_pending_count'],
                $row['charge_blocked_count'],
                $row['charge_total_amount'],
                $row['charge_outstanding_amount'],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Activity',
            'Code',
            'Category',
            'Delivery',
            'Status',
            'Term',
            'Fee Type',
            'Active Staff',
            'Active Students',
            'Historical Students',
            'Sessions',
            'Completed Sessions',
            'Attendance Marked',
            'Attendance Present',
            'Attendance Absent',
            'Present Rate (%)',
            'Events',
            'Completed Events',
            'Results',
            'Awards',
            'Points',
            'House Results',
            'Charges',
            'Posted Charges',
            'Pending Charges',
            'Blocked Charges',
            'Charge Amount',
            'Outstanding Amount',
        ];
    }
}
