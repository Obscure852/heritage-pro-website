@extends('layouts.master')
@section('title')
    Submit Leave Request
@endsection
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <style>
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .form-body {
            padding: 32px;
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

        .section-title:first-of-type {
            margin-top: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        @media (max-width: 768px) {
            .form-grid {
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
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
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

        .leave-type-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .leave-type-balance {
            font-size: 12px;
            color: #6b7280;
            margin-left: 10px;
        }

        .leave-days-display {
            text-align: center;
        }

        .leave-days-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .leave-days-label {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 4px;
        }

        .stat-item {
            padding: 10px 0;
        }

        .stat-item h4 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .stat-item small {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .quick-tips-card {
            background: white;
            border-radius: 3px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .quick-tips-card h5 {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
        }

        .quick-tips-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .quick-tips-list li {
            font-size: 13px;
            color: #6b7280;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 10px;
        }

        .quick-tips-list li:last-child {
            margin-bottom: 0;
        }

        .quick-tips-list li i {
            color: #10b981;
            margin-top: 2px;
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

        .file-list {
            margin-top: 8px;
        }

        .file-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 3px;
            margin-bottom: 4px;
            font-size: 13px;
        }

        .file-item i {
            color: #3b82f6;
        }

        .file-item .file-name {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #1e40af;
            font-weight: 500;
        }

        .file-item .file-size {
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .stat-item h4 {
                font-size: 1.25rem;
            }

            .stat-item small {
                font-size: 0.75rem;
            }
        }

        .balance-warning {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            color: #92400e;
            padding: 12px;
            border-radius: 4px;
            margin-top: 8px;
            font-size: 13px;
            display: none;
        }

        .balance-warning.show {
            display: block;
        }

        .half-day-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 12px;
            border-radius: 4px;
            margin-top: 16px;
        }

        .half-day-info h5 {
            font-size: 13px;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 8px;
        }

        .half-day-info ul {
            margin: 0;
            padding-left: 18px;
            font-size: 12px;
            color: #3b82f6;
        }

        .half-day-info li {
            margin-bottom: 4px;
        }

        .year-warning {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-left: 4px solid #f59e0b;
            color: #92400e;
            padding: 12px 16px;
            border-radius: 0 3px 3px 0;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .year-warning i {
            font-size: 18px;
            color: #f59e0b;
        }

        .year-warning strong {
            color: #78350f;
        }

        @media (max-width: 768px) {
            .form-body {
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
            <a class="text-muted font-size-14" href="{{ route('leave.requests.index') }}">My Leave Requests</a>
        @endslot
        @slot('title')
            Submit Leave Request
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

    @if ($termYear != $calendarYear)
        <div class="year-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Note:</strong> You are currently in the <strong>{{ $termYear }}</strong> academic year,
                but the calendar year is <strong>{{ $calendarYear }}</strong>.
                Please ensure you are submitting leave for the correct period.
            </div>
        </div>
    @endif

    <div class="form-container" style="padding: 0;">
        <div class="form-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin:0;">Submit Leave Request</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Request time off from work</p>
                </div>
                <div class="col-md-4">
                    <div class="row text-center">
                        <div class="col-12">
                            <div class="stat-item leave-days-display" id="days-display">
                                <h4 class="mb-0 fw-bold text-white leave-days-value" id="leave-days-value">0.0</h4>
                                <small class="opacity-75 leave-days-label">Leave Days</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="form-container">
                <div class="form-body">
                    <div class="help-text">
                        <div class="help-title">Leave Request Form</div>
                        <div class="help-content">
                            Fill out the form below to submit your leave request. Your request will be sent to your
                            manager for approval. You will be notified once a decision is made.
                        </div>
                    </div>

                    <form class="needs-validation" method="POST" action="{{ route('leave.requests.store') }}"
                        enctype="multipart/form-data" novalidate id="leave-request-form">
                        @csrf

                        <h3 class="section-title">Leave Type</h3>
                @php
                    // Default reasons for each leave type
                    $defaultReasons = [
                        'annual' => 'Taking annual leave for personal time off.',
                        'sick' => 'Taking sick leave due to illness/medical reasons.',
                        'maternity' => 'Taking maternity leave.',
                        'paternity' => 'Taking paternity leave.',
                        'compassionate' => 'Taking compassionate leave due to family circumstances.',
                        'study' => 'Taking study leave for educational purposes.',
                        'unpaid' => 'Requesting unpaid leave for personal reasons.',
                        'family' => 'Taking family responsibility leave.',
                    ];
                @endphp
                <div class="form-group mb-3">
                    <label class="form-label" for="leave_type_id">Leave Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('leave_type_id') is-invalid @enderror"
                        name="leave_type_id" id="leave_type_id" required>
                        <option value="">Select leave type...</option>
                        @foreach($leaveTypes as $type)
                            @php
                                $typeCode = strtolower($type->code ?? '');
                                $typeName = strtolower($type->name ?? '');
                                $defaultReason = '';

                                // Match by code first, then by name
                                foreach ($defaultReasons as $key => $reason) {
                                    if (str_contains($typeCode, $key) || str_contains($typeName, $key)) {
                                        $defaultReason = $reason;
                                        break;
                                    }
                                }

                                // Fallback generic reason if no match
                                if (empty($defaultReason)) {
                                    $defaultReason = 'Taking ' . strtolower($type->name) . ' leave.';
                                }
                            @endphp
                            <option value="{{ $type->id }}"
                                data-balance="{{ $balances[$type->id] ?? 0 }}"
                                data-allow-half-day="{{ $type->allow_half_day ? '1' : '0' }}"
                                data-allow-negative="{{ $type->allow_negative_balance ? '1' : '0' }}"
                                data-default-reason="{{ $defaultReason }}"
                                {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} ({{ number_format($balances[$type->id] ?? 0, 1) }} days available)
                            </option>
                        @endforeach
                    </select>
                    @error('leave_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="balance-warning" id="balance-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <span id="balance-warning-text">Warning: You may not have sufficient balance for this request.</span>
                    </div>
                </div>

                <h3 class="section-title">Leave Dates</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="start_date">Start Date <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('start_date') is-invalid @enderror"
                            name="start_date" id="start_date" placeholder="Select date..."
                            value="{{ old('start_date') }}" required>
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="end_date">End Date <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('end_date') is-invalid @enderror"
                            name="end_date" id="end_date" placeholder="Select date..."
                            value="{{ old('end_date') }}" required>
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-grid mt-3" id="half-day-options">
                    <div class="form-group">
                        <label class="form-label" for="start_half_day">Start Day Type</label>
                        <select class="form-select @error('start_half_day') is-invalid @enderror"
                            name="start_half_day" id="start_half_day">
                            <option value="">Full Day</option>
                            <option value="am" {{ old('start_half_day') == 'am' ? 'selected' : '' }}>Morning Only (AM)</option>
                            <option value="pm" {{ old('start_half_day') == 'pm' ? 'selected' : '' }}>Afternoon Only (PM)</option>
                        </select>
                        @error('start_half_day')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="end_half_day">End Day Type</label>
                        <select class="form-select @error('end_half_day') is-invalid @enderror"
                            name="end_half_day" id="end_half_day">
                            <option value="">Full Day</option>
                            <option value="am" {{ old('end_half_day') == 'am' ? 'selected' : '' }}>Morning Only (AM)</option>
                            <option value="pm" {{ old('end_half_day') == 'pm' ? 'selected' : '' }}>Afternoon Only (PM)</option>
                        </select>
                        @error('end_half_day')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="half-day-info">
                    <h5><i class="fas fa-info-circle me-1"></i> Half-Day Options</h5>
                    <ul>
                        <li><strong>Morning Only (AM):</strong> Take only the morning portion of the day (0.5 days)</li>
                        <li><strong>Afternoon Only (PM):</strong> Take only the afternoon portion of the day (0.5 days)</li>
                        <li><strong>Full Day:</strong> Take the entire day (1.0 day)</li>
                    </ul>
                </div>

                <h3 class="section-title">Request Details</h3>
                <div class="form-group mb-3">
                    <label class="form-label" for="reason">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('reason') is-invalid @enderror"
                        name="reason" id="reason" rows="4"
                        placeholder="Please provide a reason for your leave request..."
                        required maxlength="1000">{{ old('reason') }}</textarea>
                    <div class="form-text">Maximum 1000 characters</div>
                    @error('reason')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="attachments">Attachments</label>
                    <div class="custom-file-input">
                        <input type="file" name="attachments[]" id="attachments" multiple accept=".pdf,.jpg,.jpeg,.png">
                        <label for="attachments" class="file-input-label">
                            <div class="file-input-icon">
                                <i class="fas fa-paperclip"></i>
                            </div>
                            <div class="file-input-text">
                                <span class="file-label">Choose Files</span>
                                <span class="file-hint" id="fileHint">PDF, JPG, PNG (max 5MB each)</span>
                                <span class="file-selected d-none" id="fileCount"></span>
                            </div>
                        </label>
                    </div>
                    @error('attachments.*')
                        <div class="text-danger mt-1" style="font-size: 13px;">{{ $message }}</div>
                    @enderror
                    <div class="file-list" id="file-list"></div>
                </div>

                <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">

                        <div class="form-actions">
                            <a class="btn btn-secondary" href="{{ route('leave.requests.index') }}">
                                <i class="bx bx-x"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-loading" id="submit-btn">
                                <span class="btn-text"><i class="fas fa-paper-plane"></i> Submit Request</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Submitting...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="quick-tips-card">
                <h5><i class="fas fa-lightbulb text-warning me-2"></i>Quick Tips</h5>
                <ul class="quick-tips-list">
                    <li><i class="fas fa-check"></i> Select dates first to see calculated leave days</li>
                    <li><i class="fas fa-check"></i> Half-day options allow flexible scheduling</li>
                    <li><i class="fas fa-check"></i> Weekends and holidays are automatically excluded</li>
                    <li><i class="fas fa-check"></i> Attach supporting documents if required</li>
                    <li><i class="fas fa-check"></i> Your manager will be notified automatically</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Choices.js for leave type select
            const leaveTypeSelect = document.getElementById('leave_type_id');
            if (leaveTypeSelect) {
                new Choices(leaveTypeSelect, {
                    searchEnabled: true,
                    itemSelectText: '',
                    placeholder: true,
                    placeholderValue: 'Select leave type...',
                    allowHTML: false
                });
            }

            // Initialize Flatpickr for date pickers
            const today = new Date();
            const startDatePicker = flatpickr('#start_date', {
                dateFormat: 'Y-m-d',
                minDate: 'today',
                onChange: function(selectedDates, dateStr) {
                    endDatePicker.set('minDate', dateStr);
                    calculateLeaveDays();
                }
            });

            const endDatePicker = flatpickr('#end_date', {
                dateFormat: 'Y-m-d',
                minDate: 'today',
                onChange: function() {
                    calculateLeaveDays();
                }
            });

            // Listen for half-day changes
            document.getElementById('start_half_day').addEventListener('change', calculateLeaveDays);
            document.getElementById('end_half_day').addEventListener('change', calculateLeaveDays);
            document.getElementById('leave_type_id').addEventListener('change', function() {
                updateHalfDayOptions();
                checkBalance();
                fillDefaultReason();
            });

            // File input handling
            const fileInput = document.getElementById('attachments');
            const fileList = document.getElementById('file-list');
            const fileHint = document.getElementById('fileHint');
            const fileCount = document.getElementById('fileCount');

            fileInput.addEventListener('change', function() {
                fileList.innerHTML = '';

                if (this.files && this.files.length > 0) {
                    // Update the label text
                    fileHint.classList.add('d-none');
                    fileCount.classList.remove('d-none');
                    fileCount.textContent = this.files.length + ' file' + (this.files.length > 1 ? 's' : '') + ' selected';

                    // Show file list
                    Array.from(this.files).forEach(function(file, index) {
                        const fileItem = document.createElement('div');
                        fileItem.className = 'file-item';
                        fileItem.innerHTML = `
                            <i class="fas fa-file"></i>
                            <span class="file-name">${file.name}</span>
                            <span class="file-size">(${formatFileSize(file.size)})</span>
                        `;
                        fileList.appendChild(fileItem);
                    });
                } else {
                    fileHint.classList.remove('d-none');
                    fileCount.classList.add('d-none');
                    fileCount.textContent = '';
                }
            });

            // Form validation
            const form = document.getElementById('leave-request-form');
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();

                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                } else {
                    const submitBtn = document.getElementById('submit-btn');
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }

                form.classList.add('was-validated');
            });

            // Calculate leave days function
            function calculateLeaveDays() {
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                const startHalfDay = document.getElementById('start_half_day').value;
                const endHalfDay = document.getElementById('end_half_day').value;

                if (!startDate || !endDate) {
                    updateDaysDisplay(0);
                    return;
                }

                fetch('{{ route('leave.requests.calculate-days') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        start_date: startDate,
                        end_date: endDate,
                        start_half_day: startHalfDay || null,
                        end_half_day: endHalfDay || null
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateDaysDisplay(data.days);
                        checkBalance(data.days);
                    } else {
                        console.error('Calculation error:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error calculating days:', error);
                });
            }

            function updateDaysDisplay(days) {
                const display = document.getElementById('leave-days-value');
                display.textContent = parseFloat(days).toFixed(1);
            }

            function updateHalfDayOptions() {
                const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                const allowHalfDay = selectedOption ? selectedOption.dataset.allowHalfDay === '1' : false;
                const halfDaySection = document.getElementById('half-day-options');

                if (allowHalfDay) {
                    halfDaySection.style.display = 'grid';
                } else {
                    halfDaySection.style.display = 'none';
                    document.getElementById('start_half_day').value = '';
                    document.getElementById('end_half_day').value = '';
                    calculateLeaveDays();
                }
            }

            function checkBalance(requestedDays = null) {
                const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                if (!selectedOption || !selectedOption.value) {
                    hideBalanceWarning();
                    return;
                }

                const balance = parseFloat(selectedOption.dataset.balance) || 0;
                const allowNegative = selectedOption.dataset.allowNegative === '1';
                const days = requestedDays !== null ? requestedDays : parseFloat(document.getElementById('leave-days-value').textContent) || 0;

                if (days > balance && !allowNegative) {
                    showBalanceWarning(`Requested days (${days.toFixed(1)}) exceed your available balance (${balance.toFixed(1)} days).`);
                } else {
                    hideBalanceWarning();
                }
            }

            function showBalanceWarning(message) {
                const warning = document.getElementById('balance-warning');
                const warningText = document.getElementById('balance-warning-text');
                warningText.textContent = message;
                warning.classList.add('show');
            }

            function hideBalanceWarning() {
                const warning = document.getElementById('balance-warning');
                warning.classList.remove('show');
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            function fillDefaultReason() {
                const selectedOption = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                const reasonField = document.getElementById('reason');

                if (selectedOption && selectedOption.value) {
                    const defaultReason = selectedOption.dataset.defaultReason || '';
                    // Only fill if reason field is empty or contains a previous default reason
                    if (!reasonField.value.trim() || reasonField.dataset.isDefault === 'true') {
                        reasonField.value = defaultReason;
                        reasonField.dataset.isDefault = 'true';
                    }
                }
            }

            // Clear the isDefault flag when user manually types
            document.getElementById('reason').addEventListener('input', function() {
                this.dataset.isDefault = 'false';
            });

            // Initialize half-day visibility
            updateHalfDayOptions();

            // Auto-dismiss alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const dismissButton = alert.querySelector('.btn-close');
                    if (dismissButton) {
                        dismissButton.click();
                    }
                }, 5000);
            });
        });
    </script>
@endsection
