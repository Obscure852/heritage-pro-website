{{-- Elective Coupling Tab Content (CONST-07) --}}
<style>
    .coupling-group-card {
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 20px;
        margin-bottom: 16px;
        background: #fafbfc;
        transition: border-color 0.2s;
    }

    .coupling-group-card:hover {
        border-color: #c7d2fe;
    }

    .coupling-group-card .group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
    }

    .coupling-group-card .group-number {
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

    .coupling-group-card .group-label {
        font-weight: 600;
        font-size: 15px;
        color: #1f2937;
        margin-left: 10px;
    }

    .coupling-group-card .group-detail {
        margin-bottom: 12px;
    }

    .coupling-group-card .group-detail:last-child {
        margin-bottom: 0;
    }

    .coupling-group-card .detail-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        margin-bottom: 6px;
    }

    .coupling-group-card .subject-badge {
        display: inline-block;
        background: #eff6ff;
        color: #1e40af;
        border: 1px solid #bfdbfe;
        border-radius: 3px;
        padding: 4px 10px;
        font-size: 13px;
        font-weight: 500;
        margin-right: 6px;
        margin-bottom: 4px;
    }

    .coupling-group-card .block-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #e5e7eb;
        border-radius: 3px;
        padding: 4px 10px;
        font-size: 13px;
        font-weight: 500;
        margin-right: 6px;
        margin-bottom: 4px;
    }

    .coupling-group-card .block-chip i {
        font-size: 11px;
        color: #6b7280;
    }

    .coupling-empty-state {
        text-align: center;
        padding: 48px 20px;
        color: #9ca3af;
    }

    .coupling-empty-state i {
        font-size: 48px;
        opacity: 0.3;
        margin-bottom: 12px;
    }

    .coupling-empty-state p {
        margin: 0;
        font-size: 14px;
    }

    .coupling-empty-state .small {
        color: #9ca3af;
    }
</style>

<div class="help-text">
    <div class="help-title">Elective Coupling</div>
    <div class="help-content">
        Elective subject coupling is configured in the Period Settings page. Coupling groups define which optional subjects must be scheduled concurrently across classes. Students choose between options in a group, so all subjects in a coupling group are scheduled at the same time.
    </div>
</div>

<div class="settings-section">
    <div class="alert alert-info d-flex align-items-start" role="alert">
        <i class="fas fa-info-circle me-3 mt-1" style="font-size: 18px;"></i>
        <div>
            <strong>Coupling groups are managed in Period Settings.</strong>
            <p class="mb-2 mt-1" style="font-size: 13px; color: #374151;">
                To create, edit, or delete coupling groups, go to the Period Settings page and select the Coupling Groups tab.
            </p>
            <a href="{{ route('timetable.period-settings.index') }}#couplingGroups" class="btn btn-primary btn-sm">
                <i class="fas fa-external-link-alt me-2"></i>Go to Coupling Groups
            </a>
        </div>
    </div>

    @if(!empty($couplingGroups))
        <h6 class="section-title mt-4">Current Coupling Groups</h6>

        @foreach($couplingGroups as $idx => $group)
            <div class="coupling-group-card">
                <div class="group-header">
                    <div class="d-flex align-items-center">
                        <span class="group-number">{{ $idx + 1 }}</span>
                        <span class="group-label">{{ $group['label'] ?? 'Group ' . ($idx + 1) }}</span>
                    </div>
                    @php
                        $parts = [];
                        if (($group['singles'] ?? 0) > 0) $parts[] = ($group['singles']) . ' single(s)';
                        if (($group['doubles'] ?? 0) > 0) $parts[] = ($group['doubles']) . ' double(s)';
                        if (($group['triples'] ?? 0) > 0) $parts[] = ($group['triples']) . ' triple(s)';
                    @endphp
                    @if(!empty($parts))
                        <span style="font-size: 12px; color: #6b7280; font-weight: 500;">
                            <i class="fas fa-th-large me-1"></i>{{ implode(' &middot; ', $parts) }}
                        </span>
                    @endif
                </div>

                <div class="group-detail">
                    <div class="detail-label">Subjects</div>
                    @if(!empty($group['optional_subject_ids']))
                        @php
                            $subjectNames = collect($group['optional_subject_ids'])
                                ->map(fn($id) => $optionalSubjectNames[$id] ?? null)
                                ->filter()
                                ->unique()
                                ->values();
                        @endphp
                        @if($subjectNames->isNotEmpty())
                            @foreach($subjectNames as $name)
                                <span class="subject-badge">{{ $name }}</span>
                            @endforeach
                        @else
                            <span class="text-muted" style="font-size: 13px;">{{ count($group['optional_subject_ids']) }} subject(s)</span>
                        @endif
                    @else
                        <span class="text-muted" style="font-size: 13px;">No subjects assigned</span>
                    @endif
                </div>

                <div class="group-detail">
                    <div class="detail-label">Block Allocation</div>
                    @if(!empty($parts))
                        @if(($group['singles'] ?? 0) > 0)
                            <span class="block-chip"><i class="fas fa-square"></i> {{ $group['singles'] }} Single{{ $group['singles'] > 1 ? 's' : '' }}</span>
                        @endif
                        @if(($group['doubles'] ?? 0) > 0)
                            <span class="block-chip"><i class="fas fa-th-large"></i> {{ $group['doubles'] }} Double{{ $group['doubles'] > 1 ? 's' : '' }}</span>
                        @endif
                        @if(($group['triples'] ?? 0) > 0)
                            <span class="block-chip"><i class="fas fa-th"></i> {{ $group['triples'] }} Triple{{ $group['triples'] > 1 ? 's' : '' }}</span>
                        @endif
                    @else
                        <span class="text-muted" style="font-size: 13px;">Not set</span>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="coupling-empty-state">
            <i class="fas fa-link d-block"></i>
            <p>No coupling groups configured yet.</p>
            <p class="small mt-1">Go to Period Settings to create coupling groups.</p>
        </div>
    @endif
</div>
