{{-- Copy Fee Structures Modal --}}
<div class="modal fade" id="copyStructuresModal" tabindex="-1" aria-labelledby="copyStructuresModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-bottom: none;">
                <h5 class="modal-title" id="copyStructuresModalLabel">
                    <i class="fas fa-copy me-2"></i>Copy Fee Structures
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('fees.setup.structures.copy') }}" id="copyStructuresForm">
                @csrf
                <div class="modal-body">
                    <div class="help-text" style="background: #f0fdf4; border-left-color: #10b981; margin-bottom: 20px; padding: 12px; border-radius: 0 3px 3px 0;">
                        <div class="help-content" style="color: #166534; font-size: 13px; line-height: 1.4;">
                            Copy all fee structures from a source year to a destination year.
                            This will create new fee structures for all grade and fee type combinations.
                            Existing structures in the destination year will not be overwritten.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="from_year" style="font-weight: 500; color: #374151; font-size: 14px;">
                            Source Year <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" name="from_year" id="from_year" required
                            style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 14px;">
                            <option value="">Select source year to copy from</option>
                            @foreach ($years ?? [] as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        <div class="form-hint" style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                            The year containing fee structures you want to copy
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="to_year" style="font-weight: 500; color: #374151; font-size: 14px;">
                            Destination Year <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" name="to_year" id="to_year" required
                            style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 3px; font-size: 14px;">
                            <option value="">Select destination year</option>
                            @foreach ($years ?? [] as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        <div class="form-hint" style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                            Only current and future years can be selected as destination
                        </div>
                    </div>

                    <div class="mb-0">
                        <div class="form-check" style="display: flex; align-items: center; gap: 8px; padding: 12px; background: #f9fafb; border-radius: 3px;">
                            <input type="checkbox"
                                class="form-check-input"
                                name="adjust_amount"
                                id="adjust_amount"
                                value="1"
                                style="width: 18px; height: 18px; margin: 0; cursor: pointer;">
                            <div>
                                <label class="form-check-label" for="adjust_amount" style="font-size: 14px; color: #374151; cursor: pointer;">
                                    Apply percentage adjustment
                                </label>
                                <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">
                                    Increase or decrease all amounts by a percentage
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-0 mt-3" id="adjustmentContainer" style="display: none;">
                        <label class="form-label" for="adjustment_percentage" style="font-weight: 500; color: #374151; font-size: 14px;">
                            Adjustment Percentage
                        </label>
                        <div class="input-group">
                            <input type="number"
                                class="form-control"
                                name="adjustment_percentage"
                                id="adjustment_percentage"
                                placeholder="e.g., 5 for 5% increase, -5 for 5% decrease"
                                min="-100"
                                max="100"
                                step="0.01"
                                style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 3px 0 0 3px; font-size: 14px;">
                            <span class="input-group-text" style="background: #f3f4f6; border: 1px solid #d1d5db; border-left: none; color: #374151; font-weight: 500;">%</span>
                        </div>
                        <div class="form-hint" style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                            Enter positive value to increase, negative to decrease
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f3f4f6; padding: 16px 24px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        style="padding: 10px 20px; border-radius: 3px; font-size: 14px; font-weight: 500; background: #6c757d; border: none;">
                        <i class="bx bx-x"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success btn-loading"
                        style="padding: 10px 20px; border-radius: 3px; font-size: 14px; font-weight: 500; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; color: white;">
                        <span class="btn-text"><i class="fas fa-copy"></i> Copy Structures</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Copying...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    #copyStructuresModal .btn-loading.loading .btn-text {
        display: none;
    }

    #copyStructuresModal .btn-loading.loading .btn-spinner {
        display: inline-flex !important;
        align-items: center;
    }

    #copyStructuresModal .btn-loading:disabled {
        opacity: 0.7;
        cursor: not-allowed;
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
        const adjustCheckbox = document.getElementById('adjust_amount');
        const adjustmentContainer = document.getElementById('adjustmentContainer');
        const adjustmentInput = document.getElementById('adjustment_percentage');
        const copyForm = document.getElementById('copyStructuresForm');
        const fromYearSelect = document.getElementById('from_year');
        const toYearSelect = document.getElementById('to_year');

        // Toggle adjustment percentage visibility
        if (adjustCheckbox) {
            adjustCheckbox.addEventListener('change', function() {
                adjustmentContainer.style.display = this.checked ? 'block' : 'none';
                if (!this.checked) {
                    adjustmentInput.value = '';
                }
            });
        }

        // Prevent selecting same year for source and destination
        if (fromYearSelect && toYearSelect) {
            fromYearSelect.addEventListener('change', function() {
                const selectedValue = this.value;
                Array.from(toYearSelect.options).forEach(option => {
                    option.disabled = option.value === selectedValue && selectedValue !== '';
                });
            });

            toYearSelect.addEventListener('change', function() {
                const selectedValue = this.value;
                Array.from(fromYearSelect.options).forEach(option => {
                    option.disabled = option.value === selectedValue && selectedValue !== '';
                });
            });
        }

        // Form submission with loading state
        if (copyForm) {
            copyForm.addEventListener('submit', function(event) {
                const fromValue = fromYearSelect.value;
                const toValue = toYearSelect.value;

                if (fromValue === toValue) {
                    event.preventDefault();
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
