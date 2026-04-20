@extends('layouts.master')
@section('title')
    Non School Days | Attendance
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

        .year-select {
            max-width: 200px;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            color: #374151;
            background-color: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .year-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .holiday-list-card {
            background: white;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .holiday-list-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

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
            Non School Days
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

    <div class="row">
        <div class="col-lg-7">
            <div class="settings-container">
                <div class="settings-header">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3><i class="fas fa-calendar-alt me-2"></i>Non School Days</h3>
                            <p>Manage holidays and non-school days for attendance tracking</p>
                        </div>
                        <div class="term-selector">
                            <label for="termId">Term:</label>
                            <select name="term" id="termId" class="form-select form-select-sm" style="background: rgba(255,255,255,0.9); border: none;">
                                @if (!empty($terms))
                                    @foreach ($terms as $term)
                                        <option data-year="{{ $term->year }}"
                                            value="{{ $term->id }}"{{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                                            {{ 'Term ' . $term->term . ', ' . $term->year }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <div class="settings-body">
                    <div class="help-text">
                        <div class="help-title">About Non School Days</div>
                        <div class="help-content">
                            Add public holidays, school breaks, and other non-attendance days. These dates will be
                            excluded from attendance calculations and reports.
                        </div>
                    </div>

                    <form action="{{ route('attendance.add-day') }}" method="POST" id="holidayForm">
                        @csrf
                        <input type="hidden" name="term_id" value="{{ $currentTerm->id }}">
                        <input type="hidden" name="year" value="{{ $currentTerm->year }}">

                        <div class="form-section">
                            <h6 class="form-section-title"><i class="fas fa-plus-circle me-2"></i>Add New Holiday</h6>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="name" class="form-label">Holiday Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="e.g., Good Friday, Independence Day" required>
                                    <div class="form-text">Enter a descriptive name for this holiday</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                                    <div class="form-text">First day of the holiday</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                                    <div class="form-text">Last day of the holiday</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary btn-loading" id="submitBtn">
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

        <div class="col-lg-5">
            <div class="holiday-list-card">
                <h6 class="holiday-list-title"><i class="fas fa-list me-2"></i>Existing Holidays</h6>
                <div id="holiday-list">
                    <div class="text-center text-muted py-4">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Loading holidays...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Holiday Modal -->
    <div class="modal fade" id="editHolidayModal" tabindex="-1" aria-labelledby="editHolidayModalLabel" aria-hidden="true">
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
                        <div class="mb-3">
                            <label for="holidayName" class="form-label">Holiday Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="holidayName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="holidayStart" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="holidayStart" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="holidayEnd" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="holidayEnd" name="end_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary btn-loading" id="updateBtn">
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
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            var holidayListRoute = "{{ route('attendance.holiday-list', ':termId') }}";

            function updateHolidayList() {
                var termId = $('#termId').val();
                var url = holidayListRoute.replace(':termId', encodeURIComponent(termId));
                $.get(url, function(data) {
                    $('#holiday-list').html(data);
                });
            }

            $('#termId').change(updateHolidayList).trigger('change');

            // Form submit loading state
            const holidayForm = document.getElementById('holidayForm');
            const submitBtn = document.getElementById('submitBtn');

            if (holidayForm && submitBtn) {
                holidayForm.addEventListener('submit', function() {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                });
            }

            // Edit form submit loading state
            const editForm = document.getElementById('editHolidayForm');
            const updateBtn = document.getElementById('updateBtn');

            if (editForm && updateBtn) {
                editForm.addEventListener('submit', function() {
                    updateBtn.classList.add('loading');
                    updateBtn.disabled = true;
                });
            }
        });

        function openEditModal(element) {
            const id = $(element).data('id');
            const name = $(element).data('name');
            const start = $(element).data('start');
            const end = $(element).data('end');

            $('#holidayId').val(id);
            $('#holidayName').val(name);
            $('#holidayStart').val(start);
            $('#holidayEnd').val(end);

            const formAction = "{{ route('holidays.update-holiday', ':id') }}";
            $('#editHolidayForm').attr('action', formAction.replace(':id', id));

            // Reset loading state when modal opens
            const updateBtn = document.getElementById('updateBtn');
            if (updateBtn) {
                updateBtn.classList.remove('loading');
                updateBtn.disabled = false;
            }

            $('#editHolidayModal').modal('show');
        }
    </script>
@endsection
