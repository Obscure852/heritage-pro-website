@extends('layouts.master')
@section('title')
    House Award Analysis
@endsection

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

        .class-header-row td {
            background: #e9ecef !important;
            font-weight: 600;
            color: #495057;
            padding: 6px 8px !important;
        }

        .top-student td {
            background-color: #e6ffe6 !important;
        }

        .nav-tabs .nav-link {
            font-weight: 500;
            color: #6b7280;
        }

        .nav-tabs .nav-link.active {
            font-weight: 600;
            color: #4e73df;
            border-bottom: 2px solid #4e73df;
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

            .class-header-row td {
                background: #e9ecef !important;
                -webkit-print-color-adjust: exact;
            }

            .top-student td {
                background-color: #e6ffe6 !important;
                -webkit-print-color-adjust: exact;
            }

            /* Show all tabs on print */
            .tab-pane {
                display: block !important;
                opacity: 1 !important;
            }

            .tab-pane .card {
                page-break-inside: avoid;
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
            House Award Analysis
        @endslot
    @endcomponent

    <div class="row no-print">
        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
            <a class="text-muted" href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">
                <i style="font-size: 20px; margin-bottom:10px; margin-right:10px; cursor:pointer;"
                    class="bx bx-download text-muted me-2" title="Export to Excel"></i>
            </a>

            <i onclick="printContent()" style="font-size: 20px; margin-bottom:10px; cursor:pointer;"
                class="bx bx-printer text-muted" title="Print"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-12">
            {{-- School header --}}
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
                        <h5>{{ $gradeName }} - House Award Analysis - End of {{ $test->name ?? 'Month' }}</h5>
                    @else
                        <h5>{{ $gradeName }} - House Award Analysis - End of Term</h5>
                    @endif

                    <div class="d-flex align-items-start mb-2">
                        <div>
                            <strong class="text-info">Note:</strong> <i>Students showing <strong>'-'</strong> on subjects
                                indicates they are not enrolled in that subject.
                                Students showing <strong>'X'</strong> indicates they are enrolled but do not have a score
                                recorded.</i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- House tabs --}}
            <div class="card mt-3">
                <div class="card-header p-0 no-print">
                    <ul class="nav nav-tabs px-3 pt-2" id="houseTabs" role="tablist">
                        @foreach ($housesData as $houseName => $classesInHouse)
                            @php $totalStudents = collect($classesInHouse)->flatten(1)->count(); @endphp
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                    id="tab-{{ Str::slug($houseName) }}"
                                    data-bs-toggle="tab"
                                    data-bs-target="#pane-{{ Str::slug($houseName) }}"
                                    type="button" role="tab">
                                    <i class="fas fa-home me-1 text-warning"></i>
                                    {{ $houseName }}
                                    <span class="badge bg-secondary ms-1">{{ $totalStudents }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="tab-content" id="houseTabContent">
                    @foreach ($housesData as $houseName => $classesInHouse)
                        @php
                            $totalStudents = collect($classesInHouse)->flatten(1)->count();
                            $allHouseStudents = collect($classesInHouse)->flatten(1)->all();
                        @endphp
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                            id="pane-{{ Str::slug($houseName) }}" role="tabpanel">

                            {{-- Print-only house heading --}}
                            <div class="d-none d-print-block px-3 pt-3">
                                <h6>
                                    <i class="fas fa-home me-1"></i>
                                    {{ $houseName }} House
                                    <span style="font-weight:400; color:#6b7280;">({{ $totalStudents }} students)</span>
                                </h6>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
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
                                                    foreach ($allHouseStudents as $student) {
                                                        if (isset($student['subjects'][$subject]) && $student['subjects'][$subject]['grade'] !== '-') {
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
                                            <th>Credits</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $rowNum = 1; $isFirstStudent = true; @endphp
                                        @foreach ($classesInHouse as $className => $students)
                                            {{-- Class header row --}}
                                            <tr class="class-header-row">
                                                <td colspan="100">
                                                    <i class="fas fa-chalkboard me-1"></i>
                                                    {{ $className }}
                                                    <span style="font-weight:400; color:#6b7280;">({{ count($students) }} students)</span>
                                                </td>
                                            </tr>
                                            @foreach ($students as $student)
                                                <tr @if ($isFirstStudent) class="top-student" @endif>
                                                    <td>{{ $rowNum }}</td>
                                                    <td>{{ $student['class'] }}</td>
                                                    <td>{{ $student['surname'] }}</td>
                                                    <td>{{ $student['firstname'] }}</td>
                                                    <td>{{ $student['gender'] }}</td>
                                                    <td>{{ $student['jce'] }}</td>
                                                    @foreach ($allSubjects as $subject)
                                                        @php
                                                            $hasScores = false;
                                                            foreach ($allHouseStudents as $s) {
                                                                if (isset($s['subjects'][$subject]) && $s['subjects'][$subject]['grade'] !== '-') {
                                                                    $hasScores = true;
                                                                    break;
                                                                }
                                                            }
                                                        @endphp
                                                        @if ($hasScores)
                                                            <td style="text-align:center">{{ $student['subjects'][$subject]['grade'] ?? '-' }}</td>
                                                        @endif
                                                    @endforeach
                                                    <td>{{ $student['overallPoints'] }}</td>
                                                    <td>{{ $student['best6Points'] }}</td>
                                                    <td>{{ $student['credits'] }}</td>
                                                </tr>
                                                @php $rowNum++; $isFirstStudent = false; @endphp
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
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
