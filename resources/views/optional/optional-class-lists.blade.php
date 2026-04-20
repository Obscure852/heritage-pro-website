<style>
    /* Option Cards */
    .option-card {
        background: white;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        border: 1px solid #e5e7eb;
    }

    .option-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .option-card-header {
        background: #f9fafb;
        color: #374151;
        padding: 12px 16px;
        border-radius: 3px 3px 0 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .option-card-header h6 {
        margin: 0;
        font-weight: 600;
        font-size: 14px;
    }

    .option-card-body {
        padding: 16px;
        flex: 1;
        font-size: 13px;
        color: #4b5563;
    }

    .option-card-body .info-row {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }

    .option-card-body .info-row:last-child {
        margin-bottom: 0;
    }

    .option-card-body .info-row i {
        width: 20px;
        color: #6b7280;
        margin-right: 8px;
    }

    .option-card-body .info-row strong {
        color: #374151;
        margin-right: 4px;
    }

    .option-card-footer {
        padding: 12px 16px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        border-radius: 0 0 3px 3px;
    }

    /* Action Buttons - matching admissions style */
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
        font-size: 16px;
    }

    /* Accordion Styling */
    .themed-accordion.accordion-flush .accordion-item {
        border: 1px solid #e5e7eb !important;
        border-radius: 3px;
        margin-bottom: 12px;
        overflow: hidden;
    }

    .themed-accordion .accordion-button {
        background: #f9fafb;
        font-weight: 600;
        color: #374151;
        padding: 14px 20px;
    }

    .themed-accordion .accordion-button:not(.collapsed) {
        background: #4e73df;
        color: white;
        box-shadow: none;
    }

    .themed-accordion .accordion-button:not(.collapsed) .badge {
        background: rgba(255, 255, 255, 0.2) !important;
        color: white;
    }

    .themed-accordion .accordion-button:focus {
        box-shadow: none;
        border-color: transparent;
    }

    .themed-accordion .accordion-body {
        padding: 20px;
        background: white;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6b7280;
    }

    .empty-state i {
        font-size: 40px;
        color: #d1d5db;
        margin-bottom: 12px;
    }
</style>

@php
    $groupedClasses = $classes->groupBy(function ($class) {
        return $class->gradeSubject->subject->name ?? 'Uncategorized';
    });
@endphp

