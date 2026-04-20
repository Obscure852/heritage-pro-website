{{-- Teacher Availability Tab Content (CONST-01) --}}
<style>
    .avail-teacher-bar {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        margin-bottom: 20px;
    }

    .avail-teacher-bar .teacher-select-wrapper {
        flex: 0 0 280px;
    }

    .avail-teacher-bar .teacher-select-wrapper label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        margin-bottom: 4px;
        display: block;
    }

    .avail-legend {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-left: auto;
    }

    .avail-legend .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 500;
        color: #374151;
    }

    .avail-legend .legend-swatch {
        width: 20px;
        height: 20px;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }

    .avail-legend .legend-swatch.swatch-available {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .avail-legend .legend-swatch.swatch-unavailable {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .avail-stats {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        margin-bottom: 16px;
    }

    .avail-stats .stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #374151;
        font-weight: 500;
    }

    .avail-stats .stat-chip strong {
        font-weight: 700;
        color: #1f2937;
    }

    .avail-stats .stat-chip i {
        font-size: 11px;
    }

    .avail-stats .stat-divider {
        width: 1px;
        height: 18px;
        background: #e5e7eb;
    }

    /* Grid overrides — cleaner, more modern look */
    .availability-grid {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
    }

    .availability-grid thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 10px 12px;
        text-align: center;
    }

    .availability-grid thead th:first-child {
        text-align: left;
        padding-left: 16px;
    }

    .availability-grid tbody th {
        background: #f9fafb;
        font-weight: 600;
        color: #374151;
        font-size: 13px;
        padding: 0 12px;
        text-align: left;
        padding-left: 16px;
        border-right: 1px solid #e5e7eb;
        border-bottom: 1px solid #f3f4f6;
    }

    .availability-grid tbody td {
        padding: 0;
        border-bottom: 1px solid #f3f4f6;
        border-right: 1px solid #f3f4f6;
    }

    .availability-grid tbody td:last-child {
        border-right: none;
    }

    .availability-cell {
        cursor: pointer;
        transition: all 0.15s ease;
        min-width: 60px;
        height: 42px;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
    }

    .availability-cell.available {
        background: #dcfce7;
        color: #166534;
    }

    .availability-cell.available:hover {
        background: #bbf7d0;
    }

    .availability-cell.unavailable {
        background: #fee2e2;
        color: #991b1b;
    }

    .availability-cell.unavailable:hover {
        background: #fecaca;
    }

    .availability-cell i {
        font-size: 12px;
    }

    .avail-hint {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #6b7280;
        margin-top: 12px;
    }

    .avail-hint i {
        color: #9ca3af;
    }
</style>

<div class="help-text">
    <div class="help-title">Teacher Availability</div>
    <div class="help-content">
        Mark the day/period slots where a teacher is NOT available to teach. Click cells to toggle availability. Green = available, Red = unavailable. This is a hard constraint -- the system will not schedule a teacher in their unavailable slots.
    </div>
</div>

<div class="settings-section">
    {{-- Teacher select bar with legend --}}
    <div class="avail-teacher-bar">
        <div class="teacher-select-wrapper">
            <label for="availabilityTeacherSelect">Select Teacher</label>
            <select class="form-select" id="availabilityTeacherSelect">
                <option value="">-- Select a Teacher --</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->firstname }} {{ $teacher->lastname }}</option>
                @endforeach
            </select>
        </div>
        <div class="avail-legend">
            <div class="legend-item">
                <span class="legend-swatch swatch-available"><i class="fas fa-check"></i></span>
                Available
            </div>
            <div class="legend-item">
                <span class="legend-swatch swatch-unavailable"><i class="fas fa-times"></i></span>
                Unavailable
            </div>
        </div>
    </div>

    <div id="availabilityGridContainer" style="display: none;">
        {{-- Stats bar --}}
        <div class="avail-stats" id="availabilityStats">
            <div class="stat-chip">
                <i class="fas fa-check-circle" style="color: #16a34a;"></i>
                Available: <strong id="availableCount">0</strong>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-chip">
                <i class="fas fa-times-circle" style="color: #dc2626;"></i>
                Unavailable: <strong id="unavailableCount">0</strong>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-chip">
                <i class="fas fa-th" style="color: #4e73df;"></i>
                Total Slots: <strong id="totalSlots">0</strong>
            </div>
        </div>

        {{-- Grid --}}
        <table class="availability-grid">
            <thead>
                <tr>
                    <th style="width: 100px;">Period</th>
                    <th>Day 1</th>
                    <th>Day 2</th>
                    <th>Day 3</th>
                    <th>Day 4</th>
                    <th>Day 5</th>
                    <th>Day 6</th>
                </tr>
            </thead>
            <tbody id="availabilityGridBody">
                {{-- Populated by JavaScript --}}
            </tbody>
        </table>

        <div class="avail-hint">
            <i class="fas fa-mouse-pointer"></i>
            Click a cell to toggle between available and unavailable.
        </div>
    </div>

    <div class="form-actions" style="border-top: none; margin-top: 16px; padding-top: 0;">
        <button type="button" class="btn btn-primary btn-loading" id="saveAvailabilityBtn" style="display: none;">
            <span class="btn-text"><i class="fas fa-save"></i> Save Availability</span>
            <span class="btn-spinner d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Saving...
            </span>
        </button>
    </div>
</div>
