@extends('layouts.master')

@section('title')
    Safeguarding Concerns
@endsection

@section('css')
    <style>
        /* Safeguarding Container */
        .safeguarding-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .safeguarding-header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .safeguarding-body {
            padding: 24px;
        }

        .help-text {
            background: #fef2f2;
            padding: 12px 16px;
            border-left: 4px solid #ef4444;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
        }

        .help-text .help-title {
            font-weight: 600;
            color: #991b1b;
            margin-bottom: 4px;
        }

        .help-text .help-content {
            color: #dc2626;
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

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            color: white;
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
            Safeguarding
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-all me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="safeguarding-container">
        <div class="safeguarding-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 style="margin:0;">Safeguarding Concerns</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Monitor and manage child protection concerns</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalCount = $concerns->total();
                        $criticalCount = $concerns->where('risk_level', 'critical')->count();
                        $activeCount = $concerns->whereIn('status', ['identified', 'investigating', 'referred', 'monitoring'])->count();
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
                                <h4 class="mb-0 fw-bold text-white">{{ $criticalCount }}</h4>
                                <small class="opacity-75">Critical</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $activeCount }}</h4>
                                <small class="opacity-75">Active</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="safeguarding-body">
            <div class="help-text">
                <div class="help-title"><i class="fas fa-shield-alt me-1"></i> Confidential Information</div>
                <div class="help-content">
                    Safeguarding records are highly sensitive. Access is restricted to authorized personnel only. All concerns must be handled with strict confidentiality.
                </div>
            </div>

            <!-- Filters and Actions -->
            <div class="row align-items-center mb-3">
                <div class="col-lg-9 col-md-12">
                    <form method="GET" action="{{ route('welfare.safeguarding.index') }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-2 col-md-4 col-sm-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Search student..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="identified" {{ request('status') === 'identified' ? 'selected' : '' }}>Identified</option>
                                    <option value="investigating" {{ request('status') === 'investigating' ? 'selected' : '' }}>Investigating</option>
                                    <option value="referred" {{ request('status') === 'referred' ? 'selected' : '' }}>Referred</option>
                                    <option value="monitoring" {{ request('status') === 'monitoring' ? 'selected' : '' }}>Monitoring</option>
                                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select name="risk_level" class="form-select">
                                    <option value="">All Risk Levels</option>
                                    <option value="low" {{ request('risk_level') === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ request('risk_level') === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ request('risk_level') === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="critical" {{ request('risk_level') === 'critical' ? 'selected' : '' }}>Critical</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6">
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <option value="physical_abuse" {{ request('category') === 'physical_abuse' ? 'selected' : '' }}>Physical Abuse</option>
                                    <option value="emotional_abuse" {{ request('category') === 'emotional_abuse' ? 'selected' : '' }}>Emotional Abuse</option>
                                    <option value="neglect" {{ request('category') === 'neglect' ? 'selected' : '' }}>Neglect</option>
                                    <option value="sexual_abuse" {{ request('category') === 'sexual_abuse' ? 'selected' : '' }}>Sexual Abuse</option>
                                    <option value="domestic_violence" {{ request('category') === 'domestic_violence' ? 'selected' : '' }}>Domestic Violence</option>
                                    <option value="self_harm" {{ request('category') === 'self_harm' ? 'selected' : '' }}>Self Harm</option>
                                    <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-lg-1 col-md-6 col-sm-6">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter"></i>
                                </button>
                            </div>
                            <div class="col-lg-2 col-md-6 col-sm-6">
                                <a href="{{ route('welfare.safeguarding.index') }}" class="btn btn-light w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-3 col-md-12 text-lg-end text-md-start mt-lg-0 mt-3">
                    <a href="{{ route('welfare.safeguarding.create') }}" class="btn btn-danger">
                        <i class="fas fa-plus me-2"></i>Report Concern
                    </a>
                </div>
            </div>

            <!-- Concerns Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Category</th>
                                    <th>Risk Level</th>
                                    <th>Status</th>
                                    <th>Reported By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($concerns as $concern)
                                    <tr class="{{ $concern->risk_level === 'critical' ? 'table-danger' : ($concern->risk_level === 'high' ? 'table-warning' : '') }}">
                                        <td>{{ $concern->date_identified->format('d M Y') }}</td>
                                        <td>
                                            <a href="{{ route('welfare.safeguarding.edit', $concern) }}">
                                                {{ $concern->student->full_name ?? '-' }}
                                            </a>
                                        </td>
                                        <td>{{ $concern->category->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $riskColors = [
                                                    'low' => 'success',
                                                    'medium' => 'warning',
                                                    'high' => 'danger',
                                                    'critical' => 'dark',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $riskColors[$concern->risk_level] ?? 'secondary' }}">
                                                {{ ucfirst($concern->risk_level) }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'identified' => 'info',
                                                    'investigating' => 'warning',
                                                    'referred' => 'primary',
                                                    'monitoring' => 'secondary',
                                                    'closed' => 'success',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$concern->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$concern->status] ?? 'secondary' }}">
                                                {{ ucfirst($concern->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $concern->reportedBy->full_name ?? '-' }}</td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('welfare.safeguarding.edit', $concern) }}"
                                                    class="btn btn-outline-warning" title="View">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-shield-alt" style="font-size: 48px; opacity: 0.5;"></i>
                                            <p class="mb-0 mt-2">No safeguarding concerns found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($concerns->hasPages())
                        <div class="mt-3">
                            {{ $concerns->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
