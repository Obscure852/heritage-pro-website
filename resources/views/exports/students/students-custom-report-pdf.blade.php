<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Students Custom Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 16px;
            margin-bottom: 3px;
            color: #000;
        }

        .header p {
            font-size: 10px;
            color: #666;
            margin: 2px 0;
        }

        .header .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            color: #333;
        }

        .statistics {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .statistics table {
            width: 100%;
            border-collapse: collapse;
        }

        .statistics td {
            padding: 5px 10px;
            font-size: 10px;
        }

        .statistics strong {
            color: #333;
        }

        .stat-number {
            font-size: 14px;
            font-weight: bold;
            color: #007bff;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.data-table th {
            background-color: #343a40;
            color: white;
            font-weight: bold;
            padding: 6px 4px;
            text-align: left;
            font-size: 8px;
            border: 1px solid #343a40;
        }

        table.data-table td {
            border: 1px solid #dee2e6;
            padding: 4px;
            text-align: left;
            font-size: 8px;
            vertical-align: top;
        }

        table.data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table.data-table tr:hover {
            background-color: #e9ecef;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 8px;
            color: #666;
        }

        .page-break {
            page-break-after: always;
        }

        .text-muted {
            color: #6c757d;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $school_data->school_name }}</h1>
        <p>{{ $school_data->physical_address }}</p>
        <p>Tel: {{ $school_data->telephone }} | Fax: {{ $school_data->fax }}</p>
        <div class="report-title">Student Custom Report</div>
        <p>Generated: {{ now()->format('d M Y H:i') }}</p>
    </div>

    @if(isset($statistics))
    <div class="statistics">
        <table>
            <tr>
                <td style="width: 20%;"><strong>Total Students:</strong> <span class="stat-number">{{ $statistics['total_count'] }}</span></td>
                <td style="width: 15%;"><strong>Male:</strong> <span class="stat-number" style="color: #007bff;">{{ $statistics['male_count'] }}</span></td>
                <td style="width: 15%;"><strong>Female:</strong> <span class="stat-number" style="color: #dc3545;">{{ $statistics['female_count'] }}</span></td>
                <td style="width: 25%;">
                    <strong>By Status:</strong>
                    @foreach($statistics['by_status'] as $status => $count)
                        {{ $status }}: {{ $count }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </td>
                <td style="width: 25%;">
                    <strong>By Type:</strong>
                    @foreach($statistics['by_type'] as $type => $count)
                        {{ $type }}: {{ $count }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </td>
            </tr>
        </table>
    </div>
    @endif

    @if($students->isEmpty())
        <div class="no-data">
            No students found matching the selected criteria.
        </div>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">#</th>
                @foreach ($fields as $field)
                    <th>{{ $field_headers[$field] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @foreach ($fields as $field)
                        <td>
                            @switch($field)
                                @case('house_id')
                                    {{ optional($student->house)->name ?? '-' }}
                                @break

                                @case('sponsor_id')
                                    {{ optional($student->sponsor)->first_name ?? '' }} {{ optional($student->sponsor)->last_name ?? '-' }}
                                @break

                                @case('sponsor_phone')
                                    {{ optional($student->sponsor)->phone ?? '-' }}
                                @break

                                @case('sponsor_telephone')
                                    {{ optional($student->sponsor)->telephone ?? '-' }}
                                @break

                                @case('parent_email')
                                    {{ optional($student->sponsor)->email ?? '-' }}
                                @break

                                @case('physical_address')
                                    {{ optional(optional($student->sponsor)->otherInformation)->address ?? '-' }}
                                @break

                                @case('parent_workplace')
                                    {{ optional($student->sponsor)->work_place ?? '-' }}
                                @break

                                @case('parent_profession')
                                    {{ optional($student->sponsor)->profession ?? '-' }}
                                @break

                                @case('student_email')
                                    {{ $student->email ?? '-' }}
                                @break

                                @case('psle_overall_grade')
                                    {{ optional($student->psle)->overall_grade ?? '-' }}
                                @break

                                @case('class')
                                    {{ optional($student->class)->name ?? '-' }}
                                @break

                                @case('student_type')
                                    {{ optional($student->type)->type ?? '-' }}
                                @break

                                @case('klass_subjects')
                                    @if($student->classes->isNotEmpty())
                                        @php
                                            $currentClass = $student->classes->first();
                                            $subjects = $currentClass->subjects ?? collect();
                                        @endphp
                                        @if($subjects->isNotEmpty())
                                            {{ $subjects->map(fn($ks) => optional($ks->gradeSubject->subject)->name)->filter()->implode(', ') }}
                                        @else
                                            -
                                        @endif
                                    @else
                                        -
                                    @endif
                                @break

                                @case('optional_subjects')
                                    @if($student->optionalSubjects->isNotEmpty())
                                        {{ $student->optionalSubjects->map(fn($os) => optional($os->gradeSubject->subject)->name ?? $os->name)->filter()->implode(', ') }}
                                    @else
                                        -
                                    @endif
                                @break

                                @default
                                    {{ $student->$field ?? '-' }}
                            @endswitch
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <p>{{ $school_data->school_name }} - School Management System | Report generated on {{ now()->format('d M Y \a\t H:i') }}</p>
    </div>
</body>
</html>
