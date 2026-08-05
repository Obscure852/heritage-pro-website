@php
    $detail = fn (string $icon) => collect($site['contact']['details'])->firstWhere('icon', $icon)['value'] ?? null;
    $address = $detail('pin');
    $email = $detail('mail');
    $phone = $detail('phone');
@endphp
<footer class="hp-footer hp-band">
    <div class="hp-footer__grid">
        <div>
            <p class="hp-footer__brand">Heritage&nbsp;Pro</p>
            <p class="hp-footer__blurb">The school information system for junior schools, senior schools and tertiary institutions.</p>
        </div>
        <nav class="hp-footer__col" aria-label="Platform">
            <p class="hp-label">Platform</p>
            <a href="#capabilities">Student information</a>
            <a href="#capabilities">Assessment</a>
            <a href="#modules">Finance</a>
            <a href="#modules">Learning</a>
            <a href="#capabilities">Tertiary</a>
        </nav>
        <nav class="hp-footer__col" aria-label="Institutions">
            <p class="hp-label">Institutions</p>
            <a href="#editions">Junior schools</a>
            <a href="#editions">Senior schools</a>
            <a href="#editions">Colleges</a>
            <a href="{{ route('website.customers') }}">School groups</a>
            <a href="{{ route('website.customers') }}">Ministries</a>
        </nav>
        <nav class="hp-footer__col" aria-label="Company">
            <p class="hp-label">Company</p>
            <a href="{{ route('website.about') }}">About</a>
            <a href="#resellers">Resellers</a>
            <a href="#team">Team</a>
            <a href="{{ route('website.customers') }}">Case studies</a>
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
