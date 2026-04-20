@extends('layouts.master')

@section('title')
    Monthly Attendance Summary
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('staff-attendance.manual-register.index') }}">Back</a>
        @endslot
        @slot('title')
            Monthly Attendance Summary
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
    .table tfoot { font-weight: 600; background: #f1f5f9; }
    .text-success { color: #16a34a !important; }
    .text-danger { color: #dc2626 !important; }
    .text-warning { color: #d97706 !important; }
    .text-info { color: #0284c7 !important; }
</style>

<div class="header">
    <h4><i class="fas fa-calendar-alt me-2"></i>Monthly Attendance Summary</h4>
    <p>Staff attendance summary for {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</p>
</div>

<div class="report-container">
    <div class="filter-section">
        <form method="GET" action="{{ route('staff-attendance.reports.monthly') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Month</label>
                <select name="month" class="form-select">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                        </option>
                    @endfor
                </select>
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
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('staff-attendance.reports.monthly.export', request()->query()) }}" class="btn-export">
                    <i class="fas fa-file-excel"></i> Export
                </a>
            </div>
        </form>
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
                        <th class="text-center text-success">Present</th>
                        <th class="text-center text-danger">Absent</th>
                        <th class="text-center text-warning">Late</th>
                        <th class="text-center text-info">On Leave</th>
                        <th class="text-center">Half Day</th>
                        <th class="text-end">Total Hours</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                    <tr>
                        <td>{{ $record['user_name'] }}</td>
                        <td>{{ $record['department'] ?? '-' }}</td>
                        <td class="text-center">{{ $record['days_present'] }}</td>
                        <td class="text-center">{{ $record['days_absent'] }}</td>
                        <td class="text-center">{{ $record['days_late'] }}</td>
                        <td class="text-center">{{ $record['days_on_leave'] }}</td>
                        <td class="text-center">{{ $record['days_half_day'] }}</td>
                        <td class="text-end">{{ number_format($record['total_hours'] ?? 0, 1) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"><strong>Totals</strong></td>
                        <td class="text-center">{{ $totals['days_present'] }}</td>
                        <td class="text-center">{{ $totals['days_absent'] }}</td>
                        <td class="text-center">{{ $totals['days_late'] }}</td>
                        <td class="text-center">{{ $totals['days_on_leave'] }}</td>
                        <td class="text-center">-</td>
                        <td class="text-end">{{ number_format($totals['total_hours'] ?? 0, 1) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
@endsection
