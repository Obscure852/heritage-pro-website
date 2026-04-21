<header class="nav">
    <div class="container nav-inner">
        <a href="{{ route('website.home') }}" class="nav-logo">
            <img src="{{ asset('assets/images/heritage-pro-logo.svg') }}" alt="">
            <span>Heritage <b>Pro</b></span>
        </a>
        <nav class="nav-links">
            @foreach ($site['nav'] as $item)
                <a href="{{ route($item['route']) }}" @class(['active-route' => $page === str_replace('website.', '', $item['route'])])>{{ $item['label'] }}</a>
            @endforeach
        </nav>
        <div class="nav-cta">
            <a href="{{ route('website.sign-in') }}" class="btn btn-ghost">Sign in</a>
            <a href="#contact" class="btn btn-primary">Book a demo</a>
        </div>
    </div>
</header>
