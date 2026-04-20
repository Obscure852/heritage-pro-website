{{-- Leave Types Tab Content --}}
<div class="help-text mb-4">
    <div class="help-title">Leave Types Management</div>
    <div class="help-content">
        Define and manage leave types available to staff. Each leave type has configurable settings for
        entitlement, documentation requirements, and restrictions. Only active leave types are available
        for staff to request.
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-3">
        <span class="badge bg-primary">{{ $leaveTypeCounts['total'] }} Total</span>
        <span class="badge bg-success">{{ $leaveTypeCounts['active'] }} Active</span>
        <span class="badge bg-secondary">{{ $leaveTypeCounts['inactive'] }} Inactive</span>
    </div>
    @can('manage-leave-types')
        <a href="{{ route('leave.types.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Leave Type
        </a>
    @endcan
</div>

@if($leaveTypes->count() > 0)
    <div class="table-responsive">
        <table class="table table-striped align-middle leave-types-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Entitlement</th>
                    <th>Gender</th>
                    <th class="text-center">Half-Day</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($leaveTypes as $index => $leaveType)
                    <tr data-id="{{ $leaveType->id }}">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if($leaveType->color)
                                <span class="color-indicator me-2" style="background-color: {{ $leaveType->color }};"></span>
                            @endif
                            <strong>{{ $leaveType->code }}</strong>
                        </td>
                        <td>{{ $leaveType->name }}</td>
                        <td>{{ number_format($leaveType->default_entitlement, 1) }} days</td>
                        <td>
                            @if($leaveType->gender_restriction === 'male')
                                <span class="gender-badge gender-male">Male Only</span>
                            @elseif($leaveType->gender_restriction === 'female')
                                <span class="gender-badge gender-female">Female Only</span>
                            @else
                                <span class="gender-badge gender-all">All</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($leaveType->allow_half_day)
                                <i class="fas fa-check text-success"></i>
                            @else
                                <i class="fas fa-times text-muted"></i>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge {{ $leaveType->is_active ? 'status-active' : 'status-inactive' }}">
                                {{ $leaveType->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="action-buttons">
                                @can('manage-leave-types')
                                    <a href="{{ route('leave.policies.index', $leaveType) }}"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="Configure Policies">
                                        <i class="bx bx-cog"></i>
                                    </a>
                                    <a href="{{ route('leave.types.edit', $leaveType) }}"
                                        class="btn btn-sm btn-outline-info"
                                        title="Edit Leave Type">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-warning toggle-status-btn"
                                        data-id="{{ $leaveType->id }}"
                                        data-status="{{ $leaveType->is_active ? '1' : '0' }}"
                                        title="{{ $leaveType->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bx {{ $leaveType->is_active ? 'bx-pause' : 'bx-play' }}"></i>
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="empty-state">
        <i class="fas fa-calendar-alt"></i>
        <h4>No Leave Types Defined</h4>
        <p>Get started by creating your first leave type.</p>
        @can('manage-leave-types')
            <a href="{{ route('leave.types.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Leave Type
            </a>
        @endcan
    </div>
@endif
