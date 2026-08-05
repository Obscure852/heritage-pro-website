@php
    $detail = fn (string $icon) => collect($site['contact']['details'])->firstWhere('icon', $icon)['value'] ?? null;
    $address = $detail('pin');
    $email = $detail('mail');
    $phone = $detail('phone');
    // Homepage section anchors, absolute so the footer works on /journal too.
    $home = route('website.home');
@endphp
<footer class="hp-footer hp-band">
    <div class="hp-footer__grid">
        <div>
            <p class="hp-footer__brand">Heritage&nbsp;Pro</p>
            <p class="hp-footer__blurb">The school information system for junior schools, senior schools and tertiary institutions.</p>
        </div>
        <nav class="hp-footer__col" aria-label="Platform">
            <p class="hp-label">Platform</p>
            <a href="{{ $home }}#capabilities">Student information</a>
            <a href="{{ $home }}#capabilities">Assessment</a>
            <a href="{{ $home }}#modules">Finance</a>
            <a href="{{ $home }}#modules">Learning</a>
            <a href="{{ $home }}#capabilities">Tertiary</a>
        </nav>
        <nav class="hp-footer__col" aria-label="Institutions">
            <p class="hp-label">Institutions</p>
            <a href="{{ $home }}#editions">Junior schools</a>
            <a href="{{ $home }}#editions">Senior schools</a>
            <a href="{{ $home }}#editions">Colleges</a>
            <a href="{{ route('website.customers') }}">School groups</a>
            <a href="{{ route('website.customers') }}">Ministries</a>
        </nav>
        <nav class="hp-footer__col" aria-label="Company">
            <p class="hp-label">Company</p>
            <a href="{{ route('website.about') }}">About</a>
            <a href="{{ route('website.prospectus') }}">Prospectus</a>
            <a href="{{ $home }}#resellers">Resellers</a>
            <a href="{{ $home }}#team">Team</a>
            <a href="{{ route('website.journal') }}">Journal</a>
            <a href="{{ route('website.faq') }}">FAQ</a>
            <a href="#demo">Contact</a>
        </nav>
        <div class="hp-footer__col">
            <p class="hp-label">Contact</p>
            @if ($address)
                <span>{{ $address }}</span>
            @endif
            @if ($phone)
                <a href="tel:{{ str_replace(' ', '', $phone) }}">{{ $phone }}</a>
            @endif
            @if ($email)
                <a href="mailto:{{ $email }}">{{ $email }}</a>
            @endif
        </div>
    </div>
    <div class="hp-footer__base">
        <span>© {{ now()->year }} Heritage Pro. All rights reserved.</span>
        <div class="hp-footer__legal">
            <a href="#">Privacy</a>
            <a href="#">Terms</a>
            <a href="#">Data processing</a>
            <a href="#">Status</a>
        </div>
    </div>
</footer>
