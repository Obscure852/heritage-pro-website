<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Report Card</title>
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
            font-size: 14px;
            /* Ensure borders are visible */
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .remarks {
            font-size: 14px;
        }

        .table-borderless td,
        .table-borderless th {
            border: 0;
            padding: 4px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="report-card">
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
                            @if (!empty($schoolLogoPath))
                                <img src="{{ $schoolLogoPath }}" alt="School Logo" style="height: 80px;">
                            @endif
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
                    <td style="width: 33%;text-align:left;">
                        <p><strong>Class Average:</strong> {{ round($classAverage, 0) ?? '' }}%</p>
                    </td>
                </tr>
                <tr>
                    <td style="text-align:left">
                        <p><strong>School Re-opens:</strong> {{ $nextTermStartDate ?? 'N/A' }}</p>
                    </td>
                    <td style="text-align:left;">
                        <p><strong>Term Start:</strong> {{ $termStart ?? '' }}</p>
                    </td>
                    <td style="text-align:left;">
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
                        <td>{{ number_format($averagePercentage, 1) }}%</td>
                        <td colspan="2">{{ $overallGrade->grade ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="table table-borderless">
                <tbody>
                    <tr>
                        <td style="width: 50%;">
                            <p class="remarks"><strong>Other Information: </strong>
                                @if (!empty($otherInfo))
                                    {{ $otherInfo }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </td>
                        <td style="width: 50%;">
                            <p class="remarks"><strong>School Fees Owing: </strong>
                                @if ($schoolFees !== null)
                                    BWP: {{ number_format($schoolFees, 2) }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </td>

                    </tr>
                </tbody>
            </table>
            <table class="table table-sm table-bordered remarks"
                style="width: 100%; border-collapse: collapse; border: 1px solid #333; text-align: left;">
                <tr>
                    <th style="border: 1px solid #838383; width: 50%;">Class Teacher's Remarks</th>
                    <th style="border: 1px solid #838383; text-align: left; width: 50%;">
                        Teacher's Name: {{ $teacherName ?? 'N/A' }}</th>
                </tr>
                <tr>
                    <td colspan="2" style="border: 1px solid #838383;">
                        <div style="min-height: 20px; padding: 2px;">
                            {{ $classTeacherRemarks ?? 'No remarks provided.' }}</div>
                    </td>
                </tr>
            </table>
            <br>
            <table class="table table-sm table-bordered remarks"
                style="width: 100%; border-collapse: collapse; border: 1px solid #333; text-align: left;">
                <tr>
                    <th style="border: 1px solid #838383; width: 50%;">Head Teacher's Remarks</th>
                    <th style="border: 1px solid #838383; text-align: left; width: 50%;">
                        Head Teacher's Name: {{ $schoolHeadName ?? 'N/A' }}</th>
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
                        @if (!empty($teacherSignaturePath))
                            <img src="{{ $teacherSignaturePath }}"
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
                            <img src="{{ $schoolHeadSignaturePath }}"
                                alt="{{ $schoolHeadName }}'s signature" style="height: 60px;">
                        @else
                            <p>.....................</p>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
