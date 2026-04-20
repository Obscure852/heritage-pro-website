@extends('layouts.master')
@section('title')
    Student Report Card
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ $gradebookBackUrl }}">
                Back</a>
        @endslot
        @slot('title')
            {{ $student->fullname ?? '' }} 's Progress Report Card
        @endslot
    @endcomponent
    <style>
        table {
            border: none !important;
        }

        .score-table,
        .remarks-table {
            border-radius: 3px !important;
            border: 1px solid #dee2e6 !important;
        }

        .score-table th,
        .score-table td,
        .remarks-table th,
        .remarks-table td {
            border: 1px solid #dee2e6 !important;
            padding: 4px;
            text-align: start;
            font-size: 14px;
        }

        .header-table,
        .personal-details-table,
        .signature-table {
            border: none !important;
            width: 100% !important;
        }

        .header-table td,
        .personal-details-table td,
        .signature-table td {
            border: none !important;
        }

        .personal-details-table td:first-child {
            text-align: left !important;
        }

        .personal-details-table td:last-child {
            text-align: right !important;
        }

        .remarks {
            font-size: 14px;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            .printable {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-width: 800px;
                margin: 0 auto;
            }

            .card {
                page-break-inside: avoid;
                box-shadow: none !important;
            }

            .score-table,
            .remarks-table {
                font-size: 11px !important;
                margin-bottom: 10px !important;
                border-radius: 3px !important;
                overflow: hidden !important;
                border: 1px solid #dee2e6 !important;
            }

            .score-table td,
            .score-table th,
            .remarks-table td,
            .remarks-table th {
                padding: 3px !important;
                border: 1px solid #dee2e6 !important;
            }

            .remarks {
                font-size: 11px !important;
                margin-bottom: 5px !important;
            }

            img[alt*="signature"] {
                height: 40px !important;
            }

            br {
                margin: 0 !important;
                padding: 0 !important;
                line-height: 1 !important;
            }

            img[alt="School Logo"] {
                height: 60px !important;
            }

            .bx-printer {
                display: none !important;
            }
        }
    </style>
    <div class="row">
        <div class="col-8 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 18px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-8">
            <div class="card">
                <div class="card-body">
                    <div class="report-card">
                        <!-- Header Table - No borders -->
                        <table class="header-table" style="width: 100%; border-collapse: collapse;">
                            <tbody>
                                <tr>
                                    <td style="width: 20%; text-align: left; vertical-align: top;">
                                        <div
                                            style="background-image: url('{{ asset('assets/images/coat_of_arms.jpg') }}'); background-size: contain; background-repeat: no-repeat; background-position: center; height: 80px; width: 100%;">
                                        </div>
                                    </td>
                                    <td style="width: 60%; text-align: center; vertical-align: top;">
                                        <div style="font-size: 14px;">
                                            <strong>{{ $school_setup->school_name }}</strong><br>
                                            <span style="font-size: 12px;">{{ $school_setup->physical_address }}</span><br>
                                            <span style="font-size: 12px;">{{ $school_setup->postal_address }}</span><br>
                                            <span style="font-size: 12px;">Tel: {{ $school_setup->telephone }} Fax:
                                                {{ $school_setup->fax }}</span>
                                        </div>
                                    </td>
                                    <td style="width: 20%; text-align: right; vertical-align: top;">
                                        <img src="{{ URL::asset($school_setup->logo_path) }}" alt="School Logo"
                                            style="height: 80px;">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <br>

                        <!-- Personal details table - No borders -->
                        <table class="personal-details-table remarks" style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="width: 33%; text-align: left; padding: 2px 0;">
                                    <p style="margin: 0;"><strong>Firstname:</strong> {{ $student->first_name }}</p>
                                </td>
                                <td style="width: 33%; text-align: left; padding: 2px 0;">
                                    <p style="margin: 0;"><strong>Lastname:</strong> {{ $student->last_name }}</p>
                                </td>
                                <td style="width: 33%; text-align: left; padding: 2px 0;">
                                    <p style="margin: 0;"><strong>Gender:</strong> {{ $student->gender }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: left; padding: 2px 0;">
                                    <p style="margin: 0;"><strong>Class:</strong> {{ $currentClass->name }}</p>
                                </td>
                                <td style="text-align: left; padding: 2px 0;">
                                    <p style="margin: 0;"><strong>Class Position:</strong>
                                        {{ $classPosition }}/{{ $totalStudentsInClass }}</p>
                                </td>
                                <td style="text-align: left; padding: 2px 0;">
                                    <p style="margin: 0;"><strong>Grade Position:</strong>
                                        {{ $gradePosition }}/{{ $totalStudentsInGrade }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: left; padding: 2px 0;">
                                    <p style="margin: 0;"><strong>Class Average:</strong>
                                        {{ number_format($classAverage, 2) }} pts</p>
                                </td>
                                <td style="text-align: left; padding: 2px 0;">
                                    <p style="margin: 0;"><strong>Grade Average:</strong> {{ $gradeAverage }} pts</p>
                                </td>
                                <td style="text-align: left; padding: 2px 0;">
                                    <p style="margin: 0;"><strong>Absent Days:</strong>
                                        @php
                                            $currentTermId = session(
                                                'selected_term_id',
                                                \App\Helpers\TermHelper::getCurrentTerm()->id,
                                            );
                                            $manualEntry = $student
                                                ->manualAttendanceEntries()
                                                ->where('term_id', $currentTermId)
                                                ->first();
                                            if ($manualEntry && $manualEntry->days_absent !== null) {
                                                $absentDays = $manualEntry->days_absent;
                                            } else {
                                                $absentDays = $student
                                                    ->absentDays()
                                                    ->where('term_id', $currentTermId)
                                                    ->count();
                                            }
                                        @endphp
                                        {{ $absentDays }}
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: left; padding: 2px 0;">
                                    <p style="margin: 0;"><strong>Term Start:</strong>
                                        @if (isset($currentClass->term) && $currentClass->term->start_date)
                                            {{ \Carbon\Carbon::parse($currentClass->term->start_date)->format('d F Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </td>
                                <td style="text-align: left; padding: 2px 0;">
                                    <p style="margin: 0;"><strong>Term End:</strong>
                                        @if (isset($currentClass->term) && $currentClass->term->end_date)
                                            {{ \Carbon\Carbon::parse($currentClass->term->end_date)->format('d F Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </td>
                                <td style="text-align: left; padding: 2px 0;">
                                    <p style="margin: 0;"><strong>School Re-opens:</strong>
                                        @if (isset($nextTermStartDate) && $nextTermStartDate)
                                            {{ \Carbon\Carbon::parse($nextTermStartDate)->format('d F Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <!-- Score Table - With borders and rounded corners -->
                        <table class="table table-sm mt-4 score-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th>Term Mark</th>
                                    <th>Exam Marks</th>
                                    <th>%</th>
                                    <th>Points</th>
                                    <th>Grade</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($scores as $score)
                                    <tr>
                                        <td>{{ $score['subject'] }}</td>
                                        <td>{{ $score['teacher'] ?? 'N/A' }}</td>
                                        <td>
                                            @if ($score['caAverage'] !== null)
                                                {{ $score['caAverage'] }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($score['score'] !== null)
                                                {{ $score['score'] }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($score['percentage'] !== null)
                                                {{ number_format($score['percentage'], 0) }}%
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $score['points'] ?: '-' }}</td>
                                        <td>{{ $score['grade'] ?: '-' }}</td>
                                        <td>{{ $score['comments'] }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="3"></td>
                                    <td></td>
                                    <td></td>
                                    <td colspan="3">
                                        <strong>Points:</strong> {{ $totalPoints }}
                                        <strong>Grade:</strong> {{ $grade }}
                                        @if (!empty($student->psle->overall_grade))
                                            <strong>PSLE Grade:</strong> {{ $student->psle->overall_grade ?? '' }}
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        @php
                            $currentTermId = session('selected_term_id', \App\Helpers\TermHelper::getCurrentTerm()->id);
                            $manualEntry = $student
                                ->manualAttendanceEntries()
                                ->where('term_id', $currentTermId)
                                ->first();

                            $school_fees = null;
                            if ($manualEntry && $manualEntry->school_fees_owing !== null) {
                                $school_fees = $manualEntry->school_fees_owing;
                            }
                        @endphp

                        <!-- Other info table - No borders -->
                        <table class="table-borderless personal-details-table" style="width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="width: 50%; padding: 5px 0;">
                                        <p class="remarks"><strong>Other Information: </strong>
                                            @if ($manualEntry && !empty($manualEntry->other_info))
                                                {{ $manualEntry->other_info }}
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </td>
                                    <td style="width: 50%; padding: 5px 0;">
                                        <p class="remarks"><strong>School Fees Owing: </strong>
                                            @if ($school_fees !== null)
                                                BWP: {{ number_format($school_fees, 2) }}
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Remarks tables - With borders and rounded corners -->
                        <table class="table table-sm remarks remarks-table">
                            <tr>
                                <th style="width: 50%;">Class Teacher's Remarks</th>
                                <th style="width: 50%;">
                                    Teacher's Name: {{ $currentClass->teacher->full_name ?? 'N/A' }}</th>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div style="min-height: 20px; padding: 2px;">
                                        {{ $classTeacherRemarks ?? 'No remarks provided.' }}</div>
                                </td>
                            </tr>
                        </table>
                        <br>
                        <table class="table table-sm remarks remarks-table">
                            <tr>
                                <th style="width: 50%;">Head Teacher's Remarks</th>
                                <th style="width: 50%;">
                                    Head Teacher's Name: {{ $school_head->full_name ?? 'N/A' }}</th>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div style="min-height: 20px; padding: 2px;">
                                        {{ $headTeacherRemarks ?? 'No remarks provided.' }}</div>
                                </td>
                            </tr>
                        </table>

                        <!-- Signatures - No borders -->
                        <br>
                        <table class="signature-table remarks" style="width: 100%;">
                            <tr>
                                <td style="width: 33%; text-align: left; vertical-align: top;">
                                    <strong>Class Teacher's signature:</strong><br>
                                    @if (!empty($currentClass->teacher->signature_path))
                                        <img src="{{ URL::asset($currentClass->teacher->signature_path) }}"
                                            alt="{{ $currentClass->teacher->full_name }}'s signature"
                                            style="height: 60px;">
                                    @else
                                        <p style="margin-top:10px;">.....................</p>
                                    @endif
                                </td>
                                <td style="width: 34%; text-align: center; vertical-align: top;">
                                </td>
                                <td style="width: 33%; text-align: right; vertical-align: top;">
                                    <strong>Head Teacher's signature:</strong><br>
                                    @if (!empty($school_head->signature_path))
                                        <img src="{{ URL::asset($school_head->signature_path) }}"
                                            alt="{{ $school_head->full_name }}'s signature" style="height: 60px;">
                                    @else
                                        <p style="margin-top:10px;">.....................</p>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
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
