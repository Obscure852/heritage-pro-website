@extends('layouts.master')

@section('title', 'Analytics Reports')

@section('css')
    <style>
        .page-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .page-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .page-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .page-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.75;
        }

        .page-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #6366f1;
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

        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-title:not(:first-of-type) {
            margin-top: 32px;
        }

        .report-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.2s ease;
        }

        .report-card:hover {
            border-color: #6366f1;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);
        }

        .report-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .report-card-title {
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .report-card-meta {
            font-size: 13px;
            color: #6b7280;
        }

        .report-type-icon {
            width: 44px;
            height: 44px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 16px;
            flex-shrink: 0;
        }

        .report-type-icon.enrollment {
            background: #dbeafe;
            color: #2563eb;
        }

        .report-type-icon.progress {
            background: #d1fae5;
            color: #059669;
        }

        .report-type-icon.engagement {
            background: #fef3c7;
            color: #d97706;
        }

        .report-type-icon.completion {
            background: #ede9fe;
            color: #7c3aed;
        }

        .report-type-icon.quiz {
            background: #fee2e2;
            color: #dc2626;
        }

        .report-type-icon.activity {
            background: #cffafe;
            color: #0891b2;
        }

        .quick-report-btn {
            display: flex;
            align-items: center;
            padding: 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s ease;
            margin-bottom: 12px;
        }

        .quick-report-btn:hover {
            background: #fff;
            border-color: #6366f1;
            color: inherit;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);
        }

        .quick-report-btn .icon {
            width: 40px;
            height: 40px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 14px;
            font-size: 16px;
        }

        .quick-report-btn .icon.blue {
            background: #dbeafe;
            color: #2563eb;
        }

        .quick-report-btn .icon.green {
            background: #d1fae5;
            color: #059669;
        }

        .quick-report-btn .icon.purple {
            background: #ede9fe;
            color: #7c3aed;
        }

        .quick-report-btn .icon.orange {
            background: #ffedd5;
            color: #ea580c;
        }

        .quick-report-btn h6 {
            margin: 0 0 2px 0;
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        .quick-report-btn p {
            margin: 0;
            font-size: 12px;
            color: #6b7280;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-processing {
            background: #fef3c7;
            color: #92400e;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-pending {
            background: #e5e7eb;
            color: #374151;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.4;
        }

        .empty-state p {
            margin: 0;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            color: white;
        }

        .modal-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-title {
            font-weight: 600;
            color: #1f2937;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('title')
            Analytics Reports
        @endslot
    @endcomponent

    <div class="page-container">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3><i class="fas fa-file-alt me-2"></i>Analytics Reports</h3>
                    <p>Generate and manage LMS analytics reports</p>
                </div>
                <div class="col-md-6">
                    @php
                        $totalReports = $generatedReports->count();
                        $completedReports = $generatedReports->where('status', 'completed')->count();
                        $pendingReports = $generatedReports->whereIn('status', ['pending', 'processing'])->count();
                        $totalTemplates = $definitions->count();
                    @endphp
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="text-white">{{ $totalReports }}</h4>
                                <small>Total</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="text-white">{{ $completedReports }}</h4>
                                <small>Completed</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="text-white">{{ $pendingReports }}</h4>
                                <small>Pending</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="stat-item">
                                <h4 class="text-white">{{ $totalTemplates }}</h4>
                                <small>Templates</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="help-text">
                <div class="help-title">Reports Dashboard</div>
                <p class="help-content">
                    Generate comprehensive analytics reports for your LMS. Use Quick Reports for common report types,
                    or create custom report templates for recurring needs. Reports can be exported in PDF or Excel format.
                </p>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                    <i class="fas fa-bookmark me-2"></i>Create Template
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateReportModal">
                    <i class="fas fa-plus me-2"></i>Generate Report
                </button>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Quick Reports -->
                    <h6 class="section-title">Quick Reports</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <a href="javascript:void(0)" class="quick-report-btn"
                                onclick="generateQuickReport('enrollment_summary', event)">
                                <div class="icon blue">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <h6>Enrollment Summary</h6>
                                    <p>Overview of all course enrollments</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="javascript:void(0)" class="quick-report-btn"
                                onclick="generateQuickReport('progress_report', event)">
                                <div class="icon green">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <h6>Progress Report</h6>
                                    <p>Student progress across courses</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="javascript:void(0)" class="quick-report-btn"
                                onclick="generateQuickReport('completion_report', event)">
                                <div class="icon purple">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div>
                                    <h6>Completion Report</h6>
                                    <p>Course completion statistics</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="javascript:void(0)" class="quick-report-btn"
                                onclick="generateQuickReport('engagement_report', event)">
                                <div class="icon orange">
                                    <i class="fas fa-fire"></i>
                                </div>
                                <div>
                                    <h6>Engagement Report</h6>
                                    <p>Student engagement metrics</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Reports -->
                    <h6 class="section-title">Recent Reports</h6>
                    @forelse($generatedReports as $report)
                        <div class="report-card">
                            <div class="d-flex align-items-start">
                                <div class="report-type-icon {{ $report->type ?? 'activity' }}">
                                    @switch($report->status)
                                        @case('completed')
                                            <i class="fas fa-file-alt"></i>
                                        @break

                                        @case('processing')
                                            <i class="fas fa-spinner fa-spin"></i>
                                        @break

                                        @case('pending')
                                            <i class="fas fa-clock"></i>
                                        @break

                                        @case('failed')
                                            <i class="fas fa-exclamation-circle text-danger"></i>
                                        @break

                                        @default
                                            <i class="fas fa-file"></i>
                                    @endswitch
                                </div>
                                <div class="flex-grow-1">
                                    <div class="report-card-header">
                                        <div>
                                            <h6 class="report-card-title">{{ $report->name }}</h6>
                                            <div class="report-card-meta">
                                                {{ ucfirst($report->format) }} &bull;
                                                {{ $report->created_at->format('M d, Y \a\t g:i A') }}
                                            </div>
                                        </div>
                                        <span class="status-badge status-{{ $report->status }}">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                    </div>
                                    @if ($report->status === 'failed' && $report->error_message)
                                        <div class="alert alert-danger py-2 px-3 mb-2 mt-2" style="font-size: 12px;">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $report->error_message }}
                                        </div>
                                    @endif
                                    <div class="d-flex gap-2 flex-wrap">
                                        @if ($report->status === 'completed')
                                            <a href="{{ route('lms.analytics.download-report', $report) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download me-1"></i>Download
                                            </a>
                                        @endif
                                        @if (in_array($report->status, ['pending', 'failed']))
                                            <form action="{{ route('lms.analytics.retry-report', $report) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-redo me-1"></i>Retry
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('lms.analytics.delete-report', $report) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this report?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash me-1"></i>Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-file-alt"></i>
                                <p>No reports generated yet. Click "Generate Report" to create your first report.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="col-lg-4">
                        <!-- Saved Templates -->
                        <h6 class="section-title">Saved Templates</h6>
                        @forelse($definitions as $definition)
                            <div class="report-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="report-card-title">{{ $definition->name }}</h6>
                                        <div class="report-card-meta">
                                            {{ \App\Models\Lms\ReportDefinition::$reportTypes[$definition->type] ?? $definition->type }}
                                        </div>
                                    </div>
                                    <div>
                                        @if ($definition->is_public)
                                            <span class="badge bg-info">Public</span>
                                        @endif
                                        @if ($definition->schedule)
                                            <span class="badge bg-secondary">{{ ucfirst($definition->schedule) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-primary"
                                    onclick="useTemplate({{ $definition->id }}, '{{ $definition->name }}', '{{ $definition->type }}')">
                                    <i class="fas fa-play me-1"></i>Run Report
                                </button>
                            </div>
                        @empty
                            <div class="empty-state" style="padding: 32px 16px;">
                                <i class="fas fa-bookmark" style="font-size: 32px;"></i>
                                <p class="mt-2">No saved templates</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Generate Report Modal -->
        <div class="modal fade" id="generateReportModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('lms.analytics.generate-report') }}" method="POST">
                        @csrf
                        <input type="hidden" name="definition_id" id="definitionId">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-file-export me-2"></i>Generate Report</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Report Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="reportName" class="form-control" required
                                    placeholder="e.g. Monthly Progress Report">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Report Type <span class="text-danger">*</span></label>
                                <select name="type" id="reportType" class="form-select" required>
                                    @foreach (\App\Models\Lms\ReportDefinition::$reportTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Format</label>
                                <select name="format" class="form-select">
                                    @foreach (\App\Models\Lms\GeneratedReport::$formats as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-cog me-1"></i>Generate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Create Template Modal -->
        <div class="modal fade" id="createTemplateModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('lms.analytics.create-report') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-bookmark me-2"></i>Create Report Template</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Template Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required
                                    placeholder="e.g. Weekly Engagement Summary">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"
                                    placeholder="Brief description of this report template"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Report Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    @foreach (\App\Models\Lms\ReportDefinition::$reportTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Schedule (Optional)</label>
                                <select name="schedule" class="form-select">
                                    <option value="">No Schedule - Manual Only</option>
                                    @foreach (\App\Models\Lms\ReportDefinition::$schedules as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="is_public" class="form-check-input" value="1"
                                    id="isPublic">
                                <label class="form-check-label" for="isPublic">Make publicly available to other staff</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Save Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endsection

    @section('script')
        <script>
            function useTemplate(definitionId, name, type) {
                document.getElementById('definitionId').value = definitionId;
                document.getElementById('reportName').value = name + ' - ' + new Date().toLocaleDateString();
                document.getElementById('reportType').value = type;
                var modal = new bootstrap.Modal(document.getElementById('generateReportModal'));
                modal.show();
            }

            function generateQuickReport(type, e) {
                if (e) e.preventDefault();

                // Map quick report types to actual report types
                var typeMapping = {
                    'enrollment_summary': 'course_progress',
                    'progress_report': 'course_progress',
                    'completion_report': 'completion',
                    'engagement_report': 'engagement'
                };
                var typeLabels = {
                    'enrollment_summary': 'Enrollment Summary',
                    'progress_report': 'Progress Report',
                    'completion_report': 'Completion Report',
                    'engagement_report': 'Engagement Report'
                };

                var reportName = document.getElementById('reportName');
                var reportType = document.getElementById('reportType');

                if (reportName) {
                    reportName.value = typeLabels[type] + ' - ' + new Date().toLocaleDateString();
                }
                if (reportType) {
                    reportType.value = typeMapping[type] || type;
                }

                var modalEl = document.getElementById('generateReportModal');
                if (modalEl) {
                    var modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }

                return false;
            }
        </script>
    @endsection
