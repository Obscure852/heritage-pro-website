{{-- Borrowing Rules Tab Content --}}
<div class="help-text">
    <div class="help-title">Borrowing Rules</div>
    <div class="help-content">
        Configure loan periods, maximum books, and renewal limits for each borrower type. Students and staff can have different rules.
    </div>
</div>

<form id="borrowingRulesForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            {{-- Loan Period Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-clock me-2"></i>Loan Period (days)</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="loan_period_student">Students</label>
                        <input type="number"
                            class="form-control"
                            id="loan_period_student"
                            name="loan_period_student"
                            value="{{ $settings['loan_period']['student'] ?? 14 }}"
                            min="1"
                            max="365">
                        <div class="form-hint">Number of days before a loan is due</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="loan_period_staff">Staff</label>
                        <input type="number"
                            class="form-control"
                            id="loan_period_staff"
                            name="loan_period_staff"
                            value="{{ $settings['loan_period']['staff'] ?? 30 }}"
                            min="1"
                            max="365">
                        <div class="form-hint">Number of days before a loan is due</div>
                    </div>
                </div>
            </div>

            {{-- Maximum Books Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-layer-group me-2"></i>Maximum Books</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="max_books_student">Students</label>
                        <input type="number"
                            class="form-control"
                            id="max_books_student"
                            name="max_books_student"
                            value="{{ $settings['max_books']['student'] ?? 3 }}"
                            min="1"
                            max="50">
                        <div class="form-hint">Maximum number of books that can be borrowed simultaneously</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="max_books_staff">Staff</label>
                        <input type="number"
                            class="form-control"
                            id="max_books_staff"
                            name="max_books_staff"
                            value="{{ $settings['max_books']['staff'] ?? 5 }}"
                            min="1"
                            max="50">
                        <div class="form-hint">Maximum number of books that can be borrowed simultaneously</div>
                    </div>
                </div>
            </div>

            {{-- Maximum Renewals Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-redo me-2"></i>Maximum Renewals</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="max_renewals_student">Students</label>
                        <input type="number"
                            class="form-control"
                            id="max_renewals_student"
                            name="max_renewals_student"
                            value="{{ $settings['max_renewals']['student'] ?? 1 }}"
                            min="0"
                            max="10">
                        <div class="form-hint">How many times a loan can be renewed before it must be returned</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="max_renewals_staff">Staff</label>
                        <input type="number"
                            class="form-control"
                            id="max_renewals_staff"
                            name="max_renewals_staff"
                            value="{{ $settings['max_renewals']['staff'] ?? 2 }}"
                            min="0"
                            max="10">
                        <div class="form-hint">How many times a loan can be renewed before it must be returned</div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Save Borrowing Rules</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </div>
    </div>
</form>
