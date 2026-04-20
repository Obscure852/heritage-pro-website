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
</head>

@section('body')

    <body data-topbar="dark" data-layout="horizontal">
    @show

    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('layouts.horizontal')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <!-- Start content -->
                <div class="container-fluid">
                    @yield('content')
                </div> <!-- content -->
            </div>
            @include('layouts.footer')
        </div>
        <!-- ============================================================== -->
        <!-- End Right content here -->
        <!-- ============================================================== -->
    </div>
    <!-- END wrapper -->

    <!-- Right Sidebar -->
    @include('layouts.right-sidebar')
    <!-- END Right Sidebar -->

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

    @include('layouts.vendor-scripts')

    {{-- Document notification bell AJAX --}}
    @auth
    <script>
    (function() {
        if (!document.getElementById('doc-notification-dropdown')) return;

        function fetchNotificationCount() {
            $.get('/documents/notifications/unread-count', function(response) {
                var badge = $('#doc-notification-count');
                if (response.count > 0) {
                    badge.text(response.count > 99 ? '99+' : response.count).show();
                } else {
                    badge.hide();
                }
            });
        }

        function fetchNotifications() {
            $.get('/documents/notifications', function(response) {
                var list = $('#doc-notification-list');
                var empty = $('#doc-notification-empty');

                if (response.notifications.length === 0) {
                    empty.show();
                    return;
                }

                empty.hide();
                var html = '';
                response.notifications.forEach(function(n) {
                    var iconMap = {
                        'document_submitted': { icon: 'bx-file', bg: 'bg-info' },
                        'document_approved': { icon: 'bx-check-circle', bg: 'bg-success' },
                        'document_rejected': { icon: 'bx-x-circle', bg: 'bg-danger' },
                        'document_revision_requested': { icon: 'bx-revision', bg: 'bg-warning' },
                        'document_published': { icon: 'bx-globe', bg: 'bg-primary' },
                        'review_deadline_approaching': { icon: 'bx-timer', bg: 'bg-warning' }
                    };
                    var style = iconMap[n.type] || { icon: 'bx-bell', bg: 'bg-secondary' };
                    var readClass = n.read_at ? 'opacity-50' : '';

                    html += '<a href="' + (n.url || '#') + '" class="text-reset notification-item d-block p-3 border-bottom ' + readClass + '"'
                         + ' data-notification-id="' + n.id + '">'
                         + '<div class="d-flex align-items-start">'
                         + '<div class="avatar-xs me-3"><span class="avatar-title rounded-circle ' + style.bg + ' font-size-16"><i class="bx ' + style.icon + '"></i></span></div>'
                         + '<div class="flex-grow-1"><h6 class="mb-1" style="font-size: 13px;">' + n.title + '</h6>'
                         + '<p class="text-muted mb-0" style="font-size: 12px;">' + n.message + '</p>'
                         + '<p class="text-muted mb-0 mt-1" style="font-size: 11px;"><i class="bx bx-time-five"></i> ' + n.time_ago + '</p>'
                         + '</div></div></a>';
                });
                list.html(html);

                // Mark as read on click
                list.find('.notification-item').on('click', function(e) {
                    var id = $(this).data('notification-id');
                    $.post('/documents/notifications/' + id + '/read', { _token: $('meta[name="csrf-token"]').attr('content') });
                });
            });
        }

        // Fetch on dropdown open
        $('#page-header-notifications-dropdown').on('click', function() {
            fetchNotifications();
        });

        // Mark all read
        $('#doc-mark-all-read').on('click', function(e) {
            e.preventDefault();
            $.post('/documents/notifications/mark-all-read', { _token: $('meta[name="csrf-token"]').attr('content') }, function() {
                $('#doc-notification-count').hide();
                $('#doc-notification-list .notification-item').addClass('opacity-50');
            });
        });

        // Poll count every 60 seconds
        fetchNotificationCount();
        setInterval(fetchNotificationCount, 60000);
    })();
    </script>
    @endauth
</body>

</html>
