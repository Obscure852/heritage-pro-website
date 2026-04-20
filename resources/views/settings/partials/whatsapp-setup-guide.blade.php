@php
    $whatsappFeatureSetting = ($notificationSettings['feature'] ?? collect())->firstWhere('key', 'features.whatsapp_enabled');
    $whatsappSettings = collect($notificationSettings['whatsapp'] ?? []);
    $whatsappAccountSid = optional($whatsappSettings->firstWhere('key', 'whatsapp.account_sid'))->value;
    $whatsappAuthToken = optional($whatsappSettings->firstWhere('key', 'whatsapp.auth_token'))->value;
    $whatsappSender = optional($whatsappSettings->firstWhere('key', 'whatsapp.sender'))->value;
    $whatsappSyncEnabled = (bool) optional($whatsappSettings->firstWhere('key', 'whatsapp.sync_enabled'))->value;
    $statusWebhookUrl = route('api.webhooks.whatsapp.status');
    $inboundWebhookUrl = route('api.webhooks.whatsapp.inbound');
@endphp

<div class="help-text">
    <div class="help-title"><i class="fas fa-info-circle me-2"></i>WhatsApp Activation Guide</div>
    <p class="help-content">Use this checklist to get WhatsApp working from end to end. Follow the steps in order, then return to the WhatsApp Settings tab to activate the channel only after the provider side is ready.</p>
</div>

<div class="guide-status-grid">
    <div class="guide-status-card">
        <span class="guide-status-label">Channel Switch</span>
        <span class="guide-status-value {{ ($whatsappFeatureSetting && $whatsappFeatureSetting->value) ? 'success' : 'warning' }}">
            {{ ($whatsappFeatureSetting && $whatsappFeatureSetting->value) ? 'Enabled' : 'Disabled' }}
        </span>
    </div>
    <div class="guide-status-card">
        <span class="guide-status-label">Twilio Credentials</span>
        <span class="guide-status-value {{ filled($whatsappAccountSid) && filled($whatsappAuthToken) ? 'success' : 'warning' }}">
            {{ filled($whatsappAccountSid) && filled($whatsappAuthToken) ? 'Configured' : 'Missing' }}
        </span>
    </div>
    <div class="guide-status-card">
        <span class="guide-status-label">WhatsApp Sender</span>
        <span class="guide-status-value {{ filled($whatsappSender) ? 'success' : 'warning' }}">
            {{ filled($whatsappSender) ? 'Configured' : 'Missing' }}
        </span>
    </div>
    <div class="guide-status-card">
        <span class="guide-status-label">Template Sync</span>
        <span class="guide-status-value {{ $whatsappSyncEnabled ? 'success' : 'warning' }}">
            {{ $whatsappSyncEnabled ? 'Enabled' : 'Disabled' }}
        </span>
    </div>
</div>

<div class="guide-step">
    <div class="d-flex align-items-start gap-3">
        <span class="guide-step-number">1</span>
        <div>
            <div class="guide-step-title">Prepare your Twilio and Meta business accounts</div>
            <div class="guide-step-body">
                Sign in to Twilio with an account that can use WhatsApp. Make sure you also have access to the Meta Business portfolio that will own the WhatsApp sender. If Meta business verification is required for your account, complete that first so sender approval and scaling are not blocked later.
            </div>
        </div>
    </div>
</div>

<div class="guide-step">
    <div class="d-flex align-items-start gap-3">
        <span class="guide-step-number">2</span>
        <div>
            <div class="guide-step-title">Register a WhatsApp sender in Twilio</div>
            <div class="guide-step-body">
                In Twilio Console, use WhatsApp Self Sign-up to register the first sender, then connect the phone number you want to use for WhatsApp messaging. The number must be able to complete Meta ownership verification through OTP. After approval, copy the final sender value in the format <code>whatsapp:+1234567890</code>.
            </div>
        </div>
    </div>
</div>

<div class="guide-step">
    <div class="d-flex align-items-start gap-3">
        <span class="guide-step-number">3</span>
        <div>
            <div class="guide-step-title">Create approved WhatsApp templates in Twilio</div>
            <div class="guide-step-body">
                This application sends WhatsApp messages using approved templates. Create your operational templates in Twilio Content Template Builder, submit them for approval, and wait until they are approved before testing direct sends or broadcasts.
            </div>
        </div>
    </div>
