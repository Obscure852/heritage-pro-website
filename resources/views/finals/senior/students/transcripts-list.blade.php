@extends('layouts.master')

@section('title')
    Senior Students Transcripts - {{ $reportData['graduation_year'] }}
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

        .transcript-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            margin-bottom: 16px;
            padding: 16px 20px;
            transition: box-shadow 0.2s ease;
        }

        .transcript-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .transcript-card .student-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f3f4f6;
        }

        .transcript-card .student-info h5 {
            margin: 0 0 4px 0;
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
        }

        .transcript-card .student-info .meta {
            font-size: 12px;
            color: #6b7280;
        }

        .transcript-card .student-info .meta span {
            margin-right: 12px;
        }

        .transcript-card .overall {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .overall-stat {
            text-align: center;
            min-width: 64px;
        }

        .overall-stat .label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.4px;
        }

        .overall-stat .value {
            font-size: 16px;
            font-weight: 700;
            color: #1f2937;
        }

        .grade-pill {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }

        .grade-pill.pass { background: #d1fae5; color: #065f46; }
        .grade-pill.fail { background: #fee2e2; color: #991b1b; }

        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 8px;
        }

        .subject-chip {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 10px;
            border-radius: 3px;
            background: #f9fafb;
            font-size: 12px;
            border-left: 3px solid #e5e7eb;
        }

        .subject-chip.is-pass { border-left-color: #10b981; }
        .subject-chip.is-fail { border-left-color: #ef4444; }
        .subject-chip.not-taken { border-left-color: #d1d5db; opacity: 0.7; }

        .subject-chip .name {
            color: #374151;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-right: 8px;
        }

        .subject-chip .grade {
            font-weight: 700;
            color: #1f2937;
        }

        .empty-state {
            background: white;
            border: 1px dashed #d1d5db;
            border-radius: 3px;
            padding: 48px;
            text-align: center;
            color: #9ca3af;
        }

        .empty-state i { font-size: 48px; opacity: 0.4; }

        @media print {
            @page { size: A4; margin: 12mm; }
            body { font-size: 11px; background: white; }
            .no-print { display: none !important; }
            .transcript-card { page-break-inside: avoid; box-shadow: none; }
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
            Senior Students Transcripts — {{ $reportData['graduation_year'] }}
        @endslot
    @endcomponent

    <div class="row no-print mb-3">
        <div class="col-12 d-flex justify-content-end">
            <a href="#" onclick="window.print(); return false;" class="me-2 text-muted" title="Print">
                <i style="font-size: 20px;" class="bx bx-printer"></i>
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="senior-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h3><i class="fas fa-file-alt me-2"></i>Senior Students Transcripts</h3>
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
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card">
                        <div class="label">Total Transcripts</div>
                        <div class="value">{{ $reportData['total_transcripts'] }}</div>
                    </div>
                </div>
                @foreach (array_slice($reportData['overall_grades'], 0, 3) as $grade)
                    <div class="col-md-3 col-sm-6">
                        <div class="stat-card {{ $loop->first ? 'success' : ($loop->index === 1 ? 'info' : 'warning') }}">
                            <div class="label">Grade {{ $grade }}</div>
                            <div class="value">{{ $reportData['grade_distribution'][$grade] ?? 0 }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            @forelse ($reportData['transcripts'] as $student)
                <div class="transcript-card">
                    <div class="student-row">
                        <div class="student-info">
                            <h5>{{ $student['full_name'] }}</h5>
                            <div class="meta">
                                <span><i class="fas fa-id-card me-1"></i>{{ $student['exam_number'] ?: 'No candidate #' }}</span>
                                <span><i class="fas fa-users me-1"></i>{{ $student['class_name'] }}</span>
                                <span><i class="fas fa-venus-mars me-1"></i>{{ $student['gender_full'] }}</span>
                            </div>
                        </div>
                        <div class="overall">
                            <div class="overall-stat">
                                <div class="label">Subjects</div>
                                <div class="value">{{ $student['total_subjects'] }}</div>
                            </div>
                            <div class="overall-stat">
                                <div class="label">Passes</div>
                                <div class="value">{{ $student['passes'] }}</div>
                            </div>
                            <div class="overall-stat">
                                <div class="label">Points</div>
                                <div class="value">
                                    {{ $student['overall_points'] !== null ? number_format($student['overall_points'], 1) : '—' }}
                                </div>
                            </div>
                            <div class="overall-stat">
                                <div class="label">Grade</div>
                                <div class="value">
                                    @if ($student['overall_grade'])
                                        @php $pillClass = ($student['pass_percentage'] ?? 0) >= 50 ? 'pass' : 'fail'; @endphp
                                        <span class="grade-pill {{ $pillClass }}">{{ $student['overall_grade'] }}</span>
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (!empty($student['subjects']))
                        <div class="subjects-grid">
                            @foreach ($student['subjects'] as $subject)
                                @php
                                    $stateClass = !$subject['was_taken']
                                        ? 'not-taken'
                                        : ($subject['is_pass'] ? 'is-pass' : 'is-fail');
                                @endphp
                                <div class="subject-chip {{ $stateClass }}">
                                    <span class="name" title="{{ $subject['subject_name'] }}">
                                        {{ $subject['subject_name'] }}
                                    </span>
                                    <span class="grade">{{ $subject['grade'] ?: '—' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="empty-state">
                    <i class="bx bx-file"></i>
                    <p class="mt-3 mb-0">No senior BGCSE transcripts available for graduation year {{ $reportData['graduation_year'] }}.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
