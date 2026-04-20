@extends('layouts.master')
@section('title') Markbook @endsection
@section('content')
    <div class="row mt-4">
        <div id="studentTestList" class="col-md-8">
            @if(!empty($klass))
             <p>{{ $klass->klass->name.' / '.$klass->subject->name.' / '.$klass->teacher->firstname .'  '.$klass->teacher->lastname .' / '.$klass->klass->students->count() }}</p>
            <table class="table table-stripped rounded table-sm datatable dt-responsive nowrap" style="border-collapse: collapse;">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Firstname</th>
                        <th scope="col">Lastname</th>
                        <th scope="col">Class</th>
                        @foreach ($klass->subject->tests as $test)
                            <th scope="col">{{ $test->name.'('. $test->out_of .')' }}</th>
                            <th>%</th>
                            <th></th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($klass->klass->students as $student)
                        <tr>
                            <td>{{ $student->id }} </td>
                            <td>{{ $student->first_name  }}</td>
                            <td>{{ $student->last_name  }}</td>
                            <td>{{ $student->class->name  }}</td>
                            @php
                                $subjects = $klass->subject->withTermId($klass->term_id)->get();
                            @endphp
                            @if ($klass->subject->tests->isNotEmpty())
                            @foreach ($klass->subject->tests as $test)
                            @php
                                $studentTest = \App\Models\StudentTest::where('student_id', $student->id)->where('test_id', $test->id)->first();
                                $score = $studentTest ? $studentTest->score : '';
                                $grade = $studentTest ? $studentTest->grade : '';
                                $percentage = $studentTest ? $studentTest->percentage : '';
                            @endphp
                            <td>
                                <input type="hidden" name="students[{{ $student->id }}][tests][{{ $test->id }}][out_of]" class="form-control form-control-sm" value="{{ $test->out_of }}">
                                <input type="hidden" name="students[{{ $student->id }}][tests][{{ $test->id }}][score]" class="form-control form-control-sm" value="{{ $student->id }}">
                                <input type="text" class="form-control form-control-sm" name="students[{{ $student->id }}][tests][{{ $test->id }}][score]" value="{{ $score }}" style="width: 40px;">
                            </td>
                            <td>{{ $percentage }}</td>
                        <td>{{ $grade }}</td>
                        @endforeach
                            @else
                                <td>No Test Created yet!</td>    
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
@endsection
