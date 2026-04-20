@extends('layouts.master')

@section('title')
    Edit Module - {{ $module->title }}
@endsection

@section('css')
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .form-header {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .form-body { padding: 24px; }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
            margin-bottom: 24px;
        }

        .card-header {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .card-title {
            color: #1f2937;
            font-weight: 600;
            font-size: 16px;
            margin: 0;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .form-control, .form-select {
            border: 1px solid #d1d5db;
            border-radius: 3px !important;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 3px !important;
            font-size: 14px;
            font-weight: 500;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
            margin-top: 24px;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        /* Button Loading Animation */
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

        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        /* Helper Text */
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

        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }

            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }

            .action-bar {
                flex-direction: column;
                gap: 12px;
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
            <a href="{{ route('lms.courses.edit', $module->course) }}">{{ $module->course->title }}</a>
        @endslot
        @slot('title')
            Edit Module
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

    <div class="help-text">
        <div class="help-title">Module Settings</div>
        <p class="help-content">Configure module details, set prerequisites, and manage unlock dates. Use the "Manage Content" button to add and organize learning materials for this module.</p>
    </div>

    <div class="action-bar" style="justify-content: flex-end; gap: 12px;">
        <a href="{{ route('lms.assignments.create', $module) }}" class="btn btn-warning">
            <i class="fas fa-tasks me-1"></i> Create Assignment
        </a>
        <a href="{{ route('lms.content.index', $module) }}" class="btn btn-primary">
            <i class="fas fa-list me-1"></i> Manage Content
        </a>
    </div>

    <form action="{{ route('lms.modules.update', $module) }}" method="POST" class="form-container">
        @csrf
        @method('PUT')

        <div class="form-body">
            <div class="mb-3">
                <label class="form-label">Module Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-control"
                    value="{{ old('title', $module->title) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $module->description) }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Unlock Date</label>
                    <input type="datetime-local" name="unlock_date" class="form-control"
                        value="{{ old('unlock_date', $module->unlock_date?->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prerequisite Module</label>
                    <select name="prerequisite_module_id" class="form-select">
                        <option value="">No prerequisite</option>
                        @foreach ($existingModules as $mod)
                            <option value="{{ $mod->id }}"
                                {{ $module->prerequisite_module_id == $mod->id ? 'selected' : '' }}>
                                {{ $mod->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-check mb-0">
                <input type="checkbox" class="form-check-input" name="require_sequential_completion"
                    id="sequentialCompletion" value="1"
                    {{ $module->require_sequential_completion ? 'checked' : '' }}>
                <label class="form-check-label" for="sequentialCompletion">
                    Require Sequential Completion
                </label>
            </div>

            <div class="form-actions" style="justify-content: space-between;">
                <form action="{{ route('lms.modules.destroy', $module) }}" method="POST"
                    onsubmit="return confirm('Delete this module and all its content?');" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Module
                    </button>
                </form>
                <div class="d-flex gap-2">
                    <a href="{{ route('lms.courses.edit', $module->course) }}" class="btn btn-secondary">
                        <i class="bx bx-x me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save me-1"></i> Save Changes</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script>
        // Form submission loading state
        const form = document.querySelector('form[action*="modules"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        }
    </script>
@endsection
