@extends('layouts.master')
@section('title')
    Tests dd
@endsection
@section('css')
    <style>
        /* Main Container */
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

        /* Tab Styling - Clean underline style */
        .nav-tabs-custom {
            border-bottom: 1px solid #e5e7eb;
            gap: 8px;
        }

        .nav-tabs-custom .nav-link {
            border: none !important;
            border-bottom: 2px solid transparent !important;
            background: transparent !important;
            color: #6b7280;
            font-weight: 500;
            padding: 12px 20px;
            transition: all 0.2s ease;
            border-radius: 0 !important;
            margin-bottom: -1px;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #4e73df;
            background: transparent !important;
            border-color: transparent !important;
            border-bottom-color: transparent !important;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            border-bottom: 2px solid #4e73df !important;
            background: transparent !important;
        }

        /* Filter Section */
        .filter-section {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-group select {
            min-width: 180px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 8px 12px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .filter-group select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            outline: none;
        }

        .filter-actions {
            margin-left: auto;
            display: flex;
            gap: 8px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 3px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-action:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            color: white;
        }

        .btn-action-secondary {
            background: white;
            border: 1px solid #d1d5db;
            color: #374151;
        }

        .btn-action-secondary:hover {
            background: #f3f4f6;
            color: #1f2937;
            transform: none;
            box-shadow: none;
        }

        /* Tests Grid */
        #tests {
            margin-top: 0;
        }

        /* Threshold Settings Tab Styles */
        .threshold-table {
            width: 100%;
            border-collapse: collapse;
        }

        .threshold-table thead th {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            color: #374151;
            font-size: 13px;
            padding: 12px;
            text-align: left;
        }

        .threshold-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        .threshold-table tbody tr:hover {
            background-color: #f9fafb;
        }

        .threshold-table tbody td {
            padding: 12px;
            vertical-align: middle;
        }

        .threshold-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            margin-right: 6px;
            margin-bottom: 4px;
        }

        .threshold-color-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .status-badge.active {
            background: #dcfce7;
            color: #166534;
        }

        .status-badge.inactive {
            background: #f3f4f6;
            color: #6b7280;
        }

        .status-badge:hover {
            opacity: 0.8;
        }

        .scope-label {
            font-weight: 500;
            color: #374151;
        }

        .scope-sublabel {
            font-size: 12px;
            color: #6b7280;
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
            font-size: 14px;
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

        /* Modal Styles */
        .threshold-modal .modal-header {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 20px;
        }

        .threshold-modal .modal-title {
            font-weight: 600;
            color: #374151;
        }

        .threshold-modal .modal-title i {
            color: #4e73df;
            margin-right: 8px;
        }

        .threshold-row {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 12px;
            background: #f9fafb;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .threshold-row .form-control {
            flex: 1;
        }

        .threshold-row .color-input {
            width: 50px;
            height: 38px;
            padding: 4px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }

        .threshold-row .btn-remove-threshold {
            color: #ef4444;
            background: transparent;
            border: none;
            padding: 8px;
            cursor: pointer;
        }

        .threshold-row .btn-remove-threshold:hover {
            color: #dc2626;
        }

        .btn-add-threshold {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            background: #f3f4f6;
            border: 1px dashed #d1d5db;
            border-radius: 4px;
            color: #6b7280;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-add-threshold:hover {
            background: #e5e7eb;
            border-color: #9ca3af;
            color: #374151;
        }

        /* Button Loading State */
        .btn-loading .btn-spinner {
            display: none;
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
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Assessment
        @endslot
        @slot('title')
            Tests Setup
        @endslot
    @endcomponent

    @if (session('message'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="row mb-2">
        <div class="col-10"></div>
        <div class="col-2">
            <div class="filter-group">
                <select name="term" id="termId">
                    @if (!empty($terms))
                        @foreach ($terms as $term)
                            <option data-year="{{ $currentTerm->year }}"
                                value="{{ $term->id }}"{{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                                {{ 'Term ' . $term->term . ', ' . $term->year }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="settings-container">
                <div class="settings-header">
                    <h3><i class="fas fa-clipboard-list me-2"></i>Tests List</h3>
                    <p>View and manage assessment tests by grade and term</p>
                </div>
                <div class="settings-body">
                    <form action="#">
                        <input type="hidden" id="selectedGradeTestId" value="{{ session('selectedTestId') }}">
                    </form>

                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs-custom d-flex justify-content-start mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#assessments" role="tab">
                                <i class="fas fa-clipboard-list me-2 text-muted"></i>Assessments
                            </a>
                        </li>
                        @can('manage-academic')
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#threshold-settings" role="tab">
                                    <i class="fas fa-sliders-h me-2 text-muted"></i>System Threshold Settings
                                </a>
                            </li>
                        @endcan
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Assessments Tab -->
                        <div class="tab-pane active" id="assessments" role="tabpanel">
                            <div class="filter-section">
                                <div class="filter-group">
                                    <select name="testId" id="gradeId">
                                    </select>
                                </div>

                                <div class="filter-actions">
                                    @if (!session('is_past_term'))
                                        @if ($school_data->type === 'Primary')
                                            <a class="btn-action btn-action-secondary"
                                                href="{{ route('reception.criteria-tests') }}">
                                                <i class="bx bx-list-ol"></i> Criteria Based Tests
                                            </a>
                                        @endif
                                        <a class="btn-action" href="{{ route('assessment.create-test') }}">
                                            <i class="bx bx-plus"></i> Add Test
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div id="tests">
                            </div>
                        </div>

                        <!-- Threshold Settings Tab (Admin Only) -->
                        @can('manage-academic')
                            <div class="tab-pane" id="threshold-settings" role="tabpanel">
                                <div class="help-text">
                                    <div class="help-title">System Threshold Settings</div>
                                    <div class="help-content">
                                        Configure passing threshold levels to highlight student scores in markbooks. Thresholds
                                        can be set globally, by school type, grade, or subject. More specific settings override
                                        general ones.
                                    </div>
                                </div>

                                <div class="mb-3 d-flex justify-content-end">
                                    <button type="button" class="btn-action" data-bs-toggle="modal"
                                        data-bs-target="#addThresholdModal">
                                        <i class="bx bx-plus"></i> Add New Threshold Setting
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="threshold-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 25%;">Scope</th>
                                                <th style="width: 40%;">Thresholds</th>
                                                <th style="width: 15%;">Status</th>
                                                <th style="width: 20%;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="thresholdSettingsBody">
                                            @if (!empty($thresholdSettings) && $thresholdSettings->count() > 0)
                                                @foreach ($thresholdSettings as $setting)
                                                    <tr data-setting-id="{{ $setting->id }}">
                                                        <td>
                                                            <div class="scope-label">
                                                                @if ($setting->grade_subject_id)
                                                                    {{ $setting->gradeSubject->subject->name ?? 'Subject' }}
                                                                    <div class="scope-sublabel">
                                                                        {{ $setting->gradeSubject->grade->name ?? '' }}</div>
                                                                @elseif($setting->grade_id)
                                                                    {{ $setting->grade->name ?? 'Grade' }}
                                                                    @if ($setting->test_type)
                                                                        <div class="scope-sublabel">{{ $setting->test_type }}
                                                                            Tests</div>
                                                                    @endif
                                                                @elseif($setting->school_type)
                                                                    {{ $setting->school_type }} School
                                                                @else
                                                                    Global Default
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @foreach ($setting->thresholds ?? [] as $threshold)
                                                                <span class="threshold-badge"
                                                                    style="background: {{ $threshold['color'] }};">
                                                                    <span class="threshold-color-dot"
                                                                        style="background: {{ $threshold['color'] }}; border-color: rgba(0,0,0,0.2);"></span>
                                                                    {{ ucfirst($threshold['name']) }}
                                                                    ≤{{ $threshold['max_percentage'] }}%
                                                                </span>
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="status-badge {{ $setting->is_active ? 'active' : 'inactive' }}"
                                                                onclick="toggleThresholdStatus({{ $setting->id }})"
                                                                title="Click to {{ $setting->is_active ? 'deactivate' : 'activate' }}">
                                                                {{ $setting->is_active ? 'Active' : 'Inactive' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="action-buttons">
                                                                <button type="button"
                                                                    class="btn btn-outline-info edit-threshold"
                                                                    data-setting-id="{{ $setting->id }}"
                                                                    data-school-type="{{ $setting->school_type }}"
                                                                    data-grade-id="{{ $setting->grade_id }}"
                                                                    data-grade-subject-id="{{ $setting->grade_subject_id }}"
                                                                    data-test-type="{{ $setting->test_type }}"
                                                                    data-thresholds="{{ json_encode($setting->thresholds) }}"
                                                                    data-is-active="{{ $setting->is_active ? '1' : '0' }}"
                                                                    title="Edit">
                                                                    <i class="bx bx-edit-alt"></i>
                                                                </button>
                                                                <button type="button"
                                                                    class="btn btn-outline-danger delete-threshold"
                                                                    data-setting-id="{{ $setting->id }}" title="Delete">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr class="empty-row">
                                                    <td colspan="4">
                                                        <div class="empty-state">
                                                            <i class="fas fa-sliders-h"></i>
                                                            <p>No threshold settings configured yet.</p>
                                                            <p class="text-muted">Click "Add New Threshold Setting" to create
                                                                your first threshold.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Threshold Modal -->
    @can('manage-academic')
        <div class="modal fade threshold-modal" id="addThresholdModal" tabindex="-1"
            aria-labelledby="addThresholdModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addThresholdModalLabel"><i class="fas fa-sliders-h"></i> Add Threshold
                            Setting</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="thresholdSettingForm">
                            <input type="hidden" id="editSettingId" name="setting_id" value="">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="schoolType" class="form-label">School Type</label>
                                    <select class="form-select" id="schoolType" name="school_type">
                                        <option value="">All (Global Default)</option>
                                        <option value="Junior">Junior</option>
                                        <option value="Senior">Senior</option>
                                        <option value="Primary">Primary</option>
                                    </select>
                                    <small class="text-muted">Leave empty for a global default setting</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="testTypeSelect" class="form-label">Test Type</label>
                                    <select class="form-select" id="testTypeSelect" name="test_type">
                                        <option value="">All Test Types</option>
                                        <option value="CA">CA (Continuous Assessment)</option>
                                        <option value="Exam">Exam</option>
                                        <option value="Exercise">Exercise</option>
                                    </select>
                                    <small class="text-muted">Optionally limit to specific test type</small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="gradeSelect" class="form-label">Grade (Optional)</label>
                                    <select class="form-select" id="gradeSelect" name="grade_id">
                                        <option value="">All Grades</option>
                                        @if (!empty($grades))
                                            @foreach ($grades as $grade)
                                                <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="subjectSelect" class="form-label">Subject (Optional)</label>
                                    <select class="form-select" id="subjectSelect" name="grade_subject_id" disabled>
                                        <option value="">All Subjects</option>
                                    </select>
                                    <small class="text-muted">Select a grade first to choose a subject</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Threshold Levels</label>
                                <div id="thresholdLevels">
                                    <!-- Default threshold rows will be added here -->
                                </div>
                                <button type="button" class="btn-add-threshold" onclick="addThresholdRow()">
                                    <i class="bx bx-plus"></i> Add Threshold Level
                                </button>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="isActive" name="is_active" checked>
                                    <label class="form-check-label" for="isActive">
                                        Setting is active
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-loading" id="saveThresholdBtn">
                                    <span class="btn-text"><i class="fas fa-save me-1"></i> Save Setting</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"
                                            aria-hidden="true"></span>
                                        Saving...
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteThresholdModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this threshold setting?</p>
                        <input type="hidden" id="deleteSettingId" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" onclick="confirmDeleteThreshold()">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@endsection
@section('script')
    <script>
        // Default threshold template
        const defaultThresholds = [{
                name: 'failing',
                max_percentage: 39,
                color: '#fee2e2'
            },
            {
                name: 'warning',
                max_percentage: 49,
                color: '#fef3c7'
            },
            {
                name: 'caution',
                max_percentage: 59,
                color: '#fefce8'
            }
        ];

        // Grade subjects data for cascading select
        const gradeSubjects = @json($gradeSubjects ?? []);

        $(document).ready(function() {
            $('#termId').change(function() {
                updateGrades();
            });

            $('#gradeId').change(function() {
                storeInLocalStorage();
                updateTestLists();
            });

            var initialTestId = getFromLocalStorage();
            if (initialTestId) {
                $('#selectedGradeTestId').val(initialTestId);
            }
            $('#termId').trigger('change');

            // Tab persistence
            const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    const activeTabHref = event.target.getAttribute('href');
                    localStorage.setItem('testsListActiveTab', activeTabHref);
                });
            });

            // Restore active tab on page load
            const activeTab = localStorage.getItem('testsListActiveTab');
            if (activeTab) {
                const tabTriggerEl = document.querySelector(`.nav-link[href="${activeTab}"]`);
                if (tabTriggerEl) {
                    const tab = new bootstrap.Tab(tabTriggerEl);
                    tab.show();
                }
            }

            // Initialize threshold modal events
            initThresholdModal();
        });

        function storeInLocalStorage() {
            var selectedTestId = $('#gradeId').val();
            localStorage.setItem('selectedTestId', selectedTestId);
        }

        function getFromLocalStorage() {
            return localStorage.getItem('selectedTestId');
        }

        function updateTestLists() {
            var termId = $('#termId').val();
            var gradeId = $('#gradeId').val();

            var baseUrl = "{{ route('assessment.tests-lists', ['termId' => 'tempTermId', 'gradeId' => 'tempGradeId']) }}";
            var testsUrl = baseUrl.replace('tempTermId', termId).replace('tempGradeId', gradeId);

            $.get(testsUrl, function(data) {
                $('#tests').html(data);
                if (typeof window.initializeTestTabs === 'function') {
                    window.initializeTestTabs();
                }
            });
        }

        function updateGrades() {
            var termId = $('#termId').val();
            var selectedTestId = getFromLocalStorage();
            var getGradesForTerm = "{{ route('klasses.get-grades-for-term') }}";

            $.ajax({
                url: getGradesForTerm,
                type: 'GET',
                data: {
                    'term_id': termId,
                },
                success: function(data) {
                    var $gradeSelect = $('#gradeId');
                    $gradeSelect.empty();

                    $.each(data, function(index, grade) {
                        if (grade.name && grade.name.toUpperCase().indexOf('REC') !== -1) {
                            return;
                        }

                        var $option = $('<option></option>').val(grade.id).text(grade.name);
                        $gradeSelect.append($option);
                    });

                    if (selectedTestId && $gradeSelect.find('option[value="' + selectedTestId + '"]').length >
                        0) {
                        $gradeSelect.val(selectedTestId);
                    } else {
                        $gradeSelect.val($gradeSelect.find('option:first').val());
                    }

                    storeInLocalStorage();
                    updateTestLists();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        // Threshold Settings Functions
        @can('manage-academic')
            function initThresholdModal() {
                // Grade select change - update subjects
                $('#gradeSelect').on('change', function() {
                    const gradeId = $(this).val();
                    const $subjectSelect = $('#subjectSelect');

                    $subjectSelect.empty().append('<option value="">All Subjects</option>');

                    if (gradeId && gradeSubjects[gradeId]) {
                        gradeSubjects[gradeId].forEach(function(gs) {
                            const subjectName = gs.subject ? gs.subject.name : 'Unknown Subject';
                            $subjectSelect.append(`<option value="${gs.id}">${subjectName}</option>`);
                        });
                        $subjectSelect.prop('disabled', false);
                    } else {
                        $subjectSelect.prop('disabled', true);
                    }
                });

                // Edit threshold button click
                $(document).on('click', '.edit-threshold', function() {
                    const $btn = $(this);
                    const settingId = $btn.data('setting-id');
                    const schoolType = $btn.data('school-type') || '';
                    const gradeId = $btn.data('grade-id') || '';
                    const gradeSubjectId = $btn.data('grade-subject-id') || '';
                    const testType = $btn.data('test-type') || '';
                    const thresholds = $btn.data('thresholds') || defaultThresholds;
                    const isActive = $btn.data('is-active') === 1 || $btn.data('is-active') === '1';

                    // Set form values
                    $('#editSettingId').val(settingId);
                    $('#schoolType').val(schoolType);
                    $('#testTypeSelect').val(testType);
                    $('#gradeSelect').val(gradeId).trigger('change');
                    $('#isActive').prop('checked', isActive);

                    // Wait for subjects to load then set the value
                    setTimeout(function() {
                        $('#subjectSelect').val(gradeSubjectId);
                    }, 100);

                    // Clear and populate thresholds
                    $('#thresholdLevels').empty();
                    thresholds.forEach(function(threshold) {
                        addThresholdRow(threshold.name, threshold.max_percentage, threshold.color);
                    });

                    $('#addThresholdModalLabel').text('Edit Threshold Setting');
                    const modal = new bootstrap.Modal(document.getElementById('addThresholdModal'));
                    modal.show();
                });

                // Delete threshold button click
                $(document).on('click', '.delete-threshold', function() {
                    const settingId = $(this).data('setting-id');
                    $('#deleteSettingId').val(settingId);
                    const modal = new bootstrap.Modal(document.getElementById('deleteThresholdModal'));
                    modal.show();
                });

                // Reset modal on close
                $('#addThresholdModal').on('hidden.bs.modal', function() {
                    $('#thresholdSettingForm')[0].reset();
                    $('#editSettingId').val('');
                    $('#thresholdLevels').empty();
                    $('#subjectSelect').empty().append('<option value="">All Subjects</option>').prop('disabled',
                        true);
                    $('#addThresholdModalLabel').text('Add Threshold Setting');

                    // Reset button state
                    const saveBtn = document.getElementById('saveThresholdBtn');
                    saveBtn.classList.remove('loading');
                    saveBtn.disabled = false;
                });

                // Add default thresholds when modal opens for new setting
                $('#addThresholdModal').on('show.bs.modal', function(e) {
                    if (!$('#editSettingId').val()) {
                        $('#thresholdLevels').empty();
                        defaultThresholds.forEach(function(threshold) {
                            addThresholdRow(threshold.name, threshold.max_percentage, threshold.color);
                        });
                    }
                });

                // Form submission
                $('#thresholdSettingForm').on('submit', function(e) {
                    e.preventDefault();
                    saveThresholdSetting();
                });
            }

            function addThresholdRow(name = '', maxPercentage = '', color = '#fef3c7') {
                const rowId = 'threshold-row-' + Date.now();
                const html = `
                <div class="threshold-row" id="${rowId}">
                    <input type="text" class="form-control threshold-name" placeholder="Level name (e.g., Failing)"
                           value="${name}" required>
                    <input type="number" class="form-control threshold-percentage" placeholder="Max %"
                           value="${maxPercentage}" min="0" max="100" required style="width: 100px;">
                    <input type="color" class="color-input threshold-color" value="${color}" title="Choose color">
                    <button type="button" class="btn-remove-threshold" onclick="removeThresholdRow('${rowId}')" title="Remove">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
            `;
                $('#thresholdLevels').append(html);
            }

            function removeThresholdRow(rowId) {
                $('#' + rowId).remove();
            }

            function getThresholdsFromForm() {
                const thresholds = [];
                $('.threshold-row').each(function() {
                    const $row = $(this);
                    const name = $row.find('.threshold-name').val().trim();
                    const maxPercentage = parseFloat($row.find('.threshold-percentage').val());
                    const color = $row.find('.threshold-color').val();

                    if (name && !isNaN(maxPercentage)) {
                        thresholds.push({
                            name: name,
                            max_percentage: maxPercentage,
                            color: color
                        });
                    }
                });

                // Sort by max_percentage ascending
                thresholds.sort((a, b) => a.max_percentage - b.max_percentage);
                return thresholds;
            }

            function saveThresholdSetting() {
                const saveBtn = document.getElementById('saveThresholdBtn');
                saveBtn.classList.add('loading');
                saveBtn.disabled = true;

                const thresholds = getThresholdsFromForm();

                if (thresholds.length === 0) {
                    alert('Please add at least one threshold level.');
                    saveBtn.classList.remove('loading');
                    saveBtn.disabled = false;
                    return;
                }

                const data = {
                    school_type: $('#schoolType').val() || null,
                    grade_id: $('#gradeSelect').val() || null,
                    grade_subject_id: $('#subjectSelect').val() || null,
                    test_type: $('#testTypeSelect').val() || null,
                    thresholds: thresholds,
                    is_active: $('#isActive').is(':checked')
                };

                const settingId = $('#editSettingId').val();
                const url = "{{ route('threshold.store-system-setting') }}";

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: JSON.stringify(data),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            localStorage.setItem('thresholdMessage', response.message ||
                                'Setting saved successfully');
                            location.reload();
                        } else {
                            alert(response.message || 'Failed to save setting');
                            saveBtn.classList.remove('loading');
                            saveBtn.disabled = false;
                        }
                    },
                    error: function(xhr) {
                        let message = 'An error occurred while saving';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            message = Object.values(errors).flat().join('\n');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        alert(message);
                        saveBtn.classList.remove('loading');
                        saveBtn.disabled = false;
                    }
                });
            }

            function toggleThresholdStatus(settingId) {
                const url = "{{ route('threshold.toggle-system-setting', ['id' => 'SETTING_ID']) }}".replace('SETTING_ID',
                    settingId);

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            const $badge = $(`tr[data-setting-id="${settingId}"] .status-badge`);
                            if (response.data.is_active) {
                                $badge.removeClass('inactive').addClass('active').text('Active')
                                    .attr('title', 'Click to deactivate');
                            } else {
                                $badge.removeClass('active').addClass('inactive').text('Inactive')
                                    .attr('title', 'Click to activate');
                            }
                        } else {
                            alert(response.message || 'Failed to toggle status');
                        }
                    },
                    error: function(xhr) {
                        alert('An error occurred while toggling status');
                    }
                });
            }

            function confirmDeleteThreshold() {
                const settingId = $('#deleteSettingId').val();
                const url = "{{ route('threshold.delete-system-setting', ['id' => 'SETTING_ID']) }}".replace('SETTING_ID',
                    settingId);

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $(`tr[data-setting-id="${settingId}"]`).fadeOut(300, function() {
                                $(this).remove();
                                // Check if table is empty
                                if ($('#thresholdSettingsBody tr').length === 0) {
                                    $('#thresholdSettingsBody').html(`
                                    <tr class="empty-row">
                                        <td colspan="4">
                                            <div class="empty-state">
                                                <i class="fas fa-sliders-h"></i>
                                                <p>No threshold settings configured yet.</p>
                                                <p class="text-muted">Click "Add New Threshold Setting" to create your first threshold.</p>
                                            </div>
                                        </td>
                                    </tr>
                                `);
                                }
                            });
                            bootstrap.Modal.getInstance(document.getElementById('deleteThresholdModal')).hide();
                        } else {
                            alert(response.message || 'Failed to delete setting');
                        }
                    },
                    error: function(xhr) {
                        alert('An error occurred while deleting');
                    }
                });
            }

            // Check for stored message on page load
            $(document).ready(function() {
                const message = localStorage.getItem('thresholdMessage');
                if (message) {
                    // You could display this as a toast or alert
                    localStorage.removeItem('thresholdMessage');
                }
            });
        @endcan
    </script>
@endsection
