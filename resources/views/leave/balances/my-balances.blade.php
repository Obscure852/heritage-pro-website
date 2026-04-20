@extends('layouts.master')
@section('title')
    My Leave Balances
@endsection
@section('css')
    <style>
        .my-balances-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 24px;
        }

        .page-header h3 {
            margin: 0;
            color: #1f2937;
            font-weight: 600;
        }

        .page-header .year-badge {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .balance-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .balance-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .balance-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .balance-card-header {
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .leave-type-indicator {
            width: 8px;
            height: 100%;
            min-height: 60px;
            border-radius: 4px;
        }

        .leave-type-info h5 {
            margin: 0 0 4px 0;
            color: #1f2937;
            font-weight: 600;
            font-size: 16px;
        }

        .leave-type-info small {
            color: #6b7280;
            font-size: 13px;
        }

        .balance-card-body {
            padding: 16px;
        }

        .available-section {
            text-align: center;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .available-section .label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .available-section .value {
            font-size: 2rem;
            font-weight: 700;
        }

        .available-section .value.positive {
            color: #059669;
        }

        .available-section .value.zero {
            color: #6b7280;
        }

        .available-section .value.negative {
            color: #dc2626;
        }

        .available-section .unit {
            font-size: 14px;
            color: #6b7280;
            font-weight: 400;
        }

        .breakdown-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .breakdown-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 12px;
            background: #f9fafb;
            border-radius: 4px;
        }

        .breakdown-item .label {
            color: #6b7280;
            font-size: 13px;
        }

        .breakdown-item .value {
            font-weight: 500;
            color: #374151;
            font-size: 13px;
        }

        .breakdown-item .value.deduction {
            color: #dc2626;
        }

        .breakdown-item .value.addition {
            color: #059669;
        }

        .progress-section {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .progress-section .label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
            color: #6b7280;
        }

        .progress-bar-container {
            background: #e5e7eb;
            border-radius: 4px;
            height: 8px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .progress-bar-fill.low {
            background: linear-gradient(90deg, #059669 0%, #10b981 100%);
        }

        .progress-bar-fill.medium {
            background: linear-gradient(90deg, #f59e0b 0%, #fbbf24 100%);
        }

        .progress-bar-fill.high {
            background: linear-gradient(90deg, #dc2626 0%, #ef4444 100%);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h4 {
            color: #374151;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
            max-width: 400px;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .balance-cards {
                grid-template-columns: 1fr;
            }

            .breakdown-list {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }
    </style>
@endsection
@section('content')
    <div class="my-balances-container">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h3>My Leave Balances</h3>
            <span class="year-badge">
                <i class="fas fa-calendar-alt me-1"></i> Leave Year {{ $currentYear }}
            </span>
        </div>

        @if($balances->count() > 0)
            <div class="balance-cards">
                @foreach($balances as $balance)
                    @php
                        $total = $balance->entitled + $balance->carried_over + $balance->accrued + $balance->adjusted;
                        $usedAndPending = $balance->used + $balance->pending;
                        $usagePercent = $total > 0 ? min(100, ($usedAndPending / $total) * 100) : 0;
                        $progressClass = $usagePercent < 50 ? 'low' : ($usagePercent < 80 ? 'medium' : 'high');
                        $leaveTypeColor = $balance->leaveType->color ?? '#6b7280';
                    @endphp
                    <div class="balance-card">
                        <div class="balance-card-header">
                            <div class="leave-type-indicator" style="background-color: {{ $leaveTypeColor }};"></div>
                            <div class="leave-type-info">
                                <h5>{{ $balance->leaveType->name ?? 'Unknown' }}</h5>
                                <small>{{ $balance->leaveType->code ?? '' }}</small>
                            </div>
                        </div>
                        <div class="balance-card-body">
                            <div class="available-section">
                                <div class="label">Available Balance</div>
                                <div class="value {{ $balance->available > 0 ? 'positive' : ($balance->available < 0 ? 'negative' : 'zero') }}">
                                    {{ number_format($balance->available, 1) }}
                                    <span class="unit">days</span>
                                </div>
                            </div>

                            <div class="breakdown-list">
                                <div class="breakdown-item">
                                    <span class="label">Entitled</span>
                                    <span class="value">{{ number_format($balance->entitled, 1) }}</span>
                                </div>
                                <div class="breakdown-item">
                                    <span class="label">Carried Over</span>
                                    <span class="value addition">{{ number_format($balance->carried_over, 1) }}</span>
                                </div>
                                <div class="breakdown-item">
                                    <span class="label">Accrued</span>
                                    <span class="value addition">{{ number_format($balance->accrued, 1) }}</span>
                                </div>
                                <div class="breakdown-item">
                                    <span class="label">Adjusted</span>
                                    <span class="value {{ $balance->adjusted >= 0 ? 'addition' : 'deduction' }}">
                                        {{ $balance->adjusted >= 0 ? '+' : '' }}{{ number_format($balance->adjusted, 1) }}
                                    </span>
                                </div>
                                <div class="breakdown-item">
                                    <span class="label">Used</span>
                                    <span class="value deduction">-{{ number_format($balance->used, 1) }}</span>
                                </div>
                                <div class="breakdown-item">
                                    <span class="label">Pending</span>
                                    <span class="value deduction">-{{ number_format($balance->pending, 1) }}</span>
                                </div>
                            </div>

                            @if($total > 0)
                                <div class="progress-section">
                                    <div class="label">
                                        <span>Usage</span>
                                        <span>{{ number_format($usedAndPending, 1) }} / {{ number_format($total, 1) }} days</span>
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar-fill {{ $progressClass }}" style="width: {{ $usagePercent }}%;"></div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h4>No Leave Balances Found</h4>
                <p>
                    You don't have any leave balances for the current leave year.
                    Balances are typically allocated at the start of each leave year.
                    Please contact HR if you believe this is an error.
                </p>
            </div>
        @endif
    </div>
@endsection
