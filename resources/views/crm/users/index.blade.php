@extends('layouts.crm')

@section('title', 'CRM Users')
@section('crm_heading', 'Users')
@section('crm_subheading', 'Admin-managed internal CRM access for administrators, managers, and sales representatives.')

@section('crm_actions')
    <a href="{{ route('crm.users.create') }}" class="btn btn-primary">
        <i class="bx bx-user-plus"></i> New user
    </a>
@endsection

@section('content')
    <div class="crm-stack">
        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Find internal users</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.users.index') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="q">Search</label>
                        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Search by name or email">
                    </div>
                    <div class="crm-field">
                        <label for="role">Role</label>
                        <select id="role" name="role">
                            <option value="">All roles</option>
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" @selected($filters['role'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="crm-field">
                        <label for="active">Status</label>
                        <select id="active" name="active">
                            <option value="">All statuses</option>
                            <option value="1" @selected($filters['active'] === '1')>Active</option>
                            <option value="0" @selected($filters['active'] === '0')>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.users.index') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply filters</button>
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Internal team</p>
                    <h2>CRM users</h2>
                </div>
            </div>

            @if ($users->isEmpty())
                <div class="crm-empty">No CRM user records match the current filters.</div>
            @else
                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $roles[$user->role] ?? ucfirst($user->role) }}</td>
                                    <td>
                                        <span class="crm-pill {{ $user->active ? 'success' : 'muted' }}">
                                            {{ $user->active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>{{ $user->created_at?->format('d M Y') ?: 'Unknown' }}</td>
                                    <td class="crm-table-actions">
                                        <div class="crm-action-row">
                                            <a href="{{ route('crm.users.edit', $user) }}" class="btn btn-secondary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            @include('crm.partials.delete-button', [
                                                'action' => route('crm.users.destroy', $user),
                                                'message' => 'Are you sure you want to permanently delete this CRM user?',
                                                'label' => 'Delete',
                                            ])
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
