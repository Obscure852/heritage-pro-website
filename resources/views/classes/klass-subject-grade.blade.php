<style>
    /* Class Info Header */
    .class-info-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        border-radius: 3px;
        color: white;
        padding: 12px 16px;
        margin-bottom: 16px;
    }

    .class-info-header p {
        margin: 0;
        font-size: 14px;
    }

    /* Subjects Table */
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

    /* Select2 Theme Styling */
    .select2-container--default .select2-selection--single {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        height: 40px;
        padding: 4px 8px;
        background: white;
        transition: all 0.2s ease;
    }

    .select2-container--default .select2-selection--single:hover {
        border-color: #d1d5db;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #374151;
        line-height: 30px;
        padding-left: 4px;
        font-size: 14px;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9ca3af;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
        right: 8px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #6b7280 transparent transparent transparent;
    }

    /* Select2 Dropdown */
    .select2-dropdown {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 10px 12px;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .select2-container--default .select2-results__option {
        padding: 10px 12px;
        font-size: 14px;
        color: #374151;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: #3b82f6;
        color: white;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background: #f0f9ff;
        color: #1e40af;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected=true] {
        background: #3b82f6;
        color: white;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }

    .btn-back {
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

    .btn-back:hover {
        background: #f3f4f6;
        color: #1f2937;
    }

    .btn-save {
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

    .btn-save:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .btn-save.loading .btn-text {
        display: none;
    }

    .btn-save.loading .btn-spinner {
        display: inline-flex !important;
        align-items: center;
    }

    .btn-save:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-actions {
            flex-direction: column;
        }

        .btn-back,
        .btn-save {
            width: 100%;
            justify-content: center;
        }
    }
</style>

@if ($class !== null)
    @if ($class->teacher && $class->students)
        <div class="class-info-header">
            <p>
                <strong>
                    <i class="bx bx-group me-1"></i>
                    ({{ $class->name ?? '' }}) Class Teacher: {{ $class->teacher->fullName ?? '' }}
                    ({{ $class->students->count() ?? '0' }} students)
                </strong>
            </p>
        </div>
    @endif
@endif

<form action="{{ route('academic.core-subjects') }}" method="POST" id="subjectGradeForm"
    data-class-id="{{ $class->id }}"
    data-term-id="{{ $class->term_id }}"
    data-reload-url="{{ route('academic.subjects-teachers', ['classId' => $class->id, 'termId' => $class->term_id]) }}">
    <input type="hidden" name="term_id" value="{{ $class->term_id }}">
    <input type="hidden" name="grade_id" value="{{ $class->grade->id }}">
    <input type="hidden" name="year" value="{{ $class->year }}">
    @csrf

    <div class="table-responsive">
        <table class="subjects-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Subject</th>
                    <th>Subject Teacher</th>
                    <th>Assistant Teacher</th>
                    <th>Venue</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($grade_subjects ?? [] as $index => $subject)
                    <tr>
                        <td>
                            {{ $index + 1 }}
                            <input type="hidden" name="class" value="{{ $class->id }}"
                                class="form-control form-control-sm">
                        </td>
                        <td>
                            <strong>{{ $subject->subject->name }}</strong>
                            <input type="hidden" name="subjects[]" value="{{ $subject->id }}"
                                class="form-control form-control-sm">
                        </td>
                        <td>
                            <select name="teachers[]" data-trigger class="form-select form-select-sm themed-select">
                                @if (!empty($teachers))
                                    <option value="">Select Teacher ...</option>
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}"
                                            {{ isset($klass_subjects[$subject->id]) && $klass_subjects[$subject->id]->user_id == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->lastname }} {{ $teacher->firstname }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td>
                            <select name="assistants[]" data-trigger class="form-select form-select-sm themed-select">
                                @if (!empty($teachers))
                                    <option value="">Select Assistant ...</option>
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}"
                                            {{ isset($klass_subjects[$subject->id]) && $klass_subjects[$subject->id]->assistant_user_id == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->lastname }} {{ $teacher->firstname }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td>
                            <select name="venues[]" data-trigger class="form-select form-select-sm themed-select">
                                @if (!empty($venues))
                                    <option value="">Select Classroom ...</option>
                                    @foreach ($venues as $venue)
                                        <option value="{{ $venue->id }}"
                                            {{ isset($klass_subjects[$subject->id]) && $klass_subjects[$subject->id]->venue_id == $venue->id ? 'selected' : '' }}>
                                            {{ $venue->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="text-center text-muted" style="padding: 40px 0;">
                                <i class="fas fa-book" style="font-size: 48px; opacity: 0.3;"></i>
                                <p class="mt-3 mb-0" style="font-size: 15px;">No Subjects Allocated</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="form-actions">
        <a href="{{ route('academic.index') }}" class="btn-back">
            <i class="bx bx-arrow-back"></i> Back
        </a>
        @can('manage-academic')
            @if (!session('is_past_term'))
                <button type="submit" class="btn-save">
                    <span class="btn-text"><i class="fas fa-save"></i> Save</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            @endif
        @endcan
    </div>
</form>

<script>
    $(document).ready(function() {
        $('[data-trigger]').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });

        // AJAX form submission - avoids redirect issues when form is loaded via AJAX
        $('#subjectGradeForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = form.find('.btn-save');
            var reloadUrl = form.data('reload-url');

            if (btn.length) {
                btn.addClass('loading');
                btn.prop('disabled', true);
            }

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success && reloadUrl) {
                        // Reload the form content in place with fresh data
                        $.get(reloadUrl, function(html) {
                            $('#subjectGradeList').html(html);
                            if (response.message) {
                                var alertHtml = '<div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show mb-3" role="alert">' +
                                    '<i class="mdi mdi-check-all label-icon"></i><strong>' + response.message + '</strong>' +
                                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                                $('#subjectGradeList').prepend(alertHtml);
                            }
                        });
                    } else if (response.message) {
                        var alertHtml = '<div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show mb-3" role="alert">' +
                            '<i class="mdi mdi-check-all label-icon"></i><strong>' + response.message + '</strong>' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        $('#subjectGradeList').prepend(alertHtml);
                    }
                },
                error: function(xhr) {
                    if (btn.length) {
                        btn.removeClass('loading');
                        btn.prop('disabled', false);
                    }
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || (xhr.responseJSON && xhr.responseJSON.error) || 'An error occurred. Please try again.';
                    var alertHtml = '<div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show mb-3" role="alert">' +
                        '<i class="mdi mdi-block-helper label-icon"></i><strong>' + msg + '</strong>' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                    $('#subjectGradeList').prepend(alertHtml);
                }
            });
        });
    });
</script>
