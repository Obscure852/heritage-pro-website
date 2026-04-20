{{-- Audit Trail Tab Content --}}
<div class="help-text">
    <div class="help-title">Fee Audit Trail</div>
    <div class="help-content">
        View a complete audit history of all fee-related transactions including invoices, payments, refunds, discounts,
        carryovers, and clearance overrides. Each entry shows who made the change, when, and what was modified.
    </div>
</div>

{{-- Filters Row --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label">Search</label>
        <input type="text"
            class="form-control"
            id="auditSearchInput"
            placeholder="Search notes, IP, or ID...">
    </div>
    <div class="col-md-2">
        <label class="form-label">Date From</label>
        <input type="date"
            class="form-control"
            id="auditDateFrom">
    </div>
    <div class="col-md-2">
        <label class="form-label">Date To</label>
        <input type="date"
            class="form-control"
            id="auditDateTo">
    </div>
    <div class="col-md-2">
        <label class="form-label">Action</label>
        <select class="form-select" id="auditActionFilter">
            <option value="">All Actions</option>
            @foreach($auditActions as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">User</label>
        <select class="form-select" id="auditUserFilter">
            <option value="">All Users</option>
            @foreach($feeUsers as $user)
                <option value="{{ $user->id }}">{{ $user->full_name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Type</label>
        <select class="form-select" id="auditTypeFilter">
            <option value="">All Types</option>
            @foreach($auditableTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- Filter Buttons --}}
<div class="d-flex gap-2 mb-4">
    <button type="button" class="btn btn-primary btn-sm" id="auditApplyFilters">
        <i class="fas fa-filter me-1"></i> Apply Filters
    </button>
    <button type="button" class="btn btn-light btn-sm" id="auditResetFilters">
        <i class="fas fa-undo me-1"></i> Reset
    </button>
</div>

{{-- Loading Spinner --}}
<div id="auditLoadingSpinner" class="text-center py-5" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="text-muted mt-2 mb-0">Loading audit logs...</p>
</div>

{{-- Results Container --}}
<div id="auditResultsContainer" style="display: none;">
    {{-- Summary Row --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <small class="text-muted" id="auditResultsSummary">Showing 0 records</small>
    </div>

    {{-- Results Table --}}
    <div class="table-responsive">
        <table class="table table-hover" id="auditLogsTable">
            <thead>
                <tr>
                    <th style="width: 140px;">Date/Time</th>
                    <th style="width: 120px;">User</th>
                    <th style="width: 90px;">Action</th>
                    <th style="width: 110px;">Type</th>
                    <th>Details</th>
                    <th style="width: 100px;">IP Address</th>
                    <th style="width: 50px;"></th>
                </tr>
            </thead>
            <tbody id="auditLogsBody">
                {{-- Rows will be populated via JavaScript --}}
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div id="auditPaginationContainer" class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted" id="auditPaginationInfo">Page 1 of 1</small>
        <div class="btn-group" id="auditPaginationButtons">
            {{-- Pagination buttons will be populated via JavaScript --}}
        </div>
    </div>
</div>

{{-- Empty State --}}
<div id="auditEmptyState" class="text-center py-5" style="display: none;">
    <i class="fas fa-history fa-3x text-muted mb-3"></i>
    <h5 class="text-muted">No Audit Logs Found</h5>
    <p class="text-muted mb-0">No audit records match your current filters.</p>
</div>

{{-- Initial State (before loading) --}}
<div id="auditInitialState" class="text-center py-5">
    <i class="fas fa-history fa-3x text-muted mb-3"></i>
    <h5 class="text-muted">Click the Audit Trail tab to load logs</h5>
    <p class="text-muted mb-0">Audit logs will be loaded when you first view this tab.</p>
</div>
