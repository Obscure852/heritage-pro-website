<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? config('heritage_website.meta.default_title') }}</title>
    @hasSection('meta-description')
        <meta name="description" content="@yield('meta-description')">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&family=Source+Serif+4:ital,opsz,wght@0,8..60,300;0,8..60,400;0,8..60,600;1,8..60,400&display=swap">
    <style>
        @include('website.partials.editorial.base-styles')
    </style>
    @stack('page-styles')
</head>
<body>
    <a class="hp-skip" href="#main">Skip to content</a>
    @yield('content')
    @stack('page-scripts')
</body>
</html>
