{{-- Consecutive Limits Tab Content (CONST-06) --}}
<style>
    .consec-table {
        width: 100%;
        border-collapse: collapse;
    }

    .consec-table thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 10px 12px;
    }

    .consec-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        font-size: 13px;
        color: #374151;
    }

    .consec-table tbody tr:hover {
        background: #f0f5ff;
    }

    .consec-table .teacher-name {
        font-weight: 600;
        color: #1f2937;
    }

    .consec-table .row-num {
        color: #9ca3af;
        font-size: 12px;
        font-weight: 500;
    }

    .consec-table .consec-input {
        width: 70px;
        text-align: center;
        padding: 6px 8px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .consec-table .consec-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .consec-table tfoot td {
        padding: 14px 12px;
        background: #f9fafb;
        border-top: 2px solid #e5e7eb;
        font-size: 13px;
        color: #6b7280;
    }

    .consec-table .action-btns {
        display: flex;
        align-items: center;
        gap: 6px;
    }
</style>

<div class="help-text">
    <div class="help-title">Consecutive Limits</div>
    <div class="help-content">
        Set the maximum number of consecutive teaching periods allowed per teacher. A global default applies to all teachers, and individual overrides can be set for specific teachers. This is a soft constraint to prevent teacher fatigue.
    </div>
</div>

<div class="settings-section">
    @php
        $consecutiveConstraints = $constraints->get('consecutive_limit', collect());
        $globalLimit = $consecutiveConstraints->first(function($c) {
            return array_key_exists('teacher_id', $c->constraint_config) && $c->constraint_config['teacher_id'] === null;
        });
        $teacherOverrides = $consecutiveConstraints->filter(function($c) {
            return isset($c->constraint_config['teacher_id']) && $c->constraint_config['teacher_id'] !== null;
        });
        $globalMax = $globalLimit ? ($globalLimit->constraint_config['max_consecutive_periods'] ?? 3) : 3;
    @endphp

    {{-- Global Default --}}
    <h6 class="section-title"><i class="fas fa-globe me-2"></i>Global Default</h6>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label" for="globalConsecutiveLimit">Max Consecutive Periods</label>
            <div class="d-flex align-items-center gap-2">
                <input type="number" class="form-control" id="globalConsecutiveLimit" value="{{ $globalMax }}" min="1" max="10" style="max-width: 120px;">
                <button type="button" class="btn btn-primary btn-loading" id="saveGlobalConsecutiveBtn">
                    <span class="btn-text"><i class="fas fa-save"></i> Save Default</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Saving...
                    </span>
                </button>
            </div>
            <div class="form-hint">Applies to all teachers unless overridden below.</div>
        </div>
    </div>

    {{-- Per-Teacher Overrides --}}
    <h6 class="section-title"><i class="fas fa-user-cog me-2"></i>Per-Teacher Overrides</h6>
    <div class="row g-3 mb-4 align-items-end">
        <div class="col-md-4">
            <label class="form-label" for="consecutiveTeacherSelect">Teacher</label>
            <select class="form-select" id="consecutiveTeacherSelect">
                <option value="">-- Select Teacher --</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="consecutiveTeacherMax">Max Consecutive</label>
            <input type="number" class="form-control" id="consecutiveTeacherMax" value="3" min="1" max="10">
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-primary btn-sm" id="addConsecutiveOverrideBtn">
                <i class="fas fa-plus me-1"></i> Add Override
            </button>
        </div>
    </div>

    <div id="consecutiveOverridesContainer">
        @if($teacherOverrides->count() > 0)
            <div class="table-responsive">
                <table class="consec-table" id="consecutiveOverridesTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Teacher</th>
                            <th style="width: 160px; text-align: center;">Max Consecutive</th>
                            <th style="width: 120px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="consecutiveOverridesBody">
                        @foreach($teacherOverrides as $idx => $override)
                            @php
                                $teacherId = $override->constraint_config['teacher_id'] ?? null;
                                $teacher = $teachers->firstWhere('id', $teacherId);
                                $teacherName = $teacher ? ($teacher->firstname . ' ' . $teacher->lastname) : 'Unknown';
                                $maxConsec = $override->constraint_config['max_consecutive_periods'] ?? 3;
                            @endphp
                            <tr data-constraint-id="{{ $override->id }}" data-teacher-id="{{ $teacherId }}">
                                <td><span class="row-num">{{ $loop->iteration }}</span></td>
                                <td><span class="teacher-name">{{ $teacherName }}</span></td>
                                <td class="text-center">
                                    <input type="number" class="consec-input consecutive-max-input" value="{{ $maxConsec }}" min="1" max="10">
                                </td>
                                <td>
                                    <div class="action-btns justify-content-center">
                                        <button type="button" class="btn btn-sm btn-primary btn-loading save-consecutive-btn" data-teacher-id="{{ $teacherId }}">
                                            <span class="btn-text"><i class="fas fa-save"></i></span>
                                            <span class="btn-spinner d-none">
                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                            </span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-consecutive-btn" data-constraint-id="{{ $override->id }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>{{ $teacherOverrides->count() }}</strong> override{{ $teacherOverrides->count() !== 1 ? 's' : '' }} configured &middot; Global default: <strong>{{ $globalMax }}</strong> periods
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div id="consecutiveEmptyState" class="text-center text-muted py-4">
                <i class="fas fa-hourglass-half mb-2" style="font-size: 24px; opacity: 0.4;"></i>
                <p class="mb-0">No per-teacher overrides configured yet. The global default applies to all teachers.</p>
            </div>
            <div class="table-responsive" style="display: none;">
                <table class="consec-table" id="consecutiveOverridesTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Teacher</th>
                            <th style="width: 160px; text-align: center;">Max Consecutive</th>
                            <th style="width: 120px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="consecutiveOverridesBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong id="consecOverrideCount">0</strong> override(s) configured
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
