{{-- Renewal Modal --}}
<div class="modal fade" id="renewal-modal" tabindex="-1" aria-labelledby="renewalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="renewalModalLabel">
                    <i class="bx bx-refresh me-2"></i>Renew Loan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="renewal-transaction-id">

                <div class="mb-3">
                    <label class="form-label text-muted" style="font-size: 13px;">Book</label>
                    <div id="renewal-book-title" style="font-weight: 600; color: #1f2937;"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted" style="font-size: 13px;">Borrower</label>
                    <div id="renewal-borrower-name" style="font-weight: 500; color: #374151;"></div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label text-muted" style="font-size: 13px;">Current Due Date</label>
                        <div id="renewal-current-due-date" style="font-weight: 500; color: #374151;"></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-muted" style="font-size: 13px;">Times Renewed</label>
                        <div><span id="renewal-count" style="font-weight: 500; color: #374151;">0</span></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes <span class="text-muted">(optional)</span></label>
                    <textarea id="renewal-notes" class="form-control" rows="2" placeholder="Renewal notes..."></textarea>
                </div>

                <div class="info-box" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 3px; padding: 10px 14px; font-size: 13px; color: #1e40af;">
                    <i class="bx bx-info-circle me-1"></i>
                    The new due date will be calculated from today based on the borrower type's loan period.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="renewal-confirm-btn" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="bx bx-refresh me-1"></i> Confirm Renewal</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Renewing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
