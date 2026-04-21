<form method="POST" action="{{ $action }}" class="crm-form">
    @csrf
    @if (! empty($method))
        @method($method)
    @endif

    <div class="crm-field-grid">
        <div class="crm-field">
            <label for="name">Name</label>
            <input id="name" name="name" value="{{ old('name', $user->name ?? '') }}" placeholder="Enter full name" required>
        </div>
        <div class="crm-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" placeholder="Enter email address" required>
        </div>
        <div class="crm-field">
            <label for="role">Role</label>
            <select id="role" name="role">
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" @selected(old('role', $user->role ?? 'rep') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label>&nbsp;</label>
            <label class="crm-check">
                <input type="checkbox" name="active" value="1" @checked(old('active', $user->active ?? true))>
                <span>Active account</span>
            </label>
        </div>
        <div class="crm-field">
            <label for="password">{{ isset($user) ? 'New password' : 'Password' }}</label>
            <input id="password" name="password" type="password" placeholder="Enter password" @required(! isset($user))>
        </div>
        <div class="crm-field">
            <label for="password_confirmation">{{ isset($user) ? 'Confirm new password' : 'Confirm password' }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm password" @required(! isset($user))>
        </div>
    </div>

    <div class="form-actions">
        @if (! empty($deleteUrl))
            @include('crm.partials.delete-button', [
                'action' => $deleteUrl,
                'message' => $deleteMessage ?? 'Are you sure you want to permanently delete this user?',
                'label' => $deleteLabel ?? 'Delete user',
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
