<style>
    .code-stat-item {
        padding: 10px 0;
    }

    .code-stat-item h4 {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .code-stat-item small {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-transform: capitalize;
    }

    .status-active { background: #d1fae5; color: #065f46; }
    .status-inactive { background: #fee2e2; color: #991b1b; }

    .code-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 28px;
        padding: 0 10px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 13px;
        color: white;
    }

    .code-action-buttons {
        display: flex;
        gap: 4px;
        justify-content: flex-end;
    }

    .code-action-buttons .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        transition: all 0.2s ease;
    }

    .code-action-buttons .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .code-action-buttons .btn i {
        font-size: 16px;
    }

    .present-yes { color: #10b981; }
    .present-no { color: #ef4444; }

    .color-preview {
        width: 24px;
        height: 24px;
        border-radius: 4px;
        display: inline-block;
        vertical-align: middle;
        margin-left: 8px;
        border: 1px solid #e5e7eb;
    }

    .codes-stats-bar {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        color: white;
        padding: 16px 20px;
        border-radius: 3px;
        margin-bottom: 20px;
    }
</style>

<div class="help-text">
    <div class="help-title">Attendance Code Management</div>
    <div class="help-content">
        Configure attendance status codes used for recording staff attendance. Each code has a color for visual identification
        and a "counts as present" flag that determines if the code is counted towards attendance percentage calculations.
    </div>
</div>

{{-- Stats Bar --}}
<div class="codes-stats-bar">
    <div class="row text-center">
        <div class="col-4">
            <div class="code-stat-item">
                <h4 class="mb-0 fw-bold text-white">{{ $codeStats['total'] }}</h4>
                <small class="opacity-75">Total Codes</small>
            </div>
        </div>
        <div class="col-4">
            <div class="code-stat-item">
                <h4 class="mb-0 fw-bold text-white">{{ $codeStats['active'] }}</h4>
                <small class="opacity-75">Active</small>
            </div>
        </div>
        <div class="col-4">
            <div class="code-stat-item">
                <h4 class="mb-0 fw-bold text-white">{{ $codeStats['present_codes'] }}</h4>
                <small class="opacity-75">Present Codes</small>
            </div>
        </div>
    </div>
</div>

<div class="row align-items-center mb-3">
    <div class="col-md-6">
        <h5 class="mb-0">Configured Codes</h5>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCodeModal">
            <i class="fas fa-plus me-1"></i> Add Code
        </button>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th style="width: 60px;">Order</th>
                <th style="width: 100px;">Code</th>
                <th>Name</th>
                <th>Description</th>
                <th style="width: 100px;">Color</th>
                <th style="width: 120px;">Counts as Present</th>
                <th style="width: 100px;">Status</th>
                <th class="text-end" style="width: 140px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($codes as $code)
                <tr>
                    <td>{{ $code->order }}</td>
                    <td>
                        <span class="code-badge" style="background-color: {{ $code->color }};">
                            {{ $code->code }}
                        </span>
                    </td>
                    <td><strong>{{ $code->name }}</strong></td>
                    <td class="text-muted">{{ $code->description ?? '-' }}</td>
                    <td>
                        <span class="color-preview" style="background-color: {{ $code->color }};"></span>
                        <span class="text-muted" style="font-size: 12px;">{{ $code->color }}</span>
                    </td>
                    <td>
                        @if ($code->counts_as_present)
                            <i class="fas fa-check-circle present-yes"></i> Yes
                        @else
                            <i class="fas fa-times-circle present-no"></i> No
                        @endif
                    </td>
                    <td>
                        <span class="status-badge status-{{ $code->is_active ? 'active' : 'inactive' }}">
                            {{ $code->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="code-action-buttons">
                            <button type="button"
                                class="btn btn-sm btn-outline-info"
                                onclick="editCode({{ $code->id }}, {{ json_encode($code) }})"
                                title="Edit Code">
                                <i class="bx bx-edit-alt"></i>
                            </button>
                            <form action="{{ route('staff-attendance.codes.toggle', $code) }}"
                                method="POST" style="display: inline;">
                                @csrf
                                <button type="submit"
                                    class="btn btn-sm btn-outline-{{ $code->is_active ? 'warning' : 'success' }}"
                                    title="{{ $code->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="bx bx-{{ $code->is_active ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                onclick="confirmDeleteCode({{ $code->id }}, '{{ $code->code }}')"
                                title="Delete Code">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                        <form id="delete-code-form-{{ $code->id }}"
                            action="{{ route('staff-attendance.codes.destroy', $code) }}"
                            method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <div class="text-center text-muted" style="padding: 40px 0;">
                            <i class="bx bx-tag" style="font-size: 48px; opacity: 0.3;"></i>
                            <p class="mt-3 mb-0" style="font-size: 15px;">No attendance codes configured</p>
                            <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addCodeModal">
                                <i class="fas fa-plus me-1"></i> Add Your First Code
                            </button>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add Code Modal -->
<div class="modal fade" id="addCodeModal" tabindex="-1" aria-labelledby="addCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('staff-attendance.codes.store') }}" method="POST" id="addCodeForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addCodeModalLabel">Add Attendance Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="add_code" class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_code" name="code"
                                maxlength="10" required placeholder="e.g., P, A, L">
                            <small class="text-muted">Short code (max 10 chars)</small>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="add_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add_name" name="name"
                                maxlength="50" required placeholder="e.g., Present">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add_description" class="form-label">Description</label>
                        <textarea class="form-control" id="add_description" name="description"
                            rows="2" maxlength="255" placeholder="Optional description"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_color" class="form-label">Color <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="add_color_picker"
                                    value="#10b981" style="width: 50px;">
                                <input type="text" class="form-control" id="add_color" name="color"
                                    value="#10b981" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="add_order" name="order"
                                min="0" placeholder="Auto-assigned if empty">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="add_counts_as_present"
                                    name="counts_as_present" value="1" checked>
                                <label class="form-check-label" for="add_counts_as_present">
                                    Counts as Present
                                </label>
                            </div>
                            <small class="text-muted">Include in attendance percentage</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="add_is_active"
                                    name="is_active" value="1" checked>
                                <label class="form-check-label" for="add_is_active">
                                    Active
                                </label>
                            </div>
                            <small class="text-muted">Available for use</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save me-1"></i> Save Code</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Code Modal -->
<div class="modal fade" id="editCodeModal" tabindex="-1" aria-labelledby="editCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="POST" id="editCodeForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editCodeModalLabel">Edit Attendance Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_code" class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_code" name="code"
                                maxlength="10" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name"
                                maxlength="50" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description"
                            rows="2" maxlength="255"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_color" class="form-label">Color <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="edit_color_picker"
                                    style="width: 50px;">
                                <input type="text" class="form-control" id="edit_color" name="color"
                                    maxlength="7" pattern="^#[0-9A-Fa-f]{6}$" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="edit_order" name="order" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="edit_counts_as_present"
                                    name="counts_as_present" value="1">
                                <label class="form-check-label" for="edit_counts_as_present">
                                    Counts as Present
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="edit_is_active"
                                    name="is_active" value="1">
                                <label class="form-check-label" for="edit_is_active">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-loading">
                        <span class="btn-text"><i class="fas fa-save me-1"></i> Update Code</span>
                        <span class="btn-spinner d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
