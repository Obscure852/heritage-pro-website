{{-- Notifications Tab Content --}}
<div class="help-text">
    <div class="help-title">Notification Settings</div>
    <div class="help-content">
        Configure reminder notifications for upcoming leave and pending approval requests.
    </div>
</div>

<form id="notificationsForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            {{-- Leave Reminders Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-bell me-2"></i>Leave Reminders</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        @php
                            $reminderDays = $settings['leave_reminder_days_before']['days'] ?? $settings['leave_reminder_days_before'] ?? 3;
                        @endphp
                        <label class="form-label">Reminder Days Before Leave</label>
                        <input type="number"
                            class="form-control"
                            name="leave_reminder_days_before"
                            value="{{ $reminderDays }}"
                            min="1"
                            max="14"
                            placeholder="3">
                        <div class="form-hint">Number of days before leave start date to send staff a reminder notification.</div>
                    </div>
                </div>
            </div>

            {{-- Approval Reminders Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-user-clock me-2"></i>Approval Reminders</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        @php
                            $approvalHours = $settings['pending_approval_reminder_hours']['hours'] ?? $settings['pending_approval_reminder_hours'] ?? 24;
                        @endphp
                        <label class="form-label">Pending Approval Reminder Hours</label>
                        <input type="number"
                            class="form-control"
                            name="pending_approval_reminder_hours"
                            value="{{ $approvalHours }}"
                            min="1"
                            max="72"
                            placeholder="24">
                        <div class="form-hint">Hours after submission to remind approvers about pending leave requests.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-info-circle text-primary me-2"></i>Important Notes</h6>
                    <ul class="small text-muted mb-0">
                        <li class="mb-2">Leave reminders help staff prepare for their upcoming time off.</li>
                        <li class="mb-2">Approval reminders encourage timely processing of requests.</li>
                        <li>Notifications are sent via the school's configured notification channels (email/SMS).</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 pt-3 border-top">
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="fas fa-save me-2"></i>Save Notification Settings</span>
            <span class="btn-spinner d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Saving...
            </span>
        </button>
    </div>
</form>
