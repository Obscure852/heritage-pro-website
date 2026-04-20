{{-- Block Allocations Tab Content --}}
<style>
    .alloc-table {
        width: 100%;
        border-collapse: collapse;
    }

    .alloc-table thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 10px 12px;
    }

    .alloc-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        font-size: 13px;
        color: #374151;
    }

    .alloc-table tbody tr:hover {
        background: #f0f5ff;
    }

    .alloc-table .subject-name {
        font-weight: 600;
        color: #1f2937;
    }

    .alloc-table .teacher-name {
        color: #6b7280;
        font-size: 12px;
    }

    .alloc-table .alloc-input {
        width: 70px;
        text-align: center;
        padding: 6px 8px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 13px;
        transition: all 0.2s;
    }

    .alloc-table .alloc-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .alloc-table .alloc-total {
        font-weight: 700;
        font-size: 14px;
        color: #1f2937;
    }

    .alloc-table tfoot td {
        padding: 14px 12px;
        background: #f9fafb;
        border-top: 2px solid #e5e7eb;
        font-size: 14px;
    }

    .alloc-summary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .alloc-summary .alloc-used {
        font-weight: 700;
        font-size: 18px;
        color: #1f2937;
    }

    .alloc-summary .alloc-divider {
        color: #9ca3af;
        font-weight: 400;
    }

    .alloc-summary .alloc-available {
        font-weight: 600;
        font-size: 18px;
        color: #6b7280;
    }

    .alloc-note {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-left: 4px solid #f59e0b;
        border-radius: 0 3px 3px 0;
        padding: 12px 16px;
        margin-top: 16px;
    }

    .alloc-note .note-title {
        font-weight: 600;
        color: #92400e;
        font-size: 13px;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .alloc-note .note-content {
        color: #78350f;
        font-size: 13px;
        line-height: 1.5;
        margin: 0;
    }
</style>

<div class="help-text">
    <div class="help-title">Block Allocations</div>
    <div class="help-content">
        Define how many period blocks each subject gets per 6-day cycle. Choose a timetable and class to configure. Each subject can have a mix of single (1 period), double (2 consecutive periods), and triple (3 consecutive periods) blocks. The total must not exceed the available slots (periods per day x 6 days).
    </div>
</div>

@if ($timetables->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fas fa-th-large" style="font-size: 32px; opacity: 0.3;"></i>
        <p class="mt-2 mb-0">No draft timetables found.</p>
        <p class="small">Create a timetable first before configuring block allocations.</p>
    </div>
@else
    {{-- Filter Bar --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Timetable</label>
            <select id="blockAllocTimetable" class="form-select">
                <option value="">-- Select Timetable --</option>
                @foreach ($timetables as $timetable)
                    <option value="{{ $timetable->id }}">{{ $timetable->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Class</label>
            <select id="blockAllocKlass" class="form-select">
                <option value="">-- Select Class --</option>
                @php
                    $groupedKlasses = $klasses->groupBy(fn($k) => $k->grade?->name ?? 'Ungrouped');
                @endphp
                @foreach ($groupedKlasses as $gradeName => $gradeKlasses)
                    <optgroup label="{{ $gradeName }}">
                        @foreach ($gradeKlasses as $klass)
                            <option value="{{ $klass->id }}">{{ $klass->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="button" class="btn btn-primary" id="loadSubjectsBtn">
                <i class="fas fa-search me-1"></i> Load Subjects
            </button>
        </div>
    </div>

    {{-- Empty State --}}
    <div id="blockAllocEmpty" style="display: none;">
        <div class="text-center py-4 text-muted">
            <i class="fas fa-info-circle fa-2x mb-2"></i>
            <p>No class-subject assignments found for the selected term. Please assign subjects to classes first.</p>
        </div>
    </div>

    {{-- Allocation Table --}}
    <div id="blockAllocTable" style="display: none;">
        <div class="table-responsive">
            <table class="alloc-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th style="width: 100px; text-align: center;">Singles</th>
                        <th style="width: 100px; text-align: center;">Doubles</th>
                        <th style="width: 100px; text-align: center;">Triples</th>
                        <th style="width: 80px; text-align: center;">Total</th>
                    </tr>
                </thead>
                <tbody id="blockAllocBody">
                    {{-- Populated via JavaScript --}}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="text-end fw-bold" style="color: #374151;">Total Allocated / Available:</td>
                        <td class="text-center">
                            <div class="alloc-summary">
                                <span class="alloc-used" id="blockAllocTotal">0</span>
                                <span class="alloc-divider">/</span>
                                <span class="alloc-available" id="blockAllocAvailable">{{ ($settings['periods_per_day'] ?? 7) * 6 }}</span>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div id="blockAllocWarning" class="mt-2" style="display: none; color: #dc3545; font-weight: 500;">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <span>Warning: Total allocated periods exceed available slots!</span>
        </div>

        <div class="alloc-note">
            <div class="note-title"><i class="fas fa-exclamation-circle"></i> Important</div>
            <p class="note-content">
                The "Total Allocated / Available" count includes <strong>all</strong> scheduled periods for this class -- both regular subject lessons and elective coupling group blocks. If you have coupling groups configured, their periods are counted towards the total as well.
            </p>
        </div>

        {{-- Save Button --}}
        <div class="form-actions">
            <button type="button" class="btn btn-primary btn-loading" id="saveBlockAllocBtn">
                <span class="btn-text"><i class="fas fa-save"></i> Save Block Allocations</span>
                <span class="btn-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Saving...
                </span>
            </button>
        </div>
    </div>
@endif
