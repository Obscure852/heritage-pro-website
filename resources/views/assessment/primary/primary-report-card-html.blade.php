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
            {{ $student->fullname ?? '' }}
        @endslot
    @endcomponent
    <style>
        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            padding: 20px;
            font-size: 14px;
        }

        .row {
            width: 100%;
            max-width: none;
        }

        @media print {

            html,
            body {
                width: 400mm;
                height: 400mm;
                margin: 0;
                padding: 0;
                font-size: 16px;
                page-break-after: avoid;
                page-break-before: avoid;
            }

            .card {
                box-shadow: none;
                font-size: 16px;
            }

            .row {
                width: auto;
                margin-top: 40px;
                margin-left: auto;
                margin-right: auto;
                page-break-inside: avoid;
                display: block;
            }


            .table {
                width: auto;
                max-width: none;
            }

            .table-bordered th,
            .table-bordered td {
                border: 1px solid #838383 !important;
                /* Keeping your border style */
                font-size: 12pt;
                /* Adjust font size for print */
            }

            .remarks {
                font-size: 12pt;
            }

            /* Hide elements not needed for print */
            .bx-printer,
            .breadcrumb,
            .nav,
            footer,
            button {
                display: none !important;
            }
        }
    </style>
    <div class="row">
        <div class="col-md-10 col-xl-10 col-xxl-10 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 15px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer"></i>
        </div>
    </div>
    {{-- Print from here to the bottom only --}}
    <div class="row">
        <div class="col-md-10 col-xl-10 col-xxl-10">
            <div class="card">
                <table style="width: 100%; border-collapse: collapse;">
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
                <br>
                <br>
                <!-- Personal details table -->
                <table class="remarks table-responsive" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 33%;text-align:left;">
                            <p><strong>Firstname:</strong> {{ $student->first_name }}</p>
                        </td>
                        <td style="width: 33%;text-align:left;">
                            <p><strong>Lastname:</strong> {{ $student->last_name }}</p>
                        </td>
                        <td style="width: 33%;text-align:left;">
                            <p><strong>Date:</strong> {{ now()->format('Y-m-d') }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:left;">
                            <p><strong>Absent Days:</strong> {{ $absentDays }}</p>
                        </td>
                        <td style="text-align:left;">
                            <p><strong>Class:</strong> {{ $currentClass->name }}</p>
                        </td>
                        <td style="text-align:left;">
                            <p><strong>Gender:</strong> {{ $student->gender }}</p>
                        </td>
                    </tr>
                </table>
                <!-- Performance details table -->
                <table class="remarks" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 33%;text-align:left;">
                            <p><strong>Position:</strong> {{ $studentPosition ?? '' }}</p>
                        </td>
                        <td style="width: 33%;text-align:left;">
                            <p><strong>No. in Class:</strong> {{ $classSize ?? '' }}</p>
                        </td>
                        <td style="width: 33%;text-align:left">
                            <p><strong>Class Average:</strong> {{ round($classAverage, 0) ?? '' }}%</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:left">
                            <p><strong>School Re-opens:</strong> {{ $nextTermStartDate ?? 'N/A' }}</p>
                        </td>
                        <td style="text-align:left">
                            <p><strong>Term Start:</strong> {{ $termStart ?? '' }}</p>
                        </td>
                        <td style="text-align:left">
                            <p><strong>Term End:</strong> {{ $termEnd ?? '' }}</p>
                        </td>
                    </tr>
                </table>

                <!-- Score Table -->
                <table class="table table-bordered table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Possible Marks</th>
                            <th>Actual Marks</th>
                            <th>%</th>
                            <th>Grade</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($scores as $score)
                            <tr>
                                <td>{{ $score['subject'] }}</td>
                                <td>{{ $score['out_of'] }}</td>
                                <td>{{ $score['score'] }}</td>
                                <td>{{ number_format($score['percentage'], 0) }}%</td>
                                <td>{{ $score['grade'] }}</td>
                                <td>{{ $score['comments'] }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2">Grand Total:</td>
                            <td>{{ $totalScore }}</td>
                            <td>{{ number_format($averagePercentage, 0) }}%</td>
                            <td colspan="2">{{ $overallGrade->grade ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="row">
                    <div class="col-6">
                        <p class="remarks"><strong>Other Information: </strong>
                            @if (!empty($otherInfo))
                                {{ $otherInfo }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    <div class="col-6">
                        <p class="remarks"><strong>School Fees Owing: </strong>
                            @if ($schoolFees !== null)
                                BWP: {{ number_format($schoolFees, 2) }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
                <table class="remarks table table-bordered table-sm" style="width: 100%;text-align: left;">
                    <tr>
                        <th style="width: 50%;">Class Teacher's Remarks</th>
                        <th style="text-align: left; width: 50%;">
                            Teacher's Name: {{ $teacherName ?? 'N/A' }}</th>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div style="min-height: 20px; padding: 2px;">
                                {{ $classTeacherRemarks ?? 'No remarks provided.' }}</div>
                        </td>
                    </tr>
                </table>
                <br>
                <table class="remarks table table-bordered table-sm" style="width: 100%;text-align: left;">
                    <tr>
                        <th style="width: 50%;">Head Teacher's Remarks</th>
                        <th style="text-align: left; width: 50%;">
                            Head Teacher's Name: {{ $schoolHeadName ?? 'N/A' }}</th>
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
                            @if (!empty($teacherSignaturePath))
                                <img src="{{ asset(ltrim(str_replace(public_path(), '', $teacherSignaturePath), '/')) }}"
                                    alt="{{ $teacherName }}'s signature" style="height: 60px;">
                            @else
                                <p style="margin-top:10px;">.....................</p>
                            @endif
                        </td>
                        <td style="width: 34%; text-align: center; vertical-align: top;">
                        </td>
                        <td style="width: 33%; text-align: left; vertical-align: top;">
                            <strong>Head Teacher's signature:</strong><br>
                            @if (!empty($schoolHeadSignaturePath))
                                <img src="{{ asset(ltrim(str_replace(public_path(), '', $schoolHeadSignaturePath), '/')) }}"
                                    alt="{{ $schoolHeadName }}'s signature" style="height: 60px;">
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
    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection
