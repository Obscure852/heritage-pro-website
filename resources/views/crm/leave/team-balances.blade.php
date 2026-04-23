@extends('layouts.crm')

@section('title', 'Team Leave Balances')
@section('crm_heading', 'Team Leave Balances')
@section('crm_subheading', 'Leave balance overview for your team members.')

@section('content')
    <div class="crm-stack">
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">{{ $year }}</p>
                    <h2>Team balances</h2>
                </div>
            </div>

            @if ($teamMembers->isEmpty())
                <p class="crm-muted-copy" style="padding: 16px 0;">No team members found.</p>
            @else
                <div class="crm-table-wrap" style="overflow-x: auto;">
                    <table class="crm-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                @foreach ($leaveTypes as $type)
                                    <th style="text-align: center;">
                                        <span style="color: {{ $type->color }};">{{ $type->code }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teamMembers as $member)
                                <tr>
                                    <td><strong>{{ $member->name }}</strong></td>
                                    @foreach ($leaveTypes as $type)
                                        @php
                                            $bal = $member->leaveBalances->firstWhere('leave_type_id', $type->id);
                                            $available = $bal ? $bal->available_days : (float) ($type->default_days_per_year ?? 0);
                                        @endphp
                                        <td style="text-align: center;">
                                            <span title="{{ $type->name }}: {{ number_format($available, 1) }} available">
                                                {{ number_format($available, 1) }}
                                            </span>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
