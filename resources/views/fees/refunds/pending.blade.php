@extends('layouts.master')
@section('title')
    Pending Refund Approvals
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
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .fee-body {
            padding: 24px;
        }

        .help-text {
            background: #fffbeb;
            padding: 12px;
            border-left: 4px solid #f59e0b;
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
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
        }

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
            <a class="text-muted font-size-14" href="{{ route('fees.refunds.index') }}">Fee Refunds</a>
        @endslot
        @slot('title')
            Pending Approvals
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
                <div class="col-md-8">
                    <h3 style="margin:0;">Pending Refund Approvals</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Review and approve refund requests</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-white text-dark fs-6">{{ $refunds->count() }} Pending</span>
                </div>
            </div>
        </div>
        <div class="fee-body">
            <div class="help-text">
                <div class="help-title">Approval Required</div>
                <div class="help-content">
                    These refund requests are awaiting your approval. Review each request carefully before approving or rejecting.
                </div>
            </div>

            <!-- Year Filter -->
            <form method="GET" action="{{ route('fees.refunds.pending') }}" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Filter by Year</label>
                        <select class="form-select" name="year" onchange="this.form.submit()">
                            <option value="">All Years</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
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
                                <th>Requested</th>
                                <th>Reason</th>
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
                                    <td>{{ $refund->invoice->student->full_name ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $typeClass = 'type-' . $refund->refund_type;
                                        @endphp
                                        <span class="type-badge {{ $typeClass }}">{{ $refund->refund_type_label }}</span>
                                    </td>
                                    <td class="text-end amount-cell">{{ format_currency($refund->amount) }}</td>
                                    <td>
                                        {{ $refund->created_at->format('d M Y') }}<br>
                                        <small class="text-muted">by {{ $refund->requestedBy->name ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <span title="{{ $refund->reason }}">
                                            {{ Str::limit($refund->reason, 40) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('fees.refunds.show', $refund) }}" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('fees.refunds.approve', $refund) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success me-1" title="Approve"
                                                onclick="return confirm('Approve this refund request?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-check-circle d-block" style="color: #10b981;"></i>
                    <h5>All Caught Up!</h5>
                    <p class="text-muted">No pending refund requests to review.</p>
                    <a href="{{ route('fees.refunds.index') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-list me-1 font-size-14"></i> View All Refunds
                    </a>
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
