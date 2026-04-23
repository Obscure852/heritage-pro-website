@extends('layouts.crm')

@section('title', 'Team Leave Calendar')
@section('crm_heading', 'Team Calendar')
@section('crm_subheading', 'Monthly view of approved and pending leave across your team.')

@section('crm_actions')
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="bx bx-bar-chart-alt-2"></i> Reports
        </button>
        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 260px;">
            <li><a class="dropdown-item" href="{{ route('crm.leave.reports') }}"><i class="bx bx-pie-chart-alt-2 me-2" style="color: #64748b;"></i> Leave Usage Summary</a></li>
            <li><a class="dropdown-item" href="{{ route('crm.leave.team-balances') }}"><i class="bx bx-wallet me-2" style="color: #64748b;"></i> Team Balances</a></li>
        </ul>
    </div>
@endsection

@section('content')
    @php
        $currentDate = \Illuminate\Support\Carbon::create($year, $month, 1);
        $daysInMonth = $currentDate->daysInMonth;
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();
        $today = now()->startOfDay();
    @endphp

    <div class="crm-stack">
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Team Calendar</p>
                    <h2>{{ $currentDate->format('F Y') }}</h2>
                </div>
                <div style="margin-left: auto; display: flex; gap: 8px;">
                    <a href="{{ route('crm.leave.team-calendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="btn btn-light crm-btn-light"><i class="bx bx-chevron-left"></i></a>
                    <a href="{{ route('crm.leave.team-calendar', ['month' => now()->month, 'year' => now()->year]) }}" class="btn btn-light crm-btn-light">Today</a>
                    <a href="{{ route('crm.leave.team-calendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="btn btn-light crm-btn-light"><i class="bx bx-chevron-right"></i></a>
                </div>
            </div>

            @if ($teamMembers->isEmpty())
                <p class="crm-muted-copy" style="padding: 16px 0;">No team members found.</p>
            @else
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr>
                                <th style="position: sticky; left: 0; background: #fff; z-index: 2; min-width: 160px; padding: 8px 12px; text-align: left; border-bottom: 2px solid #e5e7eb;">Employee</th>
                                @for ($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $dayDate = \Illuminate\Support\Carbon::create($year, $month, $d);
                                        $isWeekend = $dayDate->isWeekend();
                                        $isToday = $dayDate->isSameDay($today);
                                    @endphp
                                    <th style="text-align: center; min-width: 38px; padding: 6px 2px; font-size: 11px; border-bottom: 2px solid #e5e7eb;
                                        {{ $isWeekend ? 'background: #f3f4f6; color: #9ca3af;' : '' }}
                                        {{ $isToday ? 'background: #eff6ff; color: #2563eb; font-weight: 700;' : '' }}">
                                        {{ $d }}<br>{{ substr($dayDate->format('D'), 0, 2) }}
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teamMembers as $member)
                                @php
                                    $memberLeaves = $leaveRequests->where('user_id', $member->id);
                                @endphp
                                <tr>
                                    <td style="position: sticky; left: 0; background: #fff; z-index: 1; padding: 6px 12px; border-bottom: 1px solid #f3f4f6; font-weight: 500; white-space: nowrap;">
                                        {{ $member->name }}
                                    </td>
                                    @for ($d = 1; $d <= $daysInMonth; $d++)
                                        @php
                                            $dayDate = \Illuminate\Support\Carbon::create($year, $month, $d);
                                            $isWeekend = $dayDate->isWeekend();
                                            $isToday = $dayDate->isSameDay($today);
                                            $leave = $memberLeaves->first(fn ($l) => $dayDate->between($l->start_date, $l->end_date));
                                        @endphp
                                        <td style="text-align: center; padding: 4px 2px; border-bottom: 1px solid #f3f4f6;
                                            {{ $isWeekend ? 'background: #f9fafb;' : '' }}
                                            {{ $isToday && !$leave ? 'background: #eff6ff;' : '' }}
                                            {{ $leave ? 'background: ' . $leave->leaveType->color . ($leave->status === 'pending' ? '20' : '30') . ';' : '' }}">
                                            @if ($leave)
                                                <a href="{{ route('crm.leave.show', $leave) }}"
                                                   title="{{ $leave->leaveType->name }} ({{ ucfirst($leave->status) }})"
                                                   style="display: block; color: {{ $leave->leaveType->color }}; font-size: 10px; font-weight: 600; text-decoration: none; line-height: 1.2; padding: 2px 0;
                                                   {{ $leave->status === 'pending' ? 'opacity: 0.7; font-style: italic;' : '' }}">
                                                    {{ $leave->leaveType->code }}
                                                </a>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Legend --}}
                <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #f3f4f6; display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
                    <span style="font-size: 12px; color: #6b7280; font-weight: 600;">Legend:</span>
                    @foreach (\App\Models\CrmLeaveType::active()->ordered()->get() as $type)
                        <div style="display: flex; align-items: center; gap: 5px; font-size: 12px;">
                            <span style="display: inline-block; width: 14px; height: 14px; border-radius: 2px; background: {{ $type->color }};"></span>
                            <span style="color: {{ $type->color }}; font-weight: 600;">{{ $type->code }}</span>
                            {{ $type->name }}
                        </div>
                    @endforeach
                    <div style="display: flex; align-items: center; gap: 5px; font-size: 12px; color: #9ca3af;">
                        <span style="display: inline-block; width: 14px; height: 14px; border-radius: 2px; background: #9ca3af40;"></span>
                        <em>Italic = Pending</em>
                    </div>
                </div>

                {{-- Summary --}}
                @if ($leaveRequests->isNotEmpty())
                    <div style="margin-top: 12px; padding: 12px; background: #f8f9fa; border-radius: 3px; border-left: 3px solid #3b82f6;">
                        <span style="font-size: 12px; color: #6b7280;">
                            <strong>{{ $leaveRequests->where('status', 'approved')->count() }}</strong> approved and
                            <strong>{{ $leaveRequests->where('status', 'pending')->count() }}</strong> pending leave request(s) this month.
                        </span>
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
