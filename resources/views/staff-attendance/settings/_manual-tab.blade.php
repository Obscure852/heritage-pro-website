<div class="help-text">
    <div class="help-title">Manual Attendance</div>
    <div class="help-content">
        Enable or disable manual attendance entry for HR administrators.
    </div>
</div>

<form id="manualAttendanceForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-edit me-2"></i>Manual Entry Settings</h6>
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="manual_attendance_enabled" value="0">
                    <input class="form-check-input" type="checkbox" id="manualAttendanceEnabled"
                        name="manual_attendance_enabled" value="1"
                        {{ ($settings['manual_attendance_enabled']['enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="manualAttendanceEnabled">
                        <strong>Enable Manual Attendance Entry</strong>
                    </label>
                </div>
                <div class="form-hint">
                    When enabled, HR administrators can manually enter attendance records.
                    This is useful for schools without biometric devices or when devices malfunction.
                </div>
            </div>

            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-th me-2"></i>Manual Register</h6>
                <p class="text-muted">Access the weekly attendance register for manual entry.</p>
                <a href="{{ route('staff-attendance.manual-register.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-calendar-alt me-2"></i>Open Manual Register
                </a>
            </div>

            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-tags me-2"></i>Attendance Codes</h6>
                <p class="text-muted">Manage attendance codes used for manual entry (Present, Absent, Leave, etc.).</p>
                <a href="{{ route('staff-attendance.codes.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-list me-2"></i>Manage Attendance Codes
                </a>
            </div>
        </div>
    </div>

    <div class="mt-4 pt-3 border-top">
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="fas fa-save me-2"></i>Save Settings</span>
            <span class="btn-spinner d-none">
                <span class="spinner-border spinner-border-sm me-2"></span>
                Saving...
            </span>
        </button>
    </div>
</form>
