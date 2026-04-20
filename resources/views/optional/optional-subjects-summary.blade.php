@extends('layouts.master')
@section('title')
    Optional Subjects Analysis
@endsection

@section('css')
    <style>
        .stat-card {
            transition: transform 0.3s;
            color: white;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .progress {
            height: 20px;
            border-radius: 10px;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                line-height: normal;
            }

            .card {
                box-shadow: none;
            }

            body * {
                visibility: hidden;
            }

            .printable,
            .printable * {
                visibility: visible;
            }

            .printable {
                position: relative;
                margin: 0 auto;
                width: 100%;
                max-width: 100%;
                overflow-x: auto;
            }

            .printable .card {
                margin: 0;
                border: none;
                width: 100%;
            }

            .printable .card-body {
                margin: 0 auto;
                padding: 0;
            }

            .printable .table {
                width: 750px;
                overflow-x: auto;
            }

            .printable .table th,
            .printable .table td {
                padding: 8px;
                text-align: left;
            }

            .no-print {
                display: none;
            }
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('optional.index') }}"> Back </a>
        @endslot
        @slot('title')
            Optional Subjects Analysis Report
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-12 d-flex justify-content-end no-print">
            <i onclick="printContent()" style="font-size: 18px;margin-bottom:10px;cursor:pointer;"
                class="bx bx-printer text-muted"></i>
        </div>
    </div>

    <div class="row printable">
        <div class="col-md-12">
            <div class="card">
                <div style="height: 120px;" class="card-header">
                    <div class="row">
                        <div class="col-md-6 align-items-start">
                            <div class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span>{{ $school_data->physical_address }}</span>
                                <br>
                                <span>{{ $school_data->postal_address }}</span>
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
                    <h4 class="mb-4">{{ $grade->name }} Optional Subjects Analysis - Term {{ $term->term ?? '' }}</h4>

                    <!-- Summary Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6>Total Subjects</h6>
                                    <h3>{{ $statistics['total_subjects'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6>Total Students</h6>
                                    <h3>{{ $statistics['total_students'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6>Average Class Size</h6>
                                    <h3>{{ $statistics['average_class_size'] }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <h6>Total Venues</h6>
                                    <h3>{{ $statistics['venue_statistics']['total_venues'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Department Distribution -->
                    <div class="table-responsive mb-4">
                        <h5>Department Distribution</h5>
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Number of Subjects</th>
                                    <th>Total Students</th>
                                    <th>Average Class Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($departmentDistribution as $department => $data)
                                    <tr>
                                        <td>{{ $department }}</td>
                                        <td>{{ $data['count'] }}</td>
                                        <td>{{ $data['students'] }}</td>
                                        <td>{{ round($data['students'] / $data['count'], 1) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Subject Details -->
                    <div class="table-responsive mb-4">
                        <h5>Optional Subjects Details</h5>
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Grade</th>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th>Department</th>
                                    <th>Students</th>
                                    <th>Venue</th>
                                    <th>Group</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($optionalSubjects as $subject)
                                    <tr>
                                        <td>{{ $subject->grade->name }}</td>
                                        <td>{{ $subject->name }}</td>
                                        <td>{{ $subject->teacher->full_name }}</td>
                                        <td>{{ $subject->gradeSubject->department->name ?? 'N/A' }}</td>
                                        <td>{{ $subject->students->count() }}</td>
                                        <td>{{ $subject->venue->name }}</td>
                                        <td>{{ $subject->grouping ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Teacher Workload -->
                    <div class="table-responsive mb-4">
                        <h5>Teacher Workload Analysis</h5>
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Teacher</th>
                                    <th>Department</th>
                                    <th>Subjects</th>
                                    <th>Total Students</th>
                                    <th>Average Class Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($teachers as $teacher)
                                    @php
                                        $totalStudents = $teacher->taughtOptionalSubjects->sum(function ($subject) {
                                            return $subject->students->count();
                                        });
                                        $subjectCount = $teacher->taughtOptionalSubjects->count();
                                    @endphp
                                    <tr>
                                        <td>{{ $teacher->full_name }}</td>
                                        <td>{{ $teacher->department }}</td>
                                        <td>{{ $subjectCount }}</td>
                                        <td>{{ $totalStudents }}</td>
                                        <td>{{ $subjectCount > 0 ? round($totalStudents / $subjectCount, 1) : 0 }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Venue Utilization -->
                    <div class="table-responsive mb-4">
                        <h5>Venue Utilization Analysis</h5>
                        <!-- Venue Statistics -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="card stat-card">
                                    <div class="card-body">
                                        <h6>Venues Over Capacity</h6>
                                        <h3>{{ $statistics['venue_statistics']['over_capacity_venues'] }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card">
                                    <div class="card-body">
                                        <h6>Average Utilization</h6>
                                        <h3>{{ $statistics['venue_statistics']['average_utilization'] }}%</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card stat-card">
                                    <div class="card-body">
                                        <h6>Peak Utilization</h6>
                                        <h3>{{ $statistics['venue_statistics']['highest_utilization'] }}%</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Venue</th>
                                    <th>Type</th>
                                    <th>Capacity</th>
                                    <th>Subjects</th>
                                    <th>Current Students</th>
                                    <th>Utilization</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($venues as $venue)
                                    <tr>
                                        <td>{{ $venue->name }}</td>
                                        <td>{{ $venue->type }}</td>
                                        <td>{{ $venue->capacity }}</td>
                                        <td>{{ $venue->optionalSubjects->count() }}</td>
                                        <td>{{ $venue->optionalSubjects->sum(function ($subject) {
                                            return $subject->students->count();
                                        }) }}
                                        </td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar {{ $venue->current_utilization > 90 ? 'bg-danger' : ($venue->current_utilization > 75 ? 'bg-warning' : 'bg-success') }}"
                                                    role="progressbar"
                                                    style="width: {{ min($venue->current_utilization, 100) }}%"
                                                    aria-valuenow="{{ $venue->current_utilization }}" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    {{ $venue->current_utilization }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($venue->is_over_capacity)
                                                <span class="badge bg-danger">Over Capacity</span>
                                            @elseif($venue->current_utilization > 90)
                                                <span class="badge bg-warning">Near Capacity</span>
                                            @else
                                                <span class="badge bg-success">Available</span>
                                            @endif
                                        </td>
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
