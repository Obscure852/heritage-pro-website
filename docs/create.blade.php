@extends('layouts.master')
@section('title')
    Create Staff
@endsection
@section('css')
    <style>
        .container-box {
            background: white;
            border-radius: 3px;
            padding: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
        }

        .header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 28px;
            border-radius: 3px 3px 0 0;
        }

        .body {
            padding: 32px;
        }

        .form-section {
            margin-bottom: 28px;
        }

        .form-section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
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

        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select,
        .form-textarea {
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

        .text-danger {
            font-size: 12px;
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
        }

        .btn-light {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-light:hover {
            background: #e9ecef;
            color: #495057;
            transform: translateY(-1px);
        }

        .required::after {
            content: '*';
            color: #dc2626;
            margin-left: 4px;
        }

        /* Match students edit layout */
        .form-container {
            background: white;
            border-radius: 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .page-title {
            font-size: 22px;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        /* Profile image widget (match students edit) */
        .profile-image-section {
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            height: fit-content;
        }

        .profile-image-container {
            text-align: center;
            position: relative;
        }

        .image-upload-area {
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 12px;
        }

        .image-upload-area:hover {
            transform: translateY(-2px);
        }

        .profile-preview {
            width: 100%;
            height: auto;
            max-width: 250px;
            max-height: 250px;
            min-width: 150px;
            min-height: 150px;
            aspect-ratio: 1;
            object-fit: cover;
            transition: all 0.3s ease;
            cursor: pointer;
            border-radius: 3px;
        }

        .profile-preview:hover {
            border-color: #3b82f6;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }

        .no-image-placeholder {
            width: 100%;
            height: auto;
            max-width: 250px;
            max-height: 250px;
            min-width: 150px;
            min-height: 150px;
            aspect-ratio: 1;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border-radius: 3px;
        }

        .no-image-placeholder:hover {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
            transform: scale(1.02);
        }

        .no-image-placeholder i {
            font-size: 52px;
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }

        .no-image-placeholder:hover i {
            font-size: 56px;
            color: #3b82f6;
        }

        .no-image-placeholder .upload-text {
            font-size: 10px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .image-overlay {
            position: absolute;
            inset: 0;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .image-upload-area:hover .image-overlay {
            opacity: 1;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .profile-preview {
                max-width: 200px;
                max-height: 200px;
                min-width: 120px;
                min-height: 120px;
            }

            .no-image-placeholder {
                max-width: 200px;
                max-height: 200px;
                min-width: 120px;
                min-height: 120px;
            }

            .no-image-placeholder i {
                font-size: 40px;
                margin-bottom: 8px;
            }

            .no-image-placeholder .upload-text {
                font-size: 9px;
            }
        }

        @media (max-width: 576px) {
            .profile-preview {
                max-width: 150px;
                max-height: 150px;
                min-width: 100px;
                min-height: 100px;
            }

            .no-image-placeholder {
                max-width: 150px;
                max-height: 150px;
                min-width: 100px;
                min-height: 100px;
            }

            .no-image-placeholder i {
                font-size: 32px;
                margin-bottom: 6px;
            }

            .no-image-placeholder .upload-text {
                font-size: 8px;
                margin-bottom: 2px;
            }

            .file-info {
                font-size: 10px;
            }
        }

        #profileImageInput {
            display: none;
        }

        .file-info {
            padding: 2px;
            margin-top: 4px;
            font-size: 11px;
            color: #64748b;
            text-align: center;
        }

        .drag-active {
            border-color: #3b82f6 !important;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%) !important;
            transform: scale(1.05) !important;
        }

        @media (max-width: 768px) {
            .body {
                padding: 16px;
            }

            .header {
                padding: 16px;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('staff.index') }}">Back</a>
        @endslot
        @slot('title')
            Human Resources
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-12">
            @if (session('message'))
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-warning alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-alert-circle label-icon"></i>
                    <strong>Please correct the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="form-container">
                <div class="page-header">
                    <h4 class="page-title text-muted">Create User</h4>
                </div>
                <form action="{{ route('staff.staff-store') }}" method="POST" id="createStaffForm"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="form-section">
                        <div class="help-text">
                            <div class="help-title">Basic Information</div>
                            <div class="help-content">Basic details about the staff member including names and contact.
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-10">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label required">First Name</label>
                                        <input type="text" name="firstname" class="form-control"
                                            value="{{ old('firstname') }}" placeholder="First name">
                                        @error('firstname')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" name="middlename" class="form-control"
                                            value="{{ old('middlename') }}" placeholder="Middle name (optional)">
                                        @error('middlename')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label required">Last Name</label>
                                        <input type="text" name="lastname" class="form-control"
                                            value="{{ old('lastname') }}" placeholder="Last name">
                                        @error('lastname')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label required">Email</label>
                                        <input type="email" name="email" class="form-control"
                                            value="{{ old('email') }}" placeholder="staff@email.com">
                                        @error('email')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label required">Gender</label>
                                        <select name="gender" class="form-select">
                                            <option value="">Select gender</option>
                                            @foreach (['M' => 'Male', 'F' => 'Female'] as $k => $v)
                                                <option value="{{ $k }}"
                                                    {{ old('gender') === $k ? 'selected' : '' }}>{{ $v }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('gender')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label required">Date of Birth</label>
                                        <input type="date" name="date_of_birth" class="form-control"
                                            value="{{ old('date_of_birth') }}">
                                        @error('date_of_birth')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label required">Nationality</label>
                                        <select name="nationality" class="form-select">
                                            <option value="">Select nationality</option>
                                            @foreach ($nationalities ?? collect() as $n)
                                                <option value="{{ $n->name ?? $n }}"
                                                    {{ old('nationality') === ($n->name ?? $n) ? 'selected' : '' }}>
                                                    {{ $n->name ?? $n }}</option>
                                            @endforeach
                                        </select>
                                        @error('nationality')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label required">Phone</label>
                                        <input type="text" name="phone" class="form-control"
                                            value="{{ old('phone') }}" placeholder="e.g., 0026771000000 or 71xxxxxx">
                                        @error('phone')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label required">National ID</label>
                                        <input type="text" name="id_number" class="form-control"
                                            value="{{ old('id_number') }}" placeholder="e.g., 123456789">
                                        @error('id_number')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                            <div class="col-2">
                                <div class="profile-image-section">
                                    <div class="profile-image-container">
                                        <div class="image-upload-area" id="uploadArea">
                                            <div class="no-image-placeholder" id="imagePlaceholder">
                                                <i class="fas fa-user"></i>
                                                <div class="upload-text">Click to upload</div>
                                            </div>
                                        </div>
                                        <input type="file" name="avatar" id="profileImageInput"
                                            accept="image/jpeg,image/jpg,image/png,image/gif">
                                        <div class="file-info">JPG, PNG, GIF up to 2MB</div>
                                        @error('avatar')
                                            <div class="text-danger mt-2 text-center">{{ $message }}</div>
                                        @enderror
                                        <input type="hidden" name="remove_image" id="removeImageFlag" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="help-text">
                            <div class="help-title">Work Information</div>
                            <div class="help-content">Select department, position, area of work and reporting manager.
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Department</label>
                                <select id="departmentSelect" name="department" class="form-select" required>
                                    <option value="">Select department</option>
                                    @foreach ($departments ?? collect() as $dept)
                                        <option value="{{ $dept->name ?? $dept }}" data-id="{{ $dept->id ?? '' }}"
                                            {{ old('department') === ($dept->name ?? $dept) ? 'selected' : '' }}>
                                            {{ $dept->name ?? $dept }}</option>
                                    @endforeach
                                </select>
                                @error('department')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                                @error('department_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Position</label>
                                <select name="position" class="form-select">
                                    <option value="">Select position</option>
                                    @foreach ($positions ?? collect() as $p)
                                        <option value="{{ $p->name ?? $p }}"
                                            {{ old('position') === ($p->name ?? $p) ? 'selected' : '' }}>
                                            {{ $p->name ?? $p }}</option>
                                    @endforeach
                                </select>
                                @error('position')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Area of Work</label>
                                <select name="area_of_work" class="form-select">
                                    <option value="">Select area of work</option>
                                    @foreach ($area_of_work ?? collect() as $a)
                                        <option value="{{ $a->name ?? $a }}"
                                            {{ old('area_of_work') === ($a->name ?? $a) ? 'selected' : '' }}>
                                            {{ $a->name ?? $a }}</option>
                                    @endforeach
                                </select>
                                @error('area_of_work')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reporting To</label>
                                <select name="reporting_to" class="form-select">
                                    <option value="">Select manager (optional)</option>
                                    @foreach ($users ?? collect() as $u)
                                        <option value="{{ $u->id }}"
                                            {{ (string) old('reporting_to') === (string) $u->id ? 'selected' : '' }}>
                                            {{ $u->firstname }} {{ $u->lastname }} @if (!empty($u->position))
                                                - {{ $u->position }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('reporting_to')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Status</label>
                                <select name="status" class="form-select">
                                    @php($statuses = ['Current', 'Inactive', 'Suspended', 'Terminated', 'Alumni'])
                                    @foreach ($statuses as $s)
                                        <option value="{{ $s }}"
                                            {{ old('status', 'Current') === $s ? 'selected' : '' }}>{{ $s }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">User Filter</label>
                                <select name="user_filter_id" class="form-select">
                                    <option value="">No filter</option>
                                    @foreach ($filters ?? collect() as $filter)
                                        <option value="{{ $filter->id }}"
                                            {{ old('user_filter_id') == ($filter->id ?? '') ? 'selected' : '' }}>
                                            {{ $filter->name ?? $filter->id }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_filter_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="department_id" id="departmentIdHidden"
                        value="{{ old('department_id') }}">

                    <div class="form-actions">
                        <div class="form-actions-left">
                            <a href="{{ route('staff.index') }}" class="btn btn-secondary"><i
                                    class="fas fa-arrow-left"></i> Back to Staff</a>
                        </div>
                        <div class="form-actions-right">
                            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fas fa-save"></i>
                                Create Staff</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Duplicate Warning Modal -->
    <div class="modal fade" id="duplicateWarningModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Potential Duplicate Staff Member Detected
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> We found <strong id="duplicateCount">0</strong> existing staff member(s)
                        with similar information.
                    </div>
                    <p class="mb-3">Please review the following existing staff member(s) before creating a new one:</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>ID Number</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="duplicateTableBody"></tbody>
                        </table>
                    </div>
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        If you believe this is a different staff member, you can proceed with creating a new record.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel & Review
                    </button>
                    <button type="button" class="btn btn-primary" id="proceedAnywayBtn">
                        <i class="fas fa-check me-1"></i> Proceed Anyway
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('createStaffForm');
            const submitBtn = document.getElementById('submitBtn');
            const departmentSelect = document.getElementById('departmentSelect');
            const departmentIdHidden = document.getElementById('departmentIdHidden');
            const profileImageInput = document.getElementById('profileImageInput');
            const uploadArea = document.getElementById('uploadArea');

            if (departmentSelect && departmentIdHidden) {
                function syncDepartmentId() {
                    const selected = departmentSelect.options[departmentSelect.selectedIndex];
                    if (selected) {
                        departmentIdHidden.value = selected.getAttribute('data-id') || '';
                    }
                }
                departmentSelect.addEventListener('change', syncDepartmentId);
                syncDepartmentId();
            }

            // Duplicate check functionality
            let duplicateCheckPassed = false;

            function checkForDuplicates() {
                const firstName = document.querySelector('input[name="firstname"]')?.value || '';
                const lastName = document.querySelector('input[name="lastname"]')?.value || '';
                const email = document.querySelector('input[name="email"]')?.value || '';
                const idNumber = document.querySelector('input[name="id_number"]')?.value || '';

                if (!firstName || !lastName) return Promise.resolve(false);

                const formData = {
                    firstname: firstName,
                    lastname: lastName,
                    email: email,
                    id_number: idNumber,
                    _token: '{{ csrf_token() }}'
                };

                return fetch('{{ route('staff.check-duplicate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.count > 0) {
                            document.getElementById('duplicateCount').textContent = data.count;
                            const tbody = document.getElementById('duplicateTableBody');
                            tbody.innerHTML = '';

                            data.matches.forEach(match => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                <td><strong>${match.name || 'N/A'}</strong></td>
                                <td>${match.email || 'N/A'}</td>
                                <td>${match.id_number || 'N/A'}</td>
                                <td>${match.position || 'N/A'}</td>
                                <td><span class="badge bg-${match.status === 'Current' ? 'success' : 'secondary'}">${match.status || 'N/A'}</span></td>
                            `;
                                tbody.appendChild(row);
                            });

                            const modal = new bootstrap.Modal(document.getElementById('duplicateWarningModal'));
                            modal.show();
                            return true; // Has duplicates
                        }
                        return false; // No duplicates
                    })
                    .catch(error => {
                        console.error('Error checking duplicates:', error);
                        return false; // Continue on error
                    });
            }

            // Handle proceed anyway button
            document.getElementById('proceedAnywayBtn')?.addEventListener('click', function() {
                duplicateCheckPassed = true;
                const modal = bootstrap.Modal.getInstance(document.getElementById('duplicateWarningModal'));
                if (modal) modal.hide();
                form.submit();
            });

            if (form && submitBtn) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // If duplicate check already passed, submit the form
                    if (duplicateCheckPassed) {
                        try {
                            submitBtn.innerHTML =
                                '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
                            submitBtn.disabled = true;
                        } catch (err) {}
                        form.submit();
                        return;
                    }

                    // Check for duplicates first
                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>Checking...';

                    checkForDuplicates().then(hasDuplicates => {
                        if (!hasDuplicates) {
                            // No duplicates found, submit the form
                            duplicateCheckPassed = true;
                            submitBtn.innerHTML =
                                '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
                            form.submit();
                        } else {
                            // Duplicates found, modal is shown - reset button
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="fas fa-save"></i> Create Staff';
                        }
                    }).catch(error => {
                        console.error('Duplicate check failed:', error);
                        // On error, allow submission
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-save"></i> Create Staff';
                        alert('Unable to check for duplicates. Please try again.');
                    });
                });
            }

            if (uploadArea) {
                uploadArea.addEventListener('click', () => profileImageInput && profileImageInput.click());
                uploadArea.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    uploadArea.classList.add('drag-active');
                });
                uploadArea.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    uploadArea.classList.remove('drag-active');
                });
                uploadArea.addEventListener('drop', function(e) {
                    e.preventDefault();
                    uploadArea.classList.remove('drag-active');
                    const files = e.dataTransfer.files;
                    if (files && files[0]) handleFile(files[0]);
                });
            }
            if (profileImageInput) {
                profileImageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) handleFile(file);
                });
            }

            function handleFile(file) {
                if (!validateFile(file)) return;
                const reader = new FileReader();
                reader.onload = function(e) {
                    const existingImg = uploadArea.querySelector('#currentImage');
                    if (existingImg) {
                        existingImg.src = e.target.result;
                    } else {
                        uploadArea.innerHTML = '<img src="' + e.target.result +
                            '" alt="Staff Photo" id="currentImage" class="profile-preview" style="border-radius:3px;">\n<div class="image-overlay"><i class="fas fa-check"></i></div>';
                    }
                };
                reader.readAsDataURL(file);
            }

            function validateFile(file) {
                const valid = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                const max = 2 * 1024 * 1024;
                if (!valid.includes(file.type)) {
                    alert('Please select a valid image (JPG, PNG, GIF)');
                    return false;
                }
                if (file.size > max) {
                    alert('Image must be less than 2MB');
                    return false;
                }
                return true;
            }
        });
    </script>
@endsection
