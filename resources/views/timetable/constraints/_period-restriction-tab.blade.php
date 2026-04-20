{{-- Period Restriction Tab Content (CONST-09) --}}
<style>
    .restrict-table {
        width: 100%;
        border-collapse: collapse;
    }

    .restrict-table thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 10px 12px;
    }

    .restrict-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        font-size: 13px;
        color: #374151;
    }

    .restrict-table tbody tr:hover {
        background: #f0f5ff;
    }

    .restrict-table .subject-name {
        font-weight: 600;
        color: #1f2937;
    }

    .restrict-table .row-num {
        color: #9ca3af;
        font-size: 12px;
        font-weight: 500;
    }

    .restrict-table .restriction-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 500;
    }

    .restrict-table .restriction-badge.type-fixed_period {
        background: #eff6ff;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }

    .restrict-table .restriction-badge.type-first_or_last {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .restrict-table .restriction-badge.type-afternoon_only {
        background: #fffbeb;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .restrict-table .restriction-badge.type-reserved_periods {
        background: #faf5ff;
        color: #6b21a8;
        border: 1px solid #e9d5ff;
    }

    .restrict-table .period-chip {
        display: inline-block;
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 2px 8px;
        font-size: 12px;
        font-weight: 600;
        margin-right: 4px;
        margin-bottom: 2px;
    }

    .restrict-table tfoot td {
        padding: 14px 12px;
        background: #f9fafb;
        border-top: 2px solid #e5e7eb;
        font-size: 13px;
        color: #6b7280;
    }

    .period-selector {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .period-selector .period-toggle {
        display: none;
    }

    .period-selector .period-label {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 34px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.15s;
        background: white;
        user-select: none;
    }

    .period-selector .period-label:hover {
        border-color: #93c5fd;
        color: #2563eb;
        background: #eff6ff;
    }

    .period-selector .period-toggle:checked + .period-label {
        background: #4e73df;
        color: white;
        border-color: #4e73df;
    }
</style>

<div class="help-text">
    <div class="help-title">Period Restrictions</div>
    <div class="help-content">
        Set time-of-day rules for subjects. For example, schedule PE as the first or last period, restrict Art to afternoon slots, or fix Assembly to a specific period. This is a soft constraint.
    </div>
</div>

<div class="settings-section">
    {{-- Add New Period Restriction --}}
    <h6 class="section-title"><i class="fas fa-plus-circle me-2"></i>Add Period Restriction</h6>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label" for="restrictionSubjectSelect">Subject</label>
            <select class="form-select" id="restrictionSubjectSelect">
                <option value="">-- Select Subject --</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="restrictionTypeSelect">Restriction Type</label>
            <select class="form-select" id="restrictionTypeSelect">
                <option value="fixed_period">Fixed Period</option>
                <option value="first_or_last">First or Last Period</option>
                <option value="afternoon_only">Afternoon Only</option>
                <option value="reserved_periods">Reserved Periods</option>
            </select>
        </div>
    </div>
    <div id="periodCheckboxesContainer" class="mb-3">
        <label class="form-label">Allowed Periods</label>
        <div class="period-selector" id="periodCheckboxes">
            @for ($p = 1; $p <= $periodsPerDay; $p++)
                <div>
                    <input class="period-toggle restriction-period-cb" type="checkbox" value="{{ $p }}" id="restrictPeriod{{ $p }}">
                    <label class="period-label" for="restrictPeriod{{ $p }}">P{{ $p }}</label>
                </div>
            @endfor
        </div>
    </div>
    <div class="mb-4">
        <button type="button" class="btn btn-primary btn-sm" id="addPeriodRestrictionBtn">
            <i class="fas fa-plus me-1"></i> Add Rule
        </button>
    </div>

    {{-- Existing Period Restrictions --}}
    <h6 class="section-title"><i class="fas fa-clock me-2"></i>Current Period Restrictions</h6>
    <div id="periodRestrictionContainer">
        @php
            $restrictionConstraints = $constraints->get('period_restriction', collect());
            $restrictionLabels = [
                'fixed_period' => 'Fixed Period',
                'first_or_last' => 'First or Last Period',
                'afternoon_only' => 'Afternoon Only',
                'reserved_periods' => 'Reserved Periods',
            ];
            $restrictionIcons = [
                'fixed_period' => 'fas fa-thumbtack',
                'first_or_last' => 'fas fa-arrows-alt-v',
                'afternoon_only' => 'fas fa-sun',
                'reserved_periods' => 'fas fa-lock',
            ];
        @endphp
        @if($restrictionConstraints->count() > 0)
            <div class="table-responsive">
                <table class="restrict-table" id="periodRestrictionTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Subject</th>
                            <th>Restriction</th>
                            <th>Periods</th>
                            <th style="width: 80px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="periodRestrictionBody">
                        @foreach($restrictionConstraints as $idx => $restriction)
                            @php
                                $subjectId = $restriction->constraint_config['subject_id'] ?? null;
                                $restrictionType = $restriction->constraint_config['restriction'] ?? '';
                                $allowedPeriods = $restriction->constraint_config['allowed_periods'] ?? [];
                                $subjectName = $subjects->firstWhere('id', $subjectId)->name ?? 'Unknown';
                            @endphp
                            <tr data-constraint-id="{{ $restriction->id }}" data-subject-id="{{ $subjectId }}">
                                <td><span class="row-num">{{ $idx + 1 }}</span></td>
                                <td><span class="subject-name">{{ $subjectName }}</span></td>
                                <td><span class="restriction-badge type-{{ $restrictionType }}"><i class="{{ $restrictionIcons[$restrictionType] ?? 'fas fa-info-circle' }}"></i> {{ $restrictionLabels[$restrictionType] ?? $restrictionType }}</span></td>
                                <td>
                                    @foreach($allowedPeriods as $ap)
                                        <span class="period-chip">P{{ $ap }}</span>
                                    @endforeach
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-restriction-btn" data-constraint-id="{{ $restriction->id }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>{{ $restrictionConstraints->count() }}</strong> restriction{{ $restrictionConstraints->count() !== 1 ? 's' : '' }} configured
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div id="restrictionEmptyState" class="text-center text-muted py-4">
                <i class="fas fa-clock mb-2" style="font-size: 24px; opacity: 0.4;"></i>
                <p class="mb-0">No period restrictions configured yet.</p>
            </div>
            <div class="table-responsive" style="display: none;">
                <table class="restrict-table" id="periodRestrictionTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Subject</th>
                            <th>Restriction</th>
                            <th>Periods</th>
                            <th style="width: 80px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="periodRestrictionBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong id="restrictionRuleCount">0</strong> restriction(s) configured
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
