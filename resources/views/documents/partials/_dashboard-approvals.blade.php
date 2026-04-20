{{-- DSH-03: Pending Approvals Widget --}}
<div class="widget-header">
    <h6><i class="fas fa-tasks" style="color: #6b7280; margin-right: 8px;"></i>Pending Approvals</h6>
    @if($pendingApprovals->count() > 0)
        <span class="badge bg-warning text-dark" style="font-size: 11px;">{{ $pendingApprovals->count() }}</span>
    @endif
</div>
<div class="widget-body">
    @if($pendingApprovals->isNotEmpty())
        @foreach($pendingApprovals as $approval)
            @php
                $isOverdue = $approval->due_date && $approval->due_date->lt(\Carbon\Carbon::today());
            @endphp
            <div class="widget-item" @if($isOverdue) style="background: #fef2f2; margin: 0 -20px; padding: 10px 20px; border-bottom: 1px solid #fecaca;" @endif>
                <div>
                    <div class="widget-item-title">
                        @if($approval->document)
                            <a href="{{ route('documents.show', $approval->document->id) }}" @if($isOverdue) style="color: #dc2626;" @endif>
                                {{ Str::limit($approval->document->title, 35) }}
                            </a>
                        @else
                            <span style="color: #9ca3af; font-style: italic;">Document unavailable</span>
                        @endif
                    </div>
                    <div class="widget-item-meta">
                        Submitted by {{ $approval->submittedBy->full_name ?? 'Unknown' }}
                    </div>
                </div>
                <div style="text-align: right;">
                    @if($isOverdue)
                        <span class="badge bg-danger" style="font-size: 10px;">Overdue</span>
                    @endif
                    @if($approval->due_date)
                        <div class="widget-item-meta" @if($isOverdue) style="color: #dc2626;" @endif>
                            Due {{ $approval->due_date->format('M d') }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="widget-empty">
            <i class="fas fa-check-circle" style="font-size: 24px; margin-bottom: 8px; display: block; color: #10b981;"></i>
            No pending approvals.
        </div>
    @endif
</div>
