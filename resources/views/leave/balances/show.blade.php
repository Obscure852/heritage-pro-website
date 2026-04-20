@extends('layouts.master')
@section('title')
    Balance Details - {{ $balance->user->name ?? 'N/A' }}
@endsection
@section('css')
    <style>
        .balance-detail-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .balance-detail-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .balance-detail-body {
            padding: 24px;
        }

        .balance-summary-card {
            background: #f8f9fa;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .balance-summary-card h5 {
            margin-bottom: 16px;
            color: #374151;
            font-weight: 600;
        }

        .balance-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .balance-row:last-child {
            border-bottom: none;
        }

        .balance-row .label {
            color: #6b7280;
            font-size: 14px;
        }

        .balance-row .value {
            font-weight: 500;
            color: #1f2937;
        }

        .available-balance {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 16px;
            border-radius: 4px;
            text-align: center;
            margin-top: 16px;
        }

        .available-balance.negative {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        }

        .available-balance h2 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .available-balance small {
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .adjustment-history {
            margin-top: 24px;
        }

        .type-badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .type-credit {
            background: #d1fae5;
            color: #065f46;
        }

        .type-debit {
            background: #fee2e2;
            color: #991b1b;
        }

        .type-correction {
            background: #fef3c7;
            color: #92400e;
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

        .btn-secondary {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            color: #374151;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
            color: #1f2937;
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

        .color-indicator {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            display: inline-block;
            vertical-align: middle;
            border: 1px solid rgba(0, 0, 0, 0.1);
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            .balance-detail-header {
                padding: 20px;
            }

            .balance-detail-body {
                padding: 16px;
            }
        }
    </style>
@endsection
@section('content')
    <div class="balance-detail-container">
        <div class="balance-detail-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 style="margin:0;">Leave Balance Details</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        {{ $balance->user->name ?? 'N/A' }} - {{ $balance->leaveType->name ?? 'N/A' }} ({{ $balance->leave_year }})
                    </p>
                </div>
                <div class="col-auto">
                    <a href="{{ route('leave.balances.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
        <div class="balance-detail-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="balance-summary-card">
                        <h5>
                            @if($balance->leaveType && $balance->leaveType->color)
                                <span class="color-indicator" style="background-color: {{ $balance->leaveType->color }};"></span>
                            @endif
                            Balance Summary
                        </h5>
                        <div class="balance-row">
                            <span class="label">Staff Member</span>
                            <span class="value">{{ $balance->user->name ?? 'N/A' }}</span>
                        </div>
                        <div class="balance-row">
                            <span class="label">Leave Type</span>
                            <span class="value">{{ $balance->leaveType->name ?? 'N/A' }}</span>
                        </div>
                        <div class="balance-row">
                            <span class="label">Leave Year</span>
                            <span class="value">{{ $balance->leave_year }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="balance-summary-card">
                        <h5>Balance Breakdown</h5>
                        <div class="balance-row">
                            <span class="label">Entitled</span>
                            <span class="value">{{ number_format($balance->entitled, 1) }} days</span>
                        </div>
                        <div class="balance-row">
                            <span class="label">Carried Over</span>
                            <span class="value">{{ number_format($balance->carried_over, 1) }} days</span>
                        </div>
                        <div class="balance-row">
                            <span class="label">Accrued</span>
                            <span class="value">{{ number_format($balance->accrued, 1) }} days</span>
                        </div>
                        <div class="balance-row">
                            <span class="label">Used</span>
                            <span class="value text-danger">-{{ number_format($balance->used, 1) }} days</span>
                        </div>
                        <div class="balance-row">
                            <span class="label">Pending</span>
                            <span class="value text-warning">-{{ number_format($balance->pending, 1) }} days</span>
                        </div>
                        <div class="balance-row">
                            <span class="label">Adjusted</span>
                            <span class="value {{ $balance->adjusted >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $balance->adjusted >= 0 ? '+' : '' }}{{ number_format($balance->adjusted, 1) }} days
                            </span>
                        </div>

                        <div class="available-balance {{ $balance->available < 0 ? 'negative' : '' }}">
                            <small>Available Balance</small>
                            <h2>{{ number_format($balance->available, 1) }} days</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="adjustment-history" id="adjustment-history">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="section-title mb-0">Adjustment History</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adjustmentModal">
                        <i class="fas fa-plus me-1"></i> Make Adjustment
                    </button>
                </div>

                @if($adjustments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th class="text-center">Days</th>
                                    <th>Reason</th>
                                    <th>Adjusted By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($adjustments as $adjustment)
                                    <tr>
                                        <td>{{ $adjustment->created_at->format('d M Y, H:i') }}</td>
                                        <td>
                                            <span class="type-badge type-{{ $adjustment->adjustment_type }}">
                                                {{ ucfirst($adjustment->adjustment_type) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($adjustment->adjustment_type === 'debit')
                                                <span class="text-danger">-{{ number_format($adjustment->days, 1) }}</span>
                                            @else
                                                <span class="text-success">+{{ number_format($adjustment->days, 1) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $adjustment->reason }}</td>
                                        <td>{{ $adjustment->adjustedBy->name ?? 'System' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <p>No adjustments have been made to this balance.</p>
                    </div>
                @endif
            </div>

            {{-- Audit Trail Section (AUDT-04) --}}
            @if(isset($auditLogs) && isset($auditService))
                @include('leave.audit._history', ['auditLogs' => $auditLogs, 'auditService' => $auditService])
            @endif
        </div>
    </div>

    @include('leave.balances._adjustment_modal', ['balance' => $balance])
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Success message display
            function showAlert(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
                alertDiv.role = 'alert';
                alertDiv.innerHTML = `
                    <strong>${message}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;

                const container = document.querySelector('.balance-detail-container');
                container.parentNode.insertBefore(alertDiv, container);

                setTimeout(() => {
                    alertDiv.classList.remove('show');
                    setTimeout(() => alertDiv.remove(), 150);
                }, 3000);
            }

            // Expose for use in modal
            window.showAlert = showAlert;
        });
    </script>
@endsection
