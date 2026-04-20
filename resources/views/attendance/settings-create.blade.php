@extends('layouts.master')
@section('title')
    Add Attendance Code | Attendance Settings
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

        /* Color Picker */
        .color-picker-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .color-picker-wrapper input[type="color"] {
            width: 40px;
            height: 40px;
            padding: 0;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            cursor: pointer;
        }

        .color-picker-wrapper input[type="text"] {
            width: 100px;
            font-family: monospace;
        }

        /* Preview Badge */
        .code-preview {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-top: 16px;
        }

        .code-preview-label {
            font-size: 13px;
            color: #6b7280;
        }

        .code-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 28px;
            padding: 0 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }

        /* Form Check Custom */
        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
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
            <a href="{{ route('attendance.index') }}">Attendance</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('attendance.settings') }}">Settings</a>
        @endslot
        @slot('title')
            Add Attendance Code
        @endslot
    @endcomponent

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
                    <h3><i class="fas fa-plus-circle me-2"></i>Add Attendance Code</h3>
                    <p>Create a new attendance code for tracking student attendance</p>
                </div>

                <div class="settings-body">
                    <div class="help-text">
                        <div class="help-title">About Attendance Codes</div>
                        <div class="help-content">
                            Define custom attendance codes to track different attendance statuses. Each code should have
                            a unique identifier, description, and color for easy identification.
                        </div>
                    </div>

                    <form action="{{ route('attendance.codes.store') }}" method="POST" id="codeForm">
                        @csrf

                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fas fa-info-circle me-2"></i>Code Details</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" id="code" class="form-control"
                                        placeholder="e.g., P, A1, L" maxlength="10" value="{{ old('code') }}" required>
                                    <div class="form-text">Short code (max 10 characters)</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                    <input type="text" name="description" id="description" class="form-control"
                                        placeholder="e.g., Present, Absent - Sick" maxlength="100" value="{{ old('description') }}" required>
                                    <div class="form-text">What this code means</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="color" class="form-label">Color <span class="text-danger">*</span></label>
                                    <div class="color-picker-wrapper">
                                        <input type="color" id="colorPicker" value="{{ old('color', '#3b82f6') }}">
                                        <input type="text" name="color" id="color" class="form-control"
                                            value="{{ old('color', '#3b82f6') }}" pattern="^#[0-9A-Fa-f]{6}$" required>
                                    </div>
                                    <div class="form-text">Badge color for this code</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Type</label>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" class="form-check-input" id="is_present" name="is_present" value="1" {{ old('is_present') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_present">
                                            This code represents "Present"
                                        </label>
                                    </div>
                                    <div class="form-text">Check if this code counts as being present</div>
                                </div>
                            </div>

                            <div class="code-preview">
                                <span class="code-preview-label">Preview:</span>
                                <span class="code-badge" id="previewBadge" style="background-color: #3b82f6;">P</span>
                                <span class="text-muted" id="previewDescription">Present</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('attendance.settings') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Settings
                            </a>
                            <button type="submit" class="btn btn-primary btn-loading" id="submitBtn">
                                <span class="btn-text"><i class="fas fa-save me-1"></i> Add Code</span>
                                <span class="btn-spinner">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Saving...
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
        $(document).ready(function() {
            // Sync color picker with text input
            $('#colorPicker').on('input', function() {
                $('#color').val(this.value);
                updatePreview();
            });

            $('#color').on('input', function() {
                if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                    $('#colorPicker').val(this.value);
                }
                updatePreview();
            });

            // Update preview when code or description changes
            $('#code').on('input', updatePreview);
            $('#description').on('input', updatePreview);

            function updatePreview() {
                const code = $('#code').val() || 'P';
                const description = $('#description').val() || 'Present';
                const color = $('#color').val() || '#3b82f6';

                $('#previewBadge').text(code).css('background-color', color);
                $('#previewDescription').text(description);
            }

            // Form submit loading state
            const codeForm = document.getElementById('codeForm');
            const submitBtn = document.getElementById('submitBtn');

            if (codeForm && submitBtn) {
                codeForm.addEventListener('submit', function() {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                });
            }
        });
    </script>
@endsection
