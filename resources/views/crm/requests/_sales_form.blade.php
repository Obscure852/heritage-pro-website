<form method="POST" action="{{ $action }}" class="crm-form" enctype="multipart/form-data">
    @csrf
    @if (! empty($method))
        @method($method)
    @endif

    <input type="hidden" name="type" value="sales">

    <div class="crm-help">Use this form only for lead-stage sales work such as cold calls, demos, proposals, procurement follow-up, and purchase conversations.</div>

    <div class="crm-field-grid">
        <div class="crm-field full">
            <label for="title">Sales item title</label>
            <input id="title" name="title" value="{{ old('title', $crmRequest->title ?? '') }}" placeholder="Enter sales item title" required>
        </div>
        <div class="crm-field">
            <label for="owner_id">Owner</label>
            <select id="owner_id" name="owner_id">
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((int) old('owner_id', $crmRequest->owner_id ?? auth()->id()) === $owner->id)>{{ $owner->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="lead_id">Lead</label>
            <select id="lead_id" name="lead_id" required>
                <option value="">Select a lead</option>
                @foreach ($leads as $lead)
                    <option value="{{ $lead->id }}" @selected((int) old('lead_id', $crmRequest->lead_id ?? null) === $lead->id)>{{ $lead->company_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="contact_id">Lead contact</label>
            <select id="contact_id" name="contact_id">
                <option value="">Select a contact</option>
                @foreach ($contacts as $contact)
                    <option value="{{ $contact->id }}" @selected((int) old('contact_id', $crmRequest->contact_id ?? null) === $contact->id)>{{ $contact->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="sales_stage_id">Sales stage</label>
            <select id="sales_stage_id" name="sales_stage_id" required>
                <option value="">Select a stage</option>
                @foreach ($salesStages as $stage)
                    <option value="{{ $stage->id }}" @selected((int) old('sales_stage_id', $crmRequest->sales_stage_id ?? null) === $stage->id)>{{ $stage->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="outcome">Outcome</label>
            <select id="outcome" name="outcome">
                <option value="">Select an outcome</option>
                @foreach ($requestOutcomes as $value => $label)
                    <option value="{{ $value }}" @selected(old('outcome', $crmRequest->outcome ?? 'pending') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="crm-field">
            <label for="next_action">Next action</label>
            <input id="next_action" name="next_action" value="{{ old('next_action', $crmRequest->next_action ?? '') }}" placeholder="Enter the next sales step">
        </div>
        <div class="crm-field">
            <label for="next_action_at">Next action due</label>
            <input id="next_action_at" name="next_action_at" type="datetime-local" value="{{ old('next_action_at', isset($crmRequest) && $crmRequest->next_action_at ? $crmRequest->next_action_at->format('Y-m-d\\TH:i') : '') }}" placeholder="Select follow-up date and time">
        </div>
        <div class="crm-field">
            <label for="last_contact_at">Last contact</label>
            <input id="last_contact_at" name="last_contact_at" type="datetime-local" value="{{ old('last_contact_at', isset($crmRequest) && $crmRequest->last_contact_at ? $crmRequest->last_contact_at->format('Y-m-d\\TH:i') : '') }}" placeholder="Select last contact date and time">
        </div>
        <div class="crm-field">
            <label for="closed_at">Closed at</label>
            <input id="closed_at" name="closed_at" type="datetime-local" value="{{ old('closed_at', isset($crmRequest) && $crmRequest->closed_at ? $crmRequest->closed_at->format('Y-m-d\\TH:i') : '') }}" placeholder="Select closed date and time">
        </div>
        <div class="crm-field full">
            <label for="description">Sales notes</label>
            <textarea id="description" name="description" placeholder="Summarize the call, proposal, objections, or buying signals">{{ old('description', $crmRequest->description ?? '') }}</textarea>
        </div>

        @include('crm.requests._attachment_dropzone')
    </div>

    <div class="form-actions">
        @if (! empty($deleteUrl))
            @include('crm.partials.delete-button', [
                'action' => $deleteUrl,
                'message' => $deleteMessage ?? 'Are you sure you want to permanently delete this sales request?',
                'label' => $deleteLabel ?? 'Delete sales request',
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
