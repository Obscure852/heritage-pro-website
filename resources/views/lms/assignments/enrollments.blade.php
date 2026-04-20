@extends('layouts.master')

@section('title')
    Assignment Enrollments
@endsection

@section('css')
    <style>
        .page-header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 28px;
            border-radius: 3px;
            margin-bottom: 24px;
        }

        .header-stats {
            display: flex;
            gap: 48px;
        }

        .stat-item {
            padding: 10px 0;
            text-align: center;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            color: white;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #3b82f6;
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

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .search-box {
            position: relative;
            width: 280px;
        }

        .search-box input {
            width: 100%;
            padding: 8px 12px 8px 36px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 13px;
        }

        .search-box input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .enrollments-table {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .table {
            margin: 0;
        }

        .table th {
            background: #f9fafb;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
            padding: 14px 16px;
        }

        .table td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }

        .student-name {
            font-weight: 500;
            color: #1f2937;
        }

        .student-id {
            font-size: 12px;
            color: #6b7280;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-submitted {
            background: #e0e7ff;
            color: #3730a3;
        }

        .status-graded {
            background: #d1fae5;
            color: #065f46;
        }

        .status-not-submitted {
            background: #fee2e2;
            color: #991b1b;
        }

        .score-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 13px;
        }

        .score-high {
            background: #d1fae5;
            color: #065f46;
        }

        .score-medium {
            background: #fef3c7;
            color: #92400e;
        }

        .score-low {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-outline-secondary {
            border: 1px solid #d1d5db;
            color: #374151;
            background: white;
        }

        .btn-outline-secondary:hover {
            background: #f9fafb;
            color: #1f2937;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .sidebar-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 20px;
        }

        .sidebar-card .card-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 14px 16px;
        }

        .sidebar-card .card-title {
            font-weight: 600;
            font-size: 14px;
            color: #1f2937;
            margin: 0;
        }

        .sidebar-card .card-body {
            padding: 16px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6b7280;
        }

        .info-value {
            font-weight: 600;
            color: #1f2937;
        }

        .quick-actions .d-grid {
            gap: 8px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('li_2')
            @if ($assignment->contentItem && $assignment->contentItem->module)
                <a href="{{ route('lms.courses.show', $assignment->contentItem->module->course) }}">
                    {{ $assignment->contentItem->module->course->title }}
                </a>
            @endif
        @endslot
        @slot('title')
            Enrollments
        @endslot
    @endcomponent

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 style="margin:0;">{{ $assignment->title }}</h3>
                <p style="margin:8px 0 0 0; opacity:0.9;">
                    @if ($assignment->contentItem && $assignment->contentItem->module)
                        {{ $assignment->contentItem->module->title }} &bull;
                        {{ $assignment->contentItem->module->course->title }}
                    @endif
                    @if ($assignment->due_date)
                        &bull; Due: {{ $assignment->due_date->format('M j, Y') }}
                    @endif
                </p>
            </div>
            <div class="header-stats">
                <div class="stat-item">
                    <h4>{{ $enrolledCount }}</h4>
                    <small>Enrolled</small>
                </div>
                <div class="stat-item">
                    <h4>{{ $submittedCount }}</h4>
                    <small>Submitted</small>
                </div>
                <div class="stat-item">
                    <h4>{{ $enrolledCount - $submittedCount }}</h4>
                    <small>Pending</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-9">
            <div class="help-text">
                <div class="help-title">Enrolled Students</div>
                <p class="help-content">View all students enrolled in the course associated with this assignment. Students who haven't submitted are highlighted so you can follow up.</p>
            </div>

            <div class="section-header">
                <h5 class="section-title">All Enrolled Students <span class="text-muted fw-normal" style="font-size: 14px;">({{ $enrollments->total() }})</span></h5>
                <form method="GET" action="{{ route('lms.assignments.enrollments', $assignment) }}" class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or ID...">
                </form>
            </div>

            <div class="enrollments-table">
                @if ($enrollments->count())
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Status</th>
                                    <th>Submissions</th>
                                    <th>Best Score</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($enrollments as $enrollment)
                                    @php
                                        $student = $enrollment->student;
                                        $submissionData = $submissions->get($student->id);
                                        $hasSubmitted = $submissionData !== null;
                                        $isGraded = $hasSubmitted && $submissionData->is_graded;
                                    @endphp
                                    <tr>
                                        <td class="text-muted">{{ $loop->iteration + ($enrollments->currentPage() - 1) * $enrollments->perPage() }}</td>
                                        <td>
                                            <div class="student-info">
                                                <div class="student-avatar">
                                                    {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="student-name">{{ $student->first_name }} {{ $student->last_name }}</div>
                                                    <div class="student-id">ID: {{ $student->id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($student->currentClass)
                                                {{ $student->currentClass->name ?? '-' }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($isGraded)
                                                <span class="status-badge status-graded">Graded</span>
                                            @elseif ($hasSubmitted)
                                                <span class="status-badge status-submitted">Submitted</span>
                                            @else
                                                <span class="status-badge status-not-submitted">Not Submitted</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($hasSubmitted)
                                                {{ $submissionData->submission_count }}
                                            @else
                                                <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($hasSubmitted && $submissionData->best_score !== null)
                                                @php
                                                    $percentage = $assignment->max_points > 0
                                                        ? ($submissionData->best_score / $assignment->max_points) * 100
                                                        : 0;
                                                    $scoreClass = $percentage >= 70 ? 'score-high' : ($percentage >= 50 ? 'score-medium' : 'score-low');
                                                @endphp
                                                <span class="score-badge {{ $scoreClass }}">
                                                    {{ number_format($submissionData->best_score, 1) }}/{{ $assignment->max_points }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($hasSubmitted)
                                                <a href="{{ route('lms.assignments.submissions', $assignment) }}" class="btn btn-outline-secondary btn-sm">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            @else
                                                <span class="text-muted" style="font-size: 12px;">No submission</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($enrollments->hasPages())
                        <div style="padding: 16px; border-top: 1px solid #e5e7eb;">
                            {{ $enrollments->links() }}
                        </div>
                    @endif
                @else
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h5>No Enrolled Students</h5>
                        <p>No students are currently enrolled in the course associated with this assignment.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-3">
            <div class="sidebar-card">
                <div class="card-header">
                    <h6 class="card-title"><i class="fas fa-info-circle me-1"></i> Assignment Info</h6>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value" style="text-transform: capitalize;">{{ $assignment->status }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Max Points</span>
                        <span class="info-value">{{ $assignment->max_points }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Submission Type</span>
                        <span class="info-value" style="text-transform: capitalize;">{{ $assignment->submission_type }}</span>
                    </div>
                    @if ($assignment->due_date)
                        <div class="info-item">
                            <span class="info-label">Due Date</span>
                            <span class="info-value">{{ $assignment->due_date->format('M j, Y') }}</span>
                        </div>
                    @endif
                    @if ($assignment->allow_late_submissions && $assignment->late_penalty_percent)
                        <div class="info-item">
                            <span class="info-label">Late Penalty</span>
                            <span class="info-value text-warning">{{ $assignment->late_penalty_percent }}%</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="sidebar-card quick-actions">
                <div class="card-header">
                    <h6 class="card-title"><i class="fas fa-bolt me-1"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid">
                        <a href="{{ route('lms.assignments.show', $assignment) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-eye me-1"></i> View Assignment
                        </a>
                        <a href="{{ route('lms.assignments.submissions', $assignment) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-clipboard-check me-1"></i> View Submissions
                        </a>
                        <a href="{{ route('lms.assignments.edit', $assignment) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-edit me-1"></i> Edit Assignment
                        </a>
                        @if ($assignment->contentItem && $assignment->contentItem->module)
                            <a href="{{ route('lms.courses.show', $assignment->contentItem->module->course) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Back to Course
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
