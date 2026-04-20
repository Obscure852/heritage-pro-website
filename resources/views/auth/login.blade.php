@extends('layouts.master-without-nav')
@section('title')
    Login | Heritage Pro School Management System
@endsection

@section('css')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .auth-page {
            background: linear-gradient(135deg, #e8f4fc 0%, #f0f4f8 50%, #e3edf5 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .login-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.02);
            max-width: 1100px;
            width: 100%;
            display: flex;
        }

        .login-form-section {
            flex: 0 0 540px;
            padding: 28px 56px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .secure-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #e8f4fc 0%, #dbeafe 100%);
            color: #2563eb;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            width: fit-content;
            border: 1px solid rgba(37, 99, 235, 0.1);
        }

        .secure-badge i {
            font-size: 11px;
        }

        .login-title {
            font-size: 26px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 18px;
            font-weight: 400;
        }

        .login-toggle {
            display: flex;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 18px;
            gap: 4px;
        }

        .toggle-btn {
            flex: 1;
            padding: 10px 16px;
            border: none;
            background: transparent;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .toggle-btn:hover {
            color: #475569;
        }

        .toggle-btn.active {
            background: #2563eb;
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .toggle-btn i {
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i.input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 16px;
            transition: color 0.2s ease;
        }

        .form-control {
            width: 100%;
            padding: 11px 16px 11px 44px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            color: #1e293b;
            background: #fafafa;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .form-control:focus+i.input-icon,
        .input-wrapper:focus-within i.input-icon {
            color: #2563eb;
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: #64748b;
        }

        .btn-signin {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 2px;
        }

        .btn-signin:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
            transform: translateY(-1px);
        }

        .btn-signin:active {
            transform: translateY(0);
        }

        .btn-signin i {
            font-size: 14px;
            transition: transform 0.2s ease;
        }

        .btn-signin:hover i {
            transform: translateX(4px);
        }

        .btn-signin.loading .btn-text {
            display: none;
        }

        .btn-signin.loading .btn-spinner {
            display: inline-flex !important;
            align-items: center;
        }

        .btn-signin:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-signin:disabled:hover {
            transform: none;
            box-shadow: none;
        }

        .forgot-password {
            text-align: center;
            margin-top: 14px;
        }

        .forgot-password a {
            color: #2563eb;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .forgot-password a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .login-image-section {
            flex: 1;
            background-image: url('{{ URL::asset(\App\Models\SchoolSetup::schoolLoginImage()) }}');
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
        }

        .login-image-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(0, 0, 0, 0.05) 100%);
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: #94a3b8;
            font-size: 13px;
            width: 100%;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .alert .btn-close {
            margin-left: auto;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            opacity: 0.5;
        }

        .form-pane {
            display: none;
        }

        .form-pane.active {
            display: block;
        }

        /* Holiday Message Styles */
        .holiday-message-container {
            position: absolute;
            bottom: 24px;
            right: 24px;
            left: 24px;
            z-index: 10;
            max-width: 320px;
            margin-left: auto;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .holiday-message-container:hover {
            transform: translateY(-4px);
        }

        .holiday-message {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            color: white;
            padding: 2.25rem 1.25rem 1.25rem 1.25rem;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            position: relative;
            border-left: 4px solid var(--holiday-color, #2563eb);
            text-align: left;
            display: flex;
            flex-direction: column;
        }

        .holiday-message::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--holiday-color, #2563eb), transparent);
            border-radius: 0 0 16px 16px;
        }

        .holiday-header {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .holiday-icon-container {
            position: relative;
            width: 40px;
            height: 40px;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        .holiday-icon-bg {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 12px;
            background: var(--holiday-color, #2563eb);
            opacity: 0.2;
            animation: pulseIcon 2.5s ease-in-out infinite;
        }

        @keyframes pulseIcon {

            0%,
            100% {
                transform: scale(0.95);
                opacity: 0.2;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.35;
            }
        }

        .holiday-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--holiday-color, #2563eb);
            font-size: 1.125rem;
        }

        .holiday-title {
            font-weight: 600;
            font-size: 0.9375rem;
            margin: 0;
            padding: 0;
            flex-grow: 1;
            letter-spacing: -0.01em;
        }

        .holiday-message-text {
            position: relative;
            z-index: 2;
            line-height: 1.6;
            font-size: 0.8125rem;
            padding: 0;
            margin: 0;
            color: rgba(255, 255, 255, 0.85);
        }

        .holiday-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
            pointer-events: none;
            border-radius: 16px;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--holiday-color, #2563eb);
            opacity: 0;
            animation: floatParticle 5s ease-in-out infinite;
            z-index: 1;
        }

        @keyframes floatParticle {
            0% {
                transform: translateY(80px);
                opacity: 0;
            }

            50% {
                opacity: 0.4;
            }

            100% {
                transform: translateY(-80px);
                opacity: 0;
            }
        }

        .holiday-countdown {
            position: absolute;
            top: 12px;
            right: 12px;
            font-size: 0.6875rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            background: rgba(255, 255, 255, 0.15);
            padding: 0.25rem 0.625rem;
            border-radius: 100px;
            z-index: 3;
            letter-spacing: 0.02em;
        }

        .holiday-botswana-independence .holiday-message {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(0, 27, 96, 0.85) 100%);
        }

        .holiday-new-year .holiday-message {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(0, 59, 17, 0.85) 100%);
        }

        .holiday-christmas .holiday-message {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(127, 29, 29, 0.85) 100%);
        }

        .holiday-labor-day .holiday-message {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(120, 53, 15, 0.85) 100%);
        }

        .holiday-valentines-day .holiday-message {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(131, 24, 67, 0.85) 100%);
        }

        .holiday-womens-day .holiday-message {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(88, 28, 135, 0.85) 100%);
        }

        .holiday-easter .holiday-message {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(75, 0, 130, 0.85) 100%);
        }

        .fade-in-up {
            animation: holidayFadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes holidayFadeInUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-out-down {
            animation: holidayFadeOutDown 0.4s ease forwards;
        }

        @keyframes holidayFadeOutDown {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(16px);
            }
        }

        @media (max-width: 900px) {
            .login-card {
                flex-direction: column;
                max-width: 480px;
            }

            .login-image-section {
                display: none;
            }

            .login-form-section {
                flex: 1;
                padding: 24px 28px;
            }

            .login-title {
                font-size: 22px;
            }

            .holiday-message-container {
                position: fixed;
                bottom: 20px;
                right: 20px;
                left: 20px;
                z-index: 1000;
                max-width: calc(100% - 40px);
            }
        }
    </style>
