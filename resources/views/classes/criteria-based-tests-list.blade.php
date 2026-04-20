<style>
    .tests-container {
        background: white;
        border-radius: 3px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Tab Styling */
    .nav-tabs {
        border-bottom: 2px solid #e5e7eb;
        margin-bottom: 20px;
        gap: 8px;
        flex-wrap: wrap;
    }

    .nav-tabs .nav-item {
        margin-bottom: 0;
    }

    .nav-tabs .nav-link {
        border: 1px solid #d1d5db;
        border-radius: 3px;
        color: #374151;
        font-weight: 500;
        padding: 8px 16px;
        transition: all 0.2s ease;
        background-color: #f9fafb;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .nav-tabs .nav-link:hover {
        color: #3b82f6;
        border-color: #3b82f6;
        background-color: #eff6ff;
    }

    .nav-tabs .nav-link.active {
        color: #ffffff;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border-color: transparent;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .tab-content {
        padding-top: 20px;
    }

    /* Section Title */
    .test-section {
        margin-bottom: 30px;
    }

    .test-section-title {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 2px solid #3b82f6;
        color: #1f2937;
        display: inline-block;
    }

    /* Test Card Styling */
    .test-card {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        transition: all 0.2s ease;
        overflow: hidden;
    }

    .test-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .test-card .card-header {
        background-color: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        padding: 12px 16px;
        font-size: 14px;
    }

    .test-card .card-body {
        padding: 16px;
    }

    .test-type {
        font-weight: 600;
        color: #3b82f6;
        font-size: 14px;
    }

    .test-info {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .test-info i {
        color: #9ca3af;
        font-size: 16px;
    }

    /* Badge Styling */
    .points-badge {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        padding: 4px 10px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 500;
    }

    /* Exam Card */
    .exam-card {
        border-left: 4px solid #10b981;
    }

    .exam-card .test-type {
        color: #10b981;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #e5e7eb;
    }

    .action-buttons .btn i {
        font-size: 18px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 48px;
        color: #d1d5db;
        margin-bottom: 16px;
    }

    /* Copy Modal Styling */
    #copyCriteriaTestModal .modal-content {
        border: none;
        border-radius: 3px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    #copyCriteriaTestModal .modal-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        background: white;
    }

    #copyCriteriaTestModal .modal-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
    }

    #copyCriteriaTestModal .modal-body {
        padding: 1.5rem;
    }

    #copyCriteriaTestModal .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        background: white;
        gap: 12px;
    }

    #copyCriteriaTestModal .btn-cancel {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: white;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        color: #374151;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    #copyCriteriaTestModal .btn-cancel:hover {
        background: #f3f4f6;
        color: #1f2937;
    }

    #copyCriteriaTestModal .btn-copy {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        border-radius: 3px;
        color: white;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    #copyCriteriaTestModal .btn-copy:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    #copyCriteriaTestModal .btn-copy:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    #copyCriteriaTestModal .btn-copy.loading .btn-text {
        display: none !important;
    }

    #copyCriteriaTestModal .btn-copy.loading .btn-spinner {
        display: inline-flex !important;
        align-items: center;
    }
</style>

