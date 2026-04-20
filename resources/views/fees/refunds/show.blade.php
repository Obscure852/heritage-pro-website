@extends('layouts.master')
@section('title')
    Refund {{ $refund->refund_number }}
@endsection
@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-card {
            background: #f9fafb;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-item {
            margin-bottom: 12px;
        }

        .info-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 500;
            color: #1f2937;
        }

        .info-value a {
            color: #3b82f6;
            text-decoration: none;
        }

        .info-value a:hover {
            text-decoration: underline;
        }

        .summary-card {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .summary-card.credit-note {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .summary-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .summary-item small {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved { background: #dbeafe; color: #1e40af; }
        .status-processed { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }

        .type-badge {
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .type-full { background: #fee2e2; color: #991b1b; }
        .type-partial { background: #fef3c7; color: #92400e; }
        .type-credit_note { background: #dbeafe; color: #1e40af; }

        .workflow-card {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .workflow-card.pending {
            background: #fffbeb;
            border-color: #fde68a;
        }

        .workflow-card.rejected {
            background: #fef2f2;
            border-color: #fecaca;
        }

        .rejection-reason {
            background: #fee2e2;
            padding: 12px;
            border-radius: 4px;
            color: #991b1b;
            font-weight: 500;
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
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            color: white;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        .header-actions {
            display: flex;
            gap: 8px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .header-actions {
                flex-wrap: wrap;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('fees.refunds.index') }}">Refunds</a>
        @endslot
        @slot('title')
            {{ $refund->isCreditNote() ? 'Credit Note' : 'Refund' }} {{ $refund->refund_number }}
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

    <div class="form-container">
        <div class="page-header">
            <h1 class="page-title">
                {{ $refund->isCreditNote() ? 'Credit Note' : 'Refund' }} {{ $refund->refund_number }}
                @php
                    $statusClass = 'status-' . $refund->status;
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ $refund->status_label }}</span>
            </h1>
            <div class="header-actions">
                @if ($refund->isProcessed())
                    <a href="{{ route('fees.refunds.print', $refund) }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-print me-1"></i> Print
                    </a>
                @endif
                @can('approve-refunds')
                    @if ($refund->isPending())
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="fas fa-check me-1"></i> Approve
                        </button>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times me-1"></i> Reject
                        </button>
                    @endif
                @endcan
                @can('process-refunds')
                    @if ($refund->isApproved())
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#processModal">
                            <i class="fas fa-check-double me-1"></i> Process
                        </button>
                    @endif
                @endcan
            </div>
        </div>

        {{-- Amount Summary --}}
        <div class="summary-card {{ $refund->isCreditNote() ? 'credit-note' : '' }}">
            <div class="summary-grid">
                <div class="summary-item">
                    <h4>{{ format_currency($refund->amount) }}</h4>
                    <small>{{ $refund->isCreditNote() ? 'Credit Amount' : 'Refund Amount' }}</small>
                </div>
                <div class="summary-item">
                    @php
                        $typeClass = 'type-' . $refund->refund_type;
                    @endphp
                    <h4><span class="type-badge {{ $typeClass }}" style="font-size: 14px;">{{ $refund->refund_type_label }}</span></h4>
                    <small>Type</small>
                </div>
                <div class="summary-item">
                    <h4>{{ ucfirst(str_replace('_', ' ', $refund->refund_method)) }}</h4>
                    <small>Method</small>
                </div>
            </div>
        </div>

        {{-- Workflow Status --}}
        @if ($refund->isPending())
            <div class="workflow-card pending">
                <h6 class="mb-2" style="color: #92400e;"><i class="fas fa-clock me-2"></i>Awaiting Approval</h6>
                <p class="mb-0 text-muted small">This {{ $refund->isCreditNote() ? 'credit note' : 'refund' }} is pending approval. An authorized user must approve it before it can be processed.</p>
            </div>
        @elseif ($refund->isApproved())
            <div class="workflow-card">
                <h6 class="mb-2" style="color: #065f46;"><i class="fas fa-check-circle me-2"></i>Approved - Ready for Processing</h6>
                <p class="mb-0 text-muted small">Approved by {{ $refund->approvedBy->name ?? 'N/A' }} on {{ $refund->approved_at?->format('d M Y H:i') ?? 'N/A' }}. Proceed to process this {{ $refund->isCreditNote() ? 'credit note' : 'refund' }}.</p>
            </div>
        @elseif ($refund->isProcessed())
            <div class="workflow-card">
                <h6 class="mb-2" style="color: #065f46;"><i class="fas fa-check-double me-2"></i>Processed</h6>
                <p class="mb-0 text-muted small">Processed by {{ $refund->processedBy->name ?? 'N/A' }} on {{ $refund->processed_at?->format('d M Y H:i') ?? 'N/A' }}.</p>
            </div>
        @elseif ($refund->isRejected())
            <div class="workflow-card rejected">
                <h6 class="mb-2" style="color: #991b1b;"><i class="fas fa-times-circle me-2"></i>Rejected</h6>
                <div class="rejection-reason mt-2">{{ $refund->rejection_reason }}</div>
            </div>
        @endif

        {{-- Student & Refund Info --}}
        <div class="info-grid">
            <div class="info-card">
                <h6 class="mb-3" style="font-weight: 600; color: #374151;">Student Information</h6>
                <div class="info-item">
                    <div class="info-label">Student Name</div>
                    <div class="info-value">{{ $refund->invoice->student->full_name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Student Number</div>
                    <div class="info-value">{{ $refund->invoice->student->student_number ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Invoice Number</div>
                    <div class="info-value">
                        <a href="{{ route('fees.collection.invoices.show', $refund->student_invoice_id) }}">
                            {{ $refund->invoice->invoice_number ?? 'N/A' }}
                        </a>
                    </div>
                </div>
                @if ($refund->payment)
                    <div class="info-item">
                        <div class="info-label">Original Payment</div>
                        <div class="info-value">
                            <a href="{{ route('fees.collection.payments.show', $refund->fee_payment_id) }}">
                                {{ $refund->payment->receipt_number ?? 'N/A' }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <div class="info-card">
                <h6 class="mb-3" style="font-weight: 600; color: #374151;">Refund Details</h6>
                <div class="info-item">
                    <div class="info-label">Refund Number</div>
                    <div class="info-value">{{ $refund->refund_number }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Refund Date</div>
                    <div class="info-value">{{ $refund->refund_date->format('d M Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Year</div>
                    <div class="info-value">{{ $refund->year }}</div>
                </div>
                @if ($refund->reference_number)
                    <div class="info-item">
                        <div class="info-label">Reference Number</div>
                        <div class="info-value">{{ $refund->reference_number }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Reason --}}
        <h3 class="section-title">Reason</h3>
        <div class="info-card">
            <p class="mb-0">{{ $refund->reason }}</p>
        </div>

        {{-- Notes --}}
        @if ($refund->notes)
            <h3 class="section-title">Notes</h3>
            <div class="info-card">
                <p class="mb-0">{{ $refund->notes }}</p>
            </div>
        @endif

        {{-- Audit Trail --}}
        <h3 class="section-title">Workflow History</h3>
        <div class="info-card">
            <div class="info-item">
                <div class="info-label">Requested By</div>
                <div class="info-value">{{ $refund->requestedBy->name ?? 'N/A' }} on {{ $refund->created_at->format('d M Y H:i') }}</div>
            </div>
            @if ($refund->approved_at)
                <div class="info-item">
                    <div class="info-label">Approved By</div>
                    <div class="info-value">{{ $refund->approvedBy->name ?? 'N/A' }} on {{ $refund->approved_at->format('d M Y H:i') }}</div>
                </div>
            @endif
            @if ($refund->processed_at)
                <div class="info-item">
                    <div class="info-label">Processed By</div>
                    <div class="info-value">{{ $refund->processedBy->name ?? 'N/A' }} on {{ $refund->processed_at->format('d M Y H:i') }}</div>
                </div>
            @endif
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('fees.refunds.index') }}">
                <i class="bx bx-arrow-back me-1"></i> Back to Refunds
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('fees.collection.students.account', $refund->student_id) }}" class="btn btn-primary">
                    <i class="fas fa-user me-1"></i> View Student Account
                </a>
            </div>
        </div>
    </div>

    {{-- Approve Modal --}}
    @can('approve-refunds')
        @if ($refund->isPending())
            <div class="modal fade" id="approveModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('fees.refunds.approve', $refund) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Approve {{ $refund->isCreditNote() ? 'Credit Note' : 'Refund' }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to approve this {{ $refund->isCreditNote() ? 'credit note' : 'refund' }} for <strong>{{ format_currency($refund->amount) }}</strong>?</p>
                                <p class="text-muted small">After approval, the refund will be ready for processing.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success btn-loading">
                                    <span class="btn-text"><i class="fas fa-check me-1"></i> Approve</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2"></span>Approving...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="rejectModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('fees.refunds.reject', $refund) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Reject {{ $refund->isCreditNote() ? 'Credit Note' : 'Refund' }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to reject this {{ $refund->isCreditNote() ? 'credit note' : 'refund' }}?</p>
                                <div class="mb-3">
                                    <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="rejection_reason" rows="3" required minlength="10"
                                        placeholder="Please provide a reason (minimum 10 characters)"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger btn-loading">
                                    <span class="btn-text"><i class="fas fa-times me-1"></i> Reject</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2"></span>Rejecting...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endcan

    {{-- Process Modal --}}
    @can('process-refunds')
        @if ($refund->isApproved())
            <div class="modal fade" id="processModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('fees.refunds.process', $refund) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Process {{ $refund->isCreditNote() ? 'Credit Note' : 'Refund' }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Process this {{ $refund->isCreditNote() ? 'credit note' : 'refund' }} for <strong>{{ format_currency($refund->amount) }}</strong>?</p>
                                @if ($refund->isCreditNote())
                                    <p class="text-info small"><i class="fas fa-info-circle me-1"></i> This will add {{ format_currency($refund->amount) }} credit to the student's invoice.</p>
                                @else
                                    <p class="text-warning small"><i class="fas fa-exclamation-triangle me-1"></i> This will adjust the invoice balance. Ensure the actual refund has been issued.</p>
                                @endif
                                <div class="mb-3">
                                    <label class="form-label">Reference Number (Optional)</label>
                                    <input type="text" class="form-control" name="reference_number"
                                        value="{{ $refund->reference_number }}" placeholder="Transaction reference">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes (Optional)</label>
                                    <textarea class="form-control" name="notes" rows="2" placeholder="Additional notes">{{ $refund->notes }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success btn-loading">
                                    <span class="btn-text"><i class="fas fa-check-double me-1"></i> Process</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2"></span>Processing...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endcan
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeAlertDismissal();
            initializeBtnLoading();
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

        function initializeBtnLoading() {
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const btn = this.querySelector('.btn-loading');
                    if (btn) {
                        btn.classList.add('loading');
                        btn.disabled = true;
                    }
                });
            });
        }
    </script>
@endsection
