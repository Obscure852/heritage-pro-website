@extends('layouts.master')
@section('title')
    Daily Collections
@endsection
@section('css')
    <style>
        .fee-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .fee-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .fee-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }

        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }

        .summary-card .value {
            font-size: 28px;
            font-weight: 700;
            color: #1e40af;
        }

        .summary-card .label {
            color: #64748b;
            font-size: 13px;
            margin-top: 4px;
        }

        .breakdown-section {
            background: #f9fafb;
            border-radius: 8px;
            padding: 16px;
            margin-top: 20px;
        }

        .breakdown-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .breakdown-row:last-child {
            border-bottom: none;
        }

        .method-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .method-cash { background: #d1fae5; color: #065f46; }
        .method-bank { background: #dbeafe; color: #1e40af; }
        .method-bank_transfer { background: #dbeafe; color: #1e40af; }
        .method-mobile { background: #ede9fe; color: #5b21b6; }
        .method-mobile_money { background: #ede9fe; color: #5b21b6; }
        .method-cheque { background: #ffedd5; color: #9a3412; }

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

        .amount-cell {
            font-weight: 600;
            color: #059669;
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

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
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

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.3;
            margin-bottom: 12px;
        }

        @media (max-width: 768px) {
            .summary-card {
                margin-bottom: 16px;
            }

            .fee-header {
                padding: 20px;
            }

            .fee-body {
                padding: 16px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('fees.reports.dashboard') }}">Back</a>
        @endslot
        @slot('title')
            Daily Collections
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="fee-container">
        <div class="fee-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Daily Collections</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">{{ \Carbon\Carbon::parse($filters['date'])->format('l, d F Y') }}</p>
                </div>
                <div class="col-md-6">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ format_currency($collections['total_collected']) }}</h4>
                                <small class="opacity-75">Total Collected</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $collections['payment_count'] }}</h4>
                                <small class="opacity-75">Payments</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ count($collections['by_collector']) }}</h4>
                                <small class="opacity-75">Collectors</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Daily Collections Report</div>
                <div class="help-content">
                    View all fee payments collected on a specific date. Payments are grouped by payment method and collector for quick reconciliation.
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('fees.reports.daily-collections') }}" id="filterForm">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <input type="date" name="date" class="form-control" value="{{ $filters['date'] }}">
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <select name="year" class="form-select">
                                        <option value="">All Years</option>
                                        @foreach ($years as $year)
                                            <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-grow-1">
                                            <i class="fas fa-filter me-1"></i> Filter
                                        </button>
                                        <a href="{{ route('fees.reports.daily-collections') }}" class="btn btn-light">Reset</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        @can('export-fee-reports')
                            <a href="{{ route('fees.reports.export.daily-collections', $filters) }}" class="btn btn-success">
                                <i class="fas fa-file-excel me-1"></i> Export to Excel
                            </a>
                        @endcan
                    </div>
                </div>
            </form>

            <!-- Breakdowns -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="breakdown-section">
                        <h6 class="mb-3"><i class="fas fa-credit-card me-2"></i>By Payment Method</h6>
                        @forelse($collections['by_method'] as $method => $info)
                            <div class="breakdown-row">
                                <span>
                                    <span class="method-badge method-{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</span>
                                </span>
                                <span><strong>{{ format_currency($info['total']) }}</strong> ({{ $info['count'] }} payments)</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No payments recorded</p>
                        @endforelse
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="breakdown-section">
                        <h6 class="mb-3"><i class="fas fa-user me-2"></i>By Collector</h6>
                        @forelse($collections['by_collector'] as $collector)
                            <div class="breakdown-row">
                                <span>{{ $collector['name'] }}</span>
                                <span><strong>{{ format_currency($collector['total']) }}</strong> ({{ $collector['count'] }} payments)</span>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No payments recorded</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Payments Table -->
            <h5 class="section-title"><i class="fas fa-list me-2"></i>Payment Details</h5>
            @if(empty($collections['payments']))
                <div class="empty-state">
                    <i class="fas fa-receipt d-block"></i>
                    <p class="mt-3 mb-0">No payments recorded for this date</p>
                    <p class="text-muted" style="font-size: 13px;">Select a different date to view collections</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Receipt #</th>
                                <th>Student</th>
                                <th class="text-end">Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Received By</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($collections['payments'] as $payment)
                                <tr>
                                    <td>{{ $payment['created_at'] }}</td>
                                    <td><code>{{ $payment['receipt_number'] }}</code></td>
                                    <td>
                                        {{ $payment['student_name'] }}
                                        <br><small class="text-muted">{{ $payment['student_number'] }}</small>
                                    </td>
                                    <td class="text-end amount-cell">{{ format_currency($payment['amount']) }}</td>
                                    <td>
                                        <span class="method-badge method-{{ $payment['payment_method'] }}">
                                            {{ ucfirst(str_replace('_', ' ', $payment['payment_method'])) }}
                                        </span>
                                    </td>
                                    <td>{{ $payment['reference_number'] ?? '-' }}</td>
                                    <td>{{ $payment['received_by'] }}</td>
                                    <td>
                                        <a href="{{ route('fees.collection.payments.show', $payment['id']) }}" class="btn btn-sm btn-outline-primary" title="View Receipt">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="3"><strong>Total</strong></td>
                                <td class="text-end"><strong>{{ format_currency($collections['total_collected']) }}</strong></td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeAlertDismissal();
        });

        function initializeAlertDismissal() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    }
                }, 5000);
            });
        }
    </script>
@endsection
