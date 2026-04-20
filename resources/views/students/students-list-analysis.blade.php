<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Tests</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
           
        }

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

            {{ $students }}
            {{-- <h5>Students List Report</h5>
            <div class="col-md-10">
                @if ($students->isNotEmpty())
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Firstname</th>
                                <th>Lastname</th>
                                <th>Gender</th>
                                <th>Class</th>
                                <th>Grade</th>
                                <th>Date of Birth</th>
                                <th>ID Number</th>
                                <th>Nationality</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $index => $student)
                                <tr>
                                    <td>{{ $index }}</td>
                                    <td>{{ $student->first_name }}</td>
                                    <td>{{ $student->last_name }}</td>
                                    <td>{{ $student->gender }}</td>
                                    <td>{{ $student->class->name ?? '' }}</td>
                                    <td>{{ $student->grade->name ?? '' }}</td>
                                    <td>{{ $student->formatted_date_of_birth }}</td>
                                    <td>{{ $student->id_number }}</td>
                                    <td>{{ $student->nationality }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                   <div class="row">
                    <div class="col-md-12 d-flex justify-content-center">
                        {{ $students->links() }}
                    </div>
                   </div>
                @endif
            </div> --}}
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
    </script>
</body>
</html>
