@extends('layouts.master')
@section('title')
    Attendance Settings
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

        /* Card Border */
        .card {
            border: none;
            box-shadow: none;
        }

        /* Tab Styling */
        .nav-tabs-custom {
            border-bottom: 1px solid #e5e7eb;
            gap: 8px;
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
            color: #4e73df;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            border-bottom-color: #4e73df;
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

        /* Code Badge */
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

        /* Table Styles */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px 10px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            color: #4b5563;
            font-size: 14px;
        }

        .table tbody tr:hover {
            background-color: #f8fafc;
        }

        .table tbody tr.inactive {
            opacity: 0.5;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            color: #3b82f6;
            border-color: #3b82f6;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
            color: white;
        }

        .btn-outline-danger {
            color: #ef4444;
            border-color: #ef4444;
        }

        .btn-outline-danger:hover {
            background: #ef4444;
            color: white;
        }

        .btn-outline-success {
            color: #10b981;
            border-color: #10b981;
        }

        .btn-outline-success:hover {
            background: #10b981;
            color: white;
        }

        .btn-outline-secondary {
            color: #6b7280;
            border-color: #6b7280;
        }

        .btn-outline-secondary:hover {
            background: #6b7280;
            color: white;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 3px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .modal-title {
            font-weight: 600;
            color: #374151;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
        }

        /* Form Check Custom */
        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        /* Status Badge */
        .status-badge {
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: 500;
        }

        .status-badge.active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .codes-list-title,
        .holiday-list-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
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
        @slot('title')
            Settings
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
            <h3><i class="fas fa-cog me-2"></i>Attendance Settings</h3>
            <p>Manage attendance codes and non-school days</p>
        </div>

        <div class="settings-body">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom d-flex justify-content-start" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#codes" role="tab">
                                <i class="fas fa-tags me-2 text-muted"></i>Attendance Codes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#holidays" role="tab">
                                <i class="fas fa-calendar-alt me-2 text-muted"></i>Non School Days
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3">
                        <!-- Attendance Codes Tab -->
                        <div class="tab-pane active" id="codes" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">About Attendance Codes</div>
                                <div class="help-content">
                                    Define custom attendance codes to track different attendance statuses. Each code should have
                                    a unique identifier, description, and color for easy identification.
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="codes-list-title mb-0" style="border-bottom: none; padding-bottom: 0;"><i class="fas fa-list me-2"></i>Attendance Codes</h6>
                                <a href="{{ route('attendance.codes.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add Code
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 60px;">Order</th>
                                            <th style="width: 80px;">Code</th>
                                            <th>Description</th>
                                            <th style="width: 80px;">Type</th>
                                            <th style="width: 80px;">Status</th>
                                            <th style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($codes as $code)
                                            <tr class="{{ !$code->is_active ? 'inactive' : '' }}">
                                                <td>
                                                    <span class="text-muted">{{ $code->order }}</span>
                                                </td>
                                                <td>
                                                    <span class="code-badge" style="background-color: {{ $code->color }};">
                                                        {{ $code->code }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-medium">{{ $code->description }}</span>
                                                </td>
                                                <td>
                                                    @if ($code->is_present)
                                                        <span class="text-success"><i class="fas fa-check-circle"></i> Present</span>
                                                    @else
                                                        <span class="text-muted"><i class="fas fa-times-circle"></i> Absent</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="status-badge {{ $code->is_active ? 'active' : 'inactive' }}">
                                                        {{ $code->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <button type="button" class="btn btn-sm btn-outline-primary action-btn"
                                                            onclick="openEditCodeModal({{ json_encode($code) }})"
                                                            title="Edit Code">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('attendance.codes.toggle', $code->id) }}"
                                                            method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-sm {{ $code->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }} action-btn"
                                                                title="{{ $code->is_active ? 'Deactivate' : 'Activate' }}">
                                                                <i class="fas {{ $code->is_active ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('attendance.codes.destroy', $code->id) }}"
                                                            method="POST" style="display: inline;"
                                                            onsubmit="return confirm('Are you sure you want to delete this code?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger action-btn"
                                                                title="Delete Code">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    <i class="fas fa-tags fa-2x mb-2 d-block opacity-50"></i>
                                                    No attendance codes found. Click "Add Code" to create your first one.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Non School Days Tab -->
                        <div class="tab-pane" id="holidays" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">About Non School Days</div>
                                <div class="help-content">
                                    Add public holidays, school breaks, and other non-attendance days. These dates will be
                                    excluded from attendance calculations and reports.
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="holiday-list-title mb-0" style="border-bottom: none; padding-bottom: 0;">
                                    <i class="fas fa-calendar-times me-2"></i>Non School Days
                                </h6>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="termId" class="form-label mb-0 text-nowrap" style="font-size: 14px;">Term:</label>
                                        <select name="term" id="termId" class="form-select form-select-sm" style="min-width: 160px;">
                                            @if (!empty($terms))
                                                @foreach ($terms as $term)
                                                    <option data-year="{{ $term->year }}"
                                                        value="{{ $term->id }}"{{ $term->id == session('selected_term_id', $currentTerm->id ?? '') ? 'selected' : '' }}>
                                                        {{ 'Term ' . $term->term . ', ' . $term->year }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                                        <i class="fas fa-plus me-1"></i> Add Holiday
                                    </button>
                                </div>
                            </div>

                            <div id="holiday-list">
                                <div class="text-center text-muted py-4">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Loading holidays...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-start mt-3">
                <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Attendance
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Code Modal -->
    <div class="modal fade" id="editCodeModal" tabindex="-1" aria-labelledby="editCodeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editCodeForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCodeModalLabel">
                            <i class="fas fa-edit me-2"></i>Edit Attendance Code
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editCode" class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editCode" name="code" maxlength="10" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editDescription" class="form-label">Description <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editDescription" name="description" maxlength="100" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editColor" class="form-label">Color <span class="text-danger">*</span></label>
                                <div class="color-picker-wrapper">
                                    <input type="color" id="editColorPicker" value="#3b82f6">
                                    <input type="text" name="color" id="editColor" class="form-control"
                                        value="#3b82f6" pattern="^#[0-9A-Fa-f]{6}$" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Options</label>
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="editIsPresent" name="is_present" value="1">
                                    <label class="form-check-label" for="editIsPresent">
                                        This code represents "Present"
                                    </label>
                                </div>
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="editIsActive" name="is_active" value="1">
                                    <label class="form-check-label" for="editIsActive">
                                        Code is active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-loading" id="updateCodeBtn">
                            <span class="btn-text"><i class="fas fa-save me-1"></i> Update Code</span>
                            <span class="btn-spinner">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Updating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Holiday Modal -->
    <div class="modal fade" id="editHolidayModal" tabindex="-1" aria-labelledby="editHolidayModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editHolidayForm" method="POST" action="{{ route('holidays.update-holiday', ':id') }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="editHolidayModalLabel">
                            <i class="fas fa-edit me-2"></i>Edit Holiday
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="holidayId">
                        <input type="hidden" name="year" id="editHolidayYear">
                        <div class="mb-3">
                            <label for="holidayName" class="form-label">Holiday Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="holidayName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editHolidayTermId" class="form-label">Term <span class="text-danger">*</span></label>
                            <select name="term_id" id="editHolidayTermId" class="form-select" required>
                                @if (!empty($terms))
                                    @foreach ($terms as $term)
                                        <option data-year="{{ $term->year }}" value="{{ $term->id }}">
                                            {{ 'Term ' . $term->term . ', ' . $term->year }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="holidayStart" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="holidayStart" name="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="holidayEnd" class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="holidayEnd" name="end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-loading" id="updateHolidayBtn">
                            <span class="btn-text"><i class="fas fa-save me-1"></i> Update Holiday</span>
                            <span class="btn-spinner">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Updating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Holiday Modal -->
    <div class="modal fade" id="addHolidayModal" tabindex="-1" aria-labelledby="addHolidayModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addHolidayForm" method="POST" action="{{ route('attendance.add-day') }}">
                    @csrf
                    <input type="hidden" name="year" id="addHolidayYear" value="{{ $currentTerm->year ?? '' }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addHolidayModalLabel">
                            <i class="fas fa-plus-circle me-2"></i>Add New Holiday
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="addHolidayName" class="form-label">Holiday Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="addHolidayName" name="name"
                                placeholder="e.g., Good Friday, Independence Day" required>
                            <div class="form-text">Enter a descriptive name for this holiday</div>
                        </div>
                        <div class="mb-3">
                            <label for="addHolidayTermId" class="form-label">Term <span class="text-danger">*</span></label>
                            <select name="term_id" id="addHolidayTermId" class="form-select" required>
                                @if (!empty($terms))
                                    @foreach ($terms as $term)
                                        <option data-year="{{ $term->year }}"
                                            value="{{ $term->id }}"{{ $term->id == session('selected_term_id', $currentTerm->id ?? '') ? ' selected' : '' }}>
                                            {{ 'Term ' . $term->term . ', ' . $term->year }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="form-text">Select the term for this holiday</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="addHolidayStart" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="addHolidayStart" name="start_date" required>
                                <div class="form-text">First day of the holiday</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="addHolidayEnd" class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="addHolidayEnd" name="end_date" required>
                                <div class="form-text">Last day of the holiday</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-loading" id="addHolidayBtn">
                            <span class="btn-text"><i class="fas fa-save me-1"></i> Save Holiday</span>
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
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Tab persistence
            const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    const activeTabHref = event.target.getAttribute('href');
                    localStorage.setItem('attendanceSettingsTab', activeTabHref);
                });
            });

            const activeTab = localStorage.getItem('attendanceSettingsTab');
            if (activeTab) {
                const tabTriggerEl = document.querySelector(`.nav-link[href="${activeTab}"]`);
                if (tabTriggerEl) {
                    const tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                }
            }

            // Edit modal color picker sync
            $('#editColorPicker').on('input', function() {
                $('#editColor').val(this.value);
            });

            $('#editColor').on('input', function() {
                if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
                    $('#editColorPicker').val(this.value);
                }
            });

            // Edit code form submit loading state
            const editCodeForm = document.getElementById('editCodeForm');
            const updateCodeBtn = document.getElementById('updateCodeBtn');

            if (editCodeForm && updateCodeBtn) {
                editCodeForm.addEventListener('submit', function() {
                    updateCodeBtn.classList.add('loading');
                    updateCodeBtn.disabled = true;
                });
            }

            // Add holiday form loading state
            const addHolidayForm = document.getElementById('addHolidayForm');
            const addHolidayBtn = document.getElementById('addHolidayBtn');

            if (addHolidayForm && addHolidayBtn) {
                addHolidayForm.addEventListener('submit', function() {
                    addHolidayBtn.classList.add('loading');
                    addHolidayBtn.disabled = true;
                });
            }

            // Edit holiday form loading state
            const editHolidayForm = document.getElementById('editHolidayForm');
            const updateHolidayBtn = document.getElementById('updateHolidayBtn');

            if (editHolidayForm && updateHolidayBtn) {
                editHolidayForm.addEventListener('submit', function() {
                    updateHolidayBtn.classList.add('loading');
                    updateHolidayBtn.disabled = true;
                });
            }

            // Reset add holiday modal when opened
            $('#addHolidayModal').on('show.bs.modal', function() {
                // Reset form fields
                $('#addHolidayName').val('');
                $('#addHolidayStart').val('');
                $('#addHolidayEnd').val('');

                // Reset loading state
                if (addHolidayBtn) {
                    addHolidayBtn.classList.remove('loading');
                    addHolidayBtn.disabled = false;
                }

                // Sync modal term selector with main page term selector
                var termId = $('#termId').val();
                $('#addHolidayTermId').val(termId);
                // Update hidden year field
                var year = $('#addHolidayTermId').find(':selected').data('year');
                $('#addHolidayYear').val(year);
            });

            // Update hidden year field when modal term selector changes
            $('#addHolidayTermId').change(function() {
                var year = $(this).find(':selected').data('year');
                $('#addHolidayYear').val(year);
            });

            // Holiday list loading
            var holidayListRoute = "{{ route('attendance.holiday-list', ':termId') }}";

            function updateHolidayList() {
                var termId = $('#termId').val();
                var url = holidayListRoute.replace(':termId', encodeURIComponent(termId));
                $.get(url, function(data) {
                    $('#holiday-list').html(data);
                });
            }

            // Update holiday list when term changes
            $('#termId').change(function() {
                updateHolidayList();
            }).trigger('change');
        });

        function openEditCodeModal(code) {
            $('#editCode').val(code.code);
            $('#editDescription').val(code.description);
            $('#editColor').val(code.color);
            $('#editColorPicker').val(code.color);
            $('#editIsPresent').prop('checked', code.is_present);
            $('#editIsActive').prop('checked', code.is_active);

            const formAction = "{{ route('attendance.codes.update', ':id') }}";
            $('#editCodeForm').attr('action', formAction.replace(':id', code.id));

            // Reset loading state when modal opens
            const updateCodeBtn = document.getElementById('updateCodeBtn');
            if (updateCodeBtn) {
                updateCodeBtn.classList.remove('loading');
                updateCodeBtn.disabled = false;
            }

            $('#editCodeModal').modal('show');
        }

        function openEditHolidayModal(element) {
            const id = $(element).data('id');
            const name = $(element).data('name');
            const termId = $(element).data('term-id');
            const start = $(element).data('start');
            const end = $(element).data('end');

            $('#holidayId').val(id);
            $('#holidayName').val(name);
            $('#editHolidayTermId').val(termId);
            $('#holidayStart').val(start);
            $('#holidayEnd').val(end);

            // Update hidden year field based on selected term
            var year = $('#editHolidayTermId').find(':selected').data('year');
            $('#editHolidayYear').val(year);

            const formAction = "{{ route('holidays.update-holiday', ':id') }}";
            $('#editHolidayForm').attr('action', formAction.replace(':id', id));

            // Reset loading state when modal opens
            const updateHolidayBtn = document.getElementById('updateHolidayBtn');
            if (updateHolidayBtn) {
                updateHolidayBtn.classList.remove('loading');
                updateHolidayBtn.disabled = false;
            }

            $('#editHolidayModal').modal('show');
        }

        // Update hidden year field when edit modal term selector changes
        $('#editHolidayTermId').change(function() {
            var year = $(this).find(':selected').data('year');
            $('#editHolidayYear').val(year);
        });
    </script>
@endsection
