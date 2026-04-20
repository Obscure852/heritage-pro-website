{{-- Coupling Groups Tab Content --}}
<style>
    .coupling-group-card {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 0;
        margin-bottom: 16px;
        background: white;
        overflow: hidden;
    }

    .coupling-group-card .group-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 20px;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .coupling-group-card .group-card-header .group-badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .coupling-group-card .group-card-header .group-title {
        font-weight: 600;
        color: #374151;
        font-size: 14px;
        margin-left: 10px;
    }

    .coupling-group-card .group-card-body {
        padding: 20px;
    }

    .coupling-group-card .block-alloc-row {
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        padding: 16px 20px;
    }

    .coupling-group-card .block-alloc-row .block-alloc-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .coupling-group-card .block-alloc-row .block-field {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .coupling-group-card .block-alloc-row .block-field .block-icon {
        width: 28px;
        height: 28px;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }

    .coupling-group-card .block-alloc-row .block-field .block-icon.single-icon {
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }

    .coupling-group-card .block-alloc-row .block-field .block-icon.double-icon {
        background: #fef3c7;
        color: #d97706;
        border: 1px solid #fde68a;
    }

    .coupling-group-card .block-alloc-row .block-field .block-icon.triple-icon {
        background: #fce7f3;
        color: #db2777;
        border: 1px solid #fbcfe8;
    }

    .coupling-group-card .block-alloc-row .block-input {
        width: 60px;
        text-align: center;
        padding: 6px 8px;
        border: 1px solid #d1d5db;
        border-radius: 3px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .coupling-group-card .block-alloc-row .block-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .coupling-group-card .block-alloc-row .block-type-label {
        font-size: 12px;
        color: #6b7280;
        font-weight: 500;
    }

    #addCouplingGroupBtn {
        border-style: dashed;
    }
</style>

<div class="help-text">
    <div class="help-title">Coupling Groups</div>
    <div class="help-content">
        Configure which optional/elective subjects run concurrently in the same time slots. Students choose between options in a group, so all subjects in a coupling group are scheduled at the same time. Set the block allocation once per group (applies to all subjects in the group).
    </div>
</div>

<form id="couplingGroupsForm" data-url="{{ route('timetable.period-settings.update-coupling-groups') }}">
    @csrf

    <div class="settings-section">
        <div id="couplingGroupsContainer">
            @php
                $existingGroups = $settings['optional_coupling_groups'] ?? [];
            @endphp
            @foreach ($existingGroups as $i => $group)
                <div class="coupling-group-card" data-index="{{ $i }}">
                    <div class="group-card-header item-type-header">
                        <div class="d-flex align-items-center">
                            <span class="group-badge">{{ $i + 1 }}</span>
                            <span class="group-title">Group {{ $i + 1 }}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-coupling-group-btn" title="Remove group">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    <div class="group-card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Group Label</label>
                                <input type="text"
                                    class="form-control"
                                    name="coupling_groups[{{ $i }}][label]"
                                    value="{{ $group['label'] ?? '' }}"
                                    placeholder="e.g. Form 2 Optionals"
                                    maxlength="100"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Grade</label>
                                <select class="form-select coupling-grade-select"
                                    name="coupling_groups[{{ $i }}][grade_id]"
                                    required>
                                    <option value="">-- Select Grade --</option>
                                    @foreach ($klasses->pluck('grade')->unique('id')->filter()->sortBy('sequence') as $grade)
                                        <option value="{{ $grade->id }}" @if(($group['grade_id'] ?? '') == $grade->id) selected @endif>
                                            {{ $grade->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Optional Subjects</label>
                                <select class="form-select coupling-subjects-select"
                                    name="coupling_groups[{{ $i }}][optional_subject_ids][]"
                                    multiple
                                    required>
                                    @if (!empty($group['grade_id']) && isset($optionalSubjectsByGrade[$group['grade_id']]))
                                        @foreach ($optionalSubjectsByGrade[$group['grade_id']] as $os)
                                            <option value="{{ $os['id'] }}"
                                                @if(in_array($os['id'], $group['optional_subject_ids'] ?? [])) selected @endif>
                                                {{ $os['name'] ?? $os['subject'] ?? 'Unknown' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="form-hint">Hold Ctrl/Cmd to select multiple</div>
                            </div>
                        </div>
                    </div>
                    <div class="block-alloc-row">
                        <div class="block-alloc-label">Block Allocation per Cycle</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="block-field">
                                    <span class="block-icon single-icon"><i class="fas fa-square"></i></span>
                                    <input type="number"
                                        class="block-input"
                                        name="coupling_groups[{{ $i }}][singles]"
                                        value="{{ $group['singles'] ?? 0 }}"
                                        min="0" max="20">
                                    <span class="block-type-label">Singles</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="block-field">
                                    <span class="block-icon double-icon"><i class="fas fa-th-large"></i></span>
                                    <input type="number"
                                        class="block-input"
                                        name="coupling_groups[{{ $i }}][doubles]"
                                        value="{{ $group['doubles'] ?? 0 }}"
                                        min="0" max="10">
                                    <span class="block-type-label">Doubles</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="block-field">
                                    <span class="block-icon triple-icon"><i class="fas fa-th"></i></span>
                                    <input type="number"
                                        class="block-input"
                                        name="coupling_groups[{{ $i }}][triples]"
                                        value="{{ $group['triples'] ?? 0 }}"
                                        min="0" max="6">
                                    <span class="block-type-label">Triples</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="addCouplingGroupBtn">
            <i class="fas fa-plus me-1"></i> Add Coupling Group
        </button>
    </div>

    {{-- Form Actions --}}
    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="fas fa-save"></i> Save Coupling Groups</span>
            <span class="btn-spinner d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Saving...
            </span>
        </button>
    </div>
</form>

<script>
    const optionalSubjectsByGrade = @json($optionalSubjectsByGrade ?? []);
</script>
