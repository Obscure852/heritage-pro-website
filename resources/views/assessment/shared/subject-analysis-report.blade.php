@extends('layouts.master')
@section('title')
    Subject Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ $markbookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            Analysis
        @endslot
    @endcomponent
    <style>
        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        }

        .report-card {
            margin-top: 0mm;
            margin-bottom: 20mm;
        }

        textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #333;
            padding: 5px;
            margin: 10px 0;
        }

        @media print {

            body {
                width: 100%;
                margin: 0;
                padding: 0;
                line-height: normal;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            .printable {
                position: absolute;
                left: 50%;
                top: 0;
                transform: translateX(-50%);
                width: 350mm;
                height: 297mm;
                margin-left: 250px;
                margin-top: 80px;
                padding: 0;
                page-break-after: avoid;
            }


            .card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0 10mm;
            }

            .card-header img {
                width: 300px;
                height: 120px;
            }

            .table {
                width: 100%;
                table-layout: fixed;
            }

            .table th,
            .table td {
                width: auto;
                overflow: visible;
                word-wrap: break-word;
            }

            textarea {
                border: none;
            }

            .card {
                box-shadow: none;
            }
        }
    </style>
    <div class="row">
        <div class="col-12 d-flex justify-content-end">
            <i onclick="alert(0)" style="font-size: 20px;margin-bottom:10px;cursor:pointer;margin-right:5px;"
                class="bx bx-sync"></i>
            <i onclick="printContent()" style="font-size: 20px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer"></i>
        </div>
    </div>

    {{-- Print from here to the bottom only --}}
    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 align-items-start">
                            <div class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span style="maring:0;padding:0;"> {{ $school_data->physical_address }}</span>
                                <br>
                                <span style="maring:0;padding:0;"> {{ $school_data->postal_address }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Nav tabs -->
                    <h5>{{ $klass_subject->subject->subject->name }} Grades Analysis </h5>
                    <span><strong>Teacher: </strong>{{ $klass_subject->teacher->fullName ?? '' }}</span>
                    <ul class="nav nav-tabs" role="tablist">
                        @foreach ($tests as $i => $test)
                            <li class="nav-item">
                                <a class="nav-link @if ($i === 0) active @endif"
                                    id="test-tab-{{ $test->id }}" data-bs-toggle="tab" href="#test{{ $test->id }}"
                                    role="tab" aria-controls="test{{ $test->id }}"
                                    aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                                    {{ $test->type === 'Exam' ? 'Exam (End Of Term)' : $test->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content p-3 text-muted">
                        @foreach ($tests as $i => $test)
                            <div class="tab-pane fade @if ($i === 0) show active @endif"
                                id="test{{ $test->id }}" role="tabpanel"
                                aria-labelledby="test-tab-{{ $test->id }}">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>

                                            <th colspan="2"><strong>A</strong></th>
                                            <th colspan="2"><strong>B</strong></th>
                                            <th colspan="2"><strong>C</strong></th>
                                            <th colspan="2"><strong>D</strong></th>
                                            <th><strong>ABC%</strong></th>
                                            <th><strong>ABCD%</strong></th>
                                        </tr>
                                        <tr>
                                            <th>M</th>
                                            <th>F</th>
                                            <th>M</th>
                                            <th>F</th>
                                            <th>M</th>
                                            <th>F</th>
                                            <th>M</th>
                                            <th>F</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $gradeDistributions[$test->id]['A']['M'] ?? 0 }}</td>
                                            <td>{{ $gradeDistributions[$test->id]['A']['F'] ?? 0 }}</td>
                                            <td>{{ $gradeDistributions[$test->id]['B']['M'] ?? 0 }}</td>
                                            <td>{{ $gradeDistributions[$test->id]['B']['F'] ?? 0 }}</td>
                                            <td>{{ $gradeDistributions[$test->id]['C']['M'] ?? 0 }}</td>
                                            <td>{{ $gradeDistributions[$test->id]['C']['F'] ?? 0 }}</td>
                                            <td>{{ $gradeDistributions[$test->id]['D']['M'] ?? 0 }}</td>
                                            <td>{{ $gradeDistributions[$test->id]['D']['F'] ?? 0 }}</td>
                                            <td>
                                                {{ number_format($gradeDistributions[$test->id]['ABC%'], 2) }}%</td>
                                            <td>
                                                {{ number_format($gradeDistributions[$test->id]['ABCD%'], 2) }}%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <!-- end card -->
        </div> <!-- end col -->
    </div>
@endsection
@section('script')
    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection
