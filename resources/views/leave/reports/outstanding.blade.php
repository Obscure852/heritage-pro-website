@extends('layouts.master')
@section('title')
    Outstanding Balances Report
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
            padding: 8px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-outline-secondary {
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 3px;
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

        .user-name {
            font-weight: 500;
            color: #1f2937;
        }

        .department-badge {
            background: #e5e7eb;
            color: #374151;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
        }

        .balance-positive {
            color: #059669;
            font-weight: 600;
        }

        .balance-zero {
            color: #6b7280;
            font-weight: 600;
        }

        /* Action Buttons */
        .btn-export {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 3px;
            font-weight: 500;
            height: 38px;
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
            padding: 8px 16px;
            border-radius: 3px;
            font-weight: 500;
            height: 38px;
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

        @media (max-width: 768px) {
            .report-header {
                padding: 20px;
            }

            .filter-section .row {
                gap: 16px;
            }

            .filter-section .col-auto.ms-auto {
                margin-left: 0 !important;
                width: 100%;
                justify-content: flex-start;
            }
        }

        /* Print Styles */
        @media print {
            .report-header {
                background: #4e73df !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .filter-section {
                display: none !important;
            }

            .report-container {
                box-shadow: none;
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
        <select name="year" class="form-select year-select" onchange="window.location.href='{{ route('leave.reports.outstanding') }}?year=' + this.value">
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
                <div class="col-md-6">
                    <h3>Outstanding Balances Report</h3>
                    <p>Staff members with remaining leave balances</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ number_format($totalRecords) }}</h4>
                                <small class="opacity-75">Records</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ number_format($totalOutstanding, 1) }}</h4>
                                <small class="opacity-75">Total Days</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $selectedYear }}</h4>
                                <small class="opacity-75">Leave Year</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="report-body">
            <!-- Filter Section with Action Buttons -->
            <div class="filter-section">
                <form method="GET" action="{{ route('leave.reports.outstanding') }}" id="filter-form">
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="leave_type_id" class="form-label">Leave Type</label>
                            <select name="leave_type_id" id="leave_type_id" class="form-select">
                                <option value="">All Leave Types</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}" {{ $selectedLeaveTypeId == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }} ({{ $type->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('leave.reports.outstanding', ['year' => $selectedYear]) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-1"></i> Reset
                            </a>
                        </div>
                        <div class="col-auto ms-auto d-flex gap-2">
                            <a href="{{ route('leave.reports.outstanding.export', ['year' => $selectedYear, 'leave_type_id' => $selectedLeaveTypeId]) }}" class="btn-export">
                                <i class="fas fa-file-excel"></i> Export
                            </a>
                            <button type="button" class="btn-print" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Results Table -->
            @if($balances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Staff Name</th>
                                <th>Department</th>
                                <th>Leave Type</th>
                                <th class="text-center">Entitled</th>
                                <th class="text-center">Used</th>
                                <th class="text-center">Pending</th>
                                <th class="text-center">Available</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($balances as $index => $balance)
                                <tr>
                                    <td>{{ $balances->firstItem() + $index }}</td>
                                    <td>
                                        <span class="user-name">{{ $balance['user_name'] }}</span>
                                    </td>
                                    <td>
                                        @if($balance['department'])
                                            <span class="department-badge">{{ $balance['department'] }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $balance['leave_type_name'] }}</td>
                                    <td class="text-center">{{ number_format($balance['entitled'], 1) }}</td>
                                    <td class="text-center">{{ number_format($balance['used'], 1) }}</td>
                                    <td class="text-center">{{ number_format($balance['pending'], 1) }}</td>
                                    <td class="text-center">
                                        @if($balance['available'] > 0)
                                            <span class="balance-positive">{{ number_format($balance['available'], 1) }}</span>
                                        @else
                                            <span class="balance-zero">{{ number_format($balance['available'], 1) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $balances->firstItem() }} to {{ $balances->lastItem() }} of {{ $balances->total() }} records
                    </div>
                    {{ $balances->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-balance-scale"></i>
                    <h4>No Outstanding Balances Found</h4>
                    <p>No staff members have outstanding leave balances matching the selected filters for {{ $selectedYear }}.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Choices.js for leave type dropdown
            const leaveTypeSelect = document.getElementById('leave_type_id');
            if (leaveTypeSelect) {
                new Choices(leaveTypeSelect, {
                    searchEnabled: true,
                    itemSelectText: '',
                    placeholder: true,
                    placeholderValue: 'Select leave type...',
                    removeItemButton: true,
                    allowHTML: false
                });
            }

            // Export button is now a direct link with current filter parameters
        });
    </script>
@endsection
