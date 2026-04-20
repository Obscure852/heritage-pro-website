@extends('layouts.master')

@section('title')
    Top Senior Performers - {{ $reportData['graduation_year'] }}
@endsection

@section('css')
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .card {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            border: none;
        }

        .senior-header {
            background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
            color: white;
            padding: 24px 28px;
            border-radius: 3px 3px 0 0;
        }

        .senior-header h3 {
            margin: 0;
            font-weight: 600;
        }

        .senior-header .meta {
            margin: 6px 0 0 0;
            opacity: .9;
            font-size: 13px;
        }

        .stat-card {
            background: white;
            border-radius: 3px;
            padding: 16px 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            border-left: 4px solid #3b82f6;
        }

        .stat-card .label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.4px;
        }

        .stat-card .value {
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.2;
            margin-top: 4px;
        }

        .stat-card.success { border-left-color: #10b981; }
        .stat-card.warning { border-left-color: #f59e0b; }
        .stat-card.info    { border-left-color: #06b6d4; }

        .table {
            font-size: 12px;
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
            font-size: 12px;
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
        }

        .table tbody td {
            padding: 0.4rem 0.5rem;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        .student-name {
            text-align: left !important;
            font-weight: 500;
            color: #1f2937;
        }

        .rank-cell {
            text-align: center;
            font-weight: 600;
            color: #6b7280;
            width: 48px;
        }

        .rank-cell.top-1 { color: #f59e0b; font-size: 14px; }
        .rank-cell.top-2 { color: #94a3b8; font-size: 14px; }
        .rank-cell.top-3 { color: #b45309; font-size: 14px; }

        .points-cell {
            text-align: center;
            font-weight: 600;
            color: #1f2937;
        }

        .grade-pill {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            min-width: 32px;
            text-align: center;
        }

        .grade-pill.pass { background: #d1fae5; color: #065f46; }
        .grade-pill.fail { background: #fee2e2; color: #991b1b; }
        .grade-pill.none { background: #f3f4f6; color: #6b7280; }

        .no-results-row td {
            text-align: center;
            color: #9ca3af;
            font-style: italic;
            padding: 32px;
        }

        .grade-distribution {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .grade-distribution .chip {
            background: #f3f4f6;
            color: #374151;
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 12px;
            border-left: 3px solid #3b82f6;
        }

        .grade-distribution .chip strong {
            color: #1f2937;
        }

        @media print {
            @page { size: landscape; margin: 10mm; }
            body { font-size: 9px; }
            .no-print { display: none !important; }
            .table td, .table th { padding: 0.2rem; }
            .senior-header { color: #1f2937; background: white; border-bottom: 2px solid #1f2937; }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('finals.students.index') }}">Finals Students</a>
        @endslot
        @slot('title')
            Top Senior Performers — {{ $reportData['graduation_year'] }}
        @endslot
    @endcomponent

    <div class="row no-print mb-3">
        <div class="col-12 d-flex justify-content-end">
            <a href="#" onclick="window.print(); return false;" class="me-2 text-muted" title="Print">
                <i style="font-size: 20px;" class="bx bx-printer"></i>
            </a>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card mb-3">
                <div class="senior-header">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h3><i class="fas fa-graduation-cap me-2"></i>Top Senior Performers</h3>
                            <p class="meta">
                                {{ $school_data->school_name ?? 'School' }} —
                                BGCSE {{ $reportData['exam_year'] }} (Graduation {{ $reportData['graduation_year'] }})
                            </p>
                        </div>
                        <div class="col-md-5 text-md-end">
                            @if ($school_data && $school_data->logo_path)
                                <img height="56" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Statistics row --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-sm-6">
                            <div class="stat-card">
                                <div class="label">Total Students</div>
                                <div class="value">{{ $reportData['stats']['total_students'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="stat-card success">
                                <div class="label">With Results</div>
                                <div class="value">{{ $reportData['stats']['students_with_results'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="stat-card warning">
                                <div class="label">Pending</div>
                                <div class="value">{{ $reportData['stats']['students_pending'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="stat-card info">
                                <div class="label">Avg Points</div>
                                <div class="value">{{ $reportData['stats']['average_points'] }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Grade distribution --}}
                    @if (!empty($reportData['stats']['grade_distribution']))
                        <div class="mb-3">
                            <div class="label" style="font-size:11px; font-weight:600; text-transform:uppercase; color:#6b7280; letter-spacing:0.4px;">
                                Overall Grade Distribution
                            </div>
                            <div class="grade-distribution">
                                @foreach ($reportData['overall_grades'] as $grade)
                                    @php $count = $reportData['stats']['grade_distribution'][$grade] ?? 0; @endphp
                                    <div class="chip">
                                        Grade <strong>{{ $grade }}</strong>: {{ $count }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Ranked table --}}
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:48px;">#</th>
                                    <th class="student-name" style="text-align:left;">Student Name</th>
                                    <th>Class</th>
                                    <th>Candidate No.</th>
                                    <th>Gender</th>
                                    <th>Subjects</th>
                                    <th>Passes</th>
                                    <th>Pass %</th>
                                    <th>Points</th>
                                    <th>Overall Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rank = 0; @endphp
                                @forelse ($reportData['students'] as $student)
                                    @php
                                        if ($student['has_results']) { $rank++; }
                                        $rankClass = match($rank) { 1 => 'top-1', 2 => 'top-2', 3 => 'top-3', default => '' };
                                    @endphp
                                    <tr>
                                        <td class="rank-cell {{ $rankClass }}">
                                            @if ($student['has_results'])
                                                {{ $rank }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="student-name">{{ $student['full_name'] }}</td>
                                        <td>{{ $student['class_name'] }}</td>
                                        <td>{{ $student['exam_number'] ?: '—' }}</td>
                                        <td>{{ $student['gender_full'] }}</td>
                                        <td class="text-center">
                                            @if ($student['has_results'])
                                                {{ $student['total_subjects'] }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($student['has_results'])
                                                {{ $student['passes'] }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($student['has_results'])
                                                {{ number_format($student['pass_percentage'], 1) }}%
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="points-cell">
                                            @if ($student['has_results'] && $student['overall_points'] !== null)
                                                {{ number_format($student['overall_points'], 1) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($student['overall_grade'])
                                                @php
                                                    $passPercent = $student['pass_percentage'] ?? 0;
                                                    $pillClass = $passPercent >= 50 ? 'pass' : 'fail';
                                                @endphp
                                                <span class="grade-pill {{ $pillClass }}">
                                                    {{ $student['overall_grade'] }}
                                                </span>
                                            @else
                                                <span class="grade-pill none">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="no-results-row">
                                        <td colspan="10">
                                            No senior students found for graduation year {{ $reportData['graduation_year'] }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
