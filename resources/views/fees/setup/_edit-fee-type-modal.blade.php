{{-- Edit Fee Type Modal --}}
<div class="modal fade" id="editFeeTypeModal" tabindex="-1" aria-labelledby="editFeeTypeModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFeeTypeModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Fee Type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editFeeTypeForm">
                <div class="modal-body">
                    <input type="hidden" id="editFeeTypeId">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="editFeeTypeCode">
                                Code <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control"
                                id="editFeeTypeCode"
                                required
                                maxlength="10">
                            <div class="form-hint">Unique identifier (max 10 characters)</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="editFeeTypeName">
                                Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control"
                                id="editFeeTypeName"
                                required>
                            <div class="form-hint">Display name for this fee type</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label" for="editFeeTypeCategory">
                                Category <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="editFeeTypeCategory" required>
                                @foreach ($categories ?? [] as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="form-hint">Group this fee type under a category</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label" for="editFeeTypeDescription">Description</label>
                            <textarea class="form-control"
                                id="editFeeTypeDescription"
                                rows="2"
                                placeholder="Optional description"></textarea>
                            <div class="form-hint">Additional details about this fee type</div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check" style="padding: 12px; background: #f9fafb; border-radius: 3px;">
                                <input type="checkbox" class="form-check-input" id="editFeeTypeOptional">
                                <label class="form-check-label" for="editFeeTypeOptional">
                                    <strong>Optional Fee</strong>
                                </label>
                                <div class="form-hint mt-1">Students can opt out of this fee</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check" style="padding: 12px; background: #f9fafb; border-radius: 3px;">
                                <input type="checkbox" class="form-check-input" id="editFeeTypeActive">
                                <label class="form-check-label" for="editFeeTypeActive">
                                    <strong>Active</strong>
                                </label>
                                <div class="form-hint mt-1">Can be used in fee structures</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save me-1"></i> Save Changes</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
