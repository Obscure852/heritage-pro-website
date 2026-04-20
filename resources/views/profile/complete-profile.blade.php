@extends('layouts.master')
@section('title')
    Complete Your Profile
@endsection
@section('css')
    <style>
        /* Grey out sidebar */
        .vertical-menu {
            pointer-events: none;
            opacity: 0.4;
        }

        .complete-profile-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 28px 32px;
            border-radius: 3px 3px 0 0;
            position: relative;
            overflow: hidden;
        }

        .complete-profile-header::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -10%;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .form-container {
            background: white;
            border-radius: 0 0 3px 3px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        @media (max-width: 992px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
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

        .text-danger {
            color: #dc2626;
        }

        .filled-field {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 3px;
            color: #166534;
            font-size: 14px;
        }

        .filled-field i {
            color: #22c55e;
        }

        .record-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 3px;
            margin-bottom: 8px;
            color: #166534;
            font-size: 14px;
        }

        .record-item i {
            color: #22c55e;
        }

        .empty-section-state {
            text-align: center;
            padding: 20px;
            background: #fefce8;
            border-radius: 3px;
            border: 1px dashed #d97706;
            color: #92400e;
            font-size: 14px;
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

        @media (max-width: 768px) {
            .form-container {
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
    <div class="row">
        <div class="col-12">
            <div class="complete-profile-header">
                <h4 class="mb-2" style="position:relative;z-index:1;">
                    <i class="fas fa-exclamation-triangle me-2"></i> Profile Update Required
                </h4>
                <p class="mb-0" style="position:relative;z-index:1;opacity:0.9;">
                    Your administrator requires you to complete your profile before accessing the system. Please fill in the missing information below.
                </p>
            </div>

            <div class="form-container">
                @if (session('message'))
                    <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                        <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                            <i class="mdi mdi-block-helper label-icon"></i><strong>{{ $error }}</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endforeach
                @endif

                <form action="{{ route('profile.complete.save') }}" method="POST" id="completeProfileForm">
                    @csrf

                    {{-- Basic Information Section --}}
                    @if (in_array('basic_info', $requiredSections))
                        @php
                            $basicFields = $sectionDefinitions['basic_info']['fields'];
                            $basicMissing = array_intersect($basicFields, $incomplete['missing_fields']);
                            $basicFilled = array_diff($basicFields, $basicMissing);
                            $fieldLabels = [
                                'firstname' => 'First Name',
                                'lastname' => 'Last Name',
                                'date_of_birth' => 'Date of Birth',
                                'id_number' => 'ID / Passport Number',
                                'email' => 'Email Address',
                                'nationality' => 'Nationality',
                            ];
                            $fieldPlaceholders = [
                                'firstname' => 'e.g. Thato',
                                'lastname' => 'e.g. Buseng',
                                'id_number' => 'e.g. 765 2188 12',
                                'email' => 'e.g. thato@example.com',
                            ];
                        @endphp

                        <h3 class="section-title"><i class="fas fa-user me-2"></i> Basic Information</h3>

                        <div class="help-text">
                            <div class="help-title">Personal Details</div>
                            <div class="help-content">
                                Your basic personal information. Fields marked with <span class="text-danger">*</span> are required. Fields already on file are shown in green.
                            </div>
                        </div>

                        @if (count($basicFilled) > 0)
                            <div class="form-grid" style="margin-bottom: 16px;">
                                @foreach ($basicFilled as $field)
                                    <div class="form-group">
                                        <label class="form-label">{{ $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}</label>
                                        <div class="filled-field">
                                            <i class="fas fa-check-circle"></i>
                                            <span>{{ $user->$field }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (count($basicMissing) > 0)
                            <div class="form-grid">
                                @foreach ($basicMissing as $field)
                                    <div class="form-group">
                                        <label class="form-label" for="basic_{{ $field }}">{{ $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }} <span class="text-danger">*</span></label>
                                        @if ($field === 'date_of_birth')
                                            <input type="text"
                                                class="form-control @error('date_of_birth') is-invalid @enderror"
                                                name="date_of_birth" id="basic_date_of_birth"
                                                value="{{ old('date_of_birth') }}"
                                                placeholder="dd/mm/yyyy" maxlength="10" required>
                                        @elseif ($field === 'nationality')
                                            <select class="form-select @error('nationality') is-invalid @enderror"
                                                name="nationality" id="basic_nationality" required>
                                                <option value="">Select Nationality</option>
                                                @foreach ($nationalities as $nat)
                                                    <option value="{{ $nat->name }}" {{ old('nationality') == $nat->name ? 'selected' : '' }}>{{ $nat->name }}</option>
                                                @endforeach
                                            </select>
                                        @elseif ($field === 'email')
                                            <input type="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                name="email" id="basic_email"
                                                placeholder="{{ $fieldPlaceholders[$field] ?? '' }}"
                                                value="{{ old('email') }}" required>
                                        @else
                                            <input type="text"
                                                class="form-control @error($field) is-invalid @enderror"
                                                name="{{ $field }}" id="basic_{{ $field }}"
                                                placeholder="{{ $fieldPlaceholders[$field] ?? '' }}"
                                                value="{{ old($field) }}" required>
                                        @endif
                                        @error($field)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    {{-- Employment Details Section --}}
                    @if (in_array('employment_details', $requiredSections))
                        @php
                            $empFields = $sectionDefinitions['employment_details']['fields'];
                            $empMissing = array_intersect($empFields, $incomplete['missing_fields']);
                            $empFilled = array_diff($empFields, $empMissing);
                            $empLabels = [
                                'personal_payroll_number' => 'Personal Payroll Number',
                                'dpsm_personal_file_number' => 'DPSM Personal File Number',
                                'date_of_appointment' => 'Date of Appointment',
                                'earning_band' => 'Grade (Earning Band)',
                            ];
                            $empPlaceholders = [
                                'personal_payroll_number' => 'e.g. PPN-1001',
                                'dpsm_personal_file_number' => 'e.g. 81716',
                            ];
                        @endphp

                        <h3 class="section-title"><i class="fas fa-briefcase me-2"></i> Employment Details</h3>

                        <div class="help-text">
                            <div class="help-title">Employment Information</div>
                            <div class="help-content">
                                Your employment details as recorded by the Ministry. If you are unsure about your payroll number or DPSM file number, please check with your school administration or HR department.
                            </div>
                        </div>

                        @if (count($empFilled) > 0)
                            <div class="form-grid" style="margin-bottom: 16px;">
                                @foreach ($empFilled as $field)
                                    <div class="form-group">
                                        <label class="form-label">{{ $empLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}</label>
                                        <div class="filled-field">
                                            <i class="fas fa-check-circle"></i>
                                            <span>{{ $user->$field }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (count($empMissing) > 0)
                            <div class="form-grid">
                                @foreach ($empMissing as $field)
                                    <div class="form-group">
                                        <label class="form-label" for="emp_{{ $field }}">{{ $empLabels[$field] ?? ucfirst(str_replace('_', ' ', $field)) }} <span class="text-danger">*</span></label>
                                        @if ($field === 'date_of_appointment')
                                            <input type="date"
                                                class="form-control @error('date_of_appointment') is-invalid @enderror"
                                                name="date_of_appointment" id="emp_date_of_appointment"
                                                value="{{ old('date_of_appointment') }}" required>
                                        @elseif ($field === 'earning_band')
                                            <select class="form-select @error('earning_band') is-invalid @enderror"
                                                name="earning_band" id="emp_earning_band" required>
                                                <option value="">Select Earning Band</option>
                                                @foreach ($earningBands as $band)
                                                    <option value="{{ $band->name }}" {{ old('earning_band') == $band->name ? 'selected' : '' }}>{{ $band->name }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text"
                                                class="form-control @error($field) is-invalid @enderror"
                                                name="{{ $field }}" id="emp_{{ $field }}"
                                                placeholder="{{ $empPlaceholders[$field] ?? '' }}"
                                                value="{{ old($field) }}" required>
                                        @endif
                                        @error($field)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    {{-- Qualifications Section --}}
                    @if (in_array('qualifications', $requiredSections))
                        <h3 class="section-title"><i class="fas fa-graduation-cap me-2"></i> Qualifications</h3>

                        <div class="help-text">
                            <div class="help-title">Academic Qualifications</div>
                            <div class="help-content">
                                Please add at least one qualification record. Include your highest qualification and any other relevant certifications. If your qualification is not listed, contact HR to have it added.
                            </div>
                        </div>

                        <div id="qualificationsList">
                            @if ($user->qualifications->count() > 0)
                                @foreach ($user->qualifications as $qual)
                                    <div class="record-item">
                                        <i class="fas fa-check-circle"></i>
                                        {{ $qual->qualification }} ({{ $qual->qualification_code }}) &mdash; {{ $qual->pivot->level ?? '' }}, {{ $qual->pivot->college ?? '' }}
                                    </div>
                                @endforeach
                            @else
                                <div class="empty-section-state" id="qualificationsEmptyState">
                                    <i class="fas fa-info-circle me-1"></i>
                                    No qualifications recorded yet. Please add at least one qualification to continue.
                                </div>
                            @endif
                        </div>

                        <button type="button" class="btn btn-outline-primary mt-3" data-bs-toggle="modal" data-bs-target="#addQualificationModal">
                            <i class="fas fa-plus me-1"></i> Add Qualification
                        </button>
                    @endif

                    {{-- Work History Section --}}
                    @if (in_array('work_history', $requiredSections))
                        <h3 class="section-title"><i class="fas fa-history me-2"></i> Work History</h3>

                        <div class="help-text">
                            <div class="help-title">Employment History</div>
                            <div class="help-content">
                                Please add at least one work history entry. Include your current position and any previous relevant employment. Leave the end date blank if you are still in that role.
                            </div>
                        </div>

                        <div id="workHistoryList">
                            @if ($user->workHistory->count() > 0)
                                @foreach ($user->workHistory as $work)
                                    <div class="record-item">
                                        <i class="fas fa-check-circle"></i>
                                        {{ $work->role }} at {{ $work->workplace }} ({{ $work->type_of_work }})
                                    </div>
                                @endforeach
                            @else
                                <div class="empty-section-state" id="workHistoryEmptyState">
                                    <i class="fas fa-info-circle me-1"></i>
                                    No work history recorded yet. Please add at least one entry to continue.
                                </div>
                            @endif
                        </div>

                        <button type="button" class="btn btn-outline-primary mt-3" data-bs-toggle="modal" data-bs-target="#addWorkHistoryModal">
                            <i class="fas fa-plus me-1"></i> Add Work History
                        </button>
                    @endif

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-loading" id="saveAndCompleteBtn">
                            <span class="btn-text"><i class="fas fa-check-circle"></i> Save & Complete</span>
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

    {{-- Add Qualification Modal --}}
    @if (in_array('qualifications', $requiredSections))
        <div class="modal fade" id="addQualificationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Qualification</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="completeProfileAddQualForm">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Qualification <span class="text-danger">*</span></label>
                                <select class="form-select" name="qualification_id" required>
                                    <option value="">Select Qualification</option>
                                    @foreach ($qualifications as $qual)
                                        <option value="{{ $qual->id }}">{{ $qual->qualification }} ({{ $qual->qualification_code }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>Can't find your qualification? Please contact HR or an administrator to have it added to the system.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Level <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="level" placeholder="e.g. Diploma, Degree, Masters" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">University / College <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="college" placeholder="e.g. University of Botswana" required>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="start_date" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Completion Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="completion_date" required>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-danger d-none" id="qualFormError"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn" style="background:#f3f4f6; color:#374151;" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-info btn-loading" name="action" value="save" id="qualSaveBtn">
                                <span class="btn-text"><i class="fas fa-save"></i> Save</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Saving...
                                </span>
                            </button>
                            <button type="submit" class="btn btn-primary btn-loading" name="action" value="save_complete" id="qualSaveCompleteBtn">
                                <span class="btn-text"><i class="fas fa-check-circle"></i> Save & Complete</span>
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
    @endif

    {{-- Add Work History Modal --}}
    @if (in_array('work_history', $requiredSections))
        <div class="modal fade" id="addWorkHistoryModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Work History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="completeProfileAddWorkForm">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Workplace <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="workplace" placeholder="e.g. Heritage Junior Secondary School" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Type of Work <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="type_of_work" placeholder="e.g. Teaching, Administration" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="role" placeholder="e.g. Senior Teacher, Head of Department" required>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="start" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="end">
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-danger d-none" id="workFormError"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn" style="background:#f3f4f6; color:#374151;" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-info btn-loading" name="action" value="save" id="workSaveBtn">
                                <span class="btn-text"><i class="fas fa-save"></i> Save</span>
                                <span class="btn-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Saving...
                                </span>
                            </button>
                            <button type="submit" class="btn btn-primary btn-loading" name="action" value="save_complete" id="workSaveCompleteBtn">
                                <span class="btn-text"><i class="fas fa-check-circle"></i> Save & Complete</span>
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
    @endif
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const checkUrl = '{{ route("profile.complete.check") }}';

            // Activate sidebar and redirect to dashboard
            function activateSidebarAndRedirect() {
                const sidebar = document.querySelector('.vertical-menu');
                if (sidebar) {
                    sidebar.style.pointerEvents = '';
                    sidebar.style.opacity = '';
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Profile Complete!',
                    text: 'Your profile is now complete. Redirecting to dashboard...',
                    timer: 2000,
                    showConfirmButton: false,
                }).then(() => {
                    window.location.href = '/';
                });
            }

            // Check completeness and act accordingly
            function checkAndActivate() {
                return fetch(checkUrl, {
                    headers: { 'Accept': 'application/json' },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.complete) {
                        activateSidebarAndRedirect();
                        return true;
                    } else {
                        let missing = [];
                        if (data.missing_fields && data.missing_fields.length > 0) {
                            missing.push('Fields: ' + data.missing_fields.join(', '));
                        }
                        if (data.missing_sections && data.missing_sections.length > 0) {
                            const sectionLabels = {
                                'qualifications': 'Qualifications (add at least one)',
                                'work_history': 'Work History (add at least one)',
                            };
                            const labels = data.missing_sections.map(s => sectionLabels[s] || s);
                            missing.push('Sections: ' + labels.join(', '));
                        }
                        Swal.fire({
                            icon: 'info',
                            title: 'Almost there!',
                            html: 'Still required:<br>' + missing.join('<br>'),
                            confirmButtonColor: '#3b82f6',
                        });
                        return false;
                    }
                });
            }

            // Handle fetch response - throw on validation errors, parse JSON safely
            function handleResponse(response) {
                if (response.status === 422) {
                    return response.json().then(data => { throw data; });
                }
                if (!response.ok) {
                    return response.json()
                        .catch(() => { throw { message: 'An unexpected error occurred. Please try again.' }; })
                        .then(data => { throw data; });
                }
                return response.json();
            }

            // Reset loading state on buttons
            function resetBtn(btn) {
                if (btn) {
                    btn.classList.remove('loading');
                    btn.disabled = false;
                }
            }

            function setLoading(btn) {
                if (btn) {
                    btn.classList.add('loading');
                    btn.disabled = true;
                }
            }

            // Main form AJAX submit
            const mainForm = document.getElementById('completeProfileForm');
            if (mainForm) {
                mainForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const submitBtn = document.getElementById('saveAndCompleteBtn');
                    setLoading(submitBtn);

                    const formData = new FormData(mainForm);

                    fetch('{{ route("profile.complete.save") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                    .then(handleResponse)
                    .then(data => {
                        if (data.success) {
                            // Mark submitted fields as filled
                            mainForm.querySelectorAll('.form-control, .form-select').forEach(input => {
                                if (input.value) {
                                    const group = input.closest('.form-group');
                                    if (group) {
                                        const label = group.querySelector('.form-label');
                                        const labelText = label ? label.textContent.replace(' *', '') : '';
                                        group.innerHTML = '<label class="form-label">' + labelText + '</label>' +
                                            '<div class="filled-field"><i class="fas fa-check-circle"></i><span>' + input.value + '</span></div>';
                                    }
                                }
                            });

                            checkAndActivate().finally(() => resetBtn(submitBtn));
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'An error occurred.' });
                            resetBtn(submitBtn);
                        }
                    })
                    .catch(error => {
                        if (error.errors) {
                            const messages = Object.values(error.errors).flat().join('\n');
                            Swal.fire({ icon: 'error', title: 'Validation Error', text: messages });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'An unexpected error occurred.' });
                        }
                        resetBtn(submitBtn);
                    });
                });
            }

            // Track which submit button was clicked in modals
            let qualClickedAction = 'save';
            let workClickedAction = 'save';

            document.querySelectorAll('#completeProfileAddQualForm button[type="submit"]').forEach(btn => {
                btn.addEventListener('click', function() { qualClickedAction = this.value; });
            });
            document.querySelectorAll('#completeProfileAddWorkForm button[type="submit"]').forEach(btn => {
                btn.addEventListener('click', function() { workClickedAction = this.value; });
            });

            // Add Qualification via AJAX
            const qualForm = document.getElementById('completeProfileAddQualForm');
            if (qualForm) {
                qualForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const action = qualClickedAction;
                    const activeBtn = action === 'save_complete'
                        ? document.getElementById('qualSaveCompleteBtn')
                        : document.getElementById('qualSaveBtn');
                    const errorDiv = document.getElementById('qualFormError');
                    errorDiv.classList.add('d-none');
                    setLoading(activeBtn);

                    const formData = new FormData(qualForm);

                    fetch('{{ route("profile.complete.add-qualification") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                    .then(handleResponse)
                    .then(data => {
                        if (data.success) {
                            const emptyState = document.getElementById('qualificationsEmptyState');
                            if (emptyState) emptyState.remove();

                            const list = document.getElementById('qualificationsList');
                            const item = document.createElement('div');
                            item.className = 'record-item';
                            item.innerHTML = '<i class="fas fa-check-circle"></i> ' +
                                data.qualification.name + ' (' + data.qualification.code + ') &mdash; ' +
                                data.qualification.level + ', ' + data.qualification.college;
                            list.appendChild(item);

                            const modal = bootstrap.Modal.getInstance(document.getElementById('addQualificationModal'));
                            modal.hide();
                            qualForm.reset();

                            if (action === 'save_complete') {
                                checkAndActivate().finally(() => resetBtn(activeBtn));
                            } else {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: data.message,
                                    timer: 2000,
                                    showConfirmButton: false,
                                });
                                resetBtn(activeBtn);
                            }
                        } else {
                            errorDiv.textContent = data.message || 'An error occurred.';
                            errorDiv.classList.remove('d-none');
                            resetBtn(activeBtn);
                        }
                    })
                    .catch(error => {
                        if (error.errors) {
                            const messages = Object.values(error.errors).flat().join('\n');
                            errorDiv.textContent = messages;
                        } else {
                            errorDiv.textContent = 'An unexpected error occurred. Please try again.';
                        }
                        errorDiv.classList.remove('d-none');
                        resetBtn(activeBtn);
                    });
                });
            }

            // Add Work History via AJAX
            const workForm = document.getElementById('completeProfileAddWorkForm');
            if (workForm) {
                workForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const action = workClickedAction;
                    const activeBtn = action === 'save_complete'
                        ? document.getElementById('workSaveCompleteBtn')
                        : document.getElementById('workSaveBtn');
                    const errorDiv = document.getElementById('workFormError');
                    errorDiv.classList.add('d-none');
                    setLoading(activeBtn);

                    const formData = new FormData(workForm);

                    fetch('{{ route("profile.complete.add-work-history") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                    .then(handleResponse)
                    .then(data => {
                        if (data.success) {
                            const emptyState = document.getElementById('workHistoryEmptyState');
                            if (emptyState) emptyState.remove();

                            const list = document.getElementById('workHistoryList');
                            const item = document.createElement('div');
                            item.className = 'record-item';
                            item.innerHTML = '<i class="fas fa-check-circle"></i> ' +
                                data.work_history.role + ' at ' + data.work_history.workplace +
                                ' (' + data.work_history.type_of_work + ')';
                            list.appendChild(item);

                            const modal = bootstrap.Modal.getInstance(document.getElementById('addWorkHistoryModal'));
                            modal.hide();
                            workForm.reset();

                            if (action === 'save_complete') {
                                checkAndActivate().finally(() => resetBtn(activeBtn));
                            } else {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: data.message,
                                    timer: 2000,
                                    showConfirmButton: false,
                                });
                                resetBtn(activeBtn);
                            }
                        } else {
                            errorDiv.textContent = data.message || 'An error occurred.';
                            errorDiv.classList.remove('d-none');
                            resetBtn(activeBtn);
                        }
                    })
                    .catch(error => {
                        if (error.errors) {
                            const messages = Object.values(error.errors).flat().join('\n');
                            errorDiv.textContent = messages;
                        } else {
                            errorDiv.textContent = 'An unexpected error occurred. Please try again.';
                        }
                        errorDiv.classList.remove('d-none');
                        resetBtn(activeBtn);
                    });
                });
            }

            // Auto-dismiss alerts
            document.querySelectorAll('.alert-dismissible').forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
@endsection
