@extends('layouts.master')
@section('title')
    Activities
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
            Activities Manager
        @endslot
    @endcomponent

    @include('activities.partials.alerts')

    @php
        $hasActiveFilters =
            request()->filled('search') ||
            request()->filled('status') ||
            request()->filled('category') ||
            request()->filled('delivery_mode');
    @endphp

    <div class="activities-container">
        <div class="activities-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Activities Manager</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        @if ($selectedTerm)
                            Staff-managed activities for Term {{ $selectedTerm->term }} - {{ $selectedTerm->year }}.
                        @else
                            No selected term was found. Activities can still be reviewed, but new records require an active
                            term context.
                        @endif
                    </p>
                </div>
                <div class="col-md-6">
                    @if ($activities->total() > 0)
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $activities->total() }}</h4>
                                    <small class="opacity-75">Activities</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ $activities->count() }}</h4>
                                    <small class="opacity-75">This Page</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h4 class="mb-0 fw-bold text-white">{{ count($categories) }}</h4>
                                    <small class="opacity-75">Categories</small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="activities-body">
            <div class="help-text">
                <div class="help-title">Module Overview</div>
                <div class="help-content">
                    Use this page to review the activities catalog for the selected term, filter records quickly, and open each activity for staffing, rosters, schedules, attendance, events, and results.
                </div>
            </div>

            @include('activities.partials.module-nav', ['current' => 'catalog'])

            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <div class="controls">
                        <form action="{{ route('activities.index') }}" method="GET" class="row g-2 align-items-center">
                            <div class="col-lg-4 col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input class="form-control" id="search" name="search" type="text"
                                        value="{{ request('search') }}" placeholder="Search by name, code, or location">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    @foreach ($statuses as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-6">
                                <select class="form-select" id="delivery_mode" name="delivery_mode">
                                    <option value="">All Delivery</option>
                                    @foreach ($deliveryModes as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ request('delivery_mode') === $key ? 'selected' : '' }}>{{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-12">
                                <div class="filter-actions">
                                    <button class="btn btn-primary w-100" type="submit">
                                        <i class="fas fa-filter me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('activities.index') }}" class="btn btn-light w-100">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                        @can('manage-activities')
                            <a href="{{ route('activities.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> New Activity
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="card-shell">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>Type</th>
                                    <th>Term</th>
                                    <th>Counts</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activities as $activity)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $activity->name }}</div>
                                            <div class="activity-meta-pills">
                                                <span class="meta-pill meta-pill-code">
                                                    <i class="bx bx-hash"></i>
                                                    {{ $activity->code }}
                                                </span>
                                                <span class="meta-pill meta-pill-location">
                                                    <i class="bx bx-map"></i>
                                                    {{ $activity->default_location ?: 'No venue set' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="small">
                                            {{ \App\Models\Activities\Activity::categories()[$activity->category] ?? $activity->category }}<br>
                                            {{ \App\Models\Activities\Activity::deliveryModes()[$activity->delivery_mode] ?? $activity->delivery_mode }}
                                        </td>
                                        <td class="small">
                                            Term {{ $activity->term?->term ?? 'N/A' }}<br>
                                            {{ $activity->year }}
                                        </td>
                                        <td class="small">
                                            {{ $activity->active_staff_assignments_count }} staff<br>
                                            {{ $activity->enrollments_count }} students
                                        </td>
                                        <td>
                                            <span class="status-badge status-{{ $activity->status }}">
                                                {{ $statuses[$activity->status] ?? ucfirst($activity->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="action-buttons">
                                                <a href="{{ route('activities.show', $activity) }}"
                                                    class="btn btn-sm btn-outline-info" title="View Activity">
                                                    <i class="bx bx-show-alt"></i>
                                                </a>
                                                @can('manage-activities')
                                                    <a href="{{ route('activities.edit', $activity) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Edit Activity">
                                                        <i class="bx bx-edit-alt"></i>
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                @if ($hasActiveFilters)
                                                    <a href="{{ route('activities.index') }}" class="btn btn-primary">
                                                        <i class="fas fa-undo me-1"></i> Reset Filters
                                                    </a>
                                                @endif
                                                <div>
                                                    <i class="fas fa-layer-group empty-state-icon"></i>
                                                </div>
                                                <p class="mb-0">
                                                    {{ $hasActiveFilters ? 'No activities found for the selected filters.' : 'No activities have been created for the selected term yet.' }}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $activities->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
