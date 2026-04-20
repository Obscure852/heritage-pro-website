@extends('layouts.master')
@section('title')
    Teacher Timetable{{ $timetable ? ' - ' . $timetable->name : '' }}
@endsection
@section('css')
    @include('timetable.views._grid-styles')
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboard
        @endslot
        @slot('title')
            Teacher Timetable
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
                <h3 style="margin:0;">Teacher Timetable</h3>
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
                        <p class="mt-3 mb-0" style="font-size: 15px;">No published timetable available for the current term.</p>
                    </div>
                @else
                    <div class="row align-items-center mb-3">
                        <div class="col-lg-8 col-md-8">
                            @if ($canSelectTeacher)
                                {{-- Teacher selector (admin/HOD) --}}
                                <form method="GET" action="{{ route('timetable.view.teacher', $timetable) }}" class="view-selector" style="margin-bottom: 0;">
                                    <div class="form-group">
                                        <label for="teacherSelect" class="form-label" style="font-size:13px;">Teacher</label>
                                        <select id="teacherSelect" name="teacher_id" class="form-select">
                                            <option value="">-- Select Teacher --</option>
                                            @foreach ($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" {{ $teacherId == $teacher->id ? 'selected' : '' }}>
                                                    {{ $teacher->firstname }} {{ $teacher->lastname }}
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
                            @else
                                {{-- Teacher locked to own timetable --}}
                                <div class="view-selector" style="margin-bottom: 0;">
                                    <div class="form-group">
                                        <label class="form-label" style="font-size:13px;">Teacher</label>
                                        <div class="form-control" style="background-color: #e9ecef; min-width: 200px;">
                                            {{ $selectedTeacherName ?? auth()->user()->firstname . ' ' . auth()->user()->lastname }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-lg-4 col-md-4 text-end">
                            <div class="btn-group reports-dropdown">
                                <button type="button" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-down ms-2" style="font-size: 10px;"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('timetable.export.teacher.pdf', ['timetable' => $timetable, 'teacher_id' => $teacherId]) }}" target="_blank">
                                            <i class="fas fa-file-pdf" style="color: #dc3545;"></i> Print PDF
                                        </a>
                                    </li>
                                    @can('manage-timetable')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('timetable.export.teacher.excel', ['timetable' => $timetable, 'teacher_id' => $teacherId]) }}">
                                            <i class="fas fa-file-excel" style="color: #198754;"></i> Export Excel
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </div>
                    </div>

                    @if ($teacherId && !empty($gridData))
                        <div class="help-text">
                            <div class="help-title">{{ $selectedTeacherName ?? 'Teacher' }}'s Timetable</div>
                            <div class="help-content">6-day rotation schedule showing all classes taught. Hover over a cell for full details.</div>
                        </div>

                        @php
                            $COLORS = ['#3b82f6','#ef4444','#10b981','#f59e0b','#8b5cf6','#ec4899','#14b8a6','#f97316','#6366f1','#84cc16','#06b6d4','#e11d48','#a855f7','#22c55e','#eab308','#0ea5e9','#d946ef','#64748b','#f43f5e','#2dd4bf'];

                            $colorMap = [];
                            $colorIndex = 0;
                        @endphp

                        <div class="table-responsive">
                            <table class="timetable-grid">
                                <thead>
                                    <tr>
                                        <th style="min-width: 55px;">Day</th>
                                        @foreach ($daySchedule as $item)
                                            @if ($item['type'] === 'period')
                                                <th>P{{ $item['period'] }}<br><span style="font-size:9px;">{{ $item['start_time'] }}-{{ $item['end_time'] }}</span></th>
                                            @elseif ($item['type'] === 'break')
                                                <th class="slot-break">{{ $item['label'] ?? 'Break' }}</th>
                                            @endif
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($day = 1; $day <= 6; $day++)
                                        <tr>
                                            <td class="day-label-cell">Day {{ $day }}</td>
                                            @php $skipUntil = 0; @endphp
                                            @foreach ($daySchedule as $si => $schedItem)
                                                @if ($schedItem['type'] === 'break')
                                                    <td class="slot-break"></td>
                                                    @continue
                                                @endif
                                                @php $period = (int) $schedItem['period']; @endphp
                                                @if ($period < $skipUntil)
                                                    @continue
                                                @endif
                                                @php $slot = $gridData[$day][$period] ?? null; @endphp
                                                @if ($slot)
                                                    @php
                                                        $duration = $slot['duration'] ?? 1;
                                                        $color = getViewColor($slot['klass_subject_id'] ?? 0, $colorMap, $colorIndex, $COLORS);
                                                        $rgb = hexToRgb($color);
                                                        $colspan = $duration > 1 ? getActualColspan($daySchedule, $si, $duration) : 1;
                                                        $skipUntil = $period + $duration;
                                                    @endphp
                                                    <td class="slot-cell slot-filled"
                                                        @if ($colspan > 1) colspan="{{ $colspan }}" @endif
                                                        style="background-color: rgba({{ $rgb['r'] }},{{ $rgb['g'] }},{{ $rgb['b'] }}, 0.12); border-left: 3px solid {{ $color }}; cursor: default;"
                                                        title="{{ $slot['subject_name'] ?? '' }} - {{ $slot['class_name'] ?? '' }}{{ !empty($slot['venue_name']) ? ' | ' . $slot['venue_name'] : '' }}">
                                                        <div class="slot-subject">{{ Str::limit($slot['subject_name'] ?? '?', 12) }}</div>
                                                        <div class="slot-teacher" style="color: #4e73df; font-weight: 500;">{{ $slot['class_name'] ?? '' }}</div>
                                                        @if (!empty($slot['venue_name']))
                                                            <div class="slot-venue">{{ $slot['venue_name'] }}</div>
                                                        @endif
                                                    </td>
                                                @else
                                                    <td class="slot-cell slot-empty"></td>
                                                @endif
                                            @endforeach
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    @elseif ($teacherId && empty($gridData))
                        <div class="text-center text-muted" style="padding: 60px 0;">
                            <i class="bx bx-grid-alt" style="font-size: 48px; opacity: 0.3;"></i>
                            <p class="mt-3 mb-0" style="font-size: 15px;">No slots assigned for this teacher yet.</p>
                        </div>
                    @else
                        <div class="help-text">
                            <div class="help-title">Getting Started</div>
                            <div class="help-content">Select a teacher above to view their timetable.</div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
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
