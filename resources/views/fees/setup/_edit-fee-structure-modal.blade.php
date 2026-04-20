{{-- Edit Fee Structure Modal --}}
<div class="modal fade" id="editFeeStructureModal" tabindex="-1" aria-labelledby="editFeeStructureModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editFeeStructureModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Fee Structure
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editFeeStructureForm">
                <div class="modal-body">
                    <input type="hidden" id="editStructureId">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label" for="editStructureFeeType">
                                Fee Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="editStructureFeeType" required>
                                <option value="">Select fee type...</option>
                                @foreach ($activeFeeTypes ?? [] as $feeType)
                                    <option value="{{ $feeType->id }}">{{ $feeType->name }} ({{ $feeType->code }})</option>
                                @endforeach
                            </select>
                            <div class="form-hint">The type of fee this structure defines</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="editStructureGrade">
                                Grade <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="editStructureGrade" required>
                                <option value="">Select grade...</option>
                                @foreach ($grades ?? [] as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-hint">The grade this amount applies to</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="editStructureYear">
                                Year <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="editStructureYear" required>
                                <option value="">Select year...</option>
                                @foreach ($years ?? [] as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                            <div class="form-hint">The academic year for this structure</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="editStructureAmount">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">P</span>
                                <input type="number"
                                    class="form-control"
                                    id="editStructureAmount"
                                    required
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00">
                            </div>
                            <div class="form-hint">The fee amount in Pula</div>
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
