@extends('layouts.crm')

@section('title', 'My Leave Balances')
@section('crm_heading', 'My Leave Balances')
@section('crm_subheading', 'Detailed view of your leave entitlements for ' . $year . '.')

@section('content')
    <div class="crm-stack">
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">{{ $year }}</p>
                    <h2>Leave balance breakdown</h2>
                </div>
            </div>

            <div class="crm-table-wrap">
                <table class="crm-table">
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th style="text-align: right;">Entitled</th>
                            <th style="text-align: right;">Carried Over</th>
                            <th style="text-align: right;">Adjustments</th>
                            <th style="text-align: right;">Used</th>
                            <th style="text-align: right;">Pending</th>
                            <th style="text-align: right;">Available</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($balances as $balance)
                            <tr>
                                <td>
                                    <span class="crm-pill" style="background: {{ $balance->leaveType->color }}20; color: {{ $balance->leaveType->color }};">
                                        {{ $balance->leaveType->name }}
                                    </span>
                                </td>
                                <td style="text-align: right;">{{ number_format((float) $balance->entitled_days, 1) }}</td>
                                <td style="text-align: right;">{{ number_format((float) $balance->carried_over_days, 1) }}</td>
                                <td style="text-align: right;">{{ number_format((float) $balance->adjustment_days, 1) }}</td>
                                <td style="text-align: right;">{{ number_format((float) $balance->used_days, 1) }}</td>
                                <td style="text-align: right;">{{ number_format((float) $balance->pending_days, 1) }}</td>
                                <td style="text-align: right;"><strong>{{ number_format($balance->available_days, 1) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
