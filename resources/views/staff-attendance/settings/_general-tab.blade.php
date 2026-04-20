<div class="help-text">
    <div class="help-title">General Settings</div>
    <div class="help-content">
        Configure working hours, grace period, and hour thresholds for attendance calculation.
    </div>
</div>

<form id="generalSettingsForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-clock me-2"></i>Working Hours</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Work Start Time</label>
                        <input type="time" class="form-control" name="work_start_time"
                            value="{{ $settings['work_start_time']['time'] ?? '07:30' }}">
                        <div class="form-hint">Standard work day start time</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Work End Time</label>
                        <input type="time" class="form-control" name="work_end_time"
                            value="{{ $settings['work_end_time']['time'] ?? '16:30' }}">
                        <div class="form-hint">Standard work day end time</div>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-stopwatch me-2"></i>Grace Period</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Grace Period (Minutes)</label>
                        <input type="number" class="form-control" name="grace_period_minutes"
                            value="{{ $settings['grace_period_minutes']['minutes'] ?? 15 }}"
                            min="0" max="60">
                        <div class="form-hint">Minutes after start time before marking as late</div>
                    </div>
                </div>
            </div>

            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-hourglass-half me-2"></i>Hour Thresholds</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Half Day Hours</label>
                        <input type="number" class="form-control" name="half_day_hours"
                            value="{{ $settings['half_day_hours']['hours'] ?? 4 }}"
                            min="1" max="12" step="0.5">
                        <div class="form-hint">Minimum hours for half day</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Full Day Hours</label>
                        <input type="number" class="form-control" name="full_day_hours"
                            value="{{ $settings['full_day_hours']['hours'] ?? 8 }}"
                            min="1" max="24" step="0.5">
                        <div class="form-hint">Minimum hours for full day</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Overtime Threshold</label>
                        <input type="number" class="form-control" name="overtime_threshold_hours"
                            value="{{ $settings['overtime_threshold_hours']['hours'] ?? 8 }}"
                            min="1" max="24" step="0.5">
                        <div class="form-hint">Hours after which overtime counts</div>
                    </div>
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
