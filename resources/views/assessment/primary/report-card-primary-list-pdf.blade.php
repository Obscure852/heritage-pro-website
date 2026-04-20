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
            font-size: 14px;
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
    @foreach ($allStudentData as $data)
        <div class="container" style="page-break-after: always;">
            <table style="width: 100%; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <td style="width: 50%; vertical-align: top;">
                            <strong>{{ $data['school_setup']->school_name }}</strong><br>
                            <span>{{ $data['school_setup']->physical_address }}</span><br>
                            <span>{{ $data['school_setup']->postal_address }}</span><br>
                            <span>Tel: {{ $data['school_setup']->telephone }} Fax:
                                {{ $data['school_setup']->fax }}</span>
                        </td>
                        <td style="width: 50%; text-align: right; vertical-align: top;">
                            @if (!empty($data['schoolLogoPath']))
                                <img src="{{ $data['schoolLogoPath'] }}" alt="School Logo" style="height: 80px;">
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>


            <table class="remarks" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 33%;text-align:left;">
                        <p><strong>Firstname:</strong> {{ $data['student']->first_name }}</p>
                    </td>
                    <td style="width: 33%;text-align:left;">
                        <p><strong>Lastname:</strong> {{ $data['student']->last_name }}</p>
                    </td>
                    <td style="width: 33%;text-align:left;">
                        <p><strong>Date:</strong> {{ now()->format('Y-m-d') }}</p>
                    </td>
                </tr>
                <tr>
                    <td style="text-align:left;">
                        <p><strong>Absent Days:</strong> {{ $data['absentDays'] }}</p>
                    </td>
                    <td style="text-align:left;">
                        <p><strong>Class:</strong> {{ $data['currentClass']->name }}</p>
                    </td>
                    <td style="text-align:left;">
                        <p><strong>Gender:</strong> {{ $data['student']->gender }}</p>
                    </td>
                </tr>
            </table>
            <table class="remarks" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 33%;text-align:left;">
                        <p><strong>Position:</strong> {{ $data['rank'] ?? '' }}</p>
                    </td>
                    <td style="width: 33%;text-align:left;">
                        <p><strong>No. in Class:</strong> {{ $data['classSize'] ?? '' }}</p>
                    </td>
                    <td style="width: 33%; text-align: left;">
                        <p><strong>Class Average:</strong> {{ number_format($data['classAverage'] ?? 0, 2) }}%</p>
                    </td>
                </tr>
                <tr>
                    <td style="text-align:left">
                        <p><strong>School Re-opens:</strong> {{ $data['nextTermStartDate'] ?? 'N/A' }}</p>
                    </td>
                    <td style="text-align:left;">
                        <p><strong>Term Start:</strong> {{ $data['termStart'] ?? '' }}</p>
                    </td>
                    <td style="text-align:left;">
                        <p><strong>Term End:</strong> {{ $data['termEnd'] ?? '' }}</p>
                    </td>
                </tr>
            </table>

            <table class="table table-bordered">
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
                    @foreach ($data['scores'] as $score)
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
                        <td>{{ $data['totalScore'] }}</td>
                        <td>{{ number_format($data['averagePercentage'], 1) }}%</td>
                        <td colspan="2">{{ $data['overallGrade']->grade ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
            <table class="table table-borderless">
                <tbody>
                    <tr>
                        <td style="width: 50%;">
                            <p class="remarks"><strong>Other Information: </strong>
                                @if (!empty($data['otherInfo']))
                                    {{ $data['otherInfo'] }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </td>
                        <td style="width: 50%;">
                            <p class="remarks"><strong>School Fees Owing: </strong>
                                @if ($data['schoolFees'] !== null)
                                    BWP: {{ number_format($data['schoolFees'], 2) }}
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
                        Teacher's Name: {{ $data['teacherName'] ?? 'N/A' }}</th>
                </tr>
                <tr>
                    <td colspan="2" style="border: 1px solid #838383;">
                        <div style="min-height: 20px; padding: 2px;">
                            {{ $data['classTeacherRemarks'] ?? 'No remarks provided.' }}</div>
                    </td>
                </tr>
            </table>
            <br>
            <table class="remarks"
                style="width: 100%; border-collapse: collapse; border: 1px solid #333; text-align: left;">
                <tr>
                    <th style="border: 1px solid #838383; width: 50%;">Head Teacher's Remarks</th>
                    <th style="border: 1px solid #838383; text-align: left; width: 50%;">
                        Head Teacher's Name: {{ $data['schoolHeadName'] ?? 'N/A' }}</th>
                </tr>
                <tr>
                    <td colspan="2" style="border: 1px solid #838383;">
                        <div style="min-height: 20px; padding: 2px;">
                            {{ $data['headTeachersRemarks'] ?? 'No remarks provided.' }}</div>
                    </td>
                </tr>
            </table>
            <!-- Signatures -->
            <br>
            <table class="remarks" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 33%; text-align: left; vertical-align: top;">
                        <strong>Class Teacher's signature:</strong><br>
                        @if (!empty($data['teacherSignaturePath']))
                            <img src="{{ $data['teacherSignaturePath'] }}"
                                alt="{{ $data['teacherName'] }}'s signature" style="height: 60px;">
                        @else
                            <p style="margin-top:10px;">.....................</p>
                        @endif
                    </td>
                    <td style="width: 34%; text-align: center; vertical-align: top;">

                    </td>
                    <td style="width: 33%; text-align: left; vertical-align: top;">
                        <strong>Head Teacher's signature:</strong><br>
                        @if (!empty($data['schoolHeadSignaturePath']))
                            <img src="{{ $data['schoolHeadSignaturePath'] }}"
                                alt="{{ $data['schoolHeadName'] ?? '' }}'s signature" style="height: 60px;">
                        @else
                            <p>.....................</p>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endforeach
</body>

</html>
