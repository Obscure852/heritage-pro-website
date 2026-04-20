<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Region Result Analysis</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <p>Term {{ $termId ?: 0 }} {{ '|' }}{{ $year ?: 0 }} Term Analysis by Grade</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @foreach ($grades as $index => $grade)
                    <a class="grade-link" style="text-decoration: none;color:black;" data-grade="{{ $grade->id }}" data-year="{{ $year }}" data-term="{{ $termId }}" href="">{{ $grade->name }}</a>
                    {{ $index != count($grades) - 1 ? '|' : '' }}
                @endforeach
            </div>
        </div>               
        <div class="row">
            <div id="region-analysis-by-grade" class="col-md-12 mt-4">
            </div>
        </div>
    </div>
    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
                
                document.addEventListener("DOMContentLoaded", function() {
                var gradeLinks = document.querySelectorAll('.grade-link');

                gradeLinks.forEach(function(link) {
                    link.addEventListener('click', function(event) {
                        event.preventDefault();

                        var gradeId = event.target.getAttribute('data-grade');
                        var termId = event.target.getAttribute('data-term');
                        var year = event.target.getAttribute('data-year');

                        updateResultAnalysis(gradeId, termId, year);
                     });
                  });
                });

        function updateResultAnalysis(gradeId, termId, year) {
            // alert(gradeId +' '+ termId +' '+ year);
            var url = '/assessment/analysis/region/' + gradeId + '/' + termId + '/' + year;

            fetch(url)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('region-analysis-by-grade').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('region-analysis-by-grade').innerHTML = "Error: No classes for the year and term selected.";
                });
        }

    </script>
</body>
</html>
