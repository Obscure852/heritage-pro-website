<div class="modal fade" id="adjustmentModal" tabindex="-1" aria-labelledby="adjustmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adjustmentModalLabel">
                    Adjust Balance - {{ $balance->leaveType->name ?? 'Leave' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="adjustment-form" action="{{ route('leave.balances.adjust', $balance) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <small>
                            <strong>Current Available Balance:</strong>
                            <span id="current-available">{{ number_format($balance->available, 1) }}</span> days
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adjustment Type <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjustment_type" id="type_credit" value="credit" required>
                                <label class="form-check-label" for="type_credit">
                                    <span class="badge bg-success">Credit</span>
                                    <small class="text-muted d-block">Add days</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjustment_type" id="type_debit" value="debit">
                                <label class="form-check-label" for="type_debit">
                                    <span class="badge bg-danger">Debit</span>
                                    <small class="text-muted d-block">Remove days</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="adjustment_type" id="type_correction" value="correction">
                                <label class="form-check-label" for="type_correction">
                                    <span class="badge bg-warning text-dark">Correction</span>
                                    <small class="text-muted d-block">Fix error</small>
                                </label>
                            </div>
                        </div>
                        <div class="invalid-feedback" id="adjustment_type-error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="days" class="form-label fw-semibold">Days <span class="text-danger">*</span></label>
                        <input type="number"
                               class="form-control"
                               id="days"
                               name="days"
                               step="0.5"
                               min="0.5"
                               max="365"
                               placeholder="Enter number of days"
                               required>
                        <div class="form-text">Minimum 0.5 days, maximum 365 days</div>
                        <div class="invalid-feedback" id="days-error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control"
                                  id="reason"
                                  name="reason"
                                  rows="3"
                                  minlength="10"
                                  maxlength="500"
                                  placeholder="Provide a detailed reason for this adjustment (e.g., 'Correcting initial allocation error', 'Additional leave granted per management approval dated...')"
                                  required></textarea>
                        <div class="form-text">
                            <span id="reason-count">0</span>/500 characters (minimum 10)
                        </div>
                        <div class="invalid-feedback" id="reason-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Save Adjustment</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .btn-loading.loading .btn-text {
        display: none;
    }

    .btn-loading.loading .btn-spinner {
        display: inline-flex !important;
        align-items: center;
    }

    .btn-loading:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    #adjustmentModal .form-check-label .badge {
        font-size: 12px;
        padding: 4px 8px;
    }

    #adjustmentModal .form-check {
        padding: 10px 15px;
        background: #f9fafb;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
        flex: 1;
    }

    #adjustmentModal .form-check:has(input:checked) {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    #adjustmentModal .form-check-input {
        margin-top: 4px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('adjustment-form');
        const reasonTextarea = document.getElementById('reason');
        const reasonCount = document.getElementById('reason-count');

        // Character counter for reason
        if (reasonTextarea && reasonCount) {
            reasonTextarea.addEventListener('input', function() {
                reasonCount.textContent = this.value.length;
            });
        }

        // Clear errors when modal opens
        const modal = document.getElementById('adjustmentModal');
        if (modal) {
            modal.addEventListener('show.bs.modal', function() {
                clearErrors();
                form.reset();
                if (reasonCount) reasonCount.textContent = '0';
            });
        }

        // Form submit handler with btn-loading
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"].btn-loading');

                // Trigger loading state
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }

                clearErrors();

                // AJAX submission
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        modalInstance.hide();

                        // Show success message and reload
                        if (window.showAlert) {
                            window.showAlert('success', data.message);
                        }

                        // Reload page to show updated balance and history
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    } else {
                        // Show validation errors
                        if (data.errors) {
                            displayErrors(data.errors);
                        } else if (data.message) {
                            // Show general error
                            displayGeneralError(data.message);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    displayGeneralError('An error occurred while saving the adjustment.');
                })
                .finally(() => {
                    // Reset button state
                    if (submitBtn) {
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                    }
                });
            });
        }

        function clearErrors() {
            const errorElements = form.querySelectorAll('.invalid-feedback');
            errorElements.forEach(el => {
                el.textContent = '';
                el.style.display = 'none';
            });

            const invalidInputs = form.querySelectorAll('.is-invalid');
            invalidInputs.forEach(el => el.classList.remove('is-invalid'));
        }

        function displayErrors(errors) {
            for (const field in errors) {
                const errorElement = document.getElementById(field + '-error');
                const inputElement = form.querySelector(`[name="${field}"]`);

                if (errorElement) {
                    errorElement.textContent = errors[field][0];
                    errorElement.style.display = 'block';
                }

                if (inputElement) {
                    inputElement.classList.add('is-invalid');
                }
            }
        }

        function displayGeneralError(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = `
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            form.querySelector('.modal-body').prepend(alertDiv);
        }
    });
</script>
