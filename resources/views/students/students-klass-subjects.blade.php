
<table class="table table-bordered">
    <thead>
        <tr>
            <td>#</td>
            <td>Class</td>
            <td>Subject</td>
            <td>Teacher</td>
            <td>Grade</td>
            <td>Venue/Classroom</td>
        </tr>
    </thead>
    <tbody>
        @if ($klass_subjects->isNotEmpty())
        @foreach ($klass_subjects as $index => $klass_subject)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ optional($klass_subject->klass)->name }}</td> 
                <td>{{ optional($klass_subject->subject)->name }}</td>
                <td>{{ optional($klass_subject->teacher)->full_name }}</td>
                <td>{{ optional($klass_subject->grade)->name }}</td>
                <td>{{ optional($klass_subject->venue)->name }}</td>
            </tr>
        @endforeach
        @endif
    </tbody>
</table>
