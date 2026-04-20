<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senior Student Report Card</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .report-card {
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            padding: 5px;
            text-align: left;
        }

        table:not(.subject-table):not(.remarks-table) th,
        table:not(.subject-table):not(.remarks-table) td {
            border: none;
        }

        .subject-table th,
        .subject-table td {
            border: 1px solid #000;
        }

        .remarks-table th,
        .remarks-table td {
            border: 1px solid #838383;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .school-info {
            text-align: left;
        }

        .logo {
            text-align: right;
        }

        .logo img {
            height: 80px;
        }

        .comments-column {
            width: 25%;
        }

        .teacher-column {
            width: 10%;
        }

        .signatures {
            margin-top: 30px;
        }
    </style>
</head>

<body>
    @php
        $schoolLogoSrc = \App\Support\PdfImage::toDataUri($school_setup->logo_path ?? null);
        $classTeacherSignatureSrc = \App\Support\PdfImage::toDataUri($currentClass->teacher->signature_path ?? null);
        $headTeacherSignatureSrc = \App\Support\PdfImage::toDataUri($school_head->signature_path ?? null);
    @endphp
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

        <table class="header">
            <tr>
                <td><strong>Firstname:</strong> {{ $student->first_name }}</td>
                <td><strong>Lastname:</strong> {{ $student->last_name }}</td>
                <td><strong>Date:</strong> {{ now()->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td><strong>Absent Days:</strong> {{ $absentDays }}</td>
                <td><strong>Class:</strong> {{ $currentClass->name }}</td>
                <td><strong>Gender:</strong> {{ $student->gender }}</td>
            </tr>
        </table>

        <table class="header">
            <tr>
                <td><strong>Position:</strong> {{ $position }}</td>
                <td><strong>No. in Class:</strong> {{ $currentClass->students->count() }}</td>
                <td><strong>Class Average Points:</strong> {{ number_format($classAverage, 1) }}</td>
            </tr>
            <tr>
                <td style="text-align: left;">
                    <p style="margin: 0;"><strong> School Re-opens:</strong>
                        @if (isset($nextTermStartDate) && $nextTermStartDate)
                            {{ \Carbon\Carbon::parse($nextTermStartDate)->format('Y-m-d') }}
                        @else
                            N/A
                        @endif
                    </p>
                </td>
                <td style="text-align: left; padding: 2px 0;">
                    <p style="margin: 0;"><strong> Term Start:</strong>
                        @if (isset($currentClass->term) && $currentClass->term->start_date)
                            {{ \Carbon\Carbon::parse($currentClass->term->start_date)->format('Y-m-d') }}
                        @else
                            N/A
                        @endif
                    </p>
                </td>
                <td style="text-align: left; padding: 2px 0;">
                    <p style="margin: 0;"><strong> Term End:</strong>
                        @if (isset($currentClass->term) && $currentClass->term->end_date)
                            {{ \Carbon\Carbon::parse($currentClass->term->end_date)->format('Y-m-d') }}
                        @else
                            N/A
                        @endif
                    </p>
                </td>
            </tr>
        </table>

        <table class="subject-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>JCE Grade</th>
                    <th>Term Average(%)</th>
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
                        <td>
                            @if ($score['jceGrade'])
                                @if ($score['isOverallJceGrade'])
                                    <strong>{{ $score['jceGrade'] }}</strong>
                                @else
                                    {{ $score['jceGrade'] }}
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $score['caAverage'] }}</td>
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
                    <td colspan="7"></td>
                    <td colspan="2"><strong>Total Points:</strong> {{ $totalPoints }}</td>
                </tr>
            </tbody>
        </table>

        <table class="header">
            <tr>
                <td style="width: 50%;">
                    <p class="remarks"><strong>Other Information: </strong>
                        {{ $otherInfo ?? 'N/A' }}
                    </p>
                </td>
                <td style="width: 50%;">
                    <p class="remarks"><strong>School Fees Owing: </strong>
                        {{ $school_fees ? 'BWP: ' . number_format($school_fees, 2) : 'N/A' }}
                    </p>
                </td>
            </tr>
        </table>

        <table class="remarks-table">
            <tr>
                <th style="width: 50%;">Class Teacher's Remarks</th>
                <th style="text-align: left; width: 50%;">Teacher's Name:
                    {{ $currentClass->teacher->full_name ?? 'N/A' }}</th>
            </tr>
            <tr>
                <td colspan="2">
                    <div style="min-height: 20px; padding: 2px;">
                        {{ $classTeacherRemarks ?? 'No remarks provided.' }}</div>
                </td>
            </tr>
        </table>
        <br>
        <table class="remarks-table">
            <tr>
                <th style="width: 50%;">Head Teacher's Remarks</th>
                <th style="text-align: left; width: 50%;">Head Teacher's Name: {{ $school_head->full_name ?? 'N/A' }}
                </th>
            </tr>
            <tr>
                <td colspan="2">
                    <div style="min-height: 20px; padding: 2px;">
                        {{ $headTeachersRemarks ?? 'No remarks provided.' }}</div>
                </td>
            </tr>
        </table>

        <div class="signatures">
            <table class="header">
                <tr>
                    <td>
                        <strong>Class Teacher's signature:</strong><br>
                        @if ($classTeacherSignatureSrc)
                            <img src="{{ $classTeacherSignatureSrc }}" alt="Class Teacher's signature"
                                style="height: 60px;">
                        @else
                            ............................
                        @endif
                    </td>
                    <td>
                        <strong>Head Teacher's signature:</strong><br>
                        @if ($headTeacherSignatureSrc)
                            <img src="{{ $headTeacherSignatureSrc }}" alt="Head Teacher's signature"
                                style="height: 60px;">
                        @else
                            ............................
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
