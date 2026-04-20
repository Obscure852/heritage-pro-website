@extends('layouts.master')

@section('title')
    Edit SCORM Package - Learning Space
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
        }

        .page-header p {
            margin: 8px 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .header-stats {
            display: flex;
            gap: 48px;
        }

        .header-stat {
            padding: 10px 0;
            text-align: center;
        }

        .header-stat h4 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            color: white;
        }

        .header-stat small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-title:first-of-type {
            margin-top: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .form-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        @media (max-width: 992px) {
            .form-grid-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .form-grid,
            .form-grid-3 {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        .form-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .required {
            color: #dc2626;
        }

        .info-box {
            background: #f5f3ff;
            border: 1px solid #ddd6fe;
            border-radius: 3px;
            padding: 16px;
            margin: 0;
        }

        .info-box h6 {
            color: #6d28d9;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #ede9fe;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-item .label {
            font-size: 13px;
            color: #6b7280;
        }

        .info-item .value {
            font-size: 13px;
            font-weight: 500;
            color: #1f2937;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 16px;
        }

        .stat-item {
            background: #f9fafb;
            padding: 12px;
            border-radius: 3px;
            text-align: center;
        }

        .stat-item .stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #8b5cf6;
        }

        .stat-item .stat-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-1px);
            color: white;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
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
            Edit Package
        @endslot
    @endcomponent

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4><i class="fas fa-edit me-2"></i>Edit SCORM Package</h4>
                <p>{{ $package->contentItem && $package->contentItem->module ? $package->contentItem->module->title . ' • ' . $package->contentItem->module->course->title : 'Update package settings and metadata' }}</p>
            </div>
            <div class="header-stats">
                <div class="header-stat">
                    <h4>{{ $package->attempts()->count() }}</h4>
                    <small>Attempts</small>
                </div>
                <div class="header-stat">
                    <h4>{{ $package->attempts()->distinct('student_id')->count('student_id') }}</h4>
                    <small>Students</small>
                </div>
                <div class="header-stat">
                    <h4>{{ $package->attempts()->where('completion_status', 'completed')->count() }}</h4>
                    <small>Completed</small>
                </div>
            </div>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="help-text">
        <div class="help-title">Edit Package Settings</div>
        <div class="help-content">
            Update the package title, description, and completion settings. The SCORM package file cannot be changed after upload.
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="form-container">
                <form action="{{ route('lms.scorm.update', $package) }}" method="POST" id="editForm" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')

                    <h3 class="section-title">Package Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Title <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                value="{{ old('title', $package->title) }}" placeholder="Enter content title" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
                                value="{{ old('description', $package->description) }}" placeholder="Brief description of the content">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h3 class="section-title">Completion Settings</h3>
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="form-label">Mastery Score (%)</label>
                            <input type="number" name="mastery_score" class="form-control @error('mastery_score') is-invalid @enderror"
                                value="{{ old('mastery_score', $package->mastery_score) }}" min="0" max="100" placeholder="e.g., 80">
                            <div class="form-text">Score needed to pass</div>
                            @error('mastery_score')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Time Limit (minutes)</label>
                            <input type="number" name="time_limit_minutes" class="form-control @error('time_limit_minutes') is-invalid @enderror"
                                value="{{ old('time_limit_minutes', $package->time_limit_minutes) }}" min="1" placeholder="No limit">
                            <div class="form-text">Optional time limit</div>
                            @error('time_limit_minutes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Max Attempts</label>
                            <input type="number" name="max_attempts" class="form-control @error('max_attempts') is-invalid @enderror"
                                value="{{ old('max_attempts', $package->max_attempts) }}" min="1" placeholder="Unlimited">
                            <div class="form-text">Leave empty for unlimited</div>
                            @error('max_attempts')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('lms.scorm.show', $package) }}" class="btn btn-secondary">
                            <i class="bx bx-x"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-loading" id="submitBtn">
                            <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="form-container" style="padding: 0;">
                <div class="info-box">
                    <h6><i class="fas fa-cube"></i> Package Info</h6>
                    <div class="info-item">
                        <span class="label">Version</span>
                        <span class="value">
                            <span class="version-badge {{ $package->is_scorm_12 ? 'version-12' : 'version-2004' }}">
                                {{ $package->is_scorm_12 ? 'SCORM 1.2' : 'SCORM 2004' }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="label">Created</span>
                        <span class="value">{{ $package->created_at->format('M d, Y') }}</span>
                    </div>
                    @if($package->contentItem && $package->contentItem->module)
                        <div class="info-item">
                            <span class="label">Course</span>
                            <span class="value">{{ Str::limit($package->contentItem->module->course->title ?? 'N/A', 20) }}</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Module</span>
                            <span class="value">{{ Str::limit($package->contentItem->module->title ?? 'N/A', 20) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('editForm');
            const submitBtn = document.getElementById('submitBtn');

            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();

                    const firstInvalidElement = form.querySelector(':invalid');
                    if (firstInvalidElement) {
                        firstInvalidElement.focus();
                        firstInvalidElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                } else {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }

                form.classList.add('was-validated');
            });
        });
    </script>
@endsection
