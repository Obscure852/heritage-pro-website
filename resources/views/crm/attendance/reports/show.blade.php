@extends('layouts.crm')

@section('title', ($data['title'] ?? 'Report') . ' — Attendance Reports')
@section('crm_heading', $data['title'] ?? 'Attendance Report')
@section('crm_subheading', 'Use the filters to adjust the report scope, then export to Excel.')

@section('content')
    <div class="crm-stack">
        {{-- Filter Card --}}
        <section class="crm-card crm-filter-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Filters</p>
                    <h2>Report Parameters</h2>
                </div>
            </div>

            <form method="GET" action="{{ route('crm.attendance.reports.show', $type) }}" class="crm-filter-form">
                <div class="crm-filter-grid">
                    @if (in_array($type, ['daily-summary']))
                        <div class="crm-field">
                            <label for="filter-date">Date</label>
                            <input type="date" id="filter-date" name="date" value="{{ $filters['date'] }}">
                        </div>
                    @endif

                    @if (in_array($type, ['monthly-register']))
                        <div class="crm-field">
                            <label for="filter-month">Month</label>
                            <input type="month" id="filter-month" name="month" value="{{ $filters['month'] }}">
                        </div>
                    @endif

                    @if (in_array($type, ['hours-worked', 'late-arrivals', 'absenteeism', 'biometric-audit']))
                        <div class="crm-field">
                            <label for="filter-from">From</label>
                            <input type="date" id="filter-from" name="from" value="{{ $filters['from'] }}">
                        </div>
                        <div class="crm-field">
                            <label for="filter-to">To</label>
                            <input type="date" id="filter-to" name="to" value="{{ $filters['to'] }}">
                        </div>
                    @endif

                    @if ($type !== 'biometric-audit')
                        <div class="crm-field">
                            <label for="filter-dept">Department</label>
                            <select id="filter-dept" name="department_id">
                                <option value="">All departments</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected($filters['department_id'] == $dept->id)>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if ($type === 'biometric-audit')
                        <div class="crm-field">
                            <label for="filter-device">Device</label>
                            <select id="filter-device" name="device_id">
                                <option value="">All devices</option>
                                @foreach ($devices as $device)
                                    <option value="{{ $device->id }}" @selected($filters['device_id'] == $device->id)>{{ $device->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="form-actions">
                    <a href="{{ route('crm.attendance.reports') }}" class="btn btn-light crm-btn-light">
                        <i class="bx bx-arrow-back"></i> All Reports
                    </a>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Apply</button>
                    <a href="{{ route('crm.attendance.reports.export', $type) }}?{{ http_build_query($filters) }}" class="btn btn-primary">
                        <i class="bx bx-download"></i> Export Excel
                    </a>
                </div>
            </form>
        </section>

        {{-- Report Data --}}
        <section class="crm-card">
            <div class="crm-card-title">
                <div>
                    <p class="crm-kicker">Results</p>
                    <h2>{{ $data['title'] ?? 'Report' }}</h2>
                </div>
            </div>

            @if ($type === 'monthly-register' && isset($data['register']))
                @php($register = $data['register'])
                @if (empty($register['rows']))
                    <div class="crm-empty">No data found for the selected month.</div>
                @else
                    <div class="crm-table-wrap">
                        <table class="crm-table" style="table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th style="min-width: 160px; position: sticky; left: 0; background: #fff; z-index: 2;">Employee</th>
                                    @foreach ($register['days'] as $day)
                                        <th style="text-align: center; min-width: 36px; font-size: 10px; padding: 6px 2px;
                                            {{ $day['is_weekend'] ? 'background: #f8fafc;' : '' }}">
                                            {{ $day['label'] }}<br>{{ $day['day'] }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($register['rows'] as $row)
                                    <tr>
                                        <td style="position: sticky; left: 0; background: #fff; z-index: 1; font-size: 12px;">{{ $row['user']->name }}</td>
                                        @foreach ($row['codes'] as $i => $code)
                                            <td style="text-align: center; font-size: 11px; font-weight: 600; padding: 6px 2px;
                                                {{ $register['days'][$i]['is_weekend'] ? 'background: #f8fafc;' : '' }}">
                                                {{ $code }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @elseif (isset($data['rows']))
                @if ($data['rows']->isEmpty())
                    <div class="crm-empty">No data found for the selected filters.</div>
                @else
                    <div class="crm-table-wrap">
                        <table class="crm-table">
                            <thead>
                                <tr>
                                    @foreach (array_keys($data['rows']->first()) as $heading)
                                        <th>{{ ucfirst(str_replace('_', ' ', $heading)) }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data['rows'] as $row)
                                    <tr>
                                        @foreach ($row as $value)
                                            <td style="font-size: 13px;">
                                                @if (is_bool($value))
                                                    {{ $value ? 'Yes' : 'No' }}
                                                @else
                                                    {{ $value }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                <div class="crm-empty">No data available.</div>
            @endif
        </section>
    </div>
@endsection
