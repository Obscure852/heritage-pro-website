@extends('layouts.master-student-portal')
@section('title')
    Progress Report
@endsection

@section('css')
<style>
    .portal-container {
        background: white;
        border-radius: 3px;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .portal-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 28px;
        border-radius: 3px 3px 0 0;
    }

    .portal-body {
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

    .term-selector {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 3px;
        color: white;
        padding: 8px 16px;
        font-size: 14px;
    }

    .term-selector:focus {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
        box-shadow: none;
        color: white;
    }

    .term-selector option {
        background: #374151;
        color: white;
    }

    .remarks-card {
        border: none;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .remarks-card .card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e5e7eb;
        padding: 16px 20px;
    }

    .remarks-card .card-header h6 {
        margin: 0;
        font-weight: 600;
        color: #374151;
    }

    .remarks-card .card-header i {
        color: #3b82f6;
    }

    .remarks-card .card-body {
        padding: 20px;
    }

    .remarks-content {
        background: #fafafa;
        padding: 16px;
        border-radius: 3px;
        border-left: 3px solid #3b82f6;
        color: #374151;
        line-height: 1.6;
        font-style: italic;
    }

    .teacher-badge {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .head-badge {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .loading-spinner {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 200px;
    }

    .loading-spinner .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    @media (max-width: 768px) {
        .portal-header {
            padding: 20px;
        }

        .portal-body {
            padding: 16px;
        }
    }
</style>
@endsection

@section('content')
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

    <div class="portal-container">
        <div class="portal-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 style="margin:0;">
                        <i class="bx bx-message-square-detail me-2"></i> Progress Report
                    </h3>
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        {{ $student->currentClass?->name ?? 'No Class Assigned' }} -
                        Term {{ $currentTerm->term }}, {{ $currentTerm->year }}
                    </p>
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-md-end justify-content-start mt-md-0 mt-3">
                        <select name="term" id="termId" class="form-select term-selector" style="max-width: 200px;">
                            @if (!empty($terms))
                                @foreach ($terms as $term)
                                    <option value="{{ $term->id }}"
                                        {{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                                        {{ 'Term ' . $term->term . ', ' . $term->year }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="portal-body">
            <div class="help-text">
                <div class="help-title">Progress Report</div>
                <div class="help-content">
                    View overall comments and remarks from your Class Teacher and School Head regarding
                    your academic progress and conduct for the selected term.
                </div>
            </div>

            <div id="progress-content">
                @if($comment)
                    <div class="row">
                        <!-- Class Teacher Remarks -->
                        <div class="col-md-6 mb-4">
                            <div class="card remarks-card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6>
                                        <i class="bx bx-user-circle me-2"></i> Class Teacher Remarks
                                    </h6>
                                    <span class="teacher-badge">
                                        <i class="bx bx-chalkboard me-1"></i> Class Teacher
                                    </span>
                                </div>
                                <div class="card-body">
                                    @if($comment->class_teacher_remarks)
                                        <div class="remarks-content">
                                            "{{ $comment->class_teacher_remarks }}"
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="bx bx-message-rounded-dots text-muted display-4"></i>
                                            <p class="text-muted mt-2 mb-0">No remarks from class teacher yet</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- School Head Remarks -->
                        <div class="col-md-6 mb-4">
                            <div class="card remarks-card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6>
                                        <i class="bx bx-user-check me-2"></i> School Head Remarks
                                    </h6>
                                    <span class="head-badge">
                                        <i class="bx bx-building me-1"></i> School Head
                                    </span>
                                </div>
                                <div class="card-body">
                                    @if($comment->school_head_remarks)
                                        <div class="remarks-content">
                                            "{{ $comment->school_head_remarks }}"
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="bx bx-message-rounded-dots text-muted display-4"></i>
                                            <p class="text-muted mt-2 mb-0">No remarks from school head yet</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bx bx-message-square-detail text-muted display-4"></i>
                        <p class="text-muted mt-3">No progress report available for this term</p>
                        <p class="text-muted small">Comments from your teachers will appear here once they are submitted.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Term change handler
        $('#termId').change(function() {
            var term = $(this).val();
            var studentTermUrl = "{{ route('student.term-session') }}";

            $.ajax({
                url: studentTermUrl,
                method: 'POST',
                data: {
                    term_id: term,
                    _token: '{{ csrf_token() }}'
                },
                error: function(xhr, status, error) {
                    console.error("Response:", xhr.responseText);
                },
                success: function() {
                    // Reload the page to get new data
                    window.location.reload();
                }
            });
        });
    });
</script>
@endsection
