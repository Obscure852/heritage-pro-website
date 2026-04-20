@extends('layouts.master')

@section('title')
    Activity Roster
@endsection

@section('css')
    @include('activities.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('activities.index') }}">Activities</a>
        @endslot
        @slot('title')
            {{ $activity->name }} Roster
        @endslot
    @endcomponent

    @include('activities.partials.alerts')

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Roster Management</h1>
                <p class="page-subtitle">Manage enrolled students, apply capacity-safe bulk adds, and preserve roster history for this activity.</p>
            </div>
            <div class="activities-actions">
                <a href="{{ route('activities.roster.export', $activity) }}" class="btn btn-light border">
                    <i class="fas fa-download"></i> Export Roster
                </a>
            </div>
        </div>

        @include('activities.partials.subnav', ['activity' => $activity, 'current' => 'roster'])

        <div class="info-note">
            <div class="help-title">Roster Guidance</div>
            <div class="help-content">
                Use this page to add eligible students, manage roster status changes, and export the current list. Only active-term students can be enrolled, and duplicate or over-capacity adds are blocked automatically.
            </div>
        </div>

        <div class="roster-summary-grid">
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Active Roster</div>
                    <div class="roster-summary-value">{{ $activity->active_enrollments_count }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">History</div>
                    <div class="roster-summary-value">{{ $activity->historical_enrollments_count }}</div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Capacity</div>
                    <div class="roster-summary-value">
                        @if ($activity->capacity)
                            {{ $remainingCapacity }} / {{ $activity->capacity }}
                        @else
                            Unlimited
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-shell">
                <div class="card-body p-3">
                    <div class="detail-label mb-1">Eligible Bulk Candidates</div>
                    <div class="roster-summary-value">{{ $bulkPreview['count'] }}</div>
                </div>
            </div>
        </div>

        <div class="management-grid">
            <div class="section-stack">
                <div class="card-shell">
                    <div class="card-body p-4">
                        <div class="management-header">
                            <div>
                                <h5 class="summary-card-title mb-0">Active Roster</h5>
                                <p class="management-subtitle">Students currently attached to this activity for Term {{ $activity->term?->term }} - {{ $activity->year }}.</p>
                            </div>
                        </div>

                        @if ($activeEnrollments->isNotEmpty())
                            <div class="management-list">
                                @foreach ($activeEnrollments as $enrollment)
                                    <div class="management-item">
                                        <div class="management-item-header">
                                            <div>
                                                <div class="management-item-title">{{ $enrollment->student?->full_name ?: 'Unknown student' }}</div>
                                                <div class="management-item-meta">
                                                    <span class="summary-chip enrollment-status-chip enrollment-status-active">
                                                        <i class="fas fa-circle"></i> Active
                                                    </span>
                                                    @if ($enrollment->gradeSnapshot?->name)
                                                        <span class="summary-chip pill-muted">{{ $enrollment->gradeSnapshot->name }}</span>
                                                    @endif
                                                    @if ($enrollment->klassSnapshot?->name)
                                                        <span class="summary-chip pill-muted">{{ $enrollment->klassSnapshot->name }}</span>
                                                    @endif
                                                    @if ($enrollment->houseSnapshot?->name)
                                                        <span class="summary-chip pill-muted">{{ $enrollment->houseSnapshot->name }}</span>
                                                    @endif
                                                    <span class="summary-chip pill-muted">
                                                        <i class="fas fa-calendar-alt"></i>
                                                        Joined {{ optional($enrollment->joined_at)->format('d M Y') ?: 'n/a' }}
                                                    </span>
                                                    <span class="summary-chip pill-muted">
                                                        {{ $sources[$enrollment->source] ?? ucfirst($enrollment->source) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        @can('manageRoster', $activity)
                                            <form action="{{ route('activities.roster.update', [$activity, $enrollment]) }}"
                                                method="POST"
                                                class="needs-validation roster-status-form"
                                                novalidate
                                                data-activity-form>
                                                @csrf
                                                @method('PATCH')

                                                <div class="roster-action-grid">
                                                    <div class="form-group">
                                                        <label class="form-label" for="status-{{ $enrollment->id }}">Status Action</label>
                                                        <select class="form-select" id="status-{{ $enrollment->id }}" name="status" required>
                                                            <option value="">Select action</option>
                                                            @foreach ($closableStatuses as $statusKey => $statusLabel)
                                                                <option value="{{ $statusKey }}">{{ $statusLabel }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="form-label" for="left-at-{{ $enrollment->id }}">Effective Date</label>
                                                        <input type="date"
                                                            class="form-control"
                                                            id="left-at-{{ $enrollment->id }}"
                                                            name="left_at"
                                                            placeholder="YYYY-MM-DD">
                                                    </div>
                                                    <div class="form-group roster-reason-field">
                                                        <label class="form-label" for="exit-reason-{{ $enrollment->id }}">Exit Reason</label>
                                                        <input type="text"
                                                            class="form-control"
                                                            id="exit-reason-{{ $enrollment->id }}"
                                                            name="exit_reason"
                                                            placeholder="Record the operational reason for this roster change."
                                                            required>
                                                    </div>
                                                    <div class="roster-action-submit">
                                                        <button type="submit" class="btn btn-primary btn-loading">
                                                            <span class="btn-text"><i class="fas fa-save"></i> Save Status</span>
                                                            <span class="btn-spinner d-none">
                                                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                                Saving...
                                                            </span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        @else
                                            <div class="management-item-notes">
                                                Added by {{ $enrollment->joinedBy?->full_name ?: 'Unknown operator' }} on {{ optional($enrollment->joined_at)->format('d M Y') ?: 'n/a' }}.
                                            </div>
                                        @endcan
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="summary-empty mb-0">No students are actively enrolled yet.</p>
                        @endif
                    </div>
                </div>

                <div class="card-shell">
                    <div class="card-body p-4">
                        <div class="management-header">
                            <div>
                                <h5 class="summary-card-title mb-0">Roster History</h5>
                                <p class="management-subtitle">Withdrawn, completed, and suspended students remain visible for audit and reporting.</p>
                            </div>
                        </div>

                        @if ($historicalEnrollments->isNotEmpty())
                            <div class="management-list">
                                @foreach ($historicalEnrollments as $enrollment)
                                    <div class="management-item">
                                        <div class="management-item-title">{{ $enrollment->student?->full_name ?: 'Unknown student' }}</div>
                                        <div class="management-item-meta">
                                            <span class="summary-chip enrollment-status-chip enrollment-status-{{ $enrollment->status }}">
                                                {{ $enrollmentStatuses[$enrollment->status] ?? ucfirst($enrollment->status) }}
                                            </span>
                                            @if ($enrollment->gradeSnapshot?->name)
                                                <span class="summary-chip pill-muted">{{ $enrollment->gradeSnapshot->name }}</span>
                                            @endif
                                            @if ($enrollment->klassSnapshot?->name)
                                                <span class="summary-chip pill-muted">{{ $enrollment->klassSnapshot->name }}</span>
                                            @endif
                                            @if ($enrollment->houseSnapshot?->name)
                                                <span class="summary-chip pill-muted">{{ $enrollment->houseSnapshot->name }}</span>
                                            @endif
                                            <span class="summary-chip pill-muted">
                                                Left {{ optional($enrollment->left_at)->format('d M Y') ?: 'n/a' }}
                                            </span>
                                        </div>
                                        <div class="management-item-notes">
                                            Joined by {{ $enrollment->joinedBy?->full_name ?: 'Unknown operator' }} on {{ optional($enrollment->joined_at)->format('d M Y') ?: 'n/a' }}.
                                            @if ($enrollment->leftBy)
                                                Status changed by {{ $enrollment->leftBy->full_name }}.
                                            @endif
                                            @if ($enrollment->exit_reason)
                                                Reason: {{ $enrollment->exit_reason }}
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="summary-empty mb-0">No historical roster changes have been recorded yet.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="section-stack">
                @can('manageRoster', $activity)
                    <div class="card-shell">
                        <div class="card-body p-4">
                            <h5 class="summary-card-title">Add Student Manually</h5>
                            <p class="management-subtitle">Use this for one-off additions when staff need to pick a specific student directly.</p>

                            <form action="{{ route('activities.roster.store', $activity) }}"
                                method="POST"
                                id="activity-roster-store-form"
                                class="needs-validation"
                                novalidate
                                data-activity-form>
                                @csrf

                                <div class="form-group mb-3">
                                    <label class="form-label" for="student_id">Student <span class="text-danger">*</span></label>
                                    <select class="form-select @error('student_id') is-invalid @enderror"
                                        id="student_id"
                                        name="student_id"
                                        data-trigger
                                        required>
                                        <option value="">Select student</option>
                                        @foreach ($manualCandidates as $student)
                                            <option value="{{ $student->id }}" {{ (string) old('student_id') === (string) $student->id ? 'selected' : '' }}>
                                                {{ trim($student->first_name . ' ' . $student->last_name) }}
                                                @if ($student->grade_name || $student->klass_name)
                                                    | {{ collect([$student->grade_name, $student->klass_name])->filter()->implode(' | ') }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('student_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="joined_at">Join Date</label>
                                    <input type="date"
                                        class="form-control @error('joined_at') is-invalid @enderror"
                                        id="joined_at"
                                        name="joined_at"
                                        value="{{ old('joined_at') }}"
                                        placeholder="YYYY-MM-DD">
                                    <div class="field-help">Leave blank to use the current date and time.</div>
                                    @error('joined_at')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-actions">
                                    <a href="{{ route('activities.show', $activity) }}" class="btn btn-secondary">
                                        <i class="bx bx-x"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-loading">
                                        <span class="btn-text"><i class="fas fa-save"></i> Save Enrollment</span>
                                        <span class="btn-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                            Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card-shell">
                        <div class="card-body p-4">
                            <h5 class="summary-card-title">Bulk Enroll Eligible Students</h5>
                            <p class="management-subtitle">Select the eligible students you want to allocate. Students with active roster entries are already excluded.</p>

                            @if ($bulkPreview['count'] > 0)
                                <div class="summary-chip-group mb-3">
                                    <span class="summary-chip pill-primary">
                                        <i class="fas fa-users"></i> {{ $bulkPreview['count'] }} student(s) ready
                                    </span>
                                    @if (!is_null($remainingCapacity))
                                        <span class="summary-chip pill-muted">{{ $remainingCapacity }} slot(s) remaining</span>
                                    @endif
                                </div>

                                <form action="{{ route('activities.roster.bulk-store', $activity) }}"
                                    method="POST"
                                    id="activity-roster-bulk-form"
                                    class="needs-validation"
                                    novalidate
                                    data-activity-form>
                                    @csrf

                                    <div class="bulk-search-shell">
                                        <span class="search-icon"><i class="fas fa-search"></i></span>
                                        <input type="text"
                                            class="bulk-search-input"
                                            id="bulk-student-search"
                                            placeholder="Search by student first name or last name">
                                    </div>

                                    <div class="bulk-select-toolbar">
                                        <label class="bulk-select-toggle">
                                            <input type="checkbox" id="bulk-select-all">
                                            <span>Select all eligible students</span>
                                        </label>
                                        <div class="bulk-select-count">
                                            <span id="bulk-selected-count">{{ count(old('student_ids', [])) }}</span> selected
                                        </div>
                                    </div>

                                    <div class="candidate-preview-list candidate-select-list mb-3">
                                        @foreach ($eligibleBulkCandidates as $student)
                                            @php
                                                $studentName = trim($student->first_name . ' ' . $student->last_name);
                                                $isChecked = in_array((string) $student->id, array_map('strval', old('student_ids', [])), true);
                                                $studentPrimaryMeta = collect([$student->grade_name, $student->klass_name])->filter();
                                                $studentSecondaryMeta = collect([$student->house_name, $student->student_filter_name])->filter()->implode(' | ');
                                            @endphp
                                            <label class="candidate-preview-item candidate-checkbox-item"
                                                for="bulk-student-{{ $student->id }}"
                                                data-student-name="{{ strtolower($studentName) }}">
                                                <span class="candidate-checkbox-shell">
                                                    <input type="checkbox"
                                                        class="bulk-student-checkbox"
                                                        id="bulk-student-{{ $student->id }}"
                                                        name="student_ids[]"
                                                        value="{{ $student->id }}"
                                                        {{ $isChecked ? 'checked' : '' }}>
                                                </span>
                                                <span class="candidate-preview-content">
                                                    <span class="candidate-preview-title">{{ $studentName }}</span>
                                                    @if ($studentPrimaryMeta->isNotEmpty())
                                                        <span class="candidate-preview-meta candidate-preview-pill-row">
                                                            @foreach ($studentPrimaryMeta as $metaItem)
                                                                <span class="summary-chip pill-muted">{{ $metaItem }}</span>
                                                            @endforeach
                                                        </span>
                                                    @endif
                                                    <span class="candidate-preview-submeta">
                                                        {{ $studentSecondaryMeta ?: 'No house or student filter snapshot available' }}
                                                    </span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="bulk-search-empty" id="bulk-search-empty">No eligible students match that search.</div>

                                    @error('student_ids')
                                        <div class="invalid-feedback d-block mb-3">{{ $message }}</div>
                                    @enderror
                                    @error('student_ids.*')
                                        <div class="invalid-feedback d-block mb-3">{{ $message }}</div>
                                    @enderror

                                    <div class="form-group">
                                        <label class="form-label" for="bulk_joined_at">Join Date</label>
                                        <input type="date"
                                            class="form-control @error('joined_at') is-invalid @enderror"
                                            id="bulk_joined_at"
                                            name="joined_at"
                                            value="{{ old('joined_at') }}"
                                            placeholder="YYYY-MM-DD">
                                        <div class="field-help">Leave blank to stamp the bulk action time.</div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary btn-loading">
                                            <span class="btn-text"><i class="fas fa-save"></i> Bulk Enroll Eligible Students</span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                Saving...
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            @else
                                <p class="summary-empty mb-0">
                                    @if ($activity->eligibilityTargets()->exists())
                                        No eligible students are currently available for bulk enrollment.
                                    @else
                                        Define eligibility targets first, then return here to bulk enroll matching students.
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card-shell">
                        <div class="card-body p-4">
                            <h5 class="summary-card-title">Roster Access</h5>
                            <p class="summary-empty mb-0">You can review the current roster and history, but roster changes are restricted to Activities Admin and Activities Edit roles.</p>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('activities.partials.form-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bulkForm = document.getElementById('activity-roster-bulk-form');

            if (!bulkForm) {
                return;
            }

            const selectAll = document.getElementById('bulk-select-all');
            const searchInput = document.getElementById('bulk-student-search');
            const emptyState = document.getElementById('bulk-search-empty');
            const checkboxes = Array.from(bulkForm.querySelectorAll('.bulk-student-checkbox'));
            const selectedCount = document.getElementById('bulk-selected-count');
            const candidateItems = Array.from(bulkForm.querySelectorAll('.candidate-checkbox-item'));

            const syncBulkSelectionState = function() {
                const checkedCount = checkboxes.filter(function(checkbox) {
                    return checkbox.checked;
                }).length;

                if (selectedCount) {
                    selectedCount.textContent = String(checkedCount);
                }

                if (selectAll) {
                    selectAll.checked = checkedCount > 0 && checkedCount === checkboxes.length;
                    selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
                }
            };

            const filterBulkCandidates = function() {
                if (!searchInput) {
                    return;
                }

                const query = searchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                candidateItems.forEach(function(item) {
                    const studentName = item.dataset.studentName || '';
                    const matches = query === '' || studentName.includes(query);

                    item.style.display = matches ? '' : 'none';

                    if (matches) {
                        visibleCount += 1;
                    }
                });

                if (emptyState) {
                    emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
                }
            };

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(function(checkbox) {
                        checkbox.checked = selectAll.checked;
                    });

                    syncBulkSelectionState();
                });
            }

            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', syncBulkSelectionState);
            });

            if (searchInput) {
                searchInput.addEventListener('input', filterBulkCandidates);
            }

            filterBulkCandidates();
            syncBulkSelectionState();
        });
    </script>
@endsection
