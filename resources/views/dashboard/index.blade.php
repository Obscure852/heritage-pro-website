@extends('layouts.master')
@section('title')
    Dashboard
@endsection
@section('css')
    <style>
        .dashboard-placeholder {
            margin: 0;
            padding: 15px;
        }

        .placeholder-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            height: 400px;
            margin-bottom: 20px;
        }

        .placeholder-header {
            height: 40px;
            background: #e9ecef;
        }

        .placeholder-body {
            padding: 15px;
            height: calc(100% - 40px);
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
        }

        .bar-container {
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            height: 100%;
            width: 100%;
        }

        .bar {
            width: 8%;
            background: #c0c0c0;
            border-radius: 4px 4px 0 0;
        }

        .bar:nth-child(1) {
            height: 60%;
        }

        .bar:nth-child(2) {
            height: 80%;
        }

        .bar:nth-child(3) {
            height: 40%;
        }

        .bar:nth-child(4) {
            height: 70%;
        }

        .bar:nth-child(5) {
            height: 50%;
        }

        .bar:nth-child(6) {
            height: 90%;
        }

        .bar:nth-child(7) {
            height: 30%;
        }

        .bar:nth-child(8) {
            height: 65%;
        }

        .bar:nth-child(9) {
            height: 75%;
        }

        .bar:nth-child(10) {
            height: 55%;
        }

        .pie {
            width: 80%;
            height: 80%;
            border-radius: 50%;
            background: conic-gradient(#c0c0c0 0deg 90deg,
                    #a0a0a0 90deg 180deg,
                    #808080 180deg 270deg,
                    #606060 270deg 360deg);
            margin: auto;
        }

        @keyframes pulse {
            0% {
                opacity: 0.6;
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0.6;
            }
        }

        .dashboard-placeholder * {
            animation: pulse 1.5s infinite;
        }

        @media (max-width: 768px) {
            .placeholder-card {
                height: 300px;
            }

            .bar {
                width: 12%;
            }
        }

        @media (max-width: 576px) {
            .placeholder-card {
                height: 250px;
            }

            .bar {
                width: 15%;
            }

            .pie {
                width: 70%;
                height: 70%;
            }
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboard
        @endslot
        @slot('title')
            Dashboard
        @endslot
    @endcomponent
    <div class="row align-items-center">
        <div class="col-md-6">
        </div>
        <div class="col-md-6">
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
                <select name="term" id="termId" class="form-select" style="width: auto; min-width: 180px;">
                    @if (!empty($terms))
                        @foreach ($terms as $term)
                            <option data-year="{{ $term->year }}"
                                value="{{ $term->id }}" {{ $term->id == session('selected_term_id', $currentTerm->id) ? 'selected' : '' }}>
                                {{ 'Term ' . $term->term . ', ' . $term->year }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div> <!-- end row-->
    <div class="row">
        <div id="dashboard" class="col-md-12">
        </div>
    </div><!-- end row-->
    <div class="row dashboard-placeholder">
        <div class="col-lg-8 col-md-12">
            <div class="placeholder-card bar-chart">
                <div class="placeholder-header"></div>
                <div class="placeholder-body">
                    <div class="bar-container">
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="placeholder-card pie-chart">
                <div class="placeholder-header"></div>
                <div class="placeholder-body">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#termId').change(function() {
                var term = $(this).val();
                var studentsSessionUrl = "{{ route('students.term-session') }}";
                $.ajax({
                    url: studentsSessionUrl,
                    method: 'POST',
                    data: {
                        term_id: term,
                        _token: '{{ csrf_token() }}'
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", xhr.status, xhr.statusText);
                        console.error("Detailed error:", error);
                        console.error("Response:", xhr.responseText);
                    },
                    success: function() {
                        fetchTermData();
                    }
                });
            });

            function fetchTermData() {
                var dashboardDataUrl = "{{ route('dashboard.dashboard-get-data') }}";
                $.ajax({
                    url: dashboardDataUrl,
                    method: 'GET',
                    success: function(response) {
                        $('.dashboard-placeholder').hide();
                        $('#dashboard').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching term data:", xhr.status, xhr.statusText);
                    }
                });
            }
            $('#termId').trigger('change');
        });
    </script>
@endsection
