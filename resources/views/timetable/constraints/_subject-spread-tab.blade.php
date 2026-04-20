{{-- Subject Spread Tab Content (CONST-05) --}}
<style>
    .spread-table {
        width: 100%;
        border-collapse: collapse;
    }

    .spread-table thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 10px 12px;
    }

    .spread-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        font-size: 13px;
        color: #374151;
    }

    .spread-table tbody tr:hover {
        background: #f0f5ff;
    }

    .spread-table .subject-name {
        font-weight: 600;
        color: #1f2937;
    }

    .spread-table .row-num {
        color: #9ca3af;
        font-size: 12px;
        font-weight: 500;
    }

    .spread-table .spread-input {
        width: 70px;
        text-align: center;
        padding: 6px 8px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .spread-table .spread-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .spread-table .distribute-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 500;
    }

    .spread-table .distribute-badge.distribute-yes {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .spread-table .distribute-badge.distribute-no {
        background: #f9fafb;
        color: #6b7280;
        border: 1px solid #e5e7eb;
    }

    .spread-table tfoot td {
        padding: 14px 12px;
        background: #f9fafb;
        border-top: 2px solid #e5e7eb;
        font-size: 13px;
        color: #6b7280;
    }

    .spread-table .action-btns {
        display: flex;
        align-items: center;
        gap: 6px;
    }
</style>

<div class="help-text">
    <div class="help-title">Subject Spread</div>
    <div class="help-content">
        Control how subjects are distributed across the 6-day cycle. Set the maximum number of lessons a subject can have on a single day. Single, double, and triple blocks each count as one lesson. This is a soft constraint.
    </div>
</div>

@if(!empty($showSubjectSpreadResetNotice))
    <div class="alert alert-info" role="alert">
        Legacy period-based subject spread rules were reset and deactivated. Add new lesson-based spread rules to enforce daily lesson limits.
    </div>
@endif

<div class="settings-section">
    {{-- Add New Spread Rule --}}
    <h6 class="section-title"><i class="fas fa-plus-circle me-2"></i>Add Subject Spread Rule</h6>
    <div class="row g-3 mb-4 align-items-end">
        <div class="col-md-3">
            <label class="form-label" for="spreadSubjectSelect">Subject</label>
            <select class="form-select" id="spreadSubjectSelect">
                <option value="">-- Select Subject --</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="spreadMaxPerDay">Max Lessons / Day</label>
            <input type="number" class="form-control" id="spreadMaxPerDay" value="1" min="1" max="{{ max(1, (int) ($periodsPerDay ?? 6)) }}">
        </div>
        <div class="col-md-3">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" id="spreadDistribute" checked>
                <label class="form-check-label" for="spreadDistribute">Distribute evenly</label>
            </div>
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-primary btn-sm" id="addSubjectSpreadBtn">
                <i class="fas fa-plus me-1"></i> Add Rule
            </button>
        </div>
    </div>

    {{-- Existing Spread Rules --}}
    <h6 class="section-title"><i class="fas fa-calendar-alt me-2"></i>Current Spread Rules</h6>
    <div id="subjectSpreadContainer">
        @php
            $spreadConstraints = $constraints->get('subject_spread', collect());
        @endphp
        @if($spreadConstraints->count() > 0)
            <div class="table-responsive">
                <table class="spread-table" id="subjectSpreadTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Subject</th>
                            <th style="width: 140px; text-align: center;">Max Lessons / Day</th>
                            <th style="width: 140px; text-align: center;">Distribute</th>
                            <th style="width: 120px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="subjectSpreadBody">
                        @foreach($spreadConstraints as $idx => $spread)
                            @php
                                $subjectId = $spread->constraint_config['subject_id'] ?? null;
                                $subjectName = $subjects->firstWhere('id', $subjectId)->name ?? 'Unknown';
                                $maxPerDay = $spread->constraint_config['max_lessons_per_day'] ?? 1;
                                $distribute = $spread->constraint_config['distribute_across_cycle'] ?? true;
                            @endphp
                            <tr data-constraint-id="{{ $spread->id }}" data-subject-id="{{ $subjectId }}">
                                <td><span class="row-num">{{ $idx + 1 }}</span></td>
                                <td><span class="subject-name">{{ $subjectName }}</span></td>
                                <td class="text-center">
                                    <input type="number" class="spread-input spread-max-per-day" value="{{ $maxPerDay }}" min="1" max="{{ max(1, (int) ($periodsPerDay ?? 6)) }}">
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input spread-distribute" @if($distribute) checked @endif>
                                </td>
                                <td>
                                    <div class="action-btns justify-content-center">
                                        <button type="button" class="btn btn-sm btn-primary btn-loading save-spread-btn" data-subject-id="{{ $subjectId }}">
                                            <span class="btn-text"><i class="fas fa-save"></i></span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            </span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-spread-btn" data-constraint-id="{{ $spread->id }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>{{ $spreadConstraints->count() }}</strong> rule{{ $spreadConstraints->count() !== 1 ? 's' : '' }} configured
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div id="spreadEmptyState" class="text-center text-muted py-4">
                <i class="fas fa-calendar-alt mb-2" style="font-size: 24px; opacity: 0.4;"></i>
                <p class="mb-0">No subject spread rules configured yet.</p>
            </div>
            <div class="table-responsive" style="display: none;">
                <table class="spread-table" id="subjectSpreadTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Subject</th>
                            <th style="width: 140px; text-align: center;">Max Lessons / Day</th>
                            <th style="width: 140px; text-align: center;">Distribute</th>
                            <th style="width: 120px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="subjectSpreadBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong id="spreadRuleCount">0</strong> rule(s) configured
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
