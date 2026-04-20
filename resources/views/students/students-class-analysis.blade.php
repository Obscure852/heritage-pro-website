<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Tests</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>

        table {
            width: 80%; /* Adjust as needed */
            border-collapse: collapse;
            margin: 0 auto; /* Center the table horizontally */
        }

        table td, table th {
            width: 1%; /* Distributes width equally among cells */
            white-space: nowrap; /* Prevents content from wrapping to the next line */
            border: 1px solid #ccc; /* Optional: Adds a border to each cell */
            padding: 8px; /* Optional: Adds some padding inside each cell */
        }

    </style>
    
</head>
<body>
    <div class="row">
        <div class="col-md-9">
        </div>
        <div class="col-md-3 d-flex justify-content-end">
            <box-icon id="printButton" style="cursor: pointer;" class="my-2 text-muted" size="sm" color="gray" name='printer'></box-icon>
            <box-icon style="cursor: pointer;"  class="my-2 text-muted" size="sm" color="gray" name='file-pdf' type='solid'></box-icon>
            <box-icon onclick="openStudentsExport(); return false;" style="cursor: pointer;"  class="my-2 text-muted" size="sm" color="gray"  name='sync' type='solid'></box-icon>
        </div>
    </div>


    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 d-flex justify-content-start">
                <p>
                    @foreach ($grades as $grade)
                    <a data-term="{{ $klass_subjects->pluck('term_id')->first() }}" data-year="{{ $klass_subjects->pluck('year')->first() }}" data-grade="{{ $grade->id }}" style="margin-left:5px;text-decoration:none;" onclick="openStudentKlassSubjects(this); return false;" href="#">{{ $grade->name  }}</a>
                    @endforeach
                </p>
            </div>
        </div>
        <div class="row">
            <div id="klass_subject" class="col-md-10">
            </div>
        </div>
    </div>
    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openStudentsExport(){
            try { 
                const url = `students/analysis/export/`;
                alert(2);
                window.open(url, 'PopupWindow', 'height=600,width=1000');
            } catch (error){
                console.error("An error occurred while opening the popup:", error);
            }
        }

        function openStudentKlassSubjects(anchor) {
        var dataAttributes = {
            term: anchor.dataset.term,
            year: anchor.dataset.year,
            grade: anchor.dataset.grade
        };

        const sanitizedTermId = encodeURIComponent(dataAttributes['term']);
        const sanitizedYear = encodeURIComponent(dataAttributes['year']);
        const sanitizedGradeId = encodeURIComponent(dataAttributes['grade']);

        const url = `/students/analysis/classes/lists/${sanitizedTermId}/${sanitizedYear}/${sanitizedGradeId}`;
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    document.getElementById('klass_subject').innerHTML = '';
                   // return response.json().then(err => { throw err; });
                }
                return response.text();
            })
            .then(data => {
                document.getElementById('klass_subject').innerHTML = data;
            })
            .catch(error => {
                console.error('Error fetching data:', error.error);
                
                // Display the error message to the user
                alert(error.error);  // Using an alert for simplicity, but you can use a more user-friendly method
            });
}



    </script>
</body>
</html>
