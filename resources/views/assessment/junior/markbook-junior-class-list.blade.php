<style>
    .table-scroll-wrapper {
        position: relative;
        overflow-x: hidden;
    }

    .scroll-progress {
        height: 6px;
        background: #e0e7ff;
        border-radius: 999px;
        margin: 6px 0;
        overflow: hidden;
    }

    .scroll-progress-bar {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #5156BE 0%, #6366f1 100%);
        border-radius: inherit;
        transition: width 120ms ease-out;
    }

    .table-responsive {
        overflow-x: auto;
        overflow-y: visible;
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .table-responsive::-webkit-scrollbar {
        display: none;
        height: 0;
        width: 0;
    }

    #markbook-class {
        width: max-content;
        min-width: 1200px;
        table-layout: auto;
        border-collapse: separate;
        border-spacing: 0;
    }

    #markbook-class thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        padding: 12px 10px;
        vertical-align: middle;
    }

    #markbook-class tbody td {
        padding: 10px;
        color: #4b5563;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
        font-size: 14px;
    }

    #markbook-class tbody tr:hover {
        background-color: #f9fafb;
    }

    #markbook-class th,
    #markbook-class td {
        white-space: nowrap;
        min-width: 55px;
        z-index: 1;
    }

    #markbook-class th:nth-child(-n+5),
    #markbook-class td:nth-child(-n+5) {
        position: sticky;
        left: 0;
        background-color: #fff;
        z-index: 2;
    }

    #markbook-class tbody tr:hover td:nth-child(-n+5) {
        background-color: #f9fafb;
    }

    #markbook-class th:nth-child(1),
    #markbook-class td:nth-child(1) {
        left: -4px;
    }

    #markbook-class th:nth-child(2),
    #markbook-class td:nth-child(2) {
        left: 40px;
    }

    #markbook-class th:nth-child(3),
    #markbook-class td:nth-child(3) {
        left: 115px;
    }

    #markbook-class th:nth-child(4),
    #markbook-class td:nth-child(4) {
        left: 190px;
    }

    #markbook-class th:nth-child(5),
    #markbook-class td:nth-child(5) {
        left: 225px;
    }

    /* Student cell with avatar */
    .student-cell {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .student-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .student-avatar-placeholder {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 12px;
        flex-shrink: 0;
    }

    .student-avatar-placeholder.male {
        background: #dbeafe;
        color: #1e40af;
    }

    .student-avatar-placeholder.female {
        background: #fce7f3;
        color: #be185d;
    }

    .gender-male {
        color: #007bff;
    }

    .gender-female {
        color: #e83e8c;
    }

    .score-cell {
        width: 80px;
        min-width: 75px;
        text-align: center;
        vertical-align: middle;
    }

    .grade-cell {
        width: 35px;
        min-width: 35px;
        text-align: center;
    }

    /* Sortable header styling */
    .sortable-header {
        cursor: pointer;
        user-select: none;
        transition: background-color 0.2s ease;
    }

    .sortable-header:hover {
        background-color: #e5e7eb !important;
    }

    .sort-icon {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        margin-left: 4px;
        font-size: 9px;
        line-height: 0.7;
        vertical-align: middle;
        color: #9ca3af;
    }

    .sort-icon .fa-caret-up,
    .sort-icon .fa-caret-down {
        display: block;
    }

    .sort-icon.asc .fa-caret-up {
        color: #3b82f6;
    }

    .sort-icon.desc .fa-caret-down {
        color: #3b82f6;
    }

    /* Test section styling - CA tests (vertical borders only) */
    #markbook-class thead th.ca-cell,
    #markbook-class thead th.exam-cell {
        font-size: 12px !important;
    }

    .ca-cell {
        border-left: 1px solid #d1d5db;
        vertical-align: middle;
        text-align: center;
    }

    .ca-cell-first {
        border-left: 1px solid #d1d5db;
    }

    .ca-cell-last {
        border-right: 1px solid #d1d5db;
    }

    /* CA header corners */
    th.ca-cell-first {
        border-top: 1px solid #d1d5db;
        border-top-left-radius: 3px !important;
        overflow: hidden;
        background-clip: padding-box;
    }

    th.ca-cell-last {
        border-top: 1px solid #d1d5db;
        border-top-right-radius: 3px !important;
        overflow: hidden;
        background-clip: padding-box;
    }

    th.ca-cell {
        border-top: 1px solid #d1d5db;
    }

    /* CA body last row corners */
    #markbook-class tbody tr:last-child td.ca-cell-first {
        border-bottom: 1px solid #d1d5db;
        border-bottom-left-radius: 3px;
    }

    #markbook-class tbody tr:last-child td.ca-cell-last {
        border-bottom: 1px solid #d1d5db;
        border-bottom-right-radius: 3px;
    }

    #markbook-class tbody tr:last-child td.ca-cell {
        border-bottom: 1px solid #d1d5db;
    }

    /* Test section styling - Exam tests (vertical borders only) */
    .exam-cell {
        border-left: 1px solid #d1d5db;
        vertical-align: middle;
        text-align: center;
    }

    .exam-cell-first {
        border-left: 1px solid #d1d5db;
    }

    .exam-cell-last {
        border-right: 1px solid #d1d5db;
    }

    /* Exam header corners */
    th.exam-cell-first {
        border-top: 1px solid #d1d5db;
        border-top-left-radius: 3px !important;
        overflow: hidden;
        background-clip: padding-box;
    }

    th.exam-cell-last {
        border-top: 1px solid #d1d5db;
        border-top-right-radius: 3px !important;
        overflow: hidden;
        background-clip: padding-box;
    }

    th.exam-cell {
        border-top: 1px solid #d1d5db;
    }

    /* Exam body last row corners */
    #markbook-class tbody tr:last-child td.exam-cell-first {
        border-bottom: 1px solid #d1d5db;
        border-bottom-left-radius: 3px;
    }

    #markbook-class tbody tr:last-child td.exam-cell-last {
        border-bottom: 1px solid #d1d5db;
        border-bottom-right-radius: 3px;
    }

    #markbook-class tbody tr:last-child td.exam-cell {
        border-bottom: 1px solid #d1d5db;
    }

    /* Spacing between test groups (CA and Exam) */
    .test-group-spacer {
        width: 2px !important;
        min-width: 2px !important;
        max-width: 2px !important;
        padding: 0 !important;
        background-color: #f9fafb !important;
        border: none !important;
    }

    thead th.test-group-spacer {
        background-color: #f9fafb !important;
    }

    .grade-cell input[type="text"],
    .grade-cell span {
        width: 100%;
        padding: 0;
        margin: 0;
        box-sizing: border-box;
    }

    #markbook-class input[type="text"],
    #markbook-class .score-input {
        width: 100%;
        min-width: 75px;
        max-width: 80px;
        font-size: 13px;
        padding: 6px 8px;
        height: auto;
        box-sizing: border-box;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        transition: all 0.2s ease;
        text-align: center;
    }

    #markbook-class input[type="text"]::placeholder {
        text-align: center;
        color: #9ca3af;
    }

    #markbook-class input[type="text"]:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        10%,
        30%,
        50%,
        70%,
        90% {
            transform: translateX(-3px);
        }

        20%,
        40%,
        60%,
        80% {
            transform: translateX(3px);
        }
    }

    input.invalid {
        border-color: #b91c1c !important;
        background-color: #fca5a5 !important;
        animation: shake 0.5s ease-in-out;
    }

    /* Spinner overlay */
    #spinner-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(248, 249, 250, 0.92);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease-in-out;
        z-index: 9999;
    }

    #spinner-overlay.show {
        opacity: 1;
        visibility: visible;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(0.85);
            opacity: 0.7;
        }

        50% {
            transform: scale(1);
            opacity: 1;
        }

        100% {
            transform: scale(0.85);
            opacity: 0.7;
        }
    }

    @keyframes gradient {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    @keyframes dots {

        0%,
        20% {
            content: '.';
        }

        40% {
            content: '..';
        }

        60%,
        80% {
            content: '...';
        }

        100% {
            content: '.';
        }
    }

    .btn-loading .bx {
        animation: spin 0.8s linear infinite;
    }

    .spinner-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ffffff, #f8f9fa, #ffffff);
        background-size: 200% 200%;
        animation: gradient 5s ease infinite;
        padding: 20px 20px;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(81, 86, 190, 0.15), 0 5px 10px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(81, 86, 190, 0.1);
    }

    .spinner-text {
        position: relative;
        margin-top: 10px;
        color: #5156BE;
        font-weight: 400;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
    }

    .spinner-text::after {
        content: '.';
        animation: dots 1.5s infinite steps(1);
    }

    .custom-spinner {
        display: inline-block;
        width: 1.5rem;
        height: 1.5rem;
        position: relative;
    }

    .custom-spinner::before,
    .custom-spinner::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .custom-spinner::before {
        border: 4px solid rgba(81, 86, 190, 0.2);
    }

    .custom-spinner::after {
        border: 4px solid transparent;
        border-top-color: #5156BE;
        animation: spin 0.8s linear infinite;
    }

    /* Progress bar styling */
    #progressBar {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #progressBarText {
        position: absolute;
        width: 100%;
        text-align: center;
        color: white;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        font-size: 14px;
        line-height: 25px;
    }

    /* Minimized indicator hover effect */
    #minimizedProgressIndicator:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    /* Option card for recalculate modal */
    .option-card:hover {
        border-color: #5156BE !important;
        background: #f8f9fa !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(81, 86, 190, 0.15);
    }

    .option-card.selected {
        border-color: #5156BE !important;
        background: rgba(81, 86, 190, 0.05) !important;
    }

    .option-card.selected::before {
        content: '✓';
        position: absolute;
        top: 8px;
        right: 8px;
        width: 22px;
        height: 22px;
        background: #5156BE;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }

    .option-card {
        position: relative;
    }

    /* Comment link styling */
    .comment-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .comment-link:hover {
        background: rgba(59, 130, 246, 0.1);
        transform: scale(1.1);
    }

    .comment-link:hover i {
        color: #3b82f6 !important;
    }

    .student-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        color: #fff;
        margin-left: 6px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
</style>

<div class="col-12">
    <div id="spinner-overlay">
        <div class="spinner-container">
            <div class="custom-spinner" role="status">
                <span class="visually-hidden">Saving...</span>
            </div>
            <div class="spinner-text">Saving changes</div>
        </div>
    </div>

    <div class="modal fade" id="recalculateModal" tabindex="-1" aria-labelledby="recalculateModalLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
                style="border-radius: 3px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
                <div class="modal-header border-0" style="padding: 1.5rem 1.5rem 1rem;">
                    <h5 class="modal-title fw-bold d-flex align-items-center" id="recalculateModalLabel"
                        style="color: #2d3748; font-size: 1.15rem;">
                        Recalculate Scores for Grades
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 0 1.5rem 1.5rem;">
                    <div class="alert alert-warning border-0 d-flex align-items-start"
                        style="background: #fff8e1; padding: 0.75rem; margin-bottom: 1rem; border-radius: 3px;">
                        <i class="bx bx-info-circle"
                            style="font-size: 18px; color: #f59e0b; margin-right: 0.5rem; margin-top: 2px;"></i>
                        <small style="color: #92400e; line-height: 1.5;">Select the subject type to recalculate for this
                            grade.</small>
                    </div>

                    <form id="recalculateForm" method="POST"
                        action="{{ route('assessment.recalculate-scores', ['id' => $klass->klass->id, 'context' => $markbookCurrentContext]) }}">
                        @csrf
                        <input type="hidden" name="subject_type" id="selectedSubjectType" value="">

                        <div class="row g-2">
                            <div class="col-6">
                                <div class="option-card" data-value="klass_subjects"
                                    style="border: 2px solid #e5e7eb; border-radius: 3px; padding: 1rem; cursor: pointer; transition: all 0.2s ease; background: #fff; height: 100%;">
                                    <div class="d-flex align-items-center mb-2">
                                        <div
                                            style="width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: rgba(81, 86, 190, 0.1); margin-right: 0.75rem; flex-shrink: 0;">
                                            <i class="bx bx-book-content" style="font-size: 20px; color: #5156BE;"></i>
                                        </div>
                                        <div
                                            style="font-weight: 600; font-size: 0.95rem; color: #1f2937; line-height: 1.3;">
                                            Class Subjects</div>
                                    </div>
                                    <div style="color: #6b7280; font-size: 0.75rem; line-height: 1.4; padding-left: 0;">
                                        Core subjects for this grade
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="option-card" data-value="optional_subjects"
                                    style="border: 2px solid #e5e7eb; border-radius: 3px; padding: 1rem; cursor: pointer; transition: all 0.2s ease; background: #fff; height: 100%;">
                                    <div class="d-flex align-items-center mb-2">
                                        <div
                                            style="width: 40px; height: 40px; border-radius: 3px; display: flex; align-items: center; justify-content: center; background: rgba(16, 185, 129, 0.1); margin-right: 0.75rem; flex-shrink: 0;">
                                            <i class="bx bx-book-open" style="font-size: 20px; color: #10b981;"></i>
                                        </div>
                                        <div
                                            style="font-weight: 600; font-size: 0.95rem; color: #1f2937; line-height: 1.3;">
                                            Optional Subjects</div>
                                    </div>
                                    <div style="color: #6b7280; font-size: 0.75rem; line-height: 1.4; padding-left: 0;">
                                        Elective subjects for students
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0"
                    style="padding: 1rem 1.5rem 1.5rem; background: #f9fafb; border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="font-size: 0.875rem; padding: 0.5rem 1rem; border-radius: 3px;">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="proceedBtn" disabled
                        style="font-size: 0.875rem; padding: 0.5rem 1.25rem; border-radius: 3px; background: linear-gradient(135deg, #5156BE 0%, #6366f1 100%); border: none;">
                        <i class="bx bx-play me-1"></i>Recalculate Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Modal -->
    <div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="progressModalLabel">
                        <i class="bx bx-loader-circle bx-spin me-2" id="progressSpinner"></i>Grade Recalculation
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="minimizeProgressBtn">
                        <i class="bx bx-minus"></i> Run in Background
                    </button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small" id="progressMessage">Initializing...</span>
                            <span class="fw-bold text-primary" id="progressPercentage">0%</span>
                        </div>
                        <div class="progress" style="height: 25px; border-radius: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                role="progressbar" id="progressBar" style="width: 0%;" aria-valuenow="0"
                                aria-valuemin="0" aria-valuemax="100">
                                <span id="progressBarText" class="fw-bold">0%</span>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info border-0 mb-0" style="background-color: #e7f3ff;">
                        <i class="bx bx-info-circle me-2"></i>
                        <small>The recalculation is running in the background. You can close this window and continue
                            working.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Minimized Progress Indicator -->
    <div id="minimizedProgressIndicator"
        style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 1050; background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 15px 20px; min-width: 300px; cursor: pointer;">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center">
                <i class="bx bx-loader-circle bx-spin me-2 text-primary" style="font-size: 20px;"></i>
                <span class="fw-bold">Recalculating Grades</span>
            </div>
            <span class="badge bg-primary" id="minimizedPercentage">0%</span>
        </div>
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-primary" id="minimizedProgressBar" style="width: 0%;"></div>
        </div>
        <small class="text-muted d-block mt-1" id="minimizedMessage">Initializing...</small>
    </div>

    {{-- Threshold Settings Modal --}}
    @include('components.threshold-settings-modal', ['thresholdSettings' => $thresholdSettings ?? null])

    @if (!empty($klass))
        <div style="background: #4e73df; border-radius: 3px; color: white; padding: 0.5rem; margin: 2px;"
            class="row">
            <div class="col-md-6">
                <p style="margin: 0; font-size: 0.85rem;" class="class-info-text">
                    ({{ $klass->klass->name ?? '' }}) Teacher: @if ($klass->teacher)
                        {{ $klass->teacher->fullName }}
                    @else
                        N/A
                    @endif
                    @if ($klass->assistantTeacher)
                        | Asst: {{ $klass->assistantTeacher->fullName }}
                    @endif
                    ({{ $klass->klass->students->count() ?? '' }})
                </p>
            </div>
            <div class="col-md-6 d-flex justify-content-end">
                {{-- Threshold Settings Button --}}
                <button type="button" class="btn btn-sm btn-link text-decoration-none" data-bs-toggle="modal"
                    data-bs-target="#thresholdSettingsModal" title="Passing Threshold Settings">
                    <i class="fa-solid fa-sliders text-white font-size-18"></i>
                </button>
                <button type="button" class="btn btn-sm btn-link text-info text-decoration-none"
                    data-bs-toggle="modal" data-bs-target="#recalculateModal" id="recalculateBtn"
                    title="Recalculate Marks for This Grade">
                    <i class="fa-solid fa-person-digging text-white font-size-18"></i>
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('assessment.update-marks') }}">
            @csrf
            <input name="term" type="hidden" value="{{ $klass->term->id }}">
            <input name="year" type="hidden" value="{{ $klass->term->year }}">
            <input name="subject" type="hidden" value="{{ $klass->subject->id }}">
            <input name="scope_type" type="hidden" value="klass_subject">
            <input name="scope_id" type="hidden" value="{{ $klass->id }}">

            @if (Auth::user()->can('manage-academic') || !session('is_past_term'))
                <div class="row">
                    <div class="col-md-12 mb-2 d-flex justify-content-end">
                        <button style="margin-right: 6px;" type="submit"
                            class="btn btn-primary btn-sm waves-effect waves-light">
                            <i class="fas fa-save font-size-18 align-middle"></i>
                        </button>
                    </div>
                </div>
            @endif

            <div class="table-scroll-wrapper">
                <div class="scroll-progress scroll-progress-top">
                    <div class="scroll-progress-bar"></div>
                </div>
                <div class="table-responsive">
                    <table id="markbook-class" class="table align-middle">
                        @php
                            $caTests = $klass->subject->tests
                                ->where('grade_id', $klass->grade_id)
                                ->where('type', 'CA')
                                ->sortBy('sequence');

                            $examTests = $klass->subject->tests
                                ->where('grade_id', $klass->grade_id)
                                ->where('type', 'Exam')
                                ->sortBy('sequence');
                        @endphp
                        <thead>
                            <tr>
                                <th style="width: 30px;text-align:left;" scope="col">#</th>
                                <th class="sortable-header" data-sort-col="1" style="width: 80px;text-align:left;"
                                    scope="col">
                                    Firstname
                                    <span class="sort-icon">
                                        <i class="fas fa-caret-up"></i>
                                        <i class="fas fa-caret-down"></i>
                                    </span>
                                </th>
                                <th class="sortable-header" data-sort-col="2" style="width: 80px;text-align:left;"
                                    scope="col">
                                    Lastname
                                    <span class="sort-icon">
                                        <i class="fas fa-caret-up"></i>
                                        <i class="fas fa-caret-down"></i>
                                    </span>
                                </th>
                                <th class="sortable-header" data-sort-col="3" style="width: 30px;text-align:left;"
                                    scope="col">
                                    Gender
                                    <span class="sort-icon">
                                        <i class="fas fa-caret-up"></i>
                                        <i class="fas fa-caret-down"></i>
                                    </span>
                                </th>
                                <th class="sortable-header" data-sort-col="4" style="width: 30px;text-align:left;"
                                    scope="col">
                                    Class
                                    <span class="sort-icon">
                                        <i class="fas fa-caret-up"></i>
                                        <i class="fas fa-caret-down"></i>
                                    </span>
                                </th>
                                @if ($caTests->isNotEmpty())
                                    @foreach ($caTests as $index => $test)
                                        <th class="score-cell ca-cell {{ $index === 0 ? 'ca-cell-first' : '' }}"
                                            style="background-color: #5156BE;color:#fff;" scope="col"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ $test->name }}">
                                            {{ strtoupper(substr($test->abbrev, 0, 3)) }}
                                        </th>
                                        <th colspan="2" class="ca-cell"
                                            style="background-color: #5156BE;color:#fff;text-align:center;"
                                            scope="col">
                                            Grade
                                        </th>
                                    @endforeach
                                    <th colspan="2" class="ca-cell ca-cell-last"
                                        style="text-align: center;background-color: #5156BE;color:#fff;"
                                        scope="col">
                                        Avg</th>
                                @endif
                                @if ($caTests->isNotEmpty() && $examTests->isNotEmpty())
                                    <th class="test-group-spacer"></th>
                                @endif
                                @if ($examTests->isNotEmpty())
                                    @foreach ($examTests as $index => $test)
                                        <th class="score-cell exam-cell {{ $index === 0 ? 'exam-cell-first' : '' }}"
                                            style="background-color: #EBBD15FF;color:#fff;" scope="col"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ $test->name }}">
                                            {{ strtoupper(substr($test->abbrev, 0, 3)) }}
                                        </th>
                                        <th colspan="2"
                                            class="exam-cell {{ $loop->last ? 'exam-cell-last' : '' }}"
                                            style="background-color: #EBBD15FF;color:#fff;text-align:center;"
                                            scope="col">
                                            Grade
                                        </th>
                                    @endforeach
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $studentIds = $klass->klass
                                    ->students()
                                    ->orderBy('first_name', 'asc')
                                    ->pluck('students.id')
                                    ->toArray();
                                $rowIndex = 0;
                            @endphp
                            @foreach ($klass->klass->students as $index => $student)
                                <tr
                                    style="{{ $student->type && $student->type->color ? 'border-left: 4px solid ' . $student->type->color . '; background-color: ' . $student->type->color . '10;' : '' }}">
                                    <td>
                                        @php
                                            $hasComments = $student
                                                ->getSubjectComment($klass->term->id, $klass->subject->id)
                                                ->exists();
                                            $initials = strtoupper(
                                                substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1),
                                            );
                                            $genderClass = $student->gender == 'M' ? 'male' : 'female';
                                        @endphp
                                        <a class="comment-link"
                                            href="{{ route('assessment.core-subject-remarks', ['studentId' => $student->id, 'id' => $klass->id, 'studentIds' => implode(',', $studentIds), 'index' => $rowIndex, 'context' => $markbookCurrentContext]) }}">
                                            <i style="color:{{ $hasComments ? '#3b82f6' : '#9ca3af' }}; font-size:16px;"
                                                data-bs-toggle="tooltip" title="Comments" class="bx bxs-note"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="student-cell">
                                            @if ($student->photo_path)
                                                <img src="{{ URL::asset($student->photo_path) }}"
                                                    alt="{{ $student->full_name }}" class="student-avatar">
                                            @else
                                                <div class="student-avatar-placeholder {{ $genderClass }}">
                                                    {{ $initials }}</div>
                                            @endif
                                            <span>{{ $student->first_name }}</span>
                                            @if ($student->type)
                                                <span class="student-type-badge"
                                                    style="background-color: {{ $student->type->color ?? '#6c757d' }};">
                                                    {{ $student->type->type }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $student->last_name }}</td>
                                    <td>
                                        @if ($student->gender == 'M')
                                            <span class="gender-male"><i class="bx bx-male-sign"></i> M</span>
                                        @else
                                            <span class="gender-female"><i class="bx bx-female-sign"></i> F</span>
                                        @endif
                                    </td>
                                    <td>{{ $student->currentClass()->name }}</td>
                                    @if ($caTests->isNotEmpty())
                                        @foreach ($caTests as $caIndex => $test)
                                            @php
                                                $studentTest = \App\Models\StudentTest::where(
                                                    'student_id',
                                                    $student->id,
                                                )
                                                    ->where('test_id', $test->id)
                                                    ->first();
                                                $score = $studentTest ? $studentTest->score : '';
                                                $grade = $studentTest ? $studentTest->grade : '';
                                                $percentage = $studentTest ? $studentTest->percentage : '';
                                            @endphp
                                            <td class="score-cell ca-cell {{ $loop->first ? 'ca-cell-first' : '' }}">
                                                <input type="hidden"
                                                    name="students[{{ $student->id }}][tests][{{ $test->id }}][out_of]"
                                                    value="{{ $test->out_of }}">
                                                <input type="text" class="form-control form-control-sm score-input"
                                                    name="students[{{ $student->id }}][tests][{{ $test->id }}][score]"
                                                    data-row="{{ $rowIndex }}" data-col="{{ $loop->index }}"
                                                    value="{{ $score }}" placeholder="{{ $test->out_of }}">
                                            </td>
                                            <td class="grade-cell ca-cell">{{ $percentage }}%</td>
                                            <td class="grade-cell ca-cell">{{ $grade }}</td>
                                        @endforeach
                                        @php
                                            $caTestWithAvg = \App\Models\StudentTest::where('student_id', $student->id)
                                                ->whereHas('test', function ($query) use ($klass) {
                                                    $query
                                                        ->where('type', 'CA')
                                                        ->where('grade_subject_id', $klass->subject->id)
                                                        ->where('term_id', $klass->term->id)
                                                        ->where('year', $klass->term->year);
                                                })
                                                ->whereNotNull('avg_score')
                                                ->first();

                                            $ca_average_score = $caTestWithAvg ? $caTestWithAvg->avg_score : '';
                                            $ca_average_grade = $caTestWithAvg ? $caTestWithAvg->avg_grade : '';
                                        @endphp
                                        <td class="grade-cell ca-cell">{{ $ca_average_score }}%</td>
                                        <td class="grade-cell ca-cell ca-cell-last">{{ $ca_average_grade }}</td>
                                    @endif
                                    @if ($caTests->isNotEmpty() && $examTests->isNotEmpty())
                                        <td class="test-group-spacer"></td>
                                    @endif
                                    @if ($examTests->isNotEmpty())
                                        @foreach ($examTests as $examIndex => $test)
                                            @php
                                                $studentTest = \App\Models\StudentTest::where(
                                                    'student_id',
                                                    $student->id,
                                                )
                                                    ->where('test_id', $test->id)
                                                    ->first();
                                                $score = $studentTest ? $studentTest->score : '';
                                                $grade = $studentTest ? $studentTest->grade : '';
                                                $percentage = $studentTest ? $studentTest->percentage : '';
                                                $points = $studentTest ? $studentTest->points : '';
                                            @endphp
                                            <td
                                                class="score-cell exam-cell {{ $loop->first ? 'exam-cell-first' : '' }}">
                                                <input type="hidden"
                                                    name="students[{{ $student->id }}][tests][{{ $test->id }}][out_of]"
                                                    value="{{ $test->out_of }}">
                                                <input type="text" class="form-control form-control-sm score-input"
                                                    name="students[{{ $student->id }}][tests][{{ $test->id }}][score]"
                                                    data-row="{{ $rowIndex }}"
                                                    data-col="{{ $caTests->count() + $loop->index }}"
                                                    value="{{ $score }}" placeholder="{{ $test->out_of }}">
                                            </td>
                                            <td class="grade-cell exam-cell">{{ $percentage }}%</td>
                                            <td
                                                class="grade-cell exam-cell {{ $loop->last ? 'exam-cell-last' : '' }}">
                                                {{ $grade }}{{ !empty($grade) ? ' (' . $points . ' .pts)' : '' }}
                                            </td>
                                        @endforeach
                                    @endif
                                </tr>
                                @php $rowIndex++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="scroll-progress scroll-progress-bottom">
                    <div class="scroll-progress-bar"></div>
                </div>
            </div>
            @if (Auth::user()->can('manage-academic') || !session('is_past_term'))
                <div class="row">
                    <div class="col-md-12 mb-4 d-flex justify-content-end">
                        <button style="margin-right: 6px;" type="submit"
                            class="btn btn-primary btn-sm waves-effect waves-light">
                            <i class="fas fa-save font-size-18 align-middle"></i>
                        </button>
                    </div>
                </div>
            @endif
        </form>
    @endif
