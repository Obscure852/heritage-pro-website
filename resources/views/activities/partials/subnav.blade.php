<div class="subnav-links">
    <a href="{{ route('activities.show', $activity) }}" class="btn {{ ($current ?? '') === 'overview' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-layer-group me-1"></i> Overview
    </a>
    @can('manageStaff', $activity)
        <a href="{{ route('activities.staff.index', $activity) }}" class="btn {{ ($current ?? '') === 'staff' ? 'btn-primary' : 'btn-light' }}">
            <i class="fas fa-users-cog me-1"></i> Manage Staff
        </a>
    @endcan
    @can('manageEligibility', $activity)
        <a href="{{ route('activities.eligibility.edit', $activity) }}" class="btn {{ ($current ?? '') === 'eligibility' ? 'btn-primary' : 'btn-light' }}">
            <i class="fas fa-filter me-1"></i> Eligibility Rules
        </a>
    @endcan
    <a href="{{ route('activities.roster.index', $activity) }}" class="btn {{ ($current ?? '') === 'roster' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-user-check me-1"></i> Roster
    </a>
    <a href="{{ route('activities.schedules.index', $activity) }}" class="btn {{ ($current ?? '') === 'schedules' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-calendar-alt me-1"></i> Schedules & Attendance
    </a>
    <a href="{{ route('activities.events.index', $activity) }}" class="btn {{ ($current ?? '') === 'events' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-trophy me-1"></i> Events & Results
    </a>
    <a href="{{ route('activities.fees.index', $activity) }}" class="btn {{ ($current ?? '') === 'fees' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-file-invoice-dollar me-1"></i> Billing & Charges
    </a>
    <a href="{{ route('activities.reports.index', ['activity_id' => $activity->id]) }}"
        class="btn {{ ($current ?? '') === 'reports' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-chart-line me-1"></i> Reports
    </a>
</div>
