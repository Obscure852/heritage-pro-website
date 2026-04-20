<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Tests</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h5>Class Statistical Analysis Report</h5>
        <div class="row">
            <div class="col-md-10">
                @if ($klasses->isNotEmpty())
                @php
                    $boys =0;
                    $girls = 0;
                @endphp
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th rowspan="2">#</th>
                                <th rowspan="2">Class</th>
                                <th rowspan="2">Class Teacher</th>
                                <th colspan="3">Totals</th>
                            </tr>
                            <tr>
                                <th>B</th>
                                <th>G</th>
                                <th>T</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($klasses as $index => $klass)
                                <tr>
                                    <td>{{ $index }}</td>
                                    <td>{{ $klass->name }}</td>
                                    <td>{{ $klass->teacher->full_name }}</td>

                                    @php
                                        $boys += $klass->boys_count;
                                        $girls += $klass->girls_count;
                                    @endphp

                                    <td>{{ $klass->boys_count }}</td>
                                    <td>{{ $klass->girls_count }}</td>
                                    <td>{{ intval($klass->boys_count) + intval($klass->girls_count) }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                {{-- <td></td>
                                <td></td> --}}
                                <td colspan="3" style="text-align: end"><strong>Totals: </strong></td>
                                <td>{{ intval($boys) }}</td>
                                <td>{{ intval($girls) }}</td>
                                <td>{{ intval($boys) + intval($girls)  }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
        <div class="row">
            <p>{{ $boys }}</p>
        </div>
        <div class="row">
            <div class="col-md-10 mb-4">
                <canvas id="classPerformanceChart" width="400" height="200"></canvas>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 mb-4">
                <canvas id="studentCountChart" width="200" height="100"></canvas>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById('classPerformanceChart').getContext('2d');
            var classPerformanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Class 1A', 'Class 1B', 'Class 2A', 'Class 2B', 'Class 3A', 'Class 3B', 'Class 4A','Class 4B'],
                    datasets: [{
                        label: 'Class Statistics',
                        data: [17, 16, 20, 19, 16, 20, 29, 16], // Fake performance data
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 60 // Assuming performance is out of 100
                        }
                    }
                }
            });

            var ctx = document.getElementById('studentCountChart').getContext('2d');
            var studentCountChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Class 1A', 'Class 1B', 'Class 2A', 'Class 2B', 'Class 3A', 'Class 3B', 'Class 4A','Class 4B'],
                    datasets: [{
                        data: [17, 16, 20, 19, 16, 20, 29, 16], // Fake total student count for each class
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 206, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                }
            });

            // Fake data for boys and girls count in each class
            var boysCounts = [7, 11, 10, 10, 11];
            var girlsCounts = [10, 5, 10, 9, 5];

            // Display the number of boys and girls in each class below the chart
            var legendDiv = document.createElement('div');
            legendDiv.style.marginTop = '20px';
            for (var i = 0; i < studentCountChart.data.labels.length; i++) {
                var classLabel = studentCountChart.data.labels[i];
                var boysCount = boysCounts[i];
                var girlsCount = girlsCounts[i];
                legendDiv.innerHTML += `<strong>${classLabel}</strong>: Boys: ${boysCount}, Girls: ${girlsCount}<br>`;
            }

            document.body.appendChild(legendDiv);
    });
</script>
</body>
</html>
