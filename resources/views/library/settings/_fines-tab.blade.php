{{-- Fines & Penalties Tab Content --}}
@php $currency = $settings['library_currency']['code'] ?? 'BWP'; @endphp
<div class="help-text">
    <div class="help-title">Fines & Penalties</div>
    <div class="help-content">
        Configure the currency, fine rates for overdue items, the threshold that blocks borrowing, and when to declare items lost.
    </div>
</div>

<form id="finesForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            {{-- Currency Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-money-bill-wave me-2"></i>Currency</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="library_currency">Currency Code</label>
                        <input type="text"
                            class="form-control"
                            id="library_currency"
                            name="library_currency"
                            value="{{ $currency }}"
                            placeholder="e.g., BWP, USD, ZAR"
                            maxlength="10">
                        <div class="form-hint">Currency code used for all fines and pricing (e.g., BWP, USD, ZAR, GBP)</div>
                    </div>
                </div>
            </div>

            {{-- Fine Rate per Day Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-coins me-2"></i>Fine Rate per Day (<span class="currency-label">{{ $currency }}</span>)</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="fine_rate_student">Students</label>
                        <input type="number"
                            class="form-control"
                            id="fine_rate_student"
                            name="fine_rate_student"
                            value="{{ $settings['fine_rate_per_day']['student'] ?? 1.00 }}"
                            min="0"
                            max="100"
                            step="0.01">
                        <div class="form-hint">Amount charged per day overdue</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="fine_rate_staff">Staff</label>
                        <input type="number"
                            class="form-control"
                            id="fine_rate_staff"
                            name="fine_rate_staff"
                            value="{{ $settings['fine_rate_per_day']['staff'] ?? 2.00 }}"
                            min="0"
                            max="100"
                            step="0.01">
                        <div class="form-hint">Amount charged per day overdue</div>
                    </div>
                </div>
            </div>

            {{-- Fine Threshold Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-ban me-2"></i>Fine Threshold (<span class="currency-label">{{ $currency }}</span>)</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="fine_threshold">Threshold Amount</label>
                        <input type="number"
                            class="form-control"
                            id="fine_threshold"
                            name="fine_threshold"
                            value="{{ $settings['fine_threshold']['amount'] ?? 50.00 }}"
                            min="0"
                            max="10000"
                            step="0.01">
                        <div class="form-hint">Borrowing is blocked when unpaid fines exceed this amount</div>
                    </div>
                </div>
            </div>

            {{-- Lost Item Fine Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-book-dead me-2"></i>Lost Item Fine (<span class="currency-label">{{ $currency }}</span>)</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="lost_book_fine_amount">Fixed Amount</label>
                        <input type="number"
                            class="form-control"
                            id="lost_book_fine_amount"
                            name="lost_book_fine_amount"
                            value="{{ $settings['lost_book_fine']['amount'] ?? 100.00 }}"
                            min="0"
                            max="10000"
                            step="0.01">
                        <div class="form-hint">Fixed fine for lost items when no replacement cost (item price) is available</div>
                    </div>
                </div>
            </div>

            {{-- Lost Item Period Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-exclamation-triangle me-2"></i>Lost Item Period (days)</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="lost_book_period_student">Students</label>
                        <input type="number"
                            class="form-control"
                            id="lost_book_period_student"
                            name="lost_book_period_student"
                            value="{{ $settings['lost_book_period']['student'] ?? 60 }}"
                            min="1"
                            max="365">
                        <div class="form-hint">Days overdue before an item is automatically declared lost</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="lost_book_period_staff">Staff</label>
                        <input type="number"
                            class="form-control"
                            id="lost_book_period_staff"
                            name="lost_book_period_staff"
                            value="{{ $settings['lost_book_period']['staff'] ?? 60 }}"
                            min="1"
                            max="365">
                        <div class="form-hint">Days overdue before an item is automatically declared lost</div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save"></i> Save Fine Settings</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
        </div>
    </div>
</form>
