@extends('layouts.master')

@section('title')
    School Organogram
@endsection

@section('css')
    <!-- Include OrgChart.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.1.1/css/jquery.orgchart.min.css">
    <style>
        #organogram {
            height: 600px;
            width: 100%;
            background-color: #f8f9fa;
        }

        .orgchart {
            background: #f8f9fa;
        }

        .orgchart .node .title {
            background-color: #007bff;
            color: #ffffff;
        }

        .orgchart .node .content {
            border-color: #007bff;
        }

        .orgchart .node {
            width: 140px;
            background-color: #007bff;
            margin: 5px 5px;
        }

        .orgchart .lines .downLine {
            background-color: #007bff;
        }

        .orgchart .lines .rightLine {
            border-right-color: #007bff;
        }

        .orgchart .lines .leftLine {
            border-left-color: #007bff;
        }

        .orgchart .lines .topLine {
            border-top-color: #007bff;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('staff.index') }}">Back</a>
        @endslot
        @slot('title')
            School Organogram
        @endslot
    @endcomponent

    <div class="row">
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
                    @if (isset($error))
                        <div class="alert alert-danger">{{ $error }}</div>
                    @elseif(isset($organogram))
                        <div id="organogram"></div>
                    @else
                        <div class="alert alert-info">No data available for the organogram.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- Include OrgChart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.1.1/js/jquery.orgchart.min.js"></script>

    <script>
        $(function() {
            var organogramData = @json($organogram);

            function transformData(node) {
                var transformed = {
                    'name': node.name,
                    'title': node.title
                };
                if (node.children && node.children.length > 0) {
                    transformed.children = node.children.map(transformData);
                }
                return transformed;
            }

            var chartData = transformData(organogramData);

            $('#organogram').orgchart({
                'data': chartData,
                'nodeContent': 'title',
                'direction': 't2b',
                'pan': true,
                'zoom': true,
                'nodeTemplate': function(data) {
                    return `
                        <div class="title">${data.name}</div>
                        <div class="content">${data.title}</div>
                    `;
                }
            });
        });
    </script>
@endsection
