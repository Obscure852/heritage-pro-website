@extends('layouts.master')
@section('title')
    Policy Configuration - {{ $leaveType->name }}
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

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title:first-of-type {
            margin-top: 0;
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
            font-size: 12px;
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
            color: white;
        }

        .leave-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            background: #f3f4f6;
            border-radius: 3px;
            font-size: 13px;
        }

        .leave-type-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            display: inline-block;
        }

        .policy-card {
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .policy-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .policy-card-header h6 {
            margin: 0;
            font-weight: 600;
            color: #1f2937;
        }

        .policy-card-actions {
            display: flex;
            gap: 8px;
        }

        .policy-card-actions .btn-icon {
            padding: 4px 8px;
            font-size: 12px;
            border-radius: 3px;
        }

        .policy-card-body {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        @media (max-width: 768px) {
            .policy-card-body {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .policy-card-body {
                grid-template-columns: 1fr;
            }
        }

        .policy-field {
            font-size: 13px;
        }

        .policy-field-label {
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .policy-field-value {
            color: #1f2937;
        }

        .badge-mode {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .badge-allocation {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-accrual {
            background: #fef3c7;
            color: #b45309;
        }

        .badge-none {
            background: #f3f4f6;
            color: #6b7280;
        }

        .badge-limited {
            background: #fce7f3;
            color: #be185d;
        }

        .badge-full {
            background: #d1fae5;
            color: #047857;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            color: #d1d5db;
        }

        .empty-state h4 {
            margin: 0 0 8px 0;
            color: #374151;
        }

        .empty-state p {
            margin: 0;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-start;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('leave.settings.index') . '#leaveTypes' }}">Leave Settings</a>
        @endslot
        @slot('title')
            Policy Configuration
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
            <h1 class="page-title">Policy Configuration</h1>
            <div class="leave-type-badge">
                @if($leaveType->color)
                    <span class="leave-type-color" style="background-color: {{ $leaveType->color }};"></span>
                @endif
                <span>{{ $leaveType->name }} ({{ $leaveType->code }})</span>
            </div>
        </div>

        <div class="help-text">
            <div class="help-title">Yearly Policy Settings</div>
            <div class="help-content">
                Configure how leave balances for <strong>{{ $leaveType->name }}</strong> are managed for each year.
                You can set different policies for different years (e.g., allocation vs accrual, carry-over rules).
                Leave year starts in <strong>{{ DateTime::createFromFormat('!m', $leaveYearStartMonth)->format('F') }}</strong>.
            </div>
        </div>

        <h3 class="section-title">
            <span>Configured Policies</span>
            <a href="{{ route('leave.policies.create', $leaveType) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Policy Year
            </a>
        </h3>

        {{-- Policies List --}}
        <div id="policies-container">
            @if(count($policies) > 0)
                @foreach($policies as $policy)
                    <div class="policy-card" data-policy-id="{{ $policy->id }}">
                        <div class="policy-card-header">
                            <h6><i class="fas fa-calendar-alt me-2"></i>{{ $policy->leave_year }}</h6>
                            <div class="policy-card-actions">
                                <a href="{{ route('leave.policies.edit', [$leaveType, $policy]) }}"
                                    class="btn btn-outline-primary btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('leave.policies.destroy', [$leaveType, $policy]) }}"
                                    method="POST" style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete the policy for {{ $policy->leave_year }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-icon" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="policy-card-body">
                            <div class="policy-field">
                                <div class="policy-field-label">Balance Mode</div>
                                <div class="policy-field-value">
                                    <span class="badge-mode {{ $policy->balance_mode === 'allocation' ? 'badge-allocation' : 'badge-accrual' }}">
                                        {{ ucfirst($policy->balance_mode) }}
                                    </span>
                                </div>
                            </div>
                            @if($policy->balance_mode === 'accrual')
                                <div class="policy-field">
                                    <div class="policy-field-label">Accrual Rate</div>
                                    <div class="policy-field-value">{{ number_format($policy->accrual_rate, 2) }} days/month</div>
                                </div>
                            @endif
                            <div class="policy-field">
                                <div class="policy-field-label">Carry-Over</div>
                                <div class="policy-field-value">
                                    @php
                                        $carryOverClass = match($policy->carry_over_mode) {
                                            'none' => 'badge-none',
                                            'limited' => 'badge-limited',
                                            'full' => 'badge-full',
                                            default => 'badge-none'
                                        };
                                    @endphp
                                    <span class="badge-mode {{ $carryOverClass }}">{{ ucfirst($policy->carry_over_mode) }}</span>
                                    @if($policy->carry_over_mode === 'limited')
                                        (max {{ number_format($policy->carry_over_limit, 1) }} days)
                                    @endif
                                </div>
                            </div>
                            @if($policy->carry_over_expiry_months)
                                <div class="policy-field">
                                    <div class="policy-field-label">Expiry</div>
                                    <div class="policy-field-value">{{ $policy->carry_over_expiry_months }} months</div>
                                </div>
                            @endif
                            <div class="policy-field">
                                <div class="policy-field-label">Prorate New Staff</div>
                                <div class="policy-field-value">{{ $policy->prorate_new_employees ? 'Yes' : 'No' }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <i class="fas fa-calendar-alt"></i>
                    <h4>No Policies Configured</h4>
                    <p>Click "Add Policy Year" to create your first policy for this leave type.</p>
                </div>
            @endif
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('leave.settings.index') . '#leaveTypes' }}">
                <i class="bx bx-arrow-back"></i> Back to Leave Types
            </a>
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
