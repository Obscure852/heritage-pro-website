@php
    $inputId = $inputId ?? 'attachments';
    $inputName = $inputName ?? 'attachments[]';
    $accept = $accept ?? '.pdf,.doc,.docx,.jpg,.jpeg,.png,.webp';
    $title = $title ?? 'Attachments';
    $hint = $hint ?? 'Upload files to include with this message.';
@endphp

<div class="crm-field full">
    <label for="{{ $inputId }}">{{ $title }}</label>
    <div class="crm-dropzone crm-discussion-dropzone" data-dropzone>
        <input
            id="{{ $inputId }}"
            name="{{ $inputName }}"
            type="file"
            class="crm-dropzone-input"
            accept="{{ $accept }}"
            multiple
            data-dropzone-input
        >
        <div class="crm-discussion-dropzone-head">
            <span class="crm-discussion-dropzone-icon"><i class="bx bx-paperclip"></i></span>
            <div class="crm-discussion-dropzone-copy">
                <strong>{{ $title }}</strong>
                <p>{{ $hint }}</p>
            </div>
            <span class="crm-discussion-dropzone-trigger">Choose files</span>
        </div>
        <div class="crm-discussion-dropzone-meta">
            <span><i class="bx bx-move"></i> Drag and drop</span>
            <span><i class="bx bx-file"></i> PDF, DOC, DOCX, images</span>
        </div>
        <div class="crm-dropzone-list" data-dropzone-list>
            <div class="crm-dropzone-empty">No files selected yet.</div>
        </div>
    </div>
</div>
