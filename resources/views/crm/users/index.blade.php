@extends('layouts.crm')

@section('title', 'CRM Users')
@section('crm_heading', 'Users')
@section('crm_subheading', 'Directory, profile data, role allocation, and access history for the internal CRM team.')

@section('crm_header_stats')
    @foreach ($userStats as $stat)
        @include('crm.partials.header-stat', [
            'value' => number_format($stat['value']),
            'label' => $stat['label'],
        ])
    @endforeach
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'Staff Directory',
            'content' => 'Filter the directory by name, department, position, or staff status, then open a profile to manage qualifications, access roles, signatures, and login history.',
        ])

        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find staff records</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.users.index') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="name">Name</label>
                        <input id="name" name="name" value="{{ $filters['name'] }}" placeholder="Search by staff name">
                    </div>
                    <div class="crm-field">
                        <label for="user_status_filter">Status</label>
                        <select id="user_status_filter" name="status">
                            <option value="">All statuses</option>
                            @foreach ($employmentStatuses as $value => $label)
                                <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id">
                            <option value="">All departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected($filters['department_id'] === (string) $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="position_id">Position</label>
                        <select id="position_id" name="position_id">
                            <option value="">All positions</option>
                            @foreach ($positions as $position)
                                <option value="{{ $position->id }}" @selected($filters['position_id'] === (string) $position->id)>{{ $position->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.users.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                    @if ($canAdminUsers)
                        <a href="{{ route('crm.users.settings.index') }}" class="btn btn-light crm-btn-light">
                            <i class="bx bx-cog"></i> Settings
                        </a>
                    @endif
                    @if ($canCreateUsers)
                        <a href="{{ route('crm.users.create') }}" class="btn btn-primary">
                            <i class="bx bx-user-plus"></i> New user
                        </a>
                    @endif
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Internal team</p>
                    <h2>CRM staff directory</h2>
                </div>
            </div>

            @if ($users->isEmpty())
                <div class="crm-empty">No staff records match the current filters.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Staff member</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Reporting to</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr id="crm-user-{{ $user->id }}">
                                    <td>
                                        <strong>{{ $user->name }}</strong>
                                        <span class="crm-muted">{{ $user->email }}</span>
                                        @if ($user->phone)
                                            <span class="crm-muted">{{ $user->phone }}</span>
                                        @endif
                                        @if ($user->personal_payroll_number)
                                            <span class="crm-muted">Payroll: {{ $user->personal_payroll_number }}</span>
                                        @endif
                                        @if ($user->customFilters->isNotEmpty())
                                            <div class="crm-inline">
                                                @foreach ($user->customFilters as $filter)
                                                    <span class="crm-pill muted">{{ $filter->name }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $roles[$user->role] ?? ucfirst($user->role) }}</td>
                                    <td>{{ $user->crm_department_name ?: 'Unassigned' }}</td>
                                    <td>{{ $user->crm_position_name ?: 'Unassigned' }}</td>
                                    <td>{{ $user->crm_reports_to_name ?: 'Not set' }}</td>
                                    <td>
                                        <div class="crm-inline">
                                            <span class="crm-pill {{ $user->active ? 'success' : 'muted' }}">
                                                {{ $user->active ? 'Active account' : 'Inactive account' }}
                                            </span>
                                            @if ($user->employment_status)
                                                <span class="crm-pill primary">{{ $employmentStatuses[$user->employment_status] ?? ucfirst(str_replace('_', ' ', $user->employment_status)) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            @include('crm.partials.view-button', [
                                                'url' => route('crm.users.edit', ['user' => $user, 'tab' => 'profile']),
                                                'label' => 'View user',
                                            ])
                                            @if ($canCreateUsers)
                                                <a href="{{ route('crm.users.edit', $user) }}" class="btn crm-icon-action" title="Edit user" aria-label="Edit user">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if ($canAdminUsers)
                                                @include('crm.partials.delete-button', [
                                                    'action' => route('crm.users.destroy', $user),
                                                    'message' => 'Are you sure you want to permanently delete this CRM user?',
                                                    'label' => 'Delete user',
                                                    'iconOnly' => true,
                                                ])
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @include('crm.partials.pager', ['paginator' => $users])
            @endif
        </section>
    </div>
@endsection
