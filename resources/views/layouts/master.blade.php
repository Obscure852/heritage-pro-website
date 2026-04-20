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
@php
    $showModal = session('term_end_modal_shown', true) === false;
@endphp
@if ($showModal)
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var termEndModal = new bootstrap.Modal(document.getElementById('termEndModal'));
            termEndModal.show();
            @php
                session(['term_end_modal_shown' => true]);
            @endphp
        });
    </script>
@endif

<body class="pace-done">
    {{-- @show --}}
    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')
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

    @auth
        @include('layouts.partials.idle-session', [
            'idleGuard' => 'web',
            'idleUserId' => auth()->id(),
            'idleActivityRoute' => route('auth.activity'),
            'idleLoginRoute' => route('login'),
            'idleLogoutRoute' => route('logout'),
            'idleLogoutMethod' => 'POST',
        ])
    @endauth

    <!-- JAVASCRIPT -->
    @include('layouts.vendor-scripts')

    @can('access-setup')
        <style>
            #termEndModal .modal-content {
                border: none;
                border-radius: 3px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            }

            #termEndModal .modal-header {
                padding: 1.5rem;
                border-bottom: 1px solid #e5e7eb;
                background: white;
            }

            #termEndModal .modal-header .modal-title {
                font-weight: 600;
                font-size: 16px;
                color: #1f2937;
            }

            #termEndModal .modal-body {
                padding: 1.5rem;
                font-size: 14px;
                color: #374151;
            }

            #termEndModal .modal-footer {
                padding: 1.5rem;
                border-top: 1px solid #e5e7eb;
            }

            #termEndModal .modal-footer .btn {
                padding: 10px 20px;
                border-radius: 3px;
                font-size: 14px;
                font-weight: 500;
                border: none;
                cursor: pointer;
                transition: all 0.2s;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            #termEndModal .modal-footer .btn-secondary {
                background: #6c757d;
                color: white;
            }

            #termEndModal .modal-footer .btn-secondary:hover {
                background: #5a6268;
                transform: translateY(-1px);
                color: white;
            }

            #termEndModal .modal-footer .btn-primary {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
                color: white;
            }

            #termEndModal .modal-footer .btn-primary:hover {
                background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
                color: white;
            }
        </style>
        <div class="modal fade" id="termEndModal" tabindex="-1" aria-labelledby="termEndModalLabel" aria-hidden="true"
            data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="termEndModalLabel">Term Ending Soon</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        The term is ending in less than 10 days. Please take action.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x"></i>Close
                        </button>
                        <a href="{{ route('setup.index') }}" class="btn btn-primary">
                            <i class="bx bx-link-external"></i>Take Action
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endcan

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
                    success: function() {
                        //window.location.reload();
                    }
                });
            });
        });
    </script>
</body>

</html>