<div class="tests-container">
    <div class="tests-body">
        @if($groupedTests->isEmpty())
            <div class="empty-state">
                <i class="bx bx-clipboard"></i>
                <h5>No Tests Found</h5>
                <p>There are no criteria-based tests for the selected grade. Create a new test to get started.</p>
            </div>
        @else
            <ul class="nav nav-tabs" id="criteriaSubjectTabs" role="tablist">
                @foreach ($groupedTests as $subject => $tests)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                            id="criteria-tab-{{ Str::slug($subject) }}"
                            data-bs-toggle="tab"
                            data-bs-target="#criteria-content-{{ Str::slug($subject) }}"
                            type="button"
                            role="tab"
                            aria-controls="criteria-content-{{ Str::slug($subject) }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                            {{ $subject }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content" id="criteriaSubjectTabContent">
                @foreach ($groupedTests as $subject => $tests)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                         id="criteria-content-{{ Str::slug($subject) }}"
                         role="tabpanel"
                         aria-labelledby="criteria-tab-{{ Str::slug($subject) }}">

                        @if($tests->where('type', '!=', 'Exam')->count() > 0)
                            <div class="test-section">
                                <h3 class="test-section-title"><i class="bx bx-edit me-2"></i>Continuous Assessment Tests</h3>
                                <div class="row">
                                    @foreach ($tests->where('type', '!=', 'Exam')->sortBy('sequence') as $test)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card test-card">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <span class="test-type">{{ ucfirst($test->type) }} - Sequence {{ $test->sequence }}</span>
                                                    <span class="points-badge">{{ $test->abbrev }}</span>
                                                </div>
                                                <div class="card-body">
                                                    <p class="test-info">
                                                        <i class="bx bx-calendar"></i>
                                                        {{ \Carbon\Carbon::parse($test->start_date)->format('M d, Y') }} -
                                                        {{ \Carbon\Carbon::parse($test->end_date)->format('M d, Y') }}
                                                    </p>
                                                    <p class="test-info">
                                                        <i class="bx bx-book"></i>
                                                        Grade: {{ $test->grade->name ?? 'N/A' }}
                                                    </p>
                                                    <p class="test-info">
                                                        <i class="bx bx-check-circle"></i>
                                                        Assessment: {{ $test->assessment ? 'Yes' : 'No' }}
                                                    </p>
                                                    @if (!session('is_past_term'))
                                                        <div class="action-buttons">
                                                            <a href="{{ route('reception.get-test-update', $test->id) }}"
                                                                class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Edit Test">
                                                                <i class="bx bx-edit-alt"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary copy-criteria-test-btn"
                                                                data-bs-toggle="modal" data-bs-target="#copyCriteriaTestModal"
                                                                data-test-id="{{ $test->id }}"
                                                                data-test-name="{{ $test->name }} ({{ ucfirst($test->type) }} - Seq {{ $test->sequence }})"
                                                                data-test-grade-id="{{ $test->grade_id }}"
                                                                data-test-subject-id="{{ $test->grade_subject_id }}"
                                                                title="Copy Test">
                                                                <i class="bx bx-copy"></i>
                                                            </button>
                                                            <a href="{{ route('reception.delete-criteria-based-test', $test->id) }}"
                                                                class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip"
                                                                onclick="return confirmDeleteTest()" title="Delete Test">
                                                                <i class="bx bx-trash"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($tests->where('type', 'Exam')->count() > 0)
                            <div class="test-section">
                                <h3 class="test-section-title"><i class="bx bx-file me-2"></i>Examinations</h3>
                                <div class="row">
                                    @foreach ($tests->where('type', 'Exam')->sortBy('sequence') as $test)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card test-card exam-card">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <span class="test-type">Exam - Sequence {{ $test->sequence }}</span>
                                                    <span class="points-badge">{{ $test->abbrev }}</span>
                                                </div>
                                                <div class="card-body">
                                                    <p class="test-info">
                                                        <i class="bx bx-calendar"></i>
                                                        {{ \Carbon\Carbon::parse($test->start_date)->format('M d, Y') }} -
                                                        {{ \Carbon\Carbon::parse($test->end_date)->format('M d, Y') }}
                                                    </p>
                                                    <p class="test-info">
                                                        <i class="bx bx-book"></i>
                                                        Grade: {{ $test->grade->name ?? 'N/A' }}
                                                    </p>
                                                    <p class="test-info">
                                                        <i class="bx bx-check-circle"></i>
                                                        Assessment: {{ $test->assessment ? 'Yes' : 'No' }}
                                                    </p>
                                                    @if (!session('is_past_term'))
                                                        <div class="action-buttons">
                                                            <a href="{{ route('reception.get-test-update', $test->id) }}"
                                                                class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Edit Exam">
                                                                <i class="bx bx-edit-alt"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary copy-criteria-test-btn"
                                                                data-bs-toggle="modal" data-bs-target="#copyCriteriaTestModal"
                                                                data-test-id="{{ $test->id }}"
                                                                data-test-name="{{ $test->name }} ({{ ucfirst($test->type) }} - Seq {{ $test->sequence }})"
                                                                data-test-grade-id="{{ $test->grade_id }}"
                                                                data-test-subject-id="{{ $test->grade_subject_id }}"
                                                                title="Copy Exam">
                                                                <i class="bx bx-copy"></i>
                                                            </button>
                                                            <a href="{{ route('reception.delete-criteria-based-test', $test->id) }}"
                                                                class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip"
                                                                onclick="return confirmDeleteTest()" title="Delete Exam">
                                                                <i class="bx bx-trash"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Copy Criteria Test Modal -->
