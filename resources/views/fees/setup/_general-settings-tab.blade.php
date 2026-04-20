{{-- General Settings Tab Content --}}
<div class="help-text">
    <div class="help-title">General Fee Settings</div>
    <div class="help-content">
        Configure general settings for the fee administration module including currency, receipt numbering,
        late fee policies, and notification preferences.
    </div>
</div>

<form id="generalSettingsForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            {{-- Currency Settings Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-coins me-2"></i>Currency Settings</h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Currency Symbol</label>
                        <input type="text"
                            class="form-control"
                            name="currency_symbol"
                            value="{{ $settings['currency_symbol'] ?? 'P' }}"
                            placeholder="e.g., P, $, R"
                            maxlength="5">
                        <div class="form-hint">Symbol displayed before/after amounts (e.g., P, $, R)</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Currency Code</label>
                        <input type="text"
                            class="form-control"
                            name="currency_code"
                            value="{{ $settings['currency_code'] ?? 'BWP' }}"
                            placeholder="e.g., BWP, USD, ZAR"
                            maxlength="3"
                            style="text-transform: uppercase;">
                        <div class="form-hint">ISO currency code (e.g., BWP, USD, ZAR)</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Currency Position</label>
                        <select class="form-select" name="currency_position">
                            <option value="before" {{ ($settings['currency_position'] ?? 'before') === 'before' ? 'selected' : '' }}>Before Amount (P 100.00)</option>
                            <option value="after" {{ ($settings['currency_position'] ?? '') === 'after' ? 'selected' : '' }}>After Amount (100.00 P)</option>
                        </select>
                        <div class="form-hint">Where to display the currency symbol</div>
                    </div>
                </div>
            </div>

            {{-- Receipt Settings Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-receipt me-2"></i>Receipt Settings</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Receipt Number Prefix</label>
                        <input type="text"
                            class="form-control"
                            name="receipt_prefix"
                            value="{{ $settings['receipt_prefix'] ?? 'RCP' }}"
                            placeholder="e.g., RCP"
                            maxlength="10">
                        <div class="form-hint">Prefix added before receipt numbers (e.g., RCP-001)</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Receipt Number Start</label>
                        <input type="number"
                            class="form-control"
                            name="receipt_number_start"
                            value="{{ $settings['receipt_number_start'] ?? 1 }}"
                            placeholder="1"
                            min="1">
                        <div class="form-hint">Starting number for receipts (only applies to new sequences)</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Receipt Footer Text</label>
                        <textarea class="form-control"
                            name="receipt_footer"
                            rows="2"
                            placeholder="e.g., Thank you for your payment">{{ $settings['receipt_footer'] ?? '' }}</textarea>
                        <div class="form-hint">Text displayed at the bottom of printed receipts</div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mt-4">
                            <input type="checkbox"
                                class="form-check-input"
                                name="auto_generate_receipt"
                                id="autoGenerateReceipt"
                                value="1"
                                {{ ($settings['auto_generate_receipt'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="autoGenerateReceipt">
                                Auto-generate receipt on payment
                            </label>
                            <div class="form-hint">Automatically create receipt when payment is recorded</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Late Fee Settings Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-clock me-2"></i>Late Fee Settings</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox"
                                class="form-check-input"
                                name="enable_late_fees"
                                id="enableLateFees"
                                value="1"
                                {{ ($settings['enable_late_fees'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="enableLateFees">
                                Enable Late Fee Charges
                            </label>
                            <div class="form-hint">Automatically apply late fees after due date</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Grace Period (Days)</label>
                        <input type="number"
                            class="form-control"
                            name="late_fee_grace_period"
                            value="{{ $settings['late_fee_grace_period'] ?? 7 }}"
                            placeholder="7"
                            min="0"
                            max="30">
                        <div class="form-hint">Days after due date before late fee is applied</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Late Fee Type</label>
                        <select class="form-select" name="late_fee_type">
                            <option value="fixed" {{ ($settings['late_fee_type'] ?? 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            <option value="percentage" {{ ($settings['late_fee_type'] ?? '') === 'percentage' ? 'selected' : '' }}>Percentage of Outstanding</option>
                        </select>
                        <div class="form-hint">How the late fee is calculated</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Late Fee Amount/Rate</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ get_currency_symbol() }}</span>
                            <input type="number"
                                class="form-control"
                                name="late_fee_amount"
                                value="{{ $settings['late_fee_amount'] ?? 50 }}"
                                placeholder="50"
                                min="0"
                                step="0.01">
                        </div>
                        <div class="form-hint">Amount or percentage (%) depending on type selected</div>
                    </div>
                </div>
            </div>

            {{-- Payment Plan Settings Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-calendar-alt me-2"></i>Payment Plan Settings</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox"
                                class="form-check-input"
                                name="enable_payment_plans"
                                id="enablePaymentPlans"
                                value="1"
                                {{ ($settings['enable_payment_plans'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="enablePaymentPlans">
                                Enable Payment Plans
                            </label>
                            <div class="form-hint">Allow creating installment payment plans for invoices</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Default Plan Frequency</label>
                        <select class="form-select" name="default_plan_frequency">
                            <option value="monthly" {{ ($settings['default_plan_frequency'] ?? 'termly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="termly" {{ ($settings['default_plan_frequency'] ?? 'termly') === 'termly' ? 'selected' : '' }}>Termly (3 Installments)</option>
                            <option value="custom" {{ ($settings['default_plan_frequency'] ?? '') === 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                        <div class="form-hint">Default frequency when creating payment plans</div>
                    </div>
                </div>
            </div>

            {{-- Notification Settings Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-bell me-2"></i>Notification Settings</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox"
                                class="form-check-input"
                                name="notify_on_payment"
                                id="notifyOnPayment"
                                value="1"
                                {{ ($settings['notify_on_payment'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notifyOnPayment">
                                Notify on Payment Received
                            </label>
                            <div class="form-hint">Send notification when payment is recorded</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox"
                                class="form-check-input"
                                name="notify_on_overdue"
                                id="notifyOnOverdue"
                                value="1"
                                {{ ($settings['notify_on_overdue'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="notifyOnOverdue">
                                Notify on Overdue Fees
                            </label>
                            <div class="form-hint">Send notification when fees become overdue</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Reminder Days Before Due</label>
                        <input type="number"
                            class="form-control"
                            name="reminder_days_before"
                            value="{{ $settings['reminder_days_before'] ?? 3 }}"
                            placeholder="3"
                            min="1"
                            max="14">
                        <div class="form-hint">Days before due date to send payment reminder</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Admin Notification Email</label>
                        <input type="email"
                            class="form-control"
                            name="admin_notification_email"
                            value="{{ $settings['admin_notification_email'] ?? '' }}"
                            placeholder="finance@school.edu">
                        <div class="form-hint">Email address for admin fee notifications</div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Overdue Reminder Intervals (Days)</label>
                        @php
                            $overdueIntervals = $settings['overdue_reminder_intervals'] ?? [7, 14, 30];
                            if (is_string($overdueIntervals)) {
                                $overdueIntervals = json_decode($overdueIntervals, true) ?? [7, 14, 30];
                            }
                        @endphp
                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">1st</span>
                                    <input type="number"
                                        class="form-control"
                                        name="overdue_reminder_intervals[]"
                                        value="{{ $overdueIntervals[0] ?? 7 }}"
                                        min="1"
                                        max="90">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">2nd</span>
                                    <input type="number"
                                        class="form-control"
                                        name="overdue_reminder_intervals[]"
                                        value="{{ $overdueIntervals[1] ?? 14 }}"
                                        min="1"
                                        max="90">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text">3rd</span>
                                    <input type="number"
                                        class="form-control"
                                        name="overdue_reminder_intervals[]"
                                        value="{{ $overdueIntervals[2] ?? 30 }}"
                                        min="1"
                                        max="90">
                                    <span class="input-group-text">days</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-hint">Days after due date to send overdue reminders (up to 3 intervals)</div>
                    </div>
                </div>
            </div>

            {{-- Invoice Settings Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-file-invoice me-2"></i>Invoice Settings</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Invoice Number Prefix</label>
                        <input type="text"
                            class="form-control"
                            name="invoice_prefix"
                            value="{{ $settings['invoice_prefix'] ?? 'INV' }}"
                            placeholder="e.g., INV"
                            maxlength="10">
                        <div class="form-hint">Prefix added before invoice numbers (e.g., INV-001)</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Default Payment Terms (Days)</label>
                        <input type="number"
                            class="form-control"
                            name="default_payment_terms"
                            value="{{ $settings['default_payment_terms'] ?? 30 }}"
                            placeholder="30"
                            min="1"
                            max="90">
                        <div class="form-hint">Default number of days before invoice is due</div>
                    </div>
                    @php
                        $maxLookbackYears = \App\Services\Fee\BalanceService::getMaxLookbackYears();
                    @endphp
                    <div class="col-md-6">
                        <label class="form-label">Carryover Lookback Years</label>
                        <input type="number"
                            class="form-control"
                            name="carryover_lookback_years"
                            value="{{ $settings['carryover_lookback_years'] ?? settings('fee.carryover_lookback_years', 3) }}"
                            placeholder="3"
                            min="1"
                            max="{{ $maxLookbackYears }}">
                        <div class="form-hint">Number of years to check for outstanding balances (max {{ $maxLookbackYears }} based on system data)</div>
                    </div>
                    <div class="col-md-6">
                        {{-- Empty column for layout balance --}}
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Invoice Notes</label>
                        <textarea class="form-control"
                            name="invoice_notes"
                            rows="2"
                            placeholder="e.g., Payment is due by the date indicated above">{{ $settings['invoice_notes'] ?? '' }}</textarea>
                        <div class="form-hint">Default notes displayed on invoices</div>
                    </div>
                </div>
            </div>

            {{-- Year Locking Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-lock me-2"></i>Year Locking</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox"
                                class="form-check-input"
                                name="auto_lock_past_years"
                                id="autoLockPastYears"
                                value="1"
                                {{ ($settings['auto_lock_past_years'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="autoLockPastYears">
                                Auto-lock Past Years
                            </label>
                            <div class="form-hint">Automatically prevent modifications to fee data from previous years</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Lock Years Up To</label>
                        <select class="form-select" name="locked_until_year">
                            <option value="">No specific lock</option>
                            @php
                                $currentYear = (int) date('Y');
                            @endphp
                            @for ($y = $currentYear - 5; $y < $currentYear; $y++)
                                <option value="{{ $y }}" {{ ($settings['locked_until_year'] ?? '') == $y ? 'selected' : '' }}>
                                    {{ $y }} and earlier
                                </option>
                            @endfor
                        </select>
                        <div class="form-hint">Manually lock all fee data up to and including this year</div>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-warning mb-0" style="font-size: 13px;">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Note:</strong> When year locking is enabled, fee structures, invoices, and payments for locked years cannot be modified.
                            Only administrators can override this lock in emergency situations.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary btn-loading">
                    <span class="btn-text"><i class="fas fa-save me-1"></i> Save Settings</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>Saving...
                    </span>
                </button>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="help-text" style="background: #fef3c7; border-left-color: #f59e0b; position: sticky; top: 20px;">
                <div class="help-title" style="color: #92400e;"><i class="fas fa-lightbulb me-1"></i> Important Notes</div>
                <div class="help-content" style="color: #92400e;">
                    <ul class="mb-0 ps-3" style="font-size: 12px;">
                        <li class="mb-2">Receipt and invoice prefixes help identify documents</li>
                        <li class="mb-2">Late fees only apply to new invoices after enabling</li>
                        <li class="mb-2">Email notifications require SMTP configuration</li>
                        <li class="mb-2">Changes to numbering don't affect existing documents</li>
                        <li>Test notification settings before going live</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
