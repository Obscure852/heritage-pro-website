@extends('layouts.master')

@section('title')
    Conflict Report
@endsection

@section('css')
    @include('invigilation.partials.theme')
@endsection

@section('content')
    @php
        $csvUrl = $series ? route('invigilation.reports.conflicts.index', ['series_id' => $series->id, 'format' => 'csv']) : '#';
    @endphp

    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('invigilation.index') }}">Invigilation Roster</a>
        @endslot
        @slot('title')
            Conflict Report
        @endslot
    @endcomponent

    @include('houses.partials.alerts')

    <div class="invigilation-report-container printable">
        <div class="invigilation-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h3 class="mb-1 text-white">Conflict and Shortage Report</h3>
                    <p class="mb-0 opacity-75">
                        {{ $series ? $series->name . ' | Term ' . ($series->term?->term ?? '-') . ', ' . ($series->term?->year ?? '-') : 'Select an invigilation series to view this report.' }}
                    </p>
                </div>
                <div class="col-md-5">
                    <div class="module-header-actions invigilation-daily-actions">
                        <div class="invigilation-daily-action-cluster" role="group" aria-label="Conflict report actions">
                            <a href="{{ $csvUrl }}" class="btn invigilation-header-button {{ $series ? '' : 'disabled' }}">
                                <span class="invigilation-header-action-icon">
                                    <i class="fas fa-download"></i>
                                </span>
                                <span>CSV</span>
                            </a>
                            <button type="button" class="btn invigilation-header-button" {{ $series ? '' : 'disabled' }} @if ($series) onclick="window.print()" @endif>
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
            @include('invigilation.partials.module-nav', ['current' => 'conflicts', 'series' => $series])
            @include('invigilation.partials.report-selector', [
                'action' => route('invigilation.reports.conflicts.index'),
                'seriesOptions' => $seriesOptions,
                'series' => $series,
                'selectClass' => 'invigilation-daily-series-select',
            ])

            @if (!$series)
                @include('invigilation.partials.empty-state', [
                    'icon' => 'fas fa-exclamation-triangle',
                    'title' => 'No invigilation series are available yet.',
                    'copy' => 'Create a series first, then return here to review conflicts and shortages.',
                ])
            @elseif ($rows->isEmpty())
                @include('invigilation.partials.empty-state', [
                    'icon' => 'fas fa-check-circle',
                    'title' => 'No shortages or conflicts are currently detected.',
                    'copy' => 'This series currently has no visible room, staffing, timetable, or policy blockers.',
                ])
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Title</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td>{{ ucwords($row['category']) }}</td>
                                    <td>{{ $row['title'] }}</td>
                                    <td>{{ $row['detail'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
