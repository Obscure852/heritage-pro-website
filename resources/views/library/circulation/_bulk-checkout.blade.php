{{-- Bulk Check Out Tab --}}
<div class="tab-pane fade" id="bulk-checkout-tab" role="tabpanel">
    <div class="help-text">
        <div class="help-title">Bulk Check Out</div>
        <div class="help-content">
            Select a borrower, then scan multiple books. Click Process All when ready.
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {{-- Borrower Search --}}
            <div class="mb-3 borrower-choices">
                <label class="form-label">Borrower</label>
                <select id="bulk-checkout-borrower-select" class="form-select">
                    <option value="">Search borrower by name or ID...</option>
                </select>
                <input type="hidden" id="bulk-checkout-borrower-type" name="borrower_type">
                <input type="hidden" id="bulk-checkout-borrower-id" name="borrower_id">
            </div>

            {{-- Borrower Status --}}
            <div id="bulk-checkout-borrower-status" style="display: none;"></div>

            {{-- Accession Number Scanning --}}
            <div class="mb-3">
                <label class="form-label">Scan Books</label>
                <div class="input-group">
                    <input type="text"
                           id="bulk-checkout-accession"
                           class="form-control"
                           placeholder="Scan or type accession number..."
                           autocomplete="off"
                           disabled>
                    <button type="button" class="btn btn-outline-secondary btn-lookup" id="bulk-checkout-add-btn" disabled>
                        <i class="bx bx-plus"></i> Add
                    </button>
                </div>
            </div>

            {{-- Scanned Items List --}}
            <div class="mb-3">
                <div class="scanned-list" id="bulk-checkout-list">
                    <div class="p-3 text-center text-muted">No books scanned yet</div>
                </div>
                <div class="scanned-counter" id="bulk-checkout-counter">0 books queued</div>
            </div>

            {{-- Notes --}}
            <div class="mb-3">
                <label class="form-label">Notes <span class="text-muted">(optional)</span></label>
                <textarea id="bulk-checkout-notes" class="form-control" rows="2" placeholder="Notes for all checkouts..."></textarea>
            </div>

            {{-- Process All Button --}}
            <div class="mt-3 text-end">
                <button type="button" id="bulk-checkout-process-btn" class="btn btn-primary btn-loading" disabled>
                    <span class="btn-text"><i class="bx bx-transfer-alt me-1"></i> Process All Checkouts</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
