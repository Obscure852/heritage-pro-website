<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Report Card</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        }
    </style>
</head>

<body>
    @php
        $schoolLogoSrc = \App\Support\PdfImage::toDataUri($school_setup->logo_path ?? null);
        $classTeacherSignatureSrc = \App\Support\PdfImage::toDataUri($currentClass->teacher->signature_path ?? null);
        $headTeacherSignatureSrc = \App\Support\PdfImage::toDataUri($school_head->signature_path ?? null);
    @endphp
    <div class="container">
        <div class="report-card">
            <table style="width: 100%; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <td style="width: 20%; text-align: left; vertical-align: top;">
                            <img height="200" width="200" src="{{ public_path('assets/images/coat_of_arms.jpg') }}"
                                alt="Coat of Arms" style="height: 80px; width: auto;">
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
            <!-- Personal details table -->
            <!-- Merged personal and performance details table -->
            <table class="remarks" style="width: 100%; border-collapse: collapse;">
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
                        <p style="margin: 0;"><strong>Class Average:</strong> {{ number_format($classAverage, 2) }} pts
                        </p>
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
                                    $absentDays = $student->absentDays()->where('term_id', $currentTermId)->count();
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
                    @foreach ($scores as $score)
                        <tr>
                            <td>{{ $score['subject'] }}</td>
                            <td>{{ $score['caAverage'] }}</td>
                            <td>{{ $score['score'] }}</td>
                            <td>{{ number_format($score['percentage'], 0) }}%</td>
                            <td>{{ $score['points'] }}</td>
                            <td>{{ $score['grade'] }}</td>
                            <td>{{ $score['comments'] }}</td>
                            <td>{{ $score['teacher'] }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="2"></td>
                        <td></td>
                        <td></td>
                        <td colspan="4"> <strong>Points:</strong> {{ $totalPoints }} <strong> Grade:</strong>
                            {{ $grade }} @if (!empty($student->psle->overall_grade))
                                <strong>PSLE Grade:</strong> {{ $student->psle->overall_grade ?? '' }}
                            @else
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            @php
                $currentTermId = session('selected_term_id', \App\Helpers\TermHelper::getCurrentTerm()->id);
                $manualEntry = $student->manualAttendanceEntries()->where('term_id', $currentTermId)->first();

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
                        Teacher's Name: {{ $currentClass->teacher->lastname ?? 'N/A' }}</th>
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
                    <th style="border: 1px solid #838383; width: 50%;">School Head's Remarks</th>
                    <th style="border: 1px solid #838383; text-align: left; width: 50%;">
                        School Head's Name: {{ $school_head->lastname ?? 'N/A' }}</th>
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
                        @if ($classTeacherSignatureSrc)
                            <img src="{{ $classTeacherSignatureSrc }}"
                                    alt="{{ $currentClass->teacher->full_name }}'s signature" style="height: 60px;">
                        @else
                            <p style="margin-top:10px;">.....................</p>
                        @endif
                    </td>
                    <td style="width: 34%; text-align: center; vertical-align: top;">

                    </td>
                    <td style="width: 33%; text-align: left; vertical-align: top;">
                        <strong>School Head's signature:</strong><br>
                        @if ($headTeacherSignatureSrc)
                            <img src="{{ $headTeacherSignatureSrc }}" alt="{{ $school_head->full_name }}'s signature"
                                    style="height: 60px;">
                        @else
                            <p>.....................</p>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <!-- Bootstrap JS and other scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
</body>

</html>
