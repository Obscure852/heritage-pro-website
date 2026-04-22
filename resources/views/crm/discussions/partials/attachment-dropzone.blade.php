@php
    $inputId = $inputId ?? 'attachments';
    $inputName = $inputName ?? 'attachments[]';
    $accept = $accept ?? '.pdf,.doc,.docx,.jpg,.jpeg,.png,.webp';
    $title = $title ?? 'Attachments';
    $hint = $hint ?? 'Upload files to include with this message.';
@endphp

<div class="crm-field full">
    <label for="{{ $inputId }}">{{ $title }}</label>
    <div class="crm-dropzone" data-dropzone>
        <input
            id="{{ $inputId }}"
            name="{{ $inputName }}"
            type="file"
            class="crm-dropzone-input"
            accept="{{ $accept }}"
            multiple
            data-dropzone-input
        >
        <div class="crm-dropzone-copy">
            <span class="crm-dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></span>
            <strong>Drag and drop files here</strong>
            <p>{{ $hint }}</p>
        </div>
        <div class="crm-dropzone-list" data-dropzone-list>
            <div class="crm-dropzone-empty">No files selected yet.</div>
        </div>
    </div>
</div>
