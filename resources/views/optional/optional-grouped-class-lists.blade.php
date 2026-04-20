@extends('layouts.master')
@section('title')
    {{ $grade->name }} Optional Subjects Analysis
@endsection

@section('css')
    <style>
        .subject-section {
            margin-bottom: 2rem;
            break-inside: avoid;
        }

        .student-count-badge {
            font-size: 0.9em;
            padding: 0.3em 0.6em;
            border-radius: 50%;
            margin-left: 0.5em;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                line-height: normal;
            }

            .card {
                box-shadow: none;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            .printable {
                left: 0;
                top: 0;
                width: 100%;
            }

            .subject-section {
                page-break-inside: avoid;
            }

            .no-print {
                display: none;
            }

            .table td,
            .table th {
                padding: 4px 8px;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('optional.index') }}"> Back </a>
        @endslot
        @slot('title')
            {{ $grade->name }} Optional Subjects Analysis
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-12 d-flex justify-content-end no-print">
            <i onclick="printContent()" style="font-size: 18px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-md-12">
            <div class="card">
                <div style="height: 120px;" class="card-header">
                    <div class="row">
                        <div class="col-md-6 align-items-start">
                            <div class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span>{{ $school_data->physical_address }}</span>
                                <br>
                                <span>{{ $school_data->postal_address }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <h4 class="mb-4">{{ $grade->name }} Optional Subjects Analysis - Term {{ $term->term }}</h4>

                    <!-- Summary Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6>Total Subject Types</h6>
                                    <h3>{{ $statistics['total_subjects'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6>Total Students</h6>
                                    <h3>{{ $statistics['total_students'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6>Avg Students per Subject</h6>
                                    <h3>{{ $statistics['average_students_per_subject'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($groupedSubjects->isEmpty())
                        <div class="alert alert-info">
                            No optional subjects found for {{ $grade->name }} in this term.
                        </div>
                    @else
                        <!-- Grouped Subjects -->
                        @foreach ($groupedSubjects as $subjectName => $subjects)
                            <div class="subject-section">
                                <h5 class="mt-4 d-flex align-items-center">
                                    {{ $subjectName }}
                                    {{ $statistics['subject_distribution'][$subjectName]['students'] }} students
                                </h5>

                                @foreach ($subjects as $subject)
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <strong>Teacher:</strong>
                                                    @if($subject->teacher)
                                                        {{ substr($subject->teacher->firstname, 0, 1) }}. {{ $subject->teacher->lastname }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>Assistant:</strong>
                                                    @if($subject->assistantTeacher)
                                                        {{ substr($subject->assistantTeacher->firstname, 0, 1) }}. {{ $subject->assistantTeacher->lastname }}
                                                    @else
                                                        —
                                                    @endif
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <strong>Students:</strong> {{ $subject->students->count() }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>ID Number</th>
                                                            <th>Student Name</th>
                                                            <th>Date of Birth</th>
                                                            <th>Gender</th>
                                                            <th>Nationality</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($subject->students as $index => $student)
                                                            <tr>
                                                                <td>{{ $index + 1 }}</td>
                                                                <td>{{ $student->id_number }}</td>
                                                                <td>{{ $student->first_name }} {{ $student->last_name }}
                                                                </td>
                                                                <td>{{ \Carbon\Carbon::parse($student->date_of_birth)->format('d/m/Y') }}
                                                                </td>
                                                                <td>{{ $student->gender }}</td>
                                                                <td>{{ $student->nationality }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
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
