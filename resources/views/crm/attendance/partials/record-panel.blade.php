<div class="crm-slide-panel-backdrop" id="crm-record-panel-backdrop"></div>
<div class="crm-slide-panel" id="crm-record-panel">
    <div class="crm-slide-panel-header">
        <div>
            <p class="crm-kicker" id="crm-panel-date">—</p>
            <h3 id="crm-panel-user-name">Record Detail</h3>
        </div>
        <button type="button" class="btn btn-light crm-btn-light" id="crm-panel-close">
            <i class="bx bx-x"></i>
        </button>
    </div>

    <div id="crm-panel-loading" style="padding: 20px; text-align: center; color: #64748b; display: none;">
        <span class="spinner-border spinner-border-sm me-2"></span> Loading...
    </div>

    <div id="crm-panel-content" style="display: none;">
        <div class="crm-meta-list" id="crm-panel-meta"></div>

        {{-- Pending corrections indicator --}}
        <div id="crm-panel-pending-corrections" style="display: none; margin-top: 16px; padding: 12px; background: #fffbeb; border-left: 4px solid #f7b84b; border-radius: 0 3px 3px 0;">
            <strong style="color: #92400e; font-size: 13px;">Pending Correction</strong>
            <p style="color: #92400e; font-size: 12px; margin: 4px 0 0;">This record has a correction request awaiting review.</p>
        </div>

        {{-- Manager edit section --}}
        <div id="crm-panel-edit-section" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #e5e7eb; display: none;">
            <p class="crm-kicker" style="margin-bottom: 12px;">Edit Record</p>
            <form id="crm-panel-edit-form" class="crm-form">
                <div class="crm-field" style="margin-bottom: 14px;">
                    <label for="panel-code">Attendance Code</label>
                    <select id="panel-code" name="attendance_code_id">
                        @foreach ($codes as $code)
                            <option value="{{ $code->id }}" data-color="{{ $code->color }}">{{ $code->code }} — {{ $code->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="crm-field" style="margin-bottom: 14px;">
                    <label for="panel-clock-in">Clock In</label>
                    <input type="datetime-local" id="panel-clock-in" name="clocked_in_at">
                </div>
                <div class="crm-field" style="margin-bottom: 14px;">
                    <label for="panel-clock-out">Clock Out</label>
                    <input type="datetime-local" id="panel-clock-out" name="clocked_out_at">
                </div>
                <div class="crm-field" style="margin-bottom: 14px;">
                    <label for="panel-note-in">Clock In Note</label>
                    <input type="text" id="panel-note-in" name="clock_in_note" maxlength="500">
                </div>
                <div class="crm-field" style="margin-bottom: 14px;">
                    <label for="panel-note-out">Clock Out Note</label>
                    <input type="text" id="panel-note-out" name="clock_out_note" maxlength="500">
                </div>
                <div class="form-actions" style="justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        {{-- User correction request section --}}
        <div id="crm-panel-correction-section" style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #e5e7eb; display: none;">
            <p class="crm-kicker" style="margin-bottom: 12px;">Request Correction</p>
            <form id="crm-panel-correction-form" class="crm-form">
                <div class="crm-field" style="margin-bottom: 14px;">
                    <label for="correction-code">Proposed Code</label>
                    <select id="correction-code" name="proposed_code_id">
                        <option value="">— No change —</option>
                        @foreach ($codes as $code)
                            <option value="{{ $code->id }}">{{ $code->code }} — {{ $code->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="crm-field" style="margin-bottom: 14px;">
                    <label for="correction-clock-in">Proposed Clock In</label>
                    <input type="datetime-local" id="correction-clock-in" name="proposed_clock_in">
                </div>
                <div class="crm-field" style="margin-bottom: 14px;">
                    <label for="correction-clock-out">Proposed Clock Out</label>
                    <input type="datetime-local" id="correction-clock-out" name="proposed_clock_out">
                </div>
                <div class="crm-field" style="margin-bottom: 14px;">
                    <label for="correction-reason">Reason <span style="color: #f06548;">*</span></label>
                    <textarea id="correction-reason" name="reason" required maxlength="1000" rows="3" placeholder="Explain why this correction is needed"></textarea>
                </div>
                <div class="form-actions" style="justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="bx bx-send"></i> Submit Correction</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Submitting...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
