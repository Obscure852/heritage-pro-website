@extends('layouts.master')

@section('title')
    Absenteeism Report
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('staff-attendance.manual-register.index') }}">Back</a>
        @endslot
        @slot('title')
            Absenteeism Report
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
    .rate-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    .rate-low { background: #dcfce7; color: #166534; }
    .rate-medium { background: #fef3c7; color: #92400e; }
    .rate-high { background: #fee2e2; color: #991b1b; }
    .alert-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        background: #fee2e2;
        color: #991b1b;
    }
</style>

<div class="header">
    <h4><i class="fas fa-user-times me-2"></i>Absenteeism Report</h4>
    <p>Absence patterns for {{ $startDate->format('M j, Y') }} - {{ $endDate->format('M j, Y') }}</p>
</div>

<div class="report-container">
    <div class="filter-section">
        <form method="GET" action="{{ route('staff-attendance.reports.absenteeism') }}" class="row g-3 align-items-end">
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
                <a href="{{ route('staff-attendance.reports.absenteeism.export', request()->query()) }}" class="btn-export">
                    <i class="fas fa-file-excel"></i> Export
                </a>
            </div>
        </form>
    </div>

    @if($records->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No absences found for the selected criteria.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Department</th>
                        <th>Absent Days</th>
                        <th>Absence Rate</th>
                        <th>Consecutive Alert</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                    <tr>
                        <td>{{ $record['user_name'] }}</td>
                        <td>{{ $record['department'] ?? '-' }}</td>
                        <td>{{ $record['total_absent_days'] }}</td>
                        <td>
                            @php
                                $rate = $record['absence_rate'];
                                $rateClass = $rate <= 5 ? 'rate-low' : ($rate <= 10 ? 'rate-medium' : 'rate-high');
                            @endphp
                            <span class="rate-badge {{ $rateClass }}">
                                {{ number_format($rate, 1) }}%
                            </span>
                        </td>
                        <td>
                            @if($record['has_consecutive_alert'])
                                <span class="alert-badge">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    {{ $record['consecutive_days'] }} consecutive days
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
