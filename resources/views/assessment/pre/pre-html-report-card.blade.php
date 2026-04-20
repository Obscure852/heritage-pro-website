@extends('layouts.master')
@section('title')
    Student Report Card
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            {{ $student->fullname ?? '' }}'s Report Card
        @endslot
    @endcomponent
    <style>
        .card {
            padding: 20px;
            font-size: 14px;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                line-height: normal;
                font-size: 10px;
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
                overflow-x: auto;
                /* Allow horizontal scrolling for wide content */
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
                width: 720px;
                overflow-x: auto;
                /* Allow horizontal scrolling for wide tables */
            }

            .printable .table th,
            .printable .table td {
                padding: 2px;
                text-align: left;
                font-size: 10px;
            }
        }

        .checkmark {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid #000;
            position: relative;
        }

        .checkmark::after {
            content: '';
            position: absolute;
            left: 3px;
            top: 0;
            width: 3px;
            height: 8px;
            border: solid #000;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .criteria-report-table {
            width: 100%;
            margin-bottom: 12px;
        }

        .criteria-report-table th,
        .criteria-report-table td {
            vertical-align: middle;
            padding: 4px 6px;
        }

        .criteria-report-table .criteria-title,
        .criteria-report-table .criteria-component {
            text-align: left;
            word-break: break-word;
        }

        .criteria-report-table .criteria-option {
            text-align: center;
            width: 32px;
            min-width: 32px;
            max-width: 32px;
            padding: 4px 2px;
        }
    </style>
    <div class="row">
        <div class="col-md-10 col-xl-10 col-xxl-10 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 15px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer"></i>
        </div>
    </div>
    {{-- Print from here to the bottom only --}}
    <div class="row printable">
        <div class="col-md-10 col-xl-10 col-xxl-10">
            <div class="card">
                <table class="table table-responsive">
                    <tbody>
                        <tr>
                            <td style="width: 50%; vertical-align: top;">
                                <strong>{{ $school_setup->school_name }}</strong><br>
                                <span>{{ $school_setup->physical_address }}</span><br>
                                <span>{{ $school_setup->postal_address }}</span><br>
                                <span>Tel: {{ $school_setup->telephone }} Fax: {{ $school_setup->fax }}</span>
                            </td>
                            <td style="width: 50%; text-align: right; vertical-align: top;">
                                <img src="{{ asset($school_setup->logo_path) }}" alt="School Logo" style="height: 80px;">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- Personal details table -->
                <table class="table table-borderless">
                    <tr>
                        <td style="width: 33%;text-align:left;">
                            <p><strong>Firstname:</strong> {{ $student->first_name ?? '' }}</p>
                        </td>
                        <td style="width: 33%;text-align:left;">
                            <p><strong>Lastname:</strong> {{ $student->last_name ?? '' }}</p>
                        </td>
                        <td style="width: 33%;text-align:left;">
                            <p><strong>Date:</strong> {{ now()->format('Y-m-d') }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:left;">
                            <p><strong>Absent Days:</strong> {{ $student->absentDaysCount() }}</p>
                        </td>
                        <td style="text-align:left;">
                            <p><strong>Class:</strong> {{ $student->currentClass()->name ?? '' }}</p>
                        </td>
                        <td style="text-align:left;">
                            <p><strong>Gender:</strong> {{ $student->gender ?? '' }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:left">
                            <p><strong>School Re-opens:</strong> {{ $nextTermStartDate ?? 'N/A' }}</p>
                        </td>
                        <td style="text-align:left">
                            <p><strong>Term Start:</strong> {{ $student->currentClass()->term->start_date ?? '' }}</p>
                        </td>
                        <td style="text-align:left">
                            <p><strong>Term End:</strong> {{ $student->currentClass()->term->start_date ?? '' }}</p>
                        </td>
                    </tr>
                </table>

                <!-- Main Table -->
                @foreach ($gradeSubjects as $gradeSubject)
                    @if ($gradeSubject->components->count() > 0 && $gradeSubject->criteriaBasedTests->count() > 0)
                        @php
                            $examTests = $gradeSubject->criteriaBasedTests->where('type', 'Exam')->sortBy('sequence');
                            $gradeOptions = optional($gradeSubject->gradeOptionSets->first())->gradeOptions ?? collect();
                            $optionColumnWidth = 32;
                        @endphp
                        <table class="table table-sm table-bordered table-striped avoid-page-break criteria-report-table">
                            <colgroup>
                                <col>
                                @foreach ($examTests as $test)
                                    @foreach ($gradeOptions as $option)
                                        <col style="width: {{ $optionColumnWidth }}px;">
                                    @endforeach
                                @endforeach
                            </colgroup>
                            <thead>
                                <tr>
                                    <th class="criteria-title">{{ $gradeSubject->subject->name }}</th>
                                    @foreach ($examTests as $test)
                                        @foreach ($gradeOptions as $option)
                                            <th class="criteria-option">{{ $option->label }}</th>
                                        @endforeach
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($gradeSubject->components as $component)
                                    <tr>
                                        <td class="criteria-component">{{ $component->name }}</td>
                                        @foreach ($examTests as $test)
                                            @foreach ($gradeOptions as $option)
                                                <td class="criteria-option">
                                                    @php
                                                        $assessment = $student->criteriaBasedStudentTests
                                                            ->where('grade_subject_id', $gradeSubject->id)
                                                            ->where('component_id', $component->id)
                                                            ->where('criteria_based_test_id', $test->id)
                                                            ->where('grade_option_id', $option->id)
                                                            ->first();
                                                    @endphp
                                                    @if ($assessment)
                                                        <small style="font-size: 15px;">✓</small>
                                                    @else
                                                        <span>&nbsp;</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                @endforeach
                <table class="table table-bordered table-sm">
                    <tr>
                        <th style="width: 50%;">Class Teacher's Remarks</th>
                        <th style="text-align: left; width: 50%;">
                            Teacher's Name: {{ $student->currentClass()->teacher->fullName ?? 'N/A' }}</th>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div style="min-height: 20px; padding: 2px;">
                                {{ $classTeacherRemarks ?? 'No remarks provided.' }}</div>
                        </td>
                    </tr>
                </table>
                <br>
                <table class="table table-bordered table-sm">
                    <tr>
                        <th style="width: 50%;">Head Teacher's Remarks</th>
                        <th style="text-align: left; width: 50%;">
                            Head Teacher's Name: {{ $student->currentClass()->teacher->fullName ?? 'N/A' }}</th>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div style="min-height: 20px; padding: 2px;">
                                {{ $headTeachersRemarks ?? 'No remarks provided.' }}</div>
                        </td>
                    </tr>
                </table>
                <!-- Signatures -->
                <br>
                <table class="remarks" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 33%; text-align: left; vertical-align: top;">
                            <strong>Class Teacher's signature:</strong><br>
                            @if (!empty($student->currentClass()->teacher->signature_path))
                                <!-- Ensure the path to the signature is accessible by the PDF generator -->
                                <img src="{{ asset($student->currentClass()->teacher->signature_path) }}"
                                    alt="{{ $student->currentClass()->teacher->fullName }}'s signature"
                                    style="height: 60px;">
                            @else
                                <p style="margin-top:10px;">.....................</p>
                            @endif
                        </td>
                        <td style="width: 34%; text-align: center; vertical-align: top;">
                        </td>
                        <td style="width: 33%; text-align: left; vertical-align: top;">
                            <strong>Head Teacher's signature:</strong><br>
                            @if (!empty($school_head->signature_path))
                                <!-- Ensure the path to the signature is accessible by the PDF generator -->
                                <img src="{{ asset($school_head->signature_path) }}"
                                    alt="{{ $school_head->fullName }}'s signature" style="height: 60px;">
                            @else
                                <p>.....................</p>
                            @endif
                        </td>
                    </tr>
                </table>

            </div>
        </div>
    </div>
@endsection
@section('script')
    <!-- pristine js -->
    <script src="{{ URL::asset('/assets/libs/pristinejs/pristinejs.min.js') }}"></script>
    <!-- form validation -->
    <script src="{{ URL::asset('/assets/js/pages/form-validation.init.js') }}"></script>
    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection
