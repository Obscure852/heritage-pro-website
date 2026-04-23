@extends('layouts.crm')

@section('title', 'Leave Settings')
@section('crm_heading', 'Leave Settings')
@section('crm_subheading', 'Configure leave module behavior, attendance integration, and approval workflow.')

@section('content')
    <div class="crm-stack">
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Configuration</p>
                    <h2>Leave module settings</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('crm.leave.settings.update') }}" id="settings-form">
                @csrf
                @method('PUT')

                {{-- Attendance Integration --}}
                <h3 class="section-title">Attendance Integration</h3>
                @include('crm.partials.helper-text', [
                    'title' => 'Attendance Sync',
                    'content' => 'When enabled, approved leave will automatically create attendance records with the "Leave" code. Cancelled leave will remove those records.',
                ])

                <div class="crm-form-grid cols-3">
                    <div class="crm-field">
                        <label class="d-flex align-items-center gap-2">
                            <input type="hidden" name="attendance_integration_enabled" value="0">
                            <input type="checkbox" name="attendance_integration_enabled" value="1" @checked($settings->attendance_integration_enabled)>
                            Enable Attendance Integration
                        </label>
                    </div>
                    <div class="crm-field">
                        <label class="d-flex align-items-center gap-2">
                            <input type="hidden" name="auto_mark_attendance_on_approve" value="0">
                            <input type="checkbox" name="auto_mark_attendance_on_approve" value="1" @checked($settings->auto_mark_attendance_on_approve)>
                            Auto-mark on Approval
                        </label>
                    </div>
                    <div class="crm-field">
                        <label class="d-flex align-items-center gap-2">
                            <input type="hidden" name="auto_clear_attendance_on_cancel" value="0">
                            <input type="checkbox" name="auto_clear_attendance_on_cancel" value="1" @checked($settings->auto_clear_attendance_on_cancel)>
                            Auto-clear on Cancel
                        </label>
                    </div>
                </div>

                {{-- Approval Workflow --}}
                <h3 class="section-title">Approval Workflow</h3>
                <div class="crm-form-grid cols-3">
                    <div class="crm-field">
                        <label for="approval_reminder_hours">Reminder After (hours)</label>
                        <input type="number" id="approval_reminder_hours" name="approval_reminder_hours" value="{{ $settings->approval_reminder_hours }}" min="1" max="720">
                        <div class="crm-muted-copy" style="margin-top: 4px;">Send reminder to approver after this many hours.</div>
                    </div>
                    <div class="crm-field">
                        <label for="max_escalation_levels">Max Escalation Levels</label>
                        <input type="number" id="max_escalation_levels" name="max_escalation_levels" value="{{ $settings->max_escalation_levels }}" min="1" max="5">
                        <div class="crm-muted-copy" style="margin-top: 4px;">How many levels up the chain to escalate.</div>
                    </div>
                    <div class="crm-field">
                        <label for="escalation_after_hours">Escalate After (hours)</label>
                        <input type="number" id="escalation_after_hours" name="escalation_after_hours" value="{{ $settings->escalation_after_hours }}" min="1" max="720">
                        <div class="crm-muted-copy" style="margin-top: 4px;">Auto-escalate if no response within this time.</div>
                    </div>
                </div>

                {{-- Retroactive Leave --}}
                <h3 class="section-title">Retroactive Leave</h3>
                <div class="crm-form-grid cols-2">
                    <div class="crm-field">
                        <label class="d-flex align-items-center gap-2">
                            <input type="hidden" name="allow_retroactive_leave" value="0">
                            <input type="checkbox" name="allow_retroactive_leave" value="1" @checked($settings->allow_retroactive_leave)>
                            Allow Retroactive Leave Requests
                        </label>
                    </div>
                    <div class="crm-field">
                        <label for="retroactive_limit_days">Retroactive Limit (days)</label>
                        <input type="number" id="retroactive_limit_days" name="retroactive_limit_days" value="{{ $settings->retroactive_limit_days }}" min="1" max="90">
                    </div>
                </div>

                {{-- Balance Year --}}
                <h3 class="section-title">Balance Year</h3>
                <div class="crm-form-grid cols-1">
                    <div class="crm-field">
                        <label for="balance_year_start_month">Leave Year Start Month</label>
                        <select id="balance_year_start_month" name="balance_year_start_month">
                            @foreach (range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($settings->balance_year_start_month == $m)>
                                    {{ \Illuminate\Support\Carbon::create(2000, $m, 1)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                        <div class="crm-muted-copy" style="margin-top: 4px;">Month when leave balances reset and carry-over is calculated.</div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Save Settings</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('settings-form');
    form.addEventListener('submit', function() {
        const submitBtn = form.querySelector('button[type="submit"].btn-loading');
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        }
    });
});
</script>
@endpush
