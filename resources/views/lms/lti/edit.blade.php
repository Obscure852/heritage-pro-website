@extends('layouts.master')

@section('title', 'Edit ' . $tool->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('lms.lti.index') }}">LTI Tools</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('lms.lti.show', $tool) }}">{{ $tool->name }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit LTI Tool</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('lms.lti.update', $tool) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Tool Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $tool->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                rows="2">{{ old('description', $tool->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Privacy Level</label>
                                <select name="privacy_level" class="form-select @error('privacy_level') is-invalid @enderror">
                                    <option value="public" {{ old('privacy_level', $tool->privacy_level) === 'public' ? 'selected' : '' }}>Public (Name & Email)</option>
                                    <option value="name_only" {{ old('privacy_level', $tool->privacy_level) === 'name_only' ? 'selected' : '' }}>Name Only</option>
                                    <option value="anonymous" {{ old('privacy_level', $tool->privacy_level) === 'anonymous' ? 'selected' : '' }}>Anonymous</option>
                                </select>
                                @error('privacy_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        {{ old('is_active', $tool->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tool URL</label>
                            <input type="url" name="tool_url" class="form-control @error('tool_url') is-invalid @enderror"
                                value="{{ old('tool_url', $tool->tool_url) }}">
                            @error('tool_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($tool->isLti13())
                            <div class="mb-3">
                                <label class="form-label">Login URL</label>
                                <input type="url" name="login_url" class="form-control @error('login_url') is-invalid @enderror"
                                    value="{{ old('login_url', $tool->login_url) }}">
                                @error('login_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Redirect URLs</label>
                                <textarea name="redirect_urls" class="form-control @error('redirect_urls') is-invalid @enderror"
                                    rows="2">{{ old('redirect_urls', $tool->redirect_urls) }}</textarea>
                                <small class="text-muted">One URL per line</small>
                                @error('redirect_urls')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Public Key URL</label>
                                    <input type="url" name="public_key_url" class="form-control @error('public_key_url') is-invalid @enderror"
                                        value="{{ old('public_key_url', $tool->public_key_url) }}">
                                    @error('public_key_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Icon URL</label>
                                    <input type="url" name="icon_url" class="form-control @error('icon_url') is-invalid @enderror"
                                        value="{{ old('icon_url', $tool->icon_url) }}">
                                    @error('icon_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Public Key (PEM)</label>
                                <textarea name="public_key" class="form-control font-monospace @error('public_key') is-invalid @enderror"
                                    rows="4">{{ old('public_key', $tool->public_key) }}</textarea>
                                @error('public_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <a href="{{ route('lms.lti.show', $tool) }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card shadow-sm mt-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">Deleting this tool will remove all resource links and launch history.</p>
                    <form action="{{ route('lms.lti.destroy', $tool) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this LTI tool? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete Tool
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Tool Information</h6>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt>LTI Version</dt>
                        <dd>LTI {{ $tool->lti_version }}</dd>

                        <dt>Created</dt>
                        <dd>{{ $tool->created_at->format('M j, Y') }}</dd>

                        <dt>Created By</dt>
                        <dd>{{ $tool->creator?->name ?? 'Unknown' }}</dd>

                        <dt>Resource Links</dt>
                        <dd>{{ $tool->resourceLinks()->count() }}</dd>

                        <dt>Total Launches</dt>
                        <dd>{{ $tool->launches()->count() }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
