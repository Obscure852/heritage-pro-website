<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Tests</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <div class="container mt-5">
        @if (!empty($klass_subject))
            <div class="row">
               <div class="col-md-6">
                  <h5>{{ $klass_subject->subject->name }}</h5>
               </div>
               <div class="col-md-6 d-flex justify-content-end">
                 <small>Print</small>
               </div>
            </div>
            <h6>Teacher : {{ $klass_subject->teacher->full_name }}</h6>
            <p>Class: ({{ $klass_subject->klass->name }}) - Total Students ({{ $klass_subject->klass->students->count() }}) </p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Student</th>
                        @foreach ($klass_subject->subject->tests as $test)
                            <th colspan="2">{{ $test->name }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th></th>
                        @foreach ($klass_subject->subject->tests as $test)
                            <th>Score</th>
                            <th>Grade</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($klass_subject->klass->students as $index => $student)
                        <tr>
                            <td>{{ $student->full_name }}</td>
                            @foreach ($klass_subject->subject->tests as $test)
                                @php
                                    $studentTest = $student->tests->where('pivot.test_id', $test->id)->first();
                                @endphp
                                <td>{{ $studentTest->pivot->score ?? 'N/A' }}</td>
                                <td>{{ $studentTest->pivot->grade ?? 'N/A' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
