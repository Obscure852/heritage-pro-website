@extends('layouts.master')

@section('title')
    Daily Attendance Report
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('staff-attendance.manual-register.index') }}">Back</a>
        @endslot
        @slot('title')
            Daily Attendance Report
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
    .status-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .status-present { background: #dcfce7; color: #166534; }
    .status-absent { background: #fee2e2; color: #991b1b; }
    .status-late { background: #fef3c7; color: #92400e; }
    .status-on_leave { background: #dbeafe; color: #1e40af; }
    .status-half_day { background: #fae8ff; color: #86198f; }
    .status-holiday { background: #f3f4f6; color: #374151; }
    .summary-stats {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .stat-item {
        background: #f8fafc;
        padding: 12px 20px;
        border-radius: 6px;
        border-left: 4px solid #3b82f6;
    }
    .stat-label { font-size: 12px; color: #6b7280; }
    .stat-value { font-size: 20px; font-weight: 600; color: #1f2937; }
    .table th { font-weight: 600; background: #f8fafc; }
</style>

<div class="header">
    <h4><i class="fas fa-calendar-day me-2"></i>Daily Attendance Report</h4>
    <p>Staff attendance for {{ $date->format('l, F j, Y') }}</p>
</div>

<div class="report-container">
    <div class="filter-section">
        <form method="GET" action="{{ route('staff-attendance.reports.daily') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="{{ $date->format('Y-m-d') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Department</label>
                <select name="department" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ $department == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Staff</label>
                <select name="user_id" class="form-select">
                    <option value="">All Staff</option>
                    @foreach($staff as $user)
                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                            {{ $user->firstname }} {{ $user->lastname }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('staff-attendance.reports.daily.export', request()->query()) }}" class="btn-export">
                    <i class="fas fa-file-excel"></i> Export
                </a>
            </div>
        </form>
    </div>

    @php
        $presentCount = $records->whereIn('status', ['present', 'late'])->count();
        $absentCount = $records->where('status', 'absent')->count();
        $lateCount = $records->where('status', 'late')->count();
        $onLeaveCount = $records->where('status', 'on_leave')->count();
    @endphp

    <div class="summary-stats">
        <div class="stat-item" style="border-color: #10b981;">
            <div class="stat-label">Present</div>
            <div class="stat-value">{{ $presentCount }}</div>
        </div>
        <div class="stat-item" style="border-color: #ef4444;">
            <div class="stat-label">Absent</div>
            <div class="stat-value">{{ $absentCount }}</div>
        </div>
        <div class="stat-item" style="border-color: #f59e0b;">
            <div class="stat-label">Late</div>
            <div class="stat-value">{{ $lateCount }}</div>
        </div>
        <div class="stat-item" style="border-color: #3b82f6;">
            <div class="stat-label">On Leave</div>
            <div class="stat-value">{{ $onLeaveCount }}</div>
        </div>
    </div>

    @if($records->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No attendance records found for the selected criteria.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Hours Worked</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                    <tr>
                        <td>{{ $record->user->firstname ?? '' }} {{ $record->user->lastname ?? '' }}</td>
                        <td>{{ $record->user->department ?? '-' }}</td>
                        <td>
                            <span class="status-badge status-{{ $record->status }}">
                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                            </span>
                        </td>
                        <td>{{ $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('H:i') : '-' }}</td>
                        <td>{{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('H:i') : '-' }}</td>
                        <td>{{ $record->hours_worked ? number_format($record->hours_worked, 2) : '-' }}</td>
                        <td>{{ $record->notes ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
