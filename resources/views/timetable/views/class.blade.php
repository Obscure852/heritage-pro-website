@extends('layouts.master')
@section('title')
    Class Timetable{{ $timetable ? ' - ' . $timetable->name : '' }}
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
            Class Timetable
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
                <h3 style="margin:0;">Class Timetable</h3>
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
                            {{-- Grade/Class selector --}}
                            <form method="GET" action="{{ route('timetable.view.class', $timetable) }}" class="view-selector" style="margin-bottom: 0;">
                                <div class="form-group">
                                    <label for="gradeSelect" class="form-label" style="font-size:13px;">Grade</label>
                                    <select id="gradeSelect" class="form-select">
                                        <option value="">-- Select Grade --</option>
                                        @foreach ($grades as $grade)
                                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="klassSelect" class="form-label" style="font-size:13px;">Class</label>
                                    <select id="klassSelect" name="klass_id" class="form-select">
                                        <option value="">-- Select Class --</option>
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
                                        <a class="dropdown-item" href="{{ route('timetable.export.class.pdf', ['timetable' => $timetable, 'klass_id' => $klassId]) }}" target="_blank">
                                            <i class="fas fa-file-pdf" style="color: #dc3545;"></i> Print PDF
                                        </a>
                                    </li>
                                    @can('manage-timetable')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('timetable.export.class.excel', ['timetable' => $timetable, 'klass_id' => $klassId]) }}">
                                            <i class="fas fa-file-excel" style="color: #198754;"></i> Export Excel
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </div>
                    </div>

                    @if ($klassId && !empty($gridData))
                        <div class="help-text">
                            <div class="help-title">{{ $selectedKlassName ?? 'Class' }} Timetable</div>
                            <div class="help-content">6-day rotation schedule. Hover over a cell for full subject and teacher details.</div>
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
                                                        title="{{ $slot['subject_name'] ?? '' }} - {{ $slot['teacher_name'] ?? '' }}{{ !empty($slot['venue_name']) ? ' | ' . $slot['venue_name'] : '' }}">
                                                        <div class="slot-subject">{{ Str::limit($slot['subject_name'] ?? '?', 12) }}</div>
                                                        <div class="slot-teacher">{{ viewGetInitials($slot['teacher_name'] ?? '') }}</div>
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
                    @elseif ($klassId && empty($gridData))
                        <div class="text-center text-muted" style="padding: 60px 0;">
                            <i class="bx bx-grid-alt" style="font-size: 48px; opacity: 0.3;"></i>
                            <p class="mt-3 mb-0" style="font-size: 15px;">No slots assigned for this class yet.</p>
                        </div>
                    @else
                        <div class="help-text">
                            <div class="help-title">Getting Started</div>
                            <div class="help-content">Select a grade and class above to view the timetable.</div>
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

        var grades = @json($grades ?? []);
        var selectedKlassId = '{{ $klassId ?? '' }}';

        document.getElementById('gradeSelect')?.addEventListener('change', function() {
            var gradeId = parseInt(this.value);
            var klassSelect = document.getElementById('klassSelect');
            klassSelect.innerHTML = '<option value="">-- Select Class --</option>';
            var grade = grades.find(g => g.id === gradeId);
            if (grade && grade.klasses) {
                grade.klasses.forEach(function(k) {
                    var opt = document.createElement('option');
                    opt.value = k.id;
                    opt.textContent = k.name;
                    if (String(k.id) === selectedKlassId) opt.selected = true;
                    klassSelect.appendChild(opt);
                });
            }
        });

        // Auto-select grade on page load if class was selected
        if (selectedKlassId) {
            for (var i = 0; i < grades.length; i++) {
                var match = (grades[i].klasses || []).find(k => String(k.id) === selectedKlassId);
                if (match) {
                    document.getElementById('gradeSelect').value = grades[i].id;
                    document.getElementById('gradeSelect').dispatchEvent(new Event('change'));
                    break;
                }
            }
        }

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
