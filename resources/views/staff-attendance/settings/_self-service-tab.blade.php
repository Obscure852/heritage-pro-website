<div class="help-text">
    <div class="help-title">Self-Service Clock In/Out</div>
    <div class="help-content">
        Enable or disable the ability for staff to clock in/out via the web interface.
    </div>
</div>

<form id="selfServiceForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-user-clock me-2"></i>Self-Service Settings</h6>
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="self_clock_in_enabled" value="0">
                    <input class="form-check-input" type="checkbox" id="selfClockInEnabled"
                        name="self_clock_in_enabled" value="1"
                        {{ ($settings['self_clock_in_enabled']['enabled'] ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="selfClockInEnabled">
                        <strong>Enable Self Clock-In/Out</strong>
                    </label>
                </div>
                <div class="form-hint">
                    When enabled, staff can clock in and out from their dashboard.
                    Disabling this will hide the clock widget from the staff dashboard.
                </div>
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
