@extends('layouts.master')

@section('title', 'Create Rubric')

@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #3b82f6;
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
            line-height: 1.4;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin: 24px 0 16px 0;
            color: #1f2937;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-group {
            margin-bottom: 16px;
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
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .form-check-input {
            border-radius: 3px;
        }

        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .text-danger {
            color: #dc2626;
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
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
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

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #f3f4f6;
            margin-top: 32px;
        }

        /* Summary Card */
        .summary-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .summary-card h3 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin: 0 0 12px 0;
        }

        .step-indicator {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .step-number {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
        }

        .step-number.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .step-number.pending {
            background: #e5e7eb;
            color: #6b7280;
        }

        .step-label {
            font-size: 13px;
            color: #374151;
        }

        .step-label.pending {
            color: #9ca3af;
        }

        .step-connector {
            flex: 1;
            height: 2px;
            background: #e5e7eb;
        }

        .alert {
            border-radius: 3px;
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
            <a class="text-muted font-size-14" href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            Create Rubric
        @endslot
    @endcomponent

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('lms.rubrics.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="form-container">
                    <div class="page-header">
                        <h1 class="page-title">Create Rubric</h1>
                    </div>

                    <div class="help-text">
                        <div class="help-title">Step 1: Basic Information</div>
                        <div class="help-content">
                            Start by entering the rubric name and description. After creating the rubric,
                            you'll be able to add grading criteria and performance levels.
                        </div>
                    </div>

                    <h3 class="section-title">Rubric Details</h3>

                    <div class="form-group">
                        <label class="form-label">Rubric Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title') }}" placeholder="e.g., Essay Writing Rubric" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                            placeholder="Brief description of this rubric's purpose and when to use it">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_template" id="isTemplate"
                                value="1" {{ old('is_template') ? 'checked' : '' }}>
                            <label class="form-check-label" for="isTemplate">
                                Save as Template
                                <small class="text-muted d-block">Templates are visible to other instructors and can be
                                    reused</small>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('lms.rubrics.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i> Create & Add Criteria
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="summary-card">
                    <h3><i class="fas fa-list-ol me-2"></i>Creation Steps</h3>
                    <div class="step-indicator">
                        <div class="step">
                            <span class="step-number active">1</span>
                            <span class="step-label">Basic Info</span>
                        </div>
                        <div class="step-connector"></div>
                        <div class="step">
                            <span class="step-number pending">2</span>
                            <span class="step-label pending">Add Criteria</span>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">
                        After creating the rubric, you'll be taken to the edit page where you can add
                        grading criteria and define performance levels.
                    </p>
                </div>

                <div class="summary-card">
                    <h3><i class="fas fa-lightbulb me-2"></i>Tips</h3>
                    <ul class="text-muted small mb-0" style="padding-left: 20px;">
                        <li class="mb-2">Choose a descriptive title that identifies the rubric's purpose</li>
                        <li class="mb-2">Add a description to help others understand when to use it</li>
                        <li>Save as template if you want to reuse it across courses</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
@endsection
