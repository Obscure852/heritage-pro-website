@extends('layouts.master')
@section('title')
    My Leave Dashboard
@endsection
@section('css')
    <style>
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .dashboard-header h3 {
            margin: 0;
        }

        .dashboard-header p {
            margin: 6px 0 0 0;
            opacity: .9;
        }

        .year-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        /* Stats Summary Row */
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        .summary-card {
            background: white;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.2s ease;
        }

        .summary-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .summary-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 20px;
        }

        .summary-card.available .icon {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #059669;
        }

        .summary-card.pending .icon {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
        }

        .summary-card.used .icon {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #2563eb;
        }

        .summary-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .summary-card.available .value { color: #059669; }
        .summary-card.pending .value { color: #d97706; }
        .summary-card.used .value { color: #2563eb; }

        .summary-card .label {
            font-size: 13px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Quick Actions */
        .quick-actions {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .quick-actions h5 {
            margin: 0 0 16px 0;
            color: #1f2937;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .action-btn.primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .action-btn.primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .action-btn.secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .action-btn.secondary:hover {
            background: #e5e7eb;
            transform: translateY(-1px);
            color: #374151;
        }

        /* Main Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        /* Balance Cards Section */
        .section-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .section-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h5 {
            margin: 0;
            color: #1f2937;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-header h5 i {
            color: #6b7280;
        }

        .section-header .view-all {
            font-size: 13px;
            color: #3b82f6;
            text-decoration: none;
        }

        .section-header .view-all:hover {
            text-decoration: underline;
        }

        .section-body {
            padding: 20px 24px;
        }

        /* Balance Cards */
        .balance-cards-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .balance-mini-card {
            display: flex;
            align-items: center;
            padding: 16px;
            background: #f9fafb;
            border-radius: 8px;
            gap: 16px;
            transition: all 0.2s ease;
        }

        .balance-mini-card:hover {
            background: #f3f4f6;
        }

        .leave-type-indicator {
            width: 6px;
            height: 40px;
            border-radius: 3px;
            flex-shrink: 0;
        }

        .balance-info {
            flex: 1;
        }

        .balance-info h6 {
            margin: 0 0 4px 0;
            color: #1f2937;
            font-weight: 600;
            font-size: 14px;
        }

        .balance-info small {
            color: #6b7280;
            font-size: 12px;
        }

        .balance-value {
            text-align: right;
        }

        .balance-value .days {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .balance-value .days.positive { color: #059669; }
        .balance-value .days.zero { color: #6b7280; }
        .balance-value .days.negative { color: #dc2626; }

        .balance-value .label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Recent Requests */
        .requests-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .request-item {
            display: flex;
            align-items: center;
            padding: 16px;
            background: #f9fafb;
            border-radius: 8px;
            gap: 16px;
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
        }

        .request-item:hover {
            background: #f3f4f6;
            text-decoration: none;
            color: inherit;
        }

        .request-type-indicator {
            width: 6px;
            height: 40px;
            border-radius: 3px;
            flex-shrink: 0;
        }

        .request-info {
            flex: 1;
        }

        .request-info .type-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .request-info .dates {
            font-size: 12px;
            color: #6b7280;
        }

        .request-meta {
            text-align: right;
        }

        .request-meta .days {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
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

        /* Empty States */
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

        .empty-state p {
            margin: 0;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .stats-summary {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 20px;
            }

            .dashboard-header .row {
                flex-direction: column;
                gap: 16px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-btn {
                justify-content: center;
            }
        }
    </style>
@endsection
@section('content')
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

    <div class="dashboard-container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3>My Leave Dashboard</h3>
                    <p>View your leave balances, track requests, and manage your time off</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="year-badge">
                        <i class="fas fa-calendar-alt me-1"></i> Leave Year {{ $currentYear }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="stats-summary">
            <div class="summary-card available">
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="value">{{ number_format($stats['total_available'], 1) }}</div>
                <div class="label">Days Available</div>
            </div>
            <div class="summary-card pending">
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="value">{{ number_format($stats['total_pending'], 1) }}</div>
                <div class="label">Days Pending</div>
            </div>
            <div class="summary-card used">
                <div class="icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="value">{{ number_format($stats['total_used'], 1) }}</div>
                <div class="label">Days Used</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            <div class="action-buttons">
                <a href="{{ route('leave.requests.create') }}" class="action-btn primary">
                    <i class="fas fa-plus"></i> New Leave Request
                </a>
                <a href="{{ route('leave.calendar.personal') }}" class="action-btn secondary">
                    <i class="fas fa-calendar"></i> View Calendar
                </a>
                <a href="{{ route('leave.balances.my-balances') }}" class="action-btn secondary">
                    <i class="fas fa-chart-pie"></i> Detailed Balances
                </a>
                <a href="{{ route('leave.policies.view') }}" class="action-btn secondary">
                    <i class="fas fa-book"></i> View Policies
                </a>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="content-grid">
            <!-- Balance Cards Section -->
            <div class="section-card">
                <div class="section-header">
                    <h5><i class="fas fa-wallet"></i> Leave Balances</h5>
                    <a href="{{ route('leave.balances.my-balances') }}" class="view-all">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                <div class="section-body">
                    @if($balances->count() > 0)
                        <div class="balance-cards-grid">
                            @foreach($balances->take(5) as $balance)
                                @php
                                    $available = (float) $balance->available;
                                    $leaveTypeColor = $balance->leaveType->color ?? '#6b7280';
                                    $daysClass = $available > 0 ? 'positive' : ($available < 0 ? 'negative' : 'zero');
                                @endphp
                                <div class="balance-mini-card">
                                    <div class="leave-type-indicator" style="background-color: {{ $leaveTypeColor }};"></div>
                                    <div class="balance-info">
                                        <h6>{{ $balance->leaveType->name ?? 'Unknown' }}</h6>
                                        <small>Entitled: {{ number_format($balance->entitled, 1) }} | Used: {{ number_format($balance->used, 1) }}</small>
                                    </div>
                                    <div class="balance-value">
                                        <div class="days {{ $daysClass }}">{{ number_format($available, 1) }}</div>
                                        <div class="label">Available</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($balances->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('leave.balances.my-balances') }}" class="text-muted" style="font-size: 13px;">
                                    + {{ $balances->count() - 5 }} more leave types
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="empty-state">
                            <i class="fas fa-wallet"></i>
                            <p>No leave balances found for this year.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Requests Section -->
            <div class="section-card">
                <div class="section-header">
                    <h5><i class="fas fa-history"></i> Recent Requests</h5>
                    <a href="{{ route('leave.requests.index') }}" class="view-all">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                <div class="section-body">
                    @if($recentRequests->count() > 0)
                        <div class="requests-list">
                            @foreach($recentRequests->take(5) as $request)
                                @php
                                    $leaveTypeColor = $request->leaveType->color ?? '#6b7280';
                                    $statusClass = 'status-' . $request->status;
                                @endphp
                                <a href="{{ route('leave.requests.show', $request) }}" class="request-item">
                                    <div class="request-type-indicator" style="background-color: {{ $leaveTypeColor }};"></div>
                                    <div class="request-info">
                                        <div class="type-name">{{ $request->leaveType->name ?? 'Unknown' }}</div>
                                        <div class="dates">
                                            {{ $request->start_date->format('d M Y') }}
                                            @if($request->start_date->format('Y-m-d') !== $request->end_date->format('Y-m-d'))
                                                - {{ $request->end_date->format('d M Y') }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="request-meta">
                                        <div class="days">{{ number_format($request->total_days, 1) }} days</div>
                                        <span class="status-badge {{ $statusClass }}">{{ ucfirst($request->status) }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        @if($recentRequests->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('leave.requests.index') }}" class="text-muted" style="font-size: 13px;">
                                    + {{ $recentRequests->count() - 5 }} more requests
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="empty-state">
                            <i class="fas fa-calendar-check"></i>
                            <p>No leave requests found.</p>
                            <a href="{{ route('leave.requests.create') }}" class="btn btn-sm btn-primary mt-3">
                                Submit Your First Request
                            </a>
                        </div>
                    @endif
                </div>
            </div>
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
