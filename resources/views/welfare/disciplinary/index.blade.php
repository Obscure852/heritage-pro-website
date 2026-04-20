@extends('layouts.master')

@section('title')
    Disciplinary Records
@endsection

@section('css')
    <style>
        /* Page Container */
        .disciplinary-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .disciplinary-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .disciplinary-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #f59e0b;
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
            Disciplinary Records
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-all me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="disciplinary-container">
        <div class="disciplinary-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Disciplinary Records</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Track and manage student disciplinary incidents</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalCount = $records->total();
                        $reportedCount = $records->where('status', 'reported')->count();
                        $resolvedCount = $records->where('status', 'resolved')->count();
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
                                <h4 class="mb-0 fw-bold text-white">{{ $reportedCount }}</h4>
                                <small class="opacity-75">Reported</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $resolvedCount }}</h4>
                                <small class="opacity-75">Resolved</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="disciplinary-body">
            <div class="help-text">
                <div class="help-title">Disciplinary Directory</div>
                <div class="help-content">
                    Browse and manage all disciplinary records. Use the search and filters to find specific incidents.
                    Click on a student name to view full incident details.
                </div>
            </div>

            <!-- Filters and Actions -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <form method="GET" action="{{ route('welfare.disciplinary.index') }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Search by student name..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="reported" {{ request('status') === 'reported' ? 'selected' : '' }}>Reported</option>
                                    <option value="investigating" {{ request('status') === 'investigating' ? 'selected' : '' }}>Investigating</option>
                                    <option value="sanctioned" {{ request('status') === 'sanctioned' ? 'selected' : '' }}>Sanctioned</option>
                                    <option value="appealed" {{ request('status') === 'appealed' ? 'selected' : '' }}>Appealed</option>
                                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select name="severity" class="form-select">
                                    <option value="">All Severity</option>
                                    <option value="minor" {{ request('severity') === 'minor' ? 'selected' : '' }}>Minor</option>
                                    <option value="moderate" {{ request('severity') === 'moderate' ? 'selected' : '' }}>Moderate</option>
                                    <option value="major" {{ request('severity') === 'major' ? 'selected' : '' }}>Major</option>
                                    <option value="severe" {{ request('severity') === 'severe' ? 'selected' : '' }}>Severe</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach ($categories ?? [] as $category)
                                        <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-1 col-md-6 col-sm-6">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                            <div class="col-lg-2 col-md-6 col-sm-6">
                                <a href="{{ route('welfare.disciplinary.index') }}" class="btn btn-light w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <a href="{{ route('welfare.disciplinary.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Incident
                    </a>
                </div>
            </div>

            <!-- Records Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Category</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                    <th>Sanction</th>
                                    <th>Reported By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($records as $record)
                                    <tr>
                                        <td>{{ $record->incident_date->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('welfare.disciplinary.edit', $record) }}">
                                                {{ $record->student->full_name ?? '-' }}
                                            </a>
                                        </td>
                                        <td>{{ $record->incidentType->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $severityColors = [
                                                    'minor' => 'secondary',
                                                    'moderate' => 'warning',
                                                    'major' => 'danger',
                                                    'severe' => 'dark',
                                                    1 => 'secondary',
                                                    2 => 'warning',
                                                    3 => 'danger',
                                                    4 => 'dark',
                                                ];
                                                $severity = $record->incidentType->severity ?? null;
                                            @endphp
                                            <span class="badge bg-{{ $severityColors[$severity] ?? 'secondary' }}">
                                                {{ ucfirst($severity ?? '-') }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'reported' => 'info',
                                                    'investigating' => 'warning',
                                                    'pending_action' => 'warning',
                                                    'action_in_progress' => 'danger',
                                                    'sanctioned' => 'danger',
                                                    'appealed' => 'primary',
                                                    'resolved' => 'success',
                                                ];
                                            @endphp
                                            <span
                                                class="badge bg-{{ $statusColors[$record->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$record->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ $record->action->name ?? '-' }}</td>
                                        <td>{{ $record->reportedBy->full_name ?? '-' }}</td>
                                        <td>
                                            <div class="action-buttons">
                                                @if (in_array($record->status, ['reported', 'investigating']))
                                                    <a href="{{ route('welfare.disciplinary.edit', $record) }}"
                                                        class="btn btn-outline-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle" style="font-size: 48px; opacity: 0.5;"></i>
                                            <p class="mb-0 mt-2">No disciplinary records found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($records->hasPages())
                        <div class="mt-3">
                            {{ $records->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
