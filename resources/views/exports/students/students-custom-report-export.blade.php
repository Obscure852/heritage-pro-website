<!DOCTYPE html>
<html>
<head>
    <title>Students Custom Report</title>
</head>
<body>
    {{-- Statistics Summary --}}
    @if(isset($statistics))
    <table>
        <tr>
            <td><strong>Total Students:</strong> {{ $statistics['total_count'] }}</td>
            <td><strong>Male:</strong> {{ $statistics['male_count'] }}</td>
            <td><strong>Female:</strong> {{ $statistics['female_count'] }}</td>
        </tr>
        <tr>
            <td colspan="3">
                <strong>By Status:</strong>
                @foreach($statistics['by_status'] as $status => $count)
                    {{ $status }}: {{ $count }}{{ !$loop->last ? ' | ' : '' }}
                @endforeach
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <strong>By Type:</strong>
                @foreach($statistics['by_type'] as $type => $count)
                    {{ $type }}: {{ $count }}{{ !$loop->last ? ' | ' : '' }}
                @endforeach
            </td>
        </tr>
    </table>
    <br>
    @endif

    <table>
        <thead>
            <tr>
                <th>#</th>
                @foreach ($fields as $field)
                    <th>{{ $field_headers[$field] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @foreach ($fields as $field)
                        <td>
                            @switch($field)
                                @case('house_id')
                                    {{ optional($student->house)->name ?? '-' }}
                                @break

                                @case('sponsor_id')
                                    {{ optional($student->sponsor)->first_name ?? '' }} {{ optional($student->sponsor)->last_name ?? '-' }}
                                @break

                                @case('sponsor_phone')
                                    {{ optional($student->sponsor)->phone ?? '-' }}
                                @break

                                @case('sponsor_telephone')
                                    {{ optional($student->sponsor)->telephone ?? '-' }}
                                @break

                                @case('parent_email')
                                    {{ optional($student->sponsor)->email ?? '-' }}
                                @break

                                @case('physical_address')
                                    {{ optional(optional($student->sponsor)->otherInformation)->address ?? '-' }}
                                @break

                                @case('parent_workplace')
                                    {{ optional($student->sponsor)->work_place ?? '-' }}
                                @break

                                @case('parent_profession')
                                    {{ optional($student->sponsor)->profession ?? '-' }}
                                @break

                                @case('student_email')
                                    {{ $student->email ?? '-' }}
                                @break

                                @case('psle_overall_grade')
                                    {{ optional($student->psle)->overall_grade ?? '-' }}
                                @break

                                @case('class')
                                    {{ optional($student->class)->name ?? '-' }}
                                @break

                                @case('student_type')
                                    {{ optional($student->type)->type ?? '-' }}
                                @break

                                @case('klass_subjects')
                                    @if($student->classes->isNotEmpty())
                                        @php
                                            $currentClass = $student->classes->first();
                                            $subjects = $currentClass->subjects ?? collect();
                                        @endphp
                                        @if($subjects->isNotEmpty())
                                            {{ $subjects->map(fn($ks) => optional($ks->gradeSubject->subject)->name)->filter()->implode(', ') }}
                                        @else
                                            -
                                        @endif
                                    @else
                                        -
                                    @endif
                                @break

                                @case('optional_subjects')
                                    @if($student->optionalSubjects->isNotEmpty())
                                        {{ $student->optionalSubjects->map(fn($os) => optional($os->gradeSubject->subject)->name ?? $os->name)->filter()->implode(', ') }}
                                    @else
                                        -
                                    @endif
                                @break

                                @default
                                    {{ $student->$field ?? '-' }}
                            @endswitch
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
