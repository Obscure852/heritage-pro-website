@extends('layouts.master')

@section('title')
    Assignment Submissions
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

        .submissions-table {
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

        .status-returned {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-draft {
            background: #f3f4f6;
            color: #6b7280;
        }

        .late-badge {
            background: #fee2e2;
            color: #991b1b;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
            margin-left: 8px;
        }

        .score-display {
            font-weight: 600;
            font-size: 16px;
        }

        .score-display.high {
            color: #059669;
        }

        .score-display.medium {
            color: #d97706;
        }

        .score-display.low {
            color: #dc2626;
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

        .file-count {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #6b7280;
            font-size: 13px;
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

        .filter-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .filter-bar select {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 13px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            Submissions
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 style="margin:0;">{{ $assignment->title }}</h3>
                <p style="margin:8px 0 0 0; opacity:0.9;">
                    {{ $assignment->contentItem->module->title }} &bull;
                    {{ $assignment->contentItem->module->course->title }}
                </p>
            </div>
            <div class="header-stats">
                <div class="stat-item">
                    <h4>{{ $stats['total'] }}</h4>
                    <small>Total</small>
                </div>
                <div class="stat-item">
                    <h4>{{ $stats['submitted'] }}</h4>
                    <small>Submitted</small>
                </div>
                <div class="stat-item">
                    <h4>{{ $stats['needs_grading'] }}</h4>
                    <small>Pending</small>
                </div>
                <div class="stat-item">
                    <h4>{{ $stats['graded'] }}</h4>
                    <small>Graded</small>
                </div>
            </div>
        </div>
    </div>

    <div class="help-text">
        <div class="help-title">Assignment Submissions</div>
        <p class="help-content">Review and grade student submissions. Click on a submission to view details, provide
            feedback, and assign scores.</p>
    </div>

    <div class="section-header">
        <h5 class="section-title">All Submissions <span class="text-muted fw-normal"
                style="font-size: 14px;">({{ $submissions->total() }})</span></h5>
        <a href="{{ route('lms.assignments.edit', $assignment) }}" class="btn btn-outline-secondary">
            <i class="fas fa-cog"></i> Settings
        </a>
    </div>

    <div class="submissions-table">
        @if ($submissions->count())
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Submitted</th>
                            <th>Files</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($submissions as $submission)
                            <tr>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">
                                            {{ strtoupper(substr($submission->student->firstname, 0, 1)) }}{{ strtoupper(substr($submission->student->lastname, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="student-name">
                                                {{ $submission->student->firstname }} {{ $submission->student->lastname }}
                                            </div>
                                            <div class="student-id">{{ $submission->student->student_id ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($submission->submitted_at)
                                        {{ $submission->submitted_at->format('M j, Y g:i A') }}
                                        @if ($submission->is_late)
                                            <span class="late-badge">Late</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Not submitted</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($submission->attachedFiles->count())
                                        <div class="file-count">
                                            <i class="fas fa-paperclip"></i>
                                            {{ $submission->attachedFiles->count() }} file(s)
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $submission->status }}">
                                        {{ $submission->status }}
                                    </span>
                                </td>
                                <td>
                                    @if ($submission->final_score !== null)
                                        @php
                                            $percentage = $assignment->max_points > 0
                                                ? ($submission->final_score / $assignment->max_points) * 100
                                                : 0;
                                            $class =
                                                $percentage >= 70 ? 'high' : ($percentage >= 50 ? 'medium' : 'low');
                                        @endphp
                                        <span class="score-display {{ $class }}">
                                            {{ number_format($submission->final_score, 1) }}/{{ $assignment->max_points }}
                                        </span>
                                        @if ($submission->late_penalty_applied > 0)
                                            <br>
                                            <small class="text-warning">-{{ $submission->late_penalty_applied }}
                                                penalty</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('lms.submissions.grade', $submission) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-pen"></i>
                                        {{ $submission->status === 'graded' ? 'Review' : 'Grade' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($submissions->hasPages())
                <div style="padding: 16px; border-top: 1px solid #e5e7eb;">
                    {{ $submissions->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h5>No Submissions Yet</h5>
                <p>Students haven't submitted any work for this assignment.</p>
            </div>
        @endif
    </div>
@endsection