</div>

<div class="guide-step">
    <div class="d-flex align-items-start gap-3">
        <span class="guide-step-number">4</span>
        <div>
            <div class="guide-step-title">Fill in the WhatsApp settings in this system</div>
            <div class="guide-step-body">
                Open the <strong>WhatsApp Settings</strong> tab and enter:
                <br>1. Twilio Account SID
                <br>2. Twilio Auth Token
                <br>3. WhatsApp Sender
                <br>4. Optional webhook secrets
                <br>5. Default template language
                <br>6. Template sync preference and sync limit
                <br>Leave the channel disabled until the next steps are completed.
            </div>
        </div>
    </div>
</div>

<div class="guide-step">
    <div class="d-flex align-items-start gap-3">
        <span class="guide-step-number">5</span>
        <div>
            <div class="guide-step-title">Set the Twilio webhook URLs</div>
            <div class="guide-step-body">
                Configure Twilio to call this application for delivery updates and inbound replies.
                <span class="guide-code">Status callback: {{ $statusWebhookUrl }}</span>
                <span class="guide-code">Inbound webhook: {{ $inboundWebhookUrl }}</span>
                Use HTTPS in production. The webhook validation in this app uses Twilio signature verification, so the incoming request URL must match the public URL configured in Twilio.
            </div>
        </div>
    </div>
</div>

<div class="guide-step">
    <div class="d-flex align-items-start gap-3">
        <span class="guide-step-number">6</span>
        <div>
            <div class="guide-step-title">Sync templates into the application</div>
            <div class="guide-step-body">
                After your templates are approved in Twilio, sync them into this application so they become selectable in staff direct messaging and broadcasts. Keep template sync enabled if you want newly approved templates to appear automatically.
            </div>
        </div>
    </div>
</div>

<div class="guide-step">
    <div class="d-flex align-items-start gap-3">
        <span class="guide-step-number">7</span>
        <div>
            <div class="guide-step-title">Prepare staff records and consent</div>
            <div class="guide-step-body">
                Make sure staff phone numbers are valid and stored in international format. Record WhatsApp consent on the staff communication tab before sending. Broadcasts will skip staff without consent or without a usable phone number.
            </div>
        </div>
    </div>
</div>

<div class="guide-step">
    <div class="d-flex align-items-start gap-3">
        <span class="guide-step-number">8</span>
        <div>
            <div class="guide-step-title">Enable WhatsApp Sending in the app</div>
            <div class="guide-step-body">
                Return to the <strong>WhatsApp Settings</strong> tab and switch on <strong>Enable WhatsApp Sending</strong>. Once enabled, the WhatsApp buttons will appear in staff messaging where the channel is supported.
            </div>
        </div>
    </div>
</div>

<div class="guide-step">
    <div class="d-flex align-items-start gap-3">
        <span class="guide-step-number">9</span>
        <div>
            <div class="guide-step-title">Run a live test and confirm delivery tracking</div>
            <div class="guide-step-body">
                Send one direct WhatsApp template message to a consented staff member. Confirm that the message is created in communication history and that delivery status changes arrive through the status webhook. After that, test a small broadcast before using the feature broadly.
            </div>
        </div>
    </div>
</div>

<div class="form-section">
    <div class="section-header">
        <i class="fas fa-shield-alt"></i>
        <h5>Important Rules</h5>
    </div>
    <div class="guide-step-body">
        Outbound WhatsApp in this application is template-based. Consent is required before sending. Broadcasts are sent as individual template messages, not WhatsApp groups. If a template is not approved or a sender is not properly registered, sends will fail even if the app settings are filled in.
    </div>
</div>

<div class="form-section guide-links">
    <div class="section-header">
        <i class="fas fa-link"></i>
        <h5>Official References</h5>
    </div>
    <div class="guide-step-body">
        <a href="https://www.twilio.com/docs/whatsapp/self-sign-up" target="_blank" rel="noopener noreferrer">Twilio WhatsApp Self Sign-up</a>
        <br><a href="https://www.twilio.com/docs/whatsapp/register-senders-using-api" target="_blank" rel="noopener noreferrer">Twilio WhatsApp Sender Registration</a>
        <br><a href="https://www.twilio.com/docs/content/create-and-send-your-first-content-api-template" target="_blank" rel="noopener noreferrer">Twilio Content API Templates</a>
    </div>
</div>
