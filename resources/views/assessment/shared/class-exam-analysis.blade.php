<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exam Analysis Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body, html { font-family: 'Helvetica', 'Arial', sans-serif; }
        .table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $school_setup->school_name }} - Exam Analysis Report</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Sex</th>
                    @foreach($allSubjects as $subject)
                        <th>{{ substr($subject, 0, 3) }}</th>
                    @endforeach
                    <th>TP</th>
                    <th>Grade</th>
                    <th>Position</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reportCards as $reportCard)
                    <tr>
                        <td>{{ $reportCard['student']->fullName ?? '' }}</td>
                        <td>{{ $reportCard['class_name'] ?? '' }}</td>
                        <td>{{ $reportCard['student']->gender ?? '' }}</td>
                       
                        @foreach ($allSubjects as $subject)
                            <td>{{ $reportCard['scores'][$subject] ?? 0 }} </td>
                        @endforeach
                        
                        <td>{{ $reportCard['totalPoints'] ?? '' }}</td>
                        <td>{{ $reportCard['grade']  ?? ''}}</td>
                        <td>{{ $reportCard['position'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
