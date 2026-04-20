@extends('layouts.master')

@section('title')
    Activity Staff
@endsection

@section('css')
    @include('activities.partials.theme')
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted font-size-14" href="{{ route('activities.index') }}">Activities</a>
        @endslot
        @slot('title')
            {{ $activity->name }} Staff
        @endslot
    @endcomponent

    @include('activities.partials.alerts')

    @php
        $activeAssignments = $assignments->where('active', true)->values();
        $historicalAssignments = $assignments->where('active', false)->values();
    @endphp

    <div class="form-container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Activity Staff</h1>
                <p class="page-subtitle">Assign operational owners, preserve retirement history, and maintain one primary coordinator.</p>
            </div>
        </div>

        @include('activities.partials.subnav', ['activity' => $activity, 'current' => 'staff'])

        <div class="help-text">
            <div class="help-title">Staff Ownership</div>
            <div class="help-content">
                Use this page to assign coordinators, coaches, and supporting staff. Keep one primary coordinator active at a time, and retain assignment history when staffing changes.
            </div>
        </div>

        <div class="management-grid">
            <div class="section-stack">
                <div class="card-shell">
                    <div class="card-body p-4">
                        <div class="management-header">
                            <div>
                                <h5 class="summary-card-title mb-0">Active Staff Assignments</h5>
                                <p class="management-subtitle">{{ $activity->active_staff_assignments_count }} active assignment(s) linked to this activity.</p>
                            </div>
                        </div>

                        @if ($activeAssignments->isNotEmpty())
                            <div class="management-list">
                                @foreach ($activeAssignments as $assignment)
                                    <div class="management-item">
                                        <div class="management-item-header">
                                            <div>
                                                <div class="management-item-title">{{ $assignment->user?->full_name ?: 'Unknown staff member' }}</div>
                                                <div class="management-item-meta">
                                                    <span class="summary-chip pill-muted">
                                                        {{ $staffRoles[$assignment->role] ?? ucfirst($assignment->role) }}
                                                    </span>
                                                    @if ($assignment->is_primary)
                                                        <span class="summary-chip pill-primary">
                                                            <i class="fas fa-star"></i> Primary Coordinator
                                                        </span>
                                                    @endif
                                                    <span class="summary-chip pill-muted">
                                                        <i class="fas fa-clock"></i>
                                                        {{ optional($assignment->assigned_at)->format('d M Y, H:i') ?: 'Assigned now' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <form method="POST"
                                                action="{{ route('activities.staff.destroy', [$activity, $assignment]) }}"
                                                class="inline-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-user-minus me-1"></i> Remove
                                                </button>
                                            </form>
                                        </div>

                                        @if ($assignment->notes)
                                            <div class="management-item-notes">{{ $assignment->notes }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="summary-empty mb-0">No active staff assignments have been added yet.</p>
                        @endif
                    </div>
                </div>

                <div class="card-shell">
                    <div class="card-body p-4">
                        <div class="management-header">
                            <div>
                                <h5 class="summary-card-title mb-0">Assignment History</h5>
                                <p class="management-subtitle">Removed staff remain on the record for audit and operational traceability.</p>
                            </div>
                        </div>

                        @if ($historicalAssignments->isNotEmpty())
                            <div class="management-list">
                                @foreach ($historicalAssignments as $assignment)
                                    <div class="management-item">
                                        <div class="management-item-title">{{ $assignment->user?->full_name ?: 'Unknown staff member' }}</div>
                                        <div class="management-item-meta">
                                            <span class="summary-chip pill-muted">
                                                {{ $staffRoles[$assignment->role] ?? ucfirst($assignment->role) }}
                                            </span>
                                            <span class="summary-chip pill-muted">
                                                Removed {{ optional($assignment->removed_at)->format('d M Y, H:i') ?: 'n/a' }}
                                            </span>
                                        </div>
                                        @if ($assignment->notes)
                                            <div class="management-item-notes">{{ $assignment->notes }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="summary-empty mb-0">No retired staff assignments are recorded yet.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-shell">
                <div class="card-body p-4">
                    <h5 class="summary-card-title">Assign Staff Member</h5>
                    <p class="management-subtitle">Use the role list below and mark the primary coordinator only when appropriate.</p>

                    <form action="{{ route('activities.staff.store', $activity) }}" method="POST" id="activity-staff-form" class="needs-validation" novalidate data-activity-form>
                        @csrf

                        <div class="form-group mb-3">
                            <label class="form-label" for="user_id">Staff Member <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                <option value="">Select staff member</option>
                                @foreach ($availableUsers as $user)
                                    <option value="{{ $user->id }}" {{ (string) old('user_id') === (string) $user->id ? 'selected' : '' }}>
                                        {{ $user->full_name }}{{ $user->position ? ' - ' . $user->position : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                <option value="">Select role</option>
                                @foreach ($staffRoles as $key => $label)
                                    <option value="{{ $key }}" {{ old('role') === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="option-card mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_primary" name="is_primary"
                                    value="1" {{ old('is_primary') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_primary">Primary coordinator</label>
                                <span class="option-help">Primary is only valid for the coordinator role and will replace any existing primary coordinator.</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="notes">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                id="notes"
                                name="notes"
                                rows="4"
                                placeholder="Add optional context for this assignment.">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('activities.show', $activity) }}" class="btn btn-secondary">
                                <i class="bx bx-x"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-loading">
                                <span class="btn-text"><i class="fas fa-save"></i> Save Assignment</span>
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
    </div>
@endsection

@section('script')
    @include('activities.partials.form-script')
@endsection
