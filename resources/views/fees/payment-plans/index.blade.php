@extends('layouts.master')
@section('title')
    Payment Plans
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
        }

        .page-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-top: 4px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
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

        .filter-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px;
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
            font-size: 14px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active { background: #dbeafe; color: #1e40af; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #e5e7eb; color: #374151; }

        .progress-cell {
            min-width: 120px;
        }

        .progress {
            height: 6px;
            border-radius: 3px;
            background: #e5e7eb;
            margin-bottom: 4px;
        }

        .progress-bar {
            border-radius: 3px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            font-size: 64px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h6 {
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 20px;
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

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
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

        .btn-outline-primary {
            background: transparent;
            border: 1px solid #3b82f6;
            color: #3b82f6;
            padding: 5px 10px;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
            color: white;
        }

        .pagination-wrapper {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .page-header {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

            .filter-section .row {
                gap: 12px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('fees.collection.invoices.index') }}">Fee Administration</a>
        @endslot
        @slot('title')
            Payment Plans
        @endslot
    @endcomponent

    @if(session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
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
            <div>
                <h1 class="page-title">Payment Plans</h1>
                <p class="page-subtitle">Manage installment payment schedules for student invoices</p>
            </div>
            <a href="{{ route('fees.collection.invoices.index') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Create New Plan
            </a>
        </div>

        <div class="help-text">
            <div class="help-title">How Payment Plans Work</div>
            <div class="help-content">
                Payment plans allow students to pay invoices in installments. To create a new plan, find the student's invoice and click the "Payment Plan" button.
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search Student</label>
                    <input type="text" name="search" class="form-control" placeholder="Name or student number..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        <option value="">All Years</option>
                        @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'year']))
                        <a href="{{ route('fees.payment-plans.index') }}" class="btn btn-outline-primary ms-2">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Invoice</th>
                        <th>Plan Name</th>
                        <th class="text-end">Total</th>
                        <th>Installments</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paymentPlans as $plan)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $plan->student->full_name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $plan->student->student_number ?? '-' }}</small>
                            </td>
                            <td>
                                <a href="{{ route('fees.collection.invoices.show', $plan->student_invoice_id) }}">
                                    {{ $plan->invoice->invoice_number ?? 'N/A' }}
                                </a>
                            </td>
                            <td>{{ $plan->name ?? '-' }}</td>
                            <td class="text-end">P{{ number_format($plan->total_amount, 2) }}</td>
                            <td>
                                <span class="fw-semibold">{{ $plan->installments->where('status', 'paid')->count() }}</span>
                                <span class="text-muted">/ {{ $plan->number_of_installments }}</span>
                            </td>
                            <td class="progress-cell">
                                @php
                                    $progress = $plan->total_amount > 0
                                        ? round(($plan->total_paid / $plan->total_amount) * 100)
                                        : 0;
                                @endphp
                                <div class="progress">
                                    <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                                </div>
                                <small class="text-muted">{{ $progress }}% paid</small>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $plan->status }}">
                                    {{ ucfirst($plan->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('fees.payment-plans.show', $plan) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-calendar-times"></i>
                                    </div>
                                    <h6>No Payment Plans Found</h6>
                                    <p>Payment plans allow students to pay invoices in installments.<br>To create one, find a student's invoice and click "Payment Plan".</p>
                                    <a href="{{ route('fees.collection.invoices.index') }}" class="btn btn-success">
                                        <i class="fas fa-plus"></i> Create New Plan
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paymentPlans->hasPages())
            <div class="pagination-wrapper">
                {{ $paymentPlans->links() }}
            </div>
        @endif
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
