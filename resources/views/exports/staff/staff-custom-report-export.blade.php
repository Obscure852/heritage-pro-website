<!DOCTYPE html>
<html>

<head>
    <title>Staff Custom Report</title>
</head>

<body>
    <table>
        <thead>
            <tr>
                @foreach ($fields as $field)
                    <th>{{ $field_headers[$field] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    @foreach ($fields as $field)
                        <td>
                            @if ($field == 'roles')
                                {{ $user->roles->pluck('name')->join(', ') }}
                            @elseif ($field == 'klasses')
                                @foreach ($user->klasses as $klass)
                                    <span>{{ $klass->klass->name ?? '' }}
                                        {{ $klass->subject->subject->name ?? '' }}</span>
                                    <br>
                                @endforeach
                            @elseif ($field == 'klassSubjects')
                                {{ $user->klassSubjects->pluck('name')->join(', ') }}
                            @elseif ($field == 'qualifications')
                                @foreach ($user->qualifications as $qualification)
                                    <span>
                                        {{ app\models\Qualification::find($qualification->qualification_id)->qualification ?? '' }}
                                        {{ $qualification->level }} from
                                        {{ $qualification->college }}</span>
                                @endforeach
                            @else
                                {{ $user->$field }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
