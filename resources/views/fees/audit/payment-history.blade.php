@extends('layouts.master')
@section('title')
    Payment Audit History
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

        .audit-summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .action-badge {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .action-create { background: #d1fae5; color: #065f46; }
        .action-update { background: #dbeafe; color: #1e40af; }
        .action-delete, .action-cancel, .action-void { background: #fee2e2; color: #991b1b; }
        .action-issue { background: #e9d5ff; color: #6b21a8; }
        .action-carryover { background: #fef3c7; color: #92400e; }

        .changes-panel {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 12px;
            margin-top: 8px;
        }

        .change-row {
            display: flex;
            gap: 16px;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .change-row:last-child {
            border-bottom: none;
        }

        .change-field {
            font-weight: 500;
            width: 150px;
            color: #374151;
            flex-shrink: 0;
        }

        .change-old {
            color: #dc2626;
            text-decoration: line-through;
            flex: 1;
            word-break: break-word;
        }

        .change-arrow {
            color: #9ca3af;
            flex-shrink: 0;
        }

        .change-new {
            color: #059669;
            flex: 1;
            word-break: break-word;
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

        .ip-address {
            font-family: monospace;
            font-size: 12px;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .method-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .method-cash { background: #d1fae5; color: #065f46; }
        .method-bank_transfer { background: #dbeafe; color: #1e40af; }
        .method-mobile_money { background: #ede9fe; color: #5b21b6; }
        .method-cheque { background: #ffedd5; color: #9a3412; }

        .voided-badge {
            background: #fee2e2;
            color: #991b1b;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
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

        @media (max-width: 768px) {
            .fee-header {
                padding: 20px;
            }

            .fee-body {
                padding: 16px;
            }

            .change-row {
                flex-direction: column;
                gap: 4px;
            }

            .change-field {
                width: 100%;
            }

            .change-arrow {
                display: none;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="javascript:history.back()">Back</a>
        @endslot
        @slot('title')
            Fee Administration
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
            <div>
                <h4 class="mb-1">Payment Audit History</h4>
                <p class="mb-0 opacity-75">Complete audit trail for payment operations</p>
            </div>
        </div>
        <div class="fee-body">
            <!-- Help Text -->
            <div class="help-text">
                <div class="help-title">Audit Trail</div>
                <div class="help-content">
                    This page shows the complete history of all changes made to this payment, including creation, updates, and voiding. Click "Details" to view the specific changes made in each action.
                </div>
            </div>

            <!-- Payment Summary Card -->
            <div class="audit-summary-card">
                <div class="row">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <small class="text-muted d-block">Receipt Number</small>
                        <div class="fw-bold">{{ $payment->receipt_number }}</div>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <small class="text-muted d-block">Amount</small>
                        <div class="fw-bold">{{ format_currency($payment->amount) }}</div>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <small class="text-muted d-block">Method</small>
                        <div>
                            <span class="method-badge method-{{ $payment->payment_method }}">
                                {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <small class="text-muted d-block">Received By</small>
                        <div class="fw-bold">{{ $payment->receivedBy?->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block">Status</small>
                        <div>
                            @if($payment->voided_at)
                                <span class="voided-badge">Voided</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Invoice</small>
                        <div>
                            <a href="{{ route('fees.collection.invoices.show', $payment->invoice) }}" class="text-primary">
                                {{ $payment->invoice->invoice_number }}
                            </a>
                            - {{ $payment->invoice->student->full_name }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Payment Date</small>
                        <div>{{ $payment->payment_date->format('d M Y') }}</div>
                    </div>
                </div>
            </div>

            <!-- Audit Logs Table -->
            @if($auditLogs->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-history d-block"></i>
                    <p class="mb-0">No audit logs found for this payment.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 160px;">Date/Time</th>
                                <th style="width: 150px;">User</th>
                                <th style="width: 120px;">Action</th>
                                <th style="width: 120px;">IP Address</th>
                                <th>Notes</th>
                                <th style="width: 100px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auditLogs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                    <td>{{ $log->user?->name ?? 'System' }}</td>
                                    <td>
                                        <span class="action-badge action-{{ $log->action }}">
                                            {{ $log->action_label }}
                                        </span>
                                    </td>
                                    <td><span class="ip-address">{{ $log->ip_address }}</span></td>
                                    <td>{{ Str::limit($log->notes, 50) }}</td>
                                    <td>
                                        @if(count($log->formatted_changes) > 0)
                                            <button class="btn btn-sm btn-outline-secondary"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#changes-{{ $log->id }}"
                                                    aria-expanded="false">
                                                <i class="fas fa-eye me-1"></i> Details
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @if(count($log->formatted_changes) > 0)
                                    <tr class="collapse" id="changes-{{ $log->id }}">
                                        <td colspan="6" class="p-0">
                                            <div class="changes-panel m-3">
                                                <strong class="mb-2 d-block">Changes:</strong>
                                                @foreach($log->formatted_changes as $change)
                                                    <div class="change-row">
                                                        <span class="change-field">{{ $change['field'] }}</span>
                                                        <span class="change-old">{{ $change['old_value'] ?? '-' }}</span>
                                                        <span class="change-arrow"><i class="fas fa-arrow-right"></i></span>
                                                        <span class="change-new">{{ $change['new_value'] ?? '-' }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    }
                }, 5000);
            });
        });
    </script>
@endsection
