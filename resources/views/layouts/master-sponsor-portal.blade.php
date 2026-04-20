<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <title> @yield('title') | Heritage Pro School Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Heritage Pro School Management System" name="description" />
    <meta content="Platinum Developers" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('assets/images/favicon.ico') }}">
    @include('layouts.head-css')
    @yield('css')
</head>


<body class="pace-done">
    {{-- @show --}}
    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('layouts.topbar-sponsor')
        @include('layouts.sidebar-sponsor')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('layouts.footer')
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->
    <!-- Right Sidebar -->
    @include('layouts.right-sidebar')
    <!-- /Right-bar -->

    @auth('sponsor')
        @include('layouts.partials.idle-session', [
            'idleGuard' => 'sponsor',
            'idleUserId' => auth('sponsor')->id(),
            'idleActivityRoute' => route('sponsor.activity'),
            'idleLoginRoute' => route('sponsor.login'),
            'idleLogoutRoute' => route('sponsor.logout'),
            'idleLogoutMethod' => 'GET',
        ])
    @endauth

    <!-- JAVASCRIPT -->
    @include('layouts.vendor-scripts')

    <script>
        $(document).ready(function() {
            $('#termId').change(function() {
                var term = $(this).val();
                const setSessionUrl = "{{ route('students.term-session') }}";
                $.ajax({
                    url: setSessionUrl,
                    method: 'POST',
                    data: {
                        term_id: term,
                        _token: '{{ csrf_token() }}'
                    },
                    error: function(xhr, status, error) {
                        console.error("Error from master layout:", xhr.status, xhr.statusText);
                    },
                });
            });
        });
    </script>
</body>

</html>
