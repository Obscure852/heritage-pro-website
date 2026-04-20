@extends('layouts.master')

@section('title')
    SCORM Packages - Learning Space
@endsection

@section('css')
    <style>
        .page-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 24px;
            color: white;
        }

        .page-header h4 {
            margin: 0;
            font-weight: 600;
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
            border-radius: 6px;
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

        .packages-card {
            background: white;
            border-radius: 8px;
            border: none;
        }

        .packages-table {
            margin: 0;
        }

        .packages-table thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            padding: 14px 16px;
        }

        .packages-table tbody td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f3f4f6;
        }

        .packages-table tbody tr:hover {
            background: #f9fafb;
        }

        .package-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .package-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .package-details h6 {
            margin: 0 0 4px;
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .package-details small {
            color: #6b7280;
            font-size: 12px;
        }

        .version-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .version-12 {
            background: #fef3c7;
            color: #92400e;
        }

        .version-2004 {
            background: #dbeafe;
            color: #1e40af;
        }

        .stats-group {
            display: flex;
            gap: 16px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            display: block;
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }

        .stat-label {
            font-size: 11px;
            color: #9ca3af;
            text-transform: uppercase;
        }

        .course-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #4e73df;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }

        .course-link:hover {
            color: #36b9cc;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5e7eb;
            background: white;
            color: #6b7280;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-action:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-action.preview:hover {
            background: #8b5cf6;
            border-color: #8b5cf6;
            color: white;
        }

        .btn-action.view:hover {
            background: #4e73df;
            border-color: #4e73df;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 40px;
        }

        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .empty-state h5 {
            color: #374151;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 20px;
        }

        .uploaded-by {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #6b7280;
        }

        .uploaded-by .avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 600;
            color: #6b7280;
        }

        .date-info {
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Learning Space
        @endslot
        @slot('li_1_url')
            {{ route('lms.courses.index') }}
        @endslot
        @slot('li_2')
            Content
        @endslot
        @slot('li_2_url')
            {{ route('lms.courses.index') }}
        @endslot
        @slot('title')
            SCORM Packages
        @endslot
    @endcomponent

    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h4><i class="fas fa-cube me-2"></i>SCORM Packages</h4>
            <p>Manage and monitor SCORM content packages</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('lms.settings.index') }}" class="btn-header">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>
    </div>

    <div class="card packages-card">
        @if($packages->count() > 0)
            <div class="table-responsive">
                <table class="table packages-table">
                    <thead>
                        <tr>
                            <th>Package</th>
                            <th>Version</th>
                            <th>Course</th>
                            <th>Statistics</th>
                            <th>Uploaded By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $package)
                            <tr>
                                <td>
                                    <div class="package-info">
                                        <div class="package-icon">
                                            <i class="fas fa-cube"></i>
                                        </div>
                                        <div class="package-details">
                                            <h6>{{ $package->title }}</h6>
                                            <small>{{ Str::limit($package->description, 40) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="version-badge {{ $package->is_scorm_12 ? 'version-12' : 'version-2004' }}">
                                        {{ $package->is_scorm_12 ? 'SCORM 1.2' : 'SCORM 2004' }}
                                    </span>
                                </td>
                                <td>
                                    @if($package->contentItem && $package->contentItem->module && $package->contentItem->module->course)
                                        <a href="{{ route('lms.courses.show', $package->contentItem->module->course) }}" class="course-link">
                                            <i class="fas fa-book"></i>
                                            {{ Str::limit($package->contentItem->module->course->title, 25) }}
                                        </a>
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="stats-group">
                                        <div class="stat-item">
                                            <span class="stat-value">{{ $package->attempts_count ?? 0 }}</span>
                                            <span class="stat-label">Attempts</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="uploaded-by">
                                        <div class="avatar">
                                            {{ $package->uploader ? strtoupper(substr($package->uploader->name, 0, 2)) : 'NA' }}
                                        </div>
                                        <span>{{ $package->uploader->name ?? 'Unknown' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="date-info">
                                        {{ $package->created_at->format('M d, Y') }}<br>
                                        <small>{{ $package->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('lms.scorm.preview', $package) }}" class="btn-action preview" title="Preview">
                                            <i class="fas fa-play"></i>
                                        </a>
                                        <a href="{{ route('lms.scorm.show', $package) }}" class="btn-action view" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('lms.scorm.edit', $package) }}" class="btn-action" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($packages->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $packages->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="fas fa-cube"></i>
                <h5>No SCORM Packages Yet</h5>
                <p>SCORM packages will appear here once they are uploaded to course modules.</p>
                <a href="{{ route('lms.courses.index') }}" class="btn btn-primary">
                    <i class="fas fa-book me-2"></i> Browse Courses
                </a>
            </div>
        @endif
    </div>
@endsection
