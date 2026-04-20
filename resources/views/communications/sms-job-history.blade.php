@extends('layouts.master')
@section('title')
    SMS Delivery Log
@endsection
@section('css')
    <style>
        .admissions-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admissions-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .admissions-body {
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

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-completed { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1e40af; }
        .status-failed { background: #fee2e2; color: #991b1b; }
        .status-cancelled { background: #f3f4f6; color: #4b5563; }

        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 14px;
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

        .cost-badge {
            background-color: #d1fae5;
            color: #065f46;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .progress-cell {
            min-width: 150px;
        }

        .job-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .job-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-radius: 3px;
            padding: 8px 0;
        }

        .dropdown-item {
            padding: 8px 16px;
            font-size: 14px;
            color: #374151;
        }

        .dropdown-item:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .dropdown-item i {
            width: 20px;
            margin-right: 8px;
        }

        /* Modal Theming */
        .modal-content {
            border: none;
            border-radius: 3px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .modal-header .modal-title {
            font-weight: 600;
            font-size: 16px;
            color: #374151;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-body h6 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }

            .admissions-header {
                padding: 20px;
            }

            .admissions-body {
                padding: 16px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('notifications.bulk-sms-index') }}">Back</a>
        @endslot
        @slot('title')
            SMS Delivery Log
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

    <div class="admissions-container">
        <div class="admissions-header">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <h3 style="margin:0;">SMS Delivery Log</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Track and monitor SMS delivery status</p>
                </div>
                <div class="col-md-7">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ number_format($summary['total_sms_sent']) }}</h4>
                                <small class="opacity-75">Sent</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $summary['success_rate'] }}%</h4>
                                <small class="opacity-75">Success</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ number_format($summary['total_jobs']) }}</h4>
                                <small class="opacity-75">Jobs</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ number_format($summary['total_cost'], 0) }}</h4>
                                <small class="opacity-75">BWP Spent</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="admissions-body">
            <div class="help-text">
                <div class="help-title">Delivery Tracking</div>
                <div class="help-content">
                    View the history of all SMS jobs. Track progress, view details, and monitor delivery status.
                    Use the filters to find specific jobs by status, recipient type, or date range.
                </div>
            </div>

            <div class="row align-items-center mb-3">
                <div class="col-lg-9 col-md-12">
                    <div class="controls">
                        <form method="GET" action="{{ route('sms.delivery-log') }}" class="row g-2 align-items-center">
                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select name="recipient_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="sponsor" {{ request('recipient_type') === 'sponsor' ? 'selected' : '' }}>Sponsors</option>
                                    <option value="user" {{ request('recipient_type') === 'user' ? 'selected' : '' }}>Staff</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From">
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To">
                            </div>
                            <div class="col-lg-1 col-md-1 col-sm-6">
                                <button type="submit" class="btn btn-primary w-100" style="padding: 8px;">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div class="col-lg-2 col-md-12">
                                <a href="{{ route('sms.delivery-log') }}" class="btn btn-light w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-3 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <a href="{{ route('notifications.bulk-sms-index') }}" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Send New SMS
                    </a>
                </div>
            </div>

            @if ($jobs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Job</th>
                                <th>Recipients</th>
                                <th>Message</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Cost</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jobs as $job)
                                <tr>
                                    <td>
                                        <div class="job-cell">
                                            <div class="job-avatar">
                                                <i class="fas fa-sms"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $job->created_at->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $job->created_at->format('h:i A') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-medium">{{ $job->total_recipients }}</span>
                                        <small class="text-muted d-block">{{ ucfirst($job->recipient_type) }}s</small>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 200px;" title="{{ $job->message }}">
                                            {{ Str::limit($job->message, 40) }}
                                        </div>
                                    </td>
                                    <td class="progress-cell">
                                        @if ($job->status === 'processing' || $job->status === 'completed')
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 6px; border-radius: 3px;">
                                                    <div class="progress-bar {{ $job->status === 'completed' ? 'bg-success' : 'bg-info progress-bar-striped progress-bar-animated' }}"
                                                        style="width: {{ $job->percentage }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $job->percentage }}%</small>
                                            </div>
                                            <small class="text-muted">
                                                <span class="text-success">{{ $job->sent_count }}</span> sent,
                                                <span class="text-danger">{{ $job->failed_count }}</span> failed
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="status-badge status-{{ $job->status }}">
                                            {{ ucfirst($job->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="cost-badge">
                                            BWP {{ number_format($job->total_cost, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-light" onclick="viewJobDetails('{{ $job->job_id }}')" title="View Details">
                                                <i class="fas fa-eye text-primary"></i>
                                            </button>
                                            @if ($job->status === 'processing')
                                                <button class="btn btn-light" onclick="cancelJob('{{ $job->job_id }}')" title="Cancel">
                                                    <i class="fas fa-stop-circle text-danger"></i>
                                                </button>
                                            @endif
                                            @if ($job->failed_count > 0)
                                                <button class="btn btn-light" onclick="retryJob('{{ $job->job_id }}')" title="Retry Failed">
                                                    <i class="fas fa-redo text-warning"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $jobs->firstItem() ?? 0 }} to {{ $jobs->lastItem() ?? 0 }} of {{ $jobs->total() }} jobs
                    </div>
                    {{ $jobs->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No SMS jobs found</h5>
                    <p class="text-muted">Your SMS sending history will appear here</p>
                    <a href="{{ route('notifications.bulk-sms-index') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-paper-plane me-1"></i> Send First SMS
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Job Details Modal -->
    <div class="modal fade" id="jobDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Job Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="jobDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function viewJobDetails(jobId) {
            $('#jobDetailsModal').modal('show');

            fetch(`/notifications/job-progress/${jobId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const progress = data.progress;
                        let html = `
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6>Status</h6>
                                    <p><span class="status-badge status-${progress.status}">${progress.status.charAt(0).toUpperCase() + progress.status.slice(1)}</span></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Progress</h6>
                                    <div class="progress mb-2" style="height: 20px; border-radius: 3px;">
                                        <div class="progress-bar bg-success" style="width: ${progress.percentage}%">
                                            ${progress.percentage}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <h6>Total Recipients</h6>
                                    <p class="fw-bold mb-0">${progress.total}</p>
                                </div>
                                <div class="col-md-4">
                                    <h6>Successfully Sent</h6>
                                    <p class="fw-bold text-success mb-0">${progress.sent}</p>
                                </div>
                                <div class="col-md-4">
                                    <h6>Failed</h6>
                                    <p class="fw-bold text-danger mb-0">${progress.failed}</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6>Status Message</h6>
                                    <p class="text-muted mb-0">${progress.message}</p>
                                </div>
                            </div>
                            ${progress.cost ? `
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6>Total Cost</h6>
                                    <p class="fw-bold mb-0">BWP ${progress.cost.toFixed(2)}</p>
                                </div>
                            </div>
                            ` : ''}
                        `;
                        document.getElementById('jobDetailsContent').innerHTML = html;
                    }
                })
                .catch(error => {
                    document.getElementById('jobDetailsContent').innerHTML = `
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Failed to load job details. Please try again.
                        </div>
                    `;
                });
        }

        function cancelJob(jobId) {
            if (confirm('Are you sure you want to cancel this SMS job? Messages already sent cannot be recalled.')) {
                fetch(`/notifications/job-cancel/${jobId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Job cancelled successfully');
                        location.reload();
                    } else {
                        alert('Failed to cancel job: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error cancelling job');
                });
            }
        }

        function retryJob(jobId) {
            alert('Retry functionality will be implemented based on your requirements');
        }

        // Auto-refresh for active jobs
        @if ($jobs->where('status', 'processing')->count() > 0)
            setInterval(() => {
                location.reload();
            }, 10000);
        @endif
    </script>
@endsection
