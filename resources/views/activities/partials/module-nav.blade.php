@php
    $query = [];

    if (!empty($activityId)) {
        $query['activity_id'] = (int) $activityId;
    }
@endphp

<div class="subnav-links module-nav-links">
    <a href="{{ route('activities.index') }}" class="btn {{ ($current ?? '') === 'catalog' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-layer-group me-1"></i> Catalog
    </a>
    <a href="{{ route('activities.reports.index', $query) }}"
        class="btn {{ ($current ?? '') === 'reports' ? 'btn-primary' : 'btn-light' }}">
        <i class="fas fa-chart-line me-1"></i> Reports
    </a>
    @can('manage-activity-settings')
        <a href="{{ route('activities.settings.index') }}"
            class="btn {{ ($current ?? '') === 'settings' ? 'btn-primary' : 'btn-light' }}">
            <i class="fas fa-sliders-h me-1"></i> Settings
        </a>
    @endcan
</div>
