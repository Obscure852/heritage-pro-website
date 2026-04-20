{{-- Copy Fee Structures Modal --}}
<div class="modal fade" id="copyStructuresModal" tabindex="-1" aria-labelledby="copyStructuresModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #f8f9fa; border-bottom: 1px solid #e5e7eb;">
                <h5 class="modal-title" id="copyStructuresModalLabel" style="color: #374151; font-weight: 600;">
                    <i class="fas fa-copy me-2"></i>Copy Fee Structures
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('fees.setup.structures.copy') }}" id="copyStructuresForm">
                @csrf
                <div class="modal-body">
                    <div class="help-text" style="background: #f0fdf4; border-left-color: #10b981; margin-bottom: 20px;">
                        <div class="help-content" style="color: #166534; font-size: 13px; line-height: 1.4;">
                            Copy all fee structures from a source year to a destination year.
                            This will create new fee structures for all grade and fee type combinations.
                            Existing structures in the destination year will not be overwritten.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="copySourceYear">
                            Source Year <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" name="from_year" id="copySourceYear" required>
                            <option value="">Select source year to copy from</option>
                            @foreach ($years ?? [] as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        <div class="form-hint">The year containing fee structures you want to copy</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="copyDestYear">
                            Destination Year <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" name="to_year" id="copyDestYear" required>
                            <option value="">Select destination year to copy to</option>
                            @foreach ($years ?? [] as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        <div class="form-hint">Only current and future years can be selected as destination</div>
                    </div>

                    <div class="mb-0">
                        <div class="form-check" style="display: flex; align-items: center; gap: 8px; padding: 12px; background: #f9fafb; border-radius: 3px;">
                            <input type="checkbox"
                                class="form-check-input"
                                name="adjust_amount"
                                id="copyAdjustAmount"
                                value="1"
                                style="width: 18px; height: 18px; margin: 0; cursor: pointer;">
                            <div>
                                <label class="form-check-label" for="copyAdjustAmount" style="font-size: 14px; color: #374151; cursor: pointer;">
                                    Apply percentage adjustment
                                </label>
                                <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">
                                    Increase or decrease all amounts by a percentage
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0 mt-3" id="copyAdjustmentContainer" style="display: none;">
                        <label class="form-label" for="copyAdjustmentPercentage">
                            Adjustment Percentage
                        </label>
                        <div class="input-group">
                            <input type="number"
                                class="form-control"
                                name="adjustment_percentage"
                                id="copyAdjustmentPercentage"
                                placeholder="e.g., 5 for 5% increase, -5 for 5% decrease"
                                min="-100"
                                max="100"
                                step="0.01">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-hint">Enter positive value to increase, negative to decrease</div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f3f4f6;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-loading">
                        <span class="btn-text"><i class="fas fa-copy me-1"></i> Copy Structures</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Copying...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #copyStructuresModal .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
    }

    #copyStructuresModal .btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    #copyStructuresModal .form-select:focus,
    #copyStructuresModal .form-control:focus {
        outline: none;
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const adjustCheckbox = document.getElementById('copyAdjustAmount');
    const adjustmentContainer = document.getElementById('copyAdjustmentContainer');
    const sourceYearSelect = document.getElementById('copySourceYear');
    const destYearSelect = document.getElementById('copyDestYear');
    const copyForm = document.getElementById('copyStructuresForm');

    if (adjustCheckbox) {
        adjustCheckbox.addEventListener('change', function() {
            adjustmentContainer.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Prevent selecting same year for source and destination
    if (sourceYearSelect && destYearSelect) {
        sourceYearSelect.addEventListener('change', function() {
            const selectedValue = this.value;
            Array.from(destYearSelect.options).forEach(option => {
                option.disabled = option.value === selectedValue && selectedValue !== '';
            });
        });

        destYearSelect.addEventListener('change', function() {
            const selectedValue = this.value;
            Array.from(sourceYearSelect.options).forEach(option => {
                option.disabled = option.value === selectedValue && selectedValue !== '';
            });
        });
    }

    if (copyForm) {
        copyForm.addEventListener('submit', function(e) {
            const fromValue = sourceYearSelect.value;
            const toValue = destYearSelect.value;

            if (fromValue === toValue) {
                e.preventDefault();
                alert('Source and destination years cannot be the same.');
                return false;
            }

            const submitBtn = copyForm.querySelector('button[type="submit"].btn-loading');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
        });
    }
});
</script>
