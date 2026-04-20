{{-- Bell Schedule Tab Content --}}
<div class="help-text">
    <div class="help-title">Bell Schedule</div>
    <div class="help-content">
        Configure the bell schedule for your school day. All 6 days in the rotation cycle use the same schedule. Times are recalculated automatically when you save.
    </div>
</div>

@include('timetable.period-settings._day-preview')

<form id="bellScheduleForm" data-url="{{ route('timetable.period-settings.update-periods') }}">
    @csrf
    <div class="settings-section">
        <h6 class="section-title"><i class="fas fa-clock me-2"></i>Period Definitions</h6>

                <div id="periodsContainer">
                    @php
                        $periods = $settings['period_definitions'] ?? [];
                    @endphp
                    @forelse ($periods as $i => $period)
                        <div class="item-type-card period-row" data-index="{{ $i }}">
                            <div class="item-type-header">
                                <div style="flex: 0 0 auto;">
                                    <span style="font-weight: 600; color: #374151; font-size: 14px;">Period {{ $period['period'] }}</span>
                                    <input type="hidden" name="period_definitions[{{ $i }}][period]" value="{{ $period['period'] }}">
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-period-btn" title="Remove period" @if(count($periods) <= 1) style="display:none;" @endif>
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Start Time</label>
                                    <input type="time"
                                        class="form-control period-start-time"
                                        name="period_definitions[{{ $i }}][start_time]"
                                        value="{{ $period['start_time'] }}"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">End Time</label>
                                    <input type="time"
                                        class="form-control period-end-time"
                                        name="period_definitions[{{ $i }}][end_time]"
                                        value="{{ $period['end_time'] }}"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Duration (min)</label>
                                    <input type="number"
                                        class="form-control period-duration"
                                        name="period_definitions[{{ $i }}][duration]"
                                        value="{{ $period['duration'] }}"
                                        min="20"
                                        max="120"
                                        required>
                                    <div class="form-hint">20-120 minutes</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="item-type-card period-row" data-index="0">
                            <div class="item-type-header">
                                <div style="flex: 0 0 auto;">
                                    <span style="font-weight: 600; color: #374151; font-size: 14px;">Period 1</span>
                                    <input type="hidden" name="period_definitions[0][period]" value="1">
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-period-btn" title="Remove period" style="display:none;">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Start Time</label>
                                    <input type="time"
                                        class="form-control period-start-time"
                                        name="period_definitions[0][start_time]"
                                        value="07:30"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">End Time</label>
                                    <input type="time"
                                        class="form-control period-end-time"
                                        name="period_definitions[0][end_time]"
                                        value="08:10"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Duration (min)</label>
                                    <input type="number"
                                        class="form-control period-duration"
                                        name="period_definitions[0][duration]"
                                        value="40"
                                        min="20"
                                        max="120"
                                        required>
                                    <div class="form-hint">20-120 minutes</div>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="addPeriodBtn">
                    <i class="fas fa-plus me-1"></i> Add Period
                </button>
            </div>

    {{-- Form Actions --}}
    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="fas fa-save"></i> Save Bell Schedule</span>
            <span class="btn-spinner d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Saving...
            </span>
        </button>
    </div>
</form>
