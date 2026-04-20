{{-- Grant Clearance Override Form Partial --}}
<div class="form-container">
    <h5 class="section-title"><i class="fas fa-shield-alt me-2"></i>Grant Clearance Override</h5>

    <div class="help-text">
        <div class="help-title">Override Information</div>
        <div class="help-content">
            Granting a clearance override will mark this student as cleared for the year despite having an outstanding balance.
            This should only be used in exceptional circumstances with proper authorization.
            All overrides are logged in the audit trail.
        </div>
    </div>

    <form action="{{ route('fees.balance.override.grant') }}" method="POST" id="overrideForm">
        @csrf
        <input type="hidden" name="student_id" value="{{ $student->id }}">
        <input type="hidden" name="year" value="{{ $year }}">

        <div class="mb-3">
            <label for="reason" class="form-label">Reason for Override <span class="text-danger">*</span></label>
            <textarea class="form-control @error('reason') is-invalid @enderror"
                id="reason"
                name="reason"
                rows="3"
                required
                minlength="10"
                maxlength="500"
                placeholder="Please provide a detailed reason for granting this clearance override (minimum 10 characters)">{{ old('reason') }}</textarea>
            @error('reason')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Minimum 10 characters required. Maximum 500 characters.</div>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Additional Notes</label>
            <textarea class="form-control @error('notes') is-invalid @enderror"
                id="notes"
                name="notes"
                rows="2"
                maxlength="1000"
                placeholder="Optional additional notes or reference information">{{ old('notes') }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end pt-3 border-top">
            <button type="submit" class="btn btn-primary btn-loading">
                <span class="btn-text"><i class="fas fa-check-circle me-1"></i> Grant Override</span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Processing...
                </span>
            </button>
        </div>
    </form>
</div>

<style>
    .form-container {
        background: white;
        border-radius: 3px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
    }
</style>
