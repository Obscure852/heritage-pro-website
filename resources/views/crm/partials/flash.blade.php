@if (session('crm_success') || session('crm_error') || $errors->any())
    <div class="crm-toast-stack" data-crm-toast-stack>
        @if (session('crm_success'))
            <div
                class="crm-toast crm-toast-success"
                data-crm-toast
                data-duration="4200"
                role="status"
                aria-live="polite"
            >
                <div class="crm-toast-icon" aria-hidden="true">
                    <i class="bx bx-check-circle"></i>
                </div>
                <div class="crm-toast-copy">
                    <p class="crm-toast-title">Action completed</p>
                    <p class="crm-toast-message">{{ session('crm_success') }}</p>
                </div>
                <button type="button" class="crm-toast-close" data-crm-toast-close aria-label="Dismiss message">
                    <i class="bx bx-x"></i>
                </button>
                <span class="crm-toast-progress" data-crm-toast-progress aria-hidden="true"></span>
            </div>
        @endif

        @if (session('crm_error'))
            <div
                class="crm-toast crm-toast-error"
                data-crm-toast
                data-duration="6500"
                role="alert"
                aria-live="assertive"
            >
                <div class="crm-toast-icon" aria-hidden="true">
                    <i class="bx bx-error-circle"></i>
                </div>
                <div class="crm-toast-copy">
                    <p class="crm-toast-title">Action not completed</p>
                    <p class="crm-toast-message">{{ session('crm_error') }}</p>
                </div>
                <button type="button" class="crm-toast-close" data-crm-toast-close aria-label="Dismiss message">
                    <i class="bx bx-x"></i>
                </button>
                <span class="crm-toast-progress" data-crm-toast-progress aria-hidden="true"></span>
            </div>
        @endif

        @if ($errors->any())
            <div
                class="crm-toast crm-toast-error"
                data-crm-toast
                data-duration="9000"
                role="alert"
                aria-live="assertive"
            >
                <div class="crm-toast-icon" aria-hidden="true">
                    <i class="bx bx-error"></i>
                </div>
                <div class="crm-toast-copy">
                    <p class="crm-toast-title">Please review the form input</p>
                    <ul class="crm-toast-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                </div>
                <button type="button" class="crm-toast-close" data-crm-toast-close aria-label="Dismiss message">
                    <i class="bx bx-x"></i>
                </button>
                <span class="crm-toast-progress" data-crm-toast-progress aria-hidden="true"></span>
            </div>
        @endif
    </div>
@endif
