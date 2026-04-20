@extends('layouts.master')

@section('title')
    Activity Billing
@endsection

@section('css')
    @include('activities.partials.theme')
    <style>
        .billing-status-chip.status-posted {
            background: rgba(5, 150, 105, 0.12);
            color: #047857;
        }

        .billing-status-chip.status-pending {
            background: rgba(59, 130, 246, 0.12);
            color: #1d4ed8;
        }

        .billing-status-chip.status-blocked {
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
        }

        .billing-status-chip.status-cancelled {
            background: rgba(107, 114, 128, 0.14);
            color: #4b5563;
        }

        .billing-amount {
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }

        .billing-ledger-note {
            font-size: 12px;
            color: #6b7280;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('activities.index') }}">Activities</a>
        @endslot
        @slot('title')
            {{ $activity->name }} Billing
        @endslot
    @endcomponent

    @include('activities.partials.alerts')

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Billing and Charges</h1>
                <p class="page-subtitle">Generate roster-linked activity charges, post them into annual invoices, and keep charge state visible for this activity.</p>
            </div>
        </div>

        @include('activities.partials.subnav', ['activity' => $activity, 'current' => 'fees'])

        <div class="help-text">
            <div class="help-title">Billing Workflow</div>
            <div class="help-content">
                Charges created here stay linked to the activity roster. When an active annual invoice already exists, the charge posts immediately into that invoice. If the student does not yet have an active annual invoice, the charge stays pending, and cancelled annual invoices are flagged as blocked until a valid invoice is available.
            </div>
        </div>

        <div class="roster-summary-grid">
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Fee Link</div>
                    <div class="detail-value">{{ $activity->feeType?->name ?: 'Not configured' }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Default Charge</div>
                    <div class="detail-value">
                        @if ($activity->default_fee_amount)
                            {{ format_currency($activity->default_fee_amount) }}
                        @else
                            Not set
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Posted Charges</div>
                    <div class="roster-summary-value">{{ $billingSummary['posted_count'] }}</div>
                    <div class="billing-ledger-note">{{ format_currency($billingSummary['posted_amount']) }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Pending / Blocked</div>
                    <div class="roster-summary-value">{{ $billingSummary['pending_count'] }} / {{ $billingSummary['blocked_count'] }}</div>
                    <div class="billing-ledger-note">{{ format_currency($billingSummary['pending_amount'] + $billingSummary['blocked_amount']) }}</div>
                </div>
            </div>
        </div>

        <div class="management-grid">
            <div class="section-stack">
                <div class="card-shell">
                    <div class="card-body p-4">
                        <div class="management-header">
                            <div>
                                <h5 class="summary-card-title mb-0">Create Activity Charge</h5>
                                <p class="management-subtitle">Charges can only be created against students with an active roster entry in this activity.</p>
                            </div>
                        </div>

                        @can('manageFees', $activity)
                            @if (!$activity->feeType)
                                <div class="help-text mb-0">
                                    <div class="help-title">Fee Link Required</div>
                                    <div class="help-content">
                                        Edit the activity first and assign an optional fee type before generating billing records from this page.
                                    </div>
                                </div>
                            @elseif ($activeEnrollments->isEmpty())
                                <p class="summary-empty mb-0">No active roster entries are available for charge generation yet.</p>
                            @else
                                <form action="{{ route('activities.fees.store', $activity) }}"
                                    method="POST"
                                    id="activity-fee-form"
                                    class="needs-validation"
                                    novalidate
                                    data-activity-form>
                                    @csrf

                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label class="form-label" for="charge-student-id">Student <span class="text-danger">*</span></label>
                                            <select class="form-select @error('student_id') is-invalid @enderror" id="charge-student-id" name="student_id" required>
                                                <option value="">Select enrolled student</option>
                                                @foreach ($activeEnrollments as $enrollment)
                                                    <option value="{{ $enrollment->student_id }}" {{ (int) old('student_id') === (int) $enrollment->student_id ? 'selected' : '' }}>
                                                        {{ $enrollment->student?->full_name ?: 'Unknown student' }}
                                                        @if ($enrollment->gradeSnapshot?->name)
                                                            | {{ $enrollment->gradeSnapshot->name }}
                                                        @endif
                                                        @if ($enrollment->klassSnapshot?->name)
                                                            | {{ $enrollment->klassSnapshot->name }}
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('student_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="charge-type">Charge Type <span class="text-danger">*</span></label>
                                            <select class="form-select @error('charge_type') is-invalid @enderror" id="charge-type" name="charge_type" required>
                                                <option value="">Select charge type</option>
                                                @foreach ($chargeTypes as $key => $label)
                                                    <option value="{{ $key }}" {{ old('charge_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('charge_type')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label" for="charge-amount">Amount <span class="text-danger">*</span></label>
                                            <input type="number"
                                                step="0.01"
                                                min="0.01"
                                                class="form-control @error('amount') is-invalid @enderror"
                                                id="charge-amount"
                                                name="amount"
                                                value="{{ old('amount', $activity->default_fee_amount ? number_format((float) $activity->default_fee_amount, 2, '.', '') : '') }}"
                                                placeholder="Enter the charge amount"
                                                required>
                                            @error('amount')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-grid mt-3">
                                        <div class="form-group">
                                            <label class="form-label" for="charge-event-id">Related Event</label>
                                            <select class="form-select @error('activity_event_id') is-invalid @enderror" id="charge-event-id" name="activity_event_id">
                                                <option value="">No specific event</option>
                                                @foreach ($events as $event)
                                                    <option value="{{ $event->id }}" {{ (int) old('activity_event_id') === (int) $event->id ? 'selected' : '' }}>
                                                        {{ $event->title }} | {{ \App\Models\Activities\ActivityEvent::statuses()[$event->status] ?? ucfirst($event->status) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('activity_event_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group grid-span-full">
                                            <label class="form-label" for="charge-notes">Notes</label>
                                            <input type="text"
                                                class="form-control @error('notes') is-invalid @enderror"
                                                id="charge-notes"
                                                name="notes"
                                                value="{{ old('notes') }}"
                                                placeholder="Add charge context, event notes, or billing instructions.">
                                            @error('notes')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary btn-loading">
                                            <span class="btn-text"><i class="fas fa-save"></i> Save Charge</span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                Saving...
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            @endif
                        @else
                            <div class="help-text mb-0">
                                <div class="help-title">Read Only Access</div>
                                <div class="help-content">
                                    You can review existing billing records for this activity, but only Activities Admin and Activities Edit roles can create or post charges.
                                </div>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="section-stack">
                <div class="card-shell">
                    <div class="card-body p-4">
                        <div class="management-header">
                            <div>
                                <h5 class="summary-card-title mb-0">Charge Ledger</h5>
                                <p class="management-subtitle">Each charge remains linked to the activity, the student, and the annual invoice outcome.</p>
                            </div>
                        </div>

                        @if ($charges->isNotEmpty())
                            <div class="management-list">
                                @foreach ($charges as $charge)
                                    <div class="management-item">
                                        <div class="management-item-header">
                                            <div>
                                                <div class="management-item-title">
                                                    {{ $charge->student?->full_name ?: 'Unknown student' }}
                                                    <span class="billing-amount ms-2">{{ format_currency($charge->amount) }}</span>
                                                </div>
                                                <div class="management-item-meta">
                                                    <span class="summary-chip billing-status-chip status-{{ $charge->billing_status }}">
                                                        {{ $chargeStatuses[$charge->billing_status] ?? ucfirst($charge->billing_status) }}
                                                    </span>
                                                    <span class="summary-chip pill-muted">
                                                        {{ $chargeTypes[$charge->charge_type] ?? ucfirst(str_replace('_', ' ', $charge->charge_type)) }}
                                                    </span>
                                                    @if ($charge->enrollment?->gradeSnapshot?->name)
                                                        <span class="summary-chip pill-muted">{{ $charge->enrollment->gradeSnapshot->name }}</span>
                                                    @endif
                                                    @if ($charge->enrollment?->klassSnapshot?->name)
                                                        <span class="summary-chip pill-muted">{{ $charge->enrollment->klassSnapshot->name }}</span>
                                                    @endif
                                                    @if ($charge->event?->title)
                                                        <span class="summary-chip pill-primary">{{ $charge->event->title }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            @can('manageFees', $activity)
                                                @if (in_array($charge->billing_status, [\App\Models\Activities\ActivityFeeCharge::STATUS_PENDING, \App\Models\Activities\ActivityFeeCharge::STATUS_BLOCKED], true))
                                                    <form action="{{ route('activities.fees.post', [$activity, $charge]) }}"
                                                        method="POST"
                                                        class="d-inline-flex"
                                                        data-activity-form>
                                                        @csrf
                                                        <button type="submit" class="btn btn-light btn-loading border">
                                                            <span class="btn-text"><i class="fas fa-link"></i> Post to Invoice</span>
                                                            <span class="btn-spinner d-none">
                                                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                                Saving...
                                                            </span>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </div>

                                        <div class="management-item-notes">
                                            Created {{ optional($charge->generated_at)->format('d M Y, H:i') ?: 'n/a' }}
                                            by {{ $charge->generatedBy?->full_name ?: 'Unknown operator' }}.
                                            @if ($charge->invoice)
                                                Linked invoice:
                                                @if ($canViewInvoices)
                                                    <a href="{{ route('fees.collection.invoices.show', $charge->invoice) }}">{{ $charge->invoice->invoice_number }}</a>
                                                @else
                                                    {{ $charge->invoice->invoice_number }}
                                                @endif
                                                ({{ ucfirst($charge->invoice->status) }}).
                                            @elseif ($charge->billing_status === \App\Models\Activities\ActivityFeeCharge::STATUS_PENDING)
                                                Waiting for an active annual invoice for {{ $charge->year }}.
                                            @elseif ($charge->billing_status === \App\Models\Activities\ActivityFeeCharge::STATUS_BLOCKED)
                                                Posting is blocked until a valid annual invoice is available.
                                            @endif
                                        </div>

                                        @if ($charge->notes)
                                            <div class="management-item-notes">{{ $charge->notes }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="summary-empty mb-0">No billing records have been created for this activity yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('activities.partials.form-script')
@endsection