@if ($groupedClasses->isNotEmpty())
    <div class="themed-accordion accordion accordion-flush" id="subjectsAccordion">
        @foreach ($groupedClasses as $subject => $subjectClasses)
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading{{ Str::slug($subject) }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse{{ Str::slug($subject) }}" aria-expanded="false"
                        aria-controls="collapse{{ Str::slug($subject) }}">
                        <strong>{{ $subject }}</strong>
                        <span class="ms-2 badge bg-info">{{ $subjectClasses->count() }}
                            {{ Str::plural('class', $subjectClasses->count()) }}</span>
                        @if ($subjectClasses->first())
                            @php
                                $grouping = $subjectClasses->first()->grouping ?? 'Other';
                                $groupingLevel =
                                    optional($subjectClasses->first()->grade)->level ??
                                    optional(optional($subjectClasses->first()->gradeSubject)->grade)->level;
                                $isSeniorGrouping = $groupingLevel === \App\Models\SchoolSetup::LEVEL_SENIOR;
                            @endphp

                            @if ($isSeniorGrouping)
                                @php
                                    $departmentBadgeColors = [
                                        'Mathematics' => 'bg-primary',
                                        'Extended Mathematics' => 'bg-primary',
                                        'Double Science' => 'bg-warning',
                                        'Accounting' => 'bg-success',
                                        'Biology' => 'bg-warning',
                                        'Physics' => 'bg-warning',
                                        'Chemistry' => 'bg-warning',
                                        'Humanities' => 'bg-primary',
                                        'English' => 'bg-secondary',
                                        'Physical Education' => 'bg-success',
                                        'Religious Education' => 'bg-primary',
                                        'Business Studies' => 'bg-warning',
                                        'Setswana' => 'bg-info',
                                        'Design & Technology' => 'bg-info',
                                        'Geography' => 'bg-dark',
                                        'Art' => 'bg-primary',
                                        'Statistics' => 'bg-secondary',
                                        'Agriculture' => 'bg-success',
                                        'French' => 'bg-info',
                                        'History' => 'bg-dark',
                                    ];
                                    $groupingNormalized = trim($grouping);
                                    if (array_key_exists($groupingNormalized, $departmentBadgeColors)) {
                                        $badgeClass = $departmentBadgeColors[$groupingNormalized];
                                        $badgeLabel = $groupingNormalized;
                                    } else {
                                        $badgeClass = 'bg-secondary';
                                        $badgeLabel = 'Other';
                                    }
                                @endphp
                                <span class="ms-2 badge {{ $badgeClass }}">{{ $badgeLabel }}</span>
                            @else
                                <span
                                    class="ms-2 badge
                                        @switch($grouping)
                                            @case('Core')
                                                bg-primary
                                                @break
                                            @case('Practicals')
                                                bg-success
                                                @break
                                            @case('Generals')
                                                bg-warning
                                                @break
                                            @case('Other')
                                                bg-info
                                                @break
                                            @default
                                                bg-secondary
                                        @endswitch
                                    ">{{ $grouping }}</span>
                            @endif
                        @endif
                    </button>
                </h2>
                <div id="collapse{{ Str::slug($subject) }}" class="accordion-collapse collapse"
                    aria-labelledby="heading{{ Str::slug($subject) }}" data-bs-parent="#subjectsAccordion">
                    <div class="accordion-body">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
                            @forelse ($subjectClasses as $klass)
                                <div class="col">
                                    <div class="option-card">
                                        <div class="option-card-header">
                                            <h6>{{ $klass->name ?? 'Unnamed Class' }}</h6>
                                        </div>
                                        <div class="option-card-body">
                                            <div class="info-row">
                                                <i class="bx bx-user"></i>
                                                <strong>Teacher:</strong>
                                                <span>@if($klass->teacher){{ substr($klass->teacher->firstname, 0, 1) }}. {{ $klass->teacher->lastname }}@else N/A @endif</span>
                                            </div>
                                            @if($klass->assistantTeacher)
                                                <div class="info-row">
                                                    <i class="bx bx-user-plus"></i>
                                                    <strong>Assistant:</strong>
                                                    <span>{{ substr($klass->assistantTeacher->firstname, 0, 1) }}. {{ $klass->assistantTeacher->lastname }}</span>
                                                </div>
                                            @endif
                                            <div class="info-row">
                                                <i class="bx bx-group"></i>
                                                <strong>Students:</strong>
                                                <span>{{ $klass->students->count() ?? '0' }}</span>
                                            </div>
                                            <div class="info-row">
                                                <i class="bx bx-book"></i>
                                                <strong>Grade:</strong>
                                                <span>{{ $klass->grade->name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                        <div class="option-card-footer">
                                            <div class="action-buttons">
                                                @can('optional-teacher', $klass)
                                                    <a href="{{ route('optional.allocated-options', $klass->id ?? 0) }}"
                                                        class="btn btn-sm btn-outline-info"
                                                        data-bs-toggle="tooltip"
                                                        title="View Class List">
                                                        <i class="bx bx-show-alt"></i>
                                                    </a>

                                                    <a href="{{ route('optional.allocate-options', $klass->id ?? 0) }}"
                                                        class="btn btn-sm btn-outline-success"
                                                        data-bs-toggle="tooltip"
                                                        title="Allocate Students">
                                                        <i class="bx bx-layer"></i>
                                                    </a>
                                                @endcan

                                                @can('optional-teacher', $klass)
                                                    <a href="{{ route('optional.edit-option', $klass->id ?? 0) }}"
                                                        class="btn btn-sm btn-outline-warning"
                                                        data-bs-toggle="tooltip"
                                                        title="Edit Class">
                                                        <i class="bx bx-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('manage-academic')
                                                    <a href="#"
                                                        onclick="if(confirm('Are you sure you want to delete {{ $klass->name ?? 'this class' }}?')) window.location.href='{{ route('optional.delete', $klass->id ?? 0) }}'; return false;"
                                                        class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="tooltip"
                                                        title="Delete Class">
                                                        <i class="bx bx-trash"></i>
                                                    </a>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="empty-state">
                                        <i class="bx bx-folder-open"></i>
                                        <p>No classes found for this subject.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="empty-state">
        <i class="bx bx-search-alt"></i>
        <h5>No Classes Found</h5>
        <p>No optional classes found for this grade. Try selecting a different grade or create a new option.</p>
    </div>
@endif

<script>
    $(document).ready(function() {
        $('.delete-option').on('click', function(e) {
            e.preventDefault();
            var optionName = $(this).data('option-name');
            var testId = $(this).data('test-id');
            var deleteUrl = $(this).attr('href');

            var checkScoresUrl = "{{ route('optional.check-scores', ':id') }}";
            checkScoresUrl = checkScoresUrl.replace(':id', testId);

            $.ajax({
                url: checkScoresUrl,
                method: 'GET',
                success: function(response) {
                    if (response.hasScores) {
                        if (confirm(
                                'Are you sure you want to delete the optional subject "' +
                                optionName + '"? This test has scores associated with it.'
                            )) {
                            window.location.href = deleteUrl;
                        }
                    } else {
                        window.location.href = deleteUrl;
                    }
                },
                error: function() {
                    if (confirm(
                            'Unable to check scores. Are you sure you want to delete the optional subject "' +
                            optionName + '"?')) {
                        window.location.href = deleteUrl;
                    }
                }
            });
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
