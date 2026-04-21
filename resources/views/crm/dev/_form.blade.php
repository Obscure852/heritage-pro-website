<form method="POST" action="{{ $action }}" class="crm-form">
    @csrf
    @if (! empty($method))
        @method($method)
    @endif

    <div class="crm-field-grid">
        <div class="crm-field full">
            <label for="title">Title</label>
            <input id="title" name="title" value="{{ old('title', $developmentRequest->title ?? '') }}" placeholder="Enter development request title" required>
        </div>
        <div class="crm-field">
            <label for="owner_id">Owner</label>
            <select id="owner_id" name="owner_id">
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((int) old('owner_id', $developmentRequest->owner_id ?? auth()->id()) === $owner->id)>{{ $owner->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="requested_by">Requested by</label>
            <input id="requested_by" name="requested_by" value="{{ old('requested_by', $developmentRequest->requested_by ?? '') }}" placeholder="Enter requester name">
        </div>
        <div class="crm-field">
            <label for="customer_id">Customer</label>
            <select id="customer_id" name="customer_id">
                <option value="">Select a customer</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected((int) old('customer_id', $developmentRequest->customer_id ?? null) === $customer->id)>{{ $customer->company_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="lead_id">Lead</label>
            <select id="lead_id" name="lead_id">
                <option value="">Select a lead</option>
                @foreach ($leads as $lead)
                    <option value="{{ $lead->id }}" @selected((int) old('lead_id', $developmentRequest->lead_id ?? null) === $lead->id)>{{ $lead->company_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="contact_id">Contact</label>
            <select id="contact_id" name="contact_id">
                <option value="">Select a contact</option>
                @foreach ($contacts as $contact)
                    <option value="{{ $contact->id }}" @selected((int) old('contact_id', $developmentRequest->contact_id ?? null) === $contact->id)>{{ $contact->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="target_module">Target module</label>
            <input id="target_module" name="target_module" value="{{ old('target_module', $developmentRequest->target_module ?? '') }}" placeholder="Enter target module or area">
        </div>
        <div class="crm-field">
            <label for="priority">Priority</label>
            <select id="priority" name="priority">
                @foreach ($developmentPriorities as $value => $label)
                    <option value="{{ $value }}" @selected(old('priority', $developmentRequest->priority ?? 'medium') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="dev_status">Status</label>
            <select id="dev_status" name="status">
                @foreach ($developmentStatuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $developmentRequest->status ?? 'backlog') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="due_at">Due at</label>
            <input id="due_at" name="due_at" type="datetime-local" value="{{ old('due_at', isset($developmentRequest) && $developmentRequest->due_at ? $developmentRequest->due_at->format('Y-m-d\TH:i') : '') }}" placeholder="Select due date and time">
        </div>
        <div class="crm-field full">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Describe the requested improvement" required>{{ old('description', $developmentRequest->description ?? '') }}</textarea>
        </div>
        <div class="crm-field full">
            <label for="business_value">Business value</label>
            <textarea id="business_value" name="business_value" placeholder="Explain the expected business value">{{ old('business_value', $developmentRequest->business_value ?? '') }}</textarea>
        </div>
        <div class="crm-field full">
            <label for="next_step">Next step</label>
            <input id="next_step" name="next_step" value="{{ old('next_step', $developmentRequest->next_step ?? '') }}" placeholder="Enter the next delivery step">
        </div>
    </div>

    <div class="form-actions">
        @if (! empty($deleteUrl))
            @include('crm.partials.delete-button', [
                'action' => $deleteUrl,
                'message' => $deleteMessage ?? 'Are you sure you want to permanently delete this development request?',
                'label' => $deleteLabel ?? 'Delete dev item',
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
