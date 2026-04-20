@extends('layouts.master')
@section('title')
    Students Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('students.students-custom-analysis') }}"> Back </a>
        @endslot
        @slot('title')
            Students Custom Analysis Report
        @endslot
    @endcomponent
    <style>
        .subjects-list {
            font-size: 0.9em;
            line-height: 1.2;
        }

        .subject-item {
            display: inline-block;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            padding: 0.1rem 0.3rem;
            margin: 0.1rem;
            font-size: 0.8em;
        }

        .stat-card {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
        }

        .stat-card h3 {
            margin-bottom: 5px;
            font-weight: bold;
        }

        .stat-card small {
            color: #6c757d;
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
                position: relative;
                margin: 0 auto;
                width: 100%;
                max-width: 100%;
            }

            .printable .card {
                margin: 0;
                border: none;
                width: 100%;
            }

            .printable .card-body {
                margin: 0 auto;
                padding: 0;
            }

            .printable .table {
                width: 750px;
                margin: 0;
            }

            .printable .table th,
            .printable .table td {
                padding: 8px;
                text-align: left;
            }

            .subject-item {
                background-color: transparent !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 0.2rem 0 0 !important;
            }

            .subject-item:after {
                content: ", ";
            }

            .subject-item:last-child:after {
                content: "";
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
    <div class="row no-print">
        <div class="col-12 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 18px; margin-bottom:10px; cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row printable d-flex justify-content-start">
        <div class="col-12">
            <div class="card">
                <div style="height: 120px;" class="card-header">
                    <div class="row">
                        <div class="col-6 align-items-start">
                            <div class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span style="margin:0; padding:0;"> {{ $school_data->physical_address }}</span>
                                <br>
                                <span style="margin:0; padding:0;"> {{ $school_data->postal_address }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted">Generated: {{ now()->format('d M Y H:i') }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($students->isEmpty())
                        <div class="alert alert-info">
                            No students found matching the selected criteria.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        @foreach ($fields as $field)
                                            <th>{{ $field_headers[$field] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $index => $student)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            @foreach ($fields as $field)
                                                <td>
                                                    @switch($field)
                                                        @case('house_id')
                                                            {{ optional($student->house)->name ?? '-' }}
                                                        @break

                                                        @case('sponsor_id')
                                                            {{ optional($student->sponsor)->first_name ?? '' }}
                                                            {{ optional($student->sponsor)->last_name ?? '-' }}
                                                        @break

                                                        @case('sponsor_phone')
                                                            {{ optional($student->sponsor)->phone ?? '-' }}
                                                        @break

                                                        @case('sponsor_telephone')
                                                            {{ optional($student->sponsor)->telephone ?? '-' }}
                                                        @break

                                                        @case('parent_email')
                                                            {{ optional($student->sponsor)->email ?? '-' }}
                                                        @break

                                                        @case('physical_address')
                                                            {{ optional(optional($student->sponsor)->otherInformation)->address ?? '-' }}
                                                        @break

                                                        @case('parent_workplace')
                                                            {{ optional($student->sponsor)->work_place ?? '-' }}
                                                        @break

                                                        @case('parent_profession')
                                                            {{ optional($student->sponsor)->profession ?? '-' }}
                                                        @break

                                                        @case('student_email')
                                                            {{ $student->email ?? '-' }}
                                                        @break

                                                        @case('psle_overall_grade')
                                                            {{ optional($student->psle)->overall_grade ?? '-' }}
                                                        @break

                                                        @case('jce_overall')
                                                            {{ optional($student->jce)->overall ?? '-' }}
                                                        @break

                                                        @case('class')
                                                            {{ optional($student->class)->name ?? '-' }}
                                                        @break

                                                        @case('student_type')
                                                            {{ optional($student->type)->type ?? '-' }}
                                                        @break

                                                        @case('klass_subjects')
                                                            <div class="subjects-list">
                                                                @if ($student->classes->isNotEmpty())
                                                                    @php
                                                                        $currentClass = $student->classes->first();
                                                                        $subjects =
                                                                            $currentClass->subjects ?? collect();
                                                                    @endphp
                                                                    @if ($subjects->isNotEmpty())
                                                                        @foreach ($subjects as $klassSubject)
                                                                            <span class="subject-item">
                                                                                {{ optional($klassSubject->gradeSubject->subject)->name ?? '-' }}
                                                                            </span>
                                                                        @endforeach
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </div>
                                                        @break

                                                        @case('optional_subjects')
                                                            <div class="subjects-list">
                                                                @if ($student->optionalSubjects->isNotEmpty())
                                                                    @foreach ($student->optionalSubjects as $optionalSubject)
                                                                        <span class="subject-item">
                                                                            {{ optional($optionalSubject->gradeSubject->subject)->name ?? $optionalSubject->name }}
                                                                        </span>
                                                                    @endforeach
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </div>
                                                        @break

                                                        @default
                                                            {{ $student->$field ?? '-' }}
                                                    @endswitch
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
