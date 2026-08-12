@extends('layouts.crm-wizard')

@section('title', 'Verify Client Setup Access')

@section('wizard_header')
    <div class="crm-wizard-header">
        <p class="crm-wizard-eyebrow">Secure access</p>
        <h1>Verify your client setup access</h1>
        <p>We use a one-time code to protect the setup information saved for {{ $maskedEmail }}.</p>
    </div>
@endsection

@section('content')
    @php
        $verificationSentAt = $invitation->verification_sent_at;
        $verificationExpiresAt = $invitation->verification_code_expires_at;
        $hasActiveCode = $verificationSentAt && $verificationExpiresAt && $verificationExpiresAt->isFuture();
        $resendAvailableAt = $verificationSentAt?->copy()->addSeconds(config('client_setup.verification_code_resend_cooldown_seconds', 60));
        $oldCode = (string) old('code');
    @endphp

    <section class="crm-verification-card" aria-labelledby="verification-card-heading" data-verification-shell>
        <div class="crm-verification-card-header">
            <div>
                <p class="crm-kicker">Protected setup</p>
                <h2 id="verification-card-heading">{{ $hasActiveCode ? 'Enter your verification code' : 'Send a verification code' }}</h2>
                <p>{{ $hasActiveCode ? 'Enter the six-digit code from the latest email to continue.' : 'We will send a six-digit code to your verified email address.' }}</p>
            </div>
            <span class="crm-verification-icon" aria-hidden="true"><i class="bx {{ $hasActiveCode ? 'bx-key' : 'bx-mail-send' }}"></i></span>
        </div>

        <div class="crm-verification-steps" aria-label="Verification progress">
            <span class="is-complete"><b>1</b> Send code</span>
            <span class="crm-verification-step-line" aria-hidden="true"></span>
            <span class="{{ $hasActiveCode ? 'is-current' : '' }}"><b>2</b> Enter code</span>
        </div>

        @if ($hasActiveCode)
            <form method="POST" action="{{ route('client-setup.verify', ['token' => request()->route('token')]) }}" class="crm-form crm-verification-form" data-otp-form data-countdown-end="{{ $verificationExpiresAt->toIso8601String() }}">
                @csrf
                <div class="crm-field">
                    <label for="otp_digit_1">Six-digit verification code</label>
                    <div class="crm-otp-inputs" role="group" aria-label="Six-digit verification code">
                        @for ($digit = 1; $digit <= 6; $digit++)
                            <input
                                id="otp_digit_{{ $digit }}"
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="1"
                                class="crm-otp-input"
                                data-otp-digit
                                data-otp-index="{{ $digit - 1 }}"
                                aria-label="Digit {{ $digit }}"
                                value="{{ substr($oldCode, $digit - 1, 1) }}"
                                @if ($digit === 1) autocomplete="one-time-code" autofocus @else autocomplete="off" @endif
                            >
                        @endfor
                    </div>
                    <input id="code" name="code" type="hidden" value="{{ old('code') }}" data-otp-value>
                    <small class="crm-verification-countdown" role="status" aria-live="polite" data-code-countdown>Code expires soon.</small>
                </div>

                <button type="submit" class="btn btn-primary btn-loading crm-verification-submit">
                    <span class="btn-text"><i class="bx bx-lock-open-alt"></i> Verify and continue</span>
                    <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Verifying...</span>
                </button>
            </form>

            <div class="crm-verification-resend">
                <span data-resend-countdown>
                    @if ($resendAvailableAt && $resendAvailableAt->isFuture())
                        You can request another code in <strong data-resend-timer>01:00</strong>.
                    @else
                        Didn’t receive it? You can request a new code.
                    @endif
                </span>
                <form method="POST" action="{{ route('client-setup.verification-code', ['token' => request()->route('token')]) }}" data-resend-form>
                    @csrf
                    <button type="submit" class="btn btn-light crm-btn-light btn-sm" data-resend-button @disabled($resendAvailableAt && $resendAvailableAt->isFuture())>
                        <i class="bx bx-refresh"></i> Resend code
                    </button>
                </form>
            </div>
        @else
            <div class="crm-verification-send-note">
                <i class="bx bx-time-five" aria-hidden="true"></i>
                <p>For your security, the code expires after {{ config('client_setup.verification_code_expires_minutes', 10) }} minutes.</p>
            </div>

            <form method="POST" action="{{ route('client-setup.verification-code', ['token' => request()->route('token')]) }}" class="crm-form">
                @csrf
                <button type="submit" class="btn btn-primary btn-loading crm-verification-submit">
                    <span class="btn-text"><i class="bx bx-send"></i> Send me the code</span>
                    <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...</span>
                </button>
            </form>
        @endif
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            var otpForm = document.querySelector('[data-otp-form]');
            var digitInputs = Array.from(document.querySelectorAll('[data-otp-digit]'));
            var otpValue = document.querySelector('[data-otp-value]');

            if (! otpForm || ! otpValue || digitInputs.length === 0) {
                return;
            }

            var syncOtpValue = function () {
                otpValue.value = digitInputs.map(function (input) { return input.value; }).join('');
            };

            var focusDigit = function (index) {
                digitInputs[Math.max(0, Math.min(index, digitInputs.length - 1))]?.focus();
            };

            digitInputs.forEach(function (input, index) {
                input.addEventListener('input', function () {
                    input.value = input.value.replace(/\D/g, '').slice(-1);
                    input.removeAttribute('aria-invalid');
                    syncOtpValue();

                    if (input.value && index < digitInputs.length - 1) {
                        focusDigit(index + 1);
                    }
                });

                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Backspace' && ! input.value && index > 0) {
                        focusDigit(index - 1);
                    }

                    if (event.key === 'ArrowLeft') {
                        event.preventDefault();
                        focusDigit(index - 1);
                    }

                    if (event.key === 'ArrowRight') {
                        event.preventDefault();
                        focusDigit(index + 1);
                    }
                });

                input.addEventListener('paste', function (event) {
                    event.preventDefault();
                    var pasted = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, digitInputs.length);

                    pasted.split('').forEach(function (digit, pastedIndex) {
                        if (digitInputs[index + pastedIndex]) {
                            digitInputs[index + pastedIndex].value = digit;
                        }
                    });

                    syncOtpValue();
                    focusDigit(Math.min(index + pasted.length, digitInputs.length - 1));
                });
            });

            otpForm.addEventListener('submit', function (event) {
                syncOtpValue();

                if (otpValue.value.length !== digitInputs.length) {
                    event.preventDefault();
                    digitInputs.find(function (input) { return ! input.value; })?.focus();
                    digitInputs.forEach(function (input) { input.setAttribute('aria-invalid', 'true'); });
                }
            });

            var countdown = document.querySelector('[data-code-countdown]');
            var countdownEnd = Date.parse(otpForm.dataset.countdownEnd || '');
            var resendTimer = document.querySelector('[data-resend-countdown]');
            var resendButton = document.querySelector('[data-resend-button]');
            var resendEnd = {{ $resendAvailableAt ? $resendAvailableAt->timestamp * 1000 : 'null' }};

            var formatTime = function (milliseconds) {
                var totalSeconds = Math.max(0, Math.ceil(milliseconds / 1000));
                var minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
                var seconds = String(totalSeconds % 60).padStart(2, '0');
                return minutes + ':' + seconds;
            };

            var updateTimers = function () {
                var now = Date.now();

                if (countdown && Number.isFinite(countdownEnd)) {
                    var remaining = countdownEnd - now;
                    countdown.textContent = remaining > 0 ? 'Code expires in ' + formatTime(remaining) + '.' : 'This code has expired. Request a new code below.';
                    countdown.classList.toggle('is-expired', remaining <= 0);
                }

                if (resendTimer && resendEnd) {
                    var resendRemaining = resendEnd - now;
                    if (resendRemaining > 0) {
                        resendTimer.innerHTML = 'You can request another code in <strong>' + formatTime(resendRemaining) + '</strong>.';
                    } else {
                        resendTimer.textContent = 'Didn’t receive it? You can request a new code.';
                        if (resendButton) {
                            resendButton.disabled = false;
                        }
                    }
                }
            };

            updateTimers();
            window.setInterval(updateTimers, 1000);
        })();
    </script>
@endpush
