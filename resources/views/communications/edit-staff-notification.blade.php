@extends('layouts.master')
@section('title')
    Notifications | Edit Notification
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

        .list-group-item {
            transition: background-color 0.2s ease;
            border-radius: 3px;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
        }

        .list-group {
            border-radius: 3px;
        }

        .alert {
            border-radius: 3px;
            border: none;
        }

        /* Custom File Input */
        .custom-file-input {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .custom-file-input input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #fff;
            border: 2px dashed #d1d5db;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .file-input-label:hover {
            border-color: #4e73df;
            background: #f0f9ff;
        }

        .file-input-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .file-input-text {
            flex: 1;
        }

        .file-input-text .file-label {
            font-weight: 500;
            color: #374151;
            display: block;
            margin-bottom: 2px;
        }

        .file-input-text .file-hint {
            font-size: 13px;
            color: #6b7280;
        }

        .file-input-text .file-selected {
            font-size: 13px;
            color: #4e73df;
            font-weight: 500;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('notifications.index') }}">Communications</a>
        @endslot
        @slot('title')
            Edit Staff Notification
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
                    <h4>Edit Staff Notification</h4>
                    <p>Update notification details for staff members</p>
                </div>
                <div class="admissions-body">
                    <div class="help-text">
                        <i class="fas fa-info-circle me-2"></i>
                        Edit the notification details below. Changes will be reflected for all recipients immediately.
                    </div>

                    <form class="needs-validation" method="POST"
                        action="{{ route('notifications.notification-update', $notification->id) }}"
                        enctype="multipart/form-data" novalidate id="notificationForm">
                        @csrf
                        @method('POST')
                        <div class="row">
                            <div class="col-md-12">
                                <!-- Notification Title -->
                                <div class="mb-3">
                                    <label class="form-label" for="notification_title">Notification Title <span style="color:red;">*</span></label>
                                    <input type="text" name="notification_title" class="form-control"
                                        placeholder="Enter notification title..." value="{{ old('notification_title', $notification->title) }}" required>
                                    @error('notification_title')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Notification Body -->
                                <div class="mb-3">
                                    <label class="form-label" for="notification_body">Notification Body <span style="color:red;">*</span></label>
                                    <textarea name="notification_body" id="ckeditor-classic" class="form-control" cols="100" rows="6" required>{{ old('notification_body', $notification->body) }}</textarea>
                                    @error('notification_body')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <!-- Start Date -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Start Date</label>
                                            <input class="form-control" type="date" name="start_date" value="{{ old('start_date', $notification->start_date ?? '') }}">
                                            @error('start_date')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- End Date -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label">End Date</label>
                                            <input class="form-control" type="date" name="end_date" value="{{ old('end_date', $notification->end_date ?? '') }}">
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
                                                <input class="form-check-input" name="is_general" type="checkbox" value="1" id="is_general_checkbox" {{ old('is_general', $notification->is_general) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_general_checkbox">
                                                    General (Send to all staff)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Allow Comments -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" name="allow_comments" type="checkbox" value="1" id="allow_comments_checkbox" {{ old('allow_comments', $notification->allow_comments) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="allow_comments_checkbox">
                                                    Allow Comments
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Department Filter -->
                                <div class="mb-3 filter-field" id="department_filter">
                                    <label class="form-label" for="department_id">Department</label>
                                    <select name="department_id" class="form-select">
                                        <option value="" selected>Select Department ...</option>
                                        @if (!empty($departments))
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}" {{ old('department_id', $notification->department_id) == $department->id ? 'selected' : '' }}>
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- Area Of Work Filter -->
                                <div class="mb-3 filter-field" id="area_of_work_filter">
                                    <label class="form-label" for="area_of_work">Area Of Work</label>
                                    <select name="area_of_work" class="form-select">
                                        <option value="" selected>Select Area Of Work ...</option>
                                        @if (!empty($areaOfWork))
                                            @foreach ($areaOfWork as $work)
                                                <option value="{{ $work->name }}" {{ old('area_of_work', $notification->area_of_work) == $work->name ? 'selected' : '' }}>
                                                    {{ $work->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- Existing Attachments -->
                                <div class="mb-3">
                                    <label class="form-label">Existing Attachments</label>
                                    <div class="list-group">
                                        @forelse ($notification->attachments as $attachment)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        $fileType = $attachment->file_type;
                                                        $isImage = str_starts_with($fileType, 'image/');
                                                    @endphp

                                                    @if ($isImage)
                                                        <i class="bx bx-image text-info fs-4 me-2"></i>
                                                    @else
                                                        @switch($fileType)
                                                            @case('application/pdf')
                                                                <i class="bx bxs-file-pdf text-danger fs-4 me-2"></i>
                                                            @break

                                                            @case('application/msword')
                                                            @case('application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                                                                <i class="bx bxs-file-doc text-primary fs-4 me-2"></i>
                                                            @break

                                                            @case('application/vnd.ms-excel')
                                                            @case('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                                                                <i class="bx bxs-file-xlsx text-success fs-4 me-2"></i>
                                                            @break

                                                            @default
                                                                <i class="bx bxs-file text-secondary fs-4 me-2"></i>
                                                        @endswitch
                                                    @endif

                                                    <div>
                                                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="text-decoration-none fw-medium">
                                                            {{ $attachment->original_name }}
                                                        </a>
                                                        <small class="text-muted d-block">
                                                            @if (Storage::disk('public')->exists($attachment->file_path))
                                                                {{ number_format(Storage::disk('public')->size($attachment->file_path) / 1024, 2) }} KB
                                                            @endif
                                                            | {{ Str::upper(pathinfo($attachment->original_name, PATHINFO_EXTENSION)) }}
                                                        </small>
                                                    </div>
                                                </div>

                                                <a class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this attachment?')"
                                                    href="{{ route('notification.destroy-attachment', ['notificationId' => $notification->id, 'attachmentId' => $attachment->id]) }}">
                                                    <i class="bx bx-trash"></i>
                                                </a>
                                            </div>
                                        @empty
                                            <div class="list-group-item text-muted">
                                                <i class="bx bx-info-circle me-2"></i>No attachments available
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <!-- New Attachment -->
                                <div class="mb-4">
                                    <label class="form-label">Upload New Attachment</label>
                                    <div class="custom-file-input">
                                        <input type="file" name="attachment" id="attachment_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">
                                        <label for="attachment_file" class="file-input-label">
                                            <div class="file-input-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <div class="file-input-text">
                                                <span class="file-label">Choose File</span>
                                                <span class="file-hint" id="fileHint">PDF, DOC, XLS, or image files (Max 5MB)</span>
                                                <span class="file-selected d-none" id="fileName"></span>
                                            </div>
                                        </label>
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
                                            <span class="btn-text"><i class="fas fa-save me-1"></i> Update Notification</span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2"></span>
                                                Updating...
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
            const departmentFilter = document.getElementById('department_filter');
            const areaOfWorkFilter = document.getElementById('area_of_work_filter');
            const form = document.getElementById('notificationForm');
            const submitBtn = document.getElementById('submitBtn');

            function toggleFilters() {
                if (isGeneralCheckbox.checked) {
                    departmentFilter.style.display = 'none';
                    areaOfWorkFilter.style.display = 'none';
                    document.querySelector('select[name="department_id"]').value = '';
                    document.querySelector('select[name="area_of_work"]').value = '';
                } else {
                    departmentFilter.style.display = 'block';
                    areaOfWorkFilter.style.display = 'block';
                }
            }

            toggleFilters();
            isGeneralCheckbox.addEventListener('change', toggleFilters);

            // Form submission with loading animation
            form.addEventListener('submit', function() {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });

            // File input handler
            const fileInput = document.getElementById('attachment_file');
            if (fileInput) {
                const fileHint = document.getElementById('fileHint');
                const fileName = document.getElementById('fileName');

                fileInput.addEventListener('change', function(e) {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        fileHint.classList.add('d-none');
                        fileName.classList.remove('d-none');
                        fileName.textContent = file.name;
                    } else {
                        fileHint.classList.remove('d-none');
                        fileName.classList.add('d-none');
                        fileName.textContent = '';
                    }
                });
            }
        });
    </script>
@endsection
