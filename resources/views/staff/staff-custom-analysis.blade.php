@extends('layouts.master')
@section('title')
    Users Analysis
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('staff.index') }}">Back</a>
        @endslot
        @slot('title')
            Staff Custom Analysis Report
        @endslot
    @endcomponent
    <style>
        .card {
            box-shadow: 0;
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

            .printable {
                position: relative;
                margin: 0 auto;
                width: 80%;
                max-width: 1000px;
            }
        }
    </style>
    <div class="row printable">
        <div class="col-12">
            <div class="card">
                <div style="height: 120px;" class="card-header">
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
                    <div>
                        <form id="reportForm" method="POST" action="{{ route('staff.staff-generate-report') }}">
                            @csrf
                            <div class="form-group">
                                <label for="area_of_work">Select Area of Work</label>
                                <select class="form-select form-select-sm" id="area_of_work" name="area_of_work">
                                    <option value="">Select Area of Work</option>
                                    @foreach ($areas_of_work as $area)
                                        <option value="{{ $area->name }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group mt-3" id="fields-selection" style="display: none;">
                                <label>Select Fields</label>
                                <div id="fields"></div>
                            </div>

                            <div class="row">
                                <div class="col-12 d-flex justify-content-start mt-4">
                                    <button type="submit" class="btn btn-sm btn-primary" style="display: none;"
                                        id="generate-report-btn">Generate Report</button>
                                    <button type="submit" id="export-to-excel" name="export_excel" value="1"
                                        style="display: none; margin-left: 5px;" class="btn btn-sm btn-success">Export to
                                        Excel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- end card -->
        </div> <!-- end col -->
    </div>
@endsection
@section('script')
    <script>
        document.getElementById('area_of_work').addEventListener('change', function() {
            const areaOfWorkId = this.value;
            if (areaOfWorkId) {
                fetchFields();
            } else {
                document.getElementById('fields-selection').style.display = 'none';
                document.getElementById('generate-report-btn').style.display = 'none';
                document.getElementById('export-to-excel').style.display = 'none';
            }
        });

        function fetchFields() {
            fetch('{{ route('staff.staff-get-fields') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                })
                .then(response => response.json())
                .then(fields => {
                    const fieldsContainer = document.getElementById('fields');
                    fieldsContainer.innerHTML = '';
                    Object.keys(fields).forEach(field => {
                        const div = document.createElement('div');
                        div.className = 'form-check';
                        const input = document.createElement('input');
                        input.className = 'form-check-input';
                        input.type = 'checkbox';
                        input.name = 'fields[]';
                        input.value = field;
                        input.id = 'field_' + field;
                        const label = document.createElement('label');
                        label.className = 'form-check-label';
                        label.htmlFor = 'field_' + field;
                        label.textContent = fields[field];
                        div.appendChild(input);
                        div.appendChild(label);
                        fieldsContainer.appendChild(div);
                    });
                    document.getElementById('fields-selection').style.display = 'block';
                    document.getElementById('generate-report-btn').style.display = 'block';
                    document.getElementById('export-to-excel').style.display = 'block';
                });
        }
    </script>
@endsection
