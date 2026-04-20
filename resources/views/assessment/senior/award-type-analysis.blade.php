@extends('layouts.master')
@section('title')
    {{ $awardLabel }} Analysis
@endsection
@php
    $jsonData = $test ? json_encode($test) : null;
    $test_data = $jsonData ? json_decode($jsonData) : null;
@endphp

@section('css')
    <style>
        .equal-width-table th,
        .equal-width-table td {
            width: 1%;
            white-space: nowrap;
        }

        .printable {
            font-size: 10pt;
        }

        .printable table {
            font-size: 12px;
        }

        @media print {
            @page {
                size: landscape;
                margin: 15px;
            }

            .no-print {
                display: none !important;
            }

            .printable {
                font-size: 10pt;
            }

            .printable table {
                font-size: 10px;
            }

            #studentsTable tbody tr:first-child {
                background-color: #e6ffe6 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a class="text-muted" href="{{ $gradebookBackUrl }}">Back</a>
        @endslot
        @slot('title')
            {{ $awardLabel }} Analysis
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
            <a class="text-muted" href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">
                <i style="font-size: 20px;color:margin-bottom:10px; margin-right:10px; cursor:pointer;"
                    class="bx bx-download text-muted me-2" title="Export to Excel"></i>
            </a>

            <i onclick="printContent()" style="font-size: 20px; margin-bottom:10px; cursor:pointer;"
                class="bx bx-printer text-muted" title="Print"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 align-items-start">
                            <div style="font-size:14px;" class="form-group">
                                <strong>{{ $school_data->school_name ?? 'School Name' }}</strong>
                                <br>
                                <span>{{ $school_data->physical_address ?? 'Physical Address' }}</span>
                                <br>
                                <span>{{ $school_data->postal_address ?? 'Postal Address' }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone ?? 'N/A' }} Fax:
                                    {{ $school_data->fax ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 d-flex justify-content-end">
                            @if (isset($school_data->logo_path))
                                <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                            @else
                                <span>Logo Not Available</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($type === 'CA')
                        <h6>{{ $gradeName }} - {{ $awardLabel }} End of {{ $test->name ?? 'Month' }} Analysis</h6>
                    @else
                        <h6>{{ $gradeName }} - {{ $awardLabel }} End of Term Analysis</h6>
                    @endif

                    <div class="d-flex align-items-start mb-2">
                        <div>
                            <strong class="text-info">Note:</strong> <i>Students showing <strong>'-'</strong> on subjects
                                indicates they are not enrolled in that subject.
                                Students showing <strong>'X'</strong> indicates they are enrolled but do not have a score
                                recorded.</i>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="studentsTable" class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Class</th>
                                    <th>Surname</th>
                                    <th>Firstname</th>
                                    <th>Gender</th>
                                    <th>JCE</th>
                                    @foreach ($allSubjects as $subject)
                                        @php
                                            $hasScores = false;
                                            foreach ($students as $student) {
                                                if ($student['subjects'][$subject]['grade'] !== '-') {
                                                    $hasScores = true;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        @if ($hasScores)
                                            <th title="{{ $subject }}" style="text-align:center">
                                                {{ substr($subject, 0, 3) }}
                                            </th>
                                        @endif
                                    @endforeach
                                    <th>Overall Pts</th>
                                    <th>Best 6</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $index => $student)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $student['class'] }}</td>
                                        <td>{{ $student['surname'] }}</td>
                                        <td>{{ $student['firstname'] }}</td>
                                        <td>{{ $student['gender'] }}</td>
                                        <td>{{ $student['jce'] }}</td>
                                        @foreach ($allSubjects as $subject)
                                            @php
                                                $hasScores = false;
                                                foreach ($students as $s) {
                                                    if ($s['subjects'][$subject]['grade'] !== '-') {
                                                        $hasScores = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            @if ($hasScores)
                                                <td style="text-align:center">{{ $student['subjects'][$subject]['grade'] }}</td>
                                            @endif
                                        @endforeach
                                        <td>{{ $student['overallPoints'] }}</td>
                                        <td>{{ $student['best6Points'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function printContent() {
            window.print();
        }
    </script>
@endsection
