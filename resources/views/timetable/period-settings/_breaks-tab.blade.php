{{-- Breaks Tab Content --}}
<div class="help-text">
    <div class="help-title">Break Intervals</div>
    <div class="help-content">
        Configure break intervals between periods. Breaks are not schedulable &mdash; the timetable generator skips these time slots. Break times are automatically calculated based on the bell schedule.
    </div>
</div>

@include('timetable.period-settings._day-preview')

<form id="breaksForm" data-url="{{ route('timetable.period-settings.update-breaks') }}">
    @csrf
    <div class="settings-section">
        <h6 class="section-title"><i class="fas fa-coffee me-2"></i>Break Intervals</h6>

                <div id="breaksContainer">
                    @php
                        $breaks = $settings['break_intervals'] ?? [];
                        $periodCount = count($settings['period_definitions'] ?? []);
                    @endphp
                    @forelse ($breaks as $i => $break)
                        <div class="item-type-card break-row" data-index="{{ $i }}">
                            <div class="item-type-header">
                                <div style="flex: 1;">
                                    <span style="font-weight: 600; color: #374151; font-size: 14px;">
                                        {{ $break['label'] }}
                                        @if (!empty($break['start_time']) && !empty($break['end_time']))
                                            <span style="font-weight: 400; color: #6b7280; font-size: 13px; margin-left: 8px;">
                                                ({{ $break['start_time'] }} - {{ $break['end_time'] }})
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-break-btn" title="Remove break">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Label</label>
                                    <input type="text"
                                        class="form-control break-label"
                                        name="break_intervals[{{ $i }}][label]"
                                        value="{{ $break['label'] }}"
                                        placeholder="e.g. Tea Break"
                                        maxlength="50"
                                        required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">After Period</label>
                                    <select class="form-select break-after-period"
                                        name="break_intervals[{{ $i }}][after_period]"
                                        required>
                                        @for ($p = 1; $p <= max($periodCount, 1); $p++)
                                            <option value="{{ $p }}" @if(($break['after_period'] ?? 0) == $p) selected @endif>After Period {{ $p }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Duration (min)</label>
                                    <input type="number"
                                        class="form-control break-duration"
                                        name="break_intervals[{{ $i }}][duration]"
                                        value="{{ $break['duration'] }}"
                                        min="5"
                                        max="90"
                                        required>
                                    <div class="form-hint">5-90 minutes</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- No breaks configured yet --}}
                    @endforelse
                </div>

                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="addBreakBtn">
                    <i class="fas fa-plus me-1"></i> Add Break
                </button>
            </div>

    {{-- Form Actions --}}
    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="fas fa-save"></i> Save Break Intervals</span>
            <span class="btn-spinner d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Saving...
            </span>
        </button>
    </div>
</form>
