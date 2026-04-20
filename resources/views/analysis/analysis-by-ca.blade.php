<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CA Assessment reports</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h5>Subjects Assessment Analysis</h5>
                @if ($klass_subjects->isNotEmpty())
                    @foreach ($klass_subjects  as $item)
                        <p>{{ $item->subject->name }}</p>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
