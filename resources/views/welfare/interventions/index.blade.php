@extends('layouts.master')

@section('title')
    Intervention Plans
@endsection

@section('css')
    <style>
        /* Page Container */
        .intervention-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .intervention-header {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .intervention-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #14b8a6;
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
            Intervention Plans
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-all me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-outline me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle-outline me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="intervention-container">
        <div class="intervention-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Intervention Plans</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage student intervention and support plans</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalCount = $plans->total();
                        $activeCount = $plans->where('status', 'active')->count();
                        $completedCount = $plans->where('status', 'completed')->count();
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
                                <h4 class="mb-0 fw-bold text-white">{{ $activeCount }}</h4>
                                <small class="opacity-75">Active</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $completedCount }}</h4>
                                <small class="opacity-75">Completed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="intervention-body">
            <div class="help-text">
                <div class="help-title">Intervention Plans Directory</div>
                <div class="help-content">
                    Browse and manage all intervention plans. Use the search and filters to find specific plans.
                    Track student progress and support strategies.
                </div>
            </div>

            <!-- Filters and Actions -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <form method="GET" action="{{ route('welfare.intervention-plans.index') }}">
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
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select name="intervention_type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="academic" {{ request('intervention_type') === 'academic' ? 'selected' : '' }}>Academic</option>
                                    <option value="behavioral" {{ request('intervention_type') === 'behavioral' ? 'selected' : '' }}>Behavioral</option>
                                    <option value="social" {{ request('intervention_type') === 'social' ? 'selected' : '' }}>Social</option>
                                    <option value="emotional" {{ request('intervention_type') === 'emotional' ? 'selected' : '' }}>Emotional</option>
                                    <option value="attendance" {{ request('intervention_type') === 'attendance' ? 'selected' : '' }}>Attendance</option>
                                    <option value="other" {{ request('intervention_type') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-lg-1 col-md-6 col-sm-6">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                            <div class="col-lg-2 col-md-6 col-sm-6">
                                <a href="{{ route('welfare.intervention-plans.index') }}" class="btn btn-light w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <a href="{{ route('welfare.intervention-plans.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Plan
                    </a>
                </div>
            </div>

            <!-- Plans Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Coordinator</th>
                                    <th>Status</th>
                                    <th>Next Review</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($plans as $plan)
                                    <tr>
                                        <td>
                                            <a href="{{ route('welfare.intervention-plans.edit', $plan) }}">
                                                {{ $plan->student->full_name ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{{ Str::limit($plan->title, 30) }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ ucfirst($plan->intervention_type) }}</span>
                                        </td>
                                        <td>{{ $plan->coordinator->full_name ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'active' => 'success',
                                                    'on_hold' => 'warning',
                                                    'completed' => 'primary',
                                                ];
                                            @endphp
                                            <span
                                                class="badge bg-{{ $statusColors[$plan->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$plan->status] ?? 'secondary' }}">
                                                {{ str_replace('_', ' ', ucfirst($plan->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($plan->next_review_date)
                                                @if ($plan->next_review_date->isPast())
                                                    <span class="text-danger">{{ $plan->next_review_date->format('d M Y') }}</span>
                                                @elseif ($plan->next_review_date->diffInDays(now()) <= 7)
                                                    <span
                                                        class="text-warning">{{ $plan->next_review_date->format('d M Y') }}</span>
                                                @else
                                                    {{ $plan->next_review_date->format('d M Y') }}
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('welfare.intervention-plans.edit', $plan) }}"
                                                    class="btn btn-outline-info" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-list-check" style="font-size: 48px; opacity: 0.5;"></i>
                                            <p class="mb-0 mt-2">No intervention plans found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($plans->hasPages())
                        <div class="mt-3">
                            {{ $plans->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
