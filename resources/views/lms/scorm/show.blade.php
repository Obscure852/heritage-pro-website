@extends('layouts.master')

@section('title')
    {{ $package->title }} - SCORM Package
@endsection

@section('css')
    <style>
        .page-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-radius: 3px;
            padding: 24px;
            margin-bottom: 24px;
            color: white;
        }

        .page-header h4 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header h4 .badge {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.2);
        }

        .page-header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-header {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 8px 16px;
            border-radius: 3px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-header:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .btn-header.primary {
            background: white;
            color: #7c3aed;
            border-color: white;
        }

        .btn-header.primary:hover {
            background: #f3f4f6;
            color: #6d28d9;
        }

        .help-text {
            background: #f5f3ff;
            padding: 12px 16px;
            border-left: 4px solid #8b5cf6;
            border-radius: 0 3px 3px 0;
            margin-bottom: 24px;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 3px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stat-card .icon {
            width: 44px;
            height: 44px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 18px;
        }

        .stat-card.attempts .icon {
            background: #eff6ff;
            color: #3b82f6;
        }

        .stat-card.students .icon {
            background: #f0fdf4;
            color: #22c55e;
        }

        .stat-card.completed .icon {
            background: #fef3c7;
            color: #f59e0b;
        }

        .stat-card.passed .icon {
            background: #dcfce7;
            color: #16a34a;
        }

        .stat-card.score .icon {
            background: #fae8ff;
            color: #a855f7;
        }

        .stat-card .value {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            display: block;
        }

        .stat-card .label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-card {
            background: white;
            border-radius: 3px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .detail-card .card-header {
            background: #f8fafc;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-card .card-body {
            padding: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .info-item {
            padding: 12px 16px;
            background: #f9fafb;
            border-radius: 3px;
        }

        .info-item .label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-item .value {
            font-size: 14px;
            font-weight: 500;
            color: #1f2937;
        }

        .attempts-table {
            margin: 0;
        }

        .attempts-table thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            padding: 12px 16px;
        }

        .attempts-table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }

        .attempts-table tbody tr:hover {
            background: #f9fafb;
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .student-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 11px;
            font-weight: 600;
        }

        .student-name {
            font-weight: 500;
            color: #1f2937;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-completed {
            background: #dcfce7;
            color: #166534;
        }

        .status-incomplete {
            background: #fef3c7;
            color: #92400e;
        }

        .status-not-attempted {
            background: #f3f4f6;
            color: #6b7280;
        }

        .status-passed {
            background: #dcfce7;
            color: #166534;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-unknown {
            background: #f3f4f6;
            color: #6b7280;
        }

        .score-display {
            font-weight: 600;
            color: #1f2937;
        }

        .course-card {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            border-radius: 3px;
            padding: 20px;
            color: white;
            margin-bottom: 24px;
        }

        .course-card h6 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.8;
            margin-bottom: 8px;
        }

        .course-card .course-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .course-card .module-title {
            font-size: 13px;
            opacity: 0.9;
        }

        .course-card .course-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            color: white;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 3px;
            transition: background 0.2s;
        }

        .course-card .course-link:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .uploader-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: #f9fafb;
            border-radius: 3px;
        }

        .uploader-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            font-weight: 600;
        }

        .uploader-info h6 {
            margin: 0 0 4px;
            font-weight: 600;
            color: #1f2937;
        }

        .uploader-info small {
            color: #6b7280;
            font-size: 12px;
        }

        .empty-attempts {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .empty-attempts i {
            font-size: 32px;
            color: #d1d5db;
            margin-bottom: 12px;
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Learning Space
        @endslot
        @slot('li_1_url')
            {{ $package->contentItem && $package->contentItem->module ? route('lms.courses.edit', $package->contentItem->module->course) : route('lms.scorm.index') }}
        @endslot
        @slot('li_2')
            {{ $package->contentItem && $package->contentItem->module ? $package->contentItem->module->course->title : 'SCORM Packages' }}
        @endslot
        @slot('li_2_url')
            {{ $package->contentItem && $package->contentItem->module ? route('lms.courses.edit', $package->contentItem->module->course) : route('lms.scorm.index') }}
        @endslot
        @slot('title')
            {{ $package->title }}
        @endslot
    @endcomponent

    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4>
                <i class="fas fa-cube me-1"></i>
                {{ $package->title }}
                <span class="badge">{{ $package->is_scorm_12 ? 'SCORM 1.2' : 'SCORM 2004' }}</span>
            </h4>
            @if($package->description)
                <p>{{ $package->description }}</p>
            @endif
        </div>
        <div class="header-actions">
            <a href="{{ route('lms.scorm.index') }}" class="btn-header">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <a href="{{ route('lms.scorm.edit', $package) }}" class="btn-header">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('lms.scorm.preview', $package) }}" class="btn-header primary" target="_blank">
                <i class="fas fa-play"></i> Preview
            </a>
        </div>
    </div>

    <div class="help-text">
        <div class="help-title">Package Overview</div>
        <div class="help-content">
            View package statistics, student attempts, and completion data. Use the Preview button to test the SCORM content as an instructor.
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card attempts">
            <div class="icon"><i class="fas fa-mouse-pointer"></i></div>
            <span class="value">{{ $stats['total_attempts'] }}</span>
            <span class="label">Total Attempts</span>
        </div>
        <div class="stat-card students">
            <div class="icon"><i class="fas fa-users"></i></div>
            <span class="value">{{ $stats['unique_students'] }}</span>
            <span class="label">Unique Students</span>
        </div>
        <div class="stat-card completed">
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <span class="value">{{ $stats['completed'] }}</span>
            <span class="label">Completed</span>
        </div>
        <div class="stat-card passed">
            <div class="icon"><i class="fas fa-trophy"></i></div>
            <span class="value">{{ $stats['passed'] }}</span>
            <span class="label">Passed</span>
        </div>
        <div class="stat-card score">
            <div class="icon"><i class="fas fa-star"></i></div>
            <span class="value">{{ $stats['avg_score'] ? number_format($stats['avg_score'], 1) : '-' }}</span>
            <span class="label">Avg Score</span>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Student Attempts -->
            <div class="detail-card">
                <div class="card-header">
                    <i class="fas fa-list"></i> Student Attempts
                </div>
                @if($package->attempts->count() > 0)
                    <div class="table-responsive">
                        <table class="table attempts-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Attempt</th>
                                    <th>Completion</th>
                                    <th>Success</th>
                                    <th>Score</th>
                                    <th>Time</th>
                                    <th>Last Activity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($package->attempts->sortByDesc('updated_at') as $attempt)
                                    <tr>
                                        <td>
                                            <div class="student-info">
                                                <div class="student-avatar">
                                                    {{ $attempt->student ? strtoupper(substr($attempt->student->firstname, 0, 1) . substr($attempt->student->surname, 0, 1)) : 'NA' }}
                                                </div>
                                                <span class="student-name">
                                                    {{ $attempt->student ? $attempt->student->firstname . ' ' . $attempt->student->surname : 'Unknown' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>#{{ $attempt->attempt_number }}</td>
                                        <td>
                                            @php
                                                $completionClass = match($attempt->completion_status) {
                                                    'completed' => 'status-completed',
                                                    'incomplete' => 'status-incomplete',
                                                    default => 'status-not-attempted'
                                                };
                                            @endphp
                                            <span class="status-badge {{ $completionClass }}">
                                                {{ ucfirst($attempt->completion_status ?? 'Not Started') }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $successClass = match($attempt->success_status) {
                                                    'passed' => 'status-passed',
                                                    'failed' => 'status-failed',
                                                    default => 'status-unknown'
                                                };
                                            @endphp
                                            <span class="status-badge {{ $successClass }}">
                                                {{ ucfirst($attempt->success_status ?? 'Unknown') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="score-display">
                                                @if($attempt->score_raw !== null)
                                                    {{ $attempt->score_raw }}
                                                    @if($attempt->score_max)
                                                        / {{ $attempt->score_max }}
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            @if($attempt->total_time)
                                                {{ $attempt->total_time }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            {{ $attempt->updated_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-attempts">
                        <i class="fas fa-user-clock d-block"></i>
                        <p class="mb-0">No attempts yet</p>
                        <small>Student attempts will appear here once they start this content.</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Associated Course -->
            @if($package->contentItem && $package->contentItem->module && $package->contentItem->module->course)
                <div class="course-card">
                    <h6>Associated Course</h6>
                    <div class="course-title">{{ $package->contentItem->module->course->title }}</div>
                    <div class="module-title">
                        <i class="fas fa-folder me-1"></i>
                        {{ $package->contentItem->module->title }}
                    </div>
                    <a href="{{ route('lms.courses.show', $package->contentItem->module->course) }}" class="course-link">
                        <i class="fas fa-external-link-alt"></i> View Course
                    </a>
                </div>
            @endif

            <!-- Package Details -->
            <div class="detail-card">
                <div class="card-header">
                    <i class="fas fa-info-circle"></i> Package Details
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="label">Version</div>
                            <div class="value">{{ $package->version }}</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Max Attempts</div>
                            <div class="value">{{ $package->max_attempts ?? 'Unlimited' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Mastery Score</div>
                            <div class="value">{{ $package->mastery_score ?? 'Not Set' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Time Limit</div>
                            <div class="value">{{ $package->time_limit_minutes ? $package->time_limit_minutes . ' min' : 'None' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical Info -->
            <div class="detail-card">
                <div class="card-header">
                    <i class="fas fa-cog"></i> Technical Info
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <div class="label">Launch URL</div>
                        <div class="value" style="word-break: break-all; font-size: 12px;">{{ $package->launch_url }}</div>
                    </div>
                    <div class="info-item mb-3">
                        <div class="label">Identifier</div>
                        <div class="value" style="font-family: monospace; font-size: 12px;">{{ $package->identifier ?? 'N/A' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="label">Created</div>
                        <div class="value">{{ $package->created_at->format('M d, Y \a\t H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Uploaded By -->
            @if($package->uploader)
                <div class="detail-card">
                    <div class="card-header">
                        <i class="fas fa-user"></i> Uploaded By
                    </div>
                    <div class="card-body p-0">
                        <div class="uploader-card m-3">
                            <div class="uploader-avatar">
                                {{ strtoupper(substr($package->uploader->name, 0, 2)) }}
                            </div>
                            <div class="uploader-info">
                                <h6>{{ $package->uploader->name }}</h6>
                                <small>{{ $package->created_at->format('M d, Y') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
