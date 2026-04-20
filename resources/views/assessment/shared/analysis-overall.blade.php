<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $klass->name .' Overall performance report' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h5>{{ $school_setup->school_name }}</h5>
                <p>Class Overall Performance Report</p>
                <p><strong>{{ 'Term '.$klass->term->term .','. $klass->term->year }}</strong></p>
                <p><strong>Class: {{ $klass->name }} Teacher: {{ $klass->teacher->full_name }}</strong></p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <thead>
                        <th>Overall</th>
                        <th>A</th>
                        <th>%</th>
                        <th>B</th>
                        <th>%</th>
                        <th>C</th>
                        <th>%</th>
                        <th>D</th>
                        <th>%</th>
                        <th>E</th>
                        <th>%</th>
                        <th>AB</th>
                        <th>%</th>
                        <th>ABC</th>
                        <th>%</th>
                        <th>DE</th>
                        <th>%</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Totals</td>
                            <td>{{ $grade_counts['A'] }}</td>
                            <td></td>
                            <td>{{ $grade_counts['B'] }}</td>
                            <td></td>
                            <td>{{ $grade_counts['C'] }}</td>
                            <td></td>
                            <td>{{ $grade_counts['D'] }}</td>
                            <td></td>
                            <td>{{ $grade_counts['E'] }}</td>
                            <td></td>
                            <td>
                                @php
                                    $sumAB = intval($grade_counts['A']) + intval($grade_counts['B']); 
                                    echo $sumAB;
                                @endphp
                            </td>
                            <td>
                                @php
                                    $sumAB = intval($grade_counts['A']) + intval($grade_counts['B']); 
                                    $total = $klass->students->count();
                                    echo $total > 0  ? number_format($sumAB/$total * 100,0).'%' : 0;
                                @endphp
                            </td>
                            <td>
                                @php
                                    $sumABC = intval($grade_counts['A']) + intval($grade_counts['B']) + intval($grade_counts['C']); 
                                    echo $sumABC;
                                @endphp
                            </td>
                            <td>
                                @php
                                    $sumABC = intval($grade_counts['A']) + intval($grade_counts['B']) + intval($grade_counts['C']); 
                                    $total = $klass->students->count();
                                    echo $total > 0 ? number_format($sumABC/$total * 100,0).'%' : 0;
                                @endphp
                            </td>
                            <td>
                                @php
                                    $sumDE = intval($grade_counts['D']) + intval($grade_counts['E']); 
                                    echo $sumDE;
                                @endphp
                            </td>
                            <td>
                                @php
                                    $sumDE = intval($grade_counts['D']) + intval($grade_counts['E']); 
                                    $total = $klass->students->count();
                                    echo $total > 0 ? number_format($sumDE/$total * 100,0).'%' : 0;
                                 @endphp
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
