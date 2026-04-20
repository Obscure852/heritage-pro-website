@extends('layouts.master')
@section('title')
    Pending Approvals
@endsection
@section('css')
    <style>
        .pending-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .pending-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .pending-body {
            padding: 24px;
        }

        .pending-count-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-left: 10px;
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

        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
        }

        .btn-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        .btn-outline-secondary {
            background: transparent;
            border: 1px solid #d1d5db;
            color: #374151;
        }

        .btn-outline-secondary:hover {
            background: #f9fafb;
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

        .empty-state p {
            margin-bottom: 20px;
        }

        .requester-info {
            display: flex;
            flex-direction: column;
        }

        .requester-name {
            font-weight: 500;
            color: #1f2937;
        }

        .requester-dept {
            font-size: 12px;
            color: #6b7280;
        }

        .leave-type-name {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .color-indicator {
            width: 12px;
            height: 12px;
            border-radius: 3px;
            display: inline-block;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .date-range {
            white-space: nowrap;
            font-size: 13px;
        }

        .days-count {
            font-weight: 600;
            color: #1f2937;
        }

        .refresh-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
        }

        .refresh-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Modal styles */
        .modal-header {
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-footer {
            border-top: 1px solid #e5e7eb;
        }

        .modal-body textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
        }

        .modal-body textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .request-summary {
            background: #f9fafb;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 16px;
        }

        .request-summary .label {
            font-size: 12px;
            color: #6b7280;
        }

        .request-summary .value {
            font-weight: 500;
            color: #1f2937;
        }

        @media (max-width: 768px) {
            .pending-header {
                padding: 20px;
            }

            .pending-body {
                padding: 16px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('leave.requests.index') }}">Back</a>
        @endslot
        @slot('title')
            My Requests
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

    <div class="pending-container">
        <div class="pending-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 style="margin:0;">
                        Pending Approvals
                        <span class="pending-count-badge">{{ $pendingRequests->count() }}</span>
                    </h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Review and approve leave requests from your team</p>
                </div>
                <button type="button" class="refresh-btn" onclick="window.location.reload();">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </div>
        <div class="pending-body">
            <div class="help-text">
                <div class="help-title">Approval Queue</div>
                <div class="help-content">
                    Review and approve or reject leave requests from your direct reports.
                    Click on a request to view full details before making a decision.
                </div>
            </div>

            @if($pendingRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Requester</th>
                                <th>Leave Type</th>
                                <th>Dates</th>
                                <th class="text-center">Days</th>
                                <th>Submitted</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingRequests as $request)
                                <tr data-request-id="{{ $request->id }}">
                                    <td>
                                        <div class="requester-info">
                                            <span class="requester-name">{{ $request->user->name ?? 'N/A' }}</span>
                                            @if($request->user && $request->user->department)
                                                <span class="requester-dept">{{ $request->user->department }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="leave-type-name">
                                            @if($request->leaveType && $request->leaveType->color)
                                                <span class="color-indicator" style="background-color: {{ $request->leaveType->color }};"></span>
                                            @endif
                                            {{ $request->leaveType->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="date-range">
                                            {{ $request->start_date->format('d M Y') }}
                                            @if($request->start_date->format('Y-m-d') !== $request->end_date->format('Y-m-d'))
                                                - {{ $request->end_date->format('d M Y') }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="days-count">{{ number_format($request->total_days, 1) }}</span>
                                    </td>
                                    <td>
                                        {{ $request->submitted_at ? $request->submitted_at->format('d M Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="text-end">
                                        <div class="action-buttons">
                                            <a href="{{ route('leave.requests.show', $request) }}"
                                                class="btn btn-info" title="View Details">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <button type="button" class="btn btn-success approve-btn"
                                                data-request-id="{{ $request->id }}"
                                                data-requester="{{ $request->user->name ?? 'N/A' }}"
                                                data-days="{{ number_format($request->total_days, 1) }}"
                                                data-dates="{{ $request->start_date->format('d M Y') }} - {{ $request->end_date->format('d M Y') }}"
                                                title="Approve">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button type="button" class="btn btn-danger reject-btn"
                                                data-request-id="{{ $request->id }}"
                                                data-requester="{{ $request->user->name ?? 'N/A' }}"
                                                data-days="{{ number_format($request->total_days, 1) }}"
                                                data-dates="{{ $request->start_date->format('d M Y') }} - {{ $request->end_date->format('d M Y') }}"
                                                title="Reject">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h4>No Pending Approvals</h4>
                    <p>You don't have any leave requests awaiting your approval at this time.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Approve Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="approve-form" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="request-summary">
                            <div class="row">
                                <div class="col-6">
                                    <div class="label">Requester</div>
                                    <div class="value" id="approve-requester"></div>
                                </div>
                                <div class="col-6">
                                    <div class="label">Leave Days</div>
                                    <div class="value" id="approve-days"></div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="label">Dates</div>
                                    <div class="value" id="approve-dates"></div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="approve-comments" class="form-label">Comments (optional)</label>
                            <textarea name="comments" id="approve-comments" class="form-control"
                                placeholder="Add any comments for the employee..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-loading">
                            <span class="btn-text"><i class="fas fa-check"></i> Approve Request</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="reject-form" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="request-summary">
                            <div class="row">
                                <div class="col-6">
                                    <div class="label">Requester</div>
                                    <div class="value" id="reject-requester"></div>
                                </div>
                                <div class="col-6">
                                    <div class="label">Leave Days</div>
                                    <div class="value" id="reject-days"></div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="label">Dates</div>
                                    <div class="value" id="reject-dates"></div>
                                </div>
                            </div>
                        </div>
                        <p class="text-danger mb-3">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            You are about to reject this leave request.
                        </p>
                        <div class="mb-3">
                            <label for="reject-reason" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea name="reason" id="reject-reason" class="form-control" required
                                placeholder="Please provide a reason for rejecting this request..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-loading">
                            <span class="btn-text"><i class="fas fa-times"></i> Reject Request</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const approveModal = new bootstrap.Modal(document.getElementById('approveModal'));
            const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));

            // Handle approve button clicks
            document.querySelectorAll('.approve-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const requestId = this.dataset.requestId;
                    const requester = this.dataset.requester;
                    const days = this.dataset.days;
                    const dates = this.dataset.dates;

                    document.getElementById('approve-requester').textContent = requester;
                    document.getElementById('approve-days').textContent = days + ' days';
                    document.getElementById('approve-dates').textContent = dates;
                    document.getElementById('approve-form').action = `/leave/requests/${requestId}/approve`;
                    document.getElementById('approve-comments').value = '';

                    approveModal.show();
                });
            });

            // Handle reject button clicks
            document.querySelectorAll('.reject-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const requestId = this.dataset.requestId;
                    const requester = this.dataset.requester;
                    const days = this.dataset.days;
                    const dates = this.dataset.dates;

                    document.getElementById('reject-requester').textContent = requester;
                    document.getElementById('reject-days').textContent = days + ' days';
                    document.getElementById('reject-dates').textContent = dates;
                    document.getElementById('reject-form').action = `/leave/requests/${requestId}/reject`;
                    document.getElementById('reject-reason').value = '';

                    rejectModal.show();
                });
            });

            // Handle form submissions with loading state
            const forms = document.querySelectorAll('#approve-form, #reject-form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });

            // Auto-dismiss alerts
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
