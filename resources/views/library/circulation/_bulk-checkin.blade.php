{{-- Bulk Check In Tab --}}
<div class="tab-pane fade" id="bulk-checkin-tab" role="tabpanel">
    <div class="help-text">
        <div class="help-title">Bulk Check In</div>
        <div class="help-content">
            Scan multiple returned books, then click Process All to check them all in.
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {{-- Accession Number Scanning --}}
            <div class="mb-3">
                <label class="form-label">Scan Returned Books</label>
                <div class="input-group">
                    <input type="text"
                           id="bulk-checkin-accession"
                           class="form-control"
                           placeholder="Scan or type accession number..."
                           autocomplete="off">
                    <button type="button" class="btn btn-outline-secondary btn-lookup" id="bulk-checkin-add-btn">
                        <i class="bx bx-plus"></i> Add
                    </button>
                </div>
            </div>

            {{-- Scanned Items List --}}
            <div class="mb-3">
                <div class="scanned-list" id="bulk-checkin-list">
                    <div class="p-3 text-center text-muted">No books scanned yet</div>
                </div>
                <div class="scanned-counter" id="bulk-checkin-counter">0 books to return</div>
            </div>

            {{-- Process All Button --}}
            <div class="mt-3 text-end">
                <button type="button" id="bulk-checkin-process-btn" class="btn btn-primary btn-loading" disabled>
                    <span class="btn-text"><i class="bx bx-transfer-alt me-1"></i> Process All Returns</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
