{{-- Review Panel — shown to assigned reviewers --}}
<div class="card mb-4" style="border: 1px solid #e5e7eb; border-radius: 3px; border-top: 3px solid #3b82f6;">
    <div class="card-header" style="background: #f0f7ff; padding: 16px; border-bottom: 1px solid #e5e7eb;">
        <h5 style="margin: 0; font-size: 16px; font-weight: 600; color: #1e40af;">
            <i class="fas fa-clipboard-check me-2"></i> Review Document
        </h5>
    </div>
    <div class="card-body" style="padding: 20px;">
        {{-- Existing approval records (other reviewers' actions) --}}
        @if($pendingApprovals->isNotEmpty())
            <div class="mb-3">
                <label class="form-label fw-semibold" style="font-size: 13px; color: #6b7280;">Reviewer Status</label>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    @foreach($pendingApprovals as $approval)
                        <span class="badge
                            @if($approval->status === 'approved') bg-success
                            @elseif($approval->status === 'rejected') bg-danger
                            @elseif($approval->status === 'revision_required') bg-warning text-dark
                            @elseif($approval->status === 'in_review') bg-info
                            @else bg-secondary
                            @endif"
                            style="font-size: 12px; padding: 5px 10px; border-radius: 20px;">
                            <i class="fas
                                @if($approval->status === 'approved') fa-check
                                @elseif($approval->status === 'rejected') fa-times
                                @elseif($approval->status === 'revision_required') fa-redo
                                @elseif($approval->status === 'in_review') fa-spinner
                                @else fa-clock
                                @endif me-1"></i>
                            {{ $approval->reviewer->full_name ?? 'Unknown' }} &mdash; {{ str_replace('_', ' ', ucfirst($approval->status)) }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Comment textarea (always visible per CONTEXT.md) --}}
        <div class="mb-3">
            <label for="review-comments" class="form-label fw-semibold">Review Comments</label>
            <textarea class="form-control" id="review-comments" rows="4"
                      placeholder="Enter your review comments..."
                      style="border-radius: 3px;"></textarea>
            <small class="text-muted">Required for Reject and Request Revision actions.</small>
        </div>

        {{-- Action buttons --}}
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="btn btn-success btn-sm" id="btn-review-approve" style="border-radius: 3px; font-weight: 500;">
                <i class="fas fa-check"></i> Approve
            </button>
            <button type="button" class="btn btn-warning btn-sm" id="btn-review-revision" style="border-radius: 3px; font-weight: 500;">
                <i class="fas fa-redo"></i> Request Revision
            </button>
            <button type="button" class="btn btn-danger btn-sm" id="btn-review-reject" style="border-radius: 3px; font-weight: 500;">
                <i class="fas fa-times"></i> Reject
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var documentId = {{ $document->id }};

    function submitReview(action) {
        var comments = document.getElementById('review-comments').value.trim();

        // Validate comments required for reject/revision
        if ((action === 'reject' || action === 'revision') && !comments) {
            Swal.fire('Comments Required', 'Please provide comments when rejecting or requesting revision.', 'warning');
            return;
        }

        var confirmTitle, confirmText, confirmColor, confirmBtnText;

        if (action === 'approve') {
            confirmTitle = 'Approve Document?';
            confirmText = 'Are you sure you want to approve this document?';
            confirmColor = '#10b981';
            confirmBtnText = 'Yes, Approve';
        } else if (action === 'revision') {
            confirmTitle = 'Request Revision?';
            confirmText = 'The document will be sent back to the author with your comments.';
            confirmColor = '#f59e0b';
            confirmBtnText = 'Yes, Request Revision';
        } else {
            confirmTitle = 'Reject Document?';
            confirmText = 'Comments: "' + (comments.length > 80 ? comments.substring(0, 80) + '...' : comments) + '"';
            confirmColor = '#ef4444';
            confirmBtnText = 'Yes, Reject';
        }

        Swal.fire({
            title: confirmTitle,
            text: confirmText,
            icon: action === 'approve' ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6b7280',
            confirmButtonText: confirmBtnText
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/documents/' + documentId + '/workflow/review',
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        action: action,
                        comments: comments || null
                    }),
                    success: function(response) {
                        Swal.fire({
                            toast: true, position: 'top-end', icon: 'success',
                            title: response.message || 'Review submitted successfully!',
                            showConfirmButton: false, timer: 2000
                        });
                        setTimeout(function() { location.reload(); }, 1000);
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON?.message || 'Failed to submit review.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    }

    // Bind button handlers
    var approveBtn = document.getElementById('btn-review-approve');
    var revisionBtn = document.getElementById('btn-review-revision');
    var rejectBtn = document.getElementById('btn-review-reject');

    if (approveBtn) approveBtn.addEventListener('click', function() { submitReview('approve'); });
    if (revisionBtn) revisionBtn.addEventListener('click', function() { submitReview('revision'); });
    if (rejectBtn) rejectBtn.addEventListener('click', function() { submitReview('reject'); });
})();
</script>
