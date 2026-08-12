@php
    // Section anchors live on the homepage, so they are absolute — the nav is
    // shared with /journal and its article pages.
    $home = route('website.home');
@endphp
<header class="hp-nav hp-band">
    <div class="hp-nav__group">
        <a href="{{ $home }}" class="hp-nav__brand">
            <img src="{{ asset('assets/images/heritage-pro-logo.jpg') }}" alt="" width="30" height="30">
            <span>Heritage&nbsp;Pro</span>
        </a>
        <nav class="hp-nav__links" aria-label="Primary">
            <a href="{{ $home }}#capabilities">Platform</a>
            <a href="{{ $home }}#editions">Institutions</a>
            <a href="{{ $home }}#modules">Modules</a>
            <a href="{{ route('website.pricing') }}">Pricing</a>
            <a href="{{ $home }}#resellers">Resellers</a>
            <a href="{{ $home }}#team">Team</a>
            <a href="{{ route('website.journal') }}" @class(['is-current' => ($page ?? null) === 'journal'])>Journal</a>
        </nav>
    </div>
    <div class="hp-nav__actions">
        <a href="{{ route('website.sign-in') }}" class="hp-nav__signin">Sign in</a>
        <a href="#demo" class="hp-btn hp-btn--sm hp-btn--solid">Book a demo</a>
    </div>
</header>
