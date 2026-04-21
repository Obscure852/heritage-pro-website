@if (session('crm_success') || session('crm_error') || $errors->any())
    <div class="crm-alerts">
        @if (session('crm_success'))
            <div class="crm-alert success">{{ session('crm_success') }}</div>
        @endif

        @if (session('crm_error'))
            <div class="crm-alert error">{{ session('crm_error') }}</div>
        @endif

        @if ($errors->any())
            <div class="crm-alert error">
                <strong>Please review the form input.</strong>
                <ul style="margin: 10px 0 0 18px; padding: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
