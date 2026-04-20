@extends('layouts.master')
@section('title')
    Leave Carry-Over Report
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

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        /* Filter Section */
        .filter-section {
            background: #f9fafb;
            padding: 16px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        /* Summary Stats */
        .summary-stats {
            display: flex;
            gap: 24px;
            margin-bottom: 20px;
            padding: 16px;
            background: #f9fafb;
            border-radius: 8px;
        }

        .summary-stat {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .summary-stat .icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .summary-stat.carried .icon {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #059669;
        }

        .summary-stat.forfeited .icon {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }

        .summary-stat .value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
        }

        .summary-stat .label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
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

        .table tbody tr.has-forfeited {
            background-color: #fef2f2;
        }

        .table tbody tr.has-forfeited:hover {
            background-color: #fee2e2;
        }

        .user-name {
            font-weight: 500;
            color: #1f2937;
        }

        .balance-positive {
            color: #059669;
            font-weight: 600;
        }

        .balance-negative {
            color: #dc2626;
            font-weight: 600;
        }

        .balance-zero {
            color: #6b7280;
            font-weight: 600;
        }

        .forfeited-badge {
            background: #fee2e2;
            color: #991b1b;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
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

        .empty-state h4 {
            color: #374151;
            margin-bottom: 8px;
        }

        .help-text {
            background: #fef3c7;
            padding: 12px;
            border-left: 4px solid #d97706;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #78350f;
            font-size: 13px;
            line-height: 1.4;
        }

        @media (max-width: 768px) {
            .report-header {
                padding: 20px;
            }

            .summary-stats {
                flex-direction: column;
                gap: 16px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .filter-section .row {
                gap: 16px;
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
            .filter-section {
                display: none !important;
            }

            .report-container {
                box-shadow: none;
            }

            .summary-stat .icon {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            tr.has-forfeited {
                background-color: #fef2f2 !important;
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

    <div class="mb-3 d-flex justify-content-end align-items-center gap-2">
        <select name="from_year" class="form-select year-select" onchange="window.location.href='{{ route('leave.reports.carryover') }}?from_year=' + this.value + '&to_year={{ $selectedToYear }}'">
            @foreach($years as $year)
                <option value="{{ $year }}" {{ $selectedFromYear == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>
        <span class="text-muted">to</span>
        <select name="to_year" class="form-select year-select" onchange="window.location.href='{{ route('leave.reports.carryover') }}?from_year={{ $selectedFromYear }}&to_year=' + this.value">
            @foreach($years as $year)
                <option value="{{ $year }}" {{ $selectedToYear == $year ? 'selected' : '' }}>
                    {{ $year }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="report-container">
        <div class="report-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3>Leave Carry-Over Report</h3>
                    <p>Track balances carried between years and forfeited leave</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $selectedFromYear }}</h4>
                                <small class="opacity-75">From Year</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white"><i class="fas fa-arrow-right"></i></h4>
                                <small class="opacity-75">To</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $selectedToYear }}</h4>
                                <small class="opacity-75">To Year</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="report-body">
            <!-- Action Buttons -->
            <div class="action-buttons justify-content-end">
                <a href="{{ route('leave.reports.carryover.export', ['from_year' => $selectedFromYear, 'to_year' => $selectedToYear]) }}" class="btn-export">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
                <button type="button" class="btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Report
                </button>
            </div>

            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title"><i class="fas fa-info-circle me-1"></i> Understanding Carry-Over</div>
                <div class="help-content">
                    This report shows leave balances that were carried over from {{ $selectedFromYear }} to {{ $selectedToYear }}.
                    Rows highlighted in red indicate staff members who forfeited leave days due to exceeding the carry-over limit.
                    Use the year selectors above to change the period.
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="summary-stats">
                <div class="summary-stat carried">
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="value">{{ number_format($totalCarriedOver, 1) }}</div>
                        <div class="label">Days Carried Over</div>
                    </div>
                </div>
                <div class="summary-stat forfeited">
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div>
                        <div class="value">{{ number_format($totalForfeited, 1) }}</div>
                        <div class="label">Days Forfeited</div>
                    </div>
                </div>
            </div>

            <!-- Results Table -->
            @if($carryoverData->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Staff Name</th>
                                <th>Leave Type</th>
                                <th class="text-center">Previous Balance</th>
                                <th class="text-center">Carry-Over Limit</th>
                                <th class="text-center">Carried Over</th>
                                <th class="text-center">Forfeited</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($carryoverData as $index => $item)
                                <tr class="{{ $item['forfeited'] > 0 ? 'has-forfeited' : '' }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <span class="user-name">{{ $item['user_name'] }}</span>
                                    </td>
                                    <td>{{ $item['leave_type_name'] }}</td>
                                    <td class="text-center">{{ number_format($item['previous_year_balance'], 1) }}</td>
                                    <td class="text-center">
                                        @if($item['carry_over_limit'] > 0)
                                            {{ number_format($item['carry_over_limit'], 1) }}
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item['carried_over'] > 0)
                                            <span class="balance-positive">{{ number_format($item['carried_over'], 1) }}</span>
                                        @else
                                            <span class="balance-zero">{{ number_format($item['carried_over'], 1) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item['forfeited'] > 0)
                                            <span class="forfeited-badge">{{ number_format($item['forfeited'], 1) }}</span>
                                        @else
                                            <span class="balance-zero">0.0</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background: #f3f4f6; font-weight: 600;">
                                <td colspan="5" class="text-end">Totals:</td>
                                <td class="text-center">
                                    <span class="balance-positive">{{ number_format($totalCarriedOver, 1) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($totalForfeited > 0)
                                        <span class="balance-negative">{{ number_format($totalForfeited, 1) }}</span>
                                    @else
                                        <span class="balance-zero">0.0</span>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-exchange-alt"></i>
                    <h4>No Carry-Over Data Found</h4>
                    <p>No leave balances were carried over from {{ $selectedFromYear }} to {{ $selectedToYear }}.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Export button is now a direct link with current filter parameters
        });
    </script>
@endsection
