<style>
    .subjects-table {
        width: 100%;
        border-collapse: collapse;
    }

    .subjects-table thead th {
        background: #f9fafb;
        padding: 12px 16px;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
    }

    .subjects-table tbody td {
        padding: 12px 16px;
        color: #4b5563;
        font-size: 14px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .subjects-table tbody tr:hover {
        background: #f9fafb;
    }

    .subjects-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Action Button Styles */
    .table-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 3px;
        border: none;
        transition: all 0.2s ease;
        text-decoration: none;
        cursor: pointer;
    }

    .table-action-btn.edit {
        background: #fef3c7;
        color: #d97706;
    }

    .table-action-btn.edit:hover {
        background: #d97706;
        color: white;
    }

    .table-action-btn.grading {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .table-action-btn.grading:hover {
        background: #1d4ed8;
        color: white;
    }

    .table-action-btn.component {
        background: #d1fae5;
        color: #059669;
    }

    .table-action-btn.component:hover {
        background: #059669;
        color: white;
    }

    .table-action-btn.syllabus {
        background: #e0f2fe;
        color: #0369a1;
    }

    .table-action-btn.syllabus:hover {
        background: #0369a1;
        color: white;
    }

    .table-action-btn.delete {
        background: #fee2e2;
        color: #dc2626;
    }

    .table-action-btn.delete:hover {
        background: #dc2626;
        color: white;
    }

    /* Badge Styles */
    .subject-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 500;
    }

    .subject-badge.tests {
        background: #dbeafe;
        color: #1e40af;
    }

    .subject-badge.criteria {
        background: #e0e7ff;
        color: #3730a3;
    }

    .subject-badge.active {
        background: #d1fae5;
        color: #065f46;
    }

    .subject-badge.inactive {
        background: #f3f4f6;
        color: #6b7280;
    }

    /* Component Indicators */
    .component-indicator {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 12px;
    }

    .component-indicator .arrow-down {
        color: #10b981;
    }

    .component-indicator .arrow-up {
        color: #3b82f6;
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

    .empty-state h5 {
        color: #374151;
        margin-bottom: 8px;
    }

    .empty-state p {
        margin: 0;
        font-size: 14px;
    }

    .badge-double {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        margin-left: 6px;
        letter-spacing: 0.3px;
        box-shadow: 0 1px 3px rgba(37, 99, 235, 0.3);
    }

    .subject-syllabus-modal .modal-content {
        border: none;
        border-radius: 3px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
    }

    .subject-syllabus-modal .modal-header {
        background: #ffffff;
        color: #0f172a;
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
    }

    .subject-syllabus-modal .modal-title {
        font-weight: 600;
    }

    .subject-syllabus-modal .btn-close {
        filter: none;
        opacity: 0.7;
    }

    .subject-syllabus-meta {
        margin: 4px 0 0;
        font-size: 13px;
        color: #64748b;
    }

    .subject-syllabus-source-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        color: #0f172a;
        background: #ffffff;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .subject-syllabus-source-link:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    .subject-syllabus-modal .modal-body {
        background: linear-gradient(180deg, #f8fafc 0%, #eff6ff 100%);
        padding: 24px;
    }

    .subject-syllabus-modal .modal-footer {
        border-top: 1px solid #e2e8f0;
        padding: 14px 24px 20px;
    }

    .syllabus-loading-state,
    .syllabus-error-state,
    .syllabus-empty-state {
        background: white;
        border: 1px solid #dbeafe;
        border-radius: 3px;
        padding: 28px;
        text-align: center;
        color: #475569;
    }

    .syllabus-loading-state i,
    .syllabus-error-state i,
    .syllabus-empty-state i {
        font-size: 30px;
        margin-bottom: 12px;
        display: inline-block;
    }

    .syllabus-loading-state i {
        color: #2563eb;
        animation: spin 1s linear infinite;
    }

    .syllabus-error-state i {
        color: #dc2626;
    }

    .syllabus-empty-state i {
        color: #64748b;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .syllabus-overview {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        background: white;
        border: 1px solid #dbeafe;
        border-radius: 3px;
        padding: 18px 20px;
        margin-bottom: 18px;
    }

    .syllabus-overview-title {
        color: #0f172a;
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .syllabus-overview-subtitle {
        color: #475569;
        font-size: 13px;
        margin: 0;
    }

    .syllabus-overview-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .syllabus-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 600;
    }

    .syllabus-section-stack {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .syllabus-section-card,
    .syllabus-unit-card,
    .syllabus-topic-card {
        background: white;
        border: 1px solid #dbeafe;
        border-radius: 3px;
    }

    .syllabus-section-card {
        padding: 18px;
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.06);
    }

    .syllabus-section-header,
    .syllabus-unit-header,
    .syllabus-topic-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .syllabus-section-header {
        margin-bottom: 14px;
    }

    .syllabus-section-form {
        color: #0f172a;
        font-size: 17px;
        font-weight: 700;
        margin: 0;
    }

    .syllabus-unit-stack,
    .syllabus-topic-stack,
    .syllabus-subtopic-stack {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .syllabus-unit-card {
        padding: 16px;
        border-color: #cbd5e1;
    }

    .syllabus-unit-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #0f172a;
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }

    .syllabus-unit-code,
    .syllabus-chip,
    .syllabus-topic-stat {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .syllabus-unit-code {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .syllabus-chip {
        background: #f1f5f9;
        color: #475569;
    }

    .syllabus-topic-card {
        padding: 14px 16px;
        border-color: #e2e8f0;
    }

    .syllabus-topic-card.is-nested {
        margin-left: 18px;
        border-left: 4px solid #bfdbfe;
    }

    .syllabus-topic-title {
        color: #0f172a;
        font-size: 15px;
        font-weight: 600;
        margin: 0;
    }

    .syllabus-topic-path,
    .syllabus-topic-description {
        color: #64748b;
        font-size: 13px;
        line-height: 1.55;
    }

    .syllabus-topic-path {
        margin-top: 4px;
    }

    .syllabus-topic-description {
        margin: 12px 0 0;
    }

    .syllabus-topic-stats {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 8px;
    }

    .syllabus-topic-stat {
        background: #ecfeff;
        color: #0f766e;
    }

    .syllabus-objective-groups {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 14px;
    }

    .syllabus-objective-group {
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 3px;
    }

    .syllabus-objective-group-label {
        color: #0f172a;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .syllabus-objective-list {
        margin: 0;
        padding-left: 18px;
        color: #334155;
        font-size: 13px;
        line-height: 1.65;
    }

    .syllabus-objective-list li + li {
        margin-top: 6px;
    }

    .syllabus-subtopic-stack {
        margin-top: 12px;
    }

    @media (max-width: 768px) {
        .subject-syllabus-modal .modal-header,
        .subject-syllabus-modal .modal-body,
        .subject-syllabus-modal .modal-footer {
            padding-left: 16px;
            padding-right: 16px;
        }

        .syllabus-overview,
        .syllabus-section-header,
        .syllabus-unit-header,
        .syllabus-topic-heading {
            flex-direction: column;
        }

        .syllabus-topic-card.is-nested {
            margin-left: 10px;
        }
    }
</style>

@if (!empty($subjects) && count($subjects) > 0)
    <div class="table-responsive">
        <table class="subjects-table">
            <thead>
                <tr>
                    <th style="width: 60px;">Seq</th>
                    <th>Subject Name</th>
                    <th style="width: 90px;">Mandatory</th>
                    <th style="width: 80px;">Optional</th>
                    <th>Department</th>
                    <th style="width: 80px;">Status</th>
                    <th style="width: 100px;">Grade</th>
                    <th style="width: 150px;">Year</th>
                    <th style="width: 200px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subjects as $index => $subject)
                    @if ($subject->subject)
                        <tr>
                            <td><strong>{{ $subject->sequence ?? '-' }}</strong></td>
                            <td>
                                <strong>{{ $subject->subject->name }}</strong>
                                @if ($subject->subject->is_double)
                                    <span class="badge-double"><i class="bx bxs-star"></i><i class="bx bxs-star"></i> Double</span>
                                @endif
                            </td>
                            <td>{{ $subject->mandatory == true ? 'Yes' : 'No' }}</td>
                            <td>{{ $subject->type == true ? 'No' : 'Yes' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span>{{ $subject->department->name ?? '-' }}</span>
                                    @if ($subject->tests->count() > 0)
                                        <span class="subject-badge tests">
                                            {{ $subject->tests->count() }}
                                            test{{ $subject->tests->count() > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                    @if ($subject->criteriaBasedTests->count() > 0)
                                        <span class="subject-badge criteria">
                                            {{ $subject->criteriaBasedTests->count() }} criteria
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if ($subject->active)
                                    <span class="subject-badge active">Active</span>
                                @else
                                    <span class="subject-badge inactive">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $subject->grade->name }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <span>{{ $subject->year }}</span>
                                    @if ($subject->subject->components == true && $subject->components->count() > 0)
                                        <span class="component-indicator">
                                            <i class="bx bxs-down-arrow arrow-down"></i>
                                            @if ($subject->gradeOptionSets->count() > 0)
                                                <i class="bx bxs-up-arrow arrow-up"></i>
                                            @endif
                                            <span>({{ $subject->components->count() ?? 0 }})</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    @php
                                        $grading = $subject->gradingScales ?? collect();
                                        $level = $subject->subject->level ?? '';
                                    @endphp

                                    @if (!in_array($level, [\App\Models\SchoolSetup::LEVEL_PRIMARY, \App\Models\SchoolSetup::LEVEL_PRE_PRIMARY, 'Preschool']))
                                        <button type="button"
                                            class="table-action-btn syllabus open-subject-syllabus-btn"
                                            data-syllabus-url="{{ route('subjects.syllabus-preview', $subject->id) }}"
                                            data-subject-name="{{ $subject->subject->name }}"
                                            data-grade-name="{{ $subject->grade->name }}"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="View Syllabus">
                                            <i class="bx bx-book-reader"></i>
                                        </button>
                                    @endif

                                    @can('view-system-admin')
                                        <a href="{{ route('subject.edit-subject', $subject->id) }}"
                                            class="table-action-btn edit" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="Edit Subject">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                    @endcan

                                    @if (in_array($level, [\App\Models\SchoolSetup::LEVEL_PRIMARY, \App\Models\SchoolSetup::LEVEL_PRE_PRIMARY, 'Preschool']) && $grading->isEmpty())
                                        @if ($subject->subject->components)
                                            <a href="{{ route('subject.link-grade-option', ['subjectId' => $subject->id]) }}"
                                                class="table-action-btn grading" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Link to Option">
                                                <i class="bx bx-slider-alt"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('subjects.grading-scale', $subject->id) }}"
                                                class="table-action-btn grading" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="Add Grading Scale">
                                                <i class="bx bx-slider-alt"></i>
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('subjects.edit-grading-scale', $subject->id) }}"
                                            class="table-action-btn grading" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Edit Grading Scale">
                                            <i class="bx bx-slider-alt"></i>
                                        </a>
                                    @endif

                                    @if ($subject->subject->components)
                                        <a href="{{ route('subject.view-component', $subject->id) }}"
                                            class="table-action-btn component" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="View Components">
                                            <i class="bx bx-layer"></i>
                                        </a>
                                    @endif

                                    @can('view-system-admin')
                                        <button type="button" class="table-action-btn delete" data-bs-toggle="tooltip"
                                            data-bs-placement="top"
                                            onclick="confirmDelete(
                                                {{ $subject->id }},
                                                '{{ $subject->subject->name }} ({{ $subject->grade->name }})',
                                                {{ $subject->tests->count() }},
                                                {{ $subject->criteriaBasedTests->count() }},
                                                {{ $subject->components->count() }},
                                                '{{ $subject->type == true ? 'No' : 'Yes' }}'
                                            )"
                                            title="Delete Subject">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="empty-state">
        <i class="bx bx-book-open"></i>
        <h5>No Subjects Found</h5>
        <p>{{ $errorMessage ?? 'There are no subjects assigned to this grade level yet. Click "New Subject" to add one.' }}</p>
    </div>
@endif

<div class="modal fade subject-syllabus-modal" id="subjectSyllabusModal" tabindex="-1"
    aria-labelledby="subjectSyllabusModalLabel" aria-hidden="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="subjectSyllabusModalLabel">Subject Syllabus</h5>
                    <p class="subject-syllabus-meta" id="subjectSyllabusModalMeta">Select a subject to review its syllabus.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="subjectSyllabusModalSourceLink" class="subject-syllabus-source-link" target="_blank"
                        rel="noopener" hidden>
                        <i class="bx bx-link-external"></i>
                        <span>Open Source</span>
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body" id="subjectSyllabusModalBody">
                <div class="syllabus-empty-state">
                    <i class="bx bx-book-reader"></i>
                    <div>Choose a subject row to preview its syllabus.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.initializeSubjectGradeSyllabusViewer = function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
            bootstrap.Tooltip.getOrCreateInstance(tooltipTriggerEl);
        });

        var modalElement = document.getElementById('subjectSyllabusModal');
        if (!modalElement) {
            return;
        }

        var modal = bootstrap.Modal.getOrCreateInstance(modalElement, {
            backdrop: 'static',
            keyboard: false,
        });

        var modalTitle = document.getElementById('subjectSyllabusModalLabel');
        var modalMeta = document.getElementById('subjectSyllabusModalMeta');
        var modalBody = document.getElementById('subjectSyllabusModalBody');
        var sourceLink = document.getElementById('subjectSyllabusModalSourceLink');
        var viewerState = window.subjectSyllabusViewerState || {
            requestId: 0,
        };

        window.subjectSyllabusViewerState = viewerState;

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, function(character) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                })[character];
            });
        }

        function countTopics(topics) {
            return (topics || []).reduce(function(total, topic) {
                return total + 1 + countTopics(topic.subtopics || []);
            }, 0);
        }

        function countObjectives(groups) {
            return (groups || []).reduce(function(total, group) {
                return total + ((group.objectives || []).length);
            }, 0);
        }

        function setSourceLink(url) {
            if (url) {
                sourceLink.href = url;
                sourceLink.hidden = false;
                return;
            }

            sourceLink.hidden = true;
            sourceLink.removeAttribute('href');
        }

        function renderLoadingState(subjectName, gradeName) {
            var gradeLabel = gradeName ? ' for ' + escapeHtml(gradeName) : '';

            return '' +
                '<div class="syllabus-loading-state">' +
                    '<i class="bx bx-loader-alt"></i>' +
                    '<h5 class="mb-2">Loading syllabus</h5>' +
                    '<p class="mb-0">Fetching the latest readable syllabus for <strong>' + escapeHtml(subjectName) +
                    '</strong>' + gradeLabel + '.</p>' +
                '</div>';
        }

        function renderErrorState(message) {
            return '' +
                '<div class="syllabus-error-state">' +
                    '<i class="bx bx-error-circle"></i>' +
                    '<h5 class="mb-2">Syllabus unavailable</h5>' +
                    '<p class="mb-0">' + escapeHtml(message) + '</p>' +
                '</div>';
        }

        function renderObjectiveGroup(group) {
            var objectives = Array.isArray(group.objectives) ? group.objectives : [];

            if (!objectives.length) {
                return '';
            }

            return '' +
                '<div class="syllabus-objective-group">' +
                    '<div class="syllabus-objective-group-label">' + escapeHtml(group.label || 'Objectives') + '</div>' +
                    '<ol class="syllabus-objective-list">' +
                        objectives.map(function(objective) {
                            return '<li>' + escapeHtml(objective.text || '') + '</li>';
                        }).join('') +
                    '</ol>' +
                '</div>';
        }

        function renderTopic(topic, depth) {
            var objectiveGroups = Array.isArray(topic.objective_groups) ? topic.objective_groups : [];
            var subtopics = Array.isArray(topic.subtopics) ? topic.subtopics : [];
            var path = Array.isArray(topic.path) ? topic.path : [];
            var stats = [];

            if (countObjectives(objectiveGroups) > 0) {
                stats.push('<span class="syllabus-topic-stat">' + countObjectives(objectiveGroups) + ' objectives</span>');
            }

            if (subtopics.length > 0) {
                stats.push('<span class="syllabus-topic-stat">' + countTopics(subtopics) + ' subtopics</span>');
            }

            return '' +
                '<article class="syllabus-topic-card' + (depth > 0 ? ' is-nested' : '') + '">' +
                    '<div class="syllabus-topic-heading">' +
                        '<div>' +
                            '<h6 class="syllabus-topic-title">' + escapeHtml(topic.title || 'Topic') + '</h6>' +
                            (path.length > 1
                                ? '<div class="syllabus-topic-path">' + escapeHtml(path.join(' > ')) + '</div>'
                                : '') +
                        '</div>' +
                        (stats.length > 0
                            ? '<div class="syllabus-topic-stats">' + stats.join('') + '</div>'
                            : '') +
                    '</div>' +
                    (topic.description
                        ? '<p class="syllabus-topic-description">' + escapeHtml(topic.description) + '</p>'
                        : '') +
                    (objectiveGroups.length > 0
                        ? '<div class="syllabus-objective-groups">' +
                            objectiveGroups.map(renderObjectiveGroup).join('') +
                          '</div>'
                        : '') +
                    (subtopics.length > 0
                        ? '<div class="syllabus-subtopic-stack">' +
                            subtopics.map(function(subtopic) {
                                return renderTopic(subtopic, depth + 1);
                            }).join('') +
                          '</div>'
                        : '') +
                '</article>';
        }

        function renderUnit(unit) {
            var topics = Array.isArray(unit.topics) ? unit.topics : [];

            return '' +
                '<section class="syllabus-unit-card">' +
                    '<div class="syllabus-unit-header">' +
                        '<h6 class="syllabus-unit-title">' +
                            (unit.id
                                ? '<span class="syllabus-unit-code">' + escapeHtml(unit.id) + '</span>'
                                : '') +
                            '<span>' + escapeHtml(unit.title || 'Unit') + '</span>' +
                        '</h6>' +
                        '<span class="syllabus-chip">' + countTopics(topics) + ' topics</span>' +
                    '</div>' +
                    '<div class="syllabus-topic-stack mt-3">' +
                        topics.map(function(topic) {
                            return renderTopic(topic, 0);
                        }).join('') +
                    '</div>' +
                '</section>';
        }

        function renderSection(section) {
            var units = Array.isArray(section.units) ? section.units : [];

            return '' +
                '<section class="syllabus-section-card">' +
                    '<div class="syllabus-section-header">' +
                        '<h5 class="syllabus-section-form">' + escapeHtml(section.form || 'Section') + '</h5>' +
                        '<span class="syllabus-chip">' + units.length + ' units</span>' +
                    '</div>' +
                    '<div class="syllabus-unit-stack">' +
                        units.map(renderUnit).join('') +
                    '</div>' +
                '</section>';
        }

        function renderStructure(response) {
            var structure = response.structure || {};
            var sections = Array.isArray(structure.sections) ? structure.sections : [];
            var unitCount = sections.reduce(function(total, section) {
                return total + ((section.units || []).length);
            }, 0);
            var topicCount = sections.reduce(function(total, section) {
                return total + (section.units || []).reduce(function(unitTotal, unit) {
                    return unitTotal + countTopics(unit.topics || []);
                }, 0);
            }, 0);

            if (!sections.length) {
                return '' +
                    '<div class="syllabus-empty-state">' +
                        '<i class="bx bx-book-open"></i>' +
                        '<h5 class="mb-2">Nothing to display</h5>' +
                        '<p class="mb-0">This syllabus does not contain any readable sections yet.</p>' +
                    '</div>';
            }

            return '' +
                '<div class="syllabus-overview">' +
                    '<div>' +
                        '<div class="syllabus-overview-title">' + escapeHtml(response.title || 'Syllabus') + '</div>' +
                        '<p class="syllabus-overview-subtitle mb-0">' +
                            escapeHtml([response.subject_name, response.grade_name].filter(Boolean).join(' • ')) +
                        '</p>' +
                    '</div>' +
                    '<div class="syllabus-overview-pills">' +
                        '<span class="syllabus-pill"><i class="bx bx-grid-alt"></i>' + sections.length + ' sections</span>' +
                        '<span class="syllabus-pill"><i class="bx bx-collection"></i>' + unitCount + ' units</span>' +
                        '<span class="syllabus-pill"><i class="bx bx-list-ul"></i>' + topicCount + ' topics</span>' +
                    '</div>' +
                '</div>' +
                '<div class="syllabus-section-stack">' +
                    sections.map(renderSection).join('') +
                '</div>';
        }

        $(document)
            .off('click.subjectSyllabusPreview', '.open-subject-syllabus-btn')
            .on('click.subjectSyllabusPreview', '.open-subject-syllabus-btn', function() {
                var subjectName = this.getAttribute('data-subject-name') || 'Subject';
                var gradeName = this.getAttribute('data-grade-name') || '';
                var requestUrl = this.getAttribute('data-syllabus-url');
                var requestId = ++viewerState.requestId;

                modalTitle.textContent = subjectName + ' Syllabus';
                modalMeta.textContent = gradeName ? gradeName + ' syllabus preview' : 'Syllabus preview';
                modalBody.innerHTML = renderLoadingState(subjectName, gradeName);
                setSourceLink(null);
                modal.show();

                $.ajax({
                    url: requestUrl,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (requestId !== viewerState.requestId) {
                            return;
                        }

                        modalTitle.textContent = response.title || (subjectName + ' Syllabus');
                        modalMeta.textContent = [response.subject_name, response.grade_name].filter(Boolean).join(' • ') || 'Syllabus preview';
                        setSourceLink(response.source_url || null);
                        modalBody.innerHTML = renderStructure(response);
                    },
                    error: function(xhr) {
                        if (requestId !== viewerState.requestId) {
                            return;
                        }

                        var message = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Unable to load the syllabus right now. Please try again later.';

                        modalTitle.textContent = subjectName + ' Syllabus';
                        modalMeta.textContent = gradeName ? gradeName + ' syllabus preview' : 'Syllabus preview';
                        setSourceLink(null);
                        modalBody.innerHTML = renderErrorState(message);
                    }
                });
            });

        $(modalElement)
            .off('hidden.bs.modal.subjectSyllabusPreview')
            .on('hidden.bs.modal.subjectSyllabusPreview', function() {
                setSourceLink(null);
            });
    };

    window.initializeSubjectGradeSyllabusViewer();
</script>
