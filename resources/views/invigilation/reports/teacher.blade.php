@extends('layouts.master')

@section('title')
    Teacher Duty Roster
@endsection

@section('css')
    @include('invigilation.partials.theme')
    @if (request()->boolean('print') && request()->query('layout', 'timetable') === 'timetable')
        <style media="print">
            @page {
                size: landscape;
                margin: 10mm;
            }
        </style>
    @endif
@endsection

@section('content')
    @php
        $activeLayout = $layout ?? 'timetable';
        $isTimetableLayout = $activeLayout === 'timetable';
        $baseQuery = $series ? ['series_id' => $series->id] : [];
        $timetableUrl = route('invigilation.reports.teacher.index', array_merge($baseQuery, ['layout' => 'timetable']));
        $tableUrl = route('invigilation.reports.teacher.index', array_merge($baseQuery, ['layout' => 'table']));
        $csvUrl = $series ? route('invigilation.reports.teacher.index', ['series_id' => $series->id, 'format' => 'csv']) : '#';
        $printUrl = $series ? route('invigilation.reports.teacher.index', ['series_id' => $series->id, 'layout' => $activeLayout, 'print' => 1]) : '#';
        $hasTimetableRows = collect($timetable['resource_rows'] ?? collect())->isNotEmpty();
    @endphp

    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('invigilation.index') }}">Invigilation Roster</a>
        @endslot
        @slot('title')
            Teacher Duties
        @endslot
    @endcomponent

    @include('houses.partials.alerts')

    <div class="invigilation-report-container printable">
        <div class="invigilation-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h3 class="mb-1 text-white">Teacher Duty Roster</h3>
                    <p class="mb-0 opacity-75">
                        {{ $series ? $series->name . ' | Term ' . ($series->term?->term ?? '-') . ', ' . ($series->term?->year ?? '-') : 'Select an invigilation series to view this report.' }}
                    </p>
                </div>
                <div class="col-md-5">
                    <div class="module-header-actions invigilation-daily-actions">
                        <div class="invigilation-daily-action-cluster" role="group" aria-label="Teacher duty actions">
                            <div class="btn-group invigilation-layout-toggle" role="group" aria-label="Teacher duty layout">
                                <a
                                    href="{{ $timetableUrl }}"
                                    class="btn invigilation-layout-button {{ $isTimetableLayout ? 'active' : '' }}"
                                    aria-current="{{ $isTimetableLayout ? 'page' : 'false' }}"
                                >
                                    <span class="invigilation-header-action-icon">
                                        <i class="fas fa-th-large"></i>
                                    </span>
                                    <span>Timetable</span>
                                </a>
                                <a
                                    href="{{ $tableUrl }}"
                                    class="btn invigilation-layout-button {{ $isTimetableLayout ? '' : 'active' }}"
                                    aria-current="{{ $isTimetableLayout ? 'false' : 'page' }}"
                                >
                                    <span class="invigilation-header-action-icon">
                                        <i class="fas fa-table"></i>
                                    </span>
                                    <span>Table</span>
                                </a>
                            </div>
                            <a href="{{ $csvUrl }}" class="btn invigilation-header-button {{ $series ? '' : 'disabled' }}">
                                <span class="invigilation-header-action-icon">
                                    <i class="fas fa-download"></i>
                                </span>
                                <span>CSV</span>
                            </a>
                            <button
                                type="button"
                                class="btn invigilation-header-button"
                                {{ $series ? '' : 'disabled' }}
                                @if ($series) onclick="window.open('{{ $printUrl }}', '_blank', 'noopener');" @endif
                            >
                                <span class="invigilation-header-action-icon">
                                    <i class="fas fa-print"></i>
                                </span>
                                <span>Print</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="invigilation-report-body">
            @include('invigilation.partials.module-nav', ['current' => 'teacher', 'series' => $series])
            @include('invigilation.partials.report-selector', [
                'action' => route('invigilation.reports.teacher.index'),
                'seriesOptions' => $seriesOptions,
                'series' => $series,
                'extraQuery' => ['layout' => $activeLayout],
                'selectClass' => 'invigilation-daily-series-select',
            ])

            @if (!$series)
                @include('invigilation.partials.empty-state', [
                    'icon' => 'fas fa-user-check',
                    'title' => 'No invigilation series are available yet.',
                    'copy' => 'Create a series first, then return here to view teacher duties.',
                ])
            @elseif ($isTimetableLayout)
                @if (!$hasTimetableRows)
                    @include('invigilation.partials.empty-state', [
                        'icon' => 'fas fa-user-check',
                        'title' => 'No teacher duties are assigned yet.',
                        'copy' => 'Generate the roster or add invigilators manually to populate this report.',
                    ])
                @else
                    <div class="card-shell invigilation-timetable-shell">
                        <div class="card-body p-0">
                            <div class="invigilation-section-header invigilation-timetable-header">
                                <div>
                                    <h5 class="invigilation-section-title">Teacher Timetable</h5>
                                    <p class="invigilation-section-subtitle">Teachers run down the left and dates run across the top, with duty tiles inside each teacher/day cell ordered by time.</p>
                                </div>
                            </div>

                            <div class="table-responsive invigilation-timetable-scroll">
                                <table class="table invigilation-timetable mb-0">
                                    <thead>
                                        <tr>
                                            <th class="invigilation-timetable-row-col">Teacher</th>
                                            @foreach ($timetable['dates'] as $date)
                                                <th class="invigilation-timetable-date-col">
                                                    <div>{{ \Illuminate\Support\Carbon::parse($date)->format('D') }}</div>
                                                    <small>{{ \Illuminate\Support\Carbon::parse($date)->format('d M Y') }}</small>
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($timetable['resource_rows'] as $resourceRow)
                                            <tr>
                                                <th class="invigilation-timetable-row-col">
                                                    <div class="invigilation-timetable-row-label">{{ $resourceRow['label'] }}</div>
                                                    <div class="invigilation-timetable-row-meta">{{ $resourceRow['count'] }} duty slot(s)</div>
                                                </th>
                                                @foreach ($timetable['dates'] as $date)
                                                    @php
                                                        $cellRows = $timetable['cells'][$resourceRow['key']][$date] ?? collect();
                                                    @endphp
                                                    <td class="invigilation-timetable-cell">
                                                        @if ($cellRows->isEmpty())
                                                            <div class="invigilation-slot-empty">No session</div>
                                                        @else
                                                            <div class="invigilation-slot-stack">
                                                                @foreach ($cellRows as $row)
                                                                    <article class="invigilation-slot-tile tile-covered">
                                                                        <div class="invigilation-slot-title-row">
                                                                            <div class="invigilation-slot-subject">{{ substr((string) $row['start_time'], 0, 5) }} - {{ substr((string) $row['end_time'], 0, 5) }}</div>
                                                                            <span class="summary-chip pill-muted">{{ ucfirst($row['source']) }}</span>
                                                                        </div>
                                                                        <div class="invigilation-slot-meta">{{ $row['subject'] }} | {{ $row['grade'] ?: 'No grade' }}</div>
                                                                        <div class="invigilation-slot-meta">{{ $row['venue'] ?: 'No venue' }} | {{ $row['group'] ?: 'No group' }}</div>
                                                                        <div class="invigilation-slot-staff">
                                                                            {{ $row['locked'] ? 'Locked assignment' : 'Unlocked assignment' }}
                                                                        </div>
                                                                    </article>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                @forelse ($rows as $teacher => $items)
                    <div class="card-shell mb-4">
                        <div class="card-body p-4">
                            <div class="invigilation-section-header">
                                <div>
                                    <h5 class="invigilation-section-title">{{ $teacher }}</h5>
                                    <p class="invigilation-section-subtitle">{{ count($items) }} duty slot(s)</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Subject</th>
                                            <th>Grade</th>
                                            <th>Venue</th>
                                            <th>Group</th>
                                            <th>Flags</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($items as $row)
                                            <tr>
                                                <td>{{ $row['date'] }}</td>
                                                <td>{{ substr((string) $row['start_time'], 0, 5) }} - {{ substr((string) $row['end_time'], 0, 5) }}</td>
                                                <td>{{ $row['subject'] }}</td>
                                                <td>{{ $row['grade'] }}</td>
                                                <td>{{ $row['venue'] }}</td>
                                                <td>{{ $row['group'] }}</td>
                                                <td>
                                                    <div class="invigilation-meta-pills">
                                                        @if ($row['locked'])
                                                            <span class="summary-chip pill-muted">Locked</span>
                                                        @endif
                                                        <span class="summary-chip pill-muted">{{ ucfirst($row['source']) }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @empty
                    @include('invigilation.partials.empty-state', [
                        'icon' => 'fas fa-user-check',
                        'title' => 'No teacher duties are assigned yet.',
                        'copy' => 'Generate the roster or add invigilators manually to populate this report.',
                    ])
                @endforelse
            @endif
        </div>
    </div>
@endsection

@section('script')
    @if (request()->boolean('print'))
        <script>
            window.addEventListener('load', function() {
                window.print();
            });
        </script>
    @endif
@endsection
