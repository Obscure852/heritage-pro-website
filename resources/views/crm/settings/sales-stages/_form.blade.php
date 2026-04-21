<form method="POST" action="{{ $action }}" class="crm-form">
    @csrf
    @if (! empty($method))
        @method($method)
    @endif

    <div class="crm-field-grid">
        <div class="crm-field">
            <label for="name">Stage name</label>
            <input id="name" name="name" value="{{ old('name', $stage->name ?? '') }}" placeholder="Enter stage name" required>
        </div>
        <div class="crm-field">
            <label for="position">Position</label>
            <input id="position" name="position" type="number" min="1" max="999" value="{{ old('position', $stage->position ?? $defaultPosition ?? 1) }}" placeholder="Enter stage order" required>
        </div>
        <div class="crm-field">
            <label>&nbsp;</label>
            <label class="crm-check">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $stage->is_active ?? true))>
                <span>Active stage</span>
            </label>
        </div>
        <div class="crm-field">
            <label>&nbsp;</label>
            <label class="crm-check">
                <input type="checkbox" name="is_won" value="1" @checked(old('is_won', $stage->is_won ?? false))>
                <span>Won stage</span>
            </label>
        </div>
        <div class="crm-field">
            <label>&nbsp;</label>
            <label class="crm-check">
                <input type="checkbox" name="is_lost" value="1" @checked(old('is_lost', $stage->is_lost ?? false))>
                <span>Lost stage</span>
            </label>
        </div>
    </div>

    <div class="form-actions">
        @if (! empty($deleteUrl))
            @include('crm.partials.delete-button', [
                'action' => $deleteUrl,
                'message' => $deleteMessage ?? 'Are you sure you want to permanently delete this sales stage?',
                'label' => $deleteLabel ?? 'Delete stage',
            ])
        @endif
        @if (! empty($cancelUrl))
            <a href="{{ $cancelUrl }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Cancel</a>
        @endif
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="{{ $submitIcon ?? 'fas fa-save' }}"></i> {{ $submitLabel }}</span>
            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...</span>
        </button>
    </div>
</form>
