{{--
    Audit History Timeline Partial

    Displays audit trail for leave requests and balances.

    Required variables:
    - $auditLogs: Collection of LeaveAuditLog models
    - $auditService: LeaveAuditService instance for formatting changes
--}}

<style>
    .audit-history {
        margin-top: 24px;
    }

    .audit-history .section-title {
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 16px 0;
        color: #1f2937;
        padding-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
    }

    .audit-timeline {
        position: relative;
        padding-left: 30px;
    }

    .audit-timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }

    .audit-timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .audit-timeline-item:last-child {
        padding-bottom: 0;
    }

    .audit-timeline-marker {
        position: absolute;
        left: -25px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #e5e7eb;
        border: 2px solid white;
    }

    /* Action-specific marker colors */
    .audit-timeline-marker.create {
        background: #10b981;
    }

    .audit-timeline-marker.update {
        background: #3b82f6;
    }

    .audit-timeline-marker.approve {
        background: #10b981;
    }

    .audit-timeline-marker.reject {
        background: #ef4444;
    }

    .audit-timeline-marker.cancel {
        background: #6b7280;
    }

    .audit-timeline-marker.adjust {
        background: #f59e0b;
    }

    .audit-timeline-marker.allocate {
        background: #8b5cf6;
    }

    .audit-timeline-marker.accrue {
        background: #06b6d4;
    }

    .audit-timeline-marker.carryover {
        background: #ec4899;
    }

    .audit-timeline-content {
        background: #f9fafb;
        border-radius: 6px;
        padding: 12px 16px;
    }

    .audit-timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .audit-action-badge {
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        text-transform: capitalize;
    }

    .audit-action-badge.badge-create {
        background: #d1fae5;
        color: #065f46;
    }

    .audit-action-badge.badge-update {
        background: #dbeafe;
        color: #1e40af;
    }

    .audit-action-badge.badge-approve {
        background: #d1fae5;
        color: #065f46;
    }

    .audit-action-badge.badge-reject {
        background: #fee2e2;
        color: #991b1b;
    }

    .audit-action-badge.badge-cancel {
        background: #f3f4f6;
        color: #4b5563;
    }

    .audit-action-badge.badge-adjust {
        background: #fef3c7;
        color: #92400e;
    }

    .audit-action-badge.badge-allocate {
        background: #ede9fe;
        color: #5b21b6;
    }

    .audit-action-badge.badge-accrue {
        background: #cffafe;
        color: #0e7490;
    }

    .audit-action-badge.badge-carryover {
        background: #fce7f3;
        color: #9d174d;
    }

    .audit-timestamp {
        font-size: 12px;
        color: #6b7280;
    }

    .audit-timeline-body {
        font-size: 13px;
    }

    .audit-user {
        color: #374151;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .audit-ip-address {
        font-size: 11px;
        color: #9ca3af;
    }

    .audit-notes {
        color: #4b5563;
        margin: 8px 0;
        font-style: italic;
        padding: 8px;
        background: white;
        border-radius: 4px;
        border-left: 3px solid #3b82f6;
    }

    .audit-changes {
        margin-top: 8px;
    }

    .audit-changes-title {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .audit-change-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .audit-change-list li {
        padding: 4px 8px;
        background: white;
        border-radius: 4px;
        margin-bottom: 4px;
        font-size: 12px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
    }

    .audit-change-list li strong {
        color: #374151;
    }

    .audit-old-value {
        color: #991b1b;
        text-decoration: line-through;
    }

    .audit-new-value {
        color: #065f46;
    }

    .audit-empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6b7280;
    }

    .audit-empty-state i {
        font-size: 36px;
        opacity: 0.3;
        margin-bottom: 12px;
    }
</style>

@if($auditLogs->isNotEmpty())
<div class="audit-history">
    <h5 class="section-title">
        <i class="fas fa-history me-2"></i>Audit Trail
    </h5>
    <div class="audit-timeline">
        @foreach($auditLogs as $log)
        <div class="audit-timeline-item">
            <div class="audit-timeline-marker {{ $log->action }}"></div>
            <div class="audit-timeline-content">
                <div class="audit-timeline-header">
                    <span class="audit-action-badge badge-{{ $log->action }}">
                        {{ $log->action_label }}
                    </span>
                    <span class="audit-timestamp">
                        {{ $log->created_at->format('M d, Y H:i') }}
                    </span>
                </div>
                <div class="audit-timeline-body">
                    <p class="audit-user mb-1">
                        <i class="fas fa-user"></i>
                        {{ $log->user?->name ?? 'System' }}
                        @if($log->ip_address)
                        <span class="audit-ip-address">({{ $log->ip_address }})</span>
                        @endif
                    </p>
                    @if($log->notes)
                    <div class="audit-notes">{{ $log->notes }}</div>
                    @endif
                    @php
                        $changes = $auditService->formatChanges($log->old_values, $log->new_values);
                    @endphp
                    @if(count($changes) > 0 && $log->action !== 'create')
                    <div class="audit-changes">
                        <small class="audit-changes-title">Changes:</small>
                        <ul class="audit-change-list">
                            @foreach($changes as $change)
                            <li>
                                <strong>{{ $change['field'] }}:</strong>
                                <span class="audit-old-value">{{ $change['old_value'] }}</span>
                                <i class="fas fa-arrow-right mx-1" style="font-size: 10px; color: #9ca3af;"></i>
                                <span class="audit-new-value">{{ $change['new_value'] }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@else
<div class="audit-history">
    <h5 class="section-title">
        <i class="fas fa-history me-2"></i>Audit Trail
    </h5>
    <div class="audit-empty-state">
        <i class="fas fa-history"></i>
        <p>No audit history available.</p>
    </div>
</div>
@endif
