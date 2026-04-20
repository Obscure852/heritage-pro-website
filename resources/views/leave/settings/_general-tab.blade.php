{{-- General Settings Tab Content --}}
<div class="help-text">
    <div class="help-title">General Leave Settings</div>
    <div class="help-content">
        Configure the leave year cycle, weekend days excluded from leave calculations, and default settings for new leave types.
    </div>
</div>

<form id="generalSettingsForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            {{-- Leave Year Settings Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-calendar-alt me-2"></i>Leave Year Settings</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Leave Year Start Month</label>
                        <select class="form-select" name="leave_year_start_month">
                            @php
                                $months = [
                                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                ];
                                $currentMonth = $settings['leave_year_start_month']['month'] ?? $settings['leave_year_start_month'] ?? 1;
                            @endphp
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ $currentMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="form-hint">Month when leave entitlements reset. Common choices: January or April (fiscal year).</div>
                    </div>
                </div>
            </div>

            {{-- Weekend Configuration Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-calendar-week me-2"></i>Weekend Configuration</h6>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Weekend Days</label>
                        <div class="weekend-checkbox-group">
                            @php
                                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                $weekendDays = $settings['weekend_days']['days'] ?? $settings['weekend_days'] ?? [0, 6];
                                if (!is_array($weekendDays)) $weekendDays = [0, 6];
                            @endphp
                            @foreach($days as $index => $day)
                                <div class="weekend-checkbox-item">
                                    <input type="checkbox"
                                        class="form-check-input"
                                        name="weekend_days[]"
                                        id="weekend_{{ $index }}"
                                        value="{{ $index }}"
                                        {{ in_array($index, $weekendDays) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="weekend_{{ $index }}">{{ $day }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-hint">Days excluded from leave calculations. Most schools use Saturday and Sunday.</div>
                    </div>
                </div>
            </div>

            {{-- Default Leave Type Settings Section --}}
            <div class="settings-section">
                <h6 class="section-title"><i class="fas fa-sliders-h me-2"></i>Default Leave Type Settings</h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Default Balance Mode</label>
                        @php
                            $balanceMode = $settings['default_balance_mode']['mode'] ?? $settings['default_balance_mode'] ?? 'allocation';
                        @endphp
                        <select class="form-select" name="default_balance_mode">
                            <option value="allocation" {{ $balanceMode === 'allocation' ? 'selected' : '' }}>Allocation (Annual Entitlement)</option>
                            <option value="accrual" {{ $balanceMode === 'accrual' ? 'selected' : '' }}>Accrual (Monthly Accumulation)</option>
                        </select>
                        <div class="form-hint">How leave balance is assigned to staff for new leave types.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Default Carry-Over Mode</label>
                        @php
                            $carryMode = $settings['default_carry_over_mode']['mode'] ?? $settings['default_carry_over_mode'] ?? 'none';
                        @endphp
                        <select class="form-select" name="default_carry_over_mode">
                            <option value="none" {{ $carryMode === 'none' ? 'selected' : '' }}>None (Use It or Lose It)</option>
                            <option value="limited" {{ $carryMode === 'limited' ? 'selected' : '' }}>Limited (Up to Limit)</option>
                            <option value="full" {{ $carryMode === 'full' ? 'selected' : '' }}>Full (All Unused)</option>
                        </select>
                        <div class="form-hint">How unused leave is handled at year-end for new leave types.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-info-circle text-primary me-2"></i>Important Notes</h6>
                    <ul class="small text-muted mb-0">
                        <li class="mb-2">Changing the leave year start month affects when balances reset for all staff.</li>
                        <li class="mb-2">Weekend days are excluded when calculating leave duration.</li>
                        <li>Default settings only apply to newly created leave types. Existing types keep their configured values.</li>
                    </ul>
                </div>
            </div>

            {{-- Sync Balances Card --}}
            @php
                use App\Helpers\TermHelper;
                $currentTerm = TermHelper::getCurrentTerm();
                $currentLeaveYear = $currentTerm ? $currentTerm->year : date('Y');
                $balanceCount = \App\Models\Leave\LeaveBalance::where('leave_year', $currentLeaveYear)->count();
                $staffCount = \App\Models\User::where('status', 'Current')->count();
            @endphp
            <div class="card border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h6 class="card-title text-white"><i class="fas fa-sync-alt me-2"></i>Sync Leave Balances</h6>
                    <p class="small opacity-75 mb-3">
                        Create, update, and clean up leave balances based on current leave type settings and policies.
                    </p>
                    <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded" style="background: rgba(255,255,255,0.15);">
                        <div class="text-center">
                            <div class="fw-bold">{{ $currentLeaveYear }}</div>
                            <small class="opacity-75">Leave Year</small>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold">{{ $staffCount }}</div>
                            <small class="opacity-75">Staff</small>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold">{{ $balanceCount }}</div>
                            <small class="opacity-75">Balances</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-light w-100 btn-loading d-flex justify-content-center align-items-center" id="initializeBalancesBtn">
                        <span class="btn-text"><i class="fas fa-sync-alt me-2"></i>Sync Balances for {{ $currentLeaveYear }}</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Syncing...
                        </span>
                    </button>
                    <small class="d-block mt-2 opacity-75">
                        <i class="fas fa-info-circle me-1"></i>Creates new, removes ineligible (unused), and updates balances.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 pt-3 border-top">
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="fas fa-save me-2"></i>Save General Settings</span>
            <span class="btn-spinner d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Saving...
            </span>
        </button>
    </div>
</form>
