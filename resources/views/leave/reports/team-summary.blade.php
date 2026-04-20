@extends('layouts.master')
@section('title')
    Team Leave Summary
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <style>
        .report-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .report-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .report-header h3 {
            margin: 0;
        }

        .report-header p {
            margin: 6px 0 0 0;
            opacity: .9;
        }

        .year-selector {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .year-select {
            max-width: 200px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            color: #374151;
            background-color: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .year-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Stats Summary */
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .stat-card .icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 18px;
        }

        .stat-card.team .icon {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #2563eb;
        }

        .stat-card.available .icon {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #059669;
        }

        .stat-card.pending .icon {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
        }

        .stat-card.upcoming .icon {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #4f46e5;
        }

        .stat-card .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .stat-card .label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        .btn-export {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-export:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
            color: white;
        }

        .btn-print {
            background: #f3f4f6;
            color: #374151;
            border: none;
            padding: 10px 20px;
            border-radius: 3px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-print:hover {
            background: #e5e7eb;
            transform: translateY(-1px);
        }

        .btn-back {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #f9fafb;
            border-color: #9ca3af;
            text-decoration: none;
            color: #374151;
        }

        /* Section Cards */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .section-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .section-card.full-width {
            grid-column: span 2;
        }

        .section-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h5 {
            margin: 0;
            color: #1f2937;
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-header h5 i {
            color: #6b7280;
        }

        .section-body {
            padding: 20px;
        }

        /* Table Styles */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .color-indicator {
            width: 10px;
            height: 10px;
            border-radius: 2px;
            display: inline-block;
            vertical-align: middle;
            border: 1px solid rgba(0, 0, 0, 0.1);
            margin-right: 6px;
        }

        /* Upcoming Leave List */
        .upcoming-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .upcoming-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: #f9fafb;
            border-radius: 8px;
            gap: 16px;
        }

        .upcoming-item .leave-indicator {
            width: 4px;
            height: 40px;
            border-radius: 2px;
        }

        .upcoming-item .user-info {
            flex: 1;
        }

        .upcoming-item .user-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .upcoming-item .leave-type {
            font-size: 12px;
            color: #6b7280;
        }

        .upcoming-item .dates {
            text-align: right;
        }

        .upcoming-item .date-range {
            font-weight: 500;
            color: #374151;
            font-size: 13px;
        }

        .upcoming-item .days-count {
            font-size: 12px;
            color: #6b7280;
        }

        /* Team Members List */
        .team-members {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .team-member-badge {
            background: #e5e7eb;
            color: #374151;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .team-member-badge i {
            color: #6b7280;
            font-size: 11px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 36px;
            opacity: 0.3;
            margin-bottom: 12px;
        }

        .empty-state p {
            margin: 0;
            font-size: 14px;
        }

        @media (max-width: 992px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .section-card.full-width {
                grid-column: span 1;
            }

            .stats-summary {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .report-header {
                padding: 20px;
            }

            .report-header .row {
                flex-direction: column;
                gap: 16px;
            }

            .stats-summary {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }
        }

        /* Print Styles */
        @media print {
            .report-header {
                background: #4e73df !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .action-buttons,
            .btn-back,
            .year-selector {
                display: none !important;
            }

            .stat-card,
            .section-card {
                box-shadow: none;
                border: 1px solid #e5e7eb;
            }

            .stat-card .icon {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('leave.balances.index') }}">Back</a>
        @endslot
        @slot('title')
            Leave Reports
        @endslot
    @endcomponent

    <div class="mb-3 d-flex justify-content-end">
        <select name="year" id="year" class="form-select year-select" onchange="window.location.href='{{ route('leave.reports.team-summary') }}?year=' + this.value">
            @foreach($years as $year)
                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="report-container">
        <!-- Report Header -->
        <div class="report-header">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <h3>Team Leave Summary</h3>
                    <p>Leave overview for your direct reports</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons justify-content-end">
            <a href="{{ route('leave.reports.team-summary.export', ['year' => $selectedYear]) }}" class="btn-export">
                <i class="fas fa-file-excel"></i> Export to Excel
            </a>
            <button type="button" class="btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>

        @if($teamSummary['team_size'] > 0)
            <!-- Stats Summary -->
            <div class="stats-summary">
                <div class="stat-card team">
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="value">{{ number_format($teamSummary['team_size']) }}</div>
                    <div class="label">Team Members</div>
                </div>
                <div class="stat-card available">
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    @php
                        $totalAvailable = collect($teamSummary['balances_by_type'])->sum('total_available');
                    @endphp
                    <div class="value">{{ number_format($totalAvailable, 1) }}</div>
                    <div class="label">Days Available</div>
                </div>
                <div class="stat-card pending">
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="value">{{ number_format($teamSummary['pending_requests']) }}</div>
                    <div class="label">Pending Requests</div>
                </div>
                <div class="stat-card upcoming">
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="value">{{ $teamSummary['upcoming_leave']->count() }}</div>
                    <div class="label">Upcoming Leave</div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Balance by Type -->
                <div class="section-card">
                    <div class="section-header">
                        <h5><i class="fas fa-chart-pie"></i> Balance by Leave Type</h5>
                    </div>
                    <div class="section-body">
                        @if(!empty($teamSummary['balances_by_type']))
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Leave Type</th>
                                            <th class="text-center">Entitled</th>
                                            <th class="text-center">Used</th>
                                            <th class="text-center">Pending</th>
                                            <th class="text-center">Available</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($teamSummary['balances_by_type'] as $balance)
                                            <tr>
                                                <td>{{ $balance['leave_type_name'] }}</td>
                                                <td class="text-center">{{ number_format($balance['total_entitled'], 1) }}</td>
                                                <td class="text-center">{{ number_format($balance['total_used'], 1) }}</td>
                                                <td class="text-center">{{ number_format($balance['total_pending'], 1) }}</td>
                                                <td class="text-center">
                                                    <span style="color: {{ $balance['total_available'] > 0 ? '#059669' : '#6b7280' }}; font-weight: 600;">
                                                        {{ number_format($balance['total_available'], 1) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-chart-pie"></i>
                                <p>No balance data available.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Team Members -->
                <div class="section-card">
                    <div class="section-header">
                        <h5><i class="fas fa-user-friends"></i> Team Members ({{ $directReports->count() }})</h5>
                    </div>
                    <div class="section-body">
                        @if($directReports->count() > 0)
                            <div class="team-members">
                                @foreach($directReports as $member)
                                    <span class="team-member-badge">
                                        <i class="fas fa-user"></i>
                                        {{ $member->name }}
                                        @if($member->department)
                                            <small class="text-muted">({{ $member->department }})</small>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-user-friends"></i>
                                <p>No team members found.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Upcoming Leave -->
                <div class="section-card full-width">
                    <div class="section-header">
                        <h5><i class="fas fa-calendar-alt"></i> Upcoming Leave (Next 30 Days)</h5>
                    </div>
                    <div class="section-body">
                        @if($teamSummary['upcoming_leave']->count() > 0)
                            <div class="upcoming-list">
                                @foreach($teamSummary['upcoming_leave'] as $leave)
                                    <div class="upcoming-item">
                                        <div class="leave-indicator" style="background-color: #3b82f6;"></div>
                                        <div class="user-info">
                                            <div class="user-name">{{ $leave['user_name'] }}</div>
                                            <div class="leave-type">{{ $leave['leave_type'] }}</div>
                                        </div>
                                        <div class="dates">
                                            <div class="date-range">
                                                {{ \Carbon\Carbon::parse($leave['start_date'])->format('d M') }}
                                                @if($leave['start_date'] !== $leave['end_date'])
                                                    - {{ \Carbon\Carbon::parse($leave['end_date'])->format('d M') }}
                                                @endif
                                            </div>
                                            <div class="days-count">{{ number_format($leave['total_days'], 1) }} days</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-calendar-alt"></i>
                                <p>No upcoming leave scheduled for your team in the next 30 days.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="section-card">
                <div class="section-body">
                    <div class="empty-state" style="padding: 60px 20px;">
                        <i class="fas fa-users" style="font-size: 48px;"></i>
                        <h4 style="color: #374151; margin: 16px 0 8px;">No Direct Reports</h4>
                        <p>You don't have any direct reports assigned to you.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Export button is now a direct link with current filter parameters
        });
    </script>
@endsection
