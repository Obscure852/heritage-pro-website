<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('welfare.cases.assign', $case) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Assign Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Assign To</label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">Select Staff Member</option>
                            @foreach(\App\Models\User::where('status', 'Current')->orderBy('firstname')->get() as $staff)
                                <option value="{{ $staff->id }}" {{ $case->assigned_to == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->full_name }} ({{ $staff->position ?? 'Staff' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Escalate Modal -->
<div class="modal fade" id="escalateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('welfare.cases.escalate', $case) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Escalate Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle me-2"></i>
                        Escalating will mark this case as high priority and notify relevant staff.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign To (Optional)</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Keep Current Assignment</option>
                            @foreach(\App\Models\User::where('status', 'Current')->orderBy('firstname')->get() as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Escalation</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Explain why this case needs escalation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Escalate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('welfare.cases.approve', $case) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Approve Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Approval Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes for this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('welfare.cases.reject', $case) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Explain why this case is being rejected..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close Modal -->
<div class="modal fade" id="closeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('welfare.cases.close', $case) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Close Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Closing Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Summary of case resolution..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Close Case</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reopen Modal -->
<div class="modal fade" id="reopenModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('welfare.cases.reopen', $case) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reopen Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason for Reopening <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Explain why this case needs to be reopened..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Reopen Case</button>
                </div>
            </form>
        </div>
    </div>
</div>
