@extends('layouts.master')
@section('title')
    Student Report Card
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ $gradebookBackUrl }}"> Back</a>
        @endslot
        @slot('title')
            {{ $student->fullname ?? '' }}'s Report Card
        @endslot
    @endcomponent
    <style>
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #838383 !important;
            padding: 4px;
            text-align: start;
            font-size: 14px;
        }

        .remarks {
            font-size: 14px;
        }

        .report-card-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-card-table th,
        .report-card-table td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }

        .report-card-table .comments-column {
            width: 25%;
        }

        .report-card-table .teacher-column {
            width: 10%;
        }
    </style>
    <div class="row">
        <div class="col-md-8 d-flex justify-content-end">
            <i onclick="printContent()" style="font-size: 18px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="report-card">
                        <table style="width: 100%; border-collapse: collapse;">
                            <tbody>
                                <tr>
                                    <td style="width: 20%; text-align: left; vertical-align: top;">
                                        <img height="200" width="200"
                                            src="{{ asset('assets/images/coat_of_arms.jpg') }}" alt="Coat of Arms"
                                            style="height: 80px; width: auto;">
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
                                        <img src="{{ public_path($school_setup->logo_path) }}" alt="School Logo"
                                            style="height: 80px;">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <br>
                        <!-- Personal details table -->
                        <table class="remarks" style="width: 100%; border-collapse: collapse;">
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
                                    <p><strong>Absent Days:</strong>
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
                                    <p><strong>Position:</strong> {{ $position }}</p>
                                </td>
                                <td style="width: 33%;text-align:left;">
                                    <p><strong>No. in Class:</strong> {{ $currentClass->students->count() ?? '' }}</p>
                                </td>
                                <td style="width: 33%;text-align:left;">
                                    <p><strong>Class Average Points:</strong> {{ number_format($classAverage, 1) }} </p>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:left">
                                    <p><strong>School Re-opens:</strong> {{ $nextTermStartDate ?? 'N/A' }}</p>
                                </td>
                                <td style="text-align:left;">
                                    <p><strong>Term Start:</strong> {{ $currentClass->term->start_date ?? '' }}</p>
                                </td>
                                <td style="text-align:left;">
                                    <p><strong>Term End:</strong> {{ $currentClass->term->end_date ?? '' }}</p>
                                </td>
                            </tr>
                        </table>
                        <!-- Score Table -->
                        <table class="report-card-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Term <br>Average(%)</th>
                                    <th>JC Grade</th>
                                    <th>Exam Mark</th>
                                    <th>%</th>
                                    <th>Grade</th>
                                    <th>Points</th>
                                    <th class="comments-column">Comments</th>
                                    <th class="teacher-column">Teacher</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($scores as $score)
                                    <tr>
                                        <td>{{ $score['subject'] }}</td>
                                        <td>{{ $score['caAverage'] }}</td>
                                        <td>
                                            @if ($score['jceGrade'])
                                                @if ($score['isOverallJceGrade'])
                                                    <span class="overall-jce-grade"
                                                        title="Overall JC Grade"><strong>{{ $score['jceGrade'] }}</strong></span>
                                                @else
                                                    {{ $score['jceGrade'] }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $score['score'] }}</td>
                                        <td>{{ number_format($score['percentage'], 0) }}</td>
                                        @if ($score['is_double'])
                                            <td>{{ $score['grade'] }} {{ $score['grade'] }}</td>
                                        @else
                                            <td>{{ $score['grade'] }}</td>
                                        @endif
                                        @if ($score['is_double'])
                                            <td>{{ $score['points'] * 2 }}</td>
                                        @else
                                            <td>{{ $score['points'] }}</td>
                                        @endif
                                        <td class="comments-column">{{ $score['comments'] }}</td>
                                        <td class="teacher-column">{{ $score['teacher'] }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="4"></td>
                                    <td></td>
                                    <td colspan="4"> <strong>Points:</strong> {{ $totalPoints }}</td>
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
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td style="width: 50%;">
                                        <p class="remarks"><strong>Other Information: </strong>
                                            @if ($manualEntry && !empty($manualEntry->other_info))
                                                {{ $manualEntry->other_info }}
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </td>
                                    <td style="width: 50%;">
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
                        <table class="remarks"
                            style="width: 100%; border-collapse: collapse; border: 1px solid #333; text-align: left;">
                            <tr>
                                <th style="border: 1px solid #838383; width: 50%;">Class Teacher's Remarks</th>
                                <th style="border: 1px solid #838383; text-align: left; width: 50%;">
                                    Teacher's Name: {{ $currentClass->teacher->full_name ?? 'N/A' }}</th>
                            </tr>
                            <tr>
                                <td colspan="2" style="border: 1px solid #838383;">
                                    <div style="min-height: 20px; padding: 2px;">
                                        {{ $classTeacherRemarks ?? 'No remarks provided.' }}</div>
                                </td>
                            </tr>
                        </table>
                        <br>
                        <table class="remarks"
                            style="width: 100%; border-collapse: collapse; border: 1px solid #333; text-align: left;">
                            <tr>
                                <th style="border: 1px solid #838383; width: 50%;">Head Teacher's Remarks</th>
                                <th style="border: 1px solid #838383; text-align: left; width: 50%;">
                                    Head Teacher's Name: {{ $school_head->full_name ?? 'N/A' }}</th>
                            </tr>
                            <tr>
                                <td colspan="2" style="border: 1px solid #838383;">
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
                                <td style="width: 33%; text-align: left; vertical-align: top;">
                                    <strong>Head Teacher's signature:</strong><br>
                                    @if (!empty($school_head->signature_path))
                                        <img src="{{ URL::asset($school_head->signature_path) }}"
                                            alt="{{ $school_head->full_name }}'s signature" style="height: 60px;">
                                    @else
                                        <p>.....................</p>
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