<div class="modal fade" id="copyCriteriaTestModal" tabindex="-1" aria-labelledby="copyCriteriaTestModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="copyCriteriaTestModalLabel">
                    <i class="bx bx-copy me-2"></i>Copy Test
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="copyCriteriaTestForm" action="{{ route('reception.copy-criteria-based-test') }}" method="POST">
                @csrf
                <input type="hidden" name="test_id" id="copyCriteriaTestId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Copying Test:</label>
                        <p id="copyCriteriaTestName" class="text-primary fw-medium mb-0"></p>
                    </div>
                    <div class="mb-3">
                        <label for="targetCriteriaSubject" class="form-label fw-semibold">Copy to Subject <span class="text-danger">*</span></label>
                        <select class="form-select" name="target_subject_id" id="targetCriteriaSubject" required>
                            <option value="">Select target subject...</option>
                            @foreach($availableSubjects ?? [] as $subject)
                                <option value="{{ $subject['id'] }}" data-grade-id="{{ $subject['grade_id'] }}">
                                    {{ $subject['subject_name'] }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Only subjects in the same grade are shown.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">
                        <i class="bx bx-x"></i> Cancel
                    </button>
                    <button type="submit" class="btn-copy" id="copyCriteriaTestSubmitBtn">
                        <span class="btn-text"><i class="bx bx-copy"></i> Copy Test</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Copying...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmDeleteTest() {
        return confirm('Are you sure you want to delete this test? This action cannot be undone.');
    }

    function initializeCriteriaTabs() {
        var triggerTabList = [].slice.call(document.querySelectorAll('#criteriaSubjectTabs button'));

        function activateTab(tabId) {
            triggerTabList.forEach(function(tab) {
                if (tab.id === tabId) {
                    tab.classList.add('active');
                    tab.setAttribute('aria-selected', 'true');
                    document.querySelector(tab.dataset.bsTarget).classList.add('show', 'active');
                } else {
                    tab.classList.remove('active');
                    tab.setAttribute('aria-selected', 'false');
                    document.querySelector(tab.dataset.bsTarget).classList.remove('show', 'active');
                }
            });
        }

        var storedTab = localStorage.getItem('selectedCriteriaSubjectTab');
        if (storedTab && document.getElementById(storedTab)) {
            activateTab(storedTab);
        } else {
            if (triggerTabList.length > 0) {
                var firstTabId = triggerTabList[0].id;
                activateTab(firstTabId);
                localStorage.setItem('selectedCriteriaSubjectTab', firstTabId);
            }
        }

        triggerTabList.forEach(function(triggerEl) {
            triggerEl.addEventListener('click', function(event) {
                event.preventDefault();
                var tabId = this.id;
                activateTab(tabId);
                localStorage.setItem('selectedCriteriaSubjectTab', tabId);
            });
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Copy Criteria Test Modal functionality
    function initializeCopyCriteriaTestModal() {
        var copyCriteriaTestModal = document.getElementById('copyCriteriaTestModal');
        if (!copyCriteriaTestModal) return;

        copyCriteriaTestModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var testId = button.getAttribute('data-test-id');
            var testName = button.getAttribute('data-test-name');
            var testGradeId = button.getAttribute('data-test-grade-id');
            var testSubjectId = button.getAttribute('data-test-subject-id');

            // Set hidden input and display name
            document.getElementById('copyCriteriaTestId').value = testId;
            document.getElementById('copyCriteriaTestName').textContent = testName;

            // Filter options: show only same grade, hide current subject
            var targetSelect = document.getElementById('targetCriteriaSubject');
            var options = targetSelect.querySelectorAll('option');
            var hasVisibleOptions = false;

            options.forEach(function(option) {
                if (option.value === '') {
                    // Keep the placeholder visible
                    option.style.display = '';
                    option.disabled = false;
                    option.selected = true;
                } else {
                    var optionGradeId = option.getAttribute('data-grade-id');
                    var optionSubjectId = option.value;

                    // Show only options with same grade and different subject
                    if (optionGradeId == testGradeId && optionSubjectId != testSubjectId) {
                        option.style.display = '';
                        option.disabled = false;
                        hasVisibleOptions = true;
                    } else {
                        option.style.display = 'none';
                        option.disabled = true;
                    }
                }
            });

            // Update placeholder and button state based on available options
            var placeholder = targetSelect.querySelector('option[value=""]');
            if (!hasVisibleOptions) {
                placeholder.textContent = 'No other subjects available in this grade';
                document.getElementById('copyCriteriaTestSubmitBtn').disabled = true;
            } else {
                placeholder.textContent = 'Select target subject...';
                document.getElementById('copyCriteriaTestSubmitBtn').disabled = false;
            }
        });

        // Reset form when modal is hidden
        copyCriteriaTestModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('copyCriteriaTestForm').reset();
            var submitBtn = document.getElementById('copyCriteriaTestSubmitBtn');
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
            var spinner = submitBtn.querySelector('.btn-spinner');
            var btnText = submitBtn.querySelector('.btn-text');
            if (spinner) spinner.classList.add('d-none');
            if (btnText) btnText.style.display = '';

            // Reset all options visibility
            var targetSelect = document.getElementById('targetCriteriaSubject');
            var options = targetSelect.querySelectorAll('option');
            options.forEach(function(option) {
                option.style.display = '';
                option.disabled = (option.value === '');
            });
            var placeholder = targetSelect.querySelector('option[value=""]');
            if (placeholder) placeholder.textContent = 'Select target subject...';
        });

        // Form submission with loading state
        var copyCriteriaForm = document.getElementById('copyCriteriaTestForm');
        copyCriteriaForm.addEventListener('submit', function(e) {
            var targetSubject = document.getElementById('targetCriteriaSubject').value;
            if (!targetSubject) {
                e.preventDefault();
                alert('Please select a target subject.');
                return;
            }

            var submitBtn = document.getElementById('copyCriteriaTestSubmitBtn');
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            submitBtn.querySelector('.btn-spinner').classList.remove('d-none');
            submitBtn.querySelector('.btn-text').style.display = 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initializeCriteriaTabs();
        initializeCopyCriteriaTestModal();
    });

    window.initializeCriteriaTabs = initializeCriteriaTabs;
    window.initializeCopyCriteriaTestModal = initializeCopyCriteriaTestModal;
</script>
