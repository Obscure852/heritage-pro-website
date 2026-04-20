@extends('layouts.master')

@section('title')
    Department Attendance Comparison
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('staff-attendance.manual-register.index') }}">Back</a>
        @endslot
        @slot('title')
            Department Attendance Comparison
        @endslot
    @endcomponent
<style>
    .header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 28px;
        border-radius: 3px 3px 0 0;
    }
    .header h4 { margin: 0; font-weight: 600; }
    .header p { margin: 8px 0 0 0; opacity: 0.9; }
    .report-container {
        background: white;
        border-radius: 0 0 3px 3px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .filter-section {
        background: #f8fafc;
        padding: 20px;
        border-radius: 6px;
        margin-bottom: 24px;
    }
    .btn-export {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 10px 20px;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-export:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: white;
    }
    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 10px 20px;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 500;
        border: none;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white;
    }
    .table th { font-weight: 600; background: #f8fafc; }
    .attendance-rate {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    .rate-excellent { background: #dcfce7; color: #166534; }
    .rate-good { background: #dbeafe; color: #1e40af; }
    .rate-fair { background: #fef3c7; color: #92400e; }
    .rate-poor { background: #fee2e2; color: #991b1b; }
    .progress {
        height: 8px;
        border-radius: 4px;
        background: #e5e7eb;
    }
    .progress-bar {
        border-radius: 4px;
    }
</style>

<div class="header">
    <h4><i class="fas fa-building me-2"></i>Department Attendance Comparison</h4>
    <p>Attendance comparison across departments for {{ $startDate->format('M d') }} - {{ $endDate->format('M d, Y') }}</p>
</div>

<div class="report-container">
    <div class="filter-section">
        <form method="GET" action="{{ route('staff-attendance.reports.department') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('staff-attendance.reports.department.export', request()->query()) }}" class="btn-export">
                    <i class="fas fa-file-excel"></i> Export
                </a>
            </div>
        </form>
    </div>

    @if($records->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No attendance records found for the selected date range.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th class="text-center">Total Records</th>
                        <th class="text-center">Present</th>
                        <th class="text-center">Absent</th>
                        <th class="text-center">Late</th>
                        <th class="text-center">On Leave</th>
                        <th class="text-center" style="min-width: 180px;">Attendance Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                    @php
                        $rate = $record['attendance_rate'];
                        $rateClass = $rate >= 95 ? 'rate-excellent' : ($rate >= 85 ? 'rate-good' : ($rate >= 75 ? 'rate-fair' : 'rate-poor'));
                        $barClass = $rate >= 95 ? 'bg-success' : ($rate >= 85 ? 'bg-primary' : ($rate >= 75 ? 'bg-warning' : 'bg-danger'));
                    @endphp
                    <tr>
                        <td><strong>{{ $record['department'] }}</strong></td>
                        <td class="text-center">{{ $record['total_records'] }}</td>
                        <td class="text-center">{{ $record['present'] }}</td>
                        <td class="text-center">{{ $record['absent'] }}</td>
                        <td class="text-center">{{ $record['late'] }}</td>
                        <td class="text-center">{{ $record['on_leave'] }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar {{ $barClass }}" style="width: {{ $rate }}%"></div>
                                </div>
                                <span class="attendance-rate {{ $rateClass }}">{{ $rate }}%</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @php
            $overallPresent = $records->sum('present');
            $overallTotal = $records->sum('total_records');
            $overallRate = $overallTotal > 0 ? round(($overallPresent / $overallTotal) * 100, 1) : 0;
        @endphp
        <div class="mt-3 p-3 bg-light rounded">
            <strong>Overall Attendance Rate:</strong>
            <span class="attendance-rate {{ $overallRate >= 95 ? 'rate-excellent' : ($overallRate >= 85 ? 'rate-good' : ($overallRate >= 75 ? 'rate-fair' : 'rate-poor')) }} ms-2">
                {{ $overallRate }}%
            </span>
            <span class="text-muted ms-3">({{ $overallPresent }} present out of {{ $overallTotal }} total records)</span>
        </div>
    @endif
</div>
@endsection
