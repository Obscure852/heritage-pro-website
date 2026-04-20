@extends('layouts.master')

@section('title')
    Enrollments - {{ $course->title }}
@endsection

@section('css')
    <style>
        .enrollment-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .enrollment-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 20px 24px;
            border-radius: 3px 3px 0 0;
        }

        .enrollment-body {
            padding: 24px;
        }

        /* Header Stats */
        .stat-item {
            padding: 6px 16px;
            text-align: center;
        }

        .stat-item h4 {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            color: #fff;
        }

        .stat-item small {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        /* Helper Text */
        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #4e73df;
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

        /* Filter Controls */
        .filter-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-controls .form-control,
        .filter-controls .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
            font-size: 14px;
            padding: 10px 12px;
            height: auto;
        }

        .filter-controls .form-control:focus,
        .filter-controls .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
        }

        .filter-controls .input-group {
            width: 280px;
        }

        .filter-controls .input-group-text {
            background: #f8f9fa;
            border: 1px solid #d1d5db;
            border-right: none;
            border-radius: 3px 0 0 3px !important;
            padding: 10px 12px;
        }

        .filter-controls .input-group .form-control {
            border-left: none;
            border-radius: 0 3px 3px 0 !important;
        }

        .filter-controls .form-select {
            width: 160px;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
        }

        .filter-spacer {
            flex-grow: 1;
        }

        /* Buttons */
        .btn {
            padding: 10px 16px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: #4e73df;
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: #3d5fc7;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
            color: white;
        }

        .btn-outline-secondary {
            border: 1px solid #d1d5db;
            color: #6b7280;
            background: #fff;
        }

        .btn-outline-secondary:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
            color: #374151;
        }

        /* Table */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px 16px;
        }

        .table tbody td {
            padding: 12px 16px;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .student-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4e73df, #36b9cc);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .progress {
            height: 8px;
            border-radius: 4px;
            background: #e5e7eb;
        }

        .progress-bar {
            background: linear-gradient(135deg, #4e73df, #36b9cc);
        }

        .badge {
            padding: 4px 10px;
            border-radius: 3px !important;
            font-weight: 500;
            font-size: 12px;
        }

        .status-active { background: #d1fae5; color: #065f46; }
        .status-completed { background: #dbeafe; color: #1e40af; }
        .status-dropped { background: #fee2e2; color: #991b1b; }

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
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 14px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state p {
            margin: 0;
        }

        @media (max-width: 992px) {
            .filter-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-controls .input-group,
            .filter-controls .form-select {
                width: 100%;
            }

            .filter-spacer {
                display: none;
            }

            .filter-actions {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.courses.edit', $course) }}">{{ $course->title }}</a>
        @endslot
        @slot('title')
            Enrollments
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="enrollment-container">
        <div class="enrollment-header">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h4 style="margin: 0 0 4px 0; font-weight: 600;">Content Enrollments</h4>
                    <p style="margin: 0; opacity: 0.9; font-size: 14px;">{{ $course->title }}</p>
                </div>
                <div class="col-lg-6">
                    <div class="d-flex justify-content-lg-end align-items-center mt-3 mt-lg-0">
                        <div class="stat-item">
                            <h4>{{ $enrollments->total() }}</h4>
                            <small>Total</small>
                        </div>
                        <div class="stat-item">
                            <h4>{{ $enrollments->where('status', 'active')->count() }}</h4>
                            <small>Active</small>
                        </div>
                        <div class="stat-item">
                            <h4>{{ $enrollments->where('status', 'completed')->count() }}</h4>
                            <small>Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="enrollment-body">
            <div class="help-text">
                <div class="help-title">Manage Enrollments</div>
                <p class="help-content">View and manage student enrollments for this content. Use the filters to search for specific students or filter by status.</p>
            </div>

            <form method="GET" action="{{ route('lms.enrollments.index', $course) }}">
                <div class="filter-controls">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control"
                            placeholder="Search students..." value="{{ $filters['search'] ?? '' }}">
                    </div>

                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="dropped" {{ ($filters['status'] ?? '') === 'dropped' ? 'selected' : '' }}>Dropped</option>
                    </select>

                    <select name="klass_id" class="form-select">
                        <option value="">All Classes</option>
                        @foreach ($classes as $klass)
                            <option value="{{ $klass->id }}" {{ ($filters['klass_id'] ?? '') == $klass->id ? 'selected' : '' }}>
                                {{ $klass->name }}
                            </option>
                        @endforeach
                    </select>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('lms.enrollments.index', $course) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>

                    <div class="filter-spacer"></div>

                    <a href="{{ route('lms.enrollments.create', $course) }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Enroll Students
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Progress</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Enrolled</th>
                            <th style="width: 60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($enrollments as $enrollment)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="student-avatar me-2">
                                            {{ strtoupper(substr($enrollment->student->first_name ?? 'S', 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong>{{ $enrollment->student->full_name }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $enrollment->student->current_class->name ?? '-' }}</td>
                                <td style="width: 150px;">
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2">
                                            <div class="progress-bar" style="width: {{ $enrollment->progress_percentage }}%"></div>
                                        </div>
                                        <span class="text-muted" style="font-size: 13px;">{{ $enrollment->progress_percentage }}%</span>
                                    </div>
                                </td>
                                <td>{{ $enrollment->grade ?? '-' }}</td>
                                <td>
                                    <span class="badge status-{{ $enrollment->status }}">
                                        {{ ucfirst($enrollment->status) }}
                                    </span>
                                </td>
                                <td>{{ $enrollment->enrolled_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <form action="{{ route('lms.enrollments.destroy', $enrollment) }}" method="POST"
                                            onsubmit="return confirm('Remove this student from the course?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-users"></i>
                                        <p>No students enrolled yet</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($enrollments->hasPages())
                <div class="d-flex justify-content-end mt-4">
                    {{ $enrollments->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
