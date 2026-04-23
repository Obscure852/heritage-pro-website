@extends('layouts.crm')

@section('title', 'Leave Types')
@section('crm_heading', 'Leave Types')
@section('crm_subheading', 'Configure the types of leave available to employees.')

@section('content')
    <div class="crm-stack">
        <section class="crm-card crm-settings-list-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Configuration</p>
                    <h2>Leave types</h2>
                </div>
                <a href="{{ route('crm.leave.types.create') }}" class="btn btn-primary" style="margin-left: auto;">
                    <i class="bx bx-plus"></i> Add leave type
                </a>
            </div>

            <div class="crm-table-wrap">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Days/Year</th>
                            <th>Carry Over</th>
                            <th>Half Day</th>
                            <th>Paid</th>
                            <th>Gender</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leaveTypes as $type)
                            <tr>
                                <td>
                                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                                        <span style="width: 10px; height: 10px; border-radius: 2px; background: {{ $type->color }};"></span>
                                        <strong>{{ $type->code }}</strong>
                                    </span>
                                </td>
                                <td>{{ $type->name }}</td>
                                <td>{{ $type->default_days_per_year !== null ? number_format((float) $type->default_days_per_year, 1) : 'Unlimited' }}</td>
                                <td>{{ $type->carry_over_limit !== null ? number_format((float) $type->carry_over_limit, 1) . 'd' : '—' }}</td>
                                <td>{{ $type->allow_half_day ? 'Yes' : 'No' }}</td>
                                <td>{{ $type->is_paid ? 'Yes' : 'No' }}</td>
                                <td>{{ $type->gender_restriction ? ucfirst($type->gender_restriction) . ' only' : 'All' }}</td>
                                <td>
                                    <span class="crm-pill {{ $type->is_active ? 'success' : 'muted' }}">
                                        {{ $type->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="crm-table-actions">
                                    <div class="crm-action-row">
                                        <a href="{{ route('crm.leave.types.edit', $type) }}"
                                           class="btn crm-icon-action"
                                           title="Edit leave type"
                                           aria-label="Edit leave type">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @include('crm.partials.delete-button', [
                                            'action' => route('crm.leave.types.destroy', $type),
                                            'message' => 'Are you sure you want to permanently delete this leave type?',
                                            'label' => 'Delete leave type',
                                            'iconOnly' => true,
                                        ])
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; color: #64748b; padding: 24px 16px;">
                                    <i class="bx bx-calendar-x" style="display: block; margin: 0 0 12px; color: #94a3b8; font-size: 30px; line-height: 1;" aria-hidden="true"></i>
                                    <p style="margin: 0;">No leave types have been added yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
