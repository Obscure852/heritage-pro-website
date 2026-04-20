{{-- Check In Tab --}}
<div class="tab-pane fade" id="checkin-tab" role="tabpanel">
    <div class="help-text">
        <div class="help-title">Return an Item</div>
        <div class="help-content">
            Scan or enter the accession number of the item being returned.
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            {{-- Accession Number Lookup --}}
            <div class="mb-3">
                <label class="form-label">Accession Number</label>
                <div class="input-group">
                    <input type="text"
                           id="checkin-accession"
                           class="form-control"
                           placeholder="Scan or type accession number..."
                           autocomplete="off">
                    <button type="button" class="btn btn-outline-secondary btn-lookup" id="checkin-lookup-btn">
                        <i class="bx bx-search"></i>
                    </button>
                </div>
            </div>

            {{-- Copy Info (hidden until lookup) --}}
            <div id="checkin-copy-info" style="display: none;"></div>

            {{-- Notes --}}
            <div class="mb-3">
                <label class="form-label">Notes <span class="text-muted">(optional)</span></label>
                <textarea id="checkin-notes" class="form-control" rows="2" placeholder="Any notes for this return..."></textarea>
            </div>

            {{-- Submit --}}
            <div class="mt-3 text-end">
                <button type="button" id="checkin-submit-btn" class="btn btn-primary btn-loading" disabled>
                    <span class="btn-text"><i class="bx bx-log-in-circle me-1"></i> Check In</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
