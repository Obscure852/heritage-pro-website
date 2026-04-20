<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Class Report Cards</title>
    <style>
        body,
        html {
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        .container {
            width: 100%;
            max-width: none;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #838383 !important;
            padding: 4px;
            text-align: start;
            font-size: 12px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .remarks {
            font-size: 12px;
            text-align: left;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    @php
        $schoolLogoSrc = \App\Support\PdfImage::toDataUri($school_setup->logo_path ?? null);
        $headTeacherSignatureSrc = \App\Support\PdfImage::toDataUri($school_head->signature_path ?? null);
    @endphp
    @foreach ($reportCards as $reportCard)
        @php
            $classTeacherSignatureSrc = \App\Support\PdfImage::toDataUri($reportCard['class_teacher_signature'] ?? null);
        @endphp
        <div class="container">
            <div class="report-card">
                <table style="width: 100%; border-collapse: collapse;">
                    <tbody>
                        <tr>
                            <td style="width: 20%; text-align: left; vertical-align: top;">
                                <img height="200" width="200"
                                    src="{{ public_path('assets/images/coat_of_arms.jpg') }}" alt="Coat of Arms"
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
                                @if ($schoolLogoSrc)
                                    <img src="{{ $schoolLogoSrc }}" alt="School Logo" style="height: 80px;">
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
                <br>
                <!-- Merged personal and performance details table -->
                <table class="remarks" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 33%; text-align: left; padding: 2px 0;">
                            <p style="margin: 0;"><strong>Firstname:</strong> {{ $reportCard['student']->first_name }}
                            </p>
                        </td>
                        <td style="width: 33%; text-align: left; padding: 2px 0;">
                            <p style="margin: 0;"><strong>Lastname:</strong> {{ $reportCard['student']->last_name }}</p>
                        </td>
                        <td style="width: 33%; text-align: left; padding: 2px 0;">
                            <p style="margin: 0;"><strong>Gender:</strong> {{ $reportCard['student']->gender }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left; padding: 2px 0;">
                            <p style="margin: 0;"><strong>Class:</strong> {{ $reportCard['class_name'] }}</p>
                        </td>
                        <td style="text-align: left; padding: 2px 0;">
                            <p style="margin: 0;"><strong>Class Position:</strong>
                                {{ $reportCard['classPosition'] }}/{{ $reportCard['totalStudentsInClass'] }}</p>
                        </td>
                        <td style="text-align: left; padding: 2px 0;">
                            <p style="margin: 0;"><strong>Grade Position:</strong>
                                {{ $reportCard['gradePosition'] }}/{{ $reportCard['totalStudentsInGrade'] }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left; padding: 2px 0;">
                            <p style="margin: 0;"><strong>Class Average:</strong> {{ $reportCard['classAverage'] }} pts
                            </p>
                        </td>
                        <td style="text-align: left; padding: 2px 0;">
                            <p style="margin: 0;"><strong>Grade Average:</strong> {{ $reportCard['gradeAverage'] }} pts
                            </p>
                        </td>
                        <td style="text-align: left; padding: 2px 0;">
                            <p style="margin: 0;"><strong>Absent Days:</strong>
                                @php
                                    $currentTermId = session(
                                        'selected_term_id',
                                        \App\Helpers\TermHelper::getCurrentTerm()->id,
                                    );
                                    $manualEntry = $reportCard['student']
                                        ->manualAttendanceEntries()
                                        ->where('term_id', $currentTermId)
                                        ->first();

                                    if ($manualEntry && $manualEntry->days_absent !== null) {
                                        $absentDays = $manualEntry->days_absent;
                                    } else {
                                        $absentDays = $reportCard['student']
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
                                @if (isset($reportCard['term_start_date']) && $reportCard['term_start_date'])
                                    {{ \Carbon\Carbon::parse($reportCard['term_start_date'])->format('d F Y') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </td>
                        <td style="text-align: left; padding: 2px 0;">
                            <p style="margin: 0;"><strong>Term End:</strong>
                                @if (isset($reportCard['term_end_date']) && $reportCard['term_end_date'])
                                    {{ \Carbon\Carbon::parse($reportCard['term_end_date'])->format('d F Y') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </td>
                        <td style="text-align: left; padding: 2px 0;">
                            <p style="margin: 0;"><strong>School Re-opens:</strong>
                                @if (isset($reportCard['nextTermStartDate']) && $reportCard['nextTermStartDate'])
                                    {{ \Carbon\Carbon::parse($reportCard['nextTermStartDate'])->format('d F Y') }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </td>
                    </tr>
                </table>
                <br>
                <!-- Score Table -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Term Mark</th>
                            <th>Exam Marks</th>
                            <th>%</th>
                            <th>Points</th>
                            <th>Grade</th>
                            <th>Comments</th>
                            <th>Teacher</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reportCard['scores'] as $score)
                            <tr>
                                <td>{{ $score['subject'] }}</td>
                                <td>{{ $score['caAverage'] }}</td>
                                <td>{{ $score['score'] }}</td>
                                <td>{{ number_format($score['percentage'], 0) }}%</td>
                                <td>{{ $score['points'] }}</td>
                                <td>{{ $score['grade'] }}</td>
                                <td>{{ $score['comments'] }}</td>
                                <td>{{ $score['teacher'] ?? '' }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2"></td>
                            <td></td>
                            <td></td>
                            <td colspan="4"> <strong>Points:</strong> {{ $reportCard['totalPoints'] }} <strong>
                                    Grade:</strong>
                                {{ $reportCard['grade'] }} @if (!empty($reportCard['student']->psle->overall_grade))
                                    <strong>PSLE Grade:</strong>
                                    {{ $reportCard['student']->psle->overall_grade ?? '' }}
                                @else
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
                @php
                    $currentTermId = session('selected_term_id', \App\Helpers\TermHelper::getCurrentTerm()->id);
                    $manualEntry = $reportCard['student']
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
                                        ___________________________
                                    @endif
                                </p>
                            </td>
                            <td style="width: 50%;">
                                <p class="remarks"><strong>School Fees Owing: </strong>
                                    @if ($school_fees !== null)
                                        BWP: {{ number_format($school_fees, 2) }}
                                    @else
                                        ___________________________
                                    @endif
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="remarks"
                    style="width: 100%; border-collapse: collapse; border: 1px solid #333; text-align: left;">
                    <tr>
                        <th style="border: 1px solid #838383; width: 50%;text-align: left;">Class Teacher's Remarks</th>
                        <th style="border: 1px solid #838383; text-align: left; width: 50%;">
                            Teacher's Name: {{ $reportCard['student']->currentClass()->teacher->lastname ?? 'N/A' }}
                        </th>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: 1px solid #838383;">
                            <div style="min-height: 20px; padding: 2px;">
                                {{ $reportCard['classTeacherRemarks'] ?? 'No remarks provided.' }}</div>
                        </td>
                    </tr>
                </table>
                <br>
                <table class="remarks"
                    style="width: 100%; border-collapse: collapse; border: 1px solid #333; text-align: left;">
                    <tr>
                        <th style="border: 1px solid #838383; width: 50%;text-align: left;">School Head's Remarks</th>
                        <th style="border: 1px solid #838383; text-align: left; width: 50%;">
                            School Head's Name: {{ $school_head->lastname ?? 'N/A' }}</th>
                    </tr>
                    <tr>
                        <td colspan="2" style="border: 1px solid #838383;">
                            <div style="min-height: 20px; padding: 2px;">
                                {{ $reportCard['headTeachersRemarks'] ?? 'No remarks provided.' }}</div>
                        </td>
                    </tr>
                </table>
                <!-- Signatures -->
                <br>
                <table class="remarks" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 33%; text-align: left; vertical-align: top;">
                            <strong>Class Teacher's signature:</strong><br>
                            @if ($classTeacherSignatureSrc)
                                <img src="{{ $classTeacherSignatureSrc }}" alt="Class Teacher's signature"
                                        style="height: 60px;">
                            @else
                                <p style="margin-top:20px;">.......................................</p>
                            @endif
                        </td>
                        <td style="width: 34%; text-align: center; vertical-align: top;">

                        </td>
                        <td style="width: 33%; text-align: left; vertical-align: top;">
                            <strong>School Head's signature:</strong><br>
                            @if ($headTeacherSignatureSrc)
                                <img src="{{ $headTeacherSignatureSrc }}" alt="Head Teacher's signature"
                                        style="height: 60px;">
                            @else
                                <p style="margin-top:20px;">...........................................</p>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="page-break"></div>
        </div>
    @endforeach
</body>

</html>
