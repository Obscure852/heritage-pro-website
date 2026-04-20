{{-- Subject Pair Tab Content (CONST-08) --}}
<style>
    .pair-table {
        width: 100%;
        border-collapse: collapse;
    }

    .pair-table thead th {
        background: #f9fafb;
        border-bottom: 2px solid #e5e7eb;
        font-weight: 600;
        color: #374151;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        padding: 10px 12px;
    }

    .pair-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
        font-size: 13px;
        color: #374151;
    }

    .pair-table tbody tr:hover {
        background: #f0f5ff;
    }

    .pair-table .subject-name {
        font-weight: 600;
        color: #1f2937;
    }

    .pair-table .class-name {
        color: #6b7280;
        font-size: 12px;
    }

    .pair-table .rule-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 500;
    }

    .pair-table .rule-badge.rule-not_same_day {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .pair-table .rule-badge.rule-not_consecutive {
        background: #fffbeb;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .pair-table .rule-badge.rule-must_same_day {
        background: #eff6ff;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }

    .pair-table .rule-badge.rule-must_follow {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .pair-table .row-num {
        color: #9ca3af;
        font-size: 12px;
        font-weight: 500;
    }

    .pair-table tfoot td {
        padding: 14px 12px;
        background: #f9fafb;
        border-top: 2px solid #e5e7eb;
        font-size: 13px;
        color: #6b7280;
    }
</style>

<div class="help-text">
    <div class="help-title">Subject Pair Rules</div>
    <div class="help-content">
        Define relationship rules between two subjects for a class. For example, prevent Maths and Science from being scheduled on the same day, or require them to be back-to-back. Optionally restrict the rule to a specific class, or apply it to all classes. This is a soft constraint.
    </div>
</div>

<div class="settings-section">
    {{-- Add New Subject Pair Rule --}}
    <h6 class="section-title"><i class="fas fa-plus-circle me-2"></i>Add Subject Pair Rule</h6>
    <div class="row g-3 mb-4 align-items-end">
        <div class="col-md-3">
            <label class="form-label" for="pairSubjectASelect">Subject A</label>
            <select class="form-select" id="pairSubjectASelect">
                <option value="">-- Select Subject --</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="pairSubjectBSelect">Subject B</label>
            <select class="form-select" id="pairSubjectBSelect">
                <option value="">-- Select Subject --</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="pairKlassSelect">Class <small class="text-muted">(optional)</small></label>
            <select class="form-select" id="pairKlassSelect">
                <option value="">All Classes</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label" for="pairRuleSelect">Rule</label>
            <select class="form-select" id="pairRuleSelect">
                <option value="not_same_day">Must not be on same day</option>
                <option value="not_consecutive">Must not be back-to-back</option>
                <option value="must_same_day">Must be on same day</option>
                <option value="must_follow">Must be adjacent</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-primary btn-sm" id="addSubjectPairBtn">
                <i class="fas fa-plus me-1"></i> Add Rule
            </button>
        </div>
    </div>

    {{-- Existing Subject Pair Rules --}}
    <h6 class="section-title"><i class="fas fa-exchange-alt me-2"></i>Current Subject Pair Rules</h6>
    <div id="subjectPairContainer">
        @php
            $pairConstraints = $constraints->get('subject_pair', collect());
            $ruleLabels = [
                'not_same_day' => 'Must not be on same day',
                'not_consecutive' => 'Must not be back-to-back',
                'must_same_day' => 'Must be on same day',
                'must_follow' => 'Must be adjacent',
            ];
            $ruleIcons = [
                'not_same_day' => 'fas fa-ban',
                'not_consecutive' => 'fas fa-arrows-alt-h',
                'must_same_day' => 'fas fa-calendar-day',
                'must_follow' => 'fas fa-link',
            ];
        @endphp
        @if($pairConstraints->count() > 0)
            <div class="table-responsive">
                <table class="pair-table" id="subjectPairTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Subject A</th>
                            <th>Subject B</th>
                            <th>Class</th>
                            <th>Rule</th>
                            <th style="width: 80px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="subjectPairBody">
                        @foreach($pairConstraints as $idx => $pair)
                            @php
                                $subjectIdA = $pair->constraint_config['subject_id_a'] ?? null;
                                $subjectIdB = $pair->constraint_config['subject_id_b'] ?? null;
                                $pairKlassId = $pair->constraint_config['klass_id'] ?? null;
                                $pairRule = $pair->constraint_config['rule'] ?? '';
                                $subjectNameA = $subjects->firstWhere('id', $subjectIdA)->name ?? 'Unknown';
                                $subjectNameB = $subjects->firstWhere('id', $subjectIdB)->name ?? 'Unknown';
                                $klassName = $pairKlassId ? ($klasses->firstWhere('id', $pairKlassId)->name ?? 'Unknown') : 'All Classes';
                            @endphp
                            <tr data-constraint-id="{{ $pair->id }}">
                                <td><span class="row-num">{{ $idx + 1 }}</span></td>
                                <td><span class="subject-name">{{ $subjectNameA }}</span></td>
                                <td><span class="subject-name">{{ $subjectNameB }}</span></td>
                                <td><span class="class-name">{{ $klassName }}</span></td>
                                <td><span class="rule-badge rule-{{ $pairRule }}"><i class="{{ $ruleIcons[$pairRule] ?? 'fas fa-info-circle' }}"></i> {{ $ruleLabels[$pairRule] ?? $pairRule }}</span></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-pair-btn" data-constraint-id="{{ $pair->id }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>{{ $pairConstraints->count() }}</strong> rule{{ $pairConstraints->count() !== 1 ? 's' : '' }} configured
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <div id="pairEmptyState" class="text-center text-muted py-4">
                <i class="fas fa-exchange-alt mb-2" style="font-size: 24px; opacity: 0.4;"></i>
                <p class="mb-0">No subject pair rules configured yet.</p>
            </div>
            <div class="table-responsive" style="display: none;">
                <table class="pair-table" id="subjectPairTable">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th>Subject A</th>
                            <th>Subject B</th>
                            <th>Class</th>
                            <th>Rule</th>
                            <th style="width: 80px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="subjectPairBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong id="pairRuleCount">0</strong> rule(s) configured
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
