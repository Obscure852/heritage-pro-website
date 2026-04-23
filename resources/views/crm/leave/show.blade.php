@extends('layouts.crm')

@section('title', 'Leave Request #' . $leaveRequest->id)
@section('crm_heading', 'Leave Request')
@section('crm_subheading', $leaveRequest->leaveType->name . ' — ' . $leaveRequest->user->name)

@section('content')
    <div class="crm-stack">
        <div style="display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 20px; align-items: start;">
            {{-- Main Details --}}
            <div class="crm-stack">
                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Request #{{ $leaveRequest->id }}</p>
                            <h2>{{ $leaveRequest->leaveType->name }}</h2>
                        </div>
                        <div style="margin-left: auto;">
                            @include('crm.leave.partials.status-badge', ['status' => $leaveRequest->status])
                        </div>
                    </div>

                    <div class="crm-detail-grid">
                        <div class="crm-detail-item">
                            <span class="crm-detail-label">Applicant</span>
                            <span class="crm-detail-value">{{ $leaveRequest->user->name }}</span>
                        </div>
                        <div class="crm-detail-item">
                            <span class="crm-detail-label">Leave Type</span>
                            <span class="crm-detail-value">
                                <span class="crm-pill" style="background: {{ $leaveRequest->leaveType->color }}20; color: {{ $leaveRequest->leaveType->color }};">
                                    {{ $leaveRequest->leaveType->name }}
                                </span>
                            </span>
                        </div>
                        <div class="crm-detail-item">
                            <span class="crm-detail-label">Start Date</span>
                            <span class="crm-detail-value">
                                {{ $leaveRequest->start_date->format('d M Y') }}
                                @if ($leaveRequest->start_half !== 'full')
                                    ({{ str_replace('_', ' ', ucfirst($leaveRequest->start_half)) }})
                                @endif
                            </span>
                        </div>
                        <div class="crm-detail-item">
                            <span class="crm-detail-label">End Date</span>
                            <span class="crm-detail-value">
                                {{ $leaveRequest->end_date->format('d M Y') }}
                                @if ($leaveRequest->end_half !== 'full')
                                    ({{ str_replace('_', ' ', ucfirst($leaveRequest->end_half)) }})
                                @endif
                            </span>
                        </div>
                        <div class="crm-detail-item">
                            <span class="crm-detail-label">Total Days</span>
                            <span class="crm-detail-value"><strong>{{ number_format((float) $leaveRequest->total_days, 1) }}</strong></span>
                        </div>
                        <div class="crm-detail-item">
                            <span class="crm-detail-label">Submitted</span>
                            <span class="crm-detail-value">{{ $leaveRequest->submitted_at?->format('d M Y H:i') ?? '—' }}</span>
                        </div>
                    </div>

                    <div style="margin-top: 16px;">
                        <span class="crm-detail-label">Reason</span>
                        <p style="margin-top: 4px;">{{ $leaveRequest->reason }}</p>
                    </div>

                    @if ($leaveRequest->rejection_reason)
                        <div style="margin-top: 16px; padding: 12px; background: #fef2f2; border-radius: 3px; border-left: 3px solid #f06548;">
                            <span class="crm-detail-label" style="color: #f06548;">Rejection Reason</span>
                            <p style="margin-top: 4px;">{{ $leaveRequest->rejection_reason }}</p>
                        </div>
                    @endif

                    @if ($leaveRequest->cancellation_reason)
                        <div style="margin-top: 16px; padding: 12px; background: #f9fafb; border-radius: 3px; border-left: 3px solid #6b7280;">
                            <span class="crm-detail-label">Cancellation Reason</span>
                            <p style="margin-top: 4px;">{{ $leaveRequest->cancellation_reason }}</p>
                        </div>
                    @endif
                </section>

                {{-- Attachments --}}
                @if ($leaveRequest->attachments->isNotEmpty())
                    <section class="crm-card">
                        <div class="crm-card-title">
                            <div>
                                <p class="crm-kicker">Files</p>
                                <h2>Attachments</h2>
                            </div>
                        </div>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            @foreach ($leaveRequest->attachments as $attachment)
                                <li style="padding: 8px 0; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; gap: 8px;">
                                    <i class="bx bx-file" style="color: #64748b;"></i>
                                    <span>{{ $attachment->original_name }}</span>
                                    <span class="crm-muted-copy" style="margin-left: auto;">{{ number_format($attachment->size_bytes / 1024, 0) }}KB</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                {{-- Approval Actions (for approver) --}}
                @if ($leaveRequest->isPending() && ($leaveRequest->current_approver_id === auth()->id() || auth()->user()->canAccessCrmModule('leave', 'admin')))
                    <section class="crm-card" style="border: 2px solid #f7b84b;">
                        <div class="crm-card-title">
                            <div>
                                <p class="crm-kicker">Action Required</p>
                                <h2>Review this request</h2>
                            </div>
                        </div>

                        <div style="display: flex; gap: 12px;">
                            <form method="POST" action="{{ route('crm.leave.approvals.review', $leaveRequest) }}" id="approve-form" style="flex: 1;">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="action" value="approve">
                                <div class="crm-field" style="margin-bottom: 12px;">
                                    <label for="approve-comment">Comment (optional)</label>
                                    <textarea id="approve-comment" name="comment" rows="2" placeholder="Optional comment..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-loading" style="background: linear-gradient(135deg, #0ab39c, #059669); width: 100%;">
                                    <span class="btn-text"><i class="bx bx-check"></i> Approve</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Approving...
                                    </span>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('crm.leave.approvals.review', $leaveRequest) }}" id="reject-form" style="flex: 1;">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="action" value="reject">
                                <div class="crm-field" style="margin-bottom: 12px;">
                                    <label for="reject-reason">Reason <span class="text-danger">*</span></label>
                                    <textarea id="reject-reason" name="reason" rows="2" placeholder="Reason for rejection..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-loading" style="background: linear-gradient(135deg, #f06548, #dc2626); width: 100%;">
                                    <span class="btn-text"><i class="bx bx-x"></i> Reject</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Rejecting...
                                    </span>
                                </button>
                            </form>
                        </div>
                    </section>
                @endif

                {{-- Cancel action (for applicant) --}}
                @if ($leaveRequest->canBeCancelled() && $leaveRequest->user_id === auth()->id())
                    <section class="crm-card">
                        <form method="POST" action="{{ route('crm.leave.cancel', $leaveRequest) }}" id="cancel-form">
                            @csrf
                            @method('PUT')
                            <div class="crm-field" style="margin-bottom: 12px;">
                                <label for="cancel-reason">Cancellation Reason</label>
                                <textarea id="cancel-reason" name="reason" rows="2" placeholder="Optional reason for cancellation..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-light crm-btn-light" onclick="return confirm('Are you sure you want to cancel this leave request?')">
                                <i class="bx bx-x-circle"></i> Cancel Request
                            </button>
                        </form>
                    </section>
                @endif
            </div>

            {{-- Sidebar: Approval Trail --}}
            <div class="crm-stack">
                <section class="crm-card">
                    <div class="crm-card-title">
                        <div>
                            <p class="crm-kicker">Timeline</p>
                            <h2>Approval trail</h2>
                        </div>
                    </div>

                    @if ($leaveRequest->approvalTrail->isEmpty())
                        <p class="crm-muted-copy">No activity yet.</p>
                    @else
                        <div style="display: grid; gap: 12px;">
                            @foreach ($leaveRequest->approvalTrail as $trail)
                                <div style="padding: 10px; background: #f9fafb; border-radius: 3px; border-left: 3px solid {{ $trail->action === 'approved' ? '#0ab39c' : ($trail->action === 'rejected' ? '#f06548' : ($trail->action === 'escalated' ? '#f7b84b' : '#3b82f6')) }};">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <strong style="font-size: 13px;">{{ ucfirst($trail->action) }}</strong>
                                        <span class="crm-muted-copy" style="font-size: 11px;">{{ $trail->created_at->format('d M H:i') }}</span>
                                    </div>
                                    <div class="crm-muted-copy" style="font-size: 12px; margin-top: 2px;">
                                        {{ $trail->user->name }}
                                        @if ($trail->comment)
                                            — {{ $trail->comment }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                @if ($leaveRequest->currentApprover && $leaveRequest->isPending())
                    <section class="crm-card">
                        <div class="crm-card-title">
                            <div>
                                <p class="crm-kicker">Waiting on</p>
                                <h2>Current approver</h2>
                            </div>
                        </div>
                        <p><strong>{{ $leaveRequest->currentApprover->name }}</strong></p>
                        <p class="crm-muted-copy">Escalation level: {{ $leaveRequest->escalation_level }}</p>
                    </section>
                @endif
            </div>
        </div>

        <div style="margin-top: 12px;">
            <a href="{{ route('crm.leave.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Back to Leave</a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    ['approve-form', 'reject-form'].forEach(function(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function() {
                const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        }
    });
});
</script>
@endpush
