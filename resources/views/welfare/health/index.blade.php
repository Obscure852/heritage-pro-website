@extends('layouts.master')

@section('title')
    Health Incidents
@endsection

@section('css')
    <style>
        /* Page Container */
        .health-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .health-header {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .health-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #06b6d4;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Cards */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .card-body {
            padding: 20px;
        }

        /* Form Elements */
        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        /* Buttons */
        .btn {
            padding: 10px 16px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-light {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background: #e9ecef;
            color: #495057;
        }

        /* Input Group */
        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #d1d5db;
            border-right: none;
            border-radius: 3px 0 0 3px !important;
            color: #6b7280;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 3px 3px 0 !important;
        }

        .input-group .form-control:focus {
            border-color: #3b82f6;
            box-shadow: none;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px !important;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 16px;
        }

        /* Table Styling */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        /* Badges */
        .badge {
            padding: 4px 8px;
            border-radius: 3px !important;
            font-weight: 500;
            font-size: 12px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('welfare.dashboard') }}">Welfare</a>
        @endslot
        @slot('title')
            Health Incidents
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-all me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="health-container">
        <div class="health-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Health Incidents</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Track and manage student health incidents</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalCount = $incidents->total();
                        $minorCount = $incidents->where('incidentType.severity', 'minor')->count();
                        $emergencyCount = $incidents->where('incidentType.severity', 'emergency')->count();
                    @endphp
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $totalCount }}</h4>
                                <small class="opacity-75">Total</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $minorCount }}</h4>
                                <small class="opacity-75">Minor</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $emergencyCount }}</h4>
                                <small class="opacity-75">Emergency</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="health-body">
            <div class="help-text">
                <div class="help-title">Health Incidents Directory</div>
                <div class="help-content">
                    Browse and manage all health incidents. Use the search and filters to find specific incidents.
                    Track illnesses, injuries, and medical emergencies.
                </div>
            </div>

            <!-- Filters and Actions -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <form method="GET" action="{{ route('welfare.health.index') }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Search by student name..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select name="incident_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="illness" {{ request('incident_type') === 'illness' ? 'selected' : '' }}>Illness</option>
                                    <option value="injury" {{ request('incident_type') === 'injury' ? 'selected' : '' }}>Injury</option>
                                    <option value="accident" {{ request('incident_type') === 'accident' ? 'selected' : '' }}>Accident</option>
                                    <option value="medication" {{ request('incident_type') === 'medication' ? 'selected' : '' }}>Medication</option>
                                    <option value="allergic_reaction" {{ request('incident_type') === 'allergic_reaction' ? 'selected' : '' }}>Allergic Reaction</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select name="severity" class="form-select">
                                    <option value="">All Severity</option>
                                    <option value="minor" {{ request('severity') === 'minor' ? 'selected' : '' }}>Minor</option>
                                    <option value="moderate" {{ request('severity') === 'moderate' ? 'selected' : '' }}>Moderate</option>
                                    <option value="serious" {{ request('severity') === 'serious' ? 'selected' : '' }}>Serious</option>
                                    <option value="emergency" {{ request('severity') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                </select>
                            </div>
                            <div class="col-lg-1 col-md-6 col-sm-6">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                            <div class="col-lg-2 col-md-6 col-sm-6">
                                <a href="{{ route('welfare.health.index') }}" class="btn btn-light w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <a href="{{ route('welfare.health.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Log Incident
                    </a>
                </div>
            </div>

            <!-- Incidents Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Student</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Severity</th>
                                    <th>Action Taken</th>
                                    <th>Recorded By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incidents as $incident)
                                    @php
                                        $severity = $incident->incidentType->severity ?? null;
                                        $severityColors = [
                                            'minor' => 'success',
                                            'moderate' => 'warning',
                                            'serious' => 'danger',
                                            'emergency' => 'dark',
                                        ];
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $incident->incident_date ? \Carbon\Carbon::parse($incident->incident_date)->format('d M Y') : '-' }}</strong>
                                            @if ($incident->incident_time)
                                                <small class="d-block text-muted">{{ $incident->incident_time }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('welfare.health.edit', $incident) }}">
                                                {{ $incident->student->full_name ?? '-' }}
                                            </a>
                                            <small
                                                class="d-block text-muted">{{ $incident->student->currentGrade->name ?? '' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">
                                                {{ $incident->incidentType->name ?? '-' }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($incident->description, 40) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $severityColors[$severity] ?? 'secondary' }}">
                                                {{ ucfirst($severity ?? '-') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($incident->sent_home)
                                                <span class="badge bg-warning-subtle text-warning">Sent Home</span>
                                            @elseif($incident->called_ambulance)
                                                <span class="badge bg-danger-subtle text-danger">Ambulance</span>
                                            @else
                                                <span class="text-muted">Treated on site</span>
                                            @endif
                                        </td>
                                        <td>{{ $incident->reportedBy->full_name ?? '-' }}</td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('welfare.health.edit', $incident) }}" class="btn btn-outline-info"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-plus-square" style="font-size: 48px; opacity: 0.5;"></i>
                                            <p class="mb-0 mt-2">No health incidents found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($incidents->hasPages())
                        <div class="mt-3">
                            {{ $incidents->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
