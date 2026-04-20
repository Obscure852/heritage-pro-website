@extends('layouts.master')

@section('title')
    Bulk Role Allocation
@endsection

@section('css')
    <style>
        .roles-container {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .roles-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .roles-body {
            padding: 24px;
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

        /* Card */
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
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

        /* Form Elements */
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
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

        /* Buttons */
        .btn {
            padding: 10px 16px;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
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

        .btn-outline-primary {
            background: transparent;
            border: 1px solid #3b82f6;
            color: #3b82f6;
        }

        .btn-outline-primary:hover {
            background: #3b82f6;
            color: white;
            transform: translateY(-1px);
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

        /* Badge */
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: 500;
            font-size: 12px;
        }

        /* Info Alert */
        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            border-radius: 3px;
        }

        .required::after {
            content: '*';
            color: #dc2626;
            margin-left: 4px;
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
            <a href="{{ route('staff.index') }}">Back</a>
        @endslot
        @slot('title')
            Bulk Role Allocation
        @endslot
    @endcomponent

    @if (session('info'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-information-outline label-icon"></i><strong>{{ session('info') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('message'))
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row">
            <div class="col-md-12">
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

    <div class="roles-container">
        <div class="roles-header">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <h3 style="margin:0;">Bulk Role Allocation</h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">Allocate roles to multiple staff members at once</p>
                </div>
            </div>
        </div>
        <div class="roles-body">
            @can('canAllocateRoles')
                @php
                    $moduleVisibility = app(\App\Services\ModuleVisibilityService::class);
                    $visibleRoles = $moduleVisibility->getVisibleRoles($roles);
                @endphp
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Select Role <span class="required"></span></h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('staff.allocate-bulk-roles') }}" id="bulk-allocation-form">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="bulk_role_id" class="form-label">Role to Allocate</label>
                                        <select name="role_id" id="bulk_role_id" class="form-select" required>
                                            <option value="" selected disabled>Choose role ...</option>
                                            @foreach ($visibleRoles as $role)
                                                <option value="{{ $role->id }}" data-role-name="{{ $role->name }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted mt-1 d-block role-selection-info">
                                            Only showing staff who do not already have the selected role.
                                        </small>
                                    </div>

                                    <div class="alert alert-info">
                                        <div class="d-flex">
                                            <i class="fas fa-info-circle me-2 fs-5"></i>
                                            <div>
                                                <strong>Quick Actions:</strong>
                                                <div class="mt-2 d-flex flex-column gap-2">
                                                    <a href="{{ route('staff.allocate-teachers-roles') }}"
                                                       class="btn btn-sm btn-outline-primary text-start"
                                                       onclick="event.preventDefault(); document.getElementById('teaching-staff-form').submit();">
                                                        <i class="fas fa-check-square me-1"></i> Allocate Teacher Role to All Teaching Staff
                                                    </a>

                                                    <a href="{{ route('staff.allocate-class-teachers-roles') }}"
                                                       class="btn btn-sm btn-outline-primary text-start"
                                                       onclick="event.preventDefault(); document.getElementById('class-teachers-form').submit();">
                                                        <i class="fas fa-check-square me-1"></i> Allocate Class Teacher Role to All Class Teachers
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid mt-3">
                                        <button type="submit" id="submit-btn" class="btn btn-primary btn-loading" disabled>
                                            <span class="btn-text">
                                                <i class="fas fa-user-plus me-1"></i>
                                                Allocate Role to Selected Staff
                                            </span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                Allocating...
                                            </span>
                                        </button>
                                    </div>
                                </form>

                                <!-- Hidden forms for quick actions -->
                                <form id="teaching-staff-form" action="{{ route('staff.allocate-teachers-roles') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>

                                <form id="class-teachers-form" action="{{ route('staff.allocate-class-teachers-roles') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-light">
                                <ul class="nav nav-tabs nav-tabs-custom card-header-tabs border-bottom-0" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#teaching-tab" role="tab" aria-selected="true">
                                            <i class="fas fa-chalkboard-teacher me-1"></i> Teaching Staff
                                            <span class="badge bg-primary rounded-pill ms-1 tab-counter" id="teaching-tab-counter">0</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#class-teachers-tab" role="tab" aria-selected="false">
                                            <i class="fas fa-users me-1"></i> Class Teachers
                                            <span class="badge bg-primary rounded-pill ms-1 tab-counter" id="class-teachers-tab-counter">0</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#all-staff-tab" role="tab" aria-selected="false">
                                            <i class="fas fa-user-tie me-1"></i> All Staff
                                            <span class="badge bg-primary rounded-pill ms-1 tab-counter" id="all-staff-tab-counter">0</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="tab-pane active" id="teaching-tab" role="tabpanel">
                                        <div class="d-flex justify-content-between mb-3">
                                            <div class="position-relative" style="width: 250px;">
                                                <input type="text" class="form-control search-input" data-target="teaching-table" placeholder="Search teaching staff...">
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle" id="teaching-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px;" class="text-center">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input header-checkbox" data-group="teaching">
                                                            </div>
                                                        </th>
                                                        <th>Name</th>
                                                        <th>Position</th>
                                                        <th>Current Roles</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($teachingStaff as $staff)
                                                    <tr class="staff-row" data-staff-id="{{ $staff->id }}" data-staff-roles="{{ json_encode($staff->roles->pluck('id')) }}">
                                                        <td class="text-center">
                                                            <div class="form-check">
                                                                <input class="form-check-input staff-checkbox teaching-checkbox" type="checkbox" name="selected_users[]" value="{{ $staff->id }}" id="staff-{{ $staff->id }}" form="bulk-allocation-form">
                                                            </div>
                                                        </td>
                                                        <td>{{ $staff->fullName }}</td>
                                                        <td>{{ $staff->position }}</td>
                                                        <td>
                                                            @foreach ($staff->roles as $role)
                                                                <span class="badge bg-primary rounded-pill">{{ $role->name }}</span>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="teaching-no-data" class="alert alert-info mt-3 d-none">
                                            <i class="fas fa-info-circle me-2"></i> All teaching staff already have the selected role.
                                        </div>
                                    </div>

                                    <div class="tab-pane" id="class-teachers-tab" role="tabpanel">
                                        <div class="d-flex justify-content-between mb-3">
                                            <div class="position-relative" style="width: 250px;">
                                                <input type="text" class="form-control search-input" data-target="class-teachers-table" placeholder="Search class teachers...">
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle" id="class-teachers-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px;" class="text-center">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input header-checkbox" data-group="class-teachers">
                                                            </div>
                                                        </th>
                                                        <th>Name</th>
                                                        <th>Class</th>
                                                        <th>Current Roles</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($classTeachers as $staff)
                                                    <tr class="staff-row" data-staff-id="{{ $staff->id }}" data-staff-roles="{{ json_encode($staff->roles->pluck('id')) }}">
                                                        <td class="text-center">
                                                            <div class="form-check">
                                                                <input class="form-check-input staff-checkbox class-teachers-checkbox" type="checkbox" name="selected_users[]" value="{{ $staff->id }}" id="staff-class-{{ $staff->id }}" form="bulk-allocation-form">
                                                            </div>
                                                        </td>
                                                        <td>{{ $staff->fullName }}</td>
                                                        <td>
                                                            @foreach($staff->klass as $class)
                                                                <span class="badge bg-info rounded-pill">{{ $class->name }}</span>
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            @foreach ($staff->roles as $role)
                                                                <span class="badge bg-primary rounded-pill">{{ $role->name }}</span>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="class-teachers-no-data" class="alert alert-info mt-3 d-none">
                                            <i class="fas fa-info-circle me-2"></i> All class teachers already have the selected role.
                                        </div>
                                    </div>

                                    <div class="tab-pane" id="all-staff-tab" role="tabpanel">
                                        <div class="d-flex justify-content-between mb-3">
                                            <div class="position-relative" style="width: 250px;">
                                                <input type="text" class="form-control search-input" data-target="all-staff-table" placeholder="Search all staff...">
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle" id="all-staff-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px;" class="text-center">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input header-checkbox" data-group="all-staff">
                                                            </div>
                                                        </th>
                                                        <th>Name</th>
                                                        <th>Position</th>
                                                        <th>Area of Work</th>
                                                        <th>Current Roles</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($allStaff as $staff)
                                                    <tr class="staff-row" data-staff-id="{{ $staff->id }}" data-staff-roles="{{ json_encode($staff->roles->pluck('id')) }}">
                                                        <td class="text-center">
                                                            <div class="form-check">
                                                                <input class="form-check-input staff-checkbox all-staff-checkbox" type="checkbox" name="selected_users[]" value="{{ $staff->id }}" id="staff-all-{{ $staff->id }}" form="bulk-allocation-form">
                                                            </div>
                                                        </td>
                                                        <td>{{ $staff->fullName }}</td>
                                                        <td>{{ $staff->position }}</td>
                                                        <td>{{ $staff->area_of_work }}</td>
                                                        <td>
                                                            @foreach ($staff->roles as $role)
                                                                <span class="badge bg-primary rounded-pill">{{ $role->name }}</span>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="all-staff-no-data" class="alert alert-info mt-3 d-none">
                                            <i class="fas fa-info-circle me-2"></i> All staff already have the selected role.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    You do not have permission to allocate roles.
                </div>
            @endcan
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInputs = document.querySelectorAll('.search-input');
            searchInputs.forEach(input => {
                const icon = document.createElement('i');
                icon.className = 'fas fa-search';
                icon.style.position = 'absolute';
                icon.style.right = '10px';
                icon.style.top = '50%';
                icon.style.transform = 'translateY(-50%)';
                icon.style.color = '#adb5bd';

                input.parentNode.appendChild(icon);
            });

            document.querySelector('.role-selection-info').style.display = 'none';
            document.getElementById('bulk_role_id').addEventListener('change', function() {
                const selectedRoleId = this.value;
                const selectedRoleName = this.options[this.selectedIndex].getAttribute('data-role-name');

                if (selectedRoleId) {
                    document.querySelector('.role-selection-info').style.display = 'block';
                    filterStaffByRole(selectedRoleId, selectedRoleName);
                } else {
                    document.querySelector('.role-selection-info').style.display = 'none';

                    document.querySelectorAll('.staff-row').forEach(row => {
                        row.style.display = '';
                        if (row.hasAttribute('data-hidden-by-search')) {
                            row.removeAttribute('data-hidden-by-search');
                        }
                    });

                    document.querySelectorAll('.tab-counter').forEach(counter => {
                        counter.textContent = '0';
                    });

                    document.querySelectorAll('#teaching-no-data, #class-teachers-no-data, #all-staff-no-data').forEach(el => {
                        el.classList.add('d-none');
                    });
                }

                document.querySelectorAll('.header-checkbox, .staff-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });

                document.querySelectorAll('.search-input').forEach(input => {
                    input.value = '';
                });
                updateSelectedCount();
            });

            function filterStaffByRole(roleId, roleName) {
                const tables = ['teaching', 'class-teachers', 'all-staff'];
                tables.forEach(tableType => {
                    const tableRows = document.querySelectorAll(`#${tableType}-table tbody tr`);
                    let visibleCount = 0;

                    tableRows.forEach(row => {
                        const staffRoles = JSON.parse(row.getAttribute('data-staff-roles'));

                        if (row.hasAttribute('data-hidden-by-search')) {
                            row.removeAttribute('data-hidden-by-search');
                        }

                        if (staffRoles.includes(parseInt(roleId))) {
                            row.style.display = 'none';
                        } else {
                            row.style.display = '';
                            visibleCount++;
                        }
                    });

                    document.getElementById(`${tableType}-tab-counter`).textContent = visibleCount;

                    if (visibleCount === 0) {
                        document.getElementById(`${tableType}-no-data`).classList.remove('d-none');
                    } else {
                        document.getElementById(`${tableType}-no-data`).classList.add('d-none');
                    }

                    updateHeaderCheckbox(`${tableType}-table`);
                });
            }

            document.querySelectorAll('.search-input').forEach(input => {
                input.addEventListener('keyup', function() {
                    const value = this.value.toLowerCase().trim();
                    const tableId = this.getAttribute('data-target');
                    const table = document.getElementById(tableId);
                    const rows = table.querySelectorAll('tbody tr');
                    let visibleCount = 0;

                    rows.forEach(row => {
                        const isHiddenByRoleFilter = row.style.display === 'none' &&
                                                  !row.hasAttribute('data-hidden-by-search');

                        if (isHiddenByRoleFilter) {
                            return;
                        }

                        if (row.hasAttribute('data-hidden-by-search')) {
                            row.removeAttribute('data-hidden-by-search');
                        }

                        const textContent = row.textContent.toLowerCase();

                        if (value === '') {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            const shouldShow = textContent.includes(value);
                            if (!shouldShow) {
                                row.style.display = 'none';
                                row.setAttribute('data-hidden-by-search', 'true');
                            } else {
                                row.style.display = '';
                                visibleCount++;
                            }
                        }
                    });

                    const tableType = tableId.replace('-table', '');
                    if (visibleCount === 0) {
                        document.getElementById(`${tableType}-no-data`).classList.remove('d-none');
                    } else {
                        document.getElementById(`${tableType}-no-data`).classList.add('d-none');
                    }

                    updateHeaderCheckbox(tableId);
                });
            });

            document.querySelectorAll('.header-checkbox').forEach(headerCheckbox => {
                headerCheckbox.addEventListener('change', function() {
                    const group = this.getAttribute('data-group');
                    const tableId = this.closest('table').id;
                    const checkboxes = document.querySelectorAll(`#${tableId} .${group}-checkbox`);

                    checkboxes.forEach(checkbox => {
                        if (checkbox.closest('tr').style.display !== 'none') {
                            checkbox.checked = this.checked;
                        }
                    });

                    updateSelectedCount();
                });
            });

            function updateHeaderCheckbox(tableId) {
                const table = document.getElementById(tableId);
                const headerCheckbox = table.querySelector('.header-checkbox');

                if (!headerCheckbox) return;

                const group = headerCheckbox.getAttribute('data-group');

                const visibleCheckboxes = Array.from(table.querySelectorAll(`.${group}-checkbox`))
                    .filter(checkbox => checkbox.closest('tr').style.display !== 'none');

                if (visibleCheckboxes.length === 0) {
                    headerCheckbox.checked = false;
                    headerCheckbox.indeterminate = false;
                } else {
                    const allChecked = visibleCheckboxes.every(checkbox => checkbox.checked);
                    const someChecked = visibleCheckboxes.some(checkbox => checkbox.checked);

                    headerCheckbox.checked = allChecked;
                    headerCheckbox.indeterminate = someChecked && !allChecked;
                }
            }

            document.querySelectorAll('.staff-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const tableId = this.closest('table').id;
                    updateHeaderCheckbox(tableId);
                    updateSelectedCount();
                });
            });

            function updateSelectedCount() {
                const totalSelected = document.querySelectorAll('.staff-checkbox:checked').length;
                const submitBtn = document.getElementById('submit-btn');
                const btnText = submitBtn.querySelector('.btn-text');

                if (totalSelected > 0) {
                    btnText.innerHTML = `<i class="fas fa-user-plus me-1"></i> Allocate Role to ${totalSelected} Selected Staff`;
                    submitBtn.disabled = false;
                } else {
                    btnText.innerHTML = `<i class="fas fa-user-plus me-1"></i> Allocate Role to Selected Staff`;
                    submitBtn.disabled = true;
                }
            }

            // Form submit loading handler
            document.getElementById('bulk-allocation-form').addEventListener('submit', function(e) {
                const submitBtn = document.getElementById('submit-btn');
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });

            // Quick action forms loading handler
            document.getElementById('teaching-staff-form').addEventListener('submit', function() {
                const btn = document.querySelector('a[onclick*="teaching-staff-form"]');
                if (btn) {
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Allocating...';
                    btn.style.pointerEvents = 'none';
                }
            });

            document.getElementById('class-teachers-form').addEventListener('submit', function() {
                const btn = document.querySelector('a[onclick*="class-teachers-form"]');
                if (btn) {
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Allocating...';
                    btn.style.pointerEvents = 'none';
                }
            });

            document.querySelectorAll('.tab-counter').forEach(counter => {
                counter.textContent = counter.closest('.nav-link').getAttribute('href').replace('#', '').replace('-tab', '-table');
                const tableId = counter.textContent;
                counter.textContent = document.querySelectorAll(`#${tableId} tbody tr`).length;
            });
        });
    </script>
@endsection
