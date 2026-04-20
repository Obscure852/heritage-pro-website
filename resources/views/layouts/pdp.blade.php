<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Staff PDP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.head-css')
    @yield('css')
</head>
<body>
    @inject('pdpAccess', 'App\Services\Pdp\PdpAccessService')
    <div class="min-vh-100" style="background: #f5f7fb;">
        <header class="border-bottom bg-white">
            <div class="container-fluid px-4 py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="text-uppercase text-muted small fw-semibold">Staff Performance Development Plan</div>
                    <h4 class="mb-0">@yield('page_title', 'Staff PDP')</h4>
                </div>
                <nav class="d-flex align-items-center gap-2">
                    <a href="{{ route('staff.pdp.my') }}"
                        class="btn btn-sm {{ request()->routeIs('staff.pdp.my') ? 'btn-primary' : 'btn-outline-primary' }}">My PDP</a>
                    @if (auth()->check() && $pdpAccess->hasElevatedAccess(auth()->user()))
                        <a href="{{ route('staff.pdp.plans.index') }}"
                            class="btn btn-sm {{ request()->routeIs('staff.pdp.plans.*') ? 'btn-primary' : 'btn-outline-primary' }}">Plans</a>
                        @if ($pdpAccess->canViewReports(auth()->user()))
                            <a href="{{ route('staff.pdp.reports.index') }}"
                                class="btn btn-sm {{ request()->routeIs('staff.pdp.reports.*') ? 'btn-primary' : 'btn-outline-primary' }}">Reports</a>
                        @endif
                        @if ($pdpAccess->canManageTemplates(auth()->user()))
                            <a href="{{ route('staff.pdp.settings.index', ['tab' => 'templates']) }}"
                                class="btn btn-sm {{ request()->routeIs('staff.pdp.templates.*') || request()->routeIs('staff.pdp.settings.*') ? 'btn-primary' : 'btn-outline-primary' }}">Templates</a>
                        @endif
                    @endif
                    <a href="{{ route('staff.pdp.plans.create') }}" class="btn btn-sm btn-primary">New Plan</a>
                </nav>
            </div>
        </header>

        <main class="container-fluid px-4 py-4">
            @if (session('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>There were validation errors.</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @include('layouts.vendor-scripts')
    @yield('scripts')
</body>
</html>
