@extends('layouts.master')
@section('title')
    Clearance Status - {{ $student->full_name }}
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

        .form-container {
            background: white;
            border-radius: 3px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .info-card {
            background: #f9fafb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-card h6 {
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6b7280;
            font-size: 13px;
        }

        .info-value {
            font-weight: 500;
            color: #374151;
        }

        .balance-card {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .balance-card.cleared {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-color: #a7f3d0;
        }

        .balance-card h6 {
            font-weight: 600;
            margin-bottom: 16px;
        }

        .balance-amount {
            font-size: 2rem;
            font-weight: 700;
            color: #dc2626;
        }

        .balance-card.cleared .balance-amount {
            color: #059669;
        }

        .clearance-status-card {
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .clearance-status-card.cleared {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #a7f3d0;
        }

        .clearance-status-card.override {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 1px solid #fcd34d;
        }

        .clearance-status-card.not-cleared {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
        }

        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-cleared { background: #d1fae5; color: #065f46; }
        .status-override { background: #fef3c7; color: #92400e; }
        .status-not-cleared { background: #fee2e2; color: #991b1b; }

        .override-details {
            background: #f9fafb;
            border-radius: 3px;
            padding: 16px;
            margin-top: 16px;
        }

        .override-details .detail-row {
            display: flex;
            margin-bottom: 8px;
        }

        .override-details .detail-label {
            width: 120px;
            font-weight: 500;
            color: #6b7280;
        }

        .override-details .detail-value {
            flex: 1;
            color: #374151;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
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

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
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

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .year-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #d1fae5;
            color: #065f46;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
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

    @if (session('success'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
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

    {{-- Year Filter - Top Right --}}
    <div class="row mb-3">
        <div class="col-9"></div>
        <div class="col-3 d-flex justify-content-end">
            <form method="GET" action="{{ route('fees.balance.clearance', $student) }}">
                <select name="year" class="form-select" onchange="this.form.submit()">
                    @foreach ($years ?? [] as $year)
                        <option value="{{ $year }}"
                            {{ $selectedYear == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="fee-container">
        <div class="fee-header">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <h3 style="margin:0;">Clearance Status</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">{{ $student->full_name }} - {{ $student->student_number ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
        <div class="fee-body">
            {{-- Help Text --}}
            <div class="help-text">
                <div class="help-title">Student Clearance Status</div>
                <div class="help-content">
                    View this student's fee clearance status for the selected year. Students are cleared when their balance is zero or when an override has been granted. Use the year filter above to check clearance for different years.
                </div>
            </div>

            {{-- Action Button --}}
            <div class="mb-4 d-flex justify-content-end">
                <a href="{{ route('fees.collection.students.account', ['student' => $student->id, 'year' => $selectedYear]) }}" class="btn btn-primary">
                    <i class="fas fa-receipt me-1"></i> View Fee Account
                </a>
            </div>

            <div class="row">
                {{-- Student Info Card --}}
                <div class="col-md-6">
                    <div class="info-card">
                        <h6><i class="fas fa-user me-2"></i>Student Information</h6>
                        <div class="info-row">
                            <span class="info-label">Name</span>
                            <span class="info-value">{{ $student->full_name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Student Number</span>
                            <span class="info-value">{{ $student->formatted_id_number ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Grade</span>
                            <span class="info-value">{{ $student->currentGrade->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Sponsor</span>
                            <span class="info-value">{{ $student->sponsor->full_name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Balance Summary Card --}}
                <div class="col-md-6">
                    @php
                        $balance = $balanceData['balance'] ?? '0.00';
                        $isCleared = bccomp($balance, '0.00', 2) === 0;
                    @endphp
                    <div class="balance-card {{ $isCleared ? 'cleared' : '' }}">
                        <h6><i class="fas fa-wallet me-2"></i>Balance Summary</h6>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-muted small">Total Invoiced</div>
                                <div class="fw-bold">{{ format_currency($balanceData['total_invoiced'] ?? 0) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Total Paid</div>
                                <div class="fw-bold">{{ format_currency($balanceData['total_paid'] ?? 0) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Outstanding</div>
                                <div class="balance-amount">{{ format_currency($balance) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Clearance Status Section --}}
            @php
                $cleared = $clearanceData['cleared'] ?? false;
                $hasOverride = $clearanceData['has_override'] ?? false;
                $statusClass = $cleared ? ($hasOverride ? 'override' : 'cleared') : 'not-cleared';
                $badgeClass = $cleared ? ($hasOverride ? 'status-override' : 'status-cleared') : 'status-not-cleared';
                $statusText = $cleared ? ($hasOverride ? 'Cleared (Override)' : 'Cleared') : 'Not Cleared';
            @endphp

            <div class="clearance-status-card {{ $statusClass }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1"><i class="fas fa-shield-alt me-2"></i>Clearance Status</h6>
                        @if ($selectedYear)
                            <span class="year-badge">{{ $selectedYear }}</span>
                        @endif
                    </div>
                    <span class="status-badge {{ $badgeClass }}">{{ $statusText }}</span>
                </div>

                @if ($hasOverride && $clearanceRecord)
                    <div class="override-details">
                        <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Override Details</h6>
                        <div class="detail-row">
                            <span class="detail-label">Granted By:</span>
                            <span class="detail-value">{{ $clearanceRecord->grantedBy->name ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Date:</span>
                            <span class="detail-value">{{ $clearanceRecord->granted_at ? $clearanceRecord->granted_at->format('d M Y, H:i') : 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Reason:</span>
                            <span class="detail-value">{{ $clearanceRecord->reason }}</span>
                        </div>
                        @if ($clearanceRecord->notes)
                            <div class="detail-row">
                                <span class="detail-label">Notes:</span>
                                <span class="detail-value">{{ $clearanceRecord->notes }}</span>
                            </div>
                        @endif

                        @can('manage-fee-setup')
                            <div class="mt-3 pt-3 border-top">
                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#revokeModal">
                                    <i class="fas fa-times me-1"></i> Revoke Override
                                </button>
                            </div>
                        @endcan
                    </div>
                @endif
            </div>

            {{-- Grant Override Form (if not cleared and user has permission) --}}
            @if (!$cleared && $selectedYear)
                @can('manage-fee-setup')
                    @include('fees.balance.override-form', [
                        'student' => $student,
                        'year' => $selectedYear,
                    ])
                @endcan
            @endif

        </div>
    </div>

    {{-- Revoke Override Modal --}}
    @if ($hasOverride && $clearanceRecord && $selectedYear)
        <div class="modal fade" id="revokeModal" tabindex="-1" aria-labelledby="revokeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('fees.balance.override.revoke', ['student' => $student->id, 'year' => $selectedYear]) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="revokeModalLabel">Revoke Clearance Override</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to revoke the clearance override for <strong>{{ $student->full_name }}</strong>?</p>
                            <p class="text-danger"><small>This will mark the student as not cleared for this year.</small></p>
                            <div class="mb-3">
                                <label for="revokeReason" class="form-label">Reason for Revocation <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="revokeReason" name="reason" rows="3"
                                    required minlength="10" placeholder="Please provide a reason (minimum 10 characters)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger btn-loading">
                                <span class="btn-text"><i class="fas fa-times me-1"></i> Revoke Override</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Revoking...
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
