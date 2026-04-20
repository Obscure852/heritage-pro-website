@extends('layouts.master')

@section('title')
    Hours Worked Report
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('staff-attendance.manual-register.index') }}">Back</a>
        @endslot
        @slot('title')
            Hours Worked Report
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
    .table tfoot th {
        background: #e5e7eb;
        font-weight: 700;
    }
    .overtime-badge {
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 500;
        background: #dbeafe;
        color: #1e40af;
    }
</style>

<div class="header">
    <h4><i class="fas fa-hourglass-half me-2"></i>Hours Worked Report</h4>
    <p>Working hours for {{ $startDate->format('M j, Y') }} - {{ $endDate->format('M j, Y') }}</p>
</div>

<div class="report-container">
    <div class="filter-section">
        <form method="GET" action="{{ route('staff-attendance.reports.hours-worked') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
            </div>
            <div class="col-md-2">
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
                <a href="{{ route('staff-attendance.reports.hours-worked.export', request()->query()) }}" class="btn-export">
                    <i class="fas fa-file-excel"></i> Export
                </a>
            </div>
        </form>
    </div>

    <div class="summary-stats">
        <div class="stat-item" style="border-color: #3b82f6;">
            <div class="stat-label">Total Hours</div>
            <div class="stat-value">{{ number_format($totals['total_hours'], 1) }}</div>
        </div>
        <div class="stat-item" style="border-color: #8b5cf6;">
            <div class="stat-label">Overtime Hours</div>
            <div class="stat-value">{{ number_format($totals['overtime_hours'], 1) }}</div>
        </div>
    </div>

    @if($records->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No hours worked data found for the selected criteria.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Department</th>
                        <th>Days Worked</th>
                        <th>Total Hours</th>
                        <th>Avg Hours/Day</th>
                        <th>Overtime</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                    <tr>
                        <td>{{ $record['user_name'] }}</td>
                        <td>{{ $record['department'] ?? '-' }}</td>
                        <td>{{ $record['days_worked'] }}</td>
                        <td>{{ number_format($record['total_hours'], 2) }}</td>
                        <td>{{ number_format($record['average_hours'], 2) }}</td>
                        <td>
                            @if($record['overtime_hours'] > 0)
                                <span class="overtime-badge">{{ number_format($record['overtime_hours'], 2) }} hrs</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Totals</th>
                        <th>{{ number_format($totals['total_hours'], 2) }}</th>
                        <th>-</th>
                        <th>{{ number_format($totals['overtime_hours'], 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
@endsection
