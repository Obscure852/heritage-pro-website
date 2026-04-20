@extends('layouts.master')

@section('title', 'Notification Preferences')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Content</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.notifications.index') }}">Notifications</a>
        @endslot
        @slot('title')
            Preferences
        @endslot
    @endcomponent

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Notification Preferences</h4>
            <p class="text-muted mb-0">Manage how you receive notifications</p>
        </div>
        </div>
    </div>

    <form action="{{ route('lms.notifications.preferences.save') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-bell me-2"></i>Notification Types</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Notification Type</th>
                                    <th class="text-center" style="width: 100px;">In-App</th>
                                    <th class="text-center" style="width: 100px;">Email</th>
                                    <th class="text-center" style="width: 100px;">Push</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notificationTypes as $type => $label)
                                    @php
                                        $pref = $preferences->firstWhere('type', $type);
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $label }}</strong>
                                            <div class="text-muted small">{{ $typeDescriptions[$type] ?? '' }}</div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="form-check d-flex justify-content-center">
                                                <input type="checkbox" name="preferences[{{ $type }}][in_app]" class="form-check-input"
                                                       value="1" {{ !$pref || $pref->in_app ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="form-check d-flex justify-content-center">
                                                <input type="checkbox" name="preferences[{{ $type }}][email]" class="form-check-input"
                                                       value="1" {{ !$pref || $pref->email ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="form-check d-flex justify-content-center">
                                                <input type="checkbox" name="preferences[{{ $type }}][push]" class="form-check-input"
                                                       value="1" {{ $pref && $pref->push ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Digest</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Email Frequency</label>
                            <select name="email_frequency" class="form-select">
                                <option value="immediate" {{ ($emailFrequency ?? 'immediate') === 'immediate' ? 'selected' : '' }}>
                                    Immediate - Send emails as events happen
                                </option>
                                <option value="daily" {{ ($emailFrequency ?? '') === 'daily' ? 'selected' : '' }}>
                                    Daily Digest - One email per day with all notifications
                                </option>
                                <option value="weekly" {{ ($emailFrequency ?? '') === 'weekly' ? 'selected' : '' }}>
                                    Weekly Digest - One email per week with all notifications
                                </option>
                                <option value="none" {{ ($emailFrequency ?? '') === 'none' ? 'selected' : '' }}>
                                    Never - Don't send email notifications
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-moon me-2"></i>Quiet Hours</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input type="checkbox" name="quiet_hours_enabled" id="quiet_hours_enabled" class="form-check-input"
                                   value="1" {{ ($quietHoursEnabled ?? false) ? 'checked' : '' }}>
                            <label for="quiet_hours_enabled" class="form-check-label">
                                Enable quiet hours (no notifications during this time)
                            </label>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="quiet_hours_start" class="form-control"
                                       value="{{ $quietHoursStart ?? '22:00' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Time</label>
                                <input type="time" name="quiet_hours_end" class="form-control"
                                       value="{{ $quietHoursEnd ?? '07:00' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>About Notifications</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">
                            <i class="fas fa-bell text-primary me-2"></i>
                            <strong>In-App:</strong> Notifications appear in the notification center.
                        </p>
                        <p class="small text-muted mb-2">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <strong>Email:</strong> Notifications are sent to your registered email.
                        </p>
                        <p class="small text-muted mb-0">
                            <i class="fas fa-mobile-alt text-primary me-2"></i>
                            <strong>Push:</strong> Browser push notifications (requires permission).
                        </p>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-1"></i>Save Preferences
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="resetToDefaults()">
                            <i class="fas fa-undo me-1"></i>Reset to Defaults
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function resetToDefaults() {
    if (confirm('Reset all notification preferences to defaults?')) {
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.checked = !cb.name.includes('[push]');
        });
        document.querySelector('[name="email_frequency"]').value = 'immediate';
        document.querySelector('[name="quiet_hours_enabled"]').checked = false;
    }
}
</script>
@endpush
@endsection
