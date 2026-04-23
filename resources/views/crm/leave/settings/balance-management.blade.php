@extends('layouts.crm')

@section('title', 'Balance Management')
@section('crm_heading', 'Leave Balance Management')
@section('crm_subheading', 'View and adjust employee leave balances.')

@section('content')
    <div class="crm-stack">
        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Search employees</h2>
                </div>
            </div>
            <form method="GET" action="{{ route('crm.leave.balance-management') }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    <div class="crm-field">
                        <label for="search">Search</label>
                        <input id="search" name="search" value="{{ request('search') }}" placeholder="Name or email">
                    </div>
                    <div class="crm-field">
                        <label for="year">Year</label>
                        <input type="number" id="year" name="year" value="{{ $year }}" min="2020" max="2030">
                    </div>
                </div>
                <div class="form-actions">
                    <a href="{{ route('crm.leave.balance-management') }}" class="btn btn-light crm-btn-light"><i class="bx bx-reset"></i> Reset</a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply</button>
                </div>
            </form>
        </section>

        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">{{ $year }}</p>
                    <h2>Employee balances</h2>
                </div>
            </div>

            <div class="crm-table-wrap" style="overflow-x: auto;">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            @foreach ($leaveTypes as $type)
                                <th style="text-align: center; min-width: 100px;">
                                    <span style="color: {{ $type->color }};">{{ $type->code }}</span>
                                    <br><span class="crm-muted-copy" style="font-size: 10px; font-weight: normal;">Available / Entitled</span>
                                </th>
                            @endforeach
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    <br><span class="crm-muted-copy">{{ $user->email }}</span>
                                </td>
                                @foreach ($leaveTypes as $type)
                                    @php
                                        $bal = $user->leaveBalances->firstWhere('leave_type_id', $type->id);
                                    @endphp
                                    <td style="text-align: center;">
                                        @if ($bal)
                                            <strong>{{ number_format($bal->available_days, 1) }}</strong>
                                            <span class="crm-muted-copy">/ {{ number_format((float) $bal->entitled_days, 1) }}</span>
                                        @else
                                            <span class="crm-muted-copy">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td>
                                    <button type="button" class="btn btn-light crm-btn-light" style="padding: 2px 8px; font-size: 11px;"
                                            onclick="openAdjustModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                            data-balances='@json($user->leaveBalances->keyBy("leave_type_id")->map(fn ($b) => ["id" => $b->id, "available" => $b->available_days]))'>
                                        <i class="bx bx-edit-alt"></i> Adjust
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $leaveTypes->count() + 2 }}" class="crm-muted-copy" style="text-align: center; padding: 24px;">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 16px;">
                {{ $users->withQueryString()->links() }}
            </div>
        </section>
    </div>

    {{-- Adjust Modal --}}
    <div class="modal fade" id="adjustModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="adjust-form">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Adjust Balance: <span id="adjust-user-name"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="crm-field" style="margin-bottom: 12px;">
                            <label for="adjust-type">Leave Type</label>
                            <select id="adjust-type" onchange="updateAdjustFormAction()">
                                @foreach ($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="crm-field" style="margin-bottom: 12px;">
                            <label for="adjustment">Adjustment (days)</label>
                            <input type="number" id="adjustment" name="adjustment" step="0.5" required placeholder="Positive to add, negative to deduct">
                        </div>
                        <div class="crm-field">
                            <label for="reason">Reason <span class="text-danger">*</span></label>
                            <textarea id="reason" name="reason" rows="2" required placeholder="Reason for adjustment..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let currentUserBalances = {};

function openAdjustModal(userId, userName) {
    document.getElementById('adjust-user-name').textContent = userName;
    const btn = event.currentTarget;
    currentUserBalances = JSON.parse(btn.dataset.balances || '{}');
    updateAdjustFormAction();
    new bootstrap.Modal(document.getElementById('adjustModal')).show();
}

function updateAdjustFormAction() {
    const typeId = document.getElementById('adjust-type').value;
    const balanceData = currentUserBalances[typeId];
    if (balanceData) {
        document.getElementById('adjust-form').action = '{{ url("crm/leave/settings/balances") }}/' + balanceData.id;
    }
}
</script>
@endpush
