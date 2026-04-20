@extends('layouts.master')
@section('title')
    Edit Grade | Settings
@endsection

@section('css')
    <style>
        .settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .settings-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .settings-header p {
            margin: 6px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .settings-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
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
            line-height: 1.4;
        }

        .form-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .form-section-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-text {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            border: none;
            color: white;
            font-weight: 500;
            padding: 10px 16px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background: #4b5563;
            color: white;
        }

        .btn-loading .btn-text {
            display: inline-flex;
            align-items: center;
        }

        .btn-loading .btn-spinner {
            display: none;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex;
            align-items: center;
        }

        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('setup.grades-setup') }}">Back</a>
        @endslot
        @slot('title')
            Edit Grade
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
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

    @if ($errors->any())
        <div class="row mb-3">
            <div class="col-md-12">
                @foreach ($errors->all() as $error)
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="settings-container">
        <div class="settings-header">
            <h3><i class="fas fa-layer-group me-2"></i>Edit Grade: {{ $grade->name ?? '' }}</h3>
            <p>Update grade configuration and promotion settings</p>
        </div>

        <div class="settings-body">
            <div class="help-text">
                <div class="help-title">About Grades</div>
                <div class="help-content">
                    Grades define the academic levels in your school. Configure the sequence, promotion path,
                    and education level for proper student progression tracking.
                </div>
            </div>

            <form action="{{ route('setup.grade-update', ['gradeId' => $grade->id]) }}" method="POST" id="gradeForm">
                @csrf

                <div class="form-section">
                    <h6 class="form-section-title"><i class="fas fa-info-circle me-2"></i>Grade Information</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sequence" class="form-label">Sequence <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sequence" name="sequence"
                                value="{{ $grade->sequence }}" required>
                            <div class="form-text">Display order for this grade</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Grade Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ $grade->name }}" required>
                            <div class="form-text">Name of the grade (e.g., Grade 1, Form 1)</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ $grade->description }}</textarea>
                            <div class="form-text">Optional description for this grade</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h6 class="form-section-title"><i class="fas fa-arrow-up me-2"></i>Promotion & Classification</h6>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="promotion" class="form-label">Promotes To <span class="text-danger">*</span></label>
                            <select class="form-select" id="promotion" name="promotion">
                                @foreach ($previousTerm as $previousGrade)
                                    <option value="{{ $previousGrade->name }}"
                                        {{ $grade->promotion == $previousGrade->name ? 'selected' : '' }}>
                                        {{ $previousGrade->name }}
                                    </option>
                                @endforeach
                                <option value="Alumni" {{ $grade->promotion == 'Alumni' ? 'selected' : '' }}>Alumni</option>
                            </select>
                            <div class="form-text">Grade students promote to next year</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="level" class="form-label">Education Level <span class="text-danger">*</span></label>
                            <select class="form-select" id="level" name="level">
                                <option value="">Select level...</option>
                                <option {{ $grade->level === 'Pre-primary' ? 'selected' : '' }} value="Pre-primary">Pre-Primary</option>
                                <option {{ $grade->level === 'Primary' ? 'selected' : '' }} value="Primary">Primary</option>
                                <option {{ $grade->level === 'Junior' ? 'selected' : '' }} value="Junior">CJSS</option>
                                <option {{ $grade->level === 'Senior' ? 'selected' : '' }} value="Senior">Senior</option>
                            </select>
                            <div class="form-text">Education level classification</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="active" class="form-label">Active Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="active" name="active">
                                <option {{ $grade->active ? 'selected' : '' }} value="1">Yes - Active</option>
                                <option {{ !$grade->active ? 'selected' : '' }} value="0">No - Inactive</option>
                            </select>
                            <div class="form-text">Whether this grade is currently in use</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('setup.grades-setup') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                    @can('view-system-admin')
                        @if (!session('is_past_term'))
                            <button type="submit" class="btn btn-primary btn-loading" id="submitBtn">
                                <span class="btn-text"><i class="fas fa-save"></i> Update Grade</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Updating...
                                </span>
                            </button>
                        @endif
                    @endcan
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('gradeForm');
            const submitBtn = document.getElementById('submitBtn');

            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                });
            }
        });
    </script>
@endsection
