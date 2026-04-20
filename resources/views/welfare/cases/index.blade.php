@extends('layouts.master')

@section('title')
    Welfare Cases
@endsection

@section('css')
    <style>
        /* Page Container */
        .cases-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .cases-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .cases-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #10b981;
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

        .card-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
            border-radius: 3px 3px 0 0 !important;
        }

        .card-title {
            color: #1f2937;
            font-weight: 600;
            font-size: 16px;
            margin: 0;
        }

        .card-body {
            padding: 20px;
        }

        /* Form Elements */
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 13px;
        }

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

        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            color: #495057;
            border-color: #dee2e6;
        }

        .btn-light {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
            padding: 10px 16px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-light:hover {
            background: #e9ecef;
            color: #495057;
            border-color: #dee2e6;
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

        .input-group .form-control:focus + .input-group-text,
        .input-group-text:has(+ .form-control:focus) {
            border-color: #3b82f6;
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
            Welfare Cases
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
            <i class="mdi mdi-check-all label-icon"></i>
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="cases-container">
        <div class="cases-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Welfare Cases</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Manage and track student welfare cases</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalCount = $cases->total();
                        $openCount = $cases->where('status', 'open')->count();
                        $closedCount = $cases->where('status', 'closed')->count();
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
                                <h4 class="mb-0 fw-bold text-white">{{ $openCount }}</h4>
                                <small class="opacity-75">Open</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $closedCount }}</h4>
                                <small class="opacity-75">Closed</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="cases-body">
            <div class="help-text">
                <div class="help-title">Cases Directory</div>
                <div class="help-content">
                    Browse and manage all welfare cases. Use the search and filters to find specific cases.
                    Click on a case number to view full details and manage the case.
                </div>
            </div>

            <!-- Filters and Actions -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-8 col-md-12">
                    <form method="GET" action="{{ route('welfare.cases.index') }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Search by case #, title, student..." value="{{ $filters['search'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="open" {{ ($filters['status'] ?? '') === 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="in_progress" {{ ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="pending_approval" {{ ($filters['status'] ?? '') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                    <option value="resolved" {{ ($filters['status'] ?? '') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                    <option value="closed" {{ ($filters['status'] ?? '') === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select name="priority" class="form-select">
                                    <option value="">All Priority</option>
                                    <option value="low" {{ ($filters['priority'] ?? '') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ ($filters['priority'] ?? '') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="critical" {{ ($filters['priority'] ?? '') === 'critical' ? 'selected' : '' }}>Critical</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select name="welfare_type_id" class="form-select">
                                    <option value="">All Types</option>
                                    @foreach ($welfareTypes as $type)
                                        <option value="{{ $type->id }}" {{ ($filters['welfare_type_id'] ?? '') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-1 col-md-6 col-sm-6">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                            <div class="col-lg-1 col-md-6 col-sm-6">
                                <a href="{{ route('welfare.cases.index') }}" class="btn btn-light w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-4 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <a href="{{ route('welfare.cases.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>New Case
                    </a>
                </div>
            </div>

            <!-- Cases Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 10%;">Case #</th>
                            <th style="width: 13%;">Student</th>
                            <th style="width: 10%;">Type</th>
                            <th style="width: 18%;">Title</th>
                            <th style="width: 8%;">Priority</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 12%;">Assigned To</th>
                            <th style="width: 9%;">Opened</th>
                            <th style="width: 10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cases as $case)
                            <tr>
                                <td>
                                    <a href="{{ route('welfare.cases.edit', $case) }}" class="fw-medium">
                                        {{ $case->case_number }}
                                    </a>
                                </td>
                                <td>{{ Str::limit($case->student->full_name ?? '-', 20) }}</td>
                                <td>
                                    <span class="badge"
                                        style="background-color: {{ $case->welfareType->color ?? '#6c757d' }};">
                                        {{ Str::limit($case->welfareType->name ?? '-', 12) }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($case->title, 25) }}</td>
                                <td>
                                    @php
                                        $priorityColors = [
                                            'low' => 'secondary',
                                            'medium' => 'info',
                                            'high' => 'warning',
                                            'critical' => 'danger',
                                        ];
                                        $priorityColor = $priorityColors[$case->priority] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $priorityColor }}">
                                        {{ ucfirst($case->priority) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'open' => 'primary',
                                            'in_progress' => 'warning',
                                            'pending_approval' => 'info',
                                            'resolved' => 'success',
                                            'closed' => 'secondary',
                                            'escalated' => 'danger',
                                        ];
                                        $statusColor = $statusColors[$case->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ str_replace('_', ' ', ucfirst($case->status)) }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($case->assignedTo->full_name ?? 'Unassigned', 15) }}</td>
                                <td>{{ $case->opened_at->format('d M Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('welfare.cases.edit', $case) }}" class="btn btn-outline-info"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if ($case->status !== 'closed')
                                            <form action="{{ route('welfare.cases.close', $case) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Close Case"
                                                    onclick="return confirm('Close this case?')">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fas fa-folder-open" style="font-size: 32px; opacity: 0.3;"></i>
                                    <p class="mt-2 mb-0">No cases found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $cases->appends($filters)->links() }}
            </div>
        </div>
    </div>
        </div>
    </div>
@endsection
