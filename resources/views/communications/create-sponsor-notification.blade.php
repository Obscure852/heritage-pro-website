@extends('layouts.master')

@section('title')
    Notifications | New Notification
@endsection

@section('css')
    <style>
        .admissions-container {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admissions-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .admissions-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 20px;
        }

        .admissions-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .admissions-body {
            padding: 24px;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
            border-left: 4px solid #4e73df;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
            font-size: 13px;
            color: #555;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control, .form-select {
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-secondary {
            background: #6b7280;
            border: none;
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 3px;
        }

        .btn-loading.loading .btn-text {
            display: none;
        }

        .btn-loading.loading .btn-spinner {
            display: inline-flex !important;
        }

        .btn-loading:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .file-upload-wrapper {
            position: relative;
            width: 100%;
        }

        .file-upload-box {
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
            cursor: pointer;
        }

        .file-upload-box:hover {
            border-color: #3b82f6;
            background-color: #f0f7ff;
        }

        .file-upload-box.dragover {
            border-color: #3b82f6;
            background-color: rgba(59, 130, 246, 0.05);
        }

        .upload-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .selected-file {
            margin-top: 0.5rem;
            font-weight: 500;
            color: #3b82f6;
            word-break: break-all;
        }

        .alert {
            border-radius: 3px;
            border: none;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('notifications.index') }}">Communications</a>
        @endslot
        @slot('title')
            New Sponsor Notification
        @endslot
    @endcomponent

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-alert-circle-outline me-2"></i>{{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endforeach
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle-outline me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-all me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="admissions-container">
                <div class="admissions-header">
                    <h4>Create Sponsor Notification</h4>
                    <p>Send a notification to parents/sponsors</p>
                </div>
                <div class="admissions-body">
                    <div class="help-text">
                        <i class="fas fa-info-circle me-2"></i>
                        Create a notification for parents/sponsors. You can filter recipients using custom filters, or send to all sponsors by checking "General".
                    </div>

                    <form class="needs-validation" method="post"
                        action="{{ route('notifications.store-sponsors-notification') }}" enctype="multipart/form-data"
                        novalidate id="notificationForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <!-- Notification Title -->
                                <div class="mb-3">
                                    <label class="form-label" for="notification_title">Notification Title <span style="color:red;">*</span></label>
                                    <input type="text" name="notification_title" class="form-control"
                                        placeholder="Enter notification title..." value="{{ old('notification_title') }}" required>
                                    @error('notification_title')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Notification Body -->
                                <div class="mb-3">
                                    <label class="form-label" for="notification_body">Notification Body <span style="color:red;">*</span></label>
                                    <textarea name="notification_body" id="ckeditor-classic" class="form-control" cols="100" rows="6" required>{{ old('notification_body') }}</textarea>
                                    @error('notification_body')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <!-- Start Date -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Start Date <span style="color:red;">*</span></label>
                                            <input class="form-control" type="date" name="start_date" value="{{ old('start_date') }}" required>
                                            @error('start_date')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- End Date -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label">End Date <span style="color:red;">*</span></label>
                                            <input class="form-control" type="date" name="end_date" value="{{ old('end_date') }}" required>
                                            @error('end_date')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Is General -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" name="is_general" type="checkbox" value="1" id="is_general_checkbox" {{ old('is_general') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_general_checkbox">
                                                    General (Send to all sponsors)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Allow Comments -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" name="allow_comments" type="checkbox" value="1" id="allow_comments_checkbox" {{ old('allow_comments') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="allow_comments_checkbox">
                                                    Allow Comments
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sponsor Filter -->
                                <div class="mb-3 filter-field" id="filter">
                                    <label class="form-label" for="filter">Sponsor Filter</label>
                                    <select name="filter" class="form-select">
                                        <option value="" selected>Select Filter ...</option>
                                        @if (!empty($filters))
                                            @foreach ($filters as $filter)
                                                <option value="{{ $filter->id }}" {{ old('filter') == $filter->id ? 'selected' : '' }}>
                                                    {{ $filter->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- Enhanced Attachment Upload -->
                                <div class="mb-4">
                                    <label for="attachment" class="form-label">
                                        Attachment <span class="text-muted">(Optional)</span>
                                    </label>
                                    <div class="file-upload-wrapper">
                                        <input type="file" class="file-upload-input" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.jpg,.png" hidden>
                                        <div class="file-upload-box">
                                            <div class="upload-content">
                                                <i class="bx bx-upload fs-2 mb-2 text-muted"></i>
                                                <div class="file-message">Drop files here or click to upload</div>
                                                <div class="selected-file"></div>
                                                <div class="text-muted small mt-1">
                                                    Accepted formats: pdf, doc, docx, jpg, png
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @error('attachment')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Form Buttons -->
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="btn btn-secondary" href="{{ route('notifications.index') }}">
                                        <i class="bx bx-arrow-back me-1"></i> Back
                                    </a>
                                    @if (!session('is_past_term'))
                                        <button type="submit" class="btn btn-primary btn-loading" id="submitBtn">
                                            <span class="btn-text"><i class="fas fa-save me-1"></i> Create Notification</span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2"></span>
                                                Creating...
                                            </span>
                                        </button>
                                    @endif
                                </div>
                            </div>
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
            const isGeneralCheckbox = document.getElementById('is_general_checkbox');
            const sponsorFilter = document.getElementById('filter');
            const fileInput = document.getElementById('attachment');
            const fileBox = document.querySelector('.file-upload-box');
            const fileMessage = document.querySelector('.file-message');
            const selectedFile = document.querySelector('.selected-file');
            const form = document.getElementById('notificationForm');
            const submitBtn = document.getElementById('submitBtn');

            // Toggle filter visibility based on checkbox
            function toggleFilters() {
                if (isGeneralCheckbox.checked) {
                    sponsorFilter.style.display = 'none';
                    const filterSelect = sponsorFilter.querySelector('select[name="filter"]');
                    if (filterSelect) {
                        filterSelect.value = '';
                    }
                } else {
                    sponsorFilter.style.display = 'block';
                }
            }

            toggleFilters();
            isGeneralCheckbox.addEventListener('change', toggleFilters);

            // File upload handling
            fileBox.addEventListener('click', () => fileInput.click());

            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    selectedFile.textContent = this.files[0].name;
                    fileMessage.style.display = 'none';
                } else {
                    selectedFile.textContent = '';
                    fileMessage.style.display = 'block';
                }
            });

            // Drag and drop handlers
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                fileBox.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                fileBox.addEventListener(eventName, () => {
                    fileBox.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                fileBox.addEventListener(eventName, () => {
                    fileBox.classList.remove('dragover');
                }, false);
            });

            fileBox.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                fileInput.files = files;

                if (files && files[0]) {
                    selectedFile.textContent = files[0].name;
                    fileMessage.style.display = 'none';
                }
            }, false);

            // Initialize CKEditor with placeholder
            ClassicEditor.create(document.querySelector('#ckeditor-classic'), {
                placeholder: 'Notification Body'
            }).then(function(editor) {
                editor.ui.view.editable.element.style.height = '200px';
            }).catch(function(error) {
                console.error(error);
            });

            // Form submission with loading animation
            form.addEventListener('submit', function() {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });
        });
    </script>
@endsection
