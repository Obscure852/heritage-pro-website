@php
    $selectedSeries = isset($series) && $series instanceof \App\Models\Invigilation\InvigilationSeries
        ? $series
        : null;

    $query = [];

    if ($selectedSeries) {
        $query['series_id'] = (int) $selectedSeries->id;
    }

    $managerUrl = $selectedSeries
        ? route('invigilation.show', $selectedSeries)
        : route('invigilation.index');
@endphp

<div class="subnav-links module-nav-links">
    <a href="{{ $managerUrl }}" class="btn {{ ($current ?? '') === 'manager' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-clipboard-list me-1"></i> Series Manager
    </a>
    <a href="{{ route('invigilation.reports.daily.index', $query) }}" class="btn {{ ($current ?? '') === 'daily' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-calendar-day me-1"></i> Daily Roster
    </a>
    <a href="{{ route('invigilation.reports.teacher.index', $query) }}" class="btn {{ ($current ?? '') === 'teacher' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-user-check me-1"></i> Teacher Duties
    </a>
    <a href="{{ route('invigilation.reports.room.index', $query) }}" class="btn {{ ($current ?? '') === 'room' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-door-open me-1"></i> Room Roster
    </a>
    <a href="{{ route('invigilation.reports.conflicts.index', $query) }}" class="btn {{ ($current ?? '') === 'conflicts' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-exclamation-triangle me-1"></i> Conflict Report
    </a>
    @can('manage-invigilation')
        <a href="{{ route('invigilation.settings.index') }}" class="btn {{ ($current ?? '') === 'settings' ? 'btn-primary' : 'btn-light' }}">
            <i class="fas fa-sliders-h me-1"></i> Settings
        </a>
    @endcan
</div>
