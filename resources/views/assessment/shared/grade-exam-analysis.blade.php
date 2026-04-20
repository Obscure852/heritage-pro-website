@extends('layouts.master') 
@section('title')
    Grade Exam Analysis - {{ $gradeName }}
@endsection

@section('css')
    <style>
        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            margin-bottom: 20px;
        }
        .printable { font-size: 10pt; }
        .printable table {
            font-size: 10px;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .printable th, .printable td {
            border: 1px solid #dee2e6;
            padding: 0.4rem;
            text-align: center;
            vertical-align: middle;
            font-size: 12px;
            white-space: nowrap;
        }
        .printable th.subject-name, .printable td.subject-name { text-align: left; width: 20%; }
        .printable thead th { background-color: #f8f9fa; vertical-align: middle; }
        .grade-header { background-color: #e9ecef !important; }
        .grade-subheader { background-color: #f2f4f6 !important; font-size: 0.9em; }
        .special-header, .deu-header { background-color: #5f5f5f !important; color: white; }
        .totals-row { background-color: #e9ecef !important; font-weight: bold; }
        .optional-subject { background-color: #fff3cd !important; }
        .totals-explanation { font-size: 0.85em; font-weight: normal; color: #6c757d; }
        @media print {
            @page { size: landscape; margin: 15px; }
            .no-print { display: none !important; }
            .printable { font-size: 9pt; }
            .printable table { font-size: 8px; }
            .printable thead th { background-color: #f8f9fa !important; }
            .grade-header { background-color: #e9ecef !important; }
            .grade-subheader { background-color: #f2f4f6 !important; }
            .special-header, .deu-header { background-color: #5f5f5f !important; color: white !important; }
            .totals-row { background-color: #e9ecef !important; font-weight: bold !important; }
            .optional-subject { background-color: #fff3cd !important; }
        }
    </style>
@endsection

@section('content')
@php
    $isJunior = isset($school_data->type) && strtolower($school_data->type) === 'junior';
@endphp

@component('components.breadcrumb')
    @slot('li_1')
        <a href="{{ $gradebookBackUrl }}">Back</a>
    @endslot
    @slot('title')
        Exam Trends Analysis (Across Terms)
    @endslot
@endcomponent

<div class="row no-print">
    <div class="col-md-12 col-lg-12 d-flex justify-content-end mb-2">
        <a title="Export to Excel" style="font-size: 20px; cursor:pointer;" class="bx bx-download text-muted me-2" href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}"></a>
        <i onclick="printContent()" style="font-size: 20px; cursor:pointer;" class="bx bx-printer text-muted" title="Print"></i>
    </div>
</div>

<div class="row printable">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                {{-- School Letterhead --}}
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <strong>{{ $school_data->school_name ?? 'School Name' }}</strong><br>
                        <span>{{ $school_data->physical_address ?? 'Physical Address' }}</span><br>
                        <span>{{ $school_data->postal_address ?? 'Postal Address' }}</span><br>
                        <span>Tel: {{ $school_data->telephone ?? 'N/A' }} Fax: {{ $school_data->fax ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-4 d-flex justify-content-end">
                        @if (isset($school_data->logo_path))
                            <img height="60" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        @else
                            <span class="text-muted">Logo Not Available</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if (count($analysisData) > 0)
                    @foreach ($analysisData as $termData)
                        <div class="mb-4">
                            <h5 class="mb-3">({{ $gradeName }}) {{ $termData['term_year'] }} - Subjects Exam Trends Analysis</h5>
                            @if (count($termData['subjects_data']) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="subject-name">Subject</th>
                                                <th>No. Students (Enrolled)</th>
                                                <th>No Score</th>
                                                <th>Avg. Exam Score (%)</th>
                                                @if($isJunior)
                                                    @foreach(['A','B','C','D','E','U'] as $grade)
                                                        <th>{{ $grade }}</th>
                                                    @endforeach
                                                    <th>AB%</th>
                                                    <th>ABC%</th>
                                                    <th>DEU%</th>
                                                @else
                                                    @foreach(['A*','A','B','C','D','E','U'] as $grade)
                                                        <th>{{ $grade }}</th>
                                                    @endforeach
                                                    <th>A*%</th>
                                                    <th>A*AB%</th>
                                                    <th>A*ABC%</th>
                                                    <th>DEU%</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($termData['subjects_data'] as $subjectData)
                                                <tr class="{{ isset($subjectData['is_optional']) && $subjectData['is_optional'] ? 'optional-subject' : '' }}">
                                                    <td class="subject-name">
                                                        {{ $subjectData['subject_name'] }}
                                                        @if(isset($subjectData['is_optional']) && $subjectData['is_optional'])
                                                            <br><small class="text-muted">Optional Subject</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $subjectData['student_count'] }}</td>
                                                    <td>{{ $subjectData['no_score_count'] ?? 0 }}</td>
                                                    <td>{{ $subjectData['average_percentage'] }}%</td>
                                                    @if($isJunior)
                                                        <td>{{ $subjectData['grade_counts']['A'] ?? 0 }}</td>
                                                        <td>{{ $subjectData['grade_counts']['B'] ?? 0 }}</td>
                                                        <td>{{ $subjectData['grade_counts']['C'] ?? 0 }}</td>
                                                        <td>{{ $subjectData['grade_counts']['D'] ?? 0 }}</td>
                                                        <td>{{ $subjectData['grade_counts']['E'] ?? 0 }}</td>
                                                        <td>{{ $subjectData['grade_counts']['U'] ?? 0 }}</td>
                                                        <td>{{ $subjectData['ab_percent'] ?? 0 }}%</td>
                                                        <td>{{ $subjectData['abc_percent'] ?? 0 }}%</td>
                                                        <td>{{ $subjectData['deu_percent'] ?? 0 }}%</td>
                                                    @else
                                                        <td>{{ $subjectData['grade_counts']['A*'] ?? 0 }}</td>
                                                        <td>{{ $subjectData['grade_counts']['A'] ?? 0 }}</td>
                                                        <td>{{ $subjectData['grade_counts']['B'] ?? 0 }}</td>
                                                        <td>{{ $subjectData['grade_counts']['C'] ?? 0 }}</td>
                                                        <td>{{ $subjectData['grade_counts']['D'] ?? 0 }}</td>
                                                        <td>{{ $subjectData['grade_counts']['E'] ?? 0 }}</td>
                                                        <td>{{ $subjectData['grade_counts']['U'] ?? 0 }}</td>
                                                        <td>{{ $subjectData['a_star_percent'] ?? 0 }}%</td>
                                                        <td>{{ $subjectData['a_star_ab_percent'] ?? 0 }}%</td>
                                                        <td>{{ $subjectData['a_star_abc_percent'] ?? 0 }}%</td>
                                                        <td>{{ $subjectData['deu_percent'] ?? 0 }}%</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                            {{-- FIXED TOTALS ROW --}}
                                            <tr class="totals-row">
                                                <td class="subject-name">
                                                    <strong>GRADE TOTALS</strong>
                                                    <br><span class="totals-explanation">({{ $termData['term_totals']['student_count'] ?? 0 }} students in grade)</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $termData['term_totals']['total_grades_counted'] ?? 0 }}</strong>
                                                    <br><span class="totals-explanation">Total Tests</span>
                                                </td>
                                                <td>{{ $termData['term_totals']['no_score_count'] ?? 0 }}</td>
                                                <td>{{ $termData['term_totals']['average_percentage'] ?? 0 }}%</td>
                                                @if($isJunior)
                                                    <td>{{ $termData['term_totals']['grade_counts']['A'] ?? 0 }}</td>
                                                    <td>{{ $termData['term_totals']['grade_counts']['B'] ?? 0 }}</td>
                                                    <td>{{ $termData['term_totals']['grade_counts']['C'] ?? 0 }}</td>
                                                    <td>{{ $termData['term_totals']['grade_counts']['D'] ?? 0 }}</td>
                                                    <td>{{ $termData['term_totals']['grade_counts']['E'] ?? 0 }}</td>
                                                    <td>{{ $termData['term_totals']['grade_counts']['U'] ?? 0 }}</td>
                                                    <td>{{ $termData['term_totals']['ab_percent'] ?? 0 }}%</td>
                                                    <td>{{ $termData['term_totals']['abc_percent'] ?? 0 }}%</td>
                                                    <td>{{ $termData['term_totals']['deu_percent'] ?? 0 }}%</td>
                                                @else
                                                    <td>{{ $termData['term_totals']['grade_counts']['A*'] ?? 0 }}</td>
                                                    <td>{{ $termData['term_totals']['grade_counts']['A'] ?? 0 }}</td>
                                                    <td>{{ $termData['term_totals']['grade_counts']['B'] ?? 0 }}</td>
                                                    <td>{{ $termData['term_totals']['grade_counts']['C'] ?? 0 }}</td>
                                                    <td>{{ $termData['term_totals']['grade_counts']['D'] ?? 0 }}</td>
                                                    <td>{{ $termData['term_totals']['grade_counts']['E'] ?? 0 }}</td>
                                                    <td>{{ $termData['term_totals']['grade_counts']['U'] ?? 0 }}</td>
                                                    <td>{{ $termData['term_totals']['a_star_percent'] ?? 0 }}%</td>
                                                    <td>{{ $termData['term_totals']['a_star_ab_percent'] ?? 0 }}%</td>
                                                    <td>{{ $termData['term_totals']['a_star_abc_percent'] ?? 0 }}%</td>
                                                    <td>{{ $termData['term_totals']['deu_percent'] ?? 0 }}%</td>
                                                @endif
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                {{-- Legend for better understanding --}}
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <strong>Note:</strong> 
                                        • Each subject shows its specific enrollment count 
                                        • Optional subjects have separate enrollment from core subjects
                                        • Grade totals show aggregate performance across all subjects
                                        • Percentages in totals are based on total tests taken across all subjects
                                    </small>
                                </div>
                            @else
                                <p class="text-muted">No exam data found for any subjects in {{ $gradeName }} for {{ $termData['term_year'] }}.</p>
                            @endif
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-warning" role="alert">
                        No exam performance data found for {{ $gradeName }} across any terms.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection