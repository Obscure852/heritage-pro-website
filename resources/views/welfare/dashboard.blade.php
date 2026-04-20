@extends('layouts.master')

@section('title')
    Student Welfare Dashboard
@endsection

@section('css')
    <style>
        /* Page Container */
        .welfare-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .welfare-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .welfare-body {
            padding: 24px;
        }

        /* Header Stats */
        .stat-item {
            text-align: center;
        }

        .stat-item h4 {
            font-size: 32px;
            margin: 0;
        }

        .stat-item small {
            font-size: 13px;
            opacity: 0.85;
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 3px !important;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            border-left: 4px solid;
            transition: all 0.2s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
        }

        .stat-card.danger {
            border-left-color: #dc3545;
        }

        .stat-card.warning {
            border-left-color: #ffc107;
        }

        .stat-card.info {
            border-left-color: #0dcaf0;
        }

        .stat-card.success {
            border-left-color: #10b981;
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 3px !important;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin: 8px 0 4px 0;
        }

        .stat-card .stat-label {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }

        .stat-card .stat-link {
            color: #6b7280;
            font-size: 13px;
            text-decoration: none;
            transition: color 0.2s;
        }

        .stat-card .stat-link:hover {
            color: #374151;
        }

        /* Alert Items */
        .alert-item {
            padding: 12px 16px;
            border-radius: 3px !important;
            margin-bottom: 10px;
            border-left: 3px solid;
        }

        .alert-item.critical {
            background-color: #fee2e2;
            border-left-color: #dc3545;
        }

        .alert-item.urgent {
            background-color: #fef3c7;
            border-left-color: #ffc107;
        }

        .alert-item.warning {
            background-color: #fef3c7;
            border-left-color: #f59e0b;
        }

        .alert-item.info {
            background-color: #dbeafe;
            border-left-color: #3b82f6;
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

        .table tbody tr[style*="cursor: pointer"]:hover {
            background-color: #e5e7eb;
            transition: background-color 0.2s;
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

        .btn-outline-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-outline-secondary:hover {
            background: #e9ecef;
            color: #495057;
            border-color: #dee2e6;
        }

        .btn-outline-primary {
            color: #3b82f6;
            border: 1px solid #3b82f6;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .btn-outline-warning {
            color: #f59e0b;
            border: 1px solid #f59e0b;
        }

        .btn-outline-warning:hover {
            background: #f59e0b;
            color: white;
            border-color: #f59e0b;
        }

        .btn-outline-danger {
            color: #ef4444;
            border: 1px solid #ef4444;
        }

        .btn-outline-danger:hover {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }

        /* Chart Containers */
        .chart-container {
            width: 100%;
            height: 300px;
        }

        /* Badges */
        .badge {
            padding: 4px 8px;
            border-radius: 3px !important;
            font-weight: 500;
            font-size: 12px;
        }

        /* Dropdown */
        .dropdown-menu {
            border: 1px solid #e5e7eb;
            border-radius: 3px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .1);
        }

        .dropdown-item {
            padding: 8px 16px;
            transition: background 0.2s;
        }

        .dropdown-item:hover {
            background: #f9fafb;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboard
        @endslot
        @slot('title')
            Student Welfare
        @endslot
    @endcomponent

    <div class="welfare-container">
        <div class="welfare-header">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <h4 class="mb-1 text-white"><i class="fas fa-heart me-2"></i>Student Welfare Dashboard</h4>
                    <p class="mb-0 opacity-75">Monitor and manage student wellbeing across all areas</p>
                </div>
                <div class="col-md-7">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $dashboardData['summary']['open_cases'] }}</h4>
                                <small class="opacity-75">Open Cases</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $dashboardData['summary']['pending_approval'] }}</h4>
                                <small class="opacity-75">Pending</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $dashboardData['summary']['high_priority'] }}</h4>
                                <small class="opacity-75">High Priority</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="mb-0 fw-bold text-white">{{ $dashboardData['summary']['students_with_open_cases'] }}</h4>
                                <small class="opacity-75">Students</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access & Actions -->
    <div class="mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('welfare.cases.index', ['status' => 'open']) }}" class="btn btn-outline-primary">
                <i class="fas fa-folder-open me-1"></i> Open Cases
            </a>
            <a href="{{ route('welfare.cases.index', ['approval_status' => 'pending']) }}" class="btn btn-outline-warning">
                <i class="fas fa-clock me-1"></i> Pending Approval
            </a>
            <a href="{{ route('welfare.cases.index', ['priority' => 'high']) }}" class="btn btn-outline-danger">
                <i class="fas fa-exclamation-triangle me-1"></i> High Priority
            </a>
        </div>
        <div class="dropdown">
            <button class="btn btn-primary" type="button" id="quickActionsDropdown" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="fas fa-plus me-2"></i>Quick Actions <i class="fas fa-chevron-down ms-1"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="quickActionsDropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('welfare.cases.create') }}">
                        <i class="fas fa-folder-plus me-2 text-primary"></i>New Welfare Case
                    </a>
                </li>
                @can('access-counseling')
                    <li>
                        <a class="dropdown-item" href="{{ route('welfare.counseling.create') }}">
                            <i class="fas fa-comments me-2 text-info"></i>Schedule Counseling
                        </a>
                    </li>
                @endcan
                @can('access-disciplinary')
                    <li>
                        <a class="dropdown-item" href="{{ route('welfare.disciplinary.create') }}">
                            <i class="fas fa-exclamation-circle me-2 text-warning"></i>Report Incident
                        </a>
                    </li>
                @endcan
                @can('access-health-incidents')
                    <li>
                        <a class="dropdown-item" href="{{ route('welfare.health.create') }}">
                            <i class="fas fa-plus-square me-2 text-danger"></i>Health Incident
                        </a>
                    </li>
                @endcan
                @can('access-safeguarding')
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('welfare.safeguarding.create') }}">
                            <i class="fas fa-shield-alt me-2 text-dark"></i>Safeguarding Concern
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </div>

    <div class="row">
        <!-- Cases by Type Chart -->
        <div class="col-xl-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Cases by Type</h5>
                </div>
                <div class="card-body">
                    <div id="casesByTypeChart" class="chart-container"></div>
                </div>
            </div>
        </div>

        <!-- Module Statistics -->
        <div class="col-xl-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">This Term</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                @can('access-counseling')
                                    <tr style="cursor: pointer;" onclick="window.location='{{ route('welfare.counseling.index') }}'">
                                        <td>
                                            <a href="{{ route('welfare.counseling.index') }}" class="text-decoration-none text-dark">
                                                <i class="fas fa-comments text-info me-2"></i>Counseling Sessions
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ $dashboardData['cases']['counseling']['total'] ?? 0 }}</strong>
                                        </td>
                                    </tr>
                                @endcan
                                @can('access-disciplinary')
                                    <tr style="cursor: pointer;" onclick="window.location='{{ route('welfare.disciplinary.index') }}'">
                                        <td>
                                            <a href="{{ route('welfare.disciplinary.index') }}" class="text-decoration-none text-dark">
                                                <i class="fas fa-exclamation-circle text-warning me-2"></i>Disciplinary Cases
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ $dashboardData['cases']['disciplinary']['total'] ?? 0 }}</strong>
                                        </td>
                                    </tr>
                                @endcan
                                @can('access-safeguarding')
                                    <tr style="cursor: pointer;" onclick="window.location='{{ route('welfare.safeguarding.index') }}'">
                                        <td>
                                            <a href="{{ route('welfare.safeguarding.index') }}" class="text-decoration-none text-dark">
                                                <i class="fas fa-shield-alt text-danger me-2"></i>Safeguarding
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ $dashboardData['cases']['safeguarding']['total'] ?? 0 }}</strong>
                                        </td>
                                    </tr>
                                @endcan
                                @can('access-health-incidents')
                                    <tr style="cursor: pointer;" onclick="window.location='{{ route('welfare.health.index') }}'">
                                        <td>
                                            <a href="{{ route('welfare.health.index') }}" class="text-decoration-none text-dark">
                                                <i class="fas fa-briefcase-medical text-success me-2"></i>Health Incidents
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <strong>{{ $dashboardData['cases']['health']['total'] ?? 0 }}</strong>
                                        </td>
                                    </tr>
                                @endcan
                                <tr style="cursor: pointer;" onclick="window.location='{{ route('welfare.intervention-plans.index') }}'">
                                    <td>
                                        <a href="{{ route('welfare.intervention-plans.index') }}" class="text-decoration-none text-dark">
                                            <i class="fas fa-clipboard-list text-primary me-2"></i>Intervention Plans
                                        </a>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{ $dashboardData['cases']['intervention_plans']['active'] ?? 0 }}
                                            active</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Recent Activity</h5>
                    @can('view-welfare-audit')
                        <a href="{{ route('welfare.audit-log') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-list me-1"></i>View All
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>Case</th>
                                    <th>Student</th>
                                    <th>User</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dashboardData['recent_activity'] as $activity)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $activity['color'] }}">
                                                {{ $activity['action'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($activity['case_number'])
                                                @php
                                                    $case = \App\Models\Welfare\WelfareCase::where(
                                                        'case_number',
                                                        $activity['case_number'],
                                                    )->first();
                                                @endphp
                                                @if ($case)
                                                    <a href="{{ route('welfare.cases.edit', $case) }}" class="text-primary">
                                                        {{ $activity['case_number'] }}
                                                    </a>
                                                @else
                                                    {{ $activity['case_number'] }}
                                                @endif
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>{{ $activity['student'] ?? '—' }}</td>
                                        <td>{{ $activity['user'] }}</td>
                                        <td class="text-muted">{{ $activity['time'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            <i class="fas fa-history" style="font-size: 32px; opacity: 0.3;"></i>
                                            <p class="mt-2 mb-0">No recent activity</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cases by Type Chart
            const casesByTypeChart = echarts.init(document.getElementById('casesByTypeChart'));

            const casesByTypeData = [
                @foreach($welfareTypes as $type)
                {
                    name: '{{ $type->name }}',
                    value: {{ $dashboardData['cases']['by_type'][$type->code] ?? 0 }},
                    itemStyle: {
                        color: '{{ $type->color ?? '#6c757d' }}'
                    }
                },
                @endforeach
            ];

            const casesByTypeOption = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} ({d}%)'
                },
                legend: {
                    orient: 'vertical',
                    right: '10%',
                    top: 'center',
                    textStyle: {
                        fontSize: 12
                    }
                },
                series: [{
                    type: 'pie',
                    radius: ['40%', '70%'],
                    center: ['35%', '50%'],
                    avoidLabelOverlap: false,
                    itemStyle: {
                        borderRadius: 3,
                        borderColor: '#fff',
                        borderWidth: 2
                    },
                    label: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: 16,
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: false
                    },
                    data: casesByTypeData
                }]
            };

            casesByTypeChart.setOption(casesByTypeOption);

            // Responsive chart resize
            window.addEventListener('resize', function() {
                casesByTypeChart.resize();
            });
        });
    </script>
@endsection
