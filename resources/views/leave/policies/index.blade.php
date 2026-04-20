@extends('layouts.master')
@section('title')
    Leave Policies
@endsection
@section('css')
    <style>
        .policies-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .policies-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .policies-header h3 {
            margin: 0;
        }

        .policies-header p {
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

        .policies-body {
            padding: 24px;
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

        /* Policy Cards Grid */
        .policy-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .policy-card {
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .policy-card:hover {
            border-color: #d1d5db;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .policy-card-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .leave-type-indicator {
            width: 8px;
            height: 100%;
            min-height: 50px;
            border-radius: 4px;
            flex-shrink: 0;
        }

        .leave-type-info {
            flex: 1;
        }

        .leave-type-info h5 {
            margin: 0 0 4px 0;
            color: #1f2937;
            font-weight: 600;
            font-size: 16px;
        }

        .leave-type-info .code {
            font-family: monospace;
            font-size: 12px;
            color: #6b7280;
            background: #f3f4f6;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .leave-type-badges {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .type-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-paid {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-unpaid {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-gender {
            background: #dbeafe;
            color: #1e40af;
        }

        .policy-card-body {
            padding: 20px;
        }

        /* Policy Details Grid */
        .policy-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .detail-item {
            padding: 12px;
            background: #f9fafb;
            border-radius: 6px;
        }

        .detail-item .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .detail-item .value {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }

        .detail-item .value.highlight {
            color: #059669;
        }

        .detail-item.full-width {
            grid-column: span 2;
        }

        /* Rules Section */
        .rules-section {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .rules-section h6 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin: 0 0 12px 0;
        }

        .rules-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .rule-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: #f3f4f6;
            border-radius: 6px;
            font-size: 12px;
            color: #374151;
        }

        .rule-tag i {
            font-size: 10px;
        }

        .rule-tag.positive i { color: #059669; }
        .rule-tag.negative i { color: #dc2626; }
        .rule-tag.neutral i { color: #6b7280; }

        /* Description */
        .leave-description {
            margin-top: 16px;
            padding: 12px;
            background: #fffbeb;
            border-radius: 6px;
            border-left: 3px solid #f59e0b;
        }

        .leave-description p {
            margin: 0;
            font-size: 13px;
            color: #92400e;
            line-height: 1.5;
        }

        /* No Policy State */
        .no-policy {
            text-align: center;
            padding: 24px;
            color: #6b7280;
            background: #f9fafb;
            border-radius: 6px;
        }

        .no-policy i {
            font-size: 24px;
            opacity: 0.4;
            margin-bottom: 8px;
        }

        .no-policy p {
            margin: 0;
            font-size: 13px;
        }

        /* Empty State */
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
            margin-bottom: 0;
        }

        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .back-link:hover {
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .policy-cards {
                grid-template-columns: 1fr;
            }

            .policy-details {
                grid-template-columns: 1fr;
            }

            .detail-item.full-width {
                grid-column: span 1;
            }

            .policies-header {
                padding: 20px;
            }

            .policies-body {
                padding: 16px;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('leave.requests.index') }}">My Leave</a>
        @endslot
        @slot('title')
            Leave Policies
        @endslot
    @endcomponent

    <div class="policies-container">
        <div class="policies-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3>Leave Policies</h3>
                    <p>View all leave types and their entitlements for this year</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="year-badge">
                        <i class="fas fa-calendar-alt me-1"></i> Leave Year {{ $currentYear }}
                    </span>
                </div>
            </div>
        </div>
        <div class="policies-body">
            <div class="help-text">
                <div class="help-title">Understanding Leave Policies</div>
                <div class="help-content">
                    Each leave type has specific entitlements and rules. Review the policies below to understand
                    your leave benefits, including annual entitlement, carry-over rules, and any restrictions.
                </div>
            </div>

            @if($leaveTypesWithPolicies->count() > 0)
                <div class="policy-cards">
                    @foreach($leaveTypesWithPolicies as $item)
                        @php
                            $leaveType = $item['leave_type'];
                            $policy = $item['policy'];
                            $leaveTypeColor = $leaveType->color ?? '#6b7280';
                        @endphp
                        <div class="policy-card">
                            <div class="policy-card-header">
                                <div class="leave-type-indicator" style="background-color: {{ $leaveTypeColor }};"></div>
                                <div class="leave-type-info">
                                    <h5>{{ $leaveType->name }}</h5>
                                    <span class="code">{{ $leaveType->code }}</span>
                                </div>
                                <div class="leave-type-badges">
                                    @if($leaveType->is_paid)
                                        <span class="type-badge badge-paid">Paid</span>
                                    @else
                                        <span class="type-badge badge-unpaid">Unpaid</span>
                                    @endif
                                    @if($leaveType->gender_restriction)
                                        <span class="type-badge badge-gender">{{ ucfirst($leaveType->gender_restriction) }} Only</span>
                                    @endif
                                </div>
                            </div>
                            <div class="policy-card-body">
                                @if($policy)
                                    <div class="policy-details">
                                        <div class="detail-item">
                                            <div class="label">Annual Entitlement</div>
                                            <div class="value highlight">{{ number_format($leaveType->default_entitlement, 1) }} days</div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="label">Balance Mode</div>
                                            <div class="value">{{ ucfirst($policy->balance_mode) }}</div>
                                        </div>
                                        @if($policy->balance_mode === 'accrual')
                                            <div class="detail-item">
                                                <div class="label">Accrual Rate</div>
                                                <div class="value">{{ number_format($policy->accrual_rate, 2) }} days/month</div>
                                            </div>
                                        @endif
                                        <div class="detail-item">
                                            <div class="label">Carry-Over Mode</div>
                                            <div class="value">{{ ucfirst(str_replace('_', ' ', $policy->carry_over_mode)) }}</div>
                                        </div>
                                        @if($policy->carry_over_mode === 'limited')
                                            <div class="detail-item">
                                                <div class="label">Carry-Over Limit</div>
                                                <div class="value">{{ number_format($policy->carry_over_limit, 1) }} days max</div>
                                            </div>
                                        @endif
                                        @if($policy->carry_over_mode !== 'none' && $policy->carry_over_expiry_months)
                                            <div class="detail-item">
                                                <div class="label">Carry-Over Expiry</div>
                                                <div class="value">{{ $policy->carry_over_expiry_months }} months</div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="rules-section">
                                        <h6>Rules & Restrictions</h6>
                                        <div class="rules-list">
                                            @if($leaveType->allow_half_day)
                                                <span class="rule-tag positive">
                                                    <i class="fas fa-check-circle"></i> Half-day allowed
                                                </span>
                                            @else
                                                <span class="rule-tag negative">
                                                    <i class="fas fa-times-circle"></i> Full days only
                                                </span>
                                            @endif

                                            @if($leaveType->allow_negative_balance)
                                                <span class="rule-tag neutral">
                                                    <i class="fas fa-info-circle"></i> Negative balance allowed
                                                </span>
                                            @else
                                                <span class="rule-tag negative">
                                                    <i class="fas fa-times-circle"></i> No overdraft
                                                </span>
                                            @endif

                                            @if($leaveType->requires_attachment)
                                                <span class="rule-tag neutral">
                                                    <i class="fas fa-paperclip"></i>
                                                    Attachment required
                                                    @if($leaveType->attachment_required_after_days)
                                                        after {{ $leaveType->attachment_required_after_days }} days
                                                    @endif
                                                </span>
                                            @endif

                                            @if($leaveType->min_notice_days)
                                                <span class="rule-tag neutral">
                                                    <i class="fas fa-clock"></i> {{ $leaveType->min_notice_days }} days notice required
                                                </span>
                                            @endif

                                            @if($leaveType->max_consecutive_days)
                                                <span class="rule-tag neutral">
                                                    <i class="fas fa-calendar-week"></i> Max {{ $leaveType->max_consecutive_days }} consecutive days
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <div class="no-policy">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <p>No policy configured for {{ $currentYear }}.</p>
                                    </div>
                                @endif

                                @if($leaveType->description)
                                    <div class="leave-description">
                                        <p><strong>Note:</strong> {{ $leaveType->description }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-book"></i>
                    <h4>No Leave Types Available</h4>
                    <p>There are no active leave types configured. Please contact HR for assistance.</p>
                </div>
            @endif
        </div>
    </div>
@endsection
