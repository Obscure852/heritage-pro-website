@extends('layouts.master')

@section('title')
    Activity Reports
@endsection

@section('css')
    @include('activities.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('activities.index') }}">Activities</a>
        @endslot
        @slot('title')
            Activity Reports
        @endslot
    @endcomponent

    @include('activities.partials.alerts')

    @php
        $reportExportFilters = array_filter($filters, fn ($value) => !is_null($value) && $value !== '');
    @endphp

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Activity Reports</h1>
                <p class="page-subtitle">Review roster, attendance, result, house, and billing output across the selected term, then export the filtered report when needed.</p>
            </div>
            <div class="activities-actions">
                <a href="{{ route('activities.reports.export', $reportExportFilters) }}" class="btn btn-light border">
                    <i class="fas fa-file-export"></i> Export Report
                </a>
            </div>
        </div>

        @include('activities.partials.module-nav', [
            'current' => 'reports',
            'activityId' => $filters['activity_id'] ?? null,
        ])

        <div class="help-text">
            <div class="help-title">Reporting Scope</div>
            <div class="help-content">
                @if ($selectedTerm)
                    The report is scoped to Term {{ $selectedTerm->term }} - {{ $selectedTerm->year }}.
                @else
                    No active term is selected, so the report is using the current session context.
                @endif
                Apply filters to focus on one activity or one operational slice before exporting.
            </div>
        </div>

        @if ($selectedActivity)
            <div class="info-note">
                <div class="help-title">Filtered Activity</div>
                <div class="help-content">
                    You are currently reviewing report output for <strong>{{ $selectedActivity->name }}</strong> ({{ $selectedActivity->code }}).
                </div>
            </div>
        @endif

        <div class="card-shell mb-4">
            <div class="card-body p-4">
                <form action="{{ route('activities.reports.index') }}" method="GET" class="controls">
                    <div class="report-filter-grid">
                        <div class="form-group">
                            <label class="form-label" for="report-search">Search</label>
                            <input type="text" class="form-control" id="report-search" name="search"
                                value="{{ $filters['search'] ?? '' }}"
                                placeholder="Search by activity name, code, venue, or description">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="report-activity">Activity</label>
                            <select class="form-select" id="report-activity" name="activity_id">
                                <option value="">All activities</option>
                                @foreach ($activityOptions as $activityOption)
                                    <option value="{{ $activityOption->id }}"
                                        {{ (int) ($filters['activity_id'] ?? 0) === (int) $activityOption->id ? 'selected' : '' }}>
                                        {{ $activityOption->name }} | {{ $activityOption->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="report-status">Status</label>
                            <select class="form-select" id="report-status" name="status">
                                <option value="">All statuses</option>
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ ($filters['status'] ?? null) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="report-category">Category</label>
                            <select class="form-select" id="report-category" name="category">
                                <option value="">All categories</option>
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}" {{ ($filters['category'] ?? null) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="report-delivery">Delivery</label>
                            <select class="form-select" id="report-delivery" name="delivery_mode">
                                <option value="">All delivery modes</option>
                                @foreach ($deliveryModes as $key => $label)
                                    <option value="{{ $key }}" {{ ($filters['delivery_mode'] ?? null) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="filter-actions justify-content-end mt-3">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('activities.reports.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="roster-summary-grid">
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Activities</div>
                    <div class="roster-summary-value">{{ $summary['activity_count'] }}</div>
                    <div class="billing-ledger-note">{{ $summary['active_count'] }} active | {{ $summary['draft_count'] }} draft</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Roster</div>
                    <div class="roster-summary-value">{{ $summary['active_roster_total'] }}</div>
                    <div class="billing-ledger-note">{{ $summary['historical_roster_total'] }} historical</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Sessions & Attendance</div>
                    <div class="roster-summary-value">{{ $summary['session_total'] }}</div>
                    <div class="billing-ledger-note">{{ $summary['attendance_marked_total'] }} attendance rows marked</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Events & Results</div>
                    <div class="roster-summary-value">{{ $summary['event_total'] }}</div>
                    <div class="billing-ledger-note">{{ $summary['result_total'] }} result rows | {{ $summary['award_total'] }} awards</div>
                </div>
            </div>
        </div>

        <div class="roster-summary-grid">
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Points</div>
                    <div class="roster-summary-value">{{ $summary['points_total'] }}</div>
                    <div class="billing-ledger-note">Across house-linked and student result outputs</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Charges</div>
                    <div class="roster-summary-value">{{ $summary['charge_total'] }}</div>
                    <div class="billing-ledger-note">{{ format_currency($summary['charge_amount_total']) }} generated</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Pending / Blocked Charges</div>
                    <div class="roster-summary-value">{{ $summary['pending_charge_total'] }} / {{ $summary['blocked_charge_total'] }}</div>
                    <div class="billing-ledger-note">Needs invoice follow-up</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Outstanding Amount</div>
                    <div class="roster-summary-value">{{ format_currency($summary['outstanding_amount_total']) }}</div>
                    <div class="billing-ledger-note">Posted charges still carrying invoice balance</div>
                </div>
            </div>
        </div>

        <div class="section-stack">
            <div class="card-shell">
                <div class="card-body p-4">
                    <div class="management-header">
                        <div>
                            <h5 class="summary-card-title mb-0">Activity Performance Summary</h5>
                            <p class="management-subtitle">Each row combines staffing, roster, attendance, result, and billing signals for one activity.</p>
                        </div>
                    </div>

                    @if ($activityRows->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table align-middle report-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Activity</th>
                                        <th>Roster</th>
                                        <th>Attendance</th>
                                        <th>Events & Results</th>
                                        <th>Billing</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($activityRows as $row)
                                        <tr>
                                            <td>
                                                <div class="management-item-title">{{ $row['name'] }}</div>
                                                <div class="activity-meta-pills mt-2">
                                                    <span class="meta-pill meta-pill-code">
                                                        <i class="bx bx-hash"></i> {{ $row['code'] }}
                                                    </span>
                                                    <span class="meta-pill meta-pill-location">
                                                        <i class="bx bx-pulse"></i> {{ $row['status'] }}
                                                    </span>
                                                </div>
                                                <div class="management-item-notes mt-2">
                                                    {{ $row['category'] }} | {{ $row['delivery_mode'] }} | {{ $row['term_label'] }}
                                                    @if ($row['fee_type'])
                                                        | {{ $row['fee_type'] }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="report-metric">{{ $row['active_enrollments_count'] }} active</div>
                                                <div class="report-metric-note">{{ $row['historical_enrollments_count'] }} historical</div>
                                                <div class="report-metric-note">{{ $row['active_staff_assignments_count'] }} staff assigned</div>
                                            </td>
                                            <td>
                                                <div class="report-metric">{{ $row['attendance_marked_count'] }} rows</div>
                                                <div class="report-metric-note">{{ $row['attendance_present_count'] }} present | {{ $row['attendance_absent_count'] }} absent</div>
                                                <div class="report-metric-note">
                                                    Present rate:
                                                    {{ is_null($row['attendance_present_rate']) ? 'n/a' : number_format((float) $row['attendance_present_rate'], 1) . '%' }}
                                                </div>
                                                <div class="report-metric-note">{{ $row['completed_sessions_count'] }} / {{ $row['sessions_count'] }} sessions completed</div>
                                            </td>
                                            <td>
                                                <div class="report-metric">{{ $row['results_count'] }} results</div>
                                                <div class="report-metric-note">{{ $row['awards_count'] }} awards | {{ $row['points_total'] }} pts</div>
                                                <div class="report-metric-note">{{ $row['house_results_count'] }} house-linked rows</div>
                                                <div class="report-metric-note">{{ $row['completed_events_count'] }} / {{ $row['events_count'] }} events completed</div>
                                            </td>
                                            <td>
                                                <div class="report-metric">{{ format_currency($row['charge_total_amount']) }}</div>
                                                <div class="report-metric-note">{{ $row['charge_posted_count'] }} posted | {{ $row['charge_pending_count'] }} pending</div>
                                                <div class="report-metric-note">{{ $row['charge_blocked_count'] }} blocked</div>
                                                <div class="report-metric-note">{{ format_currency($row['charge_outstanding_amount']) }} outstanding</div>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('activities.show', $row['id']) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bx bx-show-alt"></i> Open
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <div><i class="fas fa-chart-line empty-state-icon"></i></div>
                            <p class="mb-0">No activity report rows matched the selected filters.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="management-grid report-secondary-grid">
                <div class="card-shell">
                    <div class="card-body p-4">
                        <div class="management-header">
                            <div>
                                <h5 class="summary-card-title mb-0">House Performance</h5>
                                <p class="management-subtitle">House-linked result rows aggregated across the filtered activity set.</p>
                            </div>
                        </div>

                        @if ($houseOutputs->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>House</th>
                                            <th>Results</th>
                                            <th>Awards</th>
                                            <th>Points</th>
                                            <th>Events</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($houseOutputs as $houseOutput)
                                            <tr>
                                                <td>{{ $houseOutput->house_name }}</td>
                                                <td>{{ $houseOutput->results_count }}</td>
                                                <td>{{ $houseOutput->award_count }}</td>
                                                <td>{{ $houseOutput->points_total }}</td>
                                                <td>{{ $houseOutput->event_count }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="summary-empty mb-0">No house-linked result output is available for the current filter set.</p>
                        @endif
                    </div>
                </div>

                <div class="card-shell">
                    <div class="card-body p-4">
                        <div class="management-header">
                            <div>
                                <h5 class="summary-card-title mb-0">Charge Exceptions</h5>
                                <p class="management-subtitle">Latest pending or blocked charges that still need invoice follow-up.</p>
                            </div>
                        </div>

                        @if ($chargeExceptions->isNotEmpty())
                            <div class="management-list">
                                @foreach ($chargeExceptions as $chargeException)
                                    <div class="management-item">
                                        <div class="management-item-title">
                                            {{ $chargeException->student?->full_name ?: 'Unknown student' }}
                                        </div>
                                        <div class="management-item-meta">
                                            <span class="summary-chip pill-muted">{{ $chargeException->activity?->name ?: 'Deleted activity' }}</span>
                                            <span class="summary-chip billing-status-chip status-{{ $chargeException->billing_status }}">
                                                {{ \App\Models\Activities\ActivityFeeCharge::statuses()[$chargeException->billing_status] ?? ucfirst($chargeException->billing_status) }}
                                            </span>
                                            <span class="summary-chip pill-primary">{{ format_currency($chargeException->amount) }}</span>
                                        </div>
                                        <div class="management-item-notes">
                                            {{ $chargeException->invoice?->invoice_number ?: 'No active invoice linked yet' }}
                                            @if ($chargeException->invoice)
                                                | Balance {{ format_currency($chargeException->invoice->balance) }}
                                            @endif
                                            @if ($chargeException->notes)
                                                | {{ $chargeException->notes }}
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="summary-empty mb-0">No pending or blocked charge exceptions are currently open.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
