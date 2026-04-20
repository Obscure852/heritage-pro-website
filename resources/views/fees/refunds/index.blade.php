@extends('layouts.master')
@section('title')
    Fee Refunds
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

        .controls .form-control,
        .controls .form-select {
            font-size: 0.9rem;
        }

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

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            display: inline-block;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #dbeafe; color: #1e40af; }
        .status-processed { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }

        .type-badge {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }

        .type-full { background: #fee2e2; color: #991b1b; }
        .type-partial { background: #fef3c7; color: #92400e; }
        .type-credit_note { background: #dbeafe; color: #1e40af; }

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

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
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
            Fee Administration
        @endslot
        @slot('title')
            Fee Refunds
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

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="fee-container">
        <div class="fee-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Fee Refunds</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage refund requests and credit notes</p>
                </div>
                <div class="col-md-6">
                    @if (!empty($refunds))
                        @php
                            $totalCount = $refunds->total();
                            $pendingCount = $refunds->where('status', 'pending')->count();
                            $processedCount = $refunds->where('status', 'processed')->count();
                        @endphp
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $totalCount }}</h4>
                                    <small class="opacity-75">Total</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $pendingCount }}</h4>
                                    <small class="opacity-75">Pending</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $processedCount }}</h4>
                                    <small class="opacity-75">Processed</small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Refund Management</div>
                <div class="help-content">
                    View and manage fee refunds and credit notes. Refunds require approval before processing. Credit notes add credit to a student's account.
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" action="{{ route('fees.refunds.index') }}" id="filterForm">
                <div class="row align-items-center mb-3">
                    <div class="col-lg-8 col-md-12">
                        <div class="controls">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" name="search" value="{{ $filters['search'] ?? '' }}"
                                            placeholder="Search refund...">
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-6">
                                    <select class="form-select" name="year">
                                        @foreach ($years as $year)
                                            <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-2 col-sm-6">
                                    <select class="form-select" name="status">
                                        <option value="">All Status</option>
                                        @foreach ($statuses as $value => $label)
                                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('fees.refunds.index') }}" class="btn btn-light">Reset</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                            @can('approve-refunds')
                                <a href="{{ route('fees.refunds.pending') }}" class="btn btn-primary">
                                    <i class="fas fa-clock me-1"></i> Pending Approvals
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </form>

            @if ($refunds->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Refund #</th>
                                <th>Student</th>
                                <th>Type</th>
                                <th class="text-end">Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Requested By</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($refunds as $refund)
                                <tr>
                                    <td>
                                        <a href="{{ route('fees.refunds.show', $refund) }}">
                                            {{ $refund->refund_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('fees.collection.students.account', $refund->student_id) }}">
                                            {{ $refund->invoice->student->full_name ?? 'N/A' }}
                                        </a>
                                    </td>
                                    <td>
                                        @php
                                            $typeClass = 'type-' . $refund->refund_type;
                                        @endphp
                                        <span class="type-badge {{ $typeClass }}">{{ $refund->refund_type_label }}</span>
                                    </td>
                                    <td class="text-end amount-cell">{{ format_currency($refund->amount) }}</td>
                                    <td>{{ $refund->refund_date->format('d M Y') }}</td>
                                    <td>
                                        @php
                                            $statusClass = 'status-' . $refund->status;
                                        @endphp
                                        <span class="status-badge {{ $statusClass }}">{{ $refund->status_label }}</span>
                                    </td>
                                    <td>{{ $refund->requestedBy->name ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('fees.refunds.show', $refund) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $refunds->withQueryString()->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-undo-alt d-block"></i>
                    <h5>No Refunds Found</h5>
                    <p class="text-muted">No refund records match your search criteria.</p>
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
