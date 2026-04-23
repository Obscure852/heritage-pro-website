@extends('layouts.crm')

@section('title', 'Attendance Reports')
@section('crm_heading', 'Attendance Reports')
@section('crm_subheading', 'Generate and export attendance data across your team.')

@section('crm_header_stats')
    @include('crm.partials.header-stat', ['value' => $todayStats['total'], 'label' => 'Total Records'])
    @include('crm.partials.header-stat', ['value' => $todayStats['present'], 'label' => 'Present Today'])
    @include('crm.partials.header-stat', ['value' => $todayStats['late'], 'label' => 'Late Today'])
    @include('crm.partials.header-stat', ['value' => $todayStats['absent'], 'label' => 'Absent Today'])
@endsection

@section('content')
    <div class="crm-stack">
        @include('crm.partials.helper-text', [
            'title' => 'Report Centre',
            'content' => 'Select a report type below to generate it with date and department filters. All reports can be exported to Excel.',
        ])

        <div class="crm-grid cols-3">
            @php
                $reports = [
                    ['type' => 'daily-summary', 'icon' => 'bx bx-calendar-check', 'title' => 'Daily Summary', 'desc' => 'All staff attendance for a selected date with clock times and codes.'],
                    ['type' => 'monthly-register', 'icon' => 'bx bx-calendar', 'title' => 'Monthly Register', 'desc' => 'Full month grid per department. Exportable as Excel.'],
                    ['type' => 'hours-worked', 'icon' => 'bx bx-time-five', 'title' => 'Hours Worked', 'desc' => 'Per-user total hours, overtime, and daily average for a date range.'],
                    ['type' => 'late-arrivals', 'icon' => 'bx bx-error-circle', 'title' => 'Late Arrivals', 'desc' => 'All late-arrival records for a period with clock-in times.'],
                    ['type' => 'absenteeism', 'icon' => 'bx bx-user-x', 'title' => 'Absenteeism', 'desc' => 'Absence frequency and patterns per user for a period.'],
                    ['type' => 'biometric-audit', 'icon' => 'bx bx-chip', 'title' => 'Biometric Audit', 'desc' => 'All biometric events including unmatched and failed.'],
                ];
            @endphp

            @foreach ($reports as $report)
                <section class="crm-card" style="display: grid; gap: 14px; align-content: start;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 3px; background: #eff6ff; color: #2563eb; font-size: 18px;">
                            <i class="{{ $report['icon'] }}"></i>
                        </span>
                        <h3 style="margin: 0; font-size: 15px;">{{ $report['title'] }}</h3>
                    </div>
                    <p class="crm-muted-copy" style="font-size: 13px; margin: 0;">{{ $report['desc'] }}</p>
                    <a href="{{ route('crm.attendance.reports.show', $report['type']) }}" class="btn btn-primary" style="justify-self: start;">
                        <i class="bx bx-bar-chart-alt-2"></i> Generate
                    </a>
                </section>
            @endforeach
        </div>
    </div>
@endsection
