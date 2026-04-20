<style>
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
</style>

<div class="help-text">
    <div class="help-title">Progress Report</div>
    <div class="help-content">
        View overall comments and remarks from your Class Teacher and School Head regarding
        your academic progress and conduct for the selected term.
    </div>
</div>

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
