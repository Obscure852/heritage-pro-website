@extends('layouts.master')
@section('title')
    Students Without Houses
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('students.index') }}">Back</a>
        @endslot
        @slot('title')
            Students Without Houses
        @endslot
    @endcomponent
    <style>
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            box-shadow: none;
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
                width: 100%;
                overflow-x: auto;
            }

            .printable .table th,
            .printable .table td {
                padding: 8px;
                text-align: left;
            }

            .no-print {
                display: none !important;
            }
        }

        .class-section {
            margin-bottom: 2rem;
        }

        .class-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .suggested-house {
            background-color: #d4edda;
            color: #155724;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .no-suggestion {
            background-color: #fff3cd;
            color: #856404;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
        }
    </style>
    <div class="row">
        <div class="col-12 d-flex justify-content-end no-print">
            <i onclick="printContent()" style="font-size: 18px;margin-bottom:10px;cursor:pointer;" class="bx bx-printer text-muted"></i>
        </div>
    </div>

    @if (session('message'))
        <div class="row no-print">
            <div class="col-md-12">
                <div class="alert alert-success alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-check-all label-icon"></i><strong>{{ session('message') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="row no-print">
            <div class="col-md-12">
                <div class="alert alert-danger alert-dismissible alert-label-icon label-arrow fade show" role="alert">
                    <i class="mdi mdi-block-helper label-icon"></i><strong>{{ session('error') }}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6 col-lg-6 align-items-start">
                            <div style="font-size:14px;" class="form-group">
                                <strong>{{ $school_data->school_name }}</strong>
                                <br>
                                <span style="margin:0;padding:0;"> {{ $school_data->physical_address }}</span>
                                <br>
                                <span style="margin:0;padding:0;"> {{ $school_data->postal_address }}</span>
                                <br>
                                <span>Tel: {{ $school_data->telephone . ' Fax: ' . $school_data->fax }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-6 d-flex justify-content-end">
                            <img height="80" src="{{ URL::asset($school_data->logo_path) }}" alt="School Logo">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="report-card">
                        <div class="row mb-3">
                            <div class="col-12">
                                <h5>Students Without Houses</h5>
                                <p class="text-muted">
                                    Term: <strong>Term {{ $selectedTerm->term }}, {{ $selectedTerm->year }}</strong>
                                    <br>
                                    Total: {{ $totalStudents }} students not allocated to any house
                                </p>
                            </div>
                        </div>

                        @if($totalStudents == 0)
                            <div class="alert alert-success">
                                <i class="mdi mdi-check-circle me-2"></i>
                                All students are allocated to houses for this term.
                            </div>
                        @else
                            @foreach($studentsByClass as $classId => $classData)
                                <div class="class-section">
                                    @php
                                        $unallocatedCount = $classData['students']->filter(fn($s) => !$s->has_house)->count();
                                    @endphp
                                    <div class="class-header">
                                        <div>
                                            <strong>Class: {{ $classData['name'] }}</strong>
                                            <span class="badge bg-secondary ms-2">{{ $unallocatedCount }} unallocated</span>
                                            @if($classData['students']->count() != $unallocatedCount)
                                                <span class="badge bg-success ms-1">{{ $classData['students']->count() - $unallocatedCount }} allocated</span>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center gap-2 no-print">
                                            @if($unallocatedCount > 0)
                                                @if($classData['suggested_house_name'])
                                                    <span class="suggested-house">
                                                        <i class="mdi mdi-lightbulb-outline me-1"></i>
                                                        Suggested: {{ $classData['suggested_house_name'] }}
                                                    </span>
                                                    <form action="{{ route('students.allocate-all-to-house') }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to allocate {{ $unallocatedCount }} unallocated students in {{ $classData['name'] }} to {{ $classData['suggested_house_name'] }}?');">
                                                        @csrf
                                                        <input type="hidden" name="class_id" value="{{ $classId }}">
                                                        <input type="hidden" name="house_id" value="{{ $classData['suggested_house_id'] }}">
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <i class="mdi mdi-account-multiple-plus me-1"></i>
                                                            Allocate All ({{ $unallocatedCount }}) to {{ $classData['suggested_house_name'] }}
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="no-suggestion">
                                                        <i class="mdi mdi-alert-outline me-1"></i>
                                                        No house suggestion (no classmates have houses)
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-success">
                                                    <i class="mdi mdi-check-circle me-1"></i>
                                                    All students allocated
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <table class="table table-sm table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:40px;">#</th>
                                                <th>Student Name</th>
                                                <th style="width:80px;">Gender</th>
                                                <th style="width:120px;">Current House</th>
                                                <th style="width:200px;" class="no-print">Allocate to House</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($classData['students']->sortBy('last_name') as $index => $student)
                                                <tr class="{{ $student->has_house ? 'table-success' : '' }}">
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                                                    <td>{{ $student->gender == 'M' ? 'Male' : 'Female' }}</td>
                                                    <td>
                                                        @if($student->has_house)
                                                            <span class="badge bg-success">
                                                                <i class="mdi mdi-home me-1"></i>{{ $student->current_house->name }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">Not allocated</span>
                                                        @endif
                                                    </td>
                                                    <td class="no-print">
                                                        @if($student->has_house)
                                                            <span class="text-success">
                                                                <i class="mdi mdi-check-circle me-1"></i>Already allocated
                                                            </span>
                                                        @else
                                                            <form action="{{ route('students.allocate-to-house') }}" method="POST" class="d-flex gap-1">
                                                                @csrf
                                                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                                                <select name="house_id" class="form-select form-select-sm" style="width:120px;" required>
                                                                    <option value="">Select...</option>
                                                                    @foreach($houses as $house)
                                                                        <option value="{{ $house->id }}" {{ $classData['suggested_house_id'] == $house->id ? 'selected' : '' }}>
                                                                            {{ $house->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="submit" class="btn btn-primary btn-sm">
                                                                    <i class="mdi mdi-check"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        @endif
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
