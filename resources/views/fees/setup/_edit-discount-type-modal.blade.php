{{-- Edit Discount Type Modal --}}
<div class="modal fade" id="editDiscountTypeModal" tabindex="-1" aria-labelledby="editDiscountTypeModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDiscountTypeModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Discount Type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDiscountTypeForm">
                <div class="modal-body">
                    <input type="hidden" id="editDiscountTypeId">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="editDiscountTypeCode">
                                Code <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control"
                                id="editDiscountTypeCode"
                                required
                                maxlength="10">
                            <div class="form-hint">Unique identifier (max 10 characters)</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="editDiscountTypeName">
                                Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control"
                                id="editDiscountTypeName"
                                required>
                            <div class="form-hint">Display name for this discount type</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="editDiscountTypePercentage">
                                Percentage <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number"
                                    class="form-control"
                                    id="editDiscountTypePercentage"
                                    required
                                    min="0"
                                    max="100"
                                    step="0.01">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="form-hint">Discount percentage (0-100)</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="editDiscountTypeAppliesTo">
                                Applies To <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="editDiscountTypeAppliesTo" required>
                                @foreach ($appliesOptions ?? [] as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="form-hint">Which fees this discount applies to</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label" for="editDiscountTypeDescription">Description</label>
                            <textarea class="form-control"
                                id="editDiscountTypeDescription"
                                rows="2"
                                placeholder="Optional description"></textarea>
                            <div class="form-hint">Additional details about this discount type</div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check" style="padding: 12px; background: #f9fafb; border-radius: 3px;">
                                <input type="checkbox" class="form-check-input" id="editDiscountTypeActive">
                                <label class="form-check-label" for="editDiscountTypeActive">
                                    <strong>Active</strong>
                                </label>
                                <div class="form-hint mt-1">Discount can be assigned to students</div>
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
