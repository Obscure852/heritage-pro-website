@extends('layouts.master')
@section('title')
    Master Timetable Overview{{ $timetable ? ' - ' . $timetable->name : '' }}
@endsection
@section('css')
    @include('timetable.views._grid-styles')
    <style>
        .master-cell { min-width: 40px; padding: 2px 3px; font-size: 9px; }
        .master-grid td { min-width: 40px; }
        .master-grid th { font-size: 10px; padding: 4px 5px; }
        .grade-section { margin-bottom: 30px; }
        .grade-heading { font-size: 16px; font-weight: 600; color: #1f2937; padding-bottom: 8px; border-bottom: 2px solid #4e73df; margin-bottom: 12px; }
        .class-label { font-weight: 600; font-size: 11px; background: #eef2ff; white-space: nowrap; min-width: 60px; }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboard
        @endslot
        @slot('title')
            Master Timetable Overview
        @endslot
    @endcomponent

    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-end">
            <select name="term" id="termId" class="form-select term-select">
                @if (!empty($terms))
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}"
                            {{ $term->id == session('selected_term_id', $currentTerm->id ?? '') ? 'selected' : '' }}>
                            {{ 'Term ' . $term->term . ', ' . $term->year }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>

    <div class="container-fluid">
        <div class="view-container">
            <div class="view-header">
                <h3 style="margin:0;">Master Timetable Overview</h3>
                @if ($timetable)
                    <p style="margin:6px 0 0 0; opacity:.9;">
                        {{ $timetable->name }}
                        <span class="status-badge status-{{ $timetable->status }}" style="margin-left: 8px;">{{ $timetable->status }}</span>
                    </p>
                @endif
            </div>
            <div class="view-body">
                @if (!$timetable)
                    <div class="text-center text-muted" style="padding: 60px 0;">
                        <i class="bx bx-calendar-alt" style="font-size: 48px; opacity: 0.3;"></i>
                        <p class="mt-3 mb-0" style="font-size: 15px;">No timetable available. Create and publish a timetable first.</p>
                    </div>
                @else
                    <div class="row align-items-center mb-3">
                        <div class="col-lg-8 col-md-8">
                            {{-- Timetable and Grade selectors --}}
                            <form method="GET" action="{{ route('timetable.view.master', $timetable) }}" class="view-selector" style="margin-bottom: 0;">
                                <div class="form-group">
                                    <label for="timetableSelect" class="form-label" style="font-size:13px;">Timetable</label>
                                    <select id="timetableSelect" class="form-select" onchange="window.location.href='{{ route('timetable.view.master', ['timetable' => '__ID__']) }}'.replace('__ID__', this.value) + (gradeParam ? '?grade_id=' + gradeParam : '')">
                                        @foreach ($timetables as $tt)
                                            <option value="{{ $tt->id }}" {{ $timetable->id == $tt->id ? 'selected' : '' }}>
                                                {{ $tt->name }} ({{ $tt->status }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="gradeFilter" class="form-label" style="font-size:13px;">Grade</label>
                                    <select id="gradeFilter" name="grade_id" class="form-select">
                                        <option value="">All Grades</option>
                                        @foreach ($grades as $grade)
                                            <option value="{{ $grade->id }}" {{ $gradeFilter == $grade->id ? 'selected' : '' }}>
                                                {{ $grade->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-loading">
                                    <span class="btn-text"><i class="fas fa-search"></i> View</span>
                                    <span class="btn-spinner d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Loading...
                                    </span>
                                </button>
                            </form>
                        </div>
                        <div class="col-lg-4 col-md-4 text-end">
                            <div class="btn-group reports-dropdown">
                                <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2" style="font-size: 10px;"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('timetable.export.master.pdf', ['timetable' => $timetable, 'grade_id' => $gradeFilter]) }}" target="_blank">
                                            <i class="fas fa-file-pdf" style="color: #dc3545;"></i> Print PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('timetable.export.master.excel', ['timetable' => $timetable]) }}">
                                            <i class="fas fa-file-excel" style="color: #198754;"></i> Export Excel
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="help-text">
                        <div class="help-title">Condensed Overview</div>
                        <div class="help-content">All classes grouped by grade. Cells show subject abbreviations. Hover for teacher details.</div>
                    </div>

                    @if (!empty($masterData['grids']))
                        @php
                            $COLORS = ['#3b82f6','#ef4444','#10b981','#f59e0b','#8b5cf6','#ec4899','#14b8a6','#f97316','#6366f1','#84cc16','#06b6d4','#e11d48','#a855f7','#22c55e','#eab308','#0ea5e9','#d946ef','#64748b','#f43f5e','#2dd4bf'];

                            $colorMap = [];
                            $colorIndex = 0;

                            // Group classes by grade
                            $byGrade = [];
                            foreach ($masterData['classes'] as $klassId => $info) {
                                $gId = $info['grade_id'];
                                if ($gradeFilter && $gId != $gradeFilter) continue;
                                $byGrade[$gId]['classes'][$klassId] = $info;
                                $byGrade[$gId]['name'] = $info['grade_name'];
                                $byGrade[$gId]['sequence'] = $info['grade_sequence'];
                            }
                            // Sort by grade sequence
                            uasort($byGrade, fn($a, $b) => $a['sequence'] <=> $b['sequence']);
                        @endphp

                        @foreach ($byGrade as $gradeId => $gradeGroup)
                            <div class="grade-section">
                                <h5 class="grade-heading">{{ $gradeGroup['name'] }}</h5>
                                <div class="table-responsive">
                                    <table class="timetable-grid master-grid">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 60px;">Class</th>
                                                <th style="min-width: 30px;">Day</th>
                                                @foreach ($daySchedule as $item)
                                                    @if ($item['type'] === 'period')
                                                        <th>P{{ $item['period'] }}</th>
                                                    @elseif ($item['type'] === 'break')
                                                        <th class="slot-break" style="min-width:20px;"></th>
                                                    @endif
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                // Sort classes by name within grade
                                                $sortedClasses = collect($gradeGroup['classes'])->sortBy('name');
                                            @endphp
                                            @foreach ($sortedClasses as $klassId => $classInfo)
                                                @for ($day = 1; $day <= 6; $day++)
                                                    <tr>
                                                        @if ($day === 1)
                                                            <td rowspan="6" class="class-label">{{ $classInfo['name'] }}</td>
                                                        @endif
                                                        <td class="day-label-cell" style="font-size:10px;">D{{ $day }}</td>
                                                        @php $skipUntil = 0; @endphp
                                                        @foreach ($daySchedule as $si => $schedItem)
                                                            @if ($schedItem['type'] === 'break')
                                                                <td class="slot-break" style="min-width:20px;"></td>
                                                                @continue
                                                            @endif
                                                            @php $period = (int) $schedItem['period']; @endphp
                                                            @if ($period < $skipUntil)
                                                                @continue
                                                            @endif
                                                            @php $slot = $masterData['grids'][$klassId][$day][$period] ?? null; @endphp
                                                            @if ($slot)
                                                                @php
                                                                    $duration = $slot['duration'] ?? 1;
                                                                    $color = getViewColor($slot['klass_subject_id'] ?? 0, $colorMap, $colorIndex, $COLORS);
                                                                    $rgb = hexToRgb($color);
                                                                    $colspan = $duration > 1 ? getActualColspan($daySchedule, $si, $duration) : 1;
                                                                    $skipUntil = $period + $duration;
                                                                @endphp
                                                                <td class="master-cell slot-filled"
                                                                    @if ($colspan > 1) colspan="{{ $colspan }}" @endif
                                                                    style="background-color: rgba({{ $rgb['r'] }},{{ $rgb['g'] }},{{ $rgb['b'] }}, 0.12); border-left: 2px solid {{ $color }};"
                                                                    title="{{ $slot['subject_abbrev'] }} - {{ $slot['teacher_initials'] }}{{ !empty($slot['venue_name']) ? ' | ' . $slot['venue_name'] : '' }}">
                                                                    <span style="font-weight:600;">{{ $slot['subject_abbrev'] }}</span>
                                                                </td>
                                                            @else
                                                                <td class="master-cell slot-empty"></td>
                                                            @endif
                                                        @endforeach
                                                    </tr>
                                                @endfor
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted" style="padding: 60px 0;">
                            <i class="bx bx-grid-alt" style="font-size: 48px; opacity: 0.3;"></i>
                            <p class="mt-3 mb-0" style="font-size: 15px;">No slots found for this timetable.</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        var gradeParam = '{{ $gradeFilter ?? '' }}';

        $('#termId').change(function() {
            var term = $(this).val();
            $.ajax({
                url: "{{ route('students.term-session') }}",
                method: 'POST',
                data: { term_id: term, _token: '{{ csrf_token() }}' },
                success: function() {
                    window.location.reload();
                }
            });
        });

        // Loading state on form submit
        document.querySelector('.view-selector')?.addEventListener('submit', function() {
            var submitBtn = this.querySelector('button[type="submit"].btn-loading');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
        });
    </script>
@endsection
