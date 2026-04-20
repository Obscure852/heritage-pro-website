{{-- Section 1: Storage Quotas --}}
<div class="settings-section">
    <div class="section-title"><i class="fas fa-hdd"></i> Storage Quotas</div>
    <div class="help-text">
        <div class="help-content">Configure default storage quotas for users. Changes apply to newly created quotas.</div>
    </div>
    <form class="settings-form" action="{{ route('documents.settings.update', 'quotas') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="default_mb">Default Quota (MB)</label>
                <input type="number" class="form-control" id="default_mb" name="default_mb" value="{{ $settingsQuotas['default_mb'] }}" min="50" max="50000" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="admin_mb">Admin Quota (MB)</label>
                <input type="number" class="form-control" id="admin_mb" name="admin_mb" value="{{ $settingsQuotas['admin_mb'] }}" min="100" max="100000" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="warning_threshold_percent">Warning Threshold (%)</label>
                <input type="number" class="form-control" id="warning_threshold_percent" name="warning_threshold_percent" value="{{ $settingsQuotas['warning_threshold_percent'] }}" min="50" max="99" required>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-loading">
                <span class="btn-text"><i class="fas fa-save"></i> Save Quotas</span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Saving...
                </span>
            </button>
        </div>
    </form>
</div>

{{-- Section 2: Retention --}}
<div class="settings-section">
    <div class="section-title"><i class="fas fa-calendar-alt"></i> Document Retention</div>
    <div class="help-text">
        <div class="help-content">Set default retention periods and grace periods for document lifecycle.</div>
    </div>
    <form class="settings-form" action="{{ route('documents.settings.update', 'retention') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label" for="default_days">Default Retention (days)</label>
                <input type="number" class="form-control" id="default_days" name="default_days" value="{{ $settingsRetention['default_days'] }}" min="30" max="36500" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="grace_period_days">Grace Period (days)</label>
                <input type="number" class="form-control" id="grace_period_days" name="grace_period_days" value="{{ $settingsRetention['grace_period_days'] }}" min="1" max="365" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="trash_retention_days">Trash Retention (days)</label>
                <input type="number" class="form-control" id="trash_retention_days" name="trash_retention_days" value="{{ $settingsRetention['trash_retention_days'] }}" min="1" max="365" required>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-loading">
                <span class="btn-text"><i class="fas fa-save"></i> Save Retention</span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Saving...
                </span>
            </button>
        </div>
    </form>
</div>

{{-- Section 3: Upload Limits --}}
<div class="settings-section">
    <div class="section-title"><i class="fas fa-upload"></i> Upload Limits</div>
    <div class="help-text">
        <div class="help-content">Configure maximum file size and allowed file types.</div>
    </div>
    <form class="settings-form" action="{{ route('documents.settings.update', 'uploads') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label" for="max_file_size_mb">Max File Size (MB)</label>
            <input type="number" class="form-control" id="max_file_size_mb" name="max_file_size_mb" value="{{ $uploads['max_file_size_mb'] }}" min="1" max="500" required style="max-width: 200px;">
        </div>
        <div class="mb-3">
            <label class="form-label">Allowed Extensions</label>
            <div class="extension-grid">
                @foreach($allExtensions as $ext)
                <label>
                    <input type="checkbox" name="allowed_extensions[]" value="{{ $ext }}" {{ in_array($ext, $uploads['allowed_extensions']) ? 'checked' : '' }}>
                    .{{ $ext }}
                </label>
                @endforeach
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-loading">
                <span class="btn-text"><i class="fas fa-save"></i> Save Upload Limits</span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Saving...
                </span>
            </button>
        </div>
    </form>
</div>

{{-- Section 4: Approval Workflow --}}
<div class="settings-section">
    <div class="section-title"><i class="fas fa-check-circle"></i> Approval Workflow</div>
    <div class="help-text">
        <div class="help-content">Configure whether documents require approval before publication.</div>
    </div>
    <form class="settings-form" action="{{ route('documents.settings.update', 'approval') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="require_approval" value="0">
                    <input class="form-check-input" type="checkbox" id="require_approval" name="require_approval" value="1" {{ $approval['require_approval'] ? 'checked' : '' }}>
                    <label class="form-check-label" for="require_approval">Require Approval Before Publishing</label>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="review_deadline_days">Review Deadline (days)</label>
                <input type="number" class="form-control" id="review_deadline_days" name="review_deadline_days" value="{{ $approval['review_deadline_days'] }}" min="1" max="90" required style="max-width: 200px;">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-loading">
                <span class="btn-text"><i class="fas fa-save"></i> Save Workflow</span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Saving...
                </span>
            </button>
        </div>
    </form>
</div>