</div>

<script>
    // Initialize scroll progress bar - generic, works with any/multiple wrappers
    (function() {
        function bindProgressForWrapper(wrapper) {
            var scroller = wrapper.querySelector('.table-responsive');
            var bars = wrapper.querySelectorAll('.scroll-progress-bar');
            if (!scroller || !bars.length) return;

            // Remove previous handler on this scroller (if any)
            if (scroller._progressHandler) {
                scroller.removeEventListener('scroll', scroller._progressHandler);
                window.removeEventListener('resize', scroller._progressHandler);
            }

            var handler = function() {
                var maxScroll = scroller.scrollWidth - scroller.clientWidth;
                var percent = maxScroll > 0 ? (scroller.scrollLeft / maxScroll) * 100 : 0;
                bars.forEach(function(bar) {
                    bar.style.width = percent + '%';
                });
            };

            scroller._progressHandler = handler;

            scroller.addEventListener('scroll', handler, {
                passive: true
            });
            window.addEventListener('resize', handler);

            handler();
        }

        window.initScrollProgress = function() {
            document.querySelectorAll('.table-scroll-wrapper').forEach(bindProgressForWrapper);
        };

        // Run now + after full load (covers late layout changes)
        window.initScrollProgress();
        window.addEventListener('load', window.initScrollProgress);
    })();

    $(document).ready(function() {
        // Clear any existing typewriter timeouts to prevent accumulation
        if (window._typewriterTimeouts) {
            window._typewriterTimeouts.forEach(function(id) {
                clearTimeout(id);
            });
        }
        window._typewriterTimeouts = [];

        var classInfoElement = document.querySelector('.class-info-text');
        if (classInfoElement) {
            var originalHTML = classInfoElement.innerHTML;
            var tempDiv = document.createElement('div');
            tempDiv.innerHTML = originalHTML;

            var fullText = tempDiv.textContent || tempDiv.innerText;

            classInfoElement.innerHTML = '<strong></strong>';
            var strongElement = classInfoElement.querySelector('strong');

            var typingSpeed = 12.5;
            var charIndex = 0;

            function typeWriter() {
                if (charIndex < fullText.length) {
                    strongElement.textContent += fullText.charAt(charIndex);
                    charIndex++;
                    var tid = setTimeout(typeWriter, typingSpeed);
                    window._typewriterTimeouts.push(tid);
                } else {
                    addBlinkingCursor();
                }
            }

            function addBlinkingCursor() {
                var cursor = document.createElement('span');
                cursor.className = 'typing-cursor';
                cursor.innerHTML = '_';
                cursor.style.animation = 'blink 1s infinite';
                strongElement.appendChild(cursor);

                // Only add blink style once
                if (!document.getElementById('blink-cursor-style')) {
                    var style = document.createElement('style');
                    style.id = 'blink-cursor-style';
                    style.textContent = '@keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }';
                    document.head.appendChild(style);
                }

                var tid = setTimeout(function() {
                    cursor.remove();
                }, 3000);
                window._typewriterTimeouts.push(tid);
            }

            var startTid = setTimeout(typeWriter, 500);
            window._typewriterTimeouts.push(startTid);
        }

        // Multi-column sort: [{col: index, dir: 1|-1}, ...]
        var sortColumns = [];

        // Vertical Tab navigation for score inputs - remove any existing handler first to prevent duplicates
        if (window._markbookTabHandler) {
            document.removeEventListener('keydown', window._markbookTabHandler, true);
        }

        window._markbookTabHandler = function(e) {
            if (e.key !== 'Tab') return;

            var target = document.activeElement;
            if (!target || !target.classList.contains('score-input')) return;

            var currentRow = parseInt(target.dataset.row, 10);
            var currentCol = parseInt(target.dataset.col, 10);

            if (isNaN(currentRow) || isNaN(currentCol)) return;

            e.preventDefault();
            e.stopPropagation();

            // Calculate grid dimensions
            var inputs = document.querySelectorAll('.score-input[data-row][data-col]');
            var maxRow = -1;
            var maxCol = -1;
            inputs.forEach(function(inp) {
                var row = parseInt(inp.dataset.row, 10);
                var col = parseInt(inp.dataset.col, 10);
                if (!isNaN(row) && row > maxRow) maxRow = row;
                if (!isNaN(col) && col > maxCol) maxCol = col;
            });
            var totalRows = maxRow + 1;
            var totalCols = maxCol + 1;

            var nextRow, nextCol;

            if (e.shiftKey) {
                nextRow = currentRow - 1;
                nextCol = currentCol;
                if (nextRow < 0) {
                    nextRow = totalRows - 1;
                    nextCol = currentCol - 1;
                    if (nextCol < 0) nextCol = totalCols - 1;
                }
            } else {
                nextRow = currentRow + 1;
                nextCol = currentCol;
                if (nextRow >= totalRows) {
                    nextRow = 0;
                    nextCol = currentCol + 1;
                    if (nextCol >= totalCols) nextCol = 0;
                }
            }

            var nextInput = document.querySelector(
                '.score-input[data-row="' + nextRow + '"][data-col="' + nextCol + '"]'
            );
            if (nextInput) {
                nextInput.focus();
                nextInput.select();
            }
        };

        document.addEventListener('keydown', window._markbookTabHandler, true);

        // Score input validation - using event delegation to prevent handler accumulation on AJAX reload
        // Remove any existing handlers first, then re-attach with namespaced events
        $(document).off('keypress.scoreValidation', 'input[name*="[score]"]')
            .on('keypress.scoreValidation', 'input[name*="[score]"]', function(e) {
                var allowedKeys = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.'];
                if (allowedKeys.indexOf(e.key) === -1 &&
                    e.key !== 'Backspace' &&
                    e.key !== 'Delete' &&
                    e.key !== 'Tab' &&
                    e.key !== 'ArrowLeft' &&
                    e.key !== 'ArrowRight') {
                    e.preventDefault();
                }
                // Prevent multiple decimal points
                if (e.key === '.' && this.value.indexOf('.') !== -1) {
                    e.preventDefault();
                }
            });

        $(document).off('input.scoreValidation', 'input[name*="[score]"]')
            .on('input.scoreValidation', 'input[name*="[score]"]', function(e) {
                // Remove non-numeric characters except decimal
                var value = this.value.replace(/[^0-9.]/g, '');

                // Handle multiple decimals
                var parts = value.split('.');
                if (parts.length > 2) {
                    value = parts[0] + '.' + parts.slice(1).join('');
                }

                this.value = value;

                // Get out_of value from sibling hidden input
                var outOfInput = this.parentElement.querySelector('input[name*="[out_of]"]');
                var outOf = outOfInput ? parseFloat(outOfInput.value) : null;

                // Validate against out_of
                if (outOf !== null && value !== '') {
                    var numValue = parseFloat(value);
                    if (numValue > outOf) {
                        this.classList.add('invalid');
                        // Re-trigger animation
                        this.style.animation = 'none';
                        this.offsetHeight; // Trigger reflow
                        this.style.animation = '';
                    } else {
                        this.classList.remove('invalid');
                    }
                } else {
                    this.classList.remove('invalid');
                }
            });

        $(document).off('blur.scoreValidation', 'input[name*="[score]"]')
            .on('blur.scoreValidation', 'input[name*="[score]"]', function() {
                var outOfInput = this.parentElement.querySelector('input[name*="[out_of]"]');
                var outOf = outOfInput ? parseFloat(outOfInput.value) : null;

                if (outOf !== null && this.value !== '') {
                    var numValue = parseFloat(this.value);
                    if (numValue > outOf) {
                        this.classList.add('invalid');
                    } else {
                        this.classList.remove('invalid');
                    }
                }
            });

        // Sortable headers with visual feedback - use event delegation to prevent accumulation
        // Multi-column sorting: Click to sort by one column, Shift+Click to add secondary/tertiary sort
        $(document).off('click.sortableHeader', '.sortable-header')
            .on('click.sortableHeader', '.sortable-header', function(e) {
                var columnIndex = $(this).index();
                var existing = sortColumns.findIndex(function(s) { return s.col === columnIndex; });

                if (e.shiftKey) {
                    // Shift+Click: add or toggle this column in the sort stack
                    if (existing !== -1) {
                        sortColumns[existing].dir *= -1;
                    } else {
                        sortColumns.push({ col: columnIndex, dir: 1 });
                    }
                } else {
                    // Normal click: sort by this column only (or toggle direction if already sole sort)
                    if (sortColumns.length === 1 && sortColumns[0].col === columnIndex) {
                        sortColumns[0].dir *= -1;
                    } else {
                        sortColumns = [{ col: columnIndex, dir: 1 }];
                    }
                }

                // Update sort icons
                $('.sortable-header .sort-icon').removeClass('asc desc');
                sortColumns.forEach(function(s) {
                    var $icon = $('th').eq(s.col).find('.sort-icon');
                    $icon.addClass(s.dir === 1 ? 'asc' : 'desc');
                });

                var rows = $('#markbook-class tbody tr').get();
                rows.sort(function(a, b) {
                    for (var i = 0; i < sortColumns.length; i++) {
                        var aValue = $(a).children('td').eq(sortColumns[i].col).text().trim();
                        var bValue = $(b).children('td').eq(sortColumns[i].col).text().trim();
                        var cmp = aValue.localeCompare(bValue) * sortColumns[i].dir;
                        if (cmp !== 0) return cmp;
                    }
                    return 0;
                });
                $('#markbook-class tbody').empty().append(rows);
            });

        // Clear any existing modal setup timeout to prevent duplicates on AJAX reload
        if (window._modalSetupTimeout) {
            clearTimeout(window._modalSetupTimeout);
        }
        window._modalSetupTimeout = setTimeout(function() {
            var modal = document.getElementById('recalculateModal');
            var optionCards = document.querySelectorAll('.option-card');
            var proceedBtn = document.getElementById('proceedBtn');
            var selectedSubjectType = document.getElementById('selectedSubjectType');
            var recalculateForm = document.getElementById('recalculateForm');

            console.log('Modal elements found:', {
                modal: !!modal,
                optionCards: optionCards.length,
                proceedBtn: !!proceedBtn,
                selectedSubjectType: !!selectedSubjectType,
                recalculateForm: !!recalculateForm
            });

            if (optionCards.length === 0 || !proceedBtn || !selectedSubjectType || !recalculateForm) {
                return;
            }

            // Remove any existing click handlers first to avoid duplicates
            optionCards.forEach(function(card) {
                // Clone and replace to remove all event listeners
                var newCard = card.cloneNode(true);
                card.parentNode.replaceChild(newCard, card);
            });

            // Remove existing click handler from proceedBtn to avoid duplicates
            if (proceedBtn) {
                var newProceedBtn = proceedBtn.cloneNode(true);
                proceedBtn.parentNode.replaceChild(newProceedBtn, proceedBtn);
            }

            // Re-query ALL elements after replacement
            var freshOptionCards = document.querySelectorAll('.option-card');
            var freshProceedBtn = document.getElementById('proceedBtn');
            var freshSelectedSubjectType = document.getElementById('selectedSubjectType');

            console.log('After cloning, button found:', !!freshProceedBtn);
            if (freshProceedBtn) {
                console.log('Button is visible:', freshProceedBtn.offsetParent !== null);
                console.log('Button disabled:', freshProceedBtn.disabled);
                console.log('Button classes:', freshProceedBtn.className);
            }

            freshOptionCards.forEach(function(card, index) {
                card.addEventListener('click', function(e) {
                    console.log('Option card clicked:', this.getAttribute(
                        'data-value'));

                    freshOptionCards.forEach(function(c) {
                        c.classList.remove('selected');
                    });
                    this.classList.add('selected');

                    var value = this.getAttribute('data-value');
                    if (freshSelectedSubjectType) {
                        freshSelectedSubjectType.value = value;
                    }

                    if (freshProceedBtn) {
                        freshProceedBtn.disabled = false;
                        freshProceedBtn.classList.remove('btn-secondary');
                        freshProceedBtn.classList.add('btn-primary');
                    }
                });
            });

            // Add click handler to the fresh proceed button
            if (freshProceedBtn) {

                freshProceedBtn.addEventListener('click', function(e) {
                    e.preventDefault();

                    if (!freshSelectedSubjectType || !freshSelectedSubjectType.value) {
                        alert('Please select a subject type to recalculate.');
                        return;
                    }

                    var selectedOption = document.querySelector('.option-card.selected');
                    if (!selectedOption) {
                        alert('Please select a subject type to recalculate.');
                        return;
                    }

                    var titleElement = selectedOption.children[1];
                    var subjectTypeName = titleElement ? titleElement.textContent.trim() :
                        'selected subjects';
                    console.log('Subject type name:', subjectTypeName);

                    // Capture the subject type value BEFORE hiding the modal
                    var capturedSubjectType = freshSelectedSubjectType ?
                        freshSelectedSubjectType.value : '';
                    console.log('Captured subject type:', capturedSubjectType);

                    var confirmMessage =
                        'This will recalculate all marks for ' + subjectTypeName.toLowerCase() +
                        ' in this grade. This operation may take some time. Continue?';
                    if (confirm(confirmMessage)) {
                        $('#spinner-overlay').addClass('show');
                        $('#recalculateModal').modal('hide');

                        $.ajax({
                            url: recalculateForm.action,
                            method: 'POST',
                            data: $(recalculateForm).serialize(),
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            success: function(response) {
                                $('#spinner-overlay').removeClass('show');

                                if (response.success) {
                                    // Show progress modal and start polling
                                    $('#progressModal').modal('show');
                                    // Use the captured subject type value
                                    console.log(
                                        'Starting polling with subject type:',
                                        capturedSubjectType);
                                    startProgressPolling(capturedSubjectType);
                                }
                            },
                            error: function(xhr) {
                                $('#spinner-overlay').removeClass('show');

                                var errorMessage =
                                    'An error occurred. Please try again.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                var alertHtml =
                                    '<div class="row">' +
                                    '<div class="col-12">' +
                                    '<div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">' +
                                    '<i class="mdi mdi-block-helper label-icon"></i><strong>' +
                                    errorMessage + '</strong>' +
                                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                    '</div>' +
                                    '</div>' +
                                    '</div>';
                                $('.row').first().after(alertHtml);
                                $('html, body').animate({
                                    scrollTop: 0
                                }, 300);
                            }
                        });
                    }
                });
            }

            // Remove existing modal event handlers to avoid duplicates
            $('#recalculateModal').off('show.bs.modal').on('show.bs.modal', function() {
                console.log('Modal showing - resetting form');
                var cards = document.querySelectorAll('.option-card');
                cards.forEach(function(card) {
                    card.classList.remove('selected');
                });
                var btn = document.getElementById('proceedBtn');
                var typeInput = document.getElementById('selectedSubjectType');
                if (typeInput) typeInput.value = '';
                if (btn) {
                    btn.disabled = true;
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-secondary');
                }
            });

            $('#recalculateModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
                console.log('Modal hidden - resetting form');
                var cards = document.querySelectorAll('.option-card');
                cards.forEach(function(card) {
                    card.classList.remove('selected');
                });
                var btn = document.getElementById('proceedBtn');
                var typeInput = document.getElementById('selectedSubjectType');
                if (typeInput) typeInput.value = '';
                if (btn) {
                    btn.disabled = true;
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-secondary');
                }
            });

        }, 100);
    });

    // Use namespaced events to prevent handler accumulation on AJAX reload
    $('form').off('submit.markbookSpinner').on('submit.markbookSpinner', function() {
        $('#spinner-overlay').addClass('show');
        $(this).find('button[type="submit"]')
            .prop('disabled', true)
            .addClass('btn-loading');
    });

    $(window).off('load.markbookSpinner').on('load.markbookSpinner', function() {
        $('#spinner-overlay').removeClass('show');
    });

    // Dispose existing tooltips before creating new ones to prevent memory leaks
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
        var existingTooltip = bootstrap.Tooltip.getInstance(el);
        if (existingTooltip) {
            existingTooltip.dispose();
        }
    });
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Progress polling functionality - use var to allow re-declaration on AJAX reload
    if (typeof window._progressInterval !== 'undefined' && window._progressInterval) {
        clearInterval(window._progressInterval);
    }
    var progressInterval = window._progressInterval = null;
    var isMinimized = false;
    var pollAttempts = 0;
    var maxStartupAttempts = 30; // Allow up to 15 seconds for job to start (30 polls × 500ms)
    var progressStartTime = null; // Track when progress modal opens

    function startProgressPolling(subjectType) {
        const classId = '{{ $klass->klass->id ?? '' }}';

        if (!classId) {
            console.error('Class ID not found');
            return;
        }

        const progressUrl = '{{ route('assessment.recalculate-progress', ['id' => ':id']) }}'.replace(':id', classId);

        console.log('Starting progress polling:', {
            classId: classId,
            subjectType: subjectType,
            progressUrl: progressUrl
        });

        // Reset progress UI and poll counter
        resetProgressUI();
        pollAttempts = 0;
        progressStartTime = Date.now(); // Record start time

        // Initial poll
        pollProgress(progressUrl, subjectType);

        // Poll every 500ms (reduced from 2000ms to catch fast jobs)
        if (progressInterval) {
            clearInterval(progressInterval);
        }
        progressInterval = setInterval(function() {
            pollProgress(progressUrl, subjectType);
        }, 500);
    }

    function resetProgressUI() {
        $('#progressPercentage').text('0%');
        $('#progressMessage').text('Initializing...');
        $('#progressBar').css('width', '0%').attr('aria-valuenow', 0);
        $('#progressBarText').text('0%');
        $('#progressBar').removeClass('bg-success bg-danger').addClass('bg-primary');
        $('#progressBar').addClass('progress-bar-animated');
        $('#progressSpinner').show();
    }

    function pollProgress(url, subjectType) {
        $.ajax({
            url: url,
            method: 'GET',
            data: {
                subject_type: subjectType
            },
            success: function(progress) {
                console.log('Progress received:', progress);
                pollAttempts++; // Increment on successful poll
                updateProgressUI(progress);

                // Stop polling if completed or failed
                if (progress.status === 'completed' || progress.status === 'failed') {
                    clearInterval(progressInterval);
                    progressInterval = null;

                    // Call completion handler immediately (removed 2-second delay)
                    handleCompletion(progress);
                }
            },
            error: function(xhr) {
                pollAttempts++; // Increment even on error to track total attempts

                if (xhr.status === 404) {
                    // Job might still be starting up
                    if (pollAttempts <= maxStartupAttempts) {
                        console.log('Job starting... (attempt ' + pollAttempts + '/' + maxStartupAttempts +
                            ')');
                        // Update UI to show "starting" status
                        $('#progressMessage').text('Job starting, please wait...');
                    } else {
                        console.error('Job not found after ' + pollAttempts +
                            ' attempts (15 seconds). May have failed to start.');
                        // Stop polling after max attempts
                        clearInterval(progressInterval);
                        progressInterval = null;

                        // Show error
                        handleCompletion({
                            status: 'failed',
                            message: 'Job failed to start after 15 seconds. Please try again.'
                        });
                    }
                } else {
                    console.error('Failed to fetch progress:', xhr);
                    // Don't stop polling on other errors, might be temporary
                }
            }
        });
    }

    function updateProgressUI(progress) {
        const percentage = progress.percentage || 0;
        const message = progress.message || 'Processing...';

        // Update modal
        $('#progressPercentage').text(percentage + '%');
        $('#progressMessage').text(message);
        $('#progressBar').css('width', percentage + '%').attr('aria-valuenow', percentage);
        $('#progressBarText').text(percentage + '%');

        // Update minimized indicator
        $('#minimizedPercentage').text(percentage + '%');
        $('#minimizedMessage').text(message);
        $('#minimizedProgressBar').css('width', percentage + '%');

        // Change progress bar color based on status
        if (progress.status === 'completed') {
            $('#progressBar').removeClass('bg-primary bg-danger').addClass('bg-success');
            $('#progressBar').removeClass('progress-bar-animated');
            $('#minimizedProgressBar').removeClass('bg-primary bg-danger').addClass('bg-success');
            $('#progressSpinner').removeClass('bx-spin').addClass('bx-check-circle');
            $('#minimizedProgressIndicator .bx-loader-circle').removeClass('bx-spin bx-loader-circle').addClass(
                'bx-check-circle');
        } else if (progress.status === 'failed') {
            $('#progressBar').removeClass('bg-primary bg-success').addClass('bg-danger');
            $('#progressBar').removeClass('progress-bar-animated');
            $('#minimizedProgressBar').removeClass('bg-primary bg-success').addClass('bg-danger');
            $('#progressSpinner').removeClass('bx-spin').addClass('bx-error-circle');
            $('#minimizedProgressIndicator .bx-loader-circle').removeClass('bx-spin bx-loader-circle').addClass(
                'bx-error-circle');
        }
    }

    function handleCompletion(progress) {
        if (progress.status === 'completed') {
            // Calculate elapsed time since modal opened
            const elapsedTime = Date.now() - progressStartTime;
            const minDisplayTime = 2000; // Minimum 2 seconds display
            const remainingTime = Math.max(0, minDisplayTime - elapsedTime);

            console.log('Job completed in ' + elapsedTime + 'ms, keeping modal open for ' + remainingTime + 'ms more');

            // Keep modal open for minimum display time, then close and reload
            setTimeout(() => {
                // Hide modals and indicators
                $('#progressModal').modal('hide');
                $('#minimizedProgressIndicator').fadeOut();
                isMinimized = false;

                // Show success message
                if ($('.alert-success').length) {
                    $('.alert-success').remove();
                }

                const alertHtml = `
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                                    <i class="mdi mdi-check-all label-icon"></i><strong>${progress.message}</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        </div>
                    `;

                $('.row').first().after(alertHtml);
                $('html, body').animate({
                    scrollTop: 0
                }, 300);

                // Reload the page after a short delay
                setTimeout(() => {
                    console.log('Reloading page...');
                    window.location.reload();
                }, 500);
            }, remainingTime);

        } else if (progress.status === 'failed') {
            // Hide modals and indicators
            $('#progressModal').modal('hide');
            $('#minimizedProgressIndicator').fadeOut();
            isMinimized = false;

            // Show error message
            const alertHtml = `
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                                <i class="mdi mdi-block-helper label-icon"></i><strong>${progress.message}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                `;
            $('.row').first().after(alertHtml);
            $('html, body').animate({
                scrollTop: 0
            }, 300);
        }
    }

    // Minimize button handler (remove existing to avoid duplicates)
    $('#minimizeProgressBtn').off('click').on('click', function() {
        $('#progressModal').modal('hide');
        $('#minimizedProgressIndicator').fadeIn();
        isMinimized = true;
    });

    // Click minimized indicator to restore modal (remove existing to avoid duplicates)
    $('#minimizedProgressIndicator').off('click').on('click', function() {
        if (!isMinimized) return;
        $('#minimizedProgressIndicator').fadeOut();
        $('#progressModal').modal('show');
        isMinimized = false;
    });

    // Clean up on page unload
    $(window).on('beforeunload', function() {
        if (progressInterval) {
            // Don't prevent navigation, just inform
            return undefined;
        }
    });


</script>
