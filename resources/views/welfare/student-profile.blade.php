@extends('layouts.master')

@section('title')
    Welfare Profile - {{ $student->full_name }}
@endsection

@section('css')
    <style>
        /* Cards */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
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

        .btn-outline-primary,
        .btn-outline-warning,
        .btn-outline-info {
            border-radius: 3px !important;
            transition: all 0.2s;
        }

        .btn-outline-primary:hover {
            transform: translateY(-1px);
        }

        .btn-outline-warning:hover {
            transform: translateY(-1px);
        }

        .btn-outline-info:hover {
            transform: translateY(-1px);
        }

        .btn-light {
            border: 1px solid #dee2e6;
            border-radius: 3px !important;
            transition: all 0.2s;
        }

        .btn-light:hover {
            background: #f8f9fa;
            transform: translateY(-1px);
        }

        /* Table Styling */
        .table thead th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('welfare.dashboard') }}">Back</a>
        @endslot
        @slot('title')
            {{ $student->full_name }}
        @endslot
    @endcomponent

    <div class="row">
        <!-- Student Info Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto mb-3">
                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle font-size-24">
                                {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                            </span>
                        </div>
                        <h5 class="mb-1">{{ $student->full_name }}</h5>
                        <p class="text-muted mb-0">{{ $student->currentGrade->name ?? 'N/A' }}</p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted">Student ID:</td>
                                    <td class="text-end">{{ $student->student_id ?? $student->id }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Gender:</td>
                                    <td class="text-end">{{ ucfirst($student->gender ?? 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Date of Birth:</td>
                                    <td class="text-end">
                                        {{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M Y') : 'N/A' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Welfare Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Welfare Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Cases:</span>
                        <span class="fw-medium">{{ $summary['cases']['total'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Open Cases:</span>
                        <span class="badge bg-warning">{{ $summary['cases']['open'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Counseling Sessions:</span>
                        <span class="fw-medium">{{ $summary['counseling']['total'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Disciplinary Records:</span>
                        <span class="fw-medium">{{ $summary['disciplinary']['total'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Health Incidents:</span>
                        <span class="fw-medium">{{ $summary['health']['total'] ?? 0 }}</span>
                    </div>
                    @if ($summary['has_active_intervention'] ?? false)
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle me-1"></i> Active intervention plan in place
                        </div>
                    @endif
                    @if ($summary['has_financial_assistance'] ?? false)
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="fas fa-check-circle me-1"></i> Receiving financial assistance
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Cases -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Welfare Cases</h5>
                    <a href="{{ route('welfare.cases.create', ['student' => $student->id]) }}"
                        class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> New Case
                    </a>
                </div>
                <div class="card-body">
                    @if ($recentCases->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Case #</th>
                                        <th>Type</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Opened</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentCases as $case)
                                        <tr>
                                            <td>
                                                <a href="{{ route('welfare.cases.edit', $case) }}" class="fw-medium">
                                                    {{ $case->case_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge"
                                                    style="background-color: {{ $case->welfareType->color ?? '#6c757d' }}">
                                                    {{ $case->welfareType->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>{{ Str::limit($case->title, 30) }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $case->status_color }}-subtle text-{{ $case->status_color }}">
                                                    {{ str_replace('_', ' ', ucfirst($case->status)) }}
                                                </span>
                                            </td>
                                            <td>{{ $case->opened_at->format('d M Y') }}</td>
                                            <td>
                                                <a href="{{ route('welfare.cases.edit', $case) }}"
                                                    class="btn btn-sm btn-light" title="View">
                                                    <i class="fas fa-show"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-folder-open font-size-24 d-block mb-2"></i>
                            No welfare cases found for this student
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Quick Actions</h6>
                            <div class="d-grid gap-2">
                                <a href="{{ route('welfare.counseling.create', ['student' => $student->id]) }}"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-calendar-plus me-1"></i> Schedule Counseling
                                </a>
                                <a href="{{ route('welfare.disciplinary.create', ['student' => $student->id]) }}"
                                    class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-error me-1"></i> Report Incident
                                </a>
                                <a href="{{ route('welfare.health.create', ['student' => $student->id]) }}"
                                    class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-plus-square me-1"></i> Log Health Incident
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
