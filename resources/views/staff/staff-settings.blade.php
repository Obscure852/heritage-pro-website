@extends('layouts.master')
@section('title')
    Staff Settings
@endsection
<?php $errors = $errors ?? new \Illuminate\Support\ViewErrorBag(); ?>

@section('css')
    <style>
        .staff-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
        }

        .staff-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .staff-body {
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
            border-left: 4px solid #4e73df;
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
        }

        .form-control,
        .form-select,
        textarea.form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus,
        textarea.form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
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

        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            color: #495057;
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

        /* Action Buttons (Table) */
        .action-buttons {
            display: flex;
            gap: 4px;
            justify-content: center;
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

        .required::after {
            content: '*';
            color: #dc2626;
            margin-left: 4px;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: 500;
            font-size: 12px;
        }

        .avatar-small {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 48px;
            opacity: 0.3;
            margin-bottom: 16px;
        }

        .table-inline-input {
            min-width: 120px;
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
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ route('staff.index') }}">Back</a>
        @endslot
        @slot('title')
            Settings
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <div class="staff-container">
        <div class="staff-header">
            <h4 class="mb-1 text-white"><i class="bx bx-cog me-2"></i>Staff Settings</h4>
            <p class="mb-0 opacity-75">Manage departments, filters, qualifications, earning bands, and general settings</p>
        </div>
        <div class="staff-body">
            <div class="card">
                <div class="card-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#departments" role="tab"
                                id="tab-departments">
                                <i class="bx bx-building me-2 text-muted"></i>
                                <span>Departments</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#filters" role="tab" id="tab-filters">
                                <i class="bx bx-filter-alt me-2 text-muted"></i>
                                <span>Filters</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#qualifications" role="tab"
                                id="tab-qualifications">
                                <i class="bx bx-graduation me-2 text-muted"></i>
                                <span>Qualifications</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#earning-bands" role="tab"
                                id="tab-earning-bands">
                                <i class="bx bx-layer me-2 text-muted"></i>
                                <span>Earning Bands</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#settings" role="tab" id="tab-settings">
                                <i class="bx bx-slider me-2 text-muted"></i>
                                <span>General Settings</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3">
                        <div class="tab-pane" id="departments" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Departments</div>
                                <div class="help-content">
                                    Organize your staff into departments. Each department can have a department head and associated subjects.
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12 d-flex justify-content-end">
                                    <a href="{{ route('staff.show-department') }}" class="btn btn-primary">
                                        <i class="bx bx-plus"></i> Add Department
                                    </a>
                                </div>
                            </div>
                            @if ($departments->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th style="width: 60px;" class="text-center">#</th>
                                                <th>Department Name</th>
                                                <th>Department Head</th>
                                                <th>Subjects</th>
                                                <th style="width: 100px;" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($departments as $index => $department)
                                                <tr>
                                                    <td class="text-center">{{ $index + 1 }}</td>
                                                    <td><strong>{{ $department->name ?? '' }}</strong></td>
                                                    <td>
                                                        @if ($department->departmentHead)
                                                            <div class="d-flex align-items-center">
                                                                <div class="avatar-small">
                                                                    {{ substr($department->departmentHead->fullName ?? '', 0, 1) }}
                                                                </div>
                                                                {{ $department->departmentHead->fullName ?? '' }}
                                                            </div>
                                                        @else
                                                            <span class="text-muted">Not assigned</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $groupedSubjects = $department->gradeSubjects->groupBy(
                                                                'subject.name',
                                                            );
                                                        @endphp
                                                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                                            @foreach ($groupedSubjects as $subjectName => $gradeSubjects)
                                                                <span class="badge bg-success">
                                                                    {{ $subjectName }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <a href="{{ route('staff.edit-department', $department->id) }}"
                                                                class="btn btn-sm btn-outline-info"
                                                                title="Edit">
                                                                <i class="bx bx-edit-alt"></i>
                                                            </a>

                                                            <form method="POST"
                                                                action="{{ route('staff.delete-department', $department->id) }}"
                                                                class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this department?')">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="bx bx-building"></i>
                                    <h5 class="mb-2">No Departments Found</h5>
                                    <p class="mb-0">Get started by adding your first department.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Filters Tab -->
                        <div class="tab-pane" id="filters" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Staff Filters</div>
                                <div class="help-content">
                                    Create custom filters to categorize staff (e.g., Committee Members, Club Advisors). These filters can be used when searching and reporting on staff.
                                </div>
                            </div>

                            <form action="{{ route('filters.store-filter') }}" method="POST" class="mb-4">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="name"
                                                placeholder="Enter filter name..." required>
                                            <button class="btn btn-primary" type="submit">
                                                <i class="bx bx-plus"></i> Add Filter
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            @if ($filters->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th style="width: 60px;">#</th>
                                                <th>Name</th>
                                                <th style="width: 100px;" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($filters as $index => $filter)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td><strong>{{ $filter->name ?? '' }}</strong></td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button type="button" class="btn btn-sm btn-outline-info edit-filter"
                                                                data-bs-toggle="modal" data-bs-target="#editFilterModal"
                                                                data-id="{{ $filter->id }}"
                                                                data-name="{{ $filter->name }}">
                                                                <i class="bx bx-edit-alt"></i>
                                                            </button>

                                                            <form method="POST"
                                                                action="{{ route('filters.destroy-filter', $filter->id) }}"
                                                                class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this filter?')">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="bx bx-filter-alt"></i>
                                    <h5 class="mb-2">No Filters Found</h5>
                                    <p class="mb-0">Get started by adding your first filter.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Qualifications Tab -->
                        <div class="tab-pane" id="qualifications" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Qualifications</div>
                                <div class="help-content">
                                    Manage the list of qualifications that can be assigned to staff members. Each qualification has a code and full name.
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12 d-flex justify-content-end">
                                    <a href="{{ route('staff.show-qualification') }}" class="btn btn-primary">
                                        <i class="bx bx-plus"></i> Add Qualification
                                    </a>
                                </div>
                            </div>
                            @if ($qualifications->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th style="width: 60px;" class="text-center">#</th>
                                                <th>Qualification Code</th>
                                                <th>Qualification Name</th>
                                                <th style="width: 100px;" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($qualifications as $index => $qualification)
                                                <tr>
                                                    <td class="text-center">{{ $index + 1 }}</td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            {{ $qualification->qualification_code ?? '' }}
                                                        </span>
                                                    </td>
                                                    <td><strong>{{ $qualification->qualification ?? '' }}</strong></td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <a href="{{ route('staff.edit-qualification', $qualification->id) }}"
                                                                class="btn btn-sm btn-outline-info"
                                                                title="Edit">
                                                                <i class="bx bx-edit-alt"></i>
                                                            </a>

                                                            <form method="POST"
                                                                action="{{ route('staff.delete-qualification', $qualification->id) }}"
                                                                class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this qualification?')">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="bx bx-graduation"></i>
                                    <h5 class="mb-2">No Qualifications Found</h5>
                                    <p class="mb-0">Get started by adding your first qualification.</p>
                                </div>
                            @endif
                        </div>

                        <div class="tab-pane" id="earning-bands" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">Earning Bands</div>
                                <div class="help-content">
                                    Maintain the selectable government-style grade and earning band options used on staff profiles. Changes here update the staff create and edit forms immediately.
                                </div>
                            </div>

                            <form action="{{ route('staff.earning-bands.store') }}" method="POST" class="mb-4">
                                @csrf
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label" for="earning_band_name">Band Name</label>
                                        <input type="text" id="earning_band_name" name="band_name" class="form-control"
                                            value="{{ old('band_name') }}" placeholder="B4" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="earning_band_sort_order">Sort Order</label>
                                        <input type="number" id="earning_band_sort_order" name="sort_order"
                                            class="form-control" value="{{ old('sort_order', ($earningBands->max('sort_order') ?? 0) + 1) }}"
                                            min="1" max="999">
                                    </div>
                                    <div class="col-md-4 d-flex justify-content-md-end">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bx bx-plus"></i> Add Earning Band
                                        </button>
                                    </div>
                                </div>
                            </form>

                            @if ($earningBands->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th style="width: 60px;" class="text-center">#</th>
                                                <th>Band Name</th>
                                                <th style="width: 140px;">Sort Order</th>
                                                <th style="width: 140px;">Assigned Staff</th>
                                                <th style="width: 180px;" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($earningBands as $index => $earningBand)
                                                <tr>
                                                    <td class="text-center">{{ $index + 1 }}</td>
                                                    <td>
                                                        <form id="earning-band-form-{{ $earningBand->id }}" method="POST"
                                                            action="{{ route('staff.earning-bands.update', $earningBand->id) }}">
                                                            @csrf
                                                            <input type="text" name="band_name"
                                                                class="form-control table-inline-input"
                                                                value="{{ $earningBand->name }}" maxlength="50" required>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="sort_order"
                                                            class="form-control table-inline-input"
                                                            value="{{ $earningBand->sort_order }}" min="1"
                                                            max="999" form="earning-band-form-{{ $earningBand->id }}">
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            {{ $earningBand->users_count ?? 0 }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button type="submit" class="btn btn-sm btn-outline-info"
                                                                title="Save"
                                                                form="earning-band-form-{{ $earningBand->id }}">
                                                                <i class="fas fa-save"></i>
                                                            </button>

                                                            <form method="POST"
                                                                action="{{ route('staff.earning-bands.destroy', $earningBand->id) }}"
                                                                class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this earning band?')"
                                                                    title="Delete">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="bx bx-layer"></i>
                                    <h5 class="mb-2">No Earning Bands Found</h5>
                                    <p class="mb-0">Add your first earning band to make it available on staff profiles.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Settings Tab -->
                        <div class="tab-pane" id="settings" role="tabpanel">
                            <div class="help-text">
                                <div class="help-title">General Settings</div>
                                <div class="help-content">
                                    Configure system-wide staff profile settings and requirements.
                                </div>
                            </div>

                            <form action="{{ route('staff.settings.force-profile-update') }}" method="POST" id="forceProfileForm">
                                @csrf
                                <div class="section-title">Force Staff Profile Update</div>

                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="forceUpdateToggle" name="force_update_enabled" value="1"
                                            {{ $forceUpdateEnabled ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="forceUpdateToggle">
                                            Force staff to complete their profile before accessing the system
                                        </label>
                                    </div>
                                    <small class="text-muted d-block mt-1">When enabled, staff members (excluding administrators) will be required to fill in the selected profile sections before they can access any other part of the system.</small>
                                </div>

                                <div id="requiredSectionsContainer" style="{{ $forceUpdateEnabled ? '' : 'display:none;' }}">
                                    <label class="form-label fw-semibold mb-3">Required Sections</label>
                                    @foreach ($profileSectionOptions as $key => $section)
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" name="required_sections[]" value="{{ $key }}"
                                                id="section_{{ $key }}"
                                                {{ in_array($key, $forceUpdateSections) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="section_{{ $key }}">
                                                <span class="fw-semibold">{{ $section['label'] }}</span>
                                                <br><small class="text-muted">{{ $section['description'] }}</small>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save Settings</span>
                                        <span class="btn-spinner d-none">
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
        </div>
    </div>

    <!-- Edit Filter Modal -->
    <div class="modal fade" id="editFilterModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editFilterForm">
                        <div class="mb-3">
                            <label for="editFilterName" class="form-label">Filter Name</label>
                            <input type="text" class="form-control" id="editFilterName" name="filterName" required>
                        </div>
                        <input type="hidden" id="editFilterId" name="filterId">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="saveFilterBtn" class="btn btn-primary btn-loading">
                                <span class="btn-text">Save Changes</span>
                                <span class="btn-spinner d-none">
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
        document.addEventListener('DOMContentLoaded', function() {
            function initializeTabs() {
                const activeTab = localStorage.getItem('activeHrTab') || 'departments';

                document.querySelectorAll('.nav-link').forEach(tab => tab.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));

                const selectedTab = document.querySelector(`#tab-${activeTab}`);
                const selectedPane = document.querySelector(`#${activeTab}`);

                if (selectedTab && selectedPane) {
                    selectedTab.classList.add('active');
                    selectedPane.classList.add('active');
                } else {
                    document.querySelector('#tab-departments').classList.add('active');
                    document.querySelector('#departments').classList.add('active');
                }
            }

            document.querySelectorAll('.nav-link').forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('href').substring(1);
                    localStorage.setItem('activeHrTab', tabId);
                });
            });

            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    const currentTab = localStorage.getItem('activeHrTab');
                    if (currentTab) {
                        localStorage.setItem('activeHrTab', currentTab);
                    }
                });
            });

            const editFilterModal = document.getElementById('editFilterModal');
            const editFilterForm = document.getElementById('editFilterForm');

            document.querySelectorAll('.edit-filter').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const filterId = this.getAttribute('data-id');
                    const filterName = this.getAttribute('data-name');

                    document.getElementById('editFilterId').value = filterId;
                    document.getElementById('editFilterName').value = filterName;
                });
            });

            // Filter form submission handler
            editFilterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const filterId = document.getElementById('editFilterId').value;
                const filterName = document.getElementById('editFilterName').value;
                const saveBtn = document.getElementById('saveFilterBtn');

                if (!filterName.trim()) {
                    alert('Filter name cannot be empty');
                    return;
                }

                // Show loading state
                saveBtn.classList.add('loading');
                saveBtn.disabled = true;

                const updateFilterUrl =
                    `{{ route('filters.update-filter', ['id' => ':tempFilterId']) }}`
                    .replace(':tempFilterId', filterId);

                fetch(updateFilterUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            name: filterName
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            const modal = bootstrap.Modal.getInstance(editFilterModal);
                            modal.hide();
                            const alertHtml = `
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="mdi mdi-check-all me-2"></i>Filter updated successfully
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                            document.querySelector('.staff-body').insertAdjacentHTML('afterbegin',
                                alertHtml);
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            throw new Error(data.message || 'Error updating filter');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error updating filter: ' + error.message);
                        // Reset loading state on error
                        saveBtn.classList.remove('loading');
                        saveBtn.disabled = false;
                    });
            });

            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });

            initializeTabs();

            // Force Profile Update toggle
            const forceToggle = document.getElementById('forceUpdateToggle');
            const sectionsContainer = document.getElementById('requiredSectionsContainer');
            if (forceToggle && sectionsContainer) {
                forceToggle.addEventListener('change', function() {
                    sectionsContainer.style.display = this.checked ? '' : 'none';
                });
            }

            // Loading state for force profile form
            const forceForm = document.getElementById('forceProfileForm');
            if (forceForm) {
                forceForm.addEventListener('submit', function() {
                    const submitBtn = forceForm.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            }
        });
    </script>
@endsection
