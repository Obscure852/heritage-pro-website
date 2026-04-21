<form method="POST" action="{{ $action }}" class="crm-form">
    @csrf
    @if (! empty($method))
        @method($method)
    @endif

    <div class="crm-field-grid">
        <div class="crm-field">
            <label for="name">Full name</label>
            <input id="name" name="name" value="{{ old('name', $contact->name ?? '') }}" placeholder="Enter full name" required>
        </div>
        <div class="crm-field">
            <label for="job_title">Role</label>
            <input id="job_title" name="job_title" value="{{ old('job_title', $contact->job_title ?? '') }}" placeholder="Enter job title">
        </div>
        <div class="crm-field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $contact->email ?? '') }}" placeholder="name@institution.org">
        </div>
        <div class="crm-field">
            <label for="phone">Phone</label>
            <input id="phone" name="phone" value="{{ old('phone', $contact->phone ?? '') }}" placeholder="Enter phone number">
        </div>
        <div class="crm-field">
            <label for="lead_id">Lead</label>
            <select id="lead_id" name="lead_id">
                <option value="">Select a lead</option>
                @foreach ($leads as $leadOption)
                    <option value="{{ $leadOption->id }}" @selected((int) old('lead_id', $contact->lead_id ?? null) === $leadOption->id)>{{ $leadOption->company_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="customer_id">Customer</label>
            <select id="customer_id" name="customer_id">
                <option value="">Select a customer</option>
                @foreach ($customers as $customerOption)
                    <option value="{{ $customerOption->id }}" @selected((int) old('customer_id', $contact->customer_id ?? null) === $customerOption->id)>{{ $customerOption->company_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="owner_id">Owner</label>
            <select id="owner_id" name="owner_id">
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((int) old('owner_id', $contact->owner_id ?? auth()->id()) === $owner->id)>{{ $owner->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label>&nbsp;</label>
            <label class="crm-check">
                <input type="checkbox" name="is_primary" value="1" @checked(old('is_primary', $contact->is_primary ?? false))>
                <span>Mark as primary contact</span>
            </label>
        </div>
        <div class="crm-field full">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" placeholder="Add relationship notes, decision-maker context, or communication history">{{ old('notes', $contact->notes ?? '') }}</textarea>
        </div>
    </div>

    <div class="form-actions">
        @if (! empty($deleteUrl))
            @include('crm.partials.delete-button', [
                'action' => $deleteUrl,
                'message' => $deleteMessage ?? 'Are you sure you want to permanently delete this contact?',
                'label' => $deleteLabel ?? 'Delete contact',
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
