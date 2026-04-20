@extends('layouts.master')
@section('title')
    Leave Utilization Report
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <style>
        .report-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .report-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .report-header h3 {
            margin: 0;
        }

        .report-header p {
            margin: 6px 0 0 0;
            opacity: .9;
        }

        .report-body {
            padding: 24px;
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
            background: #f9fafb;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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

        .stat-card.staff .icon {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #2563eb;
        }

        .stat-card.entitled .icon {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #059669;
        }

        .stat-card.used .icon {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
        }

        .stat-card.rate .icon {
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

        /* Section Title */
        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Distribution Table */
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
            width: 12px;
            height: 12px;
            border-radius: 3px;
            display: inline-block;
            vertical-align: middle;
            border: 1px solid rgba(0, 0, 0, 0.1);
            margin-right: 8px;
        }

        .progress-bar-wrapper {
            background: #e5e7eb;
            border-radius: 4px;
            height: 8px;
            overflow: hidden;
            min-width: 100px;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        /* Monthly Trend */
        .trend-table {
            margin-top: 16px;
        }

        .trend-month {
            font-weight: 500;
            color: #374151;
        }

        .trend-bar-wrapper {
            background: #e5e7eb;
            border-radius: 4px;
            height: 20px;
            overflow: hidden;
            position: relative;
        }

        .trend-bar-fill {
            height: 100%;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .trend-bar-label {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            font-weight: 500;
            color: white;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.3;
            margin-bottom: 16px;
        }

        @media (max-width: 992px) {
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

            .report-container {
                box-shadow: none;
            }

            .stat-card .icon {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .progress-bar-fill,
            .trend-bar-fill {
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
        <select name="year" id="year" class="form-select year-select" onchange="window.location.href='{{ route('leave.reports.utilization') }}?year=' + this.value">
            @foreach($years as $year)
                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="report-container">
        <div class="report-header">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <h3>Leave Utilization Report</h3>
                    <p>Organization-wide leave usage statistics and trends</p>
                </div>
            </div>
        </div>
        <div class="report-body">
            <!-- Action Buttons -->
            <div class="action-buttons justify-content-end">
                <a href="{{ route('leave.reports.utilization.export', ['year' => $selectedYear]) }}" class="btn-export">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
                <button type="button" class="btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Report
                </button>
            </div>

            <!-- Stats Summary -->
            <div class="stats-summary">
                <div class="stat-card staff">
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="value">{{ number_format($stats['total_staff']) }}</div>
                    <div class="label">Total Staff</div>
                </div>
                <div class="stat-card entitled">
                    <div class="icon">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="value">{{ number_format($stats['total_entitled'], 1) }}</div>
                    <div class="label">Days Entitled</div>
                </div>
                <div class="stat-card used">
                    <div class="icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="value">{{ number_format($stats['total_used'], 1) }}</div>
                    <div class="label">Days Used</div>
                </div>
                <div class="stat-card rate">
                    <div class="icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="value">{{ number_format($stats['utilization_rate'], 1) }}%</div>
                    <div class="label">Utilization Rate</div>
                </div>
            </div>

            <!-- Leave Type Distribution -->
            <h5 class="section-title"><i class="fas fa-chart-bar me-2"></i>Leave Type Distribution</h5>
            @if($distribution->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Leave Type</th>
                                <th class="text-center">Staff</th>
                                <th class="text-center">Entitled</th>
                                <th class="text-center">Used</th>
                                <th class="text-center">Pending</th>
                                <th class="text-center">Available</th>
                                <th style="min-width: 150px;">Usage %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($distribution as $item)
                                <tr>
                                    <td>
                                        <span class="color-indicator" style="background-color: {{ $item['color'] ?? '#6b7280' }};"></span>
                                        {{ $item['leave_type_name'] }}
                                        <small class="text-muted">({{ $item['leave_type_code'] }})</small>
                                    </td>
                                    <td class="text-center">{{ $item['staff_count'] }}</td>
                                    <td class="text-center">{{ number_format($item['total_entitled'], 1) }}</td>
                                    <td class="text-center">{{ number_format($item['total_used'], 1) }}</td>
                                    <td class="text-center">{{ number_format($item['total_pending'], 1) }}</td>
                                    <td class="text-center">{{ number_format($item['total_available'], 1) }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress-bar-wrapper flex-grow-1">
                                                <div class="progress-bar-fill"
                                                     style="width: {{ min($item['usage_percentage'], 100) }}%; background-color: {{ $item['color'] ?? '#3b82f6' }};">
                                                </div>
                                            </div>
                                            <span class="text-muted" style="min-width: 45px;">{{ number_format($item['usage_percentage'], 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-chart-bar"></i>
                    <p>No leave balance data available for {{ $selectedYear }}.</p>
                </div>
            @endif

            <!-- Monthly Usage Trend -->
            <h5 class="section-title"><i class="fas fa-chart-line me-2"></i>Monthly Usage Trend</h5>
            @if(!empty($trend))
                @php
                    $maxDays = max(array_column($trend, 'total_days_taken'));
                    $maxDays = $maxDays > 0 ? $maxDays : 1;
                @endphp
                <div class="table-responsive trend-table">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 120px;">Month</th>
                                <th>Days Taken</th>
                                <th class="text-end" style="width: 100px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trend as $month)
                                <tr>
                                    <td class="trend-month">{{ $month['month'] }} {{ $month['year'] }}</td>
                                    <td>
                                        <div class="trend-bar-wrapper">
                                            @php
                                                $percentage = ($month['total_days_taken'] / $maxDays) * 100;
                                            @endphp
                                            <div class="trend-bar-fill" style="width: {{ $percentage }}%;">
                                                @if($month['total_days_taken'] > 0 && $percentage > 15)
                                                    <span class="trend-bar-label">{{ number_format($month['total_days_taken'], 1) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">{{ number_format($month['total_days_taken'], 1) }} days</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-chart-line"></i>
                    <p>No monthly trend data available.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // No additional JS needed - export button is now a direct link
        });
    </script>
@endsection
