@extends('layouts.master')

@section('title', 'Add LTI Tool')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('lms.courses.index') }}">Learning Space</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('lms.lti.index') }}">LTI Tools</a>
        @endslot
        @slot('title')
            Add LTI Tool
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add LTI Tool</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lms.lti.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Tool Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="e.g., Turnitin, Kahoot, Padlet" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                rows="2" placeholder="Brief description of what this tool does">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">LTI Version <span class="text-danger">*</span></label>
                                <select name="lti_version" class="form-select @error('lti_version') is-invalid @enderror" required>
                                    <option value="1.3" {{ old('lti_version', '1.3') === '1.3' ? 'selected' : '' }}>LTI 1.3 (Recommended)</option>
                                    <option value="1.1" {{ old('lti_version') === '1.1' ? 'selected' : '' }}>LTI 1.1 (Legacy)</option>
                                </select>
                                @error('lti_version')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Privacy Level <span class="text-danger">*</span></label>
                                <select name="privacy_level" class="form-select @error('privacy_level') is-invalid @enderror" required>
                                    <option value="public" {{ old('privacy_level') === 'public' ? 'selected' : '' }}>Public (Name & Email)</option>
                                    <option value="name_only" {{ old('privacy_level', 'name_only') === 'name_only' ? 'selected' : '' }}>Name Only</option>
                                    <option value="anonymous" {{ old('privacy_level') === 'anonymous' ? 'selected' : '' }}>Anonymous</option>
                                </select>
                                @error('privacy_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tool URL <span class="text-danger">*</span></label>
                            <input type="url" name="tool_url" class="form-control @error('tool_url') is-invalid @enderror"
                                value="{{ old('tool_url') }}" placeholder="https://example.com/lti/launch" required>
                            @error('tool_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 lti13-field">
                            <label class="form-label">Login URL (LTI 1.3)</label>
                            <input type="url" name="login_url" class="form-control @error('login_url') is-invalid @enderror"
                                value="{{ old('login_url') }}" placeholder="https://example.com/lti/login">
                            <small class="text-muted">OIDC Login initiation URL for LTI 1.3</small>
                            @error('login_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 lti13-field">
                            <label class="form-label">Redirect URLs (LTI 1.3)</label>
                            <textarea name="redirect_urls" class="form-control @error('redirect_urls') is-invalid @enderror"
                                rows="2" placeholder="https://example.com/lti/launch&#10;https://example.com/lti/deep-link">{{ old('redirect_urls') }}</textarea>
                            <small class="text-muted">One URL per line</small>
                            @error('redirect_urls')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row lti13-field">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Public Key URL</label>
                                <input type="url" name="public_key_url" class="form-control @error('public_key_url') is-invalid @enderror"
                                    value="{{ old('public_key_url') }}" placeholder="https://example.com/.well-known/jwks.json">
                                @error('public_key_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Icon URL</label>
                                <input type="url" name="icon_url" class="form-control @error('icon_url') is-invalid @enderror"
                                    value="{{ old('icon_url') }}" placeholder="https://example.com/icon.png">
                                @error('icon_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 lti13-field">
                            <label class="form-label">Public Key (PEM)</label>
                            <textarea name="public_key" class="form-control font-monospace @error('public_key') is-invalid @enderror"
                                rows="4" placeholder="-----BEGIN PUBLIC KEY-----">{{ old('public_key') }}</textarea>
                            <small class="text-muted">Optional if Public Key URL is provided</small>
                            @error('public_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('lms.lti.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Tool
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Platform Configuration</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Provide these details to the tool provider:</p>

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Platform ID (Issuer)</label>
                        <input type="text" class="form-control form-control-sm font-monospace" value="{{ config('app.url') }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">JWKS URL</label>
                        <input type="text" class="form-control form-control-sm font-monospace" value="{{ route('lms.lti.jwks') }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">OIDC Auth URL</label>
                        <input type="text" class="form-control form-control-sm font-monospace" value="{{ config('app.url') }}/lms/lti/auth" readonly>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small text-muted mb-1">Token URL</label>
                        <input type="text" class="form-control form-control-sm font-monospace" value="{{ config('app.url') }}/lms/lti/token" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const versionSelect = document.querySelector('select[name="lti_version"]');
    const lti13Fields = document.querySelectorAll('.lti13-field');

    function toggleLti13Fields() {
        const isLti13 = versionSelect.value === '1.3';
        lti13Fields.forEach(field => {
            field.style.display = isLti13 ? 'block' : 'none';
        });
    }

    versionSelect.addEventListener('change', toggleLti13Fields);
    toggleLti13Fields();
});
</script>
@endpush
@endsection
