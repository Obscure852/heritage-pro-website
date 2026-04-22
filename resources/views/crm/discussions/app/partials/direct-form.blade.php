<form method="POST" action="{{ $action }}" class="crm-form" enctype="multipart/form-data">
    @csrf

    @if ($sourceContext)
        <input type="hidden" name="source_type" value="{{ $sourceContext['type'] }}">
        <input type="hidden" name="source_id" value="{{ $sourceContext['id'] }}">
        @include('crm.partials.helper-text', [
            'title' => 'Commercial Source',
            'content' => 'This direct message is linked to ' . $sourceContext['title'] . '. The latest private PDF is attached to the first message automatically.',
        ])
    @endif

    <div class="crm-field-grid">
        <div class="crm-field full">
            <label for="recipient_user_id">Recipient</label>
            <select id="recipient_user_id" name="recipient_user_id" required>
                <option value="">Select a CRM user</option>
                @foreach ($crmUsers as $crmUser)
                    @continue((int) $crmUser->id === (int) auth()->id())
                    <option value="{{ $crmUser->id }}" @selected((int) old('recipient_user_id', request('recipient_user_id')) === (int) $crmUser->id)>
                        {{ $crmUser->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="crm-field full">
            <label for="subject">Conversation label</label>
            <input
                id="subject"
                name="subject"
                value="{{ old('subject', $sourceContext['subject'] ?? request('subject')) }}"
                placeholder="Optional subject for internal context"
            >
        </div>

        <div class="crm-field full">
            <label for="notes">Internal notes</label>
            <textarea id="notes" name="notes" placeholder="Add private notes for this direct thread">{{ old('notes', request('notes')) }}</textarea>
        </div>

        <div class="crm-field full">
            <label for="body">Opening message</label>
            <textarea id="body" name="body" placeholder="Write the first message">{{ old('body', $sourceContext['body'] ?? request('body')) }}</textarea>
        </div>

        @include('crm.discussions.partials.attachment-dropzone', [
            'inputId' => 'direct-attachments',
            'title' => 'Attachments',
            'hint' => 'Share images, PDFs, or DOCX files inside the direct message thread.',
        ])
    </div>

    <div class="form-actions">
        <a href="{{ $cancelUrl }}" class="btn btn-light crm-btn-light"><i class="bx bx-arrow-back"></i> Cancel</a>
        <button type="submit" class="btn btn-primary btn-loading">
            <span class="btn-text"><i class="bx bx-send"></i> Start conversation</span>
            <span class="btn-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Starting...</span>
        </button>
    </div>
</form>
