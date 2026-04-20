<div class="help-text">
    <div class="help-title">Device Integration</div>
    <div class="help-content">
        Manage biometric device synchronization. Automatic sync runs every 10 minutes.
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="settings-section">
            <h6 class="section-title"><i class="fas fa-sync me-2"></i>Manual Sync</h6>

            @if($devices->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No active devices configured.
                    <a href="{{ route('staff-attendance.devices.index') }}">Configure devices</a> to enable sync.
                </div>
            @else
                <div class="mb-3">
                    <label class="form-label">Select Device</label>
                    <select class="form-select" id="syncDeviceSelect">
                        <option value="">All Devices</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->ip_address }})</option>
                        @endforeach
                    </select>
                    <div class="form-hint">Select a specific device or sync all devices</div>
                </div>

                <button type="button" class="btn btn-primary btn-loading" id="triggerSyncBtn">
                    <span class="btn-text"><i class="fas fa-sync me-2"></i>Trigger Manual Sync</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Syncing...
                    </span>
                </button>

                <div id="syncOutput" class="mt-3 d-none">
                    <pre class="bg-dark text-light p-3 rounded" style="max-height: 200px; overflow-y: auto;"></pre>
                </div>
            @endif
        </div>

        <div class="settings-section">
            <h6 class="section-title"><i class="fas fa-history me-2"></i>Sync History</h6>
            <p class="text-muted">View recent sync logs and troubleshoot sync issues.</p>
            <a href="{{ route('staff-attendance.sync-history.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list me-2"></i>View Sync History
            </a>
        </div>

        <div class="settings-section">
            <h6 class="section-title"><i class="fas fa-server me-2"></i>Device Management</h6>
            <p class="text-muted">Add, edit, or remove biometric devices.</p>
            <a href="{{ route('staff-attendance.devices.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-cog me-2"></i>Manage Devices
            </a>
        </div>
    </div>
</div>
