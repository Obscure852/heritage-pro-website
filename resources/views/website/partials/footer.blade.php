<footer class="footer">
    <div class="container">
        <div class="footer-top">
            <div class="footer-brand">
                <a href="{{ route('website.home') }}" class="nav-logo">
                    <img src="{{ asset('assets/images/heritage-pro-logo.svg') }}" alt="">
                    <span>Heritage <b>Pro</b></span>
                </a>
                <p>{{ $site['footer']['description'] }}</p>
                <div class="footer-social">
                    @foreach ($site['footer']['social'] as $social)
                        <a href="{{ $social['href'] }}" aria-label="{{ $social['label'] }}">
                            @include('website.partials.icon', ['name' => $social['icon'], 'size' => 18])
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="footer-cols">
                @foreach ($site['footer']['columns'] as $column)
                    <div>
                        <b>{{ $column['title'] }}</b>
                        @foreach ($column['links'] as $link)
                            <a href="{{ isset($link['route']) ? route($link['route']) : $link['href'] }}">{{ $link['label'] }}</a>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
        <div class="footer-bottom">
            <div>© 2025 Heritage Pro (Pty) Ltd · Registered in Botswana · Reg. CO-2024/28110</div>
            <div style="display: flex; gap: 20px;">
                <a href="#">Privacy</a>
                <a href="#">Terms</a>
                <a href="#">Cookies</a>
            </div>
        </div>
    </div>
</footer>
