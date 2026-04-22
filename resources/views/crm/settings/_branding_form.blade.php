@php
    $companyNameValue = old('company_name', $settings?->company_name ?? 'Heritage Pro');
    $companyLogoPreviewId = 'crm-company-logo-preview';
    $companyLogoFallbackId = 'crm-company-logo-fallback';
    $companyLogoInputId = 'crm-company-logo-input';
    $companyLogoHiddenId = 'crm-company-logo-hidden';
    $loginImagePreviewId = 'crm-login-image-preview';
    $loginImageFallbackId = 'crm-login-image-fallback';
    $loginImageInputId = 'crm-login-image-input';
    $loginImageHiddenId = 'crm-login-image-hidden';
    $companyLogoUrl = old('company_logo_cropped_image') ?: ($settings?->company_logo_url ?? null);
    $loginImageUrl = old('login_image_cropped_image') ?: ($settings?->login_image_url ?? null);
    $companyInitials = collect(preg_split('/\s+/', trim((string) $companyNameValue)) ?: [])
        ->filter()
        ->take(2)
        ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('');
@endphp

<form method="POST" action="{{ route('crm.settings.branding.update') }}" class="crm-form crm-settings-form-shell">
    @csrf
    @method('PATCH')

    <p class="crm-settings-form-copy">Upload the company logo shown in the CRM shell and the image displayed on the login screen. Both assets use the same square cropper for consistent presentation.</p>

    <div class="crm-branding-grid">
        <div class="crm-branding-card">
            <div class="crm-branding-card-head">
                <h3>Company logo</h3>
                <p>Used in the CRM topbar and supporting brand touchpoints.</p>
            </div>

            <label for="{{ $companyLogoInputId }}" class="crm-branding-trigger">
                <span class="crm-branding-shell">
                    @if ($companyLogoUrl)
                        <img src="{{ $companyLogoUrl }}" alt="{{ $companyNameValue }}" id="{{ $companyLogoPreviewId }}" class="crm-branding-image">
                    @else
                        <img src="" alt="{{ $companyNameValue }}" id="{{ $companyLogoPreviewId }}" class="crm-branding-image d-none">
                    @endif
                    <span id="{{ $companyLogoFallbackId }}" class="crm-branding-placeholder {{ $companyLogoUrl ? 'd-none' : '' }}">
                        <span class="crm-branding-placeholder-icon" aria-hidden="true">
                            <i class="bx bx-camera"></i>
                            <span class="crm-avatar-upload-plus">
                                <i class="fas fa-plus"></i>
                            </span>
                        </span>
                        <span class="crm-branding-placeholder-text">
                            <strong>{{ $companyInitials !== '' ? $companyInitials : 'CP' }}</strong>
                            <span>Click to upload and crop the company logo.</span>
                        </span>
                    </span>
                </span>
                <span class="crm-branding-caption">Click the logo area to {{ $companyLogoUrl ? 'replace' : 'add' }} the company logo.</span>
            </label>

            <input
                id="{{ $companyLogoInputId }}"
                class="d-none"
                type="file"
                accept="image/*"
                data-cropper-input
                data-cropper-title="Crop company logo"
                data-cropper-note="The logo is saved as a square image for consistent presentation across the CRM shell."
                data-cropper-hidden-target="{{ $companyLogoHiddenId }}"
                data-cropper-preview-target="{{ $companyLogoPreviewId }}"
                data-cropper-fallback-target="{{ $companyLogoFallbackId }}"
            >
            <input type="hidden" name="company_logo_cropped_image" id="{{ $companyLogoHiddenId }}" value="{{ old('company_logo_cropped_image') }}">
        </div>

        <div class="crm-branding-card">
            <div class="crm-branding-card-head">
                <h3>Login image</h3>
                <p>Shown on the authentication screen to reinforce the CRM visual identity.</p>
            </div>

            <label for="{{ $loginImageInputId }}" class="crm-branding-trigger">
                <span class="crm-branding-shell">
                    @if ($loginImageUrl)
                        <img src="{{ $loginImageUrl }}" alt="Login image" id="{{ $loginImagePreviewId }}" class="crm-branding-image">
                    @else
                        <img src="" alt="Login image" id="{{ $loginImagePreviewId }}" class="crm-branding-image d-none">
                    @endif
                    <span id="{{ $loginImageFallbackId }}" class="crm-branding-placeholder {{ $loginImageUrl ? 'd-none' : '' }}">
                        <span class="crm-branding-placeholder-icon" aria-hidden="true">
                            <i class="bx bx-camera"></i>
                            <span class="crm-avatar-upload-plus">
                                <i class="fas fa-plus"></i>
                            </span>
                        </span>
                        <span class="crm-branding-placeholder-text">
                            <strong>LI</strong>
                            <span>Click to upload and crop the login image.</span>
                        </span>
                    </span>
                </span>
                <span class="crm-branding-caption">Click the image area to {{ $loginImageUrl ? 'replace' : 'add' }} the login image.</span>
            </label>

            <input
                id="{{ $loginImageInputId }}"
                class="d-none"
                type="file"
                accept="image/*"
                data-cropper-input
                data-cropper-title="Crop login image"
                data-cropper-note="The login image is cropped to a square asset so it stays consistent with the CRM branding layout."
                data-cropper-hidden-target="{{ $loginImageHiddenId }}"
                data-cropper-preview-target="{{ $loginImagePreviewId }}"
                data-cropper-fallback-target="{{ $loginImageFallbackId }}"
            >
            <input type="hidden" name="login_image_cropped_image" id="{{ $loginImageHiddenId }}" value="{{ old('login_image_cropped_image') }}">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="fas fa-save"></i> Save branding</span>
            <span class="btn-spinner d-none">
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...
            </span>
        </button>
    </div>
</form>
