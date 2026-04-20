<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Progress Report - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        @page {
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 15mm 12mm;
        }

        .container {
            width: 100%;
            max-width: 100%;
            padding: 0;
            margin: 0 auto;
        }

        /* Header Section */
        .header {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 10px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .school-info {
            vertical-align: top;
        }

        .school-name {
            font-size: 16px;
            font-weight: bold;
            color: #4e73df;
            margin-bottom: 4px;
        }

        .school-address {
            font-size: 10px;
            color: #666;
            line-height: 1.4;
        }

        .logo-cell {
            text-align: right;
            vertical-align: top;
        }

        .logo {
            height: 60px;
        }

        /* Title Section */
        .report-title {
            text-align: center;
            margin: 15px 0;
            padding: 8px;
            background: #4e73df;
            color: white;
            font-size: 14px;
            font-weight: bold;
            border-radius: 3px;
        }

        /* Student Info Section */
        .student-info {
            width: 100%;
            margin-bottom: 15px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 3px;
        }

        .student-info-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .student-info-table td {
            padding: 4px 8px;
            font-size: 11px;
        }

        .info-label {
            font-weight: bold;
            color: #4e73df;
            width: 110px;
        }

        .info-value {
            color: #333;
        }

        /* Term Section */
        .term-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .term-header {
            background: #4e73df;
            color: white;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 3px 3px 0 0;
        }

        /* Results Table */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            table-layout: fixed;
        }

        .results-table th {
            background: #e9ecef;
            color: #333;
            padding: 5px 6px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
            border: 1px solid #dee2e6;
        }

        .results-table td {
            padding: 4px 6px;
            border: 1px solid #dee2e6;
            font-size: 10px;
            word-wrap: break-word;
            overflow: hidden;
        }

        .results-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .text-center {
            text-align: center;
        }

        /* Grade Badges */
        .grade-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
            color: white;
        }

        .grade-a {
            background: #28a745;
        }

        .grade-b {
            background: #4e73df;
        }

        .grade-c {
            background: #17a2b8;
        }

        .grade-d {
            background: #ffc107;
            color: #333;
        }

        .grade-e,
        .grade-f,
        .grade-u {
            background: #dc3545;
        }

        .grade-merit {
            background: #6f42c1;
        }

        /* Term Summary */
        .term-summary {
            background: #e8f4f8;
            padding: 8px 10px;
            border-radius: 0 0 3px 3px;
            border: 1px solid #dee2e6;
            border-top: none;
        }

        .term-summary-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .term-summary-table td {
            padding: 2px 8px;
            font-size: 10px;
        }

        .summary-label {
            font-weight: bold;
            color: #4e73df;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            font-size: 9px;
            color: #666;
            text-align: center;
        }

        .print-date {
            float: right;
        }

        /* Page Break */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="school-info">
                        <div class="school-name">{{ $school_data->school_name ?? 'School Name' }}</div>
                        <div class="school-address">
                            {{ $school_data->physical_address ?? '' }}<br>
                            {{ $school_data->postal_address ?? '' }}<br>
                            Tel: {{ $school_data->telephone ?? '' }}
                            @if ($school_data->fax)
                                | Fax: {{ $school_data->fax }}
                            @endif
                        </div>
                    </td>
                    <td class="logo-cell">
                        @if ($school_data->logo_path)
                            <img src="{{ public_path('storage/' . $school_data->logo_path) }}" alt="School Logo"
                                class="logo">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Report Title -->
        <div class="report-title">
            STUDENT ACADEMIC PROGRESS REPORT
        </div>

        <!-- Student Information -->
        <div class="student-info">
            <table class="student-info-table">
                <tr>
                    <td class="info-label">Student Name:</td>
                    <td class="info-value">{{ $student->first_name }} {{ $student->middle_name }}
                        {{ $student->last_name }}</td>
                    <td class="info-label">Student ID:</td>
                    <td class="info-value">{{ $student->id_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Gender:</td>
                    <td class="info-value">{{ $student->gender == 'M' ? 'Male' : 'Female' }}</td>
                    <td class="info-label">Date of Birth:</td>
                    <td class="info-value">
                        {{ $student->formatted_date_of_birth ?: 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td class="info-label">Current Class:</td>
                    <td class="info-value">{{ $currentClass->name ?? 'N/A' }}</td>
                    <td class="info-label">Report Date:</td>
                    <td class="info-value">{{ now()->format('d M Y') }}</td>
                </tr>
                @if($schoolType === 'Junior' && $student->psle && $student->psle->overall_grade)
                <tr>
                    <td class="info-label">PSLE Grade:</td>
                    <td class="info-value">{{ $student->psle->overall_grade }}</td>
                    <td></td>
                    <td></td>
                </tr>
                @elseif($schoolType === 'Senior' && $student->jce && $student->jce->overall)
                <tr>
                    <td class="info-label">JCE Grade:</td>
                    <td class="info-value">{{ $student->jce->overall }}</td>
                    <td></td>
                    <td></td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Exam Results by Term -->
        @foreach ($termData as $termId => $data)
            @php
                $termExams = $data['exams'] ?? collect([]);
                $totalPoints = $data['totalPoints'] ?? 0;
                $overallGrade = $data['overallGrade'] ?? null;
                $firstExam = $termExams->first();
                $termNumber = ($firstExam && $firstExam->term) ? $firstExam->term->term : '';
                $termYear = $firstExam ? ($firstExam->year ?? '') : '';
                $subjectCount = $termExams->count();
            @endphp

            @if($termExams->isNotEmpty())
            <div class="term-section">
                <div class="term-header">
                    Term {{ $termNumber }}, {{ $termYear }} - Academic Performance
                </div>

                <table class="results-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 20%;">Subject</th>
                            <th class="text-center" style="width: 10%;">Percentage</th>
                            <th class="text-center" style="width: 8%;">Points</th>
                            <th class="text-center" style="width: 8%;">Grade</th>
                            <th style="width: 49%;">Teacher's Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($termExams as $index => $test)
                            @php
                                $comment = $student->getSubjectComment($termId, $test->grade_subject_id ?? 0)->first();
                                $grade = $test->pivot->grade ?? null;
                                $gradeClass = match (strtoupper($grade ?? '')) {
                                    'A' => 'grade-a',
                                    'B' => 'grade-b',
                                    'C' => 'grade-c',
                                    'D' => 'grade-d',
                                    default => 'grade-e',
                                };
                                $subjectName = ($test->subject && $test->subject->subject) ? $test->subject->subject->name : 'Unknown Subject';
                            @endphp
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $subjectName }}</td>
                                <td class="text-center">{{ $test->pivot->percentage ?? 0 }}%</td>
                                <td class="text-center">{{ $test->pivot->points ?? 0 }}</td>
                                <td class="text-center">
                                    <span class="grade-badge {{ $gradeClass }}">{{ $grade ?? '-' }}</span>
                                </td>
                                <td>{{ $comment->remarks ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="term-summary">
                    <table class="term-summary-table">
                        <tr>
                            <td class="summary-label">Subjects Taken:</td>
                            <td>{{ $subjectCount }}</td>
                            <td class="summary-label">Total Points:</td>
                            <td>{{ $totalPoints }}</td>
                            <td class="summary-label">Overall Grade:</td>
                            <td>
                                @php
                                    $gradeClass = match (strtoupper($overallGrade ?? '')) {
                                        'MERIT' => 'grade-merit',
                                        'A' => 'grade-a',
                                        'B' => 'grade-b',
                                        'C' => 'grade-c',
                                        'D' => 'grade-d',
                                        default => 'grade-e',
                                    };
                                @endphp
                                <span class="grade-badge {{ $gradeClass }}">{{ $overallGrade ?? '-' }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif
        @endforeach

        <!-- Footer -->
        <div class="footer">
            <span>This is a computer-generated document. No signature required.</span>
            <span class="print-date">Printed on: {{ now()->format('d M Y, H:i') }}</span>
        </div>
    </div>
</body>

</html>
