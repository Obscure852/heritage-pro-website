@extends('layouts.master')
@section('title')
    Leave Request Details
@endsection
@section('css')
    <style>
        .request-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .request-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .request-body {
            padding: 24px;
        }

        .request-id {
            font-family: monospace;
            font-size: 14px;
            opacity: 0.9;
            margin-top: 4px;
        }

        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            text-transform: capitalize;
            display: inline-block;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-cancelled {
            background: #f3f4f6;
            color: #4b5563;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            margin-bottom: 24px;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-card {
            background: #f9fafb;
            border-radius: 6px;
            padding: 20px;
        }

        .info-card h5 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 16px;
            font-weight: 600;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row .label {
            color: #6b7280;
            font-size: 13px;
        }

        .info-row .value {
            font-weight: 500;
            color: #1f2937;
            font-size: 13px;
        }

        .reason-section {
            background: #f9fafb;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .reason-section h5 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }

        .reason-section p {
            color: #4b5563;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
            white-space: pre-wrap;
        }

        .attachments-section {
            background: #f9fafb;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .attachments-section h5 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }

        .attachment-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: white;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .attachment-item:last-child {
            margin-bottom: 0;
        }

        .attachment-item i {
            color: #6b7280;
            font-size: 18px;
        }

        .attachment-item .file-name {
            flex: 1;
            font-size: 13px;
            color: #374151;
        }

        .attachment-item .file-size {
            font-size: 12px;
            color: #9ca3af;
        }

        .attachment-item a {
            color: #3b82f6;
            font-size: 13px;
            text-decoration: none;
        }

        .attachment-item a:hover {
            text-decoration: underline;
        }

        .timeline-section {
            background: #f9fafb;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .timeline-section h5 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e5e7eb;
            border: 2px solid white;
        }

        .timeline-item.completed::before {
            background: #10b981;
        }

        .timeline-item.active::before {
            background: #3b82f6;
        }

        .timeline-item.rejected::before {
            background: #ef4444;
        }

        .timeline-item.cancelled::before {
            background: #6b7280;
        }

        .timeline-item .event {
            font-weight: 500;
            color: #1f2937;
            font-size: 13px;
        }

        .timeline-item .time {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        .timeline-item .by {
            font-size: 12px;
            color: #6b7280;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
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
            .request-header {
                padding: 20px;
            }

            .request-body {
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
            <a class="text-muted font-size-14" href="{{ route('leave.requests.index') }}">My Leave Requests</a>
        @endslot
        @slot('title')
            Request Details
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

    <div class="request-container">
        <div class="request-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 style="margin:0;">Leave Request Details</h3>
                    <div class="request-id">ID: {{ $leaveRequest->ulid }}</div>
                </div>
                <div>
                    @switch($leaveRequest->status)
                        @case('pending')
                            <span class="status-badge status-pending">Pending Approval</span>
                            @break
                        @case('approved')
                            <span class="status-badge status-approved">Approved</span>
                            @break
                        @case('rejected')
                            <span class="status-badge status-rejected">Rejected</span>
                            @break
                        @case('cancelled')
                            <span class="status-badge status-cancelled">Cancelled</span>
                            @break
                    @endswitch
                </div>
            </div>
        </div>
        <div class="request-body">
            <div class="info-grid">
                <div class="info-card">
                    <h5>Leave Details</h5>
                    <div class="info-row">
                        <span class="label">Leave Type</span>
                        <span class="value">
                            @if($leaveRequest->leaveType && $leaveRequest->leaveType->color)
                                <span class="color-indicator" style="background-color: {{ $leaveRequest->leaveType->color }};"></span>
                            @endif
                            {{ $leaveRequest->leaveType->name ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Start Date</span>
                        <span class="value">
                            {{ $leaveRequest->start_date->format('d M Y') }}
                            @if($leaveRequest->start_half_day)
                                <span class="text-muted">({{ $leaveRequest->start_half_day === 'am' ? 'Morning' : 'Afternoon' }} only)</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">End Date</span>
                        <span class="value">
                            {{ $leaveRequest->end_date->format('d M Y') }}
                            @if($leaveRequest->end_half_day)
                                <span class="text-muted">({{ $leaveRequest->end_half_day === 'am' ? 'Morning' : 'Afternoon' }} only)</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Total Days</span>
                        <span class="value">{{ number_format($leaveRequest->total_days, 1) }} days</span>
                    </div>
                </div>

                <div class="info-card">
                    <h5>Request Information</h5>
                    <div class="info-row">
                        <span class="label">Requested By</span>
                        <span class="value">{{ $leaveRequest->user->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Submitted At</span>
                        <span class="value">{{ $leaveRequest->submitted_at ? $leaveRequest->submitted_at->format('d M Y H:i') : 'N/A' }}</span>
                    </div>
                    @if($leaveRequest->status === 'approved' || $leaveRequest->status === 'rejected')
                        <div class="info-row">
                            <span class="label">{{ $leaveRequest->status === 'approved' ? 'Approved By' : 'Rejected By' }}</span>
                            <span class="value">{{ $leaveRequest->approver->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Decision Date</span>
                            <span class="value">{{ $leaveRequest->approved_at ? $leaveRequest->approved_at->format('d M Y H:i') : 'N/A' }}</span>
                        </div>
                    @endif
                    @if($leaveRequest->status === 'cancelled')
                        <div class="info-row">
                            <span class="label">Cancelled By</span>
                            <span class="value">{{ $leaveRequest->cancelledBy->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Cancelled At</span>
                            <span class="value">{{ $leaveRequest->cancelled_at ? $leaveRequest->cancelled_at->format('d M Y H:i') : 'N/A' }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="reason-section">
                <h5>Reason for Leave</h5>
                <p>{{ $leaveRequest->reason ?? 'No reason provided.' }}</p>
            </div>

            @if($leaveRequest->approver_comments && ($leaveRequest->status === 'approved' || $leaveRequest->status === 'rejected'))
                <div class="reason-section">
                    <h5>{{ $leaveRequest->status === 'approved' ? 'Approver Comments' : 'Rejection Reason' }}</h5>
                    <p>{{ $leaveRequest->approver_comments }}</p>
                </div>
            @endif

            @if($leaveRequest->cancellation_reason && $leaveRequest->status === 'cancelled')
                <div class="reason-section">
                    <h5>Cancellation Reason</h5>
                    <p>{{ $leaveRequest->cancellation_reason }}</p>
                </div>
            @endif

            @if($leaveRequest->attachments && $leaveRequest->attachments->count() > 0)
                <div class="attachments-section">
                    <h5>Attachments</h5>
                    @foreach($leaveRequest->attachments as $attachment)
                        <div class="attachment-item">
                            <i class="fas fa-file-alt"></i>
                            <span class="file-name">{{ $attachment->file_name }}</span>
                            <span class="file-size">{{ number_format($attachment->file_size / 1024, 1) }} KB</span>
                            <a href="{{ Storage::url($attachment->file_path) }}" target="_blank">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="timeline-section">
                <h5>Request Timeline</h5>
                <div class="timeline">
                    <div class="timeline-item completed">
                        <div class="event">Request Submitted</div>
                        <div class="time">{{ $leaveRequest->submitted_at ? $leaveRequest->submitted_at->format('d M Y H:i') : 'N/A' }}</div>
                        <div class="by">by {{ $leaveRequest->user->name ?? 'N/A' }}</div>
                    </div>

                    @if($leaveRequest->status === 'pending')
                        <div class="timeline-item active">
                            <div class="event">Awaiting Approval</div>
                            <div class="time">Pending manager review</div>
                        </div>
                    @elseif($leaveRequest->status === 'approved')
                        <div class="timeline-item completed">
                            <div class="event">Request Approved</div>
                            <div class="time">{{ $leaveRequest->approved_at ? $leaveRequest->approved_at->format('d M Y H:i') : 'N/A' }}</div>
                            <div class="by">by {{ $leaveRequest->approver->name ?? 'N/A' }}</div>
                        </div>
                    @elseif($leaveRequest->status === 'rejected')
                        <div class="timeline-item rejected">
                            <div class="event">Request Rejected</div>
                            <div class="time">{{ $leaveRequest->approved_at ? $leaveRequest->approved_at->format('d M Y H:i') : 'N/A' }}</div>
                            <div class="by">by {{ $leaveRequest->approver->name ?? 'N/A' }}</div>
                        </div>
                    @elseif($leaveRequest->status === 'cancelled')
                        <div class="timeline-item cancelled">
                            <div class="event">Request Cancelled</div>
                            <div class="time">{{ $leaveRequest->cancelled_at ? $leaveRequest->cancelled_at->format('d M Y H:i') : 'N/A' }}</div>
                            <div class="by">by {{ $leaveRequest->cancelledBy->name ?? 'N/A' }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Audit Trail Section (AUDT-04) --}}
            @if(isset($auditLogs) && isset($auditService))
                @include('leave.audit._history', ['auditLogs' => $auditLogs, 'auditService' => $auditService])
            @endif

            <div class="action-buttons">
                <a href="{{ route('leave.requests.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Requests
                </a>

                @if($canApprove)
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times"></i> Reject
                    </button>
                @endif

                @if($canCancel)
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="fas fa-ban"></i> Cancel Request
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    @if($canApprove)
        <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">Approve Leave Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="approve-form" action="{{ route('leave.requests.approve', $leaveRequest) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>You are about to approve this leave request for <strong>{{ number_format($leaveRequest->total_days, 1) }} days</strong>.</p>
                            <div class="mb-3">
                                <label for="approve-comments" class="form-label">Comments (optional)</label>
                                <textarea name="comments" id="approve-comments" class="form-control"
                                    placeholder="Add any comments for the employee..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success btn-loading" id="approve-btn">
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
                    <form id="reject-form" action="{{ route('leave.requests.reject', $leaveRequest) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p class="text-danger">You are about to reject this leave request.</p>
                            <div class="mb-3">
                                <label for="reject-reason" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                <textarea name="reason" id="reject-reason" class="form-control" required
                                    placeholder="Please provide a reason for rejecting this request..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger btn-loading" id="reject-btn">
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
    @endif

    <!-- Cancel Modal -->
    @if($canCancel)
        <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelModalLabel">Cancel Leave Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="cancel-form" action="{{ route('leave.requests.cancel', $leaveRequest) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p class="text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                You are about to cancel this leave request. This action cannot be undone.
                            </p>
                            <div class="mb-3">
                                <label for="cancel-reason" class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
                                <textarea name="reason" id="cancel-reason" class="form-control" required
                                    placeholder="Please provide a reason for cancelling this request..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger btn-loading" id="cancel-btn">
                                <span class="btn-text"><i class="fas fa-ban"></i> Cancel Request</span>
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
    @endif
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle form submissions with loading state
            const forms = document.querySelectorAll('#approve-form, #reject-form, #cancel-form');

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
