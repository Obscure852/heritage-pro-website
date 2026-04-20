@extends('layouts.master')
@section('title')
    New Subject | Academic Management
@endsection

@section('css')
    <style>
        /* Main Container */
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

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Form Group */
        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-group label .required {
            color: #dc2626;
        }

        .form-group .form-control,
        .form-group .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
            width: 100%;
        }

        .form-group .form-control:focus,
        .form-group .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-group .form-control::placeholder {
            color: #9ca3af;
        }

        .form-group .form-hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .form-group .form-hint i {
            color: #3b82f6;
        }

        .form-group .text-danger {
            font-size: 13px;
            margin-top: 4px;
        }

        /* Full Width Form Group */
        .form-group.full-width {
            grid-column: 1 / -1;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-back:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-save:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-save.loading .btn-text {
            display: none;
        }

        .btn-save.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-save:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .settings-header {
                padding: 20px;
            }

            .settings-body {
                padding: 16px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-back,
            .btn-save {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('subjects.index') }}">Subjects</a>
        @endslot
        @slot('title')
            New Subject
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

    <div class="row">
        <div class="col-12">
            <div class="settings-container">
                <div class="settings-header">
                    <h3><i class="fas fa-plus-circle me-2"></i>Add New Subject</h3>
                    <p>Create a new subject assignment for a grade level</p>
                </div>

                <div class="settings-body">
                    <div class="help-text">
                        <div class="help-title">Subject Configuration</div>
                        <div class="help-content">
                            Fill in all required fields to add a new subject. The sequence determines the display order in reports and assessments.
                        </div>
                    </div>

                    <form class="needs-validation" method="post" action="{{ route('subjects.store') }}" novalidate>
                        @csrf

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="sequence">Sequence <span class="required">*</span></label>
                                <select name="sequence" id="sequence" class="form-select" required>
                                    <option value="">Choose ...</option>
                                    @for ($i = 1; $i <= 35; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                                <div class="form-hint">
                                    <i class="mdi mdi-information-outline"></i>
                                    Order in which subjects appear (lower numbers first)
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="grade_id">Grade <span class="required">*</span></label>
                                <select name="grade_id" id="grade_id" class="form-select">
                                    <option value="">Choose ...</option>
                                    @if (!empty($grades))
                                        @foreach ($grades as $grade)
                                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="subject_id">Subject <span class="required">*</span></label>
                                <select name="subject_id" id="subject_id" class="form-select" data-trigger>
                                    <option value="">Choose ...</option>
                                    @if (!empty($subjects))
                                        @foreach ($subjects as $subject)
                                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="department_id">Department <span class="required">*</span></label>
                                <select name="department_id" id="department_id" class="form-select" data-trigger>
                                    <option value="">Choose ...</option>
                                    @if (!empty($departments))
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->name ?? '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="type">Type <span class="required">*</span></label>
                                <select name="type" id="type" class="form-select">
                                    <option value="">Choose ...</option>
                                    <option value="1">Core</option>
                                    <option value="0">Optional</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="mandatory">Mandatory <span class="required">*</span></label>
                                <select name="mandatory" id="mandatory" class="form-select">
                                    <option value="">Choose ...</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <div class="form-group full-width">
                                <label for="active">Active <span class="required">*</span></label>
                                <select name="active" id="active" class="form-select">
                                    <option value="">Choose ...</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                <div class="form-hint">
                                    <i class="mdi mdi-information-outline"></i>
                                    Inactive subjects will not appear in student assessments or reports.
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('subjects.index') }}" class="btn-back">
                                <i class="bx bx-arrow-back"></i> Back
                            </a>
                            <button type="submit" class="btn-save">
                                <span class="btn-text"><i class="fas fa-save"></i> Create Subject</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Creating...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    } else {
                        const btn = form.querySelector('.btn-save');
                        if (btn) {
                            btn.classList.add('loading');
                            btn.disabled = true;
                        }
                    }
                    form.classList.add('was-validated');
                }, false);
            }
        });
    </script>
@endsection
