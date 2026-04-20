@extends('layouts.master')
@section('title')
    Learning Space Settings
@endsection
@section('css')
    <style>
        .settings-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
        }

        .settings-header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .settings-body {
            padding: 24px;
        }

        .card {
            border: none;
            box-shadow: none;
        }

        /* Tab Styling */
        .nav-tabs-custom {
            border-bottom: 1px solid #e5e7eb;
            gap: 8px;
            flex-wrap: wrap;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: #6b7280;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.2s ease;
            border-radius: 0;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #3b82f6;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
            background: transparent;
        }

        .help-text {
            background: #f8f9fa;
            padding: 12px 16px;
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
            line-height: 1.5;
            margin: 0;
        }

        .section-title {
            font-size: 14px;
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
            font-size: 13px;
        }

        .form-control,
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .form-text {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        @media (max-width: 768px) {

            .form-grid,
            .form-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .btn {
            padding: 10px 16px;
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

        .btn-light {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background: #e9ecef;
            color: #495057;
        }

        /* Toggle Switch */
        .form-switch .form-check-input {
            width: 44px;
            height: 24px;
            cursor: pointer;
        }

        .form-switch .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .form-switch .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            border-color: #3b82f6;
        }

        /* Table Styling */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: flex-end;
        }

        .action-buttons .btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-buttons .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-buttons .btn i {
            font-size: 16px;
        }

        .action-buttons .btn-outline-info {
            background: transparent;
            border: 1px solid #0dcaf0;
            color: #0dcaf0;
        }

        .action-buttons .btn-outline-info:hover {
            background: #0dcaf0;
            color: #000;
        }

        .action-buttons .btn-outline-secondary {
            background: transparent;
            border: 1px solid #6c757d;
            color: #6c757d;
        }

        .action-buttons .btn-outline-secondary:hover {
            background: #6c757d;
            color: #fff;
        }

        .action-buttons .btn-outline-danger {
            background: transparent;
            border: 1px solid #dc3545;
            color: #dc3545;
        }

        .action-buttons .btn-outline-danger:hover {
            background: #dc3545;
            color: #fff;
        }

        .badge-lti {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-version {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .rubric-type-badge {
            font-size: 13px;
            padding: 5px 12px;
        }

        /* Loading Animation */
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

        .form-actions {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.3;
            margin-bottom: 16px;
        }

        .input-group-text {
            background: #f9fafb;
            border: 1px solid #d1d5db;
            border-radius: 0 3px 3px 0;
            padding: 10px 12px;
            font-size: 13px;
            color: #6b7280;
        }

        .input-group .form-control {
            border-radius: 3px 0 0 3px;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('title')
            Settings
        @endslot
    @endcomponent

    @if (session('success'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('success') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div id="messageContainer"></div>

    <div class="settings-container">
        <div class="settings-header">
            <h4 class="mb-1 text-white"><i class="fas fa-cog me-2"></i>Learning Space Settings</h4>
            <p class="mb-0 opacity-75">Configure file limits, quiz settings, video options, SCORM, LTI tools, and
                gamification</p>
        </div>
        <div class="settings-body">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#general" role="tab">
                                <i class="fas fa-sliders-h me-2 text-muted"></i>General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#quiz-assignment" role="tab">
                                <i class="fas fa-clipboard-list me-2 text-muted"></i>Quiz & Assignments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#video" role="tab">
                                <i class="fas fa-video me-2 text-muted"></i>Video
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#scorm" role="tab">
                                <i class="fas fa-box-open me-2 text-muted"></i>SCORM
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#lti" role="tab">
                                <i class="fas fa-plug me-2 text-muted"></i>LTI Tools
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#gamification" role="tab">
                                <i class="fas fa-trophy me-2 text-muted"></i>Gamification
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#rubrics" role="tab">
                                <i class="fas fa-th-list me-2 text-muted"></i>Rubrics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#calendar" role="tab">
                                <i class="fas fa-calendar-alt me-2 text-muted"></i>Calendar
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3 text-muted">
                        <!-- General Settings Tab -->
                        <div class="tab-pane active" id="general" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">General Settings</div>
                                <div class="help-content">
                                    Configure file upload limits and default course settings for the LMS module.
                                </div>
                            </div>

                            <form id="generalSettingsForm" class="settings-form">
                                <div class="section-title">File Upload Limits</div>
                                <div class="form-grid">
                                    @foreach ($generalSettings as $setting)
                                        <div class="mb-3">
                                            <label class="form-label">{{ $setting->display_name }}</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control setting-input"
                                                    name="settings[{{ $setting->key }}]" value="{{ $setting->typed_value }}"
                                                    data-key="{{ $setting->key }}" min="1">
                                                <span class="input-group-text">MB</span>
                                            </div>
                                            @if ($setting->description)
                                                <div class="form-text">{{ $setting->description }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="section-title mt-4">Course Defaults</div>
                                <div class="form-grid-2">
                                    @foreach ($courseSettings as $setting)
                                        <div class="mb-3">
                                            <label class="form-label">{{ $setting->display_name }}</label>
                                            @if ($setting->type === 'string' && str_contains($setting->key, 'method'))
                                                <select class="form-select setting-input"
                                                    name="settings[{{ $setting->key }}]" data-key="{{ $setting->key }}">
                                                    <option value="weighted"
                                                        {{ $setting->typed_value === 'weighted' ? 'selected' : '' }}>
                                                        Weighted Average</option>
                                                    <option value="points"
                                                        {{ $setting->typed_value === 'points' ? 'selected' : '' }}>Total
                                                        Points</option>
                                                    <option value="simple_average"
                                                        {{ $setting->typed_value === 'simple_average' ? 'selected' : '' }}>
                                                        Simple Average</option>
                                                </select>
                                            @else
                                                <div class="input-group">
                                                    <input type="number" class="form-control setting-input"
                                                        name="settings[{{ $setting->key }}]"
                                                        value="{{ $setting->typed_value }}"
                                                        data-key="{{ $setting->key }}" min="0" max="100">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            @endif
                                            @if ($setting->description)
                                                <div class="form-text">{{ $setting->description }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                                aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Quiz & Assignments Tab -->
                        <div class="tab-pane" id="quiz-assignment" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Quiz & Assignment Settings</div>
                                <div class="help-content">
                                    Configure default settings for quizzes and assignment submissions.
                                </div>
                            </div>

                            <form id="quizAssignmentSettingsForm" class="settings-form">
                                <div class="section-title">Quiz Settings</div>
                                <div class="form-grid">
                                    @foreach ($quizSettings as $setting)
                                        <div class="mb-3">
                                            <label class="form-label">{{ $setting->display_name }}</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control setting-input"
                                                    name="settings[{{ $setting->key }}]"
                                                    value="{{ $setting->typed_value }}" data-key="{{ $setting->key }}"
                                                    min="0">
                                                <span
                                                    class="input-group-text">{{ str_contains($setting->key, 'time') ? 'min' : (str_contains($setting->key, 'score') ? '%' : 'pts') }}</span>
                                            </div>
                                            @if ($setting->description)
                                                <div class="form-text">{{ $setting->description }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="section-title mt-4">Assignment Settings</div>
                                <div class="form-grid-2">
                                    @foreach ($assignmentSettings as $setting)
                                        <div class="mb-3">
                                            <label class="form-label">{{ $setting->display_name }}</label>
                                            @if ($setting->type === 'string')
                                                <input type="text" class="form-control setting-input"
                                                    name="settings[{{ $setting->key }}]"
                                                    value="{{ $setting->typed_value }}" data-key="{{ $setting->key }}">
                                            @else
                                                <div class="input-group">
                                                    <input type="number" class="form-control setting-input"
                                                        name="settings[{{ $setting->key }}]"
                                                        value="{{ $setting->typed_value }}"
                                                        data-key="{{ $setting->key }}" min="0">
                                                    <span
                                                        class="input-group-text">{{ str_contains($setting->key, 'size') ? 'MB' : (str_contains($setting->key, 'penalty') ? '%' : (str_contains($setting->key, 'points') ? 'pts' : '')) }}</span>
                                                </div>
                                            @endif
                                            @if ($setting->description)
                                                <div class="form-text">{{ $setting->description }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                                aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Video Settings Tab -->
                        <div class="tab-pane" id="video" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Video Settings</div>
                                <div class="help-content">
                                    Configure video upload limits, supported formats, and completion tracking.
                                </div>
                            </div>

                            <form id="videoSettingsForm" class="settings-form">
                                <div class="form-grid-2">
                                    @foreach ($videoSettings as $setting)
                                        <div class="mb-3">
                                            <label class="form-label">{{ $setting->display_name }}</label>
                                            @if ($setting->type === 'string')
                                                <input type="text" class="form-control setting-input"
                                                    name="settings[{{ $setting->key }}]"
                                                    value="{{ $setting->typed_value }}" data-key="{{ $setting->key }}">
                                            @else
                                                <div class="input-group">
                                                    <input type="number" class="form-control setting-input"
                                                        name="settings[{{ $setting->key }}]"
                                                        value="{{ $setting->typed_value }}"
                                                        data-key="{{ $setting->key }}" min="0"
                                                        max="{{ str_contains($setting->key, 'threshold') ? 100 : '' }}">
                                                    <span
                                                        class="input-group-text">{{ str_contains($setting->key, 'threshold') ? '%' : '' }}</span>
                                                </div>
                                            @endif
                                            @if ($setting->description)
                                                <div class="form-text">{{ $setting->description }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                                aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- SCORM Settings Tab -->
                        <div class="tab-pane" id="scorm" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">SCORM Settings</div>
                                <div class="help-content">
                                    Configure SCORM package settings, supported versions, and default mastery scores.
                                </div>
                            </div>

                            <form id="scormSettingsForm" class="settings-form">
                                <div class="form-grid-2">
                                    @foreach ($scormSettings as $setting)
                                        <div class="mb-3">
                                            <label class="form-label">{{ $setting->display_name }}</label>
                                            @if ($setting->type === 'string')
                                                <input type="text" class="form-control setting-input"
                                                    name="settings[{{ $setting->key }}]"
                                                    value="{{ $setting->typed_value }}" data-key="{{ $setting->key }}">
                                            @else
                                                <div class="input-group">
                                                    <input type="number" class="form-control setting-input"
                                                        name="settings[{{ $setting->key }}]"
                                                        value="{{ $setting->typed_value }}"
                                                        data-key="{{ $setting->key }}" min="0" max="100">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            @endif
                                            @if ($setting->description)
                                                <div class="form-text">{{ $setting->description }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                                aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- LTI Tools Tab -->
                        <div class="tab-pane" id="lti" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">LTI Integration Settings</div>
                                <div class="help-content">
                                    Configure LTI defaults and manage external LTI tools. Use the <a
                                        href="{{ route('lms.lti.index') }}">LTI Tools Manager</a> to add, edit, or remove
                                    tools.
                                </div>
                            </div>

                            <form id="ltiSettingsForm" class="settings-form">
                                <div class="section-title">Default LTI Settings</div>
                                <div class="form-grid">
                                    @foreach ($ltiSettings as $setting)
                                        <div class="mb-3">
                                            <label class="form-label">{{ $setting->display_name }}</label>
                                            @if (str_contains($setting->key, 'version'))
                                                <select class="form-select setting-input"
                                                    name="settings[{{ $setting->key }}]" data-key="{{ $setting->key }}">
                                                    <option value="1.1"
                                                        {{ $setting->typed_value === '1.1' ? 'selected' : '' }}>LTI 1.1
                                                    </option>
                                                    <option value="1.3"
                                                        {{ $setting->typed_value === '1.3' ? 'selected' : '' }}>LTI 1.3
                                                    </option>
                                                </select>
                                            @elseif(str_contains($setting->key, 'privacy'))
                                                <select class="form-select setting-input"
                                                    name="settings[{{ $setting->key }}]" data-key="{{ $setting->key }}">
                                                    <option value="public"
                                                        {{ $setting->typed_value === 'public' ? 'selected' : '' }}>Public
                                                        (Full user data)
                                                    </option>
                                                    <option value="name_only"
                                                        {{ $setting->typed_value === 'name_only' ? 'selected' : '' }}>Name
                                                        Only</option>
                                                    <option value="anonymous"
                                                        {{ $setting->typed_value === 'anonymous' ? 'selected' : '' }}>
                                                        Anonymous</option>
                                                </select>
                                            @elseif($setting->type === 'integer')
                                                <div class="input-group">
                                                    <input type="number" class="form-control setting-input"
                                                        name="settings[{{ $setting->key }}]"
                                                        value="{{ $setting->typed_value }}"
                                                        data-key="{{ $setting->key }}" min="0">
                                                    <span class="input-group-text">pts</span>
                                                </div>
                                            @else
                                                <input type="text" class="form-control setting-input"
                                                    name="settings[{{ $setting->key }}]"
                                                    value="{{ $setting->typed_value }}" data-key="{{ $setting->key }}">
                                            @endif
                                            @if ($setting->description)
                                                <div class="form-text">{{ $setting->description }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                                aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>

                            @if ($ltiTools->isNotEmpty())
                                <div class="section-title mt-4">Registered LTI Tools</div>
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Tool Name</th>
                                                <th>Version</th>
                                                <th>Status</th>
                                                <th>Resource Links</th>
                                                <th>Launches</th>
                                                <th style="width: 100px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($ltiTools as $tool)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $tool->name }}</strong>
                                                        @if ($tool->description)
                                                            <br><small
                                                                class="text-muted">{{ Str::limit($tool->description, 50) }}</small>
                                                        @endif
                                                    </td>
                                                    <td><span class="badge badge-lti badge-version">LTI
                                                            {{ $tool->version ?? '1.3' }}</span></td>
                                                    <td>
                                                        @if ($tool->is_active)
                                                            <span class="badge badge-lti badge-active">Active</span>
                                                        @else
                                                            <span class="badge badge-lti badge-inactive">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $tool->resource_links_count ?? 0 }}</td>
                                                    <td>{{ $tool->launches_count ?? 0 }}</td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <a href="{{ route('lms.lti.edit', $tool) }}"
                                                                class="btn btn-outline-info" title="Edit">
                                                                <i class="bx bx-edit-alt"></i>
                                                            </a>
                                                            <a href="{{ route('lms.lti.show', $tool) }}"
                                                                class="btn btn-outline-secondary" title="View">
                                                                <i class="bx bx-show"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('lms.lti.create') }}" class="btn btn-primary">
                                        <i class="bx bx-plus"></i> Add New LTI Tool
                                    </a>
                                </div>
                            @else
                                <div class="section-title mt-4">Registered LTI Tools</div>
                                <div class="empty-state">
                                    <i class="fas fa-plug"></i>
                                    <p class="mb-2">No LTI tools configured yet</p>
                                    <a href="{{ route('lms.lti.create') }}" class="btn btn-primary btn-sm">Add LTI Tool
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Gamification Settings Tab -->
                        <div class="tab-pane" id="gamification" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Gamification Settings</div>
                                <div class="help-content">
                                    Enable or disable gamification features and configure display limits.
                                </div>
                            </div>

                            <form id="gamificationSettingsForm" class="settings-form">
                                <div class="section-title">Feature Toggles</div>
                                <div class="form-grid-2 mb-4">
                                    @foreach ($gamificationSettings->filter(fn($s) => $s->type === 'boolean') as $setting)
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input setting-input" type="checkbox"
                                                    id="{{ $setting->key }}" name="settings[{{ $setting->key }}]"
                                                    data-key="{{ $setting->key }}"
                                                    {{ $setting->typed_value ? 'checked' : '' }}>
                                                <label class="form-check-label" for="{{ $setting->key }}">
                                                    <strong>{{ $setting->display_name }}</strong>
                                                </label>
                                            </div>
                                            @if ($setting->description)
                                                <div class="form-text ms-5">{{ $setting->description }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="section-title">Display Limits</div>
                                <div class="form-grid-2">
                                    @foreach ($gamificationSettings->filter(fn($s) => $s->type !== 'boolean') as $setting)
                                        <div class="mb-3">
                                            <label class="form-label">{{ $setting->display_name }}</label>
                                            <input type="number" class="form-control setting-input"
                                                name="settings[{{ $setting->key }}]" value="{{ $setting->typed_value }}"
                                                data-key="{{ $setting->key }}" min="1">
                                            @if ($setting->description)
                                                <div class="form-text">{{ $setting->description }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status"
                                                aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Rubrics Tab -->
                        <div class="tab-pane" id="rubrics" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Grading Rubrics</div>
                                <div class="help-content">
                                    Create and manage reusable grading rubrics for assignments. Rubrics help standardize
                                    grading criteria and provide clear expectations to students.
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mb-3">
                                <a href="{{ route('lms.rubrics.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Rubric
                                </a>
                            </div>

                            @if ($rubrics->isNotEmpty())
                                <div class="section-title">Available Rubrics</div>
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Rubric Name</th>
                                                <th class="text-center">Criteria</th>
                                                <th class="text-center">Total Points</th>
                                                <th class="text-center">Assignments</th>
                                                <th class="text-center">Type</th>
                                                <th style="width: 120px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($rubrics as $rubric)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $rubric->title }}</strong>
                                                        @if ($rubric->description)
                                                            <br><small
                                                                class="text-muted">{{ Str::limit($rubric->description, 50) }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $rubric->criteria_count }}</td>
                                                    <td class="text-center">{{ number_format($rubric->total_points, 0) }}</td>
                                                    <td class="text-center">{{ $rubric->assignments_count }}</td>
                                                    <td class="text-center">
                                                        @if ($rubric->is_template)
                                                            <span class="badge badge-lti badge-active rubric-type-badge">Template</span>
                                                        @else
                                                            <span class="badge badge-lti badge-version rubric-type-badge">Personal</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <a href="{{ route('lms.rubrics.edit', $rubric) }}"
                                                                class="btn btn-sm btn-outline-info" title="Edit">
                                                                <i class="bx bx-edit-alt"></i>
                                                            </a>
                                                            <form action="{{ route('lms.rubrics.duplicate', $rubric) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                                    title="Duplicate">
                                                                    <i class="bx bx-copy"></i>
                                                                </button>
                                                            </form>
                                                            @if ($rubric->assignments_count == 0)
                                                                <form action="{{ route('lms.rubrics.destroy', $rubric) }}"
                                                                    method="POST" class="d-inline"
                                                                    onsubmit="return confirm('Are you sure you want to delete this rubric?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                        title="Delete">
                                                                        <i class="bx bx-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="section-title">Available Rubrics</div>
                                <div class="empty-state">
                                    <i class="fas fa-th-list"></i>
                                    <p class="mb-2">No rubrics created yet</p>
                                    <p class="text-muted mb-0">Create rubrics to standardize grading across assignments</p>
                                </div>
                            @endif
                        </div>

                        <!-- Calendar Settings Tab -->
                        <div class="tab-pane" id="calendar" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Calendar Event Notifications</div>
                                <div class="help-content">
                                    Configure how calendar event notifications are sent to students.
                                </div>
                            </div>

                            <form action="{{ route('lms.settings.calendar.update') }}" method="POST" class="settings-form">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="section-title">Notification Settings</div>

                                        <div class="mb-4">
                                            <label class="form-label fw-semibold">
                                                <i class="fas fa-toggle-on me-1 text-muted"></i>
                                                Enable Calendar Notifications
                                            </label>
                                            <div class="form-check form-switch mt-2">
                                                <input type="hidden" name="notifications_enabled" value="0">
                                                <input type="checkbox"
                                                       class="form-check-input"
                                                       name="notifications_enabled"
                                                       id="calendarNotificationsEnabled"
                                                       value="1"
                                                       {{ ($calendarSettings['notifications_enabled'] ?? false) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="calendarNotificationsEnabled">
                                                    Allow staff to send email notifications when creating calendar events
                                                </label>
                                            </div>
                                            <small class="text-muted">
                                                When enabled, staff will see a "Notify students" checkbox when creating events.
                                            </small>
                                        </div>

                                        <div class="mb-4">
                                            <label for="queueName" class="form-label fw-semibold">
                                                <i class="fas fa-stream me-1 text-muted"></i>
                                                Queue Name
                                            </label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="queueName"
                                                   name="queue_name"
                                                   value="{{ $calendarSettings['queue_name'] ?? 'calendar-notifications' }}"
                                                   pattern="[a-z0-9\-]+"
                                                   required>
                                            <small class="text-muted">
                                                The queue name for processing notification jobs. Use lowercase letters, numbers, and hyphens only.
                                            </small>
                                        </div>

                                        <div class="mb-4">
                                            <label for="batchSize" class="form-label fw-semibold">
                                                <i class="fas fa-layer-group me-1 text-muted"></i>
                                                Batch Size
                                            </label>
                                            <input type="number"
                                                   class="form-control"
                                                   id="batchSize"
                                                   name="batch_size"
                                                   value="{{ $calendarSettings['batch_size'] ?? 100 }}"
                                                   min="1"
                                                   max="500"
                                                   required>
                                            <small class="text-muted">
                                                Number of students to process per job batch (1-500). Lower values reduce memory usage.
                                            </small>
                                        </div>

                                        <div class="mt-4 pt-3 border-top">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>
                                                Save Calendar Settings
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="section-title">How It Works</div>
                                        <div class="card bg-light border-0">
                                            <div class="card-body">
                                                <ul class="mb-0 small">
                                                    <li class="mb-2">When staff create a calendar event, they can check "Notify students" to send email notifications.</li>
                                                    <li class="mb-2">Only students in the targeted audience (course, grade, class, or all) will receive the email.</li>
                                                    <li class="mb-2">Notifications are processed in the background using Laravel queues.</li>
                                                    <li class="mb-2">Students with no email address will be skipped.</li>
                                                    <li>To process notifications, run: <code>php artisan queue:work --queue={{ $calendarSettings['queue_name'] ?? 'calendar-notifications' }}</code></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Message display function
            function displayMessage(message, type = 'success') {
                const messageContainer = document.getElementById('messageContainer');
                const icon = type === 'success' ? 'mdi-check-all' : 'mdi-block-helper';
                messageContainer.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="alert alert-${type} alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                                <i class="mdi ${icon} label-icon"></i>
                                <strong>${message}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>`;

                // Auto dismiss after 5 seconds
                setTimeout(() => {
                    const alert = messageContainer.querySelector('.alert');
                    if (alert) {
                        alert.classList.remove('show');
                        setTimeout(() => alert.remove(), 150);
                    }
                }, 5000);
            }

            // Handle form submissions
            const forms = document.querySelectorAll('.settings-form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const submitBtn = this.querySelector('button[type="submit"].btn-loading');
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;

                    const formData = new FormData(this);
                    const settings = {};

                    // Handle checkboxes specially for boolean values
                    this.querySelectorAll('.setting-input').forEach(input => {
                        const key = input.dataset.key;
                        if (input.type === 'checkbox') {
                            settings[key] = input.checked ? 'true' : 'false';
                        } else {
                            settings[key] = input.value;
                        }
                    });

                    fetch('{{ route('lms.settings.update') }}', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                settings: settings
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            submitBtn.classList.remove('loading');
                            submitBtn.disabled = false;
                            if (data.success) {
                                displayMessage(data.message, 'success');
                            } else {
                                displayMessage(data.message || 'Failed to update settings.',
                                    'danger');
                            }
                        })
                        .catch(error => {
                            submitBtn.classList.remove('loading');
                            submitBtn.disabled = false;
                            console.error('Error:', error);
                            displayMessage('An error occurred while saving settings.',
                                'danger');
                        });
                });
            });

            // Tab persistence using localStorage
            const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    const activeTabHref = event.target.getAttribute('href');
                    localStorage.setItem('lmsSettingsActiveTab', activeTabHref);
                });
            });

            // Restore active tab
            const activeTab = localStorage.getItem('lmsSettingsActiveTab');
            if (activeTab) {
                const tabTriggerEl = document.querySelector(`.nav-link[href="${activeTab}"]`);
                if (tabTriggerEl) {
                    const tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                }
            }
        });
    </script>
@endsection