@endsection

@section('content')
    <div class="auth-page">
        <div class="login-card">
            <!-- LEFT SIDE: Form -->
            <div class="login-form-section">
                <div class="secure-badge">
                    <i class="fas fa-shield-alt"></i>
                    SECURE LOGIN
                </div>

                <h1 class="login-title">Welcome back</h1>
                <p class="login-subtitle">Sign in to access your Heritage Pro account</p>

                <!-- Toggle Buttons -->
                <div class="login-toggle">
                    <button type="button" class="toggle-btn active" data-target="staff">
                        <i class="fas fa-user"></i>
                        Staff
                    </button>
                    <button type="button" class="toggle-btn" data-target="sponsor">
                        <i class="fas fa-handshake"></i>
                        Sponsor
                    </button>
                    <button type="button" class="toggle-btn" data-target="student">
                        <i class="fas fa-graduation-cap"></i>
                        Student
                    </button>
                </div>

                <!-- Alerts -->
                @if (session('errors'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ session('errors')->first('email') }}</span>
                        <button type="button" class="btn-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif

                @if (session('message'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('message') }}</span>
                        <button type="button" class="btn-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif

                <!-- STAFF LOGIN FORM -->
                <div class="form-pane active" id="staff-form">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Email address</label>
                            <div class="input-wrapper">
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                                    required autocomplete="email" autofocus placeholder="name@example.com">
                                <i class="fas fa-envelope input-icon"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="input-wrapper">
                                <input type="password" class="form-control" name="password" id="staff_password" required
                                    autocomplete="current-password" placeholder="Enter your password"
                                    style="padding-right: 48px;">
                                <i class="fas fa-lock input-icon"></i>
                                <button type="button" class="password-toggle" data-target="staff_password">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-signin">
                            <span class="btn-text">Sign in <i class="fas fa-arrow-right"></i></span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Signing in...
                            </span>
                        </button>

                        <div class="forgot-password">
                            <a href="{{ route('password.request') }}">Reset password?</a>
                        </div>
                    </form>
                </div>

                <!-- SPONSOR LOGIN FORM -->
                <div class="form-pane" id="sponsor-form">
                    <form method="POST" action="{{ route('sponsor.login.post') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Email address</label>
                            <div class="input-wrapper">
                                <input type="email" class="form-control" name="email" required autocomplete="email"
                                    placeholder="name@example.com">
                                <i class="fas fa-envelope input-icon"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="input-wrapper">
                                <input type="password" class="form-control" name="password" id="sponsor_password" required
                                    autocomplete="current-password" placeholder="Enter your password"
                                    style="padding-right: 48px;">
                                <i class="fas fa-lock input-icon"></i>
                                <button type="button" class="password-toggle" data-target="sponsor_password">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-signin">
                            <span class="btn-text">Sign in <i class="fas fa-arrow-right"></i></span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Signing in...
                            </span>
                        </button>

                        <div class="forgot-password">
                            <a href="{{ route('sponsor.password.request') }}">Reset password?</a>
                        </div>
                    </form>
                </div>

                <!-- STUDENT LOGIN FORM -->
                <div class="form-pane" id="student-form">
                    <form method="POST" action="{{ route('student.login.post') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Email address</label>
                            <div class="input-wrapper">
                                <input type="email" class="form-control" name="email" required autocomplete="email"
                                    placeholder="name@example.com">
                                <i class="fas fa-envelope input-icon"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="input-wrapper">
                                <input type="password" class="form-control" name="password" id="student_password"
                                    required autocomplete="current-password" placeholder="Enter your password"
                                    style="padding-right: 48px;">
                                <i class="fas fa-lock input-icon"></i>
                                <button type="button" class="password-toggle" data-target="student_password">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-signin">
                            <span class="btn-text">Sign in <i class="fas fa-arrow-right"></i></span>
                            <span class="btn-spinner d-none">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Signing in...
                            </span>
                        </button>

                        <div class="forgot-password">
                            <a href="{{ route('student.password.request') }}">Reset password?</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- RIGHT SIDE: Image -->
            <div class="login-image-section"></div>
        </div>

        <div class="footer-text">
            &copy;
            <script>
                document.write(new Date().getFullYear())
            </script> Heritage Pro
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle buttons functionality
            const toggleButtons = document.querySelectorAll('.toggle-btn');
            const formPanes = document.querySelectorAll('.form-pane');

            // Restore saved tab preference
            const savedTab = localStorage.getItem('loginTab') || 'staff';
            activateTab(savedTab);

            // Handle URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            if (tabParam && (tabParam === 'staff' || tabParam === 'sponsor' || tabParam === 'student')) {
                activateTab(tabParam);
            }

            function activateTab(target) {
                toggleButtons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.target === target);
                });

                formPanes.forEach(pane => {
                    pane.classList.toggle('active', pane.id === `${target}-form`);
                });

                localStorage.setItem('loginTab', target);
            }

            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const target = this.dataset.target;
                    activateTab(target);

                    // Update URL
                    const url = new URL(window.location);
                    url.searchParams.set('tab', target);
                    window.history.replaceState({}, '', url);
                });
            });

            // Password toggle functionality
            document.querySelectorAll('.password-toggle').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.dataset.target;
                    const passwordInput = document.getElementById(targetId);
                    const icon = this.querySelector('i');

                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });

            // Handle server-side active tab
            const activeTab = '{{ $activeTab ?? '' }}';
            if (activeTab === 'student') {
                activateTab('student');
            } else if (activeTab === 'sponsor') {
                activateTab('sponsor');
            } else if (activeTab === 'user' || activeTab === 'staff') {
                activateTab('staff');
            }

            // Form submit loading state
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"].btn-signin');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });

            // ==================== Holiday Notifications ====================
            function getEasterDate(year) {
                year = parseInt(year);
                var a = year % 19;
                var b = Math.floor(year / 100);
                var c = year % 100;
                var d = Math.floor(b / 4);
                var e = b % 4;
                var f = Math.floor((b + 8) / 25);
                var g = Math.floor((b - f + 1) / 3);
                var h = (19 * a + b - d - g + 15) % 30;
                var i = Math.floor(c / 4);
                var k = c % 4;
                var l = (32 + 2 * e + 2 * i - h - k) % 7;
                var m = Math.floor((a + 11 * h + 22 * l) / 451);
                var n0 = (h + l - 7 * m + 114);
                var n = Math.floor(n0 / 31);
                var p = n0 % 31 + 1;
                return new Date(year, n - 1, p);
            }

            const holidays = [{
                    id: 'botswana-independence',
                    name: "Botswana Independence Day",
                    month: 8,
                    day: 30,
                    window: 3,
                    color: "#6AADE4",
                    icon: "fas fa-flag",
                    secondaryIcon: "fas fa-star",
                    messages: [
                        "Happy Independence Day! Celebrating Botswana's freedom and progress. May our nation continue to thrive in peace and unity.",
                        "Pula! Today we honor Botswana's journey to independence and continued prosperity. Join us in celebrating our heritage and national pride.",
                        "From the Kalahari to the Okavango, we celebrate the spirit of Botswana on this Independence Day. Our diversity is our strength.",
                        "Botswana Independence Day: Reflecting on our past, celebrating our present, building our future.",
                        "As the sun rises over Botswana, we commemorate our independence with pride and unity."
                    ],
                    festiveMessages: ["Pula! Happy Independence Day!", "Proud to be Motswana!",
                        "Celebrate Botswana!", "Our Heritage, Our Pride!"
                    ]
                },
                {
                    id: 'new-year',
                    name: "New Year",
                    month: 0,
                    day: 1,
                    window: 3,
                    color: "#28a745",
                    icon: "fas fa-glass-cheers",
                    secondaryIcon: "fas fa-calendar-alt",
                    messages: [
                        "Happy New Year! May this year bring new knowledge, opportunities, and success to everyone in the Heritage Pro community.",
                        "New beginnings, fresh starts! Wishing you success in the academic year ahead.",
                        "Cheers to a new year of learning, growth, and achievement at Heritage Pro.",
                        "New Year, new goals! What will you accomplish this year with Heritage Pro?",
                        "As we welcome the New Year, we're excited for all that awaits in our learning journey together."
                    ],
                    festiveMessages: ["Happy New Year!", "New Year, New Success!",
                        "Fresh Start, Bright Future!", "Dream Big This Year!"
                    ]
                },
                {
                    id: 'christmas',
                    name: "Christmas",
                    month: 11,
                    day: 25,
                    window: 3,
                    color: "#dc3545",
                    icon: "fas fa-gifts",
                    secondaryIcon: "fas fa-holly-berry",
                    messages: [
                        "Merry Christmas! May your holiday season be filled with joy, wonder, and special moments with loved ones.",
                        "Wishing our Heritage Pro community a peaceful and blessed Christmas celebration.",
                        "From our school family to yours, we wish you a magical Christmas holiday.",
                        "Celebrate the gift of knowledge this Christmas season with Heritage Pro.",
                        "The spirit of Christmas brings warmth to our hearts and minds. Happy holidays from Heritage Pro!"
                    ],
                    festiveMessages: ["Merry Christmas!", "Joy to the World!", "Season's Greetings!",
                        "Peace & Good Will!"
                    ]
                },
                {
                    id: 'easter',
                    name: "Easter Sunday",
                    isMoveable: true,
                    window: 3,
                    color: "#8B00FF",
                    icon: "fas fa-church",
                    secondaryIcon: "fas fa-egg",
                    messages: [
                        "Happy Easter! May this season of renewal bring joy, hope, and blessings to you and your family.",
                        "Easter reminds us of new beginnings and the promise of hope. May your Easter be filled with meaningful moments.",
                        "Celebrating the miracle of Easter and the gift of new life.",
                        "Wishing you a blessed Easter celebration. May the light of Easter shine in your heart.",
                        "Easter brings the promise of new beginnings. May this season refresh your spirit and renew your hope."
                    ],
                    festiveMessages: ["Happy Easter!", "He Is Risen!", "Easter Blessings!",
                        "New Life, New Hope!"
                    ]
                },
                {
                    id: 'labor-day',
                    name: "Labor Day",
                    month: 4,
                    day: 1,
                    window: 3,
                    color: "#fd7e14",
                    icon: "fas fa-hammer",
                    secondaryIcon: "fas fa-hard-hat",
                    messages: [
                        "Happy Labor Day! Today we honor the strength and dedication of workers everywhere.",
                        "On this Labor Day, we recognize the contributions that build our communities and nation.",
                        "Labor Day: Celebrating the workforce that powers our progress and prosperity.",
                        "To all who labor to build a better future \u2013 we honor your work today and every day.",
                        "Heritage Pro salutes the dignity of work and those who perform it on this Labor Day."
                    ],
                    festiveMessages: ["Happy Labor Day!", "Honor All Workers!", "Celebrate Work!",
                        "Strength in Labor!"
                    ]
                },
                {
                    id: 'valentines-day',
                    name: "Valentine's Day",
                    month: 1,
                    day: 14,
                    window: 3,
                    color: "#e83e8c",
                    icon: "fas fa-heart",
                    secondaryIcon: "fas fa-kiss-wink-heart",
                    messages: [
                        "Happy Valentine's Day! Spread love through learning and knowledge. Education is a gift of the heart that lasts a lifetime.",
                        "On this day of love, remember that education is one of life's greatest gifts.",
                        "Valentine's Day reminds us that passion drives excellence in both love and learning.",
                        "Sending warm wishes on Valentine's Day from your Heritage Pro family.",
                        "Love learning? So do we! Happy Valentine's Day from Heritage Pro."
                    ],
                    festiveMessages: ["Happy Valentine's Day!", "Spread the Love!", "Love of Learning!",
                        "Hearts & Minds!"
                    ]
                },
                {
                    id: 'womens-day',
                    name: "International Women's Day",
                    month: 2,
                    day: 8,
                    window: 3,
                    color: "#6f42c1",
                    icon: "fas fa-female",
                    secondaryIcon: "fas fa-venus",
                    messages: [
                        "Happy International Women's Day! Celebrating the achievements of women in education and beyond.",
                        "Today we honor the women who inspire, teach, and lead at Heritage Pro and around the world.",
                        "International Women's Day: Recognizing the power of women's voices in shaping our future.",
                        "Equal education builds equal futures. Happy International Women's Day!",
                        "On this Women's Day, we celebrate the strength, wisdom, and courage of women everywhere."
                    ],
                    festiveMessages: ["Happy Women's Day!", "Women Empowered!", "Celebrate Her Story!",
                        "Women Lead Change!"
                    ]
                }
            ];

            function isEasterDate(date, windowDays) {
                const easterDate = getEasterDate(date.getFullYear());
                const windowStart = new Date(easterDate);
                windowStart.setDate(easterDate.getDate() - windowDays);
                windowStart.setHours(0, 0, 0, 0);
                const windowEnd = new Date(easterDate);
                windowEnd.setDate(easterDate.getDate() + windowDays);
                windowEnd.setHours(23, 59, 59, 999);
                return date >= windowStart && date <= windowEnd;
            }

            function getDaysFromEaster(today) {
                const easterDate = getEasterDate(today.getFullYear());
                const diffDays = Math.floor((today - easterDate) / (1000 * 60 * 60 * 24));
                if (diffDays === 0) return 'Today!';
                if (diffDays === -1) return 'Tomorrow';
                if (diffDays === 1) return 'Yesterday';
                if (diffDays < 0) return `${Math.abs(diffDays)} days away`;
                return `${diffDays} days ago`;
            }

            function isWithinHolidayWindow(today, holidayMonth, holidayDay, windowDays) {
                const holidayDate = new Date(today.getFullYear(), holidayMonth, holidayDay);
                const windowStart = new Date(holidayDate);
                windowStart.setDate(holidayDate.getDate() - windowDays);
                windowStart.setHours(0, 0, 0, 0);
                const windowEnd = new Date(holidayDate);
                windowEnd.setDate(holidayDate.getDate() + windowDays);
                windowEnd.setHours(23, 59, 59, 999);
                return today >= windowStart && today <= windowEnd;
            }

            function getCountdownText(today, holidayMonth, holidayDay) {
                const holidayDate = new Date(today.getFullYear(), holidayMonth, holidayDay);
                const diffDays = Math.floor((today - holidayDate) / (1000 * 60 * 60 * 24));
                if (diffDays === 0) return 'Today!';
                if (diffDays === -1) return 'Tomorrow';
                if (diffDays === 1) return 'Yesterday';
                if (diffDays < 0) return `${Math.abs(diffDays)} days away`;
                return `${diffDays} days ago`;
            }

            function createParticles(container, holiday) {
                const particlesContainer = document.createElement('div');
                particlesContainer.className = 'holiday-particles';
                for (let i = 0; i < 15; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'particle';
                    particle.style.left = `${Math.random() * 100}%`;
                    particle.style.top = `${Math.random() * 100}%`;
                    const size = 3 + Math.random() * 5;
                    particle.style.width = `${size}px`;
                    particle.style.height = `${size}px`;
                    particle.style.animationDelay = `${Math.random() * 4}s`;
                    particle.style.backgroundColor = holiday.color;
                    particle.style.opacity = 0.2 + Math.random() * 0.4;
                    particlesContainer.appendChild(particle);
                }
                container.appendChild(particlesContainer);
            }

            function displayHolidayMessage(holiday, today, isEaster) {
                const loginImage = document.querySelector('.login-image-section');
                if (!loginImage) return;

                const messageContainer = document.createElement('div');
                messageContainer.className = `holiday-message-container fade-in-up holiday-${holiday.id}`;

                const messageEl = document.createElement('div');
                messageEl.className = 'holiday-message';
                messageEl.style.setProperty('--holiday-color', holiday.color);

                const countdownText = isEaster ?
                    getDaysFromEaster(today) :
                    getCountdownText(today, holiday.month, holiday.day);

                const seed = holiday.name.length + today.getFullYear();
                const mainMessage = holiday.messages[seed % holiday.messages.length];
                const festiveMessage = holiday.festiveMessages[(seed + 1) % holiday.festiveMessages.length];

                createParticles(messageEl, holiday);

                const countdownEl = document.createElement('div');
                countdownEl.className = 'holiday-countdown';
                countdownEl.textContent = countdownText;
                messageEl.appendChild(countdownEl);

                const headerEl = document.createElement('div');
                headerEl.className = 'holiday-header';

                const iconContainer = document.createElement('div');
                iconContainer.className = 'holiday-icon-container';
                const iconBg = document.createElement('div');
                iconBg.className = 'holiday-icon-bg';
                iconContainer.appendChild(iconBg);
                const iconEl = document.createElement('i');
                iconEl.className = `${holiday.icon} holiday-icon`;
                iconContainer.appendChild(iconEl);
                headerEl.appendChild(iconContainer);

                const titleEl = document.createElement('div');
                titleEl.className = 'holiday-title';
                titleEl.textContent = holiday.name;
                headerEl.appendChild(titleEl);

                messageEl.appendChild(headerEl);

                const messageTextEl = document.createElement('div');
                messageTextEl.className = 'holiday-message-text';
                messageTextEl.textContent = mainMessage;
                messageEl.appendChild(messageTextEl);

                messageContainer.appendChild(messageEl);
                loginImage.appendChild(messageContainer);

                messageContainer.addEventListener('click', function() {
                    const festivePopup = document.createElement('div');
                    festivePopup.style.position = 'absolute';
                    festivePopup.style.bottom = '80px';
                    festivePopup.style.right = '20px';
                    festivePopup.style.background =
                        `linear-gradient(135deg, ${holiday.color}88, ${holiday.color}44)`;
                    festivePopup.style.color = 'white';
                    festivePopup.style.padding = '10px 15px';
                    festivePopup.style.borderRadius = '20px';
                    festivePopup.style.fontWeight = 'bold';
                    festivePopup.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
                    festivePopup.style.zIndex = '20';
                    festivePopup.style.animation =
                        'holidayFadeInUp 0.3s ease forwards, holidayFadeOutDown 0.3s ease 2s forwards';
                    const icons = [holiday.icon, holiday.secondaryIcon];
                    const randomIcon = icons[Math.floor(Math.random() * icons.length)];
                    festivePopup.innerHTML =
                        `<i class="${randomIcon}" style="margin-right:8px"></i> ${festiveMessage}`;
                    loginImage.appendChild(festivePopup);
                    setTimeout(() => festivePopup.remove(), 2500);
                });
            }

            function checkForHolidays() {
                const today = new Date();
                const easterHoliday = holidays.find(h => h.id === 'easter');
                if (easterHoliday && isEasterDate(today, easterHoliday.window)) {
                    displayHolidayMessage(easterHoliday, today, true);
                    return;
                }
                for (const holiday of holidays) {
                    if (holiday.isMoveable) continue;
                    if (isWithinHolidayWindow(today, holiday.month, holiday.day, holiday.window)) {
                        displayHolidayMessage(holiday, today, false);
                        return;
                    }
                }
            }

            checkForHolidays();

            // Test mode: append ?holiday=valentines-day to URL to preview
            const holidayParam = new URLSearchParams(window.location.search).get('holiday');
            if (holidayParam) {
                const holidayToTest = holidays.find(h => h.id === holidayParam || h.id.includes(holidayParam
                    .toLowerCase()));
                if (holidayToTest) {
                    displayHolidayMessage(holidayToTest, new Date(), holidayToTest.id === 'easter');
                }
            }
        });
    </script>
@endsection
