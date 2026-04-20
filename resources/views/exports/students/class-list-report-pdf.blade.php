<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class List Report</title>
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
            background-color: #6b7280;
            color: white;
            font-weight: bold;
            padding: 6px 4px;
            text-align: left;
            font-size: 9px;
            border: 1px solid #6b7280;
        }

        table.data-table td {
            border: 1px solid #dee2e6;
            padding: 5px 4px;
            text-align: left;
            font-size: 9px;
            vertical-align: top;
        }

        table.data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 8px;
            color: #666;
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
        <div class="report-title">{{ $grade_name }} - {{ $list_name }}</div>
        <p>Generated: {{ now()->format('d M Y H:i') }}</p>
    </div>

    <div class="statistics">
        <table>
            <tr>
                <td><strong>Total Students:</strong> <span class="stat-number">{{ $statistics['total'] }}</span></td>
                <td><strong>Male:</strong> <span class="stat-number" style="color: #007bff;">{{ $statistics['male'] }}</span></td>
                <td><strong>Female:</strong> <span class="stat-number" style="color: #dc3545;">{{ $statistics['female'] }}</span></td>
                @if($statistics['show_boarding'] ?? false)
                    <td><strong>Boarding:</strong> <span class="stat-number" style="color: #28a745;">{{ $statistics['boarding'] }}</span></td>
                    <td><strong>Day:</strong> <span class="stat-number" style="color: #6c757d;">{{ $statistics['day'] }}</span></td>
                @endif
            </tr>
        </table>
    </div>

    @if($students->isEmpty())
        <div class="no-data">
            No students found for the selected class or optional subject.
        </div>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">#</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Gender</th>
                <th>PSLE</th>
                <th style="width:150px"></th>
                <th style="width:150px"></th>
                <th style="width:150px"></th>
                <th style="width:150px"></th>
                <th style="width:150px"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->first_name }}</td>
                    <td>{{ $student->last_name }}</td>
                    <td>{{ $student->gender }}</td>
                    <td>{{ optional($student->psle)->overall_grade ?? '-' }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
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
