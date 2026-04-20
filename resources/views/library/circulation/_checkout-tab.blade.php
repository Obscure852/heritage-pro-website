{{-- Check Out Tab --}}
<div class="tab-pane fade show active" id="checkout-tab" role="tabpanel">
    <div class="help-text">
        <div class="help-title">Check Out an Item</div>
        <div class="help-content">
            Scan or enter the accession number, select a borrower, then click Check Out.
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {{-- Accession Number Lookup --}}
            <div class="mb-3">
                <label class="form-label">Accession Number</label>
                <div class="input-group">
                    <input type="text"
                           id="checkout-accession"
                           class="form-control"
                           placeholder="Scan or type accession number..."
                           autocomplete="off">
                    <button type="button" class="btn btn-outline-secondary btn-lookup" id="checkout-lookup-btn">
                        <i class="bx bx-search"></i>
                    </button>
                </div>
            </div>

            {{-- Copy Info (hidden until lookup) --}}
            <div id="checkout-copy-info" style="display: none;"></div>

            {{-- Borrower Search --}}
            <div class="mb-3 borrower-choices">
                <label class="form-label">Borrower</label>
                <select id="checkout-borrower-select" class="form-select">
                    <option value="">Search borrower by name or ID...</option>
                </select>
                <input type="hidden" id="checkout-borrower-type" name="borrower_type">
                <input type="hidden" id="checkout-borrower-id" name="borrower_id">
            </div>

            {{-- Borrower Status (hidden until borrower selected) --}}
            <div id="checkout-borrower-status" style="display: none;"></div>

            {{-- Notes --}}
            <div class="mb-3">
                <label class="form-label">Notes <span class="text-muted">(optional)</span></label>
                <textarea id="checkout-notes" class="form-control" rows="2" placeholder="Any notes for this checkout..."></textarea>
            </div>

            {{-- Submit --}}
            <div class="mt-3 text-end">
                <button type="button" id="checkout-submit-btn" class="btn btn-primary btn-loading" disabled>
                    <span class="btn-text"><i class="bx bx-log-out-circle me-1"></i> Check Out</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
