{{-- Request Rules Tab Content --}}
<div class="help-text">
    <div class="help-title">Leave Request Rules</div>
    <div class="help-content">
        Configure rules for leave request submission, approval requirements, and automatic cancellation policies.
    </div>
</div>

<form id="requestRulesForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            {{-- Approval Settings Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-check-circle me-2"></i>Approval Settings</h6>

                <div class="row g-3">
                    <div class="col-12">
                        @php
                            $approvalRequired = $settings['leave_request_approval_required']['required'] ?? $settings['leave_request_approval_required'] ?? true;
                        @endphp
                        <div class="form-check">
                            <input type="checkbox"
                                class="form-check-input"
                                name="leave_request_approval_required"
                                id="leaveRequestApprovalRequired"
                                value="1"
                                {{ $approvalRequired ? 'checked' : '' }}>
                            <label class="form-check-label" for="leaveRequestApprovalRequired">
                                Require Approval for Leave Requests
                            </label>
                            <div class="form-hint">When enabled, requests route to HOD/Manager for approval. Disable for auto-approval.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Backdated Requests Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-history me-2"></i>Backdated Requests</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        @php
                            $backdatedSettings = $settings['allow_backdated_requests'] ?? [];
                            $backdatedAllowed = is_array($backdatedSettings) ? ($backdatedSettings['allowed'] ?? false) : $backdatedSettings;
                            $backdatedMaxDays = is_array($backdatedSettings) ? ($backdatedSettings['max_days'] ?? 7) : 7;
                        @endphp
                        <div class="form-check">
                            <input type="checkbox"
                                class="form-check-input"
                                name="allow_backdated_requests"
                                id="allowBackdatedRequests"
                                value="1"
                                {{ $backdatedAllowed ? 'checked' : '' }}>
                            <label class="form-check-label" for="allowBackdatedRequests">
                                Allow Backdated Requests
                            </label>
                            <div class="form-hint">Allow staff to submit leave requests for past dates.</div>
                        </div>
                    </div>
                    <div class="col-md-6 conditional-field" id="backdatedMaxDaysContainer" style="{{ $backdatedAllowed ? '' : 'display: none;' }}">
                        <label class="form-label">Maximum Days Back</label>
                        <input type="number"
                            class="form-control"
                            name="backdated_max_days"
                            value="{{ $backdatedMaxDays }}"
                            min="1"
                            max="30"
                            placeholder="7">
                        <div class="form-hint">Maximum number of days in the past a request can be submitted for.</div>
                    </div>
                </div>
            </div>

            {{-- Negative Balance Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-balance-scale me-2"></i>Negative Balance</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        @php
                            $maxNegative = $settings['max_negative_balance']['days'] ?? $settings['max_negative_balance'] ?? 5;
                        @endphp
                        <label class="form-label">Maximum Negative Balance Days</label>
                        <input type="number"
                            class="form-control"
                            name="max_negative_balance"
                            value="{{ $maxNegative }}"
                            min="0"
                            max="30"
                            placeholder="5">
                        <div class="form-hint">Maximum days leave balance can go negative for types that allow negative balance.</div>
                    </div>
                </div>
            </div>

            {{-- Auto-Cancel Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-times-circle me-2"></i>Auto-Cancel Pending Requests</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        @php
                            $autoCancelSettings = $settings['auto_cancel_pending_after_days'] ?? [];
                            $autoCancelEnabled = is_array($autoCancelSettings) ? ($autoCancelSettings['enabled'] ?? false) : false;
                            $autoCancelDays = is_array($autoCancelSettings) ? ($autoCancelSettings['days'] ?? 30) : 30;
                        @endphp
                        <div class="form-check">
                            <input type="checkbox"
                                class="form-check-input"
                                name="auto_cancel_pending_enabled"
                                id="autoCancelPendingEnabled"
                                value="1"
                                {{ $autoCancelEnabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="autoCancelPendingEnabled">
                                Auto-Cancel Stale Pending Requests
                            </label>
                            <div class="form-hint">Automatically cancel requests that remain pending too long.</div>
                        </div>
                    </div>
                    <div class="col-md-6 conditional-field" id="autoCancelDaysContainer" style="{{ $autoCancelEnabled ? '' : 'display: none;' }}">
                        <label class="form-label">Days Before Auto-Cancel</label>
                        <input type="number"
                            class="form-control"
                            name="auto_cancel_pending_days"
                            value="{{ $autoCancelDays }}"
                            min="1"
                            max="90"
                            placeholder="30">
                        <div class="form-hint">Number of days after which unapproved requests are automatically cancelled.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-info-circle text-primary me-2"></i>Important Notes</h6>
                    <ul class="small text-muted mb-0">
                        <li class="mb-2">Disabling approval allows staff leave to be immediately approved upon submission.</li>
                        <li class="mb-2">Backdated requests are useful for emergency leave reporting.</li>
                        <li class="mb-2">Negative balance should be used sparingly to prevent leave abuse.</li>
                        <li>Auto-cancel helps keep the pending queue clean.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 pt-3 border-top">
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="fas fa-save me-2"></i>Save Request Rules</span>
            <span class="btn-spinner d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Saving...
            </span>
        </button>
    </div>
</form>
