{{-- Submit for Review Modal --}}
<div class="modal fade" id="submitReviewModal" tabindex="-1" aria-labelledby="submitReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 3px;">
            <div class="modal-header" style="border-bottom: 1px solid #e5e7eb;">
                <h5 class="modal-title" id="submitReviewModalLabel" style="font-weight: 600;">
                    <i class="fas fa-paper-plane me-2" style="color: #3b82f6;"></i> Submit for Review
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                {{-- Reviewer Search --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reviewers <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="reviewer-search-input"
                           placeholder="Search by name or email..." autocomplete="off"
                           style="border-radius: 3px;">
                    <div id="reviewer-search-results" style="max-height: 200px; overflow-y: auto; display: none; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 3px 3px; background: white;">
                    </div>
                </div>

                {{-- Selected Reviewers --}}
                <div id="selected-reviewers-container" class="mb-3" style="display: none;">
                    <label class="form-label fw-semibold" style="font-size: 13px; color: #6b7280;">Selected Reviewers</label>
                    <div id="selected-reviewers-list" style="display: flex; flex-wrap: wrap; gap: 6px;">
                    </div>
                </div>

                {{-- Notes --}}
                <div class="mb-3">
                    <label for="submit-review-notes" class="form-label fw-semibold">Notes <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea class="form-control" id="submit-review-notes" rows="3"
                              placeholder="Add notes for the reviewer..." style="border-radius: 3px;"></textarea>
                </div>

                {{-- Deadline --}}
                <div class="mb-3">
                    <label for="submit-review-deadline" class="form-label fw-semibold">Review Deadline</label>
                    <input type="date" class="form-control" id="submit-review-deadline" style="border-radius: 3px;">
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm btn-loading" id="submit-review-btn" style="border-radius: 3px;">
                    <span class="btn-text"><i class="fas fa-paper-plane"></i> Submit for Review</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Submitting...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .reviewer-result-item {
        padding: 10px 14px;
        cursor: pointer;
        transition: background 0.15s;
        border-bottom: 1px solid #f3f4f6;
    }
    .reviewer-result-item:last-child {
        border-bottom: none;
    }
    .reviewer-result-item:hover {
        background: #f0f4ff;
    }
    .reviewer-result-item .reviewer-name {
        font-weight: 600;
        font-size: 14px;
        color: #1f2937;
    }
    .reviewer-result-item .reviewer-detail {
        font-size: 12px;
        color: #6b7280;
    }
    .selected-reviewer-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #dbeafe;
        color: #1e40af;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
    }
    .selected-reviewer-badge .remove-reviewer {
        cursor: pointer;
        font-size: 11px;
        opacity: 0.7;
        transition: opacity 0.15s;
    }
    .selected-reviewer-badge .remove-reviewer:hover {
        opacity: 1;
    }
</style>

<script>
(function() {
    var selectedReviewers = [];
    var searchTimeout = null;
    var currentXhr = null;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Set default deadline (today + 7 days)
    var defaultDeadline = new Date(Date.now() + 7 * 86400000);
    var deadlineInput = document.getElementById('submit-review-deadline');
    if (deadlineInput) {
        deadlineInput.value = defaultDeadline.toISOString().split('T')[0];
    }

    // Debounced reviewer search
    var searchInput = document.getElementById('reviewer-search-input');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            var query = this.value.trim();
            clearTimeout(searchTimeout);

            if (query.length < 2) {
                document.getElementById('reviewer-search-results').style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(function() {
                searchReviewers(query);
            }, 300);
        });
    }

    function searchReviewers(query) {
        if (currentXhr) {
            currentXhr.abort();
        }

        currentXhr = $.ajax({
            url: '/documents/workflow/reviewers/search',
            method: 'GET',
            data: { q: query },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(response) {
                var resultsContainer = document.getElementById('reviewer-search-results');
                resultsContainer.innerHTML = '';

                var users = response.data || response;
                if (users.length === 0) {
                    resultsContainer.innerHTML = '<div style="padding: 12px; color: #9ca3af; font-size: 13px; text-align: center;">No reviewers found</div>';
                    resultsContainer.style.display = 'block';
                    return;
                }

                users.forEach(function(user) {
                    // Skip already-selected reviewers
                    if (selectedReviewers.find(function(r) { return r.id === user.id; })) return;

                    var item = document.createElement('div');
                    item.className = 'reviewer-result-item';
                    item.innerHTML = '<div class="reviewer-name">' + escapeHtml(user.name) + '</div>' +
                        '<div class="reviewer-detail">' + escapeHtml(user.email) +
                        (user.department ? ' &middot; ' + escapeHtml(user.department) : '') + '</div>';
                    item.onclick = function() {
                        addReviewer(user);
                        resultsContainer.style.display = 'none';
                        searchInput.value = '';
                    };
                    resultsContainer.appendChild(item);
                });

                resultsContainer.style.display = 'block';
            },
            error: function(xhr) {
                if (xhr.statusText !== 'abort') {
                    console.error('Reviewer search failed:', xhr);
                }
            }
        });
    }

    function addReviewer(user) {
        if (selectedReviewers.find(function(r) { return r.id === user.id; })) return;

        selectedReviewers.push(user);
        renderSelectedReviewers();
    }

    function removeReviewer(userId) {
        selectedReviewers = selectedReviewers.filter(function(r) { return r.id !== userId; });
        renderSelectedReviewers();
    }

    function renderSelectedReviewers() {
        var container = document.getElementById('selected-reviewers-container');
        var list = document.getElementById('selected-reviewers-list');
        list.innerHTML = '';

        if (selectedReviewers.length === 0) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';
        selectedReviewers.forEach(function(user) {
            var badge = document.createElement('span');
            badge.className = 'selected-reviewer-badge';
            badge.innerHTML = '<i class="fas fa-user-check" style="font-size: 11px;"></i> ' +
                escapeHtml(user.name) +
                ' <i class="fas fa-times remove-reviewer" data-user-id="' + user.id + '"></i>';
            badge.querySelector('.remove-reviewer').onclick = function() {
                removeReviewer(user.id);
            };
            list.appendChild(badge);
        });
    }

    // Submit button handler
    var submitBtn = document.getElementById('submit-review-btn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            if (selectedReviewers.length === 0) {
                Swal.fire('Error', 'Please select at least one reviewer.', 'error');
                return;
            }

            var reviewerIds = selectedReviewers.map(function(r) { return r.id; });
            var notes = document.getElementById('submit-review-notes').value.trim();
            var deadline = document.getElementById('submit-review-deadline').value;

            submitBtn.classList.add('loading');
            submitBtn.disabled = true;

            $.ajax({
                url: '/documents/{{ $document->id }}/workflow/submit',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                contentType: 'application/json',
                data: JSON.stringify({
                    reviewer_ids: reviewerIds,
                    notes: notes || null,
                    deadline: deadline || null
                }),
                success: function(response) {
                    bootstrap.Modal.getInstance(document.getElementById('submitReviewModal')).hide();
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success',
                        title: response.message || 'Document submitted for review!',
                        showConfirmButton: false, timer: 2000
                    });
                    setTimeout(function() { location.reload(); }, 1000);
                },
                error: function(xhr) {
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                    var msg = xhr.responseJSON?.message || 'Failed to submit for review.';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        var resultsContainer = document.getElementById('reviewer-search-results');
        var searchInput = document.getElementById('reviewer-search-input');
        if (resultsContainer && searchInput && !resultsContainer.contains(e.target) && e.target !== searchInput) {
            resultsContainer.style.display = 'none';
        }
    });
})();
</script>
